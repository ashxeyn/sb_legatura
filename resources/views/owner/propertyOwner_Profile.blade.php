@extends('layouts.app')

@section('title', 'Profile - Legatura')

@section('content')
    <div class="property-owner-profile min-h-screen bg-gray-50">
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
                            <span class="profile-picture-initials">ES</span>
                        </div>
                    </div>

                    <!-- Profile Details -->
                    <div class="profile-details">
                        <h1 class="profile-name" id="profileName">Emmanuelle Santos</h1>
                        <div class="profile-meta">
                            <div class="profile-rating">
                                <i class="fi fi-rr-star"></i>
                                <span id="profileRating">4.7</span>
                                <span class="profile-rating-label">Rating</span>
                            </div>
                            <div class="profile-location">
                                <i class="fi fi-rr-marker"></i>
                                <span id="profileLocation">Zamboanga City</span>
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
                <button class="profile-tab active" data-tab="post">
                    Post
                </button>
                <button class="profile-tab" data-tab="projects">
                    Projects
                </button>
                <button class="profile-tab" data-tab="reviews">
                    Reviews
                </button>
                <button class="profile-tab" data-tab="about">
                    About
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
                            <h3 class="info-card-name" id="infoCardName">Emmanuelle Santos</h3>
                            <div class="info-card-meta">
                                <div class="info-card-rating">
                                    <i class="fi fi-rr-star"></i>
                                    <span id="infoCardRating">4.7</span>
                                    <span>Rating</span>
                                </div>
                                <div class="info-card-location">
                                    <i class="fi fi-rr-marker"></i>
                                    <span id="infoCardLocation">Zamboanga City</span>
                                </div>
                            </div>
                        </div>

                        <div class="info-card-bio">
                            <p id="infoCardBio">Emmanuelle Santos is an active property owner and client on the Legatura platform, managing multiple residential and commercial construction projects within Zamboanga City.</p>
                        </div>

                        <div class="info-card-occupation">
                            <span class="occupation-label">Occupation:</span>
                            <span class="occupation-value" id="occupationValue">Airbnb Host</span>
                        </div>

                        <div class="info-card-stats">
                            <div class="stat-item">
                                <span class="stat-label">Projects done:</span>
                                <span class="stat-value" id="projectsDone">45</span>
                            </div>
                            <div class="stat-item">
                                <span class="stat-label">Ongoing projects:</span>
                                <span class="stat-value" id="ongoingProjects">3</span>
                            </div>
                        </div>

                        <div class="info-card-contact">
                            <div class="contact-item">
                                <i class="fi fi-rr-phone-call"></i>
                                <span>Contact No.: <span id="contactNumber">+63 924 681 24098</span></span>
                            </div>
                            <div class="contact-item">
                                <i class="fi fi-rr-envelope"></i>
                                <span>Email: <span id="contactEmail">emmanuelleSantos@gmail.com</span></span>
                            </div>
                            <div class="contact-item">
                                <i class="fi fi-rr-phone"></i>
                                <span>Telephone: <span id="telephone">061 234 5878</span></span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Right Column - Project Feed (Scrollable) -->
                <div class="profile-right-column">
                    <!-- Filter Section (Projects Tab Only) -->
                    <div class="projects-filter-section hidden" id="projectsFilterSection">
                        <div class="section-header">
                            <h2 class="section-title">Projects</h2>
                            <div class="filter-container">
                                <button type="button" class="filter-icon-btn" id="projectsFilterIconBtn" aria-label="Filter projects">
                                    <i class="fi fi-rr-filter"></i>
                                    <span class="filter-badge hidden" id="projectsFilterBadge"></span>
                                </button>
                                <div class="filter-dropdown" id="projectsFilterDropdown">
                                    <div class="filter-dropdown-content">
                                        <div class="filter-dropdown-header">
                                            <h3 class="filter-dropdown-title">
                                                <i class="fi fi-rr-filter"></i>
                                                Filter Projects
                                            </h3>
                                            <button type="button" class="filter-close-btn" id="projectsFilterCloseBtn" aria-label="Close filter">
                                                <i class="fi fi-rr-cross-small"></i>
                                            </button>
                                        </div>
                                        <div class="filter-dropdown-body">
                                            <div class="filter-group">
                                                <label class="filter-label">
                                                    <i class="fi fi-rr-flag"></i>
                                                    Status
                                                </label>
                                                <select class="filter-select" id="projectsStatusFilter">
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
                                                <select class="filter-select" id="projectsSortFilter">
                                                    <option value="newest">Newest First</option>
                                                    <option value="oldest">Oldest First</option>
                                                    <option value="title">Title A-Z</option>
                                                    <option value="status">Status</option>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="filter-dropdown-footer">
                                            <button type="button" class="btn-filter-clear" id="clearProjectsFiltersBtn">
                                                <i class="fi fi-rr-cross-small"></i>
                                                Clear
                                            </button>
                                            <button type="button" class="btn-filter-apply" id="applyProjectsFiltersBtn">
                                                <i class="fi fi-rr-check"></i>
                                                Apply
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Project Posts Feed (Post Tab) -->
                    <div class="project-posts-feed" id="projectPostsFeed">
                        <!-- Project posts will be dynamically inserted here -->
                    </div>

                    <!-- Projects List (Projects Tab) -->
                    <div class="projects-list-container hidden" id="projectsListContainer">

                        <!-- Projects Grid -->
                        <div class="projects-grid" id="projectsGrid">
                            <!-- Projects will be dynamically inserted here -->
                        </div>
                    </div>

                    <!-- Reviews Container (Reviews Tab) -->
                    <div class="reviews-container hidden" id="reviewsContainer">
                        <!-- Rating Summary -->
                        <div class="reviews-summary" id="reviewsSummary">
                            <div class="reviews-summary-left">
                                <span class="reviews-summary-avg" id="reviewsAvgRating">—</span>
                                <span class="reviews-summary-label">out of 5</span>
                            </div>
                            <div class="reviews-summary-right">
                                <div class="reviews-summary-stars" id="reviewsSummaryStars"></div>
                                <span class="reviews-summary-count" id="reviewsTotalCount">No reviews yet</span>
                            </div>
                        </div>
                        <div class="reviews-section-divider"></div>
                        <div class="reviews-list" id="reviewsList">
                            {{-- Reviews dynamically inserted by JS --}}
                        </div>
                        <div class="reviews-empty-state hidden" id="reviewsEmptyState">
                            <i class="fi fi-rr-star"></i>
                            <h3>No Reviews Yet</h3>
                            <p>Be the first to leave a review</p>
                        </div>
                    </div>

                    <!-- About Section (About Tab) -->
                    <div class="about-section hidden" id="aboutSection" data-profile-id="" data-profile-type="owner">
                        <div class="about-block">
                            <h3 class="about-block-title">Bio</h3>
                            <p class="about-bio-text" id="aboutBioText">Emmanuelle Santos is an active property owner and client on the Legatura platform, managing multiple residential and commercial construction projects within Zamboanga City.</p>
                        </div>
                        <div class="about-divider"></div>
                        <div class="about-block">
                            <h3 class="about-block-title">Property Interests</h3>
                            <div class="about-skills-grid" id="aboutSkillsGrid">
                                <span class="about-skill-tag">Residential</span>
                                <span class="about-skill-tag">Commercial</span>
                                <span class="about-skill-tag">Renovation</span>
                                <span class="about-skill-tag">Airbnb</span>
                            </div>
                        </div>
                        <div class="about-divider"></div>
                        <div class="about-block">
                            <h3 class="about-block-title">Details</h3>
                            <div class="about-details-list">
                                <div class="about-detail-row">
                                    <span class="about-detail-label"><i class="fi fi-rr-briefcase"></i> Occupation</span>
                                    <span class="about-detail-value" id="aboutOccupation">Airbnb Host</span>
                                </div>
                                <div class="about-detail-row">
                                    <span class="about-detail-label"><i class="fi fi-rr-marker"></i> Location</span>
                                    <span class="about-detail-value" id="aboutLocation">Zamboanga City</span>
                                </div>
                                <div class="about-detail-row">
                                    <span class="about-detail-label"><i class="fi fi-rr-check-circle"></i> Projects Completed</span>
                                    <span class="about-detail-value" id="aboutProjectsDone">45</span>
                                </div>
                                <div class="about-detail-row">
                                    <span class="about-detail-label"><i class="fi fi-rr-time-fast"></i> Active Projects</span>
                                    <span class="about-detail-value" id="aboutActiveProjects">3</span>
                                </div>
                            </div>
                        </div>
                        <div class="about-divider"></div>
                        <div class="about-block">
                            <h3 class="about-block-title">Contact Information</h3>
                            <div class="about-details-list">
                                <div class="about-detail-row">
                                    <span class="about-detail-label"><i class="fi fi-rr-phone-call"></i> Mobile</span>
                                    <span class="about-detail-value" id="aboutPhone">+63 924 681 24098</span>
                                </div>
                                <div class="about-detail-row">
                                    <span class="about-detail-label"><i class="fi fi-rr-envelope"></i> Email</span>
                                    <span class="about-detail-value" id="aboutEmail">emmanuelleSantos@gmail.com</span>
                                </div>
                                <div class="about-detail-row">
                                    <span class="about-detail-label"><i class="fi fi-rr-phone"></i> Telephone</span>
                                    <span class="about-detail-value" id="aboutTelephone">061 234 5878</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Post Project Modal -->
    @include('owner.propertyOwner_Modals.ownerPosting_Modal')
@endsection

@section('extra_css')
    <link rel="stylesheet" href="{{ asset('css/owner/propertyOwner_Profile.css') }}">
    <link rel="stylesheet" href="{{ asset('css/owner/propertyOwner_Modals/ownerPosting_Modal.css') }}">
@endsection

@section('extra_js')
    <script src="{{ asset('js/owner/propertyOwner_Profile.js') }}"></script>
    <script src="{{ asset('js/owner/propertyOwner_Modals/ownerPosting_Modal.js') }}"></script>
    <script>
        // Set Profile link as active when on profile page
        document.addEventListener('DOMContentLoaded', () => {
            const navbarLinks = document.querySelectorAll('.navbar-link');
            navbarLinks.forEach(link => {
                link.classList.remove('active');
                if (link.textContent.trim() === 'Profile' || link.getAttribute('href') === '{{ route("owner.profile") }}') {
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
