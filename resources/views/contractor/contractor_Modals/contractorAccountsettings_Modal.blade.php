<!-- Edit Profile Information Modal - Contractor -->
<div id="editProfileModal" class="edit-profile-modal">
    <div class="modal-overlay" id="editProfileModalOverlay"></div>
    <div class="edit-profile-modal-container">
        <!-- Modal Header -->
        <div class="edit-profile-modal-header">
            <h2 class="edit-profile-modal-title">
                <i class="fi fi-rr-edit"></i>
                Edit Profile Information
            </h2>
            <button class="edit-profile-close-btn" id="closeEditProfileModalBtn" aria-label="Close modal">
                <i class="fi fi-rr-cross"></i>
            </button>
        </div>

        <!-- Modal Body -->
        <div class="edit-profile-modal-body">
            <form id="editProfileForm" class="edit-profile-form">
                <!-- Business Information Section -->
                <div class="form-section">
                    <h3 class="form-section-title">
                        <i class="fi fi-rr-building"></i>
                        Business Information
                    </h3>
                    
                    <div class="form-group full-width">
                        <label for="businessName" class="form-label">
                            Business/Company Name <span class="required">*</span>
                        </label>
                        <input 
                            type="text" 
                            id="businessName" 
                            name="businessName" 
                            class="form-input" 
                            placeholder="Enter your business or company name"
                            required
                        >
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label for="businessType" class="form-label">
                                Business Type <span class="required">*</span>
                            </label>
                            <select id="businessType" name="businessType" class="form-select" required>
                                <option value="">Select business type</option>
                                <option value="general_contractor">General Contractor</option>
                                <option value="electrical">Electrical Contractor</option>
                                <option value="plumbing">Plumbing Contractor</option>
                                <option value="hvac">HVAC Contractor</option>
                                <option value="carpentry">Carpentry & Woodwork</option>
                                <option value="masonry">Masonry & Concrete</option>
                                <option value="painting">Painting & Finishing</option>
                                <option value="roofing">Roofing Contractor</option>
                                <option value="landscaping">Landscaping & Outdoor</option>
                                <option value="other">Other Specialty</option>
                            </select>
                        </div>

                        <div class="form-group">
                            <label for="yearsExperience" class="form-label">
                                Years of Experience <span class="required">*</span>
                            </label>
                            <input 
                                type="number" 
                                id="yearsExperience" 
                                name="yearsExperience" 
                                class="form-input" 
                                placeholder="e.g., 15"
                                min="0"
                                max="100"
                                required
                            >
                        </div>
                    </div>
                </div>

                <!-- Contact Person Section -->
                <div class="form-section">
                    <h3 class="form-section-title">
                        <i class="fi fi-rr-user"></i>
                        Contact Person
                    </h3>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label for="firstName" class="form-label">
                                First Name <span class="required">*</span>
                            </label>
                            <input 
                                type="text" 
                                id="firstName" 
                                name="firstName" 
                                class="form-input" 
                                placeholder="Enter first name"
                                required
                            >
                        </div>

                        <div class="form-group">
                            <label for="lastName" class="form-label">
                                Last Name <span class="required">*</span>
                            </label>
                            <input 
                                type="text" 
                                id="lastName" 
                                name="lastName" 
                                class="form-input" 
                                placeholder="Enter last name"
                                required
                            >
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label for="position" class="form-label">
                                Position/Role <span class="required">*</span>
                            </label>
                            <input 
                                type="text" 
                                id="position" 
                                name="position" 
                                class="form-input" 
                                placeholder="e.g., Project Manager, Owner"
                                required
                            >
                        </div>

                        <div class="form-group">
                            <label for="contactNumber" class="form-label">
                                Contact Number <span class="required">*</span>
                            </label>
                            <input 
                                type="tel" 
                                id="contactNumber" 
                                name="contactNumber" 
                                class="form-input" 
                                placeholder="+63 912 345 6789"
                                required
                            >
                        </div>
                    </div>
                </div>

                <!-- License & Registration Section -->
                <div class="form-section">
                    <h3 class="form-section-title">
                        <i class="fi fi-rr-document"></i>
                        License & Registration
                    </h3>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label for="licenseNumber" class="form-label">
                                License Number <span class="optional">(Optional)</span>
                            </label>
                            <input 
                                type="text" 
                                id="licenseNumber" 
                                name="licenseNumber" 
                                class="form-input" 
                                placeholder="Enter license number"
                            >
                        </div>

                        <div class="form-group">
                            <label for="philgepsRegistration" class="form-label">
                                PhilGEPS Registration <span class="optional">(Optional)</span>
                            </label>
                            <input 
                                type="text" 
                                id="philgepsRegistration" 
                                name="philgepsRegistration" 
                                class="form-input" 
                                placeholder="Enter PhilGEPS number"
                            >
                        </div>
                    </div>
                </div>

                <!-- Business Address Section -->
                <div class="form-section">
                    <h3 class="form-section-title">
                        <i class="fi fi-rr-marker"></i>
                        Business Address
                    </h3>
                    
                    <div class="form-group full-width">
                        <label for="businessAddress" class="form-label">
                            Complete Address <span class="required">*</span>
                        </label>
                        <textarea 
                            id="businessAddress" 
                            name="businessAddress" 
                            class="form-textarea" 
                            placeholder="Enter complete business address"
                            rows="3"
                            required
                        ></textarea>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label for="city" class="form-label">
                                City/Municipality <span class="required">*</span>
                            </label>
                            <input 
                                type="text" 
                                id="city" 
                                name="city" 
                                class="form-input" 
                                placeholder="Enter city/municipality"
                                required
                            >
                        </div>

                        <div class="form-group">
                            <label for="province" class="form-label">
                                Province <span class="required">*</span>
                            </label>
                            <input 
                                type="text" 
                                id="province" 
                                name="province" 
                                class="form-input" 
                                placeholder="Enter province"
                                required
                            >
                        </div>
                    </div>
                </div>
            </form>
        </div>

        <!-- Modal Footer -->
        <div class="edit-profile-modal-footer">
            <button type="button" class="edit-profile-btn cancel-btn" id="cancelEditProfileBtn">
                Cancel
            </button>
            <button type="button" class="edit-profile-btn save-btn" id="saveEditProfileBtn">
                <i class="fi fi-rr-check"></i>
                Save Changes
            </button>
        </div>
    </div>
</div>
