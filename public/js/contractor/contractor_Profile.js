/**
 * Contractor Profile JavaScript
 * Fetches all profile data from the backend via profileController
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

        // Read config from DOM data attributes
        const root = document.getElementById('contractorProfileRoot');
        this.userId = root ? root.dataset.userId : null;
        this.role = root ? root.dataset.role : 'contractor';
        this.fetchUrl = root ? root.dataset.fetchUrl : '/contractor/profile/fetch';
        this.reviewsUrl = root ? root.dataset.reviewsUrl : '/contractor/profile/reviews';
        this.updateUrl = root ? root.dataset.updateUrl : '/contractor/profile/update';
        this.storageBase = root ? root.dataset.storageBase : '/storage';
        this.csrfToken = document.querySelector('meta[name="csrf-token"]')?.content || '';

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

        // Profile picture edit button
        const editProfilePicBtn = document.getElementById('editProfilePictureBtn');
        if (editProfilePicBtn) {
            editProfilePicBtn.addEventListener('click', (e) => {
                e.stopPropagation();
                this.handleProfilePictureClick();
            });
        }

        // Add ripple effects to interactive cards
        this.setupCardInteractions();

        // Portfolio post input button
        const openPortfolioPostBtn = document.getElementById('openPortfolioPostModalBtn');
        if (openPortfolioPostBtn) {
            openPortfolioPostBtn.addEventListener('click', () => {
                this.handleCreatePortfolioPost();
            });
        }
    }

    /**
     * Resolve a storage path to a full URL.
     * Handles paths like "profiles/xxx.jpg", "/storage/profiles/xxx.jpg", full URLs, etc.
     */
    resolveStorageUrl(path) {
        if (!path) return null;
        if (path.startsWith('http://') || path.startsWith('https://')) return path;
        // Strip leading /storage/ if present
        let clean = path.replace(/^\/?storage\//, '');
        return `${this.storageBase}/${clean}`;
    }

    /**
     * Fetch profile data from the backend (profileController::apiGetProfile)
     */
    async loadProfileData() {
        try {
            const params = new URLSearchParams({ role: this.role });
            if (this.userId) params.set('user_id', this.userId);

            const response = await fetch(`${this.fetchUrl}?${params.toString()}`, {
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                }
            });

            if (!response.ok) throw new Error(`HTTP ${response.status}`);
            const json = await response.json();

            if (!json.success) throw new Error(json.message || 'Failed to fetch profile');

            const data = json.data;
            const user = data.user || {};
            const contractor = data.contractor || {};
            const representative = data.representative || null;
            const representatives = data.representatives || [];

            // Build resolved profile data
            this.profileData = {
                userId: user.user_id,
                name: contractor.company_name || user.username || 'Contractor',
                username: user.username ? `@${user.username}` : '',
                rating: data.rating ?? null,
                totalReviews: data.total_reviews ?? 0,
                location: contractor.business_address || '',
                bio: contractor.bio || contractor.company_description || '',
                occupation: data.occupation_name || contractor.contractor_type_other || '',
                projectsDone: data.projects_done ?? 0,
                ongoingProjects: data.ongoing_projects ?? 0,
                contactNumber: contractor.company_phone || '',
                email: contractor.company_email || user.email || '',
                telephone: '', // not in schema, leave blank
                profilePicture: this.resolveStorageUrl(contractor.company_logo || user.profile_pic),
                coverImage: this.resolveStorageUrl(contractor.company_banner || user.cover_photo),
                companyDescription: contractor.company_description || '',
                companyWebsite: contractor.company_website || '',
                companySocialMedia: contractor.company_social_media || '',
                companyStartDate: contractor.company_start_date || '',
                yearsOfExperience: contractor.company_start_date
                    ? Math.max(0, new Date().getFullYear() - new Date(contractor.company_start_date).getFullYear())
                    : (contractor.years_of_experience || 0),
                servicesOffered: contractor.services_offered || '',
                verificationStatus: contractor.verification_status || '',
                picabNumber: contractor.picab_number || '',
                picabCategory: contractor.picab_category || '',
                tinNumber: contractor.tin_business_reg_number || '',
                businessPermitNumber: contractor.business_permit_number || '',
                businessPermitCity: contractor.business_permit_city || '',
                businessPermitExpiration: contractor.business_permit_expiration || '',
                representative: representative,
                typeId: contractor.type_id || null
            };

            // Build highlights from projects
            const projects = data.projects || [];
            this.allHighlights = projects.map(p => {
                let image = null;
                if (p.files && Array.isArray(p.files) && p.files.length > 0) {
                    image = this.resolveStorageUrl(p.files[0]);
                }
                return {
                    id: p.project_id,
                    title: p.project_title || 'Untitled Project',
                    type: p.type_name || p.property_type || '',
                    description: p.project_description || '',
                    image: image,
                    status: (p.project_status || 'unknown').toLowerCase(),
                    date: p.post_created_at || p.created_at || '',
                    location: p.project_location || ''
                };
            });
            this.filteredHighlights = [...this.allHighlights];

            // Portfolio items from completed projects (top 10)
            this.portfolioItems = this.allHighlights
                .filter(h => h.status === 'completed')
                .slice(0, 10)
                .map(h => ({
                    id: h.id,
                    userId: this.profileData.userId,
                    userName: this.profileData.name,
                    userHandle: this.profileData.username,
                    userInitials: this.getInitials(this.profileData.name),
                    userProfilePicture: this.profileData.profilePicture,
                    projectTitle: h.title,
                    projectImage: h.image,
                    timestamp: h.date ? this.formatDate(h.date) : '',
                    description: h.description
                }));

            // Update DOM with fetched data
            this.populateDOM();
            this.renderPortfolio();
            this.updateCoverPhoto();

            // Load reviews separately
            this.loadReviews();

            if (this.currentTab === 'portfolio') {
                this.showPortfolioTab();
            }

        } catch (error) {
            console.error('Failed to load profile data:', error);
            this.showNotification('Failed to load profile data. Please refresh the page.', 'error');
        }
    }

    /**
     * Populate all DOM elements with the fetched profile data
     */
    populateDOM() {
        const d = this.profileData;
        if (!d) return;

        const initials = this.getInitials(d.name);

        // Header section
        this.setText('profileName', d.name);
        this.setText('profileRating', d.rating !== null ? d.rating : '—');
        this.setText('profileLocation', d.location || '—');

        // Profile picture
        const profilePicImg = document.getElementById('profilePictureImg');
        const profilePicInitials = document.querySelector('.profile-picture-initials');
        if (d.profilePicture && profilePicImg) {
            profilePicImg.src = d.profilePicture;
            profilePicImg.style.display = 'block';
            if (profilePicInitials) profilePicInitials.style.display = 'none';
        } else if (profilePicInitials) {
            profilePicInitials.textContent = initials;
            profilePicInitials.style.display = 'flex';
            if (profilePicImg) profilePicImg.style.display = 'none';
        }

        // Info card (left column)
        this.setText('infoCardName', d.name);
        this.setText('infoCardRating', d.rating !== null ? d.rating : '—');
        this.setText('infoCardLocation', d.location || '—');
        this.setText('infoCardBio', d.bio || '');
        this.setText('occupationValue', d.occupation || '—');
        this.setText('projectsDone', d.projectsDone);
        this.setText('ongoingProjects', d.ongoingProjects);
        this.setText('contactNumber', d.contactNumber || '—');
        this.setText('contactEmail', d.email || '—');
        this.setText('telephone', d.telephone || '—');

        // Contact person card
        const rep = d.representative;
        const contactCard = document.getElementById('contactPersonCard');
        if (rep) {
            this.setText('contactPersonName', rep.full_name || '—');
            this.setText('contactPersonRole', rep.role || 'Representative');

            const repAvatar = document.getElementById('contactPersonAvatar');
            const repInitials = document.getElementById('contactPersonInitials');
            if (rep.profile_pic && repAvatar) {
                repAvatar.src = this.resolveStorageUrl(rep.profile_pic);
                repAvatar.style.display = 'block';
                if (repInitials) repInitials.style.display = 'none';
            } else if (repInitials) {
                repInitials.textContent = this.getInitials(rep.full_name || 'R');
                repInitials.style.display = 'flex';
            }
            if (contactCard) contactCard.style.display = '';
        } else {
            // Hide contact card if no representative
            if (contactCard) contactCard.style.display = 'none';
        }

        // Contact person stats (use same contractor stats)
        this.setText('contactPersonExperience', d.yearsOfExperience ? `${d.yearsOfExperience} Years` : '—');
        this.setText('contactPersonProjectsDone', d.projectsDone);
        this.setText('contactPersonOngoingProjects', d.ongoingProjects);
        this.setText('contactPersonPhone', (rep && rep.phone_number) ? rep.phone_number : d.contactNumber || '—');
        this.setText('contactPersonEmail', (rep && rep.email) ? rep.email : d.email || '—');
        this.setText('contactPersonTelephone', d.telephone || '—');

        // Services offered
        const servicesList = document.getElementById('servicesOfferedList');
        if (servicesList && d.servicesOffered) {
            const services = d.servicesOffered.split(',').map(s => s.trim()).filter(Boolean);
            if (services.length > 0) {
                servicesList.innerHTML = services.map(s => `<li>${this.escapeHtml(s)}</li>`).join('');
            } else {
                servicesList.innerHTML = '<li>—</li>';
            }
        }

        // Specialization tags
        const specTags = document.getElementById('specializationTags');
        if (specTags && d.occupation) {
            specTags.innerHTML = `<span class="specialization-tag">${this.escapeHtml(d.occupation)}</span>`;
        }

        // Update initials in post input
        const postInitials = document.querySelectorAll('.post-input-initials');
        postInitials.forEach(el => { el.textContent = initials; });
    }

    /**
     * Fetch reviews from the backend (profileController::apiGetReviews)
     */
    async loadReviews() {
        try {
            const params = new URLSearchParams({ role: this.role });
            if (this.userId) params.set('user_id', this.userId);

            const response = await fetch(`${this.reviewsUrl}?${params.toString()}`, {
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                }
            });

            if (!response.ok) throw new Error(`HTTP ${response.status}`);
            const json = await response.json();

            if (!json.success) throw new Error(json.message || 'Failed to fetch reviews');

            const reviewsData = json.data?.reviews || json.data || [];
            const stats = json.data?.stats || {};

            this.reviews = reviewsData.map(r => ({
                id: r.review_id,
                reviewerName: r.reviewer_company_name || r.reviewer_display_name || r.reviewer_name || r.reviewer_username || 'Anonymous',
                reviewerType: 'Property Owner',
                reviewerProfilePic: r.reviewer_profile_pic ? this.resolveStorageUrl(r.reviewer_profile_pic) : null,
                rating: parseFloat(r.rating) || 0,
                reviewText: r.comment || '',
                timestamp: r.created_at ? this.formatDate(r.created_at) : '',
                projectTitle: r.project_id ? `Project #${r.project_id}` : '',
                projectDate: r.created_at ? this.formatDate(r.created_at) : '',
                projectStatus: 'Completed'
            }));

            // Update rating display if stats available
            if (stats.avg_rating !== null && stats.avg_rating !== undefined) {
                this.setText('profileRating', stats.avg_rating);
                this.setText('infoCardRating', stats.avg_rating);
            }

            // Re-render reviews tab if currently visible
            if (this.currentTab === 'reviews') {
                this.renderReviews();
            }

        } catch (error) {
            console.error('Failed to load reviews:', error);
            this.reviews = [];
        }
    }

    /** Helper to set text content by element ID */
    setText(id, value) {
        const el = document.getElementById(id);
        if (el) el.textContent = value ?? '';
    }

    /** Escape HTML to prevent XSS */
    escapeHtml(str) {
        const div = document.createElement('div');
        div.textContent = str;
        return div.innerHTML;
    }

    updateCoverPhoto() {
        const coverPhotoImg = document.getElementById('coverPhotoImg');
        if (coverPhotoImg && this.profileData && this.profileData.coverImage) {
            coverPhotoImg.src = this.profileData.coverImage;
        }
    }

    getInitials(name) {
        if (!name) return '';
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

        // Wire up the action bar "Add Project" button (once)
        const addBtn = document.getElementById('openPortfolioPostModalBtn');
        if (addBtn && !addBtn.dataset.listenerAttached) {
            addBtn.addEventListener('click', () => {
                this.handleCreatePortfolioPost();
            });
            addBtn.dataset.listenerAttached = 'true';
        }

        // Clear the portfolio grid
        feed.innerHTML = '';

        if (!this.portfolioItems || this.portfolioItems.length === 0) {
            feed.innerHTML = `
                <div class="portfolio-empty-state">
                    <i class="fi fi-rr-picture"></i>
                    <h3>No Portfolio Items Yet</h3>
                    <p>Add your completed projects to showcase your work</p>
                </div>
            `;
            return;
        }

        this.portfolioItems.forEach(item => {
            const portfolioCard = this.createPortfolioCard(item);
            feed.appendChild(portfolioCard);
        });
    }

    createPortfolioCard(item) {
        const card = document.createElement('div');
        card.className = 'portfolio-grid-card';
        if (item.isHighlighted) card.classList.add('is-highlighted');
        card.setAttribute('data-portfolio-id', item.id);

        card.innerHTML = `
            <div class="portfolio-card-image-wrap">
                ${item.projectImage
                    ? `<img src="${item.projectImage}" alt="${item.projectTitle}" loading="lazy">`
                    : `<div style="width:100%;height:100%;display:flex;align-items:center;justify-content:center;background:#f3f4f6;"><i class="fi fi-rr-picture" style="font-size:2rem;color:#c4c4c4;"></i></div>`
                }
                ${item.isHighlighted ? '<span class="portfolio-card-highlighted-badge">Highlighted</span>' : ''}
            </div>
            <div class="portfolio-card-body">
                <h3 class="portfolio-card-title">${item.projectTitle}</h3>
                ${item.description ? `<p class="portfolio-card-description">${item.description}</p>` : ''}
                <div class="portfolio-card-meta">
                    ${item.location ? `<span class="portfolio-card-meta-item"><i class="fi fi-rr-marker"></i>${item.location}</span>` : ''}
                    ${item.timestamp ? `<span class="portfolio-card-meta-item"><i class="fi fi-rr-calendar"></i>${item.timestamp}</span>` : ''}
                </div>
            </div>
        `;

        card.addEventListener('click', () => {
            this.handleViewProjectDetails(item.id);
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
            case 'about':
                this.showAboutTab();
                break;
        }
    }

    showPortfolioTab() {
        const postFeed = document.getElementById('portfolioSection');
        const highlightsSection = document.getElementById('highlightsSection');
        const reviewsContainer = document.getElementById('reviewsContainer');
        const aboutSection = document.getElementById('aboutSection');

        // Fade out current content
        [highlightsSection, reviewsContainer, aboutSection].forEach(container => {
            if (container && !container.classList.contains('hidden')) {
                container.style.opacity = '0';
                container.style.transform = 'translateY(10px)';
                setTimeout(() => { container.classList.add('hidden'); }, 200);
            }
        });

        // Fade in portfolio
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

        this.renderPortfolio();
    }

    showHighlightsTab() {
        const postFeed = document.getElementById('portfolioSection');
        const highlightsSection = document.getElementById('highlightsSection');
        const reviewsContainer = document.getElementById('reviewsContainer');
        const aboutSection = document.getElementById('aboutSection');

        if (highlightsSection && !highlightsSection.classList.contains('hidden')) return;

        [postFeed, reviewsContainer, aboutSection].forEach(container => {
            if (container && !container.classList.contains('hidden')) {
                container.style.opacity = '0';
                container.style.transform = 'translateY(10px)';
                setTimeout(() => { container.classList.add('hidden'); }, 200);
            }
        });

        if (highlightsSection) {
            highlightsSection.classList.remove('hidden');
            highlightsSection.style.opacity = '0';
            highlightsSection.style.transform = 'translateY(10px)';
            highlightsSection.style.transition = 'opacity 0.3s ease, transform 0.3s ease';
            setTimeout(() => {
                highlightsSection.style.opacity = '1';
                highlightsSection.style.transform = 'translateY(0)';
            }, 50);
        }

        this.populateHighlightsTab();
    }

    showReviewsTab() {
        const postFeed = document.getElementById('portfolioSection');
        const highlightsSection = document.getElementById('highlightsSection');
        const reviewsContainer = document.getElementById('reviewsContainer');
        const aboutSection = document.getElementById('aboutSection');

        if (reviewsContainer && !reviewsContainer.classList.contains('hidden')) return;

        [postFeed, highlightsSection, aboutSection].forEach(container => {
            if (container && !container.classList.contains('hidden')) {
                container.style.opacity = '0';
                container.style.transform = 'translateY(10px)';
                setTimeout(() => { container.classList.add('hidden'); }, 200);
            }
        });

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

        this.renderReviews();
    }

    showAboutTab() {
        const postFeed = document.getElementById('portfolioSection');
        const highlightsSection = document.getElementById('highlightsSection');
        const reviewsContainer = document.getElementById('reviewsContainer');
        const aboutSection = document.getElementById('aboutSection');

        if (aboutSection && !aboutSection.classList.contains('hidden')) return;

        [postFeed, highlightsSection, reviewsContainer].forEach(container => {
            if (container && !container.classList.contains('hidden')) {
                container.style.opacity = '0';
                container.style.transform = 'translateY(10px)';
                setTimeout(() => { container.classList.add('hidden'); }, 200);
            }
        });

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

        this.populateAboutTab();
    }

    /**
     * Populate the Highlights tab stat cards from profileData (matching mobile viewProfile.tsx)
     */
    populateHighlightsTab() {
        if (!this.profileData) return;

        const d = this.profileData;
        this.setText('hlYearsExperience', d.yearsOfExperience || 0);
        this.setText('hlProjectsCompleted', d.projectsDone || 0);
        this.setText('hlAvgRating', d.rating !== null && d.rating !== undefined ? d.rating : '0');
        this.setText('hlClientReviews', this.reviews ? this.reviews.length : 0);

        // Services offered
        const servicesCard = document.getElementById('hlServicesCard');
        const servicesText = document.getElementById('hlServicesText');
        if (d.servicesOffered && servicesCard && servicesText) {
            servicesText.textContent = d.servicesOffered;
            servicesCard.style.display = '';
        }

        // Verification status
        const verifCard = document.getElementById('hlVerificationCard');
        const verifValue = document.getElementById('hlVerificationValue');
        const verifIcon = document.getElementById('hlVerificationIcon');
        if (d.verificationStatus && verifCard && verifValue) {
            const statusMap = {
                'verified': { label: 'Verified', cls: 'highlights-verification--verified' },
                'pending': { label: 'Pending Verification', cls: 'highlights-verification--pending' },
                'rejected': { label: 'Rejected', cls: 'highlights-verification--rejected' },
                'unverified': { label: 'Not Verified', cls: '' }
            };
            const info = statusMap[d.verificationStatus.toLowerCase()] || { label: d.verificationStatus, cls: '' };
            verifValue.textContent = info.label;
            if (verifIcon && info.cls) verifIcon.classList.add(info.cls);
            verifCard.style.display = '';
        }
    }

    /**
     * Populate the About tab from profileData
     */
    populateAboutTab() {
        if (!this.profileData) return;
        const d = this.profileData;

        this.setText('aboutBioText', d.bio || 'No bio added yet.');
        this.setText('aboutSpecialization', d.occupation || '—');
        this.setText('aboutExperience', d.yearsOfExperience ? `${d.yearsOfExperience} Years` : '—');
        this.setText('aboutLocation', d.location || '—');
        this.setText('aboutProjectsDone', d.projectsDone || 0);
        this.setText('aboutPhone', d.contactNumber || '—');
        this.setText('aboutEmail', d.email || '—');
        this.setText('aboutTelephone', d.telephone || '—');

        // Website & social media
        const websiteEl = document.getElementById('aboutWebsite');
        if (websiteEl && d.companyWebsite) {
            websiteEl.innerHTML = `<a href="${this.escapeHtml(d.companyWebsite)}" target="_blank" rel="noopener" style="color:#EC7E00;text-decoration:none;">${this.escapeHtml(d.companyWebsite)}</a>`;
        } else if (websiteEl) {
            websiteEl.textContent = '—';
        }

        const socialEl = document.getElementById('aboutSocialMedia');
        if (socialEl && d.companySocialMedia) {
            socialEl.innerHTML = `<a href="${this.escapeHtml(d.companySocialMedia)}" target="_blank" rel="noopener" style="color:#EC7E00;text-decoration:none;">${this.escapeHtml(d.companySocialMedia)}</a>`;
        } else if (socialEl) {
            socialEl.textContent = '—';
        }

        // Company start date & description
        if (d.companyStartDate) {
            const dt = new Date(d.companyStartDate);
            this.setText('aboutCompanyStartDate', dt.toLocaleDateString('en-US', { year: 'numeric', month: 'long', day: 'numeric' }));
        } else {
            this.setText('aboutCompanyStartDate', '—');
        }
        this.setText('aboutCompanyDescription', d.companyDescription || '—');

        // Skills tags
        const skillsGrid = document.getElementById('aboutSkillsGrid');
        if (skillsGrid) {
            if (d.occupation) {
                skillsGrid.innerHTML = `<span class="about-skill-tag">${this.escapeHtml(d.occupation)}</span>`;
            } else {
                skillsGrid.innerHTML = '<span style="color:#94A3B8;">No specializations listed</span>';
            }
        }

        // Certifications
        const certsList = document.getElementById('aboutCertificationsList');
        if (certsList) {
            let certsHTML = '';
            if (d.picabNumber) {
                certsHTML += `<div class="about-cert-item"><i class="fi fi-rr-badge-check"></i><span>PCAB License: ${this.escapeHtml(d.picabNumber)}${d.picabCategory ? ' — ' + this.escapeHtml(d.picabCategory) : ''}</span></div>`;
            }
            if (d.tinNumber) {
                certsHTML += `<div class="about-cert-item"><i class="fi fi-rr-badge-check"></i><span>TIN / Business Reg: ${this.escapeHtml(d.tinNumber)}</span></div>`;
            }
            certsList.innerHTML = certsHTML || '<span style="color:#94A3B8;">No certifications listed</span>';
        }
    }

    // Reviews are now loaded via loadReviews() in the async flow above

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
        // Create a hidden file input and trigger it
        const input = document.createElement('input');
        input.type = 'file';
        input.accept = 'image/*';
        input.addEventListener('change', (e) => this.handleProfilePictureChange(e));
        input.click();
    }

    async handleProfilePictureChange(event) {
        const file = event.target.files[0];
        if (!file) return;

        if (!file.type.startsWith('image/')) {
            this.showNotification('Please select a valid image file', 'error');
            return;
        }
        if (file.size > 5 * 1024 * 1024) {
            this.showNotification('Image size should be less than 5MB', 'error');
            return;
        }

        // Preview immediately
        const reader = new FileReader();
        reader.onload = (e) => {
            const img = document.getElementById('profilePictureImg');
            const initials = document.querySelector('.profile-picture-initials');
            if (img) {
                img.src = e.target.result;
                img.style.display = 'block';
            }
            if (initials) initials.style.display = 'none';
        };
        reader.readAsDataURL(file);

        // Upload to backend
        await this.uploadFile('company_logo', file);
    }

    async handleCoverPhotoChange(event) {
        const file = event.target.files[0];
        if (!file) return;

        if (!file.type.startsWith('image/')) {
            this.showNotification('Please select a valid image file', 'error');
            return;
        }
        if (file.size > 5 * 1024 * 1024) {
            this.showNotification('Image size should be less than 5MB', 'error');
            return;
        }

        // Preview immediately
        const reader = new FileReader();
        reader.onload = (e) => {
            const coverPhotoImg = document.getElementById('coverPhotoImg');
            if (coverPhotoImg) {
                coverPhotoImg.src = e.target.result;
                coverPhotoImg.style.opacity = '0';
                coverPhotoImg.style.transition = 'opacity 0.3s ease';
                setTimeout(() => { coverPhotoImg.style.opacity = '1'; }, 10);
            }
            if (this.profileData) this.profileData.coverImage = e.target.result;
        };
        reader.readAsDataURL(file);

        // Upload to backend
        await this.uploadFile('company_banner', file);
    }

    /**
     * Upload a file to the backend via profileController::update
     */
    async uploadFile(fieldName, file) {
        try {
            const formData = new FormData();
            formData.append(fieldName, file);

            const response = await fetch(this.updateUrl, {
                method: 'POST',
                body: formData,
                headers: {
                    'X-CSRF-TOKEN': this.csrfToken,
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                }
            });

            const json = await response.json();
            if (json.success) {
                this.showNotification('Photo updated successfully!', 'success');
            } else {
                this.showNotification(json.message || 'Failed to upload photo', 'error');
            }
        } catch (error) {
            console.error('Upload error:', error);
            this.showNotification('Error uploading photo', 'error');
        }
    }

    async uploadCoverPhoto(file) {
        // Handled by uploadFile now
        await this.uploadFile('company_banner', file);
    }
}

// Initialize when DOM is ready
document.addEventListener('DOMContentLoaded', () => {
    window.__contractorProfile = new ContractorProfile();
});
