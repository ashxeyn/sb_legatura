// ============================================
// TEAM MEMBERS TABLE AJAX REFRESH (Following propertyOwner.js pattern)
// ============================================

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

        // Re-attach team member action listeners
        attachTeamMemberListeners();

        // Re-apply current tab filter and pagination
        applyCurrentTeamTabFilter();
        updateTeamMembersPagination();

        // Also refresh the change representative modal list
        refreshRepresentativeModalList();

    } catch (error) {
        console.error('Error fetching team members data:', error);
        showNotification('Failed to refresh team members list', 'error');
    }
}

// Refresh contractor details after edit (called from contractor.js)
// Using location.replace to refresh without adding to browser history
function refreshContractorDetails() {
    const contractorId = document.body.dataset.contractorId;
    if (!contractorId) {
        console.error('Contractor ID not found');
        return;
    }

    // Use replace to refresh page without affecting browser history
    setTimeout(() => {
        window.location.replace(`/admin/user-management/contractor/view?id=${contractorId}`);
    }, 500);
}

// Make it globally accessible for contractor.js
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

function attachRepresentativeSelectionListeners() {
    // Since we're using event delegation on document, we don't need to re-attach
    // But we should re-initialize the search functionality
    const searchTeamMemberInput = document.getElementById('searchTeamMember');
    if (searchTeamMemberInput) {
        searchTeamMemberInput.value = '';
    }
}

function attachTeamMemberListeners() {
    // Re-attach edit button listeners
    document.querySelectorAll('.team-edit-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            const memberId = this.dataset.memberId;
            openEditTeamMemberModal(memberId);
        });
    });

    // Re-attach deactivate button listeners
    document.querySelectorAll('.team-deactivate-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            const memberId = this.dataset.memberId;
            const memberName = this.dataset.memberName;
            openDeactivateTeamMemberModal(memberId, memberName);
        });
    });

    // Re-attach reactivate button listeners
    document.querySelectorAll('.team-reactivate-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            const memberId = this.dataset.memberId;
            const memberName = this.dataset.memberName;
            openReactivateTeamMemberModal(memberId, memberName);
        });
    });
}

// Initial attachment on page load
document.addEventListener('DOMContentLoaded', function() {
    attachTeamMemberListeners();
});

// ============================================
// SUSPEND MODAL FUNCTIONS
// ============================================

// Suspend Modal Elements
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

// ============================================
// SUSPEND MODAL FUNCTIONS
// ============================================

// Toggle date picker visibility
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

        // Reset form
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
    notification.className = `fixed top-20 right-4 z-[60] max-w-[280px] px-3 py-2 rounded-md shadow-lg transform transition-all duration-500 translate-x-full ${
        type === 'success' ? 'bg-green-500' : 'bg-red-500'
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

function confirmSuspend() {
    const reason = suspendReasonTextarea ? suspendReasonTextarea.value.trim() : '';
    const selectedDuration = suspendModal.querySelector('input[name="suspensionDuration"]:checked');
    const duration = selectedDuration ? selectedDuration.value : 'temporary';
    let suspensionDate = null;
    let hasError = false;

    // Reset errors
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

    // Add loading state
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

            // Update status badge instantly
            const statusBadge = document.querySelector('.text-xs.font-medium.px-2\\.5.py-1');
            if (statusBadge) {
                statusBadge.className = 'text-xs font-medium px-2.5 py-1 rounded-full bg-red-100 text-red-600';
                statusBadge.textContent = 'Suspended';
            }

            // Hide suspend button
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

// ============================================
// TEAM MEMBERS MODAL ELEMENTS
// ============================================

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
const closeReactivateTeamMemberBtn = document.getElementById('closeReactivateTeamMemberBtn');
const cancelReactivateTeamMemberBtn = document.getElementById('cancelReactivateTeamMemberBtn');
const confirmReactivateTeamMemberBtn = document.getElementById('confirmReactivateTeamMemberBtn');

let currentEditingRow = null;
let currentDeactivatingRow = null;
let currentReactivatingRow = null;

// ============================================
// TEAM MEMBERS TAB SWITCHING + PAGINATION
// ============================================

const TEAM_MEMBERS_PAGE_SIZE = 10;
let currentTeamTab = 'active';
let currentTeamPage = 1;

function getVisibleTeamRows() {
    return Array.from(document.querySelectorAll('.team-member-row')).filter(function (row) {
        return !row.classList.contains('hidden');
    });
}

function updateTeamMembersPagination() {
    const visible = getVisibleTeamRows();
    const total = visible.length;
    const totalPages = Math.max(1, Math.ceil(total / TEAM_MEMBERS_PAGE_SIZE));
    const page = Math.min(currentTeamPage, totalPages) || 1;
    currentTeamPage = page;

    // Remove pagination-off from all, then add to rows not on current page
    document.querySelectorAll('.team-member-row').forEach(function (r) {
        r.classList.remove('team-pagination-off');
    });
    const start = (page - 1) * TEAM_MEMBERS_PAGE_SIZE;
    const end = start + TEAM_MEMBERS_PAGE_SIZE;
    visible.forEach(function (row, i) {
        if (i < start || i >= end) {
            row.classList.add('team-pagination-off');
        }
    });

    const from = total === 0 ? 0 : start + 1;
    const to = total === 0 ? 0 : Math.min(end, total);

    const wrap = document.getElementById('teamMembersPagination');
    const emptyWrap = document.getElementById('teamMembersPaginationEmpty');
    const fromEl = document.getElementById('teamMembersPaginationFrom');
    const toEl = document.getElementById('teamMembersPaginationTo');
    const totalEl = document.getElementById('teamMembersPaginationTotal');
    const totalSingleEl = document.getElementById('teamMembersPaginationTotalSingle');
    const prevBtn = document.getElementById('teamMembersPaginationPrev');
    const nextBtn = document.getElementById('teamMembersPaginationNext');
    const pagesEl = document.getElementById('teamMembersPaginationPages');

    if (!wrap || !emptyWrap) return;

    if (total === 0) {
        wrap.classList.add('hidden');
        emptyWrap.classList.remove('hidden');
        if (totalSingleEl) totalSingleEl.textContent = '0';
        return;
    }

    if (totalPages <= 1) {
        wrap.classList.add('hidden');
        emptyWrap.classList.remove('hidden');
        if (totalSingleEl) totalSingleEl.textContent = total;
        return;
    }

    emptyWrap.classList.add('hidden');
    wrap.classList.remove('hidden');
    if (fromEl) fromEl.textContent = from;
    if (toEl) toEl.textContent = to;
    if (totalEl) totalEl.textContent = total;
    if (totalSingleEl) totalSingleEl.textContent = total;

    if (prevBtn) {
        prevBtn.disabled = page <= 1;
    }
    if (nextBtn) {
        nextBtn.disabled = page >= totalPages;
    }

    // Page number links (same pattern as contractorTable)
    if (pagesEl) {
        const startPage = Math.max(1, page - 2);
        const endPage = Math.min(totalPages, page + 2);
        let html = '';
        for (let p = startPage; p <= endPage; p++) {
            if (p === page) {
                html += '<span class="px-2.5 py-1 rounded-lg text-xs bg-indigo-600 text-white font-semibold">' + p + '</span>';
            } else {
                html += '<button type="button" class="team-member-page-link px-2.5 py-1 rounded-lg text-xs border border-gray-200 hover:bg-gray-50 transition" data-page="' + p + '">' + p + '</button>';
            }
        }
        pagesEl.innerHTML = html;
        pagesEl.querySelectorAll('.team-member-page-link').forEach(function (btn) {
            btn.addEventListener('click', function () {
                currentTeamPage = parseInt(this.dataset.page, 10);
                updateTeamMembersPagination();
            });
        });
    }
}

function initTeamMemberTabs() {
    const tabs = document.querySelectorAll('.team-tab');
    const statusHeader = document.getElementById('statusColumnHeader');

    tabs.forEach(tab => {
        tab.addEventListener('click', function() {
            const tabName = this.dataset.tab;
            currentTeamTab = tabName;
            currentTeamPage = 1;

            // Update active tab styles
            tabs.forEach(t => {
                t.classList.remove('border-orange-500', 'text-orange-600');
                t.classList.add('border-transparent', 'text-gray-600');
            });
            this.classList.remove('border-transparent', 'text-gray-600');
            this.classList.add('border-orange-500', 'text-orange-600');

            // Update column header and visibility based on tab
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

            // Filter table rows by tab
            const tableRows = document.querySelectorAll('.team-member-row');
            tableRows.forEach(row => {
                const rowStatus = row.dataset.status;
                if (tabName === 'active') {
                    row.classList.toggle('hidden', rowStatus !== 'active');
                } else if (tabName === 'pending') {
                    row.classList.toggle('hidden', rowStatus !== 'pending');
                } else if (tabName === 'cancelled') {
                    row.classList.toggle('hidden', rowStatus !== 'cancelled');
                } else if (tabName === 'deactivated') {
                    row.classList.toggle('hidden', rowStatus !== 'deactivated');
                }
            });

            updateTeamMembersPagination();
        });
    });

    // Initial pagination (default tab = active)
    updateTeamMembersPagination();
}

function initTeamMembersPaginationButtons() {
    const prevBtn = document.getElementById('teamMembersPaginationPrev');
    const nextBtn = document.getElementById('teamMembersPaginationNext');
    if (prevBtn) {
        prevBtn.addEventListener('click', function () {
            if (currentTeamPage > 1) {
                currentTeamPage--;
                updateTeamMembersPagination();
            }
        });
    }
    if (nextBtn) {
        nextBtn.addEventListener('click', function () {
            const visible = getVisibleTeamRows();
            const totalPages = Math.max(1, Math.ceil(visible.length / TEAM_MEMBERS_PAGE_SIZE));
            if (currentTeamPage < totalPages) {
                currentTeamPage++;
                updateTeamMembersPagination();
            }
        });
    }
}

function applyCurrentTeamTabFilter() {
    const statusHeader = document.getElementById('statusColumnHeader');
    const tabName = currentTeamTab;
    if (tabName === 'deactivated' && statusHeader) {
        statusHeader.textContent = 'Reason';
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
        if (tabName === 'active') row.classList.toggle('hidden', rowStatus !== 'active');
        else if (tabName === 'pending') row.classList.toggle('hidden', rowStatus !== 'pending');
        else if (tabName === 'cancelled') row.classList.toggle('hidden', rowStatus !== 'cancelled');
        else if (tabName === 'deactivated') row.classList.toggle('hidden', rowStatus !== 'deactivated');
    });
}

// ============================================
// DOCUMENT VIEWER MODAL
// ============================================

(function() {
    document.addEventListener('DOMContentLoaded', function() {
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
        // Ignore elements that have a specific data-doc-scope (handled by specialized viewers)
        document.addEventListener('click', function(e) {
            const btn = e.target.closest && e.target.closest('.open-doc-btn');
            if (btn) {
                // If a scope is defined, let the scoped handler manage it (prevents duplicate viewers)
                if (btn.hasAttribute('data-doc-scope')) return;
                const src = btn.getAttribute('data-doc-src');
                if (src) openDocumentViewer(src);
            }
        });

        // Close handlers
        if (closeBtn) closeBtn.addEventListener('click', closeDocumentViewer);
        modal.addEventListener('click', function(e) {
            if (e.target === modal) closeDocumentViewer();
        });
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') closeDocumentViewer();
        });

        // Expose for debugging
        window.openDocumentViewer = openDocumentViewer;
        window.closeDocumentViewer = closeDocumentViewer;
    });
})();

// ============================================
// ADD TEAM MEMBER MODAL FUNCTIONS
// ============================================

function openAddTeamMemberModal(isRepresentative = false, fromChangeRepModal = false) {
    // Store context in modal dataset
    addTeamMemberModal.dataset.isRepresentative = isRepresentative;
    addTeamMemberModal.dataset.fromChangeRepModal = fromChangeRepModal;

    // Show/hide back button based on context
    const backBtn = document.getElementById('backToRepresentativeModalBtn');
    if (backBtn) {
        if (fromChangeRepModal) {
            backBtn.classList.remove('hidden');
        } else {
            backBtn.classList.add('hidden');
        }
    }

    const roleSelect = document.getElementById('teamMemberRole');
    const roleGroup = roleSelect ? roleSelect.closest('.form-group') : null;

    if (isRepresentative && roleSelect && roleGroup) {
        // Hide role selection for representative
        roleGroup.classList.add('hidden');
        // Auto-set role to representative
        roleSelect.value = 'representative';
    } else if (roleSelect && roleGroup) {
        // Show role selection for regular member
        roleGroup.classList.remove('hidden');
        // Remove owner and representative options
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
    // Clear search input
    if (teamMemberOwnerSearch) {
        teamMemberOwnerSearch.value = '';
    }

    // Hide dropdown
    if (teamMemberOwnerDropdown) {
        teamMemberOwnerDropdown.classList.add('hidden');
    }

    // Clear selected property owners
    selectedPropertyOwners = [];
    updateSelectedMembersList();

    // Reset role selection
    const roleSelect = document.getElementById('teamMemberRole');
    const roleOtherInput = document.getElementById('teamMemberRoleOther');
    const roleOtherGroup = document.getElementById('teamMemberRoleOtherGroup');
    const roleSelectionSection = document.getElementById('roleSelectionSection');

    if (roleSelect) roleSelect.value = '';
    if (roleOtherInput) roleOtherInput.value = '';
    if (roleOtherGroup) roleOtherGroup.classList.add('hidden');
    if (roleSelectionSection) roleSelectionSection.classList.add('hidden');

    // Clear error states
    if (roleSelect) roleSelect.classList.remove('border-red-500');
    if (roleOtherInput) roleOtherInput.classList.remove('border-red-500');

    const roleError = document.getElementById('teamMemberRoleError');
    const roleOtherError = document.getElementById('teamMemberRoleOtherError');
    if (roleError) roleError.classList.add('hidden');
    if (roleOtherError) roleOtherError.classList.add('hidden');
}

// ============================================
// PROPERTY OWNER SEARCH FOR ADD TEAM MEMBER
// ============================================

let selectedPropertyOwners = [];
const teamMemberOwnerSearch = document.getElementById('teamMemberOwnerSearch');
const teamMemberOwnerDropdown = document.getElementById('teamMemberOwnerDropdown');
const teamMemberOwnerList = document.getElementById('teamMemberOwnerList');
const teamMemberOwnerLoading = document.getElementById('teamMemberOwnerLoading');
const teamMemberOwnerEmpty = document.getElementById('teamMemberOwnerEmpty');
const selectedMembersList = document.getElementById('selectedMembersList');
const selectedMembersContainer = document.getElementById('selectedMembersContainer');
const selectedMembersCount = document.getElementById('selectedMembersCount');

// Debounce function
function debounce(func, wait) {
    let timeout;
    return function executedFunction(...args) {
        const context = this;
        const later = function() {
            clearTimeout(timeout);
            func.apply(context, args);
        };
        clearTimeout(timeout);
        timeout = setTimeout(later, wait);
    };
}

// Search property owners
async function searchPropertyOwners(searchTerm) {
    if (!teamMemberOwnerLoading || !teamMemberOwnerList || !teamMemberOwnerEmpty) return;

    // Show loading
    teamMemberOwnerLoading.classList.remove('hidden');
    teamMemberOwnerList.innerHTML = '';
    teamMemberOwnerEmpty.classList.add('hidden');

    try {
        const response = await fetch(`/admin/user-management/contractor/available-owners?search=${encodeURIComponent(searchTerm)}`, {
            headers: {
                'Accept': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content')
            }
        });

        const result = await response.json();

        // Hide loading
        teamMemberOwnerLoading.classList.add('hidden');

        if (result.success && result.data && result.data.length > 0) {
            // Filter out already selected owners
            const selectedOwnerIds = selectedPropertyOwners.map(o => o.owner_id);
            const availableOwners = result.data.filter(owner => !selectedOwnerIds.includes(owner.owner_id));

            if (availableOwners.length === 0) {
                teamMemberOwnerEmpty.classList.remove('hidden');
                return;
            }

            // Populate dropdown (compact items, consistent with modal design)
            teamMemberOwnerList.innerHTML = availableOwners.map(owner => {
                const fullName = `${owner.first_name || ''} ${owner.middle_name || ''} ${owner.last_name || ''}`.trim();
                const initials = `${owner.first_name?.[0] || ''}${owner.last_name?.[0] || ''}`.toUpperCase();

                return `
                    <div class="owner-option px-3 py-2 hover:bg-orange-50 cursor-pointer transition-colors border-b border-gray-100 last:border-b-0 flex items-center gap-2.5"
                         data-owner-id="${owner.owner_id}"
                         data-owner-name="${fullName}"
                         data-owner-email="${owner.email}"
                         data-owner-username="${owner.username}"
                         data-owner-pic="${owner.profile_pic || ''}">
                        <div class="w-8 h-8 rounded-full bg-gradient-to-br from-orange-400 to-orange-600 flex items-center justify-center text-white text-[10px] font-bold flex-shrink-0 overflow-hidden">
                            ${owner.profile_pic ?
                                `<img src="/storage/${owner.profile_pic}" alt="${fullName}" class="w-full h-full object-cover">` :
                                initials
                            }
                        </div>
                        <div class="flex-1 min-w-0">
                            <p class="text-[13px] font-semibold text-gray-800 truncate">${fullName}</p>
                            <p class="text-[11px] text-gray-500 truncate">${owner.email}</p>
                        </div>
                    </div>
                `;
            }).join('');

            // Add click listeners to options
            document.querySelectorAll('.owner-option').forEach(option => {
                option.addEventListener('click', function() {
                    selectPropertyOwner(this);
                });
            });
        } else {
            teamMemberOwnerEmpty.classList.remove('hidden');
        }
    } catch (error) {
        console.error('Error searching property owners:', error);
        teamMemberOwnerLoading.classList.add('hidden');
        teamMemberOwnerEmpty.classList.remove('hidden');
    }
}

// Select a property owner
function selectPropertyOwner(optionElement) {
    const owner = {
        owner_id: optionElement.dataset.ownerId,
        name: optionElement.dataset.ownerName,
        email: optionElement.dataset.ownerEmail,
        username: optionElement.dataset.ownerUsername,
        profile_pic: optionElement.dataset.ownerPic
    };

    // Add to selected list
    selectedPropertyOwners.push(owner);

    // Update UI
    updateSelectedMembersList();

    // Clear search and hide dropdown
    if (teamMemberOwnerSearch) teamMemberOwnerSearch.value = '';
    if (teamMemberOwnerDropdown) teamMemberOwnerDropdown.classList.add('hidden');
}

// Remove a selected property owner
function removeSelectedOwner(ownerId) {
    selectedPropertyOwners = selectedPropertyOwners.filter(o => o.owner_id !== ownerId);
    updateSelectedMembersList();
}

// Update the selected members list UI
function updateSelectedMembersList() {
    if (!selectedMembersContainer || !selectedMembersCount || !selectedMembersList) return;

    const roleSelectionSection = document.getElementById('roleSelectionSection');

    if (selectedPropertyOwners.length === 0) {
        selectedMembersList.classList.add('hidden');
        if (roleSelectionSection) roleSelectionSection.classList.add('hidden');
        return;
    }

    selectedMembersList.classList.remove('hidden');
    if (roleSelectionSection) roleSelectionSection.classList.add('hidden'); // Hide the global role section
    selectedMembersCount.textContent = selectedPropertyOwners.length;

    selectedMembersContainer.innerHTML = selectedPropertyOwners.map((owner, index) => {
        const initials = owner.name.split(' ').map(n => n[0]).join('').toUpperCase().substring(0, 2);

        return `
            <div class="flex items-start gap-3 p-3 bg-white rounded-lg border border-gray-200 hover:border-orange-300 transition-colors">
                <div class="flex items-center gap-3 flex-1">
                    <div class="w-10 h-10 rounded-full bg-gradient-to-br from-orange-400 to-orange-600 flex items-center justify-center text-white font-bold text-sm overflow-hidden flex-shrink-0">
                        ${owner.profile_pic ?
                            `<img src="/storage/${owner.profile_pic}" alt="${owner.name}" class="w-full h-full object-cover">` :
                            initials
                        }
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="font-semibold text-gray-800 text-sm truncate">${owner.name}</p>
                        <p class="text-xs text-gray-500 truncate">${owner.email}</p>

                        <!-- Role Selection for this member -->
                        <div class="mt-2 space-y-2">
                            <select class="member-role-select w-full text-sm px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-500 focus:border-orange-500"
                                    data-owner-id="${owner.owner_id}"
                                    data-index="${index}">
                                <option value="" disabled ${!owner.role ? 'selected' : ''}>Select Role</option>
                                <option value="manager" ${owner.role === 'manager' ? 'selected' : ''}>Manager</option>
                                <option value="engineer" ${owner.role === 'engineer' ? 'selected' : ''}>Engineer</option>
                                <option value="architect" ${owner.role === 'architect' ? 'selected' : ''}>Architect</option>
                                <option value="others" ${owner.role === 'others' ? 'selected' : ''}>Others</option>
                            </select>

                            <!-- Others input field -->
                            <input type="text"
                                   class="member-role-other-input w-full text-sm px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-500 focus:border-orange-500 ${owner.role === 'others' ? '' : 'hidden'}"
                                   data-owner-id="${owner.owner_id}"
                                   data-index="${index}"
                                   placeholder="Specify role (e.g., Consultant, Surveyor)"
                                   value="${owner.role_other || ''}">

                            <span class="member-role-error text-xs text-red-500 hidden" data-index="${index}"></span>
                        </div>
                    </div>
                </div>
                <button type="button" class="remove-owner-btn text-red-500 hover:text-red-700 hover:bg-red-50 p-2 rounded-lg transition-colors flex-shrink-0"
                        data-owner-id="${owner.owner_id}">
                    <i class="fi fi-rr-cross text-lg"></i>
                </button>
            </div>
        `;
    }).join('');

    // Add remove button listeners
    document.querySelectorAll('.remove-owner-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            removeSelectedOwner(this.dataset.ownerId);
        });
    });

    // Add role select listeners
    document.querySelectorAll('.member-role-select').forEach(select => {
        select.addEventListener('change', function() {
            const ownerId = this.dataset.ownerId;
            const index = parseInt(this.dataset.index);
            const owner = selectedPropertyOwners.find(o => o.owner_id === ownerId);

            if (owner) {
                owner.role = this.value;

                // Show/hide the "others" input field
                const otherInput = document.querySelector(`.member-role-other-input[data-index="${index}"]`);
                if (otherInput) {
                    if (this.value === 'others') {
                        otherInput.classList.remove('hidden');
                    } else {
                        otherInput.classList.add('hidden');
                        owner.role_other = '';
                        otherInput.value = '';
                    }
                }
            }
        });
    });

    // Add role other input listeners
    document.querySelectorAll('.member-role-other-input').forEach(input => {
        input.addEventListener('input', function() {
            const ownerId = this.dataset.ownerId;
            const owner = selectedPropertyOwners.find(o => o.owner_id === ownerId);

            if (owner) {
                owner.role_other = this.value.trim();
            }
        });
    });
}

// Event listeners for search
if (teamMemberOwnerSearch) {
    // Show dropdown on focus
    teamMemberOwnerSearch.addEventListener('focus', function() {
        if (this.value.trim().length >= 2 && teamMemberOwnerDropdown) {
            teamMemberOwnerDropdown.classList.remove('hidden');
        }
    });

    // Search on input with debounce
    teamMemberOwnerSearch.addEventListener('input', debounce(function() {
        const searchTerm = this.value.trim();

        if (searchTerm.length >= 2) {
            if (teamMemberOwnerDropdown) teamMemberOwnerDropdown.classList.remove('hidden');
            searchPropertyOwners(searchTerm);
        } else {
            if (teamMemberOwnerDropdown) teamMemberOwnerDropdown.classList.add('hidden');
        }
    }, 300));

    // Hide dropdown when clicking outside
    document.addEventListener('click', function(e) {
        if (teamMemberOwnerSearch && teamMemberOwnerDropdown &&
            !teamMemberOwnerSearch.contains(e.target) &&
            !teamMemberOwnerDropdown.contains(e.target)) {
            teamMemberOwnerDropdown.classList.add('hidden');
        }
    });
}

function showAddTeamMemberErrors(errors) {
    const errorAlert = document.getElementById('addTeamMemberErrorAlert');
    const errorList = document.getElementById('addTeamMemberErrorList');
    
    errorList.innerHTML = '';
    errors.forEach(error => {
        const li = document.createElement('li');
        li.textContent = error;
        errorList.appendChild(li);
    });
    
    errorAlert.classList.remove('hidden');
}

function clearAddTeamMemberErrors() {
    const errorAlert = document.getElementById('addTeamMemberErrorAlert');
    errorAlert.classList.add('hidden');
}

function saveAddTeamMember() {
    const contractorId = document.body.dataset.contractorId;
    
    // Clear previous errors
    clearAddTeamMemberErrors();

    // Validate that at least one property owner is selected
    if (selectedPropertyOwners.length === 0) {
        showAddTeamMemberErrors(['Please select at least one property owner to add as team member']);
        return;
    }

    // Validate that all members have roles assigned
    let hasErrors = false;
    selectedPropertyOwners.forEach((owner, index) => {
        const errorSpan = document.querySelector(`.member-role-error[data-index="${index}"]`);
        const roleSelect = document.querySelector(`.member-role-select[data-index="${index}"]`);
        const roleOtherInput = document.querySelector(`.member-role-other-input[data-index="${index}"]`);

        // Clear previous errors
        if (errorSpan) errorSpan.classList.add('hidden');
        if (roleSelect) roleSelect.classList.remove('border-red-500');
        if (roleOtherInput) roleOtherInput.classList.remove('border-red-500');

        if (!owner.role) {
            hasErrors = true;
            if (roleSelect) roleSelect.classList.add('border-red-500');
            if (errorSpan) {
                errorSpan.textContent = 'Please select a role';
                errorSpan.classList.remove('hidden');
            }
        } else if (owner.role === 'others' && !owner.role_other) {
            hasErrors = true;
            if (roleOtherInput) roleOtherInput.classList.add('border-red-500');
            if (errorSpan) {
                errorSpan.textContent = 'Please specify the role';
                errorSpan.classList.remove('hidden');
            }
        }
    });

    if (hasErrors) {
        showAddTeamMemberErrors(['Please assign roles to all selected members']);
        return;
    }

    // Get CSRF token
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');

    // Disable save button
    const saveBtn = document.getElementById('saveTeamMemberBtn');
    if (!saveBtn) return;

    const originalBtnText = saveBtn.innerHTML;
    saveBtn.disabled = true;
    saveBtn.innerHTML = '<i class="fi fi-rr-spinner animate-spin"></i> Adding...';

    // Add each selected property owner as a team member with their individual role
    const addPromises = selectedPropertyOwners.map(owner => {
        const formData = new FormData();
        formData.append('owner_id', owner.owner_id);
        formData.append('role', owner.role);
        if (owner.role === 'others' && owner.role_other) {
            formData.append('role_other', owner.role_other);
        }
        formData.append('contractor_id', contractorId);
        formData.append('_token', csrfToken);

        return fetch('/admin/user-management/contractor/team-member/store', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': csrfToken,
                'Accept': 'application/json'
            },
            body: formData
        })
        .then(response => response.json())
        .then(data => ({
            owner: owner,
            success: data.success,
            message: data.message,
            errors: data.errors
        }));
    });

    // Wait for all additions to complete
    Promise.all(addPromises)
        .then(results => {
            const successCount = results.filter(r => r.success).length;
            const failCount = results.length - successCount;

            if (successCount > 0) {
                showNotification(`${successCount} team member(s) added successfully!`, 'success');
                closeAddTeamMemberModal();
                fetchAndUpdateTeamMembers();
            }

            if (failCount > 0) {
                const failedNames = results
                    .filter(r => !r.success)
                    .map(r => r.owner.name)
                    .join(', ');
                showNotification(`Failed to add: ${failedNames}`, 'error');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showNotification('An error occurred. Please try again.', 'error');
        })
        .finally(() => {
            // Re-enable save button
            saveBtn.disabled = false;
            saveBtn.innerHTML = originalBtnText;
        });
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
// TEAM MEMBERS EVENT DELEGATION
// ============================================
document.addEventListener('click', function(e) {
    // Edit button clicked
    if (e.target.closest('.team-edit-btn')) {
        const btn = e.target.closest('.team-edit-btn');
        const memberId = btn.dataset.memberId;
        if (memberId) {
            openEditTeamMemberModal(memberId);
        }
    }

    // Deactivate button clicked
    if (e.target.closest('.team-deactivate-btn')) {
        const btn = e.target.closest('.team-deactivate-btn');
        const memberId = btn.dataset.memberId;
        const memberName = btn.dataset.memberName;
        if (memberId && memberName) {
            openDeactivateTeamMemberModal(memberId, memberName);
        }
    }

    // Reactivate button clicked
    if (e.target.closest('.team-reactivate-btn')) {
        const btn = e.target.closest('.team-reactivate-btn');
        const memberId = btn.dataset.memberId;
        const memberName = btn.dataset.memberName;
        if (memberId && memberName) {
            openReactivateTeamMemberModal(memberId, memberName);
        }
    }

    // Cancel invitation button clicked
    if (e.target.closest('.team-cancel-invitation-btn')) {
        const btn = e.target.closest('.team-cancel-invitation-btn');
        const memberId = btn.dataset.memberId;
        const memberName = btn.dataset.memberName;
        if (memberId && memberName) {
            openCancelInvitationModal(memberId, memberName);
        }
    }

    // Reapply invitation button clicked
    if (e.target.closest('.team-reapply-invitation-btn')) {
        const btn = e.target.closest('.team-reapply-invitation-btn');
        const memberId = btn.dataset.memberId;
        const memberName = btn.dataset.memberName;
        if (memberId && memberName) {
            openReapplyInvitationModal(memberId, memberName);
        }
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

// Close modals when clicking outside
if (suspendModal) {
    suspendModal.addEventListener('click', function(e) {
        if (e.target === suspendModal) {
            closeSuspendModal();
        }
    });
}

// Prevent modal content click from closing
if (suspendModalContent) {
    suspendModalContent.addEventListener('click', function(e) {
        e.stopPropagation();
    });
}

// Add textarea focus effect
if (suspendReasonTextarea) {
    suspendReasonTextarea.addEventListener('focus', function() {
        this.classList.add('ring-2', 'ring-red-200');
    });

    suspendReasonTextarea.addEventListener('blur', function() {
        this.classList.remove('ring-2', 'ring-red-200');
    });
}

// Close modals on ESC key
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
        phone: memberElement.dataset.memberPhone
    };

    // Enable confirm button
    confirmChangeRepresentativeBtn.disabled = false;
}

function confirmChangeRepresentative() {
    if (!selectedMember) return;

    const contractorId = document.body.dataset.contractorId;

    // Get CSRF token
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');

    // Disable button and show loading state
    const originalBtnText = confirmChangeRepresentativeBtn.innerHTML;
    confirmChangeRepresentativeBtn.disabled = true;
    confirmChangeRepresentativeBtn.innerHTML = '<i class="fi fi-rr-spinner animate-spin"></i> Changing...';

    // Send AJAX request
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

            // Update representative info instantly if elements exist
            const repNameEl = document.querySelector('[data-representative-name]');
            if (repNameEl) {
                repNameEl.textContent = selectedMember.name;
            }

            // Refresh team members table to update roles
            fetchAndUpdateTeamMembers();
        } else {
            // Show error in toast for major errors
            showNotification(data.message || 'Failed to change representative.', 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showNotification('An error occurred. Please try again.', 'error');
    })
    .finally(() => {
        // Re-enable button
        confirmChangeRepresentativeBtn.disabled = false;
        confirmChangeRepresentativeBtn.innerHTML = originalBtnText;
    });
}

// ============================================
// EDIT TEAM MEMBER FUNCTIONALITY
// ============================================

function openEditTeamMemberModal(memberId) {
    // Fetch team member data
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
            const hasActiveRepresentative = data.has_active_representative || false;

            // Store member ID in hidden input
            const contractorUserIdInput = document.getElementById('editTeamMemberContractorUserId');
            if (contractorUserIdInput) {
                contractorUserIdInput.value = member.staff_id || member.contractor_user_id;
            }

            // Populate display-only fields (read-only)
            const firstNameDisplay = document.getElementById('editTeamMemberFirstNameDisplay');
            const middleNameDisplay = document.getElementById('editTeamMemberMiddleNameDisplay');
            const lastNameDisplay = document.getElementById('editTeamMemberLastNameDisplay');
            const emailDisplay = document.getElementById('editTeamMemberEmailDisplay');
            const usernameDisplay = document.getElementById('editTeamMemberUsernameDisplay');

            if (firstNameDisplay) firstNameDisplay.textContent = member.first_name || '-';
            if (middleNameDisplay) middleNameDisplay.textContent = member.middle_name || '-';
            if (lastNameDisplay) lastNameDisplay.textContent = member.last_name || '-';
            if (emailDisplay) emailDisplay.textContent = member.email || '-';
            if (usernameDisplay) usernameDisplay.textContent = member.username || '-';

            // Populate editable role field
            const roleSelect = document.getElementById('editTeamMemberRole');
            if (roleSelect) {
                // Disable/hide representative option if there's already an active representative
                // and this member is not currently the representative
                const representativeOption = roleSelect.querySelector('option[value="representative"]');
                if (representativeOption) {
                    if (hasActiveRepresentative && member.company_role !== 'representative') {
                        // Disable and add note
                        representativeOption.disabled = true;
                        representativeOption.textContent = 'Representative (Already assigned)';
                        representativeOption.style.color = '#9CA3AF'; // gray-400
                    } else {
                        // Enable if no active representative or this member is the current representative
                        representativeOption.disabled = false;
                        representativeOption.textContent = 'Representative';
                        representativeOption.style.color = '';
                    }
                }

                roleSelect.value = member.company_role || member.role || '';
            }

            // Show/hide role_other field based on role
            const roleOtherDiv = document.getElementById('editRoleOtherDiv');
            const roleOtherInput = document.getElementById('editTeamMemberRoleOther');
            if (roleOtherDiv && roleOtherInput) {
                if ((member.company_role === 'others' || member.role === 'others')) {
                    roleOtherDiv.classList.remove('hidden');
                    roleOtherInput.value = member.role_if_others || member.if_others || '';
                } else {
                    roleOtherDiv.classList.add('hidden');
                    roleOtherInput.value = '';
                }
            }

            // Clear any previous error states
            const errorIds = ['editRoleError', 'editRoleOtherError'];
            errorIds.forEach(id => {
                const errorElement = document.getElementById(id);
                if (errorElement) {
                    errorElement.textContent = '';
                    errorElement.classList.add('hidden');
                }
            });

            if (roleSelect) {
                roleSelect.classList.remove('border-red-500');
            }
            if (roleOtherInput) {
                roleOtherInput.classList.remove('border-red-500');
            }

            // Show modal
            const modal = document.getElementById('editTeamMemberModal');
            if (modal) {
                const modalContent = modal.querySelector('.modal-content');
                modal.classList.remove('hidden');
                modal.classList.add('flex');
                setTimeout(() => {
                    if (modalContent) {
                        modalContent.classList.remove('scale-95', 'opacity-0');
                        modalContent.classList.add('scale-100', 'opacity-100');
                    }
                }, 10);
            }
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showNotification('Failed to load team member data', 'error');
    });
}

function saveEditTeamMember() {
    const memberId = document.getElementById('editTeamMemberContractorUserId')?.value;
    const roleSelect = document.getElementById('editTeamMemberRole');
    const roleOtherInput = document.getElementById('editTeamMemberRoleOther');

    if (!memberId) {
        showNotification('Member ID is missing', 'error');
        return;
    }

    const role = roleSelect ? roleSelect.value.trim() : '';
    const roleOther = roleOtherInput ? roleOtherInput.value.trim() : '';

    // Clear previous error states
    const errorIds = ['editRoleError', 'editRoleOtherError'];
    errorIds.forEach(id => {
        const errorElement = document.getElementById(id);
        if (errorElement) {
            errorElement.textContent = '';
            errorElement.classList.add('hidden');
        }
    });

    if (roleSelect) roleSelect.classList.remove('border-red-500');
    if (roleOtherInput) roleOtherInput.classList.remove('border-red-500');

    // Create FormData - only send role data
    const formData = new FormData();
    formData.append('staff_id', memberId);
    formData.append('role', role); // Changed from company_role to role
    if (role === 'others' && roleOther) {
        formData.append('role_other', roleOther); // Changed from role_if_others to role_other
    }

    // Laravel method spoofing for PUT request
    formData.append('_method', 'PUT');

    // Get CSRF token
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
    if (csrfToken) {
        formData.append('_token', csrfToken);
    }

    // Disable save button
    const saveBtn = document.getElementById('saveEditTeamMemberBtn');
    if (!saveBtn) return;

    const originalBtnText = saveBtn.innerHTML;
    saveBtn.disabled = true;
    saveBtn.innerHTML = '<i class="fi fi-rr-spinner animate-spin"></i> Saving...';

    // Send AJAX request
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
            showNotification(data.message || 'Team member role updated successfully!', 'success');
            closeEditTeamMemberModal();

            // Refresh team members table instantly
            fetchAndUpdateTeamMembers();
        } else {
            // Handle validation errors (422 status)
            if (status === 422 && data.errors) {
                if (data.errors.role) {
                    if (roleSelect) roleSelect.classList.add('border-red-500');
                    const errorEl = document.getElementById('editRoleError');
                    if (errorEl) {
                        errorEl.textContent = data.errors.role[0];
                        errorEl.classList.remove('hidden');
                    }
                }
                if (data.errors.role_other) {
                    if (roleOtherInput) roleOtherInput.classList.add('border-red-500');
                    const errorEl = document.getElementById('editRoleOtherError');
                    if (errorEl) {
                        errorEl.textContent = data.errors.role_other[0];
                        errorEl.classList.remove('hidden');
                    }
                }
            } else {
                // Show toast only for major errors (non-validation)
                showNotification(data.message || 'Failed to update team member role.', 'error');
            }
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showNotification('An error occurred. Please try again.', 'error');
    })
    .finally(() => {
        // Re-enable save button
        saveBtn.disabled = false;
        saveBtn.innerHTML = originalBtnText;
    });
}

function closeEditTeamMemberModal() {
    const modal = document.getElementById('editTeamMemberModal');
    if (!modal) return;

    const modalContent = modal.querySelector('.modal-content');
    if (modalContent) {
        modalContent.classList.remove('scale-100', 'opacity-100');
        modalContent.classList.add('scale-95', 'opacity-0');
    }

    setTimeout(() => {
        modal.classList.add('hidden');
        modal.classList.remove('flex');

        // Clear display fields
        const firstNameDisplay = document.getElementById('editTeamMemberFirstNameDisplay');
        const middleNameDisplay = document.getElementById('editTeamMemberMiddleNameDisplay');
        const lastNameDisplay = document.getElementById('editTeamMemberLastNameDisplay');
        const emailDisplay = document.getElementById('editTeamMemberEmailDisplay');
        const usernameDisplay = document.getElementById('editTeamMemberUsernameDisplay');

        if (firstNameDisplay) firstNameDisplay.textContent = '-';
        if (middleNameDisplay) middleNameDisplay.textContent = '-';
        if (lastNameDisplay) lastNameDisplay.textContent = '-';
        if (emailDisplay) emailDisplay.textContent = '-';
        if (usernameDisplay) usernameDisplay.textContent = '-';

        // Clear role fields
        const roleSelect = document.getElementById('editTeamMemberRole');
        const roleOtherInput = document.getElementById('editTeamMemberRoleOther');
        const roleOtherDiv = document.getElementById('editRoleOtherDiv');

        if (roleSelect) roleSelect.value = '';
        if (roleOtherInput) roleOtherInput.value = '';
        if (roleOtherDiv) roleOtherDiv.classList.add('hidden');

        // Clear hidden input
        const contractorUserIdInput = document.getElementById('editTeamMemberContractorUserId');
        if (contractorUserIdInput) contractorUserIdInput.value = '';
    }, 300);
}

// ============================================
// DEACTIVATE TEAM MEMBER FUNCTIONALITY
// ============================================

let currentDeactivateMemberId = null;

function openDeactivateTeamMemberModal(memberId, memberName) {
    currentDeactivateMemberId = memberId;

    // Set member name in modal
    document.getElementById('deactivateTeamMemberName').textContent = memberName;

    // Clear previous reason and error
    const reasonTextarea = document.getElementById('deactivateTeamMemberReason');
    const errorElement = document.getElementById('deactivateReasonError');
    const suspensionDateInput = document.getElementById('suspensionDateTeamMember');
    const suspensionDateError = document.getElementById('suspensionDateErrorTeamMember');

    reasonTextarea.value = '';
    reasonTextarea.classList.remove('border-red-500');
    errorElement.textContent = '';
    errorElement.classList.add('hidden');

    if (suspensionDateInput) {
        suspensionDateInput.value = '';
        suspensionDateInput.classList.remove('border-red-500');
    }
    if (suspensionDateError) {
        suspensionDateError.textContent = '';
        suspensionDateError.classList.add('hidden');
    }

    // Reset radio buttons to temporary
    const radioButtons = document.querySelectorAll('input[name="suspensionDurationTeamMember"]');
    if (radioButtons.length > 0) {
        radioButtons[0].checked = true;
        // Show date picker for temporary
        const dateContainer = document.getElementById('suspensionDateContainerTeamMember');
        if (dateContainer) {
            dateContainer.classList.remove('opacity-0', 'invisible');
            dateContainer.classList.add('opacity-100', 'visible');
        }
    }

    // Show modal
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

        // Clear form
        document.getElementById('deactivateTeamMemberReason').value = '';
        document.getElementById('deactivateReasonError').classList.add('hidden');

        const suspensionDateInput = document.getElementById('suspensionDateTeamMember');
        const suspensionDateError = document.getElementById('suspensionDateErrorTeamMember');
        if (suspensionDateInput) suspensionDateInput.value = '';
        if (suspensionDateError) suspensionDateError.classList.add('hidden');

        // Reset radio buttons
        const radioButtons = document.querySelectorAll('input[name="suspensionDurationTeamMember"]');
        if (radioButtons.length > 0) {
            radioButtons[0].checked = true;
        }
    }, 300);
}

function confirmDeactivateTeamMember() {
    const reason = document.getElementById('deactivateTeamMemberReason').value.trim();
    const reasonTextarea = document.getElementById('deactivateTeamMemberReason');
    const errorElement = document.getElementById('deactivateReasonError');
    const selectedDuration = document.querySelector('input[name="suspensionDurationTeamMember"]:checked');
    const duration = selectedDuration ? selectedDuration.value : 'temporary';
    const suspensionDateInput = document.getElementById('suspensionDateTeamMember');
    const suspensionDateError = document.getElementById('suspensionDateErrorTeamMember');
    let suspensionDate = null;
    let hasError = false;

    // Clear previous errors
    reasonTextarea.classList.remove('border-red-500');
    errorElement.classList.add('hidden');
    errorElement.textContent = '';

    if (suspensionDateInput) {
        suspensionDateInput.classList.remove('border-red-500');
    }
    if (suspensionDateError) {
        suspensionDateError.classList.add('hidden');
        suspensionDateError.textContent = '';
    }

    // Validate reason
    if (!reason) {
        reasonTextarea.classList.add('border-red-500');
        errorElement.textContent = 'Suspension reason is required.';
        errorElement.classList.remove('hidden');
        hasError = true;
    } else if (reason.length < 10) {
        reasonTextarea.classList.add('border-red-500');
        errorElement.textContent = 'Reason must be at least 10 characters.';
        errorElement.classList.remove('hidden');
        hasError = true;
    }

    // Validate suspension date for temporary suspension
    if (duration === 'temporary') {
        suspensionDate = suspensionDateInput ? suspensionDateInput.value : '';
        if (!suspensionDate) {
            if (suspensionDateInput) suspensionDateInput.classList.add('border-red-500');
            if (suspensionDateError) {
                suspensionDateError.textContent = 'Please select a suspension date.';
                suspensionDateError.classList.remove('hidden');
            }
            hasError = true;
        }
    }

    if (hasError) return;

    // Create FormData
    const formData = new FormData();
    formData.append('staff_id', currentDeactivateMemberId);
    formData.append('suspension_reason', reason);
    formData.append('duration', duration);
    if (suspensionDate) {
        formData.append('suspension_until', suspensionDate);
    }
    formData.append('_method', 'POST');

    // Get CSRF token
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
    if (csrfToken) {
        formData.append('_token', csrfToken);
    }

    // Disable button
    const confirmBtn = document.getElementById('confirmDeactivateTeamMemberBtn');
    const originalBtnText = confirmBtn.innerHTML;
    confirmBtn.disabled = true;
    confirmBtn.innerHTML = '<i class="fi fi-rr-spinner animate-spin"></i> Suspending...';

    // Send AJAX request
    fetch(`/admin/user-management/contractor/team-member/${currentDeactivateMemberId}/suspend`, {
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
            showNotification(data.message || 'Team member suspended successfully!', 'success');
            closeDeactivateTeamMemberModal();

            // Refresh team members table instantly
            fetchAndUpdateTeamMembers();
        } else {
            // Handle validation errors (422 status)
            if (status === 422 && data.errors) {
                if (data.errors.suspension_reason) {
                    reasonTextarea.classList.add('border-red-500');
                    errorElement.textContent = data.errors.suspension_reason[0];
                    errorElement.classList.remove('hidden');
                }
                if (data.errors.suspension_until && suspensionDateInput && suspensionDateError) {
                    suspensionDateInput.classList.add('border-red-500');
                    suspensionDateError.textContent = data.errors.suspension_until[0];
                    suspensionDateError.classList.remove('hidden');
                }
            } else {
                // Show toast for other errors
                showNotification(data.message || 'Failed to suspend team member.', 'error');
            }
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showNotification('An error occurred. Please try again.', 'error');
    })
    .finally(() => {
        // Re-enable button
        confirmBtn.disabled = false;
        confirmBtn.innerHTML = originalBtnText;
    });
}

// ============================================
// REACTIVATE TEAM MEMBER FUNCTIONALITY
// ============================================

let currentReactivateMemberId = null;

function openReactivateTeamMemberModal(memberId, memberName) {
    currentReactivateMemberId = memberId;

    // Set member name in modal
    document.getElementById('reactivateTeamMemberName').textContent = memberName;

    // Show modal
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
    // Create FormData
    const formData = new FormData();
    formData.append('staff_id', currentReactivateMemberId);
    formData.append('_method', 'PATCH');

    // Get CSRF token
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
    if (csrfToken) {
        formData.append('_token', csrfToken);
    }

    // Disable button
    const confirmBtn = document.getElementById('confirmReactivateTeamMemberBtn');
    const originalBtnText = confirmBtn.innerHTML;
    confirmBtn.disabled = true;
    confirmBtn.innerHTML = '<i class="fi fi-rr-spinner animate-spin"></i> Reactivating...';

    // Send AJAX request
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

            // Refresh team members table instantly
            fetchAndUpdateTeamMembers();
        } else {
            // Show error toast
            showNotification(data.message || 'Failed to reactivate team member.', 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showNotification('An error occurred. Please try again.', 'error');
    })
    .finally(() => {
        // Re-enable button
        confirmBtn.disabled = false;
        confirmBtn.innerHTML = originalBtnText;
    });
}

// ============================================
// CANCEL INVITATION FUNCTIONALITY
// ============================================

const cancelInvitationModal = document.getElementById('cancelInvitationModal');
const closeCancelInvitationBtn = document.getElementById('closeCancelInvitationBtn');
const confirmCancelInvitationBtn = document.getElementById('confirmCancelInvitationBtn');
let currentCancelMemberId = null;

function openCancelInvitationModal(memberId, memberName) {
    currentCancelMemberId = memberId;

    // Set member name in modal
    const memberNameEl = document.getElementById('cancelMemberName');
    if (memberNameEl) memberNameEl.textContent = memberName;

    // Clear previous reason and error
    const reasonTextarea = document.getElementById('cancelInvitationReason');
    const errorElement = document.getElementById('cancelReasonError');

    if (reasonTextarea) {
        reasonTextarea.value = '';
        reasonTextarea.classList.remove('border-red-500');
    }
    if (errorElement) {
        errorElement.textContent = '';
        errorElement.classList.add('hidden');
    }

    // Show modal
    if (cancelInvitationModal) {
        const modalContent = cancelInvitationModal.querySelector('.modal-content');
        cancelInvitationModal.classList.remove('hidden');
        cancelInvitationModal.classList.add('flex');
        setTimeout(() => {
            if (modalContent) {
                modalContent.classList.remove('scale-95', 'opacity-0');
                modalContent.classList.add('scale-100', 'opacity-100');
            }
        }, 10);
    }
}

function closeCancelInvitationModal() {
    if (!cancelInvitationModal) return;

    const modalContent = cancelInvitationModal.querySelector('.modal-content');
    if (modalContent) {
        modalContent.classList.remove('scale-100', 'opacity-100');
        modalContent.classList.add('scale-95', 'opacity-0');
    }

    setTimeout(() => {
        cancelInvitationModal.classList.add('hidden');
        cancelInvitationModal.classList.remove('flex');
        currentCancelMemberId = null;

        // Clear form
        const reasonTextarea = document.getElementById('cancelInvitationReason');
        const errorElement = document.getElementById('cancelReasonError');
        if (reasonTextarea) reasonTextarea.value = '';
        if (errorElement) errorElement.classList.add('hidden');
    }, 300);
}

function confirmCancelInvitation() {
    const reasonTextarea = document.getElementById('cancelInvitationReason');
    const errorElement = document.getElementById('cancelReasonError');
    const reason = reasonTextarea ? reasonTextarea.value.trim() : '';

    // Clear previous error
    if (reasonTextarea) reasonTextarea.classList.remove('border-red-500');
    if (errorElement) errorElement.classList.add('hidden');

    // Client-side validation
    if (!reason) {
        if (reasonTextarea) reasonTextarea.classList.add('border-red-500');
        if (errorElement) {
            errorElement.textContent = 'Cancellation reason is required.';
            errorElement.classList.remove('hidden');
        }
        return;
    }

    if (reason.length < 10) {
        if (reasonTextarea) reasonTextarea.classList.add('border-red-500');
        if (errorElement) {
            errorElement.textContent = 'Reason must be at least 10 characters.';
            errorElement.classList.remove('hidden');
        }
        return;
    }

    // Get CSRF token
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');

    // Disable button
    const confirmBtn = confirmCancelInvitationBtn;
    if (!confirmBtn) return;

    const originalBtnText = confirmBtn.innerHTML;
    confirmBtn.disabled = true;
    confirmBtn.innerHTML = '<i class="fi fi-rr-spinner animate-spin"></i> Canceling...';

    // Send AJAX request
    fetch(`/admin/user-management/contractor/team-member/${currentCancelMemberId}/cancel-invitation`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': csrfToken,
            'Accept': 'application/json'
        },
        body: JSON.stringify({
            reason: reason
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showNotification(data.message || 'Invitation canceled successfully!', 'success');
            closeCancelInvitationModal();
            fetchAndUpdateTeamMembers();
        } else {
            showNotification(data.message || 'Failed to cancel invitation.', 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showNotification('An error occurred. Please try again.', 'error');
    })
    .finally(() => {
        // Re-enable button
        confirmBtn.disabled = false;
        confirmBtn.innerHTML = originalBtnText;
    });
}

// ============================================
// REAPPLY INVITATION FUNCTIONALITY
// ============================================

const reapplyInvitationModal = document.getElementById('reapplyInvitationModal');
const closeReapplyInvitationBtn = document.getElementById('closeReapplyInvitationBtn');
const confirmReapplyInvitationBtn = document.getElementById('confirmReapplyInvitationBtn');
let currentReapplyMemberId = null;

function openReapplyInvitationModal(memberId, memberName) {
    currentReapplyMemberId = memberId;

    // Set member name in modal
    const memberNameEl = document.getElementById('reapplyMemberName');
    if (memberNameEl) memberNameEl.textContent = memberName;

    // Show modal
    if (reapplyInvitationModal) {
        const modalContent = reapplyInvitationModal.querySelector('.modal-content');
        reapplyInvitationModal.classList.remove('hidden');
        reapplyInvitationModal.classList.add('flex');
        setTimeout(() => {
            if (modalContent) {
                modalContent.classList.remove('scale-95', 'opacity-0');
                modalContent.classList.add('scale-100', 'opacity-100');
            }
        }, 10);
    }
}

function closeReapplyInvitationModal() {
    if (!reapplyInvitationModal) return;

    const modalContent = reapplyInvitationModal.querySelector('.modal-content');
    if (modalContent) {
        modalContent.classList.remove('scale-100', 'opacity-100');
        modalContent.classList.add('scale-95', 'opacity-0');
    }

    setTimeout(() => {
        reapplyInvitationModal.classList.add('hidden');
        reapplyInvitationModal.classList.remove('flex');
        currentReapplyMemberId = null;
    }, 300);
}

function confirmReapplyInvitation() {
    // Get CSRF token
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');

    // Disable button
    const confirmBtn = confirmReapplyInvitationBtn;
    if (!confirmBtn) return;

    const originalBtnText = confirmBtn.innerHTML;
    confirmBtn.disabled = true;
    confirmBtn.innerHTML = '<i class="fi fi-rr-spinner animate-spin"></i> Reapplying...';

    // Send AJAX request
    fetch(`/admin/user-management/contractor/team-member/${currentReapplyMemberId}/reapply-invitation`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': csrfToken,
            'Accept': 'application/json'
        },
        body: JSON.stringify({})
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showNotification(data.message || 'Invitation reapplied successfully!', 'success');
            closeReapplyInvitationModal();
            fetchAndUpdateTeamMembers();
        } else {
            showNotification(data.message || 'Failed to reapply invitation.', 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showNotification('An error occurred. Please try again.', 'error');
    })
    .finally(() => {
        // Re-enable button
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

// Event listeners for Change Representative modal
if (changeRepresentativeBtn) {
    changeRepresentativeBtn.addEventListener('click', function() {
        // Always open the change representative modal (it now has option to add new or select existing)
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

// Delegate click events to team member options
document.addEventListener('click', function(e) {
    const memberOption = e.target.closest('.team-member-option');
    if (memberOption) {
        selectTeamMember(memberOption);
    }
});

// Add New Representative button inside the Change Representative modal
const addNewRepresentativeBtn = document.getElementById('addNewRepresentativeBtn');
if (addNewRepresentativeBtn) {
    addNewRepresentativeBtn.addEventListener('click', function() {
        // Close the change representative modal
        closeChangeRepresentativeModal();
        // Open the add team member modal with representative flag and track it came from change rep modal
        setTimeout(() => {
            openAddTeamMemberModal(true, true);
        }, 300);
    });
}

// Back button in Add Team Member modal to return to Change Representative modal
const backToRepresentativeModalBtn = document.getElementById('backToRepresentativeModalBtn');
if (backToRepresentativeModalBtn) {
    backToRepresentativeModalBtn.addEventListener('click', function() {
        // Close the add team member modal
        closeAddTeamMemberModal();
        // Reopen the change representative modal
        setTimeout(() => {
            openChangeRepresentativeModal();
        }, 300);
    });
}

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

// Initialize team member tabs and pagination on page load
if (document.querySelector('.team-tab')) {
    initTeamMemberTabs();
    initTeamMembersPaginationButtons();
}

// Team Members Modal Events
if (addTeamMemberBtn) addTeamMemberBtn.addEventListener('click', () => openAddTeamMemberModal(false));
if (closeAddTeamMemberBtn) closeAddTeamMemberBtn.addEventListener('click', closeAddTeamMemberModal);
if (cancelAddTeamMemberBtn) cancelAddTeamMemberBtn.addEventListener('click', closeAddTeamMemberModal);
if (saveTeamMemberBtn) saveTeamMemberBtn.addEventListener('click', saveAddTeamMember);

// Role dropdown change event for "Others" field
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

// Role dropdown change event for Edit modal "Others" field
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

// Event delegation for team member edit buttons
document.addEventListener('click', function(e) {
    const editBtn = e.target.closest('.team-edit-btn');
    if (editBtn) {
        const memberId = editBtn.getAttribute('data-member-id');
        if (memberId) {
            openEditTeamMemberModal(memberId);
        }
    }

    // Event delegation for team member deactivate buttons
    const deactivateBtn = e.target.closest('.team-deactivate-btn');
    if (deactivateBtn) {
        const memberId = deactivateBtn.getAttribute('data-member-id');
        const memberName = deactivateBtn.getAttribute('data-member-name');
        if (memberId && memberName) {
            openDeactivateTeamMemberModal(memberId, memberName);
        }
    }

    // Event delegation for team member reactivate buttons
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

// Radio button listener for suspension duration (team member)
const teamMemberRadioButtons = document.querySelectorAll('input[name="suspensionDurationTeamMember"]');
teamMemberRadioButtons.forEach(radio => {
    radio.addEventListener('change', function() {
        const dateContainer = document.getElementById('suspensionDateContainerTeamMember');
        if (dateContainer) {
            if (this.value === 'temporary') {
                dateContainer.classList.remove('opacity-0', 'invisible');
                dateContainer.classList.add('opacity-100', 'visible');
            } else {
                dateContainer.classList.remove('opacity-100', 'visible');
                dateContainer.classList.add('opacity-0', 'invisible');
            }
        }
    });
});

if (closeReactivateTeamMemberBtn) closeReactivateTeamMemberBtn.addEventListener('click', closeReactivateTeamMemberModal);
if (cancelReactivateTeamMemberBtn) cancelReactivateTeamMemberBtn.addEventListener('click', closeReactivateTeamMemberModal);
if (confirmReactivateTeamMemberBtn) confirmReactivateTeamMemberBtn.addEventListener('click', confirmReactivateTeamMember);

// Cancel Invitation Modal Events
if (closeCancelInvitationBtn) closeCancelInvitationBtn.addEventListener('click', closeCancelInvitationModal);
if (confirmCancelInvitationBtn) confirmCancelInvitationBtn.addEventListener('click', confirmCancelInvitation);

// Reapply Invitation Modal Events
if (closeReapplyInvitationBtn) closeReapplyInvitationBtn.addEventListener('click', closeReapplyInvitationModal);
if (confirmReapplyInvitationBtn) confirmReapplyInvitationBtn.addEventListener('click', confirmReapplyInvitation);

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
        const href = this.getAttribute('href');
        // Only handle hash links that start with # and are valid CSS selectors
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
                // Invalid selector, ignore
                console.warn('Invalid selector:', href);
            }
        }
    });
});

// ============================================
// IMAGE UPLOADS (LOGO & BANNER)
// ============================================

(function() {
    const contractorId = document.body.dataset.contractorId;
    const coverPhotoUpload = document.getElementById('coverPhotoUpload');
    const companyLogoUpload = document.getElementById('companyLogoUpload');
    const companyCoverImg = document.getElementById('companyCoverImg');
    const companyLogoImg = document.getElementById('companyLogoImg');
    const coverPhotoPlaceholder = document.getElementById('coverPhotoPlaceholder');
    const companyLogoIcon = document.getElementById('companyLogoIcon');

    // Upload Confirmation Modal Elements
    const uploadConfirmModal = document.getElementById('uploadConfirmModal');
    const uploadConfirmModalContent = uploadConfirmModal ? uploadConfirmModal.querySelector('.modal-content') : null;
    const uploadConfirmPreview = document.getElementById('uploadConfirmPreview');
    const uploadConfirmMessage = document.getElementById('uploadConfirmMessage');
    const cancelUploadBtn = document.getElementById('cancelUploadBtn');
    const confirmUploadBtn = document.getElementById('confirmUploadBtn');

    let currentUploadFile = null;
    let currentUploadType = null; // 'logo' or 'banner'

    function openUploadConfirmModal(file, type) {
        if (!uploadConfirmModal || !uploadConfirmModalContent) return;

        currentUploadFile = file;
        currentUploadType = type;

        const reader = new FileReader();
        reader.onload = function(e) {
            if (uploadConfirmPreview) uploadConfirmPreview.src = e.target.result;
        };
        reader.readAsDataURL(file);

        if (uploadConfirmMessage) {
            uploadConfirmMessage.textContent = type === 'logo' ? 
                'Are you sure you want to update the company logo?' : 
                'Are you sure you want to update the company banner?';
        }

        uploadConfirmModal.classList.remove('hidden');
        uploadConfirmModal.classList.add('flex');
        setTimeout(() => {
            uploadConfirmModalContent.classList.remove('scale-95', 'opacity-0');
            uploadConfirmModalContent.classList.add('scale-100', 'opacity-100');
        }, 10);
    }

    function closeUploadConfirmModal() {
        if (!uploadConfirmModalContent) return;

        uploadConfirmModalContent.classList.remove('scale-100', 'opacity-100');
        uploadConfirmModalContent.classList.add('scale-95', 'opacity-0');

        setTimeout(() => {
            uploadConfirmModal.classList.add('hidden');
            uploadConfirmModal.classList.remove('flex');
            currentUploadFile = null;
            currentUploadType = null;
            if (companyLogoUpload) companyLogoUpload.value = '';
            if (coverPhotoUpload) coverPhotoUpload.value = '';
        }, 300);
    }

    if (cancelUploadBtn) {
        cancelUploadBtn.addEventListener('click', closeUploadConfirmModal);
    }

    if (companyLogoUpload) {
        companyLogoUpload.addEventListener('change', function(e) {
            const file = e.target.files[0];
            if (file) openUploadConfirmModal(file, 'logo');
        });
    }

    if (coverPhotoUpload) {
        coverPhotoUpload.addEventListener('change', function(e) {
            const file = e.target.files[0];
            if (file) openUploadConfirmModal(file, 'banner');
        });
    }

    if (confirmUploadBtn) {
        confirmUploadBtn.addEventListener('click', function() {
            if (!currentUploadFile || !currentUploadType || !contractorId) return;

            const formData = new FormData();
            let url = '';

            if (currentUploadType === 'logo') {
                formData.append('company_logo', currentUploadFile);
                url = `/admin/user-management/contractors/${contractorId}/update-logo`;
            } else {
                formData.append('company_banner', currentUploadFile);
                url = `/admin/user-management/contractors/${contractorId}/update-banner`;
            }

            confirmUploadBtn.disabled = true;
            const originalBtnText = confirmUploadBtn.innerHTML;
            confirmUploadBtn.innerHTML = '<i class="fi fi-rr-spinner animate-spin shadow-sm mt-1"></i> Uploading...';

            fetch(url, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    'Accept': 'application/json'
                },
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showNotification(data.message, 'success');
                    if (currentUploadType === 'logo') {
                        if (companyLogoImg) {
                            companyLogoImg.src = data.path;
                            companyLogoImg.classList.remove('hidden');
                        }
                        if (companyLogoIcon) companyLogoIcon.classList.add('hidden');
                    } else {
                        if (companyCoverImg) {
                            companyCoverImg.src = data.path;
                            companyCoverImg.classList.remove('hidden');
                        }
                        if (coverPhotoPlaceholder) coverPhotoPlaceholder.classList.add('hidden');
                    }
                    closeUploadConfirmModal();
                } else {
                    showNotification(data.message || 'Upload failed', 'error');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showNotification('An error occurred during upload', 'error');
            })
            .finally(() => {
                confirmUploadBtn.disabled = false;
                confirmUploadBtn.innerHTML = originalBtnText;
            });
        });
    }
})();

