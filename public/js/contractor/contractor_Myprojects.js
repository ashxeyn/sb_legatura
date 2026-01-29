/**
 * Contractor My Projects JavaScript - Interactive Design
 * Handles tab filtering, status counting, and dynamic project rendering
 */

class ContractorMyProjects {
    constructor() {
        this.projects = [];
        this.filteredProjects = [];
        this.currentTabFilter = 'all';
        this.currentFilter = {
            status: 'all',
            sort: 'newest'
        };
        
        this.init();
    }

    init() {
        // Load projects
        this.loadProjects();
        
        // Setup event listeners
        this.setupEventListeners();
        
        // Update tab badge counts
        this.updateTabBadgeCounts();
        
        // Initial render - start with needs_setup
        this.applyTabFilter('needs_setup');
    }

    loadProjects() {
        // Sample projects data
        this.projects = [
            {
                id: 1,
                title: 'Modern 2-Story Residential House',
                type: 'General Contractor',
                description: 'Complete construction of a modern 2-story residential house with 4 bedrooms, 3 bathrooms, living room, kitchen, and outdoor space.',
                location: 'Brgy. Tumaga, Zamboanga City, Zamboanga del Sur',
                budget: '₱2.92M',
                status: 'needs_setup',
                statusText: 'Needs Setup',
                date: '2024-01-15',
                progress: 0,
                owner: {
                    name: 'carl_saludo',
                    avatar: 'CS'
                },
                image: 'https://images.unsplash.com/photo-1580587771525-78b9dba3b914?w=300&h=300&fit=crop',
                awaitingSetup: true,
                statusInfo: 'Awaiting Setup'
            },
            {
                id: 2,
                title: 'Office Building Renovation',
                type: 'Commercial Contractor',
                description: 'Renovation of a 3-story office building including modern facade and interior updates.',
                location: 'Makati City, Metro Manila',
                budget: '₱5.5M',
                status: 'in_progress',
                statusText: 'In Progress',
                date: '2024-02-01',
                progress: 45,
                owner: {
                    name: 'Maria Santos',
                    avatar: 'MS'
                },
                image: 'https://images.unsplash.com/photo-1519974719765-e6559eac2575?w=300&h=300&fit=crop',
                awaitingSetup: false,
                statusInfo: 'Construction ongoing - Phase 2'
            },
            {
                id: 3,
                title: 'Luxury Villa Construction',
                type: 'Residential Contractor',
                description: 'High-end villa with infinity pool, garden, and smart home features.',
                location: 'Tagaytay City, Cavite',
                budget: '₱12.5M',
                status: 'completed',
                statusText: 'Completed',
                date: '2023-12-20',
                progress: 100,
                owner: {
                    name: 'Pedro Garcia',
                    avatar: 'PG'
                },
                image: 'https://images.unsplash.com/photo-1613490493576-7fde63acd811?w=300&h=300&fit=crop',
                awaitingSetup: false,
                statusInfo: 'Project completed successfully'
            },
            {
                id: 4,
                title: 'Shopping Mall Extension',
                type: 'Commercial Contractor',
                description: 'Extension of existing shopping mall with new retail spaces and parking area.',
                location: 'Cebu City, Cebu',
                budget: '₱25M',
                status: 'in_progress',
                statusText: 'In Progress',
                date: '2024-01-10',
                progress: 30,
                owner: {
                    name: 'ABC Corporation',
                    avatar: 'AC'
                },
                image: 'https://images.unsplash.com/photo-1519974719765-e6559eac2575?w=300&h=300&fit=crop',
                awaitingSetup: false,
                statusInfo: 'Foundation work in progress'
            },
            {
                id: 5,
                title: 'Residential Complex Phase 1',
                type: 'Residential Contractor',
                description: '20-unit townhouse development with modern amenities and green spaces.',
                location: 'Quezon City, Metro Manila',
                budget: '₱45M',
                status: 'needs_setup',
                statusText: 'Needs Setup',
                date: '2024-02-05',
                progress: 0,
                owner: {
                    name: 'Real Estate Dev Co.',
                    avatar: 'RE'
                },
                image: 'https://images.unsplash.com/photo-1560448204-e02f11c3d0e2?w=300&h=300&fit=crop',
                awaitingSetup: true,
                statusInfo: 'Awaiting Setup'
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

        // Old filter dropdown (keep for additional sorting)
        const filterBtn = document.getElementById('filterBtn');
        if (filterBtn) {
            filterBtn.addEventListener('click', () => this.toggleFilterDropdown());
        }

        const filterCloseBtn = document.getElementById('filterCloseBtn');
        if (filterCloseBtn) {
            filterCloseBtn.addEventListener('click', () => this.closeFilterDropdown());
        }

        const filterApplyBtn = document.getElementById('filterApplyBtn');
        if (filterApplyBtn) {
            filterApplyBtn.addEventListener('click', () => this.applyOldFilters());
        }

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
        });
        document.querySelector(`[data-filter="${filter}"]`)?.classList.add('active');
        
        // Apply filter and render
        this.applyTabFilter(filter);
    }

    applyTabFilter(filter) {
        // Filter by status
        let filtered = this.projects.filter(project => project.status === filter);
        
        // Apply sorting
        filtered = this.sortProjects(filtered);
        
        this.filteredProjects = filtered;
        this.renderProjects();
    }

    sortProjects(projects) {
        const sortType = this.currentFilter.sort;
        
        return projects.sort((a, b) => {
            switch (sortType) {
                case 'newest':
                    return new Date(b.date) - new Date(a.date);
                case 'oldest':
                    return new Date(a.date) - new Date(b.date);
                case 'title':
                    return a.title.localeCompare(b.title);
                case 'status':
                    return a.status.localeCompare(b.status);
                default:
                    return 0;
            }
        });
    }

    updateTabBadgeCounts() {
        const counts = {
            needs_setup: 0,
            in_progress: 0,
            completed: 0
        };
        
        this.projects.forEach(project => {
            if (counts.hasOwnProperty(project.status)) {
                counts[project.status]++;
            }
        });
        
        // Update tab badges
        const needsSetupBadge = document.getElementById('needsSetupBadge');
        const inProgressBadge = document.getElementById('inProgressBadge');
        const completedBadge = document.getElementById('completedBadge');
        
        if (needsSetupBadge) needsSetupBadge.textContent = counts.needs_setup;
        if (inProgressBadge) inProgressBadge.textContent = counts.in_progress;
        if (completedBadge) completedBadge.textContent = counts.completed;
    }

    renderProjects() {
        const container = document.getElementById('projectsContainer');
        const emptyState = document.getElementById('emptyState');
        const template = document.getElementById('projectCardTemplate');
        
        if (!container || !template) return;
        
        container.innerHTML = '';
        
        if (this.filteredProjects.length === 0) {
            container.classList.add('hidden');
            emptyState?.classList.remove('hidden');
            return;
        }
        
        container.classList.remove('hidden');
        emptyState?.classList.add('hidden');
        
        this.filteredProjects.forEach(project => {
            const card = template.content.cloneNode(true);
            const cardElement = card.querySelector('.project-card');
            
            // Add needs-setup class if applicable
            if (project.awaitingSetup) {
                cardElement.classList.add('needs-setup');
                card.querySelector('.milestone-warning-banner')?.classList.remove('hidden');
            }
            
            // Fill in project details
            card.querySelector('.project-type').textContent = project.type;
            card.querySelector('.project-title').textContent = project.title;
            card.querySelector('.project-description').textContent = project.description;
            
            // Project image
            const projectImage = card.querySelector('.project-card-image');
            if (projectImage) {
                if (project.image) {
                    projectImage.src = project.image;
                    projectImage.alt = project.title;
                } else {
                    // Default placeholder image
                    projectImage.src = 'https://via.placeholder.com/100x100/EEA24B/ffffff?text=' + encodeURIComponent(project.type?.charAt(0) || 'P');
                    projectImage.alt = 'Project placeholder';
                }
            }
            
            // Status badge
            const statusBadge = card.querySelector('.status-badge');
            statusBadge.classList.add(`status-${project.status}`);
            card.querySelector('.status-text').textContent = project.statusText;
            
            // Owner info
            card.querySelector('.contractor-avatar').textContent = project.owner.avatar;
            card.querySelector('.contractor-name').textContent = project.owner.name;
            
            // Project details
            card.querySelector('.project-location').textContent = project.location;
            card.querySelector('.project-budget').textContent = project.budget;
            
            // Status info
            card.querySelector('.status-info-text').textContent = project.statusInfo;
            
            // Action button
            const actionBtn = card.querySelector('.action-btn');
            if (project.awaitingSetup) {
                actionBtn.classList.add('setup-btn');
                actionBtn.querySelector('.action-btn-icon').className = 'fi fi-rr-settings action-btn-icon';
                actionBtn.querySelector('.action-btn-text').textContent = 'Setup';
            } else if (project.status === 'completed') {
                actionBtn.classList.add('view-btn');
                actionBtn.querySelector('.action-btn-icon').className = 'fi fi-rr-eye action-btn-icon';
                actionBtn.querySelector('.action-btn-text').textContent = 'View Details';
            } else {
                actionBtn.classList.add('view-btn');
                actionBtn.querySelector('.action-btn-icon').className = 'fi fi-rr-eye action-btn-icon';
                actionBtn.querySelector('.action-btn-text').textContent = 'View Details';
            }
            
            // Click handlers
            const milestoneWarning = card.querySelector('.milestone-warning-banner');
            if (milestoneWarning) {
                milestoneWarning.addEventListener('click', (e) => {
                    e.stopPropagation();
                    this.handleSetupMilestones(project);
                });
            }
            
            actionBtn.addEventListener('click', (e) => {
                e.stopPropagation();
                if (project.awaitingSetup) {
                    this.handleSetupMilestones(project);
                } else {
                    this.handleViewDetails(project);
                }
            });
            
            cardElement.addEventListener('click', () => {
                this.handleViewDetails(project);
            });
            
            container.appendChild(card);
        });
    }

    handleSetupMilestones(project) {
        // Open the milestone setup modal
        if (window.openMilestoneSetupModal) {
            window.openMilestoneSetupModal(project);
        } else {
            this.showNotification(`Opening milestone setup for: ${project.title}`);
            console.log('Setup milestones for project:', project);
        }
    }

    handleViewDetails(project) {
        console.log('View details for project:', project);
        
        // Check if the project details modal function exists
        if (typeof window.openProjectDetailsModal === 'function') {
            window.openProjectDetailsModal(project);
        } else {
            this.showNotification(`Opening details for: ${project.title}`);
            console.error('Project details modal not loaded');
        }
    }

    toggleFilterDropdown() {
        const dropdown = document.getElementById('filterDropdown');
        const btn = document.getElementById('filterBtn');
        
        if (dropdown) {
            dropdown.classList.toggle('active');
            btn?.classList.toggle('active');
        }
    }

    closeFilterDropdown() {
        const dropdown = document.getElementById('filterDropdown');
        const btn = document.getElementById('filterBtn');
        
        dropdown?.classList.remove('active');
        btn?.classList.remove('active');
    }

    applyOldFilters() {
        const statusFilter = document.getElementById('statusFilter')?.value || 'all';
        const sortFilter = document.getElementById('sortFilter')?.value || 'newest';
        
        this.currentFilter.status = statusFilter;
        this.currentFilter.sort = sortFilter;
        
        this.applyTabFilter(this.currentTabFilter);
        this.closeFilterDropdown();
    }

    clearFilters() {
        document.getElementById('statusFilter').value = 'all';
        document.getElementById('sortFilter').value = 'newest';
        
        this.currentFilter = {
            status: 'all',
            sort: 'newest'
        };
        
        this.applyTabFilter(this.currentTabFilter);
        this.closeFilterDropdown();
    }

    showNotification(message) {
        const toast = document.createElement('div');
        toast.className = 'notification-toast';
        toast.textContent = message;
        toast.style.cssText = `
            position: fixed;
            bottom: 2rem;
            right: 2rem;
            background: linear-gradient(135deg, #EEA24B 0%, #E89A3C 100%);
            color: white;
            padding: 1rem 1.5rem;
            border-radius: 0.75rem;
            box-shadow: 0 4px 12px rgba(238, 162, 75, 0.4);
            z-index: 1000;
            font-weight: 500;
            animation: slideInUp 0.3s ease-out;
        `;

        document.body.appendChild(toast);

        setTimeout(() => {
            toast.style.animation = 'slideOutDown 0.3s ease-out';
            setTimeout(() => toast.remove(), 300);
        }, 3000);
    }
}

// Initialize when DOM is loaded
document.addEventListener('DOMContentLoaded', () => {
    window.contractorMyProjects = new ContractorMyProjects();
});

// Add CSS animations if not already present
if (!document.getElementById('customAnimations')) {
    const style = document.createElement('style');
    style.id = 'customAnimations';
    style.textContent = `
        @keyframes slideInUp {
            from {
                transform: translateY(100%);
                opacity: 0;
            }
            to {
                transform: translateY(0);
                opacity: 1;
            }
        }
        
        @keyframes slideOutDown {
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
}
