/**
 * Contractor Homepage JavaScript
 * Handles project data fetching, display, and interactions
 */

class ContractorHomepage {
    constructor() {
        this.projects = [];
        this.filteredProjects = [];
        this.apiUrl = '/api/projects';
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
        this.filterProjectStatus = document.getElementById('filterProjectStatus');
        this.filterProvince = document.getElementById('filterProvince');
        this.filterCity = document.getElementById('filterCity');
        this.filterProjectType = document.getElementById('filterProjectType');
        this.filterBudget = document.getElementById('filterBudget');
        
        // Filter state
        this.activeFilters = {
            projectStatus: '',
            province: '',
            city: '',
            projectType: '',
            budget: ''
        };
        
        // Search state
        this.currentSearchTerm = '';
        
        this.init();
    }

    init() {
        // Display mock data immediately while API loads
        this.projects = this.getMockProjects();
        this.filteredProjects = [...this.projects];
        
        // Setup filter functionality (must be before renderProjects to populate options)
        this.setupFilters();
        
        // Render projects
        this.renderProjects();
        
        // Setup navbar search functionality
        this.setupNavbarSearch();
        
        // Load projects from API in background
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
            const response = await fetch(this.apiUrl);
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
                const location = this.formatLocation(project).toLowerCase();
                const projectType = (project.project_type || project.type || '').toLowerCase();
                const budget = (project.budget || project.estimated_budget || 0).toString();
                
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
        const location = this.formatLocation(project);
        const deadline = project.deadline || project.bid_deadline || project.end_date || 'Not specified';
        const projectType = project.project_type || project.type || 'General';
        const budget = project.budget || project.estimated_budget || 0;
        const status = project.status || 'open';
        const postedDate = project.created_at || project.posted_date || new Date().toISOString();
        const projectId = project.id || project.project_id || '';

        // Format dates
        const formattedDate = this.formatDate(postedDate);
        const formattedDeadline = this.formatDate(deadline);
        const formattedBudget = this.formatBudget(budget);

        // Get project image
        const projectImage = project.image || project.project_image || project.photo || project.thumbnail || '';
        const imageElement = card.querySelector('.project-image');
        const imageWrapper = card.querySelector('.project-image-wrapper');
        
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
        
        // Set project image
        if (projectImage) {
            imageElement.src = projectImage;
            imageElement.alt = projectTitle;
            imageElement.style.display = 'block';
        } else {
            // Hide image wrapper if no image
            if (imageWrapper) {
                imageWrapper.style.display = 'none';
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
        console.log('Apply bid for project:', projectId);
        
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
        console.log('View details for project:', projectId);
        // TODO: Implement project details view functionality
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

        // Populate filter options
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

        // Apply filters
        if (this.filterApplyBtn) {
            this.filterApplyBtn.addEventListener('click', () => {
                this.applyFiltersFromUI();
            });
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

        // Update city options when province changes
        if (this.filterProvince) {
            this.filterProvince.addEventListener('change', () => {
                this.updateCityOptions();
            });
        }
    }

    populateFilterOptions() {
        if (!this.projects || this.projects.length === 0) return;

        // Get unique values
        const provinces = new Set();
        const cities = new Set();

        this.projects.forEach(project => {
            if (project.province) provinces.add(project.province);
            if (project.city) cities.add(project.city);
        });

        // Populate provinces
        if (this.filterProvince) {
            const currentValue = this.filterProvince.value;
            this.filterProvince.innerHTML = '<option value="">All Provinces</option>';
            Array.from(provinces).sort().forEach(province => {
                const option = document.createElement('option');
                option.value = province;
                option.textContent = province;
                this.filterProvince.appendChild(option);
            });
            if (currentValue) this.filterProvince.value = currentValue;
        }

        // Populate cities (will be updated based on province selection)
        this.updateCityOptions();
    }

    updateCityOptions() {
        if (!this.filterCity || !this.filterProvince) return;

        const selectedProvince = this.filterProvince.value;
        const cities = new Set();

        this.projects.forEach(project => {
            if (project.city) {
                // If province is selected, only show cities in that province
                if (selectedProvince && project.province === selectedProvince) {
                    cities.add(project.city);
                } else if (!selectedProvince) {
                    cities.add(project.city);
                }
            }
        });

        const currentValue = this.filterCity.value;
        this.filterCity.innerHTML = '<option value="">All Cities</option>';
        Array.from(cities).sort().forEach(city => {
            const option = document.createElement('option');
            option.value = city;
            option.textContent = city;
            this.filterCity.appendChild(option);
        });
        
        // Reset city if province changed and city is no longer valid
        if (currentValue && !Array.from(cities).includes(currentValue)) {
            this.filterCity.value = '';
        } else if (currentValue) {
            this.filterCity.value = currentValue;
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
        this.activeFilters = {
            projectStatus: this.filterProjectStatus ? this.filterProjectStatus.value : '',
            province: this.filterProvince ? this.filterProvince.value : '',
            city: this.filterCity ? this.filterCity.value : '',
            projectType: this.filterProjectType ? this.filterProjectType.value : '',
            budget: this.filterBudget ? this.filterBudget.value : ''
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

        // Filter by project status
        if (this.activeFilters.projectStatus) {
            filtered = filtered.filter(project => {
                const status = project.status || 'open';
                return status === this.activeFilters.projectStatus;
            });
        }

        // Filter by province
        if (this.activeFilters.province) {
            filtered = filtered.filter(project => {
                return project.province === this.activeFilters.province;
            });
        }

        // Filter by city
        if (this.activeFilters.city) {
            filtered = filtered.filter(project => {
                return project.city === this.activeFilters.city;
            });
        }

        // Filter by project type
        if (this.activeFilters.projectType) {
            filtered = filtered.filter(project => {
                const type = project.project_type || project.type || '';
                return type.toLowerCase() === this.activeFilters.projectType.toLowerCase();
            });
        }

        // Filter by budget range
        if (this.activeFilters.budget) {
            filtered = filtered.filter(project => {
                const budget = parseFloat(project.budget || project.estimated_budget || 0);
                const range = this.activeFilters.budget;
                
                if (range === '0-50000') return budget >= 0 && budget <= 50000;
                if (range === '50000-100000') return budget > 50000 && budget <= 100000;
                if (range === '100000-500000') return budget > 100000 && budget <= 500000;
                if (range === '500000+') return budget > 500000;
                
                return true;
            });
        }

        return filtered;
    }

    clearFilters() {
        // Reset filter values
        this.activeFilters = {
            projectStatus: '',
            province: '',
            city: '',
            projectType: '',
            budget: ''
        };

        // Reset UI
        if (this.filterProjectStatus) this.filterProjectStatus.value = '';
        if (this.filterProvince) this.filterProvince.value = '';
        if (this.filterCity) this.filterCity.value = '';
        if (this.filterProjectType) this.filterProjectType.value = '';
        if (this.filterBudget) this.filterBudget.value = '';

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
