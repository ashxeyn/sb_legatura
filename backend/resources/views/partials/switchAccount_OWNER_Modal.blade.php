<!-- Switch to Property Owner Account Form Modal -->
<div id="switchToOwnerModal" class="switch-account-modal hidden">
    <div class="modal-overlay" id="switchToOwnerModalOverlay"></div>
    <div class="switch-account-modal-container max-w-2xl">
        <!-- Back Button -->
        <button type="button" class="back-button" id="backToSwitchAccountBtn">
            <i class="fi fi-rr-arrow-left"></i>
            <span>Back</span>
        </button>

        <!-- Modal Header with Icon -->
        <div class="switch-owner-header">
            <div class="owner-form-icon">
                <img src="{{ asset('img/logo2.0.svg') }}" alt="Legatura" class="owner-logo">
            </div>
            <h2 class="owner-form-title">Personal Info</h2>
            <p class="owner-form-subtitle">Add Property Owner role to your account</p>
        </div>

        <!-- Modal Body -->
        <div class="switch-account-modal-body">
            <!-- Info Box -->
            <div class="form-info-box">
                <div class="info-box-icon">
                    <span class="info-number">i</span>
                </div>
                <p class="info-box-text">Pre-filled from your Contractor account</p>
            </div>

            <form id="ownerAccountForm" class="owner-account-form">
                <!-- Personal Details Section -->
                <div class="form-section">
                    <!-- First Name -->
                    <div class="form-group">
                        <label for="ownerFirstName" class="form-label">
                            First Name <span class="required">*</span>
                        </label>
                        <input 
                            type="text" 
                            id="ownerFirstName" 
                            name="first_name"
                            class="form-input"
                            placeholder="Enter first name"
                            value="Jill"
                            required
                        >
                    </div>

                    <!-- Last Name -->
                    <div class="form-group">
                        <label for="ownerLastName" class="form-label">
                            Last Name <span class="required">*</span>
                        </label>
                        <input 
                            type="text" 
                            id="ownerLastName" 
                            name="last_name"
                            class="form-input"
                            placeholder="Enter last name"
                            value="Rose"
                            required
                        >
                    </div>

                    <!-- Occupation -->
                    <div class="form-group">
                        <label for="ownerOccupation" class="form-label">
                            Occupation <span class="required">*</span>
                        </label>
                        <div class="select-wrapper">
                            <select 
                                id="ownerOccupation" 
                                name="occupation"
                                class="form-select"
                                required
                            >
                                <option value="" disabled selected>Select Occupation</option>
                                <option value="business-owner">Business Owner</option>
                                <option value="employed">Employed</option>
                                <option value="self-employed">Self-Employed</option>
                                <option value="retired">Retired</option>
                                <option value="investor">Investor</option>
                                <option value="other">Other</option>
                            </select>
                            <i class="fi fi-rr-angle-down select-icon"></i>
                        </div>
                    </div>

                    <!-- Province -->
                    <div class="form-group">
                        <label for="ownerProvince" class="form-label">
                            Province <span class="required">*</span>
                        </label>
                        <div class="select-wrapper">
                            <select 
                                id="ownerProvince" 
                                name="province"
                                class="form-select"
                                required
                            >
                                <option value="" disabled selected>Select Province</option>
                                <option value="metro-manila">Metro Manila</option>
                                <option value="cebu">Cebu</option>
                                <option value="davao">Davao</option>
                                <option value="iloilo">Iloilo</option>
                                <option value="pampanga">Pampanga</option>
                                <option value="batangas">Batangas</option>
                                <option value="laguna">Laguna</option>
                                <option value="cavite">Cavite</option>
                            </select>
                            <i class="fi fi-rr-angle-down select-icon"></i>
                        </div>
                    </div>
                </div>

                <!-- Submit Button -->
                <div class="form-actions">
                    <button type="button" class="owner-form-submit-btn" id="personalInfoNextBtn">
                        <span>Next</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Account Setup Form -->
<div id="accountSetupForm" class="switch-account-modal hidden">
    <div class="modal-overlay" id="accountSetupOverlay"></div>
    <div class="switch-account-modal-container max-w-2xl">
        <!-- Back Button -->
        <button type="button" class="back-button" id="backToPersonalInfoBtn">
            <i class="fi fi-rr-arrow-left"></i>
            <span>Back</span>
        </button>

        <!-- Modal Header with Icon -->
        <div class="switch-owner-header">
            <div class="owner-form-icon">
                <img src="{{ asset('img/logo2.0.svg') }}" alt="Legatura" class="owner-logo">
            </div>
            <h2 class="owner-form-title">Account Setup</h2>
            <p class="owner-form-subtitle">Manage your login credentials</p>
        </div>

        <!-- Modal Body -->
        <div class="switch-account-modal-body">
            <form id="accountCredentialsForm" class="owner-account-form">
                <!-- Credentials Section -->
                <div class="form-section">
                    <!-- Username -->
                    <div class="form-group">
                        <label for="ownerUsername" class="form-label">
                            Username <span class="required">*</span>
                        </label>
                        <input 
                            type="text" 
                            id="ownerUsername" 
                            name="username"
                            class="form-input"
                            placeholder="Enter username"
                            value="jel"
                            required
                        >
                    </div>

                    <!-- Email -->
                    <div class="form-group">
                        <label for="ownerEmail" class="form-label">
                            Email <span class="required">*</span>
                        </label>
                        <input 
                            type="email" 
                            id="ownerEmail" 
                            name="email"
                            class="form-input"
                            placeholder="Enter email"
                            value="jill.rose@example.com"
                            required
                        >
                    </div>
                </div>

                <!-- Submit Button -->
                <div class="form-actions">
                    <button type="button" class="owner-form-submit-btn" id="accountSetupNextBtn">
                        <span>Next</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Profile Picture Form -->
<div id="profilePictureOwnerForm" class="switch-account-modal hidden">
    <div class="modal-overlay" id="profilePictureOwnerOverlay"></div>
    <div class="switch-account-modal-container max-w-2xl">
        <!-- Back Button -->
        <button type="button" class="back-button" id="backToAccountSetupBtn">
            <i class="fi fi-rr-arrow-left"></i>
            <span>Back</span>
        </button>

        <!-- Modal Header with Icon -->
        <div class="switch-owner-header">
            <div class="owner-form-icon">
                <img src="{{ asset('img/logo2.0.svg') }}" alt="Legatura" class="owner-logo">
            </div>
            <h2 class="owner-form-title">Profile Picture</h2>
            <p class="owner-form-subtitle">Step 3: Finalize your profile</p>
        </div>

        <!-- Modal Body -->
        <div class="switch-account-modal-body">
            <form id="ownerProfilePictureForm" class="owner-account-form">
                <!-- Profile Picture Upload Section -->
                <div class="form-section profile-picture-section">
                    <div class="profile-picture-upload-container">
                        <div class="profile-picture-circle" id="ownerProfilePictureCircle">
                            <input 
                                type="file" 
                                id="ownerProfilePictureInput" 
                                name="profile_picture"
                                accept="image/*"
                                class="profile-picture-input"
                            >
                            <label for="ownerProfilePictureInput" class="profile-picture-label">
                                <div class="profile-picture-placeholder" id="ownerProfilePicturePlaceholder">
                                    <i class="fi fi-rr-user"></i>
                                </div>
                                <img id="ownerProfilePicturePreview" class="profile-picture-preview" style="display: none;" alt="Profile Preview">
                            </label>
                        </div>
                        <p class="upload-photo-text">Upload Photo (Optional)</p>
                    </div>
                </div>

                <!-- Submit Button -->
                <div class="form-actions">
                    <button type="button" class="complete-role-switch-btn" id="ownerCompleteBtn">
                        <span>Complete Role Switch</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Confirmation Modal -->
<div id="ownerConfirmationModal" class="confirmation-modal hidden">
    <div class="confirmation-overlay" id="ownerConfirmationOverlay"></div>
    <div class="confirmation-dialog">
        <div class="confirmation-icon-wrapper">
            <div class="confirmation-icon">
                <i class="fi fi-rr-interrogation"></i>
            </div>
        </div>
        <h2 class="confirmation-title">Switch to Property Owner?</h2>
        <p class="confirmation-message">You are about to add the Property Owner role. You can switch roles anytime from settings.</p>
        <div class="confirmation-actions">
            <button type="button" class="confirmation-cancel-btn" id="ownerConfirmCancelBtn">
                <span>Cancel</span>
            </button>
            <button type="button" class="confirmation-confirm-btn" id="ownerConfirmBtn">
                <span>Confirm</span>
            </button>
        </div>
    </div>
</div>
