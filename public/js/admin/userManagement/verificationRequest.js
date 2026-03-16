// Simple tab switching
(function () {
    const tabContractors = document.getElementById("tabContractors");
    const tabOwners = document.getElementById("tabOwners");
    const contractorsWrap = document.getElementById("contractorsTableWrap");
    const ownersWrap = document.getElementById("ownersTableWrap");

    function activate(tab) {
        if (tab === "contractors") {
            tabContractors?.classList.add("text-gray-700", "border-orange-500");
            tabContractors?.classList.remove("text-gray-600", "border-transparent");
            tabOwners?.classList.remove("text-gray-700", "border-orange-500");
            tabOwners?.classList.add("text-gray-600", "border-transparent");
            contractorsWrap?.classList.remove("hidden");
            ownersWrap?.classList.add("hidden");
        } else {
            tabOwners?.classList.add("text-gray-700", "border-orange-500");
            tabOwners?.classList.remove("text-gray-600", "border-transparent");
            tabContractors?.classList.remove("text-gray-700", "border-orange-500");
            tabContractors?.classList.add("text-gray-600", "border-transparent");
            ownersWrap?.classList.remove("hidden");
            contractorsWrap?.classList.add("hidden");
        }
    }

    tabContractors?.addEventListener("click", () => activate("contractors"));
    tabOwners?.addEventListener("click", () => activate("owners"));
})();

// Verification Modal Logic
(function () {
    let currentUserId = null;
    let currentUserType = null; // 'contractor' or 'property_owner'

    // Helper functions
    function setText(id, value) {
        const el = document.getElementById(id);
        if (el) el.textContent = value || "N/A";
    }

    function setLink(id, path, filename) {
        const el = document.getElementById(id);
        if (el) {
            if (path) {
                const storagePath = `/storage/${path}`;
                el.href = storagePath;
                // also set data-doc-src so viewer picks it first
                el.dataset.docSrc = storagePath;
                // Update the filename text (first span)
                const spans = el.querySelectorAll("span");
                if (spans.length > 0)
                    spans[0].textContent = filename || path.split("/").pop();
                // Update size if available (optional, or hide it)
                if (spans.length > 1) spans[1].textContent = "";

                el.classList.remove("hidden");
                el.classList.remove("pointer-events-none", "opacity-50");
            } else {
                el.href = "#";
                el.dataset.docSrc = "#";
                // Keep element clickable so the viewer can show a 'No document available' notification
                el.classList.remove("pointer-events-none");
                el.classList.add("opacity-50");
                const spans = el.querySelectorAll("span");
                if (spans.length > 0) spans[0].textContent = "Not Uploaded";
            }
        }
    }

    // Universal File Viewer (UFV) - Dark Theme
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
                if (src && src !== '#') {
                    openDocumentViewer(src, title);
                } else {
                    showNotification('No document available', 'error');
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

        // Legacy support for old openImageModal calls
        window.openImageModal = function(src, title) {
            openDocumentViewer(src, title);
        };
        window.closeImageModal = closeDocumentViewer;
    })();

    function setInitials(name) {
        return (name || "")
            .split(/\s+/)
            .slice(0, 2)
            .map((s) => s[0]?.toUpperCase() || "")
            .join("");
    }

    function calculateAge(birthdate) {
        if (!birthdate) return "N/A";
        const dob = new Date(birthdate);
        const diff_ms = Date.now() - dob.getTime();
        const age_dt = new Date(diff_ms);
        return Math.abs(age_dt.getUTCFullYear() - 1970);
    }

    function formatDate(dateString) {
        if (!dateString) return "N/A";
        const date = new Date(dateString);
        if (isNaN(date.getTime())) return "N/A";

        const options = { year: 'numeric', month: 'long', day: '2-digit' };
        return date.toLocaleDateString('en-US', options);
    }

    // Notification helper (matches propertyOwner style)
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

    // Modal Elements
    const contractorModal = document.getElementById(
        "contractorVerificationModal"
    );
    const ownerModal = document.getElementById("ownerVerificationModal");
    const acceptModal = document.getElementById("acceptConfirmModal");
    const rejectModal = document.getElementById("rejectConfirmModal");

    // Reject flow elements & state
    const acceptCancelBtn = document.getElementById("acceptCancelBtn");
    const rejectCancelBtn = document.getElementById("rejectCancelBtn");
    const rejectConfirmBtn = document.getElementById("rejectConfirmBtn");
    const rejectReasonInput = document.getElementById("rejectReasonInput");
    const rejectReasonError = document.getElementById("rejectReasonError");
    let isRejectSubmitting = false;

    function setRejectLoading(loading) {
        if (rejectConfirmBtn) {
            rejectConfirmBtn.disabled = loading;
            rejectConfirmBtn.textContent = loading ? "Rejecting..." : "Confirm Reject";
        }
    }

    // Close Buttons
    document
        .getElementById("vrCloseBtn")
        ?.addEventListener("click", () => toggleModal(contractorModal, false));
    document
        .getElementById("poCloseBtn")
        ?.addEventListener("click", () => toggleModal(ownerModal, false));
    acceptCancelBtn?.addEventListener("click", () => toggleModal(acceptModal, false));
    rejectCancelBtn?.addEventListener("click", () => {
        toggleModal(rejectModal, false);
        const cbx = document.getElementById("tagResubmissionCheckbox");
        if (cbx) cbx.checked = false;
    });

    // Open Modal Logic
    function toggleModal(modal, show) {
        if (!modal) return;
        const panel = modal.querySelector('div[class*="panel"]');
        if (show) {
            modal.classList.remove("hidden");
            modal.classList.add("flex");
            setTimeout(() => {
                panel?.classList.remove("scale-95", "opacity-0");
                panel?.classList.add("scale-100", "opacity-100");
            }, 10);
        } else {
            panel?.classList.remove("scale-100", "opacity-100");
            panel?.classList.add("scale-95", "opacity-0");
            setTimeout(() => {
                modal.classList.add("hidden");
                modal.classList.remove("flex");
            }, 200);
        }
    }

    // Fetch and Populate Data
    async function openVerificationModal(id, type) {
        currentUserId = id;
        currentUserType = type;
        try {
            const response = await fetch(
                `/api/admin/users/verification-requests/${id}?type=${type}`
            );
            if (!response.ok) throw new Error("Failed to fetch details");
            const data = await response.json();
            console.log("Verification Request Data:", data);

            if (!data || !data.user) {
                showNotification("Data not found", "error");
                return;
            }

            const { user, profile } = data;

            if (type === "contractor") {
                if (!profile) {
                    showNotification("Contractor profile data not found", "error");
                    return;
                }
                populateContractorModal(user, profile);
                toggleModal(contractorModal, true);
            } else {
                if (!profile) {
                    showNotification("Owner profile data not found", "error");
                    return;
                }
                populateOwnerModal(user, profile);
                toggleModal(ownerModal, true);
            }
        } catch (error) {
            console.error(error);
            showNotification(
                "Error loading details. Please try again.",
                "error"
            );
        }
    }

    function populateContractorModal(user, profile) {
        setText("vrCompanyName", profile.company_name || user.username || "N/A");
        setText(
            "vrCompanyContact",
            `${user.email || "N/A"} • ${profile.company_phone || "N/A"}`
        );
        setText(
            "vrCompanyInitials",
            setInitials(profile.company_name || user.username || "NA")
        );

        // Owner Details
        const ownerFullName = [
            profile.authorized_rep_fname,
            profile.authorized_rep_mname,
            profile.authorized_rep_lname
        ].filter(Boolean).join(' ') || user.username || "N/A";

        setText("vrOwnerName", ownerFullName);
        setText("vrOwnerAddress", profile.business_address || "N/A");
        setText("vrOwnerInitials", setInitials(ownerFullName));

        setText("vrContractorType", profile.contractor_type || "N/A");
        setText("vrYears", profile.experience_years || "N/A");
        setText("vrServices", profile.services_offered || "N/A");

        setText("vrPcabNo", profile.pcab_license_number || "N/A");
        setText("vrPcabCategory", profile.pcab_category || "N/A");
        setText("vrPcabExp", profile.pcab_validity || "N/A");
        setText("vrBpExp", profile.business_permit_validity || "N/A");
        setText("vrTin", profile.tin_number || "N/A");
        setText("vrBpNo", profile.business_permit_number || "N/A");
        setText("vrBpCity", profile.business_permit_city || "N/A");
        // DTI / SEC registration file
        setLink("vrDtiFile", profile.dti_sec_registration_photo, "DTIRegistration.pdf");
        // Debug: log the DTI path and element dataset to help trace missing files
        try {
            console.log('Contractor DTI file:', profile.dti_sec_registration_photo, 'vrDtiFile.dataset.docSrc=', document.getElementById('vrDtiFile')?.dataset?.docSrc);
        } catch (e) {
            // ignore
        }
    }

    function populateOwnerModal(user, profile) {
        if (!profile) {
            console.error("Profile data is missing for property owner");
            // Clear fields or show error
            setText("poFullName", "N/A");
            setText("poContactLine", "N/A");
            return;
        }
        // first_name, middle_name, last_name come from users table
        const fullName = [user.first_name, user.middle_name, user.last_name].filter(Boolean).join(" ") || user.username || "N/A";
        setText("poFullName", fullName);
        setText(
            "poContactLine",
            `${user.email || "N/A"} • ${profile.phone_number || "N/A"}`
        );

        // handle avatar: show profile_pic if present, otherwise initials
        const avatarImg = document.getElementById("poAvatarImg");
        const initialsEl = document.getElementById("poInitials");
        if (avatarImg && initialsEl) {
            if (profile.profile_pic) {
                avatarImg.src = `/storage/${profile.profile_pic}`;
                avatarImg.classList.remove("hidden");
                initialsEl.classList.add("hidden");
            } else {
                avatarImg.src = "";
                avatarImg.classList.add("hidden");
                initialsEl.classList.remove("hidden");
                setText("poInitials", setInitials(fullName));
            }
        } else {
            setText("poInitials", setInitials(fullName));
        }

        setText("poUsername", user.username);
        setText("poEmail", user.email);

        setText(
            "poOccupation",
            profile.occupation || profile.occupation_other || "N/A"
        );
        setText("poDob", formatDate(profile.birthdate || profile.date_of_birth));
        setText(
            "poAge",
            calculateAge(profile.birthdate || profile.date_of_birth)
        );
        setText(
            "poAddress",
            profile.address ||
                `${profile.city || ""}, ${profile.province || ""}`
        );

        setText("poValidIdType", profile.valid_id_type || "N/A");
        // setText('poValidIdNumber', profile.valid_id_number || 'N/A'); // Removed as per request
        // If front ID is missing, fall back to back photo where appropriate
        const frontPath = profile.valid_id_photo || profile.valid_id_back_photo || null;
        const frontFilename = profile.valid_id_photo ? "Front.jpg" : (profile.valid_id_back_photo ? "Back.jpg" : "Front.jpg");
        setLink("poValidIdPhoto", frontPath, frontFilename);
        setLink("poValidIdBackPhoto", profile.valid_id_back_photo, "Back.jpg");
        setLink(
            "poPoliceClearance",
            profile.police_clearance,
            "Police Clearance"
        );
    }

    // Event Listeners for View Buttons
    document.addEventListener("click", function (e) {
        const btn = e.target.closest(".vr-view-btn, .po-view-btn");
        if (btn) {
            const id = btn.dataset.key;
            const type = btn.classList.contains("vr-view-btn")
                ? "contractor"
                : "property_owner";
            openVerificationModal(id, type);
        }
    });

    // Accept/Reject Logic
    const acceptBtns = [
        document.getElementById("vrAcceptBtn"),
        document.getElementById("poAcceptBtn"),
    ];
    const rejectBtns = [
        document.getElementById("vrRejectBtn"),
        document.getElementById("poRejectBtn"),
    ];

    acceptBtns.forEach((btn) =>
        btn?.addEventListener("click", () => {
            toggleModal(contractorModal, false);
            toggleModal(ownerModal, false);
            toggleModal(acceptModal, true);
        })
    );

    rejectBtns.forEach((btn) =>
        btn?.addEventListener("click", () => {
            toggleModal(contractorModal, false);
            toggleModal(ownerModal, false);
            toggleModal(rejectModal, true);
        })
    );

    // Confirm Actions
    document
        .getElementById("acceptConfirmBtn")
        ?.addEventListener("click", async function() {
            if (!currentUserId) return;
            const btn = this;
            const originalContent = btn.innerHTML;
            btn.disabled = true;
            btn.innerHTML = '<i class="fi fi-rr-spinner animate-spin"></i> Verifying...';
            try {
                const response = await fetch(
                    `/api/admin/users/verification-requests/${currentUserId}/approve`,
                    {
                        method: "POST",
                        headers: {
                            "X-CSRF-TOKEN":
                                document.querySelector(
                                    'meta[name="csrf-token"]'
                                )?.content || "",
                            "Content-Type": "application/json",
                            Accept: "application/json",
                        },
                        body: JSON.stringify({ targetRole: currentUserType }),
                    }
                );

                const payload = await response.json().catch(() => null);

                if (response.ok) {
                    toggleModal(acceptModal, false);
                    toggleModal(contractorModal, false);
                    toggleModal(ownerModal, false);

                    const rowBtn = document.querySelector(
                        `button[data-key="${currentUserId}"]`
                    );
                    if (rowBtn) {
                        const row = rowBtn.closest("tr");
                        if (row) {
                            row.style.transition = "opacity 0.5s";
                            row.style.opacity = "0";
                            setTimeout(() => row.remove(), 500);
                        }
                    }

                    showNotification("Verification approved successfully!", "success");
                } else {
                    console.error('Approve failed', response.status, payload);
                    const errMsg = payload?.message || (payload?.errors ? Object.values(payload.errors).flat().join(' ') : null) || 'Failed to approve user.';
                    showNotification(errMsg, 'error');
                    btn.disabled = false;
                    btn.innerHTML = originalContent;
                }
            } catch (error) {
                console.error(error);
                showNotification("An error occurred.", "error");
                btn.disabled = false;
                btn.innerHTML = originalContent;
            }
        });

    rejectConfirmBtn?.addEventListener("click", async () => {
            if (isRejectSubmitting) return;

            if (!currentUserId) {
                showNotification("No verification request selected.", "error");
                return;
            }
            const rawReason = rejectReasonInput?.value.trim() || "";
            const errorEl = rejectReasonError;

            if (!rawReason || rawReason.length < 10) {
                errorEl.textContent = rawReason.length === 0 ? "Reason is required." : "Reason must be at least 10 characters.";
                errorEl.classList.remove("hidden");
                return;
            }

            const tagResubmission = document.getElementById("tagResubmissionCheckbox")?.checked;
            const reason = tagResubmission ? `RESUBMISSION: ${rawReason}` : rawReason;

            isRejectSubmitting = true;
            setRejectLoading(true);

            try {
                const response = await fetch(
                    `/api/admin/users/verification-requests/${currentUserId}/reject`,
                    {
                        method: "POST",
                        headers: {
                            "X-CSRF-TOKEN":
                                document.querySelector(
                                    'meta[name="csrf-token"]'
                                )?.content || "",
                            "Content-Type": "application/json",
                            Accept: "application/json",
                        },
                        body: JSON.stringify({ reason, targetRole: currentUserType }),
                    }
                );

                if (response.ok) {
                    toggleModal(rejectModal, false);
                    toggleModal(contractorModal, false);
                    toggleModal(ownerModal, false);

                    const btn = document.querySelector(
                        `button[data-key="${currentUserId}"]`
                    );
                    if (btn) {
                        const row = btn.closest("tr");
                        if (row) {
                            row.style.transition = "opacity 0.5s";
                            row.style.opacity = "0";
                            setTimeout(() => row.remove(), 500);
                        }
                    }

                    showNotification("Verification rejected successfully!", "success");
                    if (rejectReasonInput) rejectReasonInput.value = "";
                    const cbx = document.getElementById("tagResubmissionCheckbox");
                    if (cbx) cbx.checked = false;
                    errorEl.classList.add("hidden");
                } else {
                    const err = await response.json();
                    showNotification(
                        err.message || "Failed to reject user.",
                        "error"
                    );
                }
            } catch (error) {
                console.error(error);
                showNotification("An error occurred.", "error");
            } finally {
                isRejectSubmitting = false;
                setRejectLoading(false);
            }
        });

    // Clear error when user starts typing
    document.getElementById("rejectReasonInput")?.addEventListener("input", () => {
        document.getElementById("rejectReasonError")?.classList.add("hidden");
    });
})();
