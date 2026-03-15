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
            dateFrom.addEventListener("change", applyFilters);
        }
        if (dateTo) {
            dateTo.addEventListener("change", applyFilters);
        }
        if (verdictFilter) {
            verdictFilter.addEventListener("change", applyFilters);
        }
        if (resetBtn) {
            resetBtn.addEventListener("click", resetFilters);
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
        if (dateFrom) dateFrom.value = "";
        if (dateTo) dateTo.value = "";
        if (verdictFilter) verdictFilter.value = "";

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
            <div class="bg-white border border-indigo-200 rounded-xl p-8 shadow-sm">
                <div class="flex flex-col items-center justify-center text-indigo-600">
                    <div class="animate-spin h-16 w-16 border-4 border-indigo-600 border-t-transparent rounded-full mb-6"></div>
                    <p class="text-base font-bold animate-pulse mb-2">Running Random Forest Model...</p>
                    <p class="text-sm text-indigo-400 mb-6">Fetching Weather, Milestone Data & Contractor History</p>
                    <div class="w-full max-w-md space-y-2 text-sm text-indigo-500">
                        <div class="flex items-center gap-2">
                            <i class="fi fi-rr-check-circle text-emerald-500"></i>
                            <p>Loading project data</p>
                        </div>
                        <div class="flex items-center gap-2">
                            <i class="fi fi-rr-check-circle text-emerald-500"></i>
                            <p>Computing risk factors</p>
                        </div>
                        <div class="flex items-center gap-2">
                            <div class="animate-spin h-4 w-4 border-2 border-indigo-500 border-t-transparent rounded-full"></div>
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
                                <p class="text-2xl font-black text-indigo-600">Active</p>
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

    function showDetails(data) {
        const modal = document.getElementById('detailsModal');
        const body  = document.getElementById('modalBody');
        const d     = (typeof data === 'string') ? JSON.parse(data) : data;

        if (!modal || !body) return;

        const isDelayed  = d.prediction.prediction === 'DELAYED';
        const riskColor  = isDelayed ? 'text-red-600'   : 'text-emerald-600';
        const riskBorder = isDelayed ? 'border-red-200' : 'border-emerald-200';
        const riskBg     = isDelayed ? 'bg-red-50'      : 'bg-emerald-50';
        const contractor = d.analysis_report.contractor_audit;
        const isFlagged  = contractor.flagged || (contractor.status && contractor.status.includes('Flagged'));

        const details = d.analysis_report.pacing_status.details;
        let pacingRows = '';
        if (details && details.length > 0) {
            pacingRows = details.map(item => {
                const isRejected = item.status === 'rejected';
                const isLate     = item.days_variance > 0;
                return `
                    <tr class="hover:bg-gray-50 transition">
                        <td class="px-4 py-3 text-sm font-medium text-gray-700">${item.title}</td>
                        <td class="px-4 py-3 text-xs font-bold uppercase ${isRejected ? 'text-red-600' : 'text-gray-500'}">${item.status}</td>
                        <td class="px-4 py-3 font-mono text-sm ${isLate ? 'text-amber-600' : 'text-emerald-600'}">
                            ${item.days_variance > 0 ? '+' + item.days_variance : item.days_variance} days
                        </td>
                        <td class="px-4 py-3">
                            <span class="inline-block px-3 py-1 rounded-full text-xs font-semibold
                                ${isRejected ? 'bg-red-100 text-red-700' : isLate ? 'bg-amber-100 text-amber-700' : 'bg-emerald-100 text-emerald-700'}">
                                ${item.pacing_label}
                            </span>
                        </td>
                    </tr>`;
            }).join('');
        } else {
            pacingRows = '<tr><td colspan="4" class="px-4 py-8 text-center text-sm text-gray-500 italic">No milestone data available yet.</td></tr>';
        }

        const recs     = d.dds_recommendations || [];
        const recItems = recs.length > 0
            ? recs.map(rec => `
                <div class="flex gap-3 bg-white p-4 rounded-xl border border-gray-200 shadow-sm hover:shadow-md transition">
                    <div class="flex-shrink-0 mt-1">
                        ${rec.includes('QUALITY') ? '<i class="fi fi-rr-shield-exclamation text-red-500 text-xl"></i>'
                          : rec.includes('WEATHER') || rec.includes('RAIN') ? '<i class="fi fi-rr-cloud-disabled text-orange-500 text-xl"></i>'
                          : '<i class="fi fi-rr-bulb text-purple-500 text-xl"></i>'}
                    </div>
                    <p class="text-gray-700 text-sm font-medium leading-relaxed">${rec}</p>
                </div>`).join('')
            : '<p class="text-gray-400 italic text-sm">No recommendations generated.</p>';

        const dots = [1,2,3,4,5].map(i =>
            `<div class="w-2.5 h-2.5 rounded-full ${i <= d.weather_severity ? 'bg-orange-500' : 'bg-gray-300'}"></div>`
        ).join('');

        body.innerHTML = `
            <div class="bg-indigo-50 border-l-4 border-indigo-600 p-5 rounded-r-xl mb-6">
                <h4 class="text-indigo-900 font-bold text-sm uppercase tracking-wider mb-2">Executive Summary</h4>
                <p class="text-indigo-800 text-sm leading-relaxed">"${d.analysis_report.conclusion}"</p>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <div class="lg:col-span-1 space-y-4">

                    <div class="${riskBg} border ${riskBorder} rounded-2xl p-5 shadow-sm">
                        <h5 class="text-xs font-bold text-gray-500 uppercase tracking-wider mb-4">Risk Assessment</h5>
                        <div class="flex items-center justify-between mb-4">
                            <span class="text-3xl font-black ${riskColor}">${d.prediction.prediction}</span>
                            <div class="text-right">
                                <span class="block text-xs text-gray-500 mb-1">Probability</span>
                                <span class="text-2xl font-bold text-gray-900">${(d.prediction.delay_probability * 100).toFixed(1)}%</span>
                            </div>
                        </div>
                        <div class="w-full bg-gray-200 rounded-full h-3">
                            <div class="h-3 rounded-full ${isDelayed ? 'bg-red-500' : 'bg-emerald-500'}"
                                 style="width:${(d.prediction.delay_probability * 100)}%"></div>
                        </div>
                        <p class="text-xs text-gray-600 mt-3 italic">${d.prediction.reason || ''}</p>
                    </div>

                    <div class="bg-white border border-gray-200 rounded-2xl p-5 shadow-sm relative overflow-hidden">
                        ${isFlagged ? '<div class="absolute top-0 right-0 bg-red-600 text-white text-xs font-bold px-3 py-1.5 rounded-bl-lg">FLAGGED</div>' : ''}
                        <h5 class="text-xs font-bold text-gray-500 uppercase tracking-wider mb-4">Contractor Vetting</h5>
                        <div class="space-y-3">
                            <div class="flex justify-between items-center">
                                <span class="text-sm text-gray-600">Experience</span>
                                <span class="text-sm font-bold text-gray-900">${contractor.experience}</span>
                            </div>
                            <div class="flex justify-between items-center">
                                <span class="text-sm text-gray-600">Success Rate</span>
                                <span class="text-sm font-bold ${isFlagged ? 'text-red-600' : 'text-emerald-600'}">${contractor.historical_success}</span>
                            </div>
                            <div class="flex justify-between items-center">
                                <span class="text-sm text-gray-600">Audit Status</span>
                                <span class="inline-block px-3 py-1 rounded-full text-xs font-bold
                                    ${isFlagged ? 'text-red-700 bg-red-100' : 'text-emerald-700 bg-emerald-100'}">
                                    ${contractor.status || (isFlagged ? 'High Risk' : 'Good Standing')}
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
                                <p class="font-bold text-gray-800 text-lg">${d.weather.total_rain}mm</p>
                            </div>
                            <div class="text-center p-3 bg-gray-50 rounded-xl">
                                <i class="fi fi-rr-temperature-high text-orange-500 text-2xl"></i>
                                <p class="text-xs text-gray-500 mt-2">ENSO</p>
                                <p class="font-bold text-gray-800 text-lg">${d.enso_state}</p>
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
                            <span class="w-1 h-6 bg-indigo-600 rounded-full"></span>
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
                                <span class="text-sm font-bold ${d.analysis_report.pacing_status.avg_delay_days > 0 ? 'text-red-600' : 'text-emerald-600'}">
                                    ${d.analysis_report.pacing_status.avg_delay_days} days
                                    ${d.analysis_report.pacing_status.avg_delay_days > 0 ? 'behind' : 'ahead'}
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
