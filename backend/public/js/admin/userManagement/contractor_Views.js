// Suspend Modal Elements
const suspendBtn = document.getElementById('suspendContractorBtn');
const suspendModal = document.getElementById('suspendAccountModal');
const closeSuspendBtn = document.getElementById('closeSuspendModalBtn');
const cancelSuspendBtn = document.getElementById('cancelSuspendBtn');
const confirmSuspendBtn = document.getElementById('confirmSuspendBtn');

// Edit Modal Elements
const editBtn = document.getElementById('editContractorBtn');
const editModal = document.getElementById('editContractorModal');
const closeEditBtn = document.getElementById('closeEditModalBtn');
const cancelEditBtn = document.getElementById('cancelEditBtn');
const saveEditBtn = document.getElementById('saveEditBtn');

// Edit Modal Tab Elements
const editCompanyTab = document.getElementById('editCompanyTab');
const editRepresentativeTab = document.getElementById('editRepresentativeTab');
const companyFormSection = document.getElementById('companyFormSection');
const representativeFormSection = document.getElementById('representativeFormSection');

// File Upload Elements
const editCompanyLogoUpload = document.getElementById('editCompanyLogoUpload');
const editCompanyLogoPreview = document.getElementById('editCompanyLogoPreview');
const editCompanyLogoIcon = document.getElementById('editCompanyLogoIcon');

// ============================================
// SUSPEND MODAL FUNCTIONS
// ============================================

function openSuspendModal() {
    suspendModal.classList.remove('hidden');
    suspendModal.classList.add('flex');
    setTimeout(() => {
        const modalContent = suspendModal.querySelector('.modal-content');
        modalContent.classList.remove('scale-95', 'opacity-0');
        modalContent.classList.add('scale-100', 'opacity-100');
    }, 10);
}

function closeSuspendModal() {
    const modalContent = suspendModal.querySelector('.modal-content');
    modalContent.classList.remove('scale-100', 'opacity-100');
    modalContent.classList.add('scale-95', 'opacity-0');
    setTimeout(() => {
        suspendModal.classList.remove('flex');
        suspendModal.classList.add('hidden');
        // Reset form
        document.getElementById('suspendReason').value = '';
    }, 300);
}

function confirmSuspend() {
    const reason = document.getElementById('suspendReason').value;
    const duration = document.querySelector('input[name="suspensionDuration"]:checked').value;
    
    if (!reason.trim()) {
        alert('Please provide a reason for suspension.');
        return;
    }
    
    // TODO: Implement actual suspension logic with backend
    console.log('Suspending account with reason:', reason, 'Duration:', duration);
    
    // Show success message
    alert('Account has been suspended successfully.');
    closeSuspendModal();
    
    // Optionally redirect or update UI
    // window.location.reload();
}

// ============================================
// EDIT MODAL FUNCTIONS
// ============================================

function openEditModal() {
    editModal.classList.remove('hidden');
    editModal.classList.add('flex');
    // Show company form by default
    showCompanyForm();
    setTimeout(() => {
        const modalContent = editModal.querySelector('.modal-content');
        modalContent.classList.remove('scale-95', 'opacity-0');
        modalContent.classList.add('scale-100', 'opacity-100');
    }, 10);
}

function closeEditModal() {
    const modalContent = editModal.querySelector('.modal-content');
    modalContent.classList.remove('scale-100', 'opacity-100');
    modalContent.classList.add('scale-95', 'opacity-0');
    setTimeout(() => {
        editModal.classList.remove('flex');
        editModal.classList.add('hidden');
        // Reset previews and tabs
        resetImagePreviews();
        showCompanyForm();
    }, 300);
}

function showCompanyForm() {
    // Update tab buttons
    if (editCompanyTab && editRepresentativeTab) {
        editCompanyTab.classList.remove('bg-white', 'border-2', 'border-gray-300', 'text-gray-700');
        editCompanyTab.classList.add('bg-gradient-to-r', 'from-orange-500', 'to-orange-600', 'text-white');
        
        editRepresentativeTab.classList.remove('bg-gradient-to-r', 'from-orange-500', 'to-orange-600', 'text-white');
        editRepresentativeTab.classList.add('bg-white', 'border-2', 'border-gray-300', 'text-gray-700');
    }
    
    // Show/hide form sections
    if (companyFormSection && representativeFormSection) {
        companyFormSection.classList.remove('hidden');
        representativeFormSection.classList.add('hidden');
    }
}

function showRepresentativeForm() {
    // Update tab buttons
    if (editCompanyTab && editRepresentativeTab) {
        editRepresentativeTab.classList.remove('bg-white', 'border-2', 'border-gray-300', 'text-gray-700');
        editRepresentativeTab.classList.add('bg-gradient-to-r', 'from-orange-500', 'to-orange-600', 'text-white');
        
        editCompanyTab.classList.remove('bg-gradient-to-r', 'from-orange-500', 'to-orange-600', 'text-white');
        editCompanyTab.classList.add('bg-white', 'border-2', 'border-gray-300', 'text-gray-700');
    }
    
    // Show/hide form sections
    if (companyFormSection && representativeFormSection) {
        companyFormSection.classList.add('hidden');
        representativeFormSection.classList.remove('hidden');
    }
}

function resetImagePreviews() {
    // Reset company logo
    if (editCompanyLogoPreview && editCompanyLogoIcon) {
        editCompanyLogoPreview.classList.add('hidden');
        editCompanyLogoIcon.classList.remove('hidden');
        editCompanyLogoUpload.value = '';
    }
}

function saveEditChanges() {
    // TODO: Implement actual save logic with form validation and backend
    
    // Get form data
    const formData = new FormData();
    
    // Add all form fields (example)
    const companyName = document.querySelector('input[value="J\'Lois Construction"]').value;
    formData.append('company_name', companyName);
    
    // Add file uploads if any
    if (editCompanyLogoUpload.files.length > 0) {
        formData.append('company_logo', editCompanyLogoUpload.files[0]);
    }
    
    if (editRepPhotoUpload.files.length > 0) {
        formData.append('rep_photo', editRepPhotoUpload.files[0]);
    }
    
    console.log('Saving contractor changes...');
    
    // Show success message
    alert('Changes saved successfully!');
    closeEditModal();
    
    // Optionally reload the page to show updated data
    // window.location.reload();
}

// ============================================
// IMAGE PREVIEW HANDLERS
// ============================================

// Company Logo Preview
if (editCompanyLogoUpload) {
    editCompanyLogoUpload.addEventListener('change', function(e) {
        const file = e.target.files[0];
        if (file) {
            const reader = new FileReader();
            reader.onload = function(e) {
                editCompanyLogoPreview.src = e.target.result;
                editCompanyLogoPreview.classList.remove('hidden');
                editCompanyLogoIcon.classList.add('hidden');
            };
            reader.readAsDataURL(file);
        }
    });
}

// Representative Photo Preview
if (editRepPhotoUpload) {
    editRepPhotoUpload.addEventListener('change', function(e) {
        const file = e.target.files[0];
        if (file) {
            const reader = new FileReader();
            reader.onload = function(e) {
                editRepPhotoPreview.src = e.target.result;
                editRepPhotoPreview.classList.remove('hidden');
                editRepPhotoIcon.classList.add('hidden');
            };
            reader.readAsDataURL(file);
        }
    });
}

// ============================================
// TEAM MEMBERS MODAL ELEMENTS
// ============================================

const addTeamMemberBtn = document.getElementById('addTeamMemberBtn');
const addTeamMemberModal = document.getElementById('addTeamMemberModal');
const closeAddTeamMemberBtn = document.getElementById('closeAddTeamMemberBtn');
const cancelAddTeamMemberBtn = document.getElementById('cancelAddTeamMemberBtn');
const saveAddTeamMemberBtn = document.getElementById('saveAddTeamMemberBtn');
const teamMemberUpload = document.getElementById('teamMemberUpload');
const teamMemberPhotoPreview = document.getElementById('teamMemberPhotoPreview');
const teamMemberCameraIcon = document.getElementById('teamMemberCameraIcon');

const editTeamMemberModal = document.getElementById('editTeamMemberModal');
const closeEditTeamMemberBtn = document.getElementById('closeEditTeamMemberBtn');
const cancelEditTeamMemberBtn = document.getElementById('cancelEditTeamMemberBtn');
const saveEditTeamMemberBtn = document.getElementById('saveEditTeamMemberBtn');
const editTeamMemberUpload = document.getElementById('editTeamMemberUpload');
const editTeamMemberPhotoPreview = document.getElementById('editTeamMemberPreview');
const editTeamMemberInitials = document.getElementById('editTeamMemberInitials');

const deactivateTeamMemberModal = document.getElementById('deactivateTeamMemberModal');
const closeDeactivateTeamMemberBtn = document.getElementById('closeDeactivateTeamMemberBtn');
const cancelDeactivateTeamMemberBtn = document.getElementById('cancelDeactivateTeamMemberBtn');
const confirmDeactivateTeamMemberBtn = document.getElementById('confirmDeactivateTeamMemberBtn');

const reactivateTeamMemberModal = document.getElementById('reactivateTeamMemberModal');
const cancelReactivateTeamMemberBtn = document.getElementById('cancelReactivateTeamMemberBtn');
const confirmReactivateTeamMemberBtn = document.getElementById('confirmReactivateTeamMemberBtn');

let currentEditingRow = null;
let currentDeactivatingRow = null;
let currentReactivatingRow = null;

// ============================================
// TEAM MEMBERS TAB SWITCHING
// ============================================

function initTeamMemberTabs() {
    const tabs = document.querySelectorAll('.team-tab');
    tabs.forEach(tab => {
        tab.addEventListener('click', function() {
            const tabName = this.dataset.tab;
            
            // Update active tab styles
            tabs.forEach(t => {
                t.classList.remove('border-orange-500', 'text-orange-600');
                t.classList.add('border-transparent', 'text-gray-600');
            });
            this.classList.remove('border-transparent', 'text-gray-600');
            this.classList.add('border-orange-500', 'text-orange-600');
            
            // Filter table rows
            const tableRows = document.querySelectorAll('.team-member-row');
            tableRows.forEach(row => {
                const rowStatus = row.dataset.status;
                if (tabName === 'all') {
                    // Show only active members
                    if (rowStatus === 'active') {
                        row.classList.remove('hidden');
                    } else {
                        row.classList.add('hidden');
                    }
                } else if (tabName === 'deactivated') {
                    // Show only deactivated members
                    if (rowStatus === 'deactivated') {
                        row.classList.remove('hidden');
                    } else {
                        row.classList.add('hidden');
                    }
                }
            });
        });
    });
}

// ============================================
// ADD TEAM MEMBER MODAL FUNCTIONS
// ============================================

function openAddTeamMemberModal() {
    addTeamMemberModal.classList.remove('hidden');
    addTeamMemberModal.classList.add('flex');
    setTimeout(() => {
        const modalContent = addTeamMemberModal.querySelector('.modal-content');
        modalContent.classList.remove('scale-95', 'opacity-0');
        modalContent.classList.add('scale-100', 'opacity-100');
    }, 10);
}

function closeAddTeamMemberModal() {
    const modalContent = addTeamMemberModal.querySelector('.modal-content');
    modalContent.classList.remove('scale-100', 'opacity-100');
    modalContent.classList.add('scale-95', 'opacity-0');
    setTimeout(() => {
        addTeamMemberModal.classList.remove('flex');
        addTeamMemberModal.classList.add('hidden');
        resetAddTeamMemberForm();
    }, 300);
}

function resetAddTeamMemberForm() {
    document.getElementById('teamMemberFirstName').value = '';
    document.getElementById('teamMemberLastName').value = '';
    document.getElementById('teamMemberPosition').value = '';
    document.getElementById('teamMemberEmail').value = '';
    document.getElementById('teamMemberContact').value = '';
    teamMemberUpload.value = '';
    teamMemberPhotoPreview.classList.add('hidden');
    teamMemberCameraIcon.classList.remove('hidden');
}

function saveAddTeamMember() {
    const firstName = document.getElementById('teamMemberFirstName').value.trim();
    const lastName = document.getElementById('teamMemberLastName').value.trim();
    const position = document.getElementById('teamMemberPosition').value.trim();
    const email = document.getElementById('teamMemberEmail').value.trim();
    const contact = document.getElementById('teamMemberContact').value.trim();
    
    // Validation
    if (!firstName || !lastName || !position || !email) {
        alert('Please fill in all required fields.');
        return;
    }
    
    // Email validation
    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    if (!emailRegex.test(email)) {
        alert('Please enter a valid email address.');
        return;
    }
    
    // Generate avatar initials and color
    const initials = firstName.charAt(0).toUpperCase() + lastName.charAt(0).toUpperCase();
    const colors = [
        'from-purple-500 to-purple-600',
        'from-blue-500 to-blue-600',
        'from-green-500 to-green-600',
        'from-red-500 to-red-600',
        'from-yellow-500 to-yellow-600'
    ];
    const randomColor = colors[Math.floor(Math.random() * colors.length)];
    
    // Get current date
    const today = new Date();
    const monthNames = ["Jan", "Feb", "Mar", "Apr", "May", "Jun", "Jul", "Aug", "Sep", "Oct", "Nov", "Dec"];
    const dateAdded = `${monthNames[today.getMonth()]} ${today.getDate().toString().padStart(2, '0')} ${today.getFullYear()}`;
    
    // Create new table row
    const tbody = document.querySelector('#teamMembersTable tbody');
    const newRow = document.createElement('tr');
    newRow.className = 'team-member-row hover:bg-gray-50 transition-colors group';
    newRow.dataset.status = 'active';
    
    newRow.innerHTML = `
        <td class="px-6 py-4 whitespace-nowrap">
            <div class="flex items-center space-x-3">
                <div class="w-10 h-10 rounded-full bg-gradient-to-br ${randomColor} flex items-center justify-center text-white font-semibold text-sm shadow-lg">
                    ${initials}
                </div>
                <div>
                    <div class="font-medium text-gray-900">${firstName} ${lastName}</div>
                    <div class="text-sm text-gray-500">${email}</div>
                </div>
            </div>
        </td>
        <td class="px-6 py-4 whitespace-nowrap">
            <span class="text-sm text-gray-900">${position}</span>
        </td>
        <td class="px-6 py-4 whitespace-nowrap">
            <span class="text-sm text-gray-500">${dateAdded}</span>
        </td>
        <td class="px-6 py-4 whitespace-nowrap">
            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                <i class="fi fi-sr-check-circle mr-1 text-[10px]"></i>
                Active
            </span>
        </td>
        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
            <div class="flex items-center justify-end space-x-2 opacity-0 group-hover:opacity-100 transition-opacity">
                <button class="team-edit-btn p-2 text-orange-600 hover:bg-orange-50 rounded-lg transition-colors" title="Edit Member">
                    <i class="fi fi-rr-pencil text-sm"></i>
                </button>
                <button class="team-deactivate-btn p-2 text-red-600 hover:bg-red-50 rounded-lg transition-colors" title="Deactivate">
                    <i class="fi fi-rr-ban text-sm"></i>
                </button>
            </div>
        </td>
    `;
    
    tbody.insertBefore(newRow, tbody.firstChild);
    
    // Show success notification
    showNotification('Team member added successfully!', 'success');
    
    closeAddTeamMemberModal();
}

// ============================================
// EDIT TEAM MEMBER MODAL FUNCTIONS
// ============================================

function openEditTeamMemberModal(row) {
    currentEditingRow = row;
    
    // Extract current data from row
    const nameElement = row.querySelector('.font-medium');
    const fullName = nameElement.textContent.trim();
    const nameParts = fullName.split(' ');
    const firstName = nameParts[0] || '';
    const lastName = nameParts.slice(1).join(' ') || '';
    
    const positionElement = row.querySelectorAll('td')[1];
    const position = positionElement ? positionElement.textContent.trim() : '';
    
    // Update initials in modal
    const initials = firstName.charAt(0).toUpperCase() + (lastName ? lastName.charAt(0).toUpperCase() : '');
    editTeamMemberInitials.textContent = initials;
    
    // Copy avatar gradient colors from row to modal
    const avatarElement = row.querySelector('.w-10');
    const avatarClasses = avatarElement.className;
    const gradientMatch = avatarClasses.match(/from-(\w+)-\d+\s+to-(\w+)-\d+/);
    if (gradientMatch) {
        const modalAvatar = editTeamMemberModal.querySelector('.w-20.h-20');
        modalAvatar.className = `w-20 h-20 rounded-full bg-gradient-to-br ${gradientMatch[0]} flex items-center justify-center overflow-hidden shadow-md`;
    }
    
    // Reset photo to show initials
    editTeamMemberPhotoPreview.classList.add('hidden');
    editTeamMemberInitials.classList.remove('hidden');
    
    // Populate form - use data attributes or placeholder values for email/contact
    document.getElementById('editTeamMemberFirstName').value = firstName;
    document.getElementById('editTeamMemberLastName').value = lastName;
    document.getElementById('editTeamMemberPosition').value = position;
    
    // Get email and contact from data attributes if available, otherwise use placeholders
    const email = row.dataset.email || `${firstName.toLowerCase()}.${lastName.toLowerCase().replace(/\s+/g, '')}@jlois.com`;
    const contact = row.dataset.contact || '+63 912 345 6789';
    
    document.getElementById('editTeamMemberEmail').value = email;
    document.getElementById('editTeamMemberContact').value = contact;
    
    editTeamMemberModal.classList.remove('hidden');
    editTeamMemberModal.classList.add('flex');
    setTimeout(() => {
        const modalContent = editTeamMemberModal.querySelector('.modal-content');
        modalContent.classList.remove('scale-95', 'opacity-0');
        modalContent.classList.add('scale-100', 'opacity-100');
    }, 10);
}

function closeEditTeamMemberModal() {
    const modalContent = editTeamMemberModal.querySelector('.modal-content');
    modalContent.classList.remove('scale-100', 'opacity-100');
    modalContent.classList.add('scale-95', 'opacity-0');
    setTimeout(() => {
        editTeamMemberModal.classList.remove('flex');
        editTeamMemberModal.classList.add('hidden');
        currentEditingRow = null;
        editTeamMemberUpload.value = '';
        editTeamMemberPhotoPreview.classList.add('hidden');
        editTeamMemberInitials.classList.remove('hidden');
    }, 300);
}

function saveEditTeamMember() {
    if (!currentEditingRow) return;
    
    const firstName = document.getElementById('editTeamMemberFirstName').value.trim();
    const lastName = document.getElementById('editTeamMemberLastName').value.trim();
    const position = document.getElementById('editTeamMemberPosition').value.trim();
    const email = document.getElementById('editTeamMemberEmail').value.trim();
    
    // Validation
    if (!firstName || !lastName || !position || !email) {
        alert('Please fill in all required fields.');
        return;
    }
    
    // Email validation
    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    if (!emailRegex.test(email)) {
        alert('Please enter a valid email address.');
        return;
    }
    
    // Update row data
    const nameElement = currentEditingRow.querySelector('.font-medium');
    const emailElement = currentEditingRow.querySelector('.text-gray-500');
    const positionElement = currentEditingRow.querySelectorAll('td')[1].querySelector('span');
    
    nameElement.textContent = `${firstName} ${lastName}`;
    emailElement.textContent = email;
    positionElement.textContent = position;
    
    // Update avatar initials
    const avatarElement = currentEditingRow.querySelector('.w-10');
    const initials = firstName.charAt(0).toUpperCase() + lastName.charAt(0).toUpperCase();
    avatarElement.textContent = initials;
    
    // Show success notification
    showNotification('Team member updated successfully!', 'success');
    
    closeEditTeamMemberModal();
}

// ============================================
// DEACTIVATE TEAM MEMBER MODAL FUNCTIONS
// ============================================

function openDeactivateTeamMemberModal(row) {
    currentDeactivatingRow = row;
    
    // Get member name
    const nameElement = row.querySelector('.font-medium');
    const memberName = nameElement.textContent.trim();
    
    // Update modal text
    document.getElementById('deactivateTeamMemberName').textContent = memberName;
    
    deactivateTeamMemberModal.classList.remove('hidden');
    deactivateTeamMemberModal.classList.add('flex');
    setTimeout(() => {
        const modalContent = deactivateTeamMemberModal.querySelector('.modal-content');
        modalContent.classList.remove('scale-95', 'opacity-0');
        modalContent.classList.add('scale-100', 'opacity-100');
    }, 10);
}

function closeDeactivateTeamMemberModal() {
    const modalContent = deactivateTeamMemberModal.querySelector('.modal-content');
    modalContent.classList.remove('scale-100', 'opacity-100');
    modalContent.classList.add('scale-95', 'opacity-0');
    setTimeout(() => {
        deactivateTeamMemberModal.classList.remove('flex');
        deactivateTeamMemberModal.classList.add('hidden');
        currentDeactivatingRow = null;
    }, 300);
}

function confirmDeactivateTeamMember() {
    if (!currentDeactivatingRow) return;
    
    // Update row status
    currentDeactivatingRow.dataset.status = 'deactivated';
    
    // Update status badge
    const statusBadge = currentDeactivatingRow.querySelectorAll('td')[3].querySelector('span');
    statusBadge.className = 'inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-gray-100 text-gray-600';
    statusBadge.textContent = 'Deactivated';
    
    // Update actions - replace edit/deactivate with reactivate button
    const actionsCell = currentDeactivatingRow.querySelectorAll('td')[4];
    actionsCell.innerHTML = `
        <div class="flex items-center justify-center gap-2">
            <button class="team-reactivate-btn p-2 rounded-lg hover:bg-green-50 transition-all group/btn" title="Reactivate Account">
                <i class="fi fi-rr-check-circle text-green-600 group-hover/btn:scale-110 transition-transform"></i>
            </button>
        </div>
    `;
    
    // Fade out the row
    currentDeactivatingRow.querySelector('.flex.items-center.gap-3').classList.add('opacity-60');
    const nameSpan = currentDeactivatingRow.querySelector('.font-medium');
    nameSpan.classList.remove('text-gray-800');
    nameSpan.classList.add('text-gray-600');
    
    // Hide row if on "All" tab
    const activeTab = document.querySelector('.team-tab.border-orange-500');
    if (activeTab && activeTab.dataset.tab === 'all') {
        currentDeactivatingRow.classList.add('hidden');
    }
    
    // Show success notification
    showNotification('Team member deactivated successfully!', 'success');
    
    closeDeactivateTeamMemberModal();
}

// ============================================
// REACTIVATE TEAM MEMBER MODAL FUNCTIONS
// ============================================

function openReactivateTeamMemberModal(row) {
    currentReactivatingRow = row;
    
    // Get member name
    const nameElement = row.querySelector('.font-medium');
    const memberName = nameElement.textContent.trim();
    
    // Update modal text
    document.getElementById('reactivateTeamMemberName').textContent = memberName;
    
    reactivateTeamMemberModal.classList.remove('hidden');
    reactivateTeamMemberModal.classList.add('flex');
    setTimeout(() => {
        const modalContent = reactivateTeamMemberModal.querySelector('.modal-content');
        modalContent.classList.remove('scale-95', 'opacity-0');
        modalContent.classList.add('scale-100', 'opacity-100');
    }, 10);
}

function closeReactivateTeamMemberModal() {
    const modalContent = reactivateTeamMemberModal.querySelector('.modal-content');
    modalContent.classList.remove('scale-100', 'opacity-100');
    modalContent.classList.add('scale-95', 'opacity-0');
    setTimeout(() => {
        reactivateTeamMemberModal.classList.remove('flex');
        reactivateTeamMemberModal.classList.add('hidden');
        currentReactivatingRow = null;
    }, 300);
}

function confirmReactivateTeamMember() {
    if (!currentReactivatingRow) return;
    
    const row = currentReactivatingRow;
    
    // Update row status
    row.dataset.status = 'active';
    
    // Update status badge
    const statusBadge = row.querySelectorAll('td')[3].querySelector('span');
    statusBadge.className = 'inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-green-100 text-green-700 transition-all duration-200 hover:scale-110 hover:shadow-md';
    statusBadge.textContent = 'Active';
    
    // Update actions - restore edit/deactivate buttons
    const actionsCell = row.querySelectorAll('td')[4];
    actionsCell.innerHTML = `
        <div class="flex items-center justify-center gap-2">
            <button class="team-edit-btn p-2 rounded-lg hover:bg-orange-50 transition-all group/btn" title="Edit Member">
                <i class="fi fi-rr-pencil text-orange-600 group-hover/btn:scale-110 transition-transform"></i>
            </button>
            <button class="team-deactivate-btn p-2 rounded-lg hover:bg-red-50 transition-all group/btn" title="Deactivate Account">
                <i class="fi fi-rr-ban text-red-600 group-hover/btn:scale-110 transition-transform"></i>
            </button>
        </div>
    `;
    
    // Restore normal styling
    row.querySelector('.flex.items-center.gap-3').classList.remove('opacity-60');
    const nameSpan = row.querySelector('.font-medium');
    nameSpan.classList.remove('text-gray-600');
    nameSpan.classList.add('text-gray-800');
    
    // Get member name for avatar restoration
    const memberName = nameSpan.textContent.trim();
    const nameParts = memberName.split(' ');
    const firstName = nameParts[0] || '';
    const lastName = nameParts[nameParts.length - 1] || '';
    
    // Restore avatar gradient (use different colors for variety)
    const avatarElement = row.querySelector('.w-10');
    const gradients = [
        'from-purple-400 to-purple-600',
        'from-blue-400 to-blue-600',
        'from-green-400 to-green-600',
        'from-yellow-400 to-yellow-600',
        'from-pink-400 to-pink-600'
    ];
    const randomGradient = gradients[Math.floor(Math.random() * gradients.length)];
    avatarElement.className = `w-10 h-10 rounded-full bg-gradient-to-br ${randomGradient} flex items-center justify-center overflow-hidden shadow-md group-hover:shadow-lg transition-all group-hover:scale-110`;
    
    // Hide row if on "Deactivated" tab
    const activeTab = document.querySelector('.team-tab.border-orange-500');
    if (activeTab && activeTab.dataset.tab === 'deactivated') {
        row.classList.add('hidden');
    }
    
    // Show success notification
    showNotification('Team member reactivated successfully!', 'success');
    
    closeReactivateTeamMemberModal();
}

// ============================================
// TEAM MEMBER IMAGE PREVIEW HANDLERS
// ============================================

if (teamMemberUpload) {
    teamMemberUpload.addEventListener('change', function(e) {
        const file = e.target.files[0];
        if (file) {
            const reader = new FileReader();
            reader.onload = function(e) {
                teamMemberPhotoPreview.src = e.target.result;
                teamMemberPhotoPreview.classList.remove('hidden');
                teamMemberCameraIcon.classList.add('hidden');
            };
            reader.readAsDataURL(file);
        }
    });
}

if (editTeamMemberUpload) {
    editTeamMemberUpload.addEventListener('change', function(e) {
        const file = e.target.files[0];
        if (file) {
            const reader = new FileReader();
            reader.onload = function(e) {
                editTeamMemberPhotoPreview.src = e.target.result;
                editTeamMemberPhotoPreview.classList.remove('hidden');
                editTeamMemberInitials.classList.add('hidden');
            };
            reader.readAsDataURL(file);
        }
    });
}

// ============================================
// NOTIFICATION FUNCTION
// ============================================

function showNotification(message, type = 'success') {
    const notification = document.createElement('div');
    notification.className = `fixed top-4 right-4 z-[60] px-6 py-4 rounded-lg shadow-2xl transform transition-all duration-300 translate-x-full ${
        type === 'success' ? 'bg-green-500 text-white' : 'bg-red-500 text-white'
    }`;
    notification.innerHTML = `
        <div class="flex items-center space-x-2">
            <i class="fi ${type === 'success' ? 'fi-sr-check-circle' : 'fi-sr-cross-circle'} text-lg"></i>
            <span class="font-medium">${message}</span>
        </div>
    `;
    
    document.body.appendChild(notification);
    
    setTimeout(() => {
        notification.style.transform = 'translateX(0)';
    }, 10);
    
    setTimeout(() => {
        notification.style.transform = 'translateX(150%)';
        setTimeout(() => {
            notification.remove();
        }, 300);
    }, 3000);
}

// ============================================
// TEAM MEMBERS EVENT DELEGATION
// ============================================

document.addEventListener('click', function(e) {
    // Edit button clicked
    if (e.target.closest('.team-edit-btn')) {
        const row = e.target.closest('.team-member-row');
        openEditTeamMemberModal(row);
    }
    
    // Deactivate button clicked
    if (e.target.closest('.team-deactivate-btn')) {
        const row = e.target.closest('.team-member-row');
        openDeactivateTeamMemberModal(row);
    }
    
    // Reactivate button clicked
    if (e.target.closest('.team-reactivate-btn')) {
        const row = e.target.closest('.team-member-row');
        openReactivateTeamMemberModal(row);
    }
});

// ============================================
// EVENT LISTENERS
// ============================================

// Suspend Modal Events
if (suspendBtn) suspendBtn.addEventListener('click', openSuspendModal);
if (closeSuspendBtn) closeSuspendBtn.addEventListener('click', closeSuspendModal);
if (cancelSuspendBtn) cancelSuspendBtn.addEventListener('click', closeSuspendModal);
if (confirmSuspendBtn) confirmSuspendBtn.addEventListener('click', confirmSuspend);

// Edit Modal Events
if (editBtn) editBtn.addEventListener('click', openEditModal);
if (closeEditBtn) closeEditBtn.addEventListener('click', closeEditModal);
if (cancelEditBtn) cancelEditBtn.addEventListener('click', closeEditModal);
if (saveEditBtn) saveEditBtn.addEventListener('click', saveEditChanges);

// Edit Modal Tab Events
if (editCompanyTab) editCompanyTab.addEventListener('click', showCompanyForm);
if (editRepresentativeTab) editRepresentativeTab.addEventListener('click', showRepresentativeForm);

// Representative form cancel/save buttons
document.addEventListener('click', function(e) {
    if (e.target.closest('.cancel-edit-rep-btn')) {
        closeEditModal();
    }
    if (e.target.closest('.save-edit-rep-btn')) {
        saveEditChanges();
    }
});

// Close modals when clicking outside
if (suspendModal) {
    suspendModal.addEventListener('click', function(e) {
        if (e.target === suspendModal) {
            closeSuspendModal();
        }
    });
}

if (editModal) {
    editModal.addEventListener('click', function(e) {
        if (e.target === editModal) {
            closeEditModal();
        }
    });
}

// Close modals on ESC key
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        if (!suspendModal.classList.contains('hidden')) {
            closeSuspendModal();
        }
        if (!editModal.classList.contains('hidden')) {
            closeEditModal();
        }
        if (!addTeamMemberModal.classList.contains('hidden')) {
            closeAddTeamMemberModal();
        }
        if (!editTeamMemberModal.classList.contains('hidden')) {
            closeEditTeamMemberModal();
        }
        if (!deactivateTeamMemberModal.classList.contains('hidden')) {
            closeDeactivateTeamMemberModal();
        }
        if (!reactivateTeamMemberModal.classList.contains('hidden')) {
            closeReactivateTeamMemberModal();
        }
        const changeRepModal = document.getElementById('changeRepresentativeModal');
        if (changeRepModal && !changeRepModal.classList.contains('hidden')) {
            closeChangeRepresentativeModal();
        }
    }
});

// ============================================
// CHANGE REPRESENTATIVE MODAL
// ============================================

const changeRepresentativeBtn = document.getElementById('changeRepresentativeBtn');
const changeRepresentativeModal = document.getElementById('changeRepresentativeModal');
const closeChangeRepresentativeBtn = document.getElementById('closeChangeRepresentativeBtn');
const cancelChangeRepresentativeBtn = document.getElementById('cancelChangeRepresentativeBtn');
const confirmChangeRepresentativeBtn = document.getElementById('confirmChangeRepresentativeBtn');
const searchTeamMemberInput = document.getElementById('searchTeamMember');

let selectedMember = null;

function openChangeRepresentativeModal() {
    changeRepresentativeModal.classList.remove('hidden');
    changeRepresentativeModal.classList.add('flex');
    setTimeout(() => {
        const modalContent = changeRepresentativeModal.querySelector('.modal-content');
        modalContent.classList.remove('scale-95', 'opacity-0');
        modalContent.classList.add('scale-100', 'opacity-100');
    }, 10);
}

function closeChangeRepresentativeModal() {
    const modalContent = changeRepresentativeModal.querySelector('.modal-content');
    modalContent.classList.remove('scale-100', 'opacity-100');
    modalContent.classList.add('scale-95', 'opacity-0');
    setTimeout(() => {
        changeRepresentativeModal.classList.remove('flex');
        changeRepresentativeModal.classList.add('hidden');
        selectedMember = null;
        confirmChangeRepresentativeBtn.disabled = true;
        // Clear selection styles
        document.querySelectorAll('.team-member-option').forEach(option => {
            option.classList.remove('border-blue-500', 'bg-blue-50');
            option.classList.add('border-gray-200');
        });
        // Clear search
        if (searchTeamMemberInput) searchTeamMemberInput.value = '';
    }, 300);
}

function selectTeamMember(memberElement) {
    // Remove selection from all options
    document.querySelectorAll('.team-member-option').forEach(option => {
        option.classList.remove('border-blue-500', 'bg-blue-50');
        option.classList.add('border-gray-200');
    });
    
    // Add selection to clicked option
    memberElement.classList.remove('border-gray-200');
    memberElement.classList.add('border-blue-500', 'bg-blue-50');
    
    // Store selected member data
    selectedMember = {
        id: memberElement.dataset.memberId,
        name: memberElement.dataset.memberName,
        position: memberElement.dataset.memberPosition,
        email: memberElement.dataset.memberEmail
    };
    
    // Enable confirm button
    confirmChangeRepresentativeBtn.disabled = false;
}

function confirmChangeRepresentative() {
    if (!selectedMember) return;
    
    // TODO: Implement actual API call to update representative
    console.log('Changing representative to:', selectedMember);
    
    // Show success notification
    showNotification(`Company representative changed to ${selectedMember.name}`, 'success');
    
    closeChangeRepresentativeModal();
    
    // Optionally reload page to reflect changes
    // window.location.reload();
}

function filterTeamMembers() {
    const searchTerm = searchTeamMemberInput.value.toLowerCase();
    const memberOptions = document.querySelectorAll('.team-member-option');
    
    memberOptions.forEach(option => {
        const name = option.dataset.memberName.toLowerCase();
        const position = option.dataset.memberPosition.toLowerCase();
        
        if (name.includes(searchTerm) || position.includes(searchTerm)) {
            option.classList.remove('hidden');
        } else {
            option.classList.add('hidden');
        }
    });
}

// Event listeners for Change Representative modal
if (changeRepresentativeBtn) {
    changeRepresentativeBtn.addEventListener('click', openChangeRepresentativeModal);
}

if (closeChangeRepresentativeBtn) {
    closeChangeRepresentativeBtn.addEventListener('click', closeChangeRepresentativeModal);
}

if (cancelChangeRepresentativeBtn) {
    cancelChangeRepresentativeBtn.addEventListener('click', closeChangeRepresentativeModal);
}

if (confirmChangeRepresentativeBtn) {
    confirmChangeRepresentativeBtn.addEventListener('click', confirmChangeRepresentative);
}

if (searchTeamMemberInput) {
    searchTeamMemberInput.addEventListener('input', filterTeamMembers);
}

// Delegate click events to team member options
document.addEventListener('click', function(e) {
    const memberOption = e.target.closest('.team-member-option');
    if (memberOption) {
        selectTeamMember(memberOption);
    }
});

// Close modal when clicking outside
if (changeRepresentativeModal) {
    changeRepresentativeModal.addEventListener('click', function(e) {
        if (e.target === changeRepresentativeModal) {
            closeChangeRepresentativeModal();
        }
    });
}

// ============================================
// TEAM MEMBERS INITIALIZATION
// ============================================

// Initialize team member tabs on page load
if (document.querySelector('.team-tab')) {
    initTeamMemberTabs();
}

// Team Members Modal Events
if (addTeamMemberBtn) addTeamMemberBtn.addEventListener('click', openAddTeamMemberModal);
if (closeAddTeamMemberBtn) closeAddTeamMemberBtn.addEventListener('click', closeAddTeamMemberModal);
if (cancelAddTeamMemberBtn) cancelAddTeamMemberBtn.addEventListener('click', closeAddTeamMemberModal);
if (saveAddTeamMemberBtn) saveAddTeamMemberBtn.addEventListener('click', saveAddTeamMember);

if (closeEditTeamMemberBtn) closeEditTeamMemberBtn.addEventListener('click', closeEditTeamMemberModal);
if (cancelEditTeamMemberBtn) cancelEditTeamMemberBtn.addEventListener('click', closeEditTeamMemberModal);
if (saveEditTeamMemberBtn) saveEditTeamMemberBtn.addEventListener('click', saveEditTeamMember);

if (closeDeactivateTeamMemberBtn) closeDeactivateTeamMemberBtn.addEventListener('click', closeDeactivateTeamMemberModal);
if (cancelDeactivateTeamMemberBtn) cancelDeactivateTeamMemberBtn.addEventListener('click', closeDeactivateTeamMemberModal);
if (confirmDeactivateTeamMemberBtn) confirmDeactivateTeamMemberBtn.addEventListener('click', confirmDeactivateTeamMember);

if (cancelReactivateTeamMemberBtn) cancelReactivateTeamMemberBtn.addEventListener('click', closeReactivateTeamMemberModal);
if (confirmReactivateTeamMemberBtn) confirmReactivateTeamMemberBtn.addEventListener('click', confirmReactivateTeamMember);

// Close team member modals when clicking outside
if (addTeamMemberModal) {
    addTeamMemberModal.addEventListener('click', function(e) {
        if (e.target === addTeamMemberModal) {
            closeAddTeamMemberModal();
        }
    });
}

if (editTeamMemberModal) {
    editTeamMemberModal.addEventListener('click', function(e) {
        if (e.target === editTeamMemberModal) {
            closeEditTeamMemberModal();
        }
    });
}

if (deactivateTeamMemberModal) {
    deactivateTeamMemberModal.addEventListener('click', function(e) {
        if (e.target === deactivateTeamMemberModal) {
            closeDeactivateTeamMemberModal();
        }
    });
}

if (reactivateTeamMemberModal) {
    reactivateTeamMemberModal.addEventListener('click', function(e) {
        if (e.target === reactivateTeamMemberModal) {
            closeReactivateTeamMemberModal();
        }
    });
}

// ============================================
// SMOOTH SCROLL ANIMATIONS
// ============================================

// Add smooth scroll behavior to all anchor links
document.querySelectorAll('a[href^="#"]').forEach(anchor => {
    anchor.addEventListener('click', function (e) {
        e.preventDefault();
        const target = document.querySelector(this.getAttribute('href'));
        if (target) {
            target.scrollIntoView({
                behavior: 'smooth',
                block: 'start'
            });
        }
    });
});
