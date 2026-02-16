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
        // Prefer server-provided projects when available; otherwise display mock data immediately while API loads
        if (window.serverProjects && Array.isArray(window.serverProjects) && window.serverProjects.length > 0) {
            this.projects = window.serverProjects;
        } else {
            this.projects = this.getMockProjects();
        }
        this.filteredProjects = [...this.projects];

        // Setup filter functionality (must be before renderProjects to populate options)
        this.setupFilters();

        // Render projects
        this.renderProjects();

        // Setup navbar search functionality
        this.setupNavbarSearch();

        // Load projects from API in background (will override if API returns data)
        this.loadProjects();
    }

    setupNavbarSearch() {
        // Wait a bit for navbar to be fully loaded
        setTimeout(() => {
            // Get the navbar search input
            const navbarSearchInput = document.querySelector('.navbar-search-input');
            const navbarSearchButton = document.querySelector('.navbar-search-btn');

            if (!navbarSearchInput) {
                console.warn('Navbar search input not found');
                return;
            }

            // Store reference for later use
            this.navbarSearchInput = navbarSearchInput;

            // Debounce function for search input
            let searchTimeout;
            const debounceSearch = (value) => {
                clearTimeout(searchTimeout);
                searchTimeout = setTimeout(() => {
                    this.handleSearch(value);
                }, 300); // 300ms delay
            };

            // Create named handler functions for easy removal
            const inputHandler = (e) => {
                const value = e.target.value.trim();
                if (value.length === 0) {
                    // Clear search immediately if empty
                    clearTimeout(searchTimeout);
                    this.handleSearch('');
                } else {
                    debounceSearch(value);
                }
            };

            const keypressHandler = (e) => {
                if (e.key === 'Enter') {
                    e.preventDefault();
                    clearTimeout(searchTimeout);
                    this.handleSearch(e.target.value.trim());
                }
            };

            const keydownHandler = (e) => {
                if (e.key === 'Escape') {
                    navbarSearchInput.value = '';
                    clearTimeout(searchTimeout);
                    this.handleSearch('');
                }
            };

            const buttonClickHandler = (e) => {
                e.preventDefault();
                clearTimeout(searchTimeout);
                this.handleSearch(navbarSearchInput.value.trim());
            };

            // Store handlers for potential cleanup
            this.searchHandlers = {
                input: inputHandler,
                keypress: keypressHandler,
                keydown: keydownHandler,
                buttonClick: buttonClickHandler
            };

            // Search on input with debouncing
            navbarSearchInput.addEventListener('input', inputHandler);

            // Search on Enter key (immediate, no debounce)
            navbarSearchInput.addEventListener('keypress', keypressHandler);

            // Clear search on Escape key
            navbarSearchInput.addEventListener('keydown', keydownHandler);

            // Search on button click (immediate, no debounce)
            if (navbarSearchButton) {
                navbarSearchButton.addEventListener('click', buttonClickHandler);
            }

            console.log('Navbar search initialized for contractor homepage');
        }, 100);
    }

    handleSearch(query) {
        const searchTerm = query.toLowerCase().trim();

        // Store search term for later use
        this.currentSearchTerm = searchTerm;

        // Apply both search and filters together
        this.applySearchAndFilters();

        // Update UI
        this.renderProjects();
        this.updateEmptyState();
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
        // Load projects in background and update if API succeeds
        try {
            const url = window.userId ? `${this.apiUrl}?user_id=${window.userId}` : this.apiUrl;
            const response = await fetch(url);
            const data = await response.json();

            if (data.success && data.data && data.data.length > 0) {
                this.projects = data.data;
                // Re-apply current search and filters after loading new data
                this.applySearchAndFilters();
                this.populateFilterOptions();
                this.renderProjects();
            }
            // If API fails or returns no data, keep using mock data already displayed
        } catch (error) {
            console.error('Error loading projects:', error);
            // Keep using mock data already displayed
        }
    }

    applySearchAndFilters() {
        // Start with all projects
        let projectsToFilter = [...this.projects];

        // Apply active filters first
        if (this.hasActiveFilters()) {
            projectsToFilter = this.applyFilters(projectsToFilter);
        }

        // Then apply search if there's a search term
        if (this.currentSearchTerm) {
            projectsToFilter = projectsToFilter.filter(project => {
                const title = (project.title || project.project_title || '').toLowerCase();
                const description = (project.description || project.project_description || '').toLowerCase();
                const ownerName = (project.owner_name || project.owner || project.property_owner_name || '').toLowerCase();
                const location = (project.project_location || this.formatLocation(project)).toLowerCase();
                const projectType = (project.type_name || project.project_type || project.type || '').toLowerCase();
                const budget = (project.budget_range_min || project.budget || project.estimated_budget || 0).toString();

                return title.includes(this.currentSearchTerm) ||
                    description.includes(this.currentSearchTerm) ||
                    ownerName.includes(this.currentSearchTerm) ||
                    location.includes(this.currentSearchTerm) ||
                    projectType.includes(this.currentSearchTerm) ||
                    budget.includes(this.currentSearchTerm);
            });
        }

        this.filteredProjects = projectsToFilter;
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

        // Get ALL project images from files array
        let projectImages = [];
        if (project.files && Array.isArray(project.files) && project.files.length > 0) {
            project.files.forEach(f => {
                if (f.file_type === 'image' || f.file_type === 'photo' || (f.file_path && /\.(jpg|jpeg|png|gif|webp)$/i.test(f.file_path))) {
                    // Remove leading slash
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

        // Open the apply bid modal
        if (window.applyBidModal) {
            window.applyBidModal.openModal(project);
        } else {
            console.error('Apply Bid Modal not initialized');
            // Wait a bit and try again (in case modal is still loading)
            setTimeout(() => {
                if (window.applyBidModal) {
                    window.applyBidModal.openModal(project);
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
                const response = await fetch('/api/psgc/provinces');
                const provinces = await response.json();

                // Sort by name
                provinces.sort((a, b) => a.name.localeCompare(b.name));

                this.filterProvince.innerHTML = '<option value="">All Provinces</option>';
                provinces.forEach(p => {
                    const option = document.createElement('option');
                    option.value = p.code; // Store code, but maybe text is needed for filtering? 
                    // Wait, existing projects have 'Metro Manila' or 'Cebu' as text.
                    // The API returns codes and names.
                    // If projects store NAMES, I should use p.name as value? 
                    // Previous code (Step 1608) suggests fetching cities by CODE.
                    // So value must be CODE to fetch cities.
                    // BUT filtering must match project data (which is likely Name).
                    // I will use CODE as value to fetch cities, but when Filtering, I might need to map or check Name.
                    // Actually, let's see how 'owner.js' does it.
                    // Step 1608: "Handle PSGC selects to send names instead of codes if needed".
                    // If `projects` table stores Names, I need to match Names.
                    // I'll store Name in data attribute? Or just use Code and look up Name?
                    // Let's use Code as value for the dropdown logic, and data-name for filtering.
                    option.value = p.code;
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
                const response = await fetch(`/api/psgc/provinces/${provinceCode}/cities`);
                const cities = await response.json();

                cities.sort((a, b) => a.name.localeCompare(b.name));

                cities.forEach(c => {
                    const option = document.createElement('option');
                    option.value = c.name; // Use Name for city filtering as it's likely stored as name
                    // Wait, if I use Code for Province, I need Code to fetch cities.
                    // But for City, I only need to filter.
                    // Projects likely store "Davao City", "Makati City".
                    // So City value should be NAME.
                    // What about Province value?
                    // If I filter by Province, and projects store "Davao del Sur", but value is "112400000".
                    // I need to use the Name for filtering.
                    // In `applyFilters`, I'll get the selected option's text (or data-name).
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
        // Get filter values from UI
        let provinceName = '';
        if (this.filterProvince && this.filterProvince.selectedIndex > 0) {
            // Use text content (Name) for filtering, not the Code
            const selectedOption = this.filterProvince.options[this.filterProvince.selectedIndex];
            provinceName = selectedOption.dataset.name || selectedOption.text;
        }

        this.activeFilters = {
            province: provinceName, // Filter by Name
            city: this.filterCity ? this.filterCity.value : '',
            propertyType: this.filterPropertyType ? this.filterPropertyType.value : '',
            budgetMin: this.filterBudgetMin ? this.filterBudgetMin.value : '',
            budgetMax: this.filterBudgetMax ? this.filterBudgetMax.value : ''
        };

        // Apply both filters and search
        this.applySearchAndFilters();

        // Update UI
        this.updateFilterBadge();
        this.renderProjects();
        this.updateEmptyState();
        this.closeFilterDropdown();
    }

    applyFilters(projects) {
        let filtered = [...projects];

        // Filter by province
        if (this.activeFilters.province) {
            filtered = filtered.filter(project => {
                const pLocation = (project.project_location || '').toLowerCase();
                const pProvince = (project.province || '').toLowerCase();
                const filterVal = this.activeFilters.province.toLowerCase();
                // Check if project.province matches OR if location string contains it
                return pProvince === filterVal || pLocation.includes(filterVal);
            });
        }

        // Filter by city
        if (this.activeFilters.city) {
            filtered = filtered.filter(project => {
                const pCity = (project.city || '').toLowerCase();
                const pLocation = (project.project_location || '').toLowerCase();
                const filterVal = this.activeFilters.city.toLowerCase();
                return pCity === filterVal || pLocation.includes(filterVal);
            });
        }

        // Filter by property type
        if (this.activeFilters.propertyType) {
            filtered = filtered.filter(project => {
                const type = project.property_type || project.project_type || project.type_name || '';
                return type.toLowerCase() === this.activeFilters.propertyType.toLowerCase();
            });
        }

        // Filter by budget range (Min/Max)
        const userMin = this.activeFilters.budgetMin ? parseFloat(this.activeFilters.budgetMin) : null;
        const userMax = this.activeFilters.budgetMax ? parseFloat(this.activeFilters.budgetMax) : null;

        if (userMin !== null || userMax !== null) {
            filtered = filtered.filter(project => {
                // Project budget might be single value or range
                let pMin = 0;
                let pMax = 0;

                if (project.budget_range_min !== undefined && project.budget_range_max !== undefined) {
                    pMin = parseFloat(project.budget_range_min) || 0;
                    pMax = parseFloat(project.budget_range_max) || 0;
                } else {
                    const b = parseFloat(project.budget || project.estimated_budget || 0);
                    pMin = b;
                    pMax = b;
                }

                if (userMin !== null && pMax < userMin) return false;
                if (userMax !== null && pMin > userMax) return false;

                return true;
            });
        }

        return filtered;
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

        // Re-apply search (if any) after clearing filters
        this.applySearchAndFilters();

        // Update UI
        this.updateFilterBadge();
        this.renderProjects();
        this.updateEmptyState();
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

    // Mock data for development/testing
    getMockProjects() {
        return [
            {
                id: 1,
                title: 'Modern Two-Storey House Construction',
                owner_name: 'Emmanuelle Santos',
                description: 'Looking for an experienced contractor to build a modern two-storey house with 3 bedrooms, 2 bathrooms, and an open floor plan. The project includes foundation, framing, roofing, electrical, and plumbing work.',
                city: 'Manila',
                province: 'Metro Manila',
                deadline: '2024-12-31',
                project_type: 'Residential',
                budget: 2500000,
                status: 'open',
                created_at: '2024-01-15',
                image: 'https://images.unsplash.com/photo-1600596542815-ffad4c1539a9?w=400&h=300&fit=crop'
            },
            {
                id: 2,
                title: 'Office Building Renovation',
                owner_name: 'John Smith',
                description: 'Complete renovation of a 5-story office building. Includes interior design, electrical upgrades, HVAC system replacement, and facade improvements.',
                city: 'Makati',
                province: 'Metro Manila',
                deadline: '2024-11-30',
                project_type: 'Commercial',
                budget: 5000000,
                status: 'open',
                created_at: '2024-01-10',
                image: 'https://images.unsplash.com/photo-1486406146926-c627a92ad1ab?w=400&h=300&fit=crop'
            },
            {
                id: 3,
                title: 'Luxury Villa Construction',
                owner_name: 'Maria Garcia',
                description: 'High-end villa construction with modern amenities, sustainable design features, swimming pool, and landscaped gardens. Premium materials required.',
                city: 'Quezon City',
                province: 'Metro Manila',
                deadline: '2025-03-31',
                project_type: 'Residential',
                budget: 8000000,
                status: 'open',
                created_at: '2024-01-05',
                image: 'https://images.unsplash.com/photo-1600607687939-ce8a6c25118c?w=400&h=300&fit=crop'
            },
            {
                id: 4,
                title: 'Warehouse Construction',
                owner_name: 'Robert Johnson',
                description: 'Industrial warehouse construction with loading docks, office space, and parking area. Must comply with building codes and safety regulations.',
                city: 'Caloocan',
                province: 'Metro Manila',
                deadline: '2024-10-15',
                project_type: 'Industrial',
                budget: 3500000,
                status: 'active',
                created_at: '2023-12-20',
                image: 'https://images.unsplash.com/photo-1600585154340-be6161a56a0c?w=400&h=300&fit=crop'
            },
            {
                id: 5,
                title: 'Apartment Complex Development',
                owner_name: 'Sarah Williams',
                description: 'Multi-unit apartment complex with 20 units, common areas, parking, and modern facilities. Project includes site development and landscaping.',
                city: 'Pasig',
                province: 'Metro Manila',
                deadline: '2025-06-30',
                project_type: 'Residential',
                budget: 12000000,
                status: 'open',
                created_at: '2024-01-20',
                image: 'https://images.unsplash.com/photo-1600566753190-17f0baa2a6c3?w=400&h=300&fit=crop'
            },
            {
                id: 6,
                title: 'Retail Store Fit-Out',
                owner_name: 'Michael Brown',
                description: 'Interior fit-out for a new retail store including fixtures, lighting, flooring, and display systems. Fast-track project with tight deadline.',
                city: 'Taguig',
                province: 'Metro Manila',
                deadline: '2024-09-30',
                project_type: 'Commercial',
                budget: 800000,
                status: 'open',
                created_at: '2024-01-18',
                image: 'https://images.unsplash.com/photo-1497366216548-37526070297c?w=400&h=300&fit=crop'
            }
        ];
    }
}

// Initialize when DOM is ready
document.addEventListener('DOMContentLoaded', () => {
    new ContractorHomepage();
});
