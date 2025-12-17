document.addEventListener('DOMContentLoaded', function() {
    // =====
    // ELEMENT REFERENCES
    // =====

    // Filters
    const dateFromInput = document.getElementById('dateFrom');
    const dateToInput = document.getElementById('dateTo');
    const searchInput = document.getElementById('searchInput');
    const resetBtn = document.getElementById('resetFilterBtn');
    const contractorsWrap = document.getElementById('contractorsTableWrap');

    let debounceTimer;

    
    // Period Dropdown
    const periodBtn = document.getElementById('periodBtn');
    const periodDropdown = document.getElementById('periodDropdown');
    const periodText = document.getElementById('periodText');
    const periodOptions = document.querySelectorAll('.period-option');
    
    // Add Contractor Modal
    const addContractorBtn = document.getElementById('addContractorBtn');
    const addContractorModal = document.getElementById('addContractorModal');
    const closeModalBtn = document.getElementById('closeModalBtn');
    const cancelBtn = document.getElementById('cancelBtn');
    const saveBtn = document.getElementById('saveBtn');
    const profileUpload = document.getElementById('profileUpload');
    const profilePreview = document.getElementById('profilePreview');
    const profileIcon = document.getElementById('profileIcon');
    // Representative Profile Upload
    const repProfileUpload = document.getElementById('repProfileUpload');
    const repProfilePreview = document.getElementById('repProfilePreview');
    const repProfileIcon = document.getElementById('repProfileIcon');

    // DTI/SEC Upload Elements
    const dtiDropzone = document.getElementById('dtiDropzone');
    const dtiUpload = document.getElementById('dtiUpload');
    const dtiFileName = document.getElementById('dtiFileName');

    // Profile Upload Preview
    if (profileUpload) {
        profileUpload.addEventListener('change', function(e) {
            const file = e.target.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    profilePreview.src = e.target.result;
                    profilePreview.classList.remove('hidden');
                    profileIcon.classList.add('hidden');
                };
                reader.readAsDataURL(file);
            }
        });
    }

    
    // Edit Contractor Modal
    const editContractorModal = document.getElementById('editContractorModal');
    const closeEditModalBtn = document.getElementById('closeEditModalBtn');
    const cancelEditBtn = document.getElementById('cancelEditBtn');
    const saveEditBtn = document.getElementById('saveEditBtn');
    const editProfileUpload = document.getElementById('editProfileUpload');
    const editProfilePreview = document.getElementById('editProfilePreview');
    const editProfileInitials = document.getElementById('editProfileInitials');

    
    // Delete Contractor Modal
    const deleteContractorModal = document.getElementById('deleteContractorModal');
    const confirmDeleteBtn = document.getElementById('confirmDeleteBtn');
    const cancelDeleteBtn = document.getElementById('cancelDeleteBtn');
    const deleteContractorNameSpan = document.getElementById('deleteContractorName');
    const deletionReasonInput = document.getElementById('deletionReason');
    const deletionReasonError = document.getElementById('deletionReasonError');
    let rowToDelete = null;
    let idToDelete = null;

    // =====
    // ERROR CLEARING
    // =====
    function clearInputError(input) {
        input.classList.remove('border-red-500');
        const parent = input.parentNode;
        const errorMsg = parent.querySelector('.error-message');
        if (errorMsg) errorMsg.remove();

        // Special case for DTI Dropzone
        if (input.id === 'dtiUpload') {
            const dropzone = document.getElementById('dtiDropzone');
            if (dropzone) {
                dropzone.classList.remove('border-red-500');
                const dzError = dropzone.querySelector('.error-message');
                if (dzError) dzError.remove();
            }
        }
    }

    if (addContractorModal) {
        const inputs = addContractorModal.querySelectorAll('input, select, textarea');
        inputs.forEach(input => {
            ['input', 'change'].forEach(event => {
                input.addEventListener(event, () => clearInputError(input));
            });
        });
    }

    // =====
    // FILTER FUNCTIONALITY
    // =====

    // Function to fetch and update data
    async function fetchAndUpdate(url) {
        try {
            const response = await fetch(url, {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            });

            if (!response.ok) throw new Error('Network response was not ok');

            const data = await response.json();

            if (contractorsWrap && data.html) {
                contractorsWrap.innerHTML = data.html;
            }

            // Update URL without reload
            window.history.pushState({}, '', url);

            // Re-attach listeners
            attachActionListeners();

        } catch (error) {
            console.error('Error fetching data:', error);
        }
    }

    function buildUrl() {
        const url = new URL(window.location.href);
        const params = new URLSearchParams(url.search);

        if (dateFromInput && dateFromInput.value) {
            params.set('date_from', dateFromInput.value);
        } else {
            params.delete('date_from');
        }

        if (dateToInput && dateToInput.value) {
            params.set('date_to', dateToInput.value);
        } else {
            params.delete('date_to');
        }

        if (searchInput && searchInput.value) {
            params.set('search', searchInput.value);
        } else {
            params.delete('search');
        }

        // Reset pagination when filtering
        params.delete('page');

        return `${url.pathname}?${params.toString()}`;
    }

    function handleFilterChange() {
        const url = buildUrl();
        fetchAndUpdate(url);
    }

    function handleSearchInput() {
        clearTimeout(debounceTimer);
        debounceTimer = setTimeout(() => {
            handleFilterChange();
        }, 300); // 300ms debounce
    }

    // Attach listeners
    if (dateFromInput) dateFromInput.addEventListener('change', handleFilterChange);
    if (dateToInput) dateToInput.addEventListener('change', handleFilterChange);
    if (searchInput) searchInput.addEventListener('input', handleSearchInput);

    if (resetBtn) {
        resetBtn.addEventListener('click', function() {
            if (dateFromInput) dateFromInput.value = '';
            if (dateToInput) dateToInput.value = '';
            if (searchInput) searchInput.value = '';
            handleFilterChange();
        });
    }

    // Populate inputs from URL on load
    const urlParams = new URLSearchParams(window.location.search);
    if (dateFromInput && urlParams.has('date_from')) {
        dateFromInput.value = urlParams.get('date_from');
    }
    if (dateToInput && urlParams.has('date_to')) {
        dateToInput.value = urlParams.get('date_to');
    }
    if (searchInput && urlParams.has('search')) {
        searchInput.value = urlParams.get('search');
    }

    // =====
    // ADD CONTRACTOR MODAL FUNCTIONALITY
    // =====

    let rowToDelete = null;
    
    // Table Action Buttons
    const editButtons = document.querySelectorAll('.edit-btn');
    const deleteButtons = document.querySelectorAll('.delete-btn');
    const viewButtons = document.querySelectorAll('.view-btn');
    
    // =====
    // PERIOD DROPDOWN FUNCTIONALITY
    // =====
    
    periodBtn.addEventListener('click', function() {
        periodDropdown.classList.toggle('hidden');
    });
    
    periodOptions.forEach(option => {
        option.addEventListener('click', function(e) {
            e.preventDefault();
            const period = this.getAttribute('data-period');
            const periodMap = {
                'today': 'Today',
                'week': 'This Week',
                'month': 'This Month',
                'year': 'This Year'
            };
            periodText.textContent = periodMap[period];
            periodDropdown.classList.add('hidden');
        });
    });
    
    // Close dropdown when clicking outside
    document.addEventListener('click', function(e) {
        if (!periodBtn.contains(e.target) && !periodDropdown.contains(e.target)) {
            periodDropdown.classList.add('hidden');
        }
    });
    
    // =====
    // ADD CONTRACTOR MODAL FUNCTIONALITY
    // =====
    
    function openAddModal() {
        addContractorModal.classList.remove('hidden');
        document.body.style.overflow = 'hidden';
        setTimeout(() => {
            const modalContent = addContractorModal.querySelector('.modal-content');
            modalContent.style.transform = 'scale(1)';
            modalContent.style.opacity = '1';
        }, 10);
    }

    
    function closeAddModal() {
        const modalContent = addContractorModal.querySelector('.modal-content');
        modalContent.style.transform = 'scale(0.95)';
        modalContent.style.opacity = '0';
        setTimeout(() => {
            addContractorModal.classList.add('hidden');
            document.body.style.overflow = 'auto';
            // Reset form
            addContractorModal.querySelectorAll('input, select, textarea').forEach(input => {
                if (input.type !== 'file') input.value = '';
                if (input.tagName === 'SELECT') input.selectedIndex = 0;
            });
            // Reset specific elements
            if (dtiFileName) dtiFileName.textContent = '';
            if (dtiDropzone) dtiDropzone.classList.remove('border-orange-500', 'bg-orange-100');

            // Reset Address Selects
            if (citySelect) {
                citySelect.innerHTML = '<option value="">Select City/Municipality</option>';
                citySelect.disabled = true;
            }
            if (barangaySelect) {
                barangaySelect.innerHTML = '<option value="">Select Barangay</option>';
                barangaySelect.disabled = true;
            }

            // Reset Errors
            addContractorModal.querySelectorAll('.error-message').forEach(el => el.remove());
            addContractorModal.querySelectorAll('.border-red-500').forEach(el => el.classList.remove('border-red-500'));

        }, 300);
    }

    if (addContractorBtn) addContractorBtn.addEventListener('click', openAddModal);
    if (closeModalBtn) closeModalBtn.addEventListener('click', closeAddModal);
    if (cancelBtn) cancelBtn.addEventListener('click', closeAddModal);

    // Contractor Type "Others" Toggle
    const contractorTypeSelect = document.getElementById('contractorTypeSelect');
    const contractorTypeOtherInput = document.getElementById('contractorTypeOtherInput');
    if (contractorTypeSelect) {
        contractorTypeSelect.addEventListener('change', function() {
            // Check if the selected option text is "Others" or value is 9 (based on legatura.sql)
            const selectedText = this.options[this.selectedIndex].text;
            if (selectedText === 'Others' || this.value == 9) {
                contractorTypeOtherInput.classList.remove('hidden');
                contractorTypeOtherInput.required = true;
            } else {
                contractorTypeOtherInput.classList.add('hidden');
                contractorTypeOtherInput.required = false;
                contractorTypeOtherInput.value = '';
            }
        });
    }

    // Address Handling (PSGC)
    const provinceSelect = document.getElementById('contractor_address_province');
    const citySelect = document.getElementById('contractor_address_city');
    const barangaySelect = document.getElementById('contractor_address_barangay');

    // Business Permit Address Handling
    // const bpProvinceSelect = document.getElementById('business_permit_province'); // Removed
    const bpCitySelect = document.getElementById('business_permit_city');

    if (provinceSelect) {
        provinceSelect.addEventListener('change', function() {
            const provinceCode = this.value;
            citySelect.innerHTML = '<option value="">Select City/Municipality</option>';
            barangaySelect.innerHTML = '<option value="">Select Barangay</option>';
            citySelect.disabled = true;
            barangaySelect.disabled = true;

            if (provinceCode) {
                fetch(`/api/psgc/provinces/${provinceCode}/cities`)
                    .then(response => response.json())
                    .then(data => {
                        data.forEach(city => {
                            const option = document.createElement('option');
                            option.value = city.code;
                            option.textContent = city.name;
                            option.setAttribute('data-name', city.name);
                            citySelect.appendChild(option);
                        });
                        citySelect.disabled = false;
                    });
            }
        });
    }

    if (citySelect) {
        citySelect.addEventListener('change', function() {
            const cityCode = this.value;
            barangaySelect.innerHTML = '<option value="">Select Barangay</option>';
            barangaySelect.disabled = true;

            if (cityCode) {
                fetch(`/api/psgc/cities/${cityCode}/barangays`)
                    .then(response => response.json())
                    .then(data => {
                        data.forEach(barangay => {
                            const option = document.createElement('option');
                            option.value = barangay.code;
                            option.textContent = barangay.name;
                            option.setAttribute('data-name', barangay.name);
                            barangaySelect.appendChild(option);
                        });
                        barangaySelect.disabled = false;
                    });
            }
        });
    }

    // Business Permit Province Change - REMOVED
    /*
    if (bpProvinceSelect) {
        bpProvinceSelect.addEventListener('change', function() {
            // ...
        });
    }
    */



    // Save Button Logic
    if (saveBtn) {
        saveBtn.addEventListener('click', async function() {
            const originalText = this.innerHTML;
            this.innerHTML = '<i class="fi fi-rr-spinner animate-spin"></i> Saving...';
            this.disabled = true;

            const formData = new FormData();
            const inputs = addContractorModal.querySelectorAll('input, select, textarea');

            inputs.forEach(input => {
                if (input.type === 'file') {
                    if (input.files[0]) {
                        formData.append(input.name, input.files[0]);
                    }
                } else if (input.tagName === 'SELECT') {
                     if (input.id === 'contractor_address_province' || input.id === 'contractor_address_city' || input.id === 'contractor_address_barangay') {
                        if (input.selectedIndex > 0) {
                            const name = input.options[input.selectedIndex].getAttribute('data-name');
                            // Map IDs to request fields
                            let fieldName;
                            if (input.id === 'contractor_address_province') fieldName = 'business_address_province';
                            else if (input.id === 'contractor_address_city') fieldName = 'business_address_city';
                            else if (input.id === 'contractor_address_barangay') fieldName = 'business_address_barangay';

                            formData.append(fieldName, name);
                        }
                    } else {
                        formData.append(input.name, input.value);
                    }
                } else {
                    formData.append(input.name, input.value);
                }
            });

            // Add CSRF
            const csrfToken = document.querySelector('meta[name="csrf-token"]');
            if (csrfToken) {
                formData.append('_token', csrfToken.content);
            }

            // Clear errors
            addContractorModal.querySelectorAll('.error-message').forEach(el => el.remove());
            addContractorModal.querySelectorAll('.border-red-500').forEach(el => el.classList.remove('border-red-500'));

            try {
                const response = await fetch('/admin/user-management/contractors/store', {
                    method: 'POST',
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'X-CSRF-TOKEN': csrfToken ? csrfToken.content : ''
                    },
                    body: formData
                });

                const result = await response.json();

                if (response.ok) {
                    showNotification('Contractor added successfully!', 'success');
                    closeAddModal();
                    handleFilterChange(); // Refresh table
                } else {
                    if (result.errors) {
                        for (const [key, messages] of Object.entries(result.errors)) {
                            const input = addContractorModal.querySelector(`[name="${key}"]`);
                            if (input) {
                                if (input.type === 'file' && input.parentNode.id === 'dtiDropzone') {
                                    input.parentNode.classList.add('border-red-500');
                                    const errorDiv = document.createElement('div');
                                    errorDiv.className = 'text-red-500 text-xs mt-1 error-message absolute -bottom-5 left-0 w-full text-center';
                                    errorDiv.textContent = messages[0];
                                    input.parentNode.appendChild(errorDiv);
                                } else {
                                    input.classList.add('border-red-500');
                                    const errorDiv = document.createElement('div');
                                    errorDiv.className = 'text-red-500 text-xs mt-1 error-message';
                                    errorDiv.textContent = messages[0];
                                    input.parentNode.appendChild(errorDiv);
                                }
                            } else {
                                showNotification(messages[0], 'error');
                            }
                        }
                    } else {
                        showNotification(result.message || 'An error occurred', 'error');
                    }
                }
            } catch (error) {
                console.error('Error:', error);
                showNotification('An unexpected error occurred', 'error');
            } finally {
                this.innerHTML = originalText;
                this.disabled = false;
            }
        });
    }

    // Close modal on backdrop click
    if (addContractorModal) {
        addContractorModal.addEventListener('click', function(e) {
            if (e.target === addContractorModal) {
                closeAddModal();
            }
        });
    }

    // Close modal on ESC key
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            if (addContractorModal && !addContractorModal.classList.contains('hidden')) {
                closeAddModal();
            }
            if (editContractorModal && !editContractorModal.classList.contains('hidden')) {
                closeEditModal();
            }
            if (deleteContractorModal && !deleteContractorModal.classList.contains('hidden')) {
            addContractorModal.querySelectorAll('input').forEach(input => {
                if (input.type !== 'file') input.value = '';
            });
            profilePreview.classList.add('hidden');
            profileIcon.classList.remove('hidden');
            if (repProfilePreview) repProfilePreview.classList.add('hidden');
            if (repProfileIcon) repProfileIcon.classList.remove('hidden');
            if (repProfileUpload) repProfileUpload.value = '';
        }, 300);
    }
    
    addContractorBtn.addEventListener('click', openAddModal);
    closeModalBtn.addEventListener('click', closeAddModal);
    cancelBtn.addEventListener('click', closeAddModal);
    
    // Profile Upload Preview
    profileUpload.addEventListener('change', function(e) {
        const file = e.target.files[0];
        if (file) {
            const reader = new FileReader();
            reader.onload = function(e) {
                profilePreview.src = e.target.result;
                profilePreview.classList.remove('hidden');
                profileIcon.classList.add('hidden');
            };
            reader.readAsDataURL(file);
        }
    });

    // Representative Profile Upload Preview
    if (repProfileUpload) {
        repProfileUpload.addEventListener('change', function(e) {
            const file = e.target.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    if (repProfilePreview) {
                        repProfilePreview.src = e.target.result;
                        repProfilePreview.classList.remove('hidden');
                    }
                    if (repProfileIcon) repProfileIcon.classList.add('hidden');
                };
                reader.readAsDataURL(file);
            }
        });
    }
    
    // Save Button with Loading State
    saveBtn.addEventListener('click', function() {
        const originalText = this.innerHTML;
        this.innerHTML = '<i class="fi fi-rr-spinner animate-spin"></i> Saving...';
        this.disabled = true;
        
        // Simulate save
        setTimeout(() => {
            showNotification('Contractor added successfully!', 'success');
            closeAddModal();
            this.innerHTML = originalText;
            this.disabled = false;
        }, 1500);
    });
    
    // Close modal on backdrop click
    addContractorModal.addEventListener('click', function(e) {
        if (e.target === addContractorModal) {
            closeAddModal();
        }
    });
    
    // Close modal on ESC key
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            if (!addContractorModal.classList.contains('hidden')) {
                closeAddModal();
            }
            if (!editContractorModal.classList.contains('hidden')) {
                closeEditModal();
            }
            if (!deleteContractorModal.classList.contains('hidden')) {
                closeDeleteModal();
            }
        }
    });

    // =====
    // GENERIC PASSWORD VISIBILITY TOGGLES
    // =====
    document.addEventListener('click', function(e) {
        const btn = e.target.closest('[data-toggle-password]');
        if (!btn) return;
        const targetSelector = btn.getAttribute('data-target');
        if (!targetSelector) return;
        const input = document.querySelector(targetSelector);
        if (!input) return;
        input.type = input.type === 'password' ? 'text' : 'password';
        const icon = btn.querySelector('i');
        if (icon) {
            if (icon.classList.contains('fi-rr-eye')) {
                icon.classList.remove('fi-rr-eye');
                icon.classList.add('fi-rr-eye-crossed');
            } else {
                icon.classList.remove('fi-rr-eye-crossed');
                icon.classList.add('fi-rr-eye');
            }
        }
    });

    // =====
    // EDIT CONTRACTOR MODAL FUNCTIONALITY
    // =====

    // Contractor Type "Others" Toggle for Edit Modal
    const editContractorTypeSelect = document.getElementById('edit_contractorTypeSelect');
    const editContractorTypeOtherInput = document.getElementById('edit_contractorTypeOtherInput');
    if (editContractorTypeSelect) {
        editContractorTypeSelect.addEventListener('change', function() {
            if (this.value == '9') { // Assuming 9 is 'Others'
                editContractorTypeOtherInput.classList.remove('hidden');
                editContractorTypeOtherInput.setAttribute('required', 'required');
            } else {
                editContractorTypeOtherInput.classList.add('hidden');
                editContractorTypeOtherInput.removeAttribute('required');
                editContractorTypeOtherInput.value = '';
            }
        });
    }

    // Address Handling for Edit Modal
    const editProvinceSelect = document.getElementById('edit_contractor_address_province');
    const editCitySelect = document.getElementById('edit_contractor_address_city');
    const editBarangaySelect = document.getElementById('edit_contractor_address_barangay');
    const editBpCitySelect = document.getElementById('edit_business_permit_city');

    if (editProvinceSelect) {
        editProvinceSelect.addEventListener('change', function() {
            const provinceCode = this.value;

            // Reset City and Barangay
            editCitySelect.innerHTML = '<option value="">Loading...</option>';
            editCitySelect.disabled = true;
            editBarangaySelect.innerHTML = '<option value="">Select City First</option>';
            editBarangaySelect.disabled = true;

            if (provinceCode) {
                fetch(`/api/psgc/provinces/${provinceCode}/cities`)
                    .then(response => response.json())
                    .then(data => {
                        editCitySelect.innerHTML = '<option value="">Select City/Municipality</option>';
                        data.forEach(city => {
                            const option = document.createElement('option');
                            option.value = city.code; // Use code as value to match propertyOwner logic
                            option.dataset.name = city.name; // Store name in dataset
                            option.textContent = city.name;
                            editCitySelect.appendChild(option);
                        });
                        editCitySelect.disabled = false;
                    })
                    .catch(error => {
                        console.error('Error fetching cities:', error);
                        editCitySelect.innerHTML = '<option value="">Error loading cities</option>';
                    });
            } else {
                editCitySelect.innerHTML = '<option value="">Select Province First</option>';
            }
        });
    }

    if (editCitySelect) {
        editCitySelect.addEventListener('change', function() {
            const cityCode = this.value;

            // Reset Barangay
            editBarangaySelect.innerHTML = '<option value="">Loading...</option>';
            editBarangaySelect.disabled = true;

            if (cityCode) {
                fetch(`/api/psgc/cities/${cityCode}/barangays`)
                    .then(response => response.json())
                    .then(data => {
                        editBarangaySelect.innerHTML = '<option value="">Select Barangay</option>';
                        data.forEach(barangay => {
                            const option = document.createElement('option');
                            option.value = barangay.code; // Use code as value
                            option.dataset.name = barangay.name; // Store name in dataset
                            option.textContent = barangay.name;
                            editBarangaySelect.appendChild(option);
                        });
                        editBarangaySelect.disabled = false;
                    })
                    .catch(error => {
                        console.error('Error fetching barangays:', error);
                        editBarangaySelect.innerHTML = '<option value="">Error loading barangays</option>';
                    });
            } else {
                editBarangaySelect.innerHTML = '<option value="">Select City First</option>';
            }
        });
    }

    async function openEditModal(contractorData) {
        // Fetch full details
        try {
            const response = await fetch(`/admin/user-management/contractors/${contractorData.contractor_id}/edit`);
            if (!response.ok) throw new Error('Failed to fetch details');

            const result = await response.json();
            if (!result.success) throw new Error(result.error || 'Failed to fetch details');

            const data = result.data;

            document.getElementById('edit_user_id').value = data.user_id;
            document.getElementById('edit_company_name').value = data.company_name;
            document.getElementById('edit_company_phone').value = data.company_phone;
            document.getElementById('edit_company_start_date').value = data.company_start_date;

            // Contractor Type
            const typeSelect = document.getElementById('edit_contractorTypeSelect');
            typeSelect.value = data.type_id;
            if (data.type_id == 9) {
                document.getElementById('edit_contractorTypeOtherInput').classList.remove('hidden');
                document.getElementById('edit_contractorTypeOtherInput').value = data.contractor_type_other;
            } else {
                document.getElementById('edit_contractorTypeOtherInput').classList.add('hidden');
            }

            document.getElementById('edit_services_offered').value = data.services_offered;
            document.getElementById('edit_company_website').value = data.company_website;
            document.getElementById('edit_company_social_media').value = data.company_social_media;

            // Representative
            document.getElementById('edit_first_name').value = data.authorized_rep_fname;
            document.getElementById('edit_middle_name').value = data.authorized_rep_mname;
            document.getElementById('edit_last_name').value = data.authorized_rep_lname;
            document.getElementById('edit_company_email').value = data.company_email; // From join
            document.getElementById('edit_username').value = data.username; // From join

            // Address - Populated from parsed data in controller
            document.getElementById('edit_business_address_street').value = data.business_address_street || '';
            document.getElementById('edit_business_address_postal').value = data.business_address_postal || '';

            // Address (PSGC) - Cascading Dropdowns
            const provinceSelect = document.getElementById('edit_contractor_address_province');
            const citySelect = document.getElementById('edit_contractor_address_city');
            const barangaySelect = document.getElementById('edit_contractor_address_barangay');

            // 1. Set Province
            let provinceCode = '';
            if (data.business_address_province) {
                // Try to match by name first, then by code
                for (let i = 0; i < provinceSelect.options.length; i++) {
                    const optionName = provinceSelect.options[i].getAttribute('data-name');
                    const optionValue = provinceSelect.options[i].value;

                    // Match by name (preferred) or by code (fallback for old data)
                    if ((optionName && optionName.trim() === data.business_address_province.trim()) ||
                        (optionValue && optionValue === data.business_address_province.trim())) {
                        provinceSelect.selectedIndex = i;
                        provinceCode = provinceSelect.options[i].value;
                        break;
                    }
                }
            }

            // 2. Fetch and Set City
            if (provinceCode) {
                try {
                    const citiesResponse = await fetch(`/api/psgc/provinces/${provinceCode}/cities`);
                    const cities = await citiesResponse.json();

                    citySelect.innerHTML = '<option value="">Select City/Municipality</option>';
                    let cityCode = '';

                    cities.forEach(city => {
                        const option = document.createElement('option');
                        option.value = city.code;
                        option.setAttribute('data-name', city.name);
                        option.textContent = city.name;

                        // Match by name or code
                        if (data.business_address_city &&
                            (city.name.trim() === data.business_address_city.trim() ||
                             city.code === data.business_address_city.trim())) {
                            option.selected = true;
                            cityCode = city.code;
                        }
                        citySelect.appendChild(option);
                    });
                    citySelect.disabled = false;

                    // 3. Fetch and Set Barangay
                    if (cityCode) {
                        const barangaysResponse = await fetch(`/api/psgc/cities/${cityCode}/barangays`);
                        const barangays = await barangaysResponse.json();

                        barangaySelect.innerHTML = '<option value="">Select Barangay</option>';
                        barangays.forEach(barangay => {
                            const option = document.createElement('option');
                            option.value = barangay.code;
                            option.setAttribute('data-name', barangay.name);
                            option.textContent = barangay.name;

                            // Match by name or code
                            if (data.business_address_barangay &&
                                (barangay.name.trim() === data.business_address_barangay.trim() ||
                                 barangay.code === data.business_address_barangay.trim())) {
                                option.selected = true;
                            }
                            barangaySelect.appendChild(option);
                        });
                        barangaySelect.disabled = false;
                    } else {
                        barangaySelect.innerHTML = '<option value="">Select Barangay</option>';
                        barangaySelect.disabled = true;
                    }

                } catch (err) {
                    console.error('Error fetching address data:', err);
                }
            } else {
                // Reset City and Barangay if no province matched
                citySelect.innerHTML = '<option value="">Select City/Municipality</option>';
                citySelect.disabled = true;
                barangaySelect.innerHTML = '<option value="">Select Barangay</option>';
                barangaySelect.disabled = true;
            }

            // Legal Docs
            document.getElementById('edit_picab_number').value = data.picab_number;
            document.getElementById('edit_picab_category').value = data.picab_category;
            document.getElementById('edit_picab_expiration_date').value = data.picab_expiration_date;
            document.getElementById('edit_business_permit_number').value = data.business_permit_number;
            document.getElementById('edit_business_permit_city').value = data.business_permit_city;
            document.getElementById('edit_business_permit_expiration').value = data.business_permit_expiration;
            document.getElementById('edit_tin_business_reg_number').value = data.tin_business_reg_number;

            // DTI/SEC File Link
            const dtiLinkContainer = document.getElementById('editCurrentDtiFile');
            if (data.dti_sec_registration_photo) {
                dtiLinkContainer.classList.remove('hidden');
                dtiLinkContainer.querySelector('a').href = `/storage/${data.dti_sec_registration_photo}`;
            } else {
                dtiLinkContainer.classList.add('hidden');
            }

            // Profile Pic Preview
            if (data.profile_pic) {
                document.getElementById('editProfilePreview').src = `/storage/${data.profile_pic}`;
                document.getElementById('editProfilePreview').classList.remove('hidden');
                document.getElementById('editProfileIcon').classList.add('hidden');
            } else {
                document.getElementById('editProfilePreview').classList.add('hidden');
                document.getElementById('editProfileIcon').classList.remove('hidden');
            }

        } catch (error) {
            console.error('Error populating form:', error);
            showNotification('Error fetching contractor details', 'error');
        }

    
    // =====
    // EDIT CONTRACTOR MODAL FUNCTIONALITY
    // =====
    
    function openEditModal(contractorData) {
        // Populate form with contractor data
        document.getElementById('editCompanyName').value = contractorData.name;
        document.getElementById('editYearsOperation').value = contractorData.years;
        document.getElementById('editAccountType').value = contractorData.accountType;
        document.getElementById('editContactNumber').value = contractorData.contact || '+63 912 345 6789';
        document.getElementById('editLicenseNumber').value = contractorData.license || 'LIC-2025-001';
        document.getElementById('editRegistrationDate').value = contractorData.dateRegistered;
        document.getElementById('editEmail').value = contractorData.email || 'contact@company.com';
        document.getElementById('editUsername').value = contractorData.username || 'username';
        editProfileInitials.textContent = contractorData.initials;
        
        editContractorModal.classList.remove('hidden');
        editContractorModal.classList.add('flex');
        document.body.style.overflow = 'hidden';
        setTimeout(() => {
            const modalContent = editContractorModal.querySelector('.modal-content');
            modalContent.style.transform = 'scale(1)';
            modalContent.style.opacity = '1';
        }, 10);
    }

    
    function closeEditModal() {
        const modalContent = editContractorModal.querySelector('.modal-content');
        modalContent.style.transform = 'scale(0.95)';
        modalContent.style.opacity = '0';
        setTimeout(() => {
            editContractorModal.classList.add('hidden');
            editContractorModal.classList.remove('flex');
            document.body.style.overflow = 'auto';
            // Reset form
            document.getElementById('editContractorForm').reset();
            document.getElementById('editProfilePreview').classList.add('hidden');
            document.getElementById('editProfileIcon').classList.remove('hidden');
        }, 300);
    }

    if (closeEditModalBtn) closeEditModalBtn.addEventListener('click', closeEditModal);
    if (cancelEditBtn) cancelEditBtn.addEventListener('click', closeEditModal);

    // Edit Profile Upload Preview
    if (editProfileUpload) {
        editProfileUpload.addEventListener('change', function(e) {
            const file = e.target.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    editProfilePreview.src = e.target.result;
                    editProfilePreview.classList.remove('hidden');
                    editProfileIcon.classList.add('hidden');
                };
                reader.readAsDataURL(file);
            }
        });
    }

    // Save Edit Button with Loading State
    if (saveEditBtn) {
        saveEditBtn.addEventListener('click', async function() {
            const form = document.getElementById('editContractorForm');
            const formData = new FormData(form);
            const userId = document.getElementById('edit_user_id').value;

            // Handle PSGC names - Append names for backend processing
            const editProvince = document.getElementById('edit_contractor_address_province');
            const editCity = document.getElementById('edit_contractor_address_city');
            const editBarangay = document.getElementById('edit_contractor_address_barangay');

            if (editProvince && editProvince.selectedIndex > 0) {
                // Use data-name or text content
                const name = editProvince.options[editProvince.selectedIndex].getAttribute('data-name') || editProvince.options[editProvince.selectedIndex].text;
                formData.set('business_address_province', name); // Override code with name
            }
            if (editCity && editCity.selectedIndex > 0) {
                const name = editCity.options[editCity.selectedIndex].getAttribute('data-name') || editCity.options[editCity.selectedIndex].text;
                formData.set('business_address_city', name);
            }
            if (editBarangay && editBarangay.selectedIndex > 0) {
                const name = editBarangay.options[editBarangay.selectedIndex].getAttribute('data-name') || editBarangay.options[editBarangay.selectedIndex].text;
                formData.set('business_address_barangay', name);
            }

            // Add _method PUT for Laravel
            formData.append('_method', 'PUT');

            // Clear previous errors
            const errorMessages = form.querySelectorAll('.error-message');
            errorMessages.forEach(el => el.remove());
            const errorInputs = form.querySelectorAll('.border-red-500');
            errorInputs.forEach(el => el.classList.remove('border-red-500'));

            const originalText = this.innerHTML;
            this.innerHTML = '<i class="fi fi-rr-spinner animate-spin"></i> Saving...';
            this.disabled = true;

            try {
                const response = await fetch(`/admin/user-management/contractors/update/${userId}`, {
                    method: 'POST', // Use POST with _method=PUT
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json'
                    },
                    body: formData
                });

                const result = await response.json();

                if (response.ok && result.success) {
                    showNotification('Contractor updated successfully!', 'success');
                    closeEditModal();
                    // Refresh table
                    const url = new URL(window.location.href);
                    fetchAndUpdate(url);
                } else {
                    if (result.errors) {
                        // Display validation errors
                        for (const [key, messages] of Object.entries(result.errors)) {
                            // Map key to input ID if needed, or use name attribute
                            // Since we used names matching the request, we can query by name
                            // But we prefixed IDs with 'edit_', so we might need to find by name inside the edit form
                            const input = form.querySelector(`[name="${key}"]`);
                            if (input) {
                                input.classList.add('border-red-500');
                                const errorDiv = document.createElement('div');
                                errorDiv.className = 'text-red-500 text-xs mt-1 error-message';
                                errorDiv.textContent = messages[0];
                                input.parentNode.appendChild(errorDiv);
                            } else {
                                showNotification(messages[0], 'error');
                            }
                        }
                    } else {
                        showNotification(result.message || 'An error occurred', 'error');
                    }
                }
            } catch (error) {
                console.error('Error:', error);
                showNotification('An unexpected error occurred', 'error');
            } finally {
                this.innerHTML = originalText;
                this.disabled = false;
            }
        });
    }

    // Close edit modal on backdrop click
    if (editContractorModal) {
        editContractorModal.addEventListener('click', function(e) {
            if (e.target === editContractorModal) {
                closeEditModal();
            }
        });
    }

    // =====
    // DELETE CONTRACTOR MODAL FUNCTIONALITY
    // =====

    function openDeleteModal(contractorName, id, row) {
        rowToDelete = row;
        idToDelete = id;
            editProfilePreview.classList.add('hidden');
            editProfileInitials.classList.remove('hidden');
        }, 300);
    }
    
    closeEditModalBtn.addEventListener('click', closeEditModal);
    cancelEditBtn.addEventListener('click', closeEditModal);
    
    // Edit Profile Upload Preview
    editProfileUpload.addEventListener('change', function(e) {
        const file = e.target.files[0];
        if (file) {
            const reader = new FileReader();
            reader.onload = function(e) {
                editProfilePreview.src = e.target.result;
                editProfilePreview.classList.remove('hidden');
                editProfileInitials.classList.add('hidden');
            };
            reader.readAsDataURL(file);
        }
    });
    
    // Save Edit Button with Loading State
    saveEditBtn.addEventListener('click', function() {
        const originalText = this.innerHTML;
        this.innerHTML = '<i class="fi fi-rr-spinner animate-spin"></i> Saving...';
        this.disabled = true;
        
        // Simulate save
        setTimeout(() => {
            showNotification('Contractor updated successfully!', 'success');
            closeEditModal();
            this.innerHTML = originalText;
            this.disabled = false;
        }, 1500);
    });
    
    // Close edit modal on backdrop click
    editContractorModal.addEventListener('click', function(e) {
        if (e.target === editContractorModal) {
            closeEditModal();
        }
    });
    
    // =====
    // DELETE CONTRACTOR MODAL FUNCTIONALITY
    // =====
    
    function openDeleteModal(contractorName, row) {
        rowToDelete = row;
        deleteContractorNameSpan.textContent = contractorName;
        deleteContractorModal.classList.remove('hidden');
        deleteContractorModal.classList.add('flex');
        document.body.style.overflow = 'hidden';

        // Reset deletion reason
        if (deletionReasonInput) {
            deletionReasonInput.value = '';
            deletionReasonInput.classList.remove('border-red-500');
        }
        if (deletionReasonError) {
            deletionReasonError.classList.add('hidden');
        }

        setTimeout(() => {
            const modalContent = deleteContractorModal.querySelector('.modal-content');
            modalContent.style.transform = 'scale(1)';
            modalContent.style.opacity = '1';
        }, 10);
    }

    
    function closeDeleteModal() {
        const modalContent = deleteContractorModal.querySelector('.modal-content');
        modalContent.style.transform = 'scale(0.95)';
        modalContent.style.opacity = '0';
        setTimeout(() => {
            deleteContractorModal.classList.add('hidden');
            deleteContractorModal.classList.remove('flex');
            document.body.style.overflow = 'auto';
            rowToDelete = null;
            idToDelete = null;
        }, 300);
    }

    if (confirmDeleteBtn) {
        confirmDeleteBtn.addEventListener('click', async function() {
            if (!rowToDelete || !idToDelete) return;

            // Validate reason
            const reason = deletionReasonInput.value.trim();
            if (!reason) {
                deletionReasonInput.classList.add('border-red-500');
                deletionReasonError.classList.remove('hidden');
                return;
            } else {
                deletionReasonInput.classList.remove('border-red-500');
                deletionReasonError.classList.add('hidden');
            }

            // Add loading state
            const originalContent = confirmDeleteBtn.innerHTML;
            confirmDeleteBtn.innerHTML = '<i class="fi fi-rr-spinner animate-spin"></i> Deleting...';
            confirmDeleteBtn.disabled = true;

            try {
                const csrfToken = document.querySelector('meta[name="csrf-token"]').content;
                const response = await fetch(`/admin/user-management/contractors/${idToDelete}`, {
                    method: 'POST', // Using POST with _method: DELETE
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken,
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    body: JSON.stringify({
                        _method: 'DELETE',
                        deletion_reason: reason
                    })
                });

                const result = await response.json();

                if (response.ok) {
                    showNotification('Contractor deleted successfully!', 'success');
                    closeDeleteModal();
                    handleFilterChange(); // Refresh table data
                } else {
                    alert(result.message || 'Failed to delete contractor.');
                }
            } catch (error) {
                console.error('Error deleting contractor:', error);
                alert('An error occurred while deleting.');
            } finally {
                // Reset button
                confirmDeleteBtn.innerHTML = originalContent;
                confirmDeleteBtn.disabled = false;
            }
        });
    }

    if (cancelDeleteBtn) cancelDeleteBtn.addEventListener('click', closeDeleteModal);

    // Close delete modal on backdrop click
    if (deleteContractorModal) {
        deleteContractorModal.addEventListener('click', function(e) {
            if (e.target === deleteContractorModal) {
                closeDeleteModal();
            }
        });
    }

    // =====
    // TABLE ACTION BUTTONS & PAGINATION
    // =====

    function attachActionListeners() {
        // Edit Buttons
        const editButtons = document.querySelectorAll('.edit-btn');
        editButtons.forEach(button => {
            button.addEventListener('click', async function(e) {
                addRipple(this, e);
                const id = this.getAttribute('data-id');

                // Fetch data from API
                try {
                    const response = await fetch(`/api/admin/users/contractors/${id}`);
                    if (!response.ok) throw new Error('Failed to fetch contractor data');
                    const data = await response.json();
                    openEditModal(data);
                } catch (error) {
                    console.error(error);
                    showNotification('Error fetching contractor details', 'error');
                }
            });
        });

        // Delete Buttons
        const deleteButtons = document.querySelectorAll('.delete-btn');
        deleteButtons.forEach(button => {
            button.addEventListener('click', function(e) {
                addRipple(this, e);
                const row = this.closest('tr');
                const name = row.querySelector('td:first-child .font-medium').textContent.trim();
                const id = this.getAttribute('data-id');
                openDeleteModal(name, id, row);
            });
        });

        // View Buttons
        const viewButtons = document.querySelectorAll('.view-btn');
        viewButtons.forEach(button => {
            button.addEventListener('click', function(e) {
                addRipple(this, e);
                const id = this.getAttribute('data-id');
                if (id) {
                    window.location.href = `/admin/user-management/contractor/view?id=${id}`;
                }
            });
        });

        // Pagination Links
        const paginationLinks = document.querySelectorAll('.pagination a');
        paginationLinks.forEach(link => {
            link.addEventListener('click', function(e) {
                e.preventDefault();
                const url = this.href;
                fetchAndUpdate(url);
            });
        });
    }

    // Initial attachment
    attachActionListeners();

    // =====
    // HELPER FUNCTIONS
    // =====

        }, 300);
    }
    
    confirmDeleteBtn.addEventListener('click', function() {
        const originalText = this.innerHTML;
        this.innerHTML = '<i class="fi fi-rr-spinner animate-spin"></i> Deleting...';
        this.disabled = true;
        
        // Simulate deletion
        setTimeout(() => {
            if (rowToDelete) {
                // Fade out animation
                rowToDelete.style.transition = 'all 0.3s ease';
                rowToDelete.style.opacity = '0';
                rowToDelete.style.transform = 'translateX(-20px)';
                
                setTimeout(() => {
                    rowToDelete.remove();
                    showNotification('Contractor deleted successfully!', 'success');
                }, 300);
            }
            
            closeDeleteModal();
            this.innerHTML = originalText;
            this.disabled = false;
        }, 1000);
    });
    
    cancelDeleteBtn.addEventListener('click', closeDeleteModal);
    
    // Close delete modal on backdrop click
    deleteContractorModal.addEventListener('click', function(e) {
        if (e.target === deleteContractorModal) {
            closeDeleteModal();
        }
    });
    
    // =====
    // TABLE ACTION BUTTONS
    // =====
    
    editButtons.forEach(button => {
        button.addEventListener('click', function() {
            addRipple(this, event);
            const row = this.closest('tr');
            const nameCell = row.querySelector('td:first-child span');
            const name = nameCell.textContent.trim();
            const initials = row.querySelector('.rounded-full').textContent.trim();
            const dateRegistered = row.querySelector('td:nth-child(2)').textContent.trim();
            const years = row.querySelector('td:nth-child(3)').textContent.trim().replace(' years', '');
            const accountTypeText = row.querySelector('td:nth-child(4) span').textContent.trim();
            
            // Map account type display text to value
            const accountTypeMap = {
                'General Contractor': 'general',
                'Construction Contractor': 'construction',
                'Specialty Contractor': 'specialty'
            };
            
            const contractorData = {
                name: name,
                initials: initials,
                dateRegistered: convertDateToISO(dateRegistered),
                years: years,
                accountType: accountTypeMap[accountTypeText] || 'general',
                contact: '+63 912 345 6789',
                license: 'LIC-2025-001',
                email: 'contact@' + name.toLowerCase().replace(/\s+/g, '') + '.com',
                username: name.toLowerCase().replace(/\s+/g, '')
            };
            
            openEditModal(contractorData);
        });
    });
    
    deleteButtons.forEach(button => {
        button.addEventListener('click', function() {
            addRipple(this, event);
            const row = this.closest('tr');
            const name = row.querySelector('td:first-child span').textContent.trim();
            openDeleteModal(name, row);
        });
    });
    
    viewButtons.forEach(button => {
        button.addEventListener('click', function() {
            addRipple(this, event);
            // Redirect to contractor_Views page
            window.location.href = '/admin/user-management/contractor/view';
        });
    });
    
    // =====
    // HELPER FUNCTIONS
    // =====
    
    function showNotification(message, type = 'success') {
        const notification = document.createElement('div');
        const bgColor = type === 'success' ? 'bg-green-500' : type === 'error' ? 'bg-red-500' : 'bg-blue-500';
        const icon = type === 'success' ? 'fi-rr-check-circle' : type === 'error' ? 'fi-rr-cross-circle' : 'fi-rr-info';

        
        notification.className = `fixed top-6 right-6 ${bgColor} text-white px-6 py-4 rounded-lg shadow-2xl flex items-center gap-3 z-[100] transform translate-x-[400px] transition-transform duration-300`;
        notification.innerHTML = `
            <i class="fi ${icon} text-xl"></i>
            <span class="font-medium">${message}</span>
        `;

        document.body.appendChild(notification);

        setTimeout(() => {
            notification.style.transform = 'translateX(0)';
        }, 10);

        
        document.body.appendChild(notification);
        
        setTimeout(() => {
            notification.style.transform = 'translateX(0)';
        }, 10);
        
        setTimeout(() => {
            notification.style.transform = 'translateX(400px)';
            setTimeout(() => notification.remove(), 300);
        }, 3000);
    }

    
    function addRipple(button, event) {
        const ripple = document.createElement('span');
        const rect = button.getBoundingClientRect();
        const size = Math.max(rect.width, rect.height);
        const x = event.clientX - rect.left - size / 2;
        const y = event.clientY - rect.top - size / 2;

        
        ripple.style.width = ripple.style.height = size + 'px';
        ripple.style.left = x + 'px';
        ripple.style.top = y + 'px';
        ripple.classList.add('ripple');

        button.appendChild(ripple);

        setTimeout(() => ripple.remove(), 600);
    }

    // =====
    // INPUT FOCUS EFFECTS
    // =====

        
        button.appendChild(ripple);
        
        setTimeout(() => ripple.remove(), 600);
    }
    
    function convertDateToISO(dateStr) {
        // Convert "10 Oct, 2025" to "2025-10-10"
        const months = {
            'Jan': '01', 'Feb': '02', 'Mar': '03', 'Apr': '04',
            'May': '05', 'Jun': '06', 'Jul': '07', 'Aug': '08',
            'Sep': '09', 'Oct': '10', 'Nov': '11', 'Dec': '12'
        };
        
        const parts = dateStr.split(' ');
        const day = parts[0].padStart(2, '0');
        const month = months[parts[1].replace(',', '')];
        const year = parts[2];
        
        return `${year}-${month}-${day}`;
    }
    
    // =====
    // INPUT FOCUS EFFECTS
    // =====
    
    const allInputs = document.querySelectorAll('input, select, textarea');
    allInputs.forEach(input => {
        input.addEventListener('focus', function() {
            this.parentElement.classList.add('ring-2', 'ring-orange-400');
        });

        
        input.addEventListener('blur', function() {
            this.parentElement.classList.remove('ring-2', 'ring-orange-400');
        });
    });
    
    // =====
    // RANKING FILTER
    // =====
    
    const rankingFilter = document.getElementById('rankingFilter');
    rankingFilter.addEventListener('change', function() {
        const value = this.value;
        // Placeholder for filter functionality
        console.log('Filter by:', value);
    });

    // =====
    // DTI/SEC DROPZONE UPLOAD
    // =====
    // Elements declared at top
    const dtiDropzone = document.getElementById('dtiDropzone');
    const dtiUpload = document.getElementById('dtiUpload');
    const dtiFileName = document.getElementById('dtiFileName');

    if (dtiDropzone && dtiUpload) {
        const highlight = () => dtiDropzone.classList.add('ring-2', 'ring-orange-400');
        const unhighlight = () => dtiDropzone.classList.remove('ring-2', 'ring-orange-400');

        // Click to upload
        dtiDropzone.addEventListener('click', () => dtiUpload.click());

        ['dragenter', 'dragover'].forEach(evt => {
            dtiDropzone.addEventListener(evt, (e) => {
                e.preventDefault();
                e.stopPropagation();
                highlight();
            });
        });
        ['dragleave', 'drop'].forEach(evt => {
            dtiDropzone.addEventListener(evt, (e) => {
                e.preventDefault();
                e.stopPropagation();
                unhighlight();
            });
        });
        dtiDropzone.addEventListener('drop', (e) => {
            const file = e.dataTransfer && e.dataTransfer.files && e.dataTransfer.files[0];
            if (file) {
                dtiUpload.files = e.dataTransfer.files;
                // Update UI; assigning to input.files programmatically may be restricted
                if (dtiFileName) {
                    const sizeKB = Math.round(file.size / 1024);
                    dtiFileName.textContent = `${file.name} • ${sizeKB} KB`;
                }
                // Clear error if any
                dtiDropzone.classList.remove('border-red-500');
                const errorMsg = dtiDropzone.querySelector('.error-message');
                if (errorMsg) errorMsg.remove();
            }
        });
        dtiUpload.addEventListener('change', (e) => {
            const file = e.target.files && e.target.files[0];
            if (file && dtiFileName) {
                const sizeKB = Math.round(file.size / 1024);
                dtiFileName.textContent = `${file.name} • ${sizeKB} KB`;
            }
        });
    }
});

// =====
// CSS INJECTION FOR ANIMATIONS
// =====

const style = document.createElement('style');
style.textContent = `
    .ripple {
        position: absolute;
        border-radius: 50%;
        background: rgba(255, 255, 255, 0.6);
        transform: scale(0);
        animation: ripple-animation 0.6s ease-out;
        pointer-events: none;
    }

    
    @keyframes ripple-animation {
        to {
            transform: scale(2);
            opacity: 0;
        }
    }

    
    .modal-content {
        transform: scale(0.95);
        opacity: 0;
        transition: all 0.3s ease;
    }

    
    @keyframes spin {
        from { transform: rotate(0deg); }
        to { transform: rotate(360deg); }
    }

    .animate-spin {
        animation: spin 1s linear infinite;
    }

    
    .animate-spin {
        animation: spin 1s linear infinite;
    }
    
    @keyframes ping {
        75%, 100% {
            transform: scale(2);
            opacity: 0;
        }
    }

    .animate-ping {
        animation: ping 1s cubic-bezier(0, 0, 0.2, 1) infinite;
    }

    
    .animate-ping {
        animation: ping 1s cubic-bezier(0, 0, 0.2, 1) infinite;
    }
    
    .form-group input:focus,
    .form-group select:focus {
        transform: scale(1.01);
    }

    tbody tr {
        cursor: pointer;
    }

    
    tbody tr {
        cursor: pointer;
    }
    
    tbody tr:hover .rounded-full {
        transform: scale(1.1) rotate(5deg);
    }
`;
document.head.appendChild(style);
