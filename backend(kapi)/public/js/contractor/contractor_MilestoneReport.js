/**
 * Contractor Milestone Report JavaScript
 * Handles milestone report display and interactions
 */

class ContractorMilestoneReport {
    constructor() {
        this.milestoneData = null;
        this.projectId = null;
        
        this.init();
    }

    init() {
        // Get project ID from URL parameters if available
        const urlParams = new URLSearchParams(window.location.search);
        this.projectId = urlParams.get('project_id');
        
        // Load milestone data
        this.loadMilestoneData();
        
        // Setup event listeners
        this.setupEventListeners();
    }

    loadMilestoneData() {
        // Sample milestone data - Replace with actual API call
        // In a real implementation, fetch from server based on projectId
        this.milestoneData = {
            projectId: this.projectId || 1,
            projectTitle: "Modern Residential House Construction",
            location: "Tumaga, Zamboanga City",
            description: "A beautiful 3-bedroom, 2-bathroom modern house with open floor plan, large windows, and sustainable materials. Includes full kitchen, living room, and attached garage. The design features contemporary architecture with energy-efficient systems and smart home integration.",
            totalMilestones: 4,
            pendingApproval: 0,
            totalCost: "₱36,000,000",
            milestones: [
                {
                    id: 4,
                    title: "Interior and Exterior Finishing",
                    status: "approved",
                    progress: 100,
                    cost: "₱3,600,000",
                    costPercentage: 10,
                    date: "2024-06-30",
                    description: "Final interior and exterior finishing touches including paint, flooring, and landscaping."
                },
                {
                    id: 3,
                    title: "Electrical and Plumbing Installation",
                    status: "approved",
                    progress: 90,
                    cost: "₱10,800,000",
                    costPercentage: 30,
                    date: "2024-05-31",
                    description: "Complete electrical wiring, plumbing systems, and fixture installations."
                },
                {
                    id: 2,
                    title: "Masonry and Wall Finishing",
                    status: "approved",
                    progress: 60,
                    cost: "₱10,800,000",
                    costPercentage: 30,
                    date: "2024-04-30",
                    description: "Masonry work, wall construction, and finishing materials installation."
                    
                },
                {
                    id: 1,
                    title: "Structural Framing",
                    status: "approved",
                    progress: 30,
                    cost: "₱10,800,000",
                    costPercentage: 30,
                    date: "2024-03-15",
                    description: "Complete structural framework including foundation, columns, beams, and roof structure."
                    
                }
            ]
        };
        
        this.renderMilestoneReport();
    }

    setupEventListeners() {
        // Milestone click handlers will be added after rendering
    }

    handleMilestoneClick(milestoneId) {
        console.log('handleMilestoneClick called with ID:', milestoneId);
        const milestone = this.milestoneData.milestones.find(m => m.id === milestoneId);
        console.log('Found milestone:', milestone);
        
        if (milestone) {
            const projectId = this.projectId || this.milestoneData.projectId;
            // Navigate to contractor milestone progress report page with project and milestone IDs
            const progressReportUrl = `/contractor/projects/milestone-progress-report?project_id=${projectId}&milestone_id=${milestoneId}`;
            console.log('Navigating to:', progressReportUrl);
            window.location.href = progressReportUrl;
        } else {
            console.error('Milestone not found:', milestoneId);
        }
    }

    handlePaymentHistory() {
        // Prepare payment data
        const paymentData = this.getPaymentHistoryData();
        const projectData = {
            projectId: this.milestoneData.projectId,
            projectTitle: this.milestoneData.projectTitle
        };

        // Open the payment history modal
        if (window.openPaymentHistoryModal) {
            window.openPaymentHistoryModal(paymentData, projectData);
        } else {
            // Fallback if modal is not loaded yet
            console.error('Payment History Modal not initialized');
            this.showNotification('Payment history feature coming soon!');
        }
    }

    getPaymentHistoryData() {
        // Sample payment history data - Replace with actual API call
        return {
            payments: [
                {
                    id: 1,
                    type: "Bank Payment",
                    milestoneNumber: 4,
                    milestoneId: 4,
                    amount: "₱3,600,000",
                    date: "05/15/2024",
                    time: "10:30 AM",
                    status: "pending",
                    unread: true
                },
                {
                    id: 2,
                    type: "Bank Payment",
                    milestoneNumber: 3,
                    milestoneId: 3,
                    amount: "₱10,800,000",
                    date: "04/20/2024",
                    time: "02:15 PM",
                    status: "completed",
                    unread: false
                },
                {
                    id: 3,
                    type: "Bank Payment",
                    milestoneNumber: 2,
                    milestoneId: 2,
                    amount: "₱10,800,000",
                    date: "03/25/2024",
                    time: "09:45 AM",
                    status: "completed",
                    unread: false
                },
                {
                    id: 4,
                    type: "Bank Payment",
                    milestoneNumber: 1,
                    milestoneId: 1,
                    amount: "₱10,800,000",
                    date: "02/10/2024",
                    time: "11:20 AM",
                    status: "completed",
                    unread: false
                }
            ],
            summary: {
                totalEstimated: "₱36,000,000",
                totalPaid: 32400000, // Will be formatted as -₱32,400,000
                totalRemaining: "₱3,600,000"
            }
        };
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

    renderMilestoneReport() {
        const container = document.getElementById('milestoneReportContainer');
        if (!container || !this.milestoneData) return;

        // Render milestone report content
        container.innerHTML = `
            <div class="milestone-report-content">
                <div class="report-header mb-6">
                    <h2 class="text-2xl font-bold text-gray-900 mb-3">${this.milestoneData.projectTitle || 'Milestone Report'}</h2>
                    
                    ${this.milestoneData.description ? `
                        <div class="project-description-section mb-6">
                            <h3 class="description-title">Project Description</h3>
                            <p class="description-text">${this.milestoneData.description}</p>
                        </div>
                    ` : ''}
                    
                    <div class="project-stats flex gap-4 text-sm text-gray-600 mb-6 pb-6 border-b border-gray-200">
                        ${this.milestoneData.location ? `
                            <span class="location-stat">
                                <i class="fi fi-rr-marker"></i>
                                ${this.milestoneData.location}
                            </span>
                        ` : ''}
                        <span><i class="fi fi-rr-calendar"></i> Total Milestones: ${this.milestoneData.totalMilestones}</span>
                        <span><i class="fi fi-rr-clock"></i> Pending Approval: ${this.milestoneData.pendingApproval}</span>
                        <span><i class="fi fi-rr-money"></i> Total Cost: ${this.milestoneData.totalCost}</span>
                    </div>
                </div>
                
                <!-- Milestone Timeline -->
                <div class="milestone-timeline-container">
                    <div class="milestone-timeline">
                        <!-- Milestones (rendered in reverse order for column-reverse display) -->
                        ${this.milestoneData.milestones && this.milestoneData.milestones.length > 0
                            ? [...this.milestoneData.milestones].reverse().map((milestone, reverseIndex) => {
                                // Calculate original index for alternating left/right
                                const originalIndex = this.milestoneData.milestones.length - 1 - reverseIndex;
                                const isEven = originalIndex % 2 === 0;
                                return `
                                    <div class="milestone-timeline-item ${isEven ? 'milestone-right' : 'milestone-left'}" 
                                         data-milestone-id="${milestone.id}"
                                         title="Click to view progress reports"
                                         style="cursor: pointer;">
                                        <div class="milestone-node" style="background: linear-gradient(135deg, #EEA24B 0%, #F57C00 100%);">
                                            <span class="milestone-progress-number">${milestone.progress}</span>
                                        </div>
                                        <div class="milestone-content ${isEven ? 'milestone-content-right' : 'milestone-content-left'}">
                                            <div class="milestone-number">Milestone ${milestone.id}</div>
                                            <div class="milestone-title">${milestone.title}</div>
                                            <div class="milestone-cost">${milestone.cost}</div>
                                            <div class="milestone-percentage">${milestone.costPercentage}%</div>
                                            <div style="font-size: 0.75rem; color: #f97316; margin-top: 0.5rem; font-weight: 600;">
                                                <i class="fi fi-rr-arrow-right"></i> Click to view details
                                            </div>
                                        </div>
                                    </div>
                                `;
                            }).join('')
                            : ''
                        }
                        
                        <!-- Start Point (at bottom) -->
                        <div class="timeline-start">
                            <div class="start-node"></div>
                            <div class="start-label">
                                <div class="start-text">Start</div>
                                <div class="start-percentage">0%</div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Payment History Button -->
                    <div class="payment-history-container">
                        <button class="payment-history-btn" id="paymentHistoryBtn">
                            Payment history
                        </button>
                    </div>
                </div>
            </div>
        `;
        
        // Add click event listeners to milestones
        setTimeout(() => {
            const milestoneItems = container.querySelectorAll('.milestone-timeline-item');
            console.log('Found milestone items:', milestoneItems.length);
            milestoneItems.forEach(item => {
                item.addEventListener('click', (e) => {
                    e.preventDefault();
                    const milestoneId = parseInt(item.getAttribute('data-milestone-id'));
                    console.log('Milestone clicked:', milestoneId);
                    this.handleMilestoneClick(milestoneId);
                });
                // Add visual feedback
                item.style.cursor = 'pointer';
            });
            
            // Payment history button
            const paymentHistoryBtn = document.getElementById('paymentHistoryBtn');
            if (paymentHistoryBtn) {
                paymentHistoryBtn.addEventListener('click', () => {
                    this.handlePaymentHistory();
                });
            }
        }, 100);
    }
}

// Initialize when DOM is ready
document.addEventListener('DOMContentLoaded', () => {
    new ContractorMilestoneReport();
});
