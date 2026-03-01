/**
 * Edit Contractor Profile Modal
 * Opens a modal pre-filled with current profile data, submits changes to profileController::update
 */

(function () {
    'use strict';

    const SELECTORS = {
        modal:     '#editContractorProfileModal',
        overlay:   '#ecpModalOverlay',
        closeBtn:  '#ecpCloseBtn',
        cancelBtn: '#ecpCancelBtn',
        saveBtn:   '#ecpSaveBtn',
        form:      '#ecpForm',
        cityInput:    '#ecpPermitCityInput',
        cityHidden:   '#ecpPermitCity',
        cityDropdown: '#ecpCityDropdown',
        cityCombobox: '#ecpCityCombobox'
    };

    // Field map: form input id → profileData key
    const FIELD_MAP = {
        ecpCompanyName:      'name',
        ecpCompanyEmail:     'email',
        ecpCompanyPhone:     'contactNumber',
        ecpCompanyStartDate: 'companyStartDate',
        ecpServicesOffered:  'servicesOffered',
        ecpBio:              'bio',
        ecpCompanyDescription: 'companyDescription',
        ecpBusinessAddress:  'location',
        ecpWebsite:          'companyWebsite',
        ecpSocialMedia:      'companySocialMedia',
        ecpPicabNumber:      'picabNumber',
        ecpPicabCategory:    'picabCategory',
        ecpTinNumber:        'tinNumber',
        ecpBusinessPermit:   'businessPermitNumber',
        ecpPermitExpiration: 'businessPermitExpiration'
    };

    let modalEl, overlayEl, closeBtn, cancelBtn, saveBtn, formEl;
    let cityInput, cityHidden, cityDropdown, cityCombobox;
    let allCities = null;       // cached city list [{name, code, ...}]
    let psgcCitiesUrl = '/api/psgc/cities'; // fallback, overridden from data attribute
    let cityDropdownOpen = false;
    let highlightIndex = -1;

    function init() {
        modalEl   = document.querySelector(SELECTORS.modal);
        overlayEl = document.querySelector(SELECTORS.overlay);
        closeBtn  = document.querySelector(SELECTORS.closeBtn);
        cancelBtn = document.querySelector(SELECTORS.cancelBtn);
        saveBtn   = document.querySelector(SELECTORS.saveBtn);
        formEl    = document.querySelector(SELECTORS.form);

        cityInput    = document.querySelector(SELECTORS.cityInput);
        cityHidden   = document.querySelector(SELECTORS.cityHidden);
        cityDropdown = document.querySelector(SELECTORS.cityDropdown);
        cityCombobox = document.querySelector(SELECTORS.cityCombobox);

        // Read dynamic PSGC base URL from Blade data attribute
        const rootEl = document.getElementById('contractorProfileRoot');
        if (rootEl && rootEl.dataset.psgcCitiesUrl) {
            psgcCitiesUrl = rootEl.dataset.psgcCitiesUrl;
        }

        if (!modalEl || !formEl) return;

        closeBtn?.addEventListener('click', closeModal);
        cancelBtn?.addEventListener('click', closeModal);
        overlayEl?.addEventListener('click', closeModal);
        saveBtn?.addEventListener('click', handleSave);

        // City combobox events
        if (cityInput) {
            cityInput.addEventListener('input', onCityInput);
            cityInput.addEventListener('focus', onCityFocus);
            cityInput.addEventListener('keydown', onCityKeydown);
        }

        // Close dropdown when clicking outside
        document.addEventListener('click', (e) => {
            if (cityCombobox && !cityCombobox.contains(e.target)) {
                closeCityDropdown();
            }
        });

        // ESC key
        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape' && modalEl.classList.contains('active')) {
                if (cityDropdownOpen) {
                    closeCityDropdown();
                } else {
                    closeModal();
                }
            }
        });
    }

    // ── City combobox logic ──────────────────────────────────────────

    async function ensureCitiesLoaded() {
        if (allCities) return;
        try {
            const res = await fetch(psgcCitiesUrl, {
                headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
            });
            const json = await res.json();
            allCities = (json.data || json || []).map(c => c.name);
            // deduplicate and sort
            allCities = [...new Set(allCities)].sort((a, b) => a.localeCompare(b));
        } catch (err) {
            console.error('Failed to load cities:', err);
            allCities = [];
        }
    }

    function filterCities(query) {
        if (!allCities) return [];
        const q = query.trim().toLowerCase();
        if (!q) return allCities.slice(0, 50); // show first 50 when empty
        return allCities.filter(name => name.toLowerCase().includes(q)).slice(0, 50);
    }

    function renderCityDropdown(matches) {
        if (!cityDropdown) return;
        cityDropdown.innerHTML = '';
        highlightIndex = -1;

        if (matches.length === 0) {
            cityDropdown.innerHTML = '<div class="ecp-combobox-empty">No cities found</div>';
            openCityDropdown();
            return;
        }

        matches.forEach((name, idx) => {
            const item = document.createElement('div');
            item.className = 'ecp-combobox-item';
            item.textContent = name;
            item.dataset.index = idx;
            item.addEventListener('mousedown', (e) => {
                e.preventDefault(); // prevent blur
                selectCity(name);
            });
            cityDropdown.appendChild(item);
        });

        openCityDropdown();
    }

    function selectCity(name) {
        if (cityInput) cityInput.value = name;
        if (cityHidden) cityHidden.value = name;
        closeCityDropdown();
    }

    function openCityDropdown() {
        if (cityDropdown) cityDropdown.classList.add('open');
        cityDropdownOpen = true;
    }

    function closeCityDropdown() {
        if (cityDropdown) cityDropdown.classList.remove('open');
        cityDropdownOpen = false;
        highlightIndex = -1;
    }

    async function onCityFocus() {
        await ensureCitiesLoaded();
        const matches = filterCities(cityInput.value);
        renderCityDropdown(matches);
    }

    function onCityInput() {
        const matches = filterCities(cityInput.value);
        renderCityDropdown(matches);
        // Also update hidden field as user types (in case they type manually)
        if (cityHidden) cityHidden.value = cityInput.value;
    }

    function onCityKeydown(e) {
        if (!cityDropdownOpen) return;
        const items = cityDropdown?.querySelectorAll('.ecp-combobox-item') || [];
        if (items.length === 0) return;

        if (e.key === 'ArrowDown') {
            e.preventDefault();
            highlightIndex = Math.min(highlightIndex + 1, items.length - 1);
            updateHighlight(items);
        } else if (e.key === 'ArrowUp') {
            e.preventDefault();
            highlightIndex = Math.max(highlightIndex - 1, 0);
            updateHighlight(items);
        } else if (e.key === 'Enter') {
            e.preventDefault();
            if (highlightIndex >= 0 && highlightIndex < items.length) {
                selectCity(items[highlightIndex].textContent);
            }
        }
    }

    function updateHighlight(items) {
        items.forEach((el, i) => {
            el.classList.toggle('highlighted', i === highlightIndex);
            if (i === highlightIndex) {
                el.scrollIntoView({ block: 'nearest' });
            }
        });
    }

    // ── Modal open / close ───────────────────────────────────────────

    /**
     * Open the modal and pre-fill fields from the live ContractorProfile instance.
     */
    async function openModal() {
        if (!modalEl) return;

        // Try to read profileData from the global ContractorProfile instance
        const profileInstance = window.__contractorProfile;
        const d = profileInstance?.profileData || {};

        // Populate fields
        for (const [inputId, dataKey] of Object.entries(FIELD_MAP)) {
            const el = document.getElementById(inputId);
            if (!el) continue;
            el.value = d[dataKey] ?? '';
        }

        // Extra: also populate fields that map to different data properties
        const permitNumEl = document.getElementById('ecpBusinessPermit');
        if (permitNumEl && d.businessPermitNumber) permitNumEl.value = d.businessPermitNumber;
        const permitExpEl = document.getElementById('ecpPermitExpiration');
        if (permitExpEl && d.businessPermitExpiration) permitExpEl.value = d.businessPermitExpiration;

        // Pre-fill permit city
        const savedCity = d.businessPermitCity || '';
        if (cityInput) cityInput.value = savedCity;
        if (cityHidden) cityHidden.value = savedCity;

        // Pre-load cities in background
        ensureCitiesLoaded();

        modalEl.classList.add('active');
        document.body.style.overflow = 'hidden';

        // Focus first input
        setTimeout(() => {
            document.getElementById('ecpCompanyName')?.focus();
        }, 350);
    }

    function closeModal() {
        if (!modalEl) return;
        modalEl.classList.remove('active');
        document.body.style.overflow = '';
        closeCityDropdown();
    }

    // ── Save ─────────────────────────────────────────────────────────

    /**
     * Collect form data, POST to update endpoint, refresh profile on success.
     */
    async function handleSave() {
        if (!formEl || !saveBtn) return;

        // Basic required-field validation
        const companyName = document.getElementById('ecpCompanyName');
        if (companyName && !companyName.value.trim()) {
            companyName.classList.add('invalid');
            companyName.focus();
            showNotification('Company name is required.', 'error');
            return;
        }
        companyName?.classList.remove('invalid');

        // Disable button, show loading
        saveBtn.disabled = true;
        saveBtn.classList.add('loading');

        try {
            const profileInstance = window.__contractorProfile;
            const updateUrl = profileInstance?.updateUrl || '/contractor/profile/update';
            const csrfToken = profileInstance?.csrfToken ||
                              document.querySelector('meta[name="csrf-token"]')?.content || '';

            // Build form data from the HTML form inputs (use their `name` attribute which matches backend keys)
            const formData = new FormData(formEl);

            const response = await fetch(updateUrl, {
                method: 'POST',
                body: formData,
                headers: {
                    'X-CSRF-TOKEN': csrfToken,
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                }
            });

            const json = await response.json();

            if (json.success) {
                showNotification('Profile updated successfully!', 'success');
                closeModal();

                // Refresh profile data
                if (profileInstance && typeof profileInstance.loadProfileData === 'function') {
                    await profileInstance.loadProfileData();
                }
            } else {
                const msg = json.message || 'Failed to update profile.';
                showNotification(msg, 'error');
            }
        } catch (err) {
            console.error('Edit profile save error:', err);
            showNotification('An error occurred while saving. Please try again.', 'error');
        } finally {
            saveBtn.disabled = false;
            saveBtn.classList.remove('loading');
        }
    }

    function showNotification(message, type) {
        const profileInstance = window.__contractorProfile;
        if (profileInstance && typeof profileInstance.showNotification === 'function') {
            profileInstance.showNotification(message, type);
            return;
        }
        // Fallback
        alert(message);
    }

    // Expose open/close globally so the profile page can call them
    window.openEditContractorProfileModal = openModal;
    window.closeEditContractorProfileModal = closeModal;

    // Init on DOM ready
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }
})();
