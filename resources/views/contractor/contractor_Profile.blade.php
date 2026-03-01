@extends('layouts.appContractor')

@section('title', 'Profile - Legatura')

@section('content')
    @php
        $sessionUser = session('user');
        $userId = is_object($sessionUser) ? ($sessionUser->user_id ?? $sessionUser->id ?? null) : ($sessionUser['user_id'] ?? null);
    @endphp
    <div class="contractor-profile min-h-screen bg-gray-50"
         id="contractorProfileRoot"
         data-user-id="{{ $userId }}"
         data-role="contractor"
         data-fetch-url="{{ route('contractor.profile.fetch') }}"
         data-reviews-url="{{ route('contractor.profile.reviews') }}"
         data-update-url="{{ route('contractor.profile.update') }}"
         data-storage-base="{{ asset('storage') }}"
         data-psgc-cities-url="{{ url('/api/psgc/cities') }}">
        <!-- Profile Header with Cover Image -->
        <div class="profile-header-section">
            <div class="profile-cover-container">
                <div class="profile-cover-image" id="profileCoverImage">
                    <img src="" alt="Cover" class="cover-img" id="coverPhotoImg" onerror="this.src='https://via.placeholder.com/1600x400/667eea/ffffff?text=Cover+Photo'">
                </div>
                <button class="profile-back-btn" onclick="window.history.back()">
                    <i class="fi fi-rr-arrow-left"></i>
                    <span>Back</span>
                </button>
                <button class="profile-cover-edit-btn" id="editCoverPhotoBtn" aria-label="Edit cover photo">
                    <i class="fi fi-rr-camera"></i>
                </button>
                <input type="file" id="coverPhotoInput" accept="image/*" style="display: none;">
            </div>

            <!-- Profile Info Section -->
            <div class="profile-info-section">
                <div class="profile-info-container">
                    <!-- Profile Picture -->
                    <div class="profile-picture-container">
                        <div class="profile-picture" id="profilePicture">
                            <img src="" alt="Profile" class="profile-picture-img" id="profilePictureImg" style="display:none; width:100%; height:100%; object-fit:cover; border-radius:50%;">
                            <span class="profile-picture-initials"></span>
                        </div>
                        <button class="profile-picture-edit-btn" id="editProfilePictureBtn" aria-label="Edit profile picture">
                            <i class="fi fi-rr-camera"></i>
                        </button>
                    </div>

                    <!-- Profile Details -->
                    <div class="profile-details">
                        <h1 class="profile-name" id="profileName">Loading...</h1>
                        <div class="profile-meta">
                            <div class="profile-rating">
                                <i class="fi fi-rr-star"></i>
                                <span id="profileRating">—</span>
                                <span class="profile-rating-label">Rating</span>
                            </div>
                            <div class="profile-location">
                                <i class="fi fi-rr-marker"></i>
                                <span id="profileLocation">—</span>
                            </div>
                        </div>
                    </div>

                    <!-- Edit Profile Button -->
                    <div class="profile-actions">
                        <button class="edit-profile-btn-action" id="editProfileInfoBtn" onclick="window.openEditContractorProfileModal && window.openEditContractorProfileModal()">
                            <i class="fi fi-rr-edit"></i>
                            <span>Edit Profile</span>
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
                            <h3 class="info-card-name" id="infoCardName">Loading...</h3>
                            <div class="info-card-meta">
                                <div class="info-card-rating">
                                    <i class="fi fi-rr-star"></i>
                                    <span id="infoCardRating">—</span>
                                    <span>Rating</span>
                                </div>
                                <div class="info-card-location">
                                    <i class="fi fi-rr-marker"></i>
                                    <span id="infoCardLocation">—</span>
                                </div>
                            </div>
                        </div>

                        <div class="info-card-bio">
                            <p id="infoCardBio"></p>
                        </div>

                        <div class="info-card-occupation">
                            <span class="occupation-label">Specialization:</span>
                            <span class="occupation-value" id="occupationValue">—</span>
                        </div>

                        <div class="info-card-stats">
                            <div class="stat-item">
                                <span class="stat-label">Projects completed:</span>
                                <span class="stat-value" id="projectsDone">0</span>
                            </div>
                            <div class="stat-item">
                                <span class="stat-label">Active projects:</span>
                                <span class="stat-value" id="ongoingProjects">0</span>
                            </div>
                        </div>

                        <div class="info-card-contact">
                            <div class="contact-item">
                                <i class="fi fi-rr-phone-call"></i>
                                <span>Contact No.: <span id="contactNumber">—</span></span>
                            </div>
                            <div class="contact-item">
                                <i class="fi fi-rr-envelope"></i>
                                <span>Email: <span id="contactEmail">—</span></span>
                            </div>
                            <div class="contact-item">
                                <i class="fi fi-rr-phone"></i>
                                <span>Telephone: <span id="telephone">—</span></span>
                            </div>
                        </div>
                    </div>

                    <!-- Contact Person Card -->
                    <div class="profile-contact-card" id="contactPersonCard">
                        <div class="contact-card-header">
                            <div class="contact-card-avatar">
                                <img src="" alt="" id="contactPersonAvatar" style="display:none;" onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                                <div class="contact-card-avatar-initials" id="contactPersonInitials" style="display: flex;"></div>
                            </div>
                            <div class="contact-card-name-section">
                                <h3 class="contact-card-name" id="contactPersonName">—</h3>
                                <p class="contact-card-role" id="contactPersonRole">Representative</p>
                            </div>
                        </div>

                        <div class="contact-card-divider"></div>

                        <div class="contact-card-info">
                            <div class="contact-card-contact-item">
                                <i class="fi fi-rr-phone-call"></i>
                                <span>Contact No.: <span class="contact-card-value" id="contactPersonPhone">—</span></span>
                            </div>
                            <div class="contact-card-contact-item">
                                <i class="fi fi-rr-envelope"></i>
                                <span>Email: <span class="contact-card-value" id="contactPersonEmail">—</span></span>
                            </div>
                            <div class="contact-card-contact-item">
                                <i class="fi fi-rr-phone"></i>
                                <span>Telephone: <span class="contact-card-value" id="contactPersonTelephone">—</span></span>
                            </div>
                        </div>

                        <div class="contact-card-divider"></div>

                        <div class="contact-card-section">
                            <h4 class="contact-card-section-title">Services Offered:</h4>
                            <ul class="contact-card-services-list" id="servicesOfferedList">
                                <li>—</li>
                            </ul>
                        </div>

                        <div class="contact-card-divider"></div>

                        <div class="contact-card-section">
                            <h4 class="contact-card-section-title">Specialization:</h4>
                            <div class="contact-card-specialization-tags" id="specializationTags">
                                <span class="specialization-tag">—</span>
                            </div>
                        </div>

                        <div class="contact-card-divider"></div>

                        <div class="contact-card-stats">
                            <div class="contact-card-stat-item">
                                <span class="contact-card-stat-label">Experience:</span>
                                <span class="contact-card-stat-value" id="contactPersonExperience">—</span>
                            </div>
                            <div class="contact-card-stat-item">
                                <span class="contact-card-stat-label">Projects done:</span>
                                <span class="contact-card-stat-value" id="contactPersonProjectsDone">0</span>
                            </div>
                            <div class="contact-card-stat-item">
                                <span class="contact-card-stat-label">Ongoing projects:</span>
                                <span class="contact-card-stat-value" id="contactPersonOngoingProjects">0</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Right Column - Content Feed (Scrollable) -->
                <div class="profile-right-column">
                    <!-- Portfolio Section (Portfolio Tab) -->
                    <div class="portfolio-section" id="portfolioSection">
                        <div class="section-action-bar">
                            <h2 class="section-action-title">Portfolio</h2>
                            <button type="button" class="btn-section-action" id="openPortfolioPostModalBtn">
                                <i class="fi fi-rr-plus"></i>
                                <span>Add Project</span>
                            </button>
                        </div>
                        <div class="portfolio-grid" id="portfolioFeed">
                            {{-- Portfolio cards dynamically inserted by JS --}}
                        </div>
                    </div>

                    <!-- Highlights Tab (Stat Cards + Services) -->
                    <div class="highlights-section hidden" id="highlightsSection">
                        <div class="highlights-stats-grid" id="highlightsStatsGrid">
                            <!-- Years Experience -->
                            <div class="highlights-stat-card">
                                <div class="highlights-stat-icon highlights-stat-icon--primary">
                                    <i class="fi fi-rr-trophy"></i>
                                </div>
                                <span class="highlights-stat-value" id="hlYearsExperience">0</span>
                                <span class="highlights-stat-label">Years Experience</span>
                            </div>
                            <!-- Projects Completed -->
                            <div class="highlights-stat-card">
                                <div class="highlights-stat-icon highlights-stat-icon--success">
                                    <i class="fi fi-rr-check-circle"></i>
                                </div>
                                <span class="highlights-stat-value" id="hlProjectsCompleted">0</span>
                                <span class="highlights-stat-label">Projects Completed</span>
                            </div>
                            <!-- Average Rating -->
                            <div class="highlights-stat-card">
                                <div class="highlights-stat-icon highlights-stat-icon--warning">
                                    <i class="fi fi-rr-star"></i>
                                </div>
                                <span class="highlights-stat-value" id="hlAvgRating">0</span>
                                <span class="highlights-stat-label">Average Rating</span>
                            </div>
                            <!-- Client Reviews -->
                            <div class="highlights-stat-card">
                                <div class="highlights-stat-icon highlights-stat-icon--info">
                                    <i class="fi fi-rr-comment-alt"></i>
                                </div>
                                <span class="highlights-stat-value" id="hlClientReviews">0</span>
                                <span class="highlights-stat-label">Client Reviews</span>
                            </div>
                        </div>

                        <!-- Services Offered -->
                        <div class="highlights-services-card" id="hlServicesCard" style="display:none;">
                            <h3 class="highlights-services-title">Services Offered</h3>
                            <p class="highlights-services-text" id="hlServicesText"></p>
                        </div>

                        <!-- Verification Status -->
                        <div class="highlights-verification-card" id="hlVerificationCard" style="display:none;">
                            <div class="highlights-verification-icon" id="hlVerificationIcon">
                                <i class="fi fi-rr-badge-check"></i>
                            </div>
                            <div class="highlights-verification-info">
                                <span class="highlights-verification-label" id="hlVerificationLabel">Verification Status</span>
                                <span class="highlights-verification-value" id="hlVerificationValue">—</span>
                            </div>
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
                    <div class="about-section hidden" id="aboutSection" data-profile-id="" data-profile-type="contractor">
                        <div class="about-block">
                            <h3 class="about-block-title">Bio</h3>
                            <p class="about-bio-text" id="aboutBioText">—</p>
                        </div>
                        <div class="about-divider"></div>
                        <div class="about-block">
                            <h3 class="about-block-title">Skills &amp; Specializations</h3>
                            <div class="about-skills-grid" id="aboutSkillsGrid">
                                {{-- Populated from backend --}}
                            </div>
                        </div>
                        <div class="about-divider"></div>
                        <div class="about-block">
                            <h3 class="about-block-title">Details</h3>
                            <div class="about-details-list">
                                <div class="about-detail-row">
                                    <span class="about-detail-label"><i class="fi fi-rr-briefcase"></i> Specialization</span>
                                    <span class="about-detail-value" id="aboutSpecialization">—</span>
                                </div>
                                <div class="about-detail-row">
                                    <span class="about-detail-label"><i class="fi fi-rr-time-past"></i> Experience</span>
                                    <span class="about-detail-value" id="aboutExperience">—</span>
                                </div>
                                <div class="about-detail-row">
                                    <span class="about-detail-label"><i class="fi fi-rr-marker"></i> Location</span>
                                    <span class="about-detail-value" id="aboutLocation">—</span>
                                </div>
                                <div class="about-detail-row">
                                    <span class="about-detail-label"><i class="fi fi-rr-check-circle"></i> Projects Done</span>
                                    <span class="about-detail-value" id="aboutProjectsDone">0</span>
                                </div>
                                <div class="about-detail-row">
                                    <span class="about-detail-label"><i class="fi fi-rr-calendar"></i> Company Started</span>
                                    <span class="about-detail-value" id="aboutCompanyStartDate">—</span>
                                </div>
                            </div>
                        </div>
                        <div class="about-divider"></div>
                        <div class="about-block">
                            <h3 class="about-block-title">Company Description</h3>
                            <p class="about-bio-text" id="aboutCompanyDescription">—</p>
                        </div>
                        <div class="about-divider"></div>
                        <div class="about-block">
                            <h3 class="about-block-title">Certifications</h3>
                            <div class="about-certifications-list" id="aboutCertificationsList">
                                {{-- Populated from backend --}}
                            </div>
                        </div>
                        <div class="about-divider"></div>
                        <div class="about-block">
                            <h3 class="about-block-title">Contact Information</h3>
                            <div class="about-details-list">
                                <div class="about-detail-row">
                                    <span class="about-detail-label"><i class="fi fi-rr-phone-call"></i> Mobile</span>
                                    <span class="about-detail-value" id="aboutPhone">—</span>
                                </div>
                                <div class="about-detail-row">
                                    <span class="about-detail-label"><i class="fi fi-rr-envelope"></i> Email</span>
                                    <span class="about-detail-value" id="aboutEmail">—</span>
                                </div>
                                <div class="about-detail-row">
                                    <span class="about-detail-label"><i class="fi fi-rr-phone"></i> Telephone</span>
                                    <span class="about-detail-value" id="aboutTelephone">—</span>
                                </div>
                                <div class="about-detail-row">
                                    <span class="about-detail-label"><i class="fi fi-rr-globe"></i> Website</span>
                                    <span class="about-detail-value" id="aboutWebsite">—</span>
                                </div>
                                <div class="about-detail-row">
                                    <span class="about-detail-label"><i class="fi fi-rr-share"></i> Social Media</span>
                                    <span class="about-detail-value" id="aboutSocialMedia">—</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Portfolio Post Modal -->
    @include('contractor.contractor_Modals.contractorPortfolioPost_Modal')
    <!-- Edit Profile Modal -->
    @include('contractor.contractor_Modals.contractorEditProfile_Modal')
@endsection

@section('extra_css')
    <link rel="stylesheet" href="{{ asset('css/contractor/contractor_Profile.css') }}">
    <link rel="stylesheet" href="{{ asset('css/contractor/contractor_Modals/contractorPortfolioPost_Modal.css') }}">
    <link rel="stylesheet" href="{{ asset('css/contractor/contractor_Modals/contractorEditProfile_Modal.css') }}">
@endsection

@section('extra_js')
    <script src="{{ asset('js/contractor/contractor_Profile.js') }}"></script>
    <script src="{{ asset('js/contractor/contractor_Modals/contractorPortfolioPost_Modal.js') }}"></script>
    <script src="{{ asset('js/contractor/contractor_Modals/contractorEditProfile_Modal.js') }}"></script>
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
