// Get CSRF token
const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');

// Password toggle functionality
function setupPasswordToggle(toggleBtn, passwordInput, eyeIcon) {
    if (!toggleBtn || !passwordInput || !eyeIcon) return;
    
    toggleBtn.addEventListener('click', function() {
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
}

// Initialize password toggles
document.addEventListener('DOMContentLoaded', function() {
    const togglePassword = document.getElementById('togglePassword');
    const passwordInput = document.getElementById('password');
    const eyeIcon = document.getElementById('eyeIcon');
    
    const togglePasswordConfirm = document.getElementById('togglePasswordConfirm');
    const passwordConfirmInput = document.getElementById('password_confirmation');
    const eyeIconConfirm = document.getElementById('eyeIconConfirm');
    
    setupPasswordToggle(togglePassword, passwordInput, eyeIcon);
    setupPasswordToggle(togglePasswordConfirm, passwordConfirmInput, eyeIconConfirm);
});

// Show alert message
function showAlert(message, type = 'error') {
    const alertContainer = document.getElementById('alert-container');
    if (!alertContainer) return;
    
    const alertClass = type === 'success' 
        ? 'bg-green-50 border-green-200 text-green-800' 
        : 'bg-red-50 border-red-200 text-red-800';
    
    const iconClass = type === 'success' ? 'fa-check-circle' : 'fa-exclamation-circle';
    
    alertContainer.innerHTML = `
        <div class="${alertClass} border px-4 py-3 rounded-lg flex items-start space-x-3">
            <i class="fas ${iconClass} mt-0.5"></i>
            <div class="flex-1">
                <p class="font-medium">${message}</p>
            </div>
            <button onclick="this.parentElement.remove()" class="text-gray-500 hover:text-gray-700">
                <i class="fas fa-times"></i>
            </button>
        </div>
    `;
}

// Clear alert
function clearAlert() {
    const alertContainer = document.getElementById('alert-container');
    if (alertContainer) {
        alertContainer.innerHTML = '';
    }
}

// Form validation
function validateForm() {
    clearAlert();
    let isValid = true;
    
    const fullName = document.getElementById('full_name').value.trim();
    const email = document.getElementById('email').value.trim();
    const password = document.getElementById('password').value;
    const passwordConfirmation = document.getElementById('password_confirmation').value;
    const terms = document.getElementById('terms').checked;
    
    // Validate full name
    if (fullName.length < 2) {
        showAlert('Please enter a valid full name (at least 2 characters)');
        document.getElementById('full_name').classList.add('error-border');
        return false;
    }
    
    // Validate email
    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    if (!emailRegex.test(email)) {
        showAlert('Please enter a valid email address');
        document.getElementById('email').classList.add('error-border');
        return false;
    }
    
    // Validate password strength
    if (password.length < 8) {
        showAlert('Password must be at least 8 characters long');
        document.getElementById('password').classList.add('error-border');
        return false;
    }
    
    // Validate password match
    if (password !== passwordConfirmation) {
        showAlert('Passwords do not match');
        document.getElementById('password_confirmation').classList.add('error-border');
        return false;
    }
    
    // Validate terms
    if (!terms) {
        showAlert('You must agree to the Terms & Conditions');
        return false;
    }
    
    return true;
}

// Remove error styling on input
document.addEventListener('DOMContentLoaded', function() {
    const inputs = document.querySelectorAll('input');
    inputs.forEach(input => {
        input.addEventListener('input', function() {
            this.classList.remove('error-border');
        });
    });
});

// Handle form submission
document.addEventListener('DOMContentLoaded', function() {
    const signupForm = document.getElementById('signupForm');
    const signupBtn = document.getElementById('signupBtn');
    
    if (signupForm) {
        signupForm.addEventListener('submit', async function(e) {
            e.preventDefault();
            
            // Validate form
            if (!validateForm()) {
                return;
            }
            
            // Show loading state
            const originalButtonText = signupBtn.innerHTML;
            signupBtn.disabled = true;
            signupBtn.innerHTML = '<span class="spinner"></span> Creating Account...';
            
            // Prepare form data
            const formData = new FormData(signupForm);
            
            try {
                const response = await fetch('/admin/signup', {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': csrfToken,
                        'Accept': 'application/json',
                    },
                    body: formData
                });
                
                const data = await response.json();
                
                if (response.ok) {
                    showAlert('Account created successfully! Redirecting...', 'success');
                    setTimeout(() => {
                        window.location.href = data.redirect || '/admin/dashboard';
                    }, 1500);
                } else {
                    // Handle validation errors
                    if (data.errors) {
                        const firstError = Object.values(data.errors)[0][0];
                        showAlert(firstError);
                    } else {
                        showAlert(data.message || 'An error occurred. Please try again.');
                    }
                    
                    // Reset button
                    signupBtn.disabled = false;
                    signupBtn.innerHTML = originalButtonText;
                }
            } catch (error) {
                console.error('Error:', error);
                showAlert('Network error. Please check your connection and try again.');
                
                // Reset button
                signupBtn.disabled = false;
                signupBtn.innerHTML = originalButtonText;
            }
        });
    }
});
