/**
 * Contractor Progress Report Modal JavaScript
 * Handles progress report submission
 */

class ContractorProgressreportModal {
    constructor() {
        this.modal = null;
        this.overlay = null;
        this.confirmationModal = null;
        this.confirmationOverlay = null;
        this.uploadedFiles = [];
        this.projectData = null;
        
        this.init();
    }

    init() {
        this.modal = document.getElementById('progressReportModal');
        this.overlay = document.getElementById('progressReportModalOverlay');
        this.confirmationModal = document.getElementById('submissionConfirmationModal');
        this.confirmationOverlay = document.getElementById('submissionConfirmationModalOverlay');
        
        if (!this.modal || !this.overlay) {
            console.error('Progress Report Modal elements not found');
            return;
        }

        this.setupEventListeners();
    }

    setupEventListeners() {
        // Close button
        const closeBtn = document.getElementById('closeProgressReportModalBtn');
        if (closeBtn) {
            closeBtn.addEventListener('click', () => this.close());
        }

        // Cancel button
        const cancelBtn = document.getElementById('cancelReportBtn');
        if (cancelBtn) {
            cancelBtn.addEventListener('click', () => this.close());
        }

        // Overlay click to close
        if (this.overlay) {
            this.overlay.addEventListener('click', () => this.close());
        }

        // Submit button
        const submitBtn = document.getElementById('submitReportBtn');
        if (submitBtn) {
            submitBtn.addEventListener('click', (e) => {
                e.preventDefault();
                this.handleSubmit();
            });
        }

        // File input change
        const fileInput = document.getElementById('reportFiles');
        if (fileInput) {
            fileInput.addEventListener('change', (e) => this.handleFileSelect(e));
        }

        // Confirmation modal close
        const closeConfirmationBtn = document.getElementById('closeSubmissionConfirmationBtn');
        if (closeConfirmationBtn) {
            closeConfirmationBtn.addEventListener('click', () => this.closeConfirmationModal());
        }

        const closeSubmissionBtn = document.getElementById('closeSubmissionBtn');
        if (closeSubmissionBtn) {
            closeSubmissionBtn.addEventListener('click', () => this.closeConfirmationModal());
        }

        if (this.confirmationOverlay) {
            this.confirmationOverlay.addEventListener('click', () => this.closeConfirmationModal());
        }

        // Close on Escape key
        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape') {
                if (this.confirmationModal && this.confirmationModal.classList.contains('active')) {
                    this.closeConfirmationModal();
                } else if (this.isOpen()) {
                    this.close();
                }
            }
        });
    }

    open(data) {
        this.projectData = data || {};
        this.populateModal(this.projectData);
        this.resetForm();
        
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
        this.resetForm();
    }

    isOpen() {
        return this.modal && this.modal.classList.contains('active');
    }

    populateModal(data) {
        // Set project title
        const projectTitleElement = document.getElementById('modalProjectTitle');
        if (projectTitleElement) {
            projectTitleElement.textContent = data.projectTitle || 'Project';
        }

        // Set milestone title
        const milestoneTitleElement = document.getElementById('modalMilestoneTitle');
        if (milestoneTitleElement) {
            milestoneTitleElement.textContent = data.milestoneTitle || 'Milestone';
        }
    }

    resetForm() {
        const form = document.getElementById('progressReportForm');
        if (form) {
            form.reset();
        }
        
        this.uploadedFiles = [];
        this.updateFilePreview();
    }

    handleFileSelect(event) {
        const files = Array.from(event.target.files);
        
        files.forEach(file => {
            // Check file size (10MB limit)
            if (file.size > 10 * 1024 * 1024) {
                this.showNotification(`File ${file.name} is too large. Maximum size is 10MB.`, 'error');
                return;
            }
            
            this.uploadedFiles.push(file);
        });
        
        this.updateFilePreview();
    }

    updateFilePreview() {
        const container = document.getElementById('filePreviewContainer');
        if (!container) return;
        
        container.innerHTML = '';
        
        this.uploadedFiles.forEach((file, index) => {
            const fileItem = document.createElement('div');
            fileItem.className = 'file-preview-item';
            
            const fileSize = this.formatFileSize(file.size);
            const fileIcon = this.getFileIcon(file.name);
            
            fileItem.innerHTML = `
                <div class="file-preview-info">
                    <div class="file-preview-icon">
                        <i class="${fileIcon}"></i>
                    </div>
                    <div class="file-preview-details">
                        <div class="file-preview-name">${file.name}</div>
                        <div class="file-preview-size">${fileSize}</div>
                    </div>
                </div>
                <button class="file-remove-btn" data-index="${index}">
                    <i class="fi fi-rr-cross-small"></i>
                </button>
            `;
            
            const removeBtn = fileItem.querySelector('.file-remove-btn');
            removeBtn.addEventListener('click', () => this.removeFile(index));
            
            container.appendChild(fileItem);
        });
    }

    removeFile(index) {
        this.uploadedFiles.splice(index, 1);
        this.updateFilePreview();
    }

    getFileIcon(filename) {
        const ext = filename.split('.').pop().toLowerCase();
        const iconMap = {
            'pdf': 'fi fi-rr-file-pdf',
            'doc': 'fi fi-rr-file-word',
            'docx': 'fi fi-rr-file-word',
            'jpg': 'fi fi-rr-file-image',
            'jpeg': 'fi fi-rr-file-image',
            'png': 'fi fi-rr-file-image',
            'gif': 'fi fi-rr-file-image'
        };
        
        return iconMap[ext] || 'fi fi-rr-file';
    }

    formatFileSize(bytes) {
        if (bytes === 0) return '0 Bytes';
        const k = 1024;
        const sizes = ['Bytes', 'KB', 'MB', 'GB'];
        const i = Math.floor(Math.log(bytes) / Math.log(k));
        return Math.round(bytes / Math.pow(k, i) * 100) / 100 + ' ' + sizes[i];
    }

    handleSubmit() {
        const form = document.getElementById('progressReportForm');
        if (!form.checkValidity()) {
            form.reportValidity();
            return;
        }

        const formData = new FormData(form);
        
        // Add files to form data
        this.uploadedFiles.forEach((file, index) => {
            formData.append(`files[${index}]`, file);
        });
        
        // Add project and milestone IDs
        formData.append('project_id', this.projectData.projectId || '');
        formData.append('milestone_id', this.projectData.milestoneId || '');

        // TODO: Send to backend API
        console.log('Submitting progress report:', {
            projectId: this.projectData.projectId,
            milestoneId: this.projectData.milestoneId,
            title: formData.get('reportTitle'),
            description: formData.get('reportDescription'),
            files: this.uploadedFiles.map(f => f.name)
        });

        // For now, just show success
        this.showSuccessConfirmation();
    }

    showSuccessConfirmation() {
        if (this.confirmationModal) {
            this.confirmationModal.classList.add('active');
        }
    }

    closeConfirmationModal() {
        if (this.confirmationModal) {
            this.confirmationModal.classList.remove('active');
        }
        
        // Close main modal and reset
        this.close();
        
        // Optionally reload the page or update the list
        // window.location.reload();
    }

    showNotification(message, type = 'info') {
        const toast = document.createElement('div');
        toast.className = `fixed bottom-4 right-4 text-white px-6 py-3 rounded-lg shadow-lg z-50 ${
            type === 'error' ? 'bg-red-500' : 
            type === 'success' ? 'bg-green-500' : 
            'bg-orange-500'
        }`;
        toast.textContent = message;
        toast.style.cssText = 'animation: slideUp 0.3s ease-out;';

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
let contractorProgressreportModalInstance = null;

document.addEventListener('DOMContentLoaded', () => {
    contractorProgressreportModalInstance = new ContractorProgressreportModal();
});

// Export for use in other scripts
window.ContractorProgressreportModal = ContractorProgressreportModal;
window.openProgressReportModal = (data) => {
    if (contractorProgressreportModalInstance) {
        contractorProgressreportModalInstance.open(data);
    } else {
        setTimeout(() => {
            if (contractorProgressreportModalInstance) {
                contractorProgressreportModalInstance.open(data);
            }
        }, 100);
    }
};
