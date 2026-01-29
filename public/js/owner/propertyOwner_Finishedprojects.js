/**
 * Property Owner Finished Projects JavaScript
 * Handles finished/completed projects listing and interactions
 */

class PropertyOwnerFinishedProjects {
    constructor() {
        this.projects = [];
        this.filteredProjects = [];
        this.currentFilters = {
            sort: 'newest',
            search: ''
        };
        
        this.init();
    }

    init() {
        // Load sample projects data (only completed projects)
        this.loadProjects();
        
        // Setup event listeners
        this.setupEventListeners();
        
        // Setup navbar search
        this.setupNavbarSearch();
        
        // Render projects
        this.renderProjects();
    }

    loadProjects() {
        // Sample project data - Replace with actual API call
        // Only include completed projects
        this.projects = [
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
                id: 7,
                title: "Luxury Villa Construction",
                type: "General Contractor",
                description: "A stunning 4-bedroom luxury villa with infinity pool, modern architecture, and premium finishes. Features include smart home technology, home theater, and landscaped gardens. The design showcases contemporary elegance with sustainable building practices.",
                image: "https://images.unsplash.com/photo-1600585154340-be6161a56a0c?w=400&h=300&fit=crop",
                status: "completed",
                contractor: {
                    name: "Dela Cruz Construction Co.",
                    company: "Dela Cruz Construction Co.",
                    role: "General Contractor",
                    rating: 4.8,
                    initials: "DC"
                },
                location: "Upper Calarian, Zamboanga City",
                budget: "₱8,000,000 - ₱10,000,000",
                date: "Completed: Nov 15, 2023",
                progress: 100,
                deadline: "2023-11-15",
                lotSize: "500 sqm",
                floorArea: "380 sqm",
                bedrooms: 4,
                bathrooms: 3,
                floors: 2,
                materials: "Concrete, Steel, Glass, Premium Finishes",
                style: "Luxury Contemporary",
                agreementDate: "Jan 5, 2023",
                agreementStatus: "Completed",
                milestones: {
                    total: 1,
                    pendingApproval: 0,
                    totalCost: "₱9,000,000"
                },
                totalMilestones: 1,
                pendingApproval: 0,
                totalCost: "₱9,000,000"
            },
            {
                id: 8,
                title: "Office Building Renovation",
                type: "Commercial Contractor",
                description: "Complete renovation of a 5-story office building. Modernized all floors with new electrical systems, HVAC, elevators, and contemporary office layouts. Includes collaborative spaces, private offices, and state-of-the-art technology integration.",
                image: "https://images.unsplash.com/photo-1497366216548-37526070297c?w=400&h=300&fit=crop",
                status: "completed",
                contractor: {
                    name: "Garcia Commercial Builders Inc.",
                    company: "Garcia Commercial Builders Inc.",
                    role: "Commercial Contractor",
                    rating: 4.7,
                    initials: "GC"
                },
                location: "Baliwasan Grande, Zamboanga City",
                budget: "₱12,000,000 - ₱15,000,000",
                date: "Completed: Oct 10, 2023",
                progress: 100,
                deadline: "2023-10-10",
                lotSize: "800 sqm",
                floorArea: "3,500 sqm",
                floors: 5,
                materials: "Steel Frame, Glass, Acoustic Panels, Modern HVAC",
                style: "Modern Industrial",
                agreementDate: "Mar 1, 2023",
                agreementStatus: "Completed",
                milestones: {
                    total: 1,
                    pendingApproval: 0,
                    totalCost: "₱13,500,000"
                },
                totalMilestones: 1,
                pendingApproval: 0,
                totalCost: "₱13,500,000"
            },
            {
                id: 9,
                title: "Modern Kitchen Renovation",
                type: "Interior Designer",
                description: "Complete kitchen transformation with custom cabinets, quartz countertops, high-end appliances, and modern lighting. Includes new plumbing and electrical work. Features custom cabinetry, quartz countertops, and energy-efficient appliances.",
                image: "https://crystelmontenegrohome.com/wp-content/uploads/2024/03/Screenshot-2024-03-07-at-10.42.16%E2%80%AFAM-2.jpg",
                status: "completed",
                contractor: {
                    name: "Santos Interior Design Studio",
                    company: "Santos Interior Design Studio",
                    role: "Interior Designer",
                    rating: 4.9,
                    initials: "SI"
                },
                location: "Tumaga, Zamboanga City",
                budget: "₱1,200,000 - ₱1,500,000",
                date: "Completed: Sep 25, 2023",
                progress: 100,
                deadline: "2023-09-25",
                lotSize: "N/A",
                floorArea: "28 sqm",
                materials: "Quartz, Hardwood, Stainless Steel",
                style: "Modern Minimalist",
                agreementDate: "Jul 10, 2023",
                agreementStatus: "Completed",
                milestones: {
                    total: 1,
                    pendingApproval: 0,
                    totalCost: "₱1,350,000"
                },
                totalMilestones: 1,
                pendingApproval: 0,
                totalCost: "₱1,350,000"
            },
            {
                id: 10,
                title: "Residential Fence Installation",
                type: "General Contractor",
                description: "Installation of decorative concrete fence with metal gates around residential property. Includes landscaping and gate automation system. Features decorative concrete panels, automated gates, and perimeter lighting.",
                image: "https://images.unsplash.com/photo-1416879595882-3373a0480b5b?w=400&h=300&fit=crop",
                status: "completed",
                contractor: {
                    name: "Rodriguez Construction",
                    company: "Rodriguez Construction",
                    role: "General Contractor",
                    rating: 4.5,
                    initials: "RC"
                },
                location: "Malagutay, Zamboanga City",
                budget: "₱450,000 - ₱600,000",
                date: "Completed: Aug 18, 2023",
                progress: 100,
                deadline: "2023-08-18",
                lotSize: "300 sqm",
                floorArea: "N/A",
                materials: "Concrete, Steel, Automation Systems",
                style: "Modern Security",
                agreementDate: "Jun 1, 2023",
                agreementStatus: "Completed",
                milestones: {
                    total: 1,
                    pendingApproval: 0,
                    totalCost: "₱525,000"
                },
                totalMilestones: 1,
                pendingApproval: 0,
                totalCost: "₱525,000"
            },
            {
                id: 11,
                title: "Swimming Pool Construction",
                type: "General Contractor",
                description: "Construction of an Olympic-sized swimming pool with modern filtration system, LED lighting, and surrounding deck area. Includes pool house and changing rooms. Features state-of-the-art filtration, LED lighting system, and premium decking materials.",
                image: "https://images.unsplash.com/photo-1600596542815-ffad4c1539a9?w=400&h=300&fit=crop",
                status: "completed",
                contractor: {
                    name: "Aqua Builders Inc.",
                    company: "Aqua Builders Inc.",
                    role: "Pool Specialist",
                    rating: 4.7,
                    initials: "AB"
                },
                location: "Upper Calarian, Zamboanga City",
                budget: "₱2,000,000 - ₱2,800,000",
                date: "Completed: Jul 5, 2023",
                progress: 100,
                deadline: "2023-07-05",
                lotSize: "200 sqm",
                floorArea: "N/A",
                materials: "Concrete, Tile, LED Systems, Premium Decking",
                style: "Luxury Resort",
                agreementDate: "Apr 15, 2023",
                agreementStatus: "Completed",
                milestones: {
                    total: 1,
                    pendingApproval: 0,
                    totalCost: "₱2,400,000"
                },
                totalMilestones: 1,
                pendingApproval: 0,
                totalCost: "₱2,400,000"
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
                const sortFilter = document.getElementById('sortFilter');
                
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
            navbarSearchInput.placeholder = 'Search finished projects...';
            
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

        // Apply sorting
        filtered.sort((a, b) => {
            switch (this.currentFilters.sort) {
                case 'oldest':
                    return new Date(a.date) - new Date(b.date);
                case 'title':
                    return a.title.localeCompare(b.title);
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
            sort: 'newest',
            search: ''
        };

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
                viewDetailsBtn.addEventListener('click', () => {
                    this.viewProjectDetails(project.id);
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
    new PropertyOwnerFinishedProjects();
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
