// Bid Modal Control Functions (PHP-populated modals)

// Global function to open a specific bid modal
window.openBidModal = function (projectId) {
    const modalId = `applyBidModal-${projectId}`;
    const modal = document.getElementById(modalId);

    if (modal) {
        modal.classList.add('active');
        document.body.style.overflow = 'hidden';

        // Focus on first input
        const firstInput = modal.querySelector('input[type="text"]');
        if (firstInput) {
            setTimeout(() => firstInput.focus(), 100);
        }
    } else {
        console.error(`Bid modal not found for project ${projectId}`);
    }
};

// Global function to close a specific bid modal
window.closeBidModal = function (modalId) {
    const modal = document.getElementById(modalId);
    if (modal) {
        modal.classList.remove('active');
        document.body.style.overflow = '';

        // Reset form
        const form = modal.querySelector('form');
        if (form) {
            form.reset();
            // Reset character count
            const charCount = modal.querySelector('[id^="messageCharCount"]');
            if (charCount) {
                charCount.textContent = '0';
            }
            // Clear file previews
            const filePreview = modal.querySelector('[id^="filePreviewContainer"]');
            if (filePreview) {
                filePreview.innerHTML = '';
            }
        }
    }
};

// Budget Warning Modal Functions
window.showBudgetWarningModal = function (type, message, projectId) {
    const modal = document.getElementById(`budgetWarningModal-${projectId}`);
    const iconContainer = document.getElementById(`budgetWarningIcon-${projectId}`);
    const iconSymbol = document.getElementById(`budgetWarningIconSymbol-${projectId}`);
    const title = document.getElementById(`budgetWarningTitle-${projectId}`);
    const messageEl = document.getElementById(`budgetWarningMessage-${projectId}`);

    if (modal && iconContainer && iconSymbol && title && messageEl) {
        // Update icon and styling based on type
        if (type === 'high') {
            iconContainer.className = 'modal-icon-container warning';
            iconSymbol.className = 'fi fi-rr-trending-up';
            title.textContent = 'Bid Above Budget Range';
        } else {
            iconContainer.className = 'modal-icon-container info';
            iconSymbol.className = 'fi fi-rr-trending-down';
            title.textContent = 'Bid Below Budget Range';
        }

        messageEl.textContent = message;
        modal.classList.remove('hidden');
        document.body.style.overflow = 'hidden';
    }
};

window.closeBudgetWarningModal = function (projectId) {
    const modal = document.getElementById(`budgetWarningModal-${projectId}`);
    if (modal) {
        modal.classList.add('hidden');
        document.body.style.overflow = '';
    }
};

window.handleEditBudget = function (projectId) {
    window[`editBidAmount_${projectId}`]();
};

window.handleContinueBudget = function (projectId) {
    window[`continueBidSubmission_${projectId}`]();
};

// Close on Escape key
document.addEventListener('keydown', function (e) {
    if (e.key === 'Escape') {
        const openModal = document.querySelector('.apply-bid-modal.active');
        if (openModal) {
            const modalId = openModal.id;
            window.closeBidModal(modalId);
        }
    }
});

// Initialize all modals on page load
document.addEventListener('DOMContentLoaded', function () {
    // Character count for textareas
    const textareas = document.querySelectorAll('[id^="modalCompellingMessage"]');
    textareas.forEach(textarea => {
        const projectId = textarea.id.replace('modalCompellingMessage-', '');
        const charCount = document.getElementById(`messageCharCount-${projectId}`);

        if (charCount) {
            textarea.addEventListener('input', function () {
                charCount.textContent = this.value.length;
            });
        }
    });

    // Format proposed cost inputs with commas and prevent non-numeric input
    const proposedCostInputs = document.querySelectorAll('[id^="modalProposedCost"]');
    proposedCostInputs.forEach(input => {
        // Helper to format number with commas
        const formatNumberWithCommas = (value) => {
            if (!value) return '';
            const parts = value.toString().split('.');
            parts[0] = parts[0].replace(/\B(?=(\d{3})+(?!\d))/g, ',');
            return parts.join('.');
        };

        input.addEventListener('keydown', function (e) {
            // Allow: backspace, delete, tab, escape, enter, decimal point (110, 190), comma (188)
            if ([8, 9, 27, 13, 46, 110, 190, 188].indexOf(e.keyCode) !== -1 ||
                // Allow: Ctrl+A, Ctrl+C, Ctrl+V, Ctrl+X
                (e.keyCode === 65 && e.ctrlKey === true) ||
                (e.keyCode === 67 && e.ctrlKey === true) ||
                (e.keyCode === 86 && e.ctrlKey === true) ||
                (e.keyCode === 88 && e.ctrlKey === true) ||
                // Allow: home, end, left, right
                (e.keyCode >= 35 && e.keyCode <= 39)) {
                return;
            }
            // Prevent: e, E, +, - and any non-numeric characters
            if ((e.shiftKey || (e.keyCode < 48 || e.keyCode > 57)) && (e.keyCode < 96 || e.keyCode > 105)) {
                e.preventDefault();
            }
        });

        // Format dynamically on input
        input.addEventListener('input', function (e) {
            // Save cursor position
            const start = this.selectionStart;
            const oldVal = this.value;

            // Get raw value (remove existing commas)
            let rawValue = this.value.replace(/,/g, '');

            // If it's just a dot, leave it (e.g. user starting to type .50)
            if (rawValue === '.') return;

            // Check for multiple decimals
            if ((rawValue.match(/\./g) || []).length > 1) {
                rawValue = rawValue.substring(0, rawValue.lastIndexOf('.'));
            }

            // Format
            const formatted = formatNumberWithCommas(rawValue);

            // Only update if changed
            if (this.value !== formatted) {
                this.value = formatted;

                // Restore cursor (approximate logic handles most cases)
                // If we added a comma before cursor, increment. If removed, decrement.
                // Simple version: put cursor at end or try to calc offset.
                // Robust version: count commas before cursor in old vs new.

                let oldCommas = (oldVal.substring(0, start).match(/,/g) || []).length;
                let newCommas = (formatted.substring(0, start).match(/,/g) || []).length;

                // Adjust for added comma by re-calculating position based on raw content length? 
                // Actually, just tracking standard drift is often enough.
                // Let's rely on basic behavior or just set it to end if difficult? 
                // No, users hate jumped cursors.
                // Better approach: Calculate new position based on non-comma characters count.

                let nonCommaIndices = 0;
                for (let i = 0; i < start; i++) {
                    if (oldVal[i] !== ',') nonCommaIndices++;
                }

                let newCursor = 0;
                let finalNonCommas = 0;
                while (newCursor < formatted.length && finalNonCommas < nonCommaIndices) {
                    if (formatted[newCursor] !== ',') finalNonCommas++;
                    newCursor++;
                }
                // If next char is comma, jump it?
                // This logic is safer.
                this.setSelectionRange(newCursor, newCursor);
            }
        });

        // Format with commas and .00 on blur
        input.addEventListener('blur', function (e) {
            let value = this.value.replace(/,/g, '');

            if (value && !isNaN(value)) {
                const numValue = parseFloat(value);
                this.value = numValue.toLocaleString('en-US', {
                    minimumFractionDigits: 2,
                    maximumFractionDigits: 2
                });
            }
        });
    });

    // Form submission handlers
    const bidForms = document.querySelectorAll('[id^="applyBidForm"]');
    bidForms.forEach(form => {
        // Store project budget data
        const projectId = form.id.replace('applyBidForm-', '');
        const proposedCostInput = document.getElementById(`modalProposedCost-${projectId}`);

        // Get budget range from data attributes (will be set when modal opens)
        let budgetMin = null;
        let budgetMax = null;
        let pendingFormData = null;

        form.addEventListener('submit', async function (e) {
            e.preventDefault();

            const submitBtn = document.getElementById(`submitApplyBidBtn-${projectId}`);
            const successAlert = document.getElementById(`applyBidFormSuccess-${projectId}`);
            const errorAlert = document.getElementById(`applyBidFormError-${projectId}`);

            // Clear all previous error messages
            clearFieldErrors(projectId);

            // Validate all fields
            let hasErrors = false;

            // Validate proposed cost
            const proposedCostValue = proposedCostInput.value.replace(/,/g, '');
            const proposedCost = parseFloat(proposedCostValue);

            if (!proposedCostValue || proposedCostValue.trim() === '') {
                showFieldError(projectId, 'proposed_cost', 'Proposed cost is required.');
                hasErrors = true;
            } else if (isNaN(proposedCost) || proposedCost <= 0) {
                showFieldError(projectId, 'proposed_cost', 'Please enter a valid proposed cost.');
                hasErrors = true;
            }

            // Validate estimated timeline
            const timelineInput = document.getElementById(`modalEstimatedTimeline-${projectId}`);
            const timelineValue = timelineInput.value;
            const timeline = parseInt(timelineValue);

            if (!timelineValue || timelineValue.trim() === '') {
                showFieldError(projectId, 'estimated_timeline', 'Estimated timeline is required.');
                hasErrors = true;
            } else if (isNaN(timeline) || timeline < 1) {
                showFieldError(projectId, 'estimated_timeline', 'Timeline must be at least 1 month.');
                hasErrors = true;
            }

            // Validate contractor notes
            const notesInput = document.getElementById(`modalCompellingMessage-${projectId}`);
            const notesValue = notesInput.value;

            if (!notesValue || notesValue.trim() === '') {
                showFieldError(projectId, 'contractor_notes', 'Please provide a compelling message.');
                hasErrors = true;
            } else if (notesValue.trim().length < 20) {
                showFieldError(projectId, 'contractor_notes', 'Message must be at least 20 characters.');
                hasErrors = true;
            }

            // If there are validation errors, stop submission
            if (hasErrors) {
                return;
            }

            // Get budget range from the form's dataset
            budgetMin = parseFloat(form.dataset.budgetMin) || null;
            budgetMax = parseFloat(form.dataset.budgetMax) || null;

            // Check budget range
            const budgetCheck = checkBudgetRange(proposedCost, budgetMin, budgetMax);

            if (budgetCheck.outOfRange) {
                // Store form data for later submission
                pendingFormData = new FormData(this);
                // Remove commas from proposed_cost
                if (pendingFormData.has('proposed_cost')) {
                    pendingFormData.set('proposed_cost', pendingFormData.get('proposed_cost').replace(/,/g, ''));
                }

                // Show budget warning modal
                showBudgetWarningModal(budgetCheck.type, budgetCheck.message, projectId);
                return;
            }

            // Proceed with submission if no budget warning
            await submitBidForm(this, projectId, submitBtn, successAlert, errorAlert);
        });

        // Store submit function for budget warning continuation
        window[`continueBidSubmission_${projectId}`] = async function () {
            // Close budget warning modal
            closeBudgetWarningModal(projectId);

            const submitBtn = document.getElementById(`submitApplyBidBtn-${projectId}`);
            const successAlert = document.getElementById(`applyBidFormSuccess-${projectId}`);
            const errorAlert = document.getElementById(`applyBidFormError-${projectId}`);

            if (pendingFormData) {
                await submitBidFormWithData(form.action, pendingFormData, projectId, submitBtn, successAlert, errorAlert);
                pendingFormData = null;
            }
        };

        window[`editBidAmount_${projectId}`] = function () {
            closeBudgetWarningModal();
            // Focus back on the proposed cost input
            if (proposedCostInput) {
                proposedCostInput.focus();
            }
        };
    });

    // Budget range check function
    function checkBudgetRange(cost, minBudget, maxBudget) {
        if (minBudget || maxBudget) {
            if (maxBudget && cost > maxBudget) {
                return {
                    outOfRange: true,
                    type: 'high',
                    message: `Your bid of ₱${formatNumber(cost)} is higher than the maximum budget of ₱${formatNumber(maxBudget)}. The property owner may prefer lower bids.`
                };
            } else if (minBudget && cost < minBudget) {
                return {
                    outOfRange: true,
                    type: 'low',
                    message: `Your bid of ₱${formatNumber(cost)} is lower than the minimum budget of ₱${formatNumber(minBudget)}. This may raise concerns about quality or scope.`
                };
            }
        }
        return { outOfRange: false };
    }

    // Show field error message
    function showFieldError(projectId, fieldName, message) {
        const errorDiv = document.getElementById(`error_${fieldName}-${projectId}`);
        if (errorDiv) {
            errorDiv.textContent = message;
            errorDiv.classList.remove('hidden');
        }
    }

    // Clear all field errors
    function clearFieldErrors(projectId) {
        const errorDivs = [
            `error_proposed_cost-${projectId}`,
            `error_estimated_timeline-${projectId}`,
            `error_contractor_notes-${projectId}`
        ];

        errorDivs.forEach(id => {
            const errorDiv = document.getElementById(id);
            if (errorDiv) {
                errorDiv.textContent = '';
                errorDiv.classList.add('hidden');
            }
        });
    }

    // Format number with commas
    function formatNumber(num) {
        return new Intl.NumberFormat('en-PH').format(num);
    }

    // Submit bid form function
    async function submitBidForm(form, projectId, submitBtn, successAlert, errorAlert) {
        const formData = new FormData(form);
        // Remove commas from proposed_cost
        if (formData.has('proposed_cost')) {
            formData.set('proposed_cost', formData.get('proposed_cost').replace(/,/g, ''));
        }
        await submitBidFormWithData(form.action, formData, projectId, submitBtn, successAlert, errorAlert);
    }

    // Submit bid form with data
    // Submit bid form with data
    async function submitBidFormWithData(action, formData, projectId, submitBtn, successAlert, errorAlert) {
        // Disable submit button
        if (submitBtn) {
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<span>Submitting...</span>';
        }

        // Hide previous alerts (if any still exist/used)
        if (successAlert) successAlert.classList.add('hidden');
        if (errorAlert) errorAlert.classList.add('hidden');

        try {
            const response = await fetch(action, {
                method: 'POST',
                body: formData,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            });

            const data = await response.json();

            if (response.ok && data.success) {
                // Determine which modal to close
                // If it was the apply bid modal
                window.closeBidModal(`applyBidModal-${projectId}`);
                // If it was via budget warning continue, that modal is already closed by handleContinueBudget

                // Show success toast
                showToast(data.message || 'Bid submitted successfully!', 'success');

                // Remove project card from UI without reload
                const bidButton = document.querySelector(`.apply-bid-button[data-project-id="${projectId}"]`);
                if (bidButton) {
                    const card = bidButton.closest('.project-card');
                    if (card) {
                        // Animate removal
                        card.style.transition = 'all 0.5s ease';
                        card.style.opacity = '0';
                        card.style.transform = 'scale(0.9)';

                        setTimeout(() => {
                            card.remove();

                            // Check if any projects remain
                            const grid = document.getElementById('projectsGrid');
                            // We need to check direct children or query class
                            const remainingCards = grid ? grid.querySelectorAll('.project-card').length : 0;

                            if (remainingCards === 0) {
                                const emptyState = document.getElementById('emptyState');
                                if (emptyState) emptyState.classList.remove('hidden');
                            }
                        }, 500);
                    }
                }
            } else {
                // Show error toast instead of alert? Or keep alert for error?
                // User asked for "success message should be toast".
                // Usually errors are better near the form.
                // But let's use toast for consistency if it's a general error.
                // However, validation errors should probably stay in the form.
                // The current code puts errors in errorAlert.
                // I will keep errorAlert for errors unless they are generic.

                if (errorAlert) {
                    errorAlert.textContent = data.message || 'Failed to submit bid. Please try again.';
                    errorAlert.classList.remove('hidden');
                } else {
                    showToast(data.message || 'Failed to submit bid.', 'error');
                }
            }
        } catch (error) {
            console.error('Error submitting bid:', error);
            if (errorAlert) {
                errorAlert.textContent = 'An error occurred. Please try again.';
                errorAlert.classList.remove('hidden');
            } else {
                showToast('An error occurred. Please try again.', 'error');
            }
        } finally {
            // Re-enable submit button
            if (submitBtn) {
                submitBtn.disabled = false;
                submitBtn.innerHTML = '<span>Submit</span>';
            }
        }
    }

    // File upload functionality for each modal
    const fileUploadAreas = document.querySelectorAll('[id^="fileUploadArea"]');
    fileUploadAreas.forEach(uploadArea => {
        const projectId = uploadArea.id.replace('fileUploadArea-', '');
        const fileInput = document.getElementById(`modalSupportingDocuments-${projectId}`);
        const filePreviewContainer = document.getElementById(`filePreviewContainer-${projectId}`);

        if (!fileInput || !filePreviewContainer) return;

        // Store selected files
        let selectedFiles = [];

        // Click to upload
        uploadArea.addEventListener('click', () => {
            fileInput.click();
        });

        // Drag and drop
        uploadArea.addEventListener('dragover', (e) => {
            e.preventDefault();
            uploadArea.classList.add('drag-over');
        });

        uploadArea.addEventListener('dragleave', () => {
            uploadArea.classList.remove('drag-over');
        });

        uploadArea.addEventListener('drop', (e) => {
            e.preventDefault();
            uploadArea.classList.remove('drag-over');

            const files = Array.from(e.dataTransfer.files);
            handleFiles(files, fileInput, filePreviewContainer, selectedFiles);
        });

        // File input change
        fileInput.addEventListener('change', (e) => {
            const files = Array.from(e.target.files);
            handleFiles(files, fileInput, filePreviewContainer, selectedFiles);
        });

        // Handle files function
        function handleFiles(files, input, container, filesArray) {
            // Validate file types
            const allowedTypes = ['application/pdf', 'application/msword',
                'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
                'image/jpeg', 'image/jpg', 'image/png'];

            const validFiles = files.filter(file => {
                if (!allowedTypes.includes(file.type)) {
                    alert(`File type not allowed: ${file.name}`);
                    return false;
                }
                if (file.size > 10 * 1024 * 1024) { // 10MB
                    alert(`File too large: ${file.name} (max 10MB)`);
                    return false;
                }
                return true;
            });

            // Add to selected files (max 5)
            validFiles.forEach(file => {
                if (filesArray.length < 5) {
                    filesArray.push(file);
                }
            });

            if (filesArray.length > 5) {
                filesArray = filesArray.slice(0, 5);
                alert('Maximum 5 files allowed');
            }

            // Update file input
            updateFileInput(input, filesArray);

            // Update preview
            updateFilePreview(container, filesArray, input);
        }

        function updateFileInput(input, filesArray) {
            const dataTransfer = new DataTransfer();
            filesArray.forEach(file => dataTransfer.items.add(file));
            input.files = dataTransfer.files;
        }

        function updateFilePreview(container, filesArray, input) {
            container.innerHTML = '';

            filesArray.forEach((file, index) => {
                const fileItem = document.createElement('div');
                fileItem.className = 'file-preview-item';

                const fileIcon = getFileIcon(file.type);
                const fileName = file.name.length > 20 ? file.name.substring(0, 17) + '...' : file.name;
                const fileSize = formatFileSize(file.size);

                fileItem.innerHTML = `
                    <div class="file-preview-icon">${fileIcon}</div>
                    <div class="file-preview-info">
                        <span class="file-preview-name">${fileName}</span>
                        <span class="file-preview-size">${fileSize}</span>
                    </div>
                    <button type="button" class="file-preview-remove" data-index="${index}">
                        <i class="fi fi-rr-cross"></i>
                    </button>
                `;

                // Remove file handler
                const removeBtn = fileItem.querySelector('.file-preview-remove');
                removeBtn.addEventListener('click', () => {
                    filesArray.splice(index, 1);
                    updateFileInput(input, filesArray);
                    updateFilePreview(container, filesArray, input);
                });

                container.appendChild(fileItem);
            });
        }

        function getFileIcon(fileType) {
            if (fileType.includes('pdf')) return '<i class="fi fi-rr-file-pdf"></i>';
            if (fileType.includes('word') || fileType.includes('document')) return '<i class="fi fi-rr-file-word"></i>';
            if (fileType.includes('image')) return '<i class="fi fi-rr-file-image"></i>';
            return '<i class="fi fi-rr-file"></i>';
        }

        function formatFileSize(bytes) {
            if (bytes === 0) return '0 Bytes';
            const k = 1024;
            const sizes = ['Bytes', 'KB', 'MB'];
            const i = Math.floor(Math.log(bytes) / Math.log(k));
            return Math.round(bytes / Math.pow(k, i) * 100) / 100 + ' ' + sizes[i];
        }
    });

    // Make functions globally available
    window.showBudgetWarningModal = function (type, message, projectId) {
        const modal = document.getElementById(`budgetWarningModal-${projectId}`);
        const title = document.getElementById(`budgetWarningTitle-${projectId}`);
        const msg = document.getElementById(`budgetWarningMessage-${projectId}`);
        const iconContainer = document.getElementById(`budgetWarningIcon-${projectId}`);
        const iconSymbol = document.getElementById(`budgetWarningIconSymbol-${projectId}`);

        if (modal && title && msg && iconContainer && iconSymbol) {
            msg.textContent = message;

            // Update styling based on type
            iconContainer.className = 'modal-icon-container'; // reset
            iconSymbol.className = ''; // reset

            if (type === 'high') {
                title.textContent = 'Bid Above Budget Range';
                iconContainer.classList.add('warning');
                iconSymbol.className = 'fi fi-rr-trending-up';
            } else {
                title.textContent = 'Bid Below Budget Range';
                iconContainer.classList.add('info');
                iconSymbol.className = 'fi fi-rr-trending-down';
            }

            modal.classList.remove('hidden');
        }
    };

    window.closeBudgetWarningModal = function (projectId) {
        // If projectId is not provided, try to close all open budget modals??
        // Or just handle specific one. The call site usually passes projectId.
        // If undefined, maybe look for open modals.
        if (!projectId) {
            const openModals = document.querySelectorAll('.budget-warning-modal:not(.hidden)');
            openModals.forEach(m => m.classList.add('hidden'));
            return;
        }
        const modal = document.getElementById(`budgetWarningModal-${projectId}`);
        if (modal) {
            modal.classList.add('hidden');
        }
    };

    window.handleEditBudget = function (projectId) {
        closeBudgetWarningModal(projectId);
        // Focus on the proposed cost input
        const input = document.getElementById(`modalProposedCost-${projectId}`);
        if (input) {
            // Small delay to allow modal transition
            setTimeout(() => {
                input.focus();
                // Select values? No, just focus.
            }, 100);
        }
    };

    window.handleContinueBudget = function (projectId) {
        closeBudgetWarningModal(projectId);

        // Find the form
        const form = document.getElementById(`applyBidForm-${projectId}`);
        if (form) {
            const continueFunc = window[`continueBidSubmission_${projectId}`];
            if (typeof continueFunc === 'function') {
                continueFunc();
            } else {
                console.error('Continue function not found for project:', projectId);
            }
        }
    };

    // Global Toast Function
    window.showToast = function (message, type = 'info') {
        const toast = document.createElement('div');
        let bgColor = '#EEA24B';
        let icon = '<i class="fi fi-rr-info"></i>';

        if (type === 'success') {
            bgColor = '#10b981';
            icon = '<i class="fi fi-rr-check-circle"></i>';
        } else if (type === 'error') {
            bgColor = '#ef4444';
            icon = '<i class="fi fi-rr-cross-circle"></i>';
        }

        // Use standard classes + inline styles for reliability
        toast.className = 'fixed flex items-center gap-3 px-6 py-4 rounded-lg shadow-lg text-white transition-transform duration-300';
        toast.style.position = 'fixed';
        toast.style.top = '20px';
        toast.style.right = '20px';
        toast.style.zIndex = '999999'; // Ensure it's on top of everything
        toast.style.backgroundColor = bgColor;
        toast.style.transform = 'translateX(120%)'; // Start off-screen right
        toast.style.maxWidth = '350px';

        toast.innerHTML = `${icon} <span class="font-medium">${message}</span>`;

        document.body.appendChild(toast);

        // Animate in
        requestAnimationFrame(() => {
            toast.style.transform = 'translateX(0)';
        });

        // Remove after 3 seconds
        setTimeout(() => {
            toast.style.transform = 'translateX(120%)';
            setTimeout(() => {
                if (document.body.contains(toast)) {
                    document.body.removeChild(toast);
                }
            }, 300);
        }, 3000);
    };
});
