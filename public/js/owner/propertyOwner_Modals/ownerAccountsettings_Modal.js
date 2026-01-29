/**
 * Edit Profile Information Modal JavaScript
 * Handles the edit profile modal functionality
 */

class EditProfileModal {
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
        const inputs = this.form?.querySelectorAll('input, select');
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
        // Load current profile data from the page
        const profileName = document.getElementById('profileName');
        const occupationValue = document.getElementById('occupationValue');
        
        if (profileName) {
            const fullName = profileName.textContent.trim();
            const nameParts = fullName.split(' ');
            
            const firstNameInput = document.getElementById('firstName');
            const middleNameInput = document.getElementById('middleName');
            const lastNameInput = document.getElementById('lastName');
            
            if (firstNameInput && nameParts.length > 0) {
                firstNameInput.value = nameParts[0] || '';
            }
            
            if (middleNameInput && nameParts.length > 2) {
                middleNameInput.value = nameParts.slice(1, -1).join(' ') || '';
            }
            
            if (lastNameInput && nameParts.length > 1) {
                lastNameInput.value = nameParts[nameParts.length - 1] || '';
            }
        }
        
        if (occupationValue) {
            const occupationInput = document.getElementById('occupation');
            if (occupationInput) {
                occupationInput.value = occupationValue.textContent.trim() || '';
            }
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
            firstName: document.getElementById('firstName')?.value || '',
            middleName: document.getElementById('middleName')?.value || '',
            lastName: document.getElementById('lastName')?.value || '',
            occupation: document.getElementById('occupation')?.value || '',
            gender: document.getElementById('gender')?.value || '',
            dateOfBirth: document.getElementById('dateOfBirth')?.value || ''
        };
    }

    async saveProfileData(formData) {
        try {
            // In a real implementation, make API call
            // const response = await fetch('/api/profile/update', {
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
        // Update profile name
        const profileName = document.getElementById('profileName');
        const infoCardName = document.getElementById('infoCardName');
        
        let fullName = formData.firstName;
        if (formData.middleName) {
            fullName += ' ' + formData.middleName;
        }
        fullName += ' ' + formData.lastName;

        if (profileName) {
            profileName.textContent = fullName.trim();
        }
        if (infoCardName) {
            infoCardName.textContent = fullName.trim();
        }

        // Update occupation
        const occupationValue = document.getElementById('occupationValue');
        if (occupationValue && formData.occupation) {
            occupationValue.textContent = formData.occupation;
        }

        // Update initials if needed
        const initials = this.getInitials(fullName);
        const profilePictureInitials = document.querySelector('.profile-picture-initials');
        if (profilePictureInitials) {
            profilePictureInitials.textContent = initials;
        }
    }

    getInitials(name) {
        return name
            .split(' ')
            .map(word => word[0])
            .join('')
            .toUpperCase()
            .substring(0, 3);
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
let editProfileModalInstance = null;

document.addEventListener('DOMContentLoaded', () => {
    editProfileModalInstance = new EditProfileModal();
    
    // Expose globally for navbar to use
    window.openEditProfileModal = () => {
        if (editProfileModalInstance) {
            editProfileModalInstance.open();
        }
    };
});
