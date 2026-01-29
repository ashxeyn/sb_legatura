/**
 * View Progress Report Modal JavaScript
 * Handles viewing submitted progress reports (read-only)
 */

class ContractorViewProgressReportModal {
    constructor() {
        this.modal = null;
        this.overlay = null;
        this.currentReport = null;
        
        this.init();
    }

    init() {
        this.modal = document.getElementById('viewProgressReportModal');
        this.overlay = document.getElementById('viewProgressReportModalOverlay');
        
        if (!this.modal || !this.overlay) {
            console.error('View Progress Report Modal elements not found');
            return;
        }

        this.setupEventListeners();
    }

    setupEventListeners() {
        // Close buttons
        const closeBtn = document.getElementById('closeViewProgressReportModalBtn');
        const closeViewBtn = document.getElementById('closeViewReportBtn');
        
        if (closeBtn) {
            closeBtn.addEventListener('click', () => this.close());
        }
        
        if (closeViewBtn) {
            closeViewBtn.addEventListener('click', () => this.close());
        }

        // Overlay click to close
        if (this.overlay) {
            this.overlay.addEventListener('click', () => this.close());
        }

        // Close on Escape key
        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape' && this.isOpen()) {
                this.close();
            }
        });
    }

    open(report, milestoneData) {
        if (!report) {
            console.error('No report data provided');
            return;
        }

        this.currentReport = report;
        this.populateModal(report, milestoneData);
        
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
        this.currentReport = null;
    }

    isOpen() {
        return this.modal && this.modal.classList.contains('active');
    }

    populateModal(report, milestoneData) {
        // Report Title
        const titleElement = document.getElementById('viewModalReportTitle');
        if (titleElement) {
            titleElement.textContent = report.title || 'Progress Report';
        }

        // Milestone Title
        const milestoneTitleElement = document.getElementById('viewModalMilestoneTitle');
        if (milestoneTitleElement) {
            milestoneTitleElement.textContent = milestoneData?.milestoneTitle || 'Milestone';
        }

        // Project Title
        const projectTitleElement = document.getElementById('viewModalProjectTitle');
        if (projectTitleElement) {
            projectTitleElement.textContent = milestoneData?.projectTitle || 'Project';
        }

        // Submitted Date
        const submittedDateElement = document.getElementById('viewModalSubmittedDate');
        if (submittedDateElement) {
            submittedDateElement.textContent = report.date || 'Not specified';
        }

        // Status Badge
        const statusElement = document.getElementById('viewModalStatus');
        if (statusElement) {
            // Remove existing status classes
            statusElement.className = 'status-badge-view';
            
            const status = report.status || 'not_submitted';
            statusElement.classList.add(`status-${status}`);
            
            // Set status text
            const statusText = {
                'approved': 'Approved',
                'pending': 'Pending Approval',
                'not_submitted': 'Not Submitted',
                'rejected': 'Rejected'
            };
            statusElement.textContent = statusText[status] || status;
        }

        // Description
        const descriptionElement = document.getElementById('viewModalDescription');
        if (descriptionElement) {
            descriptionElement.textContent = report.description || 'No description available.';
        }

        // Files/Attachments
        this.populateFiles(report.files || []);
    }

    populateFiles(files) {
        const fileListContainer = document.getElementById('viewModalFileList');
        const noFilesMessage = document.getElementById('noFilesMessage');
        
        if (!fileListContainer) return;
        
        fileListContainer.innerHTML = '';
        
        if (!files || files.length === 0) {
            if (noFilesMessage) {
                noFilesMessage.style.display = 'flex';
            }
            return;
        }
        
        if (noFilesMessage) {
            noFilesMessage.style.display = 'none';
        }
        
        files.forEach(file => {
            const fileItem = document.createElement('div');
            fileItem.className = 'attachment-file-item';
            
            const fileIcon = this.getFileIcon(file.name || file.type);
            const fileSize = this.formatFileSize(file.size || 0);
            
            fileItem.innerHTML = `
                <div class="file-item-icon">
                    <i class="${fileIcon}"></i>
                </div>
                <div class="file-item-info">
                    <div class="file-item-name">${file.name || 'Untitled'}</div>
                    <div class="file-item-size">${fileSize}</div>
                </div>
                <button class="file-download-btn" data-url="${file.url || '#'}" title="Download file">
                    <i class="fi fi-rr-download"></i>
                </button>
            `;
            
            const downloadBtn = fileItem.querySelector('.file-download-btn');
            downloadBtn.addEventListener('click', (e) => {
                e.preventDefault();
                this.handleFileDownload(file);
            });
            
            fileListContainer.appendChild(fileItem);
        });
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

    handleFileDownload(file) {
        console.log('Downloading file:', file.name);
        // TODO: Implement actual file download
        this.showNotification(`Downloading ${file.name}...`);
        
        // In production, trigger actual download
        // window.open(file.url, '_blank');
    }

    showNotification(message) {
        const toast = document.createElement('div');
        toast.className = 'fixed bottom-4 right-4 bg-orange-500 text-white px-6 py-3 rounded-lg shadow-lg z-50';
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
let contractorViewProgressReportModalInstance = null;

document.addEventListener('DOMContentLoaded', () => {
    contractorViewProgressReportModalInstance = new ContractorViewProgressReportModal();
});

// Export for use in other scripts
window.ContractorViewProgressReportModal = ContractorViewProgressReportModal;
window.openViewProgressReportModal = (report, milestoneData) => {
    if (contractorViewProgressReportModalInstance) {
        contractorViewProgressReportModalInstance.open(report, milestoneData);
    } else {
        setTimeout(() => {
            if (contractorViewProgressReportModalInstance) {
                contractorViewProgressReportModalInstance.open(report, milestoneData);
            }
        }, 100);
    }
};
