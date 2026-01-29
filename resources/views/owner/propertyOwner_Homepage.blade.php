@extends('layouts.app')

@section('title', 'Property Owner - Legatura')

@section('content')
    <div class="property-owner-homepage">
        <!-- Post Project Section -->
        <div class="post-project-section">
            <div class="post-project-container">
                <div class="avatar-container">
                    <div class="user-avatar navbar-avatar-initials">
                        <span>ES</span>
                    </div>
                </div>
                <button type="button" class="post-project-button" id="openPostModalBtn" aria-label="Post your project">
                    <span class="post-project-text">Post your project ...</span>
                </button>
            </div>
        </div>

        <!-- Main Content -->
        <div class="main-content">
            <div class="content-container">
                <!-- Section Header -->
                <div class="section-header">
                    <h2 class="section-title">Popular Contractors</h2>
                    <div class="filter-container">
                        <button type="button" class="filter-icon-btn" id="filterIconBtn" aria-label="Filter contractors">
                            <i class="fi fi-rr-filter"></i>
                            <span class="filter-badge hidden" id="filterBadge"></span>
                        </button>
                        <div class="filter-dropdown" id="filterDropdown">
                            <div class="filter-dropdown-content">
                                <div class="filter-dropdown-header">
                                    <h3 class="filter-dropdown-title">
                                        <i class="fi fi-rr-filter"></i>
                                        Filter Contractors
                                    </h3>
                                    <button type="button" class="filter-close-btn" id="filterCloseBtn" aria-label="Close filter">
                                        <i class="fi fi-rr-cross-small"></i>
                                    </button>
                                </div>
                                <div class="filter-dropdown-body">
                                    <div class="filter-group">
                                        <label class="filter-label">
                                            <i class="fi fi-rr-wrench"></i>
                                            Contractor Type
                                        </label>
                                        <select class="filter-select" id="filterContractorType">
                                            <option value="">All Types</option>
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
                                            <i class="fi fi-rr-star"></i>
                                            Minimum Rating
                                        </label>
                                        <select class="filter-select" id="filterRating">
                                            <option value="">Any Rating</option>
                                            <option value="4.5">4.5+ Stars</option>
                                            <option value="4.0">4.0+ Stars</option>
                                            <option value="3.5">3.5+ Stars</option>
                                            <option value="3.0">3.0+ Stars</option>
                                        </select>
                                    </div>
                                    <div class="filter-group">
                                        <label class="filter-label">
                                            <i class="fi fi-rr-briefcase"></i>
                                            Minimum Experience (Years)
                                        </label>
                                        <select class="filter-select" id="filterExperience">
                                            <option value="">Any Experience</option>
                                            <option value="10">10+ Years</option>
                                            <option value="20">20+ Years</option>
                                            <option value="30">30+ Years</option>
                                            <option value="40">40+ Years</option>
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

                <!-- Contractors Grid -->
                <div class="contractors-grid" id="contractorsGrid">
                    <!-- Contractors will be loaded here dynamically -->
                </div>

                <!-- Empty State -->
                <div class="empty-state hidden" id="emptyState">
                    <i class="fi fi-rr-user"></i>
                    <p>No contractors found</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Contractor Card Template (Hidden) -->
    <template id="contractorCardTemplate">
        <div class="contractor-card">
            <div class="contractor-header">
                <div class="contractor-avatar">
                    <span class="contractor-initials"></span>
                </div>
                <div class="contractor-info">
                    <h3 class="contractor-name"></h3>
                    <p class="contractor-experience"></p>
                </div>
                <div class="contractor-badge">
                    <span class="badge-text"></span>
                </div>
            </div>
            
            <div class="contractor-details">
                <div class="detail-item">
                    <i class="fi fi-rr-marker"></i>
                    <span class="detail-text location-text"></span>
                </div>
                <div class="detail-item">
                    <i class="fi fi-rr-star"></i>
                    <span class="detail-text rating-text"></span>
                </div>
                <div class="detail-item">
                    <i class="fi fi-rr-briefcase"></i>
                    <span class="detail-text projects-text"></span>
                </div>
                <div class="detail-item">
                    <i class="fi fi-rr-wrench"></i>
                    <span class="detail-text specialty-text"></span>
                </div>
            </div>

            <div class="contractor-actions">
                <button class="contact-button" data-contractor-id="">
                    <i class="fi fi-rr-envelope"></i>
                    <span>Contact</span>
                </button>
            </div>
        </div>
    </template>

    <!-- Include Post Project Modal -->
    @include('owner.propertyOwner_Modals.ownerPosting_Modal')
@endsection

@section('extra_css')
    <link rel="stylesheet" href="{{ asset('css/owner/propertyOwner_Homepage.css') }}">
    <link rel="stylesheet" href="{{ asset('css/owner/propertyOwner_Modals/ownerPosting_Modal.css') }}">
@endsection

@section('extra_js')
    <script src="{{ asset('js/owner/propertyOwner_Homepage.js') }}"></script>
    <script src="{{ asset('js/owner/propertyOwner_Modals/ownerPosting_Modal.js') }}"></script>
    <script>
        // Update navbar search placeholder and set active link when on property owner homepage
        document.addEventListener('DOMContentLoaded', () => {
            const navbarSearchInput = document.querySelector('.navbar-search-input');
            if (navbarSearchInput) {
                navbarSearchInput.placeholder = 'Search contractors...';
            }
            
            // Set Home link as active
            const navbarLinks = document.querySelectorAll('.navbar-link');
            navbarLinks.forEach(link => {
                link.classList.remove('active');
                if (link.textContent.trim() === 'Home' || link.getAttribute('href') === '{{ route("owner.homepage") }}') {
                    link.classList.add('active');
                }
            });
        });
    </script>
@endsection
