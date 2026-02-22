/**
 * Property Owner Homepage JavaScript
 * Handles contractor data fetching, display, and interactions
 */

class PropertyOwnerHomepage {
    constructor() {
        this.contractors = [];
        this.filteredContractors = [];
        this.apiUrl = '/api/contractors';
        this.contractorsGrid = document.getElementById('contractorsGrid');
        this.emptyState = document.getElementById('emptyState');
        this.cardTemplate = document.getElementById('contractorCardTemplate');
        
        // Filter elements
        this.filterIconBtn = document.getElementById('filterIconBtn');
        this.filterDropdown = document.getElementById('filterDropdown');
        this.filterCloseBtn = document.getElementById('filterCloseBtn');
        this.filterApplyBtn = document.getElementById('filterApplyBtn');
        this.filterClearBtn = document.getElementById('filterClearBtn');
        this.filterBadge = document.getElementById('filterBadge');
        
        // Filter select elements
        this.filterContractorType = document.getElementById('filterContractorType');
        this.filterProvince = document.getElementById('filterProvince');
        this.filterCity = document.getElementById('filterCity');
        this.filterRating = document.getElementById('filterRating');
        this.filterExperience = document.getElementById('filterExperience');
        
        // Filter state
        this.activeFilters = {
            contractorType: '',
            province: '',
            city: '',
            rating: '',
            experience: ''
        };

        // Search state
        this.currentSearchTerm = '';

        // Pagination
        this.currentPage = 1;
        this.perPage = 15;
        this.hasMore = false;
        this.totalResults = 0;
        this.isLoading = false;

        // Debounce timer
        this._searchTimeout = null;
        
        this.init();
    }

    init() {
        // Setup filter functionality
        this.setupFilters();

        // Setup navbar search functionality
        this.setupNavbarSearch();

        // Load contractors from API (server-side search)
        this.fetchFromApi();
    }

    setupNavbarSearch() {
        // Get the navbar search input
        const navbarSearchInput = document.querySelector('.navbar-search-input');
        const navbarSearchButton = document.querySelector('.navbar-search-btn');

        if (navbarSearchInput) {
            this.navbarSearchInput = navbarSearchInput;

            // Debounced search on input
            navbarSearchInput.addEventListener('input', (e) => {
                clearTimeout(this._searchTimeout);
                const value = e.target.value.trim();
                if (!value) {
                    this.currentSearchTerm = '';
                    this.fetchFromApi();
                } else {
                    this._searchTimeout = setTimeout(() => {
                        this.currentSearchTerm = value;
                        this.fetchFromApi();
                    }, 350);
                }
            });

            // Immediate search on Enter
            navbarSearchInput.addEventListener('keypress', (e) => {
                if (e.key === 'Enter') {
                    e.preventDefault();
                    clearTimeout(this._searchTimeout);
                    this.currentSearchTerm = e.target.value.trim();
                    this.fetchFromApi();
                }
            });

            // Escape clears search
            navbarSearchInput.addEventListener('keydown', (e) => {
                if (e.key === 'Escape') {
                    navbarSearchInput.value = '';
                    clearTimeout(this._searchTimeout);
                    this.currentSearchTerm = '';
                    this.fetchFromApi();
                }
            });
        }

        if (navbarSearchButton) {
            navbarSearchButton.addEventListener('click', () => {
                if (navbarSearchInput) {
                    clearTimeout(this._searchTimeout);
                    this.currentSearchTerm = navbarSearchInput.value.trim();
                    this.fetchFromApi();
                }
            });
        }
    }

    /**
     * Build query string from current search + filters + pagination
     */
    buildApiParams(page = 1) {
        const params = new URLSearchParams();
        params.set('page', page.toString());
        params.set('per_page', this.perPage.toString());

        if (this.currentSearchTerm) {
            params.set('search', this.currentSearchTerm);
        }
        if (this.activeFilters.contractorType) {
            params.set('type_id', this.activeFilters.contractorType);
        }
        if (this.activeFilters.province) {
            params.set('province', this.activeFilters.province);
        }
        if (this.activeFilters.city) {
            params.set('city', this.activeFilters.city);
        }
        if (this.activeFilters.experience) {
            params.set('min_experience', this.activeFilters.experience);
        }
        // Note: rating filter is not yet supported server-side.
        // It will be applied client-side as a post-filter below.

        return params.toString();
    }

    /**
     * Fetch contractors from backend API (server-side search/filter/pagination)
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
                let results = Array.isArray(data.data) ? data.data : [];

                // Client-side rating post-filter (not available server-side)
                if (this.activeFilters.rating) {
                    const minRating = parseFloat(this.activeFilters.rating);
                    results = results.filter(c => (c.rating || 0) >= minRating);
                }

                if (page === 1) {
                    this.contractors = results;
                } else {
                    this.contractors = [...this.contractors, ...results];
                }
                this.filteredContractors = [...this.contractors];
                this.currentPage = page;
                this.hasMore = data.pagination?.has_more || false;
                this.totalResults = data.pagination?.total || results.length;
            } else {
                if (page === 1) {
                    this.contractors = [];
                    this.filteredContractors = [];
                }
            }
        } catch (error) {
            console.error('Error fetching contractors:', error);
            if (page === 1) {
                this.contractors = [];
                this.filteredContractors = [];
            }
        } finally {
            this.isLoading = false;
            this.hideLoadingState();
            this.renderContractors();
        }
    }

    showLoadingState() {
        if (!this.contractorsGrid) return;
        // Show a small loading indicator at top of grid
        let loader = document.getElementById('searchLoadingIndicator');
        if (!loader) {
            loader = document.createElement('div');
            loader.id = 'searchLoadingIndicator';
            loader.style.cssText = 'text-align:center;padding:24px;color:#EC7E00;font-size:14px;';
            loader.innerHTML = '<i class="fi fi-rr-spinner" style="animation:spin 1s linear infinite;display:inline-block;margin-right:6px;"></i> Searching...';
            this.contractorsGrid.parentNode.insertBefore(loader, this.contractorsGrid);
        }
        loader.style.display = '';
    }

    hideLoadingState() {
        const loader = document.getElementById('searchLoadingIndicator');
        if (loader) loader.style.display = 'none';
    }


    renderContractors() {
        if (!this.contractorsGrid) return;

        // Clear existing content
        this.contractorsGrid.innerHTML = '';

        if (this.filteredContractors.length === 0) {
            this.showEmptyState();
            return;
        }

        this.hideEmptyState();

        // Render each contractor
        this.filteredContractors.forEach((contractor, index) => {
            const card = this.createContractorCard(contractor);
            card.style.animationDelay = `${index * 0.1}s`;
            this.contractorsGrid.appendChild(card);
        });

        // Setup card interactions
        this.setupCardInteractions();
    }

    createContractorCard(contractor) {
        // Clone the template
        const card = this.cardTemplate.content.cloneNode(true).querySelector('.contractor-card');
        
        // Extract contractor data
        const companyName = contractor.company_name || contractor.name || 'Unknown Company';
        const initials = this.getInitials(companyName);
        const experience = contractor.years_experience || contractor.experience || 0;
        const contractorType = contractor.contractor_type || contractor.specialty || 'General Contractor';
        const location = this.formatLocation(contractor);
        const rating = contractor.rating || 5.0;
        const reviews = contractor.reviews_count || contractor.total_reviews || 0;
        const projectsCompleted = contractor.projects_completed || contractor.completed_projects || 0;
        const specialty = contractor.specialty || contractorType;
        const contractorId = contractor.id || contractor.user_id || '';

        // Populate card data
        const avatar = card.querySelector('.contractor-avatar');
        card.querySelector('.contractor-initials').textContent = initials;
        card.querySelector('.contractor-name').textContent = companyName;
        card.querySelector('.contractor-experience').textContent = `${experience} years experience`;
        card.querySelector('.badge-text').textContent = contractorType;
        card.querySelector('.location-text').textContent = location;
        card.querySelector('.rating-text').textContent = `${rating} rating • ${reviews} reviews`;
        card.querySelector('.projects-text').textContent = `${projectsCompleted} projects completed`;
        card.querySelector('.specialty-text').textContent = specialty;
        card.querySelector('.contact-button').setAttribute('data-contractor-id', contractorId);

        // Assign different color to each contractor avatar
        const colorIndex = this.getAvatarColorIndex(contractorId, companyName);
        avatar.classList.add(`color-${colorIndex}`);

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

    getAvatarColorIndex(contractorId, companyName) {
        // Use a combination of ID and name to generate consistent color
        const hash = this.hashString(`${contractorId}-${companyName}`);
        // Return a number between 1-10 for different colors
        return (hash % 10) + 1;
    }

    hashString(str) {
        let hash = 0;
        for (let i = 0; i < str.length; i++) {
            const char = str.charCodeAt(i);
            hash = ((hash << 5) - hash) + char;
            hash = hash & hash; // Convert to 32-bit integer
        }
        return Math.abs(hash);
    }

    formatLocation(contractor) {
        const parts = [];
        
        if (contractor.street_address || contractor.address) {
            parts.push(contractor.street_address || contractor.address);
        }
        if (contractor.barangay) {
            parts.push(contractor.barangay);
        }
        if (contractor.city) {
            parts.push(contractor.city);
        }
        if (contractor.province) {
            parts.push(contractor.province);
        }
        if (contractor.postal_code) {
            parts.push(contractor.postal_code);
        }

        return parts.length > 0 ? parts.join(', ') : 'Location not specified';
    }

    setupCardInteractions() {
        // Contact button handlers
        const contactButtons = document.querySelectorAll('.contact-button');
        contactButtons.forEach(button => {
            button.addEventListener('click', (e) => {
                e.stopPropagation();
                const contractorId = button.getAttribute('data-contractor-id');
                this.handleContactClick(contractorId);
            });
        });

        // Play button handlers
        const playButtons = document.querySelectorAll('.play-button');
        playButtons.forEach(button => {
            button.addEventListener('click', (e) => {
                e.stopPropagation();
                const card = button.closest('.contractor-card');
                const contractorName = card.querySelector('.contractor-name').textContent;
                this.handlePlayClick(contractorName);
            });
        });

        // Card click handler (view profile)
        const cards = document.querySelectorAll('.contractor-card');
        cards.forEach(card => {
            card.addEventListener('click', (e) => {
                // Don't trigger if clicking on buttons
                if (!e.target.closest('button')) {
                    const contractorName = card.querySelector('.contractor-name').textContent;
                    this.handleCardClick(contractorName);
                }
            });
        });

        // Avatar interaction - add click and hover effects
        const avatars = document.querySelectorAll('.contractor-avatar');
        avatars.forEach(avatar => {
            // Click to view profile
            avatar.addEventListener('click', (e) => {
                e.stopPropagation();
                const card = avatar.closest('.contractor-card');
                const contractorName = card.querySelector('.contractor-name').textContent;
                this.handleCardClick(contractorName);
            });

            // Add ripple effect on click
            avatar.addEventListener('click', (e) => {
                this.createRippleEffect(avatar, e);
            });
        });
    }

    createRippleEffect(element, event) {
        const ripple = document.createElement('span');
        const rect = element.getBoundingClientRect();
        const size = Math.max(rect.width, rect.height);
        const x = event.clientX - rect.left - size / 2;
        const y = event.clientY - rect.top - size / 2;
        
        ripple.style.width = ripple.style.height = size + 'px';
        ripple.style.left = x + 'px';
        ripple.style.top = y + 'px';
        ripple.style.position = 'absolute';
        ripple.style.borderRadius = '50%';
        ripple.style.background = 'rgba(255, 255, 255, 0.5)';
        ripple.style.transform = 'scale(0)';
        ripple.style.animation = 'ripple 0.6s ease-out';
        ripple.style.pointerEvents = 'none';
        
        element.style.position = 'relative';
        element.style.overflow = 'hidden';
        element.appendChild(ripple);
        
        setTimeout(() => {
            ripple.remove();
        }, 600);
    }

    handleContactClick(contractorId) {
        console.log('Contact contractor:', contractorId);
        // TODO: Implement contact functionality
        // This could open a modal, navigate to a contact page, or trigger an email
        alert(`Contacting contractor (ID: ${contractorId}). This feature will be implemented.`);
    }

    handlePlayClick(contractorName) {
        console.log('View profile for:', contractorName);
        // TODO: Implement profile view functionality
        alert(`Viewing profile for ${contractorName}. This feature will be implemented.`);
    }

    handleCardClick(contractorName) {
        console.log('View details for:', contractorName);
        // TODO: Implement detailed view functionality
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

        // Populate filter options from API
        this.populateFilterOptions();

        // Load provinces from PSGC API
        if (this.filterProvince) {
            this.filterProvince.innerHTML = '<option value="">All Provinces</option>';
            fetch('/api/psgc/provinces')
                .then(res => res.json())
                .then(data => {
                    const provinces = data.data || data || [];
                    provinces.forEach(p => {
                        const option = document.createElement('option');
                        option.value = p.code;
                        option.textContent = p.name;
                        option.dataset.name = p.name;
                        this.filterProvince.appendChild(option);
                    });
                })
                .catch(err => console.error('Error loading provinces:', err));
        }

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
        // Fetch filter options from the backend API
        fetch('/api/search/filter-options', {
            headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
        })
        .then(res => res.json())
        .then(data => {
            if (!data.success || !data.data) return;
            const opts = data.data;

            // Populate contractor types from API
            if (this.filterContractorType && opts.contractor_types) {
                const currentValue = this.filterContractorType.value;
                this.filterContractorType.innerHTML = '<option value="">All Types</option>';
                opts.contractor_types.forEach(ct => {
                    const option = document.createElement('option');
                    option.value = ct.type_id;
                    option.textContent = ct.type_name;
                    this.filterContractorType.appendChild(option);
                });
                if (currentValue) this.filterContractorType.value = currentValue;
            }

            // Provinces are loaded via PSGC in setupFilters or are already populated
        })
        .catch(err => console.error('Error loading filter options:', err));
    }

    updateCityOptions() {
        if (!this.filterCity || !this.filterProvince) return;

        const selectedProvinceCode = this.filterProvince.value;
        this.filterCity.innerHTML = '<option value="">All Cities</option>';

        if (!selectedProvinceCode) {
            this.filterCity.disabled = true;
            return;
        }

        this.filterCity.disabled = false;
        this.filterCity.innerHTML = '<option value="">Loading...</option>';

        // Fetch cities from PSGC API for selected province
        fetch(`/api/psgc/provinces/${selectedProvinceCode}/cities`)
            .then(res => res.json())
            .then(data => {
                this.filterCity.innerHTML = '<option value="">All Cities</option>';
                const cities = data.data || data || [];
                cities.forEach(city => {
                    const option = document.createElement('option');
                    option.value = city.name;
                    option.textContent = city.name;
                    this.filterCity.appendChild(option);
                });
            })
            .catch(() => {
                this.filterCity.innerHTML = '<option value="">All Cities</option>';
            });
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

        // Get filter values from UI
        this.activeFilters = {
            contractorType: this.filterContractorType ? this.filterContractorType.value : '',
            province: provinceName,
            city: this.filterCity ? this.filterCity.value : '',
            rating: this.filterRating ? this.filterRating.value : '',
            experience: this.filterExperience ? this.filterExperience.value : ''
        };

        // Re-fetch from server with new filters
        this.updateFilterBadge();
        this.closeFilterDropdown();
        this.fetchFromApi();
    }

    // Kept for legacy compatibility but no longer primary path
    applyFilters(contractors) {
        return contractors;
    }

    clearFilters() {
        // Reset filter values
        this.activeFilters = {
            contractorType: '',
            province: '',
            city: '',
            rating: '',
            experience: ''
        };

        // Reset UI
        if (this.filterContractorType) this.filterContractorType.value = '';
        if (this.filterProvince) this.filterProvince.value = '';
        if (this.filterCity) this.filterCity.value = '';
        if (this.filterRating) this.filterRating.value = '';
        if (this.filterExperience) this.filterExperience.value = '';

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
    new PropertyOwnerHomepage();
});

