/**
 * Review & Rating Management — Admin
 * Handles view modal, delete modal, star rendering, and filter reset.
 */
document.addEventListener('DOMContentLoaded', function () {

    // ── View Modal ──────────────────────────────────────────────────────
    const viewModal = document.getElementById('viewReviewModal');

    // Open modal when view button is clicked
    document.querySelectorAll('.view-review-btn').forEach(function (btn) {
        btn.addEventListener('click', function () {
            const data = btn.dataset;

            // Reviewer info
            const reviewerInitials = getInitials(data.reviewerName);
            const reviewerAvatarEl = document.getElementById('modalReviewerAvatar');
            if (data.reviewerPic) {
                reviewerAvatarEl.innerHTML = '<img src="' + data.reviewerPic + '" alt="Profile" class="w-full h-full object-cover">';
            } else {
                reviewerAvatarEl.textContent = reviewerInitials;
            }
            document.getElementById('modalReviewerName').textContent = data.reviewerName;
            document.getElementById('modalReviewerType').textContent = data.reviewerType;

            // Reviewed user info
            const reviewedInitials = getInitials(data.reviewedName);
            const reviewedAvatarEl = document.getElementById('modalReviewedAvatar');
            if (data.reviewedPic) {
                reviewedAvatarEl.innerHTML = '<img src="' + data.reviewedPic + '" alt="Profile" class="w-full h-full object-cover">';
            } else {
                reviewedAvatarEl.textContent = reviewedInitials;
            }
            document.getElementById('modalReviewedName').textContent = data.reviewedName;
            document.getElementById('modalReviewedType').textContent = data.reviewedType;

            // Star rating
            const rating = parseInt(data.rating) || 0;
            document.getElementById('modalRatingValue').textContent = rating;
            renderStars(document.getElementById('modalStarRating'), rating);

            // Review text
            document.getElementById('modalReviewText').textContent = data.reviewText;

            // Meta
            document.getElementById('modalProjectTitle').textContent = data.projectTitle;
            document.getElementById('modalDate').textContent = data.date;

            // Show modal
            viewModal.classList.remove('hidden');
            viewModal.classList.add('show');
        });
    });

    // Close view modal
    viewModal.querySelectorAll('.close-modal').forEach(function (btn) {
        btn.addEventListener('click', function () {
            closeModal(viewModal);
        });
    });

    // Close view modal on backdrop click
    viewModal.addEventListener('click', function (e) {
        if (e.target === viewModal) {
            closeModal(viewModal);
        }
    });

    // ── Delete Modal ────────────────────────────────────────────────────
    const deleteModal = document.getElementById('deleteReviewModal');
    var currentDeleteId = null;

    // Open delete modal when delete button is clicked
    document.querySelectorAll('.delete-review-btn').forEach(function (btn) {
        btn.addEventListener('click', function () {
            currentDeleteId = btn.dataset.id;
            var reviewerName = btn.dataset.reviewerName;

            // Set reviewer name in modal
            document.getElementById('deleteModalReviewerName').textContent = reviewerName;

            // Clear previous reason and errors
            document.getElementById('deletionReason').value = '';
            clearDeleteReviewErrors();

            // Show modal
            deleteModal.classList.remove('hidden');
            deleteModal.classList.add('show');
        });
    });

    // Close delete modal
    deleteModal.querySelectorAll('.close-delete-modal').forEach(function (btn) {
        btn.addEventListener('click', function () {
            closeModal(deleteModal);
        });
    });

    // Close delete modal on backdrop click
    deleteModal.addEventListener('click', function (e) {
        if (e.target === deleteModal) {
            closeModal(deleteModal);
        }
    });

    // Show delete review errors
    function showDeleteReviewErrors(errors) {
        const errorAlert = document.getElementById('deleteReviewErrorAlert');
        const errorList = document.getElementById('deleteReviewErrorList');
        
        errorList.innerHTML = '';
        errors.forEach(error => {
            const li = document.createElement('li');
            li.textContent = error;
            errorList.appendChild(li);
        });
        
        errorAlert.classList.remove('hidden');
    }

    // Clear delete review errors
    function clearDeleteReviewErrors() {
        const errorAlert = document.getElementById('deleteReviewErrorAlert');
        errorAlert.classList.add('hidden');
    }

    // Confirm delete
    document.getElementById('confirmDelete').addEventListener('click', function () {
        var reason = document.getElementById('deletionReason').value.trim();

        // Clear previous errors
        clearDeleteReviewErrors();

        if (!reason) {
            showDeleteReviewErrors(['Please provide a reason for deletion']);
            document.getElementById('deletionReason').focus();
            return;
        }

        const btn = this;
        btn.disabled = true;
        btn.innerHTML = '<svg class="animate-spin h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>';

        const fetchUrl = window.location.pathname.replace(/\/$/, '') + '/' + currentDeleteId + '/delete';

        fetch(fetchUrl, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: JSON.stringify({ deletion_reason: reason })
        })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    closeModal(deleteModal);
                    // Reload the table
                    fetchReviews();
                    showNotification('Review deleted successfully', 'success');
                } else {
                    showNotification(data.message || 'Error occurred while deleting review.', 'error');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showNotification('An unexpected error occurred.', 'error');
            })
            .finally(() => {
                btn.disabled = false;
                btn.textContent = 'Confirm Deletion';
            });
    });

    // ── AJAX Filtering ──────────────────────────────────────────────────
    const searchInput = document.getElementById('topNavSearch');
    const dateFrom = document.getElementById('dateFrom');
    const dateTo = document.getElementById('dateTo');
    const ratingFilter = document.getElementById('ratingFilter');
    const resetBtn = document.getElementById('resetFilters');
    const tableBody = document.querySelector('tbody');

    // Date Range Constraints: From <= To
    if (dateFrom && dateTo) {
        dateFrom.addEventListener('change', function () {
            if (this.value) dateTo.min = this.value;
        });
        dateTo.addEventListener('change', function () {
            if (this.value) dateFrom.max = this.value;
        });
    }

    function fetchReviews() {
        if (!tableBody) return;

        const queryParams = new URLSearchParams({
            search: searchInput ? searchInput.value : '',
            date_from: dateFrom ? dateFrom.value : '',
            date_to: dateTo ? dateTo.value : '',
            rating: ratingFilter ? ratingFilter.value : ''
        });

        fetch(window.location.pathname + '?' + queryParams.toString(), {
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        })
            .then(response => response.json())
            .then(data => {
                if (data.reviews_html) {
                    tableBody.innerHTML = data.reviews_html;
                    // Re-initialize View/Delete button listeners for new rows
                    rebindActionButtons();
                    // Attach pagination listeners for page navigation
                    attachPaginationListeners();

                    // Hide/Show pagination based on results
                    const paginationWrap = document.getElementById('paginationWrap');
                    if (paginationWrap) {
                        if (data.reviews_html.includes('No reviews found')) {
                            paginationWrap.classList.add('hidden');
                        } else {
                            paginationWrap.classList.remove('hidden');
                        }
                    }
                }
            })
            .catch(error => console.error('Error fetching reviews:', error));
    }

    function rebindActionButtons() {
        // Re-attach listeners to NEW view buttons
        document.querySelectorAll('.view-review-btn').forEach(function (btn) {
            btn.addEventListener('click', function () {
                const data = btn.dataset;
                const reviewerInitials = getInitials(data.reviewerName);
                const reviewerAvatarEl = document.getElementById('modalReviewerAvatar');
                if (data.reviewerPic) {
                    reviewerAvatarEl.innerHTML = '<img src="' + data.reviewerPic + '" alt="Profile" class="w-full h-full object-cover">';
                } else {
                    reviewerAvatarEl.textContent = reviewerInitials;
                }
                document.getElementById('modalReviewerName').textContent = data.reviewerName;
                document.getElementById('modalReviewerType').textContent = data.reviewerType;

                const reviewedInitials = getInitials(data.reviewedName);
                const reviewedAvatarEl = document.getElementById('modalReviewedAvatar');
                if (data.reviewedPic) {
                    reviewedAvatarEl.innerHTML = '<img src="' + data.reviewedPic + '" alt="Profile" class="w-full h-full object-cover">';
                } else {
                    reviewedAvatarEl.textContent = reviewedInitials;
                }
                document.getElementById('modalReviewedName').textContent = data.reviewedName;
                document.getElementById('modalReviewedType').textContent = data.reviewedType;

                const rating = parseInt(data.rating) || 0;
                document.getElementById('modalRatingValue').textContent = rating;
                renderStars(document.getElementById('modalStarRating'), rating);
                document.getElementById('modalReviewText').textContent = data.reviewText;
                document.getElementById('modalProjectTitle').textContent = data.projectTitle;
                document.getElementById('modalDate').textContent = data.date;

                viewModal.classList.remove('hidden');
                viewModal.classList.add('show');
            });
        });

        // Re-attach listeners to NEW delete buttons
        document.querySelectorAll('.delete-review-btn').forEach(function (btn) {
            btn.addEventListener('click', function () {
                currentDeleteId = btn.dataset.id;
                document.getElementById('deleteModalReviewerName').textContent = btn.dataset.reviewerName;
                document.getElementById('deletionReason').value = '';
                deleteModal.classList.remove('hidden');
                deleteModal.classList.add('show');
            });
        });
    }

    function attachPaginationListeners() {
        // Attach click listeners to pagination links
        document.querySelectorAll('.review-page-link').forEach(function (link) {
            link.addEventListener('click', function (e) {
                e.preventDefault();
                const url = this.href;
                
                // Fetch data for the clicked page
                fetch(url, {
                    headers: { 'X-Requested-With': 'XMLHttpRequest' }
                })
                    .then(response => response.json())
                    .then(data => {
                        if (data.reviews_html) {
                            tableBody.innerHTML = data.reviews_html;
                            rebindActionButtons();
                            attachPaginationListeners(); // Re-attach for new pagination links
                            
                            // Update pagination controls
                            const paginationWrap = document.getElementById('paginationWrap');
                            if (paginationWrap && data.pagination_html) {
                                paginationWrap.innerHTML = data.pagination_html;
                                attachPaginationListeners(); // Re-attach for updated pagination
                            }
                        }
                        
                        // Update browser history
                        window.history.pushState({}, '', url);
                    })
                    .catch(error => console.error('Error loading page:', error));
            });
        });
    }

    // Input listeners for filters
    let searchTimeout;
    if (searchInput) {
        searchInput.addEventListener('input', () => {
            clearTimeout(searchTimeout);
            // Search upon typing with a shorter debounce for responsiveness
            searchTimeout = setTimeout(fetchReviews, 300);
        });
    }
    [dateFrom, dateTo, ratingFilter].forEach(el => {
        if (el) el.addEventListener('change', fetchReviews);
    });

    // Reset handler
    if (resetBtn) {
        resetBtn.addEventListener('click', function () {
            if (dateFrom) {
                dateFrom.value = '';
                dateFrom.max = '';
            }
            if (dateTo) {
                dateTo.value = '';
                dateTo.min = '';
            }
            if (ratingFilter) ratingFilter.value = '';
            if (searchInput) searchInput.value = '';
            fetchReviews();
        });
    }

    // ── Helper Functions ────────────────────────────────────────────────

    /**
     * Show a toast notification (Standardized Admin Design)
     */
    function showNotification(message, type = 'success') {
        const notification = document.createElement('div');
        notification.className = `fixed top-20 right-4 z-[60] max-w-[280px] px-3 py-2 rounded-md shadow-lg transform transition-all duration-500 translate-x-full ${type === 'success' ? 'bg-green-500' : 'bg-red-500'
            } text-white text-xs font-semibold leading-tight flex items-center gap-1.5`;

        notification.innerHTML = `
            <i class="fi fi-rr-${type === 'success' ? 'check-circle' : 'cross-circle'} text-base"></i>
            <span>${message}</span>
        `;

        document.body.appendChild(notification);

        // Slide in
        setTimeout(() => {
            notification.style.transform = 'translateX(0)';
        }, 10);

        // Slide out and remove
        setTimeout(() => {
            notification.style.transform = 'translateX(150%)';
            setTimeout(() => notification.remove(), 500);
        }, 3000);
    }

    /**
     * Close a modal element
     */
    function closeModal(modal) {
        modal.classList.remove('show');
        modal.classList.add('hidden');
    }

    /**
     * Get initials from a full name
     */
    function getInitials(name) {
        if (!name) return '??';
        var parts = name.trim().split(/\s+/);
        var first = parts[0] ? parts[0].charAt(0).toUpperCase() : '';
        var last = parts.length > 1 ? parts[parts.length - 1].charAt(0).toUpperCase() : '';
        return first + last;
    }

    /**
     * Render star icons into a container
     */
    function renderStars(container, rating) {
        if (!container) return;
        container.innerHTML = '';
        for (var i = 1; i <= 5; i++) {
            var svg = document.createElementNS('http://www.w3.org/2000/svg', 'svg');
            svg.setAttribute('class', 'w-5 h-5 ' + (i <= rating ? 'text-yellow-400' : 'text-gray-300'));
            svg.setAttribute('fill', 'currentColor');
            svg.setAttribute('viewBox', '0 0 20 20');
            var path = document.createElementNS('http://www.w3.org/2000/svg', 'path');
            path.setAttribute('d', 'M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z');
            svg.appendChild(path);
            container.appendChild(svg);
        }
    }

    /**
     * Prepend a new review row to the table (new data appears first)
     */
    function prependReview(reviewRowHTML) {
        if (!tableBody || !reviewRowHTML) return;

        // Create a temporary container to parse the HTML
        var tempDiv = document.createElement('div');
        tempDiv.innerHTML = reviewRowHTML.trim();
        var newRow = tempDiv.firstElementChild;

        if (newRow) {
            // Prepend to table body
            tableBody.insertAdjacentHTML('afterbegin', newRow.outerHTML);
            
            // Re-bind event handlers to the new row
            var newButton = tableBody.querySelector('.view-review-btn, .delete-review-btn');
            if (newButton && newButton.parentElement.parentElement === tableBody.firstElementChild) {
                rebindActionButtons();
            }

            // Optional: Show a brief highlight animation on the new row
            var firstRow = tableBody.firstElementChild;
            if (firstRow) {
                firstRow.style.backgroundColor = '#fef3c7';
                setTimeout(function() {
                    firstRow.style.transition = 'background-color 1s ease-in-out';
                    firstRow.style.backgroundColor = '';
                }, 100);
            }
        }
    }

    // Initialize pagination listeners on page load
    attachPaginationListeners();

});
