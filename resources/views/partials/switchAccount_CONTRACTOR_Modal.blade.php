<!-- Switch to Contractor Account Form Modal -->
<div id="switchToContractorModal" class="switch-account-modal hidden">
    <div class="modal-overlay" id="switchToContractorModalOverlay"></div>
    <div class="switch-account-modal-container max-w-2xl">
        <!-- Back Button -->
        <button type="button" class="back-button" id="backToSwitchAccountBtn">
            <i class="fi fi-rr-arrow-left"></i>
            <span>Back</span>
        </button>

        <!-- Modal Header with Icon -->
        <div class="switch-contractor-header">
            <div class="contractor-form-icon">
                <img src="{{ asset('img/logo2.0.svg') }}" alt="Legatura" class="contractor-logo">
            </div>
            <h2 class="contractor-form-title">Company Information</h2>
            <p class="contractor-form-subtitle">Add Contractor role to your account</p>
        </div>

        <!-- Modal Body -->
        <div class="switch-account-modal-body">
            <form id="contractorAccountForm" class="contractor-account-form">
                <!-- Company Details Section -->
                <div class="form-section">
                    <h3 class="form-section-title">Company Details</h3>
                    
                    <!-- Company Name -->
                    <div class="form-group">
                        <label for="companyName" class="form-label">
                            Company Name <span class="required">*</span>
                        </label>
                        <input 
                            type="text" 
                            id="companyName" 
                            name="company_name"
                            class="form-input"
                            placeholder="Enter company name"
                            required
                        >
                    </div>

                    <!-- Company Phone -->
                    <div class="form-group">
                        <label for="companyPhone" class="form-label">
                            Company Phone <span class="required">*</span>
                        </label>
                        <input 
                            type="tel" 
                            id="companyPhone" 
                            name="company_phone"
                            class="form-input"
                            placeholder="09171234567"
                            required
                        >
                    </div>

                    <!-- Years of Experience -->
                    <div class="form-group">
                        <label for="yearsExperience" class="form-label">
                            Years of Experience <span class="required">*</span>
                        </label>
                        <input 
                            type="number" 
                            id="yearsExperience" 
                            name="years_experience"
                            class="form-input"
                            placeholder="e.g. 5"
                            min="0"
                            required
                        >
                    </div>

                    <!-- Contractor Type -->
                    <div class="form-group">
                        <label for="contractorType" class="form-label">
                            Contractor Type <span class="required">*</span>
                        </label>
                        <div class="select-wrapper">
                            <select 
                                id="contractorType" 
                                name="contractor_type"
                                class="form-select"
                                required
                            >
                                <option value="" disabled selected>Select Type</option>
                                <option value="general">General Contractor</option>
                                <option value="electrical">Electrical Contractor</option>
                                <option value="plumbing">Plumbing Contractor</option>
                                <option value="hvac">HVAC Contractor</option>
                                <option value="roofing">Roofing Contractor</option>
                                <option value="landscaping">Landscaping Contractor</option>
                                <option value="painting">Painting Contractor</option>
                                <option value="flooring">Flooring Contractor</option>
                                <option value="masonry">Masonry Contractor</option>
                                <option value="carpentry">Carpentry Contractor</option>
                            </select>
                            <i class="fi fi-rr-angle-down select-icon"></i>
                        </div>
                    </div>
                </div>

                <!-- Business Address Section -->
                <div class="form-section">
                    <h3 class="form-section-title">Business Address</h3>
                    
                    <!-- Street/Building No -->
                    <div class="form-group">
                        <label for="streetAddress" class="form-label">
                            Street/Building No. <span class="required">*</span>
                        </label>
                        <input 
                            type="text" 
                            id="streetAddress" 
                            name="street_address"
                            class="form-input"
                            placeholder="e.g. 123 Main Street"
                            required
                        >
                    </div>

                    <!-- Province -->
                    <div class="form-group">
                        <label for="province" class="form-label">
                            Province <span class="required">*</span>
                        </label>
                        <div class="select-wrapper">
                            <select 
                                id="province" 
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

                    <!-- City/Municipality -->
                    <div class="form-group">
                        <label for="city" class="form-label">
                            City/Municipality <span class="required">*</span>
                        </label>
                        <div class="select-wrapper">
                            <select 
                                id="city" 
                                name="city"
                                class="form-select"
                                required
                            >
                                <option value="" disabled selected>Select City</option>
                                <!-- Cities will be populated based on province selection -->
                            </select>
                            <i class="fi fi-rr-angle-down select-icon"></i>
                        </div>
                    </div>
                </div>

                <!-- Submit Button -->
                <div class="form-actions">
                    <button type="button" class="contractor-form-submit-btn" id="companyDetailsNextBtn">
                        <span>Next</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Authorized Representative Details Form -->
<div id="authorizedRepresentativeForm" class="switch-account-modal hidden">
    <div class="modal-overlay" id="authorizedRepresentativeOverlay"></div>
    <div class="switch-account-modal-container max-w-2xl">
        <!-- Back Button -->
        <button type="button" class="back-button" id="backToCompanyDetailsBtn">
            <i class="fi fi-rr-arrow-left"></i>
            <span>Back</span>
        </button>

        <!-- Modal Header with Icon -->
        <div class="switch-contractor-header">
            <div class="contractor-form-icon">
                <img src="{{ asset('img/logo2.0.svg') }}" alt="Legatura" class="contractor-logo">
            </div>
            <h2 class="contractor-form-title">Account Setup</h2>
            <p class="contractor-form-subtitle">Authorized Representative Details</p>
        </div>

        <!-- Modal Body -->
        <div class="switch-account-modal-body">
            <!-- Info Box -->
            <div class="form-info-box">
                <div class="info-box-icon">
                    <span class="info-number">i</span>
                </div>
                <p class="info-box-text">Pre-filled from your Property Owner account</p>
            </div>

            <form id="authorizedRepForm" class="contractor-account-form">
                <!-- Personal Details Section -->
                <div class="form-section">
                    <h3 class="form-section-title">Personal Details</h3>
                    
                    <!-- First Name -->
                    <div class="form-group">
                        <label for="firstName" class="form-label">
                            First Name <span class="required">*</span>
                        </label>
                        <input 
                            type="text" 
                            id="firstName" 
                            name="first_name"
                            class="form-input"
                            placeholder="Enter first name"
                            value="Jill"
                            required
                        >
                    </div>

                    <!-- Middle Name -->
                    <div class="form-group">
                        <label for="middleName" class="form-label">
                            Middle Name (Optional)
                        </label>
                        <input 
                            type="text" 
                            id="middleName" 
                            name="middle_name"
                            class="form-input"
                            placeholder="Enter middle name"
                            value="Ann"
                        >
                    </div>

                    <!-- Last Name -->
                    <div class="form-group">
                        <label for="lastName" class="form-label">
                            Last Name <span class="required">*</span>
                        </label>
                        <input 
                            type="text" 
                            id="lastName" 
                            name="last_name"
                            class="form-input"
                            placeholder="Enter last name"
                            value="Rose"
                            required
                        >
                    </div>
                </div>

                <!-- Account Details Section -->
                <div class="form-section">
                    <h3 class="form-section-title">Account Details</h3>
                    
                    <!-- Username -->
                    <div class="form-group">
                        <label for="username" class="form-label">
                            Username <span class="required">*</span>
                        </label>
                        <input 
                            type="text" 
                            id="username" 
                            name="username"
                            class="form-input"
                            placeholder="Enter username"
                            value="jel"
                            required
                        >
                    </div>

                    <!-- Personal Email -->
                    <div class="form-group">
                        <label for="personalEmail" class="form-label">
                            Personal Email <span class="required">*</span>
                        </label>
                        <input 
                            type="email" 
                            id="personalEmail" 
                            name="personal_email"
                            class="form-input"
                            placeholder="Enter personal email"
                            value="jill@example.com"
                            required
                        >
                    </div>
                </div>

                <!-- Submit Button -->
                <div class="form-actions">
                    <button type="button" class="contractor-form-submit-btn" id="authorizedRepNextBtn">
                        <span>Next</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Verification Documents Form -->
<div id="verificationDocumentsForm" class="switch-account-modal hidden">
    <div class="modal-overlay" id="verificationDocumentsOverlay"></div>
    <div class="switch-account-modal-container max-w-2xl">
        <!-- Back Button -->
        <button type="button" class="back-button" id="backToAuthorizedRepBtn">
            <i class="fi fi-rr-arrow-left"></i>
            <span>Back</span>
        </button>

        <!-- Modal Header with Icon -->
        <div class="switch-contractor-header">
            <div class="contractor-form-icon">
                <img src="{{ asset('img/logo2.0.svg') }}" alt="Legatura" class="contractor-logo">
            </div>
            <h2 class="contractor-form-title">Verification</h2>
            <p class="contractor-form-subtitle">Step 2: Verification Documents</p>
        </div>

        <!-- Modal Body -->
        <div class="switch-account-modal-body">
            <form id="verificationForm" class="contractor-account-form">
                <!-- PICAB Information Section -->
                <div class="form-section">
                    <h3 class="form-section-title">PICAB Information</h3>
                    
                    <!-- PICAB Number -->
                    <div class="form-group">
                        <label for="picabNumber" class="form-label">
                            PICAB Number <span class="required">*</span>
                        </label>
                        <input 
                            type="text" 
                            id="picabNumber" 
                            name="picab_number"
                            class="form-input"
                            placeholder="Enter PICAB"
                            required
                        >
                    </div>

                    <!-- PICAB Expiration -->
                    <div class="form-group">
                        <label for="picabExpiration" class="form-label">
                            PICAB Expiration <span class="required">*</span>
                        </label>
                        <input 
                            type="date" 
                            id="picabExpiration" 
                            name="picab_expiration"
                            class="form-input"
                            required
                        >
                    </div>
                </div>

                <!-- Business Documents Section -->
                <div class="form-section">
                    <h3 class="form-section-title">Business Documents</h3>
                    
                    <!-- DTI/SEC Registration Photo -->
                    <div class="form-group">
                        <label for="dtiSecFile" class="form-label">
                            DTI/SEC Registration Photo <span class="required">*</span>
                        </label>
                        <div class="file-upload-area">
                            <input 
                                type="file" 
                                id="dtiSecFile" 
                                name="dti_sec_file"
                                class="file-input hidden"
                                accept=".jpg,.jpeg,.png,.pdf"
                                required
                            >
                            <label for="dtiSecFile" class="file-upload-label">
                                <div class="file-upload-icon">
                                    <i class="fi fi-rr-cloud-upload"></i>
                                </div>
                                <p class="file-upload-text">DTI/SEC Registration Photo</p>
                                <p class="file-upload-hint">JPG, PNG, or PDF (Max 5MB)</p>
                            </label>
                        </div>
                    </div>
                </div>

                <!-- Submit Button -->
                <div class="form-actions">
                    <button type="button" class="contractor-form-submit-btn" id="verificationNextBtn">
                        <span>Next</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Final Step - Profile Picture Form -->
<div id="profilePictureForm" class="switch-account-modal hidden">
    <div class="modal-overlay" id="profilePictureOverlay"></div>
    <div class="switch-account-modal-container max-w-2xl">
        <!-- Back Button -->
        <button type="button" class="back-button" id="backToVerificationBtn">
            <i class="fi fi-rr-arrow-left"></i>
            <span>Back</span>
        </button>

        <!-- Modal Header with Icon -->
        <div class="switch-contractor-header">
            <div class="contractor-form-icon">
                <img src="{{ asset('img/logo2.0.svg') }}" alt="Legatura" class="contractor-logo">
            </div>
            <h2 class="contractor-form-title">Final Step</h2>
            <p class="contractor-form-subtitle">Step 3: Profile Picture</p>
        </div>

        <!-- Modal Body -->
        <div class="switch-account-modal-body">
            <form id="profilePictureFormElement" class="contractor-account-form">
                <!-- Profile Picture Section -->
                <div class="form-section profile-picture-section">
                    <!-- Profile Picture Upload -->
                    <div class="form-group">
                        <div class="profile-picture-upload-area">
                            <input 
                                type="file" 
                                id="profilePictureFile" 
                                name="profile_picture"
                                class="file-input hidden"
                                accept=".jpg,.jpeg,.png"
                                required
                            >
                            <label for="profilePictureFile" class="profile-picture-upload-label">
                                <div class="profile-picture-circle">
                                    <i class="fi fi-rr-building"></i>
                                </div>
                                <p class="profile-picture-text">Choose Profile Picture</p>
                                <p class="profile-picture-hint">JPG or PNG (Max 2MB)</p>
                            </label>
                        </div>
                    </div>
                </div>

                <!-- Submit Button -->
                <div class="form-actions">
                    <button type="button" class="contractor-form-submit-btn complete-role-switch-btn" id="contractorCompleteBtn">
                        <span>Complete Role Switch</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Confirmation Modal -->
<div id="contractorConfirmationModal" class="confirmation-modal hidden">
    <div class="confirmation-overlay" id="contractorConfirmationOverlay"></div>
    <div class="confirmation-dialog">
        <div class="confirmation-icon-wrapper">
            <div class="confirmation-icon">
                <i class="fi fi-rr-interrogation"></i>
            </div>
        </div>
        <h2 class="confirmation-title">Switch to Contractor?</h2>
        <p class="confirmation-message">You are about to add the Contractor role. You can switch roles anytime from settings.</p>
        <div class="confirmation-actions">
            <button type="button" class="confirmation-cancel-btn" id="contractorConfirmCancelBtn">
                <span>Cancel</span>
            </button>
            <button type="button" class="confirmation-confirm-btn" id="contractorConfirmBtn">
                <span>Confirm</span>
            </button>
        </div>
    </div>
</div>
