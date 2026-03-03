/**
 * Contractor Homepage JavaScript
 * Handles project data fetching, display, and interactions
 */

class ContractorHomepage {
    constructor() {
        this.projects = [];
        this.filteredProjects = [];
        this.apiUrl = '/api/contractor/projects';
        this.projectsGrid = document.getElementById('projectsGrid');
        this.emptyState = document.getElementById('emptyState');
        this.cardTemplate = document.getElementById('projectCardTemplate');

        // Filter elements
        this.filterIconBtn = document.getElementById('filterIconBtn');
        this.filterDropdown = document.getElementById('filterDropdown');
        this.filterCloseBtn = document.getElementById('filterCloseBtn');
        this.filterApplyBtn = document.getElementById('filterApplyBtn');
        this.filterClearBtn = document.getElementById('filterClearBtn');
        this.filterBadge = document.getElementById('filterBadge');

        // Filter select elements
        this.filterProvince = document.getElementById('filterProvince');
        this.filterCity = document.getElementById('filterCity');
        this.filterPropertyType = document.getElementById('filterPropertyType');
        this.filterBudgetMin = document.getElementById('filterBudgetMin');
        this.filterBudgetMax = document.getElementById('filterBudgetMax');

        // Filter state
        this.activeFilters = {
            province: '',
            city: '',
            propertyType: '',
            budgetMin: '',
            budgetMax: ''
        };

        // Search state
        this.currentSearchTerm = '';

        this.init();
    }

    init() {
        this.projects = [];
        this.filteredProjects = [];

        // Pagination
        this.currentPage = 1;
        this.perPage = 15;
        this.hasMore = false;
        this.totalResults = 0;
        this.isLoading = false;

        // Setup filter functionality
        this.setupFilters();

        // Setup navbar search functionality
        this.setupNavbarSearch();

        // Fetch projects from API (server-side search)
        this.fetchFromApi();
    }

    setupNavbarSearch() {
        setTimeout(() => {
            const navbarSearchInput = document.querySelector('.navbar-search-input');
            const navbarSearchButton = document.querySelector('.navbar-search-btn');

            if (!navbarSearchInput) {
                console.warn('Navbar search input not found');
                return;
            }

            this.navbarSearchInput = navbarSearchInput;

            let searchTimeout;

            navbarSearchInput.addEventListener('input', (e) => {
                clearTimeout(searchTimeout);
                const value = e.target.value.trim();
                if (!value) {
                    this.currentSearchTerm = '';
                    this.fetchFromApi();
                } else {
                    searchTimeout = setTimeout(() => {
                        this.currentSearchTerm = value;
                        this.fetchFromApi();
                    }, 350);
                }
            });

            navbarSearchInput.addEventListener('keypress', (e) => {
                if (e.key === 'Enter') {
                    e.preventDefault();
                    clearTimeout(searchTimeout);
                    this.currentSearchTerm = e.target.value.trim();
                    this.fetchFromApi();
                }
            });

            navbarSearchInput.addEventListener('keydown', (e) => {
                if (e.key === 'Escape') {
                    navbarSearchInput.value = '';
                    clearTimeout(searchTimeout);
                    this.currentSearchTerm = '';
                    this.fetchFromApi();
                }
            });

            if (navbarSearchButton) {
                navbarSearchButton.addEventListener('click', (e) => {
                    e.preventDefault();
                    clearTimeout(searchTimeout);
                    this.currentSearchTerm = navbarSearchInput.value.trim();
                    this.fetchFromApi();
                });
            }

            console.log('Navbar search initialized for contractor homepage');
        }, 100);
    }

    /**
     * Build query string from current search + filters + pagination
     */
    buildApiParams(page = 1) {
        const params = new URLSearchParams();
        params.set('page', page.toString());
        params.set('per_page', this.perPage.toString());

        if (window.userId) params.set('user_id', window.userId.toString());
        if (this.currentSearchTerm) params.set('search', this.currentSearchTerm);
        if (this.activeFilters.province) params.set('province', this.activeFilters.province);
        if (this.activeFilters.city) params.set('city', this.activeFilters.city);
        if (this.activeFilters.propertyType) params.set('property_type', this.activeFilters.propertyType);
        if (this.activeFilters.budgetMin) params.set('budget_min', this.activeFilters.budgetMin);
        if (this.activeFilters.budgetMax) params.set('budget_max', this.activeFilters.budgetMax);

        return params.toString();
    }

    /**
     * Fetch projects from backend API (server-side search/filter/pagination)
     */
    async fetchFromApi(page = 1) {
        if (this.isLoading) return;
        this.isLoading = true;
        this.showLoadingState();

        try {
            const qs = this.buildApiParams(page);
            const response = await fetch(`${this.apiUrl}?${qs}`, {
                headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
            });
            const data = await response.json();

            if (data.success && data.data) {
                const results = Array.isArray(data.data) ? data.data : [];
                if (page === 1) {
                    this.projects = results;
                } else {
                    this.projects = [...this.projects, ...results];
                }
                this.filteredProjects = [...this.projects];
                this.currentPage = page;
                this.hasMore = data.pagination?.has_more || false;
                this.totalResults = data.pagination?.total || results.length;
            } else {
                if (page === 1) {
                    this.projects = [];
                    this.filteredProjects = [];
                }
            }
        } catch (error) {
            console.error('Error fetching projects:', error);
            if (page === 1) {
                this.projects = [];
                this.filteredProjects = [];
            }
        } finally {
            this.isLoading = false;
            this.hideLoadingState();
            this.renderProjects();
            this.updateEmptyState();
        }
    }

    showLoadingState() {
        if (!this.projectsGrid) return;
        let loader = document.getElementById('searchLoadingIndicator');
        if (!loader) {
            loader = document.createElement('div');
            loader.id = 'searchLoadingIndicator';
            loader.style.cssText = 'text-align:center;padding:24px;color:#EC7E00;font-size:14px;';
            loader.innerHTML = '<i class="fi fi-rr-spinner" style="animation:spin 1s linear infinite;display:inline-block;margin-right:6px;"></i> Searching...';
            this.projectsGrid.parentNode.insertBefore(loader, this.projectsGrid);
        }
        loader.style.display = '';
    }

    hideLoadingState() {
        const loader = document.getElementById('searchLoadingIndicator');
        if (loader) loader.style.display = 'none';
    }

    updateEmptyState() {
        if (!this.emptyState) return;

        if (this.filteredProjects.length === 0) {
            this.showEmptyState();
            // Update empty state message based on whether there's a search or filter
            const emptyStateText = this.emptyState.querySelector('p');
            if (emptyStateText) {
                if (this.currentSearchTerm || this.hasActiveFilters()) {
                    emptyStateText.textContent = 'No projects found matching your search or filters';
                } else {
                    emptyStateText.textContent = 'No projects found';
                }
            }
        } else {
            this.hideEmptyState();
        }
    }

    async loadProjects() {
        // Replaced by fetchFromApi()
        this.fetchFromApi();
    }

    // Legacy — replaced by server-side filtering via fetchFromApi
    applySearchAndFilters() {
        this.filteredProjects = [...this.projects];
    }

    renderProjects() {
        if (!this.projectsGrid) return;

        // Clear existing content
        this.projectsGrid.innerHTML = '';

        if (this.filteredProjects.length === 0) {
            this.showEmptyState();
            return;
        }

        this.hideEmptyState();

        // Render each project
        this.filteredProjects.forEach((project, index) => {
            const card = this.createProjectCard(project);
            card.style.animationDelay = `${index * 0.1}s`;
            this.projectsGrid.appendChild(card);
        });

        // Setup card interactions
        this.setupCardInteractions();
    }

    createProjectCard(project) {
        // Clone the template
        const card = this.cardTemplate.content.cloneNode(true).querySelector('.project-card');

        // Extract project data
        const projectTitle = project.title || project.project_title || 'Untitled Project';
        const ownerName = project.owner_name || project.owner || project.property_owner_name || 'Property Owner';
        const ownerInitials = this.getInitials(ownerName);
        const description = project.description || project.project_description || 'No description available';
        const location = project.project_location || this.formatLocation(project);
        const deadline = project.bidding_deadline || project.deadline || project.bid_deadline || project.end_date || 'Not specified';
        const projectType = project.type_name || project.project_type || project.type || 'General';
        const budget = project.budget || project.estimated_budget || 0;
        const budgetMin = project.budget_range_min ?? project.budget_min ?? project.budgetMin ?? null;
        const budgetMax = project.budget_range_max ?? project.budget_max ?? project.budgetMax ?? null;
        const status = project.status || project.project_status || 'open';
        const postedDate = project.created_at || project.posted_date || new Date().toISOString();
        const projectId = project.id || project.project_id || '';

        // Format dates
        const formattedDate = this.formatDate(postedDate);
        const formattedDeadline = this.formatDeadline(deadline);
        let formattedBudget;
        if (budgetMin !== null && budgetMax !== null) {
            // Both min and max present — show range
            const min = parseFloat(budgetMin) || 0;
            const max = parseFloat(budgetMax) || 0;
            formattedBudget = `${this.formatBudget(min)} - ${this.formatBudget(max)}`;
        } else {
            formattedBudget = this.formatBudget(budget);
        }

        // Get project images, filtering out important documents (matching mobile behavior)
        let projectImages = [];
        if (project.files && Array.isArray(project.files) && project.files.length > 0) {
            project.files.forEach(f => {
                // Skip important documents — they should never appear on the card
                if (this.isImportantDocument(f.file_type, f.file_path)) return;

                const ft = (f.file_type || '').toLowerCase().trim();
                const isImage = ft === 'image' || ft === 'photo'
                    || ft === 'desired_design' || ft === 'desired design'
                    || ft === 'blueprint' || ft === 'others'
                    || (f.file_path && /\.(jpg|jpeg|png|gif|webp|bmp)$/i.test(f.file_path));
                if (isImage) {
                    const path = f.file_path.replace(/^\//, '');
                    projectImages.push(`/storage/${path}`);
                }
            });
        }

        // Limit to 4 images for the grid (FB style usually shows 4 max with +N overlay)
        const displayImages = projectImages.slice(0, 4);
        const extraCount = projectImages.length - 4;

        const mediaGrid = card.querySelector('.project-media-grid');

        // Get avatar color class based on owner name
        const avatarColorClass = this.getAvatarColorClass(ownerName);

        // Populate card data
        const avatarElement = card.querySelector('.project-owner-avatar');
        if (avatarElement) {
            avatarElement.classList.add(avatarColorClass);
        }
        card.querySelector('.owner-initials').textContent = ownerInitials;
        card.querySelector('.project-owner-name').textContent = ownerName;
        card.querySelector('.project-posted-date').textContent = `Posted ${formattedDate}`;
        card.querySelector('.status-text').textContent = this.formatStatus(status);
        card.querySelector('.status-text').classList.add(`status-${status}`);

        // Set project images grid
        if (mediaGrid) {
            mediaGrid.innerHTML = '';
            mediaGrid.className = 'project-media-grid'; // Reset class

            if (displayImages.length > 0) {
                mediaGrid.style.display = 'grid';
                mediaGrid.classList.add(`grid-${displayImages.length}`); // Add class based on count (grid-1, grid-2, etc)

                displayImages.forEach((src, index) => {
                    const img = document.createElement('img');
                    img.src = src;
                    img.alt = `Project Image ${index + 1}`;
                    img.className = 'project-grid-image';

                    // Error handler
                    img.onerror = function () {
                        if (!this.src.includes('logo.svg')) {
                            this.src = '/img/logo.svg';
                        } else {
                            this.style.display = 'none';
                        }
                    };

                    // Handle 4th image overlay if there are more
                    if (index === 3 && extraCount > 0) {
                        const wrapper = document.createElement('div');
                        wrapper.className = 'image-overlay-wrapper';
                        wrapper.appendChild(img);

                        const overlay = document.createElement('div');
                        overlay.className = 'more-images-overlay';
                        overlay.textContent = `+${extraCount}`;
                        wrapper.appendChild(overlay);
                        mediaGrid.appendChild(wrapper);
                    } else {
                        mediaGrid.appendChild(img);
                    }
                });
            } else {
                mediaGrid.style.display = 'none';
            }
        }

        card.querySelector('.project-title').textContent = projectTitle;
        card.querySelector('.project-description').textContent = description;
        card.querySelector('.location-text').textContent = location;
        card.querySelector('.deadline-text').textContent = `Deadline: ${formattedDeadline}`;
        card.querySelector('.type-text').textContent = projectType;
        card.querySelector('.budget-text').textContent = formattedBudget;

        const applyBidButton = card.querySelector('.apply-bid-button');
        applyBidButton.setAttribute('data-project-id', projectId);
        applyBidButton.setAttribute('data-project-data', JSON.stringify(project));

        return card;
    }

    getInitials(name) {
        if (!name) return '??';
        const words = name.trim().split(/\s+/);
        if (words.length >= 2) {
            return (words[0][0] + words[1][0]).toUpperCase();
        }
        return name.substring(0, 2).toUpperCase();
    }

    getAvatarColorClass(ownerName) {
        // Generate a consistent color based on the owner's name
        // This ensures the same owner always gets the same color
        if (!ownerName) return 'avatar-color-1';

        // Special color assignments for specific owners
        const specialColors = {
            'Emmanuelle Santos': 'avatar-color-11'
        };

        // Check if this owner has a special color assigned
        if (specialColors[ownerName]) {
            return specialColors[ownerName];
        }

        // Simple hash function to convert name to a number
        let hash = 0;
        for (let i = 0; i < ownerName.length; i++) {
            hash = ownerName.charCodeAt(i) + ((hash << 5) - hash);
            hash = hash & hash; // Convert to 32bit integer
        }

        // Map hash to a color class (1-10)
        const colorNumber = (Math.abs(hash) % 10) + 1;
        return `avatar-color-${colorNumber}`;
    }

    formatLocation(project) {
        if (project.project_location) return project.project_location;

        const parts = [];

        if (project.city) {
            parts.push(project.city);
        }
        if (project.province) {
            parts.push(project.province);
        }

        return parts.length > 0 ? parts.join(', ') : 'Location not specified';
    }

    formatDate(dateString) {
        if (!dateString) return 'Not specified';
        try {
            const date = new Date(dateString);
            const now = new Date();
            const diffTime = Math.abs(now - date);
            const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24));

            if (diffDays === 0) return 'today';
            if (diffDays === 1) return 'yesterday';
            if (diffDays < 7) return `${diffDays} days ago`;
            if (diffDays < 30) return `${Math.floor(diffDays / 7)} weeks ago`;
            if (diffDays < 365) return `${Math.floor(diffDays / 30)} months ago`;
            return date.toLocaleDateString('en-US', { year: 'numeric', month: 'short', day: 'numeric' });
        } catch (e) {
            return 'Not specified';
        }
    }

    /**
     * Check if a file is an important/protected document (building permit, land title).
     * Matches mobile app logic in homepage.tsx / projectPostDetail.tsx.
     */
    isImportantDocument(fileType, filePath) {
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

    formatDeadline(dateString) {
        if (!dateString) return 'Not specified';
        try {
            const date = new Date(dateString);
            const now = new Date();
            const diffTime = date - now; // Positive for future
            const diffDays = Math.ceil(Math.abs(diffTime) / (1000 * 60 * 60 * 24));

            const isFuture = diffTime > 0;
            const dateText = date.toLocaleDateString('en-US', { year: 'numeric', month: 'short', day: 'numeric' });

            if (!isFuture) return `Ended on ${dateText}`;

            if (diffDays === 0) return 'Due Today';
            if (diffDays === 1) return 'Due Tomorrow';
            if (diffDays < 60) return `${dateText} (In ${diffDays} days)`;

            return dateText;
        } catch (e) {
            return 'Not specified';
        }
    }

    formatBudget(budget) {
        if (!budget || budget === 0) return 'Budget not specified';
        return `₱${parseFloat(budget).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 })}`;
    }

    formatStatus(status) {
        const statusMap = {
            'pending': 'Pending',
            'active': 'Active',
            'open': 'Open for Bids',
            'in_progress': 'In Progress',
            'completed': 'Completed',
            'cancelled': 'Cancelled'
        };
        return statusMap[status] || status.charAt(0).toUpperCase() + status.slice(1);
    }

    setupCardInteractions() {
        // Apply Bid button handlers
        const applyBidButtons = document.querySelectorAll('.apply-bid-button');
        applyBidButtons.forEach(button => {
            button.addEventListener('click', (e) => {
                e.stopPropagation();
                const projectId = button.getAttribute('data-project-id');
                this.handleApplyBidClick(projectId, e);
            });
        });

        // Card click handler (view project details)
        const cards = document.querySelectorAll('.project-card');
        cards.forEach(card => {
            card.addEventListener('click', (e) => {
                // Don't trigger if clicking on buttons
                if (!e.target.closest('button')) {
                    const projectId = card.querySelector('.apply-bid-button').getAttribute('data-project-id');
                    this.handleCardClick(projectId);
                }
            });
        });
    }

    handleApplyBidClick(projectId, event) {
        // Find the project data
        let project = this.projects.find(p => (p.id || p.project_id) == projectId);

        // If not found, try to get from button data attribute
        if (!project && event && event.target) {
            const button = event.target.closest('.apply-bid-button');
            if (button) {
                const projectDataAttr = button.getAttribute('data-project-data');
                if (projectDataAttr) {
                    try {
                        project = JSON.parse(projectDataAttr);
                    } catch (e) {
                        console.error('Error parsing project data:', e);
                    }
                }
            }
        }

        if (!project) {
            console.error('Project not found:', projectId);
            return;
        }

        // Open the apply bid modal using the global function
        const projectIdValue = project.project_id || project.id;
        if (typeof window.openBidModal === 'function') {
            window.openBidModal(projectIdValue);
        } else {
            console.error('Apply Bid Modal not initialized');
            // Wait a bit and try again (in case modal is still loading)
            setTimeout(() => {
                if (typeof window.openBidModal === 'function') {
                    window.openBidModal(projectIdValue);
                }
            }, 100);
        }
    }

    handleCardClick(projectId) {
        // Find the project object
        const project = this.projects.find(p => (p.project_id || p.id) == projectId);

        if (project) {
            if (typeof window.openProjectDetailsModal === 'function') {
                window.openProjectDetailsModal(project);
            } else {
                console.error('openProjectDetailsModal function is not defined');
            }
        } else {
            console.error('Project not found for ID:', projectId);
        }
    }

    showEmptyState() {
        if (this.emptyState) {
            this.emptyState.classList.remove('hidden');
        }
    }

    hideEmptyState() {
        if (this.emptyState) {
            this.emptyState.classList.add('hidden');
        }
    }

    // Filter functionality
    setupFilters() {
        if (!this.filterIconBtn || !this.filterDropdown) return;

        // Populate filter options (Provinces)
        this.populateFilterOptions();

        // Toggle dropdown on filter icon click
        this.filterIconBtn.addEventListener('click', (e) => {
            e.stopPropagation();
            this.toggleFilterDropdown();
        });

        // Close dropdown on close button click
        if (this.filterCloseBtn) {
            this.filterCloseBtn.addEventListener('click', () => {
                this.closeFilterDropdown();
            });
        }

        // Apply filters automatically on change/input
        const autoApply = () => this.applyFiltersFromUI();
        const debouncedAutoApply = this.debounce(autoApply, 500);

        if (this.filterProvince) {
            this.filterProvince.addEventListener('change', () => {
                this.updateCityOptions();
                autoApply();
            });
        }

        if (this.filterCity) {
            this.filterCity.addEventListener('change', autoApply);
        }

        if (this.filterPropertyType) {
            this.filterPropertyType.addEventListener('change', autoApply);
        }

        if (this.filterBudgetMin) {
            this.filterBudgetMin.addEventListener('input', debouncedAutoApply);
        }

        if (this.filterBudgetMax) {
            this.filterBudgetMax.addEventListener('input', debouncedAutoApply);
        }

        // Clear filters
        if (this.filterClearBtn) {
            this.filterClearBtn.addEventListener('click', () => {
                this.clearFilters();
            });
        }

        // Close dropdown when clicking outside
        document.addEventListener('click', (e) => {
            if (this.filterDropdown &&
                !this.filterDropdown.contains(e.target) &&
                !this.filterIconBtn.contains(e.target)) {
                this.closeFilterDropdown();
            }
        });
    }

    // Debounce helper
    debounce(func, wait) {
        let timeout;
        return function executedFunction(...args) {
            const later = () => {
                clearTimeout(timeout);
                func(...args);
            };
            clearTimeout(timeout);
            timeout = setTimeout(later, wait);
        };
    }

    async populateFilterOptions() {
        // Fetch Provinces from PSGC API
        if (this.filterProvince) {
            try {
                const res = await fetch('/api/psgc/provinces');
                const json = await res.json();

                // API returns { success: true, data: [...] }
                const provinces = Array.isArray(json?.data) ? json.data : [];

                // Sort by name
                provinces.sort((a, b) => a.name.localeCompare(b.name));

                this.filterProvince.innerHTML = '<option value="">All Provinces</option>';
                provinces.forEach(p => {
                    const option = document.createElement('option');
                    // Use PSGC code as the option value (used to fetch cities)
                    option.value = p.code;
                    // Keep readable name for filtering and display
                    option.dataset.name = p.name;
                    option.textContent = p.name;
                    this.filterProvince.appendChild(option);
                });
            } catch (e) {
                console.error('Error fetching provinces:', e);
            }
        }
    }

    async updateCityOptions() {
        if (!this.filterCity || !this.filterProvince) return;

        const provinceCode = this.filterProvince.value;
        this.filterCity.innerHTML = '<option value="">All Cities</option>';
        this.filterCity.disabled = true;

        if (provinceCode) {
            try {
                const res = await fetch(`/api/psgc/provinces/${provinceCode}/cities`);
                const json = await res.json();

                // API returns { success: true, data: [...] }
                const cities = Array.isArray(json?.data) ? json.data : [];

                cities.sort((a, b) => a.name.localeCompare(b.name));

                cities.forEach(c => {
                    const option = document.createElement('option');
                    // For city selection we use the NAME because project records typically store readable names
                    option.value = c.name;
                    option.textContent = c.name;
                    this.filterCity.appendChild(option);
                });
                this.filterCity.disabled = false;
            } catch (e) {
                console.error('Error fetching cities:', e);
            }
        }
    }

    toggleFilterDropdown() {
        if (!this.filterDropdown) return;

        const isActive = this.filterDropdown.classList.contains('active');
        if (isActive) {
            this.closeFilterDropdown();
        } else {
            this.openFilterDropdown();
        }
    }

    openFilterDropdown() {
        if (!this.filterDropdown || !this.filterIconBtn) return;
        this.filterDropdown.classList.add('active');
        this.filterIconBtn.classList.add('active');
    }

    closeFilterDropdown() {
        if (!this.filterDropdown || !this.filterIconBtn) return;
        this.filterDropdown.classList.remove('active');
        this.filterIconBtn.classList.remove('active');
    }

    applyFiltersFromUI() {
        // Extract province name from selected option (value is PSGC code)
        let provinceName = '';
        if (this.filterProvince && this.filterProvince.selectedIndex > 0) {
            const selectedOption = this.filterProvince.options[this.filterProvince.selectedIndex];
            provinceName = selectedOption.dataset.name || selectedOption.text;
        }

        this.activeFilters = {
            province: provinceName,
            city: this.filterCity ? this.filterCity.value : '',
            propertyType: this.filterPropertyType ? this.filterPropertyType.value : '',
            budgetMin: this.filterBudgetMin ? this.filterBudgetMin.value : '',
            budgetMax: this.filterBudgetMax ? this.filterBudgetMax.value : ''
        };

        // Re-fetch from server with new filters
        this.updateFilterBadge();
        this.fetchFromApi();
    }

    // Legacy — filtering is done server-side now
    applyFilters(projects) {
        return projects;
    }

    clearFilters() {
        // Reset filter values
        this.activeFilters = {
            province: '',
            city: '',
            propertyType: '',
            budgetMin: '',
            budgetMax: ''
        };

        // Reset UI
        if (this.filterProvince) this.filterProvince.value = '';
        if (this.filterCity) {
            this.filterCity.innerHTML = '<option value="">All Cities</option>';
            this.filterCity.value = '';
            this.filterCity.disabled = true;
        }
        if (this.filterPropertyType) this.filterPropertyType.value = '';
        if (this.filterBudgetMin) this.filterBudgetMin.value = '';
        if (this.filterBudgetMax) this.filterBudgetMax.value = '';

        // Update city options
        this.updateCityOptions();

        // Re-fetch from server without filters
        this.updateFilterBadge();
        this.fetchFromApi();
    }

    hasActiveFilters() {
        return Object.values(this.activeFilters).some(value => value !== '');
    }

    getActiveFilterCount() {
        return Object.values(this.activeFilters).filter(value => value !== '').length;
    }

    updateFilterBadge() {
        if (!this.filterBadge) return;

        const count = this.getActiveFilterCount();
        if (count > 0) {
            this.filterBadge.textContent = count;
            this.filterBadge.classList.remove('hidden');
        } else {
            this.filterBadge.classList.add('hidden');
        }
    }

}

// Initialize when DOM is ready
document.addEventListener('DOMContentLoaded', () => {
    new ContractorHomepage();
});
