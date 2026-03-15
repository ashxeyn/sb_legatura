// aiManagement.js — AI Management page interactivity

(() => {
    // ─────────────────────────────────────────────────────────────
    // UTILITY HELPERS
    // ─────────────────────────────────────────────────────────────

    function showModal(id) {
        const modal = document.getElementById(id);
        if (!modal) return;
        modal.classList.remove("hidden");
        modal.classList.add("flex");
    }

    function hideModal(id) {
        const modal = document.getElementById(id);
        if (!modal) return;
        modal.classList.add("hidden");
        modal.classList.remove("flex");
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

    // ─────────────────────────────────────────────────────────────
    // INITIALIZE TOM SELECT
    // ─────────────────────────────────────────────────────────────

    let projectSelectInstance = null;
    let deleteAnalysisId = null;

    document.addEventListener("DOMContentLoaded", function () {
        // Modal close handlers
        document.querySelectorAll(".modal-close").forEach(btn => {
            btn.addEventListener("click", () => hideModal("detailsModal"));
        });

        document.querySelectorAll(".modal-close-analysis").forEach(btn => {
            btn.addEventListener("click", () => {
                hideModal("analysisModal");
                resetAnalysisModal();
            });
        });

        document.querySelectorAll(".modal-close-delete").forEach(btn => {
            btn.addEventListener("click", () => hideModal("deleteModal"));
        });

        // Initialize filters
        initializeFilters();
    });

    // ─────────────────────────────────────────────────────────────
    // INITIALIZE FILTERS
    // ─────────────────────────────────────────────────────────────

    function initializeFilters() {
        const searchInput = document.getElementById("searchInput");
        const dateFrom = document.getElementById("dateFrom");
        const dateTo = document.getElementById("dateTo");
        const verdictFilter = document.getElementById("verdictFilter");
        const resetBtn = document.getElementById("resetFilters");

        if (searchInput) {
            searchInput.addEventListener("input", applyFilters);
        }
        
        if (dateFrom) {
            // Update min constraint on dateTo whenever dateFrom changes
            dateFrom.addEventListener("input", function() {
                if (this.value && dateTo) {
                    dateTo.setAttribute('min', this.value);
                    // If dateTo is already set and is now invalid, clear it silently
                    if (dateTo.value && dateTo.value < this.value) {
                        dateTo.value = '';
                    }
                } else if (dateTo) {
                    dateTo.removeAttribute('min');
                }
            });
            
            // Apply filters when date is fully selected
            dateFrom.addEventListener("change", applyFilters);
        }
        
        if (dateTo) {
            // Update max constraint on dateFrom whenever dateTo changes
            dateTo.addEventListener("input", function() {
                if (this.value && dateFrom) {
                    dateFrom.setAttribute('max', this.value);
                } else if (dateFrom) {
                    dateFrom.removeAttribute('max');
                }
            });
            
            // Validate only on blur (when user clicks away) or Enter key
            dateTo.addEventListener("blur", function() {
                validateAndApplyDateFilter();
            });
            
            dateTo.addEventListener("keypress", function(e) {
                if (e.key === 'Enter') {
                    validateAndApplyDateFilter();
                }
            });
            
            // Apply filters when date is fully selected (without validation on change)
            dateTo.addEventListener("change", function() {
                const fromValue = dateFrom ? dateFrom.value : '';
                // Only apply if valid or empty
                if (!this.value || !fromValue || this.value >= fromValue) {
                    clearDateError();
                    applyFilters();
                }
            });
        }
        
        if (verdictFilter) {
            verdictFilter.addEventListener("change", applyFilters);
        }
        
        if (resetBtn) {
            resetBtn.addEventListener("click", resetFilters);
        }
    }

    // ─────────────────────────────────────────────────────────────
    // VALIDATE AND APPLY DATE FILTER
    // ─────────────────────────────────────────────────────────────

    function validateAndApplyDateFilter() {
        const dateFrom = document.getElementById("dateFrom");
        const dateTo = document.getElementById("dateTo");
        
        if (!dateFrom || !dateTo) return;
        
        const fromValue = dateFrom.value;
        const toValue = dateTo.value;
        
        // Only validate if both dates are set
        if (fromValue && toValue) {
            if (toValue < fromValue) {
                showDateError();
                dateTo.value = '';
                // Apply filters after clearing
                setTimeout(() => {
                    applyFilters();
                }, 100);
                return;
            }
        }
        
        clearDateError();
        applyFilters();
    }

    // ─────────────────────────────────────────────────────────────
    // SHOW/CLEAR DATE ERROR
    // ─────────────────────────────────────────────────────────────

    function showDateError() {
        const dateToWrapper = document.getElementById("dateToWrapper");
        const dateToError = document.getElementById("dateToError");
        
        if (dateToWrapper) {
            dateToWrapper.classList.remove('border-indigo-200');
            dateToWrapper.classList.add('border-red-500');
        }
        
        if (dateToError) {
            dateToError.classList.remove('hidden');
        }
        
        // Auto-hide after 3 seconds
        setTimeout(clearDateError, 3000);
    }

    function clearDateError() {
        const dateToWrapper = document.getElementById("dateToWrapper");
        const dateToError = document.getElementById("dateToError");
        
        if (dateToWrapper) {
            dateToWrapper.classList.remove('border-red-500');
            dateToWrapper.classList.add('border-indigo-200');
        }
        
        if (dateToError) {
            dateToError.classList.add('hidden');
        }
    }

    // ─────────────────────────────────────────────────────────────
    // APPLY FILTERS
    // ─────────────────────────────────────────────────────────────

    function applyFilters() {
        const searchInput = document.getElementById("searchInput");
        const dateFrom = document.getElementById("dateFrom");
        const dateTo = document.getElementById("dateTo");
        const verdictFilter = document.getElementById("verdictFilter");
        const tbody = document.getElementById("predictionTableBody");

        if (!tbody) return;

        const searchTerm = searchInput ? searchInput.value.toLowerCase().trim() : "";
        const fromDate = dateFrom ? dateFrom.value : "";
        const toDate = dateTo ? dateTo.value : "";
        const verdict = verdictFilter ? verdictFilter.value : "";

        const rows = tbody.querySelectorAll("tr[data-project]");
        let visibleCount = 0;

        rows.forEach(row => {
            const projectName = row.getAttribute("data-project") || "";
            const rowVerdict = row.getAttribute("data-verdict") || "";
            const rowDate = row.getAttribute("data-date") || "";

            let show = true;

            // Search filter
            if (searchTerm && !projectName.includes(searchTerm)) {
                show = false;
            }

            // Verdict filter
            if (verdict && rowVerdict !== verdict) {
                show = false;
            }

            // Date range filter
            if (fromDate || toDate) {
                const rowDateObj = new Date(rowDate);
                if (fromDate) {
                    const fromDateObj = new Date(fromDate);
                    if (rowDateObj < fromDateObj) {
                        show = false;
                    }
                }
                if (toDate) {
                    const toDateObj = new Date(toDate);
                    toDateObj.setHours(23, 59, 59, 999); // End of day
                    if (rowDateObj > toDateObj) {
                        show = false;
                    }
                }
            }

            if (show) {
                row.style.display = "";
                visibleCount++;
            } else {
                row.style.display = "none";
            }
        });

        // Show/hide "no results" message
        const noResultsRow = document.getElementById("noResultsRow");
        if (noResultsRow) {
            if (visibleCount === 0 && rows.length > 0) {
                noResultsRow.style.display = "";
                noResultsRow.querySelector("td").textContent = "No matching results found";
            } else {
                noResultsRow.style.display = "none";
            }
        }
    }

    // ─────────────────────────────────────────────────────────────
    // RESET FILTERS
    // ─────────────────────────────────────────────────────────────

    function resetFilters() {
        const searchInput = document.getElementById("searchInput");
        const dateFrom = document.getElementById("dateFrom");
        const dateTo = document.getElementById("dateTo");
        const verdictFilter = document.getElementById("verdictFilter");

        if (searchInput) searchInput.value = "";
        if (dateFrom) {
            dateFrom.value = "";
            dateFrom.removeAttribute('max');
        }
        if (dateTo) {
            dateTo.value = "";
            dateTo.removeAttribute('min');
        }
        if (verdictFilter) verdictFilter.value = "";

        clearDateError();
        applyFilters();
        toast("Filters reset", "info");
    }

    // ─────────────────────────────────────────────────────────────
    // OPEN ANALYSIS MODAL
    // ─────────────────────────────────────────────────────────────

    function openAnalysisModal() {
        showModal("analysisModal");
        
        // Initialize Tom Select for modal if not already initialized
        if (!projectSelectInstance) {
            projectSelectInstance = new TomSelect("#projectSelectModal", {
                create: false,
                sortField: { field: "text", direction: "asc" },
                placeholder: "Start typing to search for a project...",
                maxOptions: 20, // Reduced to show fewer options
                openOnFocus: false, // Don't open on focus
                dropdownParent: null, // Keep dropdown inside the modal (default behavior)
                controlInput: '<input type="text" autocomplete="off" />',
                onType: function(str) {
                    // Only open dropdown if user has typed something
                    if (str && str.length > 0) {
                        this.open();
                    } else {
                        this.close();
                    }
                },
                onFocus: function() {
                    // Don't open dropdown on focus if input is empty
                    const query = this.control_input.value;
                    if (!query || query.length === 0) {
                        this.close();
                    }
                },
                onBlur: function() {
                    // Close dropdown when focus is lost
                    this.close();
                },
                render: {
                    no_results: function(data, escape) {
                        return '<div class="no-results px-4 py-3 text-sm text-gray-500">No projects found. Try a different search term.</div>';
                    }
                }
            });
        } else {
            projectSelectInstance.clear();
            projectSelectInstance.close();
        }
        
        // Focus the input after a short delay
        setTimeout(() => {
            const input = document.querySelector('#projectSelectModal-ts-control input');
            if (input) {
                input.focus();
            }
        }, 150);
    }

    // ─────────────────────────────────────────────────────────────
    // RESET ANALYSIS MODAL
    // ─────────────────────────────────────────────────────────────

    function resetAnalysisModal() {
        const selectionStep = document.getElementById("projectSelectionStep");
        const progressStep = document.getElementById("analysisProgressStep");
        const startBtn = document.getElementById("btnStartAnalysis");
        const errorDiv = document.getElementById("projectSelectError");
        const selectWrapper = document.querySelector('.ts-wrapper');

        if (selectionStep) selectionStep.classList.remove("hidden");
        if (progressStep) {
            progressStep.classList.add("hidden");
            progressStep.innerHTML = "";
        }
        if (startBtn) {
            startBtn.disabled = false;
            startBtn.innerHTML = '<i class="fi fi-br-play text-xs"></i> Start Analysis';
            startBtn.classList.remove("hidden");
        }
        
        // Hide error and reset border
        if (errorDiv) {
            errorDiv.classList.add('hidden');
        }
        if (selectWrapper) {
            selectWrapper.querySelector('.ts-control').style.borderColor = '#e5e7eb';
        }
        
        if (projectSelectInstance) {
            projectSelectInstance.clear();
        }
    }

    // ─────────────────────────────────────────────────────────────
    // START ANALYSIS
    // ─────────────────────────────────────────────────────────────

    async function startAnalysis() {
        const projectId = projectSelectInstance ? projectSelectInstance.getValue() : '';
        const selectionStep = document.getElementById("projectSelectionStep");
        const progressStep = document.getElementById("analysisProgressStep");
        const startBtn = document.getElementById("btnStartAnalysis");
        const errorDiv = document.getElementById("projectSelectError");
        const selectWrapper = document.querySelector('.ts-wrapper');

        if (!projectId) {
            // Show inline error
            if (errorDiv) {
                errorDiv.classList.remove('hidden');
            }
            // Add red border to select field
            if (selectWrapper) {
                selectWrapper.querySelector('.ts-control').style.borderColor = '#ef4444';
            }
            return;
        }

        // Hide error and reset border
        if (errorDiv) {
            errorDiv.classList.add('hidden');
        }
        if (selectWrapper) {
            selectWrapper.querySelector('.ts-control').style.borderColor = '#e5e7eb';
        }

        const tokenMeta = document.querySelector('meta[name="csrf-token"]');
        const csrfToken = tokenMeta ? tokenMeta.content : '';

        // Hide selection, show progress
        selectionStep.classList.add("hidden");
        progressStep.classList.remove("hidden");
        startBtn.disabled = true;
        startBtn.innerHTML = '<i class="fi fi-rr-spinner animate-spin mr-2"></i> Analyzing...';

        progressStep.innerHTML = `
            <div class="bg-white border border-orange-200 rounded-xl p-8 shadow-sm">
                <div class="flex flex-col items-center justify-center text-orange-600">
                    <div class="animate-spin h-16 w-16 border-4 border-orange-600 border-t-transparent rounded-full mb-6"></div>
                    <p class="text-base font-bold animate-pulse mb-2">Running Random Forest Model...</p>
                    <p class="text-sm text-orange-400 mb-6">Fetching Weather, Milestone Data & Contractor History</p>
                    <div class="w-full max-w-md space-y-2 text-sm text-orange-500">
                        <div class="flex items-center gap-2">
                            <i class="fi fi-rr-check-circle text-emerald-500"></i>
                            <p>Loading project data</p>
                        </div>
                        <div class="flex items-center gap-2">
                            <i class="fi fi-rr-check-circle text-emerald-500"></i>
                            <p>Computing risk factors</p>
                        </div>
                        <div class="flex items-center gap-2">
                            <div class="animate-spin h-4 w-4 border-2 border-orange-500 border-t-transparent rounded-full"></div>
                            <p>Processing environment variables...</p>
                        </div>
                    </div>
                </div>
            </div>`;

        try {
            const response = await fetch(`/admin/global-management/ai-management/analyze/${projectId}`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                    'X-Requested-With': 'XMLHttpRequest'
                }
            });
            const res = await response.json();

            if (res.success) {
                const data = res.data;
                progressStep.innerHTML = `
                    <div class="bg-gradient-to-br from-emerald-50 to-teal-50 border border-emerald-200 rounded-xl p-6 shadow-sm">
                        <div class="flex items-start gap-4 mb-6">
                            <div class="w-14 h-14 bg-emerald-600 rounded-full flex items-center justify-center text-white flex-shrink-0">
                                <i class="fi fi-rr-check-circle text-2xl"></i>
                            </div>
                            <div class="flex-1">
                                <h4 class="text-gray-900 font-bold text-xl mb-2">Analysis Complete</h4>
                                <p class="text-gray-600 text-sm">${data.analysis_report.conclusion || 'Analysis successful.'}</p>
                            </div>
                        </div>
                        <div class="grid grid-cols-3 gap-4 pt-4 border-t border-emerald-200">
                            <div class="text-center p-4 bg-white rounded-xl">
                                <span class="text-xs font-bold text-gray-500 uppercase block mb-2">Verdict</span>
                                <p class="text-2xl font-black ${data.prediction.prediction === 'DELAYED' ? 'text-red-600' : 'text-emerald-600'}">
                                    ${data.prediction.prediction}
                                </p>
                            </div>
                            <div class="text-center p-4 bg-white rounded-xl">
                                <span class="text-xs font-bold text-gray-500 uppercase block mb-2">Risk Level</span>
                                <p class="text-2xl font-black text-gray-800">
                                    ${(data.prediction.delay_probability * 100).toFixed(1)}%
                                </p>
                            </div>
                            <div class="text-center p-4 bg-white rounded-xl">
                                <span class="text-xs font-bold text-gray-500 uppercase block mb-2">Status</span>
                                <p class="text-2xl font-black text-orange-600">Active</p>
                            </div>
                        </div>
                    </div>`;
                
                startBtn.innerHTML = '<i class="fi fi-rr-disk"></i> Save & Refresh';
                startBtn.disabled = false;
                startBtn.onclick = () => location.reload();
                
                toast('Analysis completed successfully', 'success');
            } else {
                throw new Error(res.message || 'Analysis failed');
            }
        } catch (err) {
            console.error(err);
            progressStep.innerHTML = `
                <div class="bg-red-50 border border-red-200 rounded-xl p-6 shadow-sm">
                    <div class="flex items-start gap-4">
                        <div class="flex-shrink-0">
                            <i class="fi fi-rr-exclamation text-red-600 text-3xl"></i>
                        </div>
                        <div class="flex-1">
                            <p class="font-bold text-lg text-red-900 mb-2">Analysis Failed</p>
                            <p class="text-sm text-red-700">${err.message}</p>
                        </div>
                    </div>
                </div>`;
            
            startBtn.innerHTML = '<i class="fi fi-br-refresh"></i> Try Again';
            startBtn.disabled = false;
            startBtn.onclick = () => {
                resetAnalysisModal();
                openAnalysisModal();
            };
            
            toast(err.message || 'Analysis failed', 'error');
        }
    }

    // ─────────────────────────────────────────────────────────────
    // DELETE ANALYSIS
    // ─────────────────────────────────────────────────────────────

    function confirmDelete(analysisId, projectName) {
        deleteAnalysisId = analysisId;
        const projectNameEl = document.getElementById("deleteProjectName");
        if (projectNameEl) {
            projectNameEl.textContent = projectName;
        }
        showModal("deleteModal");
    }

    async function deleteAnalysis() {
        if (!deleteAnalysisId) return;

        const confirmBtn = document.getElementById("confirmDeleteBtn");
        const tokenMeta = document.querySelector('meta[name="csrf-token"]');
        const csrfToken = tokenMeta ? tokenMeta.content : '';

        if (confirmBtn) {
            confirmBtn.disabled = true;
            confirmBtn.innerHTML = '<i class="fi fi-rr-spinner animate-spin mr-2"></i> Deleting...';
        }

        try {
            const response = await fetch(`/admin/global-management/ai-management/delete/${deleteAnalysisId}`, {
                method: 'DELETE',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                    'X-Requested-With': 'XMLHttpRequest'
                }
            });
            const res = await response.json();

            if (res.success) {
                hideModal("deleteModal");
                toast('Analysis deleted successfully', 'success');
                setTimeout(() => location.reload(), 1000);
            } else {
                throw new Error(res.message || 'Failed to delete analysis');
            }
        } catch (err) {
            console.error(err);
            toast(err.message || 'Failed to delete analysis', 'error');
        } finally {
            if (confirmBtn) {
                confirmBtn.disabled = false;
                confirmBtn.innerHTML = '<i class="fi fi-rr-trash text-sm"></i> Delete Analysis';
            }
        }
    }

    // ─────────────────────────────────────────────────────────────
    // SHOW DETAILS MODAL
    // ─────────────────────────────────────────────────────────────

    function parseSnapshotPayload(payload) {
        if (payload && typeof payload === "object") return payload;
        if (typeof payload !== "string") return null;

        const trimmed = payload.trim();
        if (!trimmed) return null;

        try {
            return JSON.parse(trimmed);
        } catch (_) {
            const textarea = document.createElement("textarea");
            textarea.innerHTML = trimmed;
            const decoded = textarea.value;

            try {
                return JSON.parse(decoded);
            } catch (_) {
                return null;
            }
        }
    }

    function showDetails(data) {
        const modal = document.getElementById("detailsModal");
        const body = document.getElementById("modalBody");
        const d = parseSnapshotPayload(data);

        if (!modal || !body) return;

        if (!d || !d.prediction || !d.analysis_report) {
            body.innerHTML = `
                <div class="bg-red-50 border border-red-200 rounded-xl p-6 shadow-sm">
                    <div class="flex items-start gap-3">
                        <i class="fi fi-rr-exclamation text-red-600 text-xl mt-0.5"></i>
                        <div>
                            <p class="text-sm font-semibold text-red-800 mb-1">Unable to load analysis details</p>
                            <p class="text-xs text-red-700">This record has an invalid or incomplete AI snapshot.</p>
                        </div>
                    </div>
                </div>`;
            showModal("detailsModal");
            toast("Unable to load full details for this analysis", "error");
            return;
        }

        const prediction = d.prediction || {};
        const report = d.analysis_report || {};
        const pacingStatus = report.pacing_status || {};
        const contractor = report.contractor_audit || {};
        const weather = d.weather || {};
        const delayProbability = Number(prediction.delay_probability || 0);

        const isDelayed = prediction.prediction === "DELAYED";
        const riskColor = isDelayed ? "text-red-600" : "text-emerald-600";
        const riskBorder = isDelayed ? "border-red-200" : "border-emerald-200";
        const riskBg = isDelayed ? "bg-red-50" : "bg-emerald-50";
        const isFlagged = Boolean(contractor.flagged || (contractor.status && String(contractor.status).includes("Flagged")));

        const details = Array.isArray(pacingStatus.details) ? pacingStatus.details : [];
        const pacingRows = details.length > 0
            ? details.map(item => {
                const isRejected = item.status === "rejected";
                const variance = Number(item.days_variance || 0);
                const isLate = variance > 0;
                return `
                    <tr class="hover:bg-gray-50 transition">
                        <td class="px-4 py-3 text-sm font-medium text-gray-700">${item.title || "N/A"}</td>
                        <td class="px-4 py-3 text-xs font-bold uppercase ${isRejected ? "text-red-600" : "text-gray-500"}">${item.status || "N/A"}</td>
                        <td class="px-4 py-3 font-mono text-sm ${isLate ? "text-amber-600" : "text-emerald-600"}">
                            ${variance > 0 ? "+" + variance : variance} days
                        </td>
                        <td class="px-4 py-3">
                            <span class="inline-block px-3 py-1 rounded-full text-xs font-semibold
                                ${isRejected ? "bg-red-100 text-red-700" : isLate ? "bg-amber-100 text-amber-700" : "bg-emerald-100 text-emerald-700"}">
                                ${item.pacing_label || "N/A"}
                            </span>
                        </td>
                    </tr>`;
            }).join("")
            : '<tr><td colspan="4" class="px-4 py-8 text-center text-sm text-gray-500 italic">No milestone data available yet.</td></tr>';

        const recs = Array.isArray(d.dds_recommendations) ? d.dds_recommendations : [];
        const recItems = recs.length > 0
            ? recs.map(rec => {
                const text = String(rec);
                return `
                    <div class="flex gap-3 bg-white p-4 rounded-xl border border-gray-200 shadow-sm hover:shadow-md transition">
                        <div class="flex-shrink-0 mt-1">
                            ${text.includes("QUALITY") ? '<i class="fi fi-rr-shield-exclamation text-red-500 text-xl"></i>'
                              : text.includes("WEATHER") || text.includes("RAIN") ? '<i class="fi fi-rr-cloud-disabled text-orange-500 text-xl"></i>'
                              : '<i class="fi fi-rr-bulb text-purple-500 text-xl"></i>'}
                        </div>
                        <p class="text-gray-700 text-sm font-medium leading-relaxed">${text}</p>
                    </div>`;
            }).join("")
            : '<p class="text-gray-400 italic text-sm">No recommendations generated.</p>';

        const dots = [1, 2, 3, 4, 5].map(i =>
            `<div class="w-2.5 h-2.5 rounded-full ${i <= Number(d.weather_severity || 0) ? "bg-orange-500" : "bg-gray-300"}"></div>`
        ).join("");

        body.innerHTML = `
            <div class="bg-orange-50 border-l-4 border-orange-500 p-5 rounded-r-xl mb-6">
                <h4 class="text-orange-900 font-bold text-sm uppercase tracking-wider mb-2">Executive Summary</h4>
                <p class="text-orange-800 text-sm leading-relaxed">"${report.conclusion || "No conclusion available."}"</p>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <div class="lg:col-span-1 space-y-4">
                    <div class="${riskBg} border ${riskBorder} rounded-2xl p-5 shadow-sm">
                        <h5 class="text-xs font-bold text-gray-500 uppercase tracking-wider mb-4">Risk Assessment</h5>
                        <div class="flex items-center justify-between mb-4">
                            <span class="text-3xl font-black ${riskColor}">${prediction.prediction || "N/A"}</span>
                            <div class="text-right">
                                <span class="block text-xs text-gray-500 mb-1">Probability</span>
                                <span class="text-2xl font-bold text-gray-900">${(delayProbability * 100).toFixed(1)}%</span>
                            </div>
                        </div>
                        <div class="w-full bg-gray-200 rounded-full h-3">
                            <div class="h-3 rounded-full ${isDelayed ? "bg-red-500" : "bg-emerald-500"}" style="width:${(delayProbability * 100)}%"></div>
                        </div>
                        <p class="text-xs text-gray-600 mt-3 italic">${prediction.reason || ""}</p>
                    </div>

                    <div class="bg-white border border-gray-200 rounded-2xl p-5 shadow-sm relative overflow-hidden">
                        ${isFlagged ? '<div class="absolute top-0 right-0 bg-red-600 text-white text-xs font-bold px-3 py-1.5 rounded-bl-lg">FLAGGED</div>' : ""}
                        <h5 class="text-xs font-bold text-gray-500 uppercase tracking-wider mb-4">Contractor Vetting</h5>
                        <div class="space-y-3">
                            <div class="flex justify-between items-center">
                                <span class="text-sm text-gray-600">Experience</span>
                                <span class="text-sm font-bold text-gray-900">${contractor.experience || "N/A"}</span>
                            </div>
                            <div class="flex justify-between items-center">
                                <span class="text-sm text-gray-600">Success Rate</span>
                                <span class="text-sm font-bold ${isFlagged ? "text-red-600" : "text-emerald-600"}">${contractor.historical_success || "N/A"}</span>
                            </div>
                            <div class="flex justify-between items-center">
                                <span class="text-sm text-gray-600">Audit Status</span>
                                <span class="inline-block px-3 py-1 rounded-full text-xs font-bold ${isFlagged ? "text-red-700 bg-red-100" : "text-emerald-700 bg-emerald-100"}">
                                    ${contractor.status || (isFlagged ? "High Risk" : "Good Standing")}
                                </span>
                            </div>
                        </div>
                    </div>

                    <div class="bg-white border border-gray-200 rounded-2xl p-5 shadow-sm">
                        <h5 class="text-xs font-bold text-gray-500 uppercase tracking-wider mb-4">Environment Context</h5>
                        <div class="grid grid-cols-2 gap-3">
                            <div class="text-center p-3 bg-gray-50 rounded-xl">
                                <i class="fi fi-rr-cloud-showers-heavy text-blue-500 text-2xl"></i>
                                <p class="text-xs text-gray-500 mt-2">Rainfall</p>
                                <p class="font-bold text-gray-800 text-lg">${weather.total_rain ?? 0}mm</p>
                            </div>
                            <div class="text-center p-3 bg-gray-50 rounded-xl">
                                <i class="fi fi-rr-temperature-high text-orange-500 text-2xl"></i>
                                <p class="text-xs text-gray-500 mt-2">ENSO</p>
                                <p class="font-bold text-gray-800 text-lg">${d.enso_state || "N/A"}</p>
                            </div>
                        </div>
                        <div class="mt-4 pt-4 border-t border-gray-200 flex justify-between items-center">
                            <span class="text-sm text-gray-600">Weather Severity</span>
                            <div class="flex gap-1.5">${dots}</div>
                        </div>
                    </div>
                </div>

                <div class="lg:col-span-2 space-y-6">
                    <div>
                        <h5 class="text-base font-bold text-gray-800 mb-3 flex items-center gap-2">
                            <span class="w-1 h-6 bg-orange-500 rounded-full"></span>
                            Milestone Pacing Audit
                        </h5>
                        <div class="bg-white border border-gray-200 rounded-2xl overflow-hidden shadow-sm">
                            <table class="w-full">
                                <thead class="bg-gray-50 border-b border-gray-200">
                                    <tr>
                                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Milestone</th>
                                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Status</th>
                                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Variance</th>
                                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Verdict</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-200">${pacingRows}</tbody>
                            </table>
                            <div class="bg-gray-50 px-4 py-3 border-t border-gray-200 flex items-center justify-between">
                                <span class="text-sm font-semibold text-gray-600">Average Pacing:</span>
                                <span class="text-sm font-bold ${Number(pacingStatus.avg_delay_days || 0) > 0 ? "text-red-600" : "text-emerald-600"}">
                                    ${Number(pacingStatus.avg_delay_days || 0)} days
                                    ${Number(pacingStatus.avg_delay_days || 0) > 0 ? "behind" : "ahead"}
                                </span>
                            </div>
                        </div>
                    </div>

                    <div>
                        <h5 class="text-base font-bold text-gray-800 mb-3 flex items-center gap-2">
                            <span class="w-1 h-6 bg-purple-600 rounded-full"></span>
                            AI Strategic Recommendations
                        </h5>
                        <div class="space-y-3">${recItems}</div>
                    </div>
                </div>
            </div>`;

        showModal("detailsModal");
    }

    // ─────────────────────────────────────────────────────────────
    // EXPOSE PUBLIC API
    // ─────────────────────────────────────────────────────────────

    window.aiManagement = {
        openAnalysisModal,
        startAnalysis,
        resetAnalysisModal,
        showDetails,
        confirmDelete,
        deleteAnalysis,
        showModal,
        hideModal,
        toast,
        applyFilters,
        resetFilters
    };

})();
