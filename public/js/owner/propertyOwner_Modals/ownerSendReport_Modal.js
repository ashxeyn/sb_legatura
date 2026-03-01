/**
 * Send Report (File Dispute) Modal
 * Matches the mobile milestoneApproval.tsx dispute-filing flow.
 * ─────────────────────────────────────────────────────────────
 *  Form → type selector · milestone picker · description · evidence upload
 *  Success view after submission
 */

class OwnerSendReportModal {
    constructor() {
        this.modal = null;
        this.overlay = null;
        this.projectData = null;
        this.milestones = [];
        this.selectedType = null;
        this.selectedFiles = [];
        this.isSubmitting = false;
        this.init();
    }

    init() {
        this.modal = document.getElementById('sendReportModal');
        this.overlay = document.getElementById('sendReportModalOverlay');
        if (!this.modal || !this.overlay) return;
        this.setupEventListeners();
    }

    setupEventListeners() {
        const closeBtn = document.getElementById('closeSendReportModalBtn');
        if (closeBtn) closeBtn.addEventListener('click', () => this.close());

        const backBtn = document.getElementById('srBackBtn');
        if (backBtn) backBtn.addEventListener('click', () => this.close());

        if (this.overlay) this.overlay.addEventListener('click', () => this.close());

        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape' && this.isOpen()) this.close();
        });

        // Type grid buttons
        const typeGrid = document.getElementById('srTypeGrid');
        if (typeGrid) {
            typeGrid.querySelectorAll('.sr-type-option').forEach(btn => {
                btn.addEventListener('click', (e) => {
                    e.preventDefault();
                    this.selectType(btn.dataset.value);
                });
            });
        }

        // Description char count
        const desc = document.getElementById('srDescription');
        if (desc) {
            desc.addEventListener('input', () => {
                const count = document.getElementById('srCharCount');
                if (count) count.textContent = desc.value.length;
            });
        }

        // File upload
        const fileUpload = document.getElementById('srFileUpload');
        const fileInput = document.getElementById('srFileInput');
        if (fileUpload && fileInput) {
            fileUpload.addEventListener('click', () => fileInput.click());
            fileUpload.addEventListener('dragover', (e) => { e.preventDefault(); fileUpload.classList.add('sr-file-dragover'); });
            fileUpload.addEventListener('dragleave', () => fileUpload.classList.remove('sr-file-dragover'));
            fileUpload.addEventListener('drop', (e) => {
                e.preventDefault();
                fileUpload.classList.remove('sr-file-dragover');
                this.addFiles(e.dataTransfer.files);
            });
            fileInput.addEventListener('change', () => {
                this.addFiles(fileInput.files);
                fileInput.value = '';
            });
        }

        // Form submit
        const form = document.getElementById('sendReportForm');
        if (form) form.addEventListener('submit', (e) => { e.preventDefault(); this.handleSubmit(); });

        // Cancel
        const cancelBtn = document.getElementById('srCancelBtn');
        if (cancelBtn) cancelBtn.addEventListener('click', () => this.close());

        // Success close
        const successBtn = document.getElementById('srSuccessCloseBtn');
        if (successBtn) successBtn.addEventListener('click', () => this.close());
    }

    // ── Open / Close ────────────────────────────────────────────────────
    open(projectData, milestones) {
        if (!projectData) return;
        this.projectData = projectData;
        this.milestones = milestones || [];
        this.selectedType = null;
        this.selectedFiles = [];
        this.isSubmitting = false;

        this.resetForm();
        this.populateContextCard();
        this.populateMilestones();
        this.showFormView();

        requestAnimationFrame(() => {
            this.modal.classList.add('active');
            document.body.style.overflow = 'hidden';
        });
    }

    close() {
        if (!this.modal) return;
        this.modal.classList.remove('active');
        document.body.style.overflow = '';
        this.projectData = null;
        this.selectedType = null;
        this.selectedFiles = [];
    }

    isOpen() { return this.modal && this.modal.classList.contains('active'); }

    showFormView() {
        const form = document.getElementById('srFormView');
        const success = document.getElementById('srSuccessView');
        if (form) form.style.display = '';
        if (success) success.style.display = 'none';
        const title = this.modal.querySelector('.sr-header-title');
        if (title) title.textContent = 'File a Report';
    }

    showSuccessView() {
        const form = document.getElementById('srFormView');
        const success = document.getElementById('srSuccessView');
        if (form) form.style.display = 'none';
        if (success) success.style.display = '';
    }

    // ── Form Logic ──────────────────────────────────────────────────────
    resetForm() {
        const form = document.getElementById('sendReportForm');
        if (form) form.reset();

        // Reset type selection
        this.modal.querySelectorAll('.sr-type-option').forEach(b => b.classList.remove('active'));
        document.getElementById('srDisputeType').value = '';
        document.getElementById('srOthersGroup').style.display = 'none';
        document.getElementById('srCharCount').textContent = '0';
        document.getElementById('srFileList').innerHTML = '';

        const submitBtn = document.getElementById('srSubmitBtn');
        if (submitBtn) {
            submitBtn.disabled = false;
            submitBtn.innerHTML = '<i class="fi fi-rr-paper-plane"></i> Submit Report';
        }
    }

    selectType(type) {
        this.selectedType = type;
        document.getElementById('srDisputeType').value = type;

        this.modal.querySelectorAll('.sr-type-option').forEach(b => {
            b.classList.toggle('active', b.dataset.value === type);
        });

        const othersGroup = document.getElementById('srOthersGroup');
        if (othersGroup) othersGroup.style.display = type === 'Others' ? '' : 'none';
    }

    populateContextCard() {
        const card = document.getElementById('srContextCard');
        if (!card || !this.projectData) return;

        const projectEl = document.getElementById('srContextProject');
        const itemRow = document.getElementById('srContextItemRow');
        const itemEl = document.getElementById('srContextItem');

        if (projectEl && this.projectData.projectTitle) {
            projectEl.textContent = this.projectData.projectTitle;
            card.style.display = '';
        }

        if (itemRow && itemEl && this.projectData.currentItemTitle) {
            itemEl.textContent = this.projectData.currentItemTitle;
            itemRow.style.display = '';
        } else if (itemRow) {
            itemRow.style.display = 'none';
        }
    }

    populateMilestones() {
        const select = document.getElementById('srMilestoneSelect');
        if (!select) return;

        select.innerHTML = '<option value="">Select a milestone item</option>';
        this.milestones.forEach(item => {
            const opt = document.createElement('option');
            opt.value = item.item_id;
            opt.dataset.milestoneId = item.parentMilestoneId || item.milestone_id || '';
            opt.textContent = `${item.milestone_item_title || 'Item ' + item.sequence_order}`;
            select.appendChild(opt);
        });

        // Pre-select current item if provided
        if (this.projectData && this.projectData.currentItemId) {
            select.value = String(this.projectData.currentItemId);
        }
    }

    addFiles(fileList) {
        const maxFiles = 10;
        const maxSize = 5 * 1024 * 1024; // 5MB
        const allowed = ['image/jpeg', 'image/png', 'application/pdf',
                         'application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document'];

        for (const file of fileList) {
            if (this.selectedFiles.length >= maxFiles) {
                this.showToast('Maximum 10 files allowed', 'error');
                break;
            }
            if (!allowed.includes(file.type)) {
                this.showToast(`${file.name}: Invalid file type`, 'error');
                continue;
            }
            if (file.size > maxSize) {
                this.showToast(`${file.name}: File too large (max 5MB)`, 'error');
                continue;
            }
            this.selectedFiles.push(file);
        }
        this.renderFileList();
    }

    removeFile(index) {
        this.selectedFiles.splice(index, 1);
        this.renderFileList();
    }

    renderFileList() {
        const list = document.getElementById('srFileList');
        if (!list) return;

        if (this.selectedFiles.length === 0) {
            list.innerHTML = '';
            return;
        }

        list.innerHTML = this.selectedFiles.map((file, i) => {
            const isImage = file.type.startsWith('image/');
            const sizeKB = (file.size / 1024).toFixed(1);
            return `
                <div class="sr-file-item">
                    <div class="sr-file-icon${isImage ? ' sr-file-icon-image' : ''}">
                        <i class="fi ${isImage ? 'fi-rr-picture' : 'fi-rr-document'}"></i>
                    </div>
                    <div class="sr-file-info">
                        <span class="sr-file-name">${this.esc(file.name)}</span>
                        <span class="sr-file-size">${sizeKB} KB</span>
                    </div>
                    <button type="button" class="sr-file-remove" data-index="${i}" title="Remove">
                        <i class="fi fi-rr-cross-small"></i>
                    </button>
                </div>`;
        }).join('');

        list.querySelectorAll('.sr-file-remove').forEach(btn => {
            btn.addEventListener('click', (e) => {
                e.preventDefault();
                this.removeFile(parseInt(btn.dataset.index));
            });
        });
    }

    // ── Submit ──────────────────────────────────────────────────────────
    async handleSubmit() {
        if (this.isSubmitting) return;

        // Validate
        if (!this.selectedType) {
            this.showToast('Please select a report type', 'error');
            return;
        }

        if (this.selectedType === 'Others') {
            const others = document.getElementById('srOthersInput').value.trim();
            if (!others) {
                this.showToast('Please specify the report type', 'error');
                return;
            }
        }

        const milestoneSelect = document.getElementById('srMilestoneSelect');
        const selectedOption = milestoneSelect?.selectedOptions[0];
        const milestoneItemId = milestoneSelect?.value;
        const milestoneId = selectedOption?.dataset?.milestoneId;

        if (!milestoneItemId) {
            this.showToast('Please select a milestone item', 'error');
            return;
        }

        const description = document.getElementById('srDescription').value.trim();
        if (!description) {
            this.showToast('Please provide a description', 'error');
            return;
        }

        if (description.length > 2000) {
            this.showToast('Description cannot exceed 2000 characters', 'error');
            return;
        }

        this.isSubmitting = true;
        const submitBtn = document.getElementById('srSubmitBtn');
        submitBtn.disabled = true;
        submitBtn.innerHTML = '<i class="fi fi-rr-spinner sr-spin"></i> Submitting...';

        try {
            const formData = new FormData();
            formData.append('project_id', this.projectData.projectId);
            formData.append('milestone_id', milestoneId);
            formData.append('milestone_item_id', milestoneItemId);
            formData.append('dispute_type', this.selectedType);
            formData.append('dispute_desc', description);

            if (this.selectedType === 'Others') {
                formData.append('if_others_distype', document.getElementById('srOthersInput').value.trim());
            }

            this.selectedFiles.forEach(file => {
                formData.append('evidence_files[]', file);
            });

            const config = window.__milestoneReportConfig || {};
            const csrfToken = config.csrfToken || document.querySelector('meta[name="csrf-token"]')?.content || '';

            const res = await fetch('/both/disputes/file', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json',
                },
                body: formData,
            });

            const json = await res.json();

            if (json.success) {
                this.showSuccessView();
                // Dispatch event so report history can refresh
                window.dispatchEvent(new CustomEvent('disputeFiled'));
            } else {
                const errMsg = json.message || json.errors
                    ? Object.values(json.errors || {}).flat().join(', ') || json.message
                    : 'Failed to submit report';
                this.showToast(errMsg, 'error');
            }
        } catch (err) {
            console.error('[SendReport] Submit error:', err);
            this.showToast('An unexpected error occurred. Please try again.', 'error');
        } finally {
            this.isSubmitting = false;
            if (submitBtn) {
                submitBtn.disabled = false;
                submitBtn.innerHTML = '<i class="fi fi-rr-paper-plane"></i> Submit Report';
            }
        }
    }

    // ── Helpers ──────────────────────────────────────────────────────────
    esc(str) {
        if (str == null) return '';
        const d = document.createElement('div');
        d.textContent = String(str);
        return d.innerHTML;
    }

    showToast(message, type = 'info') {
        const toast = document.createElement('div');
        toast.className = 'sr-toast';
        const bg = type === 'success' ? '#10B981' : type === 'error' ? '#EF4444' : '#EC7E00';
        toast.style.background = bg;
        toast.textContent = message;
        document.body.appendChild(toast);
        setTimeout(() => {
            toast.style.opacity = '0';
            toast.style.transform = 'translateY(10px)';
            setTimeout(() => toast.remove(), 300);
        }, 3000);
    }
}

// ── Global Interface ────────────────────────────────────────────────────────
let sendReportModalInstance = null;

function initializeSendReportModal() {
    if (!sendReportModalInstance) {
        sendReportModalInstance = new OwnerSendReportModal();
        window.openSendReportModal = (projectData, milestones) => {
            if (sendReportModalInstance) sendReportModalInstance.open(projectData, milestones);
        };
    }
}

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initializeSendReportModal);
} else {
    initializeSendReportModal();
}
