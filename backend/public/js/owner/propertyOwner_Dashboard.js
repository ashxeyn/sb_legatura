/**
 * Property Owner Dashboard JavaScript
 * Handles interactivity and dynamic content updates
 */

class PropertyOwnerDashboard {
    constructor() {
        // Read initial stats from HTML elements
        this.stats = {
            total: this.getStatValue('statTotal'),
            pending: this.getStatValue('statPending'),
            active: this.getStatValue('statActive'),
            inProgress: this.getStatValue('statInProgress'),
            completed: 0
        };
        
        // Initialize pinned projects array
        this.pinnedProjects = [];
        this.filteredPinnedProjects = [];
        this.searchQuery = '';
        
        this.init();
    }

    getStatValue(elementId) {
        const element = document.getElementById(elementId);
        if (element) {
            const value = parseInt(element.textContent.trim(), 10);
            return isNaN(value) ? 0 : value;
        }
        return 0;
    }

    init() {
        // Set greeting based on time of day
        this.setGreeting();
        
        // Setup event listeners
        this.setupEventListeners();
        
        // Load user data if available
        this.loadUserData();
        
        // Initialize pinned project status styling
        this.initializePinnedProjectStatus();
        
        // Initialize pinned card empty state class
        this.initializePinnedCardState();
        
        // Load pinned projects if available
        this.loadPinnedProject();
        
        // Setup navbar search
        this.setupNavbarSearch();
        
        // Check if returning from pinning a project
        const urlParams = new URLSearchParams(window.location.search);
        if (urlParams.get('pinned') === 'true') {
            this.showNotification('Project pinned successfully!');
            // Clean URL
            window.history.replaceState({}, document.title, window.location.pathname);
        }
        
        // Initialize stats - only update if stats have been explicitly changed
        // This preserves the HTML values as the source of truth
        // Call updateStats() only when stats are updated from backend
    }

    initializePinnedProjectStatus() {
        // Initialize status styling for the default pinned project
        const statusElement = document.getElementById('pinnedProjectStatus');
        if (statusElement) {
            const statusText = statusElement.querySelector('.status-text');
            if (statusText) {
                const statusValue = statusText.textContent.trim().toLowerCase().replace(/\s+/g, '_');
                // Set the status class
                statusElement.className = `status-badge status-${statusValue} px-3 py-1 rounded-full text-xs font-semibold flex items-center gap-1.5 whitespace-nowrap`;
            }
        }
    }

    initializePinnedCardState() {
        // This is now handled in renderPinnedProjects
    }

    loadPinnedProject() {
        // Try to load pinned projects from localStorage
        const savedPinnedProjects = localStorage.getItem('pinnedProjects');
        if (savedPinnedProjects) {
            try {
                this.pinnedProjects = JSON.parse(savedPinnedProjects);
                this.filteredPinnedProjects = [...this.pinnedProjects];
                // Initialize with current search query if any
                this.filterPinnedProjects();
                return;
            } catch (e) {
                console.error('Error loading pinned projects:', e);
                // Try to migrate old single pinned project
                const oldPinned = localStorage.getItem('pinnedProject');
                if (oldPinned) {
                    try {
                        const project = JSON.parse(oldPinned);
                        this.pinnedProjects = [project];
                        this.filteredPinnedProjects = [...this.pinnedProjects];
                        this.savePinnedProjects();
                        localStorage.removeItem('pinnedProject');
                        this.filterPinnedProjects();
                        return;
                    } catch (e2) {
                        console.error('Error migrating old pinned project:', e2);
                    }
                }
                // Clear invalid data
                localStorage.removeItem('pinnedProjects');
            }
        }
        
        // If no saved projects, show empty state
        this.pinnedProjects = [];
        this.filteredPinnedProjects = [];
        this.filterPinnedProjects();
    }

    filterPinnedProjects() {
        if (!this.searchQuery) {
            // If no search query, show all projects
            this.filteredPinnedProjects = [...this.pinnedProjects];
        } else {
            // Filter projects based on search query
            this.filteredPinnedProjects = this.pinnedProjects.filter(project => {
                const searchLower = this.searchQuery.toLowerCase();
                return (
                    (project.title && project.title.toLowerCase().includes(searchLower)) ||
                    (project.type && project.type.toLowerCase().includes(searchLower)) ||
                    (project.location && project.location.toLowerCase().includes(searchLower)) ||
                    (project.description && project.description.toLowerCase().includes(searchLower)) ||
                    (project.contractor && project.contractor.name && project.contractor.name.toLowerCase().includes(searchLower)) ||
                    (project.contractor && project.contractor.company && project.contractor.company.toLowerCase().includes(searchLower)) ||
                    (project.budget && project.budget.toLowerCase().includes(searchLower))
                );
            });
        }
        
        // Re-render with filtered results
        this.renderPinnedProjects();
    }

    savePinnedProjects() {
        localStorage.setItem('pinnedProjects', JSON.stringify(this.pinnedProjects));
    }

    setupNavbarSearch() {
        // Get the navbar search input
        const navbarSearchInput = document.querySelector('.navbar-search-input');
        const navbarSearchButton = document.querySelector('.navbar-search-btn');
        
        if (navbarSearchInput) {
            // Update placeholder
            navbarSearchInput.placeholder = 'Search pinned projects...';
            
            // Search on input (handles both typing and clearing)
            navbarSearchInput.addEventListener('input', (e) => {
                const value = e.target.value.trim();
                this.searchQuery = value.toLowerCase();
                this.filterPinnedProjects();
            });

            // Search on Enter key
            navbarSearchInput.addEventListener('keypress', (e) => {
                if (e.key === 'Enter') {
                    e.preventDefault();
                    this.searchQuery = e.target.value.toLowerCase().trim();
                    this.filterPinnedProjects();
                }
            });
        }

        if (navbarSearchButton) {
            // Search on button click
            navbarSearchButton.addEventListener('click', () => {
                if (navbarSearchInput) {
                    this.searchQuery = navbarSearchInput.value.toLowerCase().trim();
                    this.filterPinnedProjects();
                }
            });
        }
    }

    renderPinnedProjects() {
        const pinnedList = document.getElementById('pinnedProjectsList');
        const emptyCard = document.getElementById('pinnedEmptyCard');
        const template = document.getElementById('pinnedProjectTemplate');
        
        if (!pinnedList || !emptyCard || !template) return;
        
        // Clear existing projects
        pinnedList.innerHTML = '';
        
        // Use filtered projects if search is active, otherwise use all projects
        const projectsToRender = this.searchQuery ? this.filteredPinnedProjects : this.pinnedProjects;
        
        if (projectsToRender.length === 0) {
            // Show empty state
            if (this.searchQuery) {
                // Show search empty state
                emptyCard.querySelector('.pinned-text').textContent = 'No pinned projects found';
                emptyCard.querySelector('.pinned-hint').textContent = `No projects match "${this.searchQuery}"`;
                emptyCard.querySelector('.pinned-add-btn').classList.add('hidden');
            } else {
                // Show regular empty state
                emptyCard.querySelector('.pinned-text').textContent = 'No pinned project';
                emptyCard.querySelector('.pinned-hint').textContent = 'Click "Add Pin" or tap here to pin a project for quick access';
                emptyCard.querySelector('.pinned-add-btn').classList.remove('hidden');
            }
            emptyCard.classList.remove('hidden');
            emptyCard.classList.add('has-empty-state');
        } else {
            // Hide empty state
            emptyCard.classList.add('hidden');
            emptyCard.classList.remove('has-empty-state');
            
            // Render each pinned project (newest first)
            projectsToRender.forEach((project, index) => {
                const card = template.content.cloneNode(true);
                const cardElement = card.querySelector('.pinned-content');
                cardElement.setAttribute('data-project-id', project.id);
                
                // Set project data
                this.populatePinnedProjectCard(cardElement, project, index);
                
                pinnedList.appendChild(card);
            });
        }
    }

    populatePinnedProjectCard(cardElement, project, index) {
        // Header
        const titleEl = cardElement.querySelector('.project-title');
        const typeEl = cardElement.querySelector('.project-type');
        const statusEl = cardElement.querySelector('.status-badge');
        const statusText = cardElement.querySelector('.status-text');
        const unpinBtn = cardElement.querySelector('.pinned-unpin-btn');
        
        if (titleEl) titleEl.textContent = project.title || 'Untitled Project';
        if (typeEl) typeEl.textContent = project.type || 'General';
        
        // Status
        if (statusEl && statusText) {
            const normalizedStatus = (project.status || 'in_progress').toLowerCase().replace(/\s+/g, '_');
            statusText.textContent = (project.status || 'In Progress').replace('_', ' ');
            statusEl.className = `status-badge status-${normalizedStatus} px-3 py-1 rounded-full text-xs font-semibold flex items-center gap-1.5 whitespace-nowrap`;
        }
        
        // Unpin button
        if (unpinBtn) {
            unpinBtn.addEventListener('click', (e) => {
                e.stopPropagation();
                this.unpinProject(project.id);
            });
        }
        
        // Body
        const imageEl = cardElement.querySelector('.project-image');
        const descEl = cardElement.querySelector('.project-description');
        const locationEl = cardElement.querySelector('.project-location');
        const budgetEl = cardElement.querySelector('.project-budget');
        const dateEl = cardElement.querySelector('.project-date');
        const contractorAvatar = cardElement.querySelector('.contractor-avatar');
        const contractorName = cardElement.querySelector('.contractor-name');
        const contractorRole = cardElement.querySelector('.contractor-role');
        const ratingValue = cardElement.querySelector('.rating-value');
        const progressBar = cardElement.querySelector('.progress-bar');
        const progressPercentage = cardElement.querySelector('.progress-percentage');
        const viewBtn = cardElement.querySelector('.view-details-btn');
        
        // Image
        if (imageEl) {
            imageEl.src = project.image || 'https://via.placeholder.com/120x120/EEA24B/ffffff?text=Project';
            imageEl.alt = project.title || 'Project image';
        }
        
        // Description
        if (descEl) descEl.textContent = project.description || 'No description available.';
        
        // Details
        if (locationEl) locationEl.textContent = project.location || 'Location not specified';
        if (budgetEl) budgetEl.textContent = project.budget || 'Budget not specified';
        if (dateEl) dateEl.textContent = project.date || '';
        
        // Contractor - Always show, use defaults if no data
        const contractor = project.contractor || {};
        if (contractorAvatar) {
            contractorAvatar.textContent = contractor.initials || 'NA';
        }
        if (contractorName) {
            contractorName.textContent = contractor.company || contractor.name || 'Not Assigned';
        }
        if (contractorRole) {
            contractorRole.textContent = contractor.role || 'Contractor';
        }
        if (ratingValue) {
            ratingValue.textContent = contractor.rating || '0.0';
        }
        
        // Progress
        if (progressBar && project.progress !== undefined) {
            progressBar.style.width = `${project.progress}%`;
        }
        if (progressPercentage && project.progress !== undefined) {
            progressPercentage.textContent = `${project.progress}%`;
        }
        
        // View button
        if (viewBtn) {
            viewBtn.addEventListener('click', (e) => {
                e.stopPropagation();
                this.handleViewPinnedProject(e, project.id);
            });
        }
    }

    setGreeting() {
        const hour = new Date().getHours();
        let greeting = 'Good Morning';
        
        if (hour >= 12 && hour < 17) {
            greeting = 'Good Afternoon';
        } else if (hour >= 17 || hour < 5) {
            greeting = 'Good Evening';
        }
        
        const greetingElement = document.getElementById('greeting');
        if (greetingElement) {
            greetingElement.textContent = greeting;
        }
    }

    loadUserData() {
        // Extract initials from name and set username if needed
        const nameElement = document.getElementById('profileUserName');
        const roleElement = document.getElementById('profileUserRole');
        const avatarElement = document.getElementById('profileAvatar');
        
        if (nameElement && avatarElement) {
            const currentName = nameElement.textContent.trim();
            const initialsElement = avatarElement.querySelector('.profile-initials');
            
            // Extract initials from name
            if (initialsElement && currentName) {
                const words = currentName.split(' ');
                if (words.length >= 2) {
                    initialsElement.textContent = (words[0][0] + words[1][0]).toUpperCase();
                } else if (words.length === 1 && currentName.length >= 2) {
                    initialsElement.textContent = currentName.substring(0, 2).toUpperCase();
                }
            }
            
            // If username/role is not set, extract from name
            if (roleElement && (!roleElement.textContent.trim() || roleElement.textContent.trim() === '@emmanuellesantos')) {
                // Extract username from name (convert to lowercase, replace spaces with nothing, add @)
                const username = '@' + currentName.toLowerCase().replace(/\s+/g, '');
                roleElement.textContent = username;
            }
        }
        
        // If you need to load user data from backend in the future, add it here
        // but make sure to check if name/username already exists before overriding
    }

    updateStats() {
        // Only update if stats have changed (to preserve HTML values if they're already set)
        // Update stat numbers with animation only if values differ
        const currentTotal = this.getStatValue('statTotal');
        if (currentTotal !== this.stats.total) {
            this.animateNumber('statTotal', this.stats.total);
        }
        
        const currentPending = this.getStatValue('statPending');
        if (currentPending !== this.stats.pending) {
            this.animateNumber('statPending', this.stats.pending);
        }
        
        const currentActive = this.getStatValue('statActive');
        if (currentActive !== this.stats.active) {
            this.animateNumber('statActive', this.stats.active);
        }
        
        const currentInProgress = this.getStatValue('statInProgress');
        if (currentInProgress !== this.stats.inProgress) {
            this.animateNumber('statInProgress', this.stats.inProgress);
        }
        
        // Update project counts
        const allProjectsCount = document.getElementById('allProjectsCount');
        if (allProjectsCount) {
            allProjectsCount.textContent = this.stats.total;
        }
        
        const finishedProjectsCount = document.getElementById('finishedProjectsCount');
        if (finishedProjectsCount) {
            finishedProjectsCount.textContent = this.stats.completed;
        }
    }

    animateNumber(elementId, targetValue) {
        const element = document.getElementById(elementId);
        if (!element) return;

        const duration = 1000; // 1 second
        const startValue = 0;
        const increment = targetValue / (duration / 16); // 60fps
        let currentValue = startValue;

        const timer = setInterval(() => {
            currentValue += increment;
            if (currentValue >= targetValue) {
                element.textContent = targetValue;
                clearInterval(timer);
            } else {
                element.textContent = Math.floor(currentValue);
            }
        }, 16);
    }

    setupEventListeners() {
        // Statistics card click handlers
        this.setupStatsInteractivity();

        // Pinned card click handler
        const pinnedCard = document.getElementById('pinnedCard');
        if (pinnedCard) {
            pinnedCard.addEventListener('click', (e) => {
                this.handlePinnedCardClick(e);
            });
        }

        // Unpin button handler
        const unpinBtn = document.getElementById('pinnedUnpinBtn');
        if (unpinBtn) {
            unpinBtn.addEventListener('click', (e) => {
                this.handleUnpinClick(e);
            });
        }

        // View pinned project button handler
        const viewPinnedBtn = document.getElementById('pinnedViewBtn');
        if (viewPinnedBtn) {
            viewPinnedBtn.addEventListener('click', (e) => {
                this.handleViewPinnedProject(e);
            });
        }

        // Add pinned project button handlers
        const addPinnedBtn = document.getElementById('addPinnedBtn');
        if (addPinnedBtn) {
            addPinnedBtn.addEventListener('click', (e) => {
                this.handleAddPinnedProject(e);
            });
        }

        const pinnedAddBtn = document.getElementById('pinnedAddBtn');
        if (pinnedAddBtn) {
            pinnedAddBtn.addEventListener('click', (e) => {
                this.handleAddPinnedProject(e);
            });
        }

        // Empty state click handler
        const pinnedEmpty = document.getElementById('pinnedEmpty');
        if (pinnedEmpty) {
            pinnedEmpty.addEventListener('click', (e) => {
                // Only trigger if clicking on the empty state itself, not on buttons
                if (!e.target.closest('.pinned-add-btn')) {
                    this.handleAddPinnedProject(e);
                }
            });
        }

        // All Projects card click handler
        const allProjectsCard = document.getElementById('allProjectsCard');
        if (allProjectsCard) {
            allProjectsCard.addEventListener('click', (event) => {
                this.handleAllProjectsClick(event);
            });
        }

        // Finished Projects card click handler
        const finishedProjectsCard = document.getElementById('finishedProjectsCard');
        if (finishedProjectsCard) {
            finishedProjectsCard.addEventListener('click', (e) => {
                this.handleFinishedProjectsClick(e);
            });
        }

        // Profile avatar click handler
        const profileAvatar = document.getElementById('profileAvatar');
        if (profileAvatar) {
            profileAvatar.addEventListener('click', (e) => {
                e.stopPropagation();
                this.handleProfileClick();
            });
        }
    }

    setupStatsInteractivity() {
        const statItems = document.querySelectorAll('.stat-item');
        
        statItems.forEach((item, index) => {
            // Add click handler
            item.addEventListener('click', () => {
                this.handleStatClick(item, index);
            });

            // Add hover sound effect (optional - can be removed if not needed)
            item.addEventListener('mouseenter', () => {
                this.animateStatItem(item);
            });

            // Add ripple effect on click
            item.addEventListener('click', (e) => {
                this.createRippleEffect(e, item);
            });
        });
    }

    handleStatClick(statItem, index) {
        // Remove active class from all items
        document.querySelectorAll('.stat-item').forEach(item => {
            item.classList.remove('active');
        });

        // Add active class to clicked item
        statItem.classList.add('active');

        // Get stat type from label
        const label = statItem.querySelector('.stat-label').textContent.trim();
        const number = statItem.querySelector('.stat-number').textContent.trim();

        // Show notification
        this.showNotification(`Viewing ${label}: ${number} projects`);

        // Add pulse animation
        this.pulseAnimation(statItem);

        // In a real implementation, you could filter projects based on the stat clicked
        // For example:
        // if (label === 'TOTAL') {
        //     this.filterProjects('all');
        // } else if (label === 'PENDING') {
        //     this.filterProjects('pending');
        // } etc.
    }

    animateStatItem(item) {
        const number = item.querySelector('.stat-number');
        if (number) {
            number.style.transform = 'scale(1.1)';
            setTimeout(() => {
                number.style.transform = '';
            }, 200);
        }
    }

    createRippleEffect(event, element) {
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
            background: rgba(255, 255, 255, 0.3);
            left: ${x}px;
            top: ${y}px;
            transform: scale(0);
            animation: ripple 0.6s ease-out;
            pointer-events: none;
        `;

        element.appendChild(ripple);

        setTimeout(() => {
            ripple.remove();
        }, 600);
    }

    pulseAnimation(element) {
        element.style.animation = 'pulse 0.5s ease-in-out';
        setTimeout(() => {
            element.style.animation = '';
        }, 500);
    }

    handlePinnedCardClick(event) {
        const pinnedCard = document.getElementById('pinnedCard');
        const pinnedEmpty = document.getElementById('pinnedEmpty');
        const pinnedContent = document.getElementById('pinnedContent');

        // Don't trigger if clicking on buttons or links inside
        if (event.target.closest('.pinned-unpin-btn') || event.target.closest('.pinned-view-btn')) {
            return;
        }

        // Add click animation
        this.pulseAnimation(pinnedCard);

        // If empty state is visible, show message to pin a project
        if (pinnedEmpty && !pinnedEmpty.classList.contains('hidden')) {
            // Show a toast/notification
            this.showNotification('Select a project to pin from your projects list');
            
            // Add ripple effect
            this.createRippleEffect(event, pinnedCard);
        }
    }

    handleUnpinClick(event) {
        event.stopPropagation();
        this.unpinProject();
    }

    handleViewPinnedProject(event, projectId) {
        if (event) event.stopPropagation();
        if (projectId) {
            // Navigate to project details
            const projectsUrl = window.ownerRoutes?.projects || '/owner/projects';
            window.location.href = `${projectsUrl}?project=${projectId}`;
        } else {
            this.showNotification('Project details not available');
        }
    }

    addPinnedProject(project) {
        // Check if project is already pinned
        const existingIndex = this.pinnedProjects.findIndex(p => p.id === project.id);
        if (existingIndex !== -1) {
            // Remove existing and add to top
            this.pinnedProjects.splice(existingIndex, 1);
        }
        
        // Add to the beginning of the array (top of list)
        this.pinnedProjects.unshift(project);

        // Save and render
        this.savePinnedProjects();
        this.filterPinnedProjects(); // This will call renderPinnedProjects
        
        this.showNotification(`Project "${project.title}" pinned successfully!`);
    }

    unpinProject(projectId) {
        const projectIndex = this.pinnedProjects.findIndex(p => p.id === projectId);
        if (projectIndex !== -1) {
            const project = this.pinnedProjects[projectIndex];
            this.pinnedProjects.splice(projectIndex, 1);
            this.savePinnedProjects();
            this.filterPinnedProjects(); // This will call renderPinnedProjects
            this.showNotification(`Project "${project.title}" unpinned`);
        }
    }

    handleAllProjectsClick(event) {
        const card = document.getElementById('allProjectsCard');
        
        // Add click animation
        this.pulseAnimation(card);
        this.createRippleEffect(event, card);
        
        // Navigate to all projects page
        const projectsUrl = window.ownerRoutes?.projects || '/owner/projects';
        window.location.href = projectsUrl;
    }

    handleFinishedProjectsClick(event) {
        const card = document.getElementById('finishedProjectsCard');
        
        // Add click animation
        this.pulseAnimation(card);
        if (event) {
        this.createRippleEffect(event, card);
        }
        
        // Navigate to finished projects page
        const finishedProjectsUrl = window.ownerRoutes?.finishedProjects || '/owner/projects/finished';
        window.location.href = finishedProjectsUrl;
    }

    handleProfileClick() {
        // Navigate to profile page or open profile menu
        this.showNotification('Opening profile...');
        
        // In a real implementation:
        // window.location.href = '/owner/profile';
    }

    handleAddPinnedProject(event) {
        if (event) {
            event.stopPropagation();
        }
        
        // Navigate to projects page to select a project to pin
        const projectsUrl = window.ownerRoutes?.projects || '/owner/projects';
        
        // Show notification
        this.showNotification('Select a project to pin...');
        
        // Navigate to projects page with pin mode
        window.location.href = `${projectsUrl}?action=pin`;
    }

    openPinProjectModal() {
        // This would open a modal to select a project to pin
        // For now, just show a notification
        this.showNotification('Project pinning feature - Select a project from your projects list');
    }

    showNotification(message) {
        // Create a simple toast notification
        const toast = document.createElement('div');
        toast.className = 'dashboard-toast';
        toast.textContent = message;
        toast.style.cssText = `
            position: fixed;
            bottom: 2rem;
            right: 2rem;
            background: #EEA24B;
            color: white;
            padding: 1rem 1.5rem;
            border-radius: 0.5rem;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
            z-index: 1000;
            animation: slideInUp 0.3s ease-out;
            font-weight: 500;
        `;

        document.body.appendChild(toast);

        // Remove after 3 seconds
        setTimeout(() => {
            toast.style.animation = 'slideOutDown 0.3s ease-out';
            setTimeout(() => {
                document.body.removeChild(toast);
            }, 300);
        }, 3000);
    }

    // Method to update stats (can be called from backend)
    updateStatsFromBackend(newStats) {
        this.stats = { ...this.stats, ...newStats };
        this.updateStats();
    }

    // Method to set pinned project from backend
    // Example usage:
    // dashboard.setPinnedProject({
    //     id: 1,
    //     title: 'Modern House Construction',
    //     type: 'Residential',
    //     description: 'A beautiful modern house with 3 bedrooms and 2 bathrooms...',
    //     location: 'Manila, Philippines',
    //     budget: '₱2,500,000 - ₱3,000,000',
    //     status: 'In Progress'
    // });
}

// Initialize dashboard when DOM is ready
document.addEventListener('DOMContentLoaded', () => {
    new PropertyOwnerDashboard();
});

// Add CSS animations for toast
const style = document.createElement('style');
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

