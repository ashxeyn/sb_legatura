// Posting Management JavaScript

document.addEventListener('DOMContentLoaded', function() {
    // Tab Switching
    const tabButtons = document.querySelectorAll('.tab-btn');
    const contractorsTable = document.getElementById('contractorsTable');
    const propertyOwnersTable = document.getElementById('propertyOwnersTable');

    tabButtons.forEach(button => {
        button.addEventListener('click', function() {
            // Remove active class from all tabs
            tabButtons.forEach(btn => {
                btn.classList.remove('active', 'text-orange-600', 'border-orange-600', 'bg-orange-50');
                btn.classList.add('text-gray-600', 'border-transparent');
            });

            // Add active class to clicked tab
            this.classList.add('active', 'text-orange-600', 'border-orange-600', 'bg-orange-50');
            this.classList.remove('text-gray-600', 'border-transparent');

            // Show/hide tables
            const tab = this.getAttribute('data-tab');
            if (tab === 'contractors') {
                contractorsTable.classList.remove('hidden');
                propertyOwnersTable.classList.add('hidden');
            } else {
                contractorsTable.classList.add('hidden');
                propertyOwnersTable.classList.remove('hidden');
            }
        });
    });

    // View Modal
    const viewModal = document.getElementById('viewModal');
    const viewButtons = document.querySelectorAll('.view-btn');
    // View modal interactive elements
    const viewCardCompany = document.getElementById('viewCardCompany');
    const viewCardHandle = document.getElementById('viewCardHandle');
    const viewCardImage = document.getElementById('viewCardImage');
    const viewCardTitle = document.getElementById('viewCardTitle');
    const viewCardTimestamp = document.getElementById('viewCardTimestamp');
    const viewImageWrapper = document.getElementById('viewImageWrapper');
    const viewMoreToggle = document.getElementById('viewMoreToggle');
    const viewMoreContent = document.getElementById('viewMoreContent');
    const copyHandleBtn = document.getElementById('copyHandleBtn');
    const copyHandleTip = document.getElementById('copyHandleTip');
    const downloadImageBtn = document.getElementById('downloadImageBtn');

    viewButtons.forEach(button => {
        button.addEventListener('click', function() {
            const name = this.getAttribute('data-name');
            const date = this.getAttribute('data-date');
            const type = this.getAttribute('data-type');

            // Update modal content
            document.getElementById('modalName').textContent = name;
            document.getElementById('modalDate').textContent = date;
            document.getElementById('modalType').textContent = type;
            document.getElementById('modalAccountType').textContent = type;

            // Generate avatar initials
            const initials = name.split(' ').map(word => word[0]).join('').substring(0, 2);
            document.getElementById('modalAvatar').textContent = initials;

            // Populate social-style preview card
            if (viewCardCompany) viewCardCompany.textContent = name || 'Panda Construction Company';
            const handle = (name || 'Panda Construction Company')
                .toLowerCase()
                .replace(/[^a-z0-9]+/g, '_')
                .replace(/^_+|_+$/g, '');
            if (viewCardHandle) viewCardHandle.textContent = `@${handle || 'pcc_official'}`;
            if (viewCardTitle) viewCardTitle.textContent = 'Modern Two-Storey House Project';
            if (viewCardImage) {
                viewCardImage.src = 'https://images.unsplash.com/photo-1600585154526-990dced4db0d?q=80&w=1600&auto=format&fit=crop';
                viewCardImage.classList.remove('zoomed');
                viewCardImage.classList.add('view-image');
            }
            if (viewImageWrapper) {
                viewImageWrapper.classList.remove('zoomed-cursor');
                viewImageWrapper.classList.add('zoom-cursor');
            }
            if (viewMoreContent) viewMoreContent.classList.add('hidden');
            if (viewMoreToggle) viewMoreToggle.textContent = 'More details...';

            // Timestamp formatting
            const now = new Date();
            const timeStr = now.toLocaleTimeString([], { hour: 'numeric', minute: '2-digit' });
            if (viewCardTimestamp) viewCardTimestamp.textContent = `${timeStr} • ${date}`;

            // Show modal with animation
            viewModal.classList.add('show');
            viewModal.classList.remove('hidden');
        });
    });

    // View modal: toggle more details
    if (viewMoreToggle && viewMoreContent) {
        viewMoreToggle.addEventListener('click', () => {
            const isHidden = viewMoreContent.classList.contains('hidden');
            viewMoreContent.classList.toggle('hidden');
            viewMoreToggle.textContent = isHidden ? 'Hide details' : 'More details...';
        });
    }

    // View modal: image zoom
    if (viewImageWrapper && viewCardImage) {
        viewImageWrapper.addEventListener('click', () => {
            const zoomed = viewCardImage.classList.toggle('zoomed');
            viewImageWrapper.classList.toggle('zoomed-cursor', zoomed);
            viewImageWrapper.classList.toggle('zoom-cursor', !zoomed);
        });
    }

    // View modal: copy handle
    if (copyHandleBtn && viewCardHandle && copyHandleTip) {
        copyHandleBtn.addEventListener('click', async () => {
            try {
                await navigator.clipboard.writeText(viewCardHandle.textContent.trim());
                copyHandleTip.classList.remove('hidden');
                copyHandleTip.classList.add('copy-pulse');
                setTimeout(() => {
                    copyHandleTip.classList.add('hidden');
                    copyHandleTip.classList.remove('copy-pulse');
                }, 1200);
            } catch (e) {
                console.warn('Clipboard not available');
            }
        });
    }

    // View modal: download image
    if (downloadImageBtn && viewCardImage) {
        downloadImageBtn.addEventListener('click', () => {
            const a = document.createElement('a');
            a.href = viewCardImage.src;
            a.download = 'project.jpg';
            document.body.appendChild(a);
            a.click();
            a.remove();
        });
    }

    // Approve Modal
    const approveModal = document.getElementById('approveModal');
    const approveButtons = document.querySelectorAll('.approve-btn');
    let currentApproveName = '';

    approveButtons.forEach(button => {
        button.addEventListener('click', function() {
            const name = this.getAttribute('data-name');
            currentApproveName = name;
            document.getElementById('approveModalName').textContent = name;
            
            // Show modal with animation
            approveModal.classList.add('show');
            approveModal.classList.remove('hidden');
        });
    });

    // Confirm Approve
    const confirmApproveBtn = document.getElementById('confirmApprove');
    confirmApproveBtn.addEventListener('click', function() {
        const buttonText = this.querySelector('span span');
        const checkIcon = this.querySelector('svg:first-child');
        const loadingIcon = this.querySelector('.approve-loading');
        
        // Add loading state
        this.disabled = true;
        this.classList.add('opacity-75', 'cursor-not-allowed');
        buttonText.textContent = 'Processing...';
        checkIcon.classList.add('hidden');
        loadingIcon.classList.remove('hidden');

        // Simulate API call
        setTimeout(() => {
            // Create success notification
            const notification = document.createElement('div');
            notification.className = 'fixed top-4 right-4 bg-green-600 text-white px-6 py-4 rounded-xl shadow-2xl flex items-center gap-3 z-[60] animate-slideInRight';
            notification.innerHTML = `
                <div class="w-8 h-8 bg-white bg-opacity-20 rounded-full flex items-center justify-center">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                    </svg>
                </div>
                <div>
                    <p class="font-semibold">Approved Successfully!</p>
                    <p class="text-sm opacity-90">${currentApproveName}</p>
                </div>
            `;
            document.body.appendChild(notification);
            
            // Remove notification after 4 seconds
            setTimeout(() => {
                notification.style.animation = 'slideOutRight 0.3s ease forwards';
                setTimeout(() => notification.remove(), 300);
            }, 4000);
            
            // Remove loading state
            this.disabled = false;
            this.classList.remove('opacity-75', 'cursor-not-allowed');
            buttonText.textContent = 'Approve Post';
            checkIcon.classList.remove('hidden');
            loadingIcon.classList.add('hidden');
            
            // Close modal with fade out
            approveModal.style.animation = 'fadeOut 0.3s ease forwards';
            setTimeout(() => {
                approveModal.classList.remove('show');
                approveModal.classList.add('hidden');
                approveModal.style.animation = '';
            }, 300);
            
            // Update UI (in real app, refresh table or update row)
            console.log('Approved:', currentApproveName);
        }, 1500);
    });

    // Decline Modal
    const declineModal = document.getElementById('declineModal');
    const declineButtons = document.querySelectorAll('.decline-btn');
    let currentDeclineName = '';

    declineButtons.forEach(button => {
        button.addEventListener('click', function() {
            const name = this.getAttribute('data-name');
            currentDeclineName = name;
            document.getElementById('declineModalName').textContent = name;
            
            // Clear previous reason
            document.getElementById('declineReason').value = '';
            
            // Show modal with animation
            declineModal.classList.add('show');
            declineModal.classList.remove('hidden');
        });
    });

    // Confirm Decline
    const confirmDeclineBtn = document.getElementById('confirmDecline');
    confirmDeclineBtn.addEventListener('click', function() {
        const reason = document.getElementById('declineReason').value.trim();
        
        // Validate reason
        if (!reason) {
            const textarea = document.getElementById('declineReason');
            textarea.classList.add('border-red-500', 'ring-2', 'ring-red-200');
            
            // Show error message
            const errorMsg = document.createElement('p');
            errorMsg.className = 'text-red-500 text-sm mt-2';
            errorMsg.textContent = 'Please provide a reason for declining.';
            errorMsg.id = 'error-message';
            
            // Remove existing error message if any
            const existingError = document.getElementById('error-message');
            if (existingError) existingError.remove();
            
            textarea.parentNode.appendChild(errorMsg);
            
            // Shake animation
            textarea.style.animation = 'shake 0.3s';
            setTimeout(() => {
                textarea.style.animation = '';
            }, 300);
            return;
        }
        
        // Remove error styling
        const textarea = document.getElementById('declineReason');
        textarea.classList.remove('border-red-500', 'ring-2', 'ring-red-200');
        const existingError = document.getElementById('error-message');
        if (existingError) existingError.remove();
        
        // Button inner elements
        const buttonText = this.querySelector('span span');
        const xIcon = this.querySelector('svg:first-child');
        const loadingIcon = this.querySelector('.decline-loading');

        // Add loading state
        this.disabled = true;
        this.classList.add('opacity-75', 'cursor-not-allowed');
        buttonText.textContent = 'Processing...';
        xIcon.classList.add('hidden');
        loadingIcon.classList.remove('hidden');

        // Simulate API call
        setTimeout(() => {
            // Create decline notification
            const notification = document.createElement('div');
            notification.className = 'fixed top-4 right-4 bg-red-600 text-white px-6 py-4 rounded-xl shadow-2xl flex items-center gap-3 z-[60] animate-slideInRight';
            notification.innerHTML = `
                <div class="w-8 h-8 bg-white bg-opacity-20 rounded-full flex items-center justify-center">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </div>
                <div>
                    <p class="font-semibold">Declined</p>
                    <p class="text-sm opacity-90">${currentDeclineName}</p>
                    <p class="text-xs opacity-80 mt-1">Reason: ${reason}</p>
                </div>
            `;
            document.body.appendChild(notification);

            // Remove notification after 4 seconds
            setTimeout(() => {
                notification.style.animation = 'slideOutRight 0.3s ease forwards';
                setTimeout(() => notification.remove(), 300);
            }, 4000);

            // Remove loading state
            this.disabled = false;
            this.classList.remove('opacity-75', 'cursor-not-allowed');
            buttonText.textContent = 'Decline Post';
            xIcon.classList.remove('hidden');
            loadingIcon.classList.add('hidden');

            // Close modal with fade out
            declineModal.style.animation = 'fadeOut 0.3s ease forwards';
            setTimeout(() => {
                declineModal.classList.remove('show');
                declineModal.classList.add('hidden');
                declineModal.style.animation = '';
            }, 300);

            // Update UI (in real app, refresh table or update row)
            console.log('Declined:', currentDeclineName, 'Reason:', reason);
        }, 1500);
    });

    // Close Modal Buttons
    const closeModalButtons = document.querySelectorAll('.close-modal');
    closeModalButtons.forEach(button => {
        button.addEventListener('click', function() {
            // Find parent modal
            const modal = this.closest('[id$="Modal"]');
            modal.classList.remove('show');
            
            // Add fade out animation
            setTimeout(() => {
                modal.classList.add('hidden');
            }, 300);
        });
    });

    // Close modal when clicking outside
    [viewModal, approveModal, declineModal].forEach(modal => {
        modal.addEventListener('click', function(e) {
            if (e.target === modal) {
                modal.classList.remove('show');
                setTimeout(() => {
                    modal.classList.add('hidden');
                }, 300);
            }
        });
    });

    // Filter Functionality
    const dateFilter = document.getElementById('dateFilter');
    const accountTypeFilter = document.getElementById('accountTypeFilter');
    const statusFilter = document.getElementById('statusFilter');
    const resetFiltersBtn = document.getElementById('resetFilters');

    function applyFilters() {
        console.log('Filters Applied:', {
            date: dateFilter.value,
            accountType: accountTypeFilter.value,
            status: statusFilter.value
        });
        // In real app, this would filter the table data
    }

    dateFilter.addEventListener('change', applyFilters);
    accountTypeFilter.addEventListener('change', applyFilters);
    statusFilter.addEventListener('change', applyFilters);

    resetFiltersBtn.addEventListener('click', function() {
        dateFilter.value = '';
        accountTypeFilter.value = '';
        statusFilter.value = '';
        
        // Add rotation animation
        this.style.transform = 'rotate(360deg)';
        this.style.transition = 'transform 0.5s ease';
        
        setTimeout(() => {
            this.style.transform = 'rotate(0deg)';
        }, 500);
        
        console.log('Filters Reset');
    });

    // Add hover effects to table rows
    const tableRows = document.querySelectorAll('tbody tr');
    tableRows.forEach(row => {
        row.addEventListener('mouseenter', function() {
            this.style.transform = 'translateX(4px)';
        });
        
        row.addEventListener('mouseleave', function() {
            this.style.transform = 'translateX(0)';
        });
    });

    // Textarea character counter (optional enhancement)
    const declineReasonTextarea = document.getElementById('declineReason');
    declineReasonTextarea.addEventListener('input', function() {
        // Remove error styling when user starts typing
        this.classList.remove('border-red-500', 'ring-2', 'ring-red-200');
        const existingError = document.getElementById('error-message');
        if (existingError) {
            existingError.remove();
        }
    });
});

// Add shake animation to CSS
const style = document.createElement('style');
style.textContent = `
    @keyframes shake {
        0%, 100% { transform: translateX(0); }
        25% { transform: translateX(-10px); }
        75% { transform: translateX(10px); }
    }
`;
document.head.appendChild(style);
