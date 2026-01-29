/**
 * Apply Bid Modal JavaScript
 * Handles modal opening/closing, form submission, and file uploads
 */

class ApplyBidModal {
    constructor() {
        this.modal = document.getElementById('applyBidModal');
        this.overlay = document.getElementById('applyBidModalOverlay');
        this.backBtn = document.getElementById('applyBidBackBtn');
        this.closeBtn = document.getElementById('closeApplyBidModalBtn');
        this.cancelBtn = document.getElementById('cancelApplyBidBtn');
        this.form = document.getElementById('applyBidForm');
        this.submitBtn = document.getElementById('submitApplyBidBtn');
        this.fileUploadArea = document.getElementById('fileUploadArea');
        this.fileInput = document.getElementById('modalSupportingDocuments');
        this.filePreviewContainer = document.getElementById('filePreviewContainer');
        this.selectedFiles = [];
        this.currentProject = null;
        
        this.init();
    }

    init() {
        this.setupEventListeners();
        this.setupFileUpload();
        this.setupInputFormatting();
        this.setupCharacterCount();
    }

    setupCharacterCount() {
        const messageTextarea = document.getElementById('modalCompellingMessage');
        const charCountElement = document.getElementById('messageCharCount');
        
        if (messageTextarea && charCountElement) {
            messageTextarea.addEventListener('input', () => {
                const length = messageTextarea.value.length;
                charCountElement.textContent = length;
                
                // Update character count styling
                if (length >= 950) {
                    charCountElement.parentElement.classList.add('error');
                    charCountElement.parentElement.classList.remove('warning');
                } else if (length >= 800) {
                    charCountElement.parentElement.classList.add('warning');
                    charCountElement.parentElement.classList.remove('error');
                } else {
                    charCountElement.parentElement.classList.remove('warning', 'error');
                }
            });
        }
    }

    setupInputFormatting() {
        // Format proposed cost input
        const proposedCostInput = document.getElementById('modalProposedCost');
        if (proposedCostInput) {
            proposedCostInput.addEventListener('input', (e) => {
                this.formatCostInput(e.target);
            });

            proposedCostInput.addEventListener('blur', (e) => {
                this.formatCostOnBlur(e.target);
            });
        }

        // Format timeline input (only allow numbers)
        const timelineInput = document.getElementById('modalEstimatedTimeline');
        if (timelineInput) {
            timelineInput.addEventListener('input', (e) => {
                // Only allow numbers and decimal point
                e.target.value = e.target.value.replace(/[^0-9.]/g, '');
            });
        }
    }

    formatCostInput(input) {
        let value = input.value.replace(/[^0-9.]/g, '');
        
        // Remove multiple decimal points
        const parts = value.split('.');
        if (parts.length > 2) {
            value = parts[0] + '.' + parts.slice(1).join('');
        }
        
        input.value = value;
    }

    formatCostOnBlur(input) {
        const value = parseFloat(input.value);
        if (!isNaN(value) && value > 0) {
            // Format with commas for thousands
            input.value = value.toLocaleString('en-US', { 
                minimumFractionDigits: 2, 
                maximumFractionDigits: 2 
            });
        }
    }

    setupEventListeners() {
        // Close modal
        if (this.closeBtn) {
            this.closeBtn.addEventListener('click', () => this.closeModal());
        }

        if (this.backBtn) {
            this.backBtn.addEventListener('click', () => this.closeModal());
        }

        if (this.cancelBtn) {
            this.cancelBtn.addEventListener('click', () => this.closeModal());
        }

        if (this.overlay) {
            this.overlay.addEventListener('click', () => this.closeModal());
        }

        // Form submission
        if (this.form) {
            this.form.addEventListener('submit', (e) => this.handleSubmit(e));
        }

        // Close on Escape key
        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape' && this.modal && this.modal.classList.contains('active')) {
                this.closeModal();
            }
        });
    }

    openModal(projectData) {
        if (this.modal) {
            this.currentProject = projectData;
            this.populateProjectInfo(projectData);
            this.modal.classList.add('active');
            document.body.style.overflow = 'hidden';
            
            // Focus on first input
            const firstInput = this.modal.querySelector('input[type="text"]');
            if (firstInput) {
                setTimeout(() => firstInput.focus(), 100);
            }
        }
    }

    closeModal() {
        if (this.modal) {
            this.modal.classList.remove('active');
            document.body.style.overflow = '';
            this.resetForm();
        }
    }

    populateProjectInfo(project) {
        const projectType = document.getElementById('modalProjectType');
        const projectName = document.getElementById('modalProjectName');
        const projectId = document.getElementById('modalProjectId');

        if (projectType && project) {
            const type = project.project_type || project.type || 'General';
            projectType.textContent = type;
        }

        if (projectName && project) {
            const title = project.title || project.project_title || 'Untitled Project';
            projectName.textContent = title;
        }

        if (projectId && project) {
            const id = project.id || project.project_id || '';
            projectId.value = id;
        }
    }

    resetForm() {
        if (this.form) {
            this.form.reset();
            this.hideAllErrors();
            this.hideAlerts();
            this.selectedFiles = [];
            this.updateFilePreview();
            this.currentProject = null;
            
            // Reset character count
            const charCountElement = document.getElementById('messageCharCount');
            if (charCountElement) {
                charCountElement.textContent = '0';
                charCountElement.parentElement.classList.remove('warning', 'error');
            }
        }
    }

    setupFileUpload() {
        if (!this.fileUploadArea || !this.fileInput) return;

        // Click to upload
        this.fileUploadArea.addEventListener('click', () => {
            this.fileInput.click();
        });

        // File input change
        this.fileInput.addEventListener('change', (e) => {
            this.handleFileSelection(e.target.files);
        });

        // Drag and drop
        this.fileUploadArea.addEventListener('dragover', (e) => {
            e.preventDefault();
            this.fileUploadArea.classList.add('dragover');
        });

        this.fileUploadArea.addEventListener('dragleave', () => {
            this.fileUploadArea.classList.remove('dragover');
        });

        this.fileUploadArea.addEventListener('drop', (e) => {
            e.preventDefault();
            this.fileUploadArea.classList.remove('dragover');
            const files = Array.from(e.dataTransfer.files);
            this.handleFileSelection(files);
        });
    }

    handleFileSelection(files) {
        const fileArray = Array.from(files);
        const maxSize = 10 * 1024 * 1024; // 10MB
        const allowedTypes = ['application/pdf', 'application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document', 'image/jpeg', 'image/jpg', 'image/png'];

        fileArray.forEach(file => {
            // Validate file size
            if (file.size > maxSize) {
                this.showError(`File "${file.name}" exceeds 10MB limit`);
                return;
            }

            // Validate file type
            if (!allowedTypes.includes(file.type)) {
                this.showError(`File "${file.name}" is not a supported format`);
                return;
            }

            // Check if file already exists
            const exists = this.selectedFiles.some(f => f.name === file.name && f.size === file.size);
            if (!exists) {
                this.selectedFiles.push(file);
            }
        });

        this.updateFilePreview();
    }

    updateFilePreview() {
        if (!this.filePreviewContainer) return;

        this.filePreviewContainer.innerHTML = '';

        if (this.selectedFiles.length === 0) {
            return;
        }

        this.selectedFiles.forEach((file, index) => {
            const previewItem = document.createElement('div');
            previewItem.className = 'file-preview-item';
            
            const fileSize = this.formatFileSize(file.size);
            const fileIcon = this.getFileIcon(file.type);

            previewItem.innerHTML = `
                <div class="file-preview-info">
                    <i class="fi ${fileIcon} file-preview-icon"></i>
                    <div>
                        <div class="file-preview-name">${file.name}</div>
                        <div class="file-preview-size">${fileSize}</div>
                    </div>
                </div>
                <button type="button" class="file-preview-remove" data-file-index="${index}" aria-label="Remove file">
                    <i class="fi fi-rr-cross-small"></i>
                </button>
            `;

            // Remove file handler
            const removeBtn = previewItem.querySelector('.file-preview-remove');
            removeBtn.addEventListener('click', () => {
                this.removeFile(index);
            });

            this.filePreviewContainer.appendChild(previewItem);
        });
    }

    removeFile(index) {
        this.selectedFiles.splice(index, 1);
        this.updateFilePreview();
        this.updateFileInput();
    }

    updateFileInput() {
        if (!this.fileInput) return;

        // Create a new DataTransfer object to update files
        const dataTransfer = new DataTransfer();
        this.selectedFiles.forEach(file => {
            dataTransfer.items.add(file);
        });
        this.fileInput.files = dataTransfer.files;
    }

    formatFileSize(bytes) {
        if (bytes === 0) return '0 Bytes';
        const k = 1024;
        const sizes = ['Bytes', 'KB', 'MB', 'GB'];
        const i = Math.floor(Math.log(bytes) / Math.log(k));
        return Math.round(bytes / Math.pow(k, i) * 100) / 100 + ' ' + sizes[i];
    }

    getFileIcon(fileType) {
        if (fileType.includes('pdf')) return 'fi-rr-file-pdf';
        if (fileType.includes('word') || fileType.includes('document')) return 'fi-rr-file-word';
        if (fileType.includes('image')) return 'fi-rr-file-image';
        return 'fi-rr-file';
    }

    async handleSubmit(e) {
        e.preventDefault();

        if (!this.validateForm()) {
            return;
        }

        // Disable submit button
        if (this.submitBtn) {
            this.submitBtn.disabled = true;
            this.submitBtn.classList.add('loading');
            this.submitBtn.innerHTML = '<span>Submitting...</span>';
        }

        try {
            const formData = new FormData(this.form);

            // Format proposed cost (remove commas)
            const proposedCost = document.getElementById('modalProposedCost');
            if (proposedCost) {
                const costValue = proposedCost.value.replace(/[^0-9.]/g, '');
                formData.set('proposed_cost', costValue);
            }

            // Add files to FormData
            this.selectedFiles.forEach((file, index) => {
                formData.append(`supporting_documents[${index}]`, file);
            });

            // TODO: Replace with actual API endpoint
            // const response = await fetch('/api/contractor/bids', {
            //     method: 'POST',
            //     body: formData,
            //     headers: {
            //         'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            //     }
            // });

            // const data = await response.json();

            // Simulate API call
            await new Promise(resolve => setTimeout(resolve, 1500));

            this.showSuccess('Bid application submitted successfully!');
            
            // Close modal after delay
            setTimeout(() => {
                this.closeModal();
            }, 2000);

        } catch (error) {
            console.error('Error submitting bid:', error);
            this.showError('Failed to submit bid. Please try again.');
        } finally {
            // Re-enable submit button
            if (this.submitBtn) {
                this.submitBtn.disabled = false;
                this.submitBtn.classList.remove('loading');
                this.submitBtn.innerHTML = '<span>Submit</span>';
            }
        }
    }

    validateForm() {
        let isValid = true;
        this.hideAllErrors();

        // Validate proposed cost
        const proposedCost = document.getElementById('modalProposedCost');
        if (!proposedCost || !proposedCost.value.trim()) {
            this.showFieldError('proposed_cost', 'Proposed cost is required');
            isValid = false;
        } else {
            const cost = parseFloat(proposedCost.value.replace(/[^0-9.]/g, ''));
            if (isNaN(cost) || cost <= 0) {
                this.showFieldError('proposed_cost', 'Please enter a valid cost amount');
                isValid = false;
            } else if (cost < 1000) {
                this.showFieldError('proposed_cost', 'Minimum cost is ₱1,000');
                isValid = false;
            }
        }

        // Validate estimated timeline
        const estimatedTimeline = document.getElementById('modalEstimatedTimeline');
        if (!estimatedTimeline || !estimatedTimeline.value.trim()) {
            this.showFieldError('estimated_timeline', 'Estimated timeline is required');
            isValid = false;
        } else {
            const timeline = parseFloat(estimatedTimeline.value);
            if (isNaN(timeline) || timeline <= 0) {
                this.showFieldError('estimated_timeline', 'Please enter a valid timeline in months');
                isValid = false;
            }
        }

        // Validate compelling message
        const compellingMessage = document.getElementById('modalCompellingMessage');
        if (!compellingMessage || !compellingMessage.value.trim()) {
            this.showFieldError('compelling_message', 'Compelling message is required');
            isValid = false;
        } else if (compellingMessage.value.trim().length < 20) {
            this.showFieldError('compelling_message', 'Message must be at least 20 characters');
            isValid = false;
        }

        return isValid;
    }

    showFieldError(fieldName, message) {
        const errorElement = document.getElementById(`error_${fieldName}`);
        if (errorElement) {
            errorElement.textContent = message;
            errorElement.classList.remove('hidden');
        }
    }

    hideAllErrors() {
        const errorElements = this.modal.querySelectorAll('.error-message');
        errorElements.forEach(element => {
            element.classList.add('hidden');
        });
    }

    showSuccess(message) {
        const successElement = document.getElementById('applyBidFormSuccess');
        if (successElement) {
            successElement.textContent = message;
            successElement.classList.remove('hidden');
            this.hideAlerts('error');
        }
    }

    showError(message) {
        const errorElement = document.getElementById('applyBidFormError');
        if (errorElement) {
            errorElement.textContent = message;
            errorElement.classList.remove('hidden');
            this.hideAlerts('success');
        }
    }

    hideAlerts(type) {
        if (type === 'success') {
            const successElement = document.getElementById('applyBidFormSuccess');
            if (successElement) successElement.classList.add('hidden');
        } else if (type === 'error') {
            const errorElement = document.getElementById('applyBidFormError');
            if (errorElement) errorElement.classList.add('hidden');
        } else {
            const successElement = document.getElementById('applyBidFormSuccess');
            const errorElement = document.getElementById('applyBidFormError');
            if (successElement) successElement.classList.add('hidden');
            if (errorElement) errorElement.classList.add('hidden');
        }
    }
}

// Initialize when DOM is ready
document.addEventListener('DOMContentLoaded', () => {
    window.applyBidModal = new ApplyBidModal();
});
