document.addEventListener("DOMContentLoaded", function () {
    // Existing filters
    const dateFromInput = document.getElementById("dateFrom");
    const dateToInput = document.getElementById("dateTo");
    const searchInput = document.getElementById("searchInput") || document.getElementById("topNavSearch");
    const resetBtn = document.getElementById("resetFilterBtn");

    // Contractor filter for suspended accounts staff tab
    const contractorFilter = document.getElementById("contractorFilter");
    const contractorFilterWrap = document.getElementById("contractorFilterWrap");

    // Dynamic Date Boundaries
    if (dateFromInput && dateToInput) {
        dateFromInput.addEventListener('change', function () {
            if (this.value) {
                dateToInput.min = this.value;
                // If dateTo is already set and is before dateFrom, clear it
                if (dateToInput.value && dateToInput.value < this.value) {
                    dateToInput.value = '';
                }
            } else {
                dateToInput.removeAttribute('min');
            }
        });
        dateToInput.addEventListener('change', function () {
            if (this.value) {
                dateFromInput.max = this.value;
                // If dateFrom is already set and is after dateTo, clear it
                if (dateFromInput.value && dateFromInput.value > this.value) {
                    dateFromInput.value = '';
                }
            } else {
                dateFromInput.removeAttribute('max');
            }
        });
    }

    // New filters for Posting Management
    const statusFilter = document.getElementById("statusFilter");
    const resetFiltersBtn = document.getElementById("resetFilters");

    // Projects filters
    const verificationFilter = document.getElementById("verificationFilter");
    const progressFilter = document.getElementById("progressFilter");

    const contractorsWrap = document.getElementById("contractorsTableWrap");
    const ownersWrap = document.getElementById("ownersTableWrap");
    const staffWrap = document.getElementById("staffTableWrap");
    const projectsWrap = document.getElementById("projectsTableWrap");

    // Tab buttons for suspended accounts
    const saTabStaff = document.getElementById("saTabStaff");
    const saTabContractors = document.getElementById("saTabContractors");
    const saTabOwners = document.getElementById("saTabOwners");

    let debounceTimer;

    // Show/hide contractor filter based on active tab
    function updateContractorFilterVisibility() {
        if (contractorFilterWrap && saTabStaff) {
            const isStaffTabActive = saTabStaff.classList.contains('text-orange-600');
            if (isStaffTabActive) {
                contractorFilterWrap.classList.remove('hidden');
                contractorFilterWrap.classList.add('flex');
            } else {
                contractorFilterWrap.classList.add('hidden');
                contractorFilterWrap.classList.remove('flex');
                // Clear contractor filter when switching away from staff tab
                if (contractorFilter) contractorFilter.value = '';
            }
        }
    }

    // Attach tab click listeners to update filter visibility
    if (saTabStaff) {
        saTabStaff.addEventListener('click', function() {
            setTimeout(updateContractorFilterVisibility, 50);
        });
    }
    if (saTabContractors) {
        saTabContractors.addEventListener('click', function() {
            setTimeout(updateContractorFilterVisibility, 50);
        });
    }
    if (saTabOwners) {
        saTabOwners.addEventListener('click', function() {
            setTimeout(updateContractorFilterVisibility, 50);
        });
    }

    // Initial visibility check
    updateContractorFilterVisibility();

    // Function to fetch and update data
    async function fetchAndUpdate(url) {
        try {
            const response = await fetch(url, {
                headers: {
                    "X-Requested-With": "XMLHttpRequest",
                },
            });

            if (!response.ok) throw new Error("Network response was not ok");

            const data = await response.json();

            if (contractorsWrap && data.contractors_html) {
                contractorsWrap.innerHTML = data.contractors_html;
            }
            if (ownersWrap && data.owners_html) {
                ownersWrap.innerHTML = data.owners_html;
            }
            if (staffWrap && data.staff_html) {
                staffWrap.innerHTML = data.staff_html;
            }
            if (projectsWrap && data.html) {
                projectsWrap.innerHTML = data.html;
            }

            // Update URL without reload
            window.history.pushState({}, "", url);

            // Re-attach pagination listeners
            attachPaginationListeners();

            // Re-attach modal listeners if needed (for posting management)
            if (typeof attachModalListeners === "function") {
                attachModalListeners();
            }

            // Re-attach reactivate listeners if needed (for suspended accounts)
            if (typeof attachReactivateListeners === "function") {
                attachReactivateListeners();
            }

            // Re-attach action listeners if needed (for projects)
            if (typeof attachActionListeners === "function") {
                attachActionListeners();
            }
        } catch (error) {
            console.error("Error fetching data:", error);
        }
    }

    // Make fetchAndUpdate globally available
    window.fetchAndUpdate = fetchAndUpdate;

    function buildUrl() {
        const url = new URL(window.location.href);
        const params = new URLSearchParams(url.search);

        // Existing params
        if (dateFromInput && dateFromInput.value) {
            params.set("date_from", dateFromInput.value);
        } else {
            params.delete("date_from");
        }

        if (dateToInput && dateToInput.value) {
            params.set("date_to", dateToInput.value);
        } else {
            params.delete("date_to");
        }

        if (searchInput && searchInput.value) {
            params.set("search", searchInput.value);
        } else {
            params.delete("search");
        }

        // Contractor filter (only add if staff tab is active)
        if (contractorFilter && contractorFilter.value && saTabStaff && saTabStaff.classList.contains('text-orange-600')) {
            params.set("contractor_id", contractorFilter.value);
        } else {
            params.delete("contractor_id");
        }

        // New params
        if (statusFilter && statusFilter.value) {
            params.set("status", statusFilter.value);
        } else {
            params.delete("status");
        }

        // Projects filters
        if (verificationFilter && verificationFilter.value) {
            params.set("verification", verificationFilter.value);
        } else {
            params.delete("verification");
        }

        if (progressFilter && progressFilter.value) {
            params.set("progress", progressFilter.value);
        } else {
            params.delete("progress");
        }

        // Reset pagination when filtering
        params.delete("contractors_page");
        params.delete("owners_page");
        params.delete("page"); // Generic page param

        return `${url.pathname}?${params.toString()}`;
    }

    function handleFilterChange() {
        const url = buildUrl();
        fetchAndUpdate(url);
    }

    function handleSearchInput() {
        clearTimeout(debounceTimer);
        debounceTimer = setTimeout(() => {
            handleFilterChange();
        }, 300); // 300ms debounce
    }

    function attachPaginationListeners() {
        // Target pagination links inside the wrappers
        const paginationLinks = document.querySelectorAll(
            "#contractorsTableWrap .pagination a, #ownersTableWrap .pagination a, #staffTableWrap .pagination a, #projectsTableWrap .pagination a"
        );
        paginationLinks.forEach((link) => {
            link.addEventListener("click", function (e) {
                e.preventDefault();
                const url = this.href;
                fetchAndUpdate(url);
            });
        });
    }

    // Populate inputs from URL on load
    const urlParams = new URLSearchParams(window.location.search);
    if (dateFromInput && urlParams.has("date_from")) {
        dateFromInput.value = urlParams.get("date_from");
    }
    if (dateToInput && urlParams.has("date_to")) {
        dateToInput.value = urlParams.get("date_to");
    }
    if (searchInput && urlParams.has("search")) {
        searchInput.value = urlParams.get("search");
    }
    if (contractorFilter && urlParams.has("contractor_id")) {
        contractorFilter.value = urlParams.get("contractor_id");
    }
    if (statusFilter && urlParams.has("status")) {
        statusFilter.value = urlParams.get("status");
    }
    if (verificationFilter && urlParams.has("verification")) {
        verificationFilter.value = urlParams.get("verification");
    }
    if (progressFilter && urlParams.has("progress")) {
        progressFilter.value = urlParams.get("progress");
    }

    // Event Listeners
    if (dateFromInput)
        dateFromInput.addEventListener("change", handleFilterChange);
    if (dateToInput) dateToInput.addEventListener("change", handleFilterChange);
    if (searchInput) searchInput.addEventListener("input", handleSearchInput);

    if (contractorFilter)
        contractorFilter.addEventListener("change", handleFilterChange);

    if (statusFilter)
        statusFilter.addEventListener("change", handleFilterChange);

    if (verificationFilter)
        verificationFilter.addEventListener("change", handleFilterChange);

    if (progressFilter)
        progressFilter.addEventListener("change", handleFilterChange);

    if (resetBtn) {
        resetBtn.addEventListener("click", function () {
            if (dateFromInput) dateFromInput.value = "";
            if (dateToInput) dateToInput.value = "";
            if (searchInput) searchInput.value = "";
            if (contractorFilter) contractorFilter.value = "";
            if (verificationFilter) verificationFilter.value = "";
            if (progressFilter) progressFilter.value = "";
            handleFilterChange();
        });
    }

    if (resetFiltersBtn) {
        resetFiltersBtn.addEventListener("click", function () {
            if (dateFromInput) dateFromInput.value = "";
            if (dateToInput) dateToInput.value = "";
            if (statusFilter) statusFilter.value = "";
            if (searchInput) searchInput.value = "";
            if (contractorFilter) contractorFilter.value = "";
            handleFilterChange();
        });
    }

    // Initial attachment of pagination listeners
    attachPaginationListeners();
});
