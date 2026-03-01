<!-- Edit Contractor Profile Modal -->
<div id="editContractorProfileModal" class="ecp-modal">
    <div class="ecp-modal-overlay" id="ecpModalOverlay"></div>
    <div class="ecp-modal-container">
        <!-- Header -->
        <div class="ecp-modal-header">
            <h2 class="ecp-modal-title">
                <i class="fi fi-rr-edit"></i>
                Edit Profile Information
            </h2>
            <button class="ecp-close-btn" id="ecpCloseBtn" aria-label="Close">
                <i class="fi fi-rr-cross"></i>
            </button>
        </div>

        <!-- Body -->
        <div class="ecp-modal-body">
            <form id="ecpForm" class="ecp-form" novalidate>
                <!-- Company Information -->
                <div class="ecp-section">
                    <h3 class="ecp-section-title"><i class="fi fi-rr-building"></i> Company Information</h3>

                    <div class="ecp-field full">
                        <label for="ecpCompanyName" class="ecp-label">Company Name <span class="ecp-req">*</span></label>
                        <input type="text" id="ecpCompanyName" name="company_name" class="ecp-input" placeholder="Enter company name" required>
                    </div>

                    <div class="ecp-row">
                        <div class="ecp-field">
                            <label for="ecpCompanyEmail" class="ecp-label">Company Email</label>
                            <input type="email" id="ecpCompanyEmail" name="company_email" class="ecp-input" placeholder="company@example.com">
                        </div>
                        <div class="ecp-field">
                            <label for="ecpCompanyPhone" class="ecp-label">Company Phone</label>
                            <input type="tel" id="ecpCompanyPhone" name="company_phone" class="ecp-input" placeholder="+63 912 345 6789">
                        </div>
                    </div>

                    <div class="ecp-row">
                        <div class="ecp-field">
                            <label for="ecpCompanyStartDate" class="ecp-label">Company Start Date</label>
                            <input type="date" id="ecpCompanyStartDate" name="company_start_date" class="ecp-input">
                        </div>
                    </div>

                    <div class="ecp-field full">
                        <label for="ecpServicesOffered" class="ecp-label">Services Offered</label>
                        <textarea id="ecpServicesOffered" name="services_offered" class="ecp-textarea" rows="3" placeholder="e.g., General Construction, Renovation, Plumbing"></textarea>
                        <span class="ecp-hint">Separate multiple services with commas</span>
                    </div>
                </div>

                <!-- Bio & Description -->
                <div class="ecp-section">
                    <h3 class="ecp-section-title"><i class="fi fi-rr-document-signed"></i> Bio & Description</h3>

                    <div class="ecp-field full">
                        <label for="ecpBio" class="ecp-label">Bio</label>
                        <textarea id="ecpBio" name="bio" class="ecp-textarea" rows="3" placeholder="Short bio about your company..."></textarea>
                    </div>

                    <div class="ecp-field full">
                        <label for="ecpCompanyDescription" class="ecp-label">Company Description</label>
                        <textarea id="ecpCompanyDescription" name="company_description" class="ecp-textarea" rows="4" placeholder="Detailed description of your company, mission, and services..."></textarea>
                    </div>
                </div>

                <!-- Address & Online Presence -->
                <div class="ecp-section">
                    <h3 class="ecp-section-title"><i class="fi fi-rr-marker"></i> Address & Online Presence</h3>

                    <div class="ecp-field full">
                        <label for="ecpBusinessAddress" class="ecp-label">Business Address</label>
                        <textarea id="ecpBusinessAddress" name="business_address" class="ecp-textarea" rows="2" placeholder="Complete business address"></textarea>
                    </div>

                    <div class="ecp-row">
                        <div class="ecp-field">
                            <label for="ecpWebsite" class="ecp-label">Website</label>
                            <input type="url" id="ecpWebsite" name="company_website" class="ecp-input" placeholder="https://yourcompany.com">
                        </div>
                        <div class="ecp-field">
                            <label for="ecpSocialMedia" class="ecp-label">Social Media</label>
                            <input type="url" id="ecpSocialMedia" name="company_social_media" class="ecp-input" placeholder="https://facebook.com/yourpage">
                        </div>
                    </div>
                </div>

                <!-- License & Registration -->
                <div class="ecp-section">
                    <h3 class="ecp-section-title"><i class="fi fi-rr-diploma"></i> License & Registration</h3>

                    <div class="ecp-row">
                        <div class="ecp-field">
                            <label for="ecpPicabNumber" class="ecp-label">PCAB License No.</label>
                            <input type="text" id="ecpPicabNumber" name="picab_number" class="ecp-input" placeholder="PCAB license number">
                        </div>
                        <div class="ecp-field">
                            <label for="ecpPicabCategory" class="ecp-label">PCAB Category</label>
                            <input type="text" id="ecpPicabCategory" name="picab_category" class="ecp-input" placeholder="e.g., AAA, AA, A">
                        </div>
                    </div>

                    <div class="ecp-row">
                        <div class="ecp-field">
                            <label for="ecpTinNumber" class="ecp-label">TIN / Business Reg No.</label>
                            <input type="text" id="ecpTinNumber" name="tin_business_reg_number" class="ecp-input" placeholder="TIN or DTI/SEC number">
                        </div>
                        <div class="ecp-field">
                            <label for="ecpBusinessPermit" class="ecp-label">Business Permit No.</label>
                            <input type="text" id="ecpBusinessPermit" name="business_permit_number" class="ecp-input" placeholder="Business permit number">
                        </div>
                    </div>

                    <div class="ecp-row">
                        <div class="ecp-field">
                            <label for="ecpPermitCity" class="ecp-label">Permit City</label>
                            <div class="ecp-combobox" id="ecpCityCombobox">
                                <input type="text" id="ecpPermitCityInput" class="ecp-input" placeholder="Type to search city…" autocomplete="off">
                                <input type="hidden" id="ecpPermitCity" name="business_permit_city">
                                <div class="ecp-combobox-dropdown" id="ecpCityDropdown"></div>
                            </div>
                        </div>
                        <div class="ecp-field">
                            <label for="ecpPermitExpiration" class="ecp-label">Permit Expiration</label>
                            <input type="date" id="ecpPermitExpiration" name="business_permit_expiration" class="ecp-input">
                        </div>
                    </div>
                </div>
            </form>
        </div>

        <!-- Footer -->
        <div class="ecp-modal-footer">
            <button type="button" class="ecp-btn ecp-btn--cancel" id="ecpCancelBtn">Cancel</button>
            <button type="button" class="ecp-btn ecp-btn--save" id="ecpSaveBtn">
                <div class="ecp-spinner"></div>
                <i class="fi fi-rr-check"></i>
                <span>Save Changes</span>
            </button>
        </div>
    </div>
</div>
