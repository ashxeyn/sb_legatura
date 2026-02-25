/**
 * Contractor My Bids JavaScript - Server-Rendered Cards
 * Handles tab filtering, status counting, sorting, and view details via server-rendered cards
 */

class ContractorMyBids {
    constructor() {
        this.bids = [];
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
        // Load bids from server data
        this.loadBids();

        // Setup event listeners
        this.setupEventListeners();

        // Initial render - show pending tab
        this.applyTabFilter('pending');
    }

    loadBids() {
        // Load from server-rendered data
        if (window.serverBids && Array.isArray(window.serverBids)) {
            this.bids = window.serverBids.map(bid => {
                // Determine tab-level status
                let tabStatus = bid.bid_status;
                let statusText = '';
                let statusInfo = '';

                if (bid.bid_status === 'submitted' || bid.bid_status === 'under_review') {
                    tabStatus = 'pending';
                    statusText = bid.bid_status === 'under_review' ? 'Under Review' : 'Pending';
                    statusInfo = bid.bid_status === 'under_review' ? 'Owner is reviewing your bid' : 'Waiting for owner review';
                } else if (bid.bid_status === 'accepted') {
                    statusText = 'Accepted';
                    statusInfo = 'Bid accepted - Contract pending';
                } else if (bid.bid_status === 'rejected') {
                    statusText = 'Rejected';
                    statusInfo = 'Owner selected another contractor';
                } else {
                    statusText = bid.bid_status ? bid.bid_status.charAt(0).toUpperCase() + bid.bid_status.slice(1).replace(/_/g, ' ') : '';
                    statusInfo = '';
                }

                // Build budget string
                let projectBudget = '—';
                if (bid.budget_range_min && bid.budget_range_max) {
                    projectBudget = `₱${Number(bid.budget_range_min).toLocaleString()} - ₱${Number(bid.budget_range_max).toLocaleString()}`;
                }

                // Build bid amount string
                const bidAmount = bid.proposed_cost ? `₱${Number(bid.proposed_cost).toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 })}` : '—';

                // Submitted date
                const submittedDate = bid.submitted_at ? new Date(bid.submitted_at).toLocaleDateString('en-US', { year: 'numeric', month: 'long', day: 'numeric' }) : '—';

                // Owner initials
                const ownerName = bid.owner_name || '—';
                const initials = ownerName.split(' ').filter(Boolean).map(w => w[0].toUpperCase()).slice(0, 2).join('');

                // Image
                let image = '';
                if (bid.project_files && bid.project_files.length > 0) {
                    const first = bid.project_files[0];
                    const filePath = first.file_path || '';
                    if (filePath) {
                        image = '/storage/' + filePath.replace(/^\//, '');
                    }
                }

                return {
                    id: bid.bid_id,
                    projectId: bid.project_id,
                    projectTitle: bid.project_title || '—',
                    projectType: bid.type_name || bid.property_type || '',
                    description: bid.project_description || '',
                    location: bid.project_location || '—',
                    projectBudget: projectBudget,
                    bidAmount: bidAmount,
                    proposedCost: bid.proposed_cost || 0,
                    status: tabStatus,
                    rawStatus: bid.bid_status,
                    statusText: statusText,
                    submittedDate: submittedDate,
                    submittedAt: bid.submitted_at,
                    owner: {
                        name: ownerName,
                        avatar: initials || '—'
                    },
                    image: image,
                    statusInfo: statusInfo,
                    timeline: bid.estimated_timeline || '',
                    proposalMessage: bid.contractor_notes || '',
                    projectFiles: bid.project_files || [],
                    bidFiles: bid.bid_files || [],
                    specifications: [],
                    documents: []
                };
            });
        } else {
            this.bids = [];
        }
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

        // Expose global handlers for server-rendered onclick attributes
        window.handleViewBidDetails = (bidId) => {
            const bid = this.bids.find(b => b.id === bidId);
            if (bid) this.handleViewDetails(bid);
        };

        window.handleWithdrawBid = (bidId) => {
            const bid = this.bids.find(b => b.id === bidId);
            if (bid) this.handleWithdraw(bid);
        };

        window.handleEditBid = (projectId, bidData) => {
            if (window.openEditBidModal) {
                window.openEditBidModal(projectId, bidData);
            } else {
                console.error('openEditBidModal function not found');
            }
        };
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

        // Show/hide server-rendered cards based on data-status attribute
        const container = document.getElementById('bidsContainer');
        const emptyState = document.getElementById('emptyState');
        if (!container) return;

        const cards = container.querySelectorAll('.bid-card[data-status]');
        let visibleCount = 0;

        cards.forEach(card => {
            const cardStatus = card.getAttribute('data-status');
            if (cardStatus === filter) {
                card.style.display = '';
                visibleCount++;
            } else {
                card.style.display = 'none';
            }
        });

        // Show/hide empty state
        if (emptyState) {
            if (visibleCount === 0) {
                emptyState.classList.remove('hidden');
            } else {
                emptyState.classList.add('hidden');
            }
        }
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

        // Re-apply current tab filter with additional sort
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
        const container = document.getElementById('bidsContainer');
        const emptyState = document.getElementById('emptyState');
        if (!container) return;

        const cards = Array.from(container.querySelectorAll('.bid-card[data-status]'));
        let visibleCount = 0;

        // First, determine visibility
        cards.forEach(card => {
            const cardStatus = card.getAttribute('data-status');
            let visible = (cardStatus === this.currentTabFilter);

            // Additional status filter from dropdown
            if (visible && this.currentFilter.status !== 'all') {
                visible = (cardStatus === this.currentFilter.status);
            }

            if (visible) {
                card.style.display = '';
                visibleCount++;
            } else {
                card.style.display = 'none';
            }
        });

        // Sort visible cards
        const sortAttr = this.currentFilter.sort;
        const visibleCards = cards.filter(c => c.style.display !== 'none');

        visibleCards.sort((a, b) => {
            switch (sortAttr) {
                case 'newest':
                    return new Date(b.dataset.submitted || 0) - new Date(a.dataset.submitted || 0);
                case 'oldest':
                    return new Date(a.dataset.submitted || 0) - new Date(b.dataset.submitted || 0);
                case 'amount_high':
                    return parseFloat(b.dataset.amount || 0) - parseFloat(a.dataset.amount || 0);
                case 'amount_low':
                    return parseFloat(a.dataset.amount || 0) - parseFloat(b.dataset.amount || 0);
                default:
                    return 0;
            }
        });

        // Re-append in sorted order
        visibleCards.forEach(card => container.appendChild(card));

        // Show/hide empty state
        if (emptyState) {
            if (visibleCount === 0) {
                emptyState.classList.remove('hidden');
            } else {
                emptyState.classList.add('hidden');
            }
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

    handleWithdraw(bid) {
        console.log('Withdraw bid delegated to modal:', bid);
        if (window.showBidWithdrawConfirmation) {
            window.showBidWithdrawConfirmation(bid);
        } else {
            console.error('Withdraw confirmation helper not found');
            this.showNotification('Unable to initiate withdrawal', 'error');
        }
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
