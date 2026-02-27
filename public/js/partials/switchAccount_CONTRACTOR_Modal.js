/**
 * Switch to Contractor Account Form Modal JavaScript
 * Handles the contractor account creation form functionality
 */

// Contractor Form Modal JavaScript
document.addEventListener('DOMContentLoaded', function() {
    const switchToContractorModal = document.getElementById('switchToContractorModal');
    const switchAccountModal = document.getElementById('switchAccountModal');
    const switchToContractorModalOverlay = document.getElementById('switchToContractorModalOverlay');
    const backToSwitchAccountFromContractorBtn = document.getElementById('backToSwitchAccountFromContractorBtn');
    const contractorAccountForm = document.getElementById('contractorAccountForm');
    const companyDetailsNextBtn = document.getElementById('companyDetailsNextBtn');
    const authorizedRepresentativeForm = document.getElementById('authorizedRepresentativeForm');
    const authorizedRepresentativeOverlay = document.getElementById('authorizedRepresentativeOverlay');
    const backToCompanyDetailsBtn = document.getElementById('backToCompanyDetailsBtn');
    const authorizedRepForm = document.getElementById('authorizedRepForm');
    const authorizedRepNextBtn = document.getElementById('authorizedRepNextBtn');
    const verificationDocumentsForm = document.getElementById('verificationDocumentsForm');
    const verificationDocumentsOverlay = document.getElementById('verificationDocumentsOverlay');
    const backToAuthorizedRepBtn = document.getElementById('backToAuthorizedRepBtn');
    const verificationForm = document.getElementById('verificationForm');
    const verificationNextBtn = document.getElementById('verificationNextBtn');
    const profilePictureForm = document.getElementById('profilePictureForm');
    const profilePictureOverlay = document.getElementById('profilePictureOverlay');
    const backToVerificationBtn = document.getElementById('backToVerificationBtn');
    const profilePictureFormElement = document.getElementById('profilePictureFormElement');
    const contractorCompleteBtn = document.getElementById('contractorCompleteBtn');
    const contractorConfirmationModal = document.getElementById('contractorConfirmationModal');
    const contractorConfirmationOverlay = document.getElementById('contractorConfirmationOverlay');
    const contractorConfirmCancelBtn = document.getElementById('contractorConfirmCancelBtn');
    const contractorConfirmBtn = document.getElementById('contractorConfirmBtn');
    const profilePictureInput = document.getElementById('profilePictureInput');
    const profilePicturePreview = document.getElementById('profilePicturePreview');
    const profilePicturePlaceholder = document.getElementById('profilePicturePlaceholder');
    const profilePictureCircle = document.getElementById('profilePictureCircle');
    const dtiSecFile = document.getElementById('dtiSecFile');
    const dtiSecPreview = document.getElementById('dtiSecPreview');
    const dtiSecPlaceholder = document.getElementById('dtiSecPlaceholder');

    // Handle clicking "Switch to Contractor" button from main switch account modal
    const switchToContractorBtns = document.querySelectorAll('[data-target="contractor"]');
    switchToContractorBtns.forEach(btn => {
        btn.addEventListener('click', function(e) {
            e.preventDefault();
            // Hide the main switch account modal
            if (switchAccountModal) {
                switchAccountModal.classList.add('hidden');
            }
            // Show the contractor form modal
            if (switchToContractorModal) {
                switchToContractorModal.classList.remove('hidden');
            }
        });
    });

    // Handle back button
    if (backToSwitchAccountFromContractorBtn) {
        backToSwitchAccountFromContractorBtn.addEventListener('click', function(e) {
            e.preventDefault();
            // Hide contractor form modal
            switchToContractorModal.classList.add('hidden');
            // Show main switch account modal with proper active class
            if (switchAccountModal) {
                switchAccountModal.classList.remove('hidden');
                switchAccountModal.classList.add('active');
                document.body.style.overflow = 'hidden';
            }
        });
    }

    // Handle overlay click
    if (switchToContractorModalOverlay) {
        switchToContractorModalOverlay.addEventListener('click', function() {
            switchToContractorModal.classList.add('hidden');
            document.body.style.overflow = '';
        });
    }

    // Handle Company Details Next button
    if (companyDetailsNextBtn) {
        companyDetailsNextBtn.addEventListener('click', function(e) {
            e.preventDefault();

            // Validate company details form
            if (contractorAccountForm.checkValidity()) {
                // Hide company details form modal
                if (switchToContractorModal) {
                    switchToContractorModal.classList.add('hidden');
                }
                // Show authorized representative form modal
                if (authorizedRepresentativeForm) {
                    authorizedRepresentativeForm.classList.remove('hidden');
                    document.body.style.overflow = 'hidden';
                }
            } else {
                // Show validation errors
                contractorAccountForm.reportValidity();
            }
        });
    }

    // Handle back button from Authorized Representative form
    if (backToCompanyDetailsBtn) {
        backToCompanyDetailsBtn.addEventListener('click', function(e) {
            e.preventDefault();
            // Hide authorized representative form
            authorizedRepresentativeForm.classList.add('hidden');
            // Show company details form
            switchToContractorModal.classList.remove('hidden');
        });
    }

    // Handle overlay click on Authorized Representative form
    if (authorizedRepresentativeOverlay) {
        authorizedRepresentativeOverlay.addEventListener('click', function() {
            authorizedRepresentativeForm.classList.add('hidden');
            document.body.style.overflow = '';
        });
    }

    // Handle Authorized Representative form submission
    if (authorizedRepForm) {
        authorizedRepForm.addEventListener('submit', function(e) {
            e.preventDefault();

            // Get form data
            const contractorData = new FormData(contractorAccountForm);
            const repData = new FormData(authorizedRepForm);

            // Combine both forms data
            const allData = {
                ...Object.fromEntries(contractorData),
                ...Object.fromEntries(repData)
            };

            console.log('Complete Contractor Account Data:', allData);

            // TODO: Send to backend API endpoint
            alert('Account setup complete! (Backend integration pending)');

            // Close modal and reset
            authorizedRepresentativeForm.classList.add('hidden');
            switchToContractorModal.classList.add('hidden');
            document.body.style.overflow = '';
        });
    }

    // Handle Authorized Rep Next button (navigate to Verification form)
    if (authorizedRepNextBtn) {
        authorizedRepNextBtn.addEventListener('click', function(e) {
            e.preventDefault();

            // Validate authorized rep form
            if (authorizedRepForm.checkValidity()) {
                // Hide authorized representative form
                authorizedRepresentativeForm.classList.add('hidden');
                // Show verification documents form
                if (verificationDocumentsForm) {
                    verificationDocumentsForm.classList.remove('hidden');
                    document.body.style.overflow = 'hidden';
                }
            } else {
                // Show validation errors
                authorizedRepForm.reportValidity();
            }
        });
    }

    // Handle back button from Verification form
    if (backToAuthorizedRepBtn) {
        backToAuthorizedRepBtn.addEventListener('click', function(e) {
            e.preventDefault();
            // Hide verification form
            verificationDocumentsForm.classList.add('hidden');
            // Show authorized representative form
            authorizedRepresentativeForm.classList.remove('hidden');
        });
    }

    // Handle overlay click on Verification form
    if (verificationDocumentsOverlay) {
        verificationDocumentsOverlay.addEventListener('click', function() {
            verificationDocumentsForm.classList.add('hidden');
            document.body.style.overflow = '';
        });
    }

    // Handle Verification form Next button (navigate to Profile Picture form)
    if (verificationNextBtn) {
        verificationNextBtn.addEventListener('click', function(e) {
            e.preventDefault();

            // Validate verification form
            if (verificationForm.checkValidity()) {
                // Hide verification documents form
                verificationDocumentsForm.classList.add('hidden');
                // Show profile picture form
                if (profilePictureForm) {
                    profilePictureForm.classList.remove('hidden');
                    document.body.style.overflow = 'hidden';
                }
            } else {
                // Show validation errors
                verificationForm.reportValidity();
            }
        });
    }

    // Handle back button from Profile Picture form
    if (backToVerificationBtn) {
        backToVerificationBtn.addEventListener('click', function(e) {
            e.preventDefault();
            // Hide profile picture form
            profilePictureForm.classList.add('hidden');
            // Show verification documents form
            verificationDocumentsForm.classList.remove('hidden');
        });
    }

    // Handle overlay click on Profile Picture form
    if (profilePictureOverlay) {
        profilePictureOverlay.addEventListener('click', function() {
            profilePictureForm.classList.add('hidden');
            document.body.style.overflow = '';
        });
    }

    // Handle Profile Picture form submission
    if (profilePictureFormElement) {
        profilePictureFormElement.addEventListener('submit', function(e) {
            e.preventDefault();

            // Get form data from all four forms
            const contractorData = new FormData(contractorAccountForm);
            const repData = new FormData(authorizedRepForm);
            const verificationData = new FormData(verificationForm);
            const profileData = new FormData(profilePictureFormElement);

            // Combine all forms data
            const allData = {
                ...Object.fromEntries(contractorData),
                ...Object.fromEntries(repData),
                ...Object.fromEntries(verificationData),
                ...Object.fromEntries(profileData)
            };

            console.log('Complete Contractor Account Setup Data:', allData);

            // TODO: Send to backend API endpoint
            alert('Contractor account setup complete!');

            // Close modal and reset
            profilePictureForm.classList.add('hidden');
            document.body.style.overflow = '';
        });
    }

    // Province and City relationship (placeholder - can be enhanced with real data)
    const provinceSelect = document.getElementById('province');
    const citySelect = document.getElementById('city');

    if (provinceSelect && citySelect) {
        provinceSelect.addEventListener('change', function() {
            // Clear current cities
            citySelect.innerHTML = '<option value="" disabled selected>Select City</option>';

            // Add placeholder cities based on province (you can expand this with real data)
            const cities = {
                'metro-manila': ['Manila', 'Quezon City', 'Makati', 'Taguig', 'Pasig', 'Mandaluyong'],
                'cebu': ['Cebu City', 'Mandaue', 'Lapu-Lapu', 'Talisay'],
                'davao': ['Davao City', 'Tagum', 'Panabo', 'Digos'],
                'iloilo': ['Iloilo City', 'Passi', 'Bacolod'],
                'pampanga': ['Angeles City', 'San Fernando', 'Mabalacat'],
                'batangas': ['Batangas City', 'Lipa', 'Tanauan'],
                'laguna': ['Calamba', 'Santa Rosa', 'Biñan', 'San Pablo'],
                'cavite': ['Bacoor', 'Imus', 'Dasmariñas', 'Tagaytay']
            };

            const selectedProvince = this.value;
            if (cities[selectedProvince]) {
                cities[selectedProvince].forEach(city => {
                    const option = document.createElement('option');
                    option.value = city.toLowerCase().replace(/\s+/g, '-');
                    option.textContent = city;
                    citySelect.appendChild(option);
                });
            }
        });
    }

    // File upload preview for DTI/SEC Registration Photo
    const dtiSecFileInput = document.getElementById('dtiSecFile');
    if (dtiSecFileInput) {
        dtiSecFileInput.addEventListener('change', function(e) {
            const file = e.target.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function(event) {
                    // Get the label element
                    const label = document.querySelector('label[for="dtiSecFile"]');

                    // Check if preview already exists
                    let previewImg = label.querySelector('.file-preview-image');
                    if (!previewImg) {
                        // Create preview image element
                        previewImg = document.createElement('img');
                        previewImg.className = 'file-preview-image';
                        label.insertBefore(previewImg, label.firstChild);

                        // Hide the upload icon and text when preview is shown
                        const uploadIcon = label.querySelector('.file-upload-icon');
                        const uploadText = label.querySelector('.file-upload-text');
                        if (uploadIcon) uploadIcon.style.display = 'none';
                        if (uploadText) uploadText.textContent = 'Change File';
                    }

                    previewImg.src = event.target.result;
                };
                reader.readAsDataURL(file);
            }
        });
    }

    // File upload preview for Profile Picture
    const profilePictureFileInput = document.getElementById('profilePictureFile');
    if (profilePictureFileInput) {
        profilePictureFileInput.addEventListener('change', function(e) {
            const file = e.target.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function(event) {
                    // Get the circle element
                    const circle = document.querySelector('.profile-picture-circle');

                    // Check if preview already exists
                    let previewImg = circle.querySelector('.profile-preview-image');
                    if (!previewImg) {
                        // Create preview image element
                        previewImg = document.createElement('img');
                        previewImg.className = 'profile-preview-image';
                        circle.appendChild(previewImg);

                        // Hide the building icon
                        const buildingIcon = circle.querySelector('i');
                        if (buildingIcon) buildingIcon.style.display = 'none';
                    }

                    previewImg.src = event.target.result;
                };
                reader.readAsDataURL(file);
            }
        });
    }

    // Handle complete button click - validate then show confirmation
    if (contractorCompleteBtn) {
        contractorCompleteBtn.addEventListener('click', function(e) {
            e.preventDefault();

            // Validate the form first
            if (!profilePictureFormElement.checkValidity()) {
                profilePictureFormElement.reportValidity();
                return;
            }

            // Show confirmation modal
            profilePictureForm.classList.add('hidden');
            contractorConfirmationModal.classList.remove('hidden');
            document.body.style.overflow = 'hidden';
        });
    }

    // Handle contractor confirmation cancel
    if (contractorConfirmCancelBtn) {
        contractorConfirmCancelBtn.addEventListener('click', function() {
            contractorConfirmationModal.classList.add('hidden');
            profilePictureForm.classList.remove('hidden');
            document.body.style.overflow = 'hidden';
        });
    }

    // Handle contractor confirmation overlay click
    if (contractorConfirmationOverlay) {
        contractorConfirmationOverlay.addEventListener('click', function() {
            contractorConfirmationModal.classList.add('hidden');
            profilePictureForm.classList.remove('hidden');
            document.body.style.overflow = 'hidden';
        });
    }

    // Handle contractor confirmation confirm
    if (contractorConfirmBtn) {
        contractorConfirmBtn.addEventListener('click', function() {
            // Combine data from all 4 forms
            const companyData = new FormData(contractorAccountForm);
            const authRepData = new FormData(authorizedRepForm);
            const verificationData = new FormData(verificationForm);
            const profilePicData = new FormData(profilePictureFormElement);

            const combinedData = {
                // Company Details
                companyName: companyData.get('company_name'),
                companyPhone: companyData.get('company_phone'),
                yearsExperience: companyData.get('years_experience'),
                contractorType: companyData.get('contractor_type'),
                streetAddress: companyData.get('street_address'),
                province: companyData.get('province'),
                city: companyData.get('city'),
                // Authorized Representative
                firstName: authRepData.get('first_name'),
                middleName: authRepData.get('middle_name'),
                lastName: authRepData.get('last_name'),
                username: authRepData.get('username'),
                personalEmail: authRepData.get('personal_email'),
                // Verification
                picabNumber: verificationData.get('picab_number'),
                picabExpiration: verificationData.get('picab_expiration'),
                dtiSecFile: verificationData.get('dti_sec_file'),
                // Profile Picture
                profilePicture: profilePicData.get('profile_picture')
            };

            // Log the data (replace with API call later)
            console.log('Combined Contractor Account Data:', combinedData);

            // TODO: Send to backend API endpoint
            alert('Contractor account setup complete!');

            // Close modal and reset all forms
            contractorConfirmationModal.classList.add('hidden');
            contractorAccountForm.reset();
            authorizedRepForm.reset();
            verificationForm.reset();
            profilePictureFormElement.reset();
            dtiSecPreview.style.display = 'none';
            dtiSecPlaceholder.style.display = 'flex';
            profilePicturePreview.style.display = 'none';
            profilePicturePlaceholder.style.display = 'flex';
            profilePictureCircle.classList.remove('has-image');
            document.body.style.overflow = '';
        });
    }
});
