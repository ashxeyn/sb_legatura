/**
 * Property Owner All Projects JavaScript
 * Handles project listing, filtering, and interactions
 */

class PropertyOwnerAllProjects {
    constructor() {
        this.projects = [];
        this.currentFilters = {
            status: 'all',
            sort: 'newest',
            search: ''
        };
        this.isPinMode = false;
        
        this.init();
    }

    init() {
        // Populate project data from server-rendered page data
        this.loadProjectsFromPage();

        // Setup event listeners
        this.setupEventListeners();

        // Setup navbar search
        this.setupNavbarSearch();

        // Check if in pin mode
        this.checkPinMode();

        // Wire interactions onto the server-rendered cards
        this.setupCardListeners();
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

    /**
     * Populate this.projects from the JSON payload injected by the controller.
     * normalizeProject maps raw DB fields to the shape expected by the modal.
     */
    loadProjectsFromPage() {
        const raw = window.projectsData;
        if (Array.isArray(raw)) {
            this.projects = raw.map(p => this.normalizeProject(p));
        } else {
            this.projects = [];
        }
    }

    /**
     * Status config matching dashboard.tsx getStatusConfig()
     * Returns { label, icon (flaticon class), bg, color }
     */
    getStatusConfig(projectStatus, postStatus) {
        if (postStatus === 'under_review') {
            return { label: 'Under Review', icon: 'fi fi-rr-clock', bg: '#FEF3C7', color: '#D97706' };
        }
        if (projectStatus === 'open') {
            return { label: 'Open for Bidding', icon: 'fi fi-rr-check-circle', bg: '#D1FAE5', color: '#059669' };
        }
        if (projectStatus === 'bidding_closed') {
            return { label: 'Bidding Closed', icon: 'fi fi-rr-lock', bg: '#DBEAFE', color: '#2563EB' };
        }
        if (projectStatus === 'in_progress' || projectStatus === 'waiting_milestone_setup') {
            return { label: 'In Progress', icon: 'fi fi-rr-hammer', bg: '#DBEAFE', color: '#2563EB' };
        }
        if (projectStatus === 'completed') {
            return { label: 'Completed', icon: 'fi fi-rr-badge-check', bg: '#D1FAE5', color: '#059669' };
        }
        return { label: projectStatus || 'Pending', icon: 'fi fi-rr-circle', bg: '#F1F5F9', color: '#94A3B8' };
    }

    /**
     * Normalise a raw API project object into the shape expected by the details modal.
     */
    normalizeProject(p) {
        // ── Image (kept for modal compatibility) ───────────────────────────────
        let image = null;
        if (p.files && p.files.length > 0) {
            const thumb =
                p.files.find(f => f.file_type === 'building_permit' || f.file_type === 'title_of_land')
                || p.files[0];
            if (thumb && thumb.file_path) {
                image = `${window.location.origin}/storage/${thumb.file_path}`;
            }
        }

        // ── Contractor (kept for modal compatibility) ──────────────────────────
        const ci = p.contractor_info;
        const contractor = ci
            ? {
                name: ci.company_name || ci.username || 'Unknown Contractor',
                company: ci.company_name || ci.username || 'Unknown',
                role: p.type_name || 'Contractor',
                rating: '—',
                initials: (ci.company_name || ci.username || 'U').slice(0, 2).toUpperCase(),
              }
            : {
                name: 'No contractor yet',
                company: '—',
                role: p.type_name || '—',
                rating: '—',
                initials: '?',
              };

        // ── Progress (kept for modal compatibility) ────────────────────────────
        let progress = 0;
        if (p.milestones && p.milestones.length > 0) {
            let totalItems = 0;
            let completedItems = 0;
            p.milestones.forEach(m => {
                if (m.items && m.items.length > 0) {
                    totalItems += m.items.length;
                    completedItems += m.items.filter(i => i.item_status === 'completed').length;
                }
            });
            progress = totalItems > 0 ? Math.round((completedItems / totalItems) * 100) : 0;
        }

        // ── Budget ─────────────────────────────────────────────────────────────
        const min = p.budget_range_min
            ? Number(p.budget_range_min).toLocaleString('en-PH')
            : '0';
        const max = p.budget_range_max
            ? Number(p.budget_range_max).toLocaleString('en-PH')
            : '0';

        // ── Date label ─────────────────────────────────────────────────────────
        const statusMap = { completed: 'Completed', in_progress: 'Started' };
        const datePrefix = statusMap[p.display_status] || statusMap[p.project_status] || 'Posted';
        const dateFmt = p.created_at
            ? new Date(p.created_at).toLocaleDateString('en-US', {
                  month: 'short', day: 'numeric', year: 'numeric',
              })
            : '—';

        // ── Lot / floor ────────────────────────────────────────────────────────
        const lotSize = p.lot_size ? `${p.lot_size} sqm` : 'Not specified';
        const floorArea = p.floor_area ? `${p.floor_area} sqm` : 'Not specified';

        return {
            id: p.project_id,
            project_id: p.project_id,
            title: p.project_title || 'Untitled Project',
            type: p.type_name || '—',
            description: p.project_description || '—',
            image,
            // Raw status fields (matching dashboard.tsx Project interface)
            project_status: p.display_status || p.project_status || 'pending',
            project_post_status: p.project_post_status || '',
            bidding_due: p.bidding_due || null,
            // Kept for legacy filter/sort
            status: p.display_status || p.project_status || 'pending',
            contractor,
            location: p.project_location || '—',
            budget: `₱${min} - ₱${max}`,
            date: `${datePrefix}: ${dateFmt}`,
            rawDate: p.created_at,
            progress,
            lotSize,
            floorArea,
            bids_count: p.bids_count || 0,
            milestones: p.milestones || [],
            _raw: p,
        };
    }

    setupEventListeners() {
        // Filter chips
        document.querySelectorAll('.filter-chip').forEach(chip => {
            chip.addEventListener('click', () => {
                document.querySelectorAll('.filter-chip').forEach(c => c.classList.remove('active'));
                chip.classList.add('active');
                this.currentFilters.status = chip.dataset.filter;
                this.applyFilters();
            });
        });

        // Custom sort dropdown
        const sortBtn = document.getElementById('sortBtn');
        const sortDropdown = document.getElementById('sortDropdown');
        if (sortBtn && sortDropdown) {
            sortBtn.addEventListener('click', (e) => {
                e.stopPropagation();
                const isOpen = sortDropdown.classList.toggle('open');
                sortBtn.classList.toggle('open', isOpen);
                sortBtn.setAttribute('aria-expanded', isOpen);
            });

            sortDropdown.querySelectorAll('.sort-option').forEach(opt => {
                opt.addEventListener('click', () => {
                    const value = opt.dataset.value;
                    // Update hidden input
                    const sortFilter = document.getElementById('sortFilter');
                    if (sortFilter) sortFilter.value = value;
                    // Update button label
                    const sortLabel = document.getElementById('sortLabel');
                    if (sortLabel) sortLabel.textContent = opt.textContent.replace(/^✓\s*/, '').trim();
                    // Update active state
                    sortDropdown.querySelectorAll('.sort-option').forEach(o => o.classList.remove('active'));
                    opt.classList.add('active');
                    // Close
                    sortDropdown.classList.remove('open');
                    sortBtn.classList.remove('open');
                    sortBtn.setAttribute('aria-expanded', 'false');
                    // Apply
                    this.currentFilters.sort = value;
                    this.applyFilters();
                });
            });

            document.addEventListener('click', (e) => {
                if (!sortBtn.contains(e.target) && !sortDropdown.contains(e.target)) {
                    sortDropdown.classList.remove('open');
                    sortBtn.classList.remove('open');
                    sortBtn.setAttribute('aria-expanded', 'false');
                }
            });
        }
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
        const search = this.currentFilters.search;
        const status = this.currentFilters.status;
        const sort   = this.currentFilters.sort;

        const container  = document.getElementById('projectsContainer');
        const emptyState = document.getElementById('emptyState');
        if (!container) return;

        const allCards = Array.from(container.querySelectorAll('.project-card'));

        // Filter
        let visible = allCards.filter(card => {
            const title       = (card.dataset.title       || '').toLowerCase();
            const type        = (card.dataset.type        || '').toLowerCase();
            const location    = (card.dataset.location    || '').toLowerCase();
            const description = (card.dataset.description || '').toLowerCase();
            const ps          = card.dataset.status     || '';
            const pps         = card.dataset.postStatus || '';

            if (search && !
                (title.includes(search) || type.includes(search) ||
                 location.includes(search) || description.includes(search)))
                return false;

            if (status === 'pending'     && pps !== 'under_review') return false;
            if (status === 'active'      && !(pps === 'approved' && ps === 'open')) return false;
            if (status === 'in_progress' && !['bidding_closed','in_progress','waiting_milestone_setup'].includes(ps)) return false;
            if (status === 'completed'   && ps !== 'completed') return false;

            return true;
        });

        // Sort
        visible.sort((a, b) => {
            if (sort === 'title')        return (a.dataset.title || '').localeCompare(b.dataset.title || '');
            if (sort === 'z_title')      return (b.dataset.title || '').localeCompare(a.dataset.title || '');
            if (sort === 'budget_high')  return Number(b.dataset.budgetMin || 0) - Number(a.dataset.budgetMin || 0);
            if (sort === 'budget_low')   return Number(a.dataset.budgetMin || 0) - Number(b.dataset.budgetMin || 0);
            if (sort === 'oldest')       return new Date(a.dataset.rawdate) - new Date(b.dataset.rawdate);
            return new Date(b.dataset.rawdate) - new Date(a.dataset.rawdate); // newest
        });

        // Hide all, then show & re-append in sorted order
        allCards.forEach(c => c.style.display = 'none');

        if (visible.length === 0) {
            container.classList.add('hidden');
            if (emptyState) emptyState.classList.remove('hidden');
        } else {
            container.classList.remove('hidden');
            if (emptyState) emptyState.classList.add('hidden');
            visible.forEach(c => { c.style.display = ''; container.appendChild(c); });
        }

        // Update filter badge
        const filterBadge = document.getElementById('filterBadge');
        if (filterBadge) {
            const active = (status !== 'all' ? 1 : 0) + (sort !== 'newest' ? 1 : 0) + (search ? 1 : 0);
            if (active > 0) {
                filterBadge.textContent = active;
                filterBadge.classList.remove('hidden');
            } else {
                filterBadge.classList.add('hidden');
            }
        }
    }

    clearFilters() {
        this.currentFilters = { status: 'all', sort: 'newest', search: '' };

        // Reset chip active state
        document.querySelectorAll('.filter-chip').forEach(c => c.classList.remove('active'));
        const allChip = document.querySelector('.filter-chip[data-filter="all"]');
        if (allChip) allChip.classList.add('active');

        const sortFilter = document.getElementById('sortFilter');
        if (sortFilter) sortFilter.value = 'newest';

        // Reset sort dropdown label and active state
        const sortLabel = document.getElementById('sortLabel');
        if (sortLabel) sortLabel.textContent = 'Newest';
        const sortDropdown = document.getElementById('sortDropdown');
        if (sortDropdown) {
            sortDropdown.querySelectorAll('.sort-option').forEach(o => o.classList.remove('active'));
            const newestOpt = sortDropdown.querySelector('.sort-option[data-value="newest"]');
            if (newestOpt) newestOpt.classList.add('active');
        }

        const navbarSearchInput = document.querySelector('.navbar-search-input');
        if (navbarSearchInput) navbarSearchInput.value = '';

        this.applyFilters();
    }

    /**
     * Wire click, ripple, pin button, and deadline countdown onto
     * every server-rendered .project-card element.
     */
    setupCardListeners() {
        document.querySelectorAll('.project-card').forEach(cardEl => {
            const projectId = parseInt(cardEl.dataset.projectId);

            // ── Read the full server-rendered project from blade (data-project) ──
            // Falls back to this.projects (JS-normalised) if the attribute is absent.
            let project = null;
            try {
                project = cardEl.dataset.project
                    ? JSON.parse(cardEl.dataset.project)
                    : this.projects.find(p => Number(p.id) === projectId) || null;
            } catch (_) {
                project = this.projects.find(p => Number(p.id) === projectId) || null;
            }

            // ── Deadline countdown ───────────────────────────────────────────────
            const deadlineInfo = cardEl.querySelector('.deadline-info');
            const biddingDue   = cardEl.dataset.biddingDue;
            if (deadlineInfo && biddingDue) {
                const daysLeft = Math.ceil((new Date(biddingDue) - new Date()) / (1000 * 60 * 60 * 24));
                if (daysLeft > 0) {
                    const isUrgent    = daysLeft <= 3;
                    const clockIcon   = cardEl.querySelector('.deadline-clock-icon');
                    const deadlineTxt = cardEl.querySelector('.deadline-text');
                    if (clockIcon)   clockIcon.style.color    = isUrgent ? '#EF4444' : '#94A3B8';
                    if (deadlineTxt) { deadlineTxt.style.color = isUrgent ? '#EF4444' : '#6B7280'; deadlineTxt.textContent = `${daysLeft}d left`; }
                    deadlineInfo.style.display = 'flex';
                }
            }

            if (!project) return;

            // ── Card / View Details click ────────────────────────────────────────
            // Pass the blade-rendered project directly — no JS lookup needed.
            const openDetails = (e) => {
                e.stopPropagation();
                if (window.openProjectDetailsModal) {
                    window.openProjectDetailsModal(project);
                } else {
                    setTimeout(() => window.openProjectDetailsModal?.(project), 150);
                }
            };
            cardEl.addEventListener('click', openDetails);
            const viewBtn = cardEl.querySelector('.view-details-btn');
            if (viewBtn) viewBtn.addEventListener('click', openDetails);

            // ── Pin button ───────────────────────────────────────────────────────
            const pinBtn = cardEl.querySelector('.pin-project-btn');
            if (pinBtn) {
                if (this.isPinMode) pinBtn.style.display = 'flex';
                pinBtn.addEventListener('click', (e) => { e.stopPropagation(); this.pinProject(project); });
            }

            // ── Ripple ───────────────────────────────────────────────────────────
            cardEl.addEventListener('click', (e) => {
                const ripple = document.createElement('span');
                ripple.classList.add('ripple');
                const rect = cardEl.getBoundingClientRect();
                const size = Math.max(rect.width, rect.height);
                ripple.style.cssText = `width:${size}px;height:${size}px;left:${e.clientX - rect.left - size/2}px;top:${e.clientY - rect.top - size/2}px;`;
                cardEl.appendChild(ripple);
                ripple.addEventListener('animationend', () => ripple.remove());
            });
        });
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

