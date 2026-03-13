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
    // Doc viewer state to avoid showing late errors after the modal is closed
    let _docViewerActive = false;
    let _docViewerObjectUrl = null;

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

    // Document viewer functions (owner/contractor files)
    function openDocViewer(src) {
        console.log('openDocViewer called with src=', src);
        const modal = document.getElementById('docViewerModal');
        const img = document.getElementById('docViewerImg');
        const iframe = document.getElementById('docViewerIframe');
        if (!modal) return;

        // mark viewer active
        _docViewerActive = true;
        // clear any previous object url reference
        _docViewerObjectUrl = null;

        // Normalize and trim src, convert to absolute URL if needed
        src = (src || '').toString().trim();
        if (!src || src === '#') {
            showNotification('No document available', 'error');
            return;
        }

        function toAbsolute(u) {
            if (!u) return u;
            u = u.toString().trim();
            if (u.startsWith('http://') || u.startsWith('https://')) return u;
            if (u.startsWith('//')) return window.location.protocol + u;
            if (u.startsWith('/')) return window.location.origin + u;
            return window.location.origin + '/' + u;
        }

        const abs = toAbsolute(src);
        const lower = abs.split('?')[0].split('.').pop().toLowerCase();
        const imgExts = ['jpg','jpeg','png','gif','webp','bmp'];

        // Reset previous sources
        if (img) {
            img.src = '';
            img.classList.add('hidden');
        }
        if (iframe) {
            iframe.src = '';
            iframe.classList.add('hidden');
        }

        if (imgExts.includes(lower)) {
            if (img) {
                // Try fetching the image first to inspect HTTP status and get blob
                fetch(abs, { method: 'GET' })
                    .then((res) => {
                        console.log('fetch result for', abs, res.status);
                        if (!res.ok) {
                            throw new Error('HTTP ' + res.status);
                        }
                        return res.blob();
                    })
                    .then((blob) => {
                        const objectUrl = URL.createObjectURL(blob);
                        // remember current object url so we can revoke it on close
                        _docViewerObjectUrl = objectUrl;
                        img.onload = function () {
                            console.log('docViewer image loaded (blob):', abs);
                            // revoke after short delay to allow render
                            setTimeout(() => {
                                try { URL.revokeObjectURL(objectUrl); } catch (e) {}
                                if (_docViewerObjectUrl === objectUrl) _docViewerObjectUrl = null;
                            }, 10000);
                        };
                        img.onerror = function (e) {
                            console.error('docViewer image failed to render blob:', abs, e);
                            if (!_docViewerActive) {
                                console.log('Ignored image onerror after viewer closed');
                                return;
                            }
                            showNotification('Failed to load image', 'error');
                        };
                        img.src = objectUrl;
                        img.classList.remove('hidden');
                    })
                    .catch((err) => {
                        console.error('Failed to fetch image:', abs, err);
                        if (!_docViewerActive) {
                            console.log('Ignored fetch error after viewer closed:', err);
                            return;
                        }
                        showNotification('Failed to load image (' + err.message + ')', 'error');
                    });
            }
        } else {
            if (iframe) {
                iframe.onload = function () {
                    console.log('docViewer iframe loaded:', abs);
                };
                iframe.onerror = function () {
                    console.error('docViewer iframe failed to load:', abs);
                    if (!_docViewerActive) {
                        console.log('Ignored iframe error after viewer closed');
                        return;
                    }
                    showNotification('Failed to load document', 'error');
                };
                iframe.src = abs;
                iframe.classList.remove('hidden');
            }
        }

        // Show modal
        modal.classList.remove('hidden');
        modal.classList.add('flex');
        document.body.style.overflow = 'hidden';
    }

    function closeDocViewer() {
        const modal = document.getElementById('docViewerModal');
        const img = document.getElementById('docViewerImg');
        const iframe = document.getElementById('docViewerIframe');
        if (!modal) return;
        modal.classList.add('hidden');
        modal.classList.remove('flex');
        // clear handlers and sources to avoid late callbacks
        if (img) {
            try { img.onload = null; } catch (e) {}
            try { img.onerror = null; } catch (e) {}
            try { img.src = ''; } catch (e) {}
        }
        if (iframe) {
            try { iframe.onload = null; } catch (e) {}
            try { iframe.onerror = null; } catch (e) {}
            try { iframe.src = ''; } catch (e) {}
        }
        // revoke any object URL we created for the viewer
        if (_docViewerObjectUrl) {
            try { URL.revokeObjectURL(_docViewerObjectUrl); } catch (e) {}
            _docViewerObjectUrl = null;
        }
        // mark viewer inactive so late errors are ignored
        _docViewerActive = false;
        document.body.style.overflow = 'auto';
    }

    // Delegate clicks on elements with class 'viewer-link' to open the doc viewer
    document.addEventListener('click', function (e) {
        const trigger = e.target.closest('.viewer-link');
        if (trigger) {
            e.preventDefault();
            const src = trigger.dataset.docSrc || trigger.href || '#';
            console.log('viewer-link clicked:', { id: trigger.id, datasetDocSrc: trigger.dataset.docSrc, href: trigger.href, src });
            openDocViewer(src);
        }
    });

    document.getElementById('docViewerCloseBtn')?.addEventListener('click', closeDocViewer);

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
