// Disputes/Reports page interactivity (cleaned)

(() => {
    // Client dataset removed — server-side rendering preferred.
    const reportsData = [];

    // Detect server-rendered rows in the disputes table and skip destructive client rendering.
    const tbody = document.querySelector("#reportsTableBody");
    const hasServerRows = !!(
        tbody &&
        tbody.querySelector("tr") &&
        tbody.querySelector("tr").textContent.trim().length > 0
    );

    // Utility helpers (minimal implementations used by the page)
    function showModal(id) {
        const modal = document.getElementById(id);
        if (!modal) return;
        // move modal to document.body so it won't be clipped by parent overflow
        if (modal.parentNode !== document.body)
            document.body.appendChild(modal);
        // ensure viewDetailsModal sits below confirmations
        if (id === "viewDetailsModal") {
            modal.style.zIndex = 100000;
        } else {
            const view = document.getElementById("viewDetailsModal");
            if (view && view.parentNode !== document.body)
                document.body.appendChild(view);
            if (view) view.style.zIndex = 100000;
            modal.style.zIndex = 100010;
        }
        modal.classList.remove("hidden");
        modal.classList.add("flex");
    }

    function hideModal(id) {
        const modal = document.getElementById(id);
        if (!modal) return;
        modal.classList.add("hidden");
        modal.classList.remove("flex");
        // clear inline z-index to avoid stacking issues later
        modal.style.zIndex = "";
    }

    function toast(message, type = "info") {
        // simple toast fallback
        const el = document.createElement("div");
        el.className = `toast ${type}`;
        el.textContent = message;
        Object.assign(el.style, {
            position: "fixed",
            right: "1rem",
            bottom: "1rem",
            padding: "0.5rem 1rem",
            background: "#111",
            color: "#fff",
            zIndex: 9999,
            borderRadius: "6px",
        });
        document.body.appendChild(el);
        setTimeout(() => el.remove(), 3000);
    }

    // Helper: return consistent status badge HTML for a given status key
    function getStatusBadgeHTML(status) {
        if (!status) status = "";
        const s = status.toString().toLowerCase();
        const map = {
            pending: "bg-amber-100 text-amber-700",
            open: "bg-amber-100 text-amber-700",
            under_review: "bg-amber-100 text-amber-700",
            "under-review": "bg-amber-100 text-amber-700",
            "under review": "bg-amber-100 text-amber-700",
            escalated: "bg-red-100 text-red-700",
            rejected: "bg-red-100 text-red-700",
            cancelled: "bg-red-100 text-red-700",
            resolved: "bg-emerald-100 text-emerald-700",
        };
        const cls = map[s] || "bg-gray-100 text-gray-700";
        const label = (s || "").replace(/_/g, " ");
        return `<span class="inline-block px-3 py-1 text-xs rounded-full font-semibold ${cls}">${(
            label || "-"
        ).toUpperCase()}</span>`;
    }

    // Render dispute files helper: files -> populate #modalDocumentsTable
    function renderDisputeFiles(files) {
        const tbody = document.getElementById("modalDocumentsTable");
        if (!tbody) return;
        if (!files || files.length === 0) {
            tbody.innerHTML = `<tr><td colspan="3" class="px-4 py-8 text-center text-gray-500 text-sm">No documents</td></tr>`;
            return;
        }

        const rows = files
            .map((f) => {
                const name =
                    f.file_name ||
                    f.original_name ||
                    f.filename ||
                    (f.file_path ? f.file_path.split("/").pop() : "Document");
                const date = f.uploaded_at || f.created_at || f.createdOn || "";
                const uploaded = date
                    ? new Date(date).toLocaleDateString()
                    : "";
                const path = f.file_path || f.storage_path || f.path || "";
                const link = path
                    ? path.startsWith("/")
                        ? path
                        : "/storage/" + path
                    : "#";
                const ext = (name.split(".").pop() || "").toLowerCase();
                const isPdf = ext === "pdf";
                const icon = isPdf
                    ? '<i class="fi fi-rr-file-pdf text-red-600"></i>'
                    : '<i class="fi fi-rr-image text-indigo-600"></i>';
                return `
        <tr class="hover:bg-gray-50 transition">
          <td class="px-4 py-3 flex items-center gap-3"><div class="w-8 h-8 rounded-lg bg-gray-100 flex items-center justify-center">${icon}</div><div class="truncate">${name}</div></td>
          <td class="px-4 py-3">${uploaded}</td>
          <td class="px-4 py-3 text-center"><a href="${link}" class="text-indigo-600 font-medium" target="_blank">View / Download</a></td>
        </tr>
      `;
            })
            .join("");

        tbody.innerHTML = rows;
    }

    // Open view modal and populate dynamically based on status
    window.openViewModal = async function (id) {
        if (!id) return;
        try {
            const res = await fetch(
                `/admin/project-management/disputes/${id}/details`,
                { headers: { "X-Requested-With": "XMLHttpRequest" } }
            );
            const json = await res.json();
            if (!json.success) {
                toast("Failed to load details", "error");
                return;
            }
            const data = json.data || {};
            const header = data.header || {};
            const content = data.content || {};
            const dispute = data.dispute || {};

            // Overview (Section A - always visible)
            const caseIdEl = document.getElementById("modalCaseId");
            if (caseIdEl)
                caseIdEl.textContent = `Case #${dispute.dispute_id || id}`;
            const reporterEl = document.getElementById("modalReporter");
            if (reporterEl)
                reporterEl.textContent =
                    dispute.reporter_username ||
                    dispute.reporter_name ||
                    header.reporter_name ||
                    "-";
            const againstEl = document.getElementById("modalAgainst");
            if (againstEl)
                againstEl.textContent =
                    dispute.against_username ||
                    dispute.respondent_username ||
                    header.against_name ||
                    "-";
            const typeEl = document.getElementById("modalType");
            if (typeEl)
                typeEl.textContent =
                    header.dispute_type || dispute.dispute_type || "-";
            const dateEl = document.getElementById("modalDate");
            if (dateEl)
                dateEl.textContent =
                    header.date_submitted || dispute.created_at
                        ? new Date(
                              header.date_submitted || dispute.created_at
                          ).toLocaleDateString()
                        : "-";
            const statusEl = document.getElementById("modalStatus");
            if (statusEl) {
                const statusVal = (
                    header.dispute_status ||
                    dispute.dispute_status ||
                    ""
                ).toString();
                statusEl.innerHTML = getStatusBadgeHTML(statusVal);
            }
            const projectEl = document.getElementById("modalProject");
            if (projectEl)
                projectEl.textContent =
                    header.project_title || dispute.project_title || "-";

            // Section A subsections: map requested fields explicitly
            const subjectEl = document.getElementById("modalSubject");
            if (subjectEl)
                subjectEl.textContent = dispute.title || content.subject || "-";
            const descEl = document.getElementById("modalDescription");
            if (descEl)
                descEl.textContent =
                    dispute.dispute_desc || content.dispute_desc || "-";
            const requestedEl = document.getElementById("modalRequestedAction");
            if (requestedEl)
                requestedEl.textContent =
                    dispute.requested_action || content.requested_action || "-";

            // Initial proofs / supporting documents
            const proofs =
                data.initial_proofs && data.initial_proofs.length
                    ? data.initial_proofs
                    : data.evidence || [];
            const docsTbody = document.getElementById("modalDocumentsTable");
            if (docsTbody) {
                if (!proofs || proofs.length === 0) {
                    docsTbody.innerHTML = `<tr><td colspan="3" class="px-4 py-8 text-center text-gray-500 text-sm">No documents</td></tr>`;
                } else {
                    docsTbody.innerHTML = proofs
                        .map((f) => {
                            const name =
                                f.file_name ||
                                f.original_name ||
                                f.filename ||
                                (f.file_path
                                    ? f.file_path.split("/").pop()
                                    : "Document");
                            const date =
                                f.uploaded_at ||
                                f.created_at ||
                                f.createdOn ||
                                "";
                            const uploaded = date
                                ? new Date(date).toLocaleDateString()
                                : "";
                            const path =
                                f.file_path || f.storage_path || f.path || "";
                            const link = path
                                ? path.startsWith("/")
                                    ? path
                                    : "/storage/" + path
                                : "#";
                            const ext = (
                                name.split(".").pop() || ""
                            ).toLowerCase();
                            const isPdf = ext === "pdf";
                            const icon = isPdf
                                ? '<i class="fi fi-rr-file-pdf text-red-600"></i>'
                                : '<i class="fi fi-rr-image text-indigo-600"></i>';
                            return `
              <tr class="hover:bg-gray-50 transition">
                <td class="px-4 py-3 flex items-center gap-3"><div class="w-8 h-8 rounded-lg bg-gray-100 flex items-center justify-center">${icon}</div><div class="truncate">${name}</div></td>
                <td class="px-4 py-3">${uploaded}</td>
                <td class="px-4 py-3 text-center"><a href="${link}" class="text-indigo-600 font-medium" target="_blank">View / Download</a></td>
              </tr>
            `;
                        })
                        .join("");
                }
            }

            // New section containers
            const sectionResubmission = document.getElementById(
                "sectionResubmission"
            );
            const sectionFeedback = document.getElementById("sectionFeedback");
            const sectionActions = document.getElementById("sectionActions");
            const sectionFinalAction =
                document.getElementById("sectionFinalAction");

            // inner tables
            const resubmittedTable = document.getElementById(
                "modalResubmittedTable"
            );
            // always clear existing resubmission rows first to avoid stale content
            if (resubmittedTable) resubmittedTable.innerHTML = "";

            // reset visibility
            if (sectionResubmission)
                sectionResubmission.classList.add("hidden");
            if (sectionFeedback) sectionFeedback.classList.add("hidden");
            if (sectionActions) sectionActions.classList.add("hidden");
            if (sectionFinalAction) sectionFinalAction.classList.add("hidden");

            const statusVal = (
                header.dispute_status ||
                dispute.dispute_status ||
                ""
            )
                .toString()
                .toLowerCase();

            // Determine whether to show final action: under-review variants or if resubmissions exist
            const hasResubmissions =
                (data.resubmissions && data.resubmissions.length) ||
                (data.progressReports && data.progressReports.length);
            const isUnderReview =
                statusVal.indexOf("under") !== -1 ||
                statusVal.indexOf("review") !== -1 ||
                statusVal.indexOf("under-review") !== -1 ||
                statusVal.indexOf("under review") !== -1 ||
                statusVal === "under_review" ||
                statusVal === "escalated";
            // Status-driven rules per spec
            if (statusVal === "open") {
                // show Section A (always) and Section D (actions)
                if (sectionActions) sectionActions.classList.remove("hidden");
            } else if (
                statusVal === "under_review" ||
                statusVal === "escalated" ||
                statusVal.indexOf("under") !== -1 ||
                statusVal.indexOf("review") !== -1 ||
                statusVal.indexOf("under-review") !== -1 ||
                statusVal.indexOf("under review") !== -1
            ) {
                // show Section A and Section B (resubmissions)
                if (sectionResubmission)
                    sectionResubmission.classList.remove("hidden");
                // hide primary actions and show final action when appropriate
                if (sectionActions) sectionActions.classList.add("hidden");
                if (sectionFinalAction && (isUnderReview || hasResubmissions))
                    sectionFinalAction.classList.remove("hidden");
                // always clear existing rows first
                if (resubmittedTable) resubmittedTable.innerHTML = "";
                const resubs =
                    data.resubmissions && data.resubmissions.length
                        ? data.resubmissions
                        : data.progressReports || [];
                if (resubmittedTable) {
                    if (!resubs || resubs.length === 0) {
                        resubmittedTable.innerHTML = `<tr><td colspan="5" class="px-4 py-8 text-center text-gray-500 text-sm">No resubmissions found.</td></tr>`;
                    } else {
                        let anyApproved = false;
                        const rows = resubs
                            .map((item) => {
                                // item expected: { original_name, file_path, progress_status, submitted_at, progress_id }
                                const name =
                                    item.original_name ||
                                    item.file_name ||
                                    "File";
                                const projectId =
                                    item.project_id || item.progress_id || "-";
                                const submitted =
                                    item.submitted_at ||
                                    item.created_at ||
                                    item.uploaded_at ||
                                    "";
                                const ds = submitted
                                    ? new Date(submitted).toLocaleDateString()
                                    : "-";
                                const status = (
                                    item.progress_status ||
                                    item.status ||
                                    ""
                                )
                                    .toString()
                                    .toLowerCase();
                                if (status === "approved") anyApproved = true;
                                // status badge color
                                const statusMap = {
                                    approved: "bg-emerald-100 text-emerald-700",
                                    pending: "bg-amber-100 text-amber-700",
                                    rejected: "bg-red-100 text-red-700",
                                };
                                const scls =
                                    statusMap[status] ||
                                    "bg-gray-100 text-gray-700";
                                const path = item.file_path
                                    ? item.file_path.startsWith("/")
                                        ? item.file_path
                                        : "/storage/" + item.file_path
                                    : "#";
                                return `
                <tr class="hover:bg-gray-50 transition">
                  <td class="px-4 py-3">${name}</td>
                  <td class="px-4 py-3 font-mono text-sm text-gray-700">${projectId}</td>
                  <td class="px-4 py-3">${ds}</td>
                  <td class="px-4 py-3"><span class="inline-flex px-3 py-1 rounded-full text-xs font-semibold ${scls}">${(
                                    status || "-"
                                ).toUpperCase()}</span></td>
                  <td class="px-4 py-3 text-center"><a href="${path}" class="text-indigo-600 font-medium" target="_blank">View</a></td>
                </tr>
              `;
                            })
                            .join("");
                        resubmittedTable.innerHTML = rows;
                        // show final action (Section E) for under_review/escalated
                        if (sectionFinalAction)
                            sectionFinalAction.classList.remove("hidden");
                    }
                }
            } else if (statusVal === "resolved" || statusVal === "closed") {
                // show A, B, C
                if (sectionResubmission)
                    sectionResubmission.classList.remove("hidden");
                if (sectionFeedback) sectionFeedback.classList.remove("hidden");
                // populate feedback/resolution using backend-provided keys
                const reporterName =
                    data.reporter_name ||
                    header.reporter_name ||
                    dispute.reporter_username ||
                    "-";
                const latestStatus = data.latest_resubmission_status || null;
                const latestDate = data.latest_resubmission_date || null;
                const adminResp =
                    (data.resolution && data.resolution.admin_response) ||
                    dispute.admin_response ||
                    "";

                const fromEl = document.getElementById("modalFeedbackFrom");
                if (fromEl) fromEl.textContent = reporterName || "-";
                const idEl = document.getElementById("modalResubmissionId");
                if (idEl)
                    idEl.textContent =
                        data.latest_resubmission_project_id || "-";
                const respEl = document.getElementById("modalFeedbackResponse");
                if (respEl) {
                    const statusText = latestStatus
                        ? latestStatus.toString()
                        : "-";
                    const st = statusText.toLowerCase();
                    const cls =
                        st === "approved"
                            ? "bg-emerald-100 text-emerald-700"
                            : st === "rejected"
                            ? "bg-red-100 text-red-700"
                            : "bg-amber-100 text-amber-700";
                    respEl.innerHTML = `<span class="inline-flex px-3 py-1 rounded-full text-xs font-semibold ${cls}">${(
                        statusText || "-"
                    ).toUpperCase()}</span>`;
                }
                const dateEl2 = document.getElementById("modalFeedbackDate");
                if (dateEl2)
                    dateEl2.textContent = latestDate
                        ? new Date(latestDate).toLocaleDateString()
                        : "-";
                const remarksEl = document.getElementById(
                    "modalFeedbackRemarks"
                );
                if (remarksEl) remarksEl.textContent = adminResp || "-";
            }

            // wire reject button to open confirmation
            const rejectBtn = document.getElementById("rejectBtn");
            if (rejectBtn) {
                rejectBtn.onclick = function () {
                    window.__selectedDisputeId = id;
                    showModal("rejectConfirmModal");
                };
            }

            // confirm reject action
            const confirmRejectBtn =
                document.getElementById("confirmRejectBtn");
            if (confirmRejectBtn) {
                confirmRejectBtn.onclick = async function () {
                    const reason =
                        (document.getElementById("rejectionReason") || {})
                            .value || "";
                    const useId = window.__selectedDisputeId || id;
                    if (!useId) return;
                    const tokenMeta = document.querySelector(
                        'meta[name="csrf-token"]'
                    );
                    const headers = {
                        "X-Requested-With": "XMLHttpRequest",
                        "Content-Type": "application/json",
                    };
                    if (tokenMeta && tokenMeta.content)
                        headers["X-CSRF-TOKEN"] = tokenMeta.content;
                    try {
                        const resp = await fetch(
                            `/admin/project-management/disputes/${useId}/reject`,
                            {
                                method: "POST",
                                headers,
                                body: JSON.stringify({ reason }),
                            }
                        );
                        const j = await resp.json();
                        hideModal("rejectConfirmModal");
                        hideModal("viewDetailsModal");
                        const tr = document.querySelector(
                            `tr[data-id="${useId}"]`
                        );
                        if (tr) {
                            tr.dataset.status = "cancelled";
                            const statusCell =
                                tr.querySelector("td:nth-child(5)");
                            if (statusCell)
                                statusCell.innerHTML =
                                    getStatusBadgeHTML("cancelled");
                        }
                        toast(
                            j.success
                                ? "Case rejected"
                                : "Case rejected (partial)",
                            "success"
                        );
                    } catch (e) {
                        console.error(e);
                        toast("Failed to reject case", "error");
                    }
                };
            }

            // Resolve flow: initial approval (open -> under_review) uses a simple confirm;
            // final resolution (under_review -> resolved) uses the resolveConfirmModal with notes.
            const modalResolveBtn = document.getElementById("resolveBtn");
            const finalResolveBtn = document.getElementById("finalResolveBtn");
            const confirmResolveBtn =
                document.getElementById("confirmResolveBtn");

            if (modalResolveBtn) {
                if (statusVal === "open") {
                    // open the approve confirmation modal instead of window.confirm
                    modalResolveBtn.onclick = function () {
                        window.__selectedDisputeId = id;
                        showModal("approveConfirmModal");
                    };

                    // wire the confirm button inside approveConfirmModal
                    const confirmApproveBtn =
                        document.getElementById("confirmApproveBtn");
                    if (confirmApproveBtn) {
                        confirmApproveBtn.onclick = async function () {
                            const useId = window.__selectedDisputeId || id;
                            if (!useId) return;
                            const tokenMeta = document.querySelector(
                                'meta[name="csrf-token"]'
                            );
                            const headers = {
                                "X-Requested-With": "XMLHttpRequest",
                                "Content-Type": "application/json",
                            };
                            if (tokenMeta && tokenMeta.content)
                                headers["X-CSRF-TOKEN"] = tokenMeta.content;
                            try {
                                const resp = await fetch(
                                    `/admin/project-management/disputes/${useId}/approve`,
                                    {
                                        method: "POST",
                                        headers,
                                        body: JSON.stringify({}),
                                    }
                                );
                                const j = await resp.json();
                                hideModal("approveConfirmModal");
                                hideModal("viewDetailsModal");
                                if (j.success) {
                                    if (sectionActions)
                                        sectionActions.classList.add("hidden");
                                    if (sectionResubmission)
                                        sectionResubmission.classList.remove(
                                            "hidden"
                                        );
                                    const tr = document.querySelector(
                                        `tr[data-id="${useId}"]`
                                    );
                                    if (tr) {
                                        tr.dataset.status = "under_review";
                                        const statusCell =
                                            tr.querySelector("td:nth-child(5)");
                                        if (statusCell)
                                            statusCell.innerHTML =
                                                getStatusBadgeHTML(
                                                    "under_review"
                                                );
                                    }
                                    toast(
                                        "Dispute approved for review",
                                        "success"
                                    );
                                    if (window.openViewModal)
                                        window.openViewModal(useId);
                                } else {
                                    toast("Failed to approve dispute", "error");
                                }
                            } catch (e) {
                                console.error(e);
                                toast("Failed to approve dispute", "error");
                            }
                        };
                    }
                } else {
                    modalResolveBtn.onclick = function () {
                        showModal("resolveConfirmModal");
                    };
                }
            }

            // finalResolve button opens the resolution modal (for under_review)
            if (finalResolveBtn) {
                finalResolveBtn.onclick = function () {
                    window.__selectedDisputeId = id;
                    showModal("resolveConfirmModal");
                };
            }

            // confirmResolveBtn submits final resolution notes to backend
            if (confirmResolveBtn) {
                confirmResolveBtn.onclick = async function () {
                    const notes =
                        (document.getElementById("resolutionNotes") || {})
                            .value || "";
                    if (!notes) {
                        toast("Please provide resolution notes", "error");
                        return;
                    }
                    const useId = window.__selectedDisputeId || id;
                    if (!useId) return;
                    const tokenMeta = document.querySelector(
                        'meta[name="csrf-token"]'
                    );
                    const headers = {
                        "X-Requested-With": "XMLHttpRequest",
                        "Content-Type": "application/json",
                    };
                    if (tokenMeta && tokenMeta.content)
                        headers["X-CSRF-TOKEN"] = tokenMeta.content;
                    try {
                        const resp = await fetch(
                            `/admin/project-management/disputes/${useId}/finalize`,
                            {
                                method: "POST",
                                headers,
                                body: JSON.stringify({ notes }),
                            }
                        );
                        const j = await resp.json();
                        hideModal("resolveConfirmModal");
                        hideModal("viewDetailsModal");
                        const tr = document.querySelector(
                            `tr[data-id="${useId}"]`
                        );
                        if (tr) {
                            tr.dataset.status = "resolved";
                            const statusCell =
                                tr.querySelector("td:nth-child(5)");
                            if (statusCell)
                                statusCell.innerHTML =
                                    getStatusBadgeHTML("resolved");
                        }
                        // clear notes
                        const notesEl =
                            document.getElementById("resolutionNotes");
                        if (notesEl) notesEl.value = "";
                        toast(
                            j.success
                                ? "Case marked as resolved"
                                : "Case updated",
                            "success"
                        );
                    } catch (e) {
                        console.error(e);
                        toast("Failed to finalize resolution", "error");
                    }
                };
            }

            window.__selectedDisputeId = id;
            showModal("viewDetailsModal");
        } catch (err) {
            console.error(err);
            toast("Failed to load details", "error");
        }
    };

    // Non-destructive filtering/search that operates on existing DOM rows when server-rendered.
    function initTableControls() {
        const searchInput = document.getElementById("globalSearch");
        if (searchInput) {
            searchInput.addEventListener("input", (e) => {
                const q = e.target.value.trim().toLowerCase();
                if (!tbody) return;
                tbody.querySelectorAll("tr").forEach((row) => {
                    const text = row.textContent.toLowerCase();
                    row.style.display = text.includes(q) ? "" : "none";
                });
            });
        }

        const sortSelect = document.getElementById("sortBy");
        if (sortSelect && tbody) {
            sortSelect.addEventListener("change", (e) => {
                // simple client-side sort based on data-date attribute if present
                const val = e.target.value;
                const rows = Array.from(tbody.querySelectorAll("tr")).filter(
                    (r) => r.style.display !== "none"
                );
                if (rows.length === 0) return;
                rows.sort((a, b) => {
                    const da = a.dataset.date || "";
                    const db = b.dataset.date || "";
                    if (!da || !db) return 0;
                    if (val === "newest") return new Date(db) - new Date(da);
                    if (val === "oldest") return new Date(da) - new Date(db);
                    return 0;
                });
                rows.forEach((r) => tbody.appendChild(r));
            });
        }

        // Filter tabs (data-filter on tabs expected)
        document.querySelectorAll(".filter-tab").forEach((tab) => {
            tab.addEventListener("click", () => {
                document
                    .querySelectorAll(".filter-tab")
                    .forEach((t) => t.classList.remove("active"));
                tab.classList.add("active");
                const filter = tab.dataset.filter;
                if (!tbody) return;
                tbody.querySelectorAll("tr").forEach((row) => {
                    if (!filter || filter === "all") {
                        row.style.display = "";
                        return;
                    }
                    const status = (row.dataset.status || "").toLowerCase();
                    row.style.display = status === filter ? "" : "none";
                });
            });
        });
    }

    // If server rendered rows exist, avoid client table rendering.
    if (hasServerRows) {
        initTableControls();
        // Attach non-destructive handlers for view/resolve modals and controls.
        let selectedDisputeId = null;

        function populateModalFromRow(tr) {
            if (!tr) return;
            const id = tr.dataset.id || "";
            const reporter = tr.dataset.reporter || "";
            const type = tr.dataset.type || "";
            const project = tr.dataset.project || "";
            const subject = tr.dataset.subject || "";
            const description = tr.dataset.description || "";
            const date = tr.dataset.date || "";

            const caseIdEl = document.getElementById("modalCaseId");
            if (caseIdEl) caseIdEl.textContent = `Case #${id}`;
            const reporterEl = document.getElementById("modalReporter");
            if (reporterEl) reporterEl.textContent = reporter || "-";
            const typeEl = document.getElementById("modalType");
            if (typeEl) typeEl.textContent = type || "-";
            const projectEl = document.getElementById("modalProject");
            if (projectEl) projectEl.textContent = project || "-";
            const subjectEl = document.getElementById("modalSubject");
            if (subjectEl) subjectEl.textContent = subject || "-";
            const descEl = document.getElementById("modalDescription");
            if (descEl) descEl.textContent = description || "-";
            const dateEl = document.getElementById("modalDate");
            if (dateEl)
                dateEl.textContent = new Date(date).toLocaleDateString();

            // copy status cell HTML into modalStatus if present
            const statusCell = tr.querySelector("td:nth-child(5)");
            const modalStatus = document.getElementById("modalStatus");
            if (statusCell && modalStatus)
                modalStatus.innerHTML = statusCell.innerHTML;
        }

        function attachRowHandlers() {
            if (!tbody) return;
            // Delegated click handler for view / resolve buttons
            tbody.addEventListener("click", (e) => {
                const viewBtn = e.target.closest(".view-btn");
                if (viewBtn) {
                    const tr = viewBtn.closest("tr");
                    selectedDisputeId = tr ? tr.dataset.id : null;
                    // open full dynamic modal (fetches details and adjusts UI per status)
                    if (selectedDisputeId && window.openViewModal)
                        window.openViewModal(selectedDisputeId);
                    return;
                }

                const resolveBtn = e.target.closest(".resolve-btn");
                if (resolveBtn) {
                    const tr = resolveBtn.closest("tr");
                    selectedDisputeId = tr ? tr.dataset.id : null;
                    window.__selectedDisputeId = selectedDisputeId;
                    // pre-fill case id in modal if opening confirm directly
                    const caseIdEl = document.getElementById("modalCaseId");
                    if (caseIdEl && selectedDisputeId)
                        caseIdEl.textContent = `Case #${selectedDisputeId}`;
                    showModal("resolveConfirmModal");
                    return;
                }
            });

            // modal-close buttons
            document.addEventListener("click", (e) => {
                if (e.target.closest && e.target.closest(".modal-close")) {
                    const modal = e.target.closest(".modal-overlay");
                    if (modal) hideModal(modal.id);
                }
            });

            // overlay click close
            document.querySelectorAll(".modal-overlay").forEach((overlay) => {
                overlay.addEventListener("click", (e) => {
                    if (e.target === overlay) hideModal(overlay.id);
                });
            });

            // confirm resolve action is handled by dynamic handler in openViewModal
            // (server-backed finalize) — keep a safe no-op to avoid duplicate bindings.
            const confirmBtn = document.getElementById("confirmResolveBtn");
            if (confirmBtn) {
                // no-op here; handler set when modal is opened to ensure correct behavior
            }
        }
        // expose reattachment helpers so AJAX-updated HTML can re-bind handlers
        window.attachDisputeRowHandlers = attachRowHandlers;
        window.initDisputesTableControls = initTableControls;

        attachRowHandlers();
        console.debug(
            "Disputes: detected server-rendered rows — handlers attached."
        );
    }

    // If no server rows, fall back to client-side rendering using reportsData (currently empty)
    function renderFromData() {
        if (!tbody) return;
        if (!reportsData || reportsData.length === 0) {
            tbody.innerHTML = `<tr><td colspan="7" class="px-4 py-8 text-center text-gray-500 text-sm">No reports available</td></tr>`;
            return;
        }
        // Simple render (kept minimal — adapt later if API is used)
        tbody.innerHTML = reportsData
            .map(
                (r) => `
      <tr class="report-row" data-status="${r.status || ""}" data-date="${
                    r.date || ""
                }">
        <td class="px-4 py-3">${r.id}</td>
        <td class="px-4 py-3">${r.reporter}</td>
        <td class="px-4 py-3">${r.project || ""}</td>
        <td class="px-4 py-3">${r.type || ""}</td>
        <td class="px-4 py-3">${r.subject || ""}</td>
        <td class="px-4 py-3">${r.status || ""}</td>
        <td class="px-4 py-3">${r.date || ""}</td>
      </tr>
    `
            )
            .join("");
    }

    renderFromData();
    initTableControls();
})();

// --- AJAX filtering and details fetching ---
(function () {
    const tbody = document.getElementById("reportsTableBody");
    const pagination = document.getElementById("paginationLinks");

    function getActiveFilter() {
        const active = document.querySelector(".filter-tab.active");
        return active ? active.dataset.filter : "all";
    }

    function gatherFilters() {
        return {
            status: getActiveFilter(),
            search: (document.getElementById("globalSearch") || {}).value || "",
            date_from: (document.getElementById("dateFrom") || {}).value || "",
            date_to: (document.getElementById("dateTo") || {}).value || "",
        };
    }

    async function fetchDisputes(page = 1) {
        const filters = gatherFilters();
        const params = new URLSearchParams(filters);
        if (page) params.set("page", page);
        const url = window.location.pathname + "?" + params.toString();
        try {
            const res = await fetch(url, {
                headers: { "X-Requested-With": "XMLHttpRequest" },
            });
            const json = await res.json();
            if (json.table !== undefined) {
                if (tbody) tbody.innerHTML = json.table;
                if (pagination) pagination.innerHTML = json.links;
                // reattach handlers on the newly injected HTML
                if (window.attachDisputeRowHandlers)
                    window.attachDisputeRowHandlers();
                if (window.initDisputesTableControls)
                    window.initDisputesTableControls();
            }
        } catch (err) {
            console.error("Failed to fetch disputes", err);
            toast("Failed to update table", "error");
        }
    }

    // debounce helper
    function debounce(fn, delay = 300) {
        let t;
        return (...args) => {
            clearTimeout(t);
            t = setTimeout(() => fn(...args), delay);
        };
    }

    // attach filter UI events
    document.querySelectorAll(".filter-tab").forEach((tab) => {
        tab.addEventListener("click", (e) => {
            document
                .querySelectorAll(".filter-tab")
                .forEach((t) => t.classList.remove("active"));
            tab.classList.add("active");
            fetchDisputes();
        });
    });

    const searchInput = document.getElementById("globalSearch");
    if (searchInput)
        searchInput.addEventListener(
            "input",
            debounce(() => fetchDisputes(), 400)
        );

    const dateFrom = document.getElementById("dateFrom");
    const dateTo = document.getElementById("dateTo");
    if (dateFrom) dateFrom.addEventListener("change", () => fetchDisputes());
    if (dateTo) dateTo.addEventListener("change", () => fetchDisputes());

    const resetBtn = document.getElementById("resetFilters");
    if (resetBtn)
        resetBtn.addEventListener("click", (e) => {
            e.preventDefault();
            if (dateFrom) dateFrom.value = "";
            if (dateTo) dateTo.value = "";
            if (searchInput) searchInput.value = "";
            document
                .querySelectorAll(".filter-tab")
                .forEach((t) => t.classList.remove("active"));
            const firstTab = document.querySelector(".filter-tab");
            if (firstTab) firstTab.classList.add("active");
            fetchDisputes();
        });

    // sort removed from UI; no listener required

    // pagination click (delegated)
    document.addEventListener("click", (e) => {
        const a = e.target.closest("#paginationLinks a");
        if (!a) return;
        e.preventDefault();
        const href = a.getAttribute("href");
        if (!href) return;
        const url = new URL(href, window.location.origin);
        const page = url.searchParams.get("page") || 1;
        fetchDisputes(page);
    });

    // fetch dispute details via AJAX and populate modal
    async function fetchDisputeDetails(id) {
        // delegate to the central openViewModal implementation
        if (window.openViewModal) window.openViewModal(id);
    }

    // listen for delegated view button clicks to fetch details
    if (tbody) {
        tbody.addEventListener("click", (e) => {
            const view = e.target.closest(".view-btn");
            if (!view) return;
            const tr = view.closest("tr");
            if (!tr) return;
            const id = tr.dataset.id;
            if (!id) return;
            if (window.openViewModal) window.openViewModal(id);
        });
    }

    // initial populate: ensure table is populated when page opens
    fetchDisputes();
})();

// Setup event listeners
function setupEventListeners() {
    // Filter tabs
    document.querySelectorAll(".filter-tab").forEach((tab) => {
        tab.addEventListener("click", () => {
            document
                .querySelectorAll(".filter-tab")
                .forEach((t) => t.classList.remove("active"));
            tab.classList.add("active");
            currentFilter = tab.dataset.filter;
            renderTable(currentFilter, currentSort);
        });
    });

    // Sort dropdown
    document.getElementById("sortBy").addEventListener("change", (e) => {
        currentSort = e.target.value;
        renderTable(currentFilter, currentSort);
    });

    // Global search
    document.getElementById("globalSearch").addEventListener("input", (e) => {
        const query = e.target.value.toLowerCase();
        document.querySelectorAll(".report-row").forEach((row) => {
            const text = row.textContent.toLowerCase();
            row.style.display = text.includes(query) ? "" : "none";
        });
    });

    // Modal close buttons
    document.querySelectorAll(".modal-close").forEach((btn) => {
        btn.addEventListener("click", (e) => {
            const modal = e.target.closest(".modal-overlay");
            if (modal) hideModal(modal.id);
        });
    });

    // Close modal on overlay click
    document.querySelectorAll(".modal-overlay").forEach((overlay) => {
        overlay.addEventListener("click", (e) => {
            if (e.target === overlay) {
                hideModal(overlay.id);
            }
        });
    });

    // Resolve button / confirm are handled dynamically per-modal in openViewModal
    // Keep safe no-op bindings to avoid missing element errors in some contexts
    const resolveBtnStub = document.getElementById("resolveBtn");
    if (resolveBtnStub) {
        // handler set dynamically when modal opens
        resolveBtnStub.addEventListener("click", (e) => e.preventDefault());
    }
    const confirmResolveStub = document.getElementById("confirmResolveBtn");
    if (confirmResolveStub) {
        confirmResolveStub.addEventListener("click", (e) => e.preventDefault());
    }

    // Download confirmation
    document
        .getElementById("confirmDownloadBtn")
        .addEventListener("click", () => {
            toast("File download started", "success");
            hideModal("downloadConfirmModal");
        });

    // Delete confirmation
    document
        .getElementById("confirmDeleteBtn")
        .addEventListener("click", () => {
            const reason = document.getElementById("deleteReason").value.trim();
            toast("File deleted successfully", "success");
            hideModal("deleteConfirmModal");
            document.getElementById("deleteReason").value = "";
            // Refresh current modal content if needed
        });

    // Approve resubmitted report button
    document
        .getElementById("approveResubmittedBtn")
        .addEventListener("click", () => {
            if (currentResubmittedReport) {
                document.getElementById("approveResubmissionId").textContent =
                    currentResubmittedReport.id || "-";
                document.getElementById("approveResubmittedBy").textContent =
                    currentResubmittedReport.by || "-";
                document.getElementById("approveResubmissionType").textContent =
                    currentResubmittedReport.type || "-";
                showModal("approveResubmittedConfirmModal");
            }
        });

    // Reject resubmitted report button
    document
        .getElementById("rejectResubmittedBtn")
        .addEventListener("click", () => {
            if (currentResubmittedReport) {
                document.getElementById("rejectResubmissionId").textContent =
                    currentResubmittedReport.id || "-";
                document.getElementById("rejectResubmittedBy").textContent =
                    currentResubmittedReport.by || "-";
                document.getElementById("rejectResubmissionType").textContent =
                    currentResubmittedReport.type || "-";
                showModal("rejectResubmittedConfirmModal");
            }
        });

    // Confirm approve resubmitted report
    document
        .getElementById("confirmApproveResubmittedBtn")
        .addEventListener("click", () => {
            const notes = document.getElementById("approveNotes").value.trim();
            if (currentResubmittedReport) {
                currentResubmittedReport.status = "Approved";
                toast("Report approved successfully", "success");
                hideModal("approveResubmittedConfirmModal");
                document.getElementById("approveNotes").value = "";
                // Refresh the resubmitted report modal with updated status
                viewResubmittedReport(currentResubmittedReport);
            }
        });

    // Confirm reject resubmitted report
    document
        .getElementById("confirmRejectResubmittedBtn")
        .addEventListener("click", () => {
            const reason = document.getElementById("rejectReason").value.trim();
            if (!reason) {
                toast("Please provide a rejection reason", "error");
                return;
            }
            if (currentResubmittedReport) {
                currentResubmittedReport.status = "Rejected";
                toast("Report rejected successfully", "success");
                hideModal("rejectResubmittedConfirmModal");
                document.getElementById("rejectReason").value = "";
                // Refresh the resubmitted report modal with updated status
                viewResubmittedReport(currentResubmittedReport);
            }
        });

    // Confirm download resubmitted file
    document
        .getElementById("confirmDownloadResubmittedBtn")
        .addEventListener("click", () => {
            toast("File download started", "success");
            hideModal("downloadResubmittedFileModal");
        });
}

// Helper functions
function getStatusColor(status) {
    const colors = {
        pending: "bg-amber-500",
        "in-progress": "bg-blue-500",
        resolved: "bg-emerald-500",
        dispute: "bg-red-500",
    };
    return colors[status] || "bg-gray-500";
}

function formatDate(dateString) {
    const date = new Date(dateString);
    const now = new Date();
    const diffTime = Math.abs(now - date);
    const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24));

    if (diffDays === 0) return "Today";
    if (diffDays === 1) return "Yesterday";
    if (diffDays < 7) return `${diffDays} days ago`;

    return date.toLocaleDateString("en-US", {
        month: "short",
        day: "numeric",
        year: "numeric",
    });
}

function showModal(id) {
    const modal = document.getElementById(id);
    if (!modal) return;
    if (modal.parentNode !== document.body) document.body.appendChild(modal);
    if (id === "viewDetailsModal") {
        modal.style.zIndex = 100000;
    } else {
        const view = document.getElementById("viewDetailsModal");
        if (view && view.parentNode !== document.body)
            document.body.appendChild(view);
        if (view) view.style.zIndex = 100000;
        modal.style.zIndex = 100010;
    }
    modal.classList.remove("hidden");
    modal.classList.add("flex");
}

function hideModal(id) {
    const modal = document.getElementById(id);
    if (!modal) return;
    modal.classList.add("hidden");
    modal.classList.remove("flex");
    modal.style.zIndex = "";
}

function toast(message, type = "info") {
    const existing = document.querySelector(".toast");
    if (existing) existing.remove();

    const toast = document.createElement("div");
    toast.className = `toast ${type}`;

    const icon =
        type === "success"
            ? "fi-rr-check-circle"
            : type === "error"
            ? "fi-rr-cross-circle"
            : "fi-rr-info";
    toast.innerHTML = `
    <i class="fi ${icon}"></i>
    <span>${message}</span>
  `;

    document.body.appendChild(toast);

    setTimeout(() => {
        toast.style.opacity = "0";
        toast.style.transform = "translateY(10px)";
        setTimeout(() => toast.remove(), 300);
    }, 3000);
}

// View Resubmitted Report Details
function viewResubmittedReport(report) {
    if (!report) return;

    // Store current report for approve/reject actions
    currentResubmittedReport = report;

    // Populate modal fields
    document.getElementById("resubmittedId").textContent = report.id || "-";
    document.getElementById("resubmittedBy").textContent = report.by || "-";
    document.getElementById("resubmittedType").textContent = report.type || "-";
    document.getElementById("resubmittedDate").textContent = report.date || "-";
    document.getElementById("resubmittedRemarks").value = report.remarks || "";

    // Status badge
    const statusEl = document.getElementById("resubmittedStatus");
    const statusColors = {
        "Under Review": {
            bg: "bg-amber-100",
            text: "text-amber-700",
            border: "border-amber-300",
        },
        Rejected: {
            bg: "bg-red-100",
            text: "text-red-700",
            border: "border-red-300",
        },
        Approved: {
            bg: "bg-emerald-100",
            text: "text-emerald-700",
            border: "border-emerald-300",
        },
    };
    const statusStyle =
        statusColors[report.status] || statusColors["Under Review"];
    statusEl.className = `inline-flex px-4 py-2 rounded-full text-sm font-semibold ${statusStyle.bg} ${statusStyle.text} border ${statusStyle.border}`;
    statusEl.textContent = report.status;

    // Populate files table
    const filesTable = document.getElementById("resubmittedFilesTable");
    if (report.files && report.files.length > 0) {
        filesTable.innerHTML = report.files
            .map(
                (file, index) => `
      <tr class="hover:bg-gray-50 transition">
        <td class="px-4 py-3">
          <input type="checkbox" class="w-4 h-4 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
        </td>
        <td class="px-4 py-3">
          <div class="flex items-center gap-2">
            <div class="w-8 h-8 rounded-lg bg-gradient-to-br from-red-100 to-rose-100 flex items-center justify-center">
              <i class="fi fi-rr-file-pdf text-red-600 text-sm"></i>
            </div>
            <span class="font-medium text-gray-800">${file.name}</span>
          </div>
        </td>
        <td class="px-4 py-3 text-gray-600">${file.date}</td>
        <td class="px-4 py-3 text-gray-600">${file.uploadedBy}</td>
        <td class="px-4 py-3">
          <span class="inline-flex px-2 py-1 rounded-full text-xs font-semibold bg-indigo-100 text-indigo-700">
            ${file.position}
          </span>
        </td>
        <td class="px-4 py-3 text-center">
          <button class="download-resubmitted-file-btn w-8 h-8 rounded-lg bg-gradient-to-br from-emerald-500 to-teal-500 hover:from-emerald-600 hover:to-teal-600 flex items-center justify-center text-white shadow-sm transition mx-auto" title="Download" data-filename="${file.name}" data-reportid="${report.id}">
            <i class="fi fi-rr-download text-xs"></i>
          </button>
        </td>
      </tr>
    `
            )
            .join("");

        // Add click listeners to download buttons
        document
            .querySelectorAll(".download-resubmitted-file-btn")
            .forEach((btn) => {
                btn.addEventListener("click", (e) => {
                    const filename = e.currentTarget.dataset.filename;
                    const reportId = e.currentTarget.dataset.reportid;
                    showDownloadResubmittedFileConfirmation(filename, reportId);
                });
            });
    } else {
        filesTable.innerHTML = `
      <tr>
        <td colspan="6" class="px-4 py-8 text-center text-gray-500 text-sm">
          <i class="fi fi-rr-folder-open text-3xl text-gray-300 mb-2"></i>
          <p>No files uploaded</p>
        </td>
      </tr>
    `;
    }

    // Show modal
    showModal("resubmittedReportModal");
}

// Show download confirmation modal
function showDownloadConfirmation(filename) {
    document.getElementById(
        "downloadFileName"
    ).textContent = `File: ${filename}`;
    document.getElementById("downloadFileNameDisplay").textContent = filename;
    showModal("downloadConfirmModal");
}

// Show delete confirmation modal
function showDeleteConfirmation(filename, uploadedBy, date) {
    document.getElementById("deleteFileNameDisplay").textContent = filename;
    document.getElementById("deleteFileUploader").textContent = uploadedBy;
    document.getElementById("deleteFileDate").textContent = date;
    showModal("deleteConfirmModal");
}

// Show download resubmitted file confirmation modal
function showDownloadResubmittedFileConfirmation(filename, reportId) {
    document.getElementById(
        "downloadResubmittedFileName"
    ).textContent = `File: ${filename}`;
    document.getElementById("downloadResubmittedFileNameDisplay").textContent =
        filename;
    document.getElementById("downloadResubmittedReportId").textContent =
        reportId || "N/A";
    showModal("downloadResubmittedFileModal");
}
