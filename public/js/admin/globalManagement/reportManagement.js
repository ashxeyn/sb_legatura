(function () {
    "use strict";

    // ── State ──
    let currentReport = null; // { id, source, status, reported_user_id }

    // ── Toast helper ──
    function toast(msg, type) {
        const t = document.createElement("div");
        t.className = `fixed top-6 right-6 z-[9999] px-5 py-3 rounded-xl shadow-lg text-white text-sm font-medium transition-all duration-300 ${
            type === "error" ? "bg-red-600" : "bg-emerald-600"
        }`;
        t.textContent = msg;
        document.body.appendChild(t);
        setTimeout(() => {
            t.style.opacity = "0";
            setTimeout(() => t.remove(), 300);
        }, 3000);
    }

    // ── CSRF headers ──
    function getHeaders() {
        const tokenMeta = document.querySelector('meta[name="csrf-token"]');
        const headers = {
            "X-Requested-With": "XMLHttpRequest",
            "Content-Type": "application/json",
        };
        if (tokenMeta && tokenMeta.content)
            headers["X-CSRF-TOKEN"] = tokenMeta.content;
        return headers;
    }

    // ── Badge helpers ──
    function getStatusBadge(status) {
        const map = {
            pending: "bg-amber-100 text-amber-700",
            under_review: "bg-blue-100 text-blue-700",
            resolved: "bg-emerald-100 text-emerald-700",
            dismissed: "bg-red-100 text-red-700",
        };
        const cls = map[status] || "bg-gray-100 text-gray-700";
        const label = (status || "-").toUpperCase().replace(/_/g, " ");
        return `<span class="inline-flex px-3 py-1 rounded-full text-xs font-semibold ${cls}">${label}</span>`;
    }

    function getSourceBadge(source) {
        const colors = {
            post: "bg-blue-100 text-blue-700",
            review: "bg-purple-100 text-purple-700",
            content: "bg-teal-100 text-teal-700",
            dispute: "bg-orange-100 text-orange-700",
        };
        const labels = { post: "Post", review: "Review", content: "Content", dispute: "Dispute" };
        const cls = colors[source] || "bg-gray-100 text-gray-700";
        return `<span class="inline-flex px-3 py-1 rounded-full text-xs font-semibold ${cls}">${labels[source] || source}</span>`;
    }

    // ── Tab switching ──
    document.querySelectorAll(".tab-btn").forEach((btn) => {
        btn.addEventListener("click", function () {
            document.querySelectorAll(".tab-btn").forEach((b) => b.classList.remove("active"));
            this.classList.add("active");

            document.querySelectorAll(".tab-panel").forEach((p) => p.classList.add("hidden"));
            const tab = this.dataset.tab;
            const panel = document.getElementById("panel" + tab.charAt(0).toUpperCase() + tab.slice(1));
            if (panel) panel.classList.remove("hidden");
        });
    });

    // ══════════════════════════════════════════════════════
    // FETCH & RENDER REPORTS TABLE
    // ══════════════════════════════════════════════════════
    async function fetchReports() {
        const params = new URLSearchParams();
        const source = document.getElementById("filterSource")?.value || "all";
        const status = document.getElementById("filterStatus")?.value || "all";
        const search = document.getElementById("filterSearch")?.value || "";
        const dateFrom = document.getElementById("filterDateFrom")?.value || "";
        const dateTo = document.getElementById("filterDateTo")?.value || "";

        if (source !== "all") params.set("source", source);
        if (status !== "all") params.set("status", status);
        if (search) params.set("search", search);
        if (dateFrom) params.set("date_from", dateFrom);
        if (dateTo) params.set("date_to", dateTo);

        try {
            const res = await fetch(
                `/admin/global-management/report-management/api?${params.toString()}`,
                { headers: { "X-Requested-With": "XMLHttpRequest" } }
            );
            const json = await res.json();
            if (!json.success) return;

            renderReportsTable(json.reports || []);

            if (json.counts) {
                const el = (id, val) => { const e = document.getElementById(id); if (e) e.textContent = val; };
                el("statTotal", json.counts.total);
                el("statPending", json.counts.pending);
                el("statUnderReview", json.counts.under_review);
                el("statResolved", json.counts.resolved);
            }
        } catch (e) {
            console.error(e);
        }
    }

    function renderReportsTable(reports) {
        const tbody = document.getElementById("reportsTableBody");
        if (!tbody) return;

        if (!reports.length) {
            tbody.innerHTML = `<tr><td colspan="8" class="px-6 py-12 text-center text-gray-500 text-sm">No reports found.</td></tr>`;
            return;
        }

        tbody.innerHTML = reports
            .map((r) => {
                const date = r.created_at
                    ? new Date(r.created_at).toLocaleDateString("en-US", { month: "short", day: "numeric", year: "numeric" })
                    : "-";
                return `<tr class="hover:bg-gray-50 transition" data-id="${r.report_id}" data-source="${r.report_source}" data-status="${r.status}">
                    <td class="px-6 py-4 text-sm font-mono text-gray-700">#${r.report_id}</td>
                    <td class="px-6 py-4">${getSourceBadge(r.report_source)}</td>
                    <td class="px-6 py-4 text-sm text-gray-600">${(r.content_type || "-").charAt(0).toUpperCase() + (r.content_type || "-").slice(1)}</td>
                    <td class="px-6 py-4 text-sm text-gray-700 font-medium">${r.reporter_username || "-"}</td>
                    <td class="px-6 py-4 text-sm text-gray-600 max-w-xs truncate" title="${r.reason || ""}">${r.reason || "-"}</td>
                    <td class="px-6 py-4">${getStatusBadge(r.status)}</td>
                    <td class="px-6 py-4 text-sm text-gray-500">${date}</td>
                    <td class="px-6 py-4 text-center">
                        <button class="px-4 py-2 rounded-xl bg-gradient-to-r from-indigo-600 to-purple-600 hover:from-indigo-700 hover:to-purple-700 text-white text-xs font-semibold shadow-sm hover:shadow-md transition view-report-btn" data-id="${r.report_id}" data-source="${r.report_source}">
                            <i class="fi fi-rr-eye mr-1"></i> View
                        </button>
                    </td>
                </tr>`;
            })
            .join("");

        bindViewButtons();
    }

    function bindViewButtons() {
        document.querySelectorAll(".view-report-btn").forEach((btn) => {
            btn.onclick = function () {
                openViewModal(this.dataset.source, this.dataset.id);
            };
        });
    }

    // ══════════════════════════════════════════════════════
    // VIEW REPORT MODAL — load detail with evidence
    // ══════════════════════════════════════════════════════
    async function openViewModal(source, reportId) {
        currentReport = null;

        // Show modal with loading state
        document.getElementById("modalCaseId").textContent = `Report #${reportId}`;
        document.getElementById("modalSource").textContent = "-";
        document.getElementById("modalReporter").textContent = "-";
        document.getElementById("modalReason").textContent = "-";
        document.getElementById("modalContentType").textContent = "-";
        document.getElementById("modalDate").textContent = "-";
        document.getElementById("modalStatus").innerHTML = "";
        document.getElementById("modalDetails").textContent = "-";
        document.getElementById("evidenceContainer").innerHTML =
            '<div class="text-center text-gray-400 text-sm py-4">Loading evidence...</div>';
        document.getElementById("modalAdminNotesWrap").classList.add("hidden");

        const viewModal = document.getElementById("viewReportModal");
        if (viewModal) {
            viewModal.classList.remove("hidden");
            viewModal.classList.add("flex");
        }

        try {
            const res = await fetch(
                `/admin/global-management/report-management/detail/${encodeURIComponent(source)}/${encodeURIComponent(reportId)}`,
                { headers: { "X-Requested-With": "XMLHttpRequest" } }
            );
            const json = await res.json();
            if (!json.success || !json.report) {
                toast("Failed to load report details", "error");
                return;
            }

            const r = json.report;
            currentReport = {
                id: reportId,
                source: source,
                status: r.status,
                reported_user_id: r.reported_user_id,
            };

            // Populate fields
            document.getElementById("modalCaseId").textContent = `Report #${reportId} — ${source.charAt(0).toUpperCase() + source.slice(1)} Report`;
            document.getElementById("modalSource").textContent = source.charAt(0).toUpperCase() + source.slice(1);
            document.getElementById("modalReporter").textContent = r.reporter
                ? `${r.reporter.first_name} ${r.reporter.last_name} (@${r.reporter.username})`
                : "-";
            document.getElementById("modalReason").textContent = r.reason || "-";
            document.getElementById("modalContentType").textContent =
                (r.content_type || "-").charAt(0).toUpperCase() + (r.content_type || "-").slice(1);
            document.getElementById("modalDate").textContent = r.created_at
                ? new Date(r.created_at).toLocaleDateString("en-US", { month: "short", day: "numeric", year: "numeric" })
                : "-";
            document.getElementById("modalStatus").innerHTML = getStatusBadge(r.status);
            document.getElementById("modalDetails").textContent = r.details || "No additional details provided.";

            // Admin notes (for already-resolved/dismissed)
            if (r.admin_notes && (r.status === "resolved" || r.status === "dismissed")) {
                document.getElementById("modalAdminNotes").textContent = r.admin_notes;
                document.getElementById("modalAdminNotesWrap").classList.remove("hidden");
            } else {
                document.getElementById("modalAdminNotesWrap").classList.add("hidden");
            }

            // Evidence
            renderEvidence(r.evidence);

            // Show/hide action buttons based on status
            const actionBtns = document.getElementById("modalActionBtns");
            if (actionBtns) {
                if (r.status === "pending" || r.status === "under_review") {
                    actionBtns.classList.remove("hidden");
                } else {
                    actionBtns.classList.add("hidden");
                }
            }
        } catch (e) {
            console.error(e);
            toast("Error loading report details", "error");
        }
    }

    // ── Render evidence section based on type ──
    function renderEvidence(evidence) {
        const container = document.getElementById("evidenceContainer");
        if (!evidence || !evidence.data) {
            container.innerHTML = '<div class="text-center text-gray-400 text-sm py-4">No evidence available for this report.</div>';
            return;
        }

        const d = evidence.data;
        let html = "";

        if (evidence.type === "review") {
            html = `
                <div class="bg-purple-50 border border-purple-200 rounded-xl p-4">
                    <h5 class="text-sm font-bold text-purple-800 mb-2 flex items-center gap-2">
                        <i class="fi fi-sr-star text-purple-600"></i> Reported Review
                    </h5>
                    <div class="space-y-2">
                        <div class="flex items-center gap-2">
                            <span class="text-xs font-semibold text-gray-500 uppercase">Reviewer:</span>
                            <span class="text-sm font-medium text-gray-800">${d.first_name || ""} ${d.last_name || ""} (@${d.username || ""})</span>
                        </div>
                        <div class="flex items-center gap-2">
                            <span class="text-xs font-semibold text-gray-500 uppercase">Project:</span>
                            <span class="text-sm text-gray-700">${d.project_title || "N/A"}</span>
                        </div>
                        <div class="flex items-center gap-2">
                            <span class="text-xs font-semibold text-gray-500 uppercase">Rating:</span>
                            <span class="text-sm font-bold text-amber-600">${"★".repeat(d.rating || 0)}${"☆".repeat(5 - (d.rating || 0))}</span>
                        </div>
                        <div>
                            <span class="text-xs font-semibold text-gray-500 uppercase block mb-1">Comment:</span>
                            <p class="text-sm text-gray-700 bg-white rounded-lg p-3 border border-gray-200">${escapeHtml(d.comment || "No comment")}</p>
                        </div>
                        ${d.is_deleted ? '<div class="mt-2 text-xs font-semibold text-red-600 bg-red-50 px-3 py-1 rounded-full inline-block">Already deleted</div>' : ""}
                    </div>
                </div>`;
        } else if (evidence.type === "showcase") {
            html = `
                <div class="bg-blue-50 border border-blue-200 rounded-xl p-4">
                    <h5 class="text-sm font-bold text-blue-800 mb-2 flex items-center gap-2">
                        <i class="fi fi-sr-picture text-blue-600"></i> Reported Showcase Post
                    </h5>
                    <div class="space-y-2">
                        <div class="flex items-center gap-2">
                            <span class="text-xs font-semibold text-gray-500 uppercase">Author:</span>
                            <span class="text-sm font-medium text-gray-800">${d.first_name || ""} ${d.last_name || ""} (@${d.username || ""})</span>
                        </div>
                        <div class="flex items-center gap-2">
                            <span class="text-xs font-semibold text-gray-500 uppercase">Title:</span>
                            <span class="text-sm text-gray-700">${escapeHtml(d.title || "N/A")}</span>
                        </div>
                        <div>
                            <span class="text-xs font-semibold text-gray-500 uppercase block mb-1">Content:</span>
                            <p class="text-sm text-gray-700 bg-white rounded-lg p-3 border border-gray-200 max-h-32 overflow-y-auto">${escapeHtml(d.content || "No content")}</p>
                        </div>
                        <div class="flex items-center gap-2">
                            <span class="text-xs font-semibold text-gray-500 uppercase">Status:</span>
                            <span class="text-sm font-medium ${d.status === "deleted" ? "text-red-600" : "text-gray-700"}">${(d.status || "").toUpperCase()}</span>
                        </div>
                    </div>
                </div>`;
        } else if (evidence.type === "project") {
            html = `
                <div class="bg-teal-50 border border-teal-200 rounded-xl p-4">
                    <h5 class="text-sm font-bold text-teal-800 mb-2 flex items-center gap-2">
                        <i class="fi fi-sr-building text-teal-600"></i> Reported Project
                    </h5>
                    <div class="space-y-2">
                        <div class="flex items-center gap-2">
                            <span class="text-xs font-semibold text-gray-500 uppercase">Owner:</span>
                            <span class="text-sm font-medium text-gray-800">${d.first_name || ""} ${d.last_name || ""} (@${d.username || ""})</span>
                        </div>
                        <div class="flex items-center gap-2">
                            <span class="text-xs font-semibold text-gray-500 uppercase">Project Title:</span>
                            <span class="text-sm text-gray-700">${escapeHtml(d.project_title || "N/A")}</span>
                        </div>
                        <div class="flex items-center gap-2">
                            <span class="text-xs font-semibold text-gray-500 uppercase">Status:</span>
                            <span class="text-sm font-medium text-gray-700">${(d.project_status || "").toUpperCase().replace(/_/g, " ")}</span>
                        </div>
                    </div>
                </div>`;
        } else if (evidence.type === "dispute") {
            const accused = d.accused;
            const project = d.project;
            html = `
                <div class="bg-orange-50 border border-orange-200 rounded-xl p-4">
                    <h5 class="text-sm font-bold text-orange-800 mb-2 flex items-center gap-2">
                        <i class="fi fi-sr-shield-exclamation text-orange-600"></i> User Dispute
                    </h5>
                    <div class="space-y-2">
                        ${accused ? `
                        <div class="flex items-center gap-2">
                            <span class="text-xs font-semibold text-gray-500 uppercase">Accused User:</span>
                            <span class="text-sm font-medium text-gray-800">${accused.first_name || ""} ${accused.last_name || ""} (@${accused.username || ""})</span>
                        </div>
                        <div class="flex items-center gap-2">
                            <span class="text-xs font-semibold text-gray-500 uppercase">Role:</span>
                            <span class="text-sm text-gray-700">${(accused.user_type || "").replace(/_/g, " ").toUpperCase()}</span>
                        </div>` : ""}
                        ${project ? `
                        <div class="flex items-center gap-2">
                            <span class="text-xs font-semibold text-gray-500 uppercase">Related Project:</span>
                            <span class="text-sm text-gray-700">${escapeHtml(project.project_title || "N/A")} (${(project.project_status || "").toUpperCase()})</span>
                        </div>` : ""}
                        <div>
                            <span class="text-xs font-semibold text-gray-500 uppercase block mb-1">Dispute Type:</span>
                            <p class="text-sm text-gray-800 font-medium">${escapeHtml(d.dispute_type || "N/A")}</p>
                        </div>
                        <div>
                            <span class="text-xs font-semibold text-gray-500 uppercase block mb-1">Description:</span>
                            <p class="text-sm text-gray-700 bg-white rounded-lg p-3 border border-gray-200">${escapeHtml(d.dispute_desc || "No description")}</p>
                        </div>
                    </div>
                </div>`;
        }

        container.innerHTML = html || '<div class="text-center text-gray-400 text-sm py-4">No evidence available.</div>';
    }

    function escapeHtml(str) {
        const div = document.createElement("div");
        div.textContent = str || "";
        return div.innerHTML;
    }

    // ══════════════════════════════════════════════════════
    // DISMISS FLOW
    // ══════════════════════════════════════════════════════
    document.getElementById("btnDismissReport")?.addEventListener("click", () => {
        if (!currentReport) return;
        document.getElementById("dismissReason").value = "";
        const modal = document.getElementById("dismissConfirmModal");
        if (modal) { modal.classList.remove("hidden"); modal.classList.add("flex"); }
    });

    document.getElementById("confirmDismissBtn")?.addEventListener("click", async () => {
        if (!currentReport) return;
        const reason = document.getElementById("dismissReason")?.value?.trim();
        if (!reason) {
            toast("Please provide a dismissal reason", "error");
            return;
        }

        const btn = document.getElementById("confirmDismissBtn");
        btn.disabled = true;
        btn.textContent = "Processing...";

        try {
            const res = await fetch(
                `/admin/global-management/report-management/${encodeURIComponent(currentReport.source)}/${encodeURIComponent(currentReport.id)}/dismiss`,
                {
                    method: "POST",
                    headers: getHeaders(),
                    body: JSON.stringify({ reason }),
                }
            );
            const json = await res.json();
            if (json.success) {
                toast("Report dismissed successfully", "success");
                closeAllModals();
                fetchReports();
            } else {
                toast(json.message || "Failed to dismiss report", "error");
            }
        } catch (e) {
            console.error(e);
            toast("Error dismissing report", "error");
        } finally {
            btn.disabled = false;
            btn.textContent = "Confirm Dismiss";
        }
    });

    // ══════════════════════════════════════════════════════
    // CONFIRM FLOW → SUSPENSION MODAL
    // ══════════════════════════════════════════════════════
    document.getElementById("btnConfirmReport")?.addEventListener("click", async () => {
        if (!currentReport || !currentReport.reported_user_id) {
            toast("Cannot determine reported user for this report", "error");
            return;
        }

        // Fetch user profile card
        try {
            const res = await fetch(
                `/admin/global-management/report-management/user-profile/${encodeURIComponent(currentReport.reported_user_id)}`,
                { headers: { "X-Requested-With": "XMLHttpRequest" } }
            );
            const json = await res.json();
            if (!json.success || !json.profile) {
                toast("Could not load user profile", "error");
                return;
            }

            populateSuspensionModal(json.profile);

            const modal = document.getElementById("suspensionModal");
            if (modal) { modal.classList.remove("hidden"); modal.classList.add("flex"); }
        } catch (e) {
            console.error(e);
            toast("Error loading user profile", "error");
        }
    });

    function populateSuspensionModal(profile) {
        document.getElementById("suspUserName").textContent = profile.name || "-";
        document.getElementById("suspUserRole").textContent =
            (profile.user_type || "").replace(/_/g, " ").replace(/\b\w/g, (c) => c.toUpperCase());
        document.getElementById("suspProjectsDone").textContent = profile.completed_projects || 0;
        document.getElementById("suspOngoingProjects").textContent = profile.ongoing_projects || 0;

        // Profile pic
        const picEl = document.getElementById("suspProfilePic");
        const iconEl = document.getElementById("suspProfileIcon");
        if (profile.profile_pic) {
            picEl.src = profile.profile_pic;
            picEl.classList.remove("hidden");
            iconEl.classList.add("hidden");
        } else {
            picEl.classList.add("hidden");
            iconEl.classList.remove("hidden");
        }

        // Reset form
        document.getElementById("suspensionReason").value = "";
        document.getElementById("suspensionUntil").value = "";
        setSuspensionType("temporary");
    }

    // ── Suspension type toggle ──
    function setSuspensionType(type) {
        document.querySelectorAll(".susp-type-btn").forEach((btn) => {
            if (btn.dataset.type === type) {
                btn.classList.add("border-blue-500", "bg-blue-50");
                btn.classList.remove("border-gray-200", "bg-white");
            } else {
                btn.classList.remove("border-blue-500", "bg-blue-50");
                btn.classList.add("border-gray-200", "bg-white");
            }
        });

        const datePicker = document.getElementById("suspDatePickerWrap");
        if (datePicker) {
            datePicker.classList.toggle("hidden", type === "permanent");
        }

        // Update consequence text
        const consequenceText = document.getElementById("consequenceContentText");
        if (consequenceText) {
            consequenceText.textContent = type === "permanent"
                ? "Account will be permanently terminated"
                : "Offending content will be removed/hidden";
        }
    }

    document.querySelectorAll(".susp-type-btn").forEach((btn) => {
        btn.addEventListener("click", () => setSuspensionType(btn.dataset.type));
    });

    // ── Confirm Suspension & Resolve ──
    document.getElementById("confirmSuspensionBtn")?.addEventListener("click", async () => {
        if (!currentReport) return;

        const reason = document.getElementById("suspensionReason")?.value?.trim();
        if (!reason) {
            toast("Please provide a suspension reason", "error");
            return;
        }

        // Determine type
        const activeTypeBtn = document.querySelector(".susp-type-btn.border-blue-500");
        const suspensionType = activeTypeBtn?.dataset.type || "temporary";

        let suspensionUntil = null;
        if (suspensionType === "temporary") {
            suspensionUntil = document.getElementById("suspensionUntil")?.value;
            if (!suspensionUntil) {
                toast("Please select a suspension end date", "error");
                return;
            }
        }

        const btn = document.getElementById("confirmSuspensionBtn");
        btn.disabled = true;
        const origText = btn.innerHTML;
        btn.innerHTML = '<i class="fi fi-sr-spinner animate-spin mr-2"></i> Processing...';

        try {
            // Check if this is a direct admin action or a report-based action
            const isDirectAction = btn.dataset.directAction === "true";

            let res;
            if (isDirectAction) {
                res = await fetch("/admin/global-management/report-management/admin-action", {
                    method: "POST",
                    headers: getHeaders(),
                    body: JSON.stringify({
                        action_type: "suspend_user",
                        target_id: btn.dataset.targetUserId || currentReport.reported_user_id,
                        reason: reason,
                        suspension_type: suspensionType,
                        suspension_until: suspensionUntil,
                    }),
                });
            } else {
                res = await fetch(
                    `/admin/global-management/report-management/${encodeURIComponent(currentReport.source)}/${encodeURIComponent(currentReport.id)}/confirm`,
                    {
                        method: "POST",
                        headers: getHeaders(),
                        body: JSON.stringify({
                            suspension_reason: reason,
                            suspension_type: suspensionType,
                            suspension_until: suspensionUntil,
                            reported_user_id: currentReport.reported_user_id,
                        }),
                    }
                );
            }

            const json = await res.json();
            if (json.success) {
                toast(isDirectAction ? "User suspended successfully" : "Report resolved and user suspended successfully", "success");
                closeAllModals();
                // Clean up direct action flag
                btn.dataset.directAction = "";
                btn.dataset.targetUserId = "";
                if (isDirectAction) {
                    adminSearch(); // Refresh search results
                } else {
                    fetchReports();
                }
            } else {
                toast(json.message || "Failed to process", "error");
            }
        } catch (e) {
            console.error(e);
            toast("Error processing confirmation", "error");
        } finally {
            btn.disabled = false;
            btn.innerHTML = origText;
        }
    });

    // ══════════════════════════════════════════════════════
    // MODAL CLOSE HELPERS
    // ══════════════════════════════════════════════════════
    function closeAllModals() {
        ["viewReportModal", "dismissConfirmModal", "suspensionModal", "hidePostModal", "hideReviewModal"].forEach((id) => {
            const el = document.getElementById(id);
            if (el) { el.classList.add("hidden"); el.classList.remove("flex"); }
        });
    }

    // Close buttons (modal-close class)
    document.querySelectorAll(".modal-close").forEach((btn) => {
        btn.addEventListener("click", function () {
            const overlay = this.closest(".modal-overlay");
            if (overlay) { overlay.classList.add("hidden"); overlay.classList.remove("flex"); }
        });
    });

    // Backdrop click close
    document.querySelectorAll(".modal-overlay").forEach((overlay) => {
        overlay.addEventListener("click", function (e) {
            if (e.target === this) {
                this.classList.add("hidden");
                this.classList.remove("flex");
            }
        });
    });

    // Escape key close
    document.addEventListener("keydown", (e) => {
        if (e.key === "Escape") closeAllModals();
    });

    // ══════════════════════════════════════════════════════
    // FILTERS
    // ══════════════════════════════════════════════════════
    ["filterSource", "filterStatus", "filterDateFrom", "filterDateTo"].forEach((id) => {
        const el = document.getElementById(id);
        if (el) el.addEventListener("change", fetchReports);
    });

    let searchTimer;
    const searchEl = document.getElementById("filterSearch");
    if (searchEl) {
        searchEl.addEventListener("input", () => {
            clearTimeout(searchTimer);
            searchTimer = setTimeout(fetchReports, 400);
        });
    }

    document.getElementById("resetFilters")?.addEventListener("click", () => {
        ["filterSource", "filterStatus"].forEach((id) => {
            const el = document.getElementById(id); if (el) el.value = "all";
        });
        ["filterDateFrom", "filterDateTo", "filterSearch"].forEach((id) => {
            const el = document.getElementById(id); if (el) el.value = "";
        });
        fetchReports();
    });

    // ══════════════════════════════════════════════════════
    // REPORT HISTORY FILTER
    // ══════════════════════════════════════════════════════
    document.getElementById("historyFilter")?.addEventListener("change", function () {
        const val = this.value;
        document.querySelectorAll(".reporter-row").forEach((row) => {
            if (val === "all") {
                row.classList.remove("hidden");
            } else if (val === "super") {
                row.classList.toggle("hidden", row.dataset.super !== "1");
            } else if (val === "abusers") {
                row.classList.toggle("hidden", row.dataset.abuser !== "1");
            }
        });
    });

    // ══════════════════════════════════════════════════════
    // TAB 3: DIRECT ADMIN ACTION — Search & Take Action
    // ══════════════════════════════════════════════════════
    let adminSearchTimer;
    let adminCurrentPage = 1;
    let adminLastPage = 1;
    let adminTotalResults = 0;
    let adminTabLoaded = false;
    const adminSearchInput = document.getElementById("adminSearchInput");
    const adminSearchTypeEl = document.getElementById("adminSearchType");

    // Debounced search on input
    if (adminSearchInput) {
        adminSearchInput.addEventListener("input", () => {
            clearTimeout(adminSearchTimer);
            adminCurrentPage = 1;
            adminSearchTimer = setTimeout(adminSearch, 400);
        });
        adminSearchInput.addEventListener("keydown", (e) => {
            if (e.key === "Enter") { clearTimeout(adminSearchTimer); adminCurrentPage = 1; adminSearch(); }
        });
    }

    document.getElementById("adminSearchBtn")?.addEventListener("click", () => { adminCurrentPage = 1; adminSearch(); });
    adminSearchTypeEl?.addEventListener("change", () => {
        if (adminSearchInput) adminSearchInput.value = "";
        adminCurrentPage = 1;
        adminSearch();
    });

    // Auto-load default data when Tab 3 is first clicked
    document.querySelectorAll(".tab-btn").forEach(btn => {
        btn.addEventListener("click", () => {
            if (btn.dataset.tab === "adminAction" && !adminTabLoaded) {
                adminTabLoaded = true;
                adminSearch();
            }
        });
    });

    // Pagination controls
    document.getElementById("adminPrevPage")?.addEventListener("click", () => {
        if (adminCurrentPage > 1) { adminCurrentPage--; adminSearch(); }
    });
    document.getElementById("adminNextPage")?.addEventListener("click", () => {
        if (adminCurrentPage < adminLastPage) { adminCurrentPage++; adminSearch(); }
    });

    async function adminSearch() {
        const type = adminSearchTypeEl?.value || "user";
        const query = adminSearchInput?.value?.trim() || "";

        const thead = document.getElementById("adminSearchThead");
        const tbody = document.getElementById("adminSearchBody");
        if (!tbody) return;

        tbody.innerHTML = `<tr><td colspan="6" class="px-6 py-8 text-center text-gray-400 text-sm"><i class="fi fi-rr-loading-spinner animate-spin text-xl"></i><br>Loading...</td></tr>`;

        try {
            const params = new URLSearchParams({ type, query, page: adminCurrentPage });
            const res = await fetch(`/admin/global-management/report-management/admin-search?${params.toString()}`, {
                headers: { "X-Requested-With": "XMLHttpRequest" },
            });
            const json = await res.json();

            if (!json.success) {
                tbody.innerHTML = `<tr><td colspan="6" class="px-6 py-8 text-center text-red-500 text-sm">${escapeHtml(json.message || "Search failed")}</td></tr>`;
                hideAdminPagination();
                return;
            }

            const results = json.results || [];
            const pagination = json.pagination || {};
            adminCurrentPage = pagination.page || 1;
            adminLastPage = pagination.last_page || 1;
            adminTotalResults = pagination.total || 0;

            if (type === "user") renderAdminUserResults(thead, tbody, results);
            else if (type === "post") renderAdminPostResults(thead, tbody, results);
            else if (type === "review") renderAdminReviewResults(thead, tbody, results);

            renderAdminPagination(pagination);

        } catch (e) {
            console.error(e);
            tbody.innerHTML = `<tr><td colspan="6" class="px-6 py-8 text-center text-red-500 text-sm">Error performing search</td></tr>`;
            hideAdminPagination();
        }
    }

    function renderAdminPagination(pagination) {
        const bar = document.getElementById("adminPaginationBar");
        const info = document.getElementById("adminPaginationInfo");
        const indicator = document.getElementById("adminPageIndicator");
        const prevBtn = document.getElementById("adminPrevPage");
        const nextBtn = document.getElementById("adminNextPage");
        if (!bar) return;

        const total = pagination.total || 0;
        const page = pagination.page || 1;
        const perPage = pagination.per_page || 15;
        const lastPage = pagination.last_page || 1;

        if (total === 0) { bar.classList.add("hidden"); return; }

        const from = (page - 1) * perPage + 1;
        const to = Math.min(page * perPage, total);

        bar.classList.remove("hidden");
        if (info) info.textContent = `Showing ${from}-${to} of ${total} results`;
        if (indicator) indicator.textContent = `Page ${page} of ${lastPage}`;
        if (prevBtn) prevBtn.disabled = page <= 1;
        if (nextBtn) nextBtn.disabled = page >= lastPage;
    }

    function hideAdminPagination() {
        const bar = document.getElementById("adminPaginationBar");
        if (bar) bar.classList.add("hidden");
    }

    function renderAdminUserResults(thead, tbody, users) {
        thead.innerHTML = `<tr>
            <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">User</th>
            <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Username</th>
            <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Email</th>
            <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Role</th>
            <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Status</th>
            <th class="px-6 py-4 text-center text-xs font-semibold text-gray-600 uppercase tracking-wider">Actions</th>
        </tr>`;

        if (!users.length) {
            tbody.innerHTML = `<tr><td colspan="6" class="px-6 py-12 text-center text-gray-500 text-sm">No users found.</td></tr>`;
            return;
        }

        tbody.innerHTML = users.map(u => {
            const isActive = u.user_type === "contractor"
                ? (u.contractor_is_active !== 0)
                : (u.owner_is_active !== 0);
            const statusBadge = isActive
                ? `<span class="inline-flex px-3 py-1 rounded-full text-xs font-semibold bg-emerald-100 text-emerald-700">Active</span>`
                : `<span class="inline-flex px-3 py-1 rounded-full text-xs font-semibold bg-red-100 text-red-700">Suspended</span>`;
            const role = (u.user_type || "").replace(/_/g, " ").replace(/\b\w/g, c => c.toUpperCase());

            return `<tr class="hover:bg-gray-50 transition border-b border-gray-100">
                <td class="px-6 py-4 text-sm font-medium text-gray-800">${escapeHtml((u.first_name || "") + " " + (u.last_name || ""))}</td>
                <td class="px-6 py-4 text-sm text-gray-600">@${escapeHtml(u.username || "")}</td>
                <td class="px-6 py-4 text-sm text-gray-600">${escapeHtml(u.email || "")}</td>
                <td class="px-6 py-4"><span class="inline-flex px-3 py-1 rounded-full text-xs font-semibold bg-indigo-100 text-indigo-700">${role}</span></td>
                <td class="px-6 py-4">${statusBadge}</td>
                <td class="px-6 py-4 text-center">
                    ${isActive ? `<button class="px-4 py-2 rounded-xl bg-gradient-to-r from-red-600 to-rose-600 hover:from-red-700 hover:to-rose-700 text-white text-xs font-semibold shadow-sm hover:shadow-md transition admin-suspend-btn" data-user-id="${u.user_id}">
                        <i class="fi fi-rr-ban mr-1"></i> Suspend
                    </button>` : `<span class="text-xs text-gray-400">Already suspended</span>`}
                </td>
            </tr>`;
        }).join("");

        bindAdminSuspendButtons();
    }

    function renderAdminPostResults(thead, tbody, posts) {
        thead.innerHTML = `<tr>
            <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">ID</th>
            <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Title</th>
            <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Author</th>
            <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Status</th>
            <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Date</th>
            <th class="px-6 py-4 text-center text-xs font-semibold text-gray-600 uppercase tracking-wider">Actions</th>
        </tr>`;

        if (!posts.length) {
            tbody.innerHTML = `<tr><td colspan="6" class="px-6 py-12 text-center text-gray-500 text-sm">No posts found.</td></tr>`;
            return;
        }

        tbody.innerHTML = posts.map(p => {
            const date = p.created_at ? new Date(p.created_at).toLocaleDateString("en-US", { month: "short", day: "numeric", year: "numeric" }) : "-";
            return `<tr class="hover:bg-gray-50 transition border-b border-gray-100">
                <td class="px-6 py-4 text-sm font-mono text-gray-700">#${p.post_id}</td>
                <td class="px-6 py-4 text-sm font-medium text-gray-800 max-w-xs truncate" title="${escapeHtml(p.title || "")}">${escapeHtml(p.title || "Untitled")}</td>
                <td class="px-6 py-4 text-sm text-gray-600">${escapeHtml((p.first_name || "") + " " + (p.last_name || ""))} (@${escapeHtml(p.username || "")})</td>
                <td class="px-6 py-4"><span class="inline-flex px-3 py-1 rounded-full text-xs font-semibold bg-blue-100 text-blue-700">${(p.status || "").toUpperCase()}</span></td>
                <td class="px-6 py-4 text-sm text-gray-500">${date}</td>
                <td class="px-6 py-4 text-center">
                    ${p.status === 'deleted' ? `<span class="text-xs text-gray-400">Already hidden</span>` : `<button class="px-4 py-2 rounded-xl bg-gradient-to-r from-amber-600 to-orange-600 hover:from-amber-700 hover:to-orange-700 text-white text-xs font-semibold shadow-sm hover:shadow-md transition admin-hide-post-btn" data-post-id="${p.post_id}" data-post-title="${escapeHtml(p.title || 'Untitled')}" data-post-author="${escapeHtml((p.first_name || '') + ' ' + (p.last_name || ''))}">
                        <i class="fi fi-rr-eye-crossed mr-1"></i> Hide Post
                    </button>`}
                </td>
            </tr>`;
        }).join("");

        bindAdminHidePostButtons();
    }

    function renderAdminReviewResults(thead, tbody, reviews) {
        thead.innerHTML = `<tr>
            <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">ID</th>
            <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Project</th>
            <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Reviewer</th>
            <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Rating</th>
            <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Comment</th>
            <th class="px-6 py-4 text-center text-xs font-semibold text-gray-600 uppercase tracking-wider">Actions</th>
        </tr>`;

        if (!reviews.length) {
            tbody.innerHTML = `<tr><td colspan="6" class="px-6 py-12 text-center text-gray-500 text-sm">No reviews found.</td></tr>`;
            return;
        }

        tbody.innerHTML = reviews.map(r => {
            const stars = "★".repeat(r.rating || 0) + "☆".repeat(5 - (r.rating || 0));
            return `<tr class="hover:bg-gray-50 transition border-b border-gray-100">
                <td class="px-6 py-4 text-sm font-mono text-gray-700">#${r.review_id}</td>
                <td class="px-6 py-4 text-sm text-gray-700 max-w-[150px] truncate" title="${escapeHtml(r.project_title || "")}">${escapeHtml(r.project_title || "N/A")}</td>
                <td class="px-6 py-4 text-sm text-gray-600">${escapeHtml((r.reviewer_first_name || "") + " " + (r.reviewer_last_name || ""))} (@${escapeHtml(r.reviewer_username || "")})</td>
                <td class="px-6 py-4 text-sm text-amber-600 font-bold">${stars}</td>
                <td class="px-6 py-4 text-sm text-gray-600 max-w-xs truncate" title="${escapeHtml(r.comment || "")}">${escapeHtml(r.comment || "No comment")}</td>
                <td class="px-6 py-4 text-center">
                    ${r.is_deleted ? `<span class="text-xs text-gray-400">Already hidden</span>` : `<button class="px-4 py-2 rounded-xl bg-gradient-to-r from-amber-600 to-orange-600 hover:from-amber-700 hover:to-orange-700 text-white text-xs font-semibold shadow-sm hover:shadow-md transition admin-hide-review-btn" data-review-id="${r.review_id}" data-review-author="${escapeHtml((r.reviewer_first_name || '') + ' ' + (r.reviewer_last_name || ''))}" data-review-rating="${"★".repeat(r.rating || 0) + "☆".repeat(5 - (r.rating || 0))}">
                        <i class="fi fi-rr-eye-crossed mr-1"></i> Hide Review
                    </button>`}
                </td>
            </tr>`;
        }).join("");

        bindAdminHideReviewButtons();
    }

    // ── Admin Action: Suspend User (reuses suspension modal) ──
    function bindAdminSuspendButtons() {
        document.querySelectorAll(".admin-suspend-btn").forEach(btn => {
            btn.onclick = async function () {
                const userId = this.dataset.userId;

                try {
                    const res = await fetch(
                        `/admin/global-management/report-management/user-profile/${encodeURIComponent(userId)}`,
                        { headers: { "X-Requested-With": "XMLHttpRequest" } }
                    );
                    const json = await res.json();
                    if (!json.success || !json.profile) {
                        toast("Could not load user profile", "error");
                        return;
                    }

                    // Set currentReport context for the suspension modal to work
                    currentReport = { id: null, source: "admin_direct", status: null, reported_user_id: parseInt(userId) };

                    populateSuspensionModal(json.profile);

                    // Change the confirm button behavior for direct action
                    const confirmBtn = document.getElementById("confirmSuspensionBtn");
                    if (confirmBtn) {
                        confirmBtn.dataset.directAction = "true";
                        confirmBtn.dataset.targetUserId = userId;
                    }

                    const modal = document.getElementById("suspensionModal");
                    if (modal) { modal.classList.remove("hidden"); modal.classList.add("flex"); }
                } catch (e) {
                    console.error(e);
                    toast("Error loading user profile", "error");
                }
            };
        });
    }

    // ── Admin Action: Hide Post (modal-based) ──
    let pendingHidePostId = null;

    function bindAdminHidePostButtons() {
        document.querySelectorAll(".admin-hide-post-btn").forEach(btn => {
            btn.onclick = function () {
                pendingHidePostId = this.dataset.postId;
                document.getElementById("hidePostId").textContent = `#${this.dataset.postId}`;
                document.getElementById("hidePostTitle").textContent = this.dataset.postTitle || "-";
                document.getElementById("hidePostAuthor").textContent = this.dataset.postAuthor || "-";
                document.getElementById("hidePostReason").value = "";
                const modal = document.getElementById("hidePostModal");
                if (modal) { modal.classList.remove("hidden"); modal.classList.add("flex"); }
            };
        });
    }

    document.getElementById("confirmHidePostBtn")?.addEventListener("click", async function () {
        const reason = document.getElementById("hidePostReason")?.value?.trim();
        if (!reason) { toast("Please provide a reason for hiding this post", "error"); return; }
        if (!pendingHidePostId) return;

        const btn = this;
        btn.disabled = true;
        const origText = btn.innerHTML;
        btn.innerHTML = '<i class="fi fi-sr-spinner animate-spin mr-1"></i> Hiding...';

        try {
            const res = await fetch("/admin/global-management/report-management/admin-action", {
                method: "POST",
                headers: getHeaders(),
                body: JSON.stringify({ action_type: "hide_post", target_id: pendingHidePostId, reason }),
            });
            const json = await res.json();
            if (json.success) {
                toast("Post hidden successfully. The owner has been notified.", "success");
                closeAllModals();
                adminSearch();
            } else {
                toast(json.message || "Failed to hide post", "error");
            }
        } catch (e) {
            console.error(e);
            toast("Error hiding post", "error");
        } finally {
            btn.disabled = false;
            btn.innerHTML = origText;
        }
    });

    // ── Admin Action: Hide Review (modal-based) ──
    let pendingHideReviewId = null;

    function bindAdminHideReviewButtons() {
        document.querySelectorAll(".admin-hide-review-btn").forEach(btn => {
            btn.onclick = function () {
                pendingHideReviewId = this.dataset.reviewId;
                document.getElementById("hideReviewId").textContent = `#${this.dataset.reviewId}`;
                document.getElementById("hideReviewAuthor").textContent = this.dataset.reviewAuthor || "-";
                document.getElementById("hideReviewRating").textContent = this.dataset.reviewRating || "-";
                document.getElementById("hideReviewReason").value = "";
                const modal = document.getElementById("hideReviewModal");
                if (modal) { modal.classList.remove("hidden"); modal.classList.add("flex"); }
            };
        });
    }

    document.getElementById("confirmHideReviewBtn")?.addEventListener("click", async function () {
        const reason = document.getElementById("hideReviewReason")?.value?.trim();
        if (!reason) { toast("Please provide a reason for hiding this review", "error"); return; }
        if (!pendingHideReviewId) return;

        const btn = this;
        btn.disabled = true;
        const origText = btn.innerHTML;
        btn.innerHTML = '<i class="fi fi-sr-spinner animate-spin mr-1"></i> Hiding...';

        try {
            const res = await fetch("/admin/global-management/report-management/admin-action", {
                method: "POST",
                headers: getHeaders(),
                body: JSON.stringify({ action_type: "hide_review", target_id: pendingHideReviewId, reason }),
            });
            const json = await res.json();
            if (json.success) {
                toast("Review hidden successfully. The reviewer has been notified.", "success");
                closeAllModals();
                adminSearch();
            } else {
                toast(json.message || "Failed to hide review", "error");
            }
        } catch (e) {
            console.error(e);
            toast("Error hiding review", "error");
        } finally {
            btn.disabled = false;
            btn.innerHTML = origText;
        }
    });

    // ── Init ──
    bindViewButtons();
})();
