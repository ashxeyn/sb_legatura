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

window.openImageModal = function(src, title) {
    const modal = document.getElementById('imageViewerModal');
    const img = document.getElementById('imageModalPreview');
    const titleEl = document.getElementById('imageModalTitle');

    img.classList.remove('scale-100', 'opacity-100');
    img.classList.add('scale-95', 'opacity-0');

    img.src = src;
    titleEl.textContent = title;
    modal.classList.remove('hidden');
    modal.classList.add('flex');

    setTimeout(() => {
        img.classList.remove('scale-95', 'opacity-0');
        img.classList.add('scale-100', 'opacity-100');
    }, 50);
};

window.closeImageModal = function() {
    const modal = document.getElementById('imageViewerModal');
    const img = document.getElementById('imageModalPreview');

    img.classList.remove('scale-100', 'opacity-100');
    img.classList.add('scale-95', 'opacity-0');

    setTimeout(() => {
        modal.classList.add('hidden');
        modal.classList.remove('flex');
        img.src = ''; // Clear src to stop loading/playing
    }, 300);
};
