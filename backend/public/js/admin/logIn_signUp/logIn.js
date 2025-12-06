// Admin Login Page JavaScript

document.addEventListener('DOMContentLoaded', function() {
    // Get form elements
    const loginForm = document.getElementById('loginForm');
    const loginBtn = document.getElementById('loginBtn');
    const togglePassword = document.getElementById('togglePassword');
    const passwordInput = document.getElementById('password');
    const eyeIcon = document.getElementById('eyeIcon');
    const alertContainer = document.getElementById('alert-container');

    // Password visibility toggle
    togglePassword.addEventListener('click', function() {
        const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
        passwordInput.setAttribute('type', type);
        
        // Toggle eye icon
        if (type === 'text') {
            eyeIcon.classList.remove('fa-eye');
            eyeIcon.classList.add('fa-eye-slash');
        } else {
            eyeIcon.classList.remove('fa-eye-slash');
            eyeIcon.classList.add('fa-eye');
        }
    });

    // Form validation
    function validateEmail(email) {
        const re = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        return re.test(email);
    }

    function showError(input, message) {
        const formGroup = input.closest('.form-group');
        const errorMessage = formGroup.querySelector('.error-message');
        
        input.classList.add('error');
        input.classList.remove('success');
        errorMessage.textContent = message;
        errorMessage.classList.remove('hidden');
    }

    function showSuccess(input) {
        const formGroup = input.closest('.form-group');
        const errorMessage = formGroup.querySelector('.error-message');
        
        input.classList.remove('error');
        input.classList.add('success');
        errorMessage.classList.add('hidden');
    }

    function clearErrors() {
        const inputs = loginForm.querySelectorAll('input');
        inputs.forEach(input => {
            input.classList.remove('error', 'success');
            const formGroup = input.closest('.form-group');
            if (formGroup) {
                const errorMessage = formGroup.querySelector('.error-message');
                if (errorMessage) {
                    errorMessage.classList.add('hidden');
                }
            }
        });
    }

    function showAlert(message, type = 'info') {
        const alertDiv = document.createElement('div');
        alertDiv.className = `alert alert-${type}`;
        
        const icon = type === 'success' ? 'fa-check-circle' : 
                     type === 'error' ? 'fa-exclamation-circle' : 
                     'fa-info-circle';
        
        alertDiv.innerHTML = `
            <i class="fas ${icon}"></i>
            <span>${message}</span>
        `;
        
        alertContainer.innerHTML = '';
        alertContainer.appendChild(alertDiv);
        
        // Auto-hide after 5 seconds
        setTimeout(() => {
            alertDiv.style.opacity = '0';
            setTimeout(() => alertDiv.remove(), 300);
        }, 5000);
    }

    // Real-time validation
    const emailInput = document.getElementById('email');
    
    emailInput.addEventListener('blur', function() {
        if (this.value.trim() === '') {
            showError(this, 'Email is required');
        } else if (!validateEmail(this.value)) {
            showError(this, 'Please enter a valid email address');
        } else {
            showSuccess(this);
        }
    });

    passwordInput.addEventListener('blur', function() {
        if (this.value.trim() === '') {
            showError(this, 'Password is required');
        } else if (this.value.length < 8) {
            showError(this, 'Password must be at least 8 characters');
        } else {
            showSuccess(this);
        }
    });

    // Clear errors on input
    emailInput.addEventListener('input', function() {
        if (this.classList.contains('error')) {
            this.classList.remove('error');
            const formGroup = this.closest('.form-group');
            const errorMessage = formGroup.querySelector('.error-message');
            errorMessage.classList.add('hidden');
        }
    });

    passwordInput.addEventListener('input', function() {
        if (this.classList.contains('error')) {
            this.classList.remove('error');
            const formGroup = this.closest('.form-group');
            const errorMessage = formGroup.querySelector('.error-message');
            errorMessage.classList.add('hidden');
        }
    });

    // Form submission
    loginForm.addEventListener('submit', async function(e) {
        e.preventDefault();
        
        // Clear previous errors
        clearErrors();
        
        // Get form data
        const email = emailInput.value.trim();
        const password = passwordInput.value;
        const remember = document.getElementById('remember').checked;
        
        // Validate
        let isValid = true;
        
        if (email === '') {
            showError(emailInput, 'Email is required');
            isValid = false;
        } else if (!validateEmail(email)) {
            showError(emailInput, 'Please enter a valid email address');
            isValid = false;
        }
        
        if (password === '') {
            showError(passwordInput, 'Password is required');
            isValid = false;
        } else if (password.length < 8) {
            showError(passwordInput, 'Password must be at least 8 characters');
            isValid = false;
        }
        
        if (!isValid) {
            return;
        }
        
        // Show loading state
        loginBtn.classList.add('loading');
        loginBtn.disabled = true;
        
        try {
            // Get CSRF token
            const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
            
            // Submit form data
            const formData = new FormData();
            formData.append('email', email);
            formData.append('password', password);
            formData.append('remember', remember ? '1' : '0');
            
            const response = await fetch('/admin/login', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json',
                },
                body: formData
            });
            
            const data = await response.json();
            
            if (response.ok && data.success) {
                showAlert('Login successful! Redirecting...', 'success');
                
                // Redirect after short delay
                setTimeout(() => {
                    window.location.href = data.redirect || '/admin/dashboard';
                }, 1500);
            } else {
                // Show error message
                showAlert(data.message || 'Invalid email or password. Please try again.', 'error');
                
                // Remove loading state
                loginBtn.classList.remove('loading');
                loginBtn.disabled = false;
            }
        } catch (error) {
            console.error('Login error:', error);
            showAlert('An error occurred. Please try again later.', 'error');
            
            // Remove loading state
            loginBtn.classList.remove('loading');
            loginBtn.disabled = false;
        }
    });

    // Add enter key support for form submission
    loginForm.addEventListener('keypress', function(e) {
        if (e.key === 'Enter') {
            e.preventDefault();
            loginForm.dispatchEvent(new Event('submit'));
        }
    });

    // Focus first input on load
    emailInput.focus();

    // Add animation to form on load
    const formElements = loginForm.querySelectorAll('.form-group, button, .flex');
    formElements.forEach((element, index) => {
        element.style.opacity = '0';
        element.style.transform = 'translateY(10px)';
        
        setTimeout(() => {
            element.style.transition = 'all 0.3s ease-out';
            element.style.opacity = '1';
            element.style.transform = 'translateY(0)';
        }, index * 50);
    });
});
