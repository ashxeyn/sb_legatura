/**
 * Switch to Property Owner Account Form Modal JavaScript
 * Handles the property owner account creation form functionality
 */

// Owner Form Modal JavaScript
document.addEventListener('DOMContentLoaded', function () {
    const switchToOwnerModal = document.getElementById('switchToOwnerModal');
    const switchAccountModal = document.getElementById('switchAccountModal');
    const switchToOwnerModalOverlay = document.getElementById('switchToOwnerModalOverlay');
    const backToSwitchAccountFromOwnerBtn = document.getElementById('backToSwitchAccountFromOwnerBtn');
    const ownerAccountForm = document.getElementById('ownerAccountForm');
    const personalInfoNextBtn = document.getElementById('personalInfoNextBtn');
    const accountSetupForm = document.getElementById('accountSetupForm');
    const accountSetupOverlay = document.getElementById('accountSetupOverlay');
    const backToPersonalInfoBtn = document.getElementById('backToPersonalInfoBtn');
    const accountCredentialsForm = document.getElementById('accountCredentialsForm');
    const accountSetupNextBtn = document.getElementById('accountSetupNextBtn');

    // Step 3: Identity Verification
    const identityVerificationOwnerForm = document.getElementById('identityVerificationOwnerForm');
    const identityVerificationOverlay = document.getElementById('identityVerificationOverlay');
    const backToAccountSetupBtn = document.getElementById('backToAccountSetupBtn');
    const identityVerificationForm = document.getElementById('identityVerificationForm');
    const identityVerificationNextBtn = document.getElementById('identityVerificationNextBtn');
    const ownerValidIdType = document.getElementById('ownerValidIdType');

    // Step 4: Profile Picture
    const profilePictureOwnerForm = document.getElementById('profilePictureOwnerForm');
    const profilePictureOwnerOverlay = document.getElementById('profilePictureOwnerOverlay');
    const backToIdentityVerificationBtn = document.getElementById('backToIdentityVerificationBtn');
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
    const ownerProvince = document.getElementById('ownerProvince');
    const ownerCity = document.getElementById('ownerCity');
    const ownerBarangay = document.getElementById('ownerBarangay');
    const ownerOccupation = document.getElementById('ownerOccupation');
    const ownerOccupationOther = document.getElementById('ownerOccupationOther');
    const pendingContractorOwnerModal = document.getElementById('pendingContractorOwnerModal');
    const pendingContractorOwnerOverlay = document.getElementById('pendingContractorOwnerOverlay');
    const pendingOwnerCloseBtn = document.getElementById('pendingOwnerCloseBtn');

    // Normalize occupation options: remove any 'Other' option, keep only 'Others'
    if (ownerOccupation) {
        // preserve current selection
        const selValue = ownerOccupation.value;
        // remove options whose text is exactly 'Other' (case-insensitive)
        Array.from(ownerOccupation.options).forEach(opt => {
            if ((opt.text || '').toLowerCase().trim() === 'other') {
                ownerOccupation.remove(opt.index);
            }
        });
        // ensure a single 'Others' option exists; if none, add it
        const hasOthers = Array.from(ownerOccupation.options).some(o => (o.text || '').toLowerCase().trim() === 'others');
        if (!hasOthers) {
            const otherOpt = document.createElement('option');
            otherOpt.value = 'others';
            otherOpt.textContent = 'Others';
            ownerOccupation.appendChild(otherOpt);
        }
        // restore selection if present
        if (selValue) ownerOccupation.value = selValue;
    }

    // Load dropdown data (Occupations and ID Types)
    async function loadDropdownData() {
        try {
            const resp = await fetch('/api/signup-form?user_type=owner', {
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                }
            });
            const json = await resp.json();
            if (json.success) {
                // The API response might be { success: true, data: { occupations: [], valid_ids: [] } }
                // or { success: true, data: { data: { occupations: [], valid_ids: [] } } } depending on wrapper
                const data = json.data?.data || json.data || {};

                // Populate occupations only when the select is empty (don't overwrite server-rendered options)
                const occupationSelect = document.getElementById('ownerOccupation');
                if (occupationSelect && data.occupations) {
                    if (occupationSelect.options.length <= 1) {
                        populateSelect(occupationSelect, data.occupations, 'Select Occupation');
                    }
                }
                // Populate ID Types
                if (ownerValidIdType && data.valid_ids) {
                    // Do not auto-select any ID type; let the user choose explicitly
                    populateSelect(ownerValidIdType, data.valid_ids, 'Select ID Type', false);
                }
            }
        } catch (err) {
            console.error('Failed to load dropdown data:', err);
        }
    }

    loadDropdownData();

    function calculateAge(birthday) {
        if (!birthday) return 0;
        const birthDate = new Date(birthday);
        const today = new Date();
        let age = today.getFullYear() - birthDate.getFullYear();
        const m = today.getMonth() - birthDate.getMonth();
        if (m < 0 || (m === 0 && today.getDate() < birthDate.getDate())) {
            age--;
        }
        return age;
    }

    function clearErrors() {
        document.querySelectorAll('.owner-account-form .error-msg').forEach(msg => {
            msg.textContent = '';
            msg.classList.remove('active');
        });
    }

    function displayErrors(errors) {
        clearErrors();
        if (!errors) return;

        if (typeof errors === 'string') {
            // Generic message (perhaps for something not specific to a field)
            const genericMsg = document.getElementById('error_generic');
            if (genericMsg) {
                genericMsg.textContent = errors;
                genericMsg.classList.add('active');
            } else {
                alert(errors);
            }
            return;
        }

        // Object of errors (field => [messages])
        for (const [field, messages] of Object.entries(errors)) {
            const errorSpan = document.getElementById(`error_${field}`);
            if (errorSpan) {
                errorSpan.textContent = Array.isArray(messages) ? messages[0] : messages;
                errorSpan.classList.add('active');
            }
        }
    }

    // Help resolve text to value for selects
    function setSelectByText(selectId, text) {
        const select = document.getElementById(selectId);
        if (!select || !text) return false;
        const normalizedTarget = text.toLowerCase().trim();
        for (let i = 0; i < select.options.length; i++) {
            const optionText = select.options[i].text.toLowerCase().trim();
            if (optionText === normalizedTarget || optionText.includes(normalizedTarget) || normalizedTarget.includes(optionText)) {
                select.selectedIndex = i;
                return select.options[i].value;
            }
        }
        return false;
    }

    // Prefill form from existing data
    async function prefillForm() {
        try {
            const resp = await fetch('/accounts/switch', {
                headers: { 'X-Requested-With': 'XMLHttpRequest' }
            });
            const json = await resp.json();
            if (json.success && json.existing_data) {
                const ed = json.existing_data;
                const user = ed.user || {};
                const contractorUser = ed.contractor_user || {};
                const contractor = ed.contractor || {};

                // Map Contractor User info to Owner Personal Info
                if (document.getElementById('ownerFirstName')) {
                    document.getElementById('ownerFirstName').value = contractorUser.authorized_rep_fname || user.first_name || '';
                }
                if (document.getElementById('ownerMiddleName')) {
                    document.getElementById('ownerMiddleName').value = contractorUser.authorized_rep_mname || user.middle_name || '';
                }
                if (document.getElementById('ownerLastName')) {
                    document.getElementById('ownerLastName').value = contractorUser.authorized_rep_lname || user.last_name || '';
                }
                if (document.getElementById('ownerPhone')) {
                    // Try contractorUser.phone_number first as it's the specific verified contact
                    document.getElementById('ownerPhone').value = contractorUser.phone_number || user.phone_number || '';
                }
                if (document.getElementById('ownerDOB')) {
                    document.getElementById('ownerDOB').value = user.date_of_birth || '';
                }

                // Username and Email
                if (document.getElementById('ownerUsername')) {
                    document.getElementById('ownerUsername').value = user.username || '';
                }
                if (document.getElementById('ownerEmail')) {
                    document.getElementById('ownerEmail').value = user.email || '';
                }

                // Address logic
                if (contractor.business_address) {
                    const addr = contractor.business_address;
                    const parts = addr.split(',').map(s => s.trim());

                    if (parts.length >= 4) {
                        const street = parts[0];
                        const brgyName = parts[1];
                        const cityName = parts[2];
                        let provName = '';
                        let postal = '';

                        // Handle "Province PostalCode" concatenation or separate parts
                        const lastPart = parts[3];
                        const postalMatch = lastPart.match(/(.*)\s+(\d{4,})$/);
                        if (postalMatch) {
                            provName = postalMatch[1].trim();
                            postal = postalMatch[2].trim();
                        } else {
                            provName = lastPart;
                            // Check if there is a 5th part for postal code
                            if (parts[4]) postal = parts[4];
                        }

                        if (document.getElementById('ownerAddressStreet')) {
                            document.getElementById('ownerAddressStreet').value = street;
                        }
                        if (document.getElementById('ownerPostal')) {
                            document.getElementById('ownerPostal').value = postal;
                        }

                        // Synchronize PSGC Dropdowns
                        if (provName) {
                            const provCode = setSelectByText('ownerProvince', provName);
                            if (provCode) {
                                await loadCities(provCode);
                                const cityCode = setSelectByText('ownerCity', cityName);
                                if (cityCode) {
                                    await loadBarangays(cityCode);
                                    setSelectByText('ownerBarangay', brgyName);
                                }
                            }
                        }
                    }
                }
            }
        } catch (err) {
            console.error('Failed to prefill form:', err);
        }
    }

    prefillForm();

    // Lightweight toast helper (fallback if app doesn't provide one)
    function showToast(message, type = 'info') {
        try {
            const toast = document.createElement('div');
            toast.className = 'la-toast la-toast-' + type;
            toast.textContent = message;
            Object.assign(toast.style, {
                position: 'fixed',
                right: '20px',
                top: '20px',
                background: type === 'success' ? '#16a34a' : '#333',
                color: '#fff',
                padding: '10px 14px',
                borderRadius: '6px',
                boxShadow: '0 6px 18px rgba(0,0,0,0.12)',
                zIndex: 99999,
                opacity: '1',
                transition: 'opacity 0.3s ease'
            });
            document.body.appendChild(toast);
            setTimeout(() => { toast.style.opacity = '0'; setTimeout(() => toast.remove(), 300); }, 3000);
        } catch (e) { console.warn('toast failed', e); }
    }

    // Mapping for file uploads in Step 3
    ['ownerValidIdFront', 'ownerValidIdBack', 'ownerPoliceClearance'].forEach(id => {
        const input = document.getElementById(id);
        if (input) {
            input.addEventListener('change', function (e) {
                const fileName = e.target.files[0] ? e.target.files[0].name : 'Click to upload';
                const label = this.nextElementSibling;
                const statusSpan = label.querySelector('.file-status');
                if (statusSpan) {
                    statusSpan.textContent = fileName;
                    label.closest('.file-upload-box').classList.add('has-file');
                }
            });
        }
    });

    // Handle clicking "Switch to Property Owner" button from main switch account modal
    const switchToOwnerBtns = document.querySelectorAll('[data-target="owner"]');
    switchToOwnerBtns.forEach(btn => {
        btn.addEventListener('click', async function (e) {
            e.preventDefault();
            e.stopPropagation();
            if (e.stopImmediatePropagation) e.stopImmediatePropagation();

            console.log('[switchAccount_OWNER] owner button clicked');
            // Try mobile-style: attempt role switch via API immediately and let server decide
            try {
                const trySwitch = await fetch('/accounts/switch-role', {
                    credentials: 'same-origin',
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': (document.querySelector('meta[name="csrf-token"]') || {}).content || '',
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    body: JSON.stringify({ role: 'owner' })
                });

                // If server responded with redirect to login (unauthenticated), follow that
                if (trySwitch.status === 401) {
                    try {
                        const txt = await trySwitch.text();
                        console.warn('[switchAccount_OWNER] /api/role/switch 401:', txt.slice ? txt.slice(0, 200) : txt);
                    } catch (e) { }
                    // Let the server handle redirect; client should navigate to login
                    window.location.href = '/accounts/login';
                    return;
                }

                let switchJson = null;
                try { switchJson = await trySwitch.json(); } catch (e) { /* non-json response */ }

                if (trySwitch.ok && switchJson && switchJson.success) {
                    // Successful switch — redirect to provided URL or fallback
                    showToast(switchJson.message || 'Switching to Property Owner...', 'success');
                    setTimeout(() => {
                        window.location.href = switchJson.redirect_url || '/owner/homepage';
                    }, 600);
                    return;
                }

                // If switch failed with a message indicating pending approval, show pending modal
                if (trySwitch.status === 403 && switchJson && /pending/i.test(switchJson.message || '')) {
                    if (switchAccountModal) switchAccountModal.classList.add('hidden');
                    if (pendingContractorOwnerModal) {
                        pendingContractorOwnerModal.classList.remove('hidden');
                        pendingContractorOwnerModal.classList.add('active');
                        document.body.style.overflow = 'hidden';
                    }
                    return;
                }

                // If the API returned an unauthenticated-ish payload with redirect_url, follow it
                if (switchJson && switchJson.redirect_url && trySwitch.status === 401) {
                    window.location.href = switchJson.redirect_url;
                    return;
                }

                // Otherwise, fall back to asking the /accounts/switch form for prefill and decision
                console.log('[switchAccount_OWNER] /api/role/switch did not complete — falling back to /accounts/switch');

                const resp = await fetch('/accounts/switch', {
                    credentials: 'same-origin',
                    headers: { 'X-Requested-With': 'XMLHttpRequest' }
                });

                if (!resp.ok) {
                    const text = await resp.text();
                    console.warn('[switchAccount_OWNER] /accounts/switch returned non-OK', resp.status, text.slice ? text.slice(0, 200) : text);
                    // Fallback: prefill then show owner signup form
                    try { await prefillForm(); } catch (e) { console.warn('prefillForm failed on non-OK /accounts/switch', e); }
                    if (switchAccountModal) switchAccountModal.classList.add('hidden');
                    if (switchToOwnerModal) {
                        switchToOwnerModal.classList.remove('hidden');
                        document.body.style.overflow = 'hidden';
                    }
                    return;
                }

                let json;
                try {
                    json = await resp.json();
                } catch (parseErr) {
                    const text = await resp.text();
                    console.error('[switchAccount_OWNER] failed to parse JSON from /accounts/switch', parseErr, text.slice ? text.slice(0, 200) : text);
                    // Fallback to showing signup modal
                    if (switchAccountModal) switchAccountModal.classList.add('hidden');
                    if (switchToOwnerModal) {
                        switchToOwnerModal.classList.remove('hidden');
                        document.body.style.overflow = 'hidden';
                    }
                    return;
                }

                console.log('[switchAccount_OWNER] /accounts/switch response', json);
                try {
                    console.log('[switchAccount_OWNER] /accounts/switch response (stringified)', JSON.stringify(json));
                } catch (e) {
                    console.log('[switchAccount_OWNER] /accounts/switch response keys', Object.keys(json || {}));
                }

                // If the user already has an approved owner account, perform role switch.
                // Accept either an active approved owner or an approved owner that may be inactive
                // (e.g. approved but is_active = 0 for users with user_type='both'). Also accept
                // responses that include a `redirect_url` pointing to owner routes.
                const approvedOwner = (json.is_approved_owner === 1 || json.is_approved_owner === true);
                const activeOwner = (json.is_active_owner === 1 || json.is_active_owner === true);
                const hasOwnerRedirect = typeof json.redirect_url === 'string' && json.redirect_url.includes('/owner');

                if (approvedOwner && (activeOwner || hasOwnerRedirect || json.user_type === 'both' || json.users_user_type === 'both')) {
                    // Send switch request and prefer server-provided redirect_url when available
                    try {
                        const switchResp = await fetch('/accounts/switch-role', {
                            credentials: 'same-origin',
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '',
                                'X-Requested-With': 'XMLHttpRequest'
                            },
                            body: JSON.stringify({ role: 'owner' })
                        });
                        let switchJson = {};
                        try { switchJson = await switchResp.json(); } catch (e) { /* ignore parse issues */ }
                        showToast((switchJson && switchJson.message) || json.message || 'Switching to Property Owner...', 'success');
                        setTimeout(() => {
                            // priority: switchJson.redirect_url > json.redirect_url > fallback
                            window.location.href = (switchJson && switchJson.redirect_url) || json.redirect_url || '/owner/homepage';
                        }, 800);
                        return;
                    } catch (err) {
                        console.error('Role switch error:', err);
                        showToast('An error occurred during role switch', 'error');
                        return;
                    }
                }

                // If user has a pending owner profile, show the pending modal instead of signup form
                if (json.is_pending_owner === 1 || json.is_pending_owner === true) {
                    console.log('[switchAccount_OWNER] user is pending owner, attempting to show pending modal');
                    try {
                        if (switchAccountModal) switchAccountModal.classList.add('hidden');
                        console.log('[switchAccount_OWNER] pendingContractorOwnerModal element:', pendingContractorOwnerModal);
                        if (pendingContractorOwnerModal) {
                            pendingContractorOwnerModal.classList.remove('hidden');
                            // also add active to ensure modal styling shows
                            pendingContractorOwnerModal.classList.add('active');
                            document.body.style.overflow = 'hidden';
                            console.log('[switchAccount_OWNER] pending modal shown');
                        } else {
                            console.warn('[switchAccount_OWNER] pendingContractorOwnerModal not found in DOM');
                        }
                    } catch (errShow) {
                        console.error('[switchAccount_OWNER] error showing pending modal', errShow);
                    }
                    return;
                }

                // Otherwise, show the owner signup/profile form modal so the user can create or complete it
                if (switchAccountModal) {
                    switchAccountModal.classList.add('hidden');
                }
                try { await prefillForm(); } catch (e) { console.warn('prefillForm failed before showing owner modal', e); }
                if (switchToOwnerModal) {
                    switchToOwnerModal.classList.remove('hidden');
                    document.body.style.overflow = 'hidden';
                }

            } catch (err) {
                console.error('Failed to check owner status:', err);
                // Fallback: show the owner form modal
                if (switchAccountModal) {
                    switchAccountModal.classList.add('hidden');
                }
                if (switchToOwnerModal) {
                    switchToOwnerModal.classList.remove('hidden');
                    document.body.style.overflow = 'hidden';
                }
            }
        });
    });

    // Handle back button (Step 1 -> main Switch Account modal)
    if (backToSwitchAccountFromOwnerBtn) {
        backToSwitchAccountFromOwnerBtn.addEventListener('click', function (e) {
            e.preventDefault();
            // Hide owner form modal
            if (switchToOwnerModal) switchToOwnerModal.classList.add('hidden');
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
        switchToOwnerModalOverlay.addEventListener('click', function () {
            switchToOwnerModal.classList.add('hidden');
            document.body.style.overflow = '';
        });
    }

    // Handle Personal Info Next button
    if (personalInfoNextBtn) {
        personalInfoNextBtn.addEventListener('click', function (e) {
            e.preventDefault();
            clearErrors();

            // Client-side validation
            const errors = {};
            const firstName = document.getElementById('ownerFirstName').value;
            const lastName = document.getElementById('ownerLastName').value;
            const dob = document.getElementById('ownerDOB').value;
            const phone = document.getElementById('ownerPhone').value;
            const occupation = document.getElementById('ownerOccupation').value;
            const prov = document.getElementById('ownerProvince').value;
            const city = document.getElementById('ownerCity').value;
            const brgy = document.getElementById('ownerBarangay').value;
            const street = document.getElementById('ownerAddressStreet').value;
            const postal = document.getElementById('ownerPostal').value;

            if (!firstName.trim()) errors.first_name = 'First name is required';
            if (!lastName.trim()) errors.last_name = 'Last name is required';
            if (!dob) {
                errors.date_of_birth = 'Date of birth is required';
            } else if (calculateAge(dob) < 18) {
                errors.date_of_birth = 'You must be at least 18 years old';
            }
            if (!phone.trim()) {
                errors.phone_number = 'Phone number is required';
            } else if (!/^09\d{9}$/.test(phone)) {
                errors.phone_number = 'Invalid phone format (09xxxxxxxxx)';
            }
            if (!occupation) errors.occupation_id = 'Occupation is required';
            if (!prov) errors.owner_address_province = 'Province is required';
            if (!city) errors.owner_address_city = 'City is required';
            if (!brgy) errors.owner_address_barangay = 'Barangay is required';
            if (!street.trim()) errors.owner_address_street = 'Street address is required';
            if (!postal.trim()) errors.owner_address_postal = 'Postal code is required';

            if (Object.keys(errors).length > 0) {
                displayErrors(errors);
                // Scroll to first error
                const firstErrorField = Object.keys(errors)[0];
                const errorEl = document.getElementById(`error_${firstErrorField}`);
                if (errorEl) errorEl.scrollIntoView({ behavior: 'smooth', block: 'center' });
                return;
            }

            // Submit personal info to web route via AJAX
            (async function () {
                try {
                    const fd = new FormData(ownerAccountForm);
                    const resp = await fetch(ownerAccountForm.action, {
                        method: 'POST',
                        body: fd,
                        credentials: 'same-origin',
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest'
                        }
                    });
                    const json = await resp.json();
                    if (json.success) {
                        switchToOwnerModal.classList.add('hidden');
                        accountSetupForm.classList.remove('hidden');
                        document.body.style.overflow = 'hidden';
                    } else {
                        displayErrors(json.errors || json.message);
                    }
                } catch (err) {
                    console.error(err);
                    alert('Failed to submit personal info');
                }
            })();
        });
    }

    // PSGC logic wrappers
    async function loadCities(provCode) {
        const cities = await fetchCitiesForProvince(provCode);
        populateSelect(ownerCity, cities, 'Select City');
    }

    async function loadBarangays(cityCode) {
        const barangays = await fetchBarangaysForCity(cityCode);
        populateSelect(ownerBarangay, barangays, 'Select Barangay');
    }

    // PSGC logic...
    async function fetchCitiesForProvince(provinceCode) {
        if (!provinceCode) return [];
        try {
            const res = await fetch('/api/psgc/provinces/' + encodeURIComponent(provinceCode) + '/cities');
            const json = await res.json();
            return json.data || [];
        } catch (e) {
            console.error('Failed to fetch cities:', e);
            return [];
        }
    }

    async function fetchBarangaysForCity(cityCode) {
        if (!cityCode) return [];
        try {
            const res = await fetch('/api/psgc/cities/' + encodeURIComponent(cityCode) + '/barangays');
            const json = await res.json();
            return json.data || [];
        } catch (e) {
            console.error('Failed to fetch barangays:', e);
            return [];
        }
    }

    function clearSelectOptions(selectEl) {
        if (!selectEl) return;
        while (selectEl.options.length > 0) {
            selectEl.remove(0);
        }
    }

    function populateSelect(selectEl, items, placeholderText, preserveSelection = true) {
        if (!selectEl) return;
        // preserve previously selected value if present
        const previous = selectEl.dataset.selected || selectEl.value || '';
        clearSelectOptions(selectEl);
        const placeholder = document.createElement('option');
        placeholder.value = '';
        placeholder.disabled = true;
        placeholder.textContent = placeholderText || 'Select';
        selectEl.appendChild(placeholder);
        items.forEach(item => {
            const opt = document.createElement('option');
            const code = (item.code !== undefined) ? item.code : (item.id ?? '');
            const name = item.name ?? item.label ?? item.occupation_name ?? '';
            opt.value = code;
            opt.textContent = name;
            if (preserveSelection && previous && String(previous) === String(code)) opt.selected = true;
            selectEl.appendChild(opt);
        });
        // If this is the occupation select, ensure an 'Others' option exists if no similar option present
        if (selectEl.id === 'ownerOccupation') {
            const hasOther = Array.from(selectEl.options).some(o => String(o.text).toLowerCase().includes('other'));
            if (!hasOther) {
                const otherOpt = document.createElement('option');
                otherOpt.value = 'others';
                otherOpt.textContent = 'Others';
                if (previous && String(previous).toLowerCase() === 'others') otherOpt.selected = true;
                selectEl.appendChild(otherOpt);
            }
        }
        if (preserveSelection) {
            if (previous && Array.from(selectEl.options).some(o => String(o.value) === String(previous))) {
                selectEl.value = previous;
            }
        } else {
            // force placeholder selected
            selectEl.selectedIndex = 0;
        }
    }

    if (ownerProvince) {
        ownerProvince.addEventListener('change', async function () {
            const prov = this.value;
            const cities = await fetchCitiesForProvince(prov);
            populateSelect(ownerCity, cities, 'Select City');
            populateSelect(ownerBarangay, [], 'Select Barangay');
        });
    }

    // Toggle 'Other' occupation text input when occupation select changes
    if (ownerOccupation) {
        ownerOccupation.addEventListener('change', function () {
            if (!ownerOccupationOther) return;
            const selText = ownerOccupation.options[ownerOccupation.selectedIndex]?.text?.toLowerCase() || '';
            if (selText.includes('other')) {
                ownerOccupationOther.classList.remove('hidden');
            } else {
                ownerOccupationOther.classList.add('hidden');
                ownerOccupationOther.value = '';
            }
        });
        // initialize visibility on load by checking selected option text
        const initText = ownerOccupation.options[ownerOccupation.selectedIndex]?.text?.toLowerCase() || '';
        if (initText.includes('other') && ownerOccupationOther) {
            ownerOccupationOther.classList.remove('hidden');
        }
    }

    if (ownerCity) {
        ownerCity.addEventListener('change', async function () {
            const city = this.value;
            const barangays = await fetchBarangaysForCity(city);
            populateSelect(ownerBarangay, barangays, 'Select Barangay');
        });
    }

    // Navigation Step 2 -> 3
    if (accountSetupNextBtn) {
        accountSetupNextBtn.addEventListener('click', function (e) {
            e.preventDefault();
            clearErrors();

            const username = document.getElementById('ownerUsername').value;
            const email = document.getElementById('ownerEmail').value;
            const errors = {};

            if (!username.trim()) errors.username = 'Username is required';
            if (!email.trim()) errors.email = 'Email is required';

            if (Object.keys(errors).length > 0) {
                displayErrors(errors);
                return;
            }

            (async function () {
                try {
                    const fd = new FormData(accountCredentialsForm);
                    const resp = await fetch(accountCredentialsForm.action, {
                        method: 'POST',
                        body: fd,
                        credentials: 'same-origin',
                        headers: { 'X-Requested-With': 'XMLHttpRequest' }
                    });
                    const json = await resp.json();
                    if (json.success) {
                        accountSetupForm.classList.add('hidden');
                        identityVerificationOwnerForm.classList.remove('hidden');
                        document.body.style.overflow = 'hidden';
                    } else {
                        displayErrors(json.errors || json.message);
                    }
                } catch (err) {
                    console.error(err);
                    alert('Failed to submit account setup');
                }
            })();
        });
    }

    // Navigation Step 3 -> 4
    if (identityVerificationNextBtn) {
        identityVerificationNextBtn.addEventListener('click', function (e) {
            e.preventDefault();
            clearErrors();

            const type = document.getElementById('ownerValidIdType').value;
            const front = document.getElementById('ownerValidIdFront').files[0];
            const back = document.getElementById('ownerValidIdBack').files[0];
            const police = document.getElementById('ownerPoliceClearance').files[0];
            const errors = {};

            if (!type) errors.valid_id_id = 'ID type is required';
            if (!front) errors.valid_id_photo = 'Front photo is required';
            if (!back) errors.valid_id_back_photo = 'Back photo is required';
            if (!police) errors.police_clearance = 'Police clearance is required';

            if (Object.keys(errors).length > 0) {
                displayErrors(errors);
                return;
            }

            (async function () {
                try {
                    const fd = new FormData(identityVerificationForm);
                    const resp = await fetch(identityVerificationForm.action, {
                        method: 'POST',
                        body: fd,
                        credentials: 'same-origin',
                        headers: { 'X-Requested-With': 'XMLHttpRequest' }
                    });
                    const json = await resp.json();
                    if (json.success) {
                        identityVerificationOwnerForm.classList.add('hidden');
                        profilePictureOwnerForm.classList.remove('hidden');
                        document.body.style.overflow = 'hidden';
                    } else {
                        displayErrors(json.errors || json.message);
                    }
                } catch (err) {
                    console.error(err);
                    alert('Failed to submit verification');
                }
            })();
        });
    }

    // Step 2 Back Button
    if (backToPersonalInfoBtn) {
        backToPersonalInfoBtn.addEventListener('click', function (e) {
            e.preventDefault();
            accountSetupForm.classList.add('hidden');
            switchToOwnerModal.classList.remove('hidden');
            document.body.style.overflow = 'hidden';
        });
    }

    // Step 3 Back Button
    if (backToAccountSetupBtn) {
        backToAccountSetupBtn.addEventListener('click', function (e) {
            e.preventDefault();
            identityVerificationOwnerForm.classList.add('hidden');
            accountSetupForm.classList.remove('hidden');
            document.body.style.overflow = 'hidden';
        });
    }

    // Step 4 Back Button
    if (backToIdentityVerificationBtn) {
        backToIdentityVerificationBtn.addEventListener('click', function (e) {
            e.preventDefault();
            profilePictureOwnerForm.classList.add('hidden');
            identityVerificationOwnerForm.classList.remove('hidden');
            document.body.style.overflow = 'hidden';
        });
    }

    // Overlays
    [accountSetupOverlay, identityVerificationOverlay, profilePictureOwnerOverlay, ownerConfirmationOverlay, pendingContractorOwnerOverlay].forEach(overlay => {
        if (overlay) {
            overlay.addEventListener('click', function () {
                const modal = this.closest('.switch-account-modal, .confirmation-modal');
                if (modal) modal.classList.add('hidden');
                document.body.style.overflow = '';
            });
        }
    });

    // Profile Pic Logic
    if (ownerProfilePictureInput) {
        ownerProfilePictureInput.addEventListener('change', function (e) {
            const file = e.target.files[0];
            if (file && file.type.startsWith('image/')) {
                const reader = new FileReader();
                reader.onload = function (event) {
                    ownerProfilePicturePreview.src = event.target.result;
                    ownerProfilePicturePreview.style.display = 'block';
                    ownerProfilePicturePlaceholder.style.display = 'none';
                    ownerProfilePictureCircle.classList.add('has-image');
                };
                reader.readAsDataURL(file);
            }
        });
    }

    function updateSummary() {
        const summaryContainer = document.getElementById('ownerSwitchSummary');
        if (!summaryContainer) return;

        const getValue = (id) => {
            const val = document.getElementById(id)?.value?.trim();
            return val || '';
        };

        const getText = (id) => {
            const el = document.getElementById(id);
            if (!el || el.selectedIndex === -1) return '';
            const text = el.options[el.selectedIndex]?.text;
            return (text && !text.toLowerCase().includes('select')) ? text : '';
        };

        const hasFile = (id) => document.getElementById(id)?.files?.length > 0 ? 'File Selected' : 'No File';

        // Refined Full Name logic: John Quincy Doe or John Doe
        const fName = getValue('ownerFirstName');
        const mName = getValue('ownerMiddleName');
        const lName = getValue('ownerLastName');
        const fullName = [fName, mName, lName].filter(Boolean).join(' ');

        // Refined Address logic
        const street = getValue('ownerAddressStreet');
        const brgy = getText('ownerBarangay');
        const city = getText('ownerCity');
        const prov = getText('ownerProvince');
        const postal = getValue('ownerPostal');
        const fullAddress = [street, brgy, city, prov, postal].filter(Boolean).join(', ');

        summaryContainer.innerHTML = `
            <div class="summary-card">
                <div class="summary-section">
                    <div class="summary-section-header personal">
                        <i class="fi fi-rr-user"></i>
                        <span>Personal Information</span>
                    </div>
                    <div class="summary-content">
                        <div class="summary-row">
                            <span class="summary-row-label">Full Name</span>
                            <span class="summary-row-value">${fullName || '---'}</span>
                        </div>
                        <div class="summary-row">
                            <span class="summary-row-label">Date of Birth</span>
                            <span class="summary-row-value">${getValue('ownerDOB') || '---'}</span>
                        </div>
                        <div class="summary-row">
                            <span class="summary-row-label">Phone Number</span>
                            <span class="summary-row-value">${getValue('ownerPhone') || '---'}</span>
                        </div>
                        <div class="summary-row">
                                            <span class="summary-row-label">Occupation</span>
                                            <span class="summary-row-value">${(() => {
                const occText = getText('ownerOccupation') || '';
                if (occText.toLowerCase().includes('other')) {
                    const otherVal = getValue('ownerOccupationOther');
                    return otherVal || occText || '---';
                }
                return occText || '---';
            })()}</span>
                                        </div>
                    </div>
                </div>

                <div class="summary-section">
                    <div class="summary-section-header address">
                        <i class="fi fi-rr-marker"></i>
                        <span>Address Details</span>
                    </div>
                    <div class="summary-content">
                        <div class="summary-row vertical">
                            <span class="summary-row-label">Complete Address</span>
                            <span class="summary-row-value">${fullAddress || '---'}</span>
                        </div>
                    </div>
                </div>

                <div class="summary-section">
                    <div class="summary-section-header identity">
                        <i class="fi fi-rr-id-badge"></i>
                        <span>Identity Verification</span>
                    </div>
                    <div class="summary-content">
                        <div class="summary-row">
                            <span class="summary-row-label">ID Type</span>
                            <span class="summary-row-value">${getText('ownerValidIdType') || '---'}</span>
                        </div>
                        <div class="summary-grid">
                            <div class="summary-file-item">
                                <span class="summary-file-label">Front ID</span>
                                <span class="summary-file-status ${hasFile('ownerValidIdFront') === 'File Selected' ? 'success' : ''}">${hasFile('ownerValidIdFront')}</span>
                            </div>
                            <div class="summary-file-item">
                                <span class="summary-file-label">Back ID</span>
                                <span class="summary-file-status ${hasFile('ownerValidIdBack') === 'File Selected' ? 'success' : ''}">${hasFile('ownerValidIdBack')}</span>
                            </div>
                            <div class="summary-file-item">
                                <span class="summary-file-label">Police Clearance</span>
                                <span class="summary-file-status ${hasFile('ownerPoliceClearance') === 'File Selected' ? 'success' : ''}">${hasFile('ownerPoliceClearance')}</span>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="summary-section">
                    <div class="summary-section-header account">
                        <i class="fi fi-rr-settings"></i>
                        <span>Account Credentials</span>
                    </div>
                    <div class="summary-content">
                        <div class="summary-row">
                            <span class="summary-row-label">Username</span>
                            <span class="summary-row-value">${getValue('ownerUsername') || '---'}</span>
                        </div>
                        <div class="summary-row">
                            <span class="summary-row-label">Email Address</span>
                            <span class="summary-row-value">${getValue('ownerEmail') || '---'}</span>
                        </div>
                    </div>
                </div>
            </div>
        `;
    }

    // Finalize Logic
    if (ownerCompleteBtn) {
        ownerCompleteBtn.addEventListener('click', function (e) {
            e.preventDefault();
            clearErrors();

            // Populate summary before showing confirmation
            updateSummary();

            profilePictureOwnerForm.classList.add('hidden');
            ownerConfirmationModal.classList.remove('hidden');
            document.body.style.overflow = 'hidden';
        });
    }

    if (ownerConfirmCancelBtn) {
        ownerConfirmCancelBtn.addEventListener('click', function () {
            ownerConfirmationModal.classList.add('hidden');
            profilePictureOwnerForm.classList.remove('hidden');
            document.body.style.overflow = 'hidden';
        });
    }

    if (ownerConfirmBtn) {
        ownerConfirmBtn.addEventListener('click', function () {
            (async function () {
                const btnText = ownerConfirmBtn.querySelector('span');
                const originalText = btnText ? btnText.textContent : 'Confirm';

                try {
                    clearErrors();
                    if (btnText) btnText.textContent = 'Submitting...';
                    ownerConfirmBtn.disabled = true;

                    // Collect all data from previous steps to ensure final validation passes
                    const fd = new FormData(ownerProfilePictureForm);

                    // Add Personal Info (Step 1)
                    const personalInfoForm = new FormData(ownerAccountForm);
                    for (let [key, value] of personalInfoForm.entries()) {
                        fd.append(key, value);
                    }

                    // Add Account Setup (Step 2)
                    const setupForm = new FormData(accountCredentialsForm);
                    for (let [key, value] of setupForm.entries()) {
                        fd.append(key, value);
                    }

                    // Add Identity Verification (Step 3)
                    const verificationForm = new FormData(identityVerificationForm);
                    for (let [key, value] of verificationForm.entries()) {
                        // Avoid double-appending files if they are handled by backend session
                        if (!(value instanceof File)) {
                            fd.append(key, value);
                        }
                    }

                    // Backend requires 'address' combined string in some validations
                    const street = personalInfoForm.get('owner_address_street') || '';
                    const brgy = personalInfoForm.get('owner_address_barangay') || '';
                    const city = personalInfoForm.get('owner_address_city') || '';
                    const prov = personalInfoForm.get('owner_address_province') || '';
                    const postal = personalInfoForm.get('owner_address_postal') || '';
                    if (street && brgy && city && prov && postal) {
                        fd.append('address', `${street}, ${brgy}, ${city}, ${prov}, ${postal}`);
                    }

                    const resp = await fetch(ownerProfilePictureForm.action, {
                        method: 'POST',
                        body: fd,
                        credentials: 'same-origin',
                        headers: { 'X-Requested-With': 'XMLHttpRequest' }
                    });
                    const json = await resp.json();
                    if (json.success) {
                        // Notify user that request was submitted and awaits admin approval
                        showToast(json.message || 'Request submitted — awaiting admin approval', 'success');
                        ownerConfirmationModal.classList.add('hidden');
                        document.body.style.overflow = '';
                        // Keep user in contractor role UI; redirect to contractor homepage
                        setTimeout(() => { window.location.href = '/contractor/homepage'; }, 900);
                        return;
                    } else {
                        ownerConfirmationModal.classList.add('hidden');
                        profilePictureOwnerForm.classList.remove('hidden');
                        displayErrors(json.errors || json.message);
                    }
                } catch (err) {
                    console.error('Final submission error:', err);
                    alert('An error occurred during final submission.');
                } finally {
                    if (btnText) btnText.textContent = originalText;
                    ownerConfirmBtn.disabled = false;
                }
            })();
        });
    }

    // Pending modal close button
    if (pendingOwnerCloseBtn) {
        pendingOwnerCloseBtn.addEventListener('click', function () {
            if (pendingContractorOwnerModal) pendingContractorOwnerModal.classList.add('hidden');
            document.body.style.overflow = '';
        });
    }
    // Handle clicking the Property Owner card itself
    const switchToOwnerCard = document.getElementById('switchToOwner');
    if (switchToOwnerCard) {
        switchToOwnerCard.addEventListener('click', function (e) {
            // Only trigger if not clicking the button itself (to avoid double trigger)
            if (!e.target.closest('.account-switch-btn')) {
                const btn = this.querySelector('[data-target="owner"]');
                if (btn && !btn.disabled) {
                    btn.click();
                }
            }
        });
    }
});
