document.addEventListener('DOMContentLoaded', function() {
    // ========================================
    // ELEMENT REFERENCES
    // ========================================
    
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
    
    // Table Action Buttons
    const editButtons = document.querySelectorAll('.edit-btn');
    const deleteButtons = document.querySelectorAll('.delete-btn');
    const viewButtons = document.querySelectorAll('.view-btn');
    
    // ========================================
    // PERIOD DROPDOWN FUNCTIONALITY
    // ========================================
    
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
    
    // ========================================
    // TABLE ACTION BUTTONS
    // ========================================
    
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
    // RANKING FILTER
    // ========================================
    
    const rankingFilter = document.getElementById('rankingFilter');
    rankingFilter.addEventListener('change', function() {
        const value = this.value;
        // Placeholder for filter functionality
        console.log('Filter by:', value);
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
