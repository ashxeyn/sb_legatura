<!-- Edit Profile Information Modal -->
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
                            placeholder="Enter your first name"
                            required
                        >
                    </div>

                    <div class="form-group">
                        <label for="middleName" class="form-label">
                            Middle Name <span class="optional">(Optional)</span>
                        </label>
                        <input 
                            type="text" 
                            id="middleName" 
                            name="middleName" 
                            class="form-input" 
                            placeholder="Enter your middle name"
                        >
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="lastName" class="form-label">
                            Last Name <span class="required">*</span>
                        </label>
                        <input 
                            type="text" 
                            id="lastName" 
                            name="lastName" 
                            class="form-input" 
                            placeholder="Enter your last name"
                            required
                        >
                    </div>

                    <div class="form-group">
                        <label for="occupation" class="form-label">
                            Occupation <span class="required">*</span>
                        </label>
                        <input 
                            type="text" 
                            id="occupation" 
                            name="occupation" 
                            class="form-input" 
                            placeholder="Enter your occupation"
                            required
                        >
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="gender" class="form-label">
                            Gender <span class="required">*</span>
                        </label>
                        <select id="gender" name="gender" class="form-select" required>
                            <option value="">Select gender</option>
                            <option value="male">Male</option>
                            <option value="female">Female</option>
                            <option value="other">Other</option>
                            <option value="prefer_not_to_say">Prefer not to say</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="dateOfBirth" class="form-label">
                            Date of Birth <span class="required">*</span>
                        </label>
                        <input 
                            type="date" 
                            id="dateOfBirth" 
                            name="dateOfBirth" 
                            class="form-input" 
                            required
                        >
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
