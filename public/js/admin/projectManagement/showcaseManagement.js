/**
 * showcaseManagement.js
 * Handles AJAX filtering, view modal (server-rendered), and approve/reject flows.
 */

document.addEventListener('DOMContentLoaded', function () {

    let currentShowcaseId = null;
    let currentShowcaseTitle = '';
    const tableBody = document.getElementById('showcaseTableBody');

    // Non-animated loading state for the modal body
    const loadingHTML = '<div class="flex items-center justify-center py-12">' +
        '<span class="text-gray-500 font-medium">Loading showcase details...</span>' +
        '</div>';

    // ── AJAX: Fetch Showcases (table reload) ──
    function fetchShowcases(page = 1) {
        if (!tableBody) return;

        var searchInput = document.getElementById('topNavSearch');
        var statusFilter = document.getElementById('statusFilter');
        var dateFrom = document.getElementById('dateFrom');
        var dateTo = document.getElementById('dateTo');

        var queryParams = new URLSearchParams({
            search: searchInput ? searchInput.value : '',
            status: statusFilter ? statusFilter.value : '',
            date_from: dateFrom ? dateFrom.value : '',
            date_to: dateTo ? dateTo.value : '',
            page: page
        });

        fetch(window.location.pathname + '?' + queryParams.toString(), {
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        })
            .then(function (response) { return response.json(); })
            .then(function (data) {
                if (data.showcases_html) {
                    tableBody.innerHTML = data.showcases_html;
                    var paginationContainer = document.getElementById('paginationLinks');
                    if (paginationContainer && data.pagination_html !== undefined) {
                        paginationContainer.innerHTML = data.pagination_html;
                    }
                    rebindActionButtons();
                }
            })
            .catch(function (error) { console.error('Error fetching showcases:', error); });
    }

    // ── Rebind action buttons after AJAX DOM swap ──
    function rebindActionButtons() {
        document.querySelectorAll('.view-showcase-btn').forEach(function (btn) {
            btn.addEventListener('click', function (e) {
                e.stopPropagation();
                var statusHint = this.dataset.status || (this.closest('.showcase-row') && this.closest('.showcase-row').dataset.status);
                openViewModal(parseInt(this.dataset.id), statusHint);
            });
        });

        document.querySelectorAll('.delete-showcase-btn').forEach(function (btn) {
            btn.addEventListener('click', function (e) {
                e.stopPropagation();
                openDeleteModal(parseInt(this.dataset.id), this.dataset.title);
            });
        });

        document.querySelectorAll('.restore-showcase-btn').forEach(function (btn) {
            btn.addEventListener('click', function (e) {
                e.stopPropagation();
                openRestoreModal(parseInt(this.dataset.id), this.dataset.title);
            });
        });

        document.querySelectorAll('.showcase-row').forEach(function (row) {
            row.addEventListener('click', function () {
                openViewModal(parseInt(this.dataset.id), this.dataset.status);
            });
        });
    }

    function openDeleteModal(id, title) {
        currentShowcaseId = id;
        currentShowcaseTitle = title;
        document.getElementById('deleteModalTitle').textContent = title;
        document.getElementById('deleteReason').value = '';
        document.getElementById('deleteReason').classList.remove('border-red-500');
        document.getElementById('deleteReasonError').classList.add('hidden');
        document.body.classList.add('overflow-hidden');
        document.getElementById('deleteShowcaseModal').classList.remove('hidden');
    }

    function openRestoreModal(id, title) {
        currentShowcaseId = id;
        currentShowcaseTitle = title;
        document.getElementById('restoreModalTitle').textContent = title;
        document.body.classList.add('overflow-hidden');
        document.getElementById('restoreShowcaseModal').classList.remove('hidden');
    }

    function normalizeShowcaseStatus(status) {
        var normalized = String(status || '').toLowerCase();

        if (normalized === 'pending_review' || normalized === 'under_review') {
            return 'pending';
        }
        if (normalized === 'approved' || normalized === 'rejected' || normalized === 'pending') {
            return normalized;
        }

        return 'pending';
    }

    function applyViewModalTheme(status) {
        var modal = document.getElementById('viewShowcaseModal');
        if (!modal) return 'pending';

        var normalizedStatus = normalizeShowcaseStatus(status);
        modal.setAttribute('data-status', normalizedStatus);

        return normalizedStatus;
    }

    // ── Open View Modal (fetches server-rendered HTML) ──
    function openViewModal(id, statusHint) {
        currentShowcaseId = id;

        var modal = document.getElementById('viewShowcaseModal');
        var bodyContent = document.getElementById('modalBodyContent');
        var approveBtn = document.getElementById('viewModalApproveBtn');
        var rejectBtn = document.getElementById('viewModalRejectBtn');

        // Show modal with loading spinner
        bodyContent.innerHTML = loadingHTML;
        approveBtn.classList.add('hidden');
        rejectBtn.classList.add('hidden');
        applyViewModalTheme(statusHint || 'pending');
        document.body.classList.add('overflow-hidden');
        modal.classList.remove('hidden');

        // Fetch server-rendered HTML
        fetch(window.location.pathname + '/' + id + '/details', {
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        })
            .then(function (response) { return response.json(); })
            .then(function (result) {
                if (!result.success) {
                    showNotification('Showcase not found.', 'error');
                    closeAllModals();
                    return;
                }

                // Inject the server-rendered HTML into the modal body
                bodyContent.innerHTML = result.html;
                currentShowcaseTitle = result.title || '';
                var normalizedStatus = applyViewModalTheme(result.status);

                // Show/hide approve/reject buttons based on status
                if (normalizedStatus === 'pending') {
                    approveBtn.classList.remove('hidden');
                    rejectBtn.classList.remove('hidden');
                } else {
                    approveBtn.classList.add('hidden');
                    rejectBtn.classList.add('hidden');
                }
            })
            .catch(function (error) {
                console.error('Error fetching showcase details:', error);
                applyViewModalTheme('pending');
                bodyContent.innerHTML = '<div class="text-center py-12"><p class="text-red-500 font-medium">Failed to load showcase details.</p></div>';
            });
    }

    // ── Initial bind ──
    rebindActionButtons();

    // ── Approve flow ──
    document.getElementById('viewModalApproveBtn').addEventListener('click', function () {
        if (!currentShowcaseId) return;
        document.getElementById('approveModalTitle').textContent = currentShowcaseTitle;
        document.getElementById('viewShowcaseModal').classList.add('hidden');
        document.body.classList.add('overflow-hidden');
        document.getElementById('approveShowcaseModal').classList.remove('hidden');
    });

    document.getElementById('confirmApproveShowcase').addEventListener('click', function () {
        if (!currentShowcaseId) return;

        var btn = this;
        btn.disabled = true;
        btn.querySelector('span').innerHTML = '<svg class="w-5 h-5 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path></svg> Approving...';

        fetch(window.location.pathname + '/' + currentShowcaseId + '/approve', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            }
        })
            .then(function (response) { return response.json(); })
            .then(function (data) {
                if (data.success) {
                    closeAllModals();
                    fetchShowcases();
                    showNotification(data.message || 'Showcase approved successfully.', 'success');
                } else {
                    showNotification(data.message || 'Failed to approve showcase.', 'error');
                }
            })
            .catch(function () {
                showNotification('An unexpected error occurred.', 'error');
            })
            .finally(function () {
                btn.disabled = false;
                btn.querySelector('span').innerHTML = '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg> Approve Showcase';
            });
    });

    // ── Reject flow ──
    document.getElementById('viewModalRejectBtn').addEventListener('click', function () {
        if (!currentShowcaseId) return;
        document.getElementById('rejectModalTitle').textContent = currentShowcaseTitle;
        document.getElementById('rejectReason').value = '';
        document.getElementById('viewShowcaseModal').classList.add('hidden');
        document.body.classList.add('overflow-hidden');
        document.getElementById('rejectShowcaseModal').classList.remove('hidden');
    });

    document.getElementById('confirmRejectShowcase').addEventListener('click', function () {
        var reason = document.getElementById('rejectReason').value.trim();
        var textarea = document.getElementById('rejectReason');
        var errorMsg = document.getElementById('rejectReasonError');

        if (!reason) {
            textarea.classList.add('border-red-500');
            errorMsg.textContent = 'Please provide a reason for rejection.';
            errorMsg.classList.remove('hidden');
            textarea.focus();

            setTimeout(function () {
                textarea.classList.remove('border-red-500');
                errorMsg.classList.add('hidden');
            }, 3000);
            return;
        }

        if (!currentShowcaseId) return;

        var btn = this;
        btn.disabled = true;
        btn.querySelector('span').textContent = 'Rejecting...';

        fetch(window.location.pathname + '/' + currentShowcaseId + '/reject', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: JSON.stringify({ rejection_reason: reason })
        })
            .then(function (response) { return response.json(); })
            .then(function (data) {
                if (data.success) {
                    closeAllModals();
                    fetchShowcases();
                    showNotification(data.message || 'Showcase rejected successfully.', 'success');
                } else {
                    showNotification(data.message || 'Failed to reject showcase.', 'error');
                }
            })
            .catch(function () {
                showNotification('An unexpected error occurred.', 'error');
            })
            .finally(function () {
                btn.disabled = false;
                btn.querySelector('span').textContent = 'Reject Showcase';
            });
    });

    // ── Delete flow ──
    document.getElementById('confirmDeleteShowcase').addEventListener('click', function () {
        var reason = document.getElementById('deleteReason').value.trim();
        var textarea = document.getElementById('deleteReason');
        var errorMsg = document.getElementById('deleteReasonError');

        if (!reason) {
            textarea.classList.add('border-red-500');
            errorMsg.textContent = 'Please provide a reason for deletion.';
            errorMsg.classList.remove('hidden');
            textarea.focus();

            setTimeout(function () {
                textarea.classList.remove('border-red-500');
                errorMsg.classList.add('hidden');
            }, 3000);
            return;
        }

        if (!currentShowcaseId) return;

        var btn = this;
        btn.disabled = true;
        btn.querySelector('span').textContent = 'Deleting...';

        fetch(window.location.pathname + '/' + currentShowcaseId + '/delete', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: JSON.stringify({ deletion_reason: reason })
        })
            .then(function (response) { return response.json(); })
            .then(function (data) {
                if (data.success) {
                    closeAllModals();
                    fetchShowcases();
                    showNotification(data.message || 'Showcase deleted successfully.', 'success');
                } else {
                    showNotification(data.message || 'Failed to delete showcase.', 'error');
                }
            })
            .catch(function () {
                showNotification('An unexpected error occurred.', 'error');
            })
            .finally(function () {
                btn.disabled = false;
                btn.querySelector('span').textContent = 'Delete Showcase';
            });
    });

    // ── Restore flow ──
    document.getElementById('confirmRestoreShowcase').addEventListener('click', function () {
        if (!currentShowcaseId) return;

        var btn = this;
        btn.disabled = true;
        btn.querySelector('span').textContent = 'Restoring...';

        fetch(window.location.pathname + '/' + currentShowcaseId + '/restore', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            }
        })
            .then(function (response) { return response.json(); })
            .then(function (data) {
                if (data.success) {
                    closeAllModals();
                    fetchShowcases();
                    showNotification(data.message || 'Showcase restored successfully.', 'success');
                } else {
                    showNotification(data.message || 'Failed to restore showcase.', 'error');
                }
            })
            .catch(function () {
                showNotification('An unexpected error occurred.', 'error');
            })
            .finally(function () {
                btn.disabled = false;
                btn.querySelector('span').textContent = 'Restore Showcase';
            });
    });

    // ── Close modals ──
    function closeAllModals() {
        document.getElementById('viewShowcaseModal').classList.add('hidden');
        document.getElementById('approveShowcaseModal').classList.add('hidden');
        document.getElementById('rejectShowcaseModal').classList.add('hidden');
        document.getElementById('deleteShowcaseModal')?.classList.add('hidden');
        document.getElementById('restoreShowcaseModal')?.classList.add('hidden');
        applyViewModalTheme('pending');
        document.body.classList.remove('overflow-hidden');

        var rejectReason = document.getElementById('rejectReason');
        if (rejectReason) {
            rejectReason.classList.remove('border-red-500');
        }
        var rejectError = document.getElementById('rejectReasonError');
        if (rejectError) rejectError.classList.add('hidden');

        var deleteReason = document.getElementById('deleteReason');
        if (deleteReason) {
            deleteReason.classList.remove('border-red-500');
        }
        var deleteError = document.getElementById('deleteReasonError');
        if (deleteError) deleteError.classList.add('hidden');

        currentShowcaseId = null;
        currentShowcaseTitle = '';
    }

    document.querySelectorAll('.close-modal').forEach(function (btn) {
        btn.addEventListener('click', function () {
            closeAllModals();
        });
    });

    ['viewShowcaseModal', 'approveShowcaseModal', 'rejectShowcaseModal', 'deleteShowcaseModal', 'restoreShowcaseModal'].forEach(function (id) {
        var modal = document.getElementById(id);
        if (modal) {
            modal.addEventListener('click', function (e) {
                if (e.target === modal) closeAllModals();
            });
        }
    });

    // ── AJAX Filtering ──
    var searchInput = document.getElementById('topNavSearch');
    var statusFilter = document.getElementById('statusFilter');
    var dateFrom = document.getElementById('dateFrom');
    var dateTo = document.getElementById('dateTo');
    var resetBtn = document.getElementById('resetFilters');

    if (dateFrom && dateTo) {
        dateFrom.addEventListener('change', function () { if (this.value) dateTo.min = this.value; });
        dateTo.addEventListener('change', function () { if (this.value) dateFrom.max = this.value; });
    }

    var searchTimeout;
    if (searchInput) {
        searchInput.addEventListener('input', function () {
            clearTimeout(searchTimeout);
            searchTimeout = setTimeout(function () { fetchShowcases(1); }, 400);
        });
    }

    if (statusFilter) {
        statusFilter.addEventListener('change', function () { fetchShowcases(1); });
    }

    if (resetBtn) {
        resetBtn.addEventListener('click', function (e) {
            e.preventDefault();
            if (searchInput) searchInput.value = '';
            if (statusFilter) statusFilter.value = '';
            if (dateFrom) { dateFrom.value = ''; dateTo.min = ''; }
            if (dateTo) { dateTo.value = ''; dateFrom.max = ''; }
            fetchShowcases(1);
        });
    }

    // ── Pagination Click Handling (Delegated) ──
    document.addEventListener('click', function (e) {
        var a = e.target.closest("#paginationLinks a");
        if (a) {
            e.preventDefault();
            var url = new URL(a.href);
            var page = url.searchParams.get('page');
            if (page) {
                fetchShowcases(page);
            }
        }
    });

    // ── Toast Notification ──
    function showNotification(message, type) {
        type = type || 'success';
        var container = document.getElementById('notificationContainer');
        if (!container) {
            container = document.createElement('div');
            container.id = 'notificationContainer';
            container.style.cssText = 'position:fixed;top:24px;right:24px;z-index:9999;display:flex;flex-direction:column;gap:12px;';
            document.body.appendChild(container);
        }
        var colors = {
            success: { bg: '#10B981', icon: '✓' },
            error: { bg: '#EF4444', icon: '✕' },
            warning: { bg: '#F59E0B', icon: '⚠' },
        };
        var c = colors[type] || colors.success;
        var toast = document.createElement('div');
        toast.style.cssText = 'display:flex;align-items:center;gap:12px;padding:16px 20px;border-radius:12px;color:#fff;font-size:14px;font-weight:600;box-shadow:0 10px 25px rgba(0,0,0,.15);min-width:320px;max-width:480px;transform:translateX(120%);transition:transform .4s cubic-bezier(.4,0,.2,1),opacity .4s;opacity:0;background:' + c.bg + ';';
        toast.innerHTML = '<span style="width:28px;height:28px;border-radius:50%;background:rgba(255,255,255,.2);display:flex;align-items:center;justify-content:center;font-size:14px;flex-shrink:0;">' + c.icon + '</span><span>' + message + '</span>';
        container.appendChild(toast);
        requestAnimationFrame(function () {
            requestAnimationFrame(function () {
                toast.style.transform = 'translateX(0)';
                toast.style.opacity = '1';
            });
        });
        setTimeout(function () {
            toast.style.transform = 'translateX(120%)';
            toast.style.opacity = '0';
            setTimeout(function () { toast.remove(); }, 400);
        }, 4000);
    }

    // ============================================
    // Universal File Viewer (UFV) - Dark Theme
    // ============================================
    (function() {
        const modal = document.getElementById('documentViewerModal');
        const iframe = document.getElementById('documentViewerFrame');
        const img = document.getElementById('documentViewerImg');
        const closeBtn = document.getElementById('closeDocumentViewerBtn');

        if (!modal) {
            console.error('UFV: documentViewerModal not found!');
            return;
        }

        function openDocumentViewer(src, title) {
            if (!modal) return;
            const isPdf = /\.pdf(\?|$)/i.test(src);
            const titleEl = document.getElementById('documentViewerTitle');
            const downloadLink = document.getElementById('documentViewerDownload');

            if (titleEl) titleEl.textContent = title || 'Document Viewer';
            if (downloadLink) downloadLink.href = src;

            if (isPdf) {
                if (iframe) {
                    iframe.src = src;
                    iframe.classList.remove('hidden');
                }
                if (img) img.classList.add('hidden');
            } else {
                if (img) {
                    img.src = src;
                    img.classList.remove('hidden');
                }
                if (iframe) iframe.classList.add('hidden');
            }

            modal.classList.remove('hidden');
            modal.classList.add('flex');
            document.body.style.overflow = 'hidden';

            const modalShell = modal.querySelector('.modal-shell');
            if (modalShell) {
                setTimeout(function() {
                    modalShell.classList.remove('scale-95', 'opacity-0');
                    modalShell.classList.add('scale-100', 'opacity-100');
                }, 10);
            }
        }

        function closeDocumentViewer() {
            if (!modal) return;
            const modalShell = modal.querySelector('.modal-shell');
            if (modalShell) {
                modalShell.classList.remove('scale-100', 'opacity-100');
                modalShell.classList.add('scale-95', 'opacity-0');
            }
            setTimeout(function() {
                modal.classList.add('hidden');
                modal.classList.remove('flex');
                document.body.style.overflow = 'auto';
                if (iframe) iframe.src = '';
                if (img) img.src = '';
            }, 200);
        }

        // Delegated click handler for open buttons
        document.addEventListener('click', function(e) {
            const btn = e.target.closest && e.target.closest('.open-doc-btn');
            if (btn) {
                e.preventDefault();
                e.stopPropagation();
                const src = btn.getAttribute('data-doc-src');
                const title = btn.getAttribute('data-doc-title') || 'Document';
                if (src && src !== '#') {
                    openDocumentViewer(src, title);
                } else {
                    showNotification('No document available', 'error');
                }
            }
        }, true); // Use capture phase

        // Close button
        if (closeBtn) {
            closeBtn.addEventListener('click', closeDocumentViewer);
        }

        // Close on backdrop click
        if (modal) {
            modal.addEventListener('click', function(e) {
                if (e.target === modal) {
                    closeDocumentViewer();
                }
            });
        }

        // Close on ESC key
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape' && modal && !modal.classList.contains('hidden')) {
                closeDocumentViewer();
            }
        });
    })();

});
