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

    // Load contractors from API and render into table
    let currentPage = 1;
    let currentSearch = '';
    let currentRanking = 'all';
    let currentPeriod = '';

    function buildUrl(page = 1, search = '', ranking = '', period = '') {
        const params = new URLSearchParams();
        if (search) params.set('search', search);
        if (ranking) params.set('ranking', ranking);
        if (period) params.set('period', period);
        if (page && page > 1) params.set('page', page);
        const qs = params.toString();
        return '/api/admin/contractors' + (qs ? ('?' + qs) : '');
    }

    async function loadContractors(page = 1) {
        const tableBody = document.getElementById('contractorsTable');
        const paginationContainer = document.getElementById('contractorsPagination');
        if (!tableBody) return;

        currentPage = page;

        try {
            const url = buildUrl(page, currentSearch, currentRanking, currentPeriod);
            const resp = await fetch(url, { credentials: 'same-origin' });
            if (!resp.ok) throw new Error('Network response was not ok');
            const json = await resp.json();

            // If paginated, json may be an object with data and meta fields
            const items = Array.isArray(json) ? json : (json.data || json || []);
            renderContractors(items, tableBody);

            // Render pagination if available
            const current = json.current_page || json.currentPage || page;
            const last = json.last_page || json.lastPage || (json.meta && json.meta.last_page) || 1;
            renderPagination(paginationContainer, current, last);
        } catch (err) {
            console.error('Failed to load contractors', err);
            tableBody.innerHTML = '<tr><td class="px-6 py-4" colspan="6">Failed to load contractors.</td></tr>';
            if (paginationContainer) paginationContainer.innerHTML = '';
        }
    }

    function renderContractors(items, tableBody) {
        if (!items || items.length === 0) {
            tableBody.innerHTML = '<tr><td class="px-6 py-4 text-center text-sm text-gray-500" colspan="6">No contractors found.</td></tr>';
            return;
        }

        tableBody.innerHTML = items.map(item => {
            const id = item.id ?? '';
            const name = escapeHtml(item.name || item.company_name || '—');
            const initials = getInitials(name);
            const dateRegistered = formatDate(item.created_at || item.date_registered || item.registration_date || '');
            const years = item.years_of_operation || item.years || '';
            const accountType = item.account_type || item.accountType || item.role || '';
            const totalProjects = item.total_projects ?? item.projects_count ?? 0;

            return `
                <tr class="hover:bg-gray-50 transition-all duration-200 group" data-id="${id}">
                  <td class="px-6 py-4">
                    <div class="flex items-center gap-3">
                      <div class="w-10 h-10 rounded-full bg-gradient-to-br from-blue-400 to-blue-600 flex items-center justify-center text-white font-bold shadow-md group-hover:shadow-lg transition-all group-hover:scale-110">${initials}</div>
                      <span class="font-medium text-gray-800 group-hover:text-indigo-600 transition">${name}</span>
                    </div>
                  </td>
                  <td class="px-6 py-4 text-center text-sm text-gray-600">${dateRegistered}</td>
                  <td class="px-6 py-4 text-center text-sm text-gray-600">${years ? years + ' years' : ''}</td>
                  <td class="px-6 py-4 text-center">
                    <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-blue-100 text-blue-700 transition-all duration-200 hover:scale-110 hover:shadow-lg">${accountType}</span>
                  </td>
                  <td class="px-6 py-4 text-center">
                    <span class="inline-flex items-center justify-center w-12 h-8 rounded-lg bg-gradient-to-br from-blue-50 to-indigo-50 text-sm font-bold text-indigo-700">${totalProjects}</span>
                  </td>
                  <td class="px-6 py-4">
                    <div class="flex items-center justify-center gap-2">
                      <button class="action-btn view-btn w-10 h-10 rounded-full bg-blue-50 hover:bg-blue-100 flex items-center justify-center transition-all duration-200 hover:scale-110" data-id="${id}">
                        <i class="fi fi-rr-eye text-blue-600"></i>
                      </button>
                      <button class="action-btn edit-btn w-10 h-10 rounded-full bg-yellow-50 hover:bg-yellow-100 flex items-center justify-center transition-all duration-200 hover:scale-110" data-id="${id}" data-name="${name}" data-initials="${initials}" data-date="${item.created_at || item.date_registered || ''}" data-years="${years}" data-account-type="${accountType}" data-contact="${item.contact || ''}" data-license="${item.license || ''}" data-email="${item.email || ''}" data-username="${item.username || ''}">
                        <i class="fi fi-rr-edit text-yellow-600"></i>
                      </button>
                      <button class="action-btn delete-btn w-10 h-10 rounded-full bg-red-50 hover:bg-red-100 flex items-center justify-center transition-all duration-200 hover:scale-110" data-id="${id}" data-name="${name}">
                        <i class="fi fi-rr-trash text-red-600"></i>
                      </button>
                    </div>
                  </td>
                </tr>`;
        }).join('');

        // After rendering, attach event listeners to the new buttons
        attachRowEventListeners();
    }

    function attachRowEventListeners() {
        const editButtonsNew = document.querySelectorAll('#contractorsTable .edit-btn');
        const deleteButtonsNew = document.querySelectorAll('#contractorsTable .delete-btn');
        const viewButtonsNew = document.querySelectorAll('#contractorsTable .view-btn');

        editButtonsNew.forEach(button => {
            button.removeEventListener('click', onEditClick);
            button.addEventListener('click', onEditClick);
        });

        deleteButtonsNew.forEach(button => {
            button.removeEventListener('click', onDeleteClick);
            button.addEventListener('click', onDeleteClick);
        });

        viewButtonsNew.forEach(button => {
            button.removeEventListener('click', onViewClick);
            button.addEventListener('click', onViewClick);
        });
    }

    function renderPagination(container, current, last) {
        if (!container) return;
        if (!last || last <= 1) {
            container.innerHTML = '';
            return;
        }

        const createBtn = (label, page, disabled = false) => {
            return `<button class="px-3 py-1 rounded-md mx-1 ${disabled ? 'bg-gray-100 text-gray-400' : 'bg-white border'}" data-page="${page}" ${disabled ? 'disabled' : ''}>${label}</button>`;
        };

        let html = '';
        html += createBtn('Prev', Math.max(1, current - 1), current === 1);

        // show a window of pages
        const start = Math.max(1, current - 2);
        const end = Math.min(last, current + 2);
        for (let p = start; p <= end; p++) {
            const active = p === current ? 'bg-indigo-600 text-white' : 'bg-white';
            html += `<button class="px-3 py-1 rounded-md mx-1 ${active}" data-page="${p}">${p}</button>`;
        }

        html += createBtn('Next', Math.min(last, current + 1), current === last);

        container.innerHTML = `<div class="flex items-center justify-center">${html}</div>`;

        container.querySelectorAll('button[data-page]').forEach(btn => {
            btn.addEventListener('click', function() {
                const page = parseInt(this.getAttribute('data-page'), 10);
                if (!isNaN(page)) loadContractors(page);
            });
        });
    }

    function onEditClick(e) {
        addRipple(this || e.currentTarget, e);
        const btn = this || e.currentTarget;
        const row = btn.closest('tr');
        const data = {
            name: btn.getAttribute('data-name') || row.querySelector('td:first-child span').textContent.trim(),
            initials: btn.getAttribute('data-initials') || row.querySelector('.rounded-full').textContent.trim(),
            dateRegistered: btn.getAttribute('data-date') || '',
            years: btn.getAttribute('data-years') || '',
            accountType: btn.getAttribute('data-account-type') || '',
            contact: btn.getAttribute('data-contact') || '',
            license: btn.getAttribute('data-license') || '',
            email: btn.getAttribute('data-email') || '',
            username: btn.getAttribute('data-username') || ''
        };

        // Normalize date to ISO if possible
        const contractorData = {
            name: data.name,
            initials: data.initials,
            dateRegistered: data.dateRegistered ? new Date(data.dateRegistered).toISOString().slice(0,10) : '',
            years: data.years,
            accountType: data.accountType,
            contact: data.contact,
            license: data.license,
            email: data.email,
            username: data.username
        };

        openEditModal(contractorData);
    }

    function onDeleteClick(e) {
        addRipple(this || e.currentTarget, e);
        const btn = this || e.currentTarget;
        const row = btn.closest('tr');
        const name = btn.getAttribute('data-name') || row.querySelector('td:first-child span').textContent.trim();
        openDeleteModal(name, row);
    }

    function onViewClick(e) {
        addRipple(this || e.currentTarget, e);
        const btn = this || e.currentTarget;
        const id = btn.getAttribute('data-id');
        // Redirect to view page for the contractor id if endpoint exists
        if (id) {
            window.location.href = `/admin/user-management/contractor/${id}/view`;
        } else {
            window.location.href = '/admin/user-management/contractor/view';
        }
    }

    function formatDate(dateStr) {
        if (!dateStr) return '';
        const d = new Date(dateStr);
        if (isNaN(d)) return dateStr;
        const opts = { year: 'numeric', month: 'short', day: '2-digit' };
        return d.toLocaleDateString(undefined, opts);
    }

    function getInitials(name) {
        if (!name) return '';
        return name.split(' ').map(s => s.charAt(0)).slice(0,2).join('').toUpperCase();
    }

    function escapeHtml(unsafe) {
        return String(unsafe)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#039;');
    }

    // Immediately load contractors on page open
    loadContractors(1);

    // Wire up search input with debounce
    const searchInput = document.getElementById('contractorSearchInput');
    let searchTimer = null;
    if (searchInput) {
        searchInput.addEventListener('input', function() {
            clearTimeout(searchTimer);
            searchTimer = setTimeout(() => {
                currentSearch = this.value.trim();
                loadContractors(1);
            }, 400);
        });
    }

    // Wire ranking filter to reload
    const rankingFilter = document.getElementById('contractorRankingFilter');
    if (rankingFilter) {
        rankingFilter.addEventListener('change', function() {
            currentRanking = this.value;
            loadContractors(1);
        });
    }

    // Wire period options to reload (some pages use different markup for period options)
    document.querySelectorAll('.period-option').forEach(opt => {
        opt.addEventListener('click', function(e) {
            e.preventDefault();
            const val = this.getAttribute('data-period') || this.textContent.trim();
            currentPeriod = val;
            loadContractors(1);
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
    // RANKING FILTER (legacy id guard)
    // ========================================
    
    const legacyRankingFilter = document.getElementById('rankingFilter');
    if (legacyRankingFilter) {
        legacyRankingFilter.addEventListener('change', function() {
            const value = this.value;
            // Placeholder for filter functionality
            console.log('Filter by (legacy):', value);
        });
    }

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
