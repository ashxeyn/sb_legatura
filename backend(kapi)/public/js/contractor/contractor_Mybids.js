/**
 * Contractor My Bids JavaScript - Interactive Design
 * Handles tab filtering, status counting, and dynamic bid rendering
 */

class ContractorMyBids {
    constructor() {
        this.bids = [];
        this.filteredBids = [];
        this.currentTabFilter = 'pending';
        this.currentFilter = {
            status: 'all',
            sort: 'newest'
        };
        this.confirmationModal = null;
        this.confirmationOverlay = null;
        this.currentBidToWithdraw = null;
        
        this.init();
    }

    init() {
        // Load bids
        this.loadBids();
        
        // Setup event listeners
        this.setupEventListeners();
        
        // Setup confirmation modal
        this.setupConfirmationModal();
        
        // Update tab badge counts
        this.updateTabBadgeCounts();
        
        // Initial render - start with pending
        this.applyTabFilter('pending');
    }

    loadBids() {
        // Sample bids data - Replace with actual API call
        this.bids = [
            {
                id: 1,
                projectTitle: 'Modern 2-Story Residential House',
                projectType: 'General Contractor',
                description: 'Complete construction of a modern 2-story residential house with 4 bedrooms, 3 bathrooms, living room, kitchen, and outdoor space.',
                location: 'Brgy. Tumaga, Zamboanga City, Zamboanga del Sur',
                projectBudget: '₱2.5M - ₱3M',
                bidAmount: '₱2.85M',
                status: 'pending',
                statusText: 'Pending',
                submittedDate: 'January 15, 2024',
                owner: {
                    name: 'carl_saludo',
                    avatar: 'CS'
                },
                image: 'https://images.unsplash.com/photo-1580587771525-78b9dba3b914?w=300&h=300&fit=crop',
                statusInfo: 'Waiting for owner review',
                specifications: [
                    { icon: 'fi-rr-bed', label: 'Bedrooms', value: '4 Bedrooms' },
                    { icon: 'fi-rr-bath', label: 'Bathrooms', value: '3 Bathrooms' },
                    { icon: 'fi-rr-room-service', label: 'Living Areas', value: '2 Living Rooms' },
                    { icon: 'fi-rr-utensils', label: 'Kitchen', value: '1 Modern Kitchen' }
                ],
                timeline: '6-8 months',
                documents: [
                    { name: 'Project Plans.pdf', type: 'pdf', size: 2456789, url: '#' },
                    { name: 'Requirements.docx', type: 'docx', size: 456789, url: '#' }
                ],
                proposalMessage: 'We are committed to delivering high-quality work within the specified timeline and budget. Our team has extensive experience in residential construction and we look forward to working with you on this project.'
            },
            {
                id: 2,
                projectTitle: 'Office Building Renovation',
                projectType: 'Commercial Contractor',
                description: 'Renovation of a 3-story office building including modern facade and interior updates.',
                location: 'Makati City, Metro Manila',
                projectBudget: '₱5M - ₱6M',
                bidAmount: '₱5.2M',
                status: 'accepted',
                statusText: 'Accepted',
                submittedDate: 'January 20, 2024',
                owner: {
                    name: 'Maria Santos',
                    avatar: 'MS'
                },
                image: 'https://images.unsplash.com/photo-1519974719765-e6559eac2575?w=300&h=300&fit=crop',
                statusInfo: 'Bid accepted - Contract pending',
                specifications: [
                    { icon: 'fi-rr-building', label: 'Floors', value: '3 Stories' },
                    { icon: 'fi-rr-door-open', label: 'Offices', value: '12 Office Spaces' },
                    { icon: 'fi-rr-users', label: 'Capacity', value: '50+ Employees' },
                    { icon: 'fi-rr-car', label: 'Parking', value: '20 Parking Slots' }
                ],
                timeline: '4-6 months',
                documents: [
                    { name: 'Building Plans.pdf', type: 'pdf', size: 3456789, url: '#' },
                    { name: 'Technical Specs.pdf', type: 'pdf', size: 1456789, url: '#' },
                    { name: 'Site Photos.zip', type: 'zip', size: 5456789, url: '#' }
                ],
                proposalMessage: 'Our team specializes in commercial renovations and has successfully completed similar projects. We ensure minimal disruption to business operations during the renovation process.'
            },
            {
                id: 3,
                projectTitle: 'Luxury Villa Construction',
                projectType: 'Residential Contractor',
                description: 'High-end villa with infinity pool, garden, and smart home features.',
                location: 'Tagaytay City, Cavite',
                projectBudget: '₱10M - ₱15M',
                bidAmount: '₱12M',
                status: 'rejected',
                statusText: 'Rejected',
                submittedDate: 'January 10, 2024',
                owner: {
                    name: 'Pedro Garcia',
                    avatar: 'PG'
                },
                image: 'https://images.unsplash.com/photo-1613490493576-7fde63acd811?w=300&h=300&fit=crop',
                statusInfo: 'Owner selected another contractor'
            },
            {
                id: 4,
                projectTitle: 'Shopping Mall Extension',
                projectType: 'Commercial Contractor',
                description: 'Extension of existing shopping mall with new retail spaces and parking area.',
                location: 'Cebu City, Cebu',
                bidAmount: '₱24M',
                status: 'pending',
                statusText: 'Pending',
                submittedDate: 'February 1, 2024',
                owner: {
                    name: 'ABC Corporation',
                    avatar: 'AC'
                },
                image: 'https://images.unsplash.com/photo-1519974719765-e6559eac2575?w=300&h=300&fit=crop',
                statusInfo: 'Under evaluation'
            },
            {
                id: 5,
                projectTitle: 'Residential Complex Phase 1',
                projectType: 'Residential Contractor',
                description: '20-unit townhouse development with modern amenities and green spaces.',
                location: 'Quezon City, Metro Manila',
                projectBudget: '₱40M - ₱50M',
                bidAmount: '₱42M',
                status: 'pending',
                statusText: 'Pending',
                submittedDate: 'February 5, 2024',
                owner: {
                    name: 'Real Estate Dev Co.',
                    avatar: 'RE'
                },
                image: 'https://images.unsplash.com/photo-1560448204-e02f11c3d0e2?w=300&h=300&fit=crop',
                statusInfo: 'Awaiting owner response'
            },
            {
                id: 6,
                projectTitle: 'Beach Resort Development',
                projectType: 'Resort & Hospitality',
                description: 'Construction of a 50-room beach resort with amenities.',
                location: 'Boracay, Aklan',
                projectBudget: '₱80M - ₱100M',
                bidAmount: '₱85M',
                status: 'accepted',
                statusText: 'Accepted',
                submittedDate: 'January 28, 2024',
                owner: {
                    name: 'Island Paradise Inc.',
                    avatar: 'IP'
                },
                image: 'https://images.unsplash.com/photo-1582719508461-905c673771fd?w=300&h=300&fit=crop',
                statusInfo: 'Contract signing scheduled',
                specifications: [
                    { icon: 'fi-rr-bed', label: 'Guest Rooms', value: '50 Rooms' },
                    { icon: 'fi-rr-swimming', label: 'Pool', value: '2 Swimming Pools' },
                    { icon: 'fi-rr-restaurant', label: 'Dining', value: '3 Restaurants' },
                    { icon: 'fi-rr-spa', label: 'Wellness', value: 'Spa & Gym' }
                ],
                timeline: '18-24 months',
                documents: [
                    { name: 'Resort Master Plan.pdf', type: 'pdf', size: 8456789, url: '#' },
                    { name: 'Environmental Study.pdf', type: 'pdf', size: 3456789, url: '#' },
                    { name: 'Architectural Renders.pdf', type: 'pdf', size: 6456789, url: '#' }
                ],
                proposalMessage: 'We have extensive experience in resort construction and understand the unique requirements of beachfront properties. Our team is committed to delivering a world-class facility while respecting the natural environment.'
            },
            {
                id: 7,
                projectTitle: 'Hospital Wing Expansion',
                projectType: 'Healthcare Facility',
                description: 'Construction of a new 5-story hospital wing with modern medical facilities and emergency department.',
                location: 'Davao City, Davao del Sur',
                projectBudget: '₱150M - ₱180M',
                bidAmount: '₱165M',
                status: 'pending',
                statusText: 'Pending',
                submittedDate: 'February 10, 2024',
                owner: {
                    name: 'HealthCare Systems Inc.',
                    avatar: 'HS'
                },
                image: 'https://images.unsplash.com/photo-1587351021759-3e566b6af7cc?w=300&h=300&fit=crop',
                statusInfo: 'Technical evaluation in progress',
                specifications: [
                    { icon: 'fi-rr-building', label: 'Floors', value: '5 Stories' },
                    { icon: 'fi-rr-bed-alt', label: 'Capacity', value: '100 Beds' },
                    { icon: 'fi-rr-ambulance', label: 'Emergency', value: '24/7 ER Department' },
                    { icon: 'fi-rr-lab', label: 'Facilities', value: 'Operating Theaters' }
                ],
                timeline: '24-30 months',
                documents: [
                    { name: 'Medical Facility Requirements.pdf', type: 'pdf', size: 5456789, url: '#' },
                    { name: 'Building Specifications.pdf', type: 'pdf', size: 4456789, url: '#' },
                    { name: 'Safety Compliance Guide.docx', type: 'docx', size: 1256789, url: '#' }
                ],
                proposalMessage: 'Our company has a proven track record in healthcare facility construction. We understand the critical importance of meeting medical standards and ensuring uninterrupted operations during the construction phase.'
            },
            {
                id: 8,
                projectTitle: 'Educational Campus Development',
                projectType: 'Educational Facility',
                description: 'Development of a modern educational campus with classrooms, laboratories, library, and sports facilities.',
                location: 'Baguio City, Benguet',
                projectBudget: '₱200M - ₱250M',
                bidAmount: '₱220M',
                status: 'rejected',
                statusText: 'Rejected',
                submittedDate: 'January 25, 2024',
                owner: {
                    name: 'Education Foundation',
                    avatar: 'EF'
                },
                image: 'https://images.unsplash.com/photo-1562774053-701939374585?w=300&h=300&fit=crop',
                statusInfo: 'Budget constraints - proposal exceeded limits',
                specifications: [
                    { icon: 'fi-rr-graduation-cap', label: 'Classrooms', value: '40 Classrooms' },
                    { icon: 'fi-rr-lab', label: 'Laboratories', value: '8 Science Labs' },
                    { icon: 'fi-rr-book', label: 'Library', value: '3-Level Library' },
                    { icon: 'fi-rr-basketball', label: 'Sports', value: 'Multi-Purpose Gym' }
                ],
                timeline: '20-24 months',
                documents: [
                    { name: 'Campus Master Plan.pdf', type: 'pdf', size: 7456789, url: '#' },
                    { name: 'Educational Requirements.pdf', type: 'pdf', size: 2456789, url: '#' }
                ],
                proposalMessage: 'We specialize in educational facility construction and understand the unique needs of learning environments. Our proposal includes sustainable design features and modern educational technology integration.'
            },
            {
                id: 9,
                projectTitle: 'Industrial Warehouse Complex',
                projectType: 'Industrial Contractor',
                description: 'Large-scale warehouse complex with loading docks, cold storage, and office facilities.',
                location: 'Calamba, Laguna',
                projectBudget: '₱60M - ₱75M',
                bidAmount: '₱68M',
                status: 'pending',
                statusText: 'Pending',
                submittedDate: 'February 8, 2024',
                owner: {
                    name: 'Logistics Corp',
                    avatar: 'LC'
                },
                image: 'https://images.unsplash.com/photo-1586528116311-ad8dd3c8310d?w=300&h=300&fit=crop',
                statusInfo: 'Awaiting site inspection approval',
                specifications: [
                    { icon: 'fi-rr-warehouse-alt', label: 'Storage', value: '50,000 sqm' },
                    { icon: 'fi-rr-truck-loading', label: 'Loading Docks', value: '20 Dock Bays' },
                    { icon: 'fi-rr-snowflake', label: 'Cold Storage', value: '5,000 sqm' },
                    { icon: 'fi-rr-building', label: 'Office', value: '2-Story Admin' }
                ],
                timeline: '12-15 months',
                documents: [
                    { name: 'Warehouse Plans.pdf', type: 'pdf', size: 6456789, url: '#' },
                    { name: 'Structural Design.pdf', type: 'pdf', size: 5456789, url: '#' },
                    { name: 'Fire Safety Plan.pdf', type: 'pdf', size: 2456789, url: '#' }
                ],
                proposalMessage: 'Our team has extensive experience in industrial construction and logistics facilities. We ensure compliance with all safety standards and can deliver this project within the required timeline and budget.'
            },
            {
                id: 10,
                projectTitle: 'Luxury Condominium Tower',
                projectType: 'High-Rise Residential',
                description: 'Construction of a 25-story luxury condominium with premium amenities including sky lounge, infinity pool, and smart home features.',
                location: 'BGC, Taguig City, Metro Manila',
                projectBudget: '₱500M - ₱600M',
                bidAmount: '₱545M',
                status: 'pending',
                statusText: 'Pending',
                submittedDate: 'February 12, 2024',
                owner: {
                    name: 'Premier Properties Inc.',
                    avatar: 'PP'
                },
                image: 'https://images.unsplash.com/photo-1545324418-cc1a3fa10c00?w=300&h=300&fit=crop',
                statusInfo: 'Under technical review',
                specifications: [
                    { icon: 'fi-rr-building', label: 'Floors', value: '25 Stories' },
                    { icon: 'fi-rr-home', label: 'Units', value: '180 Residential Units' },
                    { icon: 'fi-rr-swimming', label: 'Amenities', value: 'Infinity Pool & Sky Lounge' },
                    { icon: 'fi-rr-car', label: 'Parking', value: '250 Parking Slots' }
                ],
                timeline: '36-42 months',
                documents: [
                    { name: 'High-Rise Plans.pdf', type: 'pdf', size: 9456789, url: '#' },
                    { name: 'Structural Engineering.pdf', type: 'pdf', size: 7456789, url: '#' },
                    { name: 'MEP Systems.pdf', type: 'pdf', size: 4456789, url: '#' },
                    { name: 'Amenities Design.pdf', type: 'pdf', size: 3456789, url: '#' }
                ],
                proposalMessage: 'We have a proven track record in high-rise construction with multiple completed condominium projects. Our team specializes in luxury residential developments and employs the latest construction technologies to ensure quality, safety, and timely completion.'
            }
        ];
    }

    setupEventListeners() {
        // Tab filter buttons
        const tabFilterBtns = document.querySelectorAll('.tab-filter-btn');
        tabFilterBtns.forEach(btn => {
            btn.addEventListener('click', () => {
                const filter = btn.getAttribute('data-filter');
                this.handleTabFilter(filter);
            });
        });

        // Filter dropdown
        const filterBtn = document.getElementById('filterBtn');
        if (filterBtn) {
            filterBtn.addEventListener('click', () => this.toggleFilterDropdown());
        }

        const filterCloseBtn = document.getElementById('filterCloseBtn');
        if (filterCloseBtn) {
            filterCloseBtn.addEventListener('click', () => this.closeFilterDropdown());
        }

        // Close filter dropdown when clicking outside
        document.addEventListener('click', (e) => {
            const filterDropdown = document.getElementById('filterDropdown');
            const filterBtn = document.getElementById('filterBtn');
            
            if (filterDropdown && !filterDropdown.contains(e.target) && e.target !== filterBtn && !filterBtn.contains(e.target)) {
                this.closeFilterDropdown();
            }
        });

        // Filter apply button
        const applyFilterBtn = document.getElementById('filterApplyBtn');
        if (applyFilterBtn) {
            applyFilterBtn.addEventListener('click', () => this.applyFilters());
        }

        // Clear filters button
        const clearFiltersBtn = document.getElementById('clearFilters');
        if (clearFiltersBtn) {
            clearFiltersBtn.addEventListener('click', () => this.clearFilters());
        }
    }

    handleTabFilter(filter) {
        this.currentTabFilter = filter;
        
        // Update active tab
        document.querySelectorAll('.tab-filter-btn').forEach(btn => {
            btn.classList.remove('active');
            if (btn.getAttribute('data-filter') === filter) {
                btn.classList.add('active');
            }
        });
        
        this.applyTabFilter(filter);
    }

    applyTabFilter(filter) {
        this.currentTabFilter = filter;
        
        // Filter bids based on tab
        this.filteredBids = this.bids.filter(bid => {
            return bid.status === filter;
        });
        
        // Sort bids
        this.sortBids();
        
        // Render filtered bids
        this.renderBids();
    }

    toggleFilterDropdown() {
        const filterDropdown = document.getElementById('filterDropdown');
        if (filterDropdown) {
            filterDropdown.classList.toggle('active');
        }
    }

    closeFilterDropdown() {
        const filterDropdown = document.getElementById('filterDropdown');
        if (filterDropdown) {
            filterDropdown.classList.remove('active');
        }
    }

    applyFilters() {
        const statusFilter = document.getElementById('statusFilter').value;
        const sortFilter = document.getElementById('sortFilter').value;
        
        this.currentFilter.status = statusFilter;
        this.currentFilter.sort = sortFilter;
        
        // Filter and sort
        this.filterAndSort();
        
        // Close dropdown
        this.closeFilterDropdown();
        
        // Update filter badge
        this.updateFilterBadge();
    }

    clearFilters() {
        document.getElementById('statusFilter').value = 'all';
        document.getElementById('sortFilter').value = 'newest';
        
        this.currentFilter = {
            status: 'all',
            sort: 'newest'
        };
        
        this.filterAndSort();
        this.closeFilterDropdown();
        this.updateFilterBadge();
    }

    filterAndSort() {
        // Start with all bids
        let filtered = [...this.bids];
        
        // Apply tab filter
        if (this.currentTabFilter !== 'all') {
            filtered = filtered.filter(bid => bid.status === this.currentTabFilter);
        }
        
        // Apply status filter (if different from tab filter)
        if (this.currentFilter.status !== 'all') {
            filtered = filtered.filter(bid => bid.status === this.currentFilter.status);
        }
        
        this.filteredBids = filtered;
        
        // Sort
        this.sortBids();
        
        // Render
        this.renderBids();
    }

    sortBids() {
        switch (this.currentFilter.sort) {
            case 'newest':
                this.filteredBids.sort((a, b) => new Date(b.submittedDate) - new Date(a.submittedDate));
                break;
            case 'oldest':
                this.filteredBids.sort((a, b) => new Date(a.submittedDate) - new Date(b.submittedDate));
                break;
            case 'amount_high':
                this.filteredBids.sort((a, b) => {
                    const amountA = parseFloat(a.bidAmount.replace(/[₱,M]/g, ''));
                    const amountB = parseFloat(b.bidAmount.replace(/[₱,M]/g, ''));
                    return amountB - amountA;
                });
                break;
            case 'amount_low':
                this.filteredBids.sort((a, b) => {
                    const amountA = parseFloat(a.bidAmount.replace(/[₱,M]/g, ''));
                    const amountB = parseFloat(b.bidAmount.replace(/[₱,M]/g, ''));
                    return amountA - amountB;
                });
                break;
        }
    }

    updateFilterBadge() {
        const filterBadge = document.getElementById('filterBadge');
        const activeFilters = [];
        
        if (this.currentFilter.status !== 'all') activeFilters.push('status');
        if (this.currentFilter.sort !== 'newest') activeFilters.push('sort');
        
        if (filterBadge) {
            if (activeFilters.length > 0) {
                filterBadge.textContent = activeFilters.length;
                filterBadge.classList.remove('hidden');
            } else {
                filterBadge.classList.add('hidden');
            }
        }
    }

    updateTabBadgeCounts() {
        // Count bids by status
        const counts = {
            pending: 0,
            accepted: 0,
            rejected: 0
        };
        
        this.bids.forEach(bid => {
            if (counts.hasOwnProperty(bid.status)) {
                counts[bid.status]++;
            }
        });
        
        // Update badge elements
        const pendingBadge = document.getElementById('pendingBadge');
        const acceptedBadge = document.getElementById('acceptedBadge');
        const rejectedBadge = document.getElementById('rejectedBadge');
        
        if (pendingBadge) pendingBadge.textContent = counts.pending;
        if (acceptedBadge) acceptedBadge.textContent = counts.accepted;
        if (rejectedBadge) rejectedBadge.textContent = counts.rejected;
    }

    renderBids() {
        const container = document.getElementById('bidsContainer');
        const emptyState = document.getElementById('emptyState');
        const template = document.getElementById('bidCardTemplate');
        
        if (!container || !template) return;
        
        // Clear container
        container.innerHTML = '';
        
        if (this.filteredBids.length === 0) {
            // Show empty state
            if (emptyState) {
                emptyState.classList.remove('hidden');
            }
            return;
        }
        
        // Hide empty state
        if (emptyState) {
            emptyState.classList.add('hidden');
        }
        
        // Render bid cards
        this.filteredBids.forEach(bid => {
            const card = template.content.cloneNode(true);
            
            // Project image
            const bidImage = card.querySelector('.bid-card-image');
            if (bidImage) {
                if (bid.image) {
                    bidImage.src = bid.image;
                    bidImage.alt = bid.projectTitle;
                } else {
                    bidImage.src = 'https://via.placeholder.com/100x100/EEA24B/ffffff?text=' + encodeURIComponent(bid.projectType?.charAt(0) || 'P');
                    bidImage.alt = 'Project placeholder';
                }
            }
            
            // Project type badge
            const projectType = card.querySelector('.project-type');
            if (projectType) {
                projectType.textContent = bid.projectType;
            }
            
            // Status badge
            const statusBadge = card.querySelector('.status-badge');
            const statusText = card.querySelector('.status-text');
            if (statusBadge && statusText) {
                statusBadge.classList.add(`status-${bid.status}`);
                statusText.textContent = bid.statusText;
            }
            
            // Project title and description
            const title = card.querySelector('.project-title');
            const description = card.querySelector('.project-description');
            if (title) title.textContent = bid.projectTitle;
            if (description) description.textContent = bid.description;
            
            // Bid amount
            const bidAmount = card.querySelector('.bid-amount');
            if (bidAmount) {
                bidAmount.textContent = bid.bidAmount;
            }
            
            // Owner info
            const ownerAvatar = card.querySelector('.owner-avatar');
            const ownerName = card.querySelector('.owner-name');
            if (ownerAvatar && ownerName) {
                ownerAvatar.textContent = bid.owner.avatar;
                ownerName.textContent = bid.owner.name;
            }
            
            // Location, budget, and date
            const location = card.querySelector('.project-location');
            const projectBudget = card.querySelector('.project-budget');
            const bidDate = card.querySelector('.bid-date');
            if (location) location.textContent = bid.location;
            if (projectBudget) projectBudget.textContent = `Project Budget: ${bid.projectBudget}`;
            if (bidDate) bidDate.textContent = `Submitted: ${bid.submittedDate}`;
            
            // Status info
            const statusInfo = card.querySelector('.status-info-text');
            if (statusInfo) {
                statusInfo.textContent = bid.statusInfo;
            }
            
            // Action buttons
            const viewDetailsBtn = card.querySelector('.view-details-btn');
            const withdrawBtn = card.querySelector('.withdraw-btn');
            
            if (viewDetailsBtn) {
                viewDetailsBtn.addEventListener('click', () => this.handleViewDetails(bid));
            }
            
            // Show withdraw button only for pending bids
            if (withdrawBtn && bid.status === 'pending') {
                withdrawBtn.classList.remove('hidden');
                withdrawBtn.addEventListener('click', () => this.handleWithdraw(bid));
            }
            
            container.appendChild(card);
        });
    }

    handleViewDetails(bid) {
        console.log('View bid details:', bid);
        
        // Open bid details modal
        if (window.openBidDetailsModal) {
            window.openBidDetailsModal(bid);
        } else {
            console.error('Bid Details Modal not available');
            this.showNotification('Unable to open bid details', 'error');
        }
    }

    setupConfirmationModal() {
        this.confirmationModal = document.getElementById('withdrawConfirmationModal');
        this.confirmationOverlay = document.getElementById('withdrawConfirmationOverlay');
        
        // Cancel withdraw button
        const cancelWithdrawBtn = document.getElementById('cancelWithdrawBtn');
        if (cancelWithdrawBtn) {
            cancelWithdrawBtn.addEventListener('click', () => this.closeWithdrawConfirmation());
        }
        
        // Confirm withdraw button
        const confirmWithdrawBtn = document.getElementById('confirmWithdrawBtn');
        if (confirmWithdrawBtn) {
            confirmWithdrawBtn.addEventListener('click', () => this.confirmWithdraw());
        }
        
        // Overlay click to close
        if (this.confirmationOverlay) {
            this.confirmationOverlay.addEventListener('click', () => this.closeWithdrawConfirmation());
        }
        
        // Escape key to close
        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape' && this.isConfirmationOpen()) {
                this.closeWithdrawConfirmation();
            }
        });
    }

    handleWithdraw(bid) {
        console.log('Withdraw bid:', bid);
        this.currentBidToWithdraw = bid;
        this.showWithdrawConfirmation(bid);
    }

    showWithdrawConfirmation(bid) {
        if (!bid) return;

        // Populate confirmation modal with bid details
        const projectTitle = document.getElementById('confirmWithdrawProjectTitle');
        const bidAmount = document.getElementById('confirmWithdrawBidAmount');
        
        if (projectTitle) {
            projectTitle.textContent = bid.projectTitle || 'Untitled Project';
        }
        
        if (bidAmount) {
            bidAmount.textContent = bid.bidAmount || 'N/A';
        }

        // Show confirmation modal
        if (this.confirmationModal) {
            this.confirmationModal.classList.add('active');
        }
    }

    closeWithdrawConfirmation() {
        if (this.confirmationModal) {
            this.confirmationModal.classList.remove('active');
        }
        this.currentBidToWithdraw = null;
    }

    isConfirmationOpen() {
        return this.confirmationModal && this.confirmationModal.classList.contains('active');
    }

    confirmWithdraw() {
        if (!this.currentBidToWithdraw) return;

        console.log('Withdrawing bid:', this.currentBidToWithdraw.id);
        
        // Close confirmation modal
        this.closeWithdrawConfirmation();
        
        // Show success notification
        this.showNotification(`Bid #${this.currentBidToWithdraw.id} withdrawn successfully`, 'success');
        
        // TODO: Implement actual API call to withdraw bid
        // Example:
        // fetch(`/api/bids/${this.currentBidToWithdraw.id}/withdraw`, { method: 'POST' })
        //     .then(response => response.json())
        //     .then(data => {
        //         if (data.success) {
        //             // Update bid status to withdrawn
        //             this.currentBidToWithdraw.status = 'withdrawn';
        //             this.currentBidToWithdraw.statusText = 'Withdrawn';
        //             // Refresh the view
        //             this.applyTabFilter(this.currentTabFilter);
        //             this.updateTabBadgeCounts();
        //         }
        //     });
    }

    showNotification(message, type = 'info') {
        const toast = document.createElement('div');
        toast.className = 'fixed bottom-4 right-4 px-6 py-3 rounded-lg shadow-lg z-50 text-white';
        
        // Set color based on type
        if (type === 'success') {
            toast.classList.add('bg-green-500');
        } else if (type === 'error') {
            toast.classList.add('bg-red-500');
        } else {
            toast.classList.add('bg-orange-500');
        }
        
        toast.textContent = message;
        toast.style.animation = 'slideUp 0.3s ease-out';
        
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
}

// Initialize when DOM is ready
document.addEventListener('DOMContentLoaded', () => {
    new ContractorMyBids();
});
