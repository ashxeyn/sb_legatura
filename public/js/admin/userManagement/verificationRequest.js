// Simple tab switching
(function () {
    const tabContractors = document.getElementById("tabContractors");
    const tabOwners = document.getElementById("tabOwners");
    const contractorsWrap = document.getElementById("contractorsTableWrap");
    const ownersWrap = document.getElementById("ownersTableWrap");

    function activate(tab) {
        if (tab === "contractors") {
            tabContractors?.classList.add(
                "text-orange-600",
                "border-orange-500"
            );
            tabContractors?.classList.remove(
                "text-gray-600",
                "border-transparent"
            );
            tabOwners?.classList.remove("text-orange-600", "border-orange-500");
            tabOwners?.classList.add("text-gray-600");
            contractorsWrap?.classList.remove("hidden");
            ownersWrap?.classList.add("hidden");
        } else {
            tabOwners?.classList.add("text-orange-600", "border-orange-500");
            tabOwners?.classList.remove("text-gray-600", "border-transparent");
            tabContractors?.classList.remove(
                "text-orange-600",
                "border-orange-500"
            );
            tabContractors?.classList.add("text-gray-600");
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
                el.href = `/storage/${path}`;
                // Update the filename text (second child, index 1, or find span)
                const spans = el.querySelectorAll("span");
                if (spans.length > 0)
                    spans[0].textContent = filename || path.split("/").pop();
                // Update size if available (optional, or hide it)
                if (spans.length > 1) spans[1].textContent = "";

                el.classList.remove("hidden");
                el.classList.remove("pointer-events-none", "opacity-50");
            } else {
                el.href = "#";
                el.classList.add("pointer-events-none", "opacity-50");
                const spans = el.querySelectorAll("span");
                if (spans.length > 0) spans[0].textContent = "Not Uploaded";
            }
        }
    }

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

    // Notification helper (matches propertyOwner style)
    function showNotification(message, type = "success") {
        const notification = document.createElement("div");
        notification.className = `fixed top-24 right-8 z-[60] px-6 py-4 rounded-lg shadow-2xl transform transition-all duration-500 translate-x-full ${
            type === "success" ? "bg-green-500" : "bg-red-500"
        } text-white font-semibold flex items-center gap-3`;
        notification.innerHTML = `
      <i class="fi fi-rr-${
          type === "success" ? "check-circle" : "cross-circle"
      } text-2xl"></i>
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

    // Close Buttons
    document
        .getElementById("vrCloseBtn")
        ?.addEventListener("click", () => toggleModal(contractorModal, false));
    document
        .getElementById("poCloseBtn")
        ?.addEventListener("click", () => toggleModal(ownerModal, false));
    document
        .getElementById("acceptCancelBtn")
        ?.addEventListener("click", () => toggleModal(acceptModal, false));
    document
        .getElementById("rejectCancelBtn")
        ?.addEventListener("click", () => toggleModal(rejectModal, false));

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
                `/api/admin/users/verification-requests/${id}`
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
    }

    function populateOwnerModal(user, profile) {
        if (!profile) {
            console.error("Profile data is missing for property owner");
            // Clear fields or show error
            setText("poFullName", "N/A");
            setText("poContactLine", "N/A");
            return;
        }
        setText("poFullName", `${profile.first_name} ${profile.last_name}`);
        setText(
            "poContactLine",
            `${user.email} • ${profile.phone_number || "N/A"}`
        );
        setText(
            "poInitials",
            setInitials(`${profile.first_name} ${profile.last_name}`)
        );

        setText("poUsername", user.username);
        setText("poEmail", user.email);

        setText(
            "poOccupation",
            profile.occupation || profile.occupation_other || "N/A"
        );
        setText("poDob", profile.birthdate || profile.date_of_birth || "N/A");
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

        setLink("poValidIdPhoto", profile.valid_id_photo, "Front.jpg");
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
        ?.addEventListener("click", async () => {
            if (!currentUserId) return;
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

                    showNotification("Verification approved successfully!", "success");
                } else {
                    console.error('Approve failed', response.status, payload);
                    const errMsg = payload?.message || (payload?.errors ? Object.values(payload.errors).flat().join(' ') : null) || 'Failed to approve user.';
                    showNotification(errMsg, 'error');
                }
            } catch (error) {
                console.error(error);
                showNotification("An error occurred.", "error");
            }
        });

    document
        .getElementById("rejectConfirmBtn")
        ?.addEventListener("click", async () => {
            if (!currentUserId) return;
            const reason = document.getElementById("rejectReasonInput").value.trim();
            const errorEl = document.getElementById("rejectReasonError");

            if (!reason || reason.length < 10) {
                errorEl.textContent = reason.length === 0 ? "Reason is required." : "Reason must be at least 10 characters.";
                errorEl.classList.remove("hidden");
                return;
            }

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
                    document.getElementById("rejectReasonInput").value = "";
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
            }
        });

    // Clear error when user starts typing
    document.getElementById("rejectReasonInput")?.addEventListener("input", () => {
        document.getElementById("rejectReasonError")?.classList.add("hidden");
    });
})();
