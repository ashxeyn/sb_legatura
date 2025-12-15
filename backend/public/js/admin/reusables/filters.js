document.addEventListener('DOMContentLoaded', function() {
    const dateFromInput = document.getElementById('dateFrom');
    const dateToInput = document.getElementById('dateTo');
    const searchInput = document.getElementById('searchInput');
    const resetBtn = document.getElementById('resetFilterBtn');
    const contractorsWrap = document.getElementById('contractorsTableWrap');
    const ownersWrap = document.getElementById('ownersTableWrap');

    let debounceTimer;

    // Function to fetch and update data
    async function fetchAndUpdate(url) {
        try {
            const response = await fetch(url, {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            });

            if (!response.ok) throw new Error('Network response was not ok');

            const data = await response.json();

            if (contractorsWrap && data.contractors_html) {
                contractorsWrap.innerHTML = data.contractors_html;
            }
            if (ownersWrap && data.owners_html) {
                ownersWrap.innerHTML = data.owners_html;
            }

            // Update URL without reload
            window.history.pushState({}, '', url);

            // Re-attach pagination listeners
            attachPaginationListeners();

        } catch (error) {
            console.error('Error fetching data:', error);
        }
    }

    function buildUrl() {
        const url = new URL(window.location.href);
        const params = new URLSearchParams(url.search);

        if (dateFromInput && dateFromInput.value) {
            params.set('date_from', dateFromInput.value);
        } else {
            params.delete('date_from');
        }

        if (dateToInput && dateToInput.value) {
            params.set('date_to', dateToInput.value);
        } else {
            params.delete('date_to');
        }

        if (searchInput && searchInput.value) {
            params.set('search', searchInput.value);
        } else {
            params.delete('search');
        }

        // Reset pagination when filtering
        params.delete('contractors_page');
        params.delete('owners_page');

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
        const paginationLinks = document.querySelectorAll('#contractorsTableWrap .pagination a, #ownersTableWrap .pagination a');
        paginationLinks.forEach(link => {
            link.addEventListener('click', function(e) {
                e.preventDefault();
                const url = this.href;
                fetchAndUpdate(url);
            });
        });
    }

    // Populate inputs from URL on load
    const urlParams = new URLSearchParams(window.location.search);
    if (dateFromInput && urlParams.has('date_from')) {
        dateFromInput.value = urlParams.get('date_from');
    }
    if (dateToInput && urlParams.has('date_to')) {
        dateToInput.value = urlParams.get('date_to');
    }
    if (searchInput && urlParams.has('search')) {
        searchInput.value = urlParams.get('search');
    }

    // Event Listeners
    if (dateFromInput) {
        dateFromInput.addEventListener('change', handleFilterChange);
    }

    if (dateToInput) {
        dateToInput.addEventListener('change', handleFilterChange);
    }

    if (searchInput) {
        searchInput.addEventListener('input', handleSearchInput);
    }

    if (resetBtn) {
        resetBtn.addEventListener('click', function() {
            if (dateFromInput) dateFromInput.value = '';
            if (dateToInput) dateToInput.value = '';
            if (searchInput) searchInput.value = '';
            handleFilterChange();
        });
    }

    // Initial attachment of pagination listeners
    attachPaginationListeners();
});
