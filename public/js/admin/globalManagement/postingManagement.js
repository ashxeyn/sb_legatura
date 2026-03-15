// Posting Management JavaScript

function showNotification(message, type = "success") {
    const notification = document.createElement("div");
    notification.className = `fixed top-20 right-4 z-[60] max-w-[280px] px-3 py-2 rounded-md shadow-lg transform transition-all duration-500 translate-x-full ${
        type === "success" ? "bg-green-500" : "bg-red-500"
    } text-white text-xs font-semibold leading-tight flex items-center gap-1.5`;
    notification.innerHTML = `
      <i class="fi fi-rr-${
          type === "success" ? "check-circle" : "cross-circle"
      } text-base"></i>
      <span>${message}</span>
    `;
    document.body.appendChild(notification);

    setTimeout(() => {
        notification.style.transform = "translateX(0)";
    }, 10);

    setTimeout(() => {
        notification.style.transform = "translateX(150%)";
        setTimeout(() => notification.remove(), 500);
    }, 3000);
}

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
            if (approveBtn) {
                approveBtn.setAttribute("data-project-id", projectId);
                approveBtn.setAttribute("data-name", name);
            }
            if (declineBtn) {
                declineBtn.setAttribute("data-project-id", projectId);
                declineBtn.setAttribute("data-name", name);
            }

            // Show modal with animation
            viewModal.classList.add("show");
            viewModal.classList.remove("hidden");

            // Fetch project details from API
            try {
                const csrfToken = document
                    .querySelector('meta[name="csrf-token"]')
                    .getAttribute("content");
                const response = await fetch(
                    `/api/admin/management/postings/${projectId}`,
                    {
                        method: "GET",
                        headers: {
                            "Content-Type": "application/json",
                            "X-CSRF-TOKEN": csrfToken,
                            Accept: "application/json",
                        },
                    }
                );

                if (!response.ok) {
                    throw new Error("Failed to fetch project details");
                }

                const result = await response.json();

                if (result.success && result.data) {
                    const data = result.data;

                    // Populate owner information
                    document.getElementById("modalEmail").textContent =
                        data.owner.email || "N/A";
                    document.getElementById("modalPhone").textContent =
                        data.owner.phone || "N/A";
                    // If owner registered date is provided, show it
                    if (data.owner.registered_at) {
                        try {
                            const regDate = new Date(data.owner.registered_at);
                            document.getElementById("modalDate").textContent =
                                regDate.toLocaleDateString(undefined, {
                                    year: "numeric",
                                    month: "long",
                                    day: "2-digit",
                                });
                        } catch (e) {
                            // fallback to raw value
                            document.getElementById("modalDate").textContent =
                                data.owner.registered_at;
                        }
                    }
                    // Account type
                    document.getElementById("modalAccountType").textContent =
                        data.owner.type || "N/A";

                    // Populate project information
                    const projectTitle =
                        data.project.project_title ||
                        data.project.title ||
                        data.project.name ||
                        "Untitled Post";
                    const titleEl =
                        document.getElementById("modalProjectTitle");
                    if (titleEl) titleEl.textContent = projectTitle;

                    document.getElementById("modalDescription").textContent =
                        data.project.description || "No description available";
                    document.getElementById("modalLocation").textContent =
                        data.project.project_location || "N/A";
                    document.getElementById("modalPropertyType").textContent =
                        data.project.property_type || "N/A";

                    // Format budget range
                    const budgetMin = data.project.budget_range_min
                        ? parseFloat(
                              data.project.budget_range_min
                          ).toLocaleString("en-PH", {
                              style: "currency",
                              currency: "PHP",
                          })
                        : "N/A";
                    const budgetMax = data.project.budget_range_max
                        ? parseFloat(
                              data.project.budget_range_max
                          ).toLocaleString("en-PH", {
                              style: "currency",
                              currency: "PHP",
                          })
                        : "N/A";
                    document.getElementById(
                        "modalBudget"
                    ).textContent = `${budgetMin} - ${budgetMax}`;

                    document.getElementById("modalLotSize").textContent = data
                        .project.lot_size
                        ? `${data.project.lot_size} sqm`
                        : "N/A";
                    document.getElementById("modalFloorArea").textContent = data
                        .project.floor_area
                        ? `${data.project.floor_area} sqm`
                        : "N/A";
                    document.getElementById("modalTimeline").textContent = data
                        .project.to_finish
                        ? `${data.project.to_finish} months`
                        : "N/A";

                    // Format status
                    const statusLabel = {
                        under_review: "Under Review",
                        approved: "Approved",
                        rejected: "Rejected",
                        deleted: "Deleted",
                        due: "Due",
                    };
                    document.getElementById("modalPostStatus").textContent =
                        statusLabel[data.project.status] || data.project.status;

                    // Populate files section
                    const filesContainer =
                        document.getElementById("postFilesContainer");
                    if (data.files && data.files.length > 0) {
                        // Group files by type
                        const filesByType = {};
                        data.files.forEach((file) => {
                            const type = file.file_type || "others";
                            if (!filesByType[type]) {
                                filesByType[type] = [];
                            }
                            filesByType[type].push(file);
                        });

                        // Build HTML for files
                        let filesHTML = "";
                        const typeLabels = {
                            "building permit": "Building Permit",
                            blueprint: "Blueprint",
                            "desired design": "Desired Design",
                            title: "Title",
                            others: "Other Files",
                        };

                        Object.keys(filesByType).forEach((type) => {
                            const files = filesByType[type];
                            const label =
                                typeLabels[type] ||
                                type.charAt(0).toUpperCase() + type.slice(1);

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

                            files.forEach((file) => {
                                const rawPath = file.file_path || "";
                                const fileName =
                                    file.file_name || rawPath.split("/").pop();
                                const fileExtension = (
                                    fileName.split(".").pop() || ""
                                ).toLowerCase();
                                const isImage = [
                                    "jpg",
                                    "jpeg",
                                    "png",
                                    "gif",
                                    "webp",
                                ].includes(fileExtension);

                                // map file_type to storage folder
                                const typeKey = (file.file_type || "others")
                                    .toString()
                                    .toLowerCase()
                                    .trim();
                                const typeMap = {
                                    blueprint: "blueprints",
                                    blueprints: "blueprints",
                                    "building permit": "building_permits",
                                    building_permits: "building_permits",
                                    title: "land_titles",
                                    "land title": "land_titles",
                                    land_titles: "land_titles",
                                    "desired design": "desired_design",
                                    desired_design: "desired_design",
                                    "supporting documents":
                                        "supporting_documents",
                                    supporting_documents:
                                        "supporting_documents",
                                    others: "others",
                                };
                                const folder = typeMap[typeKey] || "others";

                                // ensure the public storage path follows project_files/<folder>/<filename>
                                let filenameOnly = fileName;
                                if (rawPath && rawPath.includes("/"))
                                    filenameOnly = rawPath.split("/").pop();
                                const storedPath = `project_files/${folder}/${filenameOnly}`;
                                const fileUrl = `/storage/${storedPath}`;

                                filesHTML += `
                                    <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg hover:bg-gray-100 transition">
                                        <div class="flex items-center gap-3 flex-1 min-w-0">
                                            ${
                                                isImage
                                                    ? `
                                                <div class="w-12 h-12 rounded bg-gray-200 overflow-hidden flex-shrink-0">
                                                    <img src="${fileUrl}" alt="${fileName}" class="w-full h-full object-cover">
                                                </div>
                                            `
                                                    : `
                                                <div class="w-12 h-12 rounded bg-blue-100 flex items-center justify-center flex-shrink-0">
                                                    <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"></path>
                                                    </svg>
                                                </div>
                                            `
                                            }
                                            <div class="flex-1 min-w-0">
                                                <p class="text-sm font-medium text-gray-900 truncate">${fileName}</p>
                                                <p class="text-xs text-gray-500">${fileExtension.toUpperCase()}</p>
                                            </div>
                                        </div>
                                        <a href="#" class="ml-3 p-2 text-gray-600 hover:bg-gray-50 rounded-lg transition flex-shrink-0 open-doc-btn" data-doc-src="${fileUrl}" data-doc-title="${fileName}">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                            </svg>
                                        </a>
                                        <a href="${fileUrl}" target="_blank" download class="ml-3 p-2 text-blue-600 hover:bg-blue-50 rounded-lg transition flex-shrink-0">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
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
                    console.error("Invalid response format:", result);
                    showErrorInModal();
                }
            } catch (error) {
                console.error("Error fetching project details:", error);
                showErrorInModal();
            }
        });
    });

    function showErrorInModal() {
        document.getElementById("modalDescription").textContent =
            "Error loading data";
        document.getElementById("modalEmail").textContent =
            "Error loading data";
        document.getElementById("modalPhone").textContent =
            "Error loading data";
        document.getElementById("postFilesContainer").innerHTML = `
            <div class="text-center py-8">
                <svg class="w-16 h-16 text-red-300 mx-auto mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
                <p class="text-sm text-red-500">Failed to load project details</p>
            </div>
        `;
        showNotification("Failed to load project details", "error");
    }

    // Inline file viewer: images and PDFs (callable from generated HTML)
    window.openFileInViewer = function (url, ext, isImage) {
        const viewer = document.getElementById("fileViewer");
        if (!viewer) return window.open(url, "_blank");
        // normalize ext
        const lower = (ext || "").toString().toLowerCase();
        viewer.innerHTML = "";

        // Close button
        const closeBtn = document.createElement("button");
        closeBtn.className =
            "mb-3 px-3 py-1 rounded bg-gray-100 text-sm hover:bg-gray-200";
        closeBtn.textContent = "Close Preview";
        closeBtn.addEventListener("click", () => (viewer.innerHTML = ""));
        viewer.appendChild(closeBtn);

        // Image
        if (isImage || ["jpg", "jpeg", "png", "gif", "webp"].includes(lower)) {
            const imgWrap = document.createElement("div");
            imgWrap.className = "w-full max-h-[60vh] overflow-auto";
            const img = document.createElement("img");
            img.src = url;
            img.alt = "Preview";
            img.className = "w-full h-auto object-contain rounded-lg shadow-sm";
            imgWrap.appendChild(img);
            viewer.appendChild(imgWrap);
            return;
        }

        // PDF
        if (lower === "pdf") {
            const frame = document.createElement("iframe");
            frame.src = url;
            frame.className = "w-full h-[60vh] border rounded-lg";
            frame.setAttribute("aria-label", "PDF preview");
            viewer.appendChild(frame);
            return;
        }

        // Other types: provide download/open link
        const other = document.createElement("div");
        other.className = "p-4 bg-gray-50 rounded-lg border border-gray-200";
        other.innerHTML = `<p class="text-sm text-gray-700 mb-2">Preview not available for this file type.</p><a href="${url}" target="_blank" class="text-blue-600 underline">Download</a>`;
        viewer.appendChild(other);
    };

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
                declTextarea.classList.remove(
                    "border-red-500",
                    "ring-2",
                    "ring-red-200"
                );
            }
            const existingError = document.getElementById("decline-error");
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
    if (typeof window.attachModalListeners === "function") {
        window.attachModalListeners();
    }

    const viewModal = document.getElementById("viewModal");
    const approveModal = document.getElementById("approveModal");
    const declineModal = document.getElementById("declineModal");

    // View Modal Actions (Approve/Decline) - Get data from PHP-populated buttons
    const viewModalApproveBtn = document.getElementById("viewModalApproveBtn");
    const viewModalDeclineBtn = document.getElementById("viewModalDeclineBtn");

    if (viewModalApproveBtn) {
        viewModalApproveBtn.addEventListener("click", function () {
            const projectId =
                this.getAttribute("data-project-id") ||
                (viewModal && viewModal.getAttribute("data-project-id"));
            const name =
                this.getAttribute("data-name") ||
                (viewModal && viewModal.getAttribute("data-current-name"));

            if (!projectId) return;

            // Setup and Show Approve Modal
            approveModal.setAttribute("data-project-id", projectId);
            document.getElementById("approveModalName").textContent = name;

            approveModal.classList.add("show");
            approveModal.classList.remove("hidden");
        });
    }

    if (viewModalDeclineBtn) {
        viewModalDeclineBtn.addEventListener("click", function () {
            const projectId =
                this.getAttribute("data-project-id") ||
                (viewModal && viewModal.getAttribute("data-project-id"));
            const name =
                this.getAttribute("data-name") ||
                (viewModal && viewModal.getAttribute("data-current-name"));

            if (!projectId) return;

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
                    const serverMessage =
                        (result &&
                            result.errors &&
                            result.errors.reason &&
                            result.errors.reason[0]) ||
                        result.message ||
                        "Failed to approve posting";
                    throw new Error(serverMessage);
                }

                if (result.success) {
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

// ============================================
// Universal File Viewer (UFV) - Dark Theme
// ============================================
(function() {
    const modal = document.getElementById('documentViewerModal');
    const iframe = document.getElementById('documentViewerFrame');
    const img = document.getElementById('documentViewerImg');
    const closeBtn = document.getElementById('closeDocumentViewerBtn');

    if (!modal) {
        console.error('UFV: documentViewerModal not found!');
        return;
    }

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
            e.stopPropagation();
            const src = btn.getAttribute('data-doc-src');
            const title = btn.getAttribute('data-doc-title') || 'Document';
            if (src && src !== '#') {
                openDocumentViewer(src, title);
            } else {
                showNotification('No document available', 'error');
            }
        }
    }, true); // Use capture phase

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
