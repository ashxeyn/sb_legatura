/**
 * Edit Profile Information Modal JavaScript - Contractor
 * Handles the contractor edit profile modal functionality
 */

class ContractorEditProfileModal {
    constructor() {
        this.modal = document.getElementById('editProfileModal');
        this.overlay = document.getElementById('editProfileModalOverlay');
        this.form = document.getElementById('editProfileForm');
        this.init();
    }

    init() {
        this.setupEventListeners();
        this.loadProfileData();
    }

    setupEventListeners() {
        // Close button
        const closeBtn = document.getElementById('closeEditProfileModalBtn');
        if (closeBtn) {
            closeBtn.addEventListener('click', () => {
                this.close();
            });
        }

        // Overlay click
        if (this.overlay) {
            this.overlay.addEventListener('click', () => {
                this.close();
            });
        }

        // Cancel button
        const cancelBtn = document.getElementById('cancelEditProfileBtn');
        if (cancelBtn) {
            cancelBtn.addEventListener('click', () => {
                this.close();
            });
        }

        // Save button
        const saveBtn = document.getElementById('saveEditProfileBtn');
        if (saveBtn) {
            saveBtn.addEventListener('click', () => {
                this.handleSave();
            });
        }

        // Form submission
        if (this.form) {
            this.form.addEventListener('submit', (e) => {
                e.preventDefault();
                this.handleSave();
            });
        }

        // ESC key to close
        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape' && this.isOpen()) {
                this.close();
            }
        });

        // Input validation on blur
        const inputs = this.form?.querySelectorAll('input, select, textarea');
        if (inputs) {
            inputs.forEach(input => {
                input.addEventListener('blur', () => {
                    this.validateField(input);
                });
                input.addEventListener('input', () => {
                    if (input.classList.contains('error')) {
                        this.validateField(input);
                    }
                });
            });
        }
    }

    open() {
        if (this.modal) {
            this.modal.classList.add('active');
            document.body.style.overflow = 'hidden';
            this.loadProfileData();
        }
    }

    close() {
        if (this.modal) {
            this.modal.classList.remove('active');
            document.body.style.overflow = '';
            // Reset form validation states
            if (this.form) {
                const errorFields = this.form.querySelectorAll('.error');
                errorFields.forEach(field => {
                    field.classList.remove('error');
                });
            }
        }
    }

    isOpen() {
        return this.modal && this.modal.classList.contains('active');
    }

    loadProfileData() {
        // Load current profile data - you can populate from existing page elements
        // Sample data for demonstration
        const businessNameInput = document.getElementById('businessName');
        const businessTypeInput = document.getElementById('businessType');
        const yearsExperienceInput = document.getElementById('yearsExperience');
        const firstNameInput = document.getElementById('firstName');
        const lastNameInput = document.getElementById('lastName');
        const positionInput = document.getElementById('position');
        const contactNumberInput = document.getElementById('contactNumber');
        const licenseNumberInput = document.getElementById('licenseNumber');
        const philgepsRegistrationInput = document.getElementById('philgepsRegistration');
        const businessAddressInput = document.getElementById('businessAddress');
        const cityInput = document.getElementById('city');
        const provinceInput = document.getElementById('province');

        // Try to load from page elements
        const profileUserName = document.getElementById('profileUserName');
        if (profileUserName && businessNameInput) {
            businessNameInput.value = profileUserName.textContent.trim() || 'BuildRight Construction';
        }

        // Set default/sample values for demonstration
        if (businessTypeInput && !businessTypeInput.value) {
            businessTypeInput.value = 'general_contractor';
        }
        
        if (yearsExperienceInput && !yearsExperienceInput.value) {
            yearsExperienceInput.value = '15';
        }

        if (firstNameInput && !firstNameInput.value) {
            firstNameInput.value = 'John';
        }

        if (lastNameInput && !lastNameInput.value) {
            lastNameInput.value = 'Smith';
        }

        if (positionInput && !positionInput.value) {
            positionInput.value = 'Project Manager';
        }

        if (contactNumberInput && !contactNumberInput.value) {
            contactNumberInput.value = '+63 912 345 6789';
        }

        if (cityInput && !cityInput.value) {
            cityInput.value = 'Zamboanga City';
        }

        if (provinceInput && !provinceInput.value) {
            provinceInput.value = 'Zamboanga del Sur';
        }
    }

    validateField(field) {
        const isValid = field.checkValidity();
        
        if (field.required && !isValid) {
            field.classList.add('error');
            return false;
        } else {
            field.classList.remove('error');
            return true;
        }
    }

    validateForm() {
        if (!this.form) return false;

        const requiredFields = this.form.querySelectorAll('[required]');
        let isValid = true;

        requiredFields.forEach(field => {
            if (!this.validateField(field)) {
                isValid = false;
            }
        });

        return isValid;
    }

    handleSave() {
        if (!this.validateForm()) {
            this.showNotification('Please fill in all required fields', 'error');
            return;
        }

        const saveBtn = document.getElementById('saveEditProfileBtn');
        const formData = this.getFormData();

        // Show loading state
        if (saveBtn) {
            const originalText = saveBtn.innerHTML;
            saveBtn.innerHTML = '<i class="fi fi-rr-spinner animate-spin"></i> Saving...';
            saveBtn.disabled = true;

            // Simulate API call
            setTimeout(() => {
                this.saveProfileData(formData);
                
                // Reset button
                saveBtn.innerHTML = originalText;
                saveBtn.disabled = false;
            }, 1000);
        }
    }

    getFormData() {
        if (!this.form) return {};

        return {
            businessName: document.getElementById('businessName')?.value || '',
            businessType: document.getElementById('businessType')?.value || '',
            yearsExperience: document.getElementById('yearsExperience')?.value || '',
            firstName: document.getElementById('firstName')?.value || '',
            lastName: document.getElementById('lastName')?.value || '',
            position: document.getElementById('position')?.value || '',
            contactNumber: document.getElementById('contactNumber')?.value || '',
            licenseNumber: document.getElementById('licenseNumber')?.value || '',
            philgepsRegistration: document.getElementById('philgepsRegistration')?.value || '',
            businessAddress: document.getElementById('businessAddress')?.value || '',
            city: document.getElementById('city')?.value || '',
            province: document.getElementById('province')?.value || ''
        };
    }

    async saveProfileData(formData) {
        try {
            // In a real implementation, make API call
            // const response = await fetch('/api/contractor/profile/update', {
            //     method: 'POST',
            //     headers: {
            //         'Content-Type': 'application/json',
            //         'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            //     },
            //     body: JSON.stringify(formData)
            // });
            // const data = await response.json();

            // For now, update the UI directly
            this.updateProfileDisplay(formData);
            this.showNotification('Profile updated successfully!', 'success');
            this.close();
        } catch (error) {
            console.error('Error saving profile:', error);
            this.showNotification('Error saving profile. Please try again.', 'error');
        }
    }

    updateProfileDisplay(formData) {
        // Update profile name (business name)
        const profileUserName = document.getElementById('profileUserName');
        if (profileUserName && formData.businessName) {
            profileUserName.textContent = formData.businessName;
        }

        // Update navbar elements if they exist
        const navbarBusinessName = document.querySelector('.navbar-user-name');
        if (navbarBusinessName && formData.businessName) {
            navbarBusinessName.textContent = formData.businessName;
        }

        // Update initials
        const initials = this.getInitials(formData.businessName);
        const profileInitials = document.querySelector('.profile-initials');
        if (profileInitials) {
            profileInitials.textContent = initials;
        }

        // Update navbar avatar initials
        const navbarAvatarInitials = document.querySelector('.navbar-avatar-initials span');
        if (navbarAvatarInitials) {
            navbarAvatarInitials.textContent = initials;
        }
    }

    getInitials(name) {
        if (!name) return 'BC';
        
        return name
            .split(' ')
            .map(word => word[0])
            .filter(char => char)
            .join('')
            .toUpperCase()
            .substring(0, 2);
    }

    showNotification(message, type = 'info') {
        // Create toast notification
        const toast = document.createElement('div');
        let bgColor = '#EEA24B';
        if (type === 'success') {
            bgColor = '#10b981';
        } else if (type === 'error') {
            bgColor = '#ef4444';
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
}

// Initialize when DOM is ready
let contractorEditProfileModalInstance = null;

document.addEventListener('DOMContentLoaded', () => {
    contractorEditProfileModalInstance = new ContractorEditProfileModal();
    
    // Expose globally for navbar to use
    window.openEditProfileModal = () => {
        if (contractorEditProfileModalInstance) {
            contractorEditProfileModalInstance.open();
        }
    };
});
