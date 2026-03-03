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
                        <button class="profile-picture-edit-btn" id="editProfilePicBtn" aria-label="Edit profile picture">
                            <i class="fi fi-rr-camera"></i>
                        </button>
                        <input type="file" id="profilePicInput" accept="image/*" style="display: none;">
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
                        <button class="edit-profile-btn" id="editProfileBtn">
                            <i class="fi fi-rr-pencil"></i>
                            <span>Edit Profile</span>
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
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Project Details Full Page Panel (Facebook-style) -->
    <div class="project-details-panel" id="projectDetailsPanel">
        <div class="project-panel-header">
            <button class="panel-back-btn" id="projectPanelBackBtn">
                <i class="fi fi-rr-arrow-left"></i>
                <span>Back to Profile</span>
            </button>
            <h2 class="panel-title">Project Details</h2>
            <div class="panel-actions">
                <button class="panel-action-btn" id="editProjectBtn" style="display: none;">
                    <i class="fi fi-rr-pencil"></i>
                </button>
            </div>
        </div>

        <div class="project-panel-content">
            <!-- Two-Column Layout: Files & Information -->
            <div class="project-two-column-layout">

                <!-- Left Column: Project Files -->
                <div class="project-files-column">
                    <!-- First Row: Design Files (Larger) -->
                    <div class="project-files-section">
                        <div class="project-files-header">
                            <i class="fi fi-rr-blueprint"></i>
                            <span class="project-files-title">Design Files</span>
                        </div>
                        <div class="project-files-grid project-files-grid-large" id="projectDesignFiles">
                            <div class="project-files-empty">
                                <i class="fi fi-rr-image"></i>
                                <span>No design files uploaded</span>
                            </div>
                        </div>
                    </div>

                    <!-- Second Row: Legal Documents (Smaller) -->
                    <div class="project-files-section">
                        <div class="project-files-header">
                            <i class="fi fi-rr-document-signed"></i>
                            <span class="project-files-title">Legal Documents</span>
                        </div>
                        <div class="project-files-grid project-files-grid-small" id="projectLegalFiles">
                            <div class="project-files-empty">
                                <i class="fi fi-rr-file"></i>
                                <span>No legal documents uploaded</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Right Column: Project Information -->
                <div class="project-info-column">
                    <div class="project-info-card-main">
                        <div class="project-info-header">
                            <i class="fi fi-rr-info"></i>
                            <span>Project Information</span>
                        </div>

                        <div class="project-info-body">
                            <!-- Status -->
                            <div class="project-info-row">
                                <span class="project-info-label">Status</span>
                                <span class="project-info-value" id="projectDetailStatus"></span>
                            </div>

                            <!-- Posted -->
                            <div class="project-info-row">
                                <span class="project-info-label">Posted</span>
                                <span class="project-info-value" id="projectDetailDate"></span>
                            </div>

                            <!-- Deadline -->
                            <div class="project-info-row" id="projectDeadlineRow">
                                <span class="project-info-label">Deadline</span>
                                <span class="project-info-value" id="projectDetailDeadline"></span>
                            </div>

                            <!-- Bids -->
                            <div class="project-info-row">
                                <span class="project-info-label">Bids</span>
                                <span class="project-info-value" id="projectDetailBids"></span>
                            </div>

                            <!-- Budget Range -->
                            <div class="project-info-row">
                                <span class="project-info-label">Budget Range</span>
                                <span class="project-info-value" id="projectBudgetRange"></span>
                            </div>

                            <!-- Description Section -->
                            <div class="project-info-section">
                                <span class="project-info-section-title">Description</span>
                                <p class="project-info-description" id="projectDetailDescription"></p>
                            </div>

                            <!-- Specifications Section -->
                            <div class="project-info-section">
                                <span class="project-info-section-title">Specifications</span>
                                <div class="project-info-specs" id="projectSpecifications"></div>
                            </div>
                        </div>
                    </div>
                </div>

            </div>

            <!-- Project Hero Section (Hidden as replaced by two-column layout) -->
            <div class="project-hero" style="display: none;">
                <div class="project-hero-image" id="projectHeroImage">
                    <img src="" alt="Project" id="projectMainImage" onerror="this.style.display='none'">
                    <div class="project-hero-overlay"></div>
                </div>
                <div class="project-hero-info">
                    <span class="project-type-badge" id="projectTypeBadge">Residential</span>
                    <h1 class="project-title" id="projectDetailTitle">Project Title</h1>
                    <div class="project-location">
                        <i class="fi fi-rr-marker"></i>
                        <span id="projectDetailLocation">Location</span>
                    </div>
                </div>
            </div>

            <!-- Project Info Cards (Hidden as replaced by two-column layout) -->
            <div class="project-info-grid" style="display: none;">
                <div class="project-info-card">
                    <div class="info-card-icon">
                        <i class="fi fi-rr-flag"></i>
                    </div>
                    <div class="info-card-content">
                        <span class="info-card-label">Status</span>
                        <span class="info-card-value" id="projectDetailStatus">Pending</span>
                    </div>
                </div>
                <div class="project-info-card">
                    <div class="info-card-icon">
                        <i class="fi fi-rr-calendar"></i>
                    </div>
                    <div class="info-card-content">
                        <span class="info-card-label">Posted</span>
                        <span class="info-card-value" id="projectDetailDate">Jan 1, 2026</span>
                    </div>
                </div>
                <div class="project-info-card">
                    <div class="info-card-icon">
                        <i class="fi fi-rr-hourglass-end"></i>
                    </div>
                    <div class="info-card-content">
                        <span class="info-card-label">Deadline</span>
                        <span class="info-card-value" id="projectDetailDeadline">--</span>
                    </div>
                </div>
                <div class="project-info-card">
                    <div class="info-card-icon">
                        <i class="fi fi-rr-document"></i>
                    </div>
                    <div class="info-card-content">
                        <span class="info-card-label">Bids</span>
                        <span class="info-card-value" id="projectDetailBids">0</span>
                    </div>
                </div>
            </div>

            <!-- Budget Section (Hidden as moved to right column) -->
            <div class="project-section" style="display: none;">
                <h3 class="section-heading"><i class="fi fi-rr-peso-sign"></i> Budget Range</h3>
                <div class="budget-display">
                    <div class="budget-item">
                        <span class="budget-label">Minimum</span>
                        <span class="budget-value" id="projectBudgetMin">₱0</span>
                    </div>
                    <div class="budget-divider">—</div>
                    <div class="budget-item">
                        <span class="budget-label">Maximum</span>
                        <span class="budget-value" id="projectBudgetMax">₱0</span>
                    </div>
                </div>
            </div>

            <!-- Description Section (Hidden as moved to right column) -->
            <div class="project-section" style="display: none;">
                <h3 class="section-heading"><i class="fi fi-rr-document"></i> Description</h3>
                <p class="project-description" id="projectDetailDescription">No description provided.</p>
            </div>

            <!-- Specifications Section (Hidden as moved to right column) -->
            <div class="project-section" id="specificationsSection" style="display: none;">
                <h3 class="section-heading"><i class="fi fi-rr-list-check"></i> Specifications</h3>
                <div class="specifications-grid" id="projectSpecifications">
                    <!-- Specifications will be populated dynamically -->
                </div>
            </div>

            <!-- Attachments Section -->
            <div class="project-section" id="attachmentsSection" style="display: none;">
                <h3 class="section-heading"><i class="fi fi-rr-clip"></i> Attachments</h3>
                <div class="attachments-grid" id="projectAttachments">
                    <!-- Attachments will be populated dynamically -->
                </div>
            </div>

            <!-- Contractor Section (for awarded projects) -->
            <div class="project-section" id="contractorSection" style="display: none;">
                <h3 class="section-heading"><i class="fi fi-rr-user-gear"></i> Assigned Contractor</h3>
                <div class="contractor-card" id="contractorCard">
                    <div class="contractor-avatar" id="contractorAvatar">
                        <span class="contractor-initials">--</span>
                    </div>
                    <div class="contractor-info">
                        <h4 class="contractor-name" id="contractorName">--</h4>
                        <p class="contractor-company" id="contractorCompany">--</p>
                    </div>
                    <button class="view-contractor-btn" id="viewContractorBtn">
                        <i class="fi fi-rr-angle-right"></i>
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Post Project Modal -->
    @include('owner.propertyOwner_Modals.ownerPosting_Modal')

    <!-- Edit Profile Information Modal (matching settings modal structure) -->
    <div id="editProfileModal" class="edit-profile-modal">
        <div class="modal-overlay" id="editProfileModalOverlay"></div>
        <div class="edit-profile-modal-container">
            <!-- Modal Header -->
            <div class="edit-profile-modal-header">
                <h2 class="edit-profile-modal-title">
                    <i class="fi fi-rr-edit"></i>
                    Edit Profile Information
                </h2>
                <button class="edit-profile-close-btn" id="editProfileModalClose" aria-label="Close modal">
                    <i class="fi fi-rr-cross"></i>
                </button>
            </div>

            <!-- Modal Body -->
            <div class="edit-profile-modal-body">
                <form id="editProfileForm" class="edit-profile-form" enctype="multipart/form-data">
                    <div class="form-row">
                        <div class="form-group">
                            <label for="editFirstName" class="form-label">
                                First Name <span class="required">*</span>
                            </label>
                            <input
                                type="text"
                                id="editFirstName"
                                name="first_name"
                                class="form-input"
                                placeholder="Enter your first name"
                                required
                            >
                        </div>

                        <div class="form-group">
                            <label for="editMiddleName" class="form-label">
                                Middle Name <span class="optional">(Optional)</span>
                            </label>
                            <input
                                type="text"
                                id="editMiddleName"
                                name="middle_name"
                                class="form-input"
                                placeholder="Enter your middle name"
                            >
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label for="editLastName" class="form-label">
                                Last Name <span class="required">*</span>
                            </label>
                            <input
                                type="text"
                                id="editLastName"
                                name="last_name"
                                class="form-input"
                                placeholder="Enter your last name"
                                required
                            >
                        </div>

                        <div class="form-group">
                            <label for="editOccupation" class="form-label">
                                Occupation <span class="required">*</span>
                            </label>
                            <select
                                id="editOccupation"
                                name="occupation_id"
                                class="form-select"
                                required
                            >
                                <option value="">Select occupation</option>
                                <option value="1">Teacher</option>
                                <option value="2">Engineer</option>
                                <option value="3">Doctor</option>
                                <option value="4">Nurse</option>
                                <option value="5">Police Officer</option>
                                <option value="6">Firefighter</option>
                                <option value="7">Lawyer</option>
                                <option value="8">Architect</option>
                                <option value="9">Driver</option>
                                <option value="10">Construction Worker</option>
                                <option value="11">Electrician</option>
                                <option value="12">Plumber</option>
                                <option value="13">Farmer</option>
                                <option value="14">Fisherman</option>
                                <option value="15">Office Clerk</option>
                                <option value="16">Salesperson</option>
                                <option value="17">Cashier</option>
                                <option value="18">Security Guard</option>
                                <option value="19">IT Specialist</option>
                                <option value="20">Call Center Agent</option>
                                <option value="21">Chef</option>
                                <option value="22">Accountant</option>
                                <option value="23">Businessman</option>
                                <option value="24">Student</option>
                                <option value="25">Unemployed</option>
                                <option value="26">Others</option>
                            </select>
                        </div>
                    </div>

                    <div class="form-row" id="editOccupationOtherRow" style="display: none;">
                        <div class="form-group" style="width: 100%;">
                            <label for="editOccupationOther" class="form-label">
                                Specify Occupation <span class="required">*</span>
                            </label>
                            <input
                                type="text"
                                id="editOccupationOther"
                                name="occupation_other"
                                class="form-input"
                                placeholder="Please specify your occupation"
                            >
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label for="editUsername" class="form-label">
                                Username <span class="required">*</span>
                            </label>
                            <input
                                type="text"
                                id="editUsername"
                                name="username"
                                class="form-input"
                                placeholder="Enter your username"
                                required
                            >
                        </div>

                        <div class="form-group">
                            <label for="editDateOfBirth" class="form-label">
                                Date of Birth <span class="required">*</span>
                            </label>
                            <input
                                type="date"
                                id="editDateOfBirth"
                                name="date_of_birth"
                                class="form-input"
                                required
                            >
                        </div>
                    </div>

                    <!-- Address Section -->
                    <div class="form-section-title">
                        <i class="fi fi-rr-marker"></i>
                        <span>Address Information</span>
                    </div>

                    <div class="form-row">
                        <div class="form-group" style="width: 100%;">
                            <label for="editAddressStreet" class="form-label">
                                Street Address <span class="required">*</span>
                            </label>
                            <input
                                type="text"
                                id="editAddressStreet"
                                name="address_street"
                                class="form-input"
                                placeholder="Enter street/house number"
                                required
                            >
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label for="editAddressProvince" class="form-label">
                                Province <span class="required">*</span>
                            </label>
                            <select
                                id="editAddressProvince"
                                name="address_province"
                                class="form-select"
                                required
                            >
                                <option value="">Select province</option>
                            </select>
                        </div>

                        <div class="form-group">
                            <label for="editAddressCity" class="form-label">
                                City/Municipality <span class="required">*</span>
                            </label>
                            <select
                                id="editAddressCity"
                                name="address_city"
                                class="form-select"
                                required
                                disabled
                            >
                                <option value="">Select city</option>
                            </select>
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label for="editAddressBarangay" class="form-label">
                                Barangay <span class="required">*</span>
                            </label>
                            <select
                                id="editAddressBarangay"
                                name="address_barangay"
                                class="form-select"
                                required
                                disabled
                            >
                                <option value="">Select barangay</option>
                            </select>
                        </div>
                    </div>
                </form>
            </div>

            <!-- Modal Footer -->
            <div class="edit-profile-modal-footer">
                <button type="button" class="edit-profile-btn cancel-btn" id="editProfileCancelBtn">
                    Cancel
                </button>
                <button type="button" class="edit-profile-btn save-btn" id="editProfileSaveBtn">
                    <i class="fi fi-rr-check"></i>
                    Save Changes
                </button>
            </div>
        </div>
    </div>
@endsection

@section('extra_css')
    <link rel="stylesheet" href="{{ asset('css/owner/propertyOwner_Profile.css') }}">
    <link rel="stylesheet" href="{{ asset('css/owner/propertyOwner_Modals/ownerPosting_Modal.css') }}">
    <link rel="stylesheet" href="{{ asset('css/owner/propertyOwner_Modals/ownerAccountsettings_Modal.css') }}">
    <style>
        /* Project Details Full Page Panel */
        .project-details-panel {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: #f8fafc;
            z-index: 1000;
            transform: translateX(100%);
            transition: transform 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            overflow-y: auto;
        }

        .project-details-panel.active {
            transform: translateX(0);
        }

        .project-panel-header {
            position: sticky;
            top: 0;
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 1rem 1.5rem;
            background: white;
            border-bottom: 1px solid #e2e8f0;
            z-index: 10;
        }

        .panel-back-btn {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.5rem 1rem;
            background: transparent;
            border: none;
            color: #64748b;
            font-size: 0.9rem;
            font-weight: 500;
            cursor: pointer;
            border-radius: 8px;
            transition: all 0.2s;
        }

        .panel-back-btn:hover {
            background: #f1f5f9;
            color: #334155;
        }

        .panel-title {
            font-size: 1.1rem;
            font-weight: 600;
            color: #1e293b;
            margin: 0;
        }

        .panel-actions {
            display: flex;
            gap: 0.5rem;
        }

        .panel-action-btn {
            width: 36px;
            height: 36px;
            display: flex;
            align-items: center;
            justify-content: center;
            background: transparent;
            border: none;
            color: #64748b;
            cursor: pointer;
            border-radius: 8px;
            transition: all 0.2s;
        }

        .panel-action-btn:hover {
            background: #f1f5f9;
            color: #334155;
        }

        .project-panel-content {
            width: 100%;
            margin: 0;
            padding: 0;
        }

        /* Project Hero */
        .project-hero {
            position: relative;
            border-radius: 16px;
            overflow: hidden;
            margin-bottom: 1.5rem;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 200px;
        }

        .project-hero-image img {
            width: 100%;
            height: 200px;
            object-fit: cover;
        }

        .project-hero-overlay {
            position: absolute;
            inset: 0;
            background: linear-gradient(to top, rgba(0,0,0,0.7) 0%, transparent 50%);
        }

        .project-hero-info {
            position: absolute;
            bottom: 0;
            left: 0;
            right: 0;
            padding: 1.5rem;
            color: white;
        }

        .project-type-badge {
            display: inline-block;
            padding: 0.25rem 0.75rem;
            background: rgba(255,255,255,0.2);
            backdrop-filter: blur(8px);
            border-radius: 20px;
            font-size: 0.75rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 0.5rem;
        }

        .project-title {
            font-size: 1.5rem;
            font-weight: 700;
            margin: 0 0 0.5rem 0;
            line-height: 1.3;
        }

        .project-location {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            font-size: 0.9rem;
            opacity: 0.9;
        }

        /* Info Cards Grid */
        .project-info-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 1rem;
            margin-bottom: 1.5rem;
        }

        @media (min-width: 640px) {
            .project-info-grid {
                grid-template-columns: repeat(4, 1fr);
            }
        }

        .project-info-card {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            padding: 1rem;
            background: white;
            border-radius: 12px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
        }

        .info-card-icon {
            width: 40px;
            height: 40px;
            display: flex;
            align-items: center;
            justify-content: center;
            background: #f1f5f9;
            border-radius: 10px;
            color: #667eea;
            font-size: 1.1rem;
        }

        .info-card-content {
            display: flex;
            flex-direction: column;
        }

        .info-card-label {
            font-size: 0.75rem;
            color: #64748b;
        }

        .info-card-value {
            font-size: 0.9rem;
            font-weight: 600;
            color: #1e293b;
        }

        /* Project Sections */
        .project-section {
            background: white;
            border-radius: 12px;
            padding: 1.25rem;
            margin-bottom: 1rem;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
        }

        .section-heading {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            font-size: 1rem;
            font-weight: 600;
            color: #1e293b;
            margin: 0 0 1rem 0;
        }

        .section-heading i {
            color: #667eea;
        }

        /* Budget Display */
        .budget-display {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 2rem;
        }

        .budget-item {
            text-align: center;
        }

        .budget-label {
            display: block;
            font-size: 0.75rem;
            color: #64748b;
            margin-bottom: 0.25rem;
        }

        .budget-value {
            font-size: 1.25rem;
            font-weight: 700;
            color: #10b981;
        }

        .budget-divider {
            color: #cbd5e1;
            font-size: 1.5rem;
        }

        /* Description */
        .project-description {
            color: #475569;
            line-height: 1.7;
            margin: 0;
            white-space: pre-wrap;
        }

        /* Specifications Grid */
        .specifications-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 0.75rem;
        }

        .spec-item {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.75rem;
            background: #f8fafc;
            border-radius: 8px;
        }

        .spec-icon {
            color: #667eea;
        }

        .spec-label {
            font-size: 0.8rem;
            color: #64748b;
        }

        .spec-value {
            font-size: 0.85rem;
            font-weight: 600;
            color: #1e293b;
            margin-left: auto;
        }

        /* Attachments */
        .attachments-grid {
            display: flex;
            flex-direction: column;
        }

        .attachment-item {
            aspect-ratio: 1;
            min-width: 100px;
            min-height: 100px;
            border-radius: 8px;
            overflow: hidden;
            cursor: pointer;
            transition: transform 0.2s;
            background: #f1f5f9;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .attachment-item:hover {
            transform: scale(1.02);
        }

        .attachment-item img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            display: block;
        }

        .attachment-file {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            background: #f1f5f9;
            color: #64748b;
            text-decoration: none;
        }

        .attachment-file i {
            font-size: 1.5rem;
            margin-bottom: 0.25rem;
        }

        .attachment-file span {
            font-size: 0.65rem;
            text-align: center;
            padding: 0 0.25rem;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
            max-width: 100%;
        }

        /* Attachment Groups */
        .attachment-group {
            margin-bottom: 1rem;
        }

        .attachment-group:last-child {
            margin-bottom: 0;
        }

        .attachment-group-header {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            font-size: 0.85rem;
            font-weight: 600;
            color: #475569;
            margin-bottom: 0.75rem;
            padding-bottom: 0.5rem;
            border-bottom: 1px solid #e2e8f0;
        }

        .attachment-group-header i {
            color: #667eea;
        }

        .attachment-count {
            font-weight: 400;
            color: #94a3b8;
            font-size: 0.8rem;
        }

        .protected-badge {
            margin-left: auto;
            background: #fef3c7;
            color: #d97706;
            font-size: 0.7rem;
            font-weight: 500;
            padding: 0.2rem 0.5rem;
            border-radius: 4px;
            display: flex;
            align-items: center;
            gap: 0.25rem;
        }

        .protected-badge i {
            font-size: 0.65rem;
            color: #d97706;
        }

        .attachment-group-files {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(100px, 1fr));
            gap: 0.75rem;
        }

        /* Protected document with watermark */
        .attachment-item.protected {
            position: relative;
        }

        .attachment-item.protected .watermark-overlay {
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background-image: url('/img/legatura_watermark.png');
            background-size: cover;
            background-position: center;
            opacity: 0.5;
            pointer-events: none;
            z-index: 1;
        }

        .attachment-item.protected .document-label {
            position: absolute;
            bottom: 0;
            left: 0;
            right: 0;
            background: linear-gradient(transparent, rgba(0,0,0,0.7));
            color: #fff;
            padding: 1.5rem 0.5rem 0.5rem;
            font-size: 0.65rem;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.25rem;
            z-index: 2;
        }

        .attachment-item.protected .document-label i {
            font-size: 0.7rem;
        }

        .attachment-item.protected .view-icon {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            background: rgba(0, 0, 0, 0.5);
            color: #fff;
            width: 32px;
            height: 32px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            z-index: 2;
            opacity: 0;
            transition: opacity 0.2s;
        }

        .attachment-item.protected:hover .view-icon {
            opacity: 1;
        }

        /* Contractor Card */
        .contractor-card {
            display: flex;
            align-items: center;
            gap: 1rem;
            padding: 1rem;
            background: #f8fafc;
            border-radius: 12px;
            cursor: pointer;
            transition: all 0.2s;
        }

        .contractor-card:hover {
            background: #f1f5f9;
        }

        .contractor-avatar {
            width: 48px;
            height: 48px;
            border-radius: 50%;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
        }

        .contractor-avatar img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .contractor-initials {
            color: white;
            font-weight: 600;
            font-size: 1rem;
        }

        .contractor-info {
            flex: 1;
        }

        .contractor-name {
            font-size: 1rem;
            font-weight: 600;
            color: #1e293b;
            margin: 0 0 0.25rem 0;
        }

        .contractor-company {
            font-size: 0.85rem;
            color: #64748b;
            margin: 0;
        }

        .view-contractor-btn {
            width: 36px;
            height: 36px;
            display: flex;
            align-items: center;
            justify-content: center;
            background: white;
            border: none;
            border-radius: 50%;
            color: #64748b;
            cursor: pointer;
            transition: all 0.2s;
        }

        .view-contractor-btn:hover {
            background: #667eea;
            color: white;
        }

        /* Status Colors */
        .status-open { color: #3b82f6; }
        .status-bidding_closed { color: #6366f1; }
        .status-in_progress { color: #8b5cf6; }
        .status-completed { color: #10b981; }
        .status-terminated { color: #ef4444; }
        .status-halt { color: #f59e0b; }
        .status-cancelled { color: #ef4444; }
        .status-deleted { color: #6b7280; }
        .status-deleted_post { color: #6b7280; }
        .status-pending { color: #f59e0b; }
        .status-active { color: #3b82f6; }

        /* ══════════════════════════════════════════════════════════════════
           Two-Column Layout: Files & Information
        ══════════════════════════════════════════════════════════════════ */

        .project-two-column-layout {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 24px;
            padding: 24px 40px;
            width: 100%;
        }

        /* ── Left Column: Files ──────────────────────────────────────────── */
        .project-files-column {
            display: flex;
            flex-direction: column;
            gap: 20px;
        }

        .project-files-section {
            background: white;
            border-radius: 12px;
            border: 1.5px solid #e5e7eb;
            overflow: hidden;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
        }

        .project-files-header {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 16px 20px;
            background: linear-gradient(135deg, #F8FAFC 0%, #F1F5F9 100%);
            border-bottom: 1.5px solid #e5e7eb;
        }

        .project-files-header i {
            font-size: 18px;
            color: #EC7E00;
        }

        .project-files-title {
            font-size: 15px;
            font-weight: 700;
            color: #0f172a;
            font-family: ui-sans-serif, system-ui, -apple-system, sans-serif;
        }

        /* ── File Grids ────────────────────────────────────────────────── */
        .project-files-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(150px, 1fr));
            gap: 14px;
            padding: 20px;
            min-height: 140px;
        }

        .project-files-grid-large {
            min-height: 200px;
        }

        .project-files-grid-small {
            min-height: 130px;
        }

        /* ── File Preview Card ────────────────────────────────────────────── */
        .project-file-preview {
            position: relative;
            background: #F8FAFC;
            border: 1.5px solid #e5e7eb;
            border-radius: 10px;
            overflow: hidden;
            cursor: pointer;
            transition: all 0.25s cubic-bezier(0.4, 0, 0.2, 1);
            aspect-ratio: 4/3;
        }

        .project-file-preview:hover {
            border-color: #EC7E00;
            box-shadow: 0 6px 16px rgba(236, 126, 0, 0.2);
            transform: translateY(-3px);
        }

        .project-file-preview-image {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .project-file-preview-icon {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            height: 100%;
            gap: 10px;
            padding: 14px;
        }

        .project-file-preview-icon i {
            font-size: 36px;
            color: #64748b;
        }

        .project-file-name {
            font-size: 11px;
            color: #94a3b8;
            text-align: center;
            word-break: break-word;
            line-height: 1.4;
            max-width: 100%;
        }

        .project-file-type-badge {
            position: absolute;
            top: 10px;
            right: 10px;
            background: rgba(0, 0, 0, 0.8);
            color: white;
            font-size: 10px;
            font-weight: 700;
            padding: 5px 10px;
            border-radius: 6px;
            text-transform: uppercase;
            backdrop-filter: blur(6px);
            -webkit-backdrop-filter: blur(6px);
        }

        /* ── Empty State ──────────────────────────────────────────────────── */
        .project-files-empty {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            gap: 10px;
            height: 100%;
            color: #94a3b8;
            grid-column: 1 / -1;
        }

        .project-files-empty i {
            font-size: 40px;
            opacity: 0.4;
        }

        .project-files-empty span {
            font-size: 13px;
            font-weight: 500;
        }

        /* ── Right Column: Information ───────────────────────────────────── */
        .project-info-column {
            display: flex;
            flex-direction: column;
        }

        .project-info-card-main {
            background: white;
            border: 1.5px solid #e5e7eb;
            border-radius: 12px;
            overflow: hidden;
            height: fit-content;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
        }

        .project-info-header {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 16px 20px;
            background: linear-gradient(135deg, #FFF3E6 0%, #FFECD1 100%);
            border-bottom: 1.5px solid #e5e7eb;
        }

        .project-info-header i {
            font-size: 18px;
            color: #EC7E00;
        }

        .project-info-header span {
            font-size: 15px;
            font-weight: 700;
            color: #0f172a;
            font-family: ui-sans-serif, system-ui, -apple-system, sans-serif;
        }

        .project-info-body {
            padding: 20px;
        }

        /* ── Info Rows ──────────────────────────────────────────────────── */
        .project-info-row {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            padding: 12px 0;
            border-bottom: 1px solid #e5e7eb;
        }

        .project-info-row:last-of-type {
            border-bottom: none;
        }

        .project-info-label {
            font-size: 14px;
            font-weight: 600;
            color: #64748b;
            font-family: ui-sans-serif, system-ui, -apple-system, sans-serif;
        }

        .project-info-value {
            font-size: 14px;
            color: #0f172a;
            font-weight: 500;
            text-align: right;
            max-width: 60%;
            word-break: break-word;
        }

        /* ── Info Sections ──────────────────────────────────────────────── */
        .project-info-section {
            margin-top: 18px;
            padding-top: 18px;
            border-top: 1.5px solid #e5e7eb;
        }

        .project-info-section:first-child {
            margin-top: 0;
            padding-top: 0;
            border-top: none;
        }

        .project-info-section-title {
            display: block;
            font-size: 14px;
            font-weight: 700;
            color: #0f172a;
            margin-bottom: 12px;
            font-family: ui-sans-serif, system-ui, -apple-system, sans-serif;
        }

        .project-info-description {
            font-size: 14px;
            color: #64748b;
            line-height: 1.6;
            margin: 0;
        }

        .project-info-specs {
            display: grid;
            gap: 10px;
        }

        .project-info-spec-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 10px 14px;
            background: #F8FAFC;
            border-radius: 8px;
        }

        .project-info-spec-label {
            font-size: 13px;
            color: #64748b;
            font-weight: 500;
        }

        .project-info-spec-value {
            font-size: 13px;
            color: #0f172a;
            font-weight: 600;
        }

        /* ── Responsive: Stack on mobile ────────────────────────────────── */
        @media (max-width: 768px) {
            .project-two-column-layout {
                grid-template-columns: 1fr;
                padding: 16px;
            }

            .project-files-grid {
                grid-template-columns: repeat(auto-fill, minmax(110px, 1fr));
                gap: 12px;
                padding: 14px;
            }
        }
    </style>
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

            // Setup project details panel
            setupProjectDetailsPanel();
        });

        function setupProjectDetailsPanel() {
            const panel = document.getElementById('projectDetailsPanel');
            const backBtn = document.getElementById('projectPanelBackBtn');

            if (backBtn) {
                backBtn.addEventListener('click', closeProjectDetailsPanel);
            }

            // Handle ESC key
            document.addEventListener('keydown', (e) => {
                if (e.key === 'Escape' && panel?.classList.contains('active')) {
                    closeProjectDetailsPanel();
                }
            });
        }

        function openProjectDetailsPanel(project) {
            const panel = document.getElementById('projectDetailsPanel');
            if (!panel || !project) return;

            // Populate project details
            populateProjectDetails(project);

            // Show panel
            panel.classList.add('active');
            document.body.style.overflow = 'hidden';
        }

        function closeProjectDetailsPanel() {
            const panel = document.getElementById('projectDetailsPanel');
            if (!panel) return;

            panel.classList.remove('active');
            document.body.style.overflow = '';
        }

        function populateProjectDetails(project) {
            // Title and basic info
            const title = project.title || project.projectTitle || 'Untitled Project';
            document.getElementById('projectDetailTitle').textContent = title;
            document.getElementById('projectTypeBadge').textContent = project.projectType || project.type || 'Project';
            document.getElementById('projectDetailLocation').textContent = project.location || 'Location not specified';

            // Hero image
            const heroImage = document.getElementById('projectMainImage');
            if (project.image || project.thumbnail) {
                heroImage.src = project.image || project.thumbnail;
                heroImage.style.display = 'block';
            } else {
                heroImage.style.display = 'none';
            }

            // Status with color
            const statusEl = document.getElementById('projectDetailStatus');
            const status = project.status || 'pending';
            statusEl.textContent = formatStatus(status);
            statusEl.className = 'info-card-value status-' + status;

            // Date
            document.getElementById('projectDetailDate').textContent = formatDate(project.date || project.created_at);

            // Deadline
            const deadline = project.biddingDeadline || project.deadline;
            document.getElementById('projectDetailDeadline').textContent = deadline ? formatDate(deadline) : '--';

            // Bids count
            document.getElementById('projectDetailBids').textContent = project.bidsCount || project.bids_count || '0';

            // Budget Range (combined for new layout)
            const budgetMin = project.budgetMin || project.budget_min || 0;
            const budgetMax = project.budgetMax ||project.budget_max || 0;
            document.getElementById('projectBudgetRange').textContent =
                `${formatCurrency(budgetMin)} — ${formatCurrency(budgetMax)}`;

            // Old budget fields (hidden sections)
            document.getElementById('projectBudgetMin').textContent = formatCurrency(budgetMin);
            document.getElementById('projectBudgetMax').textContent = formatCurrency(budgetMax);

            // Description
            document.getElementById('projectDetailDescription').textContent = project.description || 'No description provided.';

            // Specifications (new layout)
            populateSpecifications(project);

            // Files (new two-column layout)
            populateProjectFiles(project);

            // Old attachments section (hidden)
            populateAttachments(project);

            // Contractor section (if awarded)
            populateContractor(project);
        }

        function populateProjectFiles(project) {
            const files = project.attachments || project.files || project.project_files || [];

            // Separate files into design and legal categories
            const designFiles = [];
            const legalFiles = [];

            files.forEach(file => {
                const fileType = (file.file_type || '').toLowerCase();
                if (fileType === 'building permit' || fileType === 'title') {
                    legalFiles.push(file);
                } else {
                    // blueprint, desired design, others
                    designFiles.push(file);
                }
            });

            // Render design files grid
            renderFileGrid('projectDesignFiles', designFiles, {
                emptyIcon: 'fi-rr-image',
                emptyText: 'No design files uploaded'
            });

            // Render legal files grid
            renderFileGrid('projectLegalFiles', legalFiles, {
                emptyIcon: 'fi-rr-file',
                emptyText: 'No legal documents uploaded'
            });
        }

        function renderFileGrid(gridId, files, emptyConfig) {
            const grid = document.getElementById(gridId);
            if (!grid) return;

            if(files.length === 0) {
                grid.innerHTML = `
                    <div class="project-files-empty">
                        <i class="fi ${emptyConfig.emptyIcon}"></i>
                        <span>${emptyConfig.emptyText}</span>
                    </div>
                `;
                return;
            }

            grid.innerHTML = files.map(file => {
                const filePath = file.file_path || file.path || '';
                const fileName = filePath.split('/').pop() || 'Document';
                const fileType = file.file_type || '';

                // Determine if it's an image
                const isImage = /\.(jpg|jpeg|png|gif|webp|svg)$/i.test(filePath);
                const cleanPath = filePath.replace(/^\//, '');
                const url = `/storage/${cleanPath}`;

                if (isImage) {
                    return `
                        <div class="project-file-preview" onclick="window.open('${url}', '_blank')">
                            <img src="${url}" alt="${fileName}" class="project-file-preview-image"
                                 onerror="this.parentElement.innerHTML='<div class=project-file-preview-icon><i class=\\'fi fi-rr-file-pdf\\'></i><span class=project-file-name>${fileName}</span></div>'">
                            ${fileType ? `<span class="project-file-type-badge">${fileType}</span>` : ''}
                        </div>
                    `;
                } else {
                    return `
                        <div class="project-file-preview" onclick="window.open('${url}', '_blank')">
                            <div class="project-file-preview-icon">
                                <i class="fi fi-rr-file-pdf"></i>
                                <span class="project-file-name">${fileName}</span>
                            </div>
                            ${fileType ? `<span class="project-file-type-badge">${fileType}</span>` : ''}
                        </div>
                    `;
                }
            }).join('');
        }

        function populateSpecifications(project) {
            const container = document.getElementById('projectSpecifications');
            container.innerHTML = '';

            const specs = [];

            // Property Type
            if (project.propertyType) {
                specs.push({ label: 'Property Type', value: project.propertyType });
            }

            // Contractor Type
            if (project.contractorType) {
                let contractorTypeValue = project.contractorType;
                if (project.contractorType.toLowerCase() === 'others' && project.contractorTypeOther) {
                    contractorTypeValue = project.contractorTypeOther;
                }
                specs.push({ label: 'Contractor Type', value: contractorTypeValue });
            }

            // Lot Size
            if (project.lotSize) {
                specs.push({ label: 'Lot Size', value: project.lotSize.toLocaleString() + ' sqm' });
            }

            // Floor Area
            if (project.floorArea) {
                specs.push({ label: 'Floor Area', value: project.floorArea.toLocaleString() + ' sqm' });
            }

            if (specs.length === 0) {
                container.innerHTML = '<p class="project-info-description">No specifications available.</p>';
                return;
            }

            specs.forEach(spec => {
                const item = document.createElement('div');
                item.className = 'project-info-spec-item';
                item.innerHTML = `
                    <span class="project-info-spec-label">${spec.label}</span>
                    <span class="project-info-spec-value">${spec.value}</span>
                `;
                container.appendChild(item);
            });
        }

        function populateAttachments(project) {
            const container = document.getElementById('projectAttachments');
            const section = document.getElementById('attachmentsSection');
            container.innerHTML = '';

            const attachments = project.attachments || project.files || [];

            if (attachments.length === 0) {
                section.style.display = 'none';
                return;
            }

            // Helper to check if file is an important/protected document (same as contractor homepage)
            function isImportantDocument(fileType, filePath) {
                const lType = (fileType || '').toLowerCase();
                const lPath = (filePath || '').toLowerCase();

                // Exact type matches
                if (lType === 'title' || lType === 'building permit') return true;

                // Regex pattern matches on type
                if (/building.?permit|title_of_land|title-of-land|land.?title/i.test(lType)) return true;

                // Path-based matches
                if (lPath.includes('building') && lPath.includes('permit')) return true;
                if (lPath.includes('title') && lPath.includes('land')) return true;
                if (lPath.includes('project_files/titles/')) return true;

                return false;
            }

            // Group files by type (include all files, mark protected ones)
            const filesByType = {
                'building permit': [],
                'title': [],
                'blueprint': [],
                'desired design': [],
                'others': []
            };

            attachments.forEach(file => {
                const fileType = file.file_type || 'others';
                if (filesByType[fileType]) {
                    filesByType[fileType].push(file);
                } else {
                    filesByType['others'].push(file);
                }
            });

            section.style.display = 'block';

            // File type labels and icons
            const typeConfig = {
                'building permit': { label: 'Building Permit', icon: 'fi-rr-document-signed', protected: true },
                'title': { label: 'Land Title', icon: 'fi-rr-diploma', protected: true },
                'blueprint': { label: 'Blueprints', icon: 'fi-rr-blueprint', protected: false },
                'desired design': { label: 'Desired Design', icon: 'fi-rr-picture', protected: false },
                'others': { label: 'Other Files', icon: 'fi-rr-file', protected: false }
            };

            // Render files grouped by type
            Object.keys(filesByType).forEach(type => {
                const files = filesByType[type];
                if (files.length === 0) return;

                const config = typeConfig[type] || typeConfig['others'];
                const protectedBadge = config.protected ? '<span class="protected-badge"><i class="fi fi-rr-lock"></i> Protected</span>' : '';

                const groupDiv = document.createElement('div');
                groupDiv.className = 'attachment-group';
                groupDiv.innerHTML = `
                    <div class="attachment-group-header">
                        <i class="fi ${config.icon}"></i>
                        <span>${config.label}</span>
                        <span class="attachment-count">(${files.length})</span>
                        ${protectedBadge}
                    </div>
                    <div class="attachment-group-files"></div>
                `;

                const filesContainer = groupDiv.querySelector('.attachment-group-files');

                files.forEach(file => {
                    const item = document.createElement('div');
                    item.className = 'attachment-item';

                    const filePath = file.file_path || file.path || file;
                    // Clean path - remove leading slash if present
                    const cleanPath = filePath.replace(/^\//, '');
                    const isImage = /\.(jpg|jpeg|png|gif|webp)$/i.test(cleanPath);
                    const isProtected = config.protected || isImportantDocument(file.file_type, cleanPath);

                    // Add protected class for watermark styling
                    if (isProtected) {
                        item.classList.add('protected');
                    }

                    if (isImage) {
                        const img = document.createElement('img');
                        img.alt = config.label;
                        img.className = 'attachment-img';
                        img.onclick = () => window.open('/storage/' + cleanPath, '_blank');
                        // Same onerror fallback as contractor homepage
                        img.onerror = function() {
                            if (!this.src.includes('logo.svg')) {
                                this.src = '/img/logo.svg';
                                this.style.objectFit = 'contain';
                                this.style.padding = '1rem';
                                this.style.background = '#f1f5f9';
                            }
                        };
                        img.src = '/storage/' + cleanPath;
                        item.appendChild(img);

                        // Add watermark overlay for protected documents
                        if (isProtected) {
                            const watermark = document.createElement('div');
                            watermark.className = 'watermark-overlay';
                            item.appendChild(watermark);

                            const label = document.createElement('div');
                            label.className = 'document-label';
                            label.innerHTML = `<i class="fi fi-rr-lock"></i><span>${config.label}</span>`;
                            item.appendChild(label);

                            const viewIcon = document.createElement('div');
                            viewIcon.className = 'view-icon';
                            viewIcon.innerHTML = '<i class="fi fi-rr-eye"></i>';
                            item.appendChild(viewIcon);
                        }
                    } else {
                        const fileName = cleanPath.split('/').pop();
                        item.className += ' attachment-file';
                        item.innerHTML = `
                            <i class="fi fi-rr-file"></i>
                            <span>${fileName}</span>
                        `;
                        item.onclick = () => window.open('/storage/' + cleanPath, '_blank');
                    }

                    filesContainer.appendChild(item);
                });

                container.appendChild(groupDiv);
            });
        }

        function populateContractor(project) {
            const section = document.getElementById('contractorSection');
            const contractor = project.contractor;

            if (!contractor) {
                section.style.display = 'none';
                return;
            }

            section.style.display = 'block';

            const name = contractor.company_name || contractor.name || 'Unknown';
            document.getElementById('contractorName').textContent = name;
            document.getElementById('contractorCompany').textContent = contractor.business_address || '';

            const avatar = document.getElementById('contractorAvatar');
            if (contractor.profile_picture) {
                avatar.innerHTML = `<img src="/storage/${contractor.profile_picture}" alt="${name}">`;
            } else {
                const initials = name.split(' ').map(n => n[0]).join('').substring(0, 2).toUpperCase();
                avatar.innerHTML = `<span class="contractor-initials">${initials}</span>`;
            }

            // View contractor button
            document.getElementById('viewContractorBtn').onclick = () => {
                if (contractor.username) {
                    window.location.href = '/contractor/profile/' + contractor.username;
                }
            };
        }

        function formatDate(dateString) {
            if (!dateString) return '--';
            const date = new Date(dateString);
            return date.toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' });
        }

        function formatCurrency(amount) {
            const n = Number(amount) || 0;
            return '₱' + n.toLocaleString('en-PH');
        }

        function formatStatus(status) {
            const statusMap = {
                'open': 'Open for Bidding',
                'bidding_closed': 'Bidding Closed',
                'in_progress': 'In Progress',
                'completed': 'Completed',
                'terminated': 'Terminated',
                'halt': 'On Hold',
                'cancelled': 'Cancelled',
                'deleted': 'Deleted',
                'deleted_post': 'Deleted',
                'pending': 'Pending',
                'active': 'Active'
            };
            return statusMap[status] || status;
        }

        // Expose globally for the profile JS to call
        window.openProjectDetailsPanel = openProjectDetailsPanel;
    </script>
@endsection
