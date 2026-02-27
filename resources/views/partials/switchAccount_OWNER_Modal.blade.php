<!-- Switch to Property Owner Account Form Modal -->
<div id="switchToOwnerModal" class="switch-account-modal hidden">
    <div class="modal-overlay" id="switchToOwnerModalOverlay"></div>
    <div class="switch-account-modal-container max-w-2xl">
        <!-- Back Button -->
        <button type="button" class="back-button" id="backToSwitchAccountFromOwnerBtn">
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
            <div id="ownerFormInfoBox" class="form-info-box">
                <div class="info-box-icon">
                    <span class="info-number">i</span>
                </div>
                <p class="info-box-text">Pre-filled from your Contractor account</p>
            </div>

            @php
                use Illuminate\Support\Facades\DB;
                use App\Models\accounts\accountClass;

                // Merge owner_step1 and switch_owner_step1 so partial data from either step is available.
                $ownerStep = (array) session('owner_step1', []);
                $switchStep = (array) session('switch_owner_step1', []);
                $sessionStep = array_merge($ownerStep, $switchStep);

                $userObj = session('user') ?: auth()->user();
                $existingOwner = null;
                if (!empty($userObj)) {
                    $userId = is_object($userObj) ? ($userObj->user_id ?? $userObj->id ?? null) : ($userObj['user_id'] ?? null);
                    if ($userId) {
                        $existingOwner = DB::table('property_owners')->where('user_id', $userId)->first();
                        // Also fetch contractor_user authorized rep fields for contractor accounts
                        $contractorUser = DB::table('contractor_users')->where('user_id', $userId)->first();
                    }
                }

                $pref = [];
                // Basic name/phone fallbacks: prefer session -> existing owner -> contractor authorized rep -> user
                $pref['first_name'] = old('first_name', $sessionStep['first_name'] ?? data_get($existingOwner, 'first_name') ?? data_get($contractorUser, 'authorized_rep_fname') ?? data_get($userObj, 'first_name') ?? '');
                $pref['middle_name'] = old('middle_name', $sessionStep['middle_name'] ?? data_get($existingOwner, 'middle_name') ?? data_get($contractorUser, 'authorized_rep_mname') ?? data_get($userObj, 'middle_name') ?? '');
                $pref['last_name'] = old('last_name', $sessionStep['last_name'] ?? data_get($existingOwner, 'last_name') ?? data_get($contractorUser, 'authorized_rep_lname') ?? data_get($userObj, 'last_name') ?? '');
                $pref['date_of_birth'] = old('date_of_birth', $sessionStep['date_of_birth'] ?? data_get($existingOwner, 'date_of_birth') ?? '');
                $pref['phone_number'] = old('phone_number', $sessionStep['phone_number'] ?? data_get($existingOwner, 'phone_number') ?? data_get($contractorUser, 'phone_number') ?? data_get($userObj, 'phone_number') ?? '');
                $pref['occupation_id'] = old('occupation_id', $sessionStep['occupation_id'] ?? data_get($existingOwner, 'occupation_id') ?? null);
                $pref['occupation_other'] = old('occupation_other', $sessionStep['occupation_other'] ?? data_get($existingOwner, 'occupation_other') ?? null);

                // Address: session owner_step1 stores 'address' as "street, barangay, city, province, postal"
                $addressRaw = $sessionStep['address'] ?? data_get($existingOwner, 'address') ?? null;
                $addrParts = [];
                if (!empty($addressRaw) && is_string($addressRaw)) {
                    $parts = array_map('trim', explode(',', $addressRaw));
                    // assign parts safely
                    $addrParts['street'] = $parts[0] ?? '';
                    $addrParts['barangay'] = $parts[1] ?? '';
                    $addrParts['city'] = $parts[2] ?? '';
                    $addrParts['province'] = $parts[3] ?? '';
                    $addrParts['postal'] = $parts[4] ?? '';
                }

                $pref['address_street'] = old('address_street', $sessionStep['owner_address_street'] ?? $addrParts['street'] ?? '');
                $pref['address_postal'] = old('address_postal', $sessionStep['owner_address_postal'] ?? $addrParts['postal'] ?? '');
                $pref['province'] = old('address_province', $sessionStep['owner_address_province'] ?? $addrParts['province'] ?? data_get($existingOwner, 'province') ?? null);
                $pref['city'] = old('address_city', $sessionStep['owner_address_city'] ?? $addrParts['city'] ?? data_get($existingOwner, 'city') ?? null);
                $pref['barangay'] = old('address_barangay', $sessionStep['owner_address_barangay'] ?? $addrParts['barangay'] ?? data_get($existingOwner, 'barangay') ?? null);

                $pref['username'] = old('username', $sessionStep['username'] ?? data_get($userObj, 'username') ?? '');
                $pref['email'] = old('email', $sessionStep['email'] ?? data_get($userObj, 'email') ?? '');

                $accountSvc = new accountClass();
                $occupations = $accountSvc->getOccupations();
            @endphp

            <form id="ownerAccountForm" class="owner-account-form" action="/accounts/signup/owner/step1" method="POST">
                @csrf
                <!-- Personal Details Section -->
                <div class="form-section">
                    <!-- First Name -->
                    <div class="form-group">
                        <label for="ownerFirstName" class="form-label">
                            First Name <span class="required">*</span>
                        </label>
                        <input type="text" id="ownerFirstName" name="first_name" class="form-input"
                            placeholder="Enter first name" value="{{ data_get($pref, 'first_name', '') }}" required>
                        <span class="validation-error" id="error_first_name"></span>
                    </div>

                    <!-- Middle Name -->
                    <div class="form-group">
                        <label for="ownerMiddleName" class="form-label">
                            Middle Name
                        </label>
                        <input type="text" id="ownerMiddleName" name="middle_name" class="form-input"
                            placeholder="Enter middle name" value="{{ data_get($pref, 'middle_name', '') }}">
                        <span class="validation-error" id="error_middle_name"></span>
                    </div>

                    <!-- Last Name -->
                    <div class="form-group">
                        <label for="ownerLastName" class="form-label">
                            Last Name <span class="required">*</span>
                        </label>
                        <input type="text" id="ownerLastName" name="last_name" class="form-input"
                            placeholder="Enter last name" value="{{ data_get($pref, 'last_name', '') }}" required>
                        <span class="validation-error" id="error_last_name"></span>
                    </div>

                    <!-- Date of Birth -->
                    <div class="form-group">
                        <label for="ownerDOB" class="form-label">
                            Date of Birth <span class="required">*</span>
                        </label>
                        <input type="date" id="ownerDOB" name="date_of_birth" class="form-input"
                            value="{{ data_get($pref, 'date_of_birth', '') }}" required>
                        <span class="validation-error" id="error_date_of_birth"></span>
                    </div>

                    <!-- Phone Number -->
                    <div class="form-group">
                        <label for="ownerPhone" class="form-label">
                            Phone Number <span class="required">*</span>
                        </label>
                        <input type="tel" id="ownerPhone" name="phone_number" class="form-input"
                            placeholder="09171234567" value="{{ data_get($pref, 'phone_number', '') }}" required
                            pattern="09[0-9]{9}">
                        <span class="validation-error" id="error_phone_number"></span>
                    </div>

                    <!-- Occupation -->
                    <div class="form-group">
                        <label for="ownerOccupation" class="form-label">
                            Occupation <span class="required">*</span>
                        </label>
                        <div class="select-wrapper">
                            <select id="ownerOccupation" name="occupation_id" class="form-select" required>
                                <option value="" disabled {{ data_get($pref, 'occupation_id') ? '' : 'selected' }}>Select
                                    Occupation</option>
                                @foreach($occupations as $occ)
                                    <option value="{{ $occ->id }}" {{ (string) (data_get($pref, 'occupation_id', '')) === (string) ($occ->id ?? '') ? 'selected' : '' }}>{{ $occ->occupation_name ?? $occ->name ?? $occ->label }}</option>
                                @endforeach
                                <option value="other" {{ data_get($pref, 'occupation_id') === 'other' ? 'selected' : '' }}>
                                    Other</option>
                            </select>
                            <i class="fi fi-rr-angle-down select-icon"></i>
                        </div>
                        <input type="text" id="ownerOccupationOther" name="occupation_other_text"
                            class="form-input {{ data_get($pref, 'occupation_other') ? '' : 'hidden' }}"
                            placeholder="Please specify" value="{{ data_get($pref, 'occupation_other', '') }}">
                        <span class="validation-error" id="error_occupation_id"></span>
                        <span class="validation-error" id="error_occupation_other_text"></span>
                    </div>

                    <!-- Province -->
                    <div class="form-group">
                        <label for="ownerProvince" class="form-label">
                            Province <span class="required">*</span>
                        </label>
                        <div class="select-wrapper">
                            <select id="ownerProvince" name="owner_address_province" class="form-select" required>
                                <option value="" disabled {{ data_get($pref, 'province') ? '' : 'selected' }}>Select
                                    Province</option>
                                @php $psgc = new \App\Services\psgcApiService();
                                $provinces = $psgc->getProvinces(); @endphp
                                @foreach($provinces as $p)
                                    @php $pcode = data_get($p, 'code');
                                    $pname = data_get($p, 'name'); @endphp
                                    <option value="{{ $pcode }}" {{ data_get($pref, 'province', '') == $pcode ? 'selected' : '' }}>{{ $pname }}</option>
                                @endforeach
                            </select>
                            <i class="fi fi-rr-angle-down select-icon"></i>
                        </div>
                        <div class="select-wrapper mt-2">
                            @php $cities = data_get($pref, 'province') ? $psgc->getCitiesByProvince(data_get($pref, 'province')) : []; @endphp
                            <select id="ownerCity" name="owner_address_city" class="form-select" required>
                                <option value="" disabled {{ data_get($pref, 'city') ? '' : 'selected' }}>Select City
                                </option>
                                @foreach($cities as $c)
                                    @php $ccode = data_get($c, 'code');
                                    $cname = data_get($c, 'name'); @endphp
                                    <option value="{{ $ccode }}" {{ data_get($pref, 'city', '') == $ccode ? 'selected' : '' }}>
                                        {{ $cname }}</option>
                                @endforeach
                            </select>
                            <i class="fi fi-rr-angle-down select-icon"></i>
                        </div>
                        <div class="select-wrapper mt-2">
                            @php $barangays = data_get($pref, 'city') ? $psgc->getBarangaysByCity(data_get($pref, 'city')) : []; @endphp
                            <select id="ownerBarangay" name="owner_address_barangay" class="form-select" required>
                                <option value="" disabled {{ data_get($pref, 'barangay') ? '' : 'selected' }}>Select
                                    Barangay</option>
                                @foreach($barangays as $b)
                                    @php $bcode = data_get($b, 'code');
                                    $bname = data_get($b, 'name'); @endphp
                                    <option value="{{ $bcode }}" {{ data_get($pref, 'barangay', '') == $bcode ? 'selected' : '' }}>{{ $bname }}</option>
                                @endforeach
                            </select>
                            <i class="fi fi-rr-angle-down select-icon"></i>
                        </div>
                        <div class="form-group mt-2">
                            <label for="ownerAddressStreet" class="form-label">Street Address <span
                                    class="required">*</span></label>
                            <input type="text" id="ownerAddressStreet" name="owner_address_street" class="form-input"
                                required placeholder="Street / Building / House No."
                                value="{{ data_get($pref, 'address_street', '') }}">
                        </div>
                        <div class="form-group mt-2">
                            <label for="ownerPostal" class="form-label">Postal Code <span
                                    class="required">*</span></label>
                            <input type="text" id="ownerPostal" name="owner_address_postal" class="form-input" required
                                placeholder="Postal Code" value="{{ data_get($pref, 'address_postal', '') }}">
                        </div>
                        <span class="validation-error" id="error_owner_address_province"></span>
                        <span class="validation-error" id="error_owner_address_city"></span>
                        <span class="validation-error" id="error_owner_address_barangay"></span>
                        <span class="validation-error" id="error_owner_address_street"></span>
                        <span class="validation-error" id="error_owner_address_postal"></span>
                        <span class="validation-error" id="error_address"></span>
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
            <p class="owner-form-subtitle">Step 2: Manage your login credentials</p>
        </div>

        <!-- Modal Body -->
        <div class="switch-account-modal-body">
            <form id="accountCredentialsForm" class="owner-account-form" action="/accounts/switch/owner/step1"
                method="POST">
                @csrf
                <!-- Credentials Section -->
                <div class="form-section">
                    <!-- Username -->
                    <div class="form-group">
                        <label for="ownerUsername" class="form-label">
                            Username <span class="required">*</span>
                        </label>
                        <input type="text" id="ownerUsername" name="username" class="form-input"
                            placeholder="Enter username" value="{{ data_get($pref, 'username', '') }}" required>
                        <span class="validation-error" id="error_username"></span>
                    </div>

                    <!-- Email -->
                    <div class="form-group">
                        <label for="ownerEmail" class="form-label">
                            Email <span class="required">*</span>
                        </label>
                        <input type="email" id="ownerEmail" name="email" class="form-input" placeholder="Enter email"
                            value="{{ data_get($pref, 'email', '') }}" required>
                        <span class="validation-error" id="error_email"></span>
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

<!-- Step 3: Identity Verification -->
<div id="identityVerificationOwnerForm" class="switch-account-modal hidden">
    <div class="modal-overlay" id="identityVerificationOverlay"></div>
    <div class="switch-account-modal-container max-w-2xl">
        <!-- Back Button -->
        <button type="button" class="back-button" id="backToAccountSetupBtn">
            <i class="fi fi-rr-arrow-left"></i>
            <span>Back</span>
        </button>
        <div class="switch-owner-header">
            <div class="owner-form-icon">
                <img src="{{ asset('img/logo2.0.svg') }}" alt="Legatura" class="owner-logo">
            </div>
            <h2 class="owner-form-title">Identity Verification</h2>
            <p class="owner-form-subtitle">Step 3: Verify your identity</p>
        </div>
        <div class="switch-account-modal-body">
            <form id="identityVerificationForm" class="owner-account-form" action="/accounts/switch/owner/step2" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="form-section">
                    <!-- Valid ID Type -->
                    <div class="form-group">
                        <label for="ownerValidIdType" class="form-label">Valid ID Type <span class="required">*</span></label>
                        <select id="ownerValidIdType" name="valid_id_id" class="form-select" required>
                            <option value="">Select ID Type</option>
                            <!-- Dropdown options populated by JS -->
                        </select>
                        <span class="validation-error" id="error_valid_id_id"></span>
                    </div>

                    <!-- ID Photos Grid -->
                    <div class="documents-grid" style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; margin-top: 1rem;">
                        <!-- Front Photo -->
                        <div class="form-group">
                            <label class="form-label">ID Front Photo <span class="required">*</span></label>
                            <div class="file-upload-box" id="frontPhotoBox">
                                <input type="file" id="ownerValidIdFront" name="valid_id_photo" accept="image/*" class="file-input hidden" required>
                                <label for="ownerValidIdFront" class="file-upload-label">
                                    <i class="fi fi-rr-camera"></i>
                                    <span class="file-status">Click to upload</span>
                                </label>
                            </div>
                            <span class="validation-error" id="error_valid_id_photo"></span>
                        </div>
                        <!-- Back Photo -->
                        <div class="form-group">
                            <label class="form-label">ID Back Photo <span class="required">*</span></label>
                            <div class="file-upload-box" id="backPhotoBox">
                                <input type="file" id="ownerValidIdBack" name="valid_id_back_photo" accept="image/*" class="file-input hidden" required>
                                <label for="ownerValidIdBack" class="file-upload-label">
                                    <i class="fi fi-rr-camera"></i>
                                    <span class="file-status">Click to upload</span>
                                </label>
                            </div>
                            <span class="validation-error" id="error_valid_id_back_photo"></span>
                        </div>
                    </div>

                    <!-- Police Clearance -->
                    <div class="form-group" style="margin-top: 1rem;">
                        <label class="form-label">Police Clearance <span class="required">*</span></label>
                        <div class="file-upload-box full-width" id="policeClearanceBox">
                            <input type="file" id="ownerPoliceClearance" name="police_clearance" accept="image/*" class="file-input hidden" required>
                            <label for="ownerPoliceClearance" class="file-upload-label">
                                <i class="fi fi-rr-document"></i>
                                <span class="file-status">Click to upload Police Clearance</span>
                            </label>
                        </div>
                        <span class="validation-error" id="error_police_clearance"></span>
                    </div>
                </div>

                <div class="form-actions">
                    <button type="button" class="owner-form-submit-btn" id="identityVerificationNextBtn">
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
        <button type="button" class="back-button" id="backToIdentityVerificationBtn">
            <i class="fi fi-rr-arrow-left"></i>
            <span>Back</span>
        </button>

        <!-- Modal Header with Icon -->
        <div class="switch-owner-header">
            <div class="owner-form-icon">
                <img src="{{ asset('img/logo2.0.svg') }}" alt="Legatura" class="owner-logo">
            </div>
            <h2 class="owner-form-title">Profile Picture</h2>
            <p class="owner-form-subtitle">Step 4: Finalize your profile</p>
        </div>

        <!-- Modal Body -->
        <div class="switch-account-modal-body">
            <form id="ownerProfilePictureForm" class="owner-account-form" action="/accounts/switch/owner/final"
                method="POST" enctype="multipart/form-data">
                @csrf
                <!-- Profile Picture Upload Section -->
                <div class="form-section profile-picture-section">
                    <div class="profile-picture-upload-container">
                        <div class="profile-picture-circle" id="ownerProfilePictureCircle">
                            <input type="file" id="ownerProfilePictureInput" name="profile_picture" accept="image/*"
                                class="profile-picture-input">
                            <label for="ownerProfilePictureInput" class="profile-picture-label">
                                <div class="profile-picture-placeholder" id="ownerProfilePicturePlaceholder">
                                    <i class="fi fi-rr-user"></i>
                                </div>
                                <img id="ownerProfilePicturePreview" class="profile-picture-preview"
                                    style="display: none;" alt="Profile Preview">
                            </label>
                        </div>
                        <p class="upload-photo-text">Upload Photo (Optional)</p>
                        <span class="validation-error" id="error_profile_picture"></span>
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

        <!-- Role Switch Summary Container -->
        <div id="ownerSwitchSummary" class="role-switch-summary">
            <!-- Populated via JavaScript -->
        </div>

        <p class="confirmation-message">You are about to add the Property Owner role. You can switch roles anytime from
            settings.</p>
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
