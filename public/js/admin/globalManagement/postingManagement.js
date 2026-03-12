// Posting Management JavaScript - PHP-based modal version

document.addEventListener("DOMContentLoaded", function () {
    const viewModal = document.getElementById("viewModal");
    const approveModal = document.getElementById("approveModal");
    const declineModal = document.getElementById("declineModal");

    // View Modal Actions (Approve/Decline) - Get data from PHP-populated buttons
    const viewModalApproveBtn = document.getElementById("viewModalApproveBtn");
    const viewModalDeclineBtn = document.getElementById("viewModalDeclineBtn");

    if (viewModalApproveBtn) {
        viewModalApproveBtn.addEventListener("click", function () {
            const projectId = this.getAttribute("data-project-id");
            const name = this.getAttribute("data-name");

            // Setup and Show Approve Modal
            approveModal.setAttribute("data-project-id", projectId);
            document.getElementById("approveModalName").textContent = name;

            approveModal.classList.add("show");
            approveModal.classList.remove("hidden");
        });
    }

    if (viewModalDeclineBtn) {
        viewModalDeclineBtn.addEventListener("click", function () {
            const projectId = this.getAttribute("data-project-id");
            const name = this.getAttribute("data-name");

            // Setup and Show Decline Modal
            declineModal.setAttribute("data-project-id", projectId);
            document.getElementById("declineModalName").textContent = name;
            document.getElementById("declineReason").value = ""; // Clear reason

            declineModal.classList.add("show");
            declineModal.classList.remove("hidden");
        });
    }

    // Confirm Approve
    const confirmApproveBtn = document.getElementById("confirmApprove");
    if (confirmApproveBtn) {
        confirmApproveBtn.addEventListener("click", async function () {
            const projectId = approveModal.getAttribute("data-project-id");
            const currentApproveName = document.getElementById("approveModalName").textContent;
            const buttonText = this.querySelector("span span");
            const checkIcon = this.querySelector("svg:first-child");
            const loadingIcon = this.querySelector(".approve-loading");

            // Add loading state
            this.disabled = true;
            this.classList.add("opacity-75", "cursor-not-allowed");
            if (buttonText) buttonText.textContent = "Processing...";
            if (checkIcon) checkIcon.classList.add("hidden");
            if (loadingIcon) loadingIcon.classList.remove("hidden");

            try {
                const csrfToken = document
                    .querySelector('meta[name="csrf-token"]')
                    .getAttribute("content");
                const response = await fetch(
                    `/api/admin/management/postings/${projectId}/approve`,
                    {
                        method: "POST",
                        headers: {
                            "Content-Type": "application/json",
                            "X-CSRF-TOKEN": csrfToken,
                            Accept: "application/json",
                        },
                    }
                );

                const result = await response.json();

                if (!response.ok) {
                    throw new Error(
                        result.message || "Failed to approve posting"
                    );
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
                        notification.style.animation =
                            "slideOutRight 0.3s ease forwards";
                        setTimeout(() => notification.remove(), 300);
                    }, 4000);

                    // Close modal and reload
                    approveModal.style.animation = "fadeOut 0.3s ease forwards";
                    setTimeout(() => {
                        window.location.href = window.location.pathname + window.location.search.replace(/[?&]view=\d+/, '').replace(/^&/, '?');
                    }, 300);
                } else {
                    throw new Error(
                        result.message || "Failed to approve posting"
                    );
                }
            } catch (error) {
                console.error("Error approving posting:", error);

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
                    notification.style.animation =
                        "slideOutRight 0.3s ease forwards";
                    setTimeout(() => notification.remove(), 300);
                }, 4000);
            } finally {
                // Remove loading state
                this.disabled = false;
                this.classList.remove("opacity-75", "cursor-not-allowed");
                if (buttonText) buttonText.textContent = "Approve Post";
                if (checkIcon) checkIcon.classList.remove("hidden");
                if (loadingIcon) loadingIcon.classList.add("hidden");
            }
        });
    }

    // Confirm Decline
    const confirmDeclineBtn = document.getElementById("confirmDecline");
    if (confirmDeclineBtn) {
        confirmDeclineBtn.addEventListener("click", async function () {
            const projectId = declineModal.getAttribute("data-project-id");
            const currentDeclineName = document.getElementById("declineModalName").textContent;
            const reason = document
                .getElementById("declineReason")
                .value.trim();

            // Client-side validate reason
            const textarea = document.getElementById("declineReason");
            function showDeclineError(msg) {
                const existing = document.getElementById("decline-error");
                if (existing) existing.remove();
                const errorMsg = document.createElement("p");
                errorMsg.className = "text-red-500 text-sm mt-2";
                errorMsg.textContent = msg;
                errorMsg.id = "decline-error";
                textarea.parentNode.appendChild(errorMsg);
                textarea.classList.add(
                    "border-red-500",
                    "ring-2",
                    "ring-red-200"
                );
                textarea.style.animation = "shake 0.3s";
                setTimeout(() => {
                    textarea.style.animation = "";
                }, 300);
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
            textarea.classList.remove(
                "border-red-500",
                "ring-2",
                "ring-red-200"
            );

            // Button inner elements
            const buttonText = this.querySelector("span span");
            const loadingIcon = this.querySelector(".decline-loading");

            // Add loading state
            this.disabled = true;
            this.classList.add("opacity-75", "cursor-not-allowed");
            if (buttonText) buttonText.textContent = "Processing...";
            if (loadingIcon) loadingIcon.classList.remove("hidden");

            try {
                const csrfToken = document
                    .querySelector('meta[name="csrf-token"]')
                    .getAttribute("content");
                const response = await fetch(
                    `/api/admin/management/postings/${projectId}/reject`,
                    {
                        method: "POST",
                        headers: {
                            "Content-Type": "application/json",
                            "X-CSRF-TOKEN": csrfToken,
                            Accept: "application/json",
                        },
                        body: JSON.stringify({ reason: reason }),
                    }
                );

                const result = await response.json();

                if (!response.ok) {
                    if (
                        result &&
                        result.errors &&
                        result.errors.reason &&
                        result.errors.reason.length
                    ) {
                        showDeclineError(result.errors.reason[0]);
                        throw new Error(result.errors.reason[0]);
                    }
                    throw new Error(
                        result.message || "Failed to reject posting"
                    );
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
                            <p class="font-semibold">Declined Successfully!</p>
                            <p class="text-sm opacity-90">${currentDeclineName}</p>
                        </div>
                    `;
                    document.body.appendChild(notification);

                    setTimeout(() => {
                        notification.style.animation =
                            "slideOutRight 0.3s ease forwards";
                        setTimeout(() => notification.remove(), 300);
                    }, 4000);

                    // Close modal and reload
                    declineModal.style.animation = "fadeOut 0.3s ease forwards";
                    setTimeout(() => {
                        window.location.href = window.location.pathname + window.location.search.replace(/[?&]view=\d+/, '').replace(/^&/, '?');
                    }, 300);
                } else {
                    throw new Error(
                        result.message || "Failed to reject posting"
                    );
                }
            } catch (error) {
                console.error("Error rejecting posting:", error);

                // Only show notification if no validation error is displayed
                if (!document.getElementById("decline-error")) {
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
                        notification.style.animation =
                            "slideOutRight 0.3s ease forwards";
                        setTimeout(() => notification.remove(), 300);
                    }, 4000);
                }
            } finally {
                // Remove loading state
                this.disabled = false;
                this.classList.remove("opacity-75", "cursor-not-allowed");
                if (buttonText) buttonText.textContent = "Decline Post";
                if (loadingIcon) loadingIcon.classList.add("hidden");
            }
        });
    }

    // Close Modal Buttons
    const closeModalButtons = document.querySelectorAll(".close-modal");
    closeModalButtons.forEach((button) => {
        button.addEventListener("click", function () {
            const modal = this.closest('[id$="Modal"]');
            if (modal && modal.id === "declineModal") {
                const declTextarea = document.getElementById("declineReason");
                if (declTextarea)
                    declTextarea.classList.remove(
                        "border-red-500",
                        "ring-2",
                        "ring-red-200"
                    );
                const existingError = document.getElementById("decline-error");
                if (existingError) existingError.remove();
            }

            modal.classList.remove("show");
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
                    if (modal.id === "declineModal") {
                        const declTextarea =
                            document.getElementById("declineReason");
                        if (declTextarea)
                            declTextarea.classList.remove(
                                "border-red-500",
                                "ring-2",
                                "ring-red-200"
                            );
                        const existingError =
                            document.getElementById("decline-error");
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

    // Textarea error clearing
    const declineReasonTextarea = document.getElementById("declineReason");
    if (declineReasonTextarea) {
        declineReasonTextarea.addEventListener("input", function () {
            this.classList.remove("border-red-500", "ring-2", "ring-red-200");
            const existingError = document.getElementById("decline-error");
            if (existingError) {
                existingError.remove();
            }
        });
    }
});

// Add shake animation
const style = document.createElement("style");
style.textContent = `
    @keyframes shake {
        0%, 100% { transform: translateX(0); }
        25% { transform: translateX(-10px); }
        75% { transform: translateX(10px); }
    }
    @keyframes slideInRight {
        from {
            transform: translateX(100%);
            opacity: 0;
        }
        to {
            transform: translateX(0);
            opacity: 1;
        }
    }
    @keyframes slideOutRight {
        from {
            transform: translateX(0);
            opacity: 1;
        }
        to {
            transform: translateX(100%);
            opacity: 0;
        }
    }
    @keyframes fadeOut {
        from { opacity: 1; }
        to { opacity: 0; }
    }
    .animate-slideInRight {
        animation: slideInRight 0.3s ease forwards;
    }
`;
document.head.appendChild(style);
