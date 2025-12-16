document.addEventListener('DOMContentLoaded', function() {
    // ========================================
    // ELEMENT REFERENCES
    // ========================================

    // Filters
    const dateFromInput = document.getElementById('dateFrom');
    const dateToInput = document.getElementById('dateTo');
    const searchInput = document.getElementById('searchInput');
    const resetBtn = document.getElementById('resetFilterBtn');
    const contractorsWrap = document.getElementById('contractorsTableWrap');

    let debounceTimer;

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
    let rowToDelete = null;

    // ========================================
    // FILTER FUNCTIONALITY
    // ========================================

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

    // ========================================
    // ADD CONTRACTOR MODAL FUNCTIONALITY
    // ========================================

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

    if (addContractorBtn) addContractorBtn.addEventListener('click', openAddModal);
    if (closeModalBtn) closeModalBtn.addEventListener('click', closeAddModal);
    if (cancelBtn) cancelBtn.addEventListener('click', closeAddModal);

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
    if (saveBtn) {
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
                closeDeleteModal();
            }
        }
    });

    // ========================================
    // GENERIC PASSWORD VISIBILITY TOGGLES
    // ========================================
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

    // ========================================
    // EDIT CONTRACTOR MODAL FUNCTIONALITY
    // ========================================

    function openEditModal(contractorData) {
        // Populate form with contractor data
        if (document.getElementById('editCompanyName')) document.getElementById('editCompanyName').value = contractorData.company_name || '';
        if (document.getElementById('editYearsOperation')) document.getElementById('editYearsOperation').value = contractorData.years_of_experience || '';
        // if (document.getElementById('editAccountType')) document.getElementById('editAccountType').value = contractorData.accountType; // Removed
        if (document.getElementById('editContactNumber')) document.getElementById('editContactNumber').value = contractorData.contact_number || '';
        if (document.getElementById('editLicenseNumber')) document.getElementById('editLicenseNumber').value = contractorData.license_number || '';
        if (document.getElementById('editRegistrationDate')) document.getElementById('editRegistrationDate').value = contractorData.created_at ? contractorData.created_at.split('T')[0] : '';
        if (document.getElementById('editEmail')) document.getElementById('editEmail').value = contractorData.email || ''; // Need to get from user relation
        if (document.getElementById('editUsername')) document.getElementById('editUsername').value = contractorData.username || ''; // Need to get from user relation

        if (editProfileInitials) editProfileInitials.textContent = contractorData.company_name ? contractorData.company_name.substring(0, 2).toUpperCase() : 'CO';

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
            editProfilePreview.classList.add('hidden');
            editProfileInitials.classList.remove('hidden');
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
                    editProfileInitials.classList.add('hidden');
                };
                reader.readAsDataURL(file);
            }
        });
    }

    // Save Edit Button with Loading State
    if (saveEditBtn) {
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
    }

    // Close edit modal on backdrop click
    if (editContractorModal) {
        editContractorModal.addEventListener('click', function(e) {
            if (e.target === editContractorModal) {
                closeEditModal();
            }
        });
    }

    // ========================================
    // DELETE CONTRACTOR MODAL FUNCTIONALITY
    // ========================================

    function openDeleteModal(contractorName, row) {
        rowToDelete = row;
        deleteContractorNameSpan.textContent = contractorName;
        deleteContractorModal.classList.remove('hidden');
        deleteContractorModal.classList.add('flex');
        document.body.style.overflow = 'hidden';
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
        }, 300);
    }

    if (confirmDeleteBtn) {
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

    // ========================================
    // TABLE ACTION BUTTONS & PAGINATION
    // ========================================

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
                openDeleteModal(name, row);
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

    // ========================================
    // HELPER FUNCTIONS
    // ========================================

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

    // ========================================
    // INPUT FOCUS EFFECTS
    // ========================================

    const allInputs = document.querySelectorAll('input, select, textarea');
    allInputs.forEach(input => {
        input.addEventListener('focus', function() {
            this.parentElement.classList.add('ring-2', 'ring-orange-400');
        });

        input.addEventListener('blur', function() {
            this.parentElement.classList.remove('ring-2', 'ring-orange-400');
        });
    });

    // ========================================
    // DTI/SEC DROPZONE UPLOAD
    // ========================================
    const dtiDropzone = document.getElementById('dtiDropzone');
    const dtiUpload = document.getElementById('dtiUpload');
    const dtiFileName = document.getElementById('dtiFileName');

    if (dtiDropzone && dtiUpload) {
        const highlight = () => dtiDropzone.classList.add('ring-2', 'ring-orange-400');
        const unhighlight = () => dtiDropzone.classList.remove('ring-2', 'ring-orange-400');

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
                // Update UI; assigning to input.files programmatically may be restricted
                if (dtiFileName) {
                    const sizeKB = Math.round(file.size / 1024);
                    dtiFileName.textContent = `${file.name} • ${sizeKB} KB`;
                }
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

// ========================================
// CSS INJECTION FOR ANIMATIONS
// ========================================

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

    @keyframes ping {
        75%, 100% {
            transform: scale(2);
            opacity: 0;
        }
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

    tbody tr:hover .rounded-full {
        transform: scale(1.1) rotate(5deg);
    }
`;
document.head.appendChild(style);
