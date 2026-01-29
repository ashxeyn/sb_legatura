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
                                    <div class="filter-group">
                                        <label class="filter-label">
                                            <i class="fi fi-rr-flag"></i>
                                            Project Status
                                        </label>
                                        <select class="filter-select" id="filterProjectStatus">
                                            <option value="">All Status</option>
                                            <option value="pending">Pending</option>
                                            <option value="active">Active</option>
                                            <option value="open">Open for Bids</option>
                                        </select>
                                    </div>
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
                                            Project Type
                                        </label>
                                        <select class="filter-select" id="filterProjectType">
                                            <option value="">All Types</option>
                                            <option value="residential">Residential</option>
                                            <option value="commercial">Commercial</option>
                                            <option value="industrial">Industrial</option>
                                        </select>
                                    </div>
                                    <div class="filter-group">
                                        <label class="filter-label">
                                            <i class="fi fi-rr-dollar"></i>
                                            Budget Range
                                        </label>
                                        <select class="filter-select" id="filterBudget">
                                            <option value="">Any Budget</option>
                                            <option value="0-50000">₱0 - ₱50,000</option>
                                            <option value="50000-100000">₱50,000 - ₱100,000</option>
                                            <option value="100000-500000">₱100,000 - ₱500,000</option>
                                            <option value="500000+">₱500,000+</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="filter-dropdown-footer">
                                    <button type="button" class="btn-filter-clear" id="filterClearBtn">
                                        <i class="fi fi-rr-cross-small"></i>
                                        Clear
                                    </button>
                                    <button type="button" class="btn-filter-apply" id="filterApplyBtn">
                                        <i class="fi fi-rr-check"></i>
                                        Apply
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Projects Grid -->
                <div class="projects-grid" id="projectsGrid">
                    <!-- Projects will be loaded here dynamically -->
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
            
            <div class="project-image-wrapper">
                <img src="" alt="" class="project-image" onerror="this.style.display='none';">
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

            <div class="project-actions">
                <button class="apply-bid-button" data-project-id="">
                    <i class="fi fi-rr-hand-holding-usd"></i>
                    <span>Apply Bid</span>
                </button>
            </div>
        </div>
    </template>

    <!-- Include Apply Bid Modal -->
    @include('contractor.contractor_Modals.contractorApplybids_Modal')
@endsection

@section('extra_css')
    <link rel="stylesheet" href="{{ asset('css/contractor/contractor_Homepage.css') }}?v={{ time() }}">
    <link rel="stylesheet" href="{{ asset('css/contractor/contractor_Modals/contractorApplybids_Modal.css') }}">
@endsection

@section('extra_js')
    <script src="{{ asset('js/contractor/contractor_Homepage.js') }}"></script>
    <script src="{{ asset('js/contractor/contractor_Modals/contractorApplybids_Modal.js') }}"></script>
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
