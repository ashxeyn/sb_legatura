@extends('layouts.appContractor')

@section('title', 'Profile - Legatura')

@section('content')
    <div class="contractor-profile min-h-screen bg-gray-50">
        <!-- Profile Header with Cover Image -->
        <div class="profile-header-section">
            <div class="profile-cover-container">
                <div class="profile-cover-image" id="profileCoverImage">
                    <img src="https://images.unsplash.com/photo-1519389950473-47ba0277781c?w=1600&h=400&fit=crop" alt="Cover" class="cover-img" id="coverPhotoImg" onerror="this.src='https://via.placeholder.com/1600x400/667eea/ffffff?text=Cover+Photo'">
                </div>
                <button class="profile-back-btn" onclick="window.history.back()">
                    <i class="fi fi-rr-arrow-left"></i>
                    <span>Back</span>
                </button>
                <button class="profile-cover-edit-btn" id="editCoverPhotoBtn" aria-label="Edit cover photo">
                    <i class="fi fi-rr-camera"></i>
                    <span>Edit Cover Photo</span>
                </button>
                <input type="file" id="coverPhotoInput" accept="image/*" style="display: none;">
            </div>

            <!-- Profile Info Section -->
            <div class="profile-info-section">
                <div class="profile-info-container">
                    <!-- Profile Picture -->
                    <div class="profile-picture-container">
                        <div class="profile-picture" id="profilePicture">
                            <span class="profile-picture-initials">BC</span>
                        </div>
                    </div>

                    <!-- Profile Details -->
                    <div class="profile-details">
                        <h1 class="profile-name" id="profileName">BuildRight Construction</h1>
                        <div class="profile-meta">
                            <div class="profile-rating">
                                <i class="fi fi-rr-star"></i>
                                <span id="profileRating">4.8</span>
                                <span class="profile-rating-label">Rating</span>
                            </div>
                            <div class="profile-location">
                                <i class="fi fi-rr-marker"></i>
                                <span id="profileLocation">Manila, Philippines</span>
                            </div>
                        </div>
                    </div>

                    <!-- Action Button -->
                    <div class="profile-actions">
                        <button class="send-message-btn" id="sendMessageBtn">
                            <i class="fi fi-rr-paper-plane"></i>
                            <span>Send Message</span>
                        </button>
                    </div>
                </div>
            </div>

            <!-- Navigation Tabs -->
            <div class="profile-tabs">
                <button class="profile-tab active" data-tab="portfolio">
                    Portfolio
                </button>
                <button class="profile-tab" data-tab="highlights">
                    Highlights
                </button>
                <button class="profile-tab" data-tab="reviews">
                    Reviews
                </button>
            </div>
        </div>

        <!-- Main Content Area -->
        <div class="profile-content-section">
            <div class="profile-content-container">
                <!-- Left Column - Info Card (Fixed, Not Scrollable) -->
                <div class="profile-left-column">
                    <div class="profile-info-card">
                        <div class="info-card-header">
                            <h3 class="info-card-name" id="infoCardName">BuildRight Construction</h3>
                            <div class="info-card-meta">
                                <div class="info-card-rating">
                                    <i class="fi fi-rr-star"></i>
                                    <span id="infoCardRating">4.8</span>
                                    <span>Rating</span>
                                </div>
                                <div class="info-card-location">
                                    <i class="fi fi-rr-marker"></i>
                                    <span id="infoCardLocation">Manila, Philippines</span>
                                </div>
                            </div>
                        </div>

                        <div class="info-card-bio">
                            <p id="infoCardBio">BuildRight Construction is a leading construction company specializing in residential and commercial projects. With over 15 years of experience, we deliver quality craftsmanship and exceptional service to our clients.</p>
                        </div>

                        <div class="info-card-occupation">
                            <span class="occupation-label">Specialization:</span>
                            <span class="occupation-value" id="occupationValue">General Contractor</span>
                        </div>

                        <div class="info-card-stats">
                            <div class="stat-item">
                                <span class="stat-label">Projects completed:</span>
                                <span class="stat-value" id="projectsDone">128</span>
                            </div>
                            <div class="stat-item">
                                <span class="stat-label">Active projects:</span>
                                <span class="stat-value" id="ongoingProjects">5</span>
                            </div>
                        </div>

                        <div class="info-card-contact">
                            <div class="contact-item">
                                <i class="fi fi-rr-phone-call"></i>
                                <span>Contact No.: <span id="contactNumber">+63 912 345 6789</span></span>
                            </div>
                            <div class="contact-item">
                                <i class="fi fi-rr-envelope"></i>
                                <span>Email: <span id="contactEmail">info@buildrightconstruction.com</span></span>
                            </div>
                            <div class="contact-item">
                                <i class="fi fi-rr-phone"></i>
                                <span>Telephone: <span id="telephone">02 1234 5678</span></span>
                            </div>
                        </div>
                    </div>

                    <!-- Contact Person Card -->
                    <div class="profile-contact-card">
                        <div class="contact-card-header">
                            <div class="contact-card-avatar">
                                <img src="https://via.placeholder.com/60x60/EEA24B/FFFFFF?text=OFP" alt="Olive Faith Padios" id="contactPersonAvatar" onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                                <div class="contact-card-avatar-initials" style="display: none;">OFP</div>
                            </div>
                            <div class="contact-card-name-section">
                                <h3 class="contact-card-name" id="contactPersonName">Olive Faith Padios</h3>
                                <p class="contact-card-role" id="contactPersonRole">Secretary/Contact person</p>
                            </div>
                        </div>

                        <div class="contact-card-divider"></div>

                        <div class="contact-card-info">
                            <div class="contact-card-contact-item">
                                <i class="fi fi-rr-phone-call"></i>
                                <span>Contact No.: <span class="contact-card-value" id="contactPersonPhone">+63 912 345 6789</span></span>
                            </div>
                            <div class="contact-card-contact-item">
                                <i class="fi fi-rr-envelope"></i>
                                <span>Email: <span class="contact-card-value" id="contactPersonEmail">pcc_official@gmail.com</span></span>
                            </div>
                            <div class="contact-card-contact-item">
                                <i class="fi fi-rr-phone"></i>
                                <span>Telephone: <span class="contact-card-value" id="contactPersonTelephone">061 234 5678</span></span>
                            </div>
                        </div>

                        <div class="contact-card-divider"></div>

                        <div class="contact-card-section">
                            <h4 class="contact-card-section-title">Services Offered:</h4>
                            <ul class="contact-card-services-list">
                                <li>Residential and commercial building projects</li>
                                <li>Renovations and site development</li>
                                <li>Project planning, supervision, and documentation</li>
                            </ul>
                        </div>

                        <div class="contact-card-divider"></div>

                        <div class="contact-card-section">
                            <h4 class="contact-card-section-title">Specialization:</h4>
                            <div class="contact-card-specialization-tags">
                                <span class="specialization-tag">Warehouse</span>
                                <span class="specialization-tag">Factories</span>
                                <span class="specialization-tag">Large-scale</span>
                                <span class="specialization-tag">Modern</span>
                                <span class="specialization-tag">Building</span>
                            </div>
                        </div>

                        <div class="contact-card-divider"></div>

                        <div class="contact-card-stats">
                            <div class="contact-card-stat-item">
                                <span class="contact-card-stat-label">Experience:</span>
                                <span class="contact-card-stat-value" id="contactPersonExperience">50 Years</span>
                            </div>
                            <div class="contact-card-stat-item">
                                <span class="contact-card-stat-label">Projects done:</span>
                                <span class="contact-card-stat-value" id="contactPersonProjectsDone">102</span>
                            </div>
                            <div class="contact-card-stat-item">
                                <span class="contact-card-stat-label">Ongoing projects:</span>
                                <span class="contact-card-stat-value" id="contactPersonOngoingProjects">3</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Right Column - Content Feed (Scrollable) -->
                <div class="profile-right-column">
                    <!-- Filter Section (Highlights Tab Only) -->
                    <div class="projects-filter-section hidden" id="highlightsFilterSection">
                        <div class="section-header">
                            <h2 class="section-title">Highlights</h2>
                            <div class="filter-container">
                                <button type="button" class="filter-icon-btn" id="highlightsFilterIconBtn" aria-label="Filter highlights">
                                    <i class="fi fi-rr-filter"></i>
                                    <span class="filter-badge hidden" id="highlightsFilterBadge"></span>
                                </button>
                                <div class="filter-dropdown" id="highlightsFilterDropdown">
                                    <div class="filter-dropdown-content">
                                        <div class="filter-dropdown-header">
                                            <h3 class="filter-dropdown-title">
                                                <i class="fi fi-rr-filter"></i>
                                                Filter Highlights
                                            </h3>
                                            <button type="button" class="filter-close-btn" id="highlightsFilterCloseBtn" aria-label="Close filter">
                                                <i class="fi fi-rr-cross-small"></i>
                                            </button>
                                        </div>
                                        <div class="filter-dropdown-body">
                                            <div class="filter-group">
                                                <label class="filter-label">
                                                    <i class="fi fi-rr-flag"></i>
                                                    Status
                                                </label>
                                                <select class="filter-select" id="highlightsStatusFilter">
                                                    <option value="all">All Status</option>
                                                    <option value="pending">Pending</option>
                                                    <option value="active">Active</option>
                                                    <option value="in_progress">In Progress</option>
                                                    <option value="completed">Completed</option>
                                                    <option value="cancelled">Cancelled</option>
                                                </select>
                                            </div>
                                            <div class="filter-group">
                                                <label class="filter-label">
                                                    <i class="fi fi-rr-sort"></i>
                                                    Sort By
                                                </label>
                                                <select class="filter-select" id="highlightsSortFilter">
                                                    <option value="newest">Newest First</option>
                                                    <option value="oldest">Oldest First</option>
                                                    <option value="title">Title A-Z</option>
                                                    <option value="status">Status</option>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="filter-dropdown-footer">
                                            <button type="button" class="btn-filter-clear" id="clearHighlightsFiltersBtn">
                                                <i class="fi fi-rr-cross-small"></i>
                                                Clear
                                            </button>
                                            <button type="button" class="btn-filter-apply" id="applyHighlightsFiltersBtn">
                                                <i class="fi fi-rr-check"></i>
                                                Apply
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Portfolio Feed (Portfolio Tab) -->
                    <div class="project-posts-feed" id="portfolioFeed">
                        <!-- Post Input Section -->
                        <div class="post-project-input-section">
                            <div class="post-project-input-container">
                                <div class="post-input-avatar">
                                    <div class="post-input-avatar-circle">
                                        <span class="post-input-initials" id="portfolioPostInputInitials">BC</span>
                                    </div>
                                </div>
                                <button type="button" class="post-project-input-field" id="openPortfolioPostModalBtn" aria-label="Create portfolio post">
                                    <span class="post-input-placeholder">Share your portfolio work...</span>
                                </button>
                            </div>
                        </div>
                        <!-- Portfolio items will be dynamically inserted here -->
                    </div>

                    <!-- Highlights List (Highlights Tab) -->
                    <div class="projects-list-container hidden" id="highlightsListContainer">
                        <!-- Highlights Grid -->
                        <div class="projects-grid" id="highlightsGrid">
                            <!-- Highlights will be dynamically inserted here -->
                        </div>
                    </div>

                    <!-- Reviews Container (Reviews Tab) -->
                    <div class="reviews-container hidden" id="reviewsContainer">
                        <div class="reviews-header">
                            <h2 class="reviews-title">Client Reviews</h2>
                            <div class="reviews-header-divider"></div>
                        </div>
                        <div class="reviews-list" id="reviewsList">
                            <!-- Reviews will be dynamically inserted here -->
                        </div>
                        <div class="reviews-empty-state hidden" id="reviewsEmptyState">
                            <i class="fi fi-rr-star"></i>
                            <h3>No Reviews Yet</h3>
                            <p>Reviews will be displayed here</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Portfolio Post Modal -->
    @include('contractor.contractor_Modals.contractorPortfolioPost_Modal')
@endsection

@section('extra_css')
    <link rel="stylesheet" href="{{ asset('css/contractor/contractor_Profile.css') }}">
    <link rel="stylesheet" href="{{ asset('css/contractor/contractor_Modals/contractorPortfolioPost_Modal.css') }}">
@endsection

@section('extra_js')
    <script src="{{ asset('js/contractor/contractor_Profile.js') }}"></script>
    <script src="{{ asset('js/contractor/contractor_Modals/contractorPortfolioPost_Modal.js') }}"></script>
    <script>
        // Set Profile link as active when on profile page
        document.addEventListener('DOMContentLoaded', () => {
            const navbarLinks = document.querySelectorAll('.navbar-link');
            navbarLinks.forEach(link => {
                link.classList.remove('active');
                if (link.textContent.trim() === 'Profile' || link.getAttribute('href') === '{{ route("contractor.profile") }}') {
                    link.classList.add('active');
                }
            });
            
            // Update navbar search placeholder
            const navbarSearchInput = document.querySelector('.navbar-search-input');
            if (navbarSearchInput) {
                navbarSearchInput.placeholder = 'Search...';
            }
        });
    </script>
@endsection
