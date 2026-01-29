/**
 * Progress Report Modal JavaScript
 * Handles the display and interactions for the progress report modal
 */

class OwnerProgressreportModal {
    constructor() {
        this.modal = null;
        this.overlay = null;
        this.confirmationModal = null;
        this.confirmationOverlay = null;
        this.downloadConfirmationModal = null;
        this.downloadConfirmationOverlay = null;
        this.rejectionModal = null;
        this.rejectionOverlay = null;
        this.paymentModal = null;
        this.paymentOverlay = null;
        this.currentReport = null;
        this.milestoneData = null;
        this.uploadedFiles = [];
        this.paymentReceiptFile = null;
        this.pendingDownloadFile = null;
        this.pendingDownloadUrl = null;
        
        this.init();
    }

    init() {
        this.modal = document.getElementById('progressReportModal');
        this.overlay = document.getElementById('progressReportModalOverlay');
        this.confirmationModal = document.getElementById('approvalConfirmationModal');
        this.confirmationOverlay = document.getElementById('approvalConfirmationModalOverlay');
        this.downloadConfirmationModal = document.getElementById('downloadConfirmationModal');
        this.downloadConfirmationOverlay = document.getElementById('downloadConfirmationModalOverlay');
        this.rejectionModal = document.getElementById('rejectionReasonModal');
        this.rejectionOverlay = document.getElementById('rejectionReasonModalOverlay');
        this.paymentModal = document.getElementById('milestonePaymentModal');
        this.paymentOverlay = document.getElementById('milestonePaymentModalOverlay');
        
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

        // Overlay click to close
        if (this.overlay) {
            this.overlay.addEventListener('click', () => this.close());
        }

        // Reject button - show rejection modal
        const rejectBtn = document.getElementById('rejectReportBtn');
        if (rejectBtn) {
            rejectBtn.addEventListener('click', () => this.showRejectionModal());
        }

        // Approve button - show confirmation modal
        const approveBtn = document.getElementById('approveReportBtn');
        if (approveBtn) {
            approveBtn.addEventListener('click', () => this.showConfirmationModal());
        }

        // Confirmation modal close button
        const closeConfirmationBtn = document.getElementById('closeConfirmationModalBtn');
        if (closeConfirmationBtn) {
            closeConfirmationBtn.addEventListener('click', () => this.closeConfirmationModal());
        }

        // Confirmation modal overlay
        if (this.confirmationOverlay) {
            this.confirmationOverlay.addEventListener('click', () => this.closeConfirmationModal());
        }

        // Cancel button in confirmation modal
        const cancelBtn = document.getElementById('cancelApprovalBtn');
        if (cancelBtn) {
            cancelBtn.addEventListener('click', () => this.closeConfirmationModal());
        }

        // Confirm approval button
        const confirmBtn = document.getElementById('confirmApprovalBtn');
        if (confirmBtn) {
            confirmBtn.addEventListener('click', () => this.handleApprove());
        }

        // Download confirmation modal close button
        const closeDownloadConfirmationBtn = document.getElementById('closeDownloadConfirmationModalBtn');
        if (closeDownloadConfirmationBtn) {
            closeDownloadConfirmationBtn.addEventListener('click', () => this.closeDownloadConfirmationModal());
        }

        // Download confirmation modal overlay
        if (this.downloadConfirmationOverlay) {
            this.downloadConfirmationOverlay.addEventListener('click', () => this.closeDownloadConfirmationModal());
        }

        // Cancel download button
        const cancelDownloadBtn = document.getElementById('cancelDownloadBtn');
        if (cancelDownloadBtn) {
            cancelDownloadBtn.addEventListener('click', () => this.closeDownloadConfirmationModal());
        }

        // Confirm download button
        const confirmDownloadBtn = document.getElementById('confirmDownloadBtn');
        if (confirmDownloadBtn) {
            confirmDownloadBtn.addEventListener('click', () => this.handleDownload());
        }

        // Rejection modal close button
        const closeRejectionBtn = document.getElementById('closeRejectionModalBtn');
        if (closeRejectionBtn) {
            closeRejectionBtn.addEventListener('click', () => this.closeRejectionModal());
        }

        // Rejection modal overlay
        if (this.rejectionOverlay) {
            this.rejectionOverlay.addEventListener('click', () => this.closeRejectionModal());
        }

        // Cancel rejection button
        const cancelRejectionBtn = document.getElementById('cancelRejectionBtn');
        if (cancelRejectionBtn) {
            cancelRejectionBtn.addEventListener('click', () => this.closeRejectionModal());
        }

        // Submit rejection button
        const submitRejectionBtn = document.getElementById('submitRejectionBtn');
        if (submitRejectionBtn) {
            submitRejectionBtn.addEventListener('click', () => this.handleSubmitRejection());
        }

        // File upload handling for rejection modal
        this.setupFileUpload();

        // Payment modal close button
        const closePaymentBtn = document.getElementById('closePaymentModalBtn');
        if (closePaymentBtn) {
            closePaymentBtn.addEventListener('click', () => this.closePaymentModal());
        }

        // Payment modal overlay
        if (this.paymentOverlay) {
            this.paymentOverlay.addEventListener('click', () => this.closePaymentModal());
        }

        // Cancel payment button
        const cancelPaymentBtn = document.getElementById('cancelPaymentBtn');
        if (cancelPaymentBtn) {
            cancelPaymentBtn.addEventListener('click', () => this.closePaymentModal());
        }

        // Submit payment button
        const submitPaymentBtn = document.getElementById('submitPaymentBtn');
        if (submitPaymentBtn) {
            submitPaymentBtn.addEventListener('click', () => this.handleSubmitPayment());
        }

        // Payment file upload handling
        this.setupPaymentFileUpload();

        // ESC key to close
        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape') {
                if (this.isPaymentModalOpen()) {
                    this.closePaymentModal();
                } else if (this.isRejectionModalOpen()) {
                    this.closeRejectionModal();
                } else if (this.isDownloadConfirmationModalOpen()) {
                    this.closeDownloadConfirmationModal();
                } else if (this.isConfirmationModalOpen()) {
                    this.closeConfirmationModal();
                } else if (this.isOpen()) {
                    this.close();
                }
            }
        });
    }

    open(reportData, milestoneData) {
        if (!reportData) {
            console.error('No report data provided');
            return;
        }

        this.currentReport = reportData;
        this.milestoneData = milestoneData || {};

        this.populateModal(reportData, milestoneData);
        
        if (this.modal) {
            // Add active class with animation
            requestAnimationFrame(() => {
                this.modal.classList.add('active');
                document.body.style.overflow = 'hidden';
                
                // Add entrance animation to modal container
                const container = this.modal.querySelector('.modal-container');
                if (container) {
                    container.style.animation = 'slideUpScale 0.4s cubic-bezier(0.34, 1.56, 0.64, 1)';
                }
            });
        }
    }

    close() {
        if (this.modal) {
            const container = this.modal.querySelector('.modal-container');
            if (container) {
                container.style.animation = 'slideDown 0.3s ease-in';
            }
            
            setTimeout(() => {
                this.modal.classList.remove('active');
                document.body.style.overflow = '';
            }, 250);
        }
        this.currentReport = null;
        this.milestoneData = null;
    }

    isOpen() {
        return this.modal && this.modal.classList.contains('active');
    }

    populateModal(reportData, milestoneData) {
        // Set milestone title
        const milestoneTitle = document.getElementById('modalMilestoneTitle');
        if (milestoneTitle) {
            const milestoneNumber = milestoneData?.milestoneId || 1;
            const milestoneName = milestoneData?.milestoneTitle || 'Milestone';
            milestoneTitle.textContent = `Milestone ${milestoneNumber}: ${milestoneName}`;
        }

        // Set project context
        const projectCategory = document.getElementById('modalProjectCategory');
        const projectType = document.getElementById('modalProjectType');
        
        if (projectCategory) {
            projectCategory.textContent = milestoneData?.projectCategory || 'Residential';
        }
        if (projectType) {
            projectType.textContent = milestoneData?.projectType || 'House Construction';
        }

        // Set milestone description
        const milestoneDescription = document.getElementById('modalMilestoneDescription');
        if (milestoneDescription) {
            milestoneDescription.textContent = milestoneData?.description || reportData.description || 'No description available.';
        }

        // Populate attachments
        this.populateAttachments(reportData.files || []);
    }

    populateAttachments(files) {
        const previewArea = document.getElementById('attachmentsPreviewArea');
        const fileList = document.getElementById('attachmentsFileList');

        if (!fileList) return;

        // Clear existing files
        fileList.innerHTML = '';

        if (!files || files.length === 0) {
            // Show placeholder if no files
            if (previewArea) {
                previewArea.innerHTML = `
                    <div class="preview-placeholder">
                        <span class="preview-placeholder-text">PDF</span>
                    </div>
                `;
            }
            fileList.innerHTML = '<p class="text-gray-500 text-sm text-center py-4">No attachments available</p>';
            return;
        }

        // Show first file as preview
        const firstFile = files[0];
        if (previewArea) {
            // Add click handler to preview area
            previewArea.onclick = () => {
                // Check if file is PDF - show download confirmation
                const fileName = firstFile.name || firstFile.fileName || '';
                const isPdf = fileName.toLowerCase().endsWith('.pdf') || firstFile.type === 'pdf';
                
                if (isPdf) {
                    // Show download confirmation modal for PDFs
                    this.pendingDownloadFile = firstFile;
                    this.pendingDownloadUrl = firstFile.url || firstFile.fileUrl || '#';
                    this.showDownloadConfirmationModal(firstFile);
                } else {
                    // For non-PDF files, open directly
                    if (firstFile.url && firstFile.url !== '#') {
                        window.open(firstFile.url, '_blank');
                    }
                }
            };
            
            if (firstFile.type === 'image' && firstFile.url && firstFile.url !== '#') {
                previewArea.innerHTML = `
                    <img src="${firstFile.url}" alt="${firstFile.name || 'Preview'}" class="preview-image">
                `;
            } else {
                const fileType = firstFile.type?.toUpperCase() || 'PDF';
                previewArea.innerHTML = `
                    <div class="preview-placeholder">
                        <span class="preview-placeholder-text">${fileType}</span>
                    </div>
                `;
            }
        }

        // Render file list - show all files with staggered animation
        files.forEach((file, index) => {
            setTimeout(() => {
                const fileItem = this.createFileItem(file, index);
                fileList.appendChild(fileItem);
            }, index * 100);
        });
    }

    createFileItem(file, index) {
        const fileItem = document.createElement('div');
        fileItem.className = 'file-item';
        fileItem.setAttribute('data-file-index', index);
        fileItem.setAttribute('title', `Click to view/download: ${file.name || file.fileName || 'File'}`);

        // Determine file type from extension if not provided
        let fileType = file.type || 'pdf';
        const fileName = file.name || file.fileName || `File ${index + 1}`;
        const fileUrl = file.url || file.fileUrl || '#';
        
        // Auto-detect file type from extension
        if (!file.type && fileName) {
            const extension = fileName.split('.').pop()?.toLowerCase();
            if (['jpg', 'jpeg', 'png', 'gif', 'webp'].includes(extension)) {
                fileType = 'image';
            } else if (['doc', 'docx'].includes(extension)) {
                fileType = 'word';
            } else if (['xls', 'xlsx'].includes(extension)) {
                fileType = 'excel';
            } else if (['pdf'].includes(extension)) {
                fileType = 'pdf';
            } else {
                fileType = 'document';
            }
        }

        // Get file size if available
        const fileSize = file.size ? this.formatFileSize(file.size) : '';

        fileItem.innerHTML = `
            <div class="file-item-left">
                <div class="file-icon ${fileType}">
                    ${fileType.toUpperCase()}
                </div>
                <div class="file-info">
                    <p class="file-name">${fileName}</p>
                    ${fileSize ? `<span class="file-size">${fileSize}</span>` : ''}
                </div>
            </div>
            <div class="file-item-right">
                <div class="file-download-indicator"></div>
                <i class="fi fi-rr-download file-download-icon"></i>
            </div>
        `;

        // Add click handler for file download/view
        fileItem.addEventListener('click', (e) => {
            e.stopPropagation();
            this.handleFileClick(file, fileUrl, e);
        });

        return fileItem;
    }

    formatFileSize(bytes) {
        if (!bytes || bytes === 0) return '';
        const k = 1024;
        const sizes = ['Bytes', 'KB', 'MB', 'GB'];
        const i = Math.floor(Math.log(bytes) / Math.log(k));
        return Math.round(bytes / Math.pow(k, i) * 100) / 100 + ' ' + sizes[i];
    }

    handleFileClick(file, fileUrl, event) {
        console.log('File clicked:', file);
        
        // Add visual feedback
        const fileItem = event?.target?.closest('.file-item');
        if (fileItem) {
            fileItem.style.transform = 'scale(0.95)';
            setTimeout(() => {
                fileItem.style.transform = '';
            }, 150);
        }
        
        // Check if file is PDF - show download confirmation
        const fileName = file.name || file.fileName || '';
        const isPdf = fileName.toLowerCase().endsWith('.pdf') || file.type === 'pdf';
        
        if (isPdf) {
            // Show download confirmation modal for PDFs
            this.pendingDownloadFile = file;
            this.pendingDownloadUrl = fileUrl;
            this.showDownloadConfirmationModal(file);
        } else {
            // For non-PDF files, open directly
            if (fileUrl && fileUrl !== '#') {
                window.open(fileUrl, '_blank');
                this.showNotification(`Opening ${file.name || 'file'}...`, 'info');
            } else {
                this.showNotification(`Opening ${file.name || 'file'}...`, 'info');
            }
        }
    }

    showRejectionModal() {
        if (this.rejectionModal && this.currentReport && this.milestoneData) {
            // Populate milestone info
            const milestoneValue = document.getElementById('rejectionMilestoneValue');
            if (milestoneValue) {
                const milestoneNumber = this.milestoneData?.milestoneId || 1;
                const milestoneName = this.milestoneData?.milestoneTitle || 'Milestone';
                milestoneValue.textContent = `Milestone ${milestoneNumber}: ${milestoneName}`;
            }

            // Clear previous data
            const textarea = document.getElementById('rejectionReasonTextarea');
            if (textarea) {
                textarea.value = '';
            }
            this.uploadedFiles = [];
            this.updateUploadedFilesList();

            requestAnimationFrame(() => {
                this.rejectionModal.classList.add('active');
                document.body.style.overflow = 'hidden';
                
                // Add entrance animation
                const container = this.rejectionModal.querySelector('.rejection-modal-container');
                if (container) {
                    container.style.animation = 'slideUpScale 0.4s cubic-bezier(0.34, 1.56, 0.64, 1)';
                }
            });
        }
    }

    closeRejectionModal() {
        if (this.rejectionModal) {
            const container = this.rejectionModal.querySelector('.rejection-modal-container');
            if (container) {
                container.style.animation = 'slideDown 0.3s ease-in';
            }
            
            setTimeout(() => {
                this.rejectionModal.classList.remove('active');
                // Don't restore body overflow if main modal is still open
                if (!this.isOpen()) {
                    document.body.style.overflow = '';
                }
            }, 250);
        }
    }

    isRejectionModalOpen() {
        return this.rejectionModal && this.rejectionModal.classList.contains('active');
    }

    setupFileUpload() {
        const uploadArea = document.getElementById('rejectionUploadArea');
        const fileInput = document.getElementById('rejectionFileInput');

        if (!uploadArea || !fileInput) return;

        // Click to open file dialog
        uploadArea.addEventListener('click', () => {
            fileInput.click();
        });

        // File input change
        fileInput.addEventListener('change', (e) => {
            this.handleFileSelect(e.target.files);
        });

        // Drag and drop
        uploadArea.addEventListener('dragover', (e) => {
            e.preventDefault();
            uploadArea.classList.add('dragover');
        });

        uploadArea.addEventListener('dragleave', () => {
            uploadArea.classList.remove('dragover');
        });

        uploadArea.addEventListener('drop', (e) => {
            e.preventDefault();
            uploadArea.classList.remove('dragover');
            this.handleFileSelect(e.dataTransfer.files);
        });
    }

    handleFileSelect(files) {
        if (!files || files.length === 0) return;

        Array.from(files).forEach(file => {
            // Check if file already exists
            if (!this.uploadedFiles.find(f => f.name === file.name && f.size === file.size)) {
                this.uploadedFiles.push(file);
            }
        });

        this.updateUploadedFilesList();
    }

    updateUploadedFilesList() {
        const filesList = document.getElementById('rejectionUploadedFiles');
        if (!filesList) return;

        filesList.innerHTML = '';

        if (this.uploadedFiles.length === 0) {
            return;
        }

        this.uploadedFiles.forEach((file, index) => {
            const fileItem = this.createUploadedFileItem(file, index);
            filesList.appendChild(fileItem);
        });
    }

    createUploadedFileItem(file, index) {
        const fileItem = document.createElement('div');
        fileItem.className = 'uploaded-file-item';

        const fileName = file.name;
        const fileType = this.getFileType(fileName);

        fileItem.innerHTML = `
            <div class="uploaded-file-info">
                <div class="uploaded-file-icon">${fileType.toUpperCase()}</div>
                <p class="uploaded-file-name">${fileName}</p>
            </div>
            <button class="uploaded-file-remove" data-file-index="${index}" aria-label="Remove file">
                <i class="fi fi-rr-cross"></i>
            </button>
        `;

        // Remove button handler
        const removeBtn = fileItem.querySelector('.uploaded-file-remove');
        if (removeBtn) {
            removeBtn.addEventListener('click', (e) => {
                e.stopPropagation();
                this.removeFile(index);
            });
        }

        return fileItem;
    }

    removeFile(index) {
        this.uploadedFiles.splice(index, 1);
        this.updateUploadedFilesList();
    }

    getFileType(fileName) {
        const extension = fileName.split('.').pop()?.toLowerCase();
        if (['jpg', 'jpeg', 'png', 'gif', 'webp'].includes(extension)) {
            return 'image';
        } else if (['pdf'].includes(extension)) {
            return 'pdf';
        } else if (['doc', 'docx'].includes(extension)) {
            return 'word';
        } else if (['xls', 'xlsx'].includes(extension)) {
            return 'excel';
        }
        return 'file';
    }

    handleSubmitRejection() {
        const textarea = document.getElementById('rejectionReasonTextarea');
        const reason = textarea ? textarea.value.trim() : '';

        if (!reason) {
            this.showNotification('Please provide a reason for rejection', 'error');
            if (textarea) {
                textarea.focus();
                textarea.style.borderColor = '#dc2626';
                setTimeout(() => {
                    textarea.style.borderColor = '';
                }, 2000);
            }
            return;
        }

        const submitBtn = document.getElementById('submitRejectionBtn');
        if (submitBtn) {
            const originalText = submitBtn.innerHTML;
            submitBtn.innerHTML = '<i class="fi fi-rr-spinner"></i> Submitting...';
            submitBtn.disabled = true;

            // In a real implementation, send rejection request to server
            console.log('Rejecting report:', {
                report: this.currentReport,
                reason: reason,
                files: this.uploadedFiles
            });

            // Simulate API call
            setTimeout(() => {
                this.showNotification('Report rejected successfully', 'success');
                
                // Close modals after a short delay
                setTimeout(() => {
                    this.closeRejectionModal();
                    this.close();
                    // Restore button state
                    submitBtn.innerHTML = originalText;
                    submitBtn.disabled = false;
                }, 1500);
            }, 1000);
        }
    }

    showConfirmationModal() {
        if (this.confirmationModal) {
            requestAnimationFrame(() => {
                this.confirmationModal.classList.add('active');
                document.body.style.overflow = 'hidden';
                
                // Add entrance animation
                const container = this.confirmationModal.querySelector('.confirmation-modal-container');
                if (container) {
                    container.style.animation = 'slideUpScale 0.4s cubic-bezier(0.34, 1.56, 0.64, 1)';
                }
            });
        }
    }

    closeConfirmationModal() {
        if (this.confirmationModal) {
            const container = this.confirmationModal.querySelector('.confirmation-modal-container');
            if (container) {
                container.style.animation = 'slideDown 0.3s ease-in';
            }
            
            setTimeout(() => {
                this.confirmationModal.classList.remove('active');
                // Don't restore body overflow if main modal is still open
                if (!this.isOpen()) {
                    document.body.style.overflow = '';
                }
            }, 250);
        }
    }

    isConfirmationModalOpen() {
        return this.confirmationModal && this.confirmationModal.classList.contains('active');
    }

    showDownloadConfirmationModal(file) {
        if (!this.downloadConfirmationModal || !file) return;

        // Update modal content with file information
        const message = document.getElementById('downloadConfirmationMessage');
        const submessage = document.getElementById('downloadConfirmationSubmessage');
        
        if (message) {
            const fileName = file.name || file.fileName || 'this file';
            message.textContent = `Are you sure you want to download "${fileName}"?`;
        }
        
        if (submessage) {
            const fileSize = file.size ? this.formatFileSize(file.size) : '';
            submessage.textContent = fileSize 
                ? `The file (${fileSize}) will be downloaded to your device.`
                : 'The file will be downloaded to your device.';
        }

        requestAnimationFrame(() => {
            this.downloadConfirmationModal.classList.add('active');
            document.body.style.overflow = 'hidden';
            
            // Add entrance animation
            const container = this.downloadConfirmationModal.querySelector('.confirmation-modal-container');
            if (container) {
                container.style.animation = 'slideUpScale 0.4s cubic-bezier(0.34, 1.56, 0.64, 1)';
            }
        });
    }

    closeDownloadConfirmationModal() {
        if (this.downloadConfirmationModal) {
            const container = this.downloadConfirmationModal.querySelector('.confirmation-modal-container');
            if (container) {
                container.style.animation = 'slideDown 0.3s ease-in';
            }
            
            setTimeout(() => {
                this.downloadConfirmationModal.classList.remove('active');
                // Don't restore body overflow if main modal is still open
                if (!this.isOpen()) {
                    document.body.style.overflow = '';
                }
                // Clear pending download
                this.pendingDownloadFile = null;
                this.pendingDownloadUrl = null;
            }, 250);
        }
    }

    isDownloadConfirmationModalOpen() {
        return this.downloadConfirmationModal && this.downloadConfirmationModal.classList.contains('active');
    }

    handleDownload() {
        if (!this.pendingDownloadFile || !this.pendingDownloadUrl) {
            this.closeDownloadConfirmationModal();
            return;
        }

        // Add loading state to confirm button
        const confirmBtn = document.getElementById('confirmDownloadBtn');
        let originalText = '';
        if (confirmBtn) {
            originalText = confirmBtn.innerHTML;
            confirmBtn.innerHTML = '<i class="fi fi-rr-spinner"></i> Downloading...';
            confirmBtn.disabled = true;
        }

        // Close confirmation modal first
        this.closeDownloadConfirmationModal();

        // Perform download
        const fileName = this.pendingDownloadFile.name || this.pendingDownloadFile.fileName || 'download.pdf';
        
        try {
            if (this.pendingDownloadUrl && this.pendingDownloadUrl !== '#') {
                // Create a temporary anchor element to trigger download
                const link = document.createElement('a');
                link.href = this.pendingDownloadUrl;
                link.download = fileName;
                link.target = '_blank';
                document.body.appendChild(link);
                link.click();
                document.body.removeChild(link);
                
                this.showNotification(`Downloading "${fileName}"...`, 'success');
            } else {
                // If no URL, show notification
                this.showNotification('Download link not available', 'error');
            }
        } catch (error) {
            console.error('Download error:', error);
            this.showNotification('Failed to download file', 'error');
        }

        // Restore button state after a delay
        setTimeout(() => {
            if (confirmBtn && originalText) {
                confirmBtn.innerHTML = originalText;
                confirmBtn.disabled = false;
            }
        }, 1000);

        // Clear pending download
        this.pendingDownloadFile = null;
        this.pendingDownloadUrl = null;
    }

    handleApprove() {
        if (!this.currentReport) return;

        // Close confirmation modal first
        this.closeConfirmationModal();

        // Add loading state to confirm button
        const confirmBtn = document.getElementById('confirmApprovalBtn');
        if (confirmBtn) {
            const originalText = confirmBtn.innerHTML;
            confirmBtn.innerHTML = '<i class="fi fi-rr-spinner"></i> Processing...';
            confirmBtn.disabled = true;
            
            // In a real implementation, send approve request to server
            console.log('Approving report:', this.currentReport);
            
            // Simulate API call
            setTimeout(() => {
                this.showNotification('Report approved successfully', 'success');
                
                // Close main modal after a short delay
                setTimeout(() => {
                    this.close();
                    // Restore button state
                    confirmBtn.innerHTML = originalText;
                    confirmBtn.disabled = false;
                }, 1500);
            }, 1000);
        } else {
            // Fallback if button not found
            this.showNotification('Report approved successfully', 'success');
            setTimeout(() => {
                this.close();
            }, 1500);
        }
    }

    showNotification(message, type = 'info') {
        const toast = document.createElement('div');
        let bgColor = '#EEA24B';
        if (type === 'success') {
            bgColor = '#10b981';
        } else if (type === 'error') {
            bgColor = '#dc2626';
        }
        
        toast.className = 'fixed bottom-4 right-4 text-white px-6 py-3 rounded-lg shadow-lg z-50';
        toast.style.backgroundColor = bgColor;
        toast.textContent = message;
        toast.style.cssText += `
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

    showPaymentModal(milestoneData) {
        if (this.paymentModal) {
            this.milestoneData = milestoneData || {};

            // Populate milestone info
            const milestoneValue = document.getElementById('paymentMilestoneValue');
            if (milestoneValue) {
                const milestoneNumber = this.milestoneData?.milestoneId || 1;
                const milestoneName = this.milestoneData?.milestoneTitle || 'Milestone';
                milestoneValue.textContent = `Milestone ${milestoneNumber}: ${milestoneName}`;
            }

            // Clear previous data
            const receiptNumber = document.getElementById('receiptNumberInput');
            const amountPaid = document.getElementById('amountPaidInput');
            const description = document.getElementById('paymentDescriptionTextarea');
            
            if (receiptNumber) receiptNumber.value = '';
            if (amountPaid) amountPaid.value = '';
            if (description) description.value = '';
            
            this.paymentReceiptFile = null;
            this.updatePaymentReceiptPreview();

            requestAnimationFrame(() => {
                this.paymentModal.classList.add('active');
                document.body.style.overflow = 'hidden';
                
                // Add entrance animation
                const container = this.paymentModal.querySelector('.payment-modal-container');
                if (container) {
                    container.style.animation = 'slideUpScale 0.4s cubic-bezier(0.34, 1.56, 0.64, 1)';
                }
            });
        }
    }

    closePaymentModal() {
        if (this.paymentModal) {
            const container = this.paymentModal.querySelector('.payment-modal-container');
            if (container) {
                container.style.animation = 'slideDown 0.3s ease-in';
            }
            
            setTimeout(() => {
                this.paymentModal.classList.remove('active');
                // Don't restore body overflow if main modal is still open
                if (!this.isOpen()) {
                    document.body.style.overflow = '';
                }
            }, 250);
        }
    }

    isPaymentModalOpen() {
        return this.paymentModal && this.paymentModal.classList.contains('active');
    }

    setupPaymentFileUpload() {
        const uploadArea = document.getElementById('paymentUploadArea');
        const fileInput = document.getElementById('paymentFileInput');

        if (!uploadArea || !fileInput) return;

        // Click to open file dialog
        uploadArea.addEventListener('click', () => {
            fileInput.click();
        });

        // File input change
        fileInput.addEventListener('change', (e) => {
            if (e.target.files && e.target.files.length > 0) {
                this.handlePaymentFileSelect(e.target.files[0]);
            }
        });

        // Drag and drop
        uploadArea.addEventListener('dragover', (e) => {
            e.preventDefault();
            uploadArea.classList.add('dragover');
        });

        uploadArea.addEventListener('dragleave', () => {
            uploadArea.classList.remove('dragover');
        });

        uploadArea.addEventListener('drop', (e) => {
            e.preventDefault();
            uploadArea.classList.remove('dragover');
            if (e.dataTransfer.files && e.dataTransfer.files.length > 0) {
                this.handlePaymentFileSelect(e.dataTransfer.files[0]);
            }
        });
    }

    handlePaymentFileSelect(file) {
        if (!file) return;

        // Validate file type
        const validTypes = ['image/jpeg', 'image/png', 'image/jpg', 'image/gif', 'application/pdf'];
        if (!validTypes.includes(file.type)) {
            this.showNotification('Please upload an image or PDF file', 'error');
            return;
        }

        // Validate file size (max 10MB)
        const maxSize = 10 * 1024 * 1024; // 10MB
        if (file.size > maxSize) {
            this.showNotification('File size must be less than 10MB', 'error');
            return;
        }

        this.paymentReceiptFile = file;
        this.updatePaymentReceiptPreview();
    }

    updatePaymentReceiptPreview() {
        const previewContainer = document.getElementById('paymentUploadedReceipt');
        if (!previewContainer) return;

        previewContainer.innerHTML = '';

        if (!this.paymentReceiptFile) {
            return;
        }

        const file = this.paymentReceiptFile;
        const fileName = file.name;
        const fileSize = this.formatFileSize(file.size);
        const isImage = file.type.startsWith('image/');

        let previewHTML = `
            <div class="payment-receipt-preview">
                ${isImage 
                    ? `<img src="${URL.createObjectURL(file)}" alt="Receipt preview" class="payment-receipt-preview-image">`
                    : `<div class="payment-receipt-preview-image" style="background: linear-gradient(135deg, #EEA24B 0%, #F57C00 100%); display: flex; align-items: center; justify-content: center; color: white; font-weight: 700; font-size: 0.875rem;">PDF</div>`
                }
                <div class="payment-receipt-preview-info">
                    <p class="payment-receipt-preview-name">${fileName}</p>
                    <p class="payment-receipt-preview-size">${fileSize}</p>
                </div>
                <button class="payment-receipt-remove" aria-label="Remove receipt">
                    <i class="fi fi-rr-cross"></i>
                </button>
            </div>
        `;

        previewContainer.innerHTML = previewHTML;

        // Remove button handler
        const removeBtn = previewContainer.querySelector('.payment-receipt-remove');
        if (removeBtn) {
            removeBtn.addEventListener('click', () => {
                this.paymentReceiptFile = null;
                this.updatePaymentReceiptPreview();
                const fileInput = document.getElementById('paymentFileInput');
                if (fileInput) {
                    fileInput.value = '';
                }
            });
        }
    }

    handleSubmitPayment() {
        const receiptNumber = document.getElementById('receiptNumberInput');
        const amountPaid = document.getElementById('amountPaidInput');
        const description = document.getElementById('paymentDescriptionTextarea');

        const receiptNumberValue = receiptNumber ? receiptNumber.value.trim() : '';
        const amountPaidValue = amountPaid ? amountPaid.value.trim() : '';
        const descriptionValue = description ? description.value.trim() : '';

        // Validation
        if (!receiptNumberValue) {
            this.showNotification('Please enter receipt number', 'error');
            if (receiptNumber) {
                receiptNumber.focus();
                receiptNumber.style.borderColor = '#dc2626';
                setTimeout(() => {
                    receiptNumber.style.borderColor = '';
                }, 2000);
            }
            return;
        }

        if (!amountPaidValue) {
            this.showNotification('Please enter amount paid', 'error');
            if (amountPaid) {
                amountPaid.focus();
                amountPaid.style.borderColor = '#dc2626';
                setTimeout(() => {
                    amountPaid.style.borderColor = '';
                }, 2000);
            }
            return;
        }

        if (!this.paymentReceiptFile) {
            this.showNotification('Please upload a receipt', 'error');
            const uploadArea = document.getElementById('paymentUploadArea');
            if (uploadArea) {
                uploadArea.style.borderColor = '#dc2626';
                setTimeout(() => {
                    uploadArea.style.borderColor = '';
                }, 2000);
            }
            return;
        }

        const submitBtn = document.getElementById('submitPaymentBtn');
        if (submitBtn) {
            const originalText = submitBtn.innerHTML;
            submitBtn.innerHTML = '<i class="fi fi-rr-spinner"></i> Submitting...';
            submitBtn.disabled = true;

            // In a real implementation, send payment data to server
            console.log('Submitting payment:', {
                milestoneData: this.milestoneData,
                receiptNumber: receiptNumberValue,
                amountPaid: amountPaidValue,
                description: descriptionValue,
                receiptFile: this.paymentReceiptFile
            });

            // Simulate API call
            setTimeout(() => {
                this.showNotification('Payment receipt submitted successfully', 'success');
                
                // Close modal after a short delay
                setTimeout(() => {
                    this.closePaymentModal();
                    // Restore button state
                    submitBtn.innerHTML = originalText;
                    submitBtn.disabled = false;
                }, 1500);
            }, 1000);
        }
    }
}

// Initialize modal when DOM is ready
let progressReportModalInstance = null;

function initializeProgressReportModal() {
    if (!progressReportModalInstance) {
        progressReportModalInstance = new OwnerProgressreportModal();
        
        // Make it globally accessible
        window.openProgressReportModal = (reportData, milestoneData) => {
            if (progressReportModalInstance) {
                progressReportModalInstance.open(reportData, milestoneData);
            }
        };

        // Make payment modal globally accessible
        window.openPaymentModal = (milestoneData) => {
            if (progressReportModalInstance) {
                progressReportModalInstance.showPaymentModal(milestoneData);
            }
        };
    }
}

// Initialize when DOM is ready
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initializeProgressReportModal);
} else {
    // DOM is already loaded
    initializeProgressReportModal();
}
