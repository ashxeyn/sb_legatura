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

    // Universal File Viewer (UFV) – same design as Progress Feed
    (function () {
        const ufvModal     = document.getElementById('ufvModal');
        const ufvFileName  = document.getElementById('ufvFileName');
        const ufvCounter   = document.getElementById('ufvCounter');
        const ufvDownload  = document.getElementById('ufvDownload');
        const ufvViewport  = document.getElementById('ufvViewport');
        const ufvFilmstrip = document.getElementById('ufvFilmstrip');
        const ufvPrev      = document.getElementById('ufvPrev');
        const ufvNext      = document.getElementById('ufvNext');
        const ufvClose     = document.getElementById('ufvClose');

        let ufvCurrentFiles = [];
        let ufvCurrentIndex = 0;

        function fileTypeFromName(name) {
            if (!name) return 'other';
            const ext = (name.split('.').pop() || '').toLowerCase();
            const IMG_EXT   = ['jpg','jpeg','png','gif','webp','bmp','svg','heic','ico'];
            const PDF_EXT   = ['pdf'];
            const VIDEO_EXT = ['mp4','webm','mov','avi','mkv','m4v'];
            const AUDIO_EXT = ['mp3','wav','ogg','flac','aac','m4a'];
            if (IMG_EXT.includes(ext))   return 'image';
            if (PDF_EXT.includes(ext))   return 'pdf';
            if (VIDEO_EXT.includes(ext)) return 'video';
            if (AUDIO_EXT.includes(ext)) return 'audio';
            return 'other';
        }

        function resolveUrl(path) {
            if (!path) return '';
            if (path.startsWith('http') || path.startsWith('//') || path.startsWith('/')) return path;
            return '/storage/' + path;
        }

        function renderUFV() {
            if (!ufvCurrentFiles.length) return;
            const f     = ufvCurrentFiles[ufvCurrentIndex];
            const name  = f.original_name || f.file_path || '';
            const url   = resolveUrl(f.file_path);
            const type  = fileTypeFromName(name || url);
            const total = ufvCurrentFiles.length;

            if (ufvFileName) ufvFileName.textContent = name || '';
            if (ufvCounter)  ufvCounter.textContent  = (ufvCurrentIndex + 1) + ' / ' + total;
            if (ufvDownload) { ufvDownload.href = url; ufvDownload.download = name || ''; }

            if (ufvPrev) ufvPrev.style.visibility = total > 1 ? 'visible' : 'hidden';
            if (ufvNext) ufvNext.style.visibility = total > 1 ? 'visible' : 'hidden';

            let html = '';
            if (type === 'image') {
                html = `<img src="${url}" alt="${name || ''}" class="ufv-image" loading="lazy">`;
            } else if (type === 'pdf') {
                html = `<iframe src="${url}" class="ufv-iframe" title="${name || ''}"></iframe>`;
            } else if (type === 'video') {
                html = `<video class="ufv-video" controls><source src="${url}"></video>`;
            } else if (type === 'audio') {
                html = `<div class="ufv-audio-wrap">
                          <i class="fi fi-rr-music ufv-media-icon"></i>
                          <p class="ufv-media-name">${name || ''}</p>
                          <audio controls class="ufv-audio"><source src="${url}"></audio>
                        </div>`;
            } else {
                // For unknown types, just render as an image instead of a download-only fallback
                html = `<img src="${url}" alt="${name || ''}" class="ufv-image" loading="lazy">`;
            }

            if (ufvViewport) ufvViewport.innerHTML = html;

            if (ufvFilmstrip) {
                let filmHtml = '';
                ufvCurrentFiles.forEach((ff, i) => {
                    const fName = ff.original_name || ff.file_path || '';
                    const fType = fileTypeFromName(fName);
                    const fUrl  = resolveUrl(ff.file_path);
                    filmHtml += `<div class="ufv-film-thumb${i === ufvCurrentIndex ? ' ufv-film-active' : ''}" data-ufv-idx="${i}">`;
                    if (fType === 'image') {
                        filmHtml += `<img src="${fUrl}" alt="" loading="lazy">`;
                    } else {
                        const e2 = (fName.split('.').pop() || '').toUpperCase().slice(0,4);
                        filmHtml += `<i class="fi fi-rr-file ufv-film-icon"></i><span class="ufv-film-ext">${e2}</span>`;
                    }
                    filmHtml += '</div>';
                });
                ufvFilmstrip.innerHTML = filmHtml;

                ufvFilmstrip.querySelectorAll('.ufv-film-thumb').forEach((el) => {
                    el.addEventListener('click', function () {
                        ufvCurrentIndex = parseInt(el.dataset.ufvIdx, 10) || 0;
                        renderUFV();
                    });
                });
            }
        }

        function openUFV(files, startIndex) {
            ufvCurrentFiles = Array.isArray(files)
                ? files.map((f) => (typeof f === 'string' ? { file_path: f, original_name: f.split('/').pop() } : f))
                : [{ file_path: files, original_name: (files && files.split ? files.split('/').pop() : '') }];
            ufvCurrentIndex = Math.max(0, Math.min(startIndex || 0, ufvCurrentFiles.length - 1));
            renderUFV();
            if (ufvModal) {
                ufvModal.classList.remove('hidden');
                ufvModal.classList.add('flex');
            }
            document.body.style.overflow = 'hidden';
        }

        function closeUFV() {
            if (ufvModal) {
                ufvModal.classList.add('hidden');
                ufvModal.classList.remove('flex');
            }
            if (ufvViewport)  ufvViewport.innerHTML  = '';
            if (ufvFilmstrip) ufvFilmstrip.innerHTML = '';
            ufvCurrentFiles = [];
            ufvCurrentIndex = 0;
            document.body.style.overflow = 'auto';
        }

        if (ufvPrev) ufvPrev.addEventListener('click', () => {
            if (ufvCurrentFiles.length > 1) {
                ufvCurrentIndex = (ufvCurrentIndex - 1 + ufvCurrentFiles.length) % ufvCurrentFiles.length;
                renderUFV();
            }
        });
        if (ufvNext) ufvNext.addEventListener('click', () => {
            if (ufvCurrentFiles.length > 1) {
                ufvCurrentIndex = (ufvCurrentIndex + 1) % ufvCurrentFiles.length;
                renderUFV();
            }
        });
        if (ufvClose) ufvClose.addEventListener('click', closeUFV);
        if (ufvModal) {
            ufvModal.addEventListener('click', (e) => {
                if (e.target === ufvModal) closeUFV();
            });
        }
        document.addEventListener('keydown', (e) => {
            const isOpen = ufvModal && !ufvModal.classList.contains('hidden');
            if (e.key === 'Escape' && isOpen) { closeUFV(); return; }
            if (!isOpen) return;
            if (e.key === 'ArrowLeft' && ufvCurrentFiles.length > 1) {
                ufvCurrentIndex = (ufvCurrentIndex - 1 + ufvCurrentFiles.length) % ufvCurrentFiles.length;
                renderUFV();
            }
            if (e.key === 'ArrowRight' && ufvCurrentFiles.length > 1) {
                ufvCurrentIndex = (ufvCurrentIndex + 1) % ufvCurrentFiles.length;
                renderUFV();
            }
        });

        // Helper for external callers (e.g., viewer-link)
        window.openImageModal = function (urlOrFiles, titleOrIndex) {
            if (Array.isArray(urlOrFiles)) {
                openUFV(urlOrFiles, typeof titleOrIndex === 'number' ? titleOrIndex : 0);
            } else if (typeof urlOrFiles === 'object' && urlOrFiles !== null) {
                openUFV([urlOrFiles], 0);
            } else {
                const fileObj = {
                    file_path: urlOrFiles,
                    original_name:
                        typeof titleOrIndex === 'string' && titleOrIndex
                            ? titleOrIndex
                            : (urlOrFiles ? urlOrFiles.split('/').pop() : '')
                };
                openUFV([fileObj], 0);
            }
        };

        window.closeImageModal = closeUFV;

        // Delegate clicks on elements with class 'viewer-link' to UFV
        document.addEventListener('click', function (e) {
            const trigger = e.target.closest('.viewer-link');
            if (!trigger) return;
            e.preventDefault();
            const src = trigger.dataset.docSrc || trigger.href || '#';
            if (!src || src === '#') {
                showNotification('No document available', 'error');
                return;
            }
            openImageModal(src, trigger.id || 'Document');
        });
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
            }
        });

    // Clear error when user starts typing
    document.getElementById("rejectReasonInput")?.addEventListener("input", () => {
        document.getElementById("rejectReasonError")?.classList.add("hidden");
    });
})();
