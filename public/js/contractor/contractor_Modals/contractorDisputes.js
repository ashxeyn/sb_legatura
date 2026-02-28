/**
 * Contractor Disputes & Report History JavaScript
 * Handles the logic for filing disputes and viewing history
 */

class ContractorDisputes {
    constructor() {
        this.disputeModal = null;
        this.historyModal = null;
        this.currentItemId = null;
        this.projectId = null;
        this.selectedFiles = []; // Track files for removal UI
        this.allDisputes = []; // Store fetched disputes for client-side filtering
        this.currentFilter = 'all';

        this.init();
    }

    init() {
        this.disputeModal = document.getElementById('sendReportModal');
        this.historyModal = document.getElementById('reportHistoryModal');

        this.setupEventListeners();
    }

    setupEventListeners() {
        // Dispute Modal Close
        const closeBtn = document.getElementById('sendReportCloseBtn');
        const cancelBtn = document.getElementById('sendReportCancelBtn');
        if (closeBtn) closeBtn.addEventListener('click', () => this.closeDisputeModal());
        if (cancelBtn) cancelBtn.addEventListener('click', () => this.closeDisputeModal());

        // History Modal Close
        const historyCloseBtn = document.getElementById('reportHistoryCloseBtn');
        if (historyCloseBtn) historyCloseBtn.addEventListener('click', () => this.closeHistoryModal());

        const historyRefreshBtn = document.getElementById('reportHistoryRefreshBtn');
        if (historyRefreshBtn) historyRefreshBtn.addEventListener('click', () => this.loadHistory(this.currentItemId));

        // Filter Pills
        const filterPills = document.querySelectorAll('#reportHistoryModal .filter-pill');
        filterPills.forEach(pill => {
            pill.addEventListener('click', () => {
                filterPills.forEach(p => p.classList.remove('active'));
                pill.classList.add('active');
                this.currentFilter = pill.getAttribute('data-status');
                this.filterHistory();
            });
        });

        // Dispute Type Selection
        const disputeOptions = document.querySelectorAll('.dispute-option');
        const reportTypeInput = document.getElementById('reportType');
        disputeOptions.forEach(option => {
            option.addEventListener('click', () => {
                disputeOptions.forEach(opt => opt.classList.remove('selected'));
                option.classList.add('selected');
                const val = option.getAttribute('data-value');
                if (reportTypeInput) reportTypeInput.value = val;

                // Toggle Others specification
                const specifyContainer = document.getElementById('specifyDisputeContainer');
                const specifyInput = document.getElementById('if_others_distype');
                if (val === 'Others') {
                    specifyContainer?.classList.remove('hidden');
                    if (specifyInput) specifyInput.required = true;
                } else {
                    specifyContainer?.classList.add('hidden');
                    if (specifyInput) {
                        specifyInput.required = false;
                        specifyInput.value = '';
                    }
                }

                this.clearFieldError('dispute_type');
            });
        });

        const specifyInput = document.getElementById('if_others_distype');
        if (specifyInput) {
            specifyInput.addEventListener('input', () => this.clearFieldError('if_others_distype'));
            specifyInput.addEventListener('click', () => this.clearFieldError('if_others_distype'));
        }

        const description = document.getElementById('reportDescription');
        const charCount = document.getElementById('charCount');
        if (description) {
            description.addEventListener('input', () => {
                this.clearFieldError('dispute_desc');
                if (charCount) charCount.textContent = description.value.length;
            });
            description.addEventListener('click', () => this.clearFieldError('dispute_desc'));
        }

        // Form Submission
        const form = document.getElementById('sendReportForm');
        if (form) {
            form.addEventListener('submit', (e) => this.handleDisputeSubmit(e));
        }

        // Success Modal Close
        const successDoneBtn = document.getElementById('sendReportSuccessDone');
        if (successDoneBtn) {
            successDoneBtn.addEventListener('click', () => {
                const successModal = document.getElementById('sendReportSuccessModal');
                if (successModal) successModal.classList.add('hidden');
                document.body.style.overflow = '';
                // Optional: window.location.reload() since the user wanted reload on success
                window.location.reload();
            });
        }

        // Error Modal Close
        const errorDoneBtn = document.getElementById('sendReportErrorDone');
        if (errorDoneBtn) errorDoneBtn.addEventListener('click', () => this.closeErrorModal());


        // File Upload Handling
        const fileInput = document.getElementById('disputeEvidenceFiles');
        const uploadArea = document.getElementById('disputeFileUploadArea');

        if (fileInput) {
            fileInput.addEventListener('change', (e) => this.handleFileSelect(e));
        }

        if (uploadArea && fileInput) {
            // Click trigger fallback
            uploadArea.addEventListener('click', () => {
                fileInput.click();
            });

            ['dragenter', 'dragover', 'dragleave', 'drop'].forEach(eventName => {
                uploadArea.addEventListener(eventName, (e) => {
                    e.preventDefault();
                    e.stopPropagation();
                }, false);
            });

            ['dragenter', 'dragover'].forEach(eventName => {
                uploadArea.addEventListener(eventName, () => uploadArea.classList.add('dragover'), false);
            });

            ['dragleave', 'drop'].forEach(eventName => {
                uploadArea.addEventListener(eventName, () => uploadArea.classList.remove('dragover'), false);
            });

            uploadArea.addEventListener('drop', (e) => {
                const dt = e.dataTransfer;
                const files = dt.files;
                this.handleFiles(files);
            }, false);

            // Clear file error on interaction
            uploadArea.addEventListener('click', () => this.clearFieldError('evidence_files'));
        }
    }

    handleFileSelect(e) {
        this.handleFiles(e.target.files);
    }

    handleFiles(files) {
        const fileArray = Array.from(files);
        const maxFiles = 5;
        const maxSize = 5 * 1024 * 1024; // 5MB

        fileArray.forEach(file => {
            if (this.selectedFiles.length >= maxFiles) {
                alert(`Maximum ${maxFiles} files allowed.`);
                return;
            }

            if (file.size > maxSize) {
                alert(`File ${file.name} is too large (max 5MB).`);
                return;
            }

            // Check for duplicates
            if (!this.selectedFiles.some(f => f.name === file.name && f.size === file.size)) {
                this.selectedFiles.push(file);
            }
        });

        this.updateFileInput();
        this.renderFileList();
    }

    updateFileInput() {
        const fileInput = document.getElementById('disputeEvidenceFiles');
        if (!fileInput) return;

        const dataTransfer = new DataTransfer();
        this.selectedFiles.forEach(file => dataTransfer.items.add(file));
        fileInput.files = dataTransfer.files;
    }

    renderFileList() {
        const list = document.getElementById('disputeFileList');
        if (!list) return;

        list.innerHTML = '';
        this.selectedFiles.forEach((file, index) => {
            const item = document.createElement('div');
            item.className = 'file-item';

            const isImage = file.type.startsWith('image/');
            const icon = isImage ? 'fi-rr-picture' : 'fi-rr-document';

            item.innerHTML = `
                <div class="file-item-info">
                    <i class="fi ${icon}"></i>
                    <span class="file-name" title="${file.name}">${file.name}</span>
                    <span class="file-size">(${(file.size / 1024 / 1024).toFixed(2)} MB)</span>
                </div>
                <button type="button" class="file-remove-btn" data-index="${index}">
                    <i class="fi fi-rr-cross-small"></i>
                </button>
            `;

            item.querySelector('.file-remove-btn').onclick = () => this.removeFile(index);
            list.appendChild(item);
        });
    }

    removeFile(index) {
        this.selectedFiles.splice(index, 1);
        this.updateFileInput();
        this.renderFileList();
    }

    openDisputeModal(itemId, itemName, milestoneId = null, projectId = null, preselectType = null) {
        this.currentItemId = itemId;
        const nameEl = document.getElementById('reportMilestoneName');
        const itemIdInput = document.getElementById('reportItemId');
        const milestoneIdInput = document.getElementById('reportMilestoneId');
        const projectIdInput = document.getElementById('reportProjectId');

        if (nameEl) nameEl.textContent = itemName || 'Milestone Item';
        if (itemIdInput) itemIdInput.value = itemId;
        if (milestoneIdInput) milestoneIdInput.value = milestoneId || '';
        if (projectIdInput) projectIdInput.value = projectId || '';

        if (this.disputeModal) {
            this.disputeModal.classList.remove('hidden');
            document.body.style.overflow = 'hidden';
        }

        // Preselect type if provided
        if (preselectType) {
            const option = document.querySelector(`.dispute-option[data-value="${preselectType}"]`);
            if (option) option.click();
        }
    }

    closeDisputeModal() {
        if (this.disputeModal) {
            this.disputeModal.classList.add('hidden');
            document.body.style.overflow = '';
            const form = document.getElementById('sendReportForm');
            if (form) form.reset();
            const disputeOptions = document.querySelectorAll('.dispute-option');
            disputeOptions.forEach(opt => opt.classList.remove('selected'));
            const charCount = document.getElementById('charCount');
            if (charCount) charCount.textContent = '0';

            // Reset files
            this.selectedFiles = [];
            this.updateFileInput();
            this.renderFileList();

            // Hide specify container
            document.getElementById('specifyDisputeContainer')?.classList.add('hidden');

            this.clearErrors();
        }
    }

    showError(field, message) {
        const errorEl = document.getElementById(`error-${field}`);
        if (errorEl) {
            errorEl.textContent = message;
            errorEl.classList.add('visible');
        }

        // Apply invalid styling to the input or container
        if (field === 'dispute_type') {
            document.querySelector('.dispute-type-options')?.classList.add('invalid');
        } else if (field === 'evidence_files') {
            document.getElementById('disputeFileUploadArea')?.classList.add('invalid');
        } else if (field === 'dispute_desc') {
            document.getElementById('reportDescription')?.classList.add('invalid-input');
        } else if (field === 'if_others_distype') {
            document.getElementById('if_others_distype')?.classList.add('invalid-input');
        }
    }

    clearFieldError(field) {
        const errorEl = document.getElementById(`error-${field}`);
        if (errorEl) {
            errorEl.textContent = '';
            errorEl.classList.remove('visible');
        }

        // Remove invalid classes
        if (field === 'dispute_type') {
            document.querySelector('.dispute-type-options')?.classList.remove('invalid');
        } else if (field === 'evidence_files') {
            document.getElementById('disputeFileUploadArea')?.classList.remove('invalid');
        } else if (field === 'dispute_desc') {
            document.getElementById('reportDescription')?.classList.remove('invalid-input');
        } else if (field === 'if_others_distype') {
            document.getElementById('if_others_distype')?.classList.remove('invalid-input');
        }
    }

    clearErrors() {
        // Clear all text
        document.querySelectorAll('.send-report-modal .error-message').forEach(el => {
            el.textContent = '';
            el.classList.remove('visible');
        });

        // Remove all invalid classes
        document.querySelector('.dispute-type-options')?.classList.remove('invalid');
        document.getElementById('disputeFileUploadArea')?.classList.remove('invalid');
        document.getElementById('reportDescription')?.classList.remove('invalid-input');
        document.getElementById('if_others_distype')?.classList.remove('invalid-input');
    }

    async handleDisputeSubmit(e) {
        e.preventDefault();
        const form = e.target;
        const submitBtn = document.getElementById('submitDisputeBtn');
        const originalBtnText = submitBtn.innerHTML;

        const type = document.getElementById('reportType').value;
        const desc = document.getElementById('reportDescription').value;
        const othersSpec = document.getElementById('if_others_distype').value;

        let hasError = false;
        this.clearErrors();

        if (!type) {
            this.showError('dispute_type', 'Please select a dispute type.');
            hasError = true;
        }

        if (type === 'Others' && !othersSpec.trim()) {
            this.showError('if_others_distype', 'Please specify the other type of issue.');
            hasError = true;
        }

        if (!desc.trim()) {
            this.showError('dispute_desc', 'Please provide a description of the issue.');
            hasError = true;
        }

        // Enforce mandatory proof upload
        if (this.selectedFiles.length === 0) {
            this.showError('evidence_files', 'Please upload at least one image or document as proof.');
            hasError = true;
        }

        if (hasError) {
            // Smoothly scroll to the first error if needed
            const firstError = document.querySelector('.error-message.visible');
            if (firstError) {
                firstError.scrollIntoView({ behavior: 'smooth', block: 'center' });
            }
            return;
        }

        try {
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<i class="fi fi-rr-spinner fi-spin"></i> Submitting...';

            const formData = new FormData(form);
            const response = await fetch('/both/disputes/file', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '',
                    'Accept': 'application/json'
                },
                body: formData
            });

            const data = await response.json();

            if (response.ok && data.success) {
                this.closeDisputeModal();
                this.showNotification(data.message || 'Dispute filed successfully!', 'success');

                // Reload the page after a short delay to allow the toast to be seen
                setTimeout(() => {
                    window.location.reload();
                }, 1500);
            } else if (response.status === 422) {
                // Handle backend validation errors
                if (data.errors) {
                    Object.keys(data.errors).forEach(field => {
                        const message = Array.isArray(data.errors[field]) ? data.errors[field][0] : data.errors[field];
                        this.showError(field, message);
                    });
                } else {
                    this.showErrorModal(data.message || 'Validation failed.');
                }
            } else {
                this.showErrorModal(data.message || 'Failed to submit dispute.');
            }
        } catch (error) {
            console.error('Error submitting dispute:', error);
            this.showErrorModal('An unexpected error occurred. Please try again.');
        } finally {
            submitBtn.disabled = false;
            submitBtn.innerHTML = originalBtnText;
        }
    }

    async openHistoryModal(itemId) {
        this.currentItemId = itemId;

        // Reset filters when opening
        this.currentFilter = 'all';
        const filterPills = document.querySelectorAll('#reportHistoryModal .filter-pill');
        filterPills.forEach(p => {
            p.classList.remove('active');
            if (p.getAttribute('data-status') === 'all') p.classList.add('active');
        });

        if (this.historyModal) {
            this.historyModal.classList.remove('hidden');
            document.body.style.overflow = 'hidden';
        }

        await this.loadHistory(itemId);
    }

    closeHistoryModal() {
        if (this.historyModal) {
            this.historyModal.classList.add('hidden');
            document.body.style.overflow = '';
            this.allDisputes = [];
            this.currentFilter = 'all';
        }
    }

    async loadHistory(itemId) {
        const listContainer = document.getElementById('reportHistoryList');
        if (!listContainer) return;

        // Get pre-rendered HTML from the hidden template
        const templateDiv = document.getElementById('dispute_history_html_' + itemId);
        if (templateDiv) {
            this.allDisputesHtml = templateDiv.innerHTML;
            listContainer.innerHTML = this.allDisputesHtml;

            // Get summary counts from window.milestoneItemsData
            const itemsData = window.milestoneItemsData || {};
            const item = itemsData[itemId];
            if (item && item.disputeSummary) {
                this.updateSummary(item.disputeSummary);
            }

            this.filterHistory();
        } else {
            listContainer.innerHTML = `
                <div class="empty-history" style="text-align: center; padding: 3rem 1rem; color: #6b7280;">
                    <i class="fi fi-rr-folder-open" style="font-size: 3rem; margin-bottom: 1rem; display: block; opacity: 0.3;"></i>
                    <p>No dispute history found for this item.</p>
                </div>
            `;
            this.updateSummary({ total: 0, open: 0, resolved: 0 });
        }
    }

    filterHistory() {
        const listContainer = document.getElementById('reportHistoryList');
        if (!listContainer) return;

        const items = listContainer.querySelectorAll('.report-item');
        let visibleCount = 0;
        let title = 'All Reports';

        items.forEach(item => {
            const status = item.getAttribute('data-status');
            let isVisible = false;

            if (this.currentFilter === 'all') {
                isVisible = true;
            } else if (this.currentFilter === 'open') {
                isVisible = ['open', 'under_review'].includes(status);
            } else if (this.currentFilter === 'resolved') {
                isVisible = ['resolved', 'closed', 'cancelled'].includes(status);
            }

            item.style.display = isVisible ? 'block' : 'none';
            if (isVisible) visibleCount++;
        });

        // Update title based on filter
        if (this.currentFilter === 'open') title = 'Open Reports';
        else if (this.currentFilter === 'resolved') title = 'Resolved Reports';

        const titleEl = document.querySelector('#reportHistoryModal .section-title');
        if (titleEl) {
            titleEl.innerHTML = `${title} <span id="reportCount">(${visibleCount})</span>`;
        }

        // Handle empty filtered state
        let emptyEl = listContainer.querySelector('.empty-history-filtered');
        if (visibleCount === 0 && items.length > 0) {
            if (!emptyEl) {
                emptyEl = document.createElement('div');
                emptyEl.className = 'empty-history-filtered';
                emptyEl.style.cssText = 'text-align: center; padding: 3rem 1rem; color: #6b7280;';
                emptyEl.innerHTML = `
                    <i class="fi fi-rr-folder-open" style="font-size: 3rem; margin-bottom: 1rem; display: block; opacity: 0.3;"></i>
                    <p>No ${this.currentFilter === 'all' ? '' : this.currentFilter} reports found for this item.</p>
                `;
                listContainer.appendChild(emptyEl);
            } else {
                emptyEl.style.display = 'block';
                emptyEl.querySelector('p').textContent = `No ${this.currentFilter === 'all' ? '' : this.currentFilter} reports found for this item.`;
            }
        } else if (emptyEl) {
            emptyEl.style.display = 'none';
        }
    }

    updateSummary(summary) {
        const totalEl = document.getElementById('summaryTotal');
        const openEl = document.getElementById('summaryOpen');
        const resolvedEl = document.getElementById('summaryResolved');

        if (totalEl) totalEl.textContent = summary.total || 0;
        if (openEl) openEl.textContent = summary.open || 0;
        if (resolvedEl) resolvedEl.textContent = summary.resolved || 0;
    }

    showErrorModal(message) {
        const errorModal = document.getElementById('sendReportErrorModal');
        const errorMessage = document.getElementById('sendReportErrorMessage');
        if (errorMessage) errorMessage.textContent = message;
        if (errorModal) {
            errorModal.classList.remove('hidden');
            document.body.style.overflow = 'hidden';
        }
    }

    closeErrorModal() {
        const errorModal = document.getElementById('sendReportErrorModal');
        if (errorModal) {
            errorModal.classList.add('hidden');
            document.body.style.overflow = '';
        }
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

window.contractorDisputesInstance = null;
document.addEventListener('DOMContentLoaded', () => {
    window.contractorDisputesInstance = new ContractorDisputes();
});
