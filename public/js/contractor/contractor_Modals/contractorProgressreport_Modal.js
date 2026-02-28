/**
 * Contractor Progress Report Modal JavaScript
 * Handles progress report submission (matching TSX ProgressReportForm flow)
 */

const ALLOWED_EXTENSIONS = ['pdf', 'doc', 'docx', 'zip', 'jpg', 'jpeg', 'png'];
const MAX_FILE_SIZE = 10 * 1024 * 1024; // 10MB
const MAX_FILES = 10;
const MIN_FILES = 1;
const MAX_PURPOSE_LENGTH = 1000;

class ContractorProgressreportModal {
    constructor() {
        this.modal = null;
        this.overlay = null;
        this.confirmationModal = null;
        this.confirmationOverlay = null;
        this.uploadedFiles = [];
        this.projectData = null;
        this.isSubmitting = false;

        this.init();
    }

    init() {
        this.modal = document.getElementById('progressReportModal');
        this.overlay = document.getElementById('progressReportModalOverlay');
        this.confirmationModal = document.getElementById('progressUploadSuccessModal');
        this.confirmationOverlay = document.getElementById('progressUploadSuccessModalOverlay');

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

        // Purpose textarea char counter
        const purposeInput = document.getElementById('reportPurpose');
        if (purposeInput) {
            purposeInput.addEventListener('input', () => {
                const counter = document.getElementById('purposeCharCount');
                if (counter) {
                    counter.textContent = purposeInput.value.length;
                }
            });
        }

        // Confirmation modal close
        const closeSuccessBtn = document.getElementById('closeUploadSuccessBtn');
        if (closeSuccessBtn) {
            closeSuccessBtn.addEventListener('click', () => this.closeConfirmationModal());
        }

        const doneSuccessBtn = document.getElementById('doneSuccessBtn');
        if (doneSuccessBtn) {
            doneSuccessBtn.addEventListener('click', () => this.closeConfirmationModal());
        }

        if (this.confirmationOverlay) {
            this.confirmationOverlay.addEventListener('click', () => this.closeConfirmationModal());
        }

        // Rejection Modal
        this.rejectionModal = document.getElementById('rejectionReasonModal');
        this.rejectionOverlay = document.getElementById('rejectionReasonModalOverlay');

        const closeRejectionBtn = document.getElementById('closeRejectionModalBtn');
        if (closeRejectionBtn) {
            closeRejectionBtn.addEventListener('click', () => this.closeRejectionModal());
        }

        const cancelRejectionBtn = document.getElementById('cancelRejectionBtn');
        if (cancelRejectionBtn) {
            cancelRejectionBtn.addEventListener('click', () => this.closeRejectionModal());
        }

        const submitRejectionBtn = document.getElementById('submitRejectionBtn');
        if (submitRejectionBtn) {
            submitRejectionBtn.addEventListener('click', () => this.handleSubmitRejection());
        }

        // Rejection File Upload
        this.setupRejectionFileUpload();

        // Payment Modal
        this.paymentModal = document.getElementById('milestonePaymentModal');
        this.paymentOverlay = document.getElementById('milestonePaymentModalOverlay');

        const closePaymentBtn = document.getElementById('closePaymentModalBtn');
        if (closePaymentBtn) {
            closePaymentBtn.addEventListener('click', () => this.closePaymentModal());
        }

        const cancelPaymentBtn = document.getElementById('cancelPaymentBtn');
        if (cancelPaymentBtn) {
            cancelPaymentBtn.addEventListener('click', () => this.closePaymentModal());
        }

        const submitPaymentBtn = document.getElementById('submitPaymentBtn');
        if (submitPaymentBtn) {
            submitPaymentBtn.addEventListener('click', () => this.handleSubmitPayment());
        }

        // Payment File Upload
        this.setupPaymentFileUpload();

        // Close on Escape key
        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape') {
                if (this.confirmationModal && this.confirmationModal.classList.contains('active')) {
                    this.closeConfirmationModal();
                } else if (this.rejectionModal && this.rejectionModal.classList.contains('active')) {
                    this.closeRejectionModal();
                } else if (this.paymentModal && this.paymentModal.classList.contains('active')) {
                    this.closePaymentModal();
                } else if (this.isOpen()) {
                    this.close();
                }
            }
        });
    }

    // --- Modal Management ---

    openEdit(data, progressData) {
        this.projectData = data || {};
        this.isEditMode = true;
        this.editingProgressId = progressData.id;
        this.deletedFileIds = [];
        this.uploadedFiles = [];

        this.populateModal(this.projectData);
        this.resetForm();
        this.isEditMode = true;

        const titleEl = this.modal ? this.modal.querySelector('.modal-title span') : null;
        if (titleEl) titleEl.textContent = 'Edit Progress Report';

        const submitBtn = document.getElementById('submitReportBtn');
        if (submitBtn) submitBtn.innerHTML = '<i class="fi fi-rr-check"></i> Save Changes';

        const purposeInput = document.getElementById('reportPurpose');
        if (purposeInput) {
            purposeInput.value = progressData.description || '';
            const counter = document.getElementById('purposeCharCount');
            if (counter) counter.textContent = purposeInput.value.length;
        }

        const itemIdInput = document.getElementById('formItemId');
        if (itemIdInput) itemIdInput.value = data.milestoneItemId || '';

        const editingIdInput = document.getElementById('editingProgressId');
        if (editingIdInput) editingIdInput.value = progressData.id || '';

        if (progressData.files && progressData.files.length > 0) {
            progressData.files.forEach(file => {
                this.uploadedFiles.push({
                    isExisting: true,
                    id: file.id,
                    name: file.name,
                    size: 0,
                    path: file.path
                });
            });
        }

        this.updateFilePreview();

        if (this.modal) {
            this.modal.classList.add('active');
            document.body.style.overflow = 'hidden';
        }
    }

    open(data) {
        this.projectData = data || {};
        this.populateModal(this.projectData);
        this.resetForm();

        // Set hidden item_id field
        const itemIdInput = document.getElementById('formItemId');
        if (itemIdInput) {
            itemIdInput.value = data.milestoneItemId || '';
        }

        if (this.modal) {
            this.modal.classList.add('active');
            document.body.style.overflow = 'hidden';
        }
    }

    close() {
        if (this.modal) {
            this.modal.classList.remove('active');
            this.checkAndClearOverflow();
        }
        this.resetForm();
    }

    isOpen() {
        return this.modal && this.modal.classList.contains('active');
    }

    getCsrfToken() {
        return document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
    }

    // --- Rejection Modal ---

    openRejection(data) {
        this.projectData = data || {};
        if (this.rejectionModal) {
            // Populate info
            const milestoneValue = document.getElementById('rejectionMilestoneValue');
            if (milestoneValue) {
                milestoneValue.textContent = data.milestoneTitle || 'Milestone';
            }

            // Reset form
            const textarea = document.getElementById('rejectionReasonTextarea');
            if (textarea) textarea.value = '';
            this.rejectionFiles = [];
            this.updateRejectionFileList();

            this.rejectionModal.classList.add('active');
            document.body.style.overflow = 'hidden';
        }
    }

    closeRejectionModal() {
        if (this.rejectionModal) {
            this.rejectionModal.classList.remove('active');
            this.checkAndClearOverflow();
        }
    }

    setupRejectionFileUpload() {
        const uploadArea = document.getElementById('rejectionUploadArea');
        const fileInput = document.getElementById('rejectionFileInput');
        if (!uploadArea || !fileInput) return;

        uploadArea.addEventListener('click', () => fileInput.click());
        fileInput.addEventListener('change', (e) => this.handleRejectionFileSelect(e));

        uploadArea.addEventListener('dragover', (e) => {
            e.preventDefault();
            uploadArea.classList.add('dragover');
        });
        uploadArea.addEventListener('dragleave', () => uploadArea.classList.remove('dragover'));
        uploadArea.addEventListener('drop', (e) => {
            e.preventDefault();
            uploadArea.classList.remove('dragover');
            this.handleRejectionFileSelect(e, e.dataTransfer.files);
        });
    }

    handleRejectionFileSelect(e, droppedFiles = null) {
        const files = droppedFiles || Array.from(e.target.files);
        if (!this.rejectionFiles) this.rejectionFiles = [];

        files.forEach(file => {
            if (!this.rejectionFiles.find(f => f.name === file.name && f.size === file.size)) {
                this.rejectionFiles.push(file);
            }
        });
        this.updateRejectionFileList();
    }

    updateRejectionFileList() {
        const container = document.getElementById('rejectionUploadedFiles');
        if (!container) return;
        container.innerHTML = '';

        (this.rejectionFiles || []).forEach((file, index) => {
            const fileItem = document.createElement('div');
            fileItem.className = 'uploaded-file-item';
            fileItem.innerHTML = `
                <div class="uploaded-file-info">
                    <div class="uploaded-file-icon">FILE</div>
                    <p class="uploaded-file-name">${file.name}</p>
                </div>
                <button class="uploaded-file-remove" data-index="${index}">
                    <i class="fi fi-rr-cross"></i>
                </button>
            `;
            fileItem.querySelector('.uploaded-file-remove').addEventListener('click', () => {
                this.rejectionFiles.splice(index, 1);
                this.updateRejectionFileList();
            });
            container.appendChild(fileItem);
        });
    }

    async handleSubmitRejection() {
        const textarea = document.getElementById('rejectionReasonTextarea');
        const reason = textarea ? textarea.value.trim() : '';

        if (!reason) {
            this.showNotification('Please provide a reason for rejection', 'error');
            return;
        }

        const submitBtn = document.getElementById('submitRejectionBtn');
        if (submitBtn) {
            const originalText = submitBtn.innerHTML;
            submitBtn.innerHTML = '<i class="fi fi-rr-spinner"></i> Submitting...';
            submitBtn.disabled = true;

            const progressId = this.projectData.progress_id;
            const formData = new FormData();
            formData.append('reason', reason);

            (this.rejectionFiles || []).forEach(file => {
                formData.append('rejection_files[]', file);
            });

            try {
                const response = await fetch(`/contractor/progress/reject/${progressId}`, {
                    method: 'POST',
                    headers: { 'X-CSRF-TOKEN': this.getCsrfToken(), 'Accept': 'application/json' },
                    body: formData
                });
                const data = await response.json();

                if (data.success) {
                    this.showNotification('Report rejected successfully', 'success');
                    this.closeRejectionModal();
                    if (window.refreshMilestoneData) window.refreshMilestoneData();
                    else window.location.reload();
                } else {
                    this.showNotification(data.message || 'Failed to reject report', 'error');
                }
            } catch (error) {
                console.error('Rejection error:', error);
                this.showNotification('An error occurred during rejection', 'error');
            } finally {
                submitBtn.innerHTML = originalText;
                submitBtn.disabled = false;
            }
        }
    }

    // --- Payment Modal ---

    openPayment(data) {
        this.projectData = data || {};
        if (this.paymentModal) {
            const milestoneValue = document.getElementById('paymentMilestoneValue');
            if (milestoneValue) {
                milestoneValue.textContent = data.milestoneTitle || 'Milestone';
            }

            // Reset
            const receipt = document.getElementById('receiptNumberInput');
            const amount = document.getElementById('amountPaidInput');
            const desc = document.getElementById('paymentDescriptionTextarea');
            if (receipt) receipt.value = '';
            if (amount) amount.value = '';
            if (desc) desc.value = '';
            this.paymentReceiptFile = null;
            this.updatePaymentReceiptPreview();

            this.paymentModal.classList.add('active');
            document.body.style.overflow = 'hidden';
        }
    }

    closePaymentModal() {
        if (this.paymentModal) {
            this.paymentModal.classList.remove('active');
            this.checkAndClearOverflow();
        }
    }

    checkAndClearOverflow() {
        if (!this.isOpen() && !this.isPaymentOpen() && !this.isRejectionOpen()) {
            const viewModal = document.getElementById('viewProgressReportModal');
            if (!viewModal || !viewModal.classList.contains('active')) {
                document.body.style.overflow = '';
            }
        }
    }

    isPaymentOpen() {
        return this.paymentModal && this.paymentModal.classList.contains('active');
    }

    isRejectionOpen() {
        return this.rejectionModal && this.rejectionModal.classList.contains('active');
    }

    setupPaymentFileUpload() {
        const uploadArea = document.getElementById('paymentUploadArea');
        const fileInput = document.getElementById('paymentFileInput');
        if (!uploadArea || !fileInput) return;

        uploadArea.addEventListener('click', () => fileInput.click());
        fileInput.addEventListener('change', (e) => {
            if (e.target.files && e.target.files[0]) {
                this.paymentReceiptFile = e.target.files[0];
                this.updatePaymentReceiptPreview();
            }
        });
    }

    updatePaymentReceiptPreview() {
        const container = document.getElementById('paymentUploadedReceipt');
        if (!container) return;
        container.innerHTML = '';

        if (this.paymentReceiptFile) {
            const preview = document.createElement('div');
            preview.className = 'uploaded-receipt-preview';
            preview.innerHTML = `
                <div class="receipt-info">
                    <i class="fi fi-rr-file-check"></i>
                    <span>${this.paymentReceiptFile.name}</span>
                </div>
                <button class="receipt-remove">Remove</button>
            `;
            preview.querySelector('.receipt-remove').addEventListener('click', () => {
                this.paymentReceiptFile = null;
                this.updatePaymentReceiptPreview();
            });
            container.appendChild(preview);
        }
    }

    async handleSubmitPayment() {
        const receipt = document.getElementById('receiptNumberInput')?.value.trim();
        const amount = document.getElementById('amountPaidInput')?.value.trim();
        const description = document.getElementById('paymentDescriptionTextarea')?.value.trim();

        if (!receipt || !amount) {
            this.showNotification('Receipt number and amount are required', 'error');
            return;
        }

        const submitBtn = document.getElementById('submitPaymentBtn');
        if (submitBtn) {
            const originalText = submitBtn.innerHTML;
            submitBtn.innerHTML = '<i class="fi fi-rr-spinner"></i> Submitting...';
            submitBtn.disabled = true;

            const formData = new FormData();
            formData.append('project_id', this.projectData.project_id);
            formData.append('item_id', this.projectData.milestoneItemId);
            formData.append('amount', amount);
            formData.append('transaction_number', receipt);
            formData.append('description', description);
            if (this.paymentReceiptFile) {
                formData.append('receipt_photo', this.paymentReceiptFile);
            }

            try {
                // Determine payment submission endpoint - assuming it exists or needs to be unified
                const response = await fetch('/owner/payment/upload', {
                    method: 'POST',
                    headers: { 'X-CSRF-TOKEN': this.getCsrfToken(), 'Accept': 'application/json' },
                    body: formData
                });
                const data = await response.json();

                if (data.success) {
                    this.showNotification('Payment submitted successfully', 'success');
                    this.closePaymentModal();
                    if (window.refreshMilestoneData) window.refreshMilestoneData();
                    else window.location.reload();
                } else {
                    this.showNotification(data.message || 'Failed to submit payment', 'error');
                }
            } catch (error) {
                console.error('Payment error:', error);
                this.showNotification('An error occurred during payment submission', 'error');
            } finally {
                submitBtn.innerHTML = originalText;
                submitBtn.disabled = false;
            }
        }
    }

    populateModal(data) {
        const projectTitleElement = document.getElementById('modalProjectTitle');
        if (projectTitleElement) {
            projectTitleElement.textContent = data.projectTitle || 'Project';
        }

        const milestoneTitleElement = document.getElementById('modalMilestoneTitle');
        if (milestoneTitleElement) {
            milestoneTitleElement.textContent = data.milestoneTitle || 'Milestone';
        }
    }

    resetForm() {
        const form = document.getElementById('progressReportForm');
        if (form) form.reset();

        this.uploadedFiles = [];
        this.isSubmitting = false;
        this.updateFilePreview();
        this.clearErrors();

        const counter = document.getElementById('purposeCharCount');
        if (counter) counter.textContent = '0';

        const submitBtn = document.getElementById('submitReportBtn');
        if (submitBtn) {
            submitBtn.disabled = false;
            submitBtn.innerHTML = '<i class="fi fi-rr-check"></i> Submit Report';
        }
    }

    handleFileSelect(event) {
        const files = Array.from(event.target.files);
        const invalidFiles = [];

        files.forEach(file => {
            const ext = file.name.split('.').pop().toLowerCase();
            if (!ALLOWED_EXTENSIONS.includes(ext)) {
                invalidFiles.push(`${file.name}: Invalid file type`);
                return;
            }

            if (file.size > MAX_FILE_SIZE) {
                invalidFiles.push(`${file.name}: Exceeds 10MB limit`);
                return;
            }

            this.uploadedFiles.push(file);
        });

        if (this.uploadedFiles.length > MAX_FILES) {
            this.uploadedFiles = this.uploadedFiles.slice(0, MAX_FILES);
            this.showNotification(`Maximum ${MAX_FILES} files allowed.`, 'error');
        }

        if (invalidFiles.length > 0) {
            this.showNotification(`Skipped: ${invalidFiles.join(', ')}`, 'error');
        }

        this.updateFilePreview();
    }

    updateFilePreview() {
        const container = document.getElementById('filePreviewContainer');
        if (!container) return;

        container.innerHTML = '';

        const filesCounter = document.getElementById('filesCounter');
        const fileCount = document.getElementById('fileCount');
        if (filesCounter && fileCount) {
            fileCount.textContent = this.uploadedFiles.length;
            filesCounter.style.display = this.uploadedFiles.length > 0 ? 'block' : 'none';
        }

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
        const file = this.uploadedFiles[index];
        if (file.isExisting) {
            if (!this.deletedFileIds) this.deletedFileIds = [];
            this.deletedFileIds.push(file.id);
            const deletedInput = document.getElementById('deletedFileIds');
            if (deletedInput) deletedInput.value = this.deletedFileIds.join(',');
        }
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
            'zip': 'fi fi-rr-archive',
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

    async handleSubmit() {
        if (this.isSubmitting) return;

        this.clearErrors();

        // Validate purpose
        const purposeInput = document.getElementById('reportPurpose');
        const purpose = purposeInput ? purposeInput.value.trim() : '';

        if (!purpose) {
            this.showError('purpose', 'Purpose is required.');
            if (purposeInput) purposeInput.focus();
            return;
        }

        if (purpose.length > MAX_PURPOSE_LENGTH) {
            this.showError('purpose', `Purpose must be less than ${MAX_PURPOSE_LENGTH} characters.`);
            return;
        }

        // Validate files (required, matching TSX)
        // Validate files constraint based on edit mode
        if (!this.isEditMode && this.uploadedFiles.length < MIN_FILES) {
            this.showError('progress_files', `At least ${MIN_FILES} file is required.`);
            return;
        }

        if (this.uploadedFiles.length === 0) {
            this.showError('progress_files', `You must have at least 1 file.`);
            return;
        }

        if (this.uploadedFiles.length > MAX_FILES) {
            this.showError('progress_files', `Maximum ${MAX_FILES} files allowed.`);
            return;
        }

        const itemIdInput = document.getElementById('formItemId');
        const itemId = itemIdInput ? itemIdInput.value : '';
        if (!itemId) {
            this.showNotification('Milestone item ID is missing.', 'error');
            return;
        }

        const formData = new FormData();
        formData.append('item_id', itemId);
        formData.append('purpose', purpose);

        const editingIdInput = document.getElementById('editingProgressId');
        const editingId = editingIdInput ? editingIdInput.value : '';
        const deletedInput = document.getElementById('deletedFileIds');
        const deletedIds = deletedInput ? deletedInput.value : '';

        let targetUrl = '/contractor/progress/upload';

        if (this.isEditMode && editingId) {
            targetUrl = `/contractor/progress/${editingId}`;
            formData.append('_method', 'PUT');
            if (deletedIds) formData.append('deleted_file_ids', deletedIds);
        }

        let newFileIndex = 0;
        this.uploadedFiles.forEach((file) => {
            if (!file.isExisting) {
                formData.append(`progress_files[${newFileIndex}]`, file);
                newFileIndex++;
            }
        });

        // Show loading state
        this.isSubmitting = true;
        const submitBtn = document.getElementById('submitReportBtn');
        if (submitBtn) {
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<i class="fi fi-rr-spinner" style="animation: spin 1s linear infinite;"></i> Submitting...';
        }

        try {
            const response = await fetch(targetUrl, {
                method: 'POST',
                headers: {
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
                },
                body: formData,
            });

            const result = await response.json();

            if (response.status === 422) {
                // Handle Laravel validation errors
                if (result.errors) {
                    Object.keys(result.errors).forEach(field => {
                        const message = Array.isArray(result.errors[field])
                            ? result.errors[field][0]
                            : result.errors[field];
                        this.showError(field, message);
                    });
                } else {
                    this.showNotification(result.message || 'Validation failed.', 'error');
                }
            } else if (result.success || response.ok) {
                this.showNotification('Progress report submitted successfully', 'success');
                this.close();

                // Also close the details modal if it's open
                if (window.contractorViewProgressReportModalInstance) {
                    window.contractorViewProgressReportModalInstance.close();
                }
                setTimeout(() => {
                    if (window.refreshMilestoneData) window.refreshMilestoneData();
                    else window.location.reload();
                }, 1000);
            } else {
                this.showNotification(result.message || 'Failed to submit progress report.', 'error');
            }
        } catch (error) {
            console.error('Error submitting progress report:', error);
            this.showNotification('An unexpected error occurred. Please try again.', 'error');
        } finally {
            this.isSubmitting = false;
            if (submitBtn) {
                submitBtn.disabled = false;
                submitBtn.innerHTML = '<i class="fi fi-rr-check"></i> Submit Report';
            }
        }
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

        this.close();

        // Also close the details modal if it's open
        if (window.contractorViewProgressReportModalInstance) {
            window.contractorViewProgressReportModalInstance.close();
        }

        // Reload page to reflect the new progress report
        window.location.reload();
    }

    showError(field, message) {
        // field might be 'progress_files[0]', 'purpose', etc.
        // We handle mapping 'progress_files[0]' to 'progress_files'
        let fieldId = field;
        if (field.startsWith('progress_files')) {
            fieldId = 'progress_files';
        }

        const errorEl = document.getElementById(`error_${fieldId}`);
        if (errorEl) {
            errorEl.textContent = message;
            errorEl.style.display = 'block';
        }

        // Also add error class to input
        const input = document.getElementById(fieldId === 'purpose' ? 'reportPurpose' : 'reportFiles');
        if (input) {
            input.classList.add('is-invalid');
        }
    }

    clearErrors() {
        const errorEls = document.querySelectorAll('.error-message');
        errorEls.forEach(el => {
            el.textContent = '';
            el.style.display = 'none';
        });

        const inputs = document.querySelectorAll('.is-invalid');
        inputs.forEach(input => {
            input.classList.remove('is-invalid');
        });
    }

    showNotification(message, type = 'info') {
        const toast = document.createElement('div');
        const bgColors = {
            error: 'background: linear-gradient(135deg, #ef4444, #dc2626); color: white;',
            success: 'background: linear-gradient(135deg, #10b981, #059669); color: white;',
            info: 'background: linear-gradient(135deg, #EEA24B, #F57C00); color: white;',
        };
        toast.style.cssText = `
            position: fixed; top: 2rem; right: 2rem;
            padding: 0.875rem 1.5rem; border-radius: 0.75rem;
            font-size: 0.875rem; font-weight: 600; z-index: 9999;
            box-shadow: 0 8px 24px rgba(0,0,0,0.2);
            transition: opacity 0.3s ease;
            ${bgColors[type] || bgColors.info}
        `;
        toast.textContent = message;
        document.body.appendChild(toast);

        setTimeout(() => {
            toast.style.opacity = '0';
            setTimeout(() => {
                if (document.body.contains(toast)) {
                    document.body.removeChild(toast);
                }
            }, 300);
        }, 3000);
    }
}

// Initialize modal when DOM is ready
window.contractorProgressreportModalInstance = null;

document.addEventListener('DOMContentLoaded', () => {
    window.contractorProgressreportModalInstance = new ContractorProgressreportModal();
});

// Export for use in other scripts
window.ContractorProgressreportModal = ContractorProgressreportModal;
window.openProgressReportModal = (data) => {
    if (window.contractorProgressreportModalInstance) {
        window.contractorProgressreportModalInstance.open(data);
    } else {
        setTimeout(() => {
            if (window.contractorProgressreportModalInstance) {
                window.contractorProgressreportModalInstance.open(data);
            }
        }, 100);
    }
};
