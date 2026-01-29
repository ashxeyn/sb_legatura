/**
 * Post Project Modal JavaScript
 * Handles modal opening/closing and form submission
 */

class PostProjectModal {
    constructor() {
        this.modal = document.getElementById('postProjectModal');
        this.overlay = document.getElementById('modalOverlay');
        this.openBtn = document.getElementById('openPostModalBtn');
        this.closeBtn = document.getElementById('closeModalBtn');
        this.cancelBtn = document.getElementById('cancelModalBtn');
        this.form = document.getElementById('postProjectForm');
        this.submitBtn = document.getElementById('submitProjectBtn');
        this.othersFileCount = 1;
        
        this.init();
    }

    init() {
        this.setupEventListeners();
        this.loadBarangays();
        this.setupContractorTypeHandler();
        this.setupFileUploadHandlers();
        this.setMinDate();
    }

    setupEventListeners() {
        // Open modal
        if (this.openBtn) {
            this.openBtn.addEventListener('click', () => this.openModal());
        }

        // Close modal
        if (this.closeBtn) {
            this.closeBtn.addEventListener('click', () => this.closeModal());
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

    openModal() {
        if (this.modal) {
            this.modal.classList.add('active');
            document.body.style.overflow = 'hidden';
            // Focus on first input
            const firstInput = this.modal.querySelector('input, textarea, select');
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

    resetForm() {
        if (this.form) {
            this.form.reset();
            this.hideAllErrors();
            this.hideAlerts();
            this.othersFileCount = 1;
            // Reset file displays
            document.querySelectorAll('.file-name-display').forEach(display => {
                display.classList.remove('has-file');
                display.textContent = '';
            });
            // Reset others container
            const othersContainer = document.getElementById('modal_others_upload_container');
            if (othersContainer) {
                othersContainer.innerHTML = `
                    <div class="file-upload-wrapper">
                        <input type="file" id="modal_others_0" name="others[]" accept=".pdf,.doc,.docx,.jpg,.jpeg,.png" class="file-input">
                        <label for="modal_others_0" class="file-upload-label">
                            <i class="fi fi-rr-upload"></i>
                            <span>Choose File</span>
                        </label>
                    </div>
                `;
                this.setupFileUploadHandlers();
            }
        }
    }

    async loadBarangays() {
        const barangaySelect = document.getElementById('modal_project_barangay');
        if (!barangaySelect) return;

        try {
            // Get Zamboanga City code (you may need to adjust this)
            const cityCode = '097322'; // Zamboanga City code
            const response = await fetch(`/api/psgc/cities/${cityCode}/barangays`);
            const data = await response.json();

            if (data && Array.isArray(data)) {
                barangaySelect.innerHTML = '<option value="">Select Barangay</option>';
                data.forEach(barangay => {
                    const option = document.createElement('option');
                    option.value = barangay.code;
                    option.textContent = barangay.name;
                    barangaySelect.appendChild(option);
                });
                barangaySelect.disabled = false;
            }
        } catch (error) {
            console.error('Error loading barangays:', error);
            barangaySelect.innerHTML = '<option value="">Error loading barangays</option>';
        }
    }

    setupContractorTypeHandler() {
        const contractorTypeSelect = document.getElementById('modal_project_type_id');
        const otherContainer = document.getElementById('modal_other_contractor_type_container');
        const otherInput = document.getElementById('modal_if_others_ctype');

        if (contractorTypeSelect && otherContainer) {
            contractorTypeSelect.addEventListener('change', () => {
                const selectedOption = contractorTypeSelect.options[contractorTypeSelect.selectedIndex];
                const typeName = selectedOption.getAttribute('data-name') || selectedOption.textContent;

                if (typeName && typeName.toLowerCase().trim() === 'others') {
                    otherContainer.classList.remove('hidden');
                    if (otherInput) {
                        otherInput.required = true;
                    }
                } else {
                    otherContainer.classList.add('hidden');
                    if (otherInput) {
                        otherInput.required = false;
                        otherInput.value = '';
                    }
                }
            });
        }
    }

    setupFileUploadHandlers() {
        // Building permit
        const buildingPermitInput = document.getElementById('modal_building_permit');
        const buildingPermitName = document.getElementById('building_permit_name');
        if (buildingPermitInput && buildingPermitName) {
            buildingPermitInput.addEventListener('change', (e) => {
                if (e.target.files.length > 0) {
                    buildingPermitName.textContent = e.target.files[0].name;
                    buildingPermitName.classList.add('has-file');
                } else {
                    buildingPermitName.classList.remove('has-file');
                }
            });
        }

        // Land title
        const landTitleInput = document.getElementById('modal_land_title');
        const landTitleName = document.getElementById('land_title_name');
        if (landTitleInput && landTitleName) {
            landTitleInput.addEventListener('change', (e) => {
                if (e.target.files.length > 0) {
                    landTitleName.textContent = e.target.files[0].name;
                    landTitleName.classList.add('has-file');
                } else {
                    landTitleName.classList.remove('has-file');
                }
            });
        }

        // Add more files button
        const addOthersBtn = document.getElementById('modal_add_others_file');
        if (addOthersBtn) {
            addOthersBtn.addEventListener('click', () => this.addMoreFiles());
        }
    }

    addMoreFiles() {
        const container = document.getElementById('modal_others_upload_container');
        if (!container) return;

        if (this.othersFileCount >= 10) {
            alert('Maximum 10 files allowed');
            return;
        }

        const fileId = `modal_others_${this.othersFileCount}`;
        const fileWrapper = document.createElement('div');
        fileWrapper.className = 'file-upload-wrapper';
        fileWrapper.style.marginTop = '0.5rem';
        fileWrapper.innerHTML = `
            <input type="file" id="${fileId}" name="others[]" accept=".pdf,.doc,.docx,.jpg,.jpeg,.png" class="file-input">
            <label for="${fileId}" class="file-upload-label">
                <i class="fi fi-rr-upload"></i>
                <span>Choose File</span>
            </label>
        `;
        container.appendChild(fileWrapper);
        this.othersFileCount++;
    }

    setMinDate() {
        const deadlineInput = document.getElementById('modal_bidding_deadline');
        if (deadlineInput) {
            const today = new Date();
            today.setDate(today.getDate() + 1); // Minimum tomorrow
            deadlineInput.min = today.toISOString().split('T')[0];
        }
    }

    async handleSubmit(e) {
        e.preventDefault();
        
        if (!this.form) return;

        // Hide previous errors
        this.hideAllErrors();
        this.hideAlerts();

        // Disable submit button
        if (this.submitBtn) {
            this.submitBtn.disabled = true;
            const originalText = this.submitBtn.innerHTML;
            this.submitBtn.innerHTML = '<i class="fi fi-rr-spinner"></i> <span>Posting...</span>';
        }

        // Prepare location data
        this.prepareLocationData();

        // Create FormData
        const formData = new FormData(this.form);
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || 
                         document.querySelector('input[name="_token"]')?.value;

        try {
            const response = await fetch('/owner/projects', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': csrfToken,
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: formData
            });

            const data = await response.json();

            if (data.success) {
                this.showSuccess(data.message || 'Project posted successfully!');
                // Reset form and close modal after 2 seconds
                setTimeout(() => {
                    this.closeModal();
                    // Reload page or update contractors list
                    window.location.reload();
                }, 2000);
            } else {
                this.showError(data.message || 'An error occurred while posting the project.');
                if (data.errors) {
                    this.displayErrors(data.errors);
                }
            }
        } catch (error) {
            console.error('Error submitting form:', error);
            this.showError('Network error. Please try again.');
        } finally {
            // Re-enable submit button
            if (this.submitBtn) {
                this.submitBtn.disabled = false;
                this.submitBtn.innerHTML = '<i class="fi fi-rr-check"></i> <span>Post Project</span>';
            }
        }
    }

    prepareLocationData() {
        const barangaySelect = document.getElementById('modal_project_barangay');
        const streetInput = document.getElementById('modal_street_address');
        const locationHidden = document.getElementById('modal_project_location_hidden');
        const cityCodeHidden = document.getElementById('modal_project_city_code_hidden');
        const provinceCodeHidden = document.getElementById('modal_project_province_code_hidden');

        if (barangaySelect && streetInput) {
            const barangayName = barangaySelect.options[barangaySelect.selectedIndex]?.textContent || '';
            const street = streetInput.value || '';
            
            // Compose location string
            const location = `${street}, ${barangayName}, Zamboanga City, Zamboanga del Sur`;
            if (locationHidden) {
                locationHidden.value = location;
            }

            // Set PSGC codes (Zamboanga City and Zamboanga del Sur)
            if (cityCodeHidden) {
                cityCodeHidden.value = '097322'; // Zamboanga City code
            }
            if (provinceCodeHidden) {
                provinceCodeHidden.value = '097300000'; // Zamboanga del Sur code
            }
        }
    }

    displayErrors(errors) {
        Object.keys(errors).forEach(field => {
            const errorElement = document.getElementById(`error_${field}`);
            if (errorElement) {
                errorElement.textContent = Array.isArray(errors[field]) ? errors[field][0] : errors[field];
                errorElement.classList.remove('hidden');
            }
        });
    }

    hideAllErrors() {
        document.querySelectorAll('.error-message').forEach(error => {
            error.classList.add('hidden');
        });
    }

    showSuccess(message) {
        const successDiv = document.getElementById('modalFormSuccess');
        if (successDiv) {
            successDiv.textContent = message;
            successDiv.classList.remove('hidden');
            successDiv.scrollIntoView({ behavior: 'smooth', block: 'center' });
        }
    }

    showError(message) {
        const errorDiv = document.getElementById('modalFormError');
        if (errorDiv) {
            errorDiv.textContent = message;
            errorDiv.classList.remove('hidden');
            errorDiv.scrollIntoView({ behavior: 'smooth', block: 'center' });
        }
    }

    hideAlerts() {
        const successDiv = document.getElementById('modalFormSuccess');
        const errorDiv = document.getElementById('modalFormError');
        if (successDiv) successDiv.classList.add('hidden');
        if (errorDiv) errorDiv.classList.add('hidden');
    }
}

// Initialize modal when DOM is ready
document.addEventListener('DOMContentLoaded', () => {
    new PostProjectModal();
});

