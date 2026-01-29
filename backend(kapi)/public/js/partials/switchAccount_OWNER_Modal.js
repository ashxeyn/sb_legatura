/**
 * Switch to Property Owner Account Form Modal JavaScript
 * Handles the property owner account creation form functionality
 */

// Owner Form Modal JavaScript
document.addEventListener('DOMContentLoaded', function() {
    const switchToOwnerModal = document.getElementById('switchToOwnerModal');
    const switchAccountModal = document.getElementById('switchAccountModal');
    const switchToOwnerModalOverlay = document.getElementById('switchToOwnerModalOverlay');
    const backToSwitchAccountBtn = document.getElementById('backToSwitchAccountBtn');
    const ownerAccountForm = document.getElementById('ownerAccountForm');
    const personalInfoNextBtn = document.getElementById('personalInfoNextBtn');
    const accountSetupForm = document.getElementById('accountSetupForm');
    const accountSetupOverlay = document.getElementById('accountSetupOverlay');
    const backToPersonalInfoBtn = document.getElementById('backToPersonalInfoBtn');
    const accountCredentialsForm = document.getElementById('accountCredentialsForm');
    const accountSetupNextBtn = document.getElementById('accountSetupNextBtn');
    const profilePictureOwnerForm = document.getElementById('profilePictureOwnerForm');
    const profilePictureOwnerOverlay = document.getElementById('profilePictureOwnerOverlay');
    const backToAccountSetupBtn = document.getElementById('backToAccountSetupBtn');
    const ownerProfilePictureForm = document.getElementById('ownerProfilePictureForm');
    const ownerCompleteBtn = document.getElementById('ownerCompleteBtn');
    const ownerProfilePictureInput = document.getElementById('ownerProfilePictureInput');
    const ownerProfilePicturePreview = document.getElementById('ownerProfilePicturePreview');
    const ownerProfilePicturePlaceholder = document.getElementById('ownerProfilePicturePlaceholder');
    const ownerProfilePictureCircle = document.getElementById('ownerProfilePictureCircle');
    const ownerConfirmationModal = document.getElementById('ownerConfirmationModal');
    const ownerConfirmationOverlay = document.getElementById('ownerConfirmationOverlay');
    const ownerConfirmCancelBtn = document.getElementById('ownerConfirmCancelBtn');
    const ownerConfirmBtn = document.getElementById('ownerConfirmBtn');

    // Handle clicking "Switch to Property Owner" button from main switch account modal
    const switchToOwnerBtns = document.querySelectorAll('[data-target="owner"]');
    switchToOwnerBtns.forEach(btn => {
        btn.addEventListener('click', function(e) {
            e.preventDefault();
            // Hide the main switch account modal
            if (switchAccountModal) {
                switchAccountModal.classList.add('hidden');
            }
            // Show the owner form modal
            if (switchToOwnerModal) {
                switchToOwnerModal.classList.remove('hidden');
                document.body.style.overflow = 'hidden';
            }
        });
    });

    // Handle back button
    if (backToSwitchAccountBtn) {
        backToSwitchAccountBtn.addEventListener('click', function(e) {
            e.preventDefault();
            // Hide owner form modal
            switchToOwnerModal.classList.add('hidden');
            // Show main switch account modal with proper active class
            if (switchAccountModal) {
                switchAccountModal.classList.remove('hidden');
                switchAccountModal.classList.add('active');
                document.body.style.overflow = 'hidden';
            }
        });
    }

    // Handle overlay click
    if (switchToOwnerModalOverlay) {
        switchToOwnerModalOverlay.addEventListener('click', function() {
            switchToOwnerModal.classList.add('hidden');
            document.body.style.overflow = '';
        });
    }

    // Handle Personal Info Next button
    if (personalInfoNextBtn) {
        personalInfoNextBtn.addEventListener('click', function(e) {
            e.preventDefault();
            
            // Validate the form first
            if (!ownerAccountForm.checkValidity()) {
                ownerAccountForm.reportValidity();
                return;
            }
            
            // Hide personal info form and show account setup form
            switchToOwnerModal.classList.add('hidden');
            accountSetupForm.classList.remove('hidden');
            document.body.style.overflow = 'hidden';
        });
    }

    // Handle back to personal info
    if (backToPersonalInfoBtn) {
        backToPersonalInfoBtn.addEventListener('click', function(e) {
            e.preventDefault();
            accountSetupForm.classList.add('hidden');
            switchToOwnerModal.classList.remove('hidden');
            document.body.style.overflow = 'hidden';
        });
    }

    // Handle account setup overlay click
    if (accountSetupOverlay) {
        accountSetupOverlay.addEventListener('click', function() {
            accountSetupForm.classList.add('hidden');
            document.body.style.overflow = '';
        });
    }

    // Handle Account Setup Next button
    if (accountSetupNextBtn) {
        accountSetupNextBtn.addEventListener('click', function(e) {
            e.preventDefault();
            
            // Validate the form first
            if (!accountCredentialsForm.checkValidity()) {
                accountCredentialsForm.reportValidity();
                return;
            }
            
            // Hide account setup form and show profile picture form
            accountSetupForm.classList.add('hidden');
            profilePictureOwnerForm.classList.remove('hidden');
            document.body.style.overflow = 'hidden';
        });
    }

    // Handle back to account setup
    if (backToAccountSetupBtn) {
        backToAccountSetupBtn.addEventListener('click', function(e) {
            e.preventDefault();
            profilePictureOwnerForm.classList.add('hidden');
            accountSetupForm.classList.remove('hidden');
            document.body.style.overflow = 'hidden';
        });
    }

    // Handle profile picture overlay click
    if (profilePictureOwnerOverlay) {
        profilePictureOwnerOverlay.addEventListener('click', function() {
            profilePictureOwnerForm.classList.add('hidden');
            document.body.style.overflow = '';
        });
    }

    // Handle profile picture file input change for preview
    if (ownerProfilePictureInput) {
        ownerProfilePictureInput.addEventListener('change', function(e) {
            const file = e.target.files[0];
            if (file && file.type.startsWith('image/')) {
                const reader = new FileReader();
                reader.onload = function(event) {
                    ownerProfilePicturePreview.src = event.target.result;
                    ownerProfilePicturePreview.style.display = 'block';
                    ownerProfilePicturePlaceholder.style.display = 'none';
                    ownerProfilePictureCircle.classList.add('has-image');
                };
                reader.readAsDataURL(file);
            }
        });
    }

    // Handle complete button click - validate then show confirmation
    if (ownerCompleteBtn) {
        ownerCompleteBtn.addEventListener('click', function(e) {
            e.preventDefault();
            
            // Validate the form first
            if (!ownerProfilePictureForm.checkValidity()) {
                ownerProfilePictureForm.reportValidity();
                return;
            }
            
            // Show confirmation modal
            profilePictureOwnerForm.classList.add('hidden');
            ownerConfirmationModal.classList.remove('hidden');
            document.body.style.overflow = 'hidden';
        });
    }

    // Handle owner confirmation cancel
    if (ownerConfirmCancelBtn) {
        ownerConfirmCancelBtn.addEventListener('click', function() {
            ownerConfirmationModal.classList.add('hidden');
            profilePictureOwnerForm.classList.remove('hidden');
            document.body.style.overflow = 'hidden';
        });
    }

    // Handle owner confirmation overlay click
    if (ownerConfirmationOverlay) {
        ownerConfirmationOverlay.addEventListener('click', function() {
            ownerConfirmationModal.classList.add('hidden');
            profilePictureOwnerForm.classList.remove('hidden');
            document.body.style.overflow = 'hidden';
        });
    }

    // Handle owner confirmation confirm
    if (ownerConfirmBtn) {
        ownerConfirmBtn.addEventListener('click', function() {
            // Combine data from all three forms
            const personalInfoData = new FormData(ownerAccountForm);
            const accountSetupData = new FormData(accountCredentialsForm);
            const profilePictureData = new FormData(ownerProfilePictureForm);
            
            const combinedData = {
                // Personal Info
                firstName: personalInfoData.get('first_name'),
                lastName: personalInfoData.get('last_name'),
                occupation: personalInfoData.get('occupation'),
                province: personalInfoData.get('province'),
                // Account Setup
                username: accountSetupData.get('username'),
                email: accountSetupData.get('email'),
                // Profile Picture
                profilePicture: profilePictureData.get('profile_picture')
            };
            
            // Log the data (replace with API call later)
            console.log('Combined Owner Account Data:', combinedData);
            
            // TODO: Send to backend API endpoint
            alert('Property Owner account setup complete!');
            
            // Close modal and reset all forms
            ownerConfirmationModal.classList.add('hidden');
            ownerAccountForm.reset();
            accountCredentialsForm.reset();
            ownerProfilePictureForm.reset();
            ownerProfilePicturePreview.style.display = 'none';
            ownerProfilePicturePlaceholder.style.display = 'flex';
            ownerProfilePictureCircle.classList.remove('has-image');
            document.body.style.overflow = '';
        });
    }
});
