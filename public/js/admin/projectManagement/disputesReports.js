// disputesReports.js — Disputes/Reports page interactivity
// Key improvement: after Resolve is clicked and status becomes "under_review",
// the linked project's details are fetched and displayed inline inside the
// Case Details modal. The admin can take the required action (e.g. Halt Project)
// and then mark the dispute as Resolved — all without leaving the modal.

(() => {
    const reportsData = [];

    const tbody = document.querySelector("#reportsTableBody");
    const hasServerRows = !!(
        tbody &&
        tbody.querySelector("tr") &&
        tbody.querySelector("tr").textContent.trim().length > 0
    );

    // ─────────────────────────────────────────────────────────────
    // UTILITY HELPERS
    // ─────────────────────────────────────────────────────────────

    function showModal(id) {
        const modal = document.getElementById(id);
        if (!modal) return;
        if (modal.parentNode !== document.body) document.body.appendChild(modal);
        if (id === "viewDetailsModal") {
            modal.style.zIndex = 100000;
        } else {
            const view = document.getElementById("viewDetailsModal");
            if (view && view.parentNode !== document.body) document.body.appendChild(view);
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
        const existing = document.querySelector(".toast-notification");
        if (existing) existing.remove();
        const el = document.createElement("div");
        const icon =
            type === "success" ? "fi-rr-check-circle" :
            type === "error"   ? "fi-rr-cross-circle" :
            "fi-rr-info";
        const bgClass =
            type === "success" ? "bg-green-500" :
            type === "error"   ? "bg-red-500"   :
            "bg-blue-500";
        el.className = `toast-notification fixed top-24 right-8 z-[60] px-6 py-4 rounded-lg shadow-2xl transform transition-all duration-500 translate-x-full ${bgClass} text-white font-semibold flex items-center gap-3`;
        el.innerHTML = `<i class="fi ${icon} text-2xl"></i><span>${message}</span>`;
        document.body.appendChild(el);
        setTimeout(() => { el.style.transform = "translateX(0)"; }, 10);
        setTimeout(() => {
            el.style.transform = "translateX(150%)";
            setTimeout(() => el.remove(), 500);
        }, 3000);
    }

    function getStatusBadgeHTML(status) {
        if (!status) status = "";
        const s = status.toString().toLowerCase();
        const map = {
            pending:       "bg-amber-100 text-amber-700",
            open:          "bg-amber-100 text-amber-700",
            under_review:  "bg-amber-100 text-amber-700",
            "under-review":"bg-amber-100 text-amber-700",
            "under review":"bg-amber-100 text-amber-700",
            escalated:     "bg-red-100 text-red-700",
            rejected:      "bg-red-100 text-red-700",
            cancelled:     "bg-red-100 text-red-700",
            resolved:      "bg-emerald-100 text-emerald-700",
        };
        const cls   = map[s] || "bg-gray-100 text-gray-700";
        const label = (s || "").replace(/_/g, " ");
        return `<span class="inline-block px-3 py-1 text-xs rounded-full font-semibold ${cls}">${(label || "-").toUpperCase()}</span>`;
    }

    function getProjectStatusBadgeHTML(status) {
        const s = (status || "").toLowerCase();
        const map = {
            open:          "bg-indigo-100 text-indigo-700",
            in_progress:   "bg-blue-100 text-blue-700",
            bidding_closed:"bg-amber-100 text-amber-700",
            halt:          "bg-red-100 text-red-700",
            completed:     "bg-emerald-100 text-emerald-700",
            terminated:    "bg-gray-100 text-gray-700",
            deleted:       "bg-gray-100 text-gray-700",
            deleted_post:  "bg-gray-100 text-gray-700",
        };
        const cls   = map[s] || "bg-gray-100 text-gray-700";
        const label = s.replace(/_/g, " ").toUpperCase();
        return `<span class="inline-block px-3 py-1 text-xs rounded-full font-semibold ${cls}">${label || "-"}</span>`;
    }

    // ─────────────────────────────────────────────────────────────
    // PENALTY PANEL TOGGLE
    // ─────────────────────────────────────────────────────────────

    (function initPenaltyPanels() {
        ["reject", "resolve"].forEach(prefix => {
            const cb          = document.getElementById(prefix + "ApplyPenalty");
            const fields      = document.getElementById(prefix + "PenaltyFields");
            const typeSelect  = document.getElementById(prefix + "PenaltyType");
            const durationWrap = document.getElementById(prefix + "BanDurationWrap");
            if (cb && fields) {
                cb.addEventListener("change", () => {
                    fields.classList.toggle("hidden", !cb.checked);
                });
            }
            if (typeSelect && durationWrap) {
                typeSelect.addEventListener("change", () => {
                    durationWrap.classList.toggle("hidden", typeSelect.value !== "temporary_ban");
                });
            }
        });
    })();

    // ─────────────────────────────────────────────────────────────
    // LINKED PROJECT PANEL
    // Called after a dispute is moved to "under_review". Fetches the
    // linked project and renders contextual actions in the same modal.
    // ─────────────────────────────────────────────────────────────

    function getRequiredAction(disputeType, requestedAction = "") {
        const type = (disputeType || "").toString().toLowerCase();
        const req  = (requestedAction || "").toString().toLowerCase();
        if (type === "halt" || req.includes("halt")) return "halt_project";
        return null;
    }

    async function loadLinkedProjectPanel(disputeId, disputeMeta = {}) {
        const section       = document.getElementById("sectionProjectContext");
        const projectTitle  = document.getElementById("modalLinkedProjectTitle");
        const projectIdEl   = document.getElementById("modalLinkedProjectId");
        const projectStatus = document.getElementById("modalLinkedProjectStatus");
        const projectOwner  = document.getElementById("modalLinkedProjectOwner");
        const contractor    = document.getElementById("modalLinkedProjectContractor");
        const budget        = document.getElementById("modalLinkedProjectBudget");
        const timeline      = document.getElementById("modalLinkedProjectTimeline");
        const actionWrap    = document.getElementById("modalProjectActionWrap");
        const actionHint    = document.getElementById("modalProjectActionHint");
        const actionBtn     = document.getElementById("modalProjectActionBtn");
        const reasonInput   = document.getElementById("modalProjectActionReason");
        const remarksInput  = document.getElementById("modalProjectRemarks");
        const resolvedActionsWrap = document.getElementById("modalResolvedProjectActions");
        const resumeProjectBtn = document.getElementById("modalResumeProjectBtn");
        const terminateProjectBtn = document.getElementById("modalTerminateProjectBtn");
        const decisionModal = document.getElementById("projectDecisionModal");
        const decisionTitle = document.getElementById("projectDecisionTitle");
        const decisionLabel = document.getElementById("projectDecisionLabel");
        const decisionReason = document.getElementById("projectDecisionReason");
        const decisionRemarks = document.getElementById("projectDecisionRemarks");
        const confirmDecisionBtn = document.getElementById("confirmProjectDecisionBtn");
        const finalAction   = document.getElementById("sectionFinalAction");

        if (!section) return;
        section.classList.remove("hidden");

        if (projectTitle) projectTitle.textContent = "Loading project details...";
        if (projectIdEl) projectIdEl.textContent = "-";
        if (projectStatus) projectStatus.innerHTML = "-";
        if (projectOwner) projectOwner.textContent = "-";
        if (contractor) contractor.textContent = "-";
        if (budget) budget.textContent = "-";
        if (timeline) timeline.textContent = "-";
        if (actionWrap) actionWrap.classList.add("hidden");
        if (resolvedActionsWrap) resolvedActionsWrap.classList.add("hidden");
        if (finalAction) finalAction.classList.add("hidden");

        try {
            const res  = await fetch(
                `/admin/project-management/disputes/${disputeId}/linked-project`,
                { headers: { "X-Requested-With": "XMLHttpRequest" } }
            );
            const json = await res.json();

            if (!json.success || !json.data) {
                if (projectTitle) projectTitle.textContent = "No linked project found for this dispute.";
                return;
            }

            const { project, dispute_type, dispute_status } = json.data;
            const requiredAction = getRequiredAction(
                dispute_type,
                disputeMeta.requested_action || ""
            );
            const actionCompleted = requiredAction === "halt_project"
                ? (project.project_status || "").toLowerCase() === "halt"
                : true;

            if (projectTitle) projectTitle.textContent = project.project_title || "—";
            if (projectIdEl) projectIdEl.textContent = `Project ID: ${project.project_id || "-"}`;
            if (projectStatus) projectStatus.innerHTML = getProjectStatusBadgeHTML(project.project_status);
            if (projectOwner) projectOwner.textContent = project.owner_name || "—";
            if (contractor) contractor.textContent = project.contractor_name || "Not assigned";

            const min = project.budget_range_min;
            const max = project.budget_range_max;
            if (budget) {
                if (min != null && max != null) {
                    budget.textContent = `PHP ${Number(min).toLocaleString()} - PHP ${Number(max).toLocaleString()}`;
                } else {
                    budget.textContent = "—";
                }
            }
            if (timeline) timeline.textContent = project.to_finish ? `${project.to_finish} days` : "—";

            const canApplyAction =
                requiredAction === "halt_project" &&
                (dispute_status || "").toLowerCase() === "under_review" &&
                ["in_progress", "bidding_closed"].includes((project.project_status || "").toLowerCase()) &&
                !actionCompleted;

            const canDecideResolvedProject =
                (dispute_status || "").toLowerCase() === "resolved" &&
                (project.project_status || "").toLowerCase() === "halt";

            if (actionWrap && requiredAction) {
                actionWrap.classList.remove("hidden");
                if (actionHint) {
                    actionHint.textContent = actionCompleted
                        ? "Required action already completed. You can now mark this case as resolved."
                        : "Required action: Halt Project. Complete this action before marking as resolved.";
                }

                if (actionBtn) {
                    actionBtn.textContent = actionCompleted ? "Project Already Halted" : "Halt Project";
                    actionBtn.disabled = !canApplyAction;
                    actionBtn.classList.toggle("opacity-60", actionBtn.disabled);

                    actionBtn.onclick = async function () {
                        const haltReason = (reasonInput?.value || "").trim();
                        const projectRemarks = (remarksInput?.value || "").trim();
                        if (haltReason.length < 10) {
                            toast("Please provide at least 10 characters for action reason", "error");
                            return;
                        }

                        const tokenMeta = document.querySelector('meta[name="csrf-token"]');
                        const headers = {
                            "X-Requested-With": "XMLHttpRequest",
                            "Content-Type": "application/json",
                        };
                        if (tokenMeta && tokenMeta.content) headers["X-CSRF-TOKEN"] = tokenMeta.content;

                        actionBtn.disabled = true;
                        actionBtn.classList.add("opacity-60");
                        actionBtn.innerHTML = '<i class="fi fi-rr-spinner animate-spin mr-2"></i> Applying...';

                        try {
                            const resp = await fetch(`/admin/project-management/disputes/${disputeId}/project-action`, {
                                method: "POST",
                                headers,
                                body: JSON.stringify({
                                    action: "halt_project",
                                    halt_reason: haltReason,
                                    project_remarks: projectRemarks,
                                }),
                            });
                            const result = await resp.json();
                            if (!resp.ok || !result.success) {
                                throw new Error(result.message || "Failed to apply project action");
                            }

                            toast("Project action completed", "success");
                            await loadLinkedProjectPanel(disputeId, disputeMeta);
                            if (finalAction) finalAction.classList.remove("hidden");
                        } catch (e) {
                            console.error(e);
                            toast(e.message || "Failed to apply project action", "error");
                            actionBtn.disabled = false;
                            actionBtn.classList.remove("opacity-60");
                            actionBtn.textContent = "Halt Project";
                        }
                    };
                }
            }

            if (resolvedActionsWrap && canDecideResolvedProject) {
                resolvedActionsWrap.classList.remove("hidden");

                const openProjectDecisionModal = (actionType) => {
                    if (!decisionModal || !confirmDecisionBtn) return;
                    if (decisionTitle) {
                        decisionTitle.textContent = actionType === "resume_project"
                            ? "Resume Project?"
                            : "Terminate Project?";
                    }
                    if (decisionLabel) {
                        decisionLabel.textContent = actionType === "resume_project"
                            ? "This will resume the halted project and restore work progress."
                            : "This will permanently terminate the halted project.";
                    }
                    if (decisionReason) decisionReason.value = "";
                    if (decisionRemarks) decisionRemarks.value = "";

                    confirmDecisionBtn.onclick = async function () {
                        const reason = (decisionReason?.value || "").trim();
                        const remarks = (decisionRemarks?.value || "").trim();
                        if (reason.length < 10) {
                            toast("Please provide at least 10 characters for admin reason", "error");
                            return;
                        }

                        const tokenMeta = document.querySelector('meta[name="csrf-token"]');
                        const headers = {
                            "X-Requested-With": "XMLHttpRequest",
                            "Content-Type": "application/json",
                        };
                        if (tokenMeta && tokenMeta.content) headers["X-CSRF-TOKEN"] = tokenMeta.content;

                        confirmDecisionBtn.disabled = true;
                        confirmDecisionBtn.classList.add("opacity-60");
                        confirmDecisionBtn.innerHTML = '<i class="fi fi-rr-spinner animate-spin mr-2"></i> Applying...';

                        try {
                            const resp = await fetch(`/admin/project-management/disputes/${disputeId}/project-action`, {
                                method: "POST",
                                headers,
                                body: JSON.stringify({
                                    action: actionType,
                                    action_reason: reason,
                                    project_remarks: remarks,
                                }),
                            });
                            const result = await resp.json();
                            if (!resp.ok || !result.success) {
                                throw new Error(result.message || "Failed to apply project decision");
                            }

                            hideModal("projectDecisionModal");
                            toast(
                                actionType === "resume_project"
                                    ? "Project resumed successfully"
                                    : "Project terminated successfully",
                                "success"
                            );
                            await loadLinkedProjectPanel(disputeId, disputeMeta);
                        } catch (e) {
                            console.error(e);
                            toast(e.message || "Failed to apply project decision", "error");
                        } finally {
                            confirmDecisionBtn.disabled = false;
                            confirmDecisionBtn.classList.remove("opacity-60");
                            confirmDecisionBtn.textContent = "Confirm";
                        }
                    };

                    showModal("projectDecisionModal");
                };

                if (resumeProjectBtn) {
                    resumeProjectBtn.onclick = () => openProjectDecisionModal("resume_project");
                }
                if (terminateProjectBtn) {
                    terminateProjectBtn.onclick = () => openProjectDecisionModal("terminate_project");
                }
            }

            if ((dispute_status || "").toLowerCase() === "under_review" && (!requiredAction || actionCompleted)) {
                if (finalAction) finalAction.classList.remove("hidden");
            }

        } catch (err) {
            console.error("Failed to load linked project:", err);
            if (projectTitle) projectTitle.textContent = "Failed to load project details. Please try again.";
        }
    }

    // ─────────────────────────────────────────────────────────────
    // RENDER DISPUTE FILES (Initial Proofs table)
    // ─────────────────────────────────────────────────────────────

    function renderDisputeFiles(files) {
        const tbodyEl = document.getElementById("modalDocumentsTable");
        if (!tbodyEl) return;
        if (!files || files.length === 0) {
            tbodyEl.innerHTML = `<tr><td colspan="3" class="px-4 py-8 text-center text-gray-500 text-sm">No documents attached.</td></tr>`;
            return;
        }
        tbodyEl.innerHTML = files.map((f) => {
            const name    = f.file_name || f.original_name || f.filename || (f.file_path ? f.file_path.split("/").pop() : "Document");
            const date    = f.uploaded_at || f.created_at || f.createdOn || "";
            const uploaded = date ? new Date(date).toLocaleDateString() : "";
            const path    = f.file_path || f.storage_path || f.path || "";
            const link    = path ? (path.startsWith("/") ? path : "/storage/" + path) : "#";
            const ext     = (name.split(".").pop() || "").toLowerCase();
            const isPdf   = ext === "pdf";
            const icon    = isPdf
                ? '<i class="fi fi-rr-file-pdf text-red-600"></i>'
                : '<i class="fi fi-rr-image text-indigo-600"></i>';
            return `
              <tr class="hover:bg-gray-50 transition">
                <td class="px-4 py-3 flex items-center gap-3">
                  <div class="w-8 h-8 rounded-lg bg-gray-100 flex items-center justify-center">${icon}</div>
                  <div class="truncate">${name}</div>
                </td>
                <td class="px-4 py-3">${uploaded}</td>
                <td class="px-4 py-3 text-center">
                  <a href="${link}" class="text-indigo-600 font-medium" target="_blank">View / Download</a>
                </td>
              </tr>`;
        }).join("");
    }

    // ─────────────────────────────────────────────────────────────
    // RESET PROJECT PANEL (called when modal is opened fresh)
    // ─────────────────────────────────────────────────────────────

    function resetProjectPanel() {
        const section = document.getElementById("sectionProjectContext");
        if (section) section.classList.add("hidden");
        ["modalLinkedProjectTitle","modalLinkedProjectId","modalLinkedProjectOwner","modalLinkedProjectContractor","modalLinkedProjectBudget","modalLinkedProjectTimeline"].forEach(id => {
            const el = document.getElementById(id);
            if (el) el.textContent = "—";
        });
        const statusEl = document.getElementById("modalLinkedProjectStatus");
        if (statusEl) statusEl.innerHTML = "";
        const actionWrap = document.getElementById("modalProjectActionWrap");
        if (actionWrap) actionWrap.classList.add("hidden");
        const resolvedActionsWrap = document.getElementById("modalResolvedProjectActions");
        if (resolvedActionsWrap) resolvedActionsWrap.classList.add("hidden");
        const actionHint = document.getElementById("modalProjectActionHint");
        if (actionHint) actionHint.textContent = "-";
        const reasonInput = document.getElementById("modalProjectActionReason");
        if (reasonInput) reasonInput.value = "";
        const remarksInput = document.getElementById("modalProjectRemarks");
        if (remarksInput) remarksInput.value = "";
        const decisionReason = document.getElementById("projectDecisionReason");
        if (decisionReason) decisionReason.value = "";
        const decisionRemarks = document.getElementById("projectDecisionRemarks");
        if (decisionRemarks) decisionRemarks.value = "";
    }

    // ─────────────────────────────────────────────────────────────
    // OPEN VIEW MODAL — main entry point
    // ─────────────────────────────────────────────────────────────

    window.openViewModal = async function (id) {
        if (!id) return;
        try {
            const res  = await fetch(
                `/admin/project-management/disputes/${id}/details`,
                { headers: { "X-Requested-With": "XMLHttpRequest" } }
            );
            const json = await res.json();
            if (!json.success) {
                toast("Failed to load details", "error");
                return;
            }

            const data    = json.data || {};
            const header  = data.header  || {};
            const content = data.content || {};
            const dispute = data.dispute  || {};

            // ── Populate Section A (always visible) ──────────────────

            const caseIdEl = document.getElementById("modalCaseId");
            if (caseIdEl) caseIdEl.textContent = `Case #${dispute.dispute_id || id}`;

            const reporterEl = document.getElementById("modalReporter");
            if (reporterEl) reporterEl.textContent = dispute.reporter_username || dispute.reporter_name || header.reporter_name || "-";

            const againstEl = document.getElementById("modalAgainst");
            if (againstEl) againstEl.textContent = dispute.against_username || dispute.respondent_username || header.against_name || "-";

            // Penalty panel user info
            const penaltyUserName = dispute.against_username || dispute.respondent_username || header.against_name || "-";
            const penaltyUserType = header.against_user_type || dispute.against_user_type || "-";
            ["reject", "resolve"].forEach(function (prefix) {
                const nameEl  = document.getElementById(prefix + "PenaltyUserName");
                const typeEl2 = document.getElementById(prefix + "PenaltyUserType");
                if (nameEl)  nameEl.textContent  = penaltyUserName;
                if (typeEl2) typeEl2.textContent  =
                    penaltyUserType === "property_owner" ? "Property Owner" :
                    penaltyUserType === "contractor"     ? "Contractor" :
                    penaltyUserType;
                const cb     = document.getElementById(prefix + "ApplyPenalty");
                const fields = document.getElementById(prefix + "PenaltyFields");
                if (cb)     cb.checked = false;
                if (fields) fields.classList.add("hidden");
            });

            const typeEl = document.getElementById("modalType");
            if (typeEl) typeEl.textContent = header.dispute_type || dispute.dispute_type || "-";

            const dateEl = document.getElementById("modalDate");
            if (dateEl)  dateEl.textContent = (header.date_submitted || dispute.created_at)
                ? new Date(header.date_submitted || dispute.created_at).toLocaleDateString()
                : "-";

            const statusEl = document.getElementById("modalStatus");
            if (statusEl) {
                const statusVal = (header.dispute_status || dispute.dispute_status || "").toString();
                statusEl.innerHTML = getStatusBadgeHTML(statusVal);
            }

            const projectEl = document.getElementById("modalProject");
            if (projectEl) projectEl.textContent = header.project_title || dispute.project_title || "-";

            const subjectEl = document.getElementById("modalSubject");
            if (subjectEl) subjectEl.textContent = dispute.title || content.subject || "-";

            const descEl = document.getElementById("modalDescription");
            if (descEl) descEl.textContent = dispute.dispute_desc || content.dispute_desc || "-";

            const requestedEl = document.getElementById("modalRequestedAction");
            if (requestedEl) requestedEl.textContent = dispute.requested_action || content.requested_action || "-";

            // ── Initial proofs ────────────────────────────────────────

            const proofs = (data.initial_proofs && data.initial_proofs.length)
                ? data.initial_proofs
                : (data.evidence || []);
            const docsTbody = document.getElementById("modalDocumentsTable");
            if (docsTbody) {
                if (!proofs || proofs.length === 0) {
                    docsTbody.innerHTML = `<tr><td colspan="3" class="px-4 py-8 text-center text-gray-500 text-sm">No documents attached.</td></tr>`;
                } else {
                    docsTbody.innerHTML = proofs.map((f) => {
                        const name     = f.file_name || f.original_name || f.filename || (f.file_path ? f.file_path.split("/").pop() : "Document");
                        const date     = f.uploaded_at || f.created_at || f.createdOn || "";
                        const uploaded = date ? new Date(date).toLocaleDateString() : "";
                        const path     = f.file_path || f.storage_path || f.path || "";
                        const link     = path ? (path.startsWith("/") ? path : "/storage/" + path) : "#";
                        const ext      = (name.split(".").pop() || "").toLowerCase();
                        const isPdf    = ext === "pdf";
                        const icon     = isPdf
                            ? '<i class="fi fi-rr-file-pdf text-red-600"></i>'
                            : '<i class="fi fi-rr-image text-indigo-600"></i>';
                        return `
                          <tr class="hover:bg-gray-50 transition">
                            <td class="px-4 py-3 flex items-center gap-3">
                              <div class="w-8 h-8 rounded-lg bg-gray-100 flex items-center justify-center">${icon}</div>
                              <div class="truncate">${name}</div>
                            </td>
                            <td class="px-4 py-3">${uploaded}</td>
                            <td class="px-4 py-3 text-center">
                              <a href="${link}" class="text-indigo-600 font-medium" target="_blank">View / Download</a>
                            </td>
                          </tr>`;
                    }).join("");
                }
            }

            // ── Section containers ────────────────────────────────────

            const sectionResubmission  = document.getElementById("sectionResubmission");
            const sectionFeedback      = document.getElementById("sectionFeedback");
            const sectionActions       = document.getElementById("sectionActions");
            const sectionFinalAction   = document.getElementById("sectionFinalAction");
            const resubmittedTable     = document.getElementById("modalResubmittedTable");

            if (resubmittedTable)   resubmittedTable.innerHTML = "";
            if (sectionResubmission) sectionResubmission.classList.add("hidden");
            if (sectionFeedback)     sectionFeedback.classList.add("hidden");
            if (sectionActions)      sectionActions.classList.add("hidden");
            if (sectionFinalAction)  sectionFinalAction.classList.add("hidden");

            // Reset the project panel every time the modal opens
            resetProjectPanel();

            const statusVal = (header.dispute_status || dispute.dispute_status || "").toString().toLowerCase();
            const hasResubmissions = (data.resubmissions && data.resubmissions.length) || (data.progressReports && data.progressReports.length);

            const isUnderReview =
                statusVal.indexOf("under") !== -1 ||
                statusVal.indexOf("review") !== -1 ||
                statusVal === "under_review" ||
                statusVal === "escalated";

            // ── Status-driven visibility rules ────────────────────────

            if (statusVal === "open") {
                // Show Section D (Reject / Resolve buttons)
                if (sectionActions) sectionActions.classList.remove("hidden");

            } else if (isUnderReview) {
                // Show Section B (resubmissions)
                if (sectionResubmission) sectionResubmission.classList.remove("hidden");
                if (sectionActions)      sectionActions.classList.add("hidden");

                // Populate resubmissions table
                if (resubmittedTable) {
                    const resubs = (data.resubmissions && data.resubmissions.length)
                        ? data.resubmissions
                        : (data.progressReports || []);
                    if (!resubs || resubs.length === 0) {
                        resubmittedTable.innerHTML = `<tr><td colspan="5" class="px-4 py-8 text-center text-gray-500 text-sm">No resubmissions found.</td></tr>`;
                    } else {
                        resubmittedTable.innerHTML = resubs.map((item) => {
                            const name      = item.original_name || item.file_name || "File";
                            const projectId = item.project_id || item.progress_id || "-";
                            const submitted = item.submitted_at || item.created_at || item.uploaded_at || "";
                            const ds        = submitted ? new Date(submitted).toLocaleDateString() : "-";
                            const st        = (item.progress_status || item.status || "").toString().toLowerCase();
                            const statusMap = { approved: "bg-emerald-100 text-emerald-700", pending: "bg-amber-100 text-amber-700", rejected: "bg-red-100 text-red-700" };
                            const scls      = statusMap[st] || "bg-gray-100 text-gray-700";
                            const path      = item.file_path ? (item.file_path.startsWith("/") ? item.file_path : "/storage/" + item.file_path) : "#";
                            return `
                              <tr class="hover:bg-gray-50 transition">
                                <td class="px-4 py-3">${name}</td>
                                <td class="px-4 py-3 font-mono text-sm text-gray-700">${projectId}</td>
                                <td class="px-4 py-3">${ds}</td>
                                <td class="px-4 py-3"><span class="inline-flex px-3 py-1 rounded-full text-xs font-semibold ${scls}">${(st || "-").toUpperCase()}</span></td>
                                <td class="px-4 py-3 text-center"><a href="${path}" class="text-indigo-600 font-medium" target="_blank">View</a></td>
                              </tr>`;
                        }).join("");
                    }
                }

                // ── NEW: Auto-load the linked project panel ──
                await loadLinkedProjectPanel(id, {
                    requested_action: dispute.requested_action || content.requested_action || "",
                });

            } else if (statusVal === "resolved" || statusVal === "closed") {
                // Show Sections B + C (resubmissions + feedback)
                if (sectionResubmission) sectionResubmission.classList.remove("hidden");
                if (sectionFeedback)     sectionFeedback.classList.remove("hidden");

                // Populate feedback/resolution
                const reporterName  = data.reporter_name || header.reporter_name || dispute.reporter_username || "-";
                const latestStatus  = data.latest_resubmission_status || null;
                const latestDate    = data.latest_resubmission_date   || null;
                const adminResp     = (data.resolution && data.resolution.admin_response) || dispute.admin_response || "";

                const fromEl = document.getElementById("modalFeedbackFrom");
                if (fromEl)  fromEl.textContent = reporterName || "-";

                const idEl2 = document.getElementById("modalResubmissionId");
                if (idEl2)  idEl2.textContent = data.latest_resubmission_project_id || "-";

                const respEl = document.getElementById("modalFeedbackResponse");
                if (respEl) {
                    const statusText = latestStatus ? latestStatus.toString() : "-";
                    const st2        = statusText.toLowerCase();
                    const cls2       =
                        st2 === "approved" ? "bg-emerald-100 text-emerald-700" :
                        st2 === "rejected" ? "bg-red-100 text-red-700" :
                        "bg-amber-100 text-amber-700";
                    respEl.innerHTML = `<span class="inline-flex px-3 py-1 rounded-full text-xs font-semibold ${cls2}">${(statusText || "-").toUpperCase()}</span>`;
                }

                const dateEl2 = document.getElementById("modalFeedbackDate");
                if (dateEl2)  dateEl2.textContent = latestDate ? new Date(latestDate).toLocaleDateString() : "-";

                const remarksEl = document.getElementById("modalFeedbackRemarks");
                if (remarksEl)  remarksEl.textContent = adminResp || "-";

                // Show linked project context for post-resolution decisions.
                await loadLinkedProjectPanel(id, {
                    requested_action: dispute.requested_action || content.requested_action || "",
                });
            }

            // ── Wire Reject button ────────────────────────────────────

            const rejectBtn = document.getElementById("rejectBtn");
            if (rejectBtn) {
                rejectBtn.onclick = function () {
                    window.__selectedDisputeId = id;
                    showModal("rejectConfirmModal");
                };
            }

            // ── Confirm Reject ────────────────────────────────────────

            const confirmRejectBtn = document.getElementById("confirmRejectBtn");
            if (confirmRejectBtn) {
                confirmRejectBtn.onclick = async function () {
                    const reason = (document.getElementById("rejectionReason") || {}).value || "";
                    const useId  = window.__selectedDisputeId || id;
                    if (!useId)  return;

                    const applyPenaltyCb  = document.getElementById("rejectApplyPenalty");
                    const penaltyPayload  = {};
                    if (applyPenaltyCb && applyPenaltyCb.checked) {
                        penaltyPayload.apply_penalty = true;
                        penaltyPayload.penalty_type  = (document.getElementById("rejectPenaltyType") || {}).value || "";
                        penaltyPayload.ban_duration  = (document.getElementById("rejectBanDuration") || {}).value  || "";
                    }

                    const tokenMeta = document.querySelector('meta[name="csrf-token"]');
                    const headers   = { "X-Requested-With": "XMLHttpRequest", "Content-Type": "application/json" };
                    if (tokenMeta && tokenMeta.content) headers["X-CSRF-TOKEN"] = tokenMeta.content;

                    try {
                        const resp = await fetch(
                            `/admin/project-management/disputes/${useId}/reject`,
                            { method: "POST", headers, body: JSON.stringify({ reason, ...penaltyPayload }) }
                        );
                        const j = await resp.json();
                        hideModal("rejectConfirmModal");
                        hideModal("viewDetailsModal");
                        const tr = document.querySelector(`tr[data-id="${useId}"]`);
                        if (tr) {
                            tr.dataset.status = "cancelled";
                            const statusCell  = tr.querySelector("td:nth-child(5)");
                            if (statusCell)   statusCell.innerHTML = getStatusBadgeHTML("cancelled");
                        }
                        toast(j.success ? "Case rejected" : "Case rejected (partial)", "success");
                    } catch (e) {
                        console.error(e);
                        toast("Failed to reject case", "error");
                    }
                };
            }

            // ── Resolve button (open → under_review) ─────────────────

            const modalResolveBtn = document.getElementById("resolveBtn");
            const finalResolveBtn = document.getElementById("finalResolveBtn");
            const confirmResolveBtn = document.getElementById("confirmResolveBtn");

            if (modalResolveBtn) {
                if (statusVal === "open") {
                    // Open the approve confirmation modal
                    modalResolveBtn.onclick = function () {
                        window.__selectedDisputeId = id;
                        showModal("approveConfirmModal");
                    };

                    // Wire the confirm button inside approveConfirmModal
                    const confirmApproveBtn = document.getElementById("confirmApproveBtn");
                    if (confirmApproveBtn) {
                        confirmApproveBtn.onclick = async function () {
                            const useId = window.__selectedDisputeId || id;
                            if (!useId) return;

                            const tokenMeta = document.querySelector('meta[name="csrf-token"]');
                            const headers   = { "X-Requested-With": "XMLHttpRequest", "Content-Type": "application/json" };
                            if (tokenMeta && tokenMeta.content) headers["X-CSRF-TOKEN"] = tokenMeta.content;

                            try {
                                const resp = await fetch(
                                    `/admin/project-management/disputes/${useId}/approve`,
                                    { method: "POST", headers, body: JSON.stringify({}) }
                                );
                                const j = await resp.json();
                                hideModal("approveConfirmModal");

                                if (j.success) {
                                    // Update table row badge
                                    const tr = document.querySelector(`tr[data-id="${useId}"]`);
                                    if (tr) {
                                        tr.dataset.status = "under_review";
                                        const statusCell  = tr.querySelector("td:nth-child(5)");
                                        if (statusCell)   statusCell.innerHTML = getStatusBadgeHTML("under_review");
                                    }

                                    // Update modal status badge
                                    const statusEl2 = document.getElementById("modalStatus");
                                    if (statusEl2)  statusEl2.innerHTML = getStatusBadgeHTML("under_review");

                                    // Show resubmission section, hide primary actions
                                    if (sectionActions)      sectionActions.classList.add("hidden");
                                    if (sectionResubmission) sectionResubmission.classList.remove("hidden");

                                    toast("Dispute approved for review", "success");

                                    // ── NEW: Load linked project panel ──
                                    await loadLinkedProjectPanel(useId, {
                                        requested_action: dispute.requested_action || content.requested_action || "",
                                    });

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

            // ── Final Resolve button (under_review → resolved) ────────

            if (finalResolveBtn) {
                finalResolveBtn.onclick = function () {
                    window.__selectedDisputeId = id;
                    showModal("resolveConfirmModal");
                };
            }

            // ── Confirm Resolution ─────────────────────────────────────

            if (confirmResolveBtn) {
                confirmResolveBtn.onclick = async function () {
                    const notes = (document.getElementById("resolutionNotes") || {}).value || "";
                    if (!notes) {
                        toast("Please provide resolution notes", "error");
                        return;
                    }
                    const useId = window.__selectedDisputeId || id;
                    if (!useId) return;

                    const applyPenaltyCb  = document.getElementById("resolveApplyPenalty");
                    const penaltyPayload  = {};
                    if (applyPenaltyCb && applyPenaltyCb.checked) {
                        penaltyPayload.apply_penalty = true;
                        penaltyPayload.penalty_type  = (document.getElementById("resolvePenaltyType") || {}).value || "";
                        penaltyPayload.ban_duration  = (document.getElementById("resolveBanDuration") || {}).value  || "";
                    }

                    const tokenMeta = document.querySelector('meta[name="csrf-token"]');
                    const headers   = { "X-Requested-With": "XMLHttpRequest", "Content-Type": "application/json" };
                    if (tokenMeta && tokenMeta.content) headers["X-CSRF-TOKEN"] = tokenMeta.content;

                    try {
                        const resp = await fetch(
                            `/admin/project-management/disputes/${useId}/finalize`,
                            { method: "POST", headers, body: JSON.stringify({ notes, ...penaltyPayload }) }
                        );
                        const j = await resp.json();
                        hideModal("resolveConfirmModal");
                        hideModal("viewDetailsModal");

                        const tr = document.querySelector(`tr[data-id="${useId}"]`);
                        if (tr) {
                            tr.dataset.status = "resolved";
                            const statusCell  = tr.querySelector("td:nth-child(5)");
                            if (statusCell)   statusCell.innerHTML = getStatusBadgeHTML("resolved");
                        }

                        const notesEl = document.getElementById("resolutionNotes");
                        if (notesEl) notesEl.value = "";

                        toast(j.success ? "Case marked as resolved" : "Case updated", "success");
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

    // ─────────────────────────────────────────────────────────────
    // TABLE CONTROLS
    // ─────────────────────────────────────────────────────────────

    function initTableControls() {
        const searchInput = document.getElementById("globalSearch");
        if (searchInput) {
            searchInput.addEventListener("input", (e) => {
                const q = e.target.value.trim().toLowerCase();
                if (!tbody) return;
                tbody.querySelectorAll("tr").forEach((row) => {
                    row.style.display = row.textContent.toLowerCase().includes(q) ? "" : "none";
                });
            });
        }

        document.querySelectorAll(".filter-tab").forEach((tab) => {
            tab.addEventListener("click", () => {
                document.querySelectorAll(".filter-tab").forEach((t) => t.classList.remove("active"));
                tab.classList.add("active");
                const filter = tab.dataset.filter;
                if (!tbody) return;
                tbody.querySelectorAll("tr").forEach((row) => {
                    if (!filter || filter === "all") { row.style.display = ""; return; }
                    const status = (row.dataset.status || "").toLowerCase();
                    let matches = false;
                    if (filter === "pending")  matches = status === "open" || status === "pending";
                    else if (filter === "disputes") matches = status === "under_review" || status === "escalated";
                    else if (filter === "resolved") matches = status === "resolved";
                    else matches = status === filter;
                    row.style.display = matches ? "" : "none";
                });
            });
        });
    }

    // ─────────────────────────────────────────────────────────────
    // SERVER-RENDERED ROW HANDLERS
    // ─────────────────────────────────────────────────────────────

    if (hasServerRows) {
        initTableControls();

        function attachRowHandlers() {
            if (!tbody) return;
            tbody.addEventListener("click", (e) => {
                const viewBtn = e.target.closest(".view-btn");
                if (viewBtn) {
                    const tr           = viewBtn.closest("tr");
                    const selectedId   = tr ? tr.dataset.id : null;
                    if (selectedId && window.openViewModal) window.openViewModal(selectedId);
                    return;
                }
                const resolveBtn = e.target.closest(".resolve-btn");
                if (resolveBtn) {
                    const tr           = resolveBtn.closest("tr");
                    const selectedId   = tr ? tr.dataset.id : null;
                    window.__selectedDisputeId = selectedId;
                    const caseIdEl     = document.getElementById("modalCaseId");
                    if (caseIdEl && selectedId) caseIdEl.textContent = `Case #${selectedId}`;
                    showModal("resolveConfirmModal");
                    return;
                }
            });

            document.addEventListener("click", (e) => {
                if (e.target.closest && e.target.closest(".modal-close")) {
                    const modal = e.target.closest(".modal-overlay");
                    if (modal) hideModal(modal.id);
                }
            });

            document.querySelectorAll(".modal-overlay").forEach((overlay) => {
                overlay.addEventListener("click", (e) => {
                    if (e.target === overlay) hideModal(overlay.id);
                });
            });
        }

        window.attachDisputeRowHandlers    = attachRowHandlers;
        window.initDisputesTableControls  = initTableControls;
        attachRowHandlers();
        console.debug("Disputes: detected server-rendered rows — handlers attached.");
    }

    // ─────────────────────────────────────────────────────────────
    // CLIENT-SIDE FALLBACK RENDER (empty reportsData)
    // ─────────────────────────────────────────────────────────────

    function renderFromData() {
        if (!tbody) return;
        if (!reportsData || reportsData.length === 0) {
            tbody.innerHTML = `<tr><td colspan="7" class="px-4 py-8 text-center text-gray-500 text-sm">No reports available</td></tr>`;
            return;
        }
        tbody.innerHTML = reportsData.map((r) => `
          <tr class="report-row" data-status="${r.status || ""}" data-date="${r.date || ""}">
            <td class="px-4 py-3">${r.id}</td>
            <td class="px-4 py-3">${r.reporter}</td>
            <td class="px-4 py-3">${r.project || ""}</td>
            <td class="px-4 py-3">${r.type || ""}</td>
            <td class="px-4 py-3">${r.subject || ""}</td>
            <td class="px-4 py-3">${r.status || ""}</td>
            <td class="px-4 py-3">${r.date || ""}</td>
          </tr>`
        ).join("");
    }

    renderFromData();
    initTableControls();
})();

// ─────────────────────────────────────────────────────────────────────────────
// AJAX FILTERING AND DETAILS FETCHING
// ─────────────────────────────────────────────────────────────────────────────
(function () {
    const tbody      = document.getElementById("reportsTableBody");
    const pagination = document.getElementById("paginationLinks");

    function getActiveFilter() {
        const active = document.querySelector(".filter-tab.active");
        return active ? active.dataset.filter : "all";
    }

    function gatherFilters() {
        return {
            status:    getActiveFilter(),
            search:    (document.getElementById("globalSearch") || {}).value || "",
            date_from: (document.getElementById("dateFrom")     || {}).value || "",
            date_to:   (document.getElementById("dateTo")       || {}).value || "",
        };
    }

    async function fetchDisputes(page = 1) {
        const filters = gatherFilters();
        const params  = new URLSearchParams(filters);
        if (page) params.set("page", page);
        const url = window.location.pathname + "?" + params.toString();
        try {
            const res  = await fetch(url, { headers: { "X-Requested-With": "XMLHttpRequest" } });
            const json = await res.json();
            if (json.table !== undefined) {
                if (tbody)      tbody.innerHTML      = json.table;
                if (pagination) pagination.innerHTML = json.links;
                if (window.attachDisputeRowHandlers)   window.attachDisputeRowHandlers();
                if (window.initDisputesTableControls)  window.initDisputesTableControls();
            }
        } catch (err) {
            console.error("Failed to fetch disputes", err);
        }
    }

    function debounce(fn, delay = 300) {
        let t;
        return (...args) => { clearTimeout(t); t = setTimeout(() => fn(...args), delay); };
    }

    document.querySelectorAll(".filter-tab").forEach((tab) => {
        tab.addEventListener("click", () => {
            document.querySelectorAll(".filter-tab").forEach((t) => t.classList.remove("active"));
            tab.classList.add("active");
            fetchDisputes();
        });
    });

    const searchInput = document.getElementById("globalSearch");
    if (searchInput)
        searchInput.addEventListener("input", debounce(() => fetchDisputes(), 400));

    const dateFrom = document.getElementById("dateFrom");
    const dateTo   = document.getElementById("dateTo");
    if (dateFrom) dateFrom.addEventListener("change", () => fetchDisputes());
    if (dateTo)   dateTo.addEventListener("change",   () => fetchDisputes());

    const resetBtn = document.getElementById("resetFilters");
    if (resetBtn)
        resetBtn.addEventListener("click", (e) => {
            e.preventDefault();
            if (dateFrom)    dateFrom.value    = "";
            if (dateTo)      dateTo.value      = "";
            if (searchInput) searchInput.value = "";
            document.querySelectorAll(".filter-tab").forEach((t) => t.classList.remove("active"));
            const firstTab = document.querySelector(".filter-tab");
            if (firstTab) firstTab.classList.add("active");
            fetchDisputes();
        });

    // Delegated pagination click
    document.addEventListener("click", (e) => {
        const a = e.target.closest("#paginationLinks a");
        if (!a) return;
        e.preventDefault();
        const href = a.getAttribute("href");
        if (!href) return;
        const url  = new URL(href, window.location.origin);
        const page = url.searchParams.get("page") || 1;
        fetchDisputes(page);
    });

    // Delegated view-button click
    if (tbody) {
        tbody.addEventListener("click", (e) => {
            const view = e.target.closest(".view-btn");
            if (!view)  return;
            const tr   = view.closest("tr");
            if (!tr)    return;
            const id   = tr.dataset.id;
            if (!id)    return;
            if (window.openViewModal) window.openViewModal(id);
        });
    }

    // Initial populate
    fetchDisputes();
})();