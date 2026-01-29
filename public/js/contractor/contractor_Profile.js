/**
 * Contractor Profile JavaScript
 * Handles profile page display and interactions
 */

class ContractorProfile {
    constructor() {
        this.profileData = null;
        this.currentTab = 'portfolio';
        this.portfolioItems = [];
        this.allHighlights = [];
        this.filteredHighlights = [];
        this.currentFilters = {
            status: 'all',
            sort: 'newest',
            search: ''
        };
        this.reviews = [];
        
        this.init();
    }

    init() {
        this.loadProfileData();
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

        // Send message button
        const sendMessageBtn = document.getElementById('sendMessageBtn');
        if (sendMessageBtn) {
            sendMessageBtn.addEventListener('click', (e) => {
                this.createRippleEffect(e.target, e);
                this.handleSendMessage();
            });
        }

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

        // Profile picture click handler
        const profilePicture = document.getElementById('profilePicture');
        if (profilePicture) {
            profilePicture.addEventListener('click', (e) => {
                this.handleProfilePictureClick();
            });
        }

        // Add ripple effects to interactive cards
        this.setupCardInteractions();

        // Projects filter event listeners
        this.setupHighlightsFilter();

        // Portfolio post input button
        const openPortfolioPostBtn = document.getElementById('openPortfolioPostModalBtn');
        if (openPortfolioPostBtn) {
            openPortfolioPostBtn.addEventListener('click', () => {
                this.handleCreatePortfolioPost();
            });
        }
    }

    loadProfileData() {
        // Read data from HTML first (if exists), otherwise use sample data
        const profileNameEl = document.getElementById('profileName');
        const profileRatingEl = document.getElementById('profileRating');
        const profileLocationEl = document.getElementById('profileLocation');
        const infoCardBioEl = document.getElementById('infoCardBio');
        const occupationValueEl = document.getElementById('occupationValue');
        const projectsDoneEl = document.getElementById('projectsDone');
        const ongoingProjectsEl = document.getElementById('ongoingProjects');
        const contactNumberEl = document.getElementById('contactNumber');
        const contactEmailEl = document.getElementById('contactEmail');
        const telephoneEl = document.getElementById('telephone');
        const profilePictureInitialsEl = document.querySelector('.profile-picture-initials');

        // Get values from HTML or use defaults
        const name = profileNameEl ? profileNameEl.textContent.trim() : "BuildRight Construction";
        const rating = profileRatingEl ? parseFloat(profileRatingEl.textContent.trim()) : 4.8;
        const location = profileLocationEl ? profileLocationEl.textContent.trim() : "Manila, Philippines";
        const bio = infoCardBioEl ? infoCardBioEl.textContent.trim() : "BuildRight Construction is a leading construction company specializing in residential and commercial projects. With over 15 years of experience, we deliver quality craftsmanship and exceptional service to our clients.";
        const occupation = occupationValueEl ? occupationValueEl.textContent.trim() : "General Contractor";
        const projectsDone = projectsDoneEl ? parseInt(projectsDoneEl.textContent.trim()) : 128;
        const ongoingProjects = ongoingProjectsEl ? parseInt(ongoingProjectsEl.textContent.trim()) : 5;
        const contactNumber = contactNumberEl ? contactNumberEl.textContent.trim() : "+63 912 345 6789";
        const email = contactEmailEl ? contactEmailEl.textContent.trim() : "info@buildrightconstruction.com";
        const telephone = telephoneEl ? telephoneEl.textContent.trim() : "02 1234 5678";
        const initials = profilePictureInitialsEl ? profilePictureInitialsEl.textContent.trim() : this.getInitials(name);

        // Sample profile data - Replace with actual API call
        this.profileData = {
            userId: 1,
            name: name,
            username: `@${name.toLowerCase().replace(/\s+/g, '')}`,
            rating: rating,
            location: location,
            bio: bio,
            occupation: occupation,
            projectsDone: projectsDone,
            ongoingProjects: ongoingProjects,
            contactNumber: contactNumber,
            email: email,
            telephone: telephone,
            profilePicture: null, // URL to profile picture
            coverImage: null // URL to cover image
        };

        this.portfolioItems = [
            {
                id: 1,
                userId: 1,
                userName: name,
                userHandle: `@${name.toLowerCase().replace(/\s+/g, '')}`,
                userInitials: initials,
                projectTitle: "Luxury Residential Complex Completion",
                projectImage: "https://images.unsplash.com/photo-1600596542815-ffad4c1539a9?w=800",
                timestamp: "2:30 PM · May 15, 2024",
                description: "Successfully completed a luxury residential complex featuring modern architecture and sustainable design..."
            },
            {
                id: 2,
                userId: 1,
                userName: name,
                userHandle: `@${name.toLowerCase().replace(/\s+/g, '')}`,
                userInitials: initials,
                projectTitle: "Commercial Office Building Project",
                projectImage: "https://images.unsplash.com/photo-1486406146926-c627a92ad1ab?w=800",
                timestamp: "10:45 AM · April 20, 2024",
                description: "Delivered a state-of-the-art commercial office building with smart technology integration..."
            }
        ];

        // Load all projects data
        this.loadAllHighlights();

        // Load reviews data
        this.loadReviews();

        this.updateCalculatedFields();
        this.renderPortfolio();
        this.updateCoverPhoto();
        
        // Ensure Portfolio tab is shown on initial load
        if (this.currentTab === 'portfolio') {
            this.showPortfolioTab();
        }
    }

    updateCoverPhoto() {
        const coverPhotoImg = document.getElementById('coverPhotoImg');
        if (coverPhotoImg && this.profileData && this.profileData.coverImage) {
            coverPhotoImg.src = this.profileData.coverImage;
        }
    }

    loadAllHighlights() {
        // Sample highlights data - Replace with actual API call
        this.allHighlights = [
            {
                id: 1,
                title: "Luxury Residential Complex Completion",
                type: "General Contractor",
                description: "Successfully completed a luxury residential complex featuring modern architecture and sustainable design.",
                image: "https://images.unsplash.com/photo-1600596542815-ffad4c1539a9?w=400&h=300&fit=crop",
                status: "completed",
                date: "2024-05-15",
                location: "Manila, Philippines"
            },
            {
                id: 2,
                title: "Commercial Office Building Project",
                type: "Construction",
                description: "Delivered a state-of-the-art commercial office building with smart technology integration.",
                image: "https://images.unsplash.com/photo-1486406146926-c627a92ad1ab?w=400&h=300&fit=crop",
                status: "completed",
                date: "2024-04-20",
                location: "Manila, Philippines"
            },
            {
                id: 3,
                title: "Modern Shopping Mall Construction",
                type: "General Contractor",
                description: "High-end shopping mall with modern amenities and sustainable design features.",
                image: "https://images.unsplash.com/photo-1600607687939-ce8a6c25118c?w=400&h=300&fit=crop",
                status: "in_progress",
                date: "2024-03-10",
                location: "Quezon City, Philippines"
            },
            {
                id: 4,
                title: "Residential Tower Development",
                type: "Construction",
                description: "Multi-unit residential tower with modern facilities and amenities.",
                image: "https://images.unsplash.com/photo-1600585154340-be6161a56a0c?w=400&h=300&fit=crop",
                status: "active",
                date: "2024-06-01",
                location: "Makati, Philippines"
            },
            {
                id: 5,
                title: "Hospital Building Expansion",
                type: "General Contractor",
                description: "Modern hospital expansion with advanced medical facilities.",
                image: "https://images.unsplash.com/photo-1497366216548-37526070297c?w=400&h=300&fit=crop",
                status: "in_progress",
                date: "2024-03-25",
                location: "Manila, Philippines"
            },
            {
                id: 6,
                title: "Mixed-Use Development Project",
                type: "Construction",
                description: "Large-scale mixed-use development combining residential and commercial spaces.",
                image: "https://images.unsplash.com/photo-1600566753190-17f0baa2a6c3?w=400&h=300&fit=crop",
                status: "active",
                date: "2024-02-15",
                location: "Taguig, Philippines"
            }
        ];

        this.filteredHighlights = [...this.allHighlights];
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
        const postInputInitials = document.querySelectorAll('.post-input-initials');
        postInputInitials.forEach(el => {
            if (el && currentName) {
            const initials = this.getInitials(currentName);
                el.textContent = initials;
        }
        });
    }

    getInitials(name) {
        return name
            .split(' ')
            .map(word => word[0])
            .join('')
            .toUpperCase()
            .substring(0, 3);
    }

    renderPortfolio() {
        const feed = document.getElementById('portfolioFeed');
        if (!feed) return;

        // Save the post input section if it exists
        const postInputSection = feed.querySelector('.post-project-input-section');
        let postInputHTML = postInputSection ? postInputSection.outerHTML : '';

        // If post input section doesn't exist, create it
        if (!postInputHTML) {
            const userName = this.profileData?.name || 'BuildRight Construction';
            const initials = this.getInitials(userName);
            postInputHTML = `
                <div class="post-project-input-section">
                    <div class="post-project-input-container">
                        <div class="post-input-avatar">
                            <div class="post-input-avatar-circle">
                                <span class="post-input-initials" id="portfolioPostInputInitials">${initials}</span>
                            </div>
                        </div>
                        <button type="button" class="post-project-input-field" id="openPortfolioPostModalBtn" aria-label="Create portfolio post">
                            <span class="post-input-placeholder">Share your portfolio work...</span>
                        </button>
                    </div>
                </div>
            `;
        }

        // Clear feed but preserve post input section
        feed.innerHTML = '';

        // Restore/create the post input section first
        if (postInputHTML) {
            feed.insertAdjacentHTML('afterbegin', postInputHTML);
            // Re-attach event listener
            const openBtn = feed.querySelector('#openPortfolioPostModalBtn');
            if (openBtn) {
                openBtn.addEventListener('click', () => {
                    this.handleCreatePortfolioPost();
                });
            }
            // Update initials if needed
            const initialsEl = feed.querySelector('#portfolioPostInputInitials');
            if (initialsEl && this.profileData) {
                const initials = this.getInitials(this.profileData.name);
                initialsEl.textContent = initials;
            }
        }

        if (!this.portfolioItems || this.portfolioItems.length === 0) {
            const emptyState = document.createElement('div');
            emptyState.className = 'project-post-card';
            emptyState.innerHTML = `
                    <div class="post-card-content" style="text-align: center; padding: 3rem;">
                    <p class="text-gray-500">No portfolio items yet</p>
                </div>
            `;
            feed.appendChild(emptyState);
            return;
        }

        this.portfolioItems.forEach(item => {
            const portfolioCard = this.createPortfolioCard(item);
            feed.appendChild(portfolioCard);
        });
    }

    createPortfolioCard(item) {
        const card = document.createElement('div');
        card.className = 'project-post-card';
        card.setAttribute('data-post-id', item.id);

        const initials = item.userInitials || this.getInitials(item.userName);

        card.innerHTML = `
            <div class="post-card-header">
                <div class="post-card-avatar">
                    ${item.userProfilePicture 
                        ? `<img src="${item.userProfilePicture}" alt="${item.userName}">`
                        : `<span class="post-card-avatar-initials">${initials}</span>`
                    }
                </div>
                <div class="post-card-user-info">
                    <p class="post-card-name">${item.userName}</p>
                    <p class="post-card-handle">${item.userHandle || ''}</p>
                </div>
            </div>
            ${item.projectImage ? `
                <img src="${item.projectImage}" alt="${item.projectTitle}" class="post-card-image">
            ` : ''}
            <div class="post-card-content">
                <h3 class="post-card-title">${item.projectTitle}</h3>
                ${item.description ? `<p style="color: #6b7280; font-size: 0.9375rem; margin-bottom: 0.5rem;">${item.description}</p>` : ''}
                <a href="#" class="post-card-more" data-post-id="${item.id}">More details...</a>
            </div>
            <div class="post-card-footer">
                <span class="post-card-timestamp">${item.timestamp}</span>
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
                    this.handleViewProjectDetails(item.id);
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
            case 'portfolio':
                this.showPortfolioTab();
                break;
            case 'highlights':
                this.showHighlightsTab();
                break;
            case 'reviews':
                this.showReviewsTab();
                break;
        }
    }

    showPortfolioTab() {
        const postFeed = document.getElementById('portfolioFeed');
        const projectsContainer = document.getElementById('highlightsListContainer');
        const reviewsContainer = document.getElementById('reviewsContainer');
        const filterSection = document.getElementById('highlightsFilterSection');

        // Check if already showing portfolio tab
        const isAlreadyVisible = postFeed && !postFeed.classList.contains('hidden');
        
        // Fade out current content
        [projectsContainer, reviewsContainer].forEach(container => {
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
            if (!isAlreadyVisible) {
            postFeed.style.opacity = '0';
            postFeed.style.transform = 'translateY(10px)';
            postFeed.style.transition = 'opacity 0.3s ease, transform 0.3s ease';
            
            setTimeout(() => {
                postFeed.style.opacity = '1';
                postFeed.style.transform = 'translateY(0)';
            }, 50);
            }
        }
        
        if (filterSection) filterSection.classList.add('hidden');

        // Always render portfolio to ensure post input section is present
        this.renderPortfolio();
    }

    showHighlightsTab() {
        const postFeed = document.getElementById('portfolioFeed');
        const projectsContainer = document.getElementById('highlightsListContainer');
        const reviewsContainer = document.getElementById('reviewsContainer');
        const filterSection = document.getElementById('highlightsFilterSection');

        // Fade out current content
        if (projectsContainer && !projectsContainer.classList.contains('hidden')) return;
        
        [postFeed, reviewsContainer].forEach(container => {
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

        this.renderHighlights();
        this.updatehighlightsFilterBadge();
    }

    showReviewsTab() {
        const postFeed = document.getElementById('portfolioFeed');
        const projectsContainer = document.getElementById('highlightsListContainer');
        const reviewsContainer = document.getElementById('reviewsContainer');
        const filterSection = document.getElementById('highlightsFilterSection');

        // Fade out current content
        if (reviewsContainer && !reviewsContainer.classList.contains('hidden')) return;
        
        [postFeed, projectsContainer].forEach(container => {
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

    loadReviews() {
        // Sample reviews data - Replace with actual API call
        this.reviews = [
            {
                id: 1,
                reviewerName: "Emmanuelle Santos",
                reviewerType: "Property Owner",
                rating: 5.0,
                reviewText: "Working with BuildRight Construction was an outstanding experience. They communicated clearly, responded promptly to milestone updates, and delivered exceptional quality work. Highly professional team!",
                timestamp: "1:12 PM · June 3, 2024",
                projectTitle: "Luxury Residential Complex",
                projectDate: "June 1, 2024",
                projectStatus: "Completed"
            },
            {
                id: 2,
                reviewerName: "Carl Wayne Saludo",
                reviewerType: "Property Owner",
                rating: 4.8,
                reviewText: "Excellent collaboration throughout the project. BuildRight Construction was very responsive and made timely decisions. The project was completed ahead of schedule with great attention to detail.",
                timestamp: "2:30 PM · May 15, 2024",
                projectTitle: "Commercial Office Building",
                projectDate: "May 10, 2024",
                projectStatus: "Completed"
            },
            {
                id: 3,
                reviewerName: "Maria Garcia",
                reviewerType: "Property Owner",
                rating: 5.0,
                reviewText: "One of the best contractors we've worked with. Clear communication, fair pricing, and prompt project completion. Highly recommend BuildRight Construction for any construction project.",
                timestamp: "10:45 AM · April 20, 2024",
                projectTitle: "Modern Shopping Mall",
                projectDate: "April 15, 2024",
                projectStatus: "Completed"
            },
            {
                id: 4,
                reviewerName: "John Rodriguez",
                reviewerType: "Property Owner",
                rating: 4.5,
                reviewText: "Professional and organized contractor. The project management through Legatura made everything smooth and transparent. Would definitely work with BuildRight Construction again.",
                timestamp: "3:20 PM · March 28, 2024",
                projectTitle: "Residential Tower Development",
                projectDate: "March 25, 2024",
                projectStatus: "Completed"
            }
        ];
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

    applyHighlightsFilters() {
        let filtered = [...this.allHighlights];

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

        this.filteredHighlights = filtered;
        this.renderHighlights();
        this.updatehighlightsFilterBadge();
    }

    setupHighlightsFilter() {
        const filterIconBtn = document.getElementById('highlightsFilterIconBtn');
        const filterDropdown = document.getElementById('highlightsFilterDropdown');
        const filterCloseBtn = document.getElementById('highlightsFilterCloseBtn');
        const filterApplyBtn = document.getElementById('applyHighlightsFiltersBtn');
        const filterClearBtn = document.getElementById('clearHighlightsFiltersBtn');
        const filterBadge = document.getElementById('highlightsFilterBadge');
        const statusFilter = document.getElementById('highlightsStatusFilter');
        const sortFilter = document.getElementById('highlightsSortFilter');

        // Toggle dropdown on filter icon click
        if (filterIconBtn && filterDropdown) {
            filterIconBtn.addEventListener('click', (e) => {
                e.stopPropagation();
                this.togglehighlightsFilterDropdown();
            });
        }

        // Close dropdown on close button click
        if (filterCloseBtn) {
            filterCloseBtn.addEventListener('click', () => {
                this.closehighlightsFilterDropdown();
            });
        }

        // Apply filters
        if (filterApplyBtn) {
            filterApplyBtn.addEventListener('click', () => {
                this.applyHighlightsFiltersFromUI();
            });
        }

        // Clear filters
        if (filterClearBtn) {
            filterClearBtn.addEventListener('click', () => {
                this.clearHighlightsFilters();
            });
        }

        // Close dropdown when clicking outside
        document.addEventListener('click', (e) => {
            if (filterDropdown && 
                !filterDropdown.contains(e.target) && 
                filterIconBtn && 
                !filterIconBtn.contains(e.target)) {
                this.closehighlightsFilterDropdown();
            }
        });
    }

    togglehighlightsFilterDropdown() {
        const filterDropdown = document.getElementById('highlightsFilterDropdown');
        const filterIconBtn = document.getElementById('highlightsFilterIconBtn');
        
        if (filterDropdown && filterIconBtn) {
            filterDropdown.classList.toggle('active');
            filterIconBtn.classList.toggle('active');
        }
    }

    closehighlightsFilterDropdown() {
        const filterDropdown = document.getElementById('highlightsFilterDropdown');
        const filterIconBtn = document.getElementById('highlightsFilterIconBtn');
        
        if (filterDropdown) filterDropdown.classList.remove('active');
        if (filterIconBtn) filterIconBtn.classList.remove('active');
    }

    applyHighlightsFiltersFromUI() {
        const statusFilter = document.getElementById('highlightsStatusFilter');
        const sortFilter = document.getElementById('highlightsSortFilter');
        
        if (statusFilter) {
            this.currentFilters.status = statusFilter.value;
        }
        if (sortFilter) {
            this.currentFilters.sort = sortFilter.value;
        }
        
        this.applyHighlightsFilters();
        this.updatehighlightsFilterBadge();
        this.closehighlightsFilterDropdown();
    }

    updatehighlightsFilterBadge() {
        const filterBadge = document.getElementById('highlightsFilterBadge');
        const filterIconBtn = document.getElementById('highlightsFilterIconBtn');
        
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

    clearHighlightsFilters() {
        this.currentFilters = {
            status: 'all',
            sort: 'newest',
            search: ''
        };

        // Reset filter inputs
        const statusFilter = document.getElementById('highlightsStatusFilter');
        const sortFilter = document.getElementById('highlightsSortFilter');

        if (statusFilter) statusFilter.value = 'all';
        if (sortFilter) sortFilter.value = 'newest';

        this.applyHighlightsFilters();
        this.updatehighlightsFilterBadge();
        this.closehighlightsFilterDropdown();
    }

    renderHighlights() {
        const grid = document.getElementById('highlightsGrid');
        if (!grid) return;

        grid.innerHTML = '';

        if (!this.filteredHighlights || this.filteredHighlights.length === 0) {
            grid.innerHTML = `
                <div class="projects-empty-state">
                    <i class="fi fi-rr-folder-open text-6xl text-gray-300 mb-4"></i>
                    <h3 class="text-xl font-semibold text-gray-700 mb-2">No Highlights Found</h3>
                    <p class="text-gray-500">Try adjusting your filters</p>
                </div>
            `;
            return;
        }

        this.filteredHighlights.forEach((project, index) => {
            setTimeout(() => {
                const projectCard = this.createHighlightCard(project);
                grid.appendChild(projectCard);
            }, index * 50);
        });
    }

    createHighlightCard(project) {
        const card = document.createElement('div');
        card.className = 'project-post-card';
        card.setAttribute('data-project-id', project.id);

        // Get user info from profile data or use defaults
        const userName = this.profileData?.name || 'BuildRight Construction';
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
        console.log('Send message clicked');
        // In a real implementation, open a message modal or navigate to messages
        this.showNotification('Message feature coming soon!', 'info');
    }

    handleCreatePortfolioPost() {
        console.log('Create portfolio post clicked');
        // Open portfolio post modal
        if (window.openPortfolioPostModal) {
            window.openPortfolioPostModal();
        } else {
            this.showNotification('Portfolio post modal is loading...', 'info');
        }
    }

    handleViewProjectDetails(projectId) {
        console.log('View project details:', projectId);
        // In a real implementation, open project details modal or navigate to project page
        // For now, navigate to project details or show modal
        const project = this.allHighlights.find(p => p.id === projectId) || 
                       this.portfolioItems.find(p => p.id === projectId);
        
        if (project) {
            // In a real implementation, open project details modal
            // For now, show notification
            this.showNotification(`Viewing ${project.title || project.projectTitle}`, 'info');
        } else {
            this.showNotification(`Viewing project ${projectId}`, 'info');
        }
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

    handleProfilePictureClick() {
        // In a real implementation, open profile picture upload modal
        this.showNotification('Profile picture upload coming soon!', 'info');
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
            
            // In a real implementation, upload the file to the server
            // this.uploadCoverPhoto(file);
        };

        reader.onerror = () => {
            this.showNotification('Error reading the image file', 'error');
        };

        reader.readAsDataURL(file);
    }

    async uploadCoverPhoto(file) {
        // In a real implementation, upload to server
        const formData = new FormData();
        formData.append('cover_photo', file);
        
        try {
            // const response = await fetch('/api/profile/cover-photo', {
            //     method: 'POST',
            //     body: formData,
            //     headers: {
            //         'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            //     }
            // });
            // const data = await response.json();
            // if (data.success) {
            //     this.showNotification('Cover photo uploaded successfully!', 'success');
            // }
        } catch (error) {
            console.error('Error uploading cover photo:', error);
            this.showNotification('Error uploading cover photo', 'error');
        }
    }
}

// Initialize when DOM is ready
document.addEventListener('DOMContentLoaded', () => {
    new ContractorProfile();
});
