// Posting Management JavaScript

// Define attachModalListeners globally so filters.js can call it
window.attachModalListeners = function () {
    // View Modal
    const viewModal = document.getElementById("viewModal");
    const viewButtons = document.querySelectorAll(".view-btn");

    viewButtons.forEach((button) => {
        button.addEventListener("click", async function () {
            const projectId = this.getAttribute("data-id");
            const name = this.getAttribute("data-name");
            const date = this.getAttribute("data-date");
            const type = this.getAttribute("data-type");
            const status = this.getAttribute("data-status");
            const profilePic = this.getAttribute("data-pic");

            // Update basic modal content
            document.getElementById("modalName").textContent = name;
            document.getElementById("modalDate").textContent = date;
            document.getElementById("modalType").textContent = type;
            document.getElementById("modalAccountType").textContent = type;

            // Toggle buttons based on status
            const closeBtn = document.getElementById("viewModalCloseBtn");
            const declineBtn = document.getElementById("viewModalDeclineBtn");
            const approveBtn = document.getElementById("viewModalApproveBtn");

            if (status === "under_review") {
                if (closeBtn) closeBtn.classList.add("hidden");
                if (declineBtn) declineBtn.classList.remove("hidden");
                if (approveBtn) approveBtn.classList.remove("hidden");
            } else {
                if (closeBtn) closeBtn.classList.remove("hidden");
                if (declineBtn) declineBtn.classList.add("hidden");
                if (approveBtn) approveBtn.classList.add("hidden");
            }

            // Generate avatar initials or use profile picture
            const modalAvatar = document.getElementById("modalAvatar");
            if (profilePic) {
                modalAvatar.innerHTML = `<img src="${profilePic}" alt="Profile" class="w-full h-full object-cover rounded-full">`;
            } else {
                const initials = name
                    .split(" ")
                    .map((word) => word[0])
                    .join("")
                    .substring(0, 2);
                modalAvatar.textContent = initials;
            }

            // Store data for Approve/Decline actions
            viewModal.setAttribute("data-current-name", name);
            viewModal.setAttribute("data-project-id", projectId);

            // Show modal with animation
            viewModal.classList.add("show");
            viewModal.classList.remove("hidden");

            // Fetch project details from API
            try {
                const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
                const response = await fetch(`/api/admin/management/postings/${projectId}`, {
                    method: 'GET',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken,
                        'Accept': 'application/json'
                    }
                });

                if (!response.ok) {
                    throw new Error('Failed to fetch project details');
                }

                const result = await response.json();

                if (result.success && result.data) {
                    const data = result.data;

                    // Populate owner information
                    document.getElementById("modalEmail").textContent = data.owner.email || 'N/A';
                    document.getElementById("modalPhone").textContent = data.owner.phone || 'N/A';
                    // If owner registered date is provided, show it
                    if (data.owner.registered_at) {
                        try {
                            const regDate = new Date(data.owner.registered_at);
                            document.getElementById("modalDate").textContent = regDate.toLocaleDateString(undefined, { year: 'numeric', month: 'long', day: '2-digit' });
                        } catch (e) {
                            // fallback to raw value
                            document.getElementById("modalDate").textContent = data.owner.registered_at;
                        }
                    }
                    // Account type
                    document.getElementById("modalAccountType").textContent = data.owner.type || 'N/A';

                    // Populate project information
                    document.getElementById("modalDescription").textContent = data.project.description || 'No description available';
                    document.getElementById("modalLocation").textContent = data.project.project_location || 'N/A';
                    document.getElementById("modalPropertyType").textContent = data.project.property_type || 'N/A';
                    
                    // Format budget range
                    const budgetMin = data.project.budget_range_min ? parseFloat(data.project.budget_range_min).toLocaleString('en-PH', { style: 'currency', currency: 'PHP' }) : 'N/A';
                    const budgetMax = data.project.budget_range_max ? parseFloat(data.project.budget_range_max).toLocaleString('en-PH', { style: 'currency', currency: 'PHP' }) : 'N/A';
                    document.getElementById("modalBudget").textContent = `${budgetMin} - ${budgetMax}`;
                    
                    document.getElementById("modalLotSize").textContent = data.project.lot_size ? `${data.project.lot_size} sqm` : 'N/A';
                    document.getElementById("modalFloorArea").textContent = data.project.floor_area ? `${data.project.floor_area} sqm` : 'N/A';
                    document.getElementById("modalTimeline").textContent = data.project.to_finish ? `${data.project.to_finish} months` : 'N/A';
                    
                    // Format status
                    const statusLabel = {
                        'under_review': 'Under Review',
                        'approved': 'Approved',
                        'rejected': 'Rejected',
                        'deleted': 'Deleted',
                        'due': 'Due'
                    };
                    document.getElementById("modalPostStatus").textContent = statusLabel[data.project.status] || data.project.status;

                    // Populate files section
                    const filesContainer = document.getElementById("postFilesContainer");
                    if (data.files && data.files.length > 0) {
                        // Group files by type
                        const filesByType = {};
                        data.files.forEach(file => {
                            const type = file.file_type || 'others';
                            if (!filesByType[type]) {
                                filesByType[type] = [];
                            }
                            filesByType[type].push(file);
                        });

                        // Build HTML for files
                        let filesHTML = '';
                        const typeLabels = {
                            'building permit': 'Building Permit',
                            'blueprint': 'Blueprint',
                            'desired design': 'Desired Design',
                            'title': 'Title',
                            'others': 'Other Files'
                        };

                        Object.keys(filesByType).forEach(type => {
                            const files = filesByType[type];
                            const label = typeLabels[type] || type.charAt(0).toUpperCase() + type.slice(1);
                            
                            filesHTML += `
                                <div class="mb-4 last:mb-0">
                                    <h6 class="text-sm font-semibold text-gray-700 mb-2 flex items-center gap-2">
                                        <svg class="w-4 h-4 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                        </svg>
                                        ${label} (${files.length})
                                    </h6>
                                    <div class="space-y-2">
                            `;

                            files.forEach(file => {
                                const fileName = file.file_name || file.file_path.split('/').pop();
                                const fileExtension = fileName.split('.').pop().toLowerCase();
                                const isImage = ['jpg', 'jpeg', 'png', 'gif', 'webp'].includes(fileExtension);
                                
                                filesHTML += `
                                    <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg hover:bg-gray-100 transition">
                                        <div class="flex items-center gap-3 flex-1 min-w-0">
                                            ${isImage ? `
                                                <div class="w-12 h-12 rounded bg-gray-200 overflow-hidden flex-shrink-0">
                                                    <img src="/storage/${file.file_path}" alt="${fileName}" class="w-full h-full object-cover">
                                                </div>
                                            ` : `
                                                <div class="w-12 h-12 rounded bg-blue-100 flex items-center justify-center flex-shrink-0">
                                                    <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"></path>
                                                    </svg>
                                                </div>
                                            `}
                                            <div class="flex-1 min-w-0">
                                                <p class="text-sm font-medium text-gray-900 truncate">${fileName}</p>
                                                <p class="text-xs text-gray-500">${fileExtension.toUpperCase()}</p>
                                            </div>
                                        </div>
                                        <a href="/storage/${file.file_path}" target="_blank" download class="ml-3 p-2 text-blue-600 hover:bg-blue-50 rounded-lg transition flex-shrink-0">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path>
                                            </svg>
                                        </a>
                                    </div>
                                `;
                            });

                            filesHTML += `
                                    </div>
                                </div>
                            `;
                        });

                        filesContainer.innerHTML = filesHTML;
                    } else {
                        filesContainer.innerHTML = `
                            <div class="text-center py-8">
                                <svg class="w-16 h-16 text-gray-300 mx-auto mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"></path>
                                </svg>
                                <p class="text-sm text-gray-500">No files uploaded for this project</p>
                            </div>
                        `;
                    }
                } else {
                    console.error('Invalid response format:', result);
                    showErrorInModal();
                }
            } catch (error) {
                console.error('Error fetching project details:', error);
                showErrorInModal();
            }
        });
    });

    function showErrorInModal() {
        document.getElementById("modalDescription").textContent = 'Error loading data';
        document.getElementById("modalEmail").textContent = 'Error loading data';
        document.getElementById("modalPhone").textContent = 'Error loading data';
        document.getElementById("postFilesContainer").innerHTML = `
            <div class="text-center py-8">
                <svg class="w-16 h-16 text-red-300 mx-auto mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
                <p class="text-sm text-red-500">Failed to load project details</p>
            </div>
        `;
    }

    // Approve Modal
    const approveModal = document.getElementById("approveModal");
    const approveButtons = document.querySelectorAll(".approve-btn");

    approveButtons.forEach((button) => {
        button.addEventListener("click", function () {
            const name = this.getAttribute("data-name");
            // Store name in a global or data attribute of the modal for the confirm button to use
            approveModal.setAttribute("data-current-name", name);
            document.getElementById("approveModalName").textContent = name;

            // Show modal with animation
            approveModal.classList.add("show");
            approveModal.classList.remove("hidden");
        });
    });

    // Decline Modal
    const declineModal = document.getElementById("declineModal");
    const declineButtons = document.querySelectorAll(".decline-btn");

    declineButtons.forEach((button) => {
        button.addEventListener("click", function () {
            const name = this.getAttribute("data-name");
            // Store name in a global or data attribute of the modal
            declineModal.setAttribute("data-current-name", name);
            document.getElementById("declineModalName").textContent = name;

            // Clear previous reason
            const declTextarea = document.getElementById("declineReason");
            if (declTextarea) {
                declTextarea.value = "";
                declTextarea.classList.remove("border-red-500", "ring-2", "ring-red-200");
            }
            const existingError = document.getElementById('decline-error');
            if (existingError) existingError.remove();

            // Show modal with animation
            declineModal.classList.add("show");
            declineModal.classList.remove("hidden");
        });
    });

    // Add hover effects to table rows
    const tableRows = document.querySelectorAll("tbody tr");
    tableRows.forEach((row) => {
        row.addEventListener("mouseenter", function () {
            this.style.transform = "translateX(4px)";
        });

        row.addEventListener("mouseleave", function () {
            this.style.transform = "translateX(0)";
        });
    });
};

document.addEventListener("DOMContentLoaded", function () {
    // Initial attachment
    window.attachModalListeners();

    // View Modal Actions (Approve/Decline)
    const viewModalApproveBtn = document.getElementById("viewModalApproveBtn");
    const viewModalDeclineBtn = document.getElementById("viewModalDeclineBtn");
    const viewModal = document.getElementById("viewModal");
    const approveModal = document.getElementById("approveModal");
    const declineModal = document.getElementById("declineModal");

    if (viewModalApproveBtn) {
        viewModalApproveBtn.addEventListener("click", function () {
            const name = viewModal.getAttribute("data-current-name");
            const projectId = viewModal.getAttribute("data-project-id");

            // Hide View Modal
            viewModal.classList.remove("show");
            viewModal.classList.add("hidden");

            // Setup and Show Approve Modal
            approveModal.setAttribute("data-current-name", name);
            approveModal.setAttribute("data-project-id", projectId);
            document.getElementById("approveModalName").textContent = name;

            setTimeout(() => {
                approveModal.classList.add("show");
                approveModal.classList.remove("hidden");
            }, 100); // Small delay for smooth transition
        });
    }

    if (viewModalDeclineBtn) {
        viewModalDeclineBtn.addEventListener("click", function () {
            const name = viewModal.getAttribute("data-current-name");
            const projectId = viewModal.getAttribute("data-project-id");

            // Hide View Modal
            viewModal.classList.remove("show");
            viewModal.classList.add("hidden");

            // Setup and Show Decline Modal
            declineModal.setAttribute("data-current-name", name);
            declineModal.setAttribute("data-project-id", projectId);
            document.getElementById("declineModalName").textContent = name;
            document.getElementById("declineReason").value = ""; // Clear reason

            setTimeout(() => {
                declineModal.classList.add("show");
                declineModal.classList.remove("hidden");
            }, 100);
        });
    }

    // Confirm Approve
    const confirmApproveBtn = document.getElementById("confirmApprove");
    if (confirmApproveBtn) {
        confirmApproveBtn.addEventListener("click", async function () {
            const currentApproveName = approveModal.getAttribute("data-current-name");
            const projectId = approveModal.getAttribute("data-project-id");
            const buttonText = this.querySelector("span span");
            const checkIcon = this.querySelector("svg:first-child");
            const loadingIcon = this.querySelector(".approve-loading");

            // Add loading state
            this.disabled = true;
            this.classList.add("opacity-75", "cursor-not-allowed");
            buttonText.textContent = "Processing...";
            checkIcon.classList.add("hidden");
            loadingIcon.classList.remove("hidden");

            try {
                const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
                const response = await fetch(`/api/admin/management/postings/${projectId}/approve`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken,
                        'Accept': 'application/json'
                    }
                });

                const result = await response.json();

                // If server returned a non-OK status (validation error etc.), show message under textarea
                if (!response.ok) {
                    if (result && result.errors && result.errors.reason && result.errors.reason.length) {
                        showDeclineError(result.errors.reason[0]);
                        throw new Error(result.errors.reason[0]);
                    }
                    throw new Error(result.message || 'Failed to reject posting');
                }

                if (result.success) {
                    // Create success notification
                    const notification = document.createElement("div");
                    notification.className =
                        "fixed top-4 right-4 bg-green-600 text-white px-6 py-4 rounded-xl shadow-2xl flex items-center gap-3 z-[60] animate-slideInRight";
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
                        notification.style.animation = "slideOutRight 0.3s ease forwards";
                        setTimeout(() => notification.remove(), 300);
                    }, 4000);

                    // Close modal with fade out
                    approveModal.style.animation = "fadeOut 0.3s ease forwards";
                    setTimeout(() => {
                        approveModal.classList.remove("show");
                        approveModal.classList.add("hidden");
                        approveModal.style.animation = "";
                        
                        // Reload page to refresh table
                        window.location.reload();
                    }, 300);
                } else {
                    throw new Error(result.message || 'Failed to approve posting');
                }
            } catch (error) {
                console.error('Error approving posting:', error);
                
                // Show error notification
                const notification = document.createElement("div");
                notification.className =
                    "fixed top-4 right-4 bg-red-600 text-white px-6 py-4 rounded-xl shadow-2xl flex items-center gap-3 z-[60] animate-slideInRight";
                notification.innerHTML = `
                    <div class="w-8 h-8 bg-white bg-opacity-20 rounded-full flex items-center justify-center">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </div>
                    <div>
                        <p class="font-semibold">Error!</p>
                        <p class="text-sm opacity-90">${error.message}</p>
                    </div>
                `;
                document.body.appendChild(notification);

                setTimeout(() => {
                    notification.style.animation = "slideOutRight 0.3s ease forwards";
                    setTimeout(() => notification.remove(), 300);
                }, 4000);
            } finally {
                // Remove loading state
                this.disabled = false;
                this.classList.remove("opacity-75", "cursor-not-allowed");
                buttonText.textContent = "Approve Post";
                checkIcon.classList.remove("hidden");
                loadingIcon.classList.add("hidden");
            }
        });
    }

    // Confirm Decline
    const confirmDeclineBtn = document.getElementById("confirmDecline");
    if (confirmDeclineBtn) {
        confirmDeclineBtn.addEventListener("click", async function () {
            const currentDeclineName = declineModal.getAttribute("data-current-name");
            const projectId = declineModal.getAttribute("data-project-id");
            const reason = document.getElementById("declineReason").value.trim();

            // Client-side validate reason (required + min length)
            const textarea = document.getElementById("declineReason");
            function showDeclineError(msg) {
                const existing = document.getElementById("decline-error");
                if (existing) existing.remove();
                const errorMsg = document.createElement("p");
                errorMsg.className = "text-red-500 text-sm mt-2";
                errorMsg.textContent = msg;
                errorMsg.id = "decline-error";
                textarea.parentNode.appendChild(errorMsg);
                textarea.classList.add("border-red-500", "ring-2", "ring-red-200");
                textarea.style.animation = "shake 0.3s";
                setTimeout(() => { textarea.style.animation = ""; }, 300);
            }

            if (!reason) {
                showDeclineError("Please provide a reason for declining.");
                return;
            }

            if (reason.length < 10) {
                showDeclineError("Reason must be at least 10 characters.");
                return;
            }

            // Clear previous error state
            const existingError = document.getElementById("decline-error");
            if (existingError) existingError.remove();
            textarea.classList.remove("border-red-500", "ring-2", "ring-red-200");

            // Button inner elements
            const buttonText = this.querySelector("span span");
            const xIcon = this.querySelector("svg:first-child");
            const loadingIcon = this.querySelector(".decline-loading");

            // Add loading state
            this.disabled = true;
            this.classList.add("opacity-75", "cursor-not-allowed");
            buttonText.textContent = "Processing...";
            xIcon.classList.add("hidden");
            loadingIcon.classList.remove("hidden");

            try {
                const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
                const response = await fetch(`/api/admin/management/postings/${projectId}/reject`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken,
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({ reason: reason })
                });

                const result = await response.json();

                if (result.success) {
                    // Create success notification for decline
                    const notification = document.createElement("div");
                    notification.className =
                        "fixed top-4 right-4 bg-green-600 text-white px-6 py-4 rounded-xl shadow-2xl flex items-center gap-3 z-[60] animate-slideInRight";
                    notification.innerHTML = `
                        <div class="w-8 h-8 bg-white bg-opacity-20 rounded-full flex items-center justify-center">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                            </svg>
                        </div>
                        <div>
                            <p class="font-semibold">Declined Successfully!</p>
                            <p class="text-sm opacity-90">${currentDeclineName}</p>
                        </div>
                    `;
                    document.body.appendChild(notification);

                    setTimeout(() => {
                        notification.style.animation = "slideOutRight 0.3s ease forwards";
                        setTimeout(() => notification.remove(), 300);
                    }, 4000);

                    // Close modal with fade out
                    declineModal.style.animation = "fadeOut 0.3s ease forwards";
                    setTimeout(() => {
                        declineModal.classList.remove("show");
                        declineModal.classList.add("hidden");
                        declineModal.style.animation = "";
                        
                        // Reload page to refresh table
                        window.location.reload();
                    }, 300);
                } else {
                    throw new Error(result.message || 'Failed to reject posting');
                }
            } catch (error) {
                console.error('Error rejecting posting:', error);

                // If a validation error is already shown under the textarea, don't show a generic error notification
                if (!document.getElementById('decline-error')) {
                    const notification = document.createElement("div");
                    notification.className =
                        "fixed top-4 right-4 bg-red-600 text-white px-6 py-4 rounded-xl shadow-2xl flex items-center gap-3 z-[60] animate-slideInRight";
                    notification.innerHTML = `
                        <div class="w-8 h-8 bg-white bg-opacity-20 rounded-full flex items-center justify-center">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01"></path>
                            </svg>
                        </div>
                        <div>
                            <p class="font-semibold">Error!</p>
                            <p class="text-sm opacity-90">${error.message}</p>
                        </div>
                    `;
                    document.body.appendChild(notification);

                    setTimeout(() => {
                        notification.style.animation = "slideOutRight 0.3s ease forwards";
                        setTimeout(() => notification.remove(), 300);
                    }, 4000);
                }
            } finally {
                // Remove loading state
                this.disabled = false;
                this.classList.remove("opacity-75", "cursor-not-allowed");
                buttonText.textContent = "Decline Post";
                xIcon.classList.remove("hidden");
                loadingIcon.classList.add("hidden");
            }
        });
    }

    // Close Modal Buttons
    const closeModalButtons = document.querySelectorAll(".close-modal");
    closeModalButtons.forEach((button) => {
        button.addEventListener("click", function () {
            // Find parent modal
            const modal = this.closest('[id$="Modal"]');
            // If closing the decline modal, clear validation error and styling
            if (modal && modal.id === 'declineModal') {
                const declTextarea = document.getElementById('declineReason');
                if (declTextarea) declTextarea.classList.remove('border-red-500', 'ring-2', 'ring-red-200');
                const existingError = document.getElementById('decline-error');
                if (existingError) existingError.remove();
            }

            modal.classList.remove("show");

            // Add fade out animation
            setTimeout(() => {
                modal.classList.add("hidden");
            }, 300);
        });
    });

    // Close modal when clicking outside
    [viewModal, approveModal, declineModal].forEach((modal) => {
        if (modal) {
            modal.addEventListener("click", function (e) {
                if (e.target === modal) {
                    // If closing decline modal by clicking outside, clear validation UI
                    if (modal.id === 'declineModal') {
                        const declTextarea = document.getElementById('declineReason');
                        if (declTextarea) declTextarea.classList.remove('border-red-500', 'ring-2', 'ring-red-200');
                        const existingError = document.getElementById('decline-error');
                        if (existingError) existingError.remove();
                    }

                    modal.classList.remove("show");
                    setTimeout(() => {
                        modal.classList.add("hidden");
                    }, 300);
                }
            });
        }
    });

    // Textarea character counter (optional enhancement)
    const declineReasonTextarea = document.getElementById("declineReason");
    if (declineReasonTextarea) {
        declineReasonTextarea.addEventListener("input", function () {
            // Remove error styling when user starts typing
            this.classList.remove("border-red-500", "ring-2", "ring-red-200");
            const existingError = document.getElementById("decline-error");
            if (existingError) {
                existingError.remove();
            }
        });
    }
});

// Add shake animation to CSS
const style = document.createElement("style");
style.textContent = `
    @keyframes shake {
        0%, 100% { transform: translateX(0); }
        25% { transform: translateX(-10px); }
        75% { transform: translateX(10px); }
    }
`;
document.head.appendChild(style);
