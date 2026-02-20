@extends('layouts.appContractor')

@section('title', 'Contractor - Legatura')

@section('content')
    <div class="contractor-homepage">
        <!-- Main Content -->
        <div class="main-content">
            <div class="content-container">
                <!-- Section Header -->
                <div class="section-header">
                    <h2 class="section-title">Available Projects</h2>
                    <div class="filter-container">
                        <button type="button" class="filter-icon-btn" id="filterIconBtn" aria-label="Filter projects">
                            <i class="fi fi-rr-filter"></i>
                            <span class="filter-badge hidden" id="filterBadge"></span>
                        </button>
                        <div class="filter-dropdown" id="filterDropdown">
                            <div class="filter-dropdown-content">
                                <div class="filter-dropdown-header">
                                    <h3 class="filter-dropdown-title">
                                        <i class="fi fi-rr-filter"></i>
                                        Filter Projects
                                    </h3>
                                    <button type="button" class="filter-close-btn" id="filterCloseBtn" aria-label="Close filter">
                                        <i class="fi fi-rr-cross-small"></i>
                                    </button>
                                </div>
                                <div class="filter-dropdown-body">
                                    {{-- Status Filter Removed --}}
                                    <div class="filter-group">
                                        <label class="filter-label">
                                            <i class="fi fi-rr-marker"></i>
                                            Location (Province)
                                        </label>
                                        <select class="filter-select" id="filterProvince">
                                            <option value="">All Provinces</option>
                                        </select>
                                    </div>
                                    <div class="filter-group">
                                        <label class="filter-label">
                                            <i class="fi fi-rr-marker"></i>
                                            Location (City)
                                        </label>
                                        <select class="filter-select" id="filterCity">
                                            <option value="">All Cities</option>
                                        </select>
                                    </div>
                                    <div class="filter-group">
                                        <label class="filter-label">
                                            <i class="fi fi-rr-briefcase"></i>
                                            Property Type
                                        </label>
                                        <select class="filter-select" id="filterPropertyType">
                                            <option value="">All Types</option>
                                            @if(isset($propertyTypes))
                                                @foreach($propertyTypes as $type)
                                                    <option value="{{ $type }}">{{ ucfirst(str_replace('_', ' ', $type)) }}</option>
                                                @endforeach
                                            @endif
                                        </select>
                                    </div>
                                    <div class="filter-group">
                                        <label class="filter-label">
                                            <i class="fi fi-rr-dollar"></i>
                                            Budget Range
                                        </label>
                                        <div class="budget-range-container" style="display: flex; gap: 8px; align-items: center;">
                                            <input type="number" id="filterBudgetMin" class="filter-select" placeholder="Min" min="0" style="width: 50%;">
                                            <span style="color: #64748B;">-</span>
                                            <input type="number" id="filterBudgetMax" class="filter-select" placeholder="Max" min="0" style="width: 50%;">
                                        </div>
                                    </div>
                                </div>
                                <div class="filter-dropdown-footer">
                                    <button type="button" class="btn-filter-clear" id="filterClearBtn">
                                        <i class="fi fi-rr-cross-small"></i>
                                        Clear
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Projects Grid -->
                <div class="projects-grid" id="projectsGrid">
                    @if(isset($projects) && count($projects) > 0)
                        @foreach($projects as $project)
                            <div class="project-card">
                                <div class="project-header">
                                    <div class="project-owner-info">
                                        <div class="project-owner-avatar">
                                            @php
                                                $ownerName = trim($project->owner_name ?? '');
                                                $initials = collect(explode(' ', $ownerName))->filter()->map(function($w){ return strtoupper(substr($w,0,1)); })->take(2)->join('');
                                            @endphp
                                            <span class="owner-initials">{{ $initials ?: '—' }}</span>
                                        </div>
                                        <div class="project-owner-details">
                                            <h3 class="project-owner-name">{{ $project->owner_name ?? '—' }}</h3>
                                            <p class="project-posted-date">{{ isset($project->created_at) ? \Carbon\Carbon::parse($project->created_at)->format('M d, Y') : '—' }}</p>
                                        </div>
                                    </div>
                                    @php
                                        $deadlineRaw = $project->bidding_due ?? $project->bidding_deadline ?? null;
                                        $daysLeft = $deadlineRaw ? (int) now()->startOfDay()->diffInDays(\Carbon\Carbon::parse($deadlineRaw)->startOfDay(), false) : null;
                                    @endphp
                                    @if($daysLeft !== null && $daysLeft >= 0)
                                        <span class="deadline-badge @if($daysLeft <= 3) urgent @endif">
                                            @if($daysLeft === 0) Due Today
                                            @elseif($daysLeft === 1) 1 day left
                                            @else {{ $daysLeft }} days left
                                            @endif
                                        </span>
                                    @endif
                                </div>

                                <div class="project-image-wrapper">
                                    @php
                                        // Filter out important docs — only show design/optional images on the card
                                        $cardImages = [];
                                        if (!empty($project->files) && is_iterable($project->files)) {
                                            foreach ($project->files as $f) {
                                                $fPath = is_string($f) ? $f : (is_array($f) ? ($f['file_path'] ?? '') : ($f->file_path ?? ''));
                                                $fType = is_object($f) ? ($f->file_type ?? '') : (is_array($f) ? ($f['file_type'] ?? '') : '');
                                                $lType = strtolower($fType);
                                                $lPath = strtolower($fPath);

                                                // Skip important documents
                                                $isCritical = false;
                                                if ($lType === 'title' || $lType === 'building permit') $isCritical = true;
                                                elseif (preg_match('/building.?permit|title_of_land|title-of-land|land.?title/i', $lType)) $isCritical = true;
                                                elseif (str_contains($lPath, 'building') && str_contains($lPath, 'permit')) $isCritical = true;
                                                elseif (str_contains($lPath, 'title') && str_contains($lPath, 'land')) $isCritical = true;
                                                elseif (str_contains($lPath, 'project_files/titles/')) $isCritical = true;

                                                if (!$isCritical && $fPath) {
                                                    $cardImages[] = asset('storage/' . ltrim($fPath, '/'));
                                                }
                                            }
                                        } elseif (!empty($project->image_path)) {
                                            $cardImages[] = asset('storage/' . ltrim($project->image_path, '/'));
                                        }
                                        $imageSrc = count($cardImages) > 0 ? $cardImages[0] : '';
                                    @endphp
                                    @if($imageSrc)
                                        <img src="{{ $imageSrc }}" alt="" class="project-image" onerror="this.style.display='none';">
                                    @endif
                                </div>

                                <div class="header-content">
                                    <h3 class="project-title">{{ $project->project_title ?? '—' }}</h3>
                                    <p class="project-description">{{ Str::limit($project->project_description ?? '', 200) }}</p>
                                </div>

                                <div class="project-details">
                                    <div class="detail-item">
                                        <i class="fi fi-rr-briefcase"></i>
                                        <span class="detail-text type-text">{{ $project->type_name ?? $project->property_type ?? '—' }}</span>
                                    </div>
                                    <div class="detail-item">
                                        <i class="fi fi-rr-marker"></i>
                                        <span class="detail-text location-text">{{ $project->project_location ?? '—' }}</span>
                                    </div>
                                    <div class="detail-item">
                                        <i class="fi fi-rr-dollar"></i>
                                        <span class="detail-text budget-text">
                                            @if(!empty($project->budget_range_min) && !empty($project->budget_range_max))
                                                ₱{{ number_format($project->budget_range_min) }} - ₱{{ number_format($project->budget_range_max) }}
                                            @else
                                                —
                                            @endif
                                        </span>
                                    </div>
                                    <div class="detail-item">
                                        <i class="fi fi-rr-gavel"></i>
                                        <span class="detail-text bids-text">{{ $project->bids_count ?? 0 }} bids</span>
                                    </div>
                                </div>

                                <div class="project-actions">
                                    <button class="apply-bid-button" data-project-id="{{ $project->project_id ?? '' }}">
                                        <i class="fi fi-rr-hand-holding-usd"></i>
                                        <span>Apply Bid</span>
                                    </button>
                                </div>
                            </div>
                        @endforeach
                    @else
                        {{-- No projects - keep empty state below visible via JS/CSS if needed --}}
                    @endif
                </div>

                <!-- Empty State -->
                <div class="empty-state hidden" id="emptyState">
                    <i class="fi fi-rr-folder-open"></i>
                    <p>No projects found</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Project Card Template (Hidden) -->
    <template id="projectCardTemplate">
        <div class="project-card">
            <div class="project-header">
                <div class="project-owner-info">
                    <div class="project-owner-avatar">
                        <span class="owner-initials"></span>
                    </div>
                    <div class="project-owner-details">
                        <h3 class="project-owner-name"></h3>
                        <p class="project-posted-date"></p>
                    </div>
                </div>
                <div class="project-status-badge">
                    <span class="status-text"></span>
                </div>
            </div>

            <div class="project-content">
                <h3 class="project-title"></h3>
                <p class="project-description"></p>
            </div>

            <div class="project-details">
                <div class="detail-item">
                    <i class="fi fi-rr-marker"></i>
                    <span class="detail-text location-text"></span>
                </div>
                <div class="detail-item">
                    <i class="fi fi-rr-calendar"></i>
                    <span class="detail-text deadline-text"></span>
                </div>
                <div class="detail-item">
                    <i class="fi fi-rr-briefcase"></i>
                    <span class="detail-text type-text"></span>
                </div>
                <div class="detail-item">
                    <i class="fi fi-rr-dollar"></i>
                    <span class="detail-text budget-text"></span>
                </div>
            </div>

            <div class="project-media-grid" style="display: none;">
                <!-- Images will be injected here by JS -->
            </div>

            <div class="project-actions">
                <button class="apply-bid-button" data-project-id="" onclick="event.stopPropagation(); openBidModal(this.getAttribute('data-project-id'))">
                    <i class="fi fi-rr-hand-holding-usd"></i>
                    <span>Apply Bid</span>
                </button>
            </div>
        </div>
    </template>

    <!-- Include Apply Bid Modals (Populated via PHP) -->
    @if(isset($projects) && count($projects) > 0)
        @foreach($projects as $project)
            @include('contractor.contractor_Modals.contractorApplybids_Modal', ['project' => $project])
        @endforeach
    @endif

    <!-- Include Project Details Modals (Populated via PHP) -->
    @if(isset($projects) && count($projects) > 0)
        @foreach($projects as $project)
            @include('contractor.contractor_Modals.projectPostDetails_Modal', ['project' => $project])
        @endforeach
    @endif

    <!-- Include Budget Warning Modals (Populated via PHP) -->
    @if(isset($projects) && count($projects) > 0)
        @foreach($projects as $project)
            @include('contractor.contractor_Modals.budgetWarning_Modal', ['project' => $project])
        @endforeach
    @endif
@endsection

@section('extra_css')
    <link rel="stylesheet" href="{{ asset('css/contractor/contractor_Homepage.css') }}?v={{ time() }}">
    <link rel="stylesheet" href="{{ asset('css/contractor/contractor_Modals/contractorApplybids_Modal.css') }}">
    <link rel="stylesheet" href="{{ asset('css/contractor/contractor_Modals/projectPostDetails_Modal.css') }}">
@endsection

@section('extra_js')
    <script>
        window.userId = {{ session('user')->user_id ?? 'null' }};
    </script>
    @if(isset($jsProjects))
        <script>
            window.serverProjects = {!! json_encode($jsProjects, JSON_UNESCAPED_SLASHES) !!};
        </script>
    @endif
    <script src="{{ asset('js/contractor/contractor_Homepage.js') }}"></script>
    <script src="{{ asset('js/contractor/contractor_Modals/contractorApplybids_Modal.js') }}"></script>
    <script src="{{ asset('js/contractor/contractor_Modals/projectPostDetails_Modal.js') }}"></script>
    <script>
        // Update navbar search placeholder and set active link when on contractor homepage
        document.addEventListener('DOMContentLoaded', () => {
            const navbarSearchInput = document.querySelector('.navbar-search-input');
            if (navbarSearchInput) {
                navbarSearchInput.placeholder = 'Search projects...';
            }

            // Set Home link as active
            const navbarLinks = document.querySelectorAll('.navbar-link');
            navbarLinks.forEach(link => {
                link.classList.remove('active');
                if (link.textContent.trim() === 'Home') {
                    link.classList.add('active');
                }
            });
        });
    </script>
@endsection
