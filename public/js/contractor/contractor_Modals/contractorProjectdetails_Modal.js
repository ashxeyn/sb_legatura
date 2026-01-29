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
            reviewMilestoneBtn.addEventListener('click', () => {
                if (this.currentProject) {
                    // Navigate to contractor milestone report page with project ID
                    const milestoneReportUrl = `/contractor/projects/milestone-report?project_id=${this.currentProject.id}`;
                    window.location.href = milestoneReportUrl;
                    console.log('Navigating to milestone report for project:', this.currentProject.id);
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
        const owner = project.owner || {};
        
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

        // Email
        const emailElement = document.getElementById('modalOwnerEmail');
        if (emailElement) {
            emailElement.textContent = owner.email || 'Not provided';
        }

        // Phone
        const phoneElement = document.getElementById('modalOwnerPhone');
        if (phoneElement) {
            phoneElement.textContent = owner.phone || 'Not provided';
        }

        // Project Posted Date
        const projectPostedElement = document.getElementById('modalProjectPosted');
        if (projectPostedElement) {
            const postedDate = project.date || project.createdAt || 'Not specified';
            projectPostedElement.textContent = postedDate;
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
        const milestoneSection = document.getElementById('milestoneSection');
        
        // Always show milestone section like owner's modal
        if (milestoneSection) {
            milestoneSection.style.display = 'block';
        }

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

        // Completed Milestones
        const completedMilestonesElement = document.getElementById('modalCompletedMilestones');
        if (completedMilestonesElement) {
            const completedMilestones = project.milestones?.completed || project.completedMilestones || 0;
            completedMilestonesElement.textContent = completedMilestones;
        }

        // Total Cost
        const totalCostElement = document.getElementById('modalTotalCost');
        if (totalCostElement) {
            const totalCost = project.milestones?.totalCost || project.totalCost || project.budget || '₱0';
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
