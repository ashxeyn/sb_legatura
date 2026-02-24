/**
 * Project Details Modal JavaScript
 * Handles modal open/close and data population
 */

class ProjectDetailsModal {
    constructor() {
        this.modal = null;
        this.overlay = null;
        this.currentProject = null;

        this.init();
    }

    init() {
        // Get modal elements
        this.modal = document.getElementById('projectDetailsModal');
        this.overlay = document.getElementById('projectModalOverlay');

        if (!this.modal) {
            console.error('Project Details Modal not found');
            return;
        }

        // Setup event listeners
        this.setupEventListeners();
    }

    setupEventListeners() {
        // Close button
        const closeBtn = document.getElementById('closeProjectModalBtn');
        const closeModalBtn = document.getElementById('closeModalBtn');

        if (closeBtn) {
            closeBtn.addEventListener('click', () => this.close());
        }

        if (closeModalBtn) {
            closeModalBtn.addEventListener('click', () => this.close());
        }

        // Overlay click
        if (this.overlay) {
            this.overlay.addEventListener('click', () => this.close());
        }

        // View Full Project button
        const viewFullProjectBtn = document.getElementById('viewFullProjectBtn');
        if (viewFullProjectBtn) {
            viewFullProjectBtn.addEventListener('click', () => {
                if (this.currentProject) {
                    // Navigate to full project page or show notification
                    console.log('View full project:', this.currentProject.id);
                    // window.location.href = `/owner/projects/${this.currentProject.id}`;
                    this.showNotification('Full project view coming soon!');
                }
            });
        }

        // Review Milestone button
        const reviewMilestoneBtn = document.getElementById('reviewMilestoneBtn');
        if (reviewMilestoneBtn) {
            reviewMilestoneBtn.addEventListener('click', async () => {
                if (this.currentProject) {
                    try {
                        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;

                        const response = await fetch('/owner/projects/set-milestone', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': csrfToken || ''
                            },
                            body: JSON.stringify({
                                project_id: this.currentProject.id
                            })
                        });

                        const text = await response.text();
                        console.log('Session response text:', text); // Debugging

                        let data = {};
                        try {
                            data = JSON.parse(text);
                        } catch (e) {
                            console.error('Failed to parse response as JSON:', text);
                            throw new Error('Server returned invalid JSON for session set.');
                        }

                        if (data.success) {
                            console.log('Navigating to milestone report for project:', this.currentProject.id);
                            window.location.href = '/owner/projects/milestone-report';
                        } else {
                            console.error('Failed to set milestone session:', data.message);
                            this.showNotification('Could not navigate to milestone report.');
                        }
                    } catch (error) {
                        console.error('Error setting milestone session:', error);
                        this.showNotification('An error occurred. Please try again.');
                    }
                }
            });
        }

        // Close on Escape key
        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape' && this.isOpen()) {
                this.close();
            }
        });
    }

    open(project) {
        if (!project) {
            console.error('No project data provided');
            return;
        }

        this.currentProject = project;
        this.populateModal(project);

        if (this.modal) {
            this.modal.classList.add('active');
            document.body.style.overflow = 'hidden'; // Prevent background scrolling
        }
    }

    close() {
        if (this.modal) {
            this.modal.classList.remove('active');
            document.body.style.overflow = ''; // Restore scrolling
        }
        this.currentProject = null;
    }

    isOpen() {
        return this.modal && this.modal.classList.contains('active');
    }

    populateModal(project) {
        // Title and Location
        const titleElement = document.getElementById('modalProjectTitle');
        const locationText = document.getElementById('modalLocationText');

        if (titleElement) {
            titleElement.textContent = project.title || 'Untitled Project';
        }

        if (locationText) {
            locationText.textContent = project.location || 'Location not specified';
        }

        // Project Image
        const imageElement = document.getElementById('modalProjectImage');
        if (imageElement) {
            if (project.image) {
                imageElement.src = project.image;
                imageElement.alt = project.title || 'Project image';
            } else {
                imageElement.src = 'https://via.placeholder.com/800x300/EEA24B/ffffff?text=Project+Image';
                imageElement.alt = project.title || 'Project image';
            }
        }

        // Description
        const descriptionElement = document.getElementById('modalProjectDescription');
        if (descriptionElement) {
            descriptionElement.textContent = project.description || 'No description available.';
        }

        // Specifications
        this.populateSpecifications(project);

        // Lot Size and Floor Area
        const lotSizeElement = document.getElementById('modalLotSize');
        const floorAreaElement = document.getElementById('modalFloorArea');

        if (lotSizeElement) {
            lotSizeElement.textContent = project.lotSize || 'Not specified';
        }

        if (floorAreaElement) {
            floorAreaElement.textContent = project.floorArea || 'Not specified';
        }

        // Budget Range
        const budgetElement = document.getElementById('modalBudgetRange');
        if (budgetElement) {
            budgetElement.textContent = project.budget || 'Not specified';
        }

        // Contractor & Agreement
        this.populateContractorInfo(project);

        // Status and Progress
        this.populateStatusAndProgress(project);

        // Milestone Setup
        this.populateMilestoneData(project);
    }

    populateSpecifications(project) {
        const specsContainer = document.getElementById('modalSpecifications');
        if (!specsContainer) return;

        // Clear existing specifications
        specsContainer.innerHTML = '';

        // Default specifications based on project type
        const defaultSpecs = this.getDefaultSpecifications(project);

        // If project has custom specifications, use those
        const specifications = project.specifications || defaultSpecs;

        specifications.forEach(spec => {
            const specItem = document.createElement('div');
            specItem.className = 'spec-item';

            specItem.innerHTML = `
                <div class="spec-icon">
                    <i class="${spec.icon || 'fi fi-rr-settings'}"></i>
                </div>
                <div class="spec-content">
                    <span class="spec-label">${spec.label}</span>
                    <span class="spec-value">${spec.value}</span>
                </div>
            `;

            specsContainer.appendChild(specItem);
        });
    }

    getDefaultSpecifications(project) {
        // Generate specifications based on project data
        const specs = [];

        if (project.type) {
            specs.push({
                label: 'Project Type',
                value: project.type,
                icon: 'fi fi-rr-briefcase'
            });
        }

        if (project.bedrooms) {
            specs.push({
                label: 'Bedrooms',
                value: project.bedrooms,
                icon: 'fi fi-rr-bed'
            });
        }

        if (project.bathrooms) {
            specs.push({
                label: 'Bathrooms',
                value: project.bathrooms,
                icon: 'fi fi-rr-bath'
            });
        }

        if (project.floors) {
            specs.push({
                label: 'Floors',
                value: project.floors,
                icon: 'fi fi-rr-building'
            });
        }

        if (project.materials) {
            specs.push({
                label: 'Materials',
                value: project.materials,
                icon: 'fi fi-rr-hammer'
            });
        }

        if (project.style) {
            specs.push({
                label: 'Style',
                value: project.style,
                icon: 'fi fi-rr-palette'
            });
        }

        // If no specs found, add a default message
        if (specs.length === 0) {
            specs.push({
                label: 'Specifications',
                value: 'See project details',
                icon: 'fi fi-rr-info'
            });
        }

        return specs;
    }

    populateContractorInfo(project) {
        const contractor = project.contractor || {};

        // Avatar
        const avatarElement = document.getElementById('modalContractorAvatar');
        if (avatarElement) {
            avatarElement.textContent = contractor.initials || 'N/A';
        }

        // Name
        const nameElement = document.getElementById('modalContractorName');
        if (nameElement) {
            nameElement.textContent = contractor.company || contractor.name || 'No contractor assigned';
        }

        // Role
        const roleElement = document.getElementById('modalContractorRole');
        if (roleElement) {
            roleElement.textContent = contractor.role || 'Contractor';
        }

        // Rating
        const ratingElement = document.getElementById('modalContractorRating');
        if (ratingElement) {
            ratingElement.textContent = contractor.rating ? contractor.rating.toFixed(1) : 'N/A';
        }

        // Company
        const companyElement = document.getElementById('modalContractorCompany');
        if (companyElement) {
            companyElement.textContent = contractor.company || contractor.name || 'Not specified';
        }

        // Agreement Date
        const agreementDateElement = document.getElementById('modalAgreementDate');
        if (agreementDateElement) {
            const agreementDate = project.agreementDate || project.date || 'Not specified';
            agreementDateElement.textContent = `Agreement Date: ${agreementDate}`;
        }

        // Agreement Status
        const agreementStatusElement = document.getElementById('modalAgreementStatus');
        if (agreementStatusElement) {
            const agreementStatus = project.agreementStatus || 'Active';
            agreementStatusElement.textContent = `Status: ${agreementStatus}`;
        }
    }

    populateStatusAndProgress(project) {
        // Status Badge
        const statusBadge = document.getElementById('modalStatusBadge');
        const statusText = document.getElementById('modalStatusText');

        if (statusBadge && statusText) {
            // Remove existing status classes
            statusBadge.className = 'status-badge-modal';

            // Add appropriate status class
            const status = project.status || 'pending';
            statusBadge.classList.add(`status-${status}`);

            // Set status text
            statusText.textContent = status.replace('_', ' ').toUpperCase();
        }

        // Progress
        const progressPercentage = document.getElementById('modalProgressPercentage');
        const progressBar = document.getElementById('modalProgressBar');

        if (progressPercentage) {
            const progress = project.progress || 0;
            progressPercentage.textContent = `${progress}%`;
        }

        if (progressBar) {
            const progress = project.progress || 0;
            progressBar.style.width = `${progress}%`;
        }
    }

    populateMilestoneData(project) {
        // Milestone Count Indicator
        const milestoneCountElement = document.getElementById('modalMilestoneCount');
        if (milestoneCountElement) {
            const totalMilestones = project.milestones?.total || project.totalMilestones || 1;
            milestoneCountElement.textContent = totalMilestones;
        }

        // Total Milestones
        const totalMilestonesElement = document.getElementById('modalTotalMilestones');
        if (totalMilestonesElement) {
            const totalMilestones = project.milestones?.total || project.totalMilestones || 1;
            totalMilestonesElement.textContent = totalMilestones;
        }

        // Pending Approval
        const pendingApprovalElement = document.getElementById('modalPendingApproval');
        if (pendingApprovalElement) {
            const pendingApproval = project.milestones?.pendingApproval || project.pendingApproval || 0;
            pendingApprovalElement.textContent = pendingApproval;
        }

        // Total Cost
        const totalCostElement = document.getElementById('modalTotalCost');
        if (totalCostElement) {
            const totalCost = project.milestones?.totalCost || project.totalCost || '₱45,000,000';
            totalCostElement.textContent = totalCost;
        }
    }

    showNotification(message) {
        // Create a simple toast notification
        const toast = document.createElement('div');
        toast.className = 'fixed bottom-4 right-4 bg-orange-500 text-white px-6 py-3 rounded-lg shadow-lg z-50';
        toast.textContent = message;
        toast.style.cssText = `
            animation: slideUp 0.3s ease-out;
        `;

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

// Initialize modal when DOM is ready
let projectDetailsModalInstance = null;

document.addEventListener('DOMContentLoaded', () => {
    projectDetailsModalInstance = new ProjectDetailsModal();
});

// Export for use in other scripts
window.ProjectDetailsModal = ProjectDetailsModal;
window.openProjectDetailsModal = (project) => {
    if (projectDetailsModalInstance) {
        projectDetailsModalInstance.open(project);
    } else {
        // If modal hasn't been initialized yet, wait and try again
        setTimeout(() => {
            if (projectDetailsModalInstance) {
                projectDetailsModalInstance.open(project);
            }
        }, 100);
    }
};
