/**
 * Contractor My Bids Details Modal JavaScript
 * Handles bid details modal display and interactions
 */

class ContractorBidDetailsModal {
    constructor() {
        this.modal = null;
        this.overlay = null;
        this.currentBid = null;

        this.init();
    }

    init() {
        this.modal = document.getElementById('bidDetailsModal');
        this.overlay = document.getElementById('bidModalOverlay');
        this.confirmationModal = document.getElementById('withdrawConfirmationModal');
        this.confirmationOverlay = document.getElementById('withdrawConfirmationOverlay');

        if (!this.modal || !this.overlay) {
            console.error('Bid Details Modal elements not found');
            return;
        }

        this.setupEventListeners();
    }

    setupEventListeners() {
        // Close buttons
        const closeBtn = document.getElementById('closeBidModalBtn');
        const closeFooterBtn = document.getElementById('closeBidModalFooterBtn');

        if (closeBtn) {
            closeBtn.addEventListener('click', () => this.close());
        }

        if (closeFooterBtn) {
            closeFooterBtn.addEventListener('click', () => this.close());
        }

        // Overlay click to close
        if (this.overlay) {
            this.overlay.addEventListener('click', () => this.close());
        }

        // Withdraw bid button
        const withdrawBtn = document.getElementById('withdrawBidBtn');
        if (withdrawBtn) {
            withdrawBtn.addEventListener('click', () => this.showWithdrawConfirmation());
        }

        // Confirmation modal buttons
        const cancelWithdrawBtn = document.getElementById('cancelWithdrawBtn');
        const confirmWithdrawBtn = document.getElementById('confirmWithdrawBtn');

        if (cancelWithdrawBtn) {
            cancelWithdrawBtn.addEventListener('click', () => this.closeWithdrawConfirmation());
        }

        if (confirmWithdrawBtn) {
            confirmWithdrawBtn.addEventListener('click', () => this.confirmWithdraw());
        }

        // Confirmation overlay click to close
        if (this.confirmationOverlay) {
            this.confirmationOverlay.addEventListener('click', () => this.closeWithdrawConfirmation());
        }

        // Close on Escape key
        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape') {
                if (this.isConfirmationOpen()) {
                    this.closeWithdrawConfirmation();
                } else if (this.isOpen()) {
                    this.close();
                }
            }
        });
    }

    open(bidData) {
        if (!bidData) {
            console.error('No bid data provided');
            return;
        }

        this.currentBid = bidData;
        this.populateModal(bidData);

        if (this.modal) {
            this.modal.classList.add('active');
            document.body.style.overflow = 'hidden';
        }
    }

    close() {
        if (this.modal) {
            this.modal.classList.remove('active');
            document.body.style.overflow = '';
        }
        this.currentBid = null;
    }

    isOpen() {
        return this.modal && this.modal.classList.contains('active');
    }

    populateModal(bid) {
        // Bid Status Banner
        this.populateStatusBanner(bid);

        // Project Information
        this.populateProjectInfo(bid);

        // Project Specifications
        this.populateSpecifications(bid);

        // Project Documents
        this.populateDocuments(bid);

        // Your Bid
        this.populateYourBid(bid);

        // Your Bid Documents
        this.populateBidDocuments(bid);

        // Show/hide withdraw button based on status
        this.updateWithdrawButton(bid);
    }

    populateStatusBanner(bid) {
        const statusBanner = document.getElementById('bidStatusBanner');
        const statusValue = document.getElementById('modalBidStatus');
        const submittedDate = document.getElementById('modalBidSubmittedDate');

        if (statusBanner && bid.status) {
            // Remove existing status classes
            statusBanner.className = 'bid-status-banner';
            statusBanner.classList.add(`status-${bid.status}`);
        }

        if (statusValue) {
            statusValue.textContent = bid.statusText || 'Unknown';
        }

        if (submittedDate) {
            submittedDate.textContent = `Submitted: ${bid.submittedDate || 'Unknown date'}`;
        }
    }

    populateProjectInfo(bid) {
        // Project Image
        const projectImage = document.getElementById('modalBidProjectImage');
        if (projectImage) {
            projectImage.src = bid.image || 'https://via.placeholder.com/150x150/EEA24B/ffffff?text=Project';
            projectImage.alt = bid.projectTitle || 'Project image';
        }

        // Project Title
        const projectTitle = document.getElementById('modalBidProjectTitle');
        if (projectTitle) {
            projectTitle.textContent = bid.projectTitle || 'Untitled Project';
        }

        // Project Type
        const projectType = document.getElementById('modalBidProjectType');
        if (projectType) {
            projectType.textContent = bid.projectType || 'General Contractor';
        }

        // Location
        const locationText = document.getElementById('modalBidLocationText');
        if (locationText) {
            locationText.textContent = bid.location || 'Location not specified';
        }

        // Owner Info
        const ownerAvatar = document.getElementById('modalBidOwnerAvatar');
        const ownerName = document.getElementById('modalBidOwnerName');

        if (ownerAvatar && bid.owner) {
            ownerAvatar.textContent = bid.owner.avatar || 'OW';
        }

        if (ownerName && bid.owner) {
            ownerName.textContent = bid.owner.name || 'Property Owner';
        }

        // Project Description
        const projectDescription = document.getElementById('modalBidProjectDescription');
        if (projectDescription) {
            projectDescription.textContent = bid.description || 'No description available.';
        }
    }

    populateSpecifications(bid) {
        const specificationsGrid = document.getElementById('modalBidSpecifications');

        if (!specificationsGrid) return;

        // Sample specifications - replace with actual bid.specifications
        const specifications = bid.specifications || [
            { icon: 'fi-rr-bed', label: 'Bedrooms', value: '4 Bedrooms' },
            { icon: 'fi-rr-bath', label: 'Bathrooms', value: '3 Bathrooms' },
            { icon: 'fi-rr-room-service', label: 'Living Areas', value: '2 Living Rooms' },
            { icon: 'fi-rr-car', label: 'Parking', value: '2 Car Garage' }
        ];

        specificationsGrid.innerHTML = specifications.map(spec => `
            <div class="spec-item">
                <div class="spec-icon">
                    <i class="fi ${spec.icon}"></i>
                </div>
                <div class="spec-content">
                    <span class="spec-label">${spec.label}</span>
                    <span class="spec-value">${spec.value}</span>
                </div>
            </div>
        `).join('');

        // Budget and Timeline
        const projectBudget = document.getElementById('modalBidProjectBudget');
        if (projectBudget) {
            projectBudget.textContent = bid.projectBudget || 'Not specified';
        }

        const timeline = document.getElementById('modalBidTimeline');
        if (timeline) {
            timeline.textContent = bid.timeline || '6-8 months';
        }
    }

    populateDocuments(bid) {
        const documentsList = document.getElementById('modalBidDocuments');
        const noDocumentsMessage = document.getElementById('noDocumentsMessage');

        if (!documentsList || !noDocumentsMessage) return;

        const documents = bid.projectFiles || [];

        if (documents.length === 0) {
            documentsList.style.display = 'none';
            noDocumentsMessage.style.display = 'block';
            return;
        }

        documentsList.style.display = 'grid';
        noDocumentsMessage.style.display = 'none';

        documentsList.innerHTML = documents.map(doc => {
            const fileName = doc.file_name || doc.original_name || 'Document';
            const filePath = doc.file_path || '';
            const fileType = fileName.split('.').pop().toLowerCase();

            return `
                <div class="document-item" data-path="${filePath}" data-name="${fileName}">
                    <div class="document-icon">
                        <i class="${this.getDocumentIcon(fileType)}"></i>
                    </div>
                    <div class="document-info">
                        <span class="document-name">${fileName}</span>
                        <span class="document-size">Project Document</span>
                    </div>
                    <div class="document-download">
                        <i class="fi fi-rr-eye"></i>
                    </div>
                </div>
            `;
        }).join('');

        // Add click handlers for document downloads
        documentsList.querySelectorAll('.document-item').forEach(item => {
            item.addEventListener('click', () => {
                const path = item.getAttribute('data-path');
                const name = item.getAttribute('data-name');
                this.handleDocumentDownload(path, name);
            });
        });
    }

    populateBidDocuments(bid) {
        const documentsList = document.getElementById('modalYourBidDocuments');
        const noDocumentsMessage = document.getElementById('noBidDocumentsMessage');

        if (!documentsList || !noDocumentsMessage) return;

        const documents = bid.bidFiles || [];

        if (documents.length === 0) {
            documentsList.style.display = 'none';
            noDocumentsMessage.style.display = 'block';
            return;
        }

        documentsList.style.display = 'grid';
        noDocumentsMessage.style.display = 'none';

        documentsList.innerHTML = documents.map(doc => {
            const fileName = doc.file_name || doc.original_name || 'Document';
            const filePath = doc.file_path || '';
            const fileType = fileName.split('.').pop().toLowerCase();

            return `
                <div class="document-item" data-path="${filePath}" data-name="${fileName}">
                    <div class="document-icon">
                        <i class="${this.getDocumentIcon(fileType)}"></i>
                    </div>
                    <div class="document-info">
                        <span class="document-name">${fileName}</span>
                        <span class="document-size">Bid Document</span>
                    </div>
                    <div class="document-download">
                        <i class="fi fi-rr-eye"></i>
                    </div>
                </div>
            `;
        }).join('');

        // Add click handlers for document downloads
        documentsList.querySelectorAll('.document-item').forEach(item => {
            item.addEventListener('click', () => {
                const path = item.getAttribute('data-path');
                const name = item.getAttribute('data-name');
                this.handleDocumentDownload(path, name);
            });
        });
    }

    populateYourBid(bid) {
        // Bid Amount
        const bidAmount = document.getElementById('modalYourBidAmount');
        if (bidAmount) {
            bidAmount.textContent = bid.bidAmount || 'Not specified';
        }

        // Bid Comparison
        const bidComparison = document.getElementById('modalBidComparison');
        if (bidComparison && bid.projectBudget && bid.bidAmount) {
            // Simple comparison - you can make this more sophisticated
            bidComparison.textContent = 'Within budget range';
        }

        // Bid Date Full
        const bidDateFull = document.getElementById('modalBidDateFull');
        if (bidDateFull) {
            bidDateFull.textContent = bid.submittedDate || 'Not specified';
        }

        // Bid Notes/Proposal Message
        const bidNotes = document.getElementById('modalBidNotes');
        if (bidNotes) {
            bidNotes.textContent = bid.proposalMessage || 'We are committed to delivering high-quality work within the specified timeline and budget. Our team has extensive experience in similar projects and we look forward to working with you.';
        }
    }

    updateWithdrawButton(bid) {
        const withdrawBtn = document.getElementById('withdrawBidBtn');
        if (withdrawBtn) {
            // Only show withdraw button for pending bids
            // Hide for accepted, rejected, and withdrawn bids
            if (bid.status === 'pending') {
                withdrawBtn.classList.remove('hidden');
            } else {
                // This hides the button for 'accepted', 'rejected', and 'withdrawn' statuses
                withdrawBtn.classList.add('hidden');
            }
        }
    }

    showWithdrawConfirmation() {
        if (!this.currentBid) return;

        // Populate confirmation modal with bid details
        const projectTitle = document.getElementById('confirmWithdrawProjectTitle');
        const bidAmount = document.getElementById('confirmWithdrawBidAmount');

        if (projectTitle) {
            projectTitle.textContent = this.currentBid.projectTitle || 'Untitled Project';
        }

        if (bidAmount) {
            bidAmount.textContent = this.currentBid.bidAmount || 'N/A';
        }

        // Show confirmation modal
        if (this.confirmationModal) {
            this.confirmationModal.classList.add('active');
        }
    }

    closeWithdrawConfirmation() {
        if (this.confirmationModal) {
            this.confirmationModal.classList.remove('active');
        }
    }

    isConfirmationOpen() {
        return this.confirmationModal && this.confirmationModal.classList.contains('active');
    }

    async confirmWithdraw() {
        if (!this.currentBid || !this.currentBid.id) return;

        const bidId = this.currentBid.id;
        console.log('Withdrawing bid:', bidId);

        const confirmBtn = document.getElementById('confirmWithdrawBtn');
        const originalContent = confirmBtn ? confirmBtn.innerHTML : '';

        // Show loading state
        if (confirmBtn) {
            confirmBtn.disabled = true;
            confirmBtn.innerHTML = '<i class=\"fi fi-rr-spinner-alt animate-spin\"></i> <span>Withdrawing...</span>';
        }

        try {
            const csrfToken = document.querySelector('meta[name=\"csrf-token\"]')?.getAttribute('content');

            const response = await fetch(`/contractor/bids/${bidId}/cancel`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                }
            });

            const data = await response.json();

            if (response.ok && data.success) {
                // Show success notification
                this.showNotification(data.message || 'Bid withdrawn successfully', 'success');

                // Close modals
                this.closeWithdrawConfirmation();
                this.close();

                // Reload page after a short delay to refresh status
                setTimeout(() => {
                    window.location.reload();
                }, 1500);
            } else {
                this.showNotification(data.message || 'Failed to withdraw bid', 'error');
                // Reset button state
                if (confirmBtn) {
                    confirmBtn.disabled = false;
                    confirmBtn.innerHTML = originalContent;
                }
            }
        } catch (error) {
            console.error('Error withdrawing bid:', error);
            this.showNotification('An error occurred. Please try again.', 'error');

            // Reset button state
            if (confirmBtn) {
                confirmBtn.disabled = false;
                confirmBtn.innerHTML = originalContent;
            }
        }
    }

    handleDocumentDownload(path, name) {
        if (!path) return;
        console.log('Viewing document:', path);

        const viewerUrl = `/contractor/progress/document/view?file=${encodeURIComponent(path)}&name=${encodeURIComponent(name || 'Document')}`;
        window.open(viewerUrl, '_blank');
    }

    getDocumentIcon(type) {
        const iconMap = {
            'pdf': 'fi fi-rr-file-pdf',
            'doc': 'fi fi-rr-file-word',
            'docx': 'fi fi-rr-file-word',
            'xls': 'fi fi-rr-file-excel',
            'xlsx': 'fi fi-rr-file-excel',
            'jpg': 'fi fi-rr-file-image',
            'jpeg': 'fi fi-rr-file-image',
            'png': 'fi fi-rr-file-image'
        };

        return iconMap[type] || 'fi fi-rr-file';
    }

    formatFileSize(bytes) {
        if (!bytes || bytes === 0) return '0 Bytes';
        const k = 1024;
        const sizes = ['Bytes', 'KB', 'MB', 'GB'];
        const i = Math.floor(Math.log(bytes) / Math.log(k));
        return Math.round(bytes / Math.pow(k, i) * 100) / 100 + ' ' + sizes[i];
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

// Initialize modal when DOM is ready
let contractorBidDetailsModalInstance = null;

document.addEventListener('DOMContentLoaded', () => {
    window.contractorBidDetailsModalInstance = new ContractorBidDetailsModal();
});

// Export for use in other scripts
window.openBidDetailsModal = (bidData) => {
    if (window.contractorBidDetailsModalInstance) {
        window.contractorBidDetailsModalInstance.open(bidData);
    } else {
        setTimeout(() => {
            if (window.contractorBidDetailsModalInstance) {
                window.contractorBidDetailsModalInstance.open(bidData);
            }
        }, 100);
    }
};

window.showBidWithdrawConfirmation = (bidData) => {
    if (window.contractorBidDetailsModalInstance) {
        window.contractorBidDetailsModalInstance.open(bidData);
        window.contractorBidDetailsModalInstance.showWithdrawConfirmation();
    } else {
        setTimeout(() => {
            if (window.contractorBidDetailsModalInstance) {
                window.contractorBidDetailsModalInstance.open(bidData);
                window.contractorBidDetailsModalInstance.showWithdrawConfirmation();
            }
        }, 100);
    }
};
