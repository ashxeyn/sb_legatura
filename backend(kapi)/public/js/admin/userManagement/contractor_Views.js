async function fetchAndUpdateTeamMembers() {
    const contractorId = document.body.dataset.contractorId;
    if (!contractorId) {
        console.error('Contractor ID not found');
        return;
    }

    const url = `/admin/user-management/contractor/view?id=${contractorId}`;

    try {
        const response = await fetch(url, {
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        });

        if (!response.ok) throw new Error('Network response was not ok');

        const data = await response.json();
        const teamMembersTable = document.getElementById('teamMembersTable');

        if (teamMembersTable && data.html) {
            teamMembersTable.innerHTML = data.html;
        }

        attachTeamMemberListeners();

        refreshRepresentativeModalList();

    } catch (error) {
        console.error('Error fetching team members data:', error);
        showNotification('Failed to refresh team members list', 'error');
    }
}

function refreshContractorDetails() {
    const contractorId = document.body.dataset.contractorId;
    if (!contractorId) {
        console.error('Contractor ID not found');
        return;
    }

    setTimeout(() => {
        window.location.replace(`/admin/user-management/contractor/view?id=${contractorId}`);
    }, 500);
}

window.refreshContractorDetails = refreshContractorDetails;

function refreshRepresentativeModalList() {
    const contractorId = document.body.dataset.contractorId;
    if (!contractorId) return;

    fetch(`/admin/user-management/contractor/view?id=${contractorId}&modal=representative`, {
        headers: {
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(response => response.json())
    .then(data => {
        const teamMembersList = document.getElementById('teamMembersList');
        if (teamMembersList && data.modal_html) {
            teamMembersList.innerHTML = data.modal_html;
        }
    })
    .catch(error => {
        console.error('Error refreshing representative modal list:', error);
    });
}

function attachTeamMemberListeners() {

    document.querySelectorAll('.team-edit-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            const memberId = this.dataset.memberId;
            openEditTeamMemberModal(memberId);
        });
    });

    document.querySelectorAll('.team-deactivate-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            const memberId = this.dataset.memberId;
            const memberName = this.dataset.memberName;
            openDeactivateTeamMemberModal(memberId, memberName);
        });
    });

    document.querySelectorAll('.team-reactivate-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            const memberId = this.dataset.memberId;
            const memberName = this.dataset.memberName;
            openReactivateTeamMemberModal(memberId, memberName);
        });
    });
}

document.addEventListener('DOMContentLoaded', function() {
    attachTeamMemberListeners();
});

const suspendBtn = document.getElementById('suspendContractorBtn');
const suspendModal = document.getElementById('suspendAccountModal');
const suspendModalContent = suspendModal ? suspendModal.querySelector('.modal-content') : null;
const closeSuspendBtn = document.getElementById('closeSuspendModalBtn');
const cancelSuspendBtn = document.getElementById('cancelSuspendBtn');
const confirmSuspendBtn = document.getElementById('confirmSuspendBtn');
const suspendReasonTextarea = document.getElementById('suspendReason');
const suspensionDateContainer = document.getElementById('suspensionDateContainer');
const suspensionDateInput = document.getElementById('suspensionDate');
const radioButtons = document.querySelectorAll('input[name="suspensionDuration"]');

radioButtons.forEach(radio => {
    radio.addEventListener('change', function() {
        if (this.value === 'temporary') {
            suspensionDateContainer.style.height = 'auto';
            suspensionDateContainer.classList.remove('opacity-0', 'invisible');
            suspensionDateContainer.classList.add('opacity-100', 'visible', 'mt-3');
        } else {
            suspensionDateContainer.style.height = '0';
            suspensionDateContainer.classList.remove('opacity-100', 'visible', 'mt-3');
            suspensionDateContainer.classList.add('opacity-0', 'invisible');
        }
    });
});

function openSuspendModal() {
    if (!suspendModal || !suspendModalContent) return;

    suspendModal.classList.remove('hidden');
    suspendModal.classList.add('flex');
    document.body.style.overflow = 'hidden';

    setTimeout(() => {
        suspendModalContent.classList.remove('scale-95', 'opacity-0');
        suspendModalContent.classList.add('scale-100', 'opacity-100');
    }, 10);
}

function closeSuspendModal() {
    if (!suspendModalContent) return;

    suspendModalContent.classList.remove('scale-100', 'opacity-100');
    suspendModalContent.classList.add('scale-95', 'opacity-0');

    setTimeout(() => {
        suspendModal.classList.add('hidden');
        suspendModal.classList.remove('flex');
        document.body.style.overflow = 'auto';

        if (suspendReasonTextarea) {
            suspendReasonTextarea.value = '';
        }
        if (suspensionDateInput) {
            suspensionDateInput.value = '';
        }
        const radioButtons = suspendModal.querySelectorAll('input[type="radio"]');
        if (radioButtons.length > 0) {
            radioButtons[0].checked = true;
            radioButtons[0].dispatchEvent(new Event('change'));
        }
    }, 300);
}

function showNotification(message, type = 'success') {
    const notification = document.createElement('div');
    notification.className = `fixed top-24 right-8 z-[60] px-6 py-4 rounded-lg shadow-2xl transform transition-all duration-500 translate-x-full ${
        type === 'success' ? 'bg-green-500' : 'bg-red-500'
    } text-white font-semibold flex items-center gap-3`;
    notification.innerHTML = `
        <i class="fi fi-rr-${type === 'success' ? 'check-circle' : 'cross-circle'} text-2xl"></i>
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

function confirmSuspend() {
    const reason = suspendReasonTextarea ? suspendReasonTextarea.value.trim() : '';
    const selectedDuration = suspendModal.querySelector('input[name="suspensionDuration"]:checked');
    const duration = selectedDuration ? selectedDuration.value : 'temporary';
    let suspensionDate = null;
    let hasError = false;

    suspendReasonTextarea.classList.remove('border-red-500', 'shake');
    document.getElementById('suspendReasonError').classList.add('hidden');
    document.getElementById('suspendReasonError').textContent = '';

    if (suspensionDateInput) {
        suspensionDateInput.classList.remove('border-red-500', 'shake');
        document.getElementById('suspensionDateError').classList.add('hidden');
        document.getElementById('suspensionDateError').textContent = '';
    }

    if (!reason) {
        suspendReasonTextarea.classList.add('border-red-500', 'shake');
        const errorEl = document.getElementById('suspendReasonError');
        errorEl.textContent = 'Please provide a reason for suspension';
        errorEl.classList.remove('hidden');

        setTimeout(() => {
            suspendReasonTextarea.classList.remove('shake');
        }, 500);
        hasError = true;
    }

    if (duration === 'temporary') {
        suspensionDate = suspensionDateInput.value;
        if (!suspensionDate) {
            suspensionDateInput.classList.add('border-red-500', 'shake');
            const errorEl = document.getElementById('suspensionDateError');
            errorEl.textContent = 'Please select a suspension date';
            errorEl.classList.remove('hidden');

            setTimeout(() => {
                suspensionDateInput.classList.remove('shake');
            }, 500);
            hasError = true;
        }
    }

    if (hasError) return;

    const originalContent = confirmSuspendBtn.innerHTML;
    confirmSuspendBtn.innerHTML = '<i class="fi fi-rr-spinner animate-spin"></i> Suspending...';
    confirmSuspendBtn.disabled = true;

    const contractorId = suspendBtn.getAttribute('data-id');

    fetch(`/api/admin/users/contractors/${contractorId}/suspend`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        },
        body: JSON.stringify({
            reason: reason,
            duration: duration,
            suspension_until: suspensionDate
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showNotification('Contractor account suspended successfully!', 'success');
            closeSuspendModal();

            const statusBadge = document.querySelector('.text-xs.font-medium.px-2\\.5.py-1');
            if (statusBadge) {
                statusBadge.className = 'text-xs font-medium px-2.5 py-1 rounded-full bg-red-100 text-red-600';
                statusBadge.textContent = 'Suspended';
            }

            if (suspendBtn) {
                suspendBtn.style.display = 'none';
            }
        } else {
            if (data.errors) {
                if (data.errors.reason) {
                    suspendReasonTextarea.classList.add('border-red-500', 'shake');
                    const errorEl = document.getElementById('suspendReasonError');
                    errorEl.textContent = data.errors.reason[0];
                    errorEl.classList.remove('hidden');
                    setTimeout(() => suspendReasonTextarea.classList.remove('shake'), 500);
                }
                if (data.errors.suspension_until) {
                    suspensionDateInput.classList.add('border-red-500', 'shake');
                    const errorEl = document.getElementById('suspensionDateError');
                    errorEl.textContent = data.errors.suspension_until[0];
                    errorEl.classList.remove('hidden');
                    setTimeout(() => suspensionDateInput.classList.remove('shake'), 500);
                }
                showNotification('Please correct the errors below', 'error');
            } else {
                showNotification(data.message || 'Failed to suspend account', 'error');
            }
            confirmSuspendBtn.innerHTML = originalContent;
            confirmSuspendBtn.disabled = false;
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showNotification('An error occurred while suspending the account', 'error');
        confirmSuspendBtn.innerHTML = originalContent;
        confirmSuspendBtn.disabled = false;
    });
}

const addTeamMemberBtn = document.getElementById('addTeamMemberBtn');
const addTeamMemberModal = document.getElementById('addTeamMemberModal');
const closeAddTeamMemberBtn = document.getElementById('closeAddTeamMemberBtn');
const cancelAddTeamMemberBtn = document.getElementById('cancelAddTeamMemberBtn');
const saveTeamMemberBtn = document.getElementById('saveTeamMemberBtn');
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

function initTeamMemberTabs() {
    const tabs = document.querySelectorAll('.team-tab');
    const statusHeader = document.getElementById('statusColumnHeader');

    tabs.forEach(tab => {
        tab.addEventListener('click', function() {
            const tabName = this.dataset.tab;

            tabs.forEach(t => {
                t.classList.remove('border-orange-500', 'text-orange-600');
                t.classList.add('border-transparent', 'text-gray-600');
            });
            this.classList.remove('border-transparent', 'text-gray-600');
            this.classList.add('border-orange-500', 'text-orange-600');

            if (tabName === 'deactivated') {
                if (statusHeader) statusHeader.textContent = 'Reason';

                document.querySelectorAll('.status-cell').forEach(cell => {
                    const badge = cell.querySelector('.status-badge');
                    const reason = cell.querySelector('.deletion-reason');
                    if (badge) badge.classList.add('hidden');
                    if (reason) reason.classList.remove('hidden');
                });
            } else {
                if (statusHeader) statusHeader.textContent = 'Status';

                document.querySelectorAll('.status-cell').forEach(cell => {
                    const badge = cell.querySelector('.status-badge');
                    const reason = cell.querySelector('.deletion-reason');
                    if (badge) badge.classList.remove('hidden');
                    if (reason) reason.classList.add('hidden');
                });
            }

            const tableRows = document.querySelectorAll('.team-member-row');
            tableRows.forEach(row => {
                const rowStatus = row.dataset.status;
                if (tabName === 'all') {

                    if (rowStatus === 'active') {
                        row.classList.remove('hidden');
                    } else {
                        row.classList.add('hidden');
                    }
                } else if (tabName === 'deactivated') {

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

function openAddTeamMemberModal(isRepresentative = false, fromChangeRepModal = false) {

    addTeamMemberModal.dataset.isRepresentative = isRepresentative;
    addTeamMemberModal.dataset.fromChangeRepModal = fromChangeRepModal;

    const backBtn = document.getElementById('backToRepresentativeModalBtn');
    if (backBtn) {
        if (fromChangeRepModal) {
            backBtn.classList.remove('hidden');
        } else {
            backBtn.classList.add('hidden');
        }
    }

    const roleGroup = document.getElementById('teamMemberRole').closest('.form-group');
    const roleSelect = document.getElementById('teamMemberRole');

    if (isRepresentative) {

        roleGroup.classList.add('hidden');

        roleSelect.value = 'representative';
    } else {

        roleGroup.classList.remove('hidden');

        const options = roleSelect.querySelectorAll('option');
        options.forEach(option => {
            if (option.value === 'owner' || option.value === 'representative') {
                option.style.display = 'none';
            } else {
                option.style.display = '';
            }
        });
    }

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
    document.getElementById('teamMemberMiddleName').value = '';
    document.getElementById('teamMemberLastName').value = '';
    document.getElementById('teamMemberEmail').value = '';
    document.getElementById('teamMemberRole').value = '';
    document.getElementById('teamMemberRoleOther').value = '';
    document.getElementById('teamMemberContact').value = '';
    document.getElementById('teamMemberRoleOtherGroup').classList.add('hidden');
    teamMemberUpload.value = '';
    teamMemberPhotoPreview.classList.add('hidden');
    teamMemberCameraIcon.classList.remove('hidden');

    const roleGroup = document.getElementById('teamMemberRole').closest('.form-group');
    roleGroup.classList.remove('hidden');

    const roleSelect = document.getElementById('teamMemberRole');
    const options = roleSelect.querySelectorAll('option');
    options.forEach(option => {
        option.style.display = '';
    });

    const inputs = ['teamMemberFirstName', 'teamMemberMiddleName', 'teamMemberLastName', 'teamMemberEmail', 'teamMemberRole', 'teamMemberRoleOther', 'teamMemberContact'];
    inputs.forEach(id => {
        const element = document.getElementById(id);
        const errorElement = document.getElementById(id + 'Error');
        if (element) {
            element.classList.remove('border-red-500');
        }
        if (errorElement) {
            errorElement.textContent = '';
            errorElement.classList.add('hidden');
        }
    });
}

function saveAddTeamMember() {
    const firstName = document.getElementById('teamMemberFirstName').value.trim();
    const middleName = document.getElementById('teamMemberMiddleName').value.trim();
    const lastName = document.getElementById('teamMemberLastName').value.trim();
    const email = document.getElementById('teamMemberEmail').value.trim();
    const role = document.getElementById('teamMemberRole').value.trim();
    const roleOther = document.getElementById('teamMemberRoleOther').value.trim();
    const contact = document.getElementById('teamMemberContact').value.trim();
    const contractorId = document.body.dataset.contractorId;

    const inputs = ['teamMemberFirstName', 'teamMemberMiddleName', 'teamMemberLastName', 'teamMemberEmail', 'teamMemberRole', 'teamMemberRoleOther', 'teamMemberContact'];
    inputs.forEach(id => {
        const element = document.getElementById(id);
        const errorElement = document.getElementById(id + 'Error');
        if (element) {
            element.classList.remove('border-red-500');
        }
        if (errorElement) {
            errorElement.textContent = '';
            errorElement.classList.add('hidden');
        }
    });

    const formData = new FormData();
    formData.append('first_name', firstName);
    if (middleName) formData.append('middle_name', middleName);
    formData.append('last_name', lastName);
    if (email) formData.append('email', email);
    formData.append('role', role);
    if (role === 'others' && roleOther) formData.append('role_other', roleOther);
    formData.append('phone_number', contact);
    formData.append('contractor_id', contractorId);

    const profilePicFile = teamMemberUpload.files[0];
    if (profilePicFile) {
        formData.append('profile_pic', profilePicFile);
    }

    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');

    if (csrfToken) {
        formData.append('_token', csrfToken);
    }

    const saveBtn = document.getElementById('saveTeamMemberBtn');
    const originalBtnText = saveBtn.innerHTML;
    saveBtn.disabled = true;
    saveBtn.innerHTML = '<i class="fi fi-rr-spinner animate-spin"></i> Adding...';

    fetch('/admin/user-management/contractor/team-member/store', {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': csrfToken,
            'Accept': 'application/json'
        },
        body: formData
    })
    .then(response => {

        return response.json().then(data => ({
            ok: response.ok,
            status: response.status,
            data: data
        }));
    })
    .then(({ok, status, data}) => {
        if (ok && data.success === true) {
            showNotification(data.message || 'Team member added successfully!', 'success');
            closeAddTeamMemberModal();

            fetchAndUpdateTeamMembers();
        } else {

            if (status === 422 && data.errors) {
                Object.keys(data.errors).forEach(field => {

                    const fieldMap = {
                        'first_name': 'teamMemberFirstName',
                        'middle_name': 'teamMemberMiddleName',
                        'last_name': 'teamMemberLastName',
                        'email': 'teamMemberEmail',
                        'role': 'teamMemberRole',
                        'role_other': 'teamMemberRoleOther',
                        'phone_number': 'teamMemberContact',
                        'contractor_id': null
                    };

                    const elementId = fieldMap[field];
                    if (elementId) {
                        const element = document.getElementById(elementId);
                        const errorElement = document.getElementById(elementId + 'Error');

                        if (element) {
                            element.classList.add('border-red-500');
                        }

                        if (errorElement) {
                            errorElement.textContent = data.errors[field][0];
                            errorElement.classList.remove('hidden');
                        }
                    }
                });

            } else {

                showNotification(data.message || 'Failed to add team member.', 'error');
            }
        }
    })
    .catch(error => {
        console.error('Error:', error);

        showNotification('An error occurred. Please try again.', 'error');
    })
    .finally(() => {

        saveBtn.disabled = false;
        saveBtn.innerHTML = originalBtnText;
    });
}

function openEditTeamMemberModal(row) {
    currentEditingRow = row;

    const nameElement = row.querySelector('.font-medium');
    const fullName = nameElement.textContent.trim();
    const nameParts = fullName.split(' ');
    const firstName = nameParts[0] || '';
    const lastName = nameParts.slice(1).join(' ') || '';

    const positionElement = row.querySelectorAll('td')[1];
    const position = positionElement ? positionElement.textContent.trim() : '';

    const initials = firstName.charAt(0).toUpperCase() + (lastName ? lastName.charAt(0).toUpperCase() : '');
    editTeamMemberInitials.textContent = initials;

    const avatarElement = row.querySelector('.w-10');
    const avatarClasses = avatarElement.className;
    const gradientMatch = avatarClasses.match(/from-(\w+)-\d+\s+to-(\w+)-\d+/);
    if (gradientMatch) {
        const modalAvatar = editTeamMemberModal.querySelector('.w-20.h-20');
        modalAvatar.className = `w-20 h-20 rounded-full bg-gradient-to-br ${gradientMatch[0]} flex items-center justify-center overflow-hidden shadow-md`;
    }

    editTeamMemberPhotoPreview.classList.add('hidden');
    editTeamMemberInitials.classList.remove('hidden');

    document.getElementById('editTeamMemberFirstName').value = firstName;
    document.getElementById('editTeamMemberLastName').value = lastName;
    document.getElementById('editTeamMemberPosition').value = position;

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

    if (!firstName || !lastName || !position || !email) {
        alert('Please fill in all required fields.');
        return;
    }

    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    if (!emailRegex.test(email)) {
        alert('Please enter a valid email address.');
        return;
    }

    const nameElement = currentEditingRow.querySelector('.font-medium');
    const emailElement = currentEditingRow.querySelector('.text-gray-500');
    const positionElement = currentEditingRow.querySelectorAll('td')[1].querySelector('span');

    nameElement.textContent = `${firstName} ${lastName}`;
    emailElement.textContent = email;
    positionElement.textContent = position;

    const avatarElement = currentEditingRow.querySelector('.w-10');
    const initials = firstName.charAt(0).toUpperCase() + lastName.charAt(0).toUpperCase();
    avatarElement.textContent = initials;

    showNotification('Team member updated successfully!', 'success');

    closeEditTeamMemberModal();
}

function openDeactivateTeamMemberModal(row) {
    currentDeactivatingRow = row;

    const nameElement = row.querySelector('.font-medium');
    const memberName = nameElement.textContent.trim();

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

    currentDeactivatingRow.dataset.status = 'deactivated';

    const statusBadge = currentDeactivatingRow.querySelectorAll('td')[3].querySelector('span');
    statusBadge.className = 'inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-gray-100 text-gray-600';
    statusBadge.textContent = 'Deactivated';

    const actionsCell = currentDeactivatingRow.querySelectorAll('td')[4];
    actionsCell.innerHTML = `
        <div class="flex items-center justify-center gap-2">
            <button class="team-reactivate-btn p-2 rounded-lg hover:bg-green-50 transition-all group/btn" title="Reactivate Account">
                <i class="fi fi-rr-check-circle text-green-600 group-hover/btn:scale-110 transition-transform"></i>
            </button>
        </div>
    `;

    currentDeactivatingRow.querySelector('.flex.items-center.gap-3').classList.add('opacity-60');
    const nameSpan = currentDeactivatingRow.querySelector('.font-medium');
    nameSpan.classList.remove('text-gray-800');
    nameSpan.classList.add('text-gray-600');

    const activeTab = document.querySelector('.team-tab.border-orange-500');
    if (activeTab && activeTab.dataset.tab === 'all') {
        currentDeactivatingRow.classList.add('hidden');
    }

    showNotification('Team member deactivated successfully!', 'success');

    closeDeactivateTeamMemberModal();
}

function openReactivateTeamMemberModal(row) {
    currentReactivatingRow = row;

    const nameElement = row.querySelector('.font-medium');
    const memberName = nameElement.textContent.trim();

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

    row.dataset.status = 'active';

    const statusBadge = row.querySelectorAll('td')[3].querySelector('span');
    statusBadge.className = 'inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-green-100 text-green-700 transition-all duration-200 hover:scale-110 hover:shadow-md';
    statusBadge.textContent = 'Active';

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

    row.querySelector('.flex.items-center.gap-3').classList.remove('opacity-60');
    const nameSpan = row.querySelector('.font-medium');
    nameSpan.classList.remove('text-gray-600');
    nameSpan.classList.add('text-gray-800');

    const memberName = nameSpan.textContent.trim();
    const nameParts = memberName.split(' ');
    const firstName = nameParts[0] || '';
    const lastName = nameParts[nameParts.length - 1] || '';

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

    const activeTab = document.querySelector('.team-tab.border-orange-500');
    if (activeTab && activeTab.dataset.tab === 'deactivated') {
        row.classList.add('hidden');
    }

    showNotification('Team member reactivated successfully!', 'success');

    closeReactivateTeamMemberModal();
}

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

document.addEventListener('click', function(e) {

    if (e.target.closest('.team-edit-btn')) {
        const row = e.target.closest('.team-member-row');
        openEditTeamMemberModal(row);
    }

    if (e.target.closest('.team-deactivate-btn')) {
        const row = e.target.closest('.team-member-row');
        openDeactivateTeamMemberModal(row);
    }

    if (e.target.closest('.team-reactivate-btn')) {
        const row = e.target.closest('.team-member-row');
        openReactivateTeamMemberModal(row);
    }
});

if (suspendBtn) suspendBtn.addEventListener('click', openSuspendModal);
if (closeSuspendBtn) closeSuspendBtn.addEventListener('click', closeSuspendModal);
if (cancelSuspendBtn) cancelSuspendBtn.addEventListener('click', closeSuspendModal);
if (confirmSuspendBtn) confirmSuspendBtn.addEventListener('click', confirmSuspend);

if (suspendModal) {
    suspendModal.addEventListener('click', function(e) {
        if (e.target === suspendModal) {
            closeSuspendModal();
        }
    });
}

if (suspendModalContent) {
    suspendModalContent.addEventListener('click', function(e) {
        e.stopPropagation();
    });
}

if (suspendReasonTextarea) {
    suspendReasonTextarea.addEventListener('focus', function() {
        this.classList.add('ring-2', 'ring-red-200');
    });

    suspendReasonTextarea.addEventListener('blur', function() {
        this.classList.remove('ring-2', 'ring-red-200');
    });
}

document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        if (!suspendModal.classList.contains('hidden')) {
            closeSuspendModal();
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

        document.querySelectorAll('.team-member-option').forEach(option => {
            option.classList.remove('border-blue-500', 'bg-blue-50');
            option.classList.add('border-gray-200');
        });

        if (searchTeamMemberInput) searchTeamMemberInput.value = '';
    }, 300);
}

function selectTeamMember(memberElement) {

    document.querySelectorAll('.team-member-option').forEach(option => {
        option.classList.remove('border-blue-500', 'bg-blue-50');
        option.classList.add('border-gray-200');
    });

    memberElement.classList.remove('border-gray-200');
    memberElement.classList.add('border-blue-500', 'bg-blue-50');

    selectedMember = {
        id: memberElement.dataset.memberId,
        name: memberElement.dataset.memberName,
        position: memberElement.dataset.memberPosition,
        phone: memberElement.dataset.memberPhone
    };

    confirmChangeRepresentativeBtn.disabled = false;
}

function confirmChangeRepresentative() {
    if (!selectedMember) return;

    const contractorId = document.body.dataset.contractorId;

    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');

    const originalBtnText = confirmChangeRepresentativeBtn.innerHTML;
    confirmChangeRepresentativeBtn.disabled = true;
    confirmChangeRepresentativeBtn.innerHTML = '<i class="fi fi-rr-spinner animate-spin"></i> Changing...';

    fetch('/admin/user-management/contractor/representative/change', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': csrfToken,
            'Accept': 'application/json'
        },
        body: JSON.stringify({
            contractor_id: contractorId,
            new_representative_id: selectedMember.id,
            _token: csrfToken
        })
    })
    .then(response => {
        return response.json().then(data => ({
            ok: response.ok,
            status: response.status,
            data: data
        }));
    })
    .then(({ok, status, data}) => {
        if (ok && data.success === true) {
            showNotification(data.message || `Company representative changed to ${selectedMember.name}`, 'success');
            closeChangeRepresentativeModal();

            const repNameEl = document.querySelector('[data-representative-name]');
            if (repNameEl) {
                repNameEl.textContent = selectedMember.name;
            }

            fetchAndUpdateTeamMembers();
        } else {

            showNotification(data.message || 'Failed to change representative.', 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showNotification('An error occurred. Please try again.', 'error');
    })
    .finally(() => {

        confirmChangeRepresentativeBtn.disabled = false;
        confirmChangeRepresentativeBtn.innerHTML = originalBtnText;
    });
}

function openEditTeamMemberModal(memberId) {

    fetch(`/admin/user-management/contractor/team-member/${memberId}/edit`, {
        method: 'GET',
        headers: {
            'Accept': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content')
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success && data.data) {
            const member = data.data;

            document.getElementById('editTeamMemberContractorUserId').value = member.contractor_user_id;

            document.getElementById('editTeamMemberFirstName').value = member.first_name || '';
            document.getElementById('editTeamMemberMiddleName').value = member.middle_name || '';
            document.getElementById('editTeamMemberLastName').value = member.last_name || '';
            document.getElementById('editTeamMemberContact').value = member.phone_number || '';
            document.getElementById('editTeamMemberUsername').value = member.username || '';
            document.getElementById('editTeamMemberEmail').value = member.email || '';
            document.getElementById('editTeamMemberPassword').value = ''; // Always blank for security
            document.getElementById('editTeamMemberRole').value = member.role || '';

            const roleOtherDiv = document.getElementById('editRoleOtherDiv');
            const roleOtherInput = document.getElementById('editTeamMemberRoleOther');
            if (member.role === 'others') {
                roleOtherDiv.classList.remove('hidden');
                roleOtherInput.value = member.if_others || '';
            } else {
                roleOtherDiv.classList.add('hidden');
                roleOtherInput.value = '';
            }

            const previewImg = document.getElementById('editTeamMemberPreview');
            const initialsSpan = document.getElementById('editTeamMemberInitials');

            if (member.profile_pic) {
                previewImg.src = `/${member.profile_pic}`;
                previewImg.classList.remove('hidden');
                initialsSpan.classList.add('hidden');
            } else {
                previewImg.classList.add('hidden');
                initialsSpan.classList.remove('hidden');
                const initials = (member.first_name?.[0] || '') + (member.last_name?.[0] || '');
                initialsSpan.textContent = initials.toUpperCase();
            }

            const inputs = ['editTeamMemberFirstName', 'editTeamMemberMiddleName', 'editTeamMemberLastName',
                          'editTeamMemberContact', 'editTeamMemberUsername', 'editTeamMemberEmail',
                          'editTeamMemberPassword', 'editTeamMemberRole', 'editTeamMemberRoleOther'];
            inputs.forEach(id => {
                const element = document.getElementById(id);
                const errorElement = document.getElementById(id.replace('editTeamMember', 'edit') + 'Error');
                if (element) {
                    element.classList.remove('border-red-500');
                }
                if (errorElement) {
                    errorElement.textContent = '';
                    errorElement.classList.add('hidden');
                }
            });

            const modal = document.getElementById('editTeamMemberModal');
            const modalContent = modal.querySelector('.modal-content');
            modal.classList.remove('hidden');
            modal.classList.add('flex');
            setTimeout(() => {
                modalContent.classList.remove('scale-95', 'opacity-0');
                modalContent.classList.add('scale-100', 'opacity-100');
            }, 10);
        }
    })
    .catch(error => {
        console.error('Error:', error);
    });
}

function saveEditTeamMember() {
    const memberId = document.getElementById('editTeamMemberContractorUserId').value;
    const firstName = document.getElementById('editTeamMemberFirstName').value.trim();
    const middleName = document.getElementById('editTeamMemberMiddleName').value.trim();
    const lastName = document.getElementById('editTeamMemberLastName').value.trim();
    const contact = document.getElementById('editTeamMemberContact').value.trim();
    const username = document.getElementById('editTeamMemberUsername').value.trim();
    const email = document.getElementById('editTeamMemberEmail').value.trim();
    const password = document.getElementById('editTeamMemberPassword').value.trim();
    const role = document.getElementById('editTeamMemberRole').value.trim();
    const roleOther = document.getElementById('editTeamMemberRoleOther').value.trim();

    const inputs = ['editTeamMemberFirstName', 'editTeamMemberMiddleName', 'editTeamMemberLastName',
                  'editTeamMemberContact', 'editTeamMemberUsername', 'editTeamMemberEmail',
                  'editTeamMemberPassword', 'editTeamMemberRole', 'editTeamMemberRoleOther'];
    inputs.forEach(id => {
        const element = document.getElementById(id);
        const errorElement = document.getElementById(id.replace('editTeamMember', 'edit') + 'Error');
        if (element) {
            element.classList.remove('border-red-500');
        }
        if (errorElement) {
            errorElement.textContent = '';
            errorElement.classList.add('hidden');
        }
    });

    const formData = new FormData();
    formData.append('contractor_user_id', memberId);
    formData.append('first_name', firstName);
    if (middleName) formData.append('middle_name', middleName);
    formData.append('last_name', lastName);
    formData.append('phone_number', contact);
    formData.append('username', username);
    formData.append('email', email);
    if (password) formData.append('password', password); // Only add if not empty
    formData.append('role', role);
    if (role === 'others' && roleOther) formData.append('role_other', roleOther);

    const profilePicFile = document.getElementById('editTeamMemberUpload').files[0];
    if (profilePicFile) {
        formData.append('profile_pic', profilePicFile);
    }

    formData.append('_method', 'PUT');

    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
    if (csrfToken) {
        formData.append('_token', csrfToken);
    }

    const saveBtn = document.getElementById('saveEditTeamMemberBtn');
    const originalBtnText = saveBtn.innerHTML;
    saveBtn.disabled = true;
    saveBtn.innerHTML = '<i class="fi fi-rr-spinner animate-spin"></i> Saving...';

    fetch(`/admin/user-management/contractor/team-member/update/${memberId}`, {
        method: 'POST', // Using POST with _method spoofing
        headers: {
            'X-CSRF-TOKEN': csrfToken,
            'Accept': 'application/json'
        },
        body: formData
    })
    .then(response => {
        return response.json().then(data => ({
            ok: response.ok,
            status: response.status,
            data: data
        }));
    })
    .then(({ok, status, data}) => {
        if (ok && data.success === true) {
            showNotification(data.message || 'Team member updated successfully!', 'success');
            closeEditTeamMemberModal();

            fetchAndUpdateTeamMembers();
        } else {

            if (status === 422 && data.errors) {
                Object.keys(data.errors).forEach(field => {

                    const fieldMap = {
                        'first_name': 'editTeamMemberFirstName',
                        'middle_name': 'editTeamMemberMiddleName',
                        'last_name': 'editTeamMemberLastName',
                        'phone_number': 'editTeamMemberContact',
                        'username': 'editTeamMemberUsername',
                        'email': 'editTeamMemberEmail',
                        'password': 'editTeamMemberPassword',
                        'role': 'editTeamMemberRole',
                        'role_other': 'editTeamMemberRoleOther',
                        'contractor_user_id': null
                    };

                    const elementId = fieldMap[field];
                    if (elementId) {
                        const element = document.getElementById(elementId);
                        const errorId = elementId.replace('editTeamMember', 'edit') + 'Error';
                        const errorElement = document.getElementById(errorId);

                        if (element) {
                            element.classList.add('border-red-500');
                        }

                        if (errorElement) {
                            errorElement.textContent = data.errors[field][0];
                            errorElement.classList.remove('hidden');
                        }
                    }
                });

            } else {

                showNotification(data.message || 'Failed to update team member.', 'error');
            }
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showNotification('An error occurred. Please try again.', 'error');
    })
    .finally(() => {

        saveBtn.disabled = false;
        saveBtn.innerHTML = originalBtnText;
    });
}

function closeEditTeamMemberModal() {
    const modal = document.getElementById('editTeamMemberModal');
    const modalContent = modal.querySelector('.modal-content');

    modalContent.classList.remove('scale-100', 'opacity-100');
    modalContent.classList.add('scale-95', 'opacity-0');

    setTimeout(() => {
        modal.classList.add('hidden');
        modal.classList.remove('flex');

        document.getElementById('editTeamMemberFirstName').value = '';
        document.getElementById('editTeamMemberMiddleName').value = '';
        document.getElementById('editTeamMemberLastName').value = '';
        document.getElementById('editTeamMemberContact').value = '';
        document.getElementById('editTeamMemberUsername').value = '';
        document.getElementById('editTeamMemberEmail').value = '';
        document.getElementById('editTeamMemberPassword').value = '';
        document.getElementById('editTeamMemberRole').value = '';
        document.getElementById('editTeamMemberRoleOther').value = '';
        document.getElementById('editRoleOtherDiv').classList.add('hidden');

        const previewImg = document.getElementById('editTeamMemberPreview');
        const initialsSpan = document.getElementById('editTeamMemberInitials');
        previewImg.src = '';
        previewImg.classList.add('hidden');
        initialsSpan.classList.remove('hidden');
        initialsSpan.textContent = 'TM';

        document.getElementById('editTeamMemberUpload').value = '';
    }, 300);
}

let currentDeactivateMemberId = null;

function openDeactivateTeamMemberModal(memberId, memberName) {
    currentDeactivateMemberId = memberId;

    document.getElementById('deactivateTeamMemberName').textContent = memberName;

    const reasonTextarea = document.getElementById('deactivateTeamMemberReason');
    const errorElement = document.getElementById('deactivateReasonError');

    reasonTextarea.value = '';
    reasonTextarea.classList.remove('border-red-500');
    errorElement.textContent = '';
    errorElement.classList.add('hidden');

    const modal = document.getElementById('deactivateTeamMemberModal');
    const modalContent = modal.querySelector('.modal-content');
    modal.classList.remove('hidden');
    modal.classList.add('flex');
    setTimeout(() => {
        modalContent.classList.remove('scale-95', 'opacity-0');
        modalContent.classList.add('scale-100', 'opacity-100');
    }, 10);
}

function closeDeactivateTeamMemberModal() {
    const modal = document.getElementById('deactivateTeamMemberModal');
    const modalContent = modal.querySelector('.modal-content');

    modalContent.classList.remove('scale-100', 'opacity-100');
    modalContent.classList.add('scale-95', 'opacity-0');

    setTimeout(() => {
        modal.classList.add('hidden');
        modal.classList.remove('flex');
        currentDeactivateMemberId = null;

        document.getElementById('deactivateTeamMemberReason').value = '';
        document.getElementById('deactivateReasonError').classList.add('hidden');
    }, 300);
}

function confirmDeactivateTeamMember() {
    const reason = document.getElementById('deactivateTeamMemberReason').value.trim();
    const reasonTextarea = document.getElementById('deactivateTeamMemberReason');
    const errorElement = document.getElementById('deactivateReasonError');

    reasonTextarea.classList.remove('border-red-500');
    errorElement.classList.add('hidden');
    errorElement.textContent = '';

    if (!reason) {
        reasonTextarea.classList.add('border-red-500');
        errorElement.textContent = 'Deactivation reason is required.';
        errorElement.classList.remove('hidden');
        return;
    }

    if (reason.length < 10) {
        reasonTextarea.classList.add('border-red-500');
        errorElement.textContent = 'Reason must be at least 10 characters.';
        errorElement.classList.remove('hidden');
        return;
    }

    const formData = new FormData();
    formData.append('contractor_user_id', currentDeactivateMemberId);
    formData.append('deletion_reason', reason);
    formData.append('_method', 'DELETE');

    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
    if (csrfToken) {
        formData.append('_token', csrfToken);
    }

    const confirmBtn = document.getElementById('confirmDeactivateTeamMemberBtn');
    const originalBtnText = confirmBtn.innerHTML;
    confirmBtn.disabled = true;
    confirmBtn.innerHTML = '<i class="fi fi-rr-spinner animate-spin"></i> Deactivating...';

    fetch(`/admin/user-management/contractor/team-member/deactivate/${currentDeactivateMemberId}`, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': csrfToken,
            'Accept': 'application/json'
        },
        body: formData
    })
    .then(response => {
        return response.json().then(data => ({
            ok: response.ok,
            status: response.status,
            data: data
        }));
    })
    .then(({ok, status, data}) => {
        if (ok && data.success === true) {
            showNotification(data.message || 'Team member deactivated successfully!', 'success');
            closeDeactivateTeamMemberModal();

            fetchAndUpdateTeamMembers();
        } else {

            if (status === 422 && data.errors) {
                if (data.errors.deletion_reason) {
                    reasonTextarea.classList.add('border-red-500');
                    errorElement.textContent = data.errors.deletion_reason[0];
                    errorElement.classList.remove('hidden');
                }
            } else {

                showNotification(data.message || 'Failed to deactivate team member.', 'error');
            }
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showNotification('An error occurred. Please try again.', 'error');
    })
    .finally(() => {

        confirmBtn.disabled = false;
        confirmBtn.innerHTML = originalBtnText;
    });
}

let currentReactivateMemberId = null;

function openReactivateTeamMemberModal(memberId, memberName) {
    currentReactivateMemberId = memberId;

    document.getElementById('reactivateTeamMemberName').textContent = memberName;

    const modal = document.getElementById('reactivateTeamMemberModal');
    const modalContent = modal.querySelector('.modal-content');
    modal.classList.remove('hidden');
    modal.classList.add('flex');
    setTimeout(() => {
        modalContent.classList.remove('scale-95', 'opacity-0');
        modalContent.classList.add('scale-100', 'opacity-100');
    }, 10);
}

function closeReactivateTeamMemberModal() {
    const modal = document.getElementById('reactivateTeamMemberModal');
    const modalContent = modal.querySelector('.modal-content');

    modalContent.classList.remove('scale-100', 'opacity-100');
    modalContent.classList.add('scale-95', 'opacity-0');

    setTimeout(() => {
        modal.classList.add('hidden');
        modal.classList.remove('flex');
        currentReactivateMemberId = null;
    }, 300);
}

function confirmReactivateTeamMember() {

    const formData = new FormData();
    formData.append('contractor_user_id', currentReactivateMemberId);
    formData.append('_method', 'PATCH');

    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
    if (csrfToken) {
        formData.append('_token', csrfToken);
    }

    const confirmBtn = document.getElementById('confirmReactivateTeamMemberBtn');
    const originalBtnText = confirmBtn.innerHTML;
    confirmBtn.disabled = true;
    confirmBtn.innerHTML = '<i class="fi fi-rr-spinner animate-spin"></i> Reactivating...';

    fetch(`/admin/user-management/contractor/team-member/reactivate/${currentReactivateMemberId}`, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': csrfToken,
            'Accept': 'application/json'
        },
        body: formData
    })
    .then(response => {
        return response.json().then(data => ({
            ok: response.ok,
            status: response.status,
            data: data
        }));
    })
    .then(({ok, status, data}) => {
        if (ok && data.success === true) {
            showNotification(data.message || 'Team member reactivated successfully!', 'success');
            closeReactivateTeamMemberModal();

            fetchAndUpdateTeamMembers();
        } else {

            showNotification(data.message || 'Failed to reactivate team member.', 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showNotification('An error occurred. Please try again.', 'error');
    })
    .finally(() => {

        confirmBtn.disabled = false;
        confirmBtn.innerHTML = originalBtnText;
    });
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

if (changeRepresentativeBtn) {
    changeRepresentativeBtn.addEventListener('click', function() {

        openChangeRepresentativeModal();
    });
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

document.addEventListener('click', function(e) {
    const memberOption = e.target.closest('.team-member-option');
    if (memberOption) {
        selectTeamMember(memberOption);
    }
});

const addNewRepresentativeBtn = document.getElementById('addNewRepresentativeBtn');
if (addNewRepresentativeBtn) {
    addNewRepresentativeBtn.addEventListener('click', function() {

        closeChangeRepresentativeModal();

        setTimeout(() => {
            openAddTeamMemberModal(true, true);
        }, 300);
    });
}

const backToRepresentativeModalBtn = document.getElementById('backToRepresentativeModalBtn');
if (backToRepresentativeModalBtn) {
    backToRepresentativeModalBtn.addEventListener('click', function() {

        closeAddTeamMemberModal();

        setTimeout(() => {
            openChangeRepresentativeModal();
        }, 300);
    });
}

if (changeRepresentativeModal) {
    changeRepresentativeModal.addEventListener('click', function(e) {
        if (e.target === changeRepresentativeModal) {
            closeChangeRepresentativeModal();
        }
    });
}

if (document.querySelector('.team-tab')) {
    initTeamMemberTabs();
}

if (addTeamMemberBtn) addTeamMemberBtn.addEventListener('click', () => openAddTeamMemberModal(false));
if (closeAddTeamMemberBtn) closeAddTeamMemberBtn.addEventListener('click', closeAddTeamMemberModal);
if (cancelAddTeamMemberBtn) cancelAddTeamMemberBtn.addEventListener('click', closeAddTeamMemberModal);
if (saveTeamMemberBtn) saveTeamMemberBtn.addEventListener('click', saveAddTeamMember);

const teamMemberRoleSelect = document.getElementById('teamMemberRole');
const teamMemberRoleOtherGroup = document.getElementById('teamMemberRoleOtherGroup');
if (teamMemberRoleSelect && teamMemberRoleOtherGroup) {
    teamMemberRoleSelect.addEventListener('change', function() {
        if (this.value === 'others') {
            teamMemberRoleOtherGroup.classList.remove('hidden');
        } else {
            teamMemberRoleOtherGroup.classList.add('hidden');
            document.getElementById('teamMemberRoleOther').value = '';
        }
    });
}

const editTeamMemberRoleSelect = document.getElementById('editTeamMemberRole');
const editRoleOtherDiv = document.getElementById('editRoleOtherDiv');
if (editTeamMemberRoleSelect && editRoleOtherDiv) {
    editTeamMemberRoleSelect.addEventListener('change', function() {
        if (this.value === 'others') {
            editRoleOtherDiv.classList.remove('hidden');
        } else {
            editRoleOtherDiv.classList.add('hidden');
            document.getElementById('editTeamMemberRoleOther').value = '';
        }
    });
}

document.addEventListener('click', function(e) {
    const editBtn = e.target.closest('.team-edit-btn');
    if (editBtn) {
        const memberId = editBtn.getAttribute('data-member-id');
        if (memberId) {
            openEditTeamMemberModal(memberId);
        }
    }

    const deactivateBtn = e.target.closest('.team-deactivate-btn');
    if (deactivateBtn) {
        const memberId = deactivateBtn.getAttribute('data-member-id');
        const memberName = deactivateBtn.getAttribute('data-member-name');
        if (memberId && memberName) {
            openDeactivateTeamMemberModal(memberId, memberName);
        }
    }

    const reactivateBtn = e.target.closest('.team-reactivate-btn');
    if (reactivateBtn) {
        const memberId = reactivateBtn.getAttribute('data-member-id');
        const memberName = reactivateBtn.getAttribute('data-member-name');
        if (memberId && memberName) {
            openReactivateTeamMemberModal(memberId, memberName);
        }
    }
});

if (closeEditTeamMemberBtn) closeEditTeamMemberBtn.addEventListener('click', closeEditTeamMemberModal);
if (cancelEditTeamMemberBtn) cancelEditTeamMemberBtn.addEventListener('click', closeEditTeamMemberModal);
if (saveEditTeamMemberBtn) saveEditTeamMemberBtn.addEventListener('click', saveEditTeamMember);

if (closeDeactivateTeamMemberBtn) closeDeactivateTeamMemberBtn.addEventListener('click', closeDeactivateTeamMemberModal);
if (cancelDeactivateTeamMemberBtn) cancelDeactivateTeamMemberBtn.addEventListener('click', closeDeactivateTeamMemberModal);
if (confirmDeactivateTeamMemberBtn) confirmDeactivateTeamMemberBtn.addEventListener('click', confirmDeactivateTeamMember);

if (cancelReactivateTeamMemberBtn) cancelReactivateTeamMemberBtn.addEventListener('click', closeReactivateTeamMemberModal);
if (confirmReactivateTeamMemberBtn) confirmReactivateTeamMemberBtn.addEventListener('click', confirmReactivateTeamMember);

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

document.querySelectorAll('a[href^="#"]').forEach(anchor => {
    anchor.addEventListener('click', function (e) {
        const href = this.getAttribute('href');

        if (href && href.startsWith('#') && href.length > 1) {
            try {
                const target = document.querySelector(href);
                if (target) {
                    e.preventDefault();
                    target.scrollIntoView({
                        behavior: 'smooth',
                        block: 'start'
                    });
                }
            } catch (err) {

                console.warn('Invalid selector:', href);
            }
        }
    });
});
