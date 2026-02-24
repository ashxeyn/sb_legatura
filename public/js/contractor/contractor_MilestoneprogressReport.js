/**
 * Contractor Milestone Progress Report JavaScript
 * Handles milestone progress report display and submission
 */

class ContractorMilestoneProgressReport {
    constructor() {
        this.milestoneData = null;
        this.projectId = null;
        this.milestoneId = null;

        this.init();
    }

    init() {
        // Get project ID and milestone ID from URL parameters if available
        const urlParams = new URLSearchParams(window.location.search);
        this.projectId = urlParams.get('project_id');
        this.milestoneId = urlParams.get('milestone_id');

        // Setup event listeners
        this.setupEventListeners();

        // Load milestone progress data
        this.loadMilestoneProgressData();
    }

    setupEventListeners() {
        // Submit Progress Report button
        const submitProgressBtn = document.getElementById('submitProgressBtn');
        if (submitProgressBtn) {
            submitProgressBtn.addEventListener('click', () => {
                this.openProgressReportModal();
            });
        }
    }

    openProgressReportModal() {
        // Open the progress report modal for submitting
        if (window.openProgressReportModal) {
            window.openProgressReportModal({
                projectId: this.projectId,
                milestoneId: this.milestoneId,
                projectTitle: this.milestoneData?.projectTitle || 'Project',
                milestoneTitle: this.milestoneData?.milestoneTitle || 'Milestone'
            });
        } else {
            console.error('Progress report modal not available');
            this.showNotification('Progress report modal not available', 'error');
        }
    }

    loadMilestoneProgressData() {
        // Sample milestone progress data - Replace with actual API call
        // In a real implementation, fetch from server based on projectId and milestoneId
        this.milestoneData = {
            projectId: this.projectId || 1,
            milestoneId: this.milestoneId || 1,
            projectTitle: "Modern Residential House Construction",
            milestoneTitle: "Structural Framing",
            projectCategory: "Residential",
            projectType: "House Construction",
            progress: 30,
            status: "approved",
            cost: "₱10,800,000",
            costPercentage: 30,
            date: "2024-03-15",
            description: "Complete structural framework including foundation, columns, beams, and roof structure.",
            reports: [
                {
                    id: 1,
                    title: "Progress Report 1.4",
                    date: "Tuesday, 28 May 2024",
                    description: "Final inspection completed. All structural elements verified and approved. Quality checks passed with excellent results. Ready for next phase.",
                    status: "not_submitted", // not_submitted, pending, approved
                    hasReport: false,
                    files: []
                },
                {
                    id: 2,
                    title: "Progress Report 1.3",
                    date: "Monday, 20 May 2024",
                    description: "Roof structure installation in progress. All beams and columns are properly aligned. Weather conditions favorable for continued work.",
                    status: "pending", // Submitted but waiting for approval
                    hasReport: true,
                    files: []
                },
                {
                    id: 3,
                    title: "Progress Report 1.2",
                    date: "Friday, 10 May 2024",
                    description: "Foundation work completed successfully. All inspections passed. Structural framework beginning. Materials delivered on schedule.",
                    status: "approved", // Submitted and approved
                    hasReport: true,
                    files: [
                        {
                            name: "Foundation-Report.pdf",
                            type: "pdf",
                            url: "#",
                            size: 987654
                        },
                        {
                            name: "Material-Delivery-Receipt.pdf",
                            type: "pdf",
                            url: "#",
                            size: 456789
                        }
                    ]
                },
                {
                    id: 4,
                    title: "Progress Report 1.1",
                    date: "Wednesday, 1 May 2024",
                    description: "Project initiation and site preparation completed. Foundation excavation started. Initial materials ordered and scheduled for delivery.",
                    status: "approved", // Submitted and approved
                    hasReport: true,
                    files: [
                        {
                            name: "Project-Initiation-Report.pdf",
                            type: "pdf",
                            url: "#",
                            size: 567890
                        }
                    ]
                }
            ]
        };

        this.renderMilestoneProgress();

        // Setup event listeners after initial render
        this.setupEventListeners();
    }

    setupEventListeners() {
        // Add event listeners after rendering
        setTimeout(() => {
            // View more links
            const viewMoreLinks = document.querySelectorAll('.report-view-more');
            viewMoreLinks.forEach(link => {
                link.addEventListener('click', (e) => {
                    e.preventDefault();
                    e.stopPropagation();
                    const reportId = link.getAttribute('data-report-id');
                    this.handleViewMore(reportId);
                });
            });

            // Report item click handlers for expand/collapse
            const reportItems = document.querySelectorAll('.progress-report-item');
            reportItems.forEach(item => {
                // Click on description to expand/collapse
                const description = item.querySelector('.report-description');
                if (description) {
                    description.addEventListener('click', (e) => {
                        e.stopPropagation();
                        this.toggleReportDescription(item);
                    });
                }

                // Click on entire item to expand/collapse
                item.addEventListener('click', (e) => {
                    // Don't trigger if clicking on "View more" link
                    if (!e.target.closest('.report-view-more')) {
                        this.toggleReportDescription(item);
                    }
                });
            });
        }, 100);
    }

    toggleReportDescription(reportItem) {
        const description = reportItem.querySelector('.report-description');
        const isExpanded = reportItem.classList.contains('expanded');

        if (isExpanded) {
            // Collapse
            reportItem.classList.remove('expanded');
            description.classList.remove('expanded');
            this.animateCollapse(description);
        } else {
            // Expand
            reportItem.classList.add('expanded');
            description.classList.add('expanded');
            this.animateExpand(description);

            // Smooth scroll to expanded item if needed
            setTimeout(() => {
                const rect = reportItem.getBoundingClientRect();
                const isInViewport = rect.top >= 0 && rect.bottom <= window.innerHeight;

                if (!isInViewport) {
                    reportItem.scrollIntoView({
                        behavior: 'smooth',
                        block: 'center'
                    });
                }
            }, 100);
        }
    }

    animateExpand(element) {
        element.style.maxHeight = '0';
        element.style.opacity = '0';

        requestAnimationFrame(() => {
            element.style.transition = 'max-height 0.4s cubic-bezier(0.4, 0, 0.2, 1), opacity 0.4s ease';
            element.style.maxHeight = element.scrollHeight + 'px';
            element.style.opacity = '1';
        });
    }

    animateCollapse(element) {
        element.style.maxHeight = element.scrollHeight + 'px';
        element.style.opacity = '1';

        requestAnimationFrame(() => {
            element.style.transition = 'max-height 0.4s cubic-bezier(0.4, 0, 0.2, 1), opacity 0.4s ease';
            element.style.maxHeight = '0';
            element.style.opacity = '0';
        });
    }

    handleViewMore(reportId) {
        const report = this.milestoneData.reports.find(r => r.id === parseInt(reportId));
        if (report) {
            // Check report status to determine which modal to open
            const isNotSubmitted = report.status === 'not_submitted' || !report.status;

            if (isNotSubmitted) {
                // Open submission modal for creating new report
                if (window.openProgressReportModal) {
                    window.openProgressReportModal({
                        projectId: this.projectId,
                        milestoneId: this.milestoneId,
                        reportId: reportId,
                        projectTitle: this.milestoneData?.projectTitle || 'Project',
                        milestoneTitle: this.milestoneData?.milestoneTitle || 'Milestone'
                    });
                } else {
                    console.error('Progress Report Modal not initialized');
                    this.showNotification('Progress report submission modal not available', 'error');
                }
            } else {
                // Open view modal for viewing existing report
                if (window.openViewProgressReportModal) {
                    window.openViewProgressReportModal(report, this.milestoneData);
                } else {
                    console.error('View Progress Report Modal not initialized');
                    this.showNotification('Progress report view modal not available', 'error');
                }
            }
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

    renderMilestoneProgress() {
        const container = document.getElementById('milestoneProgressContainer');
        if (!container || !this.milestoneData) return;

        // Render milestone progress content
        container.innerHTML = `
            <div class="milestone-progress-content">
                <div class="progress-header mb-6">
                    <h2 class="text-2xl font-bold text-gray-900 mb-2">${this.milestoneData.projectTitle || 'Project'}</h2>
                    <h3 class="text-xl font-semibold text-gray-700 mb-4">${this.milestoneData.milestoneTitle || 'Milestone Progress'}</h3>
                    
                    <div class="progress-stats flex gap-4 text-sm text-gray-600 mb-6 pb-6 border-b border-gray-200">
                        <span><i class="fi fi-rr-chart-line-up"></i> Progress: ${this.milestoneData.progress}%</span>
                        <span><i class="fi fi-rr-money"></i> Cost: ${this.milestoneData.cost}</span>
                        <span><i class="fi fi-rr-calendar"></i> Date: ${this.milestoneData.date}</span>
                    </div>
                </div>
                
                <div class="progress-description mb-6">
                    <h4 class="text-lg font-semibold text-gray-900 mb-2">Description</h4>
                    <p class="text-gray-600">${this.milestoneData.description || 'No description available.'}</p>
                </div>
                
                <div class="progress-reports-section">
                    <h4 class="progress-reports-title text-lg font-semibold text-gray-900 mb-6">Progress Reports</h4>
                    <div class="progress-reports-timeline">
                        ${this.milestoneData.reports && this.milestoneData.reports.length > 0
                ? this.milestoneData.reports.map((report, index) => {
                    const statusClass = report.status || 'not_submitted';
                    const isApproved = report.status === 'approved';
                    const isPending = report.status === 'pending';
                    const isNotSubmitted = report.status === 'not_submitted' || !report.status;

                    // Determine icon and colors based on status
                    let iconClass = 'fi fi-rr-cross'; // default for not submitted
                    let iconColor = '#9ca3af'; // gray

                    if (isApproved) {
                        iconClass = 'fi fi-rr-check';
                        iconColor = '#10b981'; // green
                    } else if (isPending) {
                        iconClass = 'fi fi-rr-clock';
                        iconColor = '#f59e0b'; // orange
                    }

                    return `
                                    <div class="progress-report-item report-status-${statusClass}" data-report-id="${report.id}">
                                    <div class="report-timeline-marker">
                                            <div class="report-checkmark-icon" style="background-color: ${iconColor}; border-color: ${iconColor};">
                                                <i class="${iconClass}"></i>
                                        </div>
                                    </div>
                                    <div class="report-content">
                                        <div class="report-header-content">
                                            <h5 class="report-title">${report.title}</h5>
                                                <a href="#" class="report-view-more" data-report-id="${report.id}">
                                                    ${isNotSubmitted ? 'Submit Progress Report' : (isPending ? 'View Report (Pending)' : 'View Report')}
                                                </a>
                                        </div>
                                        <p class="report-description" title="Click to expand/collapse">${report.description || ''}</p>
                                            <div class="report-footer-info">
                                        <p class="report-date">${report.date}</p>
                                                ${isPending ? '<span class="report-status-badge pending">Pending Approval</span>' : ''}
                                                ${isApproved ? '<span class="report-status-badge approved">Approved</span>' : ''}
                                                ${isNotSubmitted ? '<span class="report-status-badge not-submitted">Not Submitted</span>' : ''}
                                            </div>
                                    </div>
                                </div>
                                `;
                }).join('')
                : '<p class="text-gray-500 text-center py-8">No progress reports available</p>'
            }
                    </div>
                </div>
            </div>
        `;

        // Re-setup event listeners after rendering
        this.setupEventListeners();
    }
}

// Initialize when DOM is ready
document.addEventListener('DOMContentLoaded', () => {
    new ContractorMilestoneProgressReport();
});
