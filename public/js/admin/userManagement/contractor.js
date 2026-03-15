document.addEventListener('DOMContentLoaded', function () {
    try {
        const dateFromInput = document.getElementById('dateFrom');
    const dateToInput = document.getElementById('dateTo');
    const searchInput = document.getElementById('searchInput') || document.getElementById('topNavSearch');
    const resetBtn = document.getElementById('resetFilterBtn');
    const contractorsWrap = document.getElementById('contractorsTableWrap');

    let debounceTimer;

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

            window.history.pushState({}, '', url);

            attachPaginationListeners();

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

    function attachPaginationListeners() {
        const paginationLinks = document.querySelectorAll('#contractorsTableWrap .contractor-page-link, #contractorsTableWrap .pagination a');
        paginationLinks.forEach(link => {
            link.addEventListener('click', function (e) {
                e.preventDefault();
                const url = this.href;
                fetchAndUpdate(url);
            });
        });
    }

    if (dateFromInput) {
        dateFromInput.addEventListener('change', function () {
            if (this.value) {
                dateToInput.min = this.value;
                // If dateTo is already set and is before dateFrom, clear it
                if (dateToInput.value && dateToInput.value < this.value) {
                    dateToInput.value = '';
                }
            }
            handleFilterChange();
        });
    }
    if (dateToInput) {
        dateToInput.addEventListener('change', function () {
            if (this.value) {
                dateFromInput.max = this.value;
                // If dateFrom is already set and is after dateTo, clear it
                if (dateFromInput.value && dateFromInput.value > this.value) {
                    dateFromInput.value = '';
                }
            }
            handleFilterChange();
        });
    }
    if (searchInput) searchInput.addEventListener('input', handleSearchInput);

    if (resetBtn) {
        resetBtn.addEventListener('click', function () {
            if (dateFromInput) dateFromInput.value = '';
            if (dateToInput) dateToInput.value = '';
            if (searchInput) searchInput.value = '';
            handleFilterChange();
        });
    }

    attachPaginationListeners();

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

    function attachActionListeners() {
        console.log('[contractor.js] attachActionListeners');
        const contractorsTable = document.getElementById('contractorsTable');

        // Use single delegated listener to avoid duplicate bindings and event conflicts
        if (contractorsTable && !contractorsTable.dataset.delegationAttached) {
            contractorsTable.addEventListener('click', function (e) {
                const btn = e.target.closest('.action-btn');
                if (!btn || !contractorsTable.contains(btn)) return;

                e.stopPropagation();
                addRipple(btn, e);

                // Debug
                try { console.log('[contractor.js] button click:', btn.className); } catch (e) {}

                // View
                if (btn.classList.contains('view-btn')) {
                    const id = btn.getAttribute('data-id');
                    try { console.log('[contractor.js] view-btn id=', id); } catch (e) {}
                    if (id) {
                        setTimeout(() => {
                            window.location.href = `/admin/user-management/contractor/view?id=${id}`;
                        }, 200);
                    }
                    return;
                }

                // Edit
                if (btn.classList.contains('edit-btn')) {
                    const id = btn.getAttribute('data-id');
                    try { console.log('[contractor.js] edit-btn id=', id); } catch (e) {}
                    if (id) {
                        setTimeout(() => {
                            openEditModal(id);
                        }, 200);
                    }
                    return;
                }

                // Delete
                if (btn.classList.contains('delete-btn')) {
                    const row = btn.closest('tr');
                    const nameEl = row ? row.querySelector('.font-medium') : null;
                    const name = nameEl ? nameEl.textContent : '';
                    const id = btn.getAttribute('data-id');
                    try { console.log('[contractor.js] delete-btn id=', id, 'name=', name); } catch (e) {}
                    setTimeout(() => {
                        openDeleteModal(name, row, id);
                    }, 200);
                    return;
                }
            });

            contractorsTable.dataset.delegationAttached = '1';
            try { console.log('[contractor.js] delegation attached to #contractorsTable'); } catch (e) {}
        }

        // Row highlight behavior (keeps previous behavior)
        const tableRows = document.querySelectorAll('#contractorsTable tr');
        tableRows.forEach(row => {
            row.addEventListener('click', function () {
                tableRows.forEach(r => r.classList.remove('bg-indigo-50'));
                this.classList.add('bg-indigo-50');
            });
        });
    }

    attachActionListeners();

    // Helper function to show validation errors in modal alert
    function showModalErrors(errors) {
        const errorAlert = document.getElementById('addContractorErrorAlert');
        const errorList = document.getElementById('addContractorErrorList');
        
        if (!errorAlert || !errorList) return;
        
        // Clear previous errors
        errorList.innerHTML = '';
        
        // Add all errors to list
        if (Array.isArray(errors)) {
            errors.forEach(error => {
                const li = document.createElement('li');
                li.textContent = error;
                errorList.appendChild(li);
            });
        } else if (typeof errors === 'object') {
            Object.values(errors).forEach(messages => {
                if (Array.isArray(messages)) {
                    messages.forEach(msg => {
                        const li = document.createElement('li');
                        li.textContent = msg;
                        errorList.appendChild(li);
                    });
                } else {
                    const li = document.createElement('li');
                    li.textContent = messages;
                    errorList.appendChild(li);
                }
            });
        }
        
        // Show the alert
        errorAlert.classList.remove('hidden');
        
        // Scroll to top of modal
        const modalBody = document.querySelector('#addContractorModal .add-contractor-modal-scroll');
        if (modalBody) {
            modalBody.scrollTop = 0;
        }
    }

    function clearModalErrors() {
        const errorAlert = document.getElementById('addContractorErrorAlert');
        if (errorAlert) {
            errorAlert.classList.add('hidden');
        }
    }

    // Helper functions for Edit Contractor Modal errors
    function showEditModalErrors(errors) {
        const errorAlert = document.getElementById('editContractorErrorAlert');
        const errorList = document.getElementById('editContractorErrorList');
        
        if (!errorAlert || !errorList) return;
        
        // Clear previous errors
        errorList.innerHTML = '';
        
        // Add all errors to list
        if (Array.isArray(errors)) {
            errors.forEach(error => {
                const li = document.createElement('li');
                li.textContent = error;
                errorList.appendChild(li);
            });
        } else if (typeof errors === 'object') {
            Object.values(errors).forEach(messages => {
                if (Array.isArray(messages)) {
                    messages.forEach(msg => {
                        const li = document.createElement('li');
                        li.textContent = msg;
                        errorList.appendChild(li);
                    });
                } else {
                    const li = document.createElement('li');
                    li.textContent = messages;
                    errorList.appendChild(li);
                }
            });
        }
        
        // Show the alert
        errorAlert.classList.remove('hidden');
        
        // Scroll to top of modal
        const modalBody = document.querySelector('#editContractorModal .edit-contractor-modal-scroll');
        if (modalBody) {
            modalBody.scrollTop = 0;
        }
    }

    function clearEditModalErrors() {
        const errorAlert = document.getElementById('editContractorErrorAlert');
        if (errorAlert) {
            errorAlert.classList.add('hidden');
        }
    }

    // Wire up close button for edit modal error alert
    const closeEditErrorAlertBtn = document.getElementById('closeEditErrorAlert');
    if (closeEditErrorAlertBtn) {
        closeEditErrorAlertBtn.addEventListener('click', clearEditModalErrors);
    }

    const addBtn = document.querySelector('#addContractorBtn');
    const modal = document.getElementById('addContractorModal');
    const closeModalBtn = document.getElementById('closeModalBtn');
    const cancelBtn = document.getElementById('cancelBtn');
    const saveBtn = document.getElementById('saveBtn');

    if (addBtn && modal) {

        addBtn.addEventListener('click', function () {
            modal.classList.remove('hidden');
            document.body.style.overflow = 'hidden';
            
            // Clear any previous errors
            clearModalErrors();
            modal.querySelectorAll('.add-contractor-field.error').forEach(el => el.classList.remove('error'));
            modal.querySelectorAll('.add-contractor-error').forEach(el => el.classList.add('hidden'));

            const modalContent = modal.querySelector('.modal-content');
            if (!modalContent) return;
            modalContent.style.transform = 'scale(0.9)';
            modalContent.style.opacity = '0';

            setTimeout(() => {
                modalContent.style.transition = 'all 0.3s ease';
                modalContent.style.transform = 'scale(1)';
                modalContent.style.opacity = '1';
            }, 10);
        });

        const closeModal = () => {
            const modalContent = modal.querySelector('.modal-content');
            if (!modalContent) return;
            modalContent.style.transform = 'scale(0.9)';
            modalContent.style.opacity = '0';

            setTimeout(() => {
                modal.classList.add('hidden');
                document.body.style.overflow = 'auto';

                resetModalForm();
            }, 300);
        };

        if (closeModalBtn) {
            closeModalBtn.addEventListener('click', closeModal);
        }
        if (cancelBtn) {
            cancelBtn.addEventListener('click', closeModal);
        }

        modal.addEventListener('click', function (e) {
            if (e.target === modal) {
                closeModal();
            }
        });

        document.addEventListener('keydown', function (e) {
            if (e.key === 'Escape' && !modal.classList.contains('hidden')) {
                closeModal();
            }
        });

        if (saveBtn) {
            saveBtn.addEventListener('click', async function () {
            // Clear previous errors
            clearModalErrors();
            modal.querySelectorAll('.add-contractor-field.error').forEach(el => el.classList.remove('error'));
            modal.querySelectorAll('.add-contractor-error').forEach(el => el.classList.add('hidden'));

            const formData = new FormData();

            const inputs = modal.querySelectorAll('input, select, textarea');
            inputs.forEach(input => {
                if (input.type === 'file') {
                    if (input.files[0]) {
                        formData.append(input.name, input.files[0]);
                    }
                } else if (input.type === 'checkbox' || input.type === 'radio') {
                    if (input.checked) {
                        formData.append(input.name, input.value);
                    }
                } else if (input.type === 'hidden') {
                    // Only append hidden inputs if they have a value
                    if (input.value && input.value.trim() !== '') {
                        formData.append(input.name, input.value);
                    }
                } else if (input.tagName === 'SELECT') {

                    if (input.id === 'contractor_address_province' || input.id === 'contractor_address_city' || input.id === 'contractor_address_barangay') {
                        if (input.selectedIndex > 0) {
                            const name = input.options[input.selectedIndex].getAttribute('data-name');
                            const fieldName = input.id === 'contractor_address_province' ? 'business_address_province' :
                                (input.id === 'contractor_address_city' ? 'business_address_city' : 'business_address_barangay');
                            formData.append(fieldName, name);
                        }
                    } else {
                        formData.append(input.name, input.value);
                    }
                } else {
                    formData.append(input.name, input.value);
                }
            });

            const csrfToken = document.querySelector('meta[name="csrf-token"]');
            if (csrfToken) {
                formData.append('_token', csrfToken.content);
            }

            // Debug: Log the owner_id value
            const ownerIdDebug = document.getElementById('selectedOwnerId');
            console.log('=== FORM SUBMISSION DEBUG ===');
            console.log('Owner ID input element:', ownerIdDebug);
            console.log('Owner ID value before submit:', ownerIdDebug ? ownerIdDebug.value : 'not found');
            console.log('Owner ID value length:', ownerIdDebug ? ownerIdDebug.value.length : 0);
            console.log('FormData has owner_id:', formData.has('owner_id'));
            console.log('FormData owner_id value:', formData.get('owner_id'));
            console.log('All FormData entries:');
            for (let [key, value] of formData.entries()) {
                console.log(`  ${key}:`, value);
            }
            console.log('=== END DEBUG ===');

            const originalText = this.innerHTML;
            this.innerHTML = '<i class="fi fi-rr-spinner animate-spin"></i> Saving...';
            this.disabled = true;

            modal.querySelectorAll('.error-message').forEach(el => el.remove());
            modal.querySelectorAll('.border-red-500').forEach(el => el.classList.remove('border-red-500'));

            try {
                const response = await fetch('/admin/user-management/contractors/store', {
                    method: 'POST',
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    body: formData
                });

                const result = await response.json();

                if (response.ok) {
                    showNotification('Contractor added successfully!', 'success');
                    closeModal();

                    handleFilterChange();
                } else {
                    if (result.errors) {
                        const errorMessages = [];
                        
                        for (const [key, messages] of Object.entries(result.errors)) {
                            const errorMsg = Array.isArray(messages) ? messages[0] : messages;
                            errorMessages.push(errorMsg);
                            
                            // Mark field with error class and show inline error
                            if (key === 'owner_id') {
                                const ownerSearch = modal.querySelector('#ownerSearchInput');
                                if (ownerSearch) {
                                    ownerSearch.classList.add('error');
                                    const errorElement = ownerSearch.parentElement.querySelector('.add-contractor-error');
                                    if (errorElement) {
                                        errorElement.textContent = errorMsg;
                                        errorElement.classList.remove('hidden');
                                    }
                                }
                            } else {
                                // Try to find the input by name
                                let input = modal.querySelector(`[name="${key}"]`);
                                
                                // If not found, try by data-field attribute
                                if (!input) {
                                    input = modal.querySelector(`[data-field="${key}"]`);
                                }
                                
                                if (input) {
                                    // Handle file upload dropzones
                                    if (input.type === 'file' && input.id === 'dtiUpload') {
                                        const dropzone = document.getElementById('dtiDropzone');
                                        if (dropzone) {
                                            dropzone.classList.add('error');
                                            const errorElement = dropzone.parentElement.querySelector('.add-contractor-error');
                                            if (errorElement) {
                                                errorElement.textContent = errorMsg;
                                                errorElement.classList.remove('hidden');
                                            }
                                        }
                                    } else {
                                        // Add error class to the input
                                        input.classList.add('error');
                                        
                                        // Find and show the error message element
                                        const errorElement = input.parentElement.querySelector('.add-contractor-error');
                                        if (errorElement) {
                                            errorElement.textContent = errorMsg;
                                            errorElement.classList.remove('hidden');
                                        }
                                    }
                                }
                            }
                        }
                        
                        // Don't show the modal alert, only inline errors
                        // showModalErrors(errorMessages);
                    } else {
                        showModalErrors([result.message || 'An error occurred']);
                    }
                }
            } catch (error) {
                console.error('Error:', error);
                showModalErrors(['An unexpected error occurred']);
            } finally {
                this.innerHTML = originalText;
                this.disabled = false;
            }
            });
        }
    }

    // Owner live-search for Add Contractor modal
    const ownerSearchInput = document.getElementById('ownerSearchInput');
    const ownerSearchResults = document.getElementById('ownerSearchResults');
    const selectedOwnerIdInput = document.getElementById('selectedOwnerId');
    const selectedOwnerName = document.getElementById('selectedOwnerName');
    const selectedOwnerEmail = document.getElementById('selectedOwnerEmail');
    const selectedOwnerSummary = document.getElementById('selectedOwnerSummary');
    const clearSelectedOwnerBtn = document.getElementById('clearSelectedOwner');

    if (ownerSearchInput) {
        let ownerDebounce;
        ownerSearchInput.addEventListener('input', function (e) {
            clearTimeout(ownerDebounce);
            const q = this.value.trim();
            if (!q) {
                if (ownerSearchResults) ownerSearchResults.classList.add('hidden');
                return;
            }
            ownerDebounce = setTimeout(async () => {
                try {
                    const res = await fetch(`/api/admin/users/property-owners?search=${encodeURIComponent(q)}&eligible=1`, { headers: { 'X-Requested-With': 'XMLHttpRequest' } });
                    if (!res.ok) throw new Error('Network error');
                    const json = await res.json();
                    const owners = json.data || json;
                    if (!ownerSearchResults) return;
                    ownerSearchResults.innerHTML = '';
                    if (!owners || owners.length === 0) {
                        ownerSearchResults.innerHTML = '<div class="p-2 text-sm text-gray-500">No results</div>';
                        ownerSearchResults.classList.remove('hidden');
                        return;
                    }
                    owners.forEach(owner => {
                        const div = document.createElement('div');
                        div.className = 'p-2 hover:bg-gray-50 cursor-pointer flex items-center gap-3';
                        div.innerHTML = `<div class="flex-1 text-sm"><div class="font-medium">${owner.first_name || ''} ${owner.last_name || ''}</div><div class="text-xs text-gray-500">${owner.email || ''}</div></div>`;
                        div.dataset.ownerId = owner.owner_id;
                        div.dataset.firstName = owner.first_name || '';
                        div.dataset.lastName = owner.last_name || '';
                        div.dataset.email = owner.email || '';
                        div.addEventListener('click', function () {
                            if (selectedOwnerIdInput) {
                                selectedOwnerIdInput.value = this.dataset.ownerId;
                                console.log('Owner selected! ID:', this.dataset.ownerId);
                            }
                            if (selectedOwnerName) selectedOwnerName.textContent = `${this.dataset.firstName} ${this.dataset.lastName}`;
                            if (selectedOwnerEmail) selectedOwnerEmail.textContent = this.dataset.email;
                            if (selectedOwnerSummary) selectedOwnerSummary.classList.remove('hidden');
                            ownerSearchResults.classList.add('hidden');
                            ownerSearchInput.value = '';

                            // Clear any inline error on the owner search input
                            const ownerSearchEl = document.getElementById('ownerSearchInput');
                            if (ownerSearchEl) {
                                ownerSearchEl.classList.remove('border-red-500');
                                ownerSearchEl.classList.remove('error');
                                const err = ownerSearchEl.parentElement.querySelector('.error-message');
                                if (err) err.remove();
                                
                                // Clear inline error message
                                const errorElement = ownerSearchEl.parentElement.querySelector('.add-contractor-error');
                                if (errorElement) {
                                    errorElement.classList.add('hidden');
                                    errorElement.textContent = '';
                                }
                            }

                            // Also remove any error attached to the hidden input parent
                            if (selectedOwnerIdInput && selectedOwnerIdInput.parentElement) {
                                const hiddenErr = selectedOwnerIdInput.parentElement.querySelector('.error-message');
                                if (hiddenErr) hiddenErr.remove();
                            }

                            // Prefill representative name inputs with the owner's identity (do NOT prefill company email)
                            const firstNameInput = document.querySelector('#addContractorModal [name="first_name"]');
                            const lastNameInput = document.querySelector('#addContractorModal [name="last_name"]');
                            if (firstNameInput) firstNameInput.value = this.dataset.firstName;
                            if (lastNameInput) lastNameInput.value = this.dataset.lastName;
                        });
                        ownerSearchResults.appendChild(div);
                    });
                    ownerSearchResults.classList.remove('hidden');
                } catch (err) {
                    console.error('Owner search error', err);
                }
            }, 300);
        });

        // Close results when clicking outside
        document.addEventListener('click', function (e) {
            if (ownerSearchResults && !ownerSearchResults.contains(e.target) && e.target !== ownerSearchInput) {
                ownerSearchResults.classList.add('hidden');
            }
        });
    }

    if (clearSelectedOwnerBtn) {
        clearSelectedOwnerBtn.addEventListener('click', function () {
            if (selectedOwnerIdInput) selectedOwnerIdInput.value = '';
            if (selectedOwnerName) selectedOwnerName.textContent = '';
            if (selectedOwnerEmail) selectedOwnerEmail.textContent = '';
            if (selectedOwnerSummary) selectedOwnerSummary.classList.add('hidden');
            // Optionally clear representative fields
            const firstNameInput = document.querySelector('#addContractorModal [name="first_name"]');
            const lastNameInput = document.querySelector('#addContractorModal [name="last_name"]');
            const companyEmailInput = document.querySelector('#addContractorModal [name="company_email"]');
            if (firstNameInput) firstNameInput.value = '';
            if (lastNameInput) lastNameInput.value = '';
            if (companyEmailInput) companyEmailInput.value = '';
            // Remove inline owner validation error if present
            const ownerSearchEl = document.getElementById('ownerSearchInput');
            if (ownerSearchEl) {
                ownerSearchEl.classList.remove('border-red-500');
                const err = ownerSearchEl.parentElement.querySelector('.error-message');
                if (err) err.remove();
            }
            if (selectedOwnerIdInput && selectedOwnerIdInput.parentElement) {
                const hiddenErr = selectedOwnerIdInput.parentElement.querySelector('.error-message');
                if (hiddenErr) hiddenErr.remove();
            }
        });
    }

    const contractorTypeSelect = document.getElementById('contractorTypeSelect');
    const contractorTypeOtherInput = document.getElementById('contractorTypeOtherInput');
    if (contractorTypeSelect && contractorTypeOtherInput) {
        contractorTypeSelect.addEventListener('change', function () {
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

    const provinceSelect = document.getElementById('contractor_address_province');
    const citySelect = document.getElementById('contractor_address_city');
    const barangaySelect = document.getElementById('contractor_address_barangay');

    if (provinceSelect) {
        provinceSelect.addEventListener('change', function () {
            const provinceCode = this.value;
            citySelect.innerHTML = '<option value="">Select City/Municipality</option>';
            barangaySelect.innerHTML = '<option value="">Select Barangay</option>';
            citySelect.disabled = true;
            barangaySelect.disabled = true;

            if (provinceCode) {
                fetch(`/api/psgc/provinces/${provinceCode}/cities`)
                    .then(response => response.json())
                    .then(json => {
                        const data = Array.isArray(json) ? json : (json.data || []);
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
        citySelect.addEventListener('change', function () {
            const cityCode = this.value;
            barangaySelect.innerHTML = '<option value="">Select Barangay</option>';
            barangaySelect.disabled = true;

            if (cityCode) {
                fetch(`/api/psgc/cities/${cityCode}/barangays`)
                    .then(response => response.json())
                    .then(json => {
                        const data = Array.isArray(json) ? json : (json.data || []);
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

    const profileUpload = document.getElementById('profileUpload');
    const profilePreview = document.getElementById('profilePreview');
    const profileIcon = document.getElementById('profileIcon');

    if (profileUpload && profilePreview && profileIcon) {
        profileUpload.addEventListener('change', function (e) {
            const file = e.target.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function (e) {
                    profilePreview.src = e.target.result;
                    profilePreview.classList.remove('hidden');
                    profileIcon.classList.add('hidden');
                };
                reader.readAsDataURL(file);
            }
        });
    }

    const dtiDropzone = document.getElementById('dtiDropzone');
    const dtiUpload = document.getElementById('dtiUpload');
    const dtiFileName = document.getElementById('dtiFileName');

    if (dtiDropzone && dtiUpload) {
        const highlight = () => dtiDropzone.classList.add('ring-2', 'ring-orange-400');
        const unhighlight = () => dtiDropzone.classList.remove('ring-2', 'ring-orange-400');

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

                const dataTransfer = new DataTransfer();
                dataTransfer.items.add(file);
                dtiUpload.files = dataTransfer.files;

                if (dtiFileName) {
                    const sizeKB = Math.round(file.size / 1024);
                    dtiFileName.textContent = `${file.name} • ${sizeKB} KB`;
                }

                dtiDropzone.classList.remove('border-red-500');
                const errorMsg = dtiDropzone.parentElement.querySelector('.error-message');
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

    function resetModalForm() {
        // Clear error alert
        clearModalErrors();
        
        const inputs = modal.querySelectorAll('input, select, textarea');
        inputs.forEach(input => {
            if (input.type === 'file') {
                input.value = '';
            } else if (input.type === 'checkbox' || input.type === 'radio') {
                input.checked = false;
            } else if (input.tagName === 'SELECT') {
                input.selectedIndex = 0;
            } else {
                input.value = '';
            }

            input.classList.remove('border-red-500', 'error');
        });

        // Clear individual field errors
        modal.querySelectorAll('.add-contractor-field.error').forEach(el => el.classList.remove('error'));
        modal.querySelectorAll('.add-contractor-error').forEach(el => el.classList.add('hidden'));
        modal.querySelectorAll('.error-message').forEach(el => el.remove());

        modal.querySelectorAll('.border-red-500').forEach(el => el.classList.remove('border-red-500'));

        if (profilePreview && profileIcon) {
            profilePreview.classList.add('hidden');
            profileIcon.classList.remove('hidden');
        }

        if (dtiFileName) {
            dtiFileName.textContent = '';
            if (dtiDropzone) dtiDropzone.classList.remove('border-orange-500', 'bg-orange-100', 'error');
        }

        if (citySelect) {
            citySelect.innerHTML = '<option value="">Select City/Municipality</option>';
            citySelect.disabled = true;
        }
        if (barangaySelect) {
            barangaySelect.innerHTML = '<option value="">Select Barangay</option>';
            barangaySelect.disabled = true;
        }

        // Clear owner selection UI (selected owner summary, hidden id, and search input/results)
        try {
            if (selectedOwnerIdInput) selectedOwnerIdInput.value = '';
            if (selectedOwnerName) selectedOwnerName.textContent = '';
            if (selectedOwnerEmail) selectedOwnerEmail.textContent = '';
            if (selectedOwnerSummary) selectedOwnerSummary.classList.add('hidden');
            if (ownerSearchInput) {
                ownerSearchInput.value = '';
                ownerSearchInput.classList.remove('border-red-500');
            }
            if (ownerSearchResults) {
                ownerSearchResults.innerHTML = '';
                ownerSearchResults.classList.add('hidden');
            }
        } catch (e) {
            // Defensive: ignore if elements are not present
        }
    }

    const modalInputs = document.querySelectorAll('#addContractorModal input, #addContractorModal select, #addContractorModal textarea');
    modalInputs.forEach(input => {
        input.addEventListener('focus', function () {
            this.parentElement.classList.add('transform', 'scale-[1.02]');
            this.style.transition = 'all 0.2s ease';
        });

        input.addEventListener('blur', function () {
            this.parentElement.classList.remove('transform', 'scale-[1.02]');
        });

        input.addEventListener('input', function () {
            if (this.classList.contains('border-red-500')) {
                this.classList.remove('border-red-500');
                const errorMsg = this.parentElement.querySelector('.error-message');
                if (errorMsg) {
                    errorMsg.remove();
                }
            }
        });

        if (input.tagName === 'SELECT') {
            input.addEventListener('change', function () {
                if (this.classList.contains('border-red-500')) {
                    this.classList.remove('border-red-500');
                    const errorMsg = this.parentElement.querySelector('.error-message');
                    if (errorMsg) {
                        errorMsg.remove();
                    }
                }
            });
        }
    });

    function addRipple(button, event) {
        const ripple = document.createElement('span');
        const rect = button.getBoundingClientRect();
        const size = Math.max(rect.width, rect.height);
        const x = event.clientX - rect.left - size / 2;
        const y = event.clientY - rect.top - size / 2;

        ripple.style.width = ripple.style.height = size + 'px';
        ripple.style.left = x + 'px';
        ripple.style.top = y + 'px';
        ripple.classList.add('ripple-effect');

        button.appendChild(ripple);

        setTimeout(() => {
            ripple.remove();
        }, 600);
    }

    const rows = document.querySelectorAll('#contractorsTable tr');
    rows.forEach((row, index) => {
        row.style.opacity = '0';
        row.style.transform = 'translateY(20px)';

        setTimeout(() => {
            row.style.transition = 'all 0.4s ease';
            row.style.opacity = '1';
            row.style.transform = 'translateY(0)';
        }, index * 50);
    });

    const editModal = document.getElementById('editContractorModal');
    const editModalContent = editModal ? editModal.querySelector('.modal-content') : null;
    const closeEditModalBtn = document.getElementById('closeEditModalBtn');
    const cancelEditBtn = document.getElementById('cancelEditBtn');
    const saveEditBtn = document.getElementById('saveEditBtn');
    const editProfileUpload = document.getElementById('editProfileUpload');
    const editProfilePreview = document.getElementById('editProfilePreview');
    const editProfileIcon = document.getElementById('editProfileIcon');

    async function openEditModal(contractorId) {
        try { console.log('[contractor.js] openEditModal contractorId=', contractorId); } catch (e) {}
        if (!editModal || !editModalContent) return;

        // Clear any previous errors
        clearEditModalErrors();
        editModal.querySelectorAll('.edit-contractor-field.error').forEach(el => el.classList.remove('error'));
        editModal.querySelectorAll('.edit-contractor-error').forEach(el => el.classList.add('hidden'));

        try {
            const response = await fetch(`/admin/user-management/contractors/${contractorId}/edit`, {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            });

            if (!response.ok) throw new Error('Failed to fetch contractor data');

            const result = await response.json();
            if (!result.success) throw new Error(result.error || 'Failed to fetch details');

            const data = result.data;

            // Use contractor_id here so the update URL receives the contractor's id
            document.getElementById('edit_user_id').value = data.contractor_id;
            document.getElementById('edit_company_name').value = data.company_name || '';
            document.getElementById('edit_company_start_date').value = data.company_start_date || '';
            const editCompanyPhoneEl = document.getElementById('edit_company_phone');
            if (editCompanyPhoneEl) editCompanyPhoneEl.value = data.company_phone || data.phone || '';

            const typeSelect = document.getElementById('edit_contractorTypeSelect');
            if (typeSelect) {
                typeSelect.value = data.type_id || '';
                if (data.type_id == 9) {
                    const otherInput = document.getElementById('edit_contractorTypeOtherInput');
                    if (otherInput) {
                        otherInput.classList.remove('hidden');
                        otherInput.value = data.contractor_type_other || '';
                    }
                }
            }

            document.getElementById('edit_services_offered').value = data.services_offered || '';
            document.getElementById('edit_company_website').value = data.company_website || '';
            document.getElementById('edit_company_social_media').value = data.company_social_media || '';

            const editFirstNameEl = document.getElementById('edit_first_name');
            if (editFirstNameEl) editFirstNameEl.value = data.first_name || '';
            const editMiddleNameEl = document.getElementById('edit_middle_name');
            if (editMiddleNameEl) editMiddleNameEl.value = data.middle_name || '';
            const editLastNameEl = document.getElementById('edit_last_name');
            if (editLastNameEl) editLastNameEl.value = data.last_name || '';
            document.getElementById('edit_company_email').value = data.company_email || '';
            document.getElementById('edit_username').value = data.username || '';

            document.getElementById('edit_business_address_street').value = data.business_address_street || '';
            document.getElementById('edit_business_address_postal').value = data.business_address_postal || '';

            document.getElementById('edit_picab_number').value = data.picab_number || '';
            document.getElementById('edit_picab_category').value = data.picab_category || '';
            document.getElementById('edit_picab_expiration_date').value = data.picab_expiration_date || '';
            document.getElementById('edit_business_permit_number').value = data.business_permit_number || '';

            const permitCitySelect = document.getElementById('edit_business_permit_city');
            if (permitCitySelect && data.business_permit_city) {
                const cityStr = String(data.business_permit_city).trim();
                for (let i = 0; i < permitCitySelect.options.length; i++) {
                    if (String(permitCitySelect.options[i].value).trim() === cityStr) {
                        permitCitySelect.selectedIndex = i;
                        break;
                    }
                }
            }

            document.getElementById('edit_business_permit_expiration').value = data.business_permit_expiration || '';
            document.getElementById('edit_tin_business_reg_number').value = data.tin_business_reg_number || '';

            const dtiLinkContainer = document.getElementById('editCurrentDtiFile');
            if (dtiLinkContainer) {
                if (data.dti_sec_registration_photo) {
                    dtiLinkContainer.classList.remove('hidden');

                    // Prefer .open-doc-btn (new modal viewer) and set its data-doc-src.
                    const openDocBtn = dtiLinkContainer.querySelector('.open-doc-btn');
                    if (openDocBtn) {
                        openDocBtn.setAttribute('data-doc-src', `/storage/${data.dti_sec_registration_photo}`);
                    } else {
                        // Fallback for older markup that used an <a>
                        const anchor = dtiLinkContainer.querySelector('a');
                        if (anchor) {
                            anchor.href = `/storage/${data.dti_sec_registration_photo}`;
                            // Mark the anchor so the delegated handler can pick it up
                            anchor.classList.add('open-doc-btn');
                            anchor.setAttribute('data-doc-src', `/storage/${data.dti_sec_registration_photo}`);
                        }
                    }
                } else {
                    dtiLinkContainer.classList.add('hidden');
                }
            }

            if (data.company_logo) {
                editProfilePreview.src = `/storage/${data.company_logo}`;
                editProfilePreview.classList.remove('hidden');
                editProfileIcon.classList.add('hidden');
            } else {
                editProfilePreview.classList.add('hidden');
                editProfileIcon.classList.remove('hidden');
            }

            // Populate owner information if exists
            const editSelectedOwnerIdInput = document.getElementById('edit_selectedOwnerId');
            const editSelectedOwnerSummary = document.getElementById('edit_selectedOwnerSummary');
            const editOwnerDisplayName = document.getElementById('edit_ownerDisplayName');
            const editOwnerDisplayEmail = document.getElementById('edit_ownerDisplayEmail');

            if (data.owner_id && editSelectedOwnerIdInput) {
                editSelectedOwnerIdInput.value = data.owner_id;
                
                if (editSelectedOwnerSummary && editOwnerDisplayName && editOwnerDisplayEmail) {
                    // Construct owner name from available data
                    const ownerName = [data.owner_first_name, data.owner_middle_name, data.owner_last_name]
                        .filter(Boolean)
                        .join(' ') || 'N/A';
                    
                    editOwnerDisplayName.textContent = ownerName;
                    editOwnerDisplayEmail.textContent = data.owner_email || 'N/A';
                    editSelectedOwnerSummary.classList.remove('hidden');
                }
            } else if (editSelectedOwnerSummary) {
                editSelectedOwnerSummary.classList.add('hidden');
                if (editSelectedOwnerIdInput) editSelectedOwnerIdInput.value = '';
            }

            const provinceSelect = document.getElementById('edit_contractor_address_province');
            const citySelect = document.getElementById('edit_contractor_address_city');
            const barangaySelect = document.getElementById('edit_contractor_address_barangay');

            let provinceCode = '';
            if (data.business_address_province && provinceSelect) {
                const ownerProvStr = String(data.business_address_province).trim();
                for (let i = 0; i < provinceSelect.options.length; i++) {
                    const optionName = provinceSelect.options[i].getAttribute('data-name');
                    const optionValue = provinceSelect.options[i].value;

                    if ((optionName && String(optionName).trim() === ownerProvStr) ||
                        (optionValue && String(optionValue).trim() === ownerProvStr)) {
                        provinceSelect.selectedIndex = i;
                        provinceCode = provinceSelect.options[i].value;
                        break;
                    }
                }
            }

            if (provinceCode && citySelect) {
                try {
                    const citiesResponse = await fetch(`/api/psgc/provinces/${provinceCode}/cities`);
                    const citiesJson = await citiesResponse.json();
                    const cities = Array.isArray(citiesJson) ? citiesJson : (citiesJson.data || []);

                    citySelect.innerHTML = '<option value="">Select City/Municipality</option>';
                    let cityCode = '';

                    if (data.business_address_city) {
                        const ownerCityStr = String(data.business_address_city).trim();
                        cities.forEach(city => {
                            const option = document.createElement('option');
                            option.value = city.code;
                            option.setAttribute('data-name', city.name);
                            option.textContent = city.name;

                            if ((city.name && String(city.name).trim() === ownerCityStr) ||
                                String(city.code).trim() === ownerCityStr) {
                                option.selected = true;
                                cityCode = city.code;
                            }
                            citySelect.appendChild(option);
                        });
                    } else {
                        cities.forEach(city => {
                            const option = document.createElement('option');
                            option.value = city.code;
                            option.setAttribute('data-name', city.name);
                            option.textContent = city.name;
                            citySelect.appendChild(option);
                        });
                    }
                    citySelect.disabled = false;

                    if (cityCode && barangaySelect) {
                        const barangaysResponse = await fetch(`/api/psgc/cities/${cityCode}/barangays`);
                        const barangaysJson = await barangaysResponse.json();
                        const barangays = Array.isArray(barangaysJson) ? barangaysJson : (barangaysJson.data || []);

                        barangaySelect.innerHTML = '<option value="">Select Barangay</option>';
                        if (data.business_address_barangay) {
                            const ownerBrgyStr = String(data.business_address_barangay).trim();
                            barangays.forEach(barangay => {
                                const option = document.createElement('option');
                                option.value = barangay.code;
                                option.setAttribute('data-name', barangay.name);
                                option.textContent = barangay.name;

                                if ((barangay.name && String(barangay.name).trim() === ownerBrgyStr) ||
                                    String(barangay.code).trim() === ownerBrgyStr) {
                                    option.selected = true;
                                }
                                barangaySelect.appendChild(option);
                            });
                        } else {
                            barangays.forEach(barangay => {
                                const option = document.createElement('option');
                                option.value = barangay.code;
                                option.setAttribute('data-name', barangay.name);
                                option.textContent = barangay.name;
                                barangaySelect.appendChild(option);
                            });
                        }
                        barangaySelect.disabled = false;
                    }
                } catch (err) {
                    console.error('Error fetching address data:', err);
                }
            }

            // Force-close delete modal if it's stuck open
            if (deleteModal && !deleteModal.classList.contains('hidden')) {
                deleteModal.classList.add('hidden');
                deleteModal.classList.remove('flex');
                if (deleteModalContent) {
                    deleteModalContent.classList.add('scale-95', 'opacity-0');
                    deleteModalContent.classList.remove('scale-100', 'opacity-100');
                }
            }

            // Capture form state before showing modal
            captureEditFormState();

            editModal.classList.remove('hidden');
            editModal.classList.add('flex');
            document.body.style.overflow = 'hidden';

            setTimeout(() => {
                editModalContent.classList.remove('scale-95', 'opacity-0');
                editModalContent.classList.add('scale-100', 'opacity-100');
            }, 10);

        } catch (error) {
            console.error('Error fetching contractor data:', error);
            showNotification('Failed to load contractor data', 'error');
        }
    }

    function closeEditModal() {
        if (!editModalContent) return;

        editModalContent.classList.remove('scale-100', 'opacity-100');
        editModalContent.classList.add('scale-95', 'opacity-0');

        setTimeout(() => {
            editModal.classList.add('hidden');
            editModal.classList.remove('flex');
            document.body.style.overflow = 'auto';

            const form = document.getElementById('editContractorForm');
            if (form) form.reset();

            // Clear modal errors
            clearEditModalErrors();
            editModal.querySelectorAll('.edit-contractor-field.error').forEach(el => el.classList.remove('error'));
            editModal.querySelectorAll('.edit-contractor-error').forEach(el => el.classList.add('hidden'));
            editModal.querySelectorAll('.error-message').forEach(el => el.remove());
            editModal.querySelectorAll('.border-red-500').forEach(el => el.classList.remove('border-red-500'));

            if (editProfilePreview) editProfilePreview.classList.add('hidden');
            if (editProfileIcon) editProfileIcon.classList.remove('hidden');
        }, 300);
    }

    if (closeEditModalBtn) {
        closeEditModalBtn.addEventListener('click', closeEditModal);
    }

    if (cancelEditBtn) {
        cancelEditBtn.addEventListener('click', closeEditModal);
    }

    if (editModal) {
        editModal.addEventListener('click', function (e) {
            if (e.target === editModal) {
                closeEditModal();
            }
        });
    }

    if (editProfileUpload && editProfilePreview && editProfileIcon) {
        editProfileUpload.addEventListener('change', function (e) {
            const file = e.target.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function (event) {
                    editProfilePreview.src = event.target.result;
                    editProfilePreview.classList.remove('hidden');
                    editProfileIcon.classList.add('hidden');
                };
                reader.readAsDataURL(file);
            }
        });
    }

    // Edit DTI/SEC Upload Handler
    const editDtiDropzone = document.getElementById('editDtiDropzone');
    const editDtiUpload = document.getElementById('editDtiUpload');
    const editDtiFileName = document.getElementById('editDtiFileName');

    if (editDtiDropzone && editDtiUpload) {
        const editHighlight = () => editDtiDropzone.classList.add('ring-2', 'ring-orange-400');
        const editUnhighlight = () => editDtiDropzone.classList.remove('ring-2', 'ring-orange-400');

        editDtiDropzone.addEventListener('click', () => editDtiUpload.click());

        ['dragenter', 'dragover'].forEach(evt => {
            editDtiDropzone.addEventListener(evt, (e) => {
                e.preventDefault();
                e.stopPropagation();
                editHighlight();
            });
        });

        ['dragleave', 'drop'].forEach(evt => {
            editDtiDropzone.addEventListener(evt, (e) => {
                e.preventDefault();
                e.stopPropagation();
                editUnhighlight();
            });
        });

        editDtiDropzone.addEventListener('drop', (e) => {
            const file = e.dataTransfer && e.dataTransfer.files && e.dataTransfer.files[0];
            if (file) {
                const dataTransfer = new DataTransfer();
                dataTransfer.items.add(file);
                editDtiUpload.files = dataTransfer.files;

                if (editDtiFileName) {
                    const sizeKB = Math.round(file.size / 1024);
                    editDtiFileName.textContent = `${file.name} • ${sizeKB} KB`;
                }

                editDtiDropzone.classList.remove('border-red-500');
                const errorMsg = editDtiDropzone.parentElement.querySelector('.error-message');
                if (errorMsg) errorMsg.remove();
            }
        });

        editDtiUpload.addEventListener('change', (e) => {
            const file = e.target.files && e.target.files[0];
            if (file && editDtiFileName) {
                const sizeKB = Math.round(file.size / 1024);
                editDtiFileName.textContent = `${file.name} • ${sizeKB} KB`;
            }
        });
    }

    const editProvince = document.getElementById('edit_contractor_address_province');
    const editCity = document.getElementById('edit_contractor_address_city');
    const editBarangay = document.getElementById('edit_contractor_address_barangay');

    if (editProvince) {
        editProvince.addEventListener('change', function () {
            const provinceCode = this.value;

            editCity.innerHTML = '<option value="">Loading...</option>';
            editCity.disabled = true;
            editBarangay.innerHTML = '<option value="">Select City First</option>';
            editBarangay.disabled = true;

            if (provinceCode) {
                fetch(`/api/psgc/provinces/${provinceCode}/cities`)
                    .then(response => response.json())
                    .then(json => {
                        const data = Array.isArray(json) ? json : (json.data || []);
                        editCity.innerHTML = '<option value="">Select City/Municipality</option>';
                        data.forEach(city => {
                            const option = document.createElement('option');
                            option.value = city.code;
                            option.setAttribute('data-name', city.name);
                            option.textContent = city.name;
                            editCity.appendChild(option);
                        });
                        editCity.disabled = false;
                    })
                    .catch(error => {
                        console.error('Error fetching cities:', error);
                        editCity.innerHTML = '<option value="">Error loading cities</option>';
                    });
            } else {
                editCity.innerHTML = '<option value="">Select Province First</option>';
            }
        });
    }

    if (editCity) {
        editCity.addEventListener('change', function () {
            const cityCode = this.value;

            editBarangay.innerHTML = '<option value="">Loading...</option>';
            editBarangay.disabled = true;

            if (cityCode) {
                fetch(`/api/psgc/cities/${cityCode}/barangays`)
                    .then(response => response.json())
                    .then(json => {
                        const data = Array.isArray(json) ? json : (json.data || []);
                        editBarangay.innerHTML = '<option value="">Select Barangay</option>';
                        data.forEach(barangay => {
                            const option = document.createElement('option');
                            option.value = barangay.code;
                            option.setAttribute('data-name', barangay.name);
                            option.textContent = barangay.name;
                            editBarangay.appendChild(option);
                        });
                        editBarangay.disabled = false;
                    })
                    .catch(error => {
                        console.error('Error fetching barangays:', error);
                        editBarangay.innerHTML = '<option value="">Error loading barangays</option>';
                    });
            } else {
                editBarangay.innerHTML = '<option value="">Select City First</option>';
            }
        });
    }

    const editContractorTypeSelect = document.getElementById('edit_contractorTypeSelect');
    const editContractorTypeOtherInput = document.getElementById('edit_contractorTypeOtherInput');
    if (editContractorTypeSelect && editContractorTypeOtherInput) {
        editContractorTypeSelect.addEventListener('change', function () {
            if (this.value == '9') {
                editContractorTypeOtherInput.classList.remove('hidden');
                editContractorTypeOtherInput.setAttribute('required', 'required');
            } else {
                editContractorTypeOtherInput.classList.add('hidden');
                editContractorTypeOtherInput.removeAttribute('required');
                editContractorTypeOtherInput.value = '';
            }
        });
    }

    // Store initial form state for change detection
    let editFormInitialState = {};

    function captureEditFormState() {
        editFormInitialState = {};
        const form = document.getElementById('editContractorForm');
        if (!form) return;
        
        const inputs = form.querySelectorAll('input, select, textarea');
        inputs.forEach(input => {
            if (input.type === 'file') {
                editFormInitialState[input.name] = input.files.length;
            } else if (input.type === 'checkbox' || input.type === 'radio') {
                editFormInitialState[input.name] = input.checked;
            } else {
                editFormInitialState[input.name] = input.value;
            }
        });
    }

    function hasEditFormChanged() {
        const form = document.getElementById('editContractorForm');
        if (!form) return false;
        
        const inputs = form.querySelectorAll('input, select, textarea');
        for (let input of inputs) {
            let currentValue;
            if (input.type === 'file') {
                currentValue = input.files.length;
            } else if (input.type === 'checkbox' || input.type === 'radio') {
                currentValue = input.checked;
            } else {
                currentValue = input.value;
            }
            
            if (currentValue !== editFormInitialState[input.name]) {
                return true;
            }
        }
        return false;
    }

    if (saveEditBtn) {
        saveEditBtn.addEventListener('click', async function (e) {
            e.preventDefault();

            // Check if form has changed
            if (!hasEditFormChanged()) {
                showEditModalErrors(['No changes detected. Please modify at least one field before saving.']);
                return;
            }

            // Clear previous errors
            clearEditModalErrors();
            editModal.querySelectorAll('.edit-contractor-field.error').forEach(el => el.classList.remove('error'));
            editModal.querySelectorAll('.edit-contractor-error').forEach(el => el.classList.add('hidden'));

            const form = document.getElementById('editContractorForm');
            const userId = document.getElementById('edit_user_id').value;
            const formData = new FormData(form);

            if (editProvince && editProvince.selectedIndex > 0) {
                const name = editProvince.options[editProvince.selectedIndex].getAttribute('data-name') || editProvince.options[editProvince.selectedIndex].text;
                formData.set('business_address_province', name);
            }
            if (editCity && editCity.selectedIndex > 0) {
                const name = editCity.options[editCity.selectedIndex].getAttribute('data-name') || editCity.options[editCity.selectedIndex].text;
                formData.set('business_address_city', name);
            }
            if (editBarangay && editBarangay.selectedIndex > 0) {
                const name = editBarangay.options[editBarangay.selectedIndex].getAttribute('data-name') || editBarangay.options[editBarangay.selectedIndex].text;
                formData.set('business_address_barangay', name);
            }

            const csrfToken = document.querySelector('meta[name="csrf-token"]');
            if (csrfToken) {
                formData.append('_token', csrfToken.content);
            }

            formData.append('_method', 'PUT');

            // Debug: Log owner_id value
            console.log('=== Edit Contractor Form Data ===');
            console.log('owner_id input element:', document.getElementById('edit_selectedOwnerId'));
            console.log('owner_id value:', document.getElementById('edit_selectedOwnerId')?.value);
            console.log('FormData has owner_id:', formData.has('owner_id'));
            console.log('FormData owner_id value:', formData.get('owner_id'));
            console.log('All FormData entries:');
            for (let [key, value] of formData.entries()) {
                if (value instanceof File) {
                    console.log(`${key}:`, value.name, `(${value.size} bytes)`);
                } else {
                    console.log(`${key}:`, value);
                }
            }
            console.log('================================');

            const originalContent = this.innerHTML;
            this.innerHTML = '<i class="fi fi-rr-spinner animate-spin"></i> Saving...';
            this.disabled = true;

            editModal.querySelectorAll('.error-message').forEach(el => el.remove());
            editModal.querySelectorAll('.border-red-500').forEach(el => el.classList.remove('border-red-500'));

            try {
                const response = await fetch(`/admin/user-management/contractors/update/${userId}`, {
                    method: 'POST',
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    body: formData
                });

                const result = await response.json();

                if (response.ok && result.success) {
                    showNotification('Contractor updated successfully!', 'success');
                    closeEditModal();

                    const isViewPage = document.querySelector('[data-contractor-id]');
                    if (isViewPage) {

                        refreshContractorDetails();
                    } else {
                        handleFilterChange(); // Refresh table on main page
                    }
                } else {
                    if (result.errors) {
                        const errorMessages = [];
                        
                        for (const [key, messages] of Object.entries(result.errors)) {
                            const errorMsg = Array.isArray(messages) ? messages[0] : messages;
                            errorMessages.push(errorMsg);
                            
                            // Mark field with error class and show inline message
                            const input = editModal.querySelector(`[name="${key}"]`);
                            if (input) {
                                if (input.classList.contains('edit-contractor-field')) {
                                    input.classList.add('error');
                                    const errorElement = input.parentElement.querySelector('.edit-contractor-error');
                                    if (errorElement) {
                                        errorElement.textContent = errorMsg;
                                        errorElement.classList.remove('hidden');
                                    }
                                } else {
                                    input.classList.add('border-red-500');
                                    const errorDisplay = document.createElement('p');
                                    errorDisplay.className = 'text-red-500 text-xs mt-1 error-message';
                                    errorDisplay.textContent = errorMsg;
                                    input.parentElement.appendChild(errorDisplay);
                                }
                            }
                        }
                        
                        // Show all errors in the modal alert
                        showEditModalErrors(errorMessages);
                    } else {
                        showEditModalErrors([result.message || 'An error occurred']);
                    }
                }
            } catch (error) {
                console.error('Error updating contractor:', error);
                showEditModalErrors(['An unexpected error occurred']);
            } finally {
                this.innerHTML = originalContent;
                this.disabled = false;
            }
        });
    }

    const editInputs = editModal ? editModal.querySelectorAll('input, select, textarea') : [];
    editInputs.forEach(input => {
        input.addEventListener('focus', function () {
            this.parentElement.classList.add('ring-2', 'ring-orange-200');
        });

        input.addEventListener('blur', function () {
            this.parentElement.classList.remove('ring-2', 'ring-orange-200');
        });

        input.addEventListener('input', function () {
            // Clear old-style error styling
            if (this.classList.contains('border-red-500')) {
                this.classList.remove('border-red-500');
                const errorMsg = this.parentElement.querySelector('.error-message');
                if (errorMsg) {
                    errorMsg.remove();
                }
            }
            
            // Clear new-style error styling
            if (this.classList.contains('edit-contractor-field') && this.classList.contains('error')) {
                this.classList.remove('error');
                const errorElement = this.parentElement.querySelector('.edit-contractor-error');
                if (errorElement) {
                    errorElement.classList.add('hidden');
                }
            }
        });

        if (input.tagName === 'SELECT') {
            input.addEventListener('change', function () {
                // Clear old-style error styling
                if (this.classList.contains('border-red-500')) {
                    this.classList.remove('border-red-500');
                    const errorMsg = this.parentElement.querySelector('.error-message');
                    if (errorMsg) {
                        errorMsg.remove();
                    }
                }
                
                // Clear new-style error styling
                if (this.classList.contains('edit-contractor-field') && this.classList.contains('error')) {
                    this.classList.remove('error');
                    const errorElement = this.parentElement.querySelector('.edit-contractor-error');
                    if (errorElement) {
                        errorElement.classList.add('hidden');
                    }
                }
            });
        }
    });

    const deleteModal = document.getElementById('deleteContractorModal');
    const deleteModalContent = deleteModal ? deleteModal.querySelector('.modal-content') : null;
    const closeDeleteModalBtn = document.getElementById('closeDeleteModalBtn');
    const confirmDeleteBtn = document.getElementById('confirmDeleteBtn');
    const cancelDeleteBtn = document.getElementById('cancelDeleteBtn');
    const deleteContractorNameSpan = document.getElementById('deleteContractorName');
    const deletionReasonInput = document.getElementById('deletionReason');
    const deletionReasonError = document.getElementById('deletionReasonError');
    let rowToDelete = null;
    let idToDelete = null;

    function openDeleteModal(contractorName, row, id) {
        try { console.log('[contractor.js] openDeleteModal', { contractorName, id }); } catch (e) {}

        // Re-query modal elements in case DOM was re-rendered
        const modalEl = document.getElementById('deleteContractorModal');
        const contentEl = modalEl ? modalEl.querySelector('.modal-content') : null;

        try { console.log('[contractor.js] modalEl, contentEl ->', !!modalEl, !!contentEl); } catch (e) {}

        if (!modalEl || !contentEl) {
            console.error('[contractor.js] delete modal elements not found in DOM');
            return;
        }

        // Force-close edit modal if it's stuck open
        if (editModal && !editModal.classList.contains('hidden')) {
            editModal.classList.add('hidden');
            editModal.classList.remove('flex');
            if (editModalContent) {
                editModalContent.classList.add('scale-95', 'opacity-0');
                editModalContent.classList.remove('scale-100', 'opacity-100');
            }
        }

        rowToDelete = row;
        idToDelete = id;

        if (deleteContractorNameSpan) {
            deleteContractorNameSpan.textContent = contractorName;
        }

        if (deletionReasonInput) {
            deletionReasonInput.value = '';
            deletionReasonInput.classList.remove('border-red-500');
        }
        if (deletionReasonError) {
            deletionReasonError.classList.add('hidden');
        }

        // Log computed styles before changing visibility
        try {
            const before = window.getComputedStyle(modalEl);
            console.log('[contractor.js] before show computed:', { display: before.display, visibility: before.visibility, opacity: before.opacity, zIndex: before.zIndex, position: before.position });
        } catch (e) {}

        // Attempt to move modal to document.body to avoid parent stacking-context issues
        try {
            if (modalEl && modalEl.parentElement && modalEl.parentElement !== document.body) {
                document.body.appendChild(modalEl);
                console.log('[contractor.js] moved delete modal to document.body to avoid stacking issues');
            }
            // Force inline z-index as an extra safeguard
            modalEl.style.zIndex = modalEl.style.zIndex || '100000';
            if (contentEl) contentEl.style.zIndex = contentEl.style.zIndex || '100001';
        } catch (err) {
            console.warn('[contractor.js] could not move modal to body:', err);
        }

        modalEl.classList.remove('hidden');
        modalEl.classList.add('flex');
        document.body.style.overflow = 'hidden';

        // Force a small delay to let CSS apply, then transition the content
        setTimeout(() => {
            try {
                contentEl.classList.remove('scale-95', 'opacity-0');
                contentEl.classList.add('scale-100', 'opacity-100');

                const after = window.getComputedStyle(modalEl);
                console.log('[contractor.js] after show computed:', { display: after.display, visibility: after.visibility, opacity: after.opacity, zIndex: after.zIndex, position: after.position });
                console.log('[contractor.js] modalEl.classList:', modalEl.className);
                console.log('[contractor.js] contentEl.classList:', contentEl.className);
            } catch (err) {
                console.error('[contractor.js] error during modal show transition:', err);
            }
        }, 10);
    }

    function closeDeleteModal() {
        if (!deleteModal) return;

        if (deleteModalContent) {
            deleteModalContent.classList.remove('scale-100', 'opacity-100');
            deleteModalContent.classList.add('scale-95', 'opacity-0');
        }

        setTimeout(() => {
            deleteModal.classList.add('hidden');
            deleteModal.classList.remove('flex');
            document.body.style.overflow = 'auto';
            rowToDelete = null;
            idToDelete = null;
        }, 300);
    }

    if (cancelDeleteBtn) {
        cancelDeleteBtn.addEventListener('click', closeDeleteModal);
    }

    if (closeDeleteModalBtn) {
        closeDeleteModalBtn.addEventListener('click', closeDeleteModal);
    }

    if (confirmDeleteBtn) {
        confirmDeleteBtn.addEventListener('click', async function () {
            if (!rowToDelete || !idToDelete) return;

            const reason = deletionReasonInput.value.trim();
            if (!reason) {
                deletionReasonInput.classList.add('border-red-500');
                deletionReasonError.classList.remove('hidden');
                return;
            } else {
                deletionReasonInput.classList.remove('border-red-500');
                deletionReasonError.classList.add('hidden');
            }

            const originalContent = this.innerHTML;
            this.innerHTML = '<i class="fi fi-rr-spinner animate-spin"></i> Deleting...';
            this.disabled = true;

            try {
                const csrfToken = document.querySelector('meta[name="csrf-token"]').content;
                const response = await fetch(`/admin/user-management/contractors/${idToDelete}`, {
                    method: 'POST',
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
                    showNotification(result.message || 'Failed to delete contractor', 'error');
                }
            } catch (error) {
                console.error('Error deleting contractor:', error);
                showNotification('An error occurred while deleting', 'error');
            } finally {

                this.innerHTML = originalContent;
                this.disabled = false;
            }
        });
    }

    if (deleteModal) {
        deleteModal.addEventListener('click', function (e) {
            if (e.target === deleteModal) {
                closeDeleteModal();
            }
        });
    }

    function showNotification(message, type = 'success') {
        const notification = document.createElement('div');
                notification.className = `fixed top-20 right-4 z-[60] max-w-[280px] px-3 py-2 rounded-md shadow-lg transform transition-all duration-500 translate-x-full ${type === 'success' ? 'bg-green-500' : 'bg-red-500'
                        } text-white text-xs font-semibold leading-tight flex items-center gap-1.5`;
        notification.innerHTML = `
            <i class="fi fi-rr-${type === 'success' ? 'check-circle' : 'cross-circle'} text-base"></i>
      <span>${message}</span>
    `;
        document.body.appendChild(notification);

        setTimeout(() => {
            notification.style.transform = 'translateX(0)';
        }, 10);

        setTimeout(() => {
            notification.style.transform = 'translateX(150%)';
            setTimeout(() => notification.remove(), 500);
        }, 3000);
    }

    // Removed old light-themed viewFileModal - now using dark UFV modal

    // Owner live-search for Edit Contractor modal
    const editOwnerSearchInput = document.getElementById('edit_ownerSearchInput');
    const editOwnerSearchResults = document.getElementById('edit_ownerSearchResults');
    const editSelectedOwnerIdInput = document.getElementById('edit_selectedOwnerId');
    const editSelectedOwnerSummary = document.getElementById('edit_selectedOwnerSummary');
    const editClearOwnerBtn = document.getElementById('edit_clearOwnerBtn');
    const editSelectedOwnerName = document.getElementById('edit_ownerDisplayName');
    const editSelectedOwnerEmail = document.getElementById('edit_ownerDisplayEmail');

    if (editOwnerSearchInput) {
        let editOwnerDebounce;
        editOwnerSearchInput.addEventListener('input', function (e) {
            clearTimeout(editOwnerDebounce);
            const q = this.value.trim();
            if (!q) {
                if (editOwnerSearchResults) editOwnerSearchResults.classList.add('hidden');
                return;
            }
            editOwnerDebounce = setTimeout(async () => {
                try {
                    const res = await fetch(`/api/admin/users/property-owners?search=${encodeURIComponent(q)}&eligible=1`, { headers: { 'X-Requested-With': 'XMLHttpRequest' } });
                    if (!res.ok) throw new Error('Network error');
                    const json = await res.json();
                    const owners = json.data || json;
                    if (!editOwnerSearchResults) return;
                    editOwnerSearchResults.innerHTML = '';
                    if (!owners || owners.length === 0) {
                        editOwnerSearchResults.innerHTML = '<div class="p-2 text-sm text-gray-500">No results</div>';
                        editOwnerSearchResults.classList.remove('hidden');
                        return;
                    }
                    owners.forEach(owner => {
                        const div = document.createElement('div');
                        div.className = 'p-2 hover:bg-gray-50 cursor-pointer flex items-center gap-3';
                        div.innerHTML = `<div class="flex-1 text-sm"><div class="font-medium">${owner.first_name || ''} ${owner.last_name || ''}</div><div class="text-xs text-gray-500">${owner.email || ''}</div></div>`;
                        div.dataset.ownerId = owner.owner_id;
                        div.dataset.firstName = owner.first_name || '';
                        div.dataset.lastName = owner.last_name || '';
                        div.dataset.email = owner.email || '';
                        div.addEventListener('click', function () {
                            if (editSelectedOwnerIdInput) editSelectedOwnerIdInput.value = this.dataset.ownerId;
                            if (editSelectedOwnerName) editSelectedOwnerName.textContent = `${this.dataset.firstName} ${this.dataset.lastName}`;
                            if (editSelectedOwnerEmail) editSelectedOwnerEmail.textContent = this.dataset.email;
                            if (editSelectedOwnerSummary) editSelectedOwnerSummary.classList.remove('hidden');
                            editOwnerSearchResults.classList.add('hidden');
                            editOwnerSearchInput.value = '';

                            // Clear any inline error on the owner search input
                            const editOwnerSearchEl = document.getElementById('edit_ownerSearchInput');
                            if (editOwnerSearchEl) {
                                editOwnerSearchEl.classList.remove('border-red-500');
                                const err = editOwnerSearchEl.parentElement.querySelector('.error-message');
                                if (err) err.remove();
                            }

                            // Prefill representative name inputs with the owner's identity
                            const firstNameInput = document.querySelector('#editContractorModal [name="first_name"]');
                            const lastNameInput = document.querySelector('#editContractorModal [name="last_name"]');
                            if (firstNameInput) firstNameInput.value = this.dataset.firstName;
                            if (lastNameInput) lastNameInput.value = this.dataset.lastName;
                        });
                        editOwnerSearchResults.appendChild(div);
                    });
                    editOwnerSearchResults.classList.remove('hidden');
                } catch (err) {
                    console.error('Edit owner search error', err);
                }
            }, 300);
        });

        // Close results when clicking outside
        document.addEventListener('click', function (e) {
            if (editOwnerSearchResults && !editOwnerSearchResults.contains(e.target) && e.target !== editOwnerSearchInput) {
                editOwnerSearchResults.classList.add('hidden');
            }
        });
    }

    if (editClearOwnerBtn) {
        editClearOwnerBtn.addEventListener('click', function () {
            if (editSelectedOwnerIdInput) editSelectedOwnerIdInput.value = '';
            if (editSelectedOwnerName) editSelectedOwnerName.textContent = '';
            if (editSelectedOwnerEmail) editSelectedOwnerEmail.textContent = '';
            if (editSelectedOwnerSummary) editSelectedOwnerSummary.classList.add('hidden');
            // Clear representative fields
            const firstNameInput = document.querySelector('#editContractorModal [name="first_name"]');
            const lastNameInput = document.querySelector('#editContractorModal [name="last_name"]');
            if (firstNameInput) firstNameInput.value = '';
            if (lastNameInput) lastNameInput.value = '';
            // Remove inline owner validation error if present
            const editOwnerSearchEl = document.getElementById('edit_ownerSearchInput');
            if (editOwnerSearchEl) {
                editOwnerSearchEl.classList.remove('border-red-500');
                const err = editOwnerSearchEl.parentElement.querySelector('.error-message');
                if (err) err.remove();
            }
        });
    }

    window.openDeleteModal = openDeleteModal;
    window.openEditModal = openEditModal;
    window.closeEditModal = closeEditModal;
    } catch (err) {
        console.error('[contractor.js] initialization error:', err);
    }
});



// ============================================
// Universal File Viewer (UFV) for Contractor Page
// ============================================
(function() {
    const modal = document.getElementById('documentViewerModal');
    const iframe = document.getElementById('documentViewerFrame');
    const img = document.getElementById('documentViewerImg');
    const closeBtn = document.getElementById('closeDocumentViewerBtn');

    if (!modal) return;

    function openDocumentViewer(src, title) {
        if (!modal) return;
        const isPdf = /\.pdf(\?|$)/i.test(src);
        const titleEl = document.getElementById('documentViewerTitle');
        const downloadLink = document.getElementById('documentViewerDownload');

        if (titleEl) titleEl.textContent = title || 'Document Viewer';
        if (downloadLink) downloadLink.href = src;

        if (isPdf) {
            if (iframe) {
                iframe.src = src;
                iframe.classList.remove('hidden');
            }
            if (img) img.classList.add('hidden');
        } else {
            if (img) {
                img.src = src;
                img.classList.remove('hidden');
            }
            if (iframe) iframe.classList.add('hidden');
        }

        modal.classList.remove('hidden');
        modal.classList.add('flex');
        document.body.style.overflow = 'hidden';

        const modalShell = modal.querySelector('.modal-shell');
        if (modalShell) {
            setTimeout(function() {
                modalShell.classList.remove('scale-95', 'opacity-0');
                modalShell.classList.add('scale-100', 'opacity-100');
            }, 10);
        }
    }

    function closeDocumentViewer() {
        if (!modal) return;
        const modalShell = modal.querySelector('.modal-shell');
        if (modalShell) {
            modalShell.classList.remove('scale-100', 'opacity-100');
            modalShell.classList.add('scale-95', 'opacity-0');
        }
        setTimeout(function() {
            modal.classList.add('hidden');
            modal.classList.remove('flex');
            document.body.style.overflow = 'auto';
            if (iframe) iframe.src = '';
            if (img) img.src = '';
        }, 200);
    }

    // Delegated click handler for open buttons
    document.addEventListener('click', function(e) {
        const btn = e.target.closest && e.target.closest('.open-doc-btn');
        if (btn) {
            e.preventDefault();
            const src = btn.getAttribute('data-doc-src');
            const title = btn.getAttribute('data-doc-title') || 'Document';
            if (src) {
                openDocumentViewer(src, title);
            }
        }
    });

    // Close button
    if (closeBtn) {
        closeBtn.addEventListener('click', closeDocumentViewer);
    }

    // Close on backdrop click
    if (modal) {
        modal.addEventListener('click', function(e) {
            if (e.target === modal) {
                closeDocumentViewer();
            }
        });
    }

    // Close on ESC key
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape' && modal && !modal.classList.contains('hidden')) {
            closeDocumentViewer();
        }
    });
})();
