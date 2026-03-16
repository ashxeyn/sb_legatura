(function () {
    "use strict";

    // ── State ──
    let currentReport = null; // { id, source, status, reported_user_id, required_action, action_completed, project_id }
    let currentDirectPreviewItem = null; // { type, id, item }
    let moderationCurrentPage = 1;
    let moderationLastPage = 1;
    let isOpeningReportModal = false;

    function animateRowsInWrap(wrap) {
        if (!wrap) return;
        const rows = Array.from(wrap.querySelectorAll('tbody tr')).filter(r => r.style.display !== 'none');
        rows.forEach((row, index) => {
            row.style.opacity = '0';
            row.style.transform = 'translateY(20px)';
            setTimeout(() => {
                row.style.transition = 'all 0.4s ease';
                row.style.opacity = '1';
                row.style.transform = 'translateY(0)';
            }, index * 50);
        });
}
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
            pending: "bg-amber-100 text-amber-700 border-amber-200",
            under_review: "bg-blue-100 text-blue-700 border-blue-200",
            resolved: "bg-emerald-100 text-emerald-700 border-emerald-200",
            dismissed: "bg-red-100 text-red-700 border-red-200",
        };
        const cls = map[status] || "bg-gray-100 text-gray-700 border-gray-200";
        const label = (status || "-").toUpperCase().replace(/_/g, " ");
        return `<span class="inline-flex items-center px-2 py-0.5 rounded-full text-[11px] font-semibold border ${cls}">${label}</span>`;
    }

    function getReportModalTheme(status) {
        const normalized = String(status || "").toLowerCase();
        const map = {
            pending: "pending",
            under_review: "under_review",
            resolved: "resolved",
            approved: "resolved",
            resumed: "resolved",
            dismissed: "dismissed",
            rejected: "dismissed",
            deleted: "dismissed",
            deleted_post: "dismissed",
            hidden: "dismissed",
            removed: "dismissed",
        };
        return map[normalized] || "default";
    }

    function applyViewModalTheme(status) {
        const modalCard = document.querySelector("#viewReportModal .report-view-modal");
        if (!modalCard) return;
        modalCard.dataset.statusTheme = getReportModalTheme(status);
    }

    function getSourceBadge(source) {
        const colors = {
            project: "bg-blue-100 text-blue-700 border-blue-200",
            showcase: "bg-teal-100 text-teal-700 border-teal-200",
            review: "bg-purple-100 text-purple-700 border-purple-200",
            user: "bg-cyan-100 text-cyan-700 border-cyan-200",
            dispute: "bg-orange-100 text-orange-700 border-orange-200",
        };
        const labels = { project: "Project", showcase: "Showcase", review: "Review", user: "User", dispute: "Dispute" };
        const cls = colors[source] || "bg-gray-100 text-gray-700 border-gray-200";
        return `<span class="inline-flex items-center px-2 py-0.5 rounded-full text-[11px] font-semibold border ${cls}">${labels[source] || source}</span>`;
    }

    function getCaseTypeBadge(type) {
        const colors = {
            report: "bg-indigo-100 text-indigo-700 border-indigo-200",
            dispute: "bg-orange-100 text-orange-700 border-orange-200",
        };
        const cls = colors[type] || "bg-gray-100 text-gray-700 border-gray-200";
        const label = (type || "-").charAt(0).toUpperCase() + (type || "-").slice(1);
        return `<span class="inline-flex items-center px-2 py-0.5 rounded-full text-[11px] font-semibold border ${cls}">${label}</span>`;
    }

    function getAdminActionBadge(adminAction) {
        const val = (adminAction || "").trim();
        if (!val || val === "-") {
            return `<span class="inline-flex items-center px-2 py-0.5 rounded-full text-[11px] font-semibold border bg-gray-100 text-gray-500 border-gray-200" title="">—</span>`;
        }
        const key = val.toLowerCase().replace(/\s+/g, "_");
        const colors = {
            warned: "bg-amber-100 text-amber-700 border-amber-200",
            terminated: "bg-red-100 text-red-700 border-red-200",
            halted: "bg-rose-100 text-rose-700 border-rose-200",
            suspended: "bg-red-100 text-red-700 border-red-200",
            resumed: "bg-emerald-100 text-emerald-700 border-emerald-200",
        };
        const cls = colors[key] || "bg-gray-100 text-gray-700 border-gray-200";
        const label = val.charAt(0).toUpperCase() + val.slice(1).toLowerCase();
        return `<span class="inline-flex items-center px-2 py-0.5 rounded-full text-[11px] font-semibold border ${cls}" title="${escapeHtml(val)}">${escapeHtml(label)}</span>`;
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

    function inferDisputeRequiredAction(report) {
        const disputeType = String(report.reason || report.dispute_type || "").toLowerCase();
        const requestedAction = String(report.requested_action || "").toLowerCase();
        if (disputeType === "halt" || requestedAction.includes("halt")) {
            return "halt_project";
        }
        return null;
    }

    function isDisputeProjectActionCompleted(report) {
        const projectStatus = String(report?.evidence?.data?.project?.project_status || "").toLowerCase();
        const requiredAction = inferDisputeRequiredAction(report);
        if (!requiredAction) return true;
        if (requiredAction === "halt_project") return projectStatus === "halt";
        return false;
    }

    async function fetchLinkedDisputeProject(disputeId) {
        const res = await fetch(
            `/admin/project-management/disputes/${encodeURIComponent(disputeId)}/linked-project`,
            { headers: { "X-Requested-With": "XMLHttpRequest" } }
        );
        const json = await res.json();
        if (!res.ok || !json.success) {
            throw new Error(json.message || "Failed to load linked project details");
        }
        return json.data || null;
    }

    function formatProjectStatusLabel(status) {
        const s = String(status || "").toLowerCase();
        return s ? s.replace(/_/g, " ").toUpperCase() : "-";
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

            const promptEl = document.getElementById("resolutionApprovalPrompt");
            if (promptEl) {
                if (currentReport.source === "review" || currentReport.content_type === "review") {
                    promptEl.textContent = "Are you sure you want to hide this review?";
                } else if (currentReport.source === "post" || currentReport.source === "content") {
                    promptEl.textContent = "Are you sure you want to hide this post?";
                } else {
                    promptEl.textContent = "Are you sure you want to approve this report?";
                }
            }

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
            if (panel) {
                panel.classList.remove("hidden");
                requestAnimationFrame(() => animateRowsInWrap(panel));
            }
        });
    });

    // ══════════════════════════════════════════════════════
    // TAB 2: REPORTER STATS — client-side pagination
    // ══════════════════════════════════════════════════════
    const REPORTER_STATS_PER_PAGE = 10;
    let reporterStatsPage = 1;

    function getAllReporterRows() {
        return Array.from(document.querySelectorAll("#reporterStatsBody .reporter-row"));
    }

    function getFilteredReporterRows() {
        const filter = document.getElementById("historyFilter")?.value || "all";
        return getAllReporterRows().filter((row) => {
            if (filter === "super") return row.dataset.super === "1";
            if (filter === "abusers") return row.dataset.abuser === "1";
            return true;
        });
    }

    function renderReporterStatsPage() {
        const allRows = getAllReporterRows();
        const filtered = getFilteredReporterRows();
        const total = filtered.length;
        const lastPage = Math.max(1, Math.ceil(total / REPORTER_STATS_PER_PAGE));

        if (reporterStatsPage > lastPage) reporterStatsPage = lastPage;

        const from = total ? (reporterStatsPage - 1) * REPORTER_STATS_PER_PAGE + 1 : 0;
        const to = total ? Math.min(reporterStatsPage * REPORTER_STATS_PER_PAGE, total) : 0;

        // Hide all rows then show only the current page slice
        allRows.forEach((row) => (row.style.display = "none"));
        filtered.slice(from - 1, to).forEach((row) => (row.style.display = ""));

        const bar = document.getElementById("reporterStatsPaginationBar");
        const infoEl = document.getElementById("reporterStatsPaginationInfo");
        const indicator = document.getElementById("reporterStatsPageIndicator");
        const prevBtn = document.getElementById("reporterStatsPrevPage");
        const nextBtn = document.getElementById("reporterStatsNextPage");

        if (bar) bar.classList.toggle("hidden", total === 0);

        if (infoEl) {
            infoEl.innerHTML = total
                ? `Showing <strong>${from}</strong>–<strong>${to}</strong> of <strong>${total}</strong> reporters`
                : `Showing <strong>0</strong> reporters`;
        }
        if (indicator) indicator.textContent = `Page ${reporterStatsPage} of ${lastPage}`;
        if (prevBtn) prevBtn.disabled = reporterStatsPage <= 1;
        if (nextBtn) nextBtn.disabled = reporterStatsPage >= lastPage;
    }

    document.getElementById("historyFilter")?.addEventListener("change", () => {
        reporterStatsPage = 1;
        renderReporterStatsPage();
    });

    document.getElementById("reporterStatsPrevPage")?.addEventListener("click", () => {
        if (reporterStatsPage > 1) { reporterStatsPage--; renderReporterStatsPage(); }
    });

    document.getElementById("reporterStatsNextPage")?.addEventListener("click", () => {
        const lastPage = Math.max(1, Math.ceil(getFilteredReporterRows().length / REPORTER_STATS_PER_PAGE));
        if (reporterStatsPage < lastPage) { reporterStatsPage++; renderReporterStatsPage(); }
    });

    // Initial render for reporter stats
    renderReporterStatsPage();

    // ══════════════════════════════════════════════════════
    // FETCH & RENDER REPORTS TABLE
    // ══════════════════════════════════════════════════════
    async function fetchReports() {
        const params = new URLSearchParams();
        const sourceType = document.getElementById("filterSource")?.value || "all";
        const status = document.getElementById("filterStatus")?.value || "all";
        const search = document.getElementById("topNavSearch")?.value || "";
        const dateFrom = document.getElementById("filterDateFrom")?.value || "";
        const dateTo = document.getElementById("filterDateTo")?.value || "";

        if (sourceType !== "all") params.set("source_type", sourceType);
        if (status !== "all") params.set("status", status);
        if (search) params.set("search", search);
        if (dateFrom) params.set("date_from", dateFrom);
        if (dateTo) params.set("date_to", dateTo);
        params.set("page", moderationCurrentPage);
        params.set("per_page", 10);

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
            tbody.innerHTML = `<tr><td colspan="10" class="px-4 py-12 text-center text-gray-400"><i class="fi fi-sr-file-invoice text-3xl block mb-2"></i><p class="text-base font-medium text-gray-500">No moderation cases found</p><p class="text-xs mt-1">Try adjusting your search or filter criteria.</p></td></tr>`;
            return;
        }

        tbody.innerHTML = reports
            .map((r) => {
                const date = r.created_at
                    ? new Date(r.created_at).toLocaleDateString("en-US", { month: "short", day: "numeric", year: "numeric" })
                    : "-";
                return `<tr class="hover:bg-indigo-50/60 transition-colors" data-id="${r.case_ref_id}" data-source="${r.source}" data-case-type="${r.case_type}" data-status="${r.status}">
                    <td class="px-2.5 py-2.5 text-[11px] font-mono text-gray-700 truncate" title="${escapeHtml(r.case_id || "")}">${escapeHtml(r.case_id || "-")}</td>
                    <td class="px-2.5 py-2.5">${getCaseTypeBadge(r.case_type)}</td>
                    <td class="px-2.5 py-2.5">${getSourceBadge(r.source_type)}</td>
                    <td class="px-2.5 py-2.5 text-[11px] text-gray-700 font-medium truncate" title="${escapeHtml(r.reporter || "")}">${escapeHtml(r.reporter || "-")}</td>
                    <td class="px-2.5 py-2.5 text-[11px] text-gray-600 truncate" title="${escapeHtml(r.target || "")}">${escapeHtml(r.target || "-")}</td>
                    <td class="px-2.5 py-2.5 text-[11px] text-gray-600 truncate" title="${escapeHtml(r.reason || "")}">${escapeHtml(r.reason || "-")}</td>
                    <td class="px-2.5 py-2.5 whitespace-nowrap">${getStatusBadge(r.status)}</td>
                    <td class="px-2.5 py-2.5">${getAdminActionBadge(r.admin_action)}</td>
                    <td class="px-2.5 py-2.5 text-[11px] text-gray-500 whitespace-nowrap">${date}</td>
                    <td class="px-2.5 py-2.5 text-center">
                        <button class="action-btn view-btn w-8 h-8 inline-flex items-center justify-center p-1.5 rounded-xl border border-indigo-200 bg-indigo-50 text-indigo-600 shadow-[0_2px_8px_rgba(0,0,0,0.06)] hover:bg-indigo-100 hover:shadow-sm hover:border-indigo-300 hover:-translate-y-0.5 transition-all view-report-btn" title="View" data-id="${r.case_ref_id}" data-source="${r.source}" data-case-type="${r.case_type}" data-status="${escapeHtml(r.status || "")}">
                            <i class="fi fi-rr-eye text-[13px] leading-none"></i>
                        </button>
                    </td>
                </tr>`;
            })
            .join("");
        animateRowsInWrap(tbody);
    }

    function bindModerationTableDelegates() {
        const tbody = document.getElementById("reportsTableBody");
        if (!tbody) return;

        tbody.addEventListener("click", async (event) => {
            const button = event.target.closest(".view-report-btn");
            if (!button || !tbody.contains(button)) return;

            event.preventDefault();

            if (isOpeningReportModal) return;
            isOpeningReportModal = true;

            try {
                const row = button.closest("tr[data-status]");
                const initialStatus = button.dataset.status || (row ? row.dataset.status : null);
                await openViewModal(button.dataset.source, button.dataset.id, button.dataset.caseType, initialStatus);
            } finally {
                isOpeningReportModal = false;
            }
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
        const perPage = pagination.per_page || 10;
        const lastPage = pagination.last_page || 1;

        moderationCurrentPage = page;
        moderationLastPage = lastPage;

        const from = total ? (page - 1) * perPage + 1 : 0;
        const to = total ? Math.min(page * perPage, total) : 0;

        if (info) info.innerHTML = total ? `Showing <strong>${from}</strong>–<strong>${to}</strong> of <strong>${total}</strong> cases` : "No cases found";
        if (indicator) indicator.textContent = `Page ${page} of ${lastPage}`;
        if (prev) prev.disabled = page <= 1;
        if (next) next.disabled = page >= lastPage;
    }

    function updateModerationRowStatus(reportId, status) {
        const targetId = String(reportId);
        const rows = document.querySelectorAll("#reportsTableBody tr[data-id]");
        const row = Array.from(rows).find((item) => String(item.dataset.id) === targetId);
        if (!row) return;

        row.dataset.status = status;
        const statusCell = row.children[6];
        if (statusCell) {
            statusCell.innerHTML = getStatusBadge(status);
        }
    }

    function hideAllModalActionGroups() {
        const reportBtns = document.getElementById("modalActionBtns");
        const disputeBtns = document.getElementById("modalDisputeActionBtns");
        const directBtns = document.getElementById("modalDirectActionBtns");

        if (reportBtns) {
            reportBtns.classList.add("hidden");
        }
        if (disputeBtns) {
            disputeBtns.classList.add("hidden");
            disputeBtns.classList.remove("flex");
        }
        if (directBtns) {
            directBtns.classList.add("hidden");
            directBtns.classList.remove("flex");
        }
    }

    // ══════════════════════════════════════════════════════
    // VIEW REPORT MODAL — load detail with evidence
    // ══════════════════════════════════════════════════════
    async function openViewModal(source, reportId, caseType = null, initialStatus = null) {
        currentReport = null;
        hideAllModalActionGroups();
        applyViewModalTheme(initialStatus || "default");

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
        document.getElementById("disputeWorkflowSection")?.classList.add("hidden");

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
                content_type: String(r.content_type || "").toLowerCase(),
                dispute_type: String(r.dispute_type || r.reason || "").toLowerCase(),
                reported_user_id: inferReportedUserId(r),
                required_action: source === "dispute" ? inferDisputeRequiredAction(r) : null,
                action_completed: source === "dispute" ? isDisputeProjectActionCompleted(r) : true,
                project_id: r?.evidence?.data?.project?.project_id || null,
            };

            if (source !== "dispute" && String(r.status || "").toLowerCase() === "pending") {
                await autoMoveCaseToUnderReview(source, reportId);
                r.status = "under_review";
                currentReport.status = "under_review";
            }

            if (source === "dispute" && String(r.status || "").toLowerCase() === "pending") {
                const moved = await autoMoveDisputeToUnderReview(reportId);
                if (moved) {
                    r.status = "under_review";
                    currentReport.status = "under_review";
                }
            }

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
            applyViewModalTheme(r.status);
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

            if (source === "dispute") {
                await renderDisputeWorkflow(r);
            }

            // Show/hide action buttons based on status
            configureCaseActionButtons(source, r.status);
        } catch (e) {
            console.error(e);
            toast("Error loading report details", "error");
        }
    }

    async function autoMoveCaseToUnderReview(source, reportId) {
        try {
            const res = await fetch(
                `/admin/global-management/report-management/${encodeURIComponent(source)}/${encodeURIComponent(reportId)}/status`,
                {
                    method: "POST",
                    headers: getHeaders(),
                    body: JSON.stringify({
                        status: "under_review",
                        admin_notes: "Automatically moved to under review when viewed by admin.",
                    }),
                }
            );
            const json = await res.json();
            if (json.success) {
                updateModerationRowStatus(reportId, "under_review");
            }
        } catch (error) {
            console.warn("Auto under-review update failed:", error);
        }
    }

    async function autoMoveDisputeToUnderReview(disputeId) {
        try {
            const res = await fetch(
                `/admin/project-management/disputes/${encodeURIComponent(disputeId)}/approve`,
                {
                    method: "POST",
                    headers: getHeaders(),
                    body: JSON.stringify({}),
                }
            );
            const json = await res.json();
            if (json.success) {
                updateModerationRowStatus(disputeId, "under_review");
                return true;
            }
        } catch (error) {
            console.warn("Auto dispute under-review update failed:", error);
        }
        return false;
    }

    function configureCaseActionButtons(source, status) {
        const reportBtns = document.getElementById("modalActionBtns");
        const underReviewBtn = document.getElementById("btnUnderReviewReport");
        const disputeBtns = document.getElementById("modalDisputeActionBtns");
        const directBtns = document.getElementById("modalDirectActionBtns");
        const reviewBtn = document.getElementById("btnReviewDispute");
        const resolveBtn = document.getElementById("btnResolveDispute");
        const rejectBtn = document.getElementById("btnRejectDispute");
        const disputeWorkflowSection = document.getElementById("disputeWorkflowSection");

        const actionable = status === "pending" || status === "under_review";

        if (source === "dispute") {
            const isHaltDispute = currentReport?.required_action === "halt_project";

            if (directBtns) {
                directBtns.classList.add("hidden");
                directBtns.classList.remove("flex");
            }
            if (reportBtns) reportBtns.classList.add("hidden");
            if (disputeBtns) {
                disputeBtns.classList.toggle("hidden", !actionable);
                disputeBtns.classList.toggle("flex", actionable);
            }
            if (disputeWorkflowSection) {
                const shouldShowWorkflow = status === "under_review" || status === "resolved" || status === "dismissed";
                disputeWorkflowSection.classList.toggle("hidden", !shouldShowWorkflow);
            }

            if (reviewBtn) reviewBtn.classList.toggle("hidden", status !== "pending");
            if (resolveBtn) {
                if (isHaltDispute) {
                    const canHalt = status === "under_review" && !(currentReport?.action_completed);
                    resolveBtn.classList.toggle("hidden", status !== "under_review");
                    resolveBtn.disabled = !canHalt;
                    resolveBtn.classList.toggle("opacity-60", !canHalt);
                    resolveBtn.title = canHalt ? "" : "Project action already completed for this dispute.";
                    resolveBtn.innerHTML = '<i class="fi fi-rr-briefcase mr-1"></i> Halt Project';
                    resolveBtn.classList.remove("from-emerald-600", "to-teal-600", "hover:from-emerald-700", "hover:to-teal-700");
                    resolveBtn.classList.add("from-red-600", "to-rose-600", "hover:from-red-700", "hover:to-rose-700");
                } else {
                    const canResolve = status === "under_review" && !!(currentReport?.action_completed);
                    resolveBtn.classList.toggle("hidden", status !== "under_review");
                    resolveBtn.disabled = !canResolve;
                    resolveBtn.classList.toggle("opacity-60", !canResolve);
                    resolveBtn.title = canResolve ? "" : "Complete the required project action first.";
                    resolveBtn.innerHTML = '<i class="fi fi-rr-check mr-1"></i> Approve';
                    resolveBtn.classList.remove("from-red-600", "to-rose-600", "hover:from-red-700", "hover:to-rose-700");
                    resolveBtn.classList.add("from-emerald-600", "to-teal-600", "hover:from-emerald-700", "hover:to-teal-700");
                }
            }
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
        if (disputeWorkflowSection) {
            disputeWorkflowSection.classList.add("hidden");
        }
        if (reportBtns) {
            reportBtns.classList.toggle("hidden", !actionable);
        }
        if (underReviewBtn) {
            underReviewBtn.classList.add("hidden");
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

    async function renderDisputeWorkflow(report) {
        const section = document.getElementById("disputeWorkflowSection");
        if (!section) return;

        let project = report?.evidence?.data?.project || null;
        try {
            const linked = await fetchLinkedDisputeProject(currentReport?.id || report?.report_id);
            if (linked?.project) {
                project = linked.project;
                if (report?.evidence?.data) report.evidence.data.project = project;
            }
        } catch (err) {
            console.warn("Linked project fetch failed:", err);
        }

        const requiredAction = inferDisputeRequiredAction(report);
        const actionCompleted = isDisputeProjectActionCompleted(report);
        const status = String(report?.status || "").toLowerCase();

        const projectTitleEl = document.getElementById("disputeProjectTitle");
        const projectIdEl = document.getElementById("disputeProjectId");
        const projectStatusEl = document.getElementById("disputeProjectStatus");
        const projectOwnerEl = document.getElementById("disputeProjectOwner");
        const projectContractorEl = document.getElementById("disputeProjectContractor");
        const projectBudgetEl = document.getElementById("disputeProjectBudget");
        const projectTimelineEl = document.getElementById("disputeProjectTimeline");
        const requiredActionEl = document.getElementById("disputeRequiredAction");
        const actionStateEl = document.getElementById("disputeActionState");
        const disputeSubjectField = document.getElementById("disputeSubjectField");
        const disputeRequestedActionField = document.getElementById("disputeRequestedActionField");
        const disputeDescriptionField = document.getElementById("disputeDescriptionField");
        const disputeInitialProofsList = document.getElementById("disputeInitialProofsList");
        const disputeResubmittedPanel = document.getElementById("disputeResubmittedPanel");
        const actionForm = document.getElementById("disputeProjectActionForm");
        const actionBtn = document.getElementById("btnApplyDisputeProjectAction");
        const resolvedActionsWrap = document.getElementById("disputeResolvedProjectActions");
        const resumeBtn = document.getElementById("btnResumeDisputeProject");
        const terminateBtn = document.getElementById("btnTerminateDisputeProject");

        if (projectTitleEl) projectTitleEl.textContent = project?.project_title || "No linked project";
        if (projectIdEl) projectIdEl.textContent = project?.project_id ? `Project ID: ${project.project_id}` : "-";
        if (projectStatusEl) {
            projectStatusEl.textContent = formatProjectStatusLabel(project?.project_status);
        }
        if (projectOwnerEl) projectOwnerEl.textContent = project?.owner_name || "-";
        if (projectContractorEl) projectContractorEl.textContent = project?.contractor_name || "Not assigned";
        if (projectBudgetEl) {
            const min = project?.budget_range_min;
            const max = project?.budget_range_max;
            projectBudgetEl.textContent = (min != null && max != null)
                ? `PHP ${Number(min).toLocaleString()} - PHP ${Number(max).toLocaleString()}`
                : "-";
        }
        if (projectTimelineEl) projectTimelineEl.textContent = project?.to_finish ? `${project.to_finish} days` : "-";

        if (requiredActionEl) {
            requiredActionEl.textContent = requiredAction === "halt_project"
                ? "Halt Project"
                : "No project action required";
        }

        if (actionStateEl) {
            if (!requiredAction) {
                actionStateEl.textContent = "Not required";
            } else if (actionCompleted) {
                actionStateEl.textContent = "Completed";
            } else {
                actionStateEl.textContent = "Pending";
            }
        }

        if (disputeSubjectField) {
            disputeSubjectField.textContent = report?.dispute_subject || report?.reason || "-";
        }
        if (disputeRequestedActionField) {
            disputeRequestedActionField.textContent = report?.requested_action || "-";
        }
        if (disputeDescriptionField) {
            disputeDescriptionField.textContent = report?.details || "-";
        }
        if (disputeInitialProofsList) {
            const proofs = Array.isArray(report?.evidence_files) ? report.evidence_files : [];
            disputeInitialProofsList.innerHTML = proofs.length
                ? proofs
                    .map((file) => {
                        const name = escapeHtml(file?.file_name || "Initial proof");
                        const path = file?.file_path ? toStorageUrl(file.file_path) : "#";
                        return `<a href="${path}" target="_blank" class="flex items-center justify-between rounded-lg border border-gray-200 bg-gray-50 px-3 py-2 hover:border-indigo-300 hover:bg-indigo-50 transition"><span>${name}</span><i class="fi fi-rr-arrow-up-right-from-square text-gray-400"></i></a>`;
                    })
                    .join("")
                : '<p class="text-sm text-gray-500">No initial proofs uploaded.</p>';
        }
        if (disputeResubmittedPanel) {
            const resubmissions = Array.isArray(report?.resubmissions) ? report.resubmissions : [];
            disputeResubmittedPanel.innerHTML = resubmissions.length
                ? resubmissions
                    .map((entry) => {
                        const statusText = escapeHtml(String(entry?.progress_status || "Unknown").replace(/_/g, " "));
                        const dateText = entry?.submitted_at ? formatDisplayDate(entry.submitted_at, true) : "No submission date";
                        const files = Array.isArray(entry?.files) ? entry.files : [];
                        const fileLinks = files.length
                            ? `<div class="mt-2 space-y-1">${files
                                .map((file) => {
                                    const name = escapeHtml(file?.file_name || "Attachment");
                                    const path = file?.file_path ? toStorageUrl(file.file_path) : "#";
                                    return `<a href="${path}" target="_blank" class="block text-xs text-indigo-700 hover:underline">${name}</a>`;
                                })
                                .join("")}</div>`
                            : '<p class="text-xs text-gray-500 mt-1">No attached files.</p>';

                        return `<div class="rounded-lg border border-gray-200 bg-gray-50 px-3 py-2"><p class="text-sm font-semibold text-gray-800">Status: ${statusText}</p><p class="text-xs text-gray-600">Submitted: ${escapeHtml(dateText)}</p>${fileLinks}</div>`;
                    })
                    .join("")
                : '<p class="text-sm text-gray-500">No resubmitted progress reports found.</p>';
        }

        const normalizedProjectStatus = String(project?.project_status || "").toLowerCase();
        const canApplyAction =
            requiredAction === "halt_project" &&
            status === "under_review" &&
            !actionCompleted &&
            ["in_progress", "bidding_closed", "in progress", "bidding closed", "open"].includes(normalizedProjectStatus);

        const canDecideResolvedProject =
            status === "resolved" &&
            String(project?.project_status || "").toLowerCase() === "halt";

        if (actionForm) {
            const showInlineActionForm = !!requiredAction && requiredAction !== "halt_project" && status === "under_review";
            actionForm.classList.toggle("hidden", !showInlineActionForm);
        }

        if (resolvedActionsWrap) {
            resolvedActionsWrap.classList.toggle("hidden", !canDecideResolvedProject);
        }

        if (resumeBtn) resumeBtn.onclick = () => openDisputeProjectDecisionModal("resume_project");
        if (terminateBtn) terminateBtn.onclick = () => openDisputeProjectDecisionModal("terminate_project");

        if (actionBtn) {
            actionBtn.disabled = !canApplyAction;
            actionBtn.classList.toggle("opacity-60", !canApplyAction);
            actionBtn.textContent = actionCompleted
                ? "Required Action Completed"
                : (requiredAction === "halt_project" ? "Halt Project" : "Apply Required Action");
        }

        if (currentReport) {
            currentReport.required_action = requiredAction;
            currentReport.action_completed = actionCompleted;
            currentReport.project_id = project?.project_id || null;
        }

        const banner = document.getElementById("disputeWorkflowBanner");
        const title = document.getElementById("disputeWorkflowTitle");
        const message = document.getElementById("disputeWorkflowMessage");
        if (banner && title && message) {
            banner.className = "rounded-xl border px-4 py-3";
            if (status === "pending") {
                banner.classList.add("bg-amber-50", "border-amber-200");
                title.textContent = "Step 1: Move Case to Under Review";
                message.textContent = "Start by moving this dispute to Under Review.";
            } else if (status === "under_review" && requiredAction && !actionCompleted) {
                banner.classList.add("bg-indigo-50", "border-indigo-200");
                title.textContent = "Step 2: Apply Required Project Action";
                message.textContent = "Complete the required project action in this modal before resolving.";
            } else if (status === "under_review") {
                banner.classList.add("bg-emerald-50", "border-emerald-200");
                title.textContent = "Step 3: Approve Dispute";
                message.textContent = "Required actions are complete. You can now approve this dispute.";
            } else if (status === "resolved") {
                if (canDecideResolvedProject) {
                    banner.classList.add("bg-indigo-50", "border-indigo-200");
                    title.textContent = "Final Project Decision Needed";
                    message.textContent = "Project is halted. Choose Resume or Terminate in this modal.";
                } else {
                    banner.classList.add("bg-gray-50", "border-gray-200");
                    title.textContent = "Case Closed";
                    message.textContent = "This dispute has been resolved.";
                }
            } else if (status === "dismissed") {
                banner.classList.add("bg-red-50", "border-red-200");
                title.textContent = "Case Dismissed";
                message.textContent = "This dispute was dismissed.";
            } else {
                banner.classList.add("bg-gray-50", "border-gray-200");
                title.textContent = "Case Workflow";
                message.textContent = "Follow the dispute workflow steps to complete this case.";
            }
        }
    }

    function openDisputeProjectDecisionModal(actionType) {
        if (!currentReport || currentReport.source !== "dispute") return;

        const modal = document.getElementById("disputeProjectDecisionModal");
        const title = document.getElementById("disputeProjectDecisionTitle");
        const label = document.getElementById("disputeProjectDecisionLabel");
        const reason = document.getElementById("disputeProjectDecisionReason");
        const remarks = document.getElementById("disputeProjectDecisionRemarks");
        const header = document.getElementById("disputeProjectDecisionHeader");
        const banner = document.getElementById("disputeProjectDecisionBanner");
        const confirmBtn = document.getElementById("confirmDisputeProjectDecisionBtn");

        if (!modal) return;

        modal.dataset.action = actionType;
        if (title) {
            title.textContent = actionType === "resume_project" ? "Resume Project?" : "Terminate Project?";
        }
        if (label) {
            label.textContent = actionType === "resume_project"
                ? "This will resume the halted project and continue project progress."
                : "This will permanently terminate the halted project.";
        }
        if (reason) reason.value = "";
        if (remarks) remarks.value = "";

        const isResume = actionType === "resume_project";
        if (header) {
            header.className = "flex items-center justify-between px-4 py-3.5 border-b text-white " +
                (isResume ? "bg-gradient-to-r from-emerald-600 to-teal-600 border-emerald-600" : "bg-gradient-to-r from-red-600 to-rose-600 border-red-600");
        }
        if (banner) {
            banner.className = "flex items-start gap-3 p-3 rounded-lg " +
                (isResume ? "bg-emerald-50 border-l-4 border-emerald-500" : "bg-red-50 border-l-4 border-red-500");
            const icon = banner.querySelector(".fi-rr-info-circle");
            const labelEl = banner.querySelector(".flex-1 p:first-child");
            const subEl = banner.querySelector(".flex-1 p:last-child");
            if (icon) icon.className = "fi fi-rr-info-circle text-base mt-0.5 " + (isResume ? "text-emerald-600" : "text-red-600");
            if (labelEl) labelEl.className = "text-[12px] font-semibold mb-0.5 " + (isResume ? "text-emerald-900" : "text-red-900");
            if (subEl) subEl.className = "text-[10px] " + (isResume ? "text-emerald-800" : "text-red-800");
        }
        if (reason) {
            reason.className = "w-full px-3 py-2 border-2 border-gray-200 rounded-lg text-[12px] transition resize-none " +
                (isResume ? "focus:ring-2 focus:ring-emerald-300 focus:border-emerald-300" : "focus:ring-2 focus:ring-red-300 focus:border-red-300");
        }
        if (remarks) {
            remarks.className = "w-full px-3 py-2 border-2 border-gray-200 rounded-lg text-[12px] transition resize-none " +
                (isResume ? "focus:ring-2 focus:ring-emerald-300 focus:border-emerald-300" : "focus:ring-2 focus:ring-red-300 focus:border-red-300");
        }
        if (confirmBtn) {
            confirmBtn.className = "px-3.5 py-2 rounded-lg text-white text-[12px] font-semibold transition " +
                (isResume ? "bg-gradient-to-r from-emerald-600 to-teal-600 hover:from-emerald-700 hover:to-teal-700" : "bg-gradient-to-r from-red-600 to-rose-600 hover:from-red-700 hover:to-rose-700");
        }

        modal.classList.remove("hidden");
        modal.classList.add("flex");
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
    const dismissReasonInput = document.getElementById("dismissReason");
    const dismissReasonError = document.getElementById("dismissReasonError");
    const disputeWarningMessageInput = document.getElementById("disputeWarningMessage");
    const disputeWarningMessageError = document.getElementById("disputeWarningMessageError");

    function clearInlineReasonError(input, errorEl) {
        if (input) input.classList.remove("border-red-500");
        if (errorEl) errorEl.classList.add("hidden");
    }

    function showInlineReasonError(input, errorEl, message) {
        if (input) input.classList.add("border-red-500");
        if (errorEl) {
            errorEl.textContent = message;
            errorEl.classList.remove("hidden");
        }
    }

    function resetReportReasonErrors() {
        clearInlineReasonError(dismissReasonInput, dismissReasonError);
        clearInlineReasonError(disputeWarningMessageInput, disputeWarningMessageError);
    }

    dismissReasonInput?.addEventListener("input", () => clearInlineReasonError(dismissReasonInput, dismissReasonError));
    dismissReasonInput?.addEventListener("change", () => clearInlineReasonError(dismissReasonInput, dismissReasonError));
    disputeWarningMessageInput?.addEventListener("input", () => clearInlineReasonError(disputeWarningMessageInput, disputeWarningMessageError));
    disputeWarningMessageInput?.addEventListener("change", () => clearInlineReasonError(disputeWarningMessageInput, disputeWarningMessageError));

    document.getElementById("btnDismissReport")?.addEventListener("click", () => {
        if (!currentReport) return;
        if (dismissReasonInput) dismissReasonInput.value = "";
        clearInlineReasonError(dismissReasonInput, dismissReasonError);
        const modal = document.getElementById("dismissConfirmModal");
        if (modal) { modal.classList.remove("hidden"); modal.classList.add("flex"); }
    });

    document.getElementById("confirmDismissBtn")?.addEventListener("click", async () => {
        if (!currentReport) return;
        const reason = dismissReasonInput?.value?.trim();
        if (!reason) {
            showInlineReasonError(dismissReasonInput, dismissReasonError, "Reason is required.");
            dismissReasonInput?.focus();
            return;
        }

        clearInlineReasonError(dismissReasonInput, dismissReasonError);

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

        await openResolutionActionModal();
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
            const icon = btn.querySelector("i");
            if (btn.dataset.type === type) {
                btn.classList.add("border-red-500", "bg-red-50", "text-red-700");
                btn.classList.remove("border-gray-200", "bg-white", "text-gray-700");
                if (icon) { icon.classList.add("text-red-600"); icon.classList.remove("text-gray-400"); }
            } else {
                btn.classList.remove("border-red-500", "bg-red-50", "text-red-700");
                btn.classList.add("border-gray-200", "bg-white", "text-gray-700");
                if (icon) { icon.classList.remove("text-red-600"); icon.classList.add("text-gray-400"); }
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
        const activeTypeBtn = document.querySelector(".susp-type-btn.border-red-500");
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
        resetReportReasonErrors();
        ["viewReportModal", "dismissConfirmModal", "disputeProjectDecisionModal", "disputeHaltConfirmModal", "disputeWarningModal", "suspensionModal", "resolutionActionModal", "hidePostModal", "hideReviewModal", "removeReviewModal"].forEach((id) => {
            const el = document.getElementById(id);
            if (el) { el.classList.add("hidden"); el.classList.remove("flex"); }
        });
    }

    // Close buttons (modal-close class)
    document.querySelectorAll(".modal-close").forEach((btn) => {
        btn.addEventListener("click", function () {
            resetReportReasonErrors();
            const overlay = this.closest(".modal-overlay");
            if (overlay) { overlay.classList.add("hidden"); overlay.classList.remove("flex"); }
        });
    });

    // Backdrop click close
    document.querySelectorAll(".modal-overlay").forEach((overlay) => {
        overlay.addEventListener("click", function (e) {
            if (e.target === this) {
                resetReportReasonErrors();
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
        if (!currentReport) {
            toast("No active case selected", "error");
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

            currentReport.status = "resolved";
            toast("Report approved and action applied successfully", "success");
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

    let searchTimer;
    const searchEl = document.getElementById("topNavSearch");
    if (searchEl) {
        searchEl.addEventListener("input", () => {
            clearTimeout(searchTimer);
            moderationCurrentPage = 1;
            searchTimer = setTimeout(fetchReports, 400);
        });
    }

    document.getElementById("resetFilters")?.addEventListener("click", () => {
        ["filterSource", "filterStatus"].forEach((id) => {
            const el = document.getElementById(id); if (el) el.value = "all";
        });
        ["filterDateFrom", "filterDateTo"].forEach((id) => {
            const el = document.getElementById(id); if (el) el.value = "";
        });
        const topSearch = document.getElementById("topNavSearch");
        if (topSearch) topSearch.value = "";
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
    function openDisputeHaltModal() {
        const modal = document.getElementById("disputeHaltConfirmModal");
        const reason = document.getElementById("disputeHaltReason");
        const remarks = document.getElementById("disputeHaltRemarks");
        if (reason) reason.value = "";
        if (remarks) remarks.value = "";
        if (modal) {
            modal.classList.remove("hidden");
            modal.classList.add("flex");
        }
    }

    document.getElementById("btnReviewDispute")?.addEventListener("click", async () => {
        if (!currentReport || currentReport.source !== "dispute") return;
        await performDisputeAction("review");
    });

    document.getElementById("btnResolveDispute")?.addEventListener("click", async () => {
        if (!currentReport || currentReport.source !== "dispute") return;

        if (currentReport.required_action === "halt_project") {
            openDisputeHaltModal();
            return;
        }

        const disputeType = String(currentReport.dispute_type || "").toLowerCase();
        const needsWarningModal = ["payment", "delay", "quality", "others"].includes(disputeType);

        if (needsWarningModal) {
            const warningModal = document.getElementById("disputeWarningModal");
            if (disputeWarningMessageInput) disputeWarningMessageInput.value = "";
            clearInlineReasonError(disputeWarningMessageInput, disputeWarningMessageError);
            if (warningModal) {
                warningModal.classList.remove("hidden");
                warningModal.classList.add("flex");
            }
            return;
        }

        await performDisputeAction("resolve");
    });

    document.getElementById("btnRejectDispute")?.addEventListener("click", async () => {
        if (!currentReport || currentReport.source !== "dispute") return;
        if (dismissReasonInput) dismissReasonInput.value = "";
        clearInlineReasonError(dismissReasonInput, dismissReasonError);
        const modal = document.getElementById("dismissConfirmModal");
        if (modal) { modal.classList.remove("hidden"); modal.classList.add("flex"); }
    });

    document.getElementById("btnApplyDisputeProjectAction")?.addEventListener("click", async () => {
        if (!currentReport || currentReport.source !== "dispute") return;
        if (currentReport.required_action !== "halt_project") {
            toast("No project action is required for this dispute.", "error");
            return;
        }

        openDisputeHaltModal();
    });

    document.getElementById("confirmDisputeHaltBtn")?.addEventListener("click", async function () {
        if (!currentReport || currentReport.source !== "dispute") return;

        const reason = document.getElementById("disputeHaltReason")?.value?.trim() || "";
        const remarks = document.getElementById("disputeHaltRemarks")?.value?.trim() || "";
        if (reason.length < 10) {
            toast("Please provide at least 10 characters for the halt reason", "error");
            return;
        }

        this.disabled = true;
        const original = this.textContent;
        this.textContent = "Processing...";

        try {
            const res = await fetch(
                `/admin/project-management/disputes/${encodeURIComponent(currentReport.id)}/project-action`,
                {
                    method: "POST",
                    headers: getHeaders(),
                    body: JSON.stringify({
                        action: "halt_project",
                        halt_reason: reason,
                        project_remarks: remarks,
                    }),
                }
            );
            const json = await res.json();
            if (!json.success) {
                toast(json.message || "Failed to halt project", "error");
                return;
            }

            toast("Project halted and dispute resolved", "success");
            const haltModal = document.getElementById("disputeHaltConfirmModal");
            haltModal?.classList.add("hidden");
            haltModal?.classList.remove("flex");
            await openViewModal("dispute", currentReport.id, "dispute");
            fetchReports();
        } catch (e) {
            console.error(e);
            toast("Error halting project", "error");
        } finally {
            this.disabled = false;
            this.textContent = original;
        }
    });

    document.getElementById("confirmDisputeWarningBtn")?.addEventListener("click", async function () {
        if (!currentReport || currentReport.source !== "dispute") return;

        const warningMessage = disputeWarningMessageInput?.value?.trim() || "";
        if (warningMessage.length < 10) {
            showInlineReasonError(
                disputeWarningMessageInput,
                disputeWarningMessageError,
                "Warning message must be at least 10 characters."
            );
            disputeWarningMessageInput?.focus();
            return;
        }

        clearInlineReasonError(disputeWarningMessageInput, disputeWarningMessageError);

        this.disabled = true;
        const original = this.textContent;
        this.textContent = "Sending...";

        try {
            const res = await fetch(
                `/admin/project-management/disputes/${encodeURIComponent(currentReport.id)}/finalize`,
                {
                    method: "POST",
                    headers: getHeaders(),
                    body: JSON.stringify({
                        notes: warningMessage,
                        warning_message: warningMessage,
                    }),
                }
            );

            const json = await res.json();
            if (!json.success) {
                toast(json.message || "Failed to send warning and approve dispute", "error");
                return;
            }

            toast("Warning sent and dispute approved successfully", "success");
            const warningModal = document.getElementById("disputeWarningModal");
            warningModal?.classList.add("hidden");
            warningModal?.classList.remove("flex");
            closeAllModals();
            fetchReports();
        } catch (e) {
            console.error(e);
            toast("Error sending dispute warning", "error");
        } finally {
            this.disabled = false;
            this.textContent = original;
        }
    });

    document.getElementById("confirmDisputeProjectDecisionBtn")?.addEventListener("click", async function () {
        if (!currentReport || currentReport.source !== "dispute") return;

        const modal = document.getElementById("disputeProjectDecisionModal");
        const actionType = modal?.dataset?.action || "";
        if (!["resume_project", "terminate_project"].includes(actionType)) {
            toast("Invalid project decision action", "error");
            return;
        }

        const reason = document.getElementById("disputeProjectDecisionReason")?.value?.trim() || "";
        const remarks = document.getElementById("disputeProjectDecisionRemarks")?.value?.trim() || "";
        if (reason.length < 10) {
            toast("Please provide at least 10 characters for admin reason", "error");
            return;
        }

        this.disabled = true;
        const original = this.textContent;
        this.textContent = "Processing...";

        try {
            const res = await fetch(
                `/admin/project-management/disputes/${encodeURIComponent(currentReport.id)}/project-action`,
                {
                    method: "POST",
                    headers: getHeaders(),
                    body: JSON.stringify({
                        action: actionType,
                        action_reason: reason,
                        project_remarks: remarks,
                    }),
                }
            );
            const json = await res.json();
            if (!json.success) {
                toast(json.message || "Failed to apply project decision", "error");
                return;
            }

            toast(actionType === "resume_project" ? "Project resumed successfully" : "Project terminated successfully", "success");
            modal?.classList.add("hidden");
            modal?.classList.remove("flex");
            await openViewModal("dispute", currentReport.id, "dispute");
            fetchReports();
        } catch (e) {
            console.error(e);
            toast("Error applying project decision", "error");
        } finally {
            this.disabled = false;
            this.textContent = original;
        }
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

            if (action === "review") {
                toast("Dispute moved to under review", "success");
                await openViewModal("dispute", currentReport.id, "dispute");
            } else if (action === "resolve") {
                toast("Dispute approved successfully", "success");
                closeAllModals();
                fetchReports();
            }

            if (action !== "resolve") {
                fetchReports();
            }
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

        tbody.innerHTML = `<tr><td colspan="6" class="px-2.5 py-8 text-center text-gray-400 text-sm"><i class="fi fi-rr-loading-spinner animate-spin text-xl"></i><br>Loading...</td></tr>`;

        try {
            const params = new URLSearchParams({ type, query, page: adminCurrentPage, per_page: 10 });
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
            animateRowsInWrap(tbody);

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
        const perPage = pagination.per_page || 10;
        const lastPage = pagination.last_page || 1;

        if (total === 0) { bar.classList.add("hidden"); return; }

        const from = (page - 1) * perPage + 1;
        const to = Math.min(page * perPage, total);

        bar.classList.remove("hidden");
        if (info) info.innerHTML = `Showing <strong>${from}</strong>–<strong>${to}</strong> of <strong>${total}</strong> results`;
        if (indicator) indicator.textContent = `Page ${page} of ${lastPage}`;
        if (prevBtn) prevBtn.disabled = page <= 1;
        if (nextBtn) nextBtn.disabled = page >= lastPage;
    }

    function hideAdminPagination() {
        const bar = document.getElementById("adminPaginationBar");
        if (bar) bar.classList.add("hidden");
    }

    function renderAdminUserResults(thead, tbody, users) {
        thead.innerHTML = `<tr class="bg-gradient-to-r from-indigo-50 to-blue-50 border-b border-gray-200">
            <th class="px-2.5 py-2.5 text-left text-[11px] font-semibold text-gray-700 uppercase tracking-wider">User</th>
            <th class="px-2.5 py-2.5 text-left text-[11px] font-semibold text-gray-700 uppercase tracking-wider">Username</th>
            <th class="px-2.5 py-2.5 text-left text-[11px] font-semibold text-gray-700 uppercase tracking-wider">Email</th>
            <th class="px-2.5 py-2.5 text-left text-[11px] font-semibold text-gray-700 uppercase tracking-wider">Role</th>
            <th class="px-2.5 py-2.5 text-left text-[11px] font-semibold text-gray-700 uppercase tracking-wider">Status</th>
            <th class="px-2.5 py-2.5 text-center text-[11px] font-semibold text-gray-700 uppercase tracking-wider">Actions</th>
        </tr>`;

        if (!users.length) {
            tbody.innerHTML = `<tr><td colspan="6" class="px-4 py-12 text-center text-gray-400"><i class="fi fi-sr-users text-3xl block mb-2"></i><p class="text-base font-medium text-gray-500">No users found</p></td></tr>`;
            return;
        }

        tbody.innerHTML = users.map(u => {
            const isActive = u.user_type === "contractor"
                ? (u.contractor_is_active !== 0)
                : (u.owner_is_active !== 0);
            const statusBadge = isActive
                ? `<span class="inline-flex items-center px-2 py-0.5 rounded-full text-[11px] font-semibold border bg-emerald-100 text-emerald-700 border-emerald-200">Active</span>`
                : `<span class="inline-flex items-center px-2 py-0.5 rounded-full text-[11px] font-semibold border bg-red-100 text-red-700 border-red-200">Suspended</span>`;
            const role = (u.user_type || "").replace(/_/g, " ").replace(/\b\w/g, c => c.toUpperCase());

            return `<tr class="hover:bg-indigo-50/60 transition-colors">
                <td class="px-2.5 py-2.5 text-[11px] font-medium text-gray-800 truncate" title="${escapeHtml((u.first_name || "") + " " + (u.last_name || ""))}">${escapeHtml((u.first_name || "") + " " + (u.last_name || ""))}</td>
                <td class="px-2.5 py-2.5 text-[11px] text-gray-600">@${escapeHtml(u.username || "")}</td>
                <td class="px-2.5 py-2.5 text-[11px] text-gray-600 truncate" title="${escapeHtml(u.email || "")}">${escapeHtml(u.email || "")}</td>
                <td class="px-2.5 py-2.5"><span class="inline-flex items-center px-2 py-0.5 rounded-full text-[11px] font-semibold border bg-indigo-100 text-indigo-700 border-indigo-200">${role}</span></td>
                <td class="px-2.5 py-2.5">${statusBadge}</td>
                <td class="px-2.5 py-2.5 text-center">
                    ${isActive ? `<button class="action-btn w-8 h-8 inline-flex items-center justify-center p-1.5 rounded-xl border border-red-200 bg-red-50 text-red-600 shadow-[0_2px_8px_rgba(0,0,0,0.06)] hover:bg-red-100 hover:shadow-sm hover:border-red-300 hover:-translate-y-0.5 transition-all admin-suspend-btn" title="Suspend" data-user-id="${u.user_id}"><i class="fi fi-rr-ban text-[13px] leading-none"></i></button>` : `<span class="text-[11px] text-gray-400 italic">Suspended</span>`}
                </td>
            </tr>`;
        }).join("");

        bindAdminSuspendButtons();
    }

    function renderAdminPostResults(thead, tbody, posts) {
        adminDirectItemCache.showcase.clear();
        const uniquePosts = Array.from(new Map((posts || []).map((p) => [String(p.post_id), p])).values());

        thead.innerHTML = `<tr class="bg-gradient-to-r from-indigo-50 to-blue-50 border-b border-gray-200">
            <th class="px-2.5 py-2.5 text-left text-[11px] font-semibold text-gray-700 uppercase tracking-wider">ID</th>
            <th class="px-2.5 py-2.5 text-left text-[11px] font-semibold text-gray-700 uppercase tracking-wider">Title</th>
            <th class="px-2.5 py-2.5 text-left text-[11px] font-semibold text-gray-700 uppercase tracking-wider">Author</th>
            <th class="px-2.5 py-2.5 text-left text-[11px] font-semibold text-gray-700 uppercase tracking-wider">Status</th>
            <th class="px-2.5 py-2.5 text-left text-[11px] font-semibold text-gray-700 uppercase tracking-wider">Date</th>
            <th class="px-2.5 py-2.5 text-center text-[11px] font-semibold text-gray-700 uppercase tracking-wider">Actions</th>
        </tr>`;

        if (!uniquePosts.length) {
            tbody.innerHTML = `<tr><td colspan="6" class="px-4 py-12 text-center text-gray-400"><i class="fi fi-sr-picture text-3xl block mb-2"></i><p class="text-base font-medium text-gray-500">No posts found</p></td></tr>`;
            return;
        }

        tbody.innerHTML = uniquePosts.map(p => {
            adminDirectItemCache.showcase.set(String(p.post_id), p);
            const date = p.created_at ? new Date(p.created_at).toLocaleDateString("en-US", { month: "short", day: "numeric", year: "numeric" }) : "-";
            return `<tr class="hover:bg-indigo-50/60 transition-colors">
                <td class="px-2.5 py-2.5 text-[11px] font-mono text-gray-700">#${p.post_id}</td>
                <td class="px-2.5 py-2.5 text-[11px] font-medium text-gray-800 truncate" title="${escapeHtml(p.title || "")}">${escapeHtml(p.title || "Untitled")}</td>
                <td class="px-2.5 py-2.5 text-[11px] text-gray-600 truncate" title="${escapeHtml((p.first_name || "") + " " + (p.last_name || ""))}">${escapeHtml((p.first_name || "") + " " + (p.last_name || ""))} (@${escapeHtml(p.username || "")})</td>
                <td class="px-2.5 py-2.5"><span class="inline-flex items-center px-2 py-0.5 rounded-full text-[11px] font-semibold border bg-blue-100 text-blue-700 border-blue-200">${(p.status || "").toUpperCase()}</span></td>
                <td class="px-2.5 py-2.5 text-[11px] text-gray-500 whitespace-nowrap">${date}</td>
                <td class="px-2.5 py-2.5 text-center">
                    <div class="flex items-center justify-center gap-1">
                        <button class="action-btn view-btn w-8 h-8 inline-flex items-center justify-center p-1.5 rounded-xl border border-gray-200 bg-gray-50 text-gray-600 shadow-[0_2px_8px_rgba(0,0,0,0.06)] hover:bg-gray-100 hover:shadow-sm hover:-translate-y-0.5 transition-all admin-view-item-btn" title="View" data-item-type="showcase" data-item-id="${p.post_id}"><i class="fi fi-rr-eye text-[13px] leading-none"></i></button>
                        ${p.status === 'deleted' ? `<span class="text-[11px] text-gray-400 italic">Hidden</span>` : `<button class="action-btn w-8 h-8 inline-flex items-center justify-center p-1.5 rounded-xl border border-amber-200 bg-amber-50 text-amber-600 shadow-[0_2px_8px_rgba(0,0,0,0.06)] hover:bg-amber-100 hover:shadow-sm hover:border-amber-300 hover:-translate-y-0.5 transition-all admin-hide-post-btn" title="Hide Post" data-post-id="${p.post_id}" data-post-title="${escapeHtml(p.title || 'Untitled')}" data-post-author="${escapeHtml((p.first_name || '') + ' ' + (p.last_name || ''))}"><i class="fi fi-rr-eye-crossed text-[13px] leading-none"></i></button>`}
                    </div>
                </td>
            </tr>`;
        }).join("");

        bindAdminViewButtons();
        bindAdminHidePostButtons();
    }

    function renderAdminProjectResults(thead, tbody, projects) {
        adminDirectItemCache.project.clear();

        thead.innerHTML = `<tr class="bg-gradient-to-r from-indigo-50 to-blue-50 border-b border-gray-200">
            <th class="px-2.5 py-2.5 text-left text-[11px] font-semibold text-gray-700 uppercase tracking-wider">ID</th>
            <th class="px-2.5 py-2.5 text-left text-[11px] font-semibold text-gray-700 uppercase tracking-wider">Project Title</th>
            <th class="px-2.5 py-2.5 text-left text-[11px] font-semibold text-gray-700 uppercase tracking-wider">Owner</th>
            <th class="px-2.5 py-2.5 text-left text-[11px] font-semibold text-gray-700 uppercase tracking-wider">Location</th>
            <th class="px-2.5 py-2.5 text-left text-[11px] font-semibold text-gray-700 uppercase tracking-wider">Status</th>
            <th class="px-2.5 py-2.5 text-center text-[11px] font-semibold text-gray-700 uppercase tracking-wider">Actions</th>
        </tr>`;

        if (!projects.length) {
            tbody.innerHTML = `<tr><td colspan="6" class="px-4 py-12 text-center text-gray-400"><i class="fi fi-sr-building text-3xl block mb-2"></i><p class="text-base font-medium text-gray-500">No project posts found</p></td></tr>`;
            return;
        }

        tbody.innerHTML = projects.map(p => {
            adminDirectItemCache.project.set(String(p.project_id), p);
            const owner = `${p.first_name || ""} ${p.last_name || ""}`.trim();
            const normalizedStatus = String(p.project_status || "").toLowerCase();
            const isHidden = normalizedStatus === "deleted_post";

            return `<tr class="hover:bg-indigo-50/60 transition-colors">
                <td class="px-2.5 py-2.5 text-[11px] font-mono text-gray-700">#${p.project_id}</td>
                <td class="px-2.5 py-2.5 text-[11px] font-medium text-gray-800 truncate" title="${escapeHtml(p.project_title || "")}">${escapeHtml(p.project_title || "Untitled")}</td>
                <td class="px-2.5 py-2.5 text-[11px] text-gray-600 truncate" title="${escapeHtml(owner)}">${escapeHtml(owner || "Unknown")} (@${escapeHtml(p.username || "")})</td>
                <td class="px-2.5 py-2.5 text-[11px] text-gray-600 truncate" title="${escapeHtml(p.project_location || "")}">${escapeHtml(p.project_location || "N/A")}</td>
                <td class="px-2.5 py-2.5"><span class="inline-flex items-center px-2 py-0.5 rounded-full text-[11px] font-semibold border bg-blue-100 text-blue-700 border-blue-200">${escapeHtml((p.project_status || "").toUpperCase().replace(/_/g, " "))}</span></td>
                <td class="px-2.5 py-2.5 text-center">
                    <div class="flex items-center justify-center gap-1">
                        <button class="action-btn view-btn w-8 h-8 inline-flex items-center justify-center p-1.5 rounded-xl border border-gray-200 bg-gray-50 text-gray-600 shadow-[0_2px_8px_rgba(0,0,0,0.06)] hover:bg-gray-100 hover:shadow-sm hover:-translate-y-0.5 transition-all admin-view-item-btn" title="View" data-item-type="project" data-item-id="${p.project_id}"><i class="fi fi-rr-eye text-[13px] leading-none"></i></button>
                        ${isHidden ? `<span class="text-[11px] text-gray-400 italic">Hidden</span>` : `<button class="action-btn w-8 h-8 inline-flex items-center justify-center p-1.5 rounded-xl border border-amber-200 bg-amber-50 text-amber-600 shadow-[0_2px_8px_rgba(0,0,0,0.06)] hover:bg-amber-100 hover:shadow-sm hover:border-amber-300 hover:-translate-y-0.5 transition-all admin-hide-post-btn admin-hide-project-btn" title="Hide Project" data-post-id="${p.project_id}" data-post-title="${escapeHtml(p.project_title || 'Untitled')}" data-post-author="${escapeHtml(owner || 'Unknown')}" data-hide-kind="project"><i class="fi fi-rr-eye-crossed text-[13px] leading-none"></i></button>`}
                    </div>
                </td>
            </tr>`;
        }).join("");

        bindAdminViewButtons();
        bindAdminHidePostButtons();
    }

    function renderAdminReviewResults(thead, tbody, reviews) {
        adminDirectItemCache.review.clear();

        thead.innerHTML = `<tr class="bg-gradient-to-r from-indigo-50 to-blue-50 border-b border-gray-200">
            <th class="px-2.5 py-2.5 text-left text-[11px] font-semibold text-gray-700 uppercase tracking-wider">ID</th>
            <th class="px-2.5 py-2.5 text-left text-[11px] font-semibold text-gray-700 uppercase tracking-wider">Project</th>
            <th class="px-2.5 py-2.5 text-left text-[11px] font-semibold text-gray-700 uppercase tracking-wider">Reviewer</th>
            <th class="px-2.5 py-2.5 text-left text-[11px] font-semibold text-gray-700 uppercase tracking-wider">Rating</th>
            <th class="px-2.5 py-2.5 text-left text-[11px] font-semibold text-gray-700 uppercase tracking-wider">Comment</th>
            <th class="px-2.5 py-2.5 text-center text-[11px] font-semibold text-gray-700 uppercase tracking-wider">Actions</th>
        </tr>`;

        if (!reviews.length) {
            tbody.innerHTML = `<tr><td colspan="6" class="px-4 py-12 text-center text-gray-400"><i class="fi fi-sr-star text-3xl block mb-2"></i><p class="text-base font-medium text-gray-500">No reviews found</p></td></tr>`;
            return;
        }

        tbody.innerHTML = reviews.map(r => {
            adminDirectItemCache.review.set(String(r.review_id), r);
            const stars = "★".repeat(r.rating || 0) + "☆".repeat(5 - (r.rating || 0));
            return `<tr class="hover:bg-indigo-50/60 transition-colors">
                <td class="px-2.5 py-2.5 text-[11px] font-mono text-gray-700">#${r.review_id}</td>
                <td class="px-2.5 py-2.5 text-[11px] text-gray-700 truncate" title="${escapeHtml(r.project_title || "")}">${escapeHtml(r.project_title || "N/A")}</td>
                <td class="px-2.5 py-2.5 text-[11px] text-gray-600 truncate" title="${escapeHtml((r.reviewer_first_name || "") + " " + (r.reviewer_last_name || ""))}">${escapeHtml((r.reviewer_first_name || "") + " " + (r.reviewer_last_name || ""))} (@${escapeHtml(r.reviewer_username || "")})</td>
                <td class="px-2.5 py-2.5 text-[11px] text-amber-600 font-bold">${stars}</td>
                <td class="px-2.5 py-2.5 text-[11px] text-gray-600 truncate" title="${escapeHtml(r.comment || "")}">${escapeHtml(r.comment || "No comment")}</td>
                <td class="px-2.5 py-2.5 text-center">
                    <div class="flex items-center justify-center gap-1">
                        <button class="action-btn view-btn w-8 h-8 inline-flex items-center justify-center p-1.5 rounded-xl border border-gray-200 bg-gray-50 text-gray-600 shadow-[0_2px_8px_rgba(0,0,0,0.06)] hover:bg-gray-100 hover:shadow-sm hover:-translate-y-0.5 transition-all admin-view-item-btn" title="View" data-item-type="review" data-item-id="${r.review_id}"><i class="fi fi-rr-eye text-[13px] leading-none"></i></button>
                        ${r.is_deleted ? `<span class="text-[11px] text-gray-400 italic">Hidden</span>` : `
                            <button class="action-btn w-8 h-8 inline-flex items-center justify-center p-1.5 rounded-xl border border-amber-200 bg-amber-50 text-amber-600 shadow-[0_2px_8px_rgba(0,0,0,0.06)] hover:bg-amber-100 hover:shadow-sm hover:border-amber-300 hover:-translate-y-0.5 transition-all admin-hide-review-btn" title="Hide Review" data-review-id="${r.review_id}" data-review-author="${escapeHtml((r.reviewer_first_name || '') + ' ' + (r.reviewer_last_name || ''))}" data-review-rating="${"★".repeat(r.rating || 0) + "☆".repeat(5 - (r.rating || 0))}"><i class="fi fi-rr-eye-crossed text-[13px] leading-none"></i></button>
                            <button class="action-btn w-8 h-8 inline-flex items-center justify-center p-1.5 rounded-xl border border-red-200 bg-red-50 text-red-600 shadow-[0_2px_8px_rgba(0,0,0,0.06)] hover:bg-red-100 hover:shadow-sm hover:border-red-300 hover:-translate-y-0.5 transition-all admin-remove-review-btn" title="Remove" data-review-id="${r.review_id}"><i class="fi fi-rr-trash text-[13px] leading-none"></i></button>
                        `}
                    </div>
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
        applyViewModalTheme("default");
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
        applyViewModalTheme(overviewStatus);

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
    bindModerationTableDelegates();
    fetchReports();
})();

