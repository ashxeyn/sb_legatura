/**
 * Property Owner Profile JavaScript
 * Handles profile page display and interactions
 */

class PropertyOwnerProfile {
    constructor() {
        this.profileData = null;
        this.currentTab = 'post';
        this.projectPosts = [];
        this.allProjects = [];
        this.filteredProjects = [];
        this.currentFilters = {
            status: 'all',
            sort: 'newest',
            search: ''
        };
        this.reviews = [];

        this.init();
    }

    async init() {
        await this.loadProfileData();
        this.setupEventListeners();
    }

    setupEventListeners() {
        // Tab switching
        const tabs = document.querySelectorAll('.profile-tab');
        tabs.forEach(tab => {
            tab.addEventListener('click', (e) => {
                this.createRippleEffect(e.target, e);
                const tabName = tab.getAttribute('data-tab');
                this.switchTab(tabName);
            });
        });

        // Edit profile button
        const editProfileBtn = document.getElementById('editProfileBtn');
        if (editProfileBtn) {
            editProfileBtn.addEventListener('click', (e) => {
                this.createRippleEffect(e.target, e);
                this.openEditProfileModal();
            });
        }

        // Edit profile modal event listeners
        this.setupEditProfileModal();

        // Cover photo edit button
        const editCoverPhotoBtn = document.getElementById('editCoverPhotoBtn');
        const coverPhotoInput = document.getElementById('coverPhotoInput');
        if (editCoverPhotoBtn && coverPhotoInput) {
            editCoverPhotoBtn.addEventListener('click', (e) => {
                this.createRippleEffect(e.target, e);
                coverPhotoInput.click();
            });
            coverPhotoInput.addEventListener('change', (e) => {
                this.handleCoverPhotoChange(e);
            });
        }

        // Profile picture edit button
        const editProfilePicBtn = document.getElementById('editProfilePicBtn');
        const profilePicInput = document.getElementById('profilePicInput');
        if (editProfilePicBtn && profilePicInput) {
            editProfilePicBtn.addEventListener('click', (e) => {
                this.createRippleEffect(e.target, e);
                profilePicInput.click();
            });
            profilePicInput.addEventListener('change', (e) => {
                this.handleProfilePictureChange(e);
            });
        }

        // Profile picture click handler (also triggers file input)
        const profilePicture = document.getElementById('profilePicture');
        if (profilePicture && profilePicInput) {
            profilePicture.addEventListener('click', (e) => {
                profilePicInput.click();
            });
        }

        // Add ripple effects to interactive cards
        this.setupCardInteractions();

        // Projects filter event listeners
        this.setupProjectsFilter();
    }

    async loadProfileData() {
        // Fetch profile data from backend API
        try {
            const response = await fetch('/owner/profile/fetch?role=owner', {
                method: 'GET',
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                credentials: 'same-origin'
            });

            if (!response.ok) {
                throw new Error('Failed to fetch profile data');
            }

            const result = await response.json();
            console.log('Profile API response:', result);

            // API returns { success: true, data: {...} }
            if (!result.success || !result.data || !result.data.user) {
                console.warn('No profile data received, using defaults');
                this.setDefaultProfileData();
                return;
            }

            const data = result.data;
            const user = data.user;
            const owner = data.owner || {};

            // Name comes from property_owners table (first_name, middle_name, last_name)
            const firstName = owner.first_name || '';
            const middleName = owner.middle_name || '';
            const lastName = owner.last_name || '';
            const name = `${firstName} ${middleName} ${lastName}`.replace(/\s+/g, ' ').trim() || 'Property Owner';
            const location = data.address_display || owner.address || 'Location not set';

            // Parse address into parts (format: "Street, Barangay, City, Province [PostalCode]")
            const addressParts = this.parseAddress(owner.address || '');

            console.log('Owner data:', owner);
            console.log('Parsed name:', name);
            console.log('Parsed address:', addressParts);

            this.profileData = {
                userId: user.user_id,
                name: name,
                username: `@${user.username || name.toLowerCase().replace(/\s+/g, '')}`,
                rawUsername: user.username || '',
                rating: data.rating || null,
                totalReviews: data.total_reviews || 0,
                location: location,
                bio: 'Property owner on Legatura platform', // No bio field in database
                occupation: data.occupation_name || 'Not specified',
                occupationId: owner.occupation_id || null,
                occupationOther: owner.occupation_other || null,
                projectsDone: data.projects_done || 0,
                ongoingProjects: data.ongoing_projects || 0,
                profilePicture: user.profile_pic ? `/storage/${user.profile_pic}` : null,
                coverImage: user.cover_photo ? `/storage/${user.cover_photo}` : null,
                dateOfBirth: owner.date_of_birth || null,
                // Address parts
                addressStreet: addressParts.street || '',
                addressBarangay: addressParts.barangay || '',
                addressCity: addressParts.city || '',
                addressProvince: addressParts.province || ''
            };

            // Transform projects to posts
            const projects = data.projects || [];
            this.projectPosts = projects.map((project, index) => {
                // Get first image from files array (handle both old string format and new object format)
                let firstImage = null;
                if (project.files && project.files.length > 0) {
                    const firstFile = project.files[0];
                    if (typeof firstFile === 'string') {
                        firstImage = `/storage/${firstFile}`;
                    } else if (firstFile.file_path) {
                        firstImage = `/storage/${firstFile.file_path}`;
                    }
                }
                const postDate = project.post_created_at || project.created_at;
                return {
                    id: project.project_id,
                    userId: user.user_id,
                    userName: name,
                    userHandle: `@${user.username || name.toLowerCase().replace(/\s+/g, '')}`,
                    userInitials: this.getInitials(name),
                    userProfilePicture: this.profileData.profilePicture,
                    projectTitle: project.project_title || 'Untitled Project',
                    projectImage: firstImage,
                    timestamp: postDate ? this.formatTimestamp(postDate) : '',
                    description: project.project_description || ''
                };
            });

            // Store projects for Projects tab
            this.allProjects = projects.map(project => {
                // Get first image from files array (handle both old string format and new object format)
                let firstImage = null;
                if (project.files && project.files.length > 0) {
                    const firstFile = project.files[0];
                    if (typeof firstFile === 'string') {
                        firstImage = `/storage/${firstFile}`;
                    } else if (firstFile.file_path) {
                        firstImage = `/storage/${firstFile.file_path}`;
                    }
                }
                return {
                    id: project.project_id,
                    title: project.project_title || 'Untitled Project',
                    description: project.project_description || '',
                    location: project.project_location || this.profileData.location,
                    budgetMin: project.budget_range_min || 0,
                    budgetMax: project.budget_range_max || 0,
                    lotSize: project.lot_size || 0,
                    floorArea: project.floor_area || 0,
                    propertyType: project.property_type || 'Residential',
                    contractorType: project.contractor_type_name || 'General Contractor',
                    contractorTypeOther: project.if_others_ctype || null,
                    status: project.project_status || 'open',
                    biddingDeadline: project.bidding_due || null,
                    bidsCount: project.bids_count || 0,
                    date: project.post_created_at || project.created_at || '',
                    image: firstImage,
                    files: project.files || [],
                    selectedContractorId: project.selected_contractor_id || null
                };
            });
            this.filteredProjects = [...this.allProjects];

            // Update the DOM with profile data
            this.updateProfileDOM();

            // Load reviews data
            await this.loadReviews();

            this.updateCalculatedFields();
            this.renderProjectPosts();
            this.updateCoverPhoto();

        } catch (error) {
            console.error('Error loading profile data:', error);
            this.setDefaultProfileData();
        }
    }

    formatTimestamp(dateStr) {
        try {
            const date = new Date(dateStr);
            const options = { hour: 'numeric', minute: '2-digit', hour12: true };
            const timeStr = date.toLocaleTimeString('en-US', options);
            const dateOptions = { month: 'long', day: 'numeric', year: 'numeric' };
            const dateFormatted = date.toLocaleDateString('en-US', dateOptions);
            return `${timeStr} · ${dateFormatted}`;
        } catch (e) {
            return dateStr;
        }
    }

    setDefaultProfileData() {
        this.profileData = {
            userId: null,
            name: 'Property Owner',
            username: '@propertyowner',
            rating: null,
            totalReviews: 0,
            location: 'Location not set',
            bio: 'No bio available',
            occupation: 'Not specified',
            projectsDone: 0,
            ongoingProjects: 0,
            profilePicture: null,
            coverImage: null
        };
        this.projectPosts = [];
        this.allProjects = [];
        this.filteredProjects = [];
        this.reviews = [];

        this.updateProfileDOM();
        this.updateCalculatedFields();
        this.renderProjectPosts();
    }

    updateProfileDOM() {
        if (!this.profileData) return;

        // Update profile header
        const profileName = document.getElementById('profileName');
        const profileRating = document.getElementById('profileRating');
        const profileLocation = document.getElementById('profileLocation');
        const infoCardName = document.getElementById('infoCardName');
        const infoCardRating = document.getElementById('infoCardRating');
        const infoCardLocation = document.getElementById('infoCardLocation');
        const infoCardBio = document.getElementById('infoCardBio');
        const occupationValue = document.getElementById('occupationValue');
        const projectsDone = document.getElementById('projectsDone');
        const ongoingProjects = document.getElementById('ongoingProjects');

        // About section elements
        const aboutBioText = document.getElementById('aboutBioText');
        const aboutOccupation = document.getElementById('aboutOccupation');
        const aboutLocation = document.getElementById('aboutLocation');
        const aboutProjectsDone = document.getElementById('aboutProjectsDone');
        const aboutActiveProjects = document.getElementById('aboutActiveProjects');

        // Update values
        if (profileName) profileName.textContent = this.profileData.name;
        if (profileRating) profileRating.textContent = this.profileData.rating !== null ? this.profileData.rating.toFixed(1) : '—';
        if (profileLocation) profileLocation.textContent = this.profileData.location;
        if (infoCardName) infoCardName.textContent = this.profileData.name;
        if (infoCardRating) infoCardRating.textContent = this.profileData.rating !== null ? this.profileData.rating.toFixed(1) : '—';
        if (infoCardLocation) infoCardLocation.textContent = this.profileData.location;
        if (infoCardBio) infoCardBio.textContent = this.profileData.bio;
        if (occupationValue) occupationValue.textContent = this.profileData.occupation;
        if (projectsDone) projectsDone.textContent = this.profileData.projectsDone;
        if (ongoingProjects) ongoingProjects.textContent = this.profileData.ongoingProjects;

        // Update about section
        if (aboutBioText) aboutBioText.textContent = this.profileData.bio;
        if (aboutOccupation) aboutOccupation.textContent = this.profileData.occupation;
        if (aboutLocation) aboutLocation.textContent = this.profileData.location;
        if (aboutProjectsDone) aboutProjectsDone.textContent = this.profileData.projectsDone;
        if (aboutActiveProjects) aboutActiveProjects.textContent = this.profileData.ongoingProjects;

        // Update profile picture
        const profilePicture = document.getElementById('profilePicture');
        if (profilePicture) {
            if (this.profileData.profilePicture) {
                profilePicture.innerHTML = `<img src="${this.profileData.profilePicture}" alt="${this.profileData.name}" class="profile-picture-img" onerror="this.parentElement.innerHTML='<span class=\\'profile-picture-initials\\'>${this.getInitials(this.profileData.name)}</span>'">`;
            } else {
                profilePicture.innerHTML = `<span class="profile-picture-initials">${this.getInitials(this.profileData.name)}</span>`;
            }
        }
    }

    updateCoverPhoto() {
        const coverPhotoImg = document.getElementById('coverPhotoImg');
        if (coverPhotoImg && this.profileData && this.profileData.coverImage) {
            coverPhotoImg.src = this.profileData.coverImage;
        }
    }

    loadAllProjects() {
        // Projects already loaded in loadProfileData from API
        // This is a fallback that will use already loaded data
        if (!this.allProjects || this.allProjects.length === 0) {
            this.allProjects = [];
        }
        this.filteredProjects = [...this.allProjects];
    }

    updateCalculatedFields() {
        // Only update calculated fields like initials based on current HTML content
        // This preserves all HTML content and only updates what needs to be calculated

        const profileNameCurrent = document.getElementById('profileName');
        const currentName = profileNameCurrent ? profileNameCurrent.textContent.trim() : this.profileData.name;

        // Update profile picture initials based on current name
        const profilePictureInitials = document.querySelector('.profile-picture-initials');
        if (profilePictureInitials && currentName) {
            const initials = this.getInitials(currentName);
            profilePictureInitials.textContent = initials;
        }

        // Update post input initials
        const postInputInitials = document.querySelector('.post-input-initials');
        if (postInputInitials && currentName) {
            const initials = this.getInitials(currentName);
            postInputInitials.textContent = initials;
        }
    }

    getInitials(name) {
        return name
            .split(' ')
            .map(word => word[0])
            .join('')
            .toUpperCase()
            .substring(0, 3);
    }

    renderProjectPosts() {
        const feed = document.getElementById('projectPostsFeed');
        if (!feed) return;

        feed.innerHTML = '';

        if (!this.projectPosts || this.projectPosts.length === 0) {
            feed.innerHTML = `
                <div class="project-post-card">
                    <div class="post-card-content" style="text-align: center; padding: 3rem;">
                        <p class="text-gray-500">No project posts yet</p>
                    </div>
                </div>
            `;
            return;
        }

        this.projectPosts.forEach(post => {
            const postCard = this.createProjectPostCard(post);
            feed.appendChild(postCard);
        });
    }

    createProjectPostCard(post) {
        const card = document.createElement('div');
        card.className = 'project-post-card';
        card.setAttribute('data-post-id', post.id);

        const initials = post.userInitials || this.getInitials(post.userName);

        card.innerHTML = `
            <div class="post-card-header">
                <div class="post-card-avatar">
                    ${post.userProfilePicture
                        ? `<img src="${post.userProfilePicture}" alt="${post.userName}">`
                        : `<span class="post-card-avatar-initials">${initials}</span>`
                    }
                </div>
                <div class="post-card-user-info">
                    <p class="post-card-name">${post.userName}</p>
                    <p class="post-card-handle">${post.userHandle || ''}</p>
                </div>
            </div>
            ${post.projectImage ? `
                <img src="${post.projectImage}" alt="${post.projectTitle}" class="post-card-image">
            ` : ''}
            <div class="post-card-content">
                <h3 class="post-card-title">${post.projectTitle}</h3>
                ${post.description ? `<p style="color: #6b7280; font-size: 0.9375rem; margin-bottom: 0.5rem;">${post.description}</p>` : ''}
                <a href="#" class="post-card-more" data-post-id="${post.id}">More details...</a>
            </div>
            <div class="post-card-footer">
                <span class="post-card-timestamp">${post.timestamp}</span>
            </div>
        `;

        // Add click handler for "More details"
        const moreLink = card.querySelector('.post-card-more');
        if (moreLink) {
            moreLink.addEventListener('click', (e) => {
                e.preventDefault();
                e.stopPropagation();
                this.createRippleEffect(moreLink, e);
                setTimeout(() => {
                this.handleViewProjectDetails(post.id);
                }, 300);
            });
        }

        // Add hover effect animation
        card.addEventListener('mouseenter', () => {
            card.style.transition = 'all 0.3s cubic-bezier(0.4, 0, 0.2, 1)';
        });

        return card;
    }

    switchTab(tabName) {
        this.currentTab = tabName;

        // Update tab active states with animation
        const tabs = document.querySelectorAll('.profile-tab');
        tabs.forEach(tab => {
            if (tab.getAttribute('data-tab') === tabName) {
                tab.classList.add('active');
                tab.style.transform = 'scale(1.05)';
                setTimeout(() => {
                    tab.style.transform = 'scale(1)';
                }, 200);
            } else {
                tab.classList.remove('active');
            }
        });

        // Handle tab content switching with fade animation
        switch(tabName) {
            case 'post':
                this.showPostTab();
                break;
            case 'projects':
                this.showProjectsTab();
                break;
            case 'reviews':
                this.showReviewsTab();
                break;
            case 'about':
                this.showAboutTab();
                break;
        }
    }

    showPostTab() {
        const postFeed = document.getElementById('projectPostsFeed');
        const projectsContainer = document.getElementById('projectsListContainer');
        const reviewsContainer = document.getElementById('reviewsContainer');
        const aboutSection = document.getElementById('aboutSection');
        const filterSection = document.getElementById('projectsFilterSection');

        // Fade out current content
        if (postFeed && !postFeed.classList.contains('hidden')) return;

        [projectsContainer, reviewsContainer, aboutSection].forEach(container => {
            if (container && !container.classList.contains('hidden')) {
                container.style.opacity = '0';
                container.style.transform = 'translateY(10px)';
                setTimeout(() => {
                    container.classList.add('hidden');
                }, 200);
            }
        });

        // Fade in new content
        if (postFeed) {
            postFeed.classList.remove('hidden');
            postFeed.style.opacity = '0';
            postFeed.style.transform = 'translateY(10px)';
            postFeed.style.transition = 'opacity 0.3s ease, transform 0.3s ease';

            setTimeout(() => {
                postFeed.style.opacity = '1';
                postFeed.style.transform = 'translateY(0)';
            }, 50);
        }

        if (filterSection) filterSection.classList.add('hidden');

        this.renderProjectPosts();
    }

    showProjectsTab() {
        const postFeed = document.getElementById('projectPostsFeed');
        const projectsContainer = document.getElementById('projectsListContainer');
        const reviewsContainer = document.getElementById('reviewsContainer');
        const aboutSection = document.getElementById('aboutSection');
        const filterSection = document.getElementById('projectsFilterSection');

        // Fade out current content
        if (projectsContainer && !projectsContainer.classList.contains('hidden')) return;

        [postFeed, reviewsContainer, aboutSection].forEach(container => {
            if (container && !container.classList.contains('hidden')) {
                container.style.opacity = '0';
                container.style.transform = 'translateY(10px)';
                setTimeout(() => {
                    container.classList.add('hidden');
                }, 200);
            }
        });

        // Fade in new content
        if (projectsContainer) {
            projectsContainer.classList.remove('hidden');
            projectsContainer.style.opacity = '0';
            projectsContainer.style.transform = 'translateY(10px)';
            projectsContainer.style.transition = 'opacity 0.3s ease, transform 0.3s ease';

            setTimeout(() => {
                projectsContainer.style.opacity = '1';
                projectsContainer.style.transform = 'translateY(0)';
            }, 50);
        }

        if (filterSection) {
            filterSection.classList.remove('hidden');
            filterSection.style.opacity = '0';
            filterSection.style.transition = 'opacity 0.3s ease';
            setTimeout(() => {
                filterSection.style.opacity = '1';
            }, 50);
        }

        this.renderProjects();
        this.updateProjectsFilterBadge();
    }

    showReviewsTab() {
        const postFeed = document.getElementById('projectPostsFeed');
        const projectsContainer = document.getElementById('projectsListContainer');
        const reviewsContainer = document.getElementById('reviewsContainer');
        const aboutSection = document.getElementById('aboutSection');
        const filterSection = document.getElementById('projectsFilterSection');

        // Fade out current content
        if (reviewsContainer && !reviewsContainer.classList.contains('hidden')) return;

        [postFeed, projectsContainer, aboutSection].forEach(container => {
            if (container && !container.classList.contains('hidden')) {
                container.style.opacity = '0';
                container.style.transform = 'translateY(10px)';
                setTimeout(() => {
                    container.classList.add('hidden');
                }, 200);
            }
        });

        // Fade in new content
        if (reviewsContainer) {
            reviewsContainer.classList.remove('hidden');
            reviewsContainer.style.opacity = '0';
            reviewsContainer.style.transform = 'translateY(10px)';
            reviewsContainer.style.transition = 'opacity 0.3s ease, transform 0.3s ease';

            setTimeout(() => {
                reviewsContainer.style.opacity = '1';
                reviewsContainer.style.transform = 'translateY(0)';
            }, 50);
        }

        if (filterSection) filterSection.classList.add('hidden');

        this.renderReviews();
    }

    showAboutTab() {
        const postFeed = document.getElementById('projectPostsFeed');
        const projectsContainer = document.getElementById('projectsListContainer');
        const reviewsContainer = document.getElementById('reviewsContainer');
        const aboutSection = document.getElementById('aboutSection');
        const filterSection = document.getElementById('projectsFilterSection');

        // Fade out current content
        if (aboutSection && !aboutSection.classList.contains('hidden')) return;

        [postFeed, projectsContainer, reviewsContainer].forEach(container => {
            if (container && !container.classList.contains('hidden')) {
                container.style.opacity = '0';
                container.style.transform = 'translateY(10px)';
                setTimeout(() => {
                    container.classList.add('hidden');
                }, 200);
            }
        });

        // Fade in about section
        if (aboutSection) {
            aboutSection.classList.remove('hidden');
            aboutSection.style.opacity = '0';
            aboutSection.style.transform = 'translateY(10px)';
            aboutSection.style.transition = 'opacity 0.3s ease, transform 0.3s ease';
            setTimeout(() => {
                aboutSection.style.opacity = '1';
                aboutSection.style.transform = 'translateY(0)';
            }, 50);
        }

        if (filterSection) filterSection.classList.add('hidden');
    }

    async loadReviews() {
        // Fetch reviews from backend API
        try {
            // Need to pass user_id for the reviews endpoint
            if (!this.profileData || !this.profileData.userId) {
                console.warn('No user ID available for loading reviews');
                this.reviews = [];
                return;
            }

            const response = await fetch(`/owner/profile/reviews?role=owner&user_id=${this.profileData.userId}`, {
                method: 'GET',
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                credentials: 'same-origin'
            });

            if (!response.ok) {
                throw new Error('Failed to fetch reviews');
            }

            const result = await response.json();
            console.log('Reviews API response:', result);

            // API returns { success: true, data: { reviews: [...], stats: {...} } }
            if (!result.success || !result.data || !result.data.reviews) {
                this.reviews = [];
                return;
            }

            this.reviews = result.data.reviews.map(review => ({
                id: review.review_id,
                reviewerName: review.reviewer_company_name || review.reviewer_name || review.reviewer_username || 'Anonymous',
                reviewerType: 'Contractor',
                reviewerProfilePic: review.reviewer_profile_pic ? `/storage/${review.reviewer_profile_pic}` : null,
                rating: parseFloat(review.rating) || 0,
                reviewText: review.comment || '',
                timestamp: review.created_at ? this.formatTimestamp(review.created_at) : '',
                projectTitle: review.project_title || 'Project',
                projectDate: review.project_date || '',
                projectStatus: 'Completed'
            }));

            // Update rating from reviews stats if available
            if (result.data.stats && result.data.stats.avg_rating !== null) {
                this.profileData.rating = result.data.stats.avg_rating;
                this.profileData.totalReviews = result.data.stats.total_reviews || 0;
                this.updateProfileDOM();
            }

        } catch (error) {
            console.error('Error loading reviews:', error);
            this.reviews = [];
        }
    }

    getReviewAvatarColor(reviewerName) {
        // Generate a consistent color based on the company name
        const colors = [
            { bg: 'linear-gradient(135deg, #1f2937 0%, #374151 100%)', class: 'avatar-color-1' },
            { bg: 'linear-gradient(135deg, #EEA24B 0%, #F57C00 100%)', class: 'avatar-color-2' },
            { bg: 'linear-gradient(135deg, #3b82f6 0%, #2563eb 100%)', class: 'avatar-color-3' },
            { bg: 'linear-gradient(135deg, #10b981 0%, #059669 100%)', class: 'avatar-color-4' },
            { bg: 'linear-gradient(135deg, #8b5cf6 0%, #7c3aed 100%)', class: 'avatar-color-5' },
            { bg: 'linear-gradient(135deg, #ec4899 0%, #db2777 100%)', class: 'avatar-color-6' },
            { bg: 'linear-gradient(135deg, #f59e0b 0%, #d97706 100%)', class: 'avatar-color-7' },
            { bg: 'linear-gradient(135deg, #14b8a6 0%, #0d9488 100%)', class: 'avatar-color-8' }
        ];

        // Use hash of company name to get consistent color
        let hash = 0;
        for (let i = 0; i < reviewerName.length; i++) {
            hash = reviewerName.charCodeAt(i) + ((hash << 5) - hash);
        }

        const index = Math.abs(hash) % colors.length;
        return colors[index];
    }

    renderReviews() {
        const reviewsList = document.getElementById('reviewsList');
        const emptyState = document.getElementById('reviewsEmptyState');

        if (!reviewsList) return;

        reviewsList.innerHTML = '';

        // Update reviews summary
        this.updateReviewsSummary();

        if (!this.reviews || this.reviews.length === 0) {
            if (emptyState) emptyState.classList.remove('hidden');
            return;
        }

        if (emptyState) emptyState.classList.add('hidden');

        this.reviews.forEach((review, index) => {
            const reviewCard = this.createReviewCard(review);
            reviewsList.appendChild(reviewCard);

            // Add staggered animation
            setTimeout(() => {
                reviewCard.style.opacity = '0';
                reviewCard.style.transform = 'translateY(20px)';
                reviewCard.style.transition = 'all 0.4s ease';

                requestAnimationFrame(() => {
                    reviewCard.style.opacity = '1';
                    reviewCard.style.transform = 'translateY(0)';
                });
            }, index * 100);
        });
    }

    updateReviewsSummary() {
        const avgEl = document.getElementById('reviewsAvgRating');
        const starsEl = document.getElementById('reviewsSummaryStars');
        const countEl = document.getElementById('reviewsTotalCount');

        if (!this.reviews || this.reviews.length === 0) {
            if (avgEl) avgEl.textContent = '—';
            if (starsEl) starsEl.innerHTML = '';
            if (countEl) countEl.textContent = 'No reviews yet';
            return;
        }

        const total = this.reviews.length;
        const avg = this.reviews.reduce((sum, r) => sum + (r.rating || 0), 0) / total;
        const avgRounded = Math.round(avg * 10) / 10;

        if (avgEl) avgEl.textContent = avgRounded.toFixed(1);
        if (countEl) countEl.textContent = `Based on ${total} review${total !== 1 ? 's' : ''}`;

        if (starsEl) {
            let starsHTML = '';
            for (let i = 1; i <= 5; i++) {
                starsHTML += `<i class="fi fi-rr-star star-icon${i > avg ? ' empty' : ''}"></i>`;
            }
            starsEl.innerHTML = starsHTML;
        }
    }

    createReviewCard(review) {
        const card = document.createElement('div');
        card.className = 'review-card';
        card.setAttribute('data-review-id', review.id);

        // Generate star rating HTML
        const starsHTML = this.generateStarsHTML(review.rating);

        // Get avatar color for this company
        const avatarColor = this.getReviewAvatarColor(review.reviewerName);
        const initials = this.getInitials(review.reviewerName);

        card.innerHTML = `
            <div class="review-card-content">
                <div class="review-header">
                    <div class="review-avatar ${avatarColor.class}" style="background: ${avatarColor.bg};">
                        <div class="review-avatar-initials">${initials}</div>
                    </div>
                    <div class="review-info">
                        <h3 class="review-company-name">${review.reviewerName}</h3>
                        <div class="review-rating-display">
                            <span class="review-rating-value">${review.rating}</span>
                            <div class="review-stars">${starsHTML}</div>
                        </div>
                    </div>
                </div>
                <div class="review-body">
                    <p class="review-text">${review.reviewText}</p>
                </div>
                <div class="review-meta">
                    <div class="review-project-info">
                        <i class="fi fi-rr-briefcase"></i>
                        <span class="review-project-title">${review.projectTitle}</span>
                    </div>
                    <div class="review-project-details">
                        <span class="review-project-date">
                            <i class="fi fi-rr-calendar"></i>
                            ${review.projectDate}
                        </span>
                        <span class="review-project-status status-${review.projectStatus.toLowerCase()}">
                            <i class="fi fi-rr-flag"></i>
                            ${review.projectStatus}
                        </span>
                    </div>
                </div>
                <div class="review-footer">
                    <span class="review-timestamp">${review.timestamp}</span>
                </div>
            </div>
            <div class="review-divider"></div>
        `;

        // Add click handler to view project details
        const projectInfo = card.querySelector('.review-project-info');
        if (projectInfo) {
            projectInfo.style.cursor = 'pointer';
            projectInfo.addEventListener('click', (e) => {
                e.stopPropagation();
                this.createRippleEffect(projectInfo, e);
                setTimeout(() => {
                this.handleViewReviewProject(review);
                }, 300);
            });
        }

        // Add hover effect animation
        card.addEventListener('mouseenter', () => {
            card.style.transition = 'all 0.3s cubic-bezier(0.4, 0, 0.2, 1)';
        });

        return card;
    }

    generateStarsHTML(rating) {
        const fullStars = Math.floor(rating);
        const hasHalfStar = rating % 1 >= 0.5;
        let starsHTML = '';

        for (let i = 0; i < fullStars; i++) {
            starsHTML += '<i class="fi fi-sr-star review-star filled"></i>';
        }

        if (hasHalfStar) {
            starsHTML += '<i class="fi fi-sr-star review-star half"></i>';
        }

        const emptyStars = 5 - fullStars - (hasHalfStar ? 1 : 0);
        for (let i = 0; i < emptyStars; i++) {
            starsHTML += '<i class="fi fi-sr-star review-star empty"></i>';
        }

        return starsHTML;
    }

    handleViewReviewProject(review) {
        console.log('View project from review:', review);
        // In a real implementation, navigate to project details or open modal
        this.showNotification(`Viewing project: ${review.projectTitle}`, 'info');
    }

    applyProjectsFilters() {
        let filtered = [...this.allProjects];

        // Apply status filter
        if (this.currentFilters.status && this.currentFilters.status !== 'all') {
            filtered = filtered.filter(project => project.status === this.currentFilters.status);
        }

        // Apply sorting
        filtered.sort((a, b) => {
            switch (this.currentFilters.sort) {
                case 'oldest':
                    return new Date(a.date) - new Date(b.date);
                case 'title':
                    return a.title.localeCompare(b.title);
                case 'status':
                    return a.status.localeCompare(b.status);
                case 'newest':
                default:
                    return new Date(b.date) - new Date(a.date);
            }
        });

        this.filteredProjects = filtered;
        this.renderProjects();
        this.updateProjectsFilterBadge();
    }

    setupProjectsFilter() {
        const filterIconBtn = document.getElementById('projectsFilterIconBtn');
        const filterDropdown = document.getElementById('projectsFilterDropdown');
        const filterCloseBtn = document.getElementById('projectsFilterCloseBtn');
        const filterApplyBtn = document.getElementById('applyProjectsFiltersBtn');
        const filterClearBtn = document.getElementById('clearProjectsFiltersBtn');
        const filterBadge = document.getElementById('projectsFilterBadge');
        const statusFilter = document.getElementById('projectsStatusFilter');
        const sortFilter = document.getElementById('projectsSortFilter');

        // Toggle dropdown on filter icon click
        if (filterIconBtn && filterDropdown) {
            filterIconBtn.addEventListener('click', (e) => {
                e.stopPropagation();
                this.toggleProjectsFilterDropdown();
            });
        }

        // Close dropdown on close button click
        if (filterCloseBtn) {
            filterCloseBtn.addEventListener('click', () => {
                this.closeProjectsFilterDropdown();
            });
        }

        // Apply filters
        if (filterApplyBtn) {
            filterApplyBtn.addEventListener('click', () => {
                this.applyProjectsFiltersFromUI();
            });
        }

        // Clear filters
        if (filterClearBtn) {
            filterClearBtn.addEventListener('click', () => {
                this.clearProjectsFilters();
            });
        }

        // Close dropdown when clicking outside
        document.addEventListener('click', (e) => {
            if (filterDropdown &&
                !filterDropdown.contains(e.target) &&
                filterIconBtn &&
                !filterIconBtn.contains(e.target)) {
                this.closeProjectsFilterDropdown();
            }
        });
    }

    toggleProjectsFilterDropdown() {
        const filterDropdown = document.getElementById('projectsFilterDropdown');
        const filterIconBtn = document.getElementById('projectsFilterIconBtn');

        if (filterDropdown && filterIconBtn) {
            filterDropdown.classList.toggle('active');
            filterIconBtn.classList.toggle('active');
        }
    }

    closeProjectsFilterDropdown() {
        const filterDropdown = document.getElementById('projectsFilterDropdown');
        const filterIconBtn = document.getElementById('projectsFilterIconBtn');

        if (filterDropdown) filterDropdown.classList.remove('active');
        if (filterIconBtn) filterIconBtn.classList.remove('active');
    }

    applyProjectsFiltersFromUI() {
        const statusFilter = document.getElementById('projectsStatusFilter');
        const sortFilter = document.getElementById('projectsSortFilter');

        if (statusFilter) {
            this.currentFilters.status = statusFilter.value;
        }
        if (sortFilter) {
            this.currentFilters.sort = sortFilter.value;
        }

        this.applyProjectsFilters();
        this.updateProjectsFilterBadge();
        this.closeProjectsFilterDropdown();
    }

    updateProjectsFilterBadge() {
        const filterBadge = document.getElementById('projectsFilterBadge');
        const filterIconBtn = document.getElementById('projectsFilterIconBtn');

        if (!filterBadge || !filterIconBtn) return;

        const activeFilterCount = this.getActiveFiltersCount();

        if (activeFilterCount > 0) {
            filterBadge.textContent = activeFilterCount;
            filterBadge.classList.remove('hidden');
            filterIconBtn.classList.add('active');
        } else {
            filterBadge.classList.add('hidden');
            filterIconBtn.classList.remove('active');
        }
    }

    getActiveFiltersCount() {
        let count = 0;
        if (this.currentFilters.status && this.currentFilters.status !== 'all') count++;
        if (this.currentFilters.sort && this.currentFilters.sort !== 'newest') count++;
        return count;
    }

    clearProjectsFilters() {
        this.currentFilters = {
            status: 'all',
            sort: 'newest',
            search: ''
        };

        // Reset filter inputs
        const statusFilter = document.getElementById('projectsStatusFilter');
        const sortFilter = document.getElementById('projectsSortFilter');

        if (statusFilter) statusFilter.value = 'all';
        if (sortFilter) sortFilter.value = 'newest';

        this.applyProjectsFilters();
        this.updateProjectsFilterBadge();
        this.closeProjectsFilterDropdown();
    }

    renderProjects() {
        const grid = document.getElementById('projectsGrid');
        if (!grid) return;

        grid.innerHTML = '';

        if (!this.filteredProjects || this.filteredProjects.length === 0) {
            grid.innerHTML = `
                <div class="projects-empty-state">
                    <i class="fi fi-rr-folder-open text-6xl text-gray-300 mb-4"></i>
                    <h3 class="text-xl font-semibold text-gray-700 mb-2">No Projects Found</h3>
                    <p class="text-gray-500">Try adjusting your filters</p>
                </div>
            `;
            return;
        }

        this.filteredProjects.forEach((project, index) => {
            setTimeout(() => {
                const projectCard = this.createProjectCard(project);
                grid.appendChild(projectCard);
            }, index * 50);
        });
    }

    createProjectCard(project) {
        const card = document.createElement('div');
        card.className = 'project-post-card';
        card.setAttribute('data-project-id', project.id);

        // Get user info from profile data or use defaults
        const userName = this.profileData?.name || 'Property Owner';
        const userInitials = this.getInitials(userName);
        const userHandle = this.profileData?.handle || '';

        // Status colors and labels
        const statusColors = {
            'pending': '#f59e0b',
            'active': '#10b981',
            'in_progress': '#3b82f6',
            'completed': '#6b7280',
            'cancelled': '#ef4444'
        };

        const statusLabels = {
            'pending': 'Pending',
            'active': 'Active',
            'in_progress': 'In Progress',
            'completed': 'Completed',
            'cancelled': 'Cancelled'
        };

        const statusColor = statusColors[project.status] || '#6b7280';
        const statusLabel = statusLabels[project.status] || project.status;

        card.innerHTML = `
            <div class="post-card-header">
                <div class="post-card-avatar">
                    ${this.profileData?.profilePicture
                        ? `<img src="${this.profileData.profilePicture}" alt="${userName}">`
                        : `<span class="post-card-avatar-initials">${userInitials}</span>`
                    }
                </div>
                <div class="post-card-user-info">
                    <p class="post-card-name">${userName}</p>
                    ${userHandle ? `<p class="post-card-handle">${userHandle}</p>` : ''}
                </div>
            </div>
            ${project.image ? `
                <div class="post-card-image-wrapper" style="position: relative;">
                    <img src="${project.image}" alt="${project.title}" class="post-card-image">
                    <div class="project-status-badge" style="position: absolute; top: 1rem; right: 1rem; padding: 0.5rem 0.875rem; border-radius: 0.5rem; font-size: 0.8125rem; font-weight: 600; display: flex; align-items: center; gap: 0.5rem; background-color: ${statusColor}20; color: ${statusColor}; border: 2px solid ${statusColor}; backdrop-filter: blur(8px); box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);">
                        <span style="width: 8px; height: 8px; border-radius: 50%; background-color: ${statusColor}; display: inline-block; flex-shrink: 0;"></span>
                        ${statusLabel}
                    </div>
                </div>
            ` : ''}
            <div class="post-card-content">
                <h3 class="post-card-title">${project.title}</h3>
                ${project.type ? `<p style="color: #EEA24B; font-size: 0.875rem; font-weight: 600; margin-bottom: 0.5rem;">${project.type}</p>` : ''}
                ${project.description ? `<p style="color: #6b7280; font-size: 0.9375rem; margin-bottom: 0.5rem;">${project.description}</p>` : ''}
                ${project.location ? `<p style="color: #6b7280; font-size: 0.875rem; margin-bottom: 0.5rem;"><i class="fi fi-rr-marker" style="color: #EEA24B; margin-right: 0.5rem;"></i>${project.location}</p>` : ''}
                <a href="#" class="post-card-more" data-project-id="${project.id}">More details...</a>
            </div>
            <div class="post-card-footer">
                <span class="post-card-timestamp">${this.formatDate(project.date)}</span>
            </div>
        `;

        // Add click handler for "More details"
        const moreLink = card.querySelector('.post-card-more');
        if (moreLink) {
            moreLink.addEventListener('click', (e) => {
                e.preventDefault();
                e.stopPropagation();
                this.createRippleEffect(moreLink, e);
                setTimeout(() => {
                this.handleViewProjectDetails(project.id);
                }, 300);
            });
        }

        // Add click handler for card
        card.addEventListener('click', (e) => {
            if (!e.target.closest('.post-card-more') && !e.target.closest('.project-status-badge')) {
                this.createRippleEffect(card, e);
                setTimeout(() => {
                this.handleViewProjectDetails(project.id);
                }, 300);
            }
        });

        // Add hover effect animation
        card.addEventListener('mouseenter', () => {
            card.style.transition = 'all 0.3s cubic-bezier(0.4, 0, 0.2, 1)';
        });

        return card;
    }

    formatDate(dateString) {
        const date = new Date(dateString);
        const options = { year: 'numeric', month: 'short', day: 'numeric' };
        return date.toLocaleDateString('en-US', options);
    }

    handleSendMessage() {
        // Deprecated - replaced with openEditProfileModal
        this.openEditProfileModal();
    }

    setupEditProfileModal() {
        const modal = document.getElementById('editProfileModal');
        const modalOverlay = document.getElementById('editProfileModalOverlay');
        const modalClose = document.getElementById('editProfileModalClose');
        const cancelBtn = document.getElementById('editProfileCancelBtn');
        const saveBtn = document.getElementById('editProfileSaveBtn');
        const editForm = document.getElementById('editProfileForm');

        // Close modal handlers
        if (modalClose) {
            modalClose.addEventListener('click', () => this.closeEditProfileModal());
        }
        if (cancelBtn) {
            cancelBtn.addEventListener('click', () => this.closeEditProfileModal());
        }
        if (modalOverlay) {
            modalOverlay.addEventListener('click', () => this.closeEditProfileModal());
        }

        // Save button click
        if (saveBtn) {
            saveBtn.addEventListener('click', () => this.saveProfileChanges());
        }

        // Form submission
        if (editForm) {
            editForm.addEventListener('submit', (e) => {
                e.preventDefault();
                this.saveProfileChanges();
            });
        }

        // ESC key to close
        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape' && this.isEditModalOpen()) {
                this.closeEditProfileModal();
            }
        });

        // Occupation dropdown change handler - show/hide "Other" field
        const occupationSelect = document.getElementById('editOccupation');
        if (occupationSelect) {
            occupationSelect.addEventListener('change', (e) => {
                this.toggleOccupationOtherField(e.target.value);
            });
        }

        // Address cascading dropdowns
        const provinceSelect = document.getElementById('editAddressProvince');
        const citySelect = document.getElementById('editAddressCity');
        const barangaySelect = document.getElementById('editAddressBarangay');

        if (provinceSelect) {
            provinceSelect.addEventListener('change', (e) => {
                this.loadCitiesByProvince(e.target.value);
            });
        }

        if (citySelect) {
            citySelect.addEventListener('change', (e) => {
                this.loadBarangaysByCity(e.target.value);
            });
        }

        // Input validation on blur
        const inputs = editForm?.querySelectorAll('input, select');
        if (inputs) {
            inputs.forEach(input => {
                input.addEventListener('blur', () => this.validateField(input));
                input.addEventListener('input', () => {
                    if (input.classList.contains('error')) {
                        this.validateField(input);
                    }
                });
            });
        }
    }

    toggleOccupationOtherField(occupationId) {
        const otherRow = document.getElementById('editOccupationOtherRow');
        const otherInput = document.getElementById('editOccupationOther');

        if (occupationId === '26') { // "Others" option
            if (otherRow) otherRow.style.display = 'flex';
            if (otherInput) otherInput.required = true;
        } else {
            if (otherRow) otherRow.style.display = 'none';
            if (otherInput) {
                otherInput.required = false;
                otherInput.value = '';
            }
        }
    }
    isEditModalOpen() {
        const modal = document.getElementById('editProfileModal');
        return modal && modal.classList.contains('active');
    }

    validateField(field) {
        const isValid = field.checkValidity();
        if (field.required && !isValid) {
            field.classList.add('error');
            return false;
        } else {
            field.classList.remove('error');
            return true;
        }
    }

    validateEditForm() {
        const editForm = document.getElementById('editProfileForm');
        if (!editForm) return false;

        const requiredFields = editForm.querySelectorAll('[required]');
        let isValid = true;

        requiredFields.forEach(field => {
            if (!this.validateField(field)) {
                isValid = false;
            }
        });

        return isValid;
    }

    openEditProfileModal() {
        const modal = document.getElementById('editProfileModal');
        if (!modal) return;

        // Populate form with current data
        this.populateEditForm();

        // Load provinces for address dropdown
        this.loadProvinces();

        // Show modal
        modal.classList.add('active');
        document.body.style.overflow = 'hidden';
    }

    async loadProvinces() {
        const provinceSelect = document.getElementById('editAddressProvince');
        if (!provinceSelect) return;

        try {
            const response = await fetch('/api/psgc/provinces', {
                method: 'GET',
                headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
            });
            const data = await response.json();

            // Reset and populate provinces
            provinceSelect.innerHTML = '<option value="">Select province</option>';
            (data.data || data || []).forEach(province => {
                const option = document.createElement('option');
                option.value = province.code;
                option.textContent = province.name;
                option.dataset.name = province.name;
                provinceSelect.appendChild(option);
            });

            // If we have saved province, try to select it
            if (this.profileData && this.profileData.addressProvince) {
                this.selectProvinceByName(this.profileData.addressProvince);
            }
        } catch (error) {
            console.error('Error loading provinces:', error);
        }
    }

    selectProvinceByName(provinceName) {
        const provinceSelect = document.getElementById('editAddressProvince');
        if (!provinceSelect || !provinceName) return;

        const options = provinceSelect.querySelectorAll('option');
        for (const option of options) {
            if (option.dataset.name && option.dataset.name.toLowerCase() === provinceName.toLowerCase()) {
                provinceSelect.value = option.value;
                // Trigger change to load cities
                this.loadCitiesByProvince(option.value);
                return;
            }
        }
    }

    async loadCitiesByProvince(provinceCode) {
        const citySelect = document.getElementById('editAddressCity');
        const barangaySelect = document.getElementById('editAddressBarangay');

        if (!citySelect) return;

        // Reset city and barangay
        citySelect.innerHTML = '<option value="">Select city</option>';
        citySelect.disabled = !provinceCode;

        if (barangaySelect) {
            barangaySelect.innerHTML = '<option value="">Select barangay</option>';
            barangaySelect.disabled = true;
        }

        if (!provinceCode) return;

        try {
            const response = await fetch(`/api/psgc/provinces/${provinceCode}/cities`, {
                method: 'GET',
                headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
            });
            const data = await response.json();

            (data.data || data || []).forEach(city => {
                const option = document.createElement('option');
                option.value = city.code;
                option.textContent = city.name;
                option.dataset.name = city.name;
                citySelect.appendChild(option);
            });

            citySelect.disabled = false;

            // If we have saved city, try to select it
            if (this.profileData && this.profileData.addressCity) {
                this.selectCityByName(this.profileData.addressCity);
            }
        } catch (error) {
            console.error('Error loading cities:', error);
        }
    }

    selectCityByName(cityName) {
        const citySelect = document.getElementById('editAddressCity');
        if (!citySelect || !cityName) return;

        const options = citySelect.querySelectorAll('option');
        for (const option of options) {
            if (option.dataset.name && option.dataset.name.toLowerCase() === cityName.toLowerCase()) {
                citySelect.value = option.value;
                // Trigger change to load barangays
                this.loadBarangaysByCity(option.value);
                return;
            }
        }
    }

    async loadBarangaysByCity(cityCode) {
        const barangaySelect = document.getElementById('editAddressBarangay');
        if (!barangaySelect) return;

        // Reset barangay
        barangaySelect.innerHTML = '<option value="">Select barangay</option>';
        barangaySelect.disabled = !cityCode;

        if (!cityCode) return;

        try {
            const response = await fetch(`/api/psgc/cities/${cityCode}/barangays`, {
                method: 'GET',
                headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
            });
            const data = await response.json();

            (data.data || data || []).forEach(barangay => {
                const option = document.createElement('option');
                option.value = barangay.code;
                option.textContent = barangay.name;
                option.dataset.name = barangay.name;
                barangaySelect.appendChild(option);
            });

            barangaySelect.disabled = false;

            // If we have saved barangay, try to select it
            if (this.profileData && this.profileData.addressBarangay) {
                this.selectBarangayByName(this.profileData.addressBarangay);
            }
        } catch (error) {
            console.error('Error loading barangays:', error);
        }
    }

    selectBarangayByName(barangayName) {
        const barangaySelect = document.getElementById('editAddressBarangay');
        if (!barangaySelect || !barangayName) return;

        const options = barangaySelect.querySelectorAll('option');
        for (const option of options) {
            if (option.dataset.name && option.dataset.name.toLowerCase() === barangayName.toLowerCase()) {
                barangaySelect.value = option.value;
                return;
            }
        }
    }

    closeEditProfileModal() {
        const modal = document.getElementById('editProfileModal');
        if (modal) {
            modal.classList.remove('active');
            document.body.style.overflow = '';
            // Reset form validation states
            const editForm = document.getElementById('editProfileForm');
            if (editForm) {
                const errorFields = editForm.querySelectorAll('.error');
                errorFields.forEach(field => field.classList.remove('error'));
            }
        }
    }

    populateEditForm() {
        if (!this.profileData) return;

        // Parse name into parts
        const nameParts = this.profileData.name.split(' ');
        let firstName = '';
        let middleName = '';
        let lastName = '';

        if (nameParts.length === 1) {
            firstName = nameParts[0];
        } else if (nameParts.length === 2) {
            firstName = nameParts[0];
            lastName = nameParts[1];
        } else if (nameParts.length >= 3) {
            firstName = nameParts[0];
            middleName = nameParts.slice(1, -1).join(' ');
            lastName = nameParts[nameParts.length - 1];
        }

        // Populate form fields
        const firstNameInput = document.getElementById('editFirstName');
        const middleNameInput = document.getElementById('editMiddleName');
        const lastNameInput = document.getElementById('editLastName');
        const usernameInput = document.getElementById('editUsername');
        const occupationSelect = document.getElementById('editOccupation');
        const occupationOtherInput = document.getElementById('editOccupationOther');
        const dobInput = document.getElementById('editDateOfBirth');

        if (firstNameInput) firstNameInput.value = firstName;
        if (middleNameInput) middleNameInput.value = middleName;
        if (lastNameInput) lastNameInput.value = lastName;
        if (usernameInput && this.profileData.rawUsername) usernameInput.value = this.profileData.rawUsername;

        // Set occupation dropdown and Other field
        if (occupationSelect && this.profileData.occupationId) {
            occupationSelect.value = this.profileData.occupationId;
            this.toggleOccupationOtherField(this.profileData.occupationId.toString());

            // If "Others" is selected, populate the other field
            if (this.profileData.occupationId == 26 && occupationOtherInput && this.profileData.occupationOther) {
                occupationOtherInput.value = this.profileData.occupationOther;
            }
        }

        if (dobInput && this.profileData.dateOfBirth) dobInput.value = this.profileData.dateOfBirth;

        // Populate street address (province/city/barangay are handled by cascading loads)
        const streetInput = document.getElementById('editAddressStreet');
        if (streetInput && this.profileData.addressStreet) {
            streetInput.value = this.profileData.addressStreet;
        }
    }

    async saveProfileChanges() {
        if (!this.validateEditForm()) {
            this.showNotification('Please fill in all required fields', 'error');
            return;
        }

        const saveBtn = document.getElementById('editProfileSaveBtn');
        const originalBtnHtml = saveBtn?.innerHTML;

        if (saveBtn) {
            saveBtn.disabled = true;
            saveBtn.innerHTML = '<i class="fi fi-rr-spinner animate-spin"></i> Saving...';
        }

        try {
            const formData = new FormData();

            // Get form values
            const firstName = document.getElementById('editFirstName')?.value;
            const middleName = document.getElementById('editMiddleName')?.value;
            const lastName = document.getElementById('editLastName')?.value;
            const username = document.getElementById('editUsername')?.value;
            const occupationId = document.getElementById('editOccupation')?.value;
            const occupationOther = document.getElementById('editOccupationOther')?.value;
            const dateOfBirth = document.getElementById('editDateOfBirth')?.value;

            // Address fields - get the selected text (name), not the code value
            const addressStreet = document.getElementById('editAddressStreet')?.value;
            const provinceSelect = document.getElementById('editAddressProvince');
            const citySelect = document.getElementById('editAddressCity');
            const barangaySelect = document.getElementById('editAddressBarangay');
            const addressProvince = provinceSelect?.selectedOptions[0]?.dataset?.name || '';
            const addressCity = citySelect?.selectedOptions[0]?.dataset?.name || '';
            const addressBarangay = barangaySelect?.selectedOptions[0]?.dataset?.name || '';

            if (firstName) formData.append('first_name', firstName);
            if (middleName) formData.append('middle_name', middleName);
            if (lastName) formData.append('last_name', lastName);
            if (username) formData.append('username', username);
            if (occupationId) formData.append('occupation_id', occupationId);
            // Only send occupation_other if "Others" is selected
            if (occupationId === '26' && occupationOther) {
                formData.append('occupation_other', occupationOther);
            }
            if (dateOfBirth) formData.append('date_of_birth', dateOfBirth);

            // Address fields
            if (addressStreet) formData.append('address_street', addressStreet);
            if (addressProvince) formData.append('address_province', addressProvince);
            if (addressCity) formData.append('address_city', addressCity);
            if (addressBarangay) formData.append('address_barangay', addressBarangay);

            const response = await fetch('/owner/profile/update', {
                method: 'POST',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || ''
                },
                credentials: 'same-origin',
                body: formData
            });

            const result = await response.json();

            if (result.success) {
                // Update the UI with new data
                this.updateProfileDisplay({
                    firstName,
                    middleName,
                    lastName,
                    occupationId,
                    occupationOther,
                    dateOfBirth
                });
                this.showNotification('Profile updated successfully!', 'success');
                this.closeEditProfileModal();
                // Reload profile data
                await this.loadProfileData();
            } else {
                this.showNotification(result.message || 'Failed to update profile', 'error');
            }
        } catch (error) {
            console.error('Error saving profile:', error);
            this.showNotification('An error occurred while saving', 'error');
        } finally {
            if (saveBtn) {
                saveBtn.disabled = false;
                saveBtn.innerHTML = originalBtnHtml || '<i class="fi fi-rr-check"></i> Save Changes';
            }
        }
    }

    updateProfileDisplay(formData) {
        // Update profile name
        const profileName = document.getElementById('profileName');
        const infoCardName = document.getElementById('infoCardName');

        let fullName = formData.firstName;
        if (formData.middleName) {
            fullName += ' ' + formData.middleName;
        }
        fullName += ' ' + formData.lastName;

        if (profileName) {
            profileName.textContent = fullName.trim();
        }
        if (infoCardName) {
            infoCardName.textContent = fullName.trim();
        }

        // Update occupation - will be refreshed from backend after loadProfileData()
        // For immediate feedback, map occupationId to name
        const occupationValue = document.getElementById('occupationValue');
        if (occupationValue && formData.occupationId) {
            const occupationMap = {
                '1': 'Teacher', '2': 'Engineer', '3': 'Doctor', '4': 'Nurse',
                '5': 'Police Officer', '6': 'Firefighter', '7': 'Lawyer', '8': 'Architect',
                '9': 'Driver', '10': 'Construction Worker', '11': 'Electrician', '12': 'Plumber',
                '13': 'Farmer', '14': 'Fisherman', '15': 'Office Clerk', '16': 'Salesperson',
                '17': 'Cashier', '18': 'Security Guard', '19': 'IT Specialist', '20': 'Call Center Agent',
                '21': 'Chef', '22': 'Accountant', '23': 'Businessman', '24': 'Student',
                '25': 'Unemployed', '26': 'Others'
            };
            let occupationText = occupationMap[formData.occupationId] || 'Not specified';
            if (formData.occupationId === '26' && formData.occupationOther) {
                occupationText = formData.occupationOther;
            }
            occupationValue.textContent = occupationText;
        }

        // Update initials if needed
        const initials = this.getInitials(fullName);
        const profilePictureInitials = document.querySelector('.profile-picture-initials');
        if (profilePictureInitials) {
            profilePictureInitials.textContent = initials;
        }
    }

    handleViewProjectDetails(projectId) {
        console.log('View project details:', projectId);
        // Navigate to dedicated project details page
        window.location.href = `/owner/projects/${projectId}`;
    }

    showNotification(message, type = 'info') {
        const toast = document.createElement('div');
        let bgColor = '#EEA24B';
        if (type === 'success') {
            bgColor = '#10b981';
        } else if (type === 'error') {
            bgColor = '#dc2626';
        }

        toast.className = 'fixed bottom-4 right-4 text-white px-6 py-3 rounded-lg shadow-lg z-50';
        toast.style.backgroundColor = bgColor;
        toast.textContent = message;
        toast.style.cssText += `
            animation: slideUp 0.3s ease-out;
        `;

        document.body.appendChild(toast);

        setTimeout(() => {
            toast.style.animation = 'slideDown 0.3s ease-out';
            setTimeout(() => {
                if (document.body.contains(toast)) {
                    document.body.removeChild(toast);
                }
            }, 300);
        }, 3000);
    }

    setupCardInteractions() {
        // Add click handlers to project post cards
        document.addEventListener('click', (e) => {
            const postCard = e.target.closest('.project-post-card');
            if (postCard && !e.target.closest('.post-card-more')) {
                const postId = postCard.getAttribute('data-post-id');
                if (postId) {
                    this.createRippleEffect(postCard, e);
                    setTimeout(() => {
                        this.handleViewProjectDetails(parseInt(postId));
                    }, 300);
                }
            }

            // Add ripple to review cards
            const reviewCard = e.target.closest('.review-card');
            if (reviewCard && !e.target.closest('.review-project-info')) {
                const reviewId = reviewCard.getAttribute('data-review-id');
                if (reviewId) {
                    this.createRippleEffect(reviewCard, e);
                }
            }
        });
    }

    createRippleEffect(element, event) {
        const ripple = document.createElement('span');
        const rect = element.getBoundingClientRect();
        const size = Math.max(rect.width, rect.height);
        const x = event.clientX - rect.left - size / 2;
        const y = event.clientY - rect.top - size / 2;

        ripple.style.cssText = `
            position: absolute;
            width: ${size}px;
            height: ${size}px;
            border-radius: 50%;
            background: rgba(238, 162, 75, 0.3);
            left: ${x}px;
            top: ${y}px;
            pointer-events: none;
            transform: scale(0);
            animation: ripple 0.6s ease-out;
            z-index: 1000;
        `;

        // Ensure parent has position relative
        const originalPosition = element.style.position;
        if (getComputedStyle(element).position === 'static') {
            element.style.position = 'relative';
        }
        element.style.overflow = 'hidden';

        element.appendChild(ripple);

        setTimeout(() => {
            ripple.remove();
            if (originalPosition) {
                element.style.position = originalPosition;
            }
        }, 600);
    }

    handleProfilePictureChange(event) {
        const file = event.target.files[0];
        if (!file) return;

        // Validate file type
        if (!file.type.startsWith('image/')) {
            this.showNotification('Please select a valid image file', 'error');
            return;
        }

        // Validate file size (max 5MB)
        if (file.size > 5 * 1024 * 1024) {
            this.showNotification('Image size should be less than 5MB', 'error');
            return;
        }

        // Create preview
        const reader = new FileReader();
        reader.onload = (e) => {
            const profilePicture = document.getElementById('profilePicture');
            if (profilePicture) {
                profilePicture.innerHTML = `<img src="${e.target.result}" alt="Profile" class="profile-picture-img" style="width:100%;height:100%;object-fit:cover;border-radius:50%;">`;
            }

            // Update profile data
            if (this.profileData) {
                this.profileData.profilePicture = e.target.result;
            }
        };
        reader.readAsDataURL(file);

        // Upload to server
        this.uploadProfilePicture(file);
    }

    async uploadProfilePicture(file) {
        const formData = new FormData();
        formData.append('profile_pic', file);

        try {
            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content || '';
            const response = await fetch('/owner/profile/update', {
                method: 'POST',
                body: formData,
                headers: {
                    'X-CSRF-TOKEN': csrfToken,
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                }
            });

            const data = await response.json();
            if (data.success) {
                this.showNotification('Profile picture updated successfully!', 'success');
            } else {
                this.showNotification(data.message || 'Failed to update profile picture', 'error');
            }
        } catch (error) {
            console.error('Error uploading profile picture:', error);
            this.showNotification('Error uploading profile picture', 'error');
        }
    }

    handleCoverPhotoChange(event) {
        const file = event.target.files[0];
        if (!file) return;

        // Validate file type
        if (!file.type.startsWith('image/')) {
            this.showNotification('Please select a valid image file', 'error');
            return;
        }

        // Validate file size (max 5MB)
        if (file.size > 5 * 1024 * 1024) {
            this.showNotification('Image size should be less than 5MB', 'error');
            return;
        }

        // Create preview
        const reader = new FileReader();
        reader.onload = (e) => {
            const coverPhotoImg = document.getElementById('coverPhotoImg');
            if (coverPhotoImg) {
                coverPhotoImg.src = e.target.result;
                coverPhotoImg.style.opacity = '0';
                coverPhotoImg.style.transition = 'opacity 0.3s ease';

                setTimeout(() => {
                    coverPhotoImg.style.opacity = '1';
                }, 10);
            }

            // Update profile data
            if (this.profileData) {
                this.profileData.coverImage = e.target.result;
            }

            this.showNotification('Cover photo updated successfully!', 'success');

            // Upload to server
            this.uploadCoverPhoto(file);
        };

        reader.onerror = () => {
            this.showNotification('Error reading the image file', 'error');
        };

        reader.readAsDataURL(file);
    }

    async uploadCoverPhoto(file) {
        const formData = new FormData();
        formData.append('cover_photo', file);

        try {
            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content || '';
            const response = await fetch('/owner/profile/update', {
                method: 'POST',
                body: formData,
                headers: {
                    'X-CSRF-TOKEN': csrfToken,
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                }
            });

            const data = await response.json();
            if (!data.success) {
                this.showNotification(data.message || 'Failed to upload cover photo', 'error');
            }
        } catch (error) {
            console.error('Error uploading cover photo:', error);
            this.showNotification('Error uploading cover photo', 'error');
        }
    }

    /**
     * Parse address string into parts
     * Format: "Street, Barangay, City, Province [PostalCode]"
     */
    parseAddress(addressString) {
        if (!addressString) return { street: '', barangay: '', city: '', province: '' };

        // Split by comma
        const parts = addressString.split(',').map(p => p.trim());

        // Typical format: "Street, Barangay, City, Province PostalCode"
        // We need at least 4 parts
        if (parts.length >= 4) {
            // Last part may have postal code, extract province name
            let provincePart = parts[parts.length - 1];
            // Remove postal code (digits at the end)
            provincePart = provincePart.replace(/\s*\d+\s*$/, '').trim();

            return {
                street: parts[0] || '',
                barangay: parts[1] || '',
                city: parts[2] || '',
                province: provincePart || ''
            };
        } else if (parts.length === 3) {
            // Only 3 parts: might be "Street, City, Province"
            let provincePart = parts[2].replace(/\s*\d+\s*$/, '').trim();
            return {
                street: parts[0] || '',
                barangay: '',
                city: parts[1] || '',
                province: provincePart || ''
            };
        } else if (parts.length === 2) {
            return {
                street: parts[0] || '',
                barangay: '',
                city: '',
                province: parts[1].replace(/\s*\d+\s*$/, '').trim() || ''
            };
        } else {
            return {
                street: addressString,
                barangay: '',
                city: '',
                province: ''
            };
        }
    }
}

// Initialize when DOM is ready
document.addEventListener('DOMContentLoaded', () => {
    new PropertyOwnerProfile();
});
