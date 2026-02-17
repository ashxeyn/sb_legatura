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
        // Setup event listeners first
        this.setupEventListeners();

        // Try to resolve user id from Blade/global and then load projects
        this.resolveUserId();
        this.loadProjects().then(() => {
            // Update tab badge counts and render default tab after data loads
            this.updateTabBadgeCounts();
            this.applyTabFilter('needs_setup');
        });
    }

    resolveUserId() {
        // Allow Blade to provide window.currentUser.id or window.userId
        this.userId = (window.currentUser && window.currentUser.id) || window.userId || null;
        // Try parsing numeric id in case it's a string
        if (this.userId && typeof this.userId === 'string' && this.userId.match(/^\d+$/)) {
            this.userId = parseInt(this.userId, 10);
        }
    }

    loadProjects() {
        // If server already rendered projects, prefer using them
        if (window.serverRendered && Array.isArray(window.serverProjects)) {
            const data = window.serverProjects;
            this.projects = (Array.isArray(data) ? data : []).map(p => {
                const milestones = Array.isArray(p.milestones) ? p.milestones : [];
                const totalMs = milestones.length;
                const doneMs = totalMs ? milestones.filter(m => (m.milestone_status === 'approved' || m.milestone_status === 'completed')).length : 0;
                const progress = totalMs ? Math.round((doneMs / totalMs) * 100) : 0;

                const ownerInfo = p.owner_info || null;
                const ownerName = ownerInfo ? (ownerInfo.username || ((ownerInfo.first_name || '') + ' ' + (ownerInfo.last_name || ''))) : (p.owner_name || '');
                const ownerAvatar = ownerInfo && ownerInfo.profile_pic ? ownerInfo.profile_pic : (ownerName ? ownerName.split(/\s+/).map(w => w.charAt(0)).slice(0, 2).join('').toUpperCase() : 'PO');

                let budget = '-';
                if (p.budget_range_min && p.budget_range_max) {
                    try { budget = '₱' + Number(p.budget_range_min).toLocaleString() + ' - ₱' + Number(p.budget_range_max).toLocaleString(); } catch (e) { budget = '-'; }
                }

                const display = p.display_status || p.project_status || '';
                let status = 'in_progress';
                let statusText = display;
                let awaitingSetup = false;
                let awaitingApproval = false;
                if (display === 'waiting_milestone_setup') {
                    status = 'needs_setup';
                    statusText = 'Needs Setup';
                    awaitingSetup = true;
                } else if (display === 'waiting_for_approval') {
                    status = 'waiting_approval';
                    statusText = 'Waiting for Approval';
                    awaitingApproval = true;
                } else if (display === 'in_progress') {
                    status = 'in_progress';
                    statusText = 'In Progress';
                } else if (display === 'completed') {
                    status = 'completed';
                    statusText = 'Completed';
                } else {
                    status = display || (p.project_status || 'pending');
                    statusText = (typeof status === 'string') ? status.replace(/_/g, ' ') : statusText;
                }

                return {
                    id: p.project_id,
                    title: p.project_title,
                    type: p.type_name || p.property_type || '',
                    description: p.project_description || '',
                    location: p.project_location || '',
                    budget: budget,
                    status: status,
                    statusText: statusText,
                    date: p.created_at || '',
                    progress: progress,
                    owner: { name: ownerName, avatar: ownerAvatar, username: ownerInfo ? ownerInfo.username : null, profile_pic: ownerInfo ? ownerInfo.profile_pic : null },
                    image: p.project_image || '',
                    awaitingSetup: awaitingSetup,
                    awaitingApproval: awaitingApproval,
                    statusInfo: p.project_status || display || '',
                    proposed_cost: p.proposed_cost,
                    raw: p
                };
            });
            this.filteredProjects = [...this.projects];
            return Promise.resolve();
        }

        // Fallback to client fetch if server data not present
        const userId = this.userId || (window.currentUser && window.currentUser.id) || null;
        if (!userId) {
            this.projects = [];
            this.filteredProjects = [];
            return Promise.resolve();
        }

        return (async () => {
            try {
                const csrf = document.querySelector('meta[name="csrf-token"]') ? document.querySelector('meta[name="csrf-token"]').getAttribute('content') : null;
                const url = `/api/contractor/my-projects?user_id=${encodeURIComponent(userId)}`;
                const res = await fetch(url, {
                    method: 'GET',
                    headers: {
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                        ...(csrf ? { 'X-CSRF-TOKEN': csrf } : {})
                    },
                    credentials: 'same-origin'
                });

                if (!res.ok) {
                    console.warn('Failed to fetch contractor projects', res.status);
                    this.projects = [];
                    this.filteredProjects = [];
                    return;
                }

                const payload = await res.json();
                const data = payload && payload.data ? payload.data : [];

                this.projects = (Array.isArray(data) ? data : []).map(p => {
                    const milestones = Array.isArray(p.milestones) ? p.milestones : [];
                    const totalMs = milestones.length;
                    const doneMs = totalMs ? milestones.filter(m => (m.milestone_status === 'approved' || m.milestone_status === 'completed')).length : 0;
                    const progress = totalMs ? Math.round((doneMs / totalMs) * 100) : 0;

                    const ownerInfo = p.owner_info || null;
                    const ownerName = ownerInfo ? (ownerInfo.username || ((ownerInfo.first_name || '') + ' ' + (ownerInfo.last_name || ''))) : (p.owner_name || '');
                    const ownerAvatar = ownerInfo && ownerInfo.profile_pic ? ownerInfo.profile_pic : (ownerName ? ownerName.split(/\s+/).map(w => w.charAt(0)).slice(0, 2).join('').toUpperCase() : 'PO');

                    let budget = '-';
                    if (p.budget_range_min && p.budget_range_max) {
                        try { budget = '₱' + Number(p.budget_range_min).toLocaleString() + ' - ₱' + Number(p.budget_range_max).toLocaleString(); } catch (e) { budget = '-'; }
                    }

                    const display = p.display_status || p.project_status || '';
                    let status = 'in_progress';
                    let statusText = display;
                    let awaitingSetup = false;
                    if (display === 'waiting_milestone_setup') {
                        status = 'needs_setup';
                        statusText = 'Needs Setup';
                        awaitingSetup = true;
                    } else if (display === 'in_progress') {
                        status = 'in_progress';
                        statusText = 'In Progress';
                    } else if (display === 'completed') {
                        status = 'completed';
                        statusText = 'Completed';
                    } else {
                        status = display || (p.project_status || 'pending');
                        statusText = (typeof status === 'string') ? status.replace(/_/g, ' ') : statusText;
                    }

                    return {
                        id: p.project_id,
                        title: p.project_title,
                        type: p.type_name || p.property_type || '',
                        description: p.project_description || '',
                        location: p.project_location || '',
                        budget: budget,
                        status: status,
                        statusText: statusText,
                        date: p.created_at || '',
                        progress: progress,
                        owner: { name: ownerName, avatar: ownerAvatar, username: ownerInfo ? ownerInfo.username : null, profile_pic: ownerInfo ? ownerInfo.profile_pic : null },
                        image: p.project_image || '',
                        awaitingSetup: awaitingSetup,
                        statusInfo: p.project_status || display || '',
                        proposed_cost: p.proposed_cost,
                        raw: p
                    };
                });

                this.filteredProjects = [...this.projects];
            } catch (err) {
                console.error('Error loading projects:', err);
                this.projects = [];
                this.filteredProjects = [];
            }
        })();
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
            waiting_approval: 0,
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
        const waitingApprovalBadge = document.getElementById('waitingApprovalBadge');
        const inProgressBadge = document.getElementById('inProgressBadge');
        const completedBadge = document.getElementById('completedBadge');

        if (needsSetupBadge) needsSetupBadge.textContent = counts.needs_setup;
        if (waitingApprovalBadge) waitingApprovalBadge.textContent = counts.waiting_approval;
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
