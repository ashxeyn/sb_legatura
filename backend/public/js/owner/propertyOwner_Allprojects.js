/**
 * Property Owner All Projects JavaScript
 * Handles project listing, filtering, and interactions
 */

class PropertyOwnerAllProjects {
    constructor() {
        this.projects = [];
        this.filteredProjects = [];
        this.currentFilters = {
            status: 'all',
            sort: 'newest',
            search: ''
        };
        this.isPinMode = false;
        
        this.init();
    }

    init() {
        // Load sample projects data
        this.loadProjects();
        
        // Setup event listeners
        this.setupEventListeners();
        
        // Setup navbar search
        this.setupNavbarSearch();
        
        // Check if in pin mode
        this.checkPinMode();
        
        // Render projects
        this.renderProjects();
    }

    checkPinMode() {
        // Check if URL has action=pin parameter
        const urlParams = new URLSearchParams(window.location.search);
        this.isPinMode = urlParams.get('action') === 'pin';
        
        if (this.isPinMode) {
            // Show notification
            this.showNotification('Select a project to pin to your dashboard');
            
            // Update page title or add indicator
            const header = document.querySelector('.section-header h1');
            if (header) {
                const originalText = header.textContent;
                header.innerHTML = `<i class="fi fi-rr-bookmark" style="color: #EEA24B; margin-right: 0.5rem;"></i>${originalText} - Select to Pin`;
            }
            
            // Add visual indicator banner
            this.addPinModeBanner();
        }
    }

    addPinModeBanner() {
        // Remove existing banner if any
        const existingBanner = document.getElementById('pinModeBanner');
        if (existingBanner) {
            existingBanner.remove();
        }
        
        // Create banner
        const banner = document.createElement('div');
        banner.id = 'pinModeBanner';
        banner.className = 'pin-mode-banner';
        banner.innerHTML = `
            <div class="pin-mode-banner-content">
                <i class="fi fi-rr-bookmark"></i>
                <span>Pin Mode: Click "Pin Project" on any project card to pin it to your dashboard</span>
                <button class="pin-mode-close" onclick="this.parentElement.parentElement.remove()">
                    <i class="fi fi-rr-cross-small"></i>
                </button>
            </div>
        `;
        
        // Insert after the header section
        const headerSection = document.querySelector('.allprojects-header');
        const projectsSection = document.querySelector('.max-w-7xl');
        if (headerSection) {
            headerSection.insertAdjacentElement('afterend', banner);
        } else if (projectsSection) {
            projectsSection.insertAdjacentElement('beforebegin', banner);
        }
    }

    loadProjects() {
        // Sample project data - Replace with actual API call
        this.projects = [
            {
                id: 1,
                title: "Modern Residential House Construction",
                type: "General Contractor",
                description: "A beautiful 3-bedroom, 2-bathroom modern house with open floor plan, large windows, and sustainable materials. Includes full kitchen, living room, and attached garage. The design features contemporary architecture with energy-efficient systems and smart home integration.",
                image: "https://images.unsplash.com/photo-1600596542815-ffad4c1539a9?w=400&h=300&fit=crop",
                status: "in_progress",
                contractor: {
                    name: "Dela Cruz Construction Co.",
                    company: "Dela Cruz Construction Co.",
                    role: "General Contractor",
                    rating: 4.8,
                    initials: "DC"
                },
                location: "Tumaga, Zamboanga City",
                budget: "₱2,500,000 - ₱3,000,000",
                date: "Started: Jan 15, 2024",
                progress: 65,
                deadline: "2024-06-30",
                lotSize: "250 sqm",
                floorArea: "180 sqm",
                bedrooms: 3,
                bathrooms: 2,
                floors: 2,
                materials: "Concrete, Steel, Glass",
                style: "Modern Contemporary",
                agreementDate: "Jan 10, 2024",
                agreementStatus: "Active",
                milestones: {
                    total: 1,
                    pendingApproval: 0,
                    totalCost: "₱2,750,000"
                },
                totalMilestones: 1,
                pendingApproval: 0,
                totalCost: "₱2,750,000"
            },
            {
                id: 2,
                title: "Kitchen Renovation Project",
                type: "Interior Designer",
                description: "Complete kitchen makeover with new cabinets, countertops, appliances, and modern fixtures. Includes plumbing and electrical work. Features custom cabinetry, quartz countertops, and energy-efficient appliances.",
                image: "https://crystelmontenegrohome.com/wp-content/uploads/2024/03/Screenshot-2024-03-07-at-10.42.16%E2%80%AFAM-2.jpg",
                status: "active",
                contractor: {
                    name: "Santos Interior Design Studio",
                    company: "Santos Interior Design Studio",
                    role: "Interior Designer",
                    rating: 4.9,
                    initials: "SI"
                },
                location: "Malagutay, Zamboanga City",
                budget: "₱800,000 - ₱1,200,000",
                date: "Started: Feb 1, 2024",
                progress: 35,
                deadline: "2024-05-15",
                lotSize: "N/A",
                floorArea: "25 sqm",
                materials: "Quartz, Hardwood, Stainless Steel",
                style: "Modern Minimalist",
                agreementDate: "Jan 28, 2024",
                agreementStatus: "Active",
                milestones: {
                    total: 1,
                    pendingApproval: 0,
                    totalCost: "₱1,000,000"
                },
                totalMilestones: 1,
                pendingApproval: 0,
                totalCost: "₱1,000,000"
            },
            {
                id: 3,
                title: "Commercial Office Space Build-out",
                type: "Commercial Contractor",
                description: "Complete office space renovation for a tech company. Includes open workspace, meeting rooms, break area, and modern amenities. Features collaborative spaces, private offices, and state-of-the-art technology integration.",
                image: "https://images.unsplash.com/photo-1497366216548-37526070297c?w=400&h=300&fit=crop",
                status: "pending",
                contractor: {
                    name: "Garcia Commercial Builders Inc.",
                    company: "Garcia Commercial Builders Inc.",
                    role: "Commercial Contractor",
                    rating: 4.7,
                    initials: "GC"
                },
                location: "Baliwasan Grande, Zamboanga City",
                budget: "₱5,000,000 - ₱7,000,000",
                date: "Posted: Mar 10, 2024",
                progress: 0,
                deadline: "2024-08-30",
                lotSize: "500 sqm",
                floorArea: "450 sqm",
                floors: 1,
                materials: "Steel Frame, Glass, Acoustic Panels",
                style: "Modern Industrial",
                agreementDate: "Pending",
                agreementStatus: "Pending",
                milestones: {
                    total: 1,
                    pendingApproval: 1,
                    totalCost: "₱6,000,000"
                },
                totalMilestones: 1,
                pendingApproval: 1,
                totalCost: "₱6,000,000"
            },
            {
                id: 4,
                title: "Bathroom Remodeling",
                type: "Plumber",
                description: "Full bathroom renovation including new tiles, fixtures, bathtub, shower, vanity, and lighting. Modern design with premium materials. Features walk-in shower, freestanding bathtub, and double vanity.",
                image: "https://images.unsplash.com/photo-1620626011761-996317b8d101?w=400&h=300&fit=crop",
                status: "completed",
                contractor: {
                    name: "Mendoza Plumbing Services",
                    company: "Mendoza Plumbing Services",
                    role: "Master Plumber",
                    rating: 4.6,
                    initials: "MP"
                },
                location: "Malagutay, Zamboanga City",
                budget: "₱350,000 - ₱500,000",
                date: "Completed: Dec 20, 2023",
                progress: 100,
                deadline: "2023-12-20",
                lotSize: "N/A",
                floorArea: "12 sqm",
                materials: "Porcelain Tiles, Chrome Fixtures, Marble",
                style: "Luxury Modern",
                agreementDate: "Nov 15, 2023",
                agreementStatus: "Completed",
                milestones: {
                    total: 1,
                    pendingApproval: 0,
                    totalCost: "₱425,000"
                },
                totalMilestones: 1,
                pendingApproval: 0,
                totalCost: "₱425,000"
            },
            {
                id: 5,
                title: "Garden Landscaping & Hardscaping",
                type: "Landscape Architect",
                description: "Complete garden transformation with native plants, stone pathways, water feature, outdoor seating area, and garden lighting. Includes irrigation system and sustainable landscaping practices.",
                image: "https://images.unsplash.com/photo-1416879595882-3373a0480b5b?w=400&h=300&fit=crop",
                status: "in_progress",
                contractor: {
                    name: "Rodriguez Landscape Design",
                    company: "Rodriguez Landscape Design",
                    role: "Landscape Architect",
                    rating: 4.9,
                    initials: "RL"
                },
                location: "Malagutay, Zamboanga City",
                budget: "₱1,200,000 - ₱1,800,000",
                date: "Started: Feb 20, 2024",
                progress: 45,
                deadline: "2024-07-15",
                lotSize: "300 sqm",
                floorArea: "N/A",
                materials: "Natural Stone, Native Plants, LED Lighting",
                style: "Tropical Modern",
                agreementDate: "Feb 15, 2024",
                agreementStatus: "Active",
                milestones: {
                    total: 1,
                    pendingApproval: 0,
                    totalCost: "₱1,500,000"
                },
                totalMilestones: 1,
                pendingApproval: 0,
                totalCost: "₱1,500,000"
            },
            {
                id: 6,
                title: "Modern Residential House Construction",
                type: "General Contractor",
                description: "A beautiful 3-bedroom, 2-bathroom modern house with open floor plan, large windows, and sustainable materials. Includes full kitchen, living room, and attached garage. Features solar panels and rainwater harvesting system.",
                image: "https://images.unsplash.com/photo-1600585154340-be6161a56a0c?w=400&h=300&fit=crop",
                status: "in_progress",
                contractor: {
                    name: "Dela Cruz Construction Co.",
                    company: "Dela Cruz Construction Co.",
                    role: "General Contractor",
                    rating: 4.8,
                    initials: "DC"
                },
                location: "Upper Calarian, Zamboanga City",
                budget: "₱2,500,000 - ₱3,000,000",
                date: "Started: Jan 15, 2024",
                progress: 65,
                deadline: "2024-06-30",
                lotSize: "280 sqm",
                floorArea: "200 sqm",
                bedrooms: 3,
                bathrooms: 2,
                floors: 2,
                materials: "Concrete, Steel, Glass, Solar Panels",
                style: "Eco-Modern",
                agreementDate: "Jan 10, 2024",
                agreementStatus: "Active",
                milestones: {
                    total: 1,
                    pendingApproval: 0,
                    totalCost: "₱2,750,000"
                },
                totalMilestones: 1,
                pendingApproval: 0,
                totalCost: "₱2,750,000"
            }
        ];
        
        this.filteredProjects = [...this.projects];
    }

    setupEventListeners() {
        // Filter button
        const filterBtn = document.getElementById('filterBtn');
        const filterDropdown = document.getElementById('filterDropdown');
        const filterCloseBtn = document.getElementById('filterCloseBtn');
        const filterApplyBtn = document.getElementById('filterApplyBtn');
        
        if (filterBtn && filterDropdown) {
            filterBtn.addEventListener('click', (e) => {
                e.stopPropagation();
                filterDropdown.classList.toggle('active');
                filterBtn.classList.toggle('active');
            });
        }

        // Close filter button
        if (filterCloseBtn && filterDropdown) {
            filterCloseBtn.addEventListener('click', () => {
                filterDropdown.classList.remove('active');
                filterBtn.classList.remove('active');
            });
        }

        // Apply filter button
        if (filterApplyBtn) {
            filterApplyBtn.addEventListener('click', () => {
                // Read current filter values from dropdowns
                const statusFilter = document.getElementById('statusFilter');
                const sortFilter = document.getElementById('sortFilter');
                
                if (statusFilter) {
                    this.currentFilters.status = statusFilter.value;
                }
                if (sortFilter) {
                    this.currentFilters.sort = sortFilter.value;
                }
                
                this.applyFilters();
                filterDropdown.classList.remove('active');
                filterBtn.classList.remove('active');
            });
        }

        // Clear filters
        const clearFilters = document.getElementById('clearFilters');
        if (clearFilters) {
            clearFilters.addEventListener('click', () => {
                this.clearFilters();
            });
        }

        // Close filter dropdown when clicking outside
        document.addEventListener('click', (e) => {
            if (filterDropdown && filterBtn) {
                if (!filterDropdown.contains(e.target) && 
                    e.target !== filterBtn && !filterBtn.contains(e.target)) {
                    filterDropdown.classList.remove('active');
                    filterBtn.classList.remove('active');
                }
            }
        });
    }

    setupNavbarSearch() {
        // Get the navbar search input
        const navbarSearchInput = document.querySelector('.navbar-search-input');
        const navbarSearchButton = document.querySelector('.navbar-search-btn');
        
        if (navbarSearchInput) {
            // Update placeholder
            navbarSearchInput.placeholder = 'Search projects...';
            
            // Search on input
            navbarSearchInput.addEventListener('input', (e) => {
                this.currentFilters.search = e.target.value.toLowerCase().trim();
                this.applyFilters();
            });

            // Search on Enter key
            navbarSearchInput.addEventListener('keypress', (e) => {
                if (e.key === 'Enter') {
                    e.preventDefault();
                    this.currentFilters.search = e.target.value.toLowerCase().trim();
                    this.applyFilters();
                }
            });
        }

        if (navbarSearchButton) {
            // Search on button click
            navbarSearchButton.addEventListener('click', () => {
                if (navbarSearchInput) {
                    this.currentFilters.search = navbarSearchInput.value.toLowerCase().trim();
                    this.applyFilters();
                }
            });
        }
    }

    applyFilters() {
        let filtered = [...this.projects];

        // Apply search filter
        if (this.currentFilters.search) {
            filtered = filtered.filter(project => 
                project.title.toLowerCase().includes(this.currentFilters.search) ||
                project.type.toLowerCase().includes(this.currentFilters.search) ||
                project.location.toLowerCase().includes(this.currentFilters.search) ||
                project.contractor.name.toLowerCase().includes(this.currentFilters.search) ||
                project.description.toLowerCase().includes(this.currentFilters.search)
            );
        }

        // Apply status filter
        if (this.currentFilters.status !== 'all') {
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
    }

    clearFilters() {
        this.currentFilters = {
            status: 'all',
            sort: 'newest',
            search: ''
        };

        document.getElementById('statusFilter').value = 'all';
        document.getElementById('sortFilter').value = 'newest';
        
        // Clear navbar search
        const navbarSearchInput = document.querySelector('.navbar-search-input');
        if (navbarSearchInput) {
            navbarSearchInput.value = '';
        }

        this.applyFilters();
    }

    renderProjects() {
        const container = document.getElementById('projectsContainer');
        const emptyState = document.getElementById('emptyState');
        const template = document.getElementById('projectCardTemplate');

        if (!container || !template) return;

        // Clear container
        container.innerHTML = '';

        if (this.filteredProjects.length === 0) {
            container.classList.add('hidden');
            if (emptyState) {
                emptyState.classList.remove('hidden');
            }
            return;
        }

        container.classList.remove('hidden');
        if (emptyState) {
            emptyState.classList.add('hidden');
        }

        // Render each project
        this.filteredProjects.forEach(project => {
            const card = template.content.cloneNode(true);
            const cardElement = card.querySelector('.project-card');

            // Set project data
            cardElement.querySelector('.project-title').textContent = project.title;
            cardElement.querySelector('.project-type').textContent = project.type;
            cardElement.querySelector('.project-description').textContent = project.description;
            
            // Set project image
            const projectImage = cardElement.querySelector('.project-image');
            if (projectImage) {
                if (project.image) {
                    projectImage.src = project.image;
                    projectImage.alt = project.title || 'Project image';
                } else {
                    // Use placeholder if no image
                    projectImage.src = 'https://via.placeholder.com/120x120/EEA24B/ffffff?text=Project';
                    projectImage.alt = project.title || 'Project image';
                }
            }
            
            cardElement.querySelector('.project-location').textContent = project.location;
            cardElement.querySelector('.project-budget').textContent = project.budget;
            cardElement.querySelector('.project-date').textContent = project.date;
            
            // Contractor info
            const companyName = project.contractor.company || project.contractor.name;
            cardElement.querySelector('.contractor-name').textContent = companyName;
            cardElement.querySelector('.contractor-role').textContent = project.contractor.role;
            cardElement.querySelector('.contractor-avatar').textContent = project.contractor.initials;
            cardElement.querySelector('.rating-value').textContent = project.contractor.rating;

            // Status badge
            const statusBadge = cardElement.querySelector('.status-badge');
            const statusText = cardElement.querySelector('.status-text');
            statusBadge.className = `status-badge status-${project.status} px-3 py-1 rounded-full text-xs font-semibold flex items-center gap-1.5 whitespace-nowrap`;
            statusText.textContent = project.status.replace('_', ' ');

            // Progress bar
            const progressBar = cardElement.querySelector('.progress-bar');
            const progressPercentage = cardElement.querySelector('.progress-percentage');
            progressBar.style.width = `${project.progress}%`;
            progressPercentage.textContent = `${project.progress}%`;

            // Add click handlers
            const viewDetailsBtn = cardElement.querySelector('.view-details-btn');
            if (viewDetailsBtn) {
                viewDetailsBtn.addEventListener('click', (e) => {
                    e.stopPropagation();
                    this.viewProjectDetails(project.id);
                });
            }

            // Add pin button handler
            const pinBtn = cardElement.querySelector('.pin-project-btn');
            if (pinBtn) {
                // Show pin button if in pin mode
                if (this.isPinMode) {
                    pinBtn.classList.remove('hidden');
                    // Adjust view details button to be smaller
                    if (viewDetailsBtn) {
                        viewDetailsBtn.classList.remove('hidden');
                        viewDetailsBtn.style.flex = '1';
                    }
                } else {
                    // Hide pin button in normal mode
                    pinBtn.classList.add('hidden');
                    if (viewDetailsBtn) {
                        viewDetailsBtn.style.flex = '1';
                    }
                }
                
                pinBtn.addEventListener('click', (e) => {
                    e.stopPropagation();
                    this.pinProject(project);
                });
            }

            // Add hover effect
            cardElement.addEventListener('mouseenter', () => {
                cardElement.style.transform = 'translateY(-4px)';
            });

            cardElement.addEventListener('mouseleave', () => {
                cardElement.style.transform = 'translateY(0)';
            });

            container.appendChild(card);
        });

        // Animate progress bars
        setTimeout(() => {
            document.querySelectorAll('.progress-bar').forEach(bar => {
                bar.style.transition = 'width 1s ease-out';
            });
        }, 100);
    }

    viewProjectDetails(projectId) {
        const project = this.projects.find(p => p.id === projectId);
        if (project) {
            // Open the project details modal
            if (window.openProjectDetailsModal) {
                window.openProjectDetailsModal(project);
            } else {
                // Fallback if modal hasn't loaded yet
                console.log('Opening project details modal:', project);
                setTimeout(() => {
                    if (window.openProjectDetailsModal) {
                        window.openProjectDetailsModal(project);
                    }
                }, 100);
            }
        }
    }

    showProjectOptions(projectId) {
        const project = this.projects.find(p => p.id === projectId);
        if (project) {
            // Show notification
            this.showNotification(`Options for: ${project.title}`);
            
            // In a real implementation, show a dropdown menu with options
            // (Edit, Delete, Pin, etc.)
            console.log('Show project options:', project);
        }
    }

    pinProject(project) {
        // Prepare project data
        const pinnedProject = {
            id: project.id,
            title: project.title,
            type: project.type,
            description: project.description,
            image: project.image,
            location: project.location,
            budget: project.budget,
            date: project.date,
            status: project.status,
            progress: project.progress,
            contractor: project.contractor,
            pinnedAt: new Date().toISOString() // Track when it was pinned
        };
        
        // Load existing pinned projects
        let pinnedProjects = [];
        const savedPinnedProjects = localStorage.getItem('pinnedProjects');
        if (savedPinnedProjects) {
            try {
                pinnedProjects = JSON.parse(savedPinnedProjects);
            } catch (e) {
                console.error('Error loading pinned projects:', e);
                // Try to migrate old single pinned project
                const oldPinned = localStorage.getItem('pinnedProject');
                if (oldPinned) {
                    try {
                        pinnedProjects = [JSON.parse(oldPinned)];
                        localStorage.removeItem('pinnedProject');
                    } catch (e2) {
                        console.error('Error migrating old pinned project:', e2);
                    }
                }
            }
        }
        
        // Check if project is already pinned
        const existingIndex = pinnedProjects.findIndex(p => p.id === project.id);
        if (existingIndex !== -1) {
            // Remove existing and add to top
            pinnedProjects.splice(existingIndex, 1);
        }
        
        // Add to the beginning of the array (top of list)
        pinnedProjects.unshift(pinnedProject);
        
        // Save to localStorage
        localStorage.setItem('pinnedProjects', JSON.stringify(pinnedProjects));
        
        // Show success notification
        this.showNotification(`Project "${project.title}" pinned successfully!`);
        
        // Redirect back to dashboard after a short delay
        setTimeout(() => {
            const dashboardUrl = window.location.origin + '/owner/dashboard';
            window.location.href = dashboardUrl;
        }, 1500);
    }

    showNotification(message) {
        // Create a simple toast notification
        const toast = document.createElement('div');
        toast.className = 'fixed bottom-4 right-4 bg-orange-500 text-white px-6 py-3 rounded-lg shadow-lg z-50 animate-slide-up';
        toast.textContent = message;
        toast.style.cssText = `
            animation: slideUp 0.3s ease-out;
        `;

        document.body.appendChild(toast);

        setTimeout(() => {
            toast.style.animation = 'slideDown 0.3s ease-out';
            setTimeout(() => {
                document.body.removeChild(toast);
            }, 300);
        }, 3000);
    }
}

// Initialize when DOM is ready
document.addEventListener('DOMContentLoaded', () => {
    new PropertyOwnerAllProjects();
});

// Add CSS animations
const style = document.createElement('style');
style.textContent = `
    @keyframes slideUp {
        from {
            transform: translateY(100%);
            opacity: 0;
        }
        to {
            transform: translateY(0);
            opacity: 1;
        }
    }
    
    @keyframes slideDown {
        from {
            transform: translateY(0);
            opacity: 1;
        }
        to {
            transform: translateY(100%);
            opacity: 0;
        }
    }
`;
document.head.appendChild(style);

