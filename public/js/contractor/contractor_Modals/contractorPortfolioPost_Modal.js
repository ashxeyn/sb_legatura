/**
 * Portfolio Post Modal JavaScript
 * Handles modal opening/closing and form submission
 */

class PortfolioPostModal {
    constructor() {
        this.modal = document.getElementById('portfolioPostModal');
        this.overlay = document.getElementById('portfolioModalOverlay');
        this.closeBtn = document.getElementById('closePortfolioModalBtn');
        this.cancelBtn = document.getElementById('cancelPortfolioModalBtn');
        this.form = document.getElementById('portfolioPostForm');
        this.submitBtn = document.getElementById('submitPortfolioBtn');
        this.imageInput = document.getElementById('portfolio_image');
        this.imagePreview = document.getElementById('portfolio_image_preview');
        this.imagePreviewImg = document.getElementById('portfolio_image_preview_img');
        this.removeImageBtn = document.getElementById('remove_portfolio_image');
        
        this.init();
    }

    init() {
        this.setupEventListeners();
        this.setupImagePreview();
    }

    setupEventListeners() {
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

    setupImagePreview() {
        if (this.imageInput) {
            this.imageInput.addEventListener('change', (e) => this.handleImageChange(e));
        }

        if (this.removeImageBtn) {
            this.removeImageBtn.addEventListener('click', () => this.removeImage());
        }
    }

    handleImageChange(event) {
        const file = event.target.files[0];
        if (!file) return;

        // Validate file type
        const validTypes = ['image/jpeg', 'image/jpg', 'image/png'];
        if (!validTypes.includes(file.type)) {
            this.showError('portfolio_image', 'Please select a valid image file (JPG, JPEG, or PNG)');
            this.imageInput.value = '';
            return;
        }

        // Validate file size (10MB)
        const maxSize = 10 * 1024 * 1024; // 10MB in bytes
        if (file.size > maxSize) {
            this.showError('portfolio_image', 'Image size must be less than 10MB');
            this.imageInput.value = '';
            return;
        }

        // Hide error if any
        this.hideError('portfolio_image');

        // Show preview
        const reader = new FileReader();
        reader.onload = (e) => {
            if (this.imagePreviewImg) {
                this.imagePreviewImg.src = e.target.result;
            }
            if (this.imagePreview) {
                this.imagePreview.classList.remove('hidden');
            }
        };
        reader.readAsDataURL(file);

        // Update file name display
        const fileNameDisplay = document.getElementById('portfolio_image_name');
        if (fileNameDisplay) {
            fileNameDisplay.textContent = file.name;
            fileNameDisplay.classList.add('has-file');
        }
    }

    removeImage() {
        if (this.imageInput) {
            this.imageInput.value = '';
        }
        if (this.imagePreview) {
            this.imagePreview.classList.add('hidden');
        }
        if (this.imagePreviewImg) {
            this.imagePreviewImg.src = '';
        }
        const fileNameDisplay = document.getElementById('portfolio_image_name');
        if (fileNameDisplay) {
            fileNameDisplay.textContent = '';
            fileNameDisplay.classList.remove('has-file');
        }
        this.hideError('portfolio_image');
    }

    openModal() {
        if (this.modal) {
            this.modal.classList.add('active');
            document.body.style.overflow = 'hidden';
            // Focus on first input
            const firstInput = this.modal.querySelector('input, textarea');
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
            this.removeImage();
        }
    }

    handleSubmit(e) {
        e.preventDefault();
        
        // Hide previous errors
        this.hideAllErrors();
        this.hideAlerts();

        // Basic validation
        const title = document.getElementById('portfolio_title').value.trim();
        const description = document.getElementById('portfolio_description').value.trim();
        const image = this.imageInput?.files[0];

        let hasError = false;

        if (!title) {
            this.showError('portfolio_title', 'Portfolio title is required');
            hasError = true;
        }

        if (!description) {
            this.showError('portfolio_description', 'Description is required');
            hasError = true;
        }

        if (!image) {
            this.showError('portfolio_image', 'Portfolio image is required');
            hasError = true;
        }

        if (hasError) {
            return;
        }

        // Disable submit button
        if (this.submitBtn) {
            this.submitBtn.disabled = true;
            this.submitBtn.innerHTML = '<i class="fi fi-rr-spinner"></i> <span>Posting...</span>';
        }

        // In a real implementation, submit the form
        // For now, simulate success
        setTimeout(() => {
            this.showSuccess('Portfolio post created successfully!');
            setTimeout(() => {
                this.closeModal();
                // In a real implementation, refresh the portfolio feed
                // window.location.reload();
            }, 1500);
        }, 1000);

        // TODO: Uncomment when backend is ready
        // this.form.submit();
    }

    showError(fieldId, message) {
        const errorEl = document.getElementById(`error_${fieldId}`);
        if (errorEl) {
            errorEl.textContent = message;
            errorEl.classList.remove('hidden');
        }
    }

    hideError(fieldId) {
        const errorEl = document.getElementById(`error_${fieldId}`);
        if (errorEl) {
            errorEl.classList.add('hidden');
        }
    }

    hideAllErrors() {
        const errorElements = this.modal?.querySelectorAll('.error-message');
        if (errorElements) {
            errorElements.forEach(el => el.classList.add('hidden'));
        }
    }

    showSuccess(message) {
        const successEl = document.getElementById('portfolioFormSuccess');
        if (successEl) {
            successEl.textContent = message;
            successEl.classList.remove('hidden');
        }
    }

    showErrorAlert(message) {
        const errorEl = document.getElementById('portfolioFormError');
        if (errorEl) {
            errorEl.textContent = message;
            errorEl.classList.remove('hidden');
        }
    }

    hideAlerts() {
        const successEl = document.getElementById('portfolioFormSuccess');
        const errorEl = document.getElementById('portfolioFormError');
        if (successEl) successEl.classList.add('hidden');
        if (errorEl) errorEl.classList.add('hidden');
    }
}

// Initialize when DOM is ready
let portfolioPostModalInstance = null;

document.addEventListener('DOMContentLoaded', () => {
    portfolioPostModalInstance = new PortfolioPostModal();
    
    // Expose globally for contractor profile to use
    window.openPortfolioPostModal = () => {
        if (portfolioPostModalInstance) {
            portfolioPostModalInstance.openModal();
        }
    };
});
