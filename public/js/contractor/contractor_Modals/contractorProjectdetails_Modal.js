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

                        const response = await fetch('/contractor/projects/set-milestone', {
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
                            window.location.href = '/contractor/projects/milestone-report';
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
        // Try to get PHP-precomputed data if available
        const phpData = (window.projectDetailsData && project.id)
            ? window.projectDetailsData[project.id]
            : null;

        // Title and Location
        const titleElement = document.getElementById('modalProjectTitle');
        const locationText = document.getElementById('modalLocationText');

        if (titleElement) {
            titleElement.textContent = (phpData ? phpData.title : null) || project.title || 'Untitled Project';
        }

        if (locationText) {
            locationText.textContent = (phpData ? phpData.location : null) || project.location || 'Location not specified';
        }

        // Project Image
        const imageElement = document.getElementById('modalProjectImage');
        if (imageElement) {
            const imgSrc = (phpData ? phpData.image : null) || project.image || '';
            if (imgSrc) {
                imageElement.src = imgSrc;
                imageElement.alt = (phpData ? phpData.title : null) || project.title || 'Project image';
            } else {
                imageElement.src = 'https://via.placeholder.com/800x300/EEA24B/ffffff?text=Project+Image';
                imageElement.alt = project.title || 'Project image';
            }
        }

        // Description
        const descriptionElement = document.getElementById('modalProjectDescription');
        if (descriptionElement) {
            descriptionElement.textContent = (phpData ? phpData.description : null) || project.description || 'No description available.';
        }

        // Specifications
        this.populateSpecifications(project, phpData);

        // Lot Size and Floor Area — use PHP-precomputed values
        const lotSizeElement = document.getElementById('modalLotSize');
        const floorAreaElement = document.getElementById('modalFloorArea');

        if (lotSizeElement) {
            lotSizeElement.textContent = (phpData ? phpData.lotSize : null) || project.lotSize || 'Not specified';
        }

        if (floorAreaElement) {
            floorAreaElement.textContent = (phpData ? phpData.floorArea : null) || project.floorArea || 'Not specified';
        }

        // Budget Range — use PHP-precomputed value
        const budgetElement = document.getElementById('modalBudgetRange');
        if (budgetElement) {
            budgetElement.textContent = (phpData ? phpData.budget : null) || project.budget || 'Not specified';
        }

        // Owner Info — use PHP-precomputed values
        this.populateContractorInfo(project, phpData);

        // Status and Progress — use PHP-precomputed values
        this.populateStatusAndProgress(project, phpData);

        // Milestone Setup — use PHP-precomputed values
        this.populateMilestoneData(project, phpData);
    }

    populateSpecifications(project, phpData) {
        const specsContainer = document.getElementById('modalSpecifications');
        if (!specsContainer) return;

        // Clear existing specifications
        specsContainer.innerHTML = '';

        // Default specifications based on project type
        const defaultSpecs = this.getDefaultSpecifications(project, phpData);

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

    getDefaultSpecifications(project, phpData) {
        // Generate specifications based on project data
        const specs = [];

        const projectType = (phpData ? phpData.type : null) || project.type;
        if (projectType) {
            specs.push({
                label: 'Project Type',
                value: projectType,
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

    populateContractorInfo(project, phpData) {
        const owner = phpData ? phpData.owner : (project.owner || {});

        // Avatar
        const avatarElement = document.getElementById('modalOwnerAvatar');
        if (avatarElement) {
            avatarElement.textContent = owner.initials || owner.name?.charAt(0) || 'O';
        }

        // Name
        const nameElement = document.getElementById('modalOwnerName');
        if (nameElement) {
            nameElement.textContent = owner.name || 'Property Owner';
        }

        // Email — from PHP-precomputed data
        const emailElement = document.getElementById('modalOwnerEmail');
        if (emailElement) {
            emailElement.textContent = owner.email || 'Not provided';
        }

        // Phone — from PHP-precomputed data
        const phoneElement = document.getElementById('modalOwnerPhone');
        if (phoneElement) {
            phoneElement.textContent = owner.phone || 'Not provided';
        }

        // Project Posted Date — from PHP-precomputed data
        const projectPostedElement = document.getElementById('modalProjectPosted');
        if (projectPostedElement) {
            const postedDate = (phpData ? phpData.postedDate : null) || project.date || project.createdAt || 'Not specified';
            projectPostedElement.textContent = postedDate;
        }
    }

    populateStatusAndProgress(project, phpData) {
        // Status Badge
        const statusBadge = document.getElementById('modalStatusBadge');
        const statusText = document.getElementById('modalStatusText');

        if (statusBadge && statusText) {
            // Remove existing status classes
            statusBadge.className = 'status-badge-modal';

            // Add appropriate status class — prefer PHP-precomputed
            const status = (phpData ? phpData.status : null) || project.status || 'pending';
            statusBadge.classList.add(`status-${status}`);

            // Set status text — prefer PHP-precomputed
            const displayText = (phpData ? phpData.statusText : null) || status.replace('_', ' ').toUpperCase();
            statusText.textContent = displayText;
        }

        // Progress — prefer PHP-precomputed
        const progressPercentage = document.getElementById('modalProgressPercentage');
        const progressBar = document.getElementById('modalProgressBar');
        const progress = (phpData ? phpData.progress : null) ?? project.progress ?? 0;

        if (progressPercentage) {
            progressPercentage.textContent = `${progress}%`;
        }

        if (progressBar) {
            progressBar.style.width = `${progress}%`;
        }
    }

    populateMilestoneData(project, phpData) {
        const milestoneSection = document.getElementById('milestoneSection');

        // Always show milestone section like owner's modal
        if (milestoneSection) {
            milestoneSection.style.display = 'block';
        }

        // Use PHP-precomputed milestone data if available
        const phpMilestones = phpData ? phpData.milestones : null;

        // Milestone Count Indicator
        const milestoneCountElement = document.getElementById('modalMilestoneCount');
        if (milestoneCountElement) {
            const totalMilestones = (phpMilestones ? phpMilestones.total : null) || project.milestones?.total || project.totalMilestones || 0;
            milestoneCountElement.textContent = totalMilestones;
        }

        // Total Milestones
        const totalMilestonesElement = document.getElementById('modalTotalMilestones');
        if (totalMilestonesElement) {
            const totalMilestones = (phpMilestones ? phpMilestones.total : null) || project.milestones?.total || project.totalMilestones || 0;
            totalMilestonesElement.textContent = totalMilestones;
        }

        // Completed Milestones
        const completedMilestonesElement = document.getElementById('modalCompletedMilestones');
        if (completedMilestonesElement) {
            const completedMilestones = (phpMilestones ? phpMilestones.completed : null) ?? project.milestones?.completed ?? project.completedMilestones ?? 0;
            completedMilestonesElement.textContent = completedMilestones;
        }

        // Total Cost
        const totalCostElement = document.getElementById('modalTotalCost');
        if (totalCostElement) {
            const totalCost = (phpMilestones ? phpMilestones.totalCost : null) || project.milestones?.totalCost || project.totalCost || project.budget || '₱0';
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
