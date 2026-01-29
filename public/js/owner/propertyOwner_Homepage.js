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
        
        this.init();
    }

    init() {
        // Display mock data immediately while API loads
        this.contractors = this.getMockContractors();
        this.filteredContractors = [...this.contractors];
        
        // Setup filter functionality (must be before renderContractors to populate options)
        this.setupFilters();
        
        // Render contractors
        this.renderContractors();
        
        // Setup navbar search functionality
        this.setupNavbarSearch();
        
        // Load contractors from API in background
        this.loadContractors();
    }

    setupNavbarSearch() {
        // Get the navbar search input
        const navbarSearchInput = document.querySelector('.navbar-search-input');
        const navbarSearchButton = document.querySelector('.navbar-search-btn');
        
        if (navbarSearchInput) {
            // Search on input
            navbarSearchInput.addEventListener('input', (e) => {
                this.handleSearch(e.target.value);
            });

            // Search on Enter key
            navbarSearchInput.addEventListener('keypress', (e) => {
                if (e.key === 'Enter') {
                    e.preventDefault();
                    this.handleSearch(e.target.value);
                }
            });
        }

        if (navbarSearchButton) {
            // Search on button click
            navbarSearchButton.addEventListener('click', () => {
                if (navbarSearchInput) {
                    this.handleSearch(navbarSearchInput.value);
                }
            });
        }
    }

    handleSearch(query) {
        const searchTerm = query.toLowerCase().trim();
        
        // Apply search on already filtered contractors
        let contractorsToSearch = this.contractors;
        
        // First apply active filters
        if (this.hasActiveFilters()) {
            contractorsToSearch = this.applyFilters(this.contractors);
        }
        
        if (!searchTerm) {
            this.filteredContractors = contractorsToSearch;
        } else {
            this.filteredContractors = contractorsToSearch.filter(contractor => {
                const name = (contractor.company_name || contractor.name || '').toLowerCase();
                const specialty = (contractor.specialty || contractor.contractor_type || '').toLowerCase();
                const location = this.formatLocation(contractor).toLowerCase();
                const contractorType = (contractor.contractor_type || '').toLowerCase();
                
                return name.includes(searchTerm) || 
                       specialty.includes(searchTerm) || 
                       location.includes(searchTerm) ||
                       contractorType.includes(searchTerm);
            });
        }
        
        this.renderContractors();
    }

    async loadContractors() {
        // Load contractors in background and update if API succeeds
        try {
            const response = await fetch(this.apiUrl);
            const data = await response.json();

            if (data.success && data.data && data.data.length > 0) {
                this.contractors = data.data;
                this.filteredContractors = [...this.contractors];
                this.populateFilterOptions();
                this.renderContractors();
            }
            // If API fails or returns no data, keep using mock data already displayed
        } catch (error) {
            console.error('Error loading contractors:', error);
            // Keep using mock data already displayed
        }
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
        if (!this.contractors || this.contractors.length === 0) return;

        // Get unique values
        const contractorTypes = new Set();
        const provinces = new Set();
        const cities = new Set();

        this.contractors.forEach(contractor => {
            const type = contractor.contractor_type || contractor.specialty || '';
            if (type) contractorTypes.add(type);

            if (contractor.province) provinces.add(contractor.province);
            if (contractor.city) cities.add(contractor.city);
        });

        // Populate contractor types
        if (this.filterContractorType) {
            const currentValue = this.filterContractorType.value;
            this.filterContractorType.innerHTML = '<option value="">All Types</option>';
            Array.from(contractorTypes).sort().forEach(type => {
                const option = document.createElement('option');
                option.value = type;
                option.textContent = type;
                this.filterContractorType.appendChild(option);
            });
            if (currentValue) this.filterContractorType.value = currentValue;
        }

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

        this.contractors.forEach(contractor => {
            if (contractor.city) {
                // If province is selected, only show cities in that province
                if (selectedProvince && contractor.province === selectedProvince) {
                    cities.add(contractor.city);
                } else if (!selectedProvince) {
                    cities.add(contractor.city);
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
            contractorType: this.filterContractorType ? this.filterContractorType.value : '',
            province: this.filterProvince ? this.filterProvince.value : '',
            city: this.filterCity ? this.filterCity.value : '',
            rating: this.filterRating ? this.filterRating.value : '',
            experience: this.filterExperience ? this.filterExperience.value : ''
        };

        // Apply filters
        this.filteredContractors = this.applyFilters(this.contractors);
        
        // Update UI
        this.updateFilterBadge();
        this.renderContractors();
        this.closeFilterDropdown();
    }

    applyFilters(contractors) {
        let filtered = [...contractors];

        // Filter by contractor type
        if (this.activeFilters.contractorType) {
            filtered = filtered.filter(contractor => {
                const type = contractor.contractor_type || contractor.specialty || '';
                return type === this.activeFilters.contractorType;
            });
        }

        // Filter by province
        if (this.activeFilters.province) {
            filtered = filtered.filter(contractor => {
                return contractor.province === this.activeFilters.province;
            });
        }

        // Filter by city
        if (this.activeFilters.city) {
            filtered = filtered.filter(contractor => {
                return contractor.city === this.activeFilters.city;
            });
        }

        // Filter by minimum rating
        if (this.activeFilters.rating) {
            const minRating = parseFloat(this.activeFilters.rating);
            filtered = filtered.filter(contractor => {
                const rating = contractor.rating || 0;
                return rating >= minRating;
            });
        }

        // Filter by minimum experience
        if (this.activeFilters.experience) {
            const minExperience = parseInt(this.activeFilters.experience);
            filtered = filtered.filter(contractor => {
                const experience = contractor.years_experience || contractor.experience || 0;
                return experience >= minExperience;
            });
        }

        return filtered;
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

        // Reset filtered contractors
        this.filteredContractors = [...this.contractors];
        
        // Update UI
        this.updateFilterBadge();
        this.renderContractors();
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
    getMockContractors() {
        return [
            {
                id: 1,
                company_name: 'ABC Company',
                years_experience: 123,
                contractor_type: 'Landscaping Contractor',
                street_address: 'asdasda',
                barangay: 'Catacdegan Nuevo',
                city: 'Manabo',
                province: 'Abra',
                postal_code: '2311',
                rating: 5.0,
                reviews_count: 0,
                projects_completed: 0,
                specialty: 'adasd'
            },
            {
                id: 2,
                company_name: "ADAD's Inc.",
                years_experience: 23,
                contractor_type: 'Electrical Contractor',
                street_address: 'adad',
                barangay: 'Singi',
                city: 'Vinzons',
                province: 'Camarines Norte',
                postal_code: '2311',
                rating: 5.0,
                reviews_count: 0,
                projects_completed: 0,
                specialty: 'sdada'
            },
            {
                id: 3,
                company_name: 'BuildRight Construction',
                years_experience: 45,
                contractor_type: 'General Contractor',
                street_address: '123 Main Street',
                barangay: 'Poblacion',
                city: 'Manila',
                province: 'Metro Manila',
                postal_code: '1000',
                rating: 4.8,
                reviews_count: 127,
                projects_completed: 89,
                specialty: 'Residential and Commercial Construction'
            },
            {
                id: 4,
                company_name: 'PlumbPro Services',
                years_experience: 30,
                contractor_type: 'Plumbing Contractor',
                street_address: '456 Oak Avenue',
                barangay: 'San Antonio',
                city: 'Quezon City',
                province: 'Metro Manila',
                postal_code: '1100',
                rating: 4.9,
                reviews_count: 203,
                projects_completed: 156,
                specialty: 'Plumbing Installation and Repair'
            },
            {
                id: 5,
                company_name: 'RoofMaster Solutions',
                years_experience: 67,
                contractor_type: 'Roofing Contractor',
                street_address: '789 Pine Road',
                barangay: 'Makati',
                city: 'Makati',
                province: 'Metro Manila',
                postal_code: '1200',
                rating: 4.7,
                reviews_count: 89,
                projects_completed: 67,
                specialty: 'Roof Installation and Maintenance'
            },
            {
                id: 6,
                company_name: 'PaintWorks Studio',
                years_experience: 15,
                contractor_type: 'Painting Contractor',
                street_address: '321 Elm Street',
                barangay: 'Taguig',
                city: 'Taguig',
                province: 'Metro Manila',
                postal_code: '1630',
                rating: 4.6,
                reviews_count: 145,
                projects_completed: 112,
                specialty: 'Interior and Exterior Painting'
            }
        ];
    }
}

// Initialize when DOM is ready
document.addEventListener('DOMContentLoaded', () => {
    new PropertyOwnerHomepage();
});

