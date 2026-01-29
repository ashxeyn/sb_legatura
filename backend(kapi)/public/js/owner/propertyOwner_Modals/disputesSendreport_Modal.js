// Send Report Modal Functionality

document.addEventListener('DOMContentLoaded', () => {
    const sendReportModal = document.getElementById('sendReportModal');
    const sendReportForm = document.getElementById('sendReportForm');
    const sendReportCloseBtn = document.getElementById('sendReportCloseBtn');
    const sendReportCancelBtn = document.getElementById('sendReportCancelBtn');
    const modalOverlay = sendReportModal?.querySelector('.modal-overlay');
    const reportDescription = document.getElementById('reportDescription');
    const charCount = document.getElementById('charCount');
    const fileUploadArea = document.getElementById('fileUploadArea');
    const fileInput = document.getElementById('reportAttachments');
    const fileList = document.getElementById('fileList');
    const reportTypeInput = document.getElementById('reportType');
    const disputeOptions = document.querySelectorAll('.dispute-option');
    const successModal = document.getElementById('sendReportSuccessModal');
    const successOverlay = successModal?.querySelector('.modal-overlay');
    const successCloseBtn = document.getElementById('sendReportSuccessClose');
    const successDoneBtn = document.getElementById('sendReportSuccessDone');

    // Open Send Report Modal
    window.openSendReportModal = function() {
        if (sendReportModal) {
            sendReportModal.classList.remove('hidden');
            document.body.style.overflow = 'hidden';
        }
    };

    // Close Send Report Modal
    const closeSendReportModal = () => {
        if (sendReportModal) {
            sendReportModal.classList.add('hidden');
            document.body.style.overflow = '';
            resetForm();
        }
    };

    const openSuccessModal = () => {
        if (successModal) {
            successModal.classList.remove('hidden');
            document.body.style.overflow = 'hidden';
        }
    };

    const closeSuccessModal = () => {
        if (successModal) {
            successModal.classList.add('hidden');
            document.body.style.overflow = '';
        }
    };

    // Reset Form
    const resetForm = () => {
        if (sendReportForm) {
            sendReportForm.reset();
        }
        if (charCount) {
            charCount.textContent = '0';
        }
        if (fileList) {
            fileList.innerHTML = '';
        }
        // Reset dispute option selections
        disputeOptions.forEach(option => {
            option.classList.remove('selected');
        });
        if (reportTypeInput) {
            reportTypeInput.value = '';
        }
        // Hide specify dispute container
        const specifyDisputeContainer = document.getElementById('specifyDisputeContainer');
        const specifyDisputeInput = document.getElementById('specifyDisputeType');
        if (specifyDisputeContainer) {
            specifyDisputeContainer.classList.add('hidden');
        }
        if (specifyDisputeInput) {
            specifyDisputeInput.value = '';
        }
    };

    // Dispute Option Selection
    disputeOptions.forEach(option => {
        option.addEventListener('click', (e) => {
            e.preventDefault();
            
            // Remove selected class from all options
            disputeOptions.forEach(opt => {
                opt.classList.remove('selected');
            });
            
            // Add selected class to clicked option
            option.classList.add('selected');
            
            // Set the hidden input value
            const selectedValue = option.getAttribute('data-value');
            if (reportTypeInput) {
                reportTypeInput.value = selectedValue;
            }

            // Show/hide specify dispute type field based on selection
            const specifyDisputeContainer = document.getElementById('specifyDisputeContainer');
            const specifyDisputeInput = document.getElementById('specifyDisputeType');
            
            if (selectedValue === 'other-issue') {
                specifyDisputeContainer.classList.remove('hidden');
                if (specifyDisputeInput) {
                    specifyDisputeInput.required = true;
                    specifyDisputeInput.focus();
                }
            } else {
                specifyDisputeContainer.classList.add('hidden');
                if (specifyDisputeInput) {
                    specifyDisputeInput.required = false;
                    specifyDisputeInput.value = '';
                }
            }
        });
    });

    // Character Count for Textarea
    if (reportDescription) {
        reportDescription.addEventListener('input', () => {
            if (charCount) {
                charCount.textContent = reportDescription.value.length;
            }
        });
    }

    // Close Modal Events
    if (sendReportCloseBtn) {
        sendReportCloseBtn.addEventListener('click', closeSendReportModal);
    }

    if (sendReportCancelBtn) {
        sendReportCancelBtn.addEventListener('click', closeSendReportModal);
    }

    if (modalOverlay) {
        modalOverlay.addEventListener('click', closeSendReportModal);
    }

    // Close modal on ESC key
    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape' && sendReportModal && !sendReportModal.classList.contains('hidden')) {
            closeSendReportModal();
        }
        if (e.key === 'Escape' && successModal && !successModal.classList.contains('hidden')) {
            closeSuccessModal();
        }
    });

    if (successOverlay) {
        successOverlay.addEventListener('click', closeSuccessModal);
    }

    if (successCloseBtn) {
        successCloseBtn.addEventListener('click', closeSuccessModal);
    }

    if (successDoneBtn) {
        successDoneBtn.addEventListener('click', closeSuccessModal);
    }

    // File Upload Handling
    if (fileUploadArea && fileInput) {
        // Click upload button to open file dialog
        const uploadBtn = fileUploadArea.querySelector('.upload-files-btn');
        if (uploadBtn) {
            uploadBtn.addEventListener('click', (e) => {
                e.preventDefault();
                fileInput.click();
            });
        }

        // Click on upload area to open file dialog
        fileUploadArea.addEventListener('click', (e) => {
            if (e.target === fileUploadArea || e.target.closest('.upload-files-btn')) {
                fileInput.click();
            }
        });

        // File selection
        fileInput.addEventListener('change', handleFileSelection);

        // Drag and drop
        fileUploadArea.addEventListener('dragover', (e) => {
            e.preventDefault();
            fileUploadArea.classList.add('dragover');
        });

        fileUploadArea.addEventListener('dragleave', () => {
            fileUploadArea.classList.remove('dragover');
        });

        fileUploadArea.addEventListener('drop', (e) => {
            e.preventDefault();
            fileUploadArea.classList.remove('dragover');
            const files = e.dataTransfer.files;
            fileInput.files = files;
            handleFileSelection();
        });
    }

    // Handle File Selection
    function handleFileSelection() {
        if (fileList) {
            fileList.innerHTML = '';
        }

        if (fileInput && fileInput.files.length > 0) {
            Array.from(fileInput.files).forEach((file, index) => {
                const fileItem = document.createElement('div');
                fileItem.className = 'file-item';
                fileItem.innerHTML = `
                    <div class="file-item-info">
                        <i class="fi fi-rr-file"></i>
                        <span>${file.name} (${formatFileSize(file.size)})</span>
                    </div>
                    <button type="button" class="file-remove-btn" data-index="${index}">
                        <i class="fi fi-rr-trash"></i>
                    </button>
                `;

                if (fileList) {
                    fileList.appendChild(fileItem);
                }

                fileItem.querySelector('.file-remove-btn').addEventListener('click', (e) => {
                    e.preventDefault();
                    removeFile(index);
                });
            });
        }
    }

    // Remove File
    function removeFile(index) {
        const dt = new DataTransfer();
        const files = fileInput.files;

        for (let i = 0; i < files.length; i++) {
            if (i !== index) {
                dt.items.add(files[i]);
            }
        }

        fileInput.files = dt.files;
        handleFileSelection();
    }

    // Format File Size
    function formatFileSize(bytes) {
        if (bytes === 0) return '0 Bytes';
        const k = 1024;
        const sizes = ['Bytes', 'KB', 'MB'];
        const i = Math.floor(Math.log(bytes) / Math.log(k));
        return Math.round((bytes / Math.pow(k, i)) * 100) / 100 + ' ' + sizes[i];
    }

    // Form Submission
    if (sendReportForm) {
        sendReportForm.addEventListener('submit', (e) => {
            e.preventDefault();

            // Validate that a dispute type is selected
            if (!reportTypeInput.value) {
                alert('Please select a dispute type');
                return;
            }

            // Validate specify dispute type if "Other Issue" is selected
            const specifyDisputeInput = document.getElementById('specifyDisputeType');
            if (reportTypeInput.value === 'other-issue' && (!specifyDisputeInput.value || specifyDisputeInput.value.trim() === '')) {
                alert('Please specify the dispute type');
                specifyDisputeInput.focus();
                return;
            }

            const formData = {
                reportType: reportTypeInput.value,
                specifyDisputeType: specifyDisputeInput?.value || '',
                reportDescription: reportDescription.value,
                projectName: document.getElementById('reportProjectName').textContent,
                milestoneName: document.getElementById('reportMilestoneName').textContent,
            };

            console.log('Form Data:', formData);
            console.log('Files:', fileInput?.files);

            // TODO: Implement backend API call
            closeSendReportModal();
            openSuccessModal();
        });
    }
});
