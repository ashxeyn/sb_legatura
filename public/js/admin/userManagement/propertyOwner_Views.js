document.addEventListener('DOMContentLoaded', function() {

    const projectFilter = document.getElementById('projectFilter');
    if (projectFilter) {
        projectFilter.addEventListener('change', function() {
            const selectedStatus = this.value;
            const projectCards = document.querySelectorAll('.project-card');
            const projectsList = document.getElementById('projectsList');
            let visibleCount = 0;

            projectCards.forEach(card => {
                const status = card.getAttribute('data-status');
                if (selectedStatus === 'all' || status === selectedStatus) {
                    card.style.display = ''; // Show
                    visibleCount++;
                } else {
                    card.style.display = 'none'; // Hide
                }
            });

            let emptyMessage = projectsList.querySelector('.empty-message');
            if (visibleCount === 0) {
                if (!emptyMessage) {
                    emptyMessage = document.createElement('div');
                    emptyMessage.className = 'text-center py-8 text-gray-500 empty-message';
                    emptyMessage.innerHTML = `
                        <i class="fi fi-rr-box-open text-4xl mb-2 block"></i>
                        <p>No projects found with this status.</p>
                    `;
                    projectsList.appendChild(emptyMessage);
                } else {
                    emptyMessage.style.display = '';
                }
            } else if (emptyMessage) {
                emptyMessage.style.display = 'none';
            }
        });
    }

    const ownerProfileUpload = document.getElementById('ownerProfileUpload');
    const ownerProfileImg = document.getElementById('ownerProfileImg');
    const ownerProfileInitials = document.getElementById('ownerProfileInitials');
    const ownerCoverUpload = document.getElementById('ownerCoverUpload');
    const ownerCoverImg = document.getElementById('ownerCoverImg');
    const ownerCoverPlaceholder = document.getElementById('ownerCoverPlaceholder');

    // Upload Confirmation Modal Elements
    const uploadConfirmModal = document.getElementById('uploadConfirmModal');
    const uploadConfirmModalContent = uploadConfirmModal ? uploadConfirmModal.querySelector('.modal-content') : null;
    const uploadConfirmPreview = document.getElementById('uploadConfirmPreview');
    const uploadConfirmMessage = document.getElementById('uploadConfirmMessage');
    const cancelUploadBtn = document.getElementById('cancelUploadBtn');
    const confirmUploadBtn = document.getElementById('confirmUploadBtn');

    let currentUploadFile = null;
    let currentUploadType = null; // 'profile' or 'cover'

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
            uploadConfirmMessage.textContent = type === 'profile' ? 
                'Are you sure you want to update the profile picture?' : 
                'Are you sure you want to update the cover photo?';
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
            // Reset the file inputs so the change event can fire again for the same file if needed
            if (ownerProfileUpload) ownerProfileUpload.value = '';
            if (ownerCoverUpload) ownerCoverUpload.value = '';
        }, 300);
    }

    if (cancelUploadBtn) {
        cancelUploadBtn.addEventListener('click', closeUploadConfirmModal);
    }

    if (ownerProfileUpload && ownerProfileImg) {
        ownerProfileUpload.addEventListener('change', function(e) {
            const file = e.target.files[0];
            if (file) openUploadConfirmModal(file, 'profile');
        });
    }

    if (ownerCoverUpload && ownerCoverImg) {
        ownerCoverUpload.addEventListener('change', function(e) {
            const file = e.target.files[0];
            if (file) openUploadConfirmModal(file, 'cover');
        });
    }

    if (confirmUploadBtn) {
        confirmUploadBtn.addEventListener('click', function() {
            if (!currentUploadFile || !currentUploadType) return;

            const ownerId = document.body.dataset.ownerId;
            const formData = new FormData();
            
            let url = '';
            if (currentUploadType === 'profile') {
                formData.append('profile_pic', currentUploadFile);
                url = `/admin/user-management/property-owners/${ownerId}/update-profile-pic`;
            } else {
                formData.append('cover_photo', currentUploadFile);
                url = `/admin/user-management/property-owners/${ownerId}/update-cover-photo`;
            }

            confirmUploadBtn.disabled = true;
            const originalBtnText = confirmUploadBtn.innerHTML;
            confirmUploadBtn.innerHTML = '<i class="fi fi-rr-spinner animate-spin mt-1"></i> Uploading...';

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
                    // Update the image on the page
                    if (currentUploadType === 'profile') {
                        if (ownerProfileImg) {
                            ownerProfileImg.src = data.path;
                            ownerProfileImg.classList.remove('hidden');
                        }
                        if (ownerProfileInitials) ownerProfileInitials.classList.add('hidden');
                    } else {
                        if (ownerCoverImg) {
                            ownerCoverImg.src = data.path;
                            ownerCoverImg.classList.remove('hidden');
                        }
                        if (ownerCoverPlaceholder) ownerCoverPlaceholder.classList.add('hidden');
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


    const suspendBtn = document.getElementById('suspendPropertyOwnerBtn');
    const suspendModal = document.getElementById('suspendAccountModal');
    const suspendModalContent = suspendModal ? suspendModal.querySelector('.modal-content') : null;
    const closeSuspendModalBtn = document.getElementById('closeSuspendModalBtn');
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

    if (suspendBtn && suspendModal && suspendModalContent) {
        suspendBtn.addEventListener('click', function() {
            suspendModal.classList.remove('hidden');
            suspendModal.classList.add('flex');
            document.body.style.overflow = 'hidden';

            setTimeout(() => {
                suspendModalContent.classList.remove('scale-95', 'opacity-0');
                suspendModalContent.classList.add('scale-100', 'opacity-100');
            }, 10);
        });
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

    if (closeSuspendModalBtn) {
        closeSuspendModalBtn.addEventListener('click', closeSuspendModal);
    }

    if (cancelSuspendBtn) {
        cancelSuspendBtn.addEventListener('click', closeSuspendModal);
    }

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

    if (confirmSuspendBtn) {
        confirmSuspendBtn.addEventListener('click', function() {
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

            const ownerId = suspendBtn.getAttribute('data-id');

            fetch(`/api/admin/users/property-owners/${ownerId}/suspend`, {
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
                    showNotification('Account suspended successfully!', 'success');
                    closeSuspendModal();
                    setTimeout(() => {
                        window.location.reload();
                    }, 1500);
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
            if (suspendModal && !suspendModal.classList.contains('hidden')) {
                closeSuspendModal();
            }
        }
    });
});

// ============================================
// Universal File Viewer (UFV) - Dark Theme
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


