(function () {
    "use strict";

    // ── State ──
    let currentReport = null; // { id, source, status, reported_user_id }
    let currentDirectPreviewItem = null; // { type, id, item }
    let moderationCurrentPage = 1;
    let moderationLastPage = 1;

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
            project: "bg-blue-100 text-blue-700",
            showcase: "bg-teal-100 text-teal-700",
            review: "bg-purple-100 text-purple-700",
            user: "bg-cyan-100 text-cyan-700",
            dispute: "bg-orange-100 text-orange-700",
        };
        const labels = { project: "Project", showcase: "Showcase", review: "Review", user: "User", dispute: "Dispute" };
        const cls = colors[source] || "bg-gray-100 text-gray-700";
        return `<span class="inline-flex px-3 py-1 rounded-full text-xs font-semibold ${cls}">${labels[source] || source}</span>`;
    }

    function getCaseTypeBadge(type) {
        const colors = {
            report: "bg-indigo-100 text-indigo-700",
            dispute: "bg-orange-100 text-orange-700",
        };
        const cls = colors[type] || "bg-gray-100 text-gray-700";
        const label = (type || "-").charAt(0).toUpperCase() + (type || "-").slice(1);
        return `<span class="inline-flex px-3 py-1 rounded-full text-xs font-semibold ${cls}">${label}</span>`;
    }

    function formatUserName(user) {
        if (!user) return "Unknown";

        const first = (user.first_name || "").trim();
        const last = (user.last_name || "").trim();
        const username = (user.username || "").trim();
        const fullName = `${first} ${last}`.trim();

        if (fullName && username) return `${fullName} (@${username})`;
        if (fullName) return fullName;
        if (username) return `@${username}`;
        return user.email || "Unknown";
    }

    function formatDisplayDate(value, withTime = false) {
        if (!value) return "-";
        const options = withTime
            ? { month: "short", day: "numeric", year: "numeric", hour: "numeric", minute: "2-digit" }
            : { month: "short", day: "numeric", year: "numeric" };
        return new Date(value).toLocaleDateString("en-US", options);
    }

    function toStorageUrl(path) {
        if (!path) return "#";
        return `/storage/${String(path).replace(/^\/+/, "")}`;
    }

    function renderAttachmentList(items, type) {
        if (!Array.isArray(items) || !items.length) return "";

        const label = type === "image" ? "Media Gallery" : "Attached Files";
        const content = items
            .map((item) => {
                if (type === "image") {
                    const title = escapeHtml(item.original_name || `Image ${item.sort_order ?? ""}`.trim());
                    return `<a href="${toStorageUrl(item.file_path)}" target="_blank" class="group block overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm hover:shadow-md transition">
                        <img src="${toStorageUrl(item.file_path)}" alt="${title}" class="h-32 w-full object-cover group-hover:scale-[1.02] transition">
                        <div class="px-3 py-2 text-xs font-medium text-gray-600 truncate">${title}</div>
                    </a>`;
                }

                const name = escapeHtml(item.original_name || item.file_type || item.file_name || "Attachment");
                const meta = [item.file_type, item.uploaded_at ? formatDisplayDate(item.uploaded_at, true) : null]
                    .filter(Boolean)
                    .map((value) => escapeHtml(String(value)))
                    .join(" • ");

                return `<a href="${toStorageUrl(item.file_path)}" target="_blank" class="flex items-start justify-between gap-3 rounded-xl border border-gray-200 bg-white px-4 py-3 hover:border-indigo-300 hover:bg-indigo-50 transition">
                    <div>
                        <p class="text-sm font-semibold text-gray-800">${name}</p>
                        <p class="text-xs text-gray-500 mt-1">${meta || "Open attachment"}</p>
                    </div>
                    <i class="fi fi-rr-arrow-up-right-from-square text-gray-400"></i>
                </a>`;
            })
            .join("");

        return `<div>
            <span class="text-xs font-semibold text-gray-500 uppercase block mb-2">${label}</span>
            <div class="${type === "image" ? "grid grid-cols-2 gap-3" : "space-y-2"}">${content}</div>
        </div>`;
    }

    function normalizeUserId(value) {
        const parsed = Number.parseInt(value, 10);
        return Number.isInteger(parsed) && parsed > 0 ? parsed : null;
    }

    function inferReportedUserId(report) {
        const topLevelId = normalizeUserId(report.reported_user_id);
        if (topLevelId) return topLevelId;

        const embeddedId = normalizeUserId(report.reported_user?.user_id);
        if (embeddedId) return embeddedId;

        const evidence = report.evidence || {};
        const data = evidence.data || {};

        if (evidence.type === "showcase") return normalizeUserId(data.author_user_id) || normalizeUserId(data.user_id);
        if (evidence.type === "project") return normalizeUserId(data.author_user_id);
        if (evidence.type === "review") return normalizeUserId(data.reviewee_account_user_id) || normalizeUserId(data.reviewee_user_id);
        if (evidence.type === "user_report") return normalizeUserId(data.reported_user?.user_id);
        if (evidence.type === "dispute") return normalizeUserId(data.accused?.user_id);

        return null;
    }

    function buildDisputePayload(action, reason = "") {
        if (action === "resolve") {
            return { notes: "Resolved by admin from Global Report Management." };
        }

        if (action === "reject") {
            return { reason: reason || "Dismissed by admin from Global Report Management." };
        }

        return {};
    }

    function toggleResolutionActionDate() {
        const type = document.querySelector('input[name="resolutionActionType"]:checked')?.value || "warning";
        const dateWrap = document.getElementById("resolutionBanUntilWrap");
        if (dateWrap) {
            dateWrap.classList.toggle("hidden", type !== "temporary_ban");
        }
    }

    function populateResolutionActionModal(profile) {
        document.getElementById("resolutionUserName").textContent = profile.name || "-";
        document.getElementById("resolutionUserRole").textContent =
            (profile.user_type || "").replace(/_/g, " ").replace(/\b\w/g, (c) => c.toUpperCase());

        const picEl = document.getElementById("resolutionProfilePic");
        const iconEl = document.getElementById("resolutionProfileIcon");
        if (profile.profile_pic) {
            picEl.src = profile.profile_pic;
            picEl.classList.remove("hidden");
            iconEl.classList.add("hidden");
        } else {
            picEl.classList.add("hidden");
            iconEl.classList.remove("hidden");
        }

        const warningType = document.querySelector('input[name="resolutionActionType"][value="warning"]');
        if (warningType) warningType.checked = true;
        document.getElementById("resolutionActionReason").value = "";
        document.getElementById("resolutionBanUntil").value = "";
        toggleResolutionActionDate();
    }

    async function openResolutionActionModal() {
        if (!currentReport) {
            toast("No active case selected for resolution action.", "error");
            return;
        }

        try {
            const res = await fetch(
                `/admin/global-management/report-management/${encodeURIComponent(currentReport.source)}/${encodeURIComponent(currentReport.id)}/reported-user-profile`,
                { headers: { "X-Requested-With": "XMLHttpRequest" } }
            );
            const json = await res.json();
            if (!json.success || !json.profile) {
                toast("Could not load user profile", "error");
                return;
            }

            currentReport.reported_user_id = normalizeUserId(json.reported_user_id) || currentReport.reported_user_id;

            populateResolutionActionModal(json.profile);

            const modal = document.getElementById("resolutionActionModal");
            if (modal) {
                modal.classList.remove("hidden");
                modal.classList.add("flex");
            }
        } catch (e) {
            console.error(e);
            toast("Error loading resolution action data", "error");
        }
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
        const sourceType = document.getElementById("filterSource")?.value || "all";
        const caseType = document.getElementById("filterCaseType")?.value || "all";
        const status = document.getElementById("filterStatus")?.value || "all";
        const search = document.getElementById("filterSearch")?.value || "";
        const dateFrom = document.getElementById("filterDateFrom")?.value || "";
        const dateTo = document.getElementById("filterDateTo")?.value || "";

        if (sourceType !== "all") params.set("source_type", sourceType);
        if (caseType !== "all") params.set("case_type", caseType);
        if (status !== "all") params.set("status", status);
        if (search) params.set("search", search);
        if (dateFrom) params.set("date_from", dateFrom);
        if (dateTo) params.set("date_to", dateTo);
        params.set("page", moderationCurrentPage);
        params.set("per_page", 15);

        try {
            const res = await fetch(
                `/admin/global-management/report-management/api?${params.toString()}`,
                { headers: { "X-Requested-With": "XMLHttpRequest" } }
            );
            const json = await res.json();
            if (!json.success) return;

            renderReportsTable(json.reports || []);
            renderModerationPagination(json.pagination || null);

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
            tbody.innerHTML = `<tr><td colspan="9" class="px-6 py-12 text-center text-gray-500 text-sm">No moderation cases found.</td></tr>`;
            return;
        }

        tbody.innerHTML = reports
            .map((r) => {
                const date = r.created_at
                    ? new Date(r.created_at).toLocaleDateString("en-US", { month: "short", day: "numeric", year: "numeric" })
                    : "-";
                return `<tr class="hover:bg-gray-50 transition" data-id="${r.case_ref_id}" data-source="${r.source}" data-case-type="${r.case_type}" data-status="${r.status}">
                    <td class="px-6 py-4 text-sm font-mono text-gray-700">${r.case_id || "-"}</td>
                    <td class="px-6 py-4">${getCaseTypeBadge(r.case_type)}</td>
                    <td class="px-6 py-4">${getSourceBadge(r.source_type)}</td>
                    <td class="px-6 py-4 text-sm text-gray-700 font-medium">${escapeHtml(r.reporter || "-")}</td>
                    <td class="px-6 py-4 text-sm text-gray-600 max-w-xs truncate" title="${escapeHtml(r.target || "")}">${escapeHtml(r.target || "-")}</td>
                    <td class="px-6 py-4 text-sm text-gray-600 max-w-xs truncate" title="${escapeHtml(r.reason || "")}">${escapeHtml(r.reason || "-")}</td>
                    <td class="px-6 py-4">${getStatusBadge(r.status)}</td>
                    <td class="px-6 py-4 text-sm text-gray-500">${date}</td>
                    <td class="px-6 py-4 text-center">
                        <button class="px-4 py-2 rounded-xl bg-gradient-to-r from-indigo-600 to-purple-600 hover:from-indigo-700 hover:to-purple-700 text-white text-xs font-semibold shadow-sm hover:shadow-md transition view-report-btn" data-id="${r.case_ref_id}" data-source="${r.source}" data-case-type="${r.case_type}">
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
                openViewModal(this.dataset.source, this.dataset.id, this.dataset.caseType);
            };
        });
    }

    function renderModerationPagination(pagination) {
        const info = document.getElementById("moderationPaginationInfo");
        const indicator = document.getElementById("moderationPageIndicator");
        const prev = document.getElementById("moderationPrevPage");
        const next = document.getElementById("moderationNextPage");

        if (!pagination) return;

        const total = pagination.total || 0;
        const page = pagination.page || 1;
        const perPage = pagination.per_page || 15;
        const lastPage = pagination.last_page || 1;

        moderationCurrentPage = page;
        moderationLastPage = lastPage;

        const from = total ? (page - 1) * perPage + 1 : 0;
        const to = total ? Math.min(page * perPage, total) : 0;

        if (info) info.textContent = total ? `Showing ${from}-${to} of ${total} cases` : "No cases found";
        if (indicator) indicator.textContent = `Page ${page} of ${lastPage}`;
        if (prev) prev.disabled = page <= 1;
        if (next) next.disabled = page >= lastPage;
    }

    // ══════════════════════════════════════════════════════
    // VIEW REPORT MODAL — load detail with evidence
    // ══════════════════════════════════════════════════════
    async function openViewModal(source, reportId, caseType = null) {
        currentReport = null;

        // Show modal with loading state
        document.getElementById("modalCaseId").textContent = `Case #${reportId}`;
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
                case_type: caseType || (source === "dispute" ? "dispute" : "report"),
                status: r.status,
                reported_user_id: inferReportedUserId(r),
            };

            // Populate fields
            document.getElementById("modalCaseId").textContent = `Case #${reportId} — ${source.charAt(0).toUpperCase() + source.slice(1)}`;
            const schemaSourceLabel = (r.content_type || (source === "review" ? "review" : source === "dispute" ? "dispute" : ""));
            document.getElementById("modalSource").textContent = schemaSourceLabel
                ? schemaSourceLabel.charAt(0).toUpperCase() + schemaSourceLabel.slice(1)
                : "-";
            document.getElementById("modalReporter").textContent = formatUserName(r.reporter);
            document.getElementById("modalReason").textContent = r.reason || "-";
            document.getElementById("modalContentType").textContent =
                (r.content_type || "-").charAt(0).toUpperCase() + (r.content_type || "-").slice(1);
            document.getElementById("modalDate").textContent = formatDisplayDate(r.created_at);
            document.getElementById("modalStatus").innerHTML = getStatusBadge(r.status);
            const extra = source === "dispute" && r.requested_action
                ? `\n\nRequested Action: ${r.requested_action}`
                : "";
            const evidenceData = r.evidence?.data || {};
            const projectSummary = r.content_type === "project"
                ? `\n\nProject Details:\nTitle: ${evidenceData.project_title || "N/A"}\nLocation: ${evidenceData.project_location || "N/A"}\nStatus: ${String(evidenceData.project_status || "N/A").replace(/_/g, " ")}`
                : "";
            document.getElementById("modalDetails").textContent = (r.details || "No additional details provided.") + extra + projectSummary;

            // Admin notes (for already-resolved/dismissed)
            if (r.admin_notes && (r.status === "resolved" || r.status === "dismissed")) {
                document.getElementById("modalAdminNotes").textContent = r.admin_notes;
                document.getElementById("modalAdminNotesWrap").classList.remove("hidden");
            } else {
                document.getElementById("modalAdminNotesWrap").classList.add("hidden");
            }

            // Evidence
            const evidencePayload = source === "dispute"
                ? Object.assign({}, r.evidence || {}, { files: r.evidence_files || [] })
                : r.evidence;
            renderEvidence(evidencePayload);

            // Show/hide action buttons based on status
            configureCaseActionButtons(source, r.status);
        } catch (e) {
            console.error(e);
            toast("Error loading report details", "error");
        }
    }

    function configureCaseActionButtons(source, status) {
        const reportBtns = document.getElementById("modalActionBtns");
        const underReviewBtn = document.getElementById("btnUnderReviewReport");
        const disputeBtns = document.getElementById("modalDisputeActionBtns");
        const directBtns = document.getElementById("modalDirectActionBtns");
        const reviewBtn = document.getElementById("btnReviewDispute");
        const resolveBtn = document.getElementById("btnResolveDispute");
        const rejectBtn = document.getElementById("btnRejectDispute");

        const actionable = status === "pending" || status === "under_review";

        if (source === "dispute") {
            if (directBtns) {
                directBtns.classList.add("hidden");
                directBtns.classList.remove("flex");
            }
            if (reportBtns) reportBtns.classList.add("hidden");
            if (disputeBtns) {
                disputeBtns.classList.toggle("hidden", !actionable);
                disputeBtns.classList.toggle("flex", actionable);
            }

            if (reviewBtn) reviewBtn.classList.toggle("hidden", status !== "pending");
            if (resolveBtn) resolveBtn.classList.toggle("hidden", !actionable);
            if (rejectBtn) rejectBtn.classList.toggle("hidden", !actionable);
            return;
        }

        if (disputeBtns) {
            disputeBtns.classList.add("hidden");
            disputeBtns.classList.remove("flex");
        }
        if (directBtns) {
            directBtns.classList.add("hidden");
            directBtns.classList.remove("flex");
        }
        if (reportBtns) {
            reportBtns.classList.toggle("hidden", !actionable);
        }
        if (underReviewBtn) {
            underReviewBtn.classList.toggle("hidden", status !== "pending");
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
                            <span class="text-sm font-medium text-gray-800">${escapeHtml(formatUserName(d))}</span>
                        </div>
                        <div class="flex items-center gap-2">
                            <span class="text-xs font-semibold text-gray-500 uppercase">Reviewee:</span>
                            <span class="text-sm text-gray-700">${escapeHtml(formatUserName({ first_name: d.reviewee_first_name, last_name: d.reviewee_last_name, username: d.reviewee_username }))}</span>
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
            const imagesHtml = renderAttachmentList(evidence.images || [], "image");
            html = `
                <div class="bg-blue-50 border border-blue-200 rounded-xl p-4">
                    <h5 class="text-sm font-bold text-blue-800 mb-2 flex items-center gap-2">
                        <i class="fi fi-sr-picture text-blue-600"></i> Reported Showcase Post
                    </h5>
                    <div class="space-y-2">
                        <div class="flex items-center gap-2">
                            <span class="text-xs font-semibold text-gray-500 uppercase">Author:</span>
                            <span class="text-sm font-medium text-gray-800">${escapeHtml(formatUserName(d))}</span>
                        </div>
                        <div class="flex items-center gap-2">
                            <span class="text-xs font-semibold text-gray-500 uppercase">Title:</span>
                            <span class="text-sm text-gray-700">${escapeHtml(d.title || "N/A")}</span>
                        </div>
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
                            <div>
                                <span class="text-xs font-semibold text-gray-500 uppercase block mb-1">Linked Project</span>
                                <p class="text-sm text-gray-700">${escapeHtml(d.linked_project_title || "None")}</p>
                            </div>
                            <div>
                                <span class="text-xs font-semibold text-gray-500 uppercase block mb-1">Location</span>
                                <p class="text-sm text-gray-700">${escapeHtml(d.location || "N/A")}</p>
                            </div>
                            <div>
                                <span class="text-xs font-semibold text-gray-500 uppercase block mb-1">Created</span>
                                <p class="text-sm text-gray-700">${escapeHtml(formatDisplayDate(d.created_at, true))}</p>
                            </div>
                        </div>
                        <div>
                            <span class="text-xs font-semibold text-gray-500 uppercase block mb-1">Content:</span>
                            <p class="text-sm text-gray-700 bg-white rounded-lg p-3 border border-gray-200 max-h-32 overflow-y-auto">${escapeHtml(d.content || "No content")}</p>
                        </div>
                        <div class="flex items-center gap-2">
                            <span class="text-xs font-semibold text-gray-500 uppercase">Status:</span>
                            <span class="text-sm font-medium ${d.status === "deleted" ? "text-red-600" : "text-gray-700"}">${escapeHtml((d.status || "").toUpperCase())}</span>
                        </div>
                        ${imagesHtml}
                    </div>
                </div>`;
        } else if (evidence.type === "project") {
            const filesHtml = renderAttachmentList(evidence.files || [], "file");
            const budget = d.budget_range_min && d.budget_range_max
                ? `${d.budget_range_min} - ${d.budget_range_max}`
                : "N/A";
            html = `
                <div class="bg-teal-50 border border-teal-200 rounded-xl p-4">
                    <h5 class="text-sm font-bold text-teal-800 mb-2 flex items-center gap-2">
                        <i class="fi fi-sr-building text-teal-600"></i> Reported Project
                    </h5>
                    <div class="space-y-2">
                        <div class="flex items-center gap-2">
                            <span class="text-xs font-semibold text-gray-500 uppercase">Owner:</span>
                            <span class="text-sm font-medium text-gray-800">${escapeHtml(formatUserName(d))}</span>
                        </div>
                        <div class="flex items-center gap-2">
                            <span class="text-xs font-semibold text-gray-500 uppercase">Project Title:</span>
                            <span class="text-sm text-gray-700">${escapeHtml(d.project_title || "N/A")}</span>
                        </div>
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
                            <div>
                                <span class="text-xs font-semibold text-gray-500 uppercase block mb-1">Category</span>
                                <p class="text-sm text-gray-700">${escapeHtml(d.category_name || d.if_others_ctype || "N/A")}</p>
                            </div>
                            <div>
                                <span class="text-xs font-semibold text-gray-500 uppercase block mb-1">Property Type</span>
                                <p class="text-sm text-gray-700">${escapeHtml(d.property_type || "N/A")}</p>
                            </div>
                            <div>
                                <span class="text-xs font-semibold text-gray-500 uppercase block mb-1">Created</span>
                                <p class="text-sm text-gray-700">${escapeHtml(formatDisplayDate(d.relationship_created_at, true))}</p>
                            </div>
                            <div>
                                <span class="text-xs font-semibold text-gray-500 uppercase block mb-1">Budget Range</span>
                                <p class="text-sm text-gray-700">${escapeHtml(budget)}</p>
                            </div>
                            <div>
                                <span class="text-xs font-semibold text-gray-500 uppercase block mb-1">Location</span>
                                <p class="text-sm text-gray-700">${escapeHtml(d.project_location || "N/A")}</p>
                            </div>
                            <div>
                                <span class="text-xs font-semibold text-gray-500 uppercase block mb-1">Status</span>
                                <p class="text-sm font-medium text-gray-700">${escapeHtml((d.project_status || "").toUpperCase().replace(/_/g, " "))}</p>
                            </div>
                        </div>
                        <div>
                            <span class="text-xs font-semibold text-gray-500 uppercase block mb-1">Description:</span>
                            <p class="text-sm text-gray-700 bg-white rounded-lg p-3 border border-gray-200">${escapeHtml(d.project_description || "No project description available.")}</p>
                        </div>
                        ${filesHtml}
                    </div>
                </div>`;
        } else if (evidence.type === "dispute") {
            const accused = d.accused;
            const project = d.project;
            const files = Array.isArray(evidence.files) ? evidence.files : [];
            const filesHtml = renderAttachmentList(files, "file");
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
                            <span class="text-sm text-gray-700">${escapeHtml(project.project_title || "N/A")} (${escapeHtml((project.project_status || "").toUpperCase())})</span>
                        </div>` : ""}
                        <div>
                            <span class="text-xs font-semibold text-gray-500 uppercase block mb-1">Dispute Type:</span>
                            <p class="text-sm text-gray-800 font-medium">${escapeHtml(d.dispute_type || "N/A")}</p>
                        </div>
                        <div>
                            <span class="text-xs font-semibold text-gray-500 uppercase block mb-1">Description:</span>
                            <p class="text-sm text-gray-700 bg-white rounded-lg p-3 border border-gray-200">${escapeHtml(d.dispute_desc || "No description")}</p>
                        </div>
                        ${filesHtml}
                    </div>
                </div>`;
        } else if (evidence.type === "user_report") {
            const reportedUser = d.reported_user || null;
            html = `
                <div class="bg-cyan-50 border border-cyan-200 rounded-xl p-4">
                    <h5 class="text-sm font-bold text-cyan-800 mb-2 flex items-center gap-2">
                        <i class="fi fi-sr-user text-cyan-600"></i> Reported User
                    </h5>
                    <div class="space-y-2">
                        <div class="flex items-center gap-2">
                            <span class="text-xs font-semibold text-gray-500 uppercase">Reported User:</span>
                            <span class="text-sm font-medium text-gray-800">${escapeHtml(formatUserName(reportedUser))}</span>
                        </div>
                        <div class="flex items-center gap-2">
                            <span class="text-xs font-semibold text-gray-500 uppercase">Role:</span>
                            <span class="text-sm text-gray-700">${escapeHtml(((reportedUser?.user_type || "N/A").replace(/_/g, " ")).toUpperCase())}</span>
                        </div>
                        <div>
                            <span class="text-xs font-semibold text-gray-500 uppercase block mb-1">Report Description:</span>
                            <p class="text-sm text-gray-700 bg-white rounded-lg p-3 border border-gray-200">${escapeHtml(d.description || "No additional details provided.")}</p>
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
    // MARK UNDER REVIEW FLOW
    // ══════════════════════════════════════════════════════
    document.getElementById("btnUnderReviewReport")?.addEventListener("click", async () => {
        if (!currentReport || currentReport.source === "dispute") return;

        try {
            const res = await fetch(
                `/admin/global-management/report-management/${encodeURIComponent(currentReport.source)}/${encodeURIComponent(currentReport.id)}/status`,
                {
                    method: "POST",
                    headers: getHeaders(),
                    body: JSON.stringify({ status: "under_review", admin_notes: "Marked under review by admin" }),
                }
            );

            const json = await res.json();
            if (!json.success) {
                toast(json.message || "Failed to update report status", "error");
                return;
            }

            toast("Report marked under review", "success");
            closeAllModals();
            fetchReports();
        } catch (e) {
            console.error(e);
            toast("Error updating report status", "error");
        }
    });

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
            const isDispute = currentReport.source === "dispute";
            const url = isDispute
                ? `/admin/project-management/disputes/${encodeURIComponent(currentReport.id)}/reject`
                : `/admin/global-management/report-management/${encodeURIComponent(currentReport.source)}/${encodeURIComponent(currentReport.id)}/dismiss`;

            const payload = isDispute ? { reason } : { reason };

            const res = await fetch(url, {
                method: "POST",
                headers: getHeaders(),
                body: JSON.stringify(payload),
            });
            const json = await res.json();
            if (json.success) {
                toast(isDispute ? "Dispute dismissed successfully" : "Report dismissed successfully", "success");
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
    // RESOLVE CASE FLOW → RESOLUTION ACTION MODAL
    // ══════════════════════════════════════════════════════
    document.getElementById("btnConfirmReport")?.addEventListener("click", async () => {
        if (!currentReport) {
            return;
        }

        try {
            const res = await fetch(
                `/admin/global-management/report-management/${encodeURIComponent(currentReport.source)}/${encodeURIComponent(currentReport.id)}/status`,
                {
                    method: "POST",
                    headers: getHeaders(),
                    body: JSON.stringify({ status: "resolved", admin_notes: "Resolved by admin from Global Report Management." }),
                }
            );
            const json = await res.json();
            if (!json.success) {
                toast(json.message || "Failed to resolve case", "error");
                return;
            }

            currentReport.status = "resolved";
            document.getElementById("modalStatus").innerHTML = getStatusBadge("resolved");
            fetchReports();

            closeAllModals();
            await openResolutionActionModal();
        } catch (e) {
            console.error(e);
            toast("Error resolving case", "error");
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
        ["viewReportModal", "dismissConfirmModal", "suspensionModal", "resolutionActionModal", "hidePostModal", "hideReviewModal", "removeReviewModal"].forEach((id) => {
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

    document.querySelectorAll('input[name="resolutionActionType"]').forEach((input) => {
        input.addEventListener("change", toggleResolutionActionDate);
    });

    document.getElementById("confirmResolutionActionBtn")?.addEventListener("click", async function () {
        if (!currentReport || currentReport.status !== "resolved") {
            toast("Resolve the case first before applying a resolution action", "error");
            return;
        }

        if (!currentReport.reported_user_id) {
            toast("Cannot determine reported user for this case", "error");
            return;
        }

        const actionType = document.querySelector('input[name="resolutionActionType"]:checked')?.value || "warning";
        const reason = document.getElementById("resolutionActionReason")?.value?.trim();
        const banUntil = document.getElementById("resolutionBanUntil")?.value || null;

        if (!reason) {
            toast("Please provide the action reason", "error");
            return;
        }

        if (actionType === "temporary_ban" && !banUntil) {
            toast("Please select a ban end date", "error");
            return;
        }

        this.disabled = true;
        const original = this.textContent;
        this.textContent = "Processing...";

        try {
            const res = await fetch(
                `/admin/global-management/report-management/${encodeURIComponent(currentReport.source)}/${encodeURIComponent(currentReport.id)}/resolution-action`,
                {
                    method: "POST",
                    headers: getHeaders(),
                    body: JSON.stringify({
                        action_type: actionType,
                        reason,
                        ban_until: banUntil,
                        reported_user_id: currentReport.reported_user_id,
                    }),
                }
            );

            const json = await res.json();
            if (!json.success) {
                toast(json.message || "Failed to apply resolution action", "error");
                return;
            }

            toast("Resolution action applied successfully", "success");
            closeAllModals();
            fetchReports();
        } catch (e) {
            console.error(e);
            toast("Error applying resolution action", "error");
        } finally {
            this.disabled = false;
            this.textContent = original;
        }
    });

    // ══════════════════════════════════════════════════════
    // FILTERS
    // ══════════════════════════════════════════════════════
    ["filterSource", "filterStatus", "filterDateFrom", "filterDateTo"].forEach((id) => {
        const el = document.getElementById(id);
        if (el) el.addEventListener("change", () => {
            moderationCurrentPage = 1;
            fetchReports();
        });
    });

    document.getElementById("filterCaseType")?.addEventListener("change", () => {
        moderationCurrentPage = 1;
        fetchReports();
    });

    let searchTimer;
    const searchEl = document.getElementById("filterSearch");
    if (searchEl) {
        searchEl.addEventListener("input", () => {
            clearTimeout(searchTimer);
            moderationCurrentPage = 1;
            searchTimer = setTimeout(fetchReports, 400);
        });
    }

    document.getElementById("resetFilters")?.addEventListener("click", () => {
        ["filterSource", "filterStatus", "filterCaseType"].forEach((id) => {
            const el = document.getElementById(id); if (el) el.value = "all";
        });
        ["filterDateFrom", "filterDateTo", "filterSearch"].forEach((id) => {
            const el = document.getElementById(id); if (el) el.value = "";
        });
        moderationCurrentPage = 1;
        fetchReports();
    });

    document.getElementById("moderationPrevPage")?.addEventListener("click", () => {
        if (moderationCurrentPage > 1) {
            moderationCurrentPage--;
            fetchReports();
        }
    });

    document.getElementById("moderationNextPage")?.addEventListener("click", () => {
        if (moderationCurrentPage < moderationLastPage) {
            moderationCurrentPage++;
            fetchReports();
        }
    });

    // ══════════════════════════════════════════════════════
    // DISPUTE ACTIONS
    // ══════════════════════════════════════════════════════
    document.getElementById("btnReviewDispute")?.addEventListener("click", async () => {
        if (!currentReport || currentReport.source !== "dispute") return;
        await performDisputeAction("review");
    });

    document.getElementById("btnResolveDispute")?.addEventListener("click", async () => {
        if (!currentReport || currentReport.source !== "dispute") return;
        await performDisputeAction("resolve");
    });

    document.getElementById("btnRejectDispute")?.addEventListener("click", async () => {
        if (!currentReport || currentReport.source !== "dispute") return;
        await performDisputeAction("reject");
    });

    async function performDisputeAction(action) {
        if (!currentReport) return;

        let url = "";
        const payload = buildDisputePayload(action);
        if (payload === null) return;

        if (action === "review") {
            url = `/admin/project-management/disputes/${encodeURIComponent(currentReport.id)}/approve`;
        } else if (action === "resolve") {
            url = `/admin/project-management/disputes/${encodeURIComponent(currentReport.id)}/finalize`;
        } else {
            url = `/admin/project-management/disputes/${encodeURIComponent(currentReport.id)}/reject`;
        }

        try {
            const res = await fetch(url, {
                method: "POST",
                headers: getHeaders(),
                body: JSON.stringify(payload),
            });
            const json = await res.json();
            if (!json.success) {
                toast(json.message || "Failed to process dispute action", "error");
                return;
            }

            if (action === "resolve") {
                currentReport.status = "resolved";
                toast("Dispute resolved successfully", "success");
                closeAllModals();
                await openResolutionActionModal();
            } else {
                toast(action === "reject" ? "Dispute dismissed successfully" : "Dispute moved under review", "success");
                closeAllModals();
            }

            fetchReports();
        } catch (e) {
            console.error(e);
            toast("Error processing dispute action", "error");
        }
    }

    // ══════════════════════════════════════════════════════
    // TAB 3: DIRECT ADMIN ACTION — Search & Take Action
    // ══════════════════════════════════════════════════════
    let adminSearchTimer;
    let adminCurrentPage = 1;
    let adminLastPage = 1;
    let adminTotalResults = 0;
    let adminTabLoaded = false;
    const adminDirectItemCache = {
        showcase: new Map(),
        project: new Map(),
        review: new Map(),
    };
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
            else if (type === "showcase" || type === "post") renderAdminPostResults(thead, tbody, results);
            else if (type === "project") renderAdminProjectResults(thead, tbody, results);
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
        adminDirectItemCache.showcase.clear();
        const uniquePosts = Array.from(new Map((posts || []).map((p) => [String(p.post_id), p])).values());

        thead.innerHTML = `<tr>
            <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">ID</th>
            <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Title</th>
            <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Author</th>
            <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Status</th>
            <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Date</th>
            <th class="px-6 py-4 text-center text-xs font-semibold text-gray-600 uppercase tracking-wider">Actions</th>
        </tr>`;

        if (!uniquePosts.length) {
            tbody.innerHTML = `<tr><td colspan="6" class="px-6 py-12 text-center text-gray-500 text-sm">No posts found.</td></tr>`;
            return;
        }

        tbody.innerHTML = uniquePosts.map(p => {
            adminDirectItemCache.showcase.set(String(p.post_id), p);
            const date = p.created_at ? new Date(p.created_at).toLocaleDateString("en-US", { month: "short", day: "numeric", year: "numeric" }) : "-";
            return `<tr class="hover:bg-gray-50 transition border-b border-gray-100">
                <td class="px-6 py-4 text-sm font-mono text-gray-700">#${p.post_id}</td>
                <td class="px-6 py-4 text-sm font-medium text-gray-800 max-w-xs truncate" title="${escapeHtml(p.title || "")}">${escapeHtml(p.title || "Untitled")}</td>
                <td class="px-6 py-4 text-sm text-gray-600">${escapeHtml((p.first_name || "") + " " + (p.last_name || ""))} (@${escapeHtml(p.username || "")})</td>
                <td class="px-6 py-4"><span class="inline-flex px-3 py-1 rounded-full text-xs font-semibold bg-blue-100 text-blue-700">${(p.status || "").toUpperCase()}</span></td>
                <td class="px-6 py-4 text-sm text-gray-500">${date}</td>
                <td class="px-6 py-4 text-center">
                    <button class="px-4 py-2 mr-2 rounded-xl bg-gradient-to-r from-slate-600 to-slate-700 hover:from-slate-700 hover:to-slate-800 text-white text-xs font-semibold shadow-sm hover:shadow-md transition admin-view-item-btn" data-item-type="showcase" data-item-id="${p.post_id}">
                        <i class="fi fi-rr-eye mr-1"></i> View
                    </button>
                    ${p.status === 'deleted' ? `<span class="text-xs text-gray-400">Already hidden</span>` : `<button class="px-4 py-2 rounded-xl bg-gradient-to-r from-amber-600 to-orange-600 hover:from-amber-700 hover:to-orange-700 text-white text-xs font-semibold shadow-sm hover:shadow-md transition admin-hide-post-btn" data-post-id="${p.post_id}" data-post-title="${escapeHtml(p.title || 'Untitled')}" data-post-author="${escapeHtml((p.first_name || '') + ' ' + (p.last_name || ''))}">
                        <i class="fi fi-rr-eye-crossed mr-1"></i> Hide Post
                    </button>`}
                </td>
            </tr>`;
        }).join("");

        bindAdminViewButtons();
        bindAdminHidePostButtons();
    }

    function renderAdminProjectResults(thead, tbody, projects) {
        adminDirectItemCache.project.clear();

        thead.innerHTML = `<tr>
            <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">ID</th>
            <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Project Title</th>
            <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Owner</th>
            <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Location</th>
            <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Status</th>
            <th class="px-6 py-4 text-center text-xs font-semibold text-gray-600 uppercase tracking-wider">Actions</th>
        </tr>`;

        if (!projects.length) {
            tbody.innerHTML = `<tr><td colspan="6" class="px-6 py-12 text-center text-gray-500 text-sm">No project posts found.</td></tr>`;
            return;
        }

        tbody.innerHTML = projects.map(p => {
            adminDirectItemCache.project.set(String(p.project_id), p);
            const owner = `${p.first_name || ""} ${p.last_name || ""}`.trim();
            const normalizedStatus = String(p.project_status || "").toLowerCase();
            const isHidden = normalizedStatus === "deleted_post";

            return `<tr class="hover:bg-gray-50 transition border-b border-gray-100">
                <td class="px-6 py-4 text-sm font-mono text-gray-700">#${p.project_id}</td>
                <td class="px-6 py-4 text-sm font-medium text-gray-800 max-w-xs truncate" title="${escapeHtml(p.project_title || "")}">${escapeHtml(p.project_title || "Untitled")}</td>
                <td class="px-6 py-4 text-sm text-gray-600">${escapeHtml(owner || "Unknown")} (@${escapeHtml(p.username || "")})</td>
                <td class="px-6 py-4 text-sm text-gray-600 max-w-[180px] truncate" title="${escapeHtml(p.project_location || "")}">${escapeHtml(p.project_location || "N/A")}</td>
                <td class="px-6 py-4"><span class="inline-flex px-3 py-1 rounded-full text-xs font-semibold bg-blue-100 text-blue-700">${escapeHtml((p.project_status || "").toUpperCase().replace(/_/g, " "))}</span></td>
                <td class="px-6 py-4 text-center">
                    <button class="px-4 py-2 mr-2 rounded-xl bg-gradient-to-r from-slate-600 to-slate-700 hover:from-slate-700 hover:to-slate-800 text-white text-xs font-semibold shadow-sm hover:shadow-md transition admin-view-item-btn" data-item-type="project" data-item-id="${p.project_id}">
                        <i class="fi fi-rr-eye mr-1"></i> View
                    </button>
                    ${isHidden ? `<span class="text-xs text-gray-400">Already hidden</span>` : `<button class="px-4 py-2 rounded-xl bg-gradient-to-r from-amber-600 to-orange-600 hover:from-amber-700 hover:to-orange-700 text-white text-xs font-semibold shadow-sm hover:shadow-md transition admin-hide-post-btn admin-hide-project-btn" data-post-id="${p.project_id}" data-post-title="${escapeHtml(p.project_title || 'Untitled')}" data-post-author="${escapeHtml(owner || 'Unknown')}" data-hide-kind="project">
                        <i class="fi fi-rr-eye-crossed mr-1"></i> Hide Project
                    </button>`}
                </td>
            </tr>`;
        }).join("");

        bindAdminViewButtons();
        bindAdminHidePostButtons();
    }

    function renderAdminReviewResults(thead, tbody, reviews) {
        adminDirectItemCache.review.clear();

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
            adminDirectItemCache.review.set(String(r.review_id), r);
            const stars = "★".repeat(r.rating || 0) + "☆".repeat(5 - (r.rating || 0));
            return `<tr class="hover:bg-gray-50 transition border-b border-gray-100">
                <td class="px-6 py-4 text-sm font-mono text-gray-700">#${r.review_id}</td>
                <td class="px-6 py-4 text-sm text-gray-700 max-w-[150px] truncate" title="${escapeHtml(r.project_title || "")}">${escapeHtml(r.project_title || "N/A")}</td>
                <td class="px-6 py-4 text-sm text-gray-600">${escapeHtml((r.reviewer_first_name || "") + " " + (r.reviewer_last_name || ""))} (@${escapeHtml(r.reviewer_username || "")})</td>
                <td class="px-6 py-4 text-sm text-amber-600 font-bold">${stars}</td>
                <td class="px-6 py-4 text-sm text-gray-600 max-w-xs truncate" title="${escapeHtml(r.comment || "")}">${escapeHtml(r.comment || "No comment")}</td>
                <td class="px-6 py-4 text-center">
                    <button class="px-3 py-2 mr-2 rounded-xl bg-gradient-to-r from-slate-600 to-slate-700 hover:from-slate-700 hover:to-slate-800 text-white text-xs font-semibold shadow-sm hover:shadow-md transition admin-view-item-btn" data-item-type="review" data-item-id="${r.review_id}">
                        <i class="fi fi-rr-eye mr-1"></i> View
                    </button>
                    ${r.is_deleted ? `<span class="text-xs text-gray-400">Already hidden</span>` : `<button class="px-3 py-2 mr-2 rounded-xl bg-gradient-to-r from-amber-600 to-orange-600 hover:from-amber-700 hover:to-orange-700 text-white text-xs font-semibold shadow-sm hover:shadow-md transition admin-hide-review-btn" data-review-id="${r.review_id}" data-review-author="${escapeHtml((r.reviewer_first_name || '') + ' ' + (r.reviewer_last_name || ''))}" data-review-rating="${"★".repeat(r.rating || 0) + "☆".repeat(5 - (r.rating || 0))}">
                        <i class="fi fi-rr-eye-crossed mr-1"></i> Hide Review
                    </button>
                    <button class="px-3 py-2 rounded-xl bg-gradient-to-r from-red-600 to-rose-600 hover:from-red-700 hover:to-rose-700 text-white text-xs font-semibold shadow-sm hover:shadow-md transition admin-remove-review-btn" data-review-id="${r.review_id}">
                        <i class="fi fi-rr-trash mr-1"></i> Remove
                    </button>`}
                </td>
            </tr>`;
        }).join("");

        bindAdminViewButtons();
        bindAdminHideReviewButtons();
        bindAdminRemoveReviewButtons();
    }

    function openAdminItemViewModal(itemType, item) {
        if (!item) return;

        currentReport = null;
        currentDirectPreviewItem = {
            type: itemType,
            id: itemType === "showcase" ? item.post_id : itemType === "project" ? item.project_id : item.review_id,
            item,
        };

        const sourceLabel = itemType === "showcase"
            ? "Showcase"
            : itemType === "project"
                ? "Project"
                : "Review";

        const contentTypeLabel = itemType === "showcase"
            ? "Showcase"
            : itemType === "project"
                ? "Project"
                : "Review";

        const itemId = itemType === "showcase"
            ? item.post_id
            : itemType === "project"
                ? item.project_id
                : item.review_id;

        document.getElementById("modalCaseId").textContent = `Direct Admin View — ${sourceLabel} #${itemId}`;
        document.getElementById("modalSource").textContent = sourceLabel;
        document.getElementById("modalReason").textContent = "Direct Admin Preview";
        document.getElementById("modalContentType").textContent = contentTypeLabel;
        document.getElementById("modalDate").textContent = formatDisplayDate(item.created_at);
        document.getElementById("modalAdminNotesWrap").classList.add("hidden");

        const overviewStatus = itemType === "showcase"
            ? (item.status || "unknown")
            : itemType === "project"
                ? (item.project_status || "unknown")
                : ((item.is_deleted ? "deleted" : "active"));

        document.getElementById("modalStatus").innerHTML = `<span class="inline-flex px-3 py-1 rounded-full text-xs font-semibold bg-gray-100 text-gray-700">${escapeHtml(String(overviewStatus).toUpperCase().replace(/_/g, " "))}</span>`;

        if (itemType === "showcase") {
            document.getElementById("modalReporter").textContent = `${(item.first_name || "")} ${(item.last_name || "")}`.trim() || "Unknown";
            document.getElementById("modalDetails").textContent = item.content || "No showcase content provided.";
            renderEvidence({ type: "showcase", data: item, images: [] });
        } else if (itemType === "project") {
            document.getElementById("modalReporter").textContent = `${(item.first_name || "")} ${(item.last_name || "")}`.trim() || "Unknown";
            document.getElementById("modalDetails").textContent = item.project_description || "No project description provided.";
            renderEvidence({ type: "project", data: item, files: [] });
        } else {
            document.getElementById("modalReporter").textContent = `${(item.reviewer_first_name || "")} ${(item.reviewer_last_name || "")}`.trim() || "Unknown";
            document.getElementById("modalDetails").textContent = item.comment || "No review comment provided.";
            renderEvidence({ type: "review", data: item });
        }

        const reportBtns = document.getElementById("modalActionBtns");
        const disputeBtns = document.getElementById("modalDisputeActionBtns");
        const directBtns = document.getElementById("modalDirectActionBtns");
        const directHideBtn = document.getElementById("btnDirectHide");
        const directUnhideBtn = document.getElementById("btnDirectUnhide");
        const directRemoveReviewBtn = document.getElementById("btnDirectRemoveReview");

        const isHidden = itemType === "showcase"
            ? String(item.status || "").toLowerCase() === "deleted"
            : itemType === "project"
                ? String(item.project_status || "").toLowerCase() === "deleted_post"
                : !!item.is_deleted;

        if (reportBtns) reportBtns.classList.add("hidden");
        if (disputeBtns) {
            disputeBtns.classList.add("hidden");
            disputeBtns.classList.remove("flex");
        }
        if (directBtns) {
            directBtns.classList.remove("hidden");
            directBtns.classList.add("flex");
        }
        if (directHideBtn) {
            directHideBtn.textContent = itemType === "project" ? "Hide Project" : itemType === "review" ? "Hide Review" : "Hide Post";
            directHideBtn.classList.toggle("hidden", isHidden);
        }
        if (directUnhideBtn) {
            directUnhideBtn.textContent = itemType === "project" ? "Unhide Project" : itemType === "review" ? "Unhide Review" : "Unhide Post";
            directUnhideBtn.classList.toggle("hidden", !isHidden);
        }
        if (directRemoveReviewBtn) {
            directRemoveReviewBtn.classList.toggle("hidden", itemType !== "review");
        }

        const viewModal = document.getElementById("viewReportModal");
        if (viewModal) {
            viewModal.classList.remove("hidden");
            viewModal.classList.add("flex");
        }
    }

    function bindAdminViewButtons() {
        document.querySelectorAll(".admin-view-item-btn").forEach((btn) => {
            btn.onclick = function () {
                const itemType = this.dataset.itemType;
                const itemId = String(this.dataset.itemId || "");
                if (!itemType || !itemId) return;

                const item = adminDirectItemCache[itemType]?.get(itemId) || null;
                if (!item) {
                    toast("Could not load item details", "error");
                    return;
                }

                openAdminItemViewModal(itemType, item);
            };
        });
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
    let pendingHidePostKind = "showcase";

    function bindAdminHidePostButtons() {
        document.querySelectorAll(".admin-hide-post-btn").forEach(btn => {
            btn.onclick = function () {
                pendingHidePostId = this.dataset.postId;
                pendingHidePostKind = this.dataset.hideKind || "showcase";
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
            const actionType = pendingHidePostKind === "project" ? "hide_project" : "hide_post";
            const res = await fetch("/admin/global-management/report-management/admin-action", {
                method: "POST",
                headers: getHeaders(),
                body: JSON.stringify({ action_type: actionType, target_id: pendingHidePostId, reason }),
            });
            const json = await res.json();
            if (json.success) {
                toast(pendingHidePostKind === "project" ? "Project hidden successfully. The owner has been notified." : "Post hidden successfully. The owner has been notified.", "success");
                closeAllModals();
                adminSearch();
            } else {
                toast(json.message || (pendingHidePostKind === "project" ? "Failed to hide project" : "Failed to hide post"), "error");
            }
        } catch (e) {
            console.error(e);
            toast(pendingHidePostKind === "project" ? "Error hiding project" : "Error hiding post", "error");
        } finally {
            btn.disabled = false;
            btn.innerHTML = origText;
        }
    });

    // ── Admin Action: Hide Review (modal-based) ──
    let pendingHideReviewId = null;

    let pendingRemoveReviewId = null;

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

    function bindAdminRemoveReviewButtons() {
        document.querySelectorAll(".admin-remove-review-btn").forEach(btn => {
            btn.onclick = function () {
                const reviewId = this.dataset.reviewId;
                const item = adminDirectItemCache.review.get(String(reviewId));
                pendingRemoveReviewId = reviewId;
                document.getElementById("removeReviewId").textContent = `#${reviewId}`;
                document.getElementById("removeReviewAuthor").textContent = item
                    ? `${item.reviewer_first_name || ""} ${item.reviewer_last_name || ""}`.trim() || "-"
                    : "-";
                document.getElementById("removeReviewRating").textContent = item
                    ? ("★".repeat(item.rating || 0) + "☆".repeat(5 - (item.rating || 0)))
                    : "-";
                document.getElementById("removeReviewReason").value = "";
                const modal = document.getElementById("removeReviewModal");
                if (modal) { modal.classList.remove("hidden"); modal.classList.add("flex"); }
            };
        });
    }

    document.getElementById("confirmRemoveReviewBtn")?.addEventListener("click", async function () {
        const reason = document.getElementById("removeReviewReason")?.value?.trim();
        if (!reason) { toast("Please provide a reason for removing this review", "error"); return; }
        if (!pendingRemoveReviewId) return;

        const btn = this;
        btn.disabled = true;
        const origText = btn.innerHTML;
        btn.innerHTML = '<i class="fi fi-sr-spinner animate-spin mr-1"></i> Removing...';

        try {
            const res = await fetch(`/admin/global-management/review-management/${encodeURIComponent(pendingRemoveReviewId)}/delete`, {
                method: "POST",
                headers: getHeaders(),
                body: JSON.stringify({ deletion_reason: reason }),
            });
            const json = await res.json();
            if (!json.success) {
                toast(json.message || "Failed to remove review", "error");
                return;
            }
            toast("Review removed successfully", "success");
            closeAllModals();
            adminSearch();
        } catch (e) {
            console.error(e);
            toast("Error removing review", "error");
        } finally {
            btn.disabled = false;
            btn.innerHTML = origText;
        }
    });

    async function applyDirectQuickAction(actionType, targetId, successMessage) {
        try {
            const res = await fetch("/admin/global-management/report-management/admin-action", {
                method: "POST",
                headers: getHeaders(),
                body: JSON.stringify({ action_type: actionType, target_id: targetId }),
            });
            const json = await res.json();
            if (!json.success) {
                toast(json.message || "Failed to apply action", "error");
                return;
            }

            toast(successMessage, "success");
            closeAllModals();
            adminSearch();
        } catch (e) {
            console.error(e);
            toast("Error applying action", "error");
        }
    }

    document.getElementById("btnDirectHide")?.addEventListener("click", () => {
        if (!currentDirectPreviewItem) return;

        if (currentDirectPreviewItem.type === "showcase" || currentDirectPreviewItem.type === "project") {
            pendingHidePostId = currentDirectPreviewItem.id;
            pendingHidePostKind = currentDirectPreviewItem.type === "project" ? "project" : "showcase";
            const item = currentDirectPreviewItem.item || {};
            document.getElementById("hidePostId").textContent = `#${currentDirectPreviewItem.id}`;
            document.getElementById("hidePostTitle").textContent = item.title || item.project_title || "-";
            document.getElementById("hidePostAuthor").textContent = `${item.first_name || ""} ${item.last_name || ""}`.trim() || "-";
            document.getElementById("hidePostReason").value = "";
            const modal = document.getElementById("hidePostModal");
            if (modal) { modal.classList.remove("hidden"); modal.classList.add("flex"); }
            return;
        }

        if (currentDirectPreviewItem.type === "review") {
            pendingHideReviewId = currentDirectPreviewItem.id;
            const item = currentDirectPreviewItem.item || {};
            document.getElementById("hideReviewId").textContent = `#${currentDirectPreviewItem.id}`;
            document.getElementById("hideReviewAuthor").textContent = `${item.reviewer_first_name || ""} ${item.reviewer_last_name || ""}`.trim() || "-";
            document.getElementById("hideReviewRating").textContent = "★".repeat(item.rating || 0) + "☆".repeat(5 - (item.rating || 0));
            document.getElementById("hideReviewReason").value = "";
            const modal = document.getElementById("hideReviewModal");
            if (modal) { modal.classList.remove("hidden"); modal.classList.add("flex"); }
        }
    });

    document.getElementById("btnDirectUnhide")?.addEventListener("click", async () => {
        if (!currentDirectPreviewItem) return;

        if (currentDirectPreviewItem.type === "showcase") {
            await applyDirectQuickAction("unhide_post", currentDirectPreviewItem.id, "Post restored successfully");
            return;
        }

        if (currentDirectPreviewItem.type === "project") {
            await applyDirectQuickAction("unhide_project", currentDirectPreviewItem.id, "Project restored successfully");
            return;
        }

        if (currentDirectPreviewItem.type === "review") {
            await applyDirectQuickAction("unhide_review", currentDirectPreviewItem.id, "Review restored successfully");
        }
    });

    document.getElementById("btnDirectRemoveReview")?.addEventListener("click", () => {
        if (!currentDirectPreviewItem || currentDirectPreviewItem.type !== "review") return;
        const item = currentDirectPreviewItem.item || {};
        pendingRemoveReviewId = currentDirectPreviewItem.id;
        document.getElementById("removeReviewId").textContent = `#${currentDirectPreviewItem.id}`;
        document.getElementById("removeReviewAuthor").textContent = `${item.reviewer_first_name || ""} ${item.reviewer_last_name || ""}`.trim() || "-";
        document.getElementById("removeReviewRating").textContent = "★".repeat(item.rating || 0) + "☆".repeat(5 - (item.rating || 0));
        document.getElementById("removeReviewReason").value = "";
        const modal = document.getElementById("removeReviewModal");
        if (modal) { modal.classList.remove("hidden"); modal.classList.add("flex"); }
    });

    // ── Init ──
    bindViewButtons();
    fetchReports();
})();
