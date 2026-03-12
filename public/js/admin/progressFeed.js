/**
 * progressFeed.js
 * System-wide Progress Feed — Admin Dashboard
 *
 * Fetches paginated contractor milestone-progress reports from
 * GET /admin/progress-feed/data  and renders them as evidence cards.
 *
 * Filters: status dropdown · date-range · top-nav search (debounced)
 */

document.addEventListener('DOMContentLoaded', function () {

    // ── Element references ──────────────────────────────────────────────
    var feedList       = document.getElementById('feedList');
    var feedLoading    = document.getElementById('feedLoading');
    var feedEmpty      = document.getElementById('feedEmpty');
    var feedError      = document.getElementById('feedError');
    var feedPagination = document.getElementById('feedPagination');
    var resultsCount   = document.getElementById('resultsCount');

    var searchInput  = document.getElementById('topNavSearch');
    var statusFilter = document.getElementById('statusFilter');
    var dateFrom     = document.getElementById('dateFrom');
    var dateTo       = document.getElementById('dateTo');
    var resetBtn     = document.getElementById('resetFilters');

    var companyFilter   = document.getElementById('companyFilter');
    var companyDropdown = document.getElementById('companyDropdown');
    var companyFilterWrap = document.getElementById('companyFilterWrap');

    var ufvModal     = document.getElementById('ufvModal');
    var ufvFileName  = document.getElementById('ufvFileName');
    var ufvCounter   = document.getElementById('ufvCounter');
    var ufvDownload  = document.getElementById('ufvDownload');
    var ufvViewport  = document.getElementById('ufvViewport');
    var ufvFilmstrip = document.getElementById('ufvFilmstrip');
    var ufvPrev      = document.getElementById('ufvPrev');
    var ufvNext      = document.getElementById('ufvNext');
    var ufvClose     = document.getElementById('ufvClose');

    var currentPage    = 1;
    var searchTimeout  = null;
    var allCompanies   = [];
    var selectedCompany = '';

    // ── File-type detection ──────────────────────────────────────────────────
    var IMG_EXT   = ['jpg', 'jpeg', 'png', 'gif', 'webp', 'bmp', 'svg', 'heic', 'ico'];
    var PDF_EXT   = ['pdf'];
    var VIDEO_EXT = ['mp4', 'webm', 'mov', 'avi', 'mkv', 'm4v'];
    var AUDIO_EXT = ['mp3', 'wav', 'ogg', 'flac', 'aac', 'm4a'];

    function isImage(filename) {
        if (!filename) return false;
        var ext = filename.split('.').pop().toLowerCase();
        return IMG_EXT.indexOf(ext) !== -1;
    }

    function getFileType(filename) {
        if (!filename) return 'other';
        var ext = filename.split('.').pop().toLowerCase();
        if (IMG_EXT.indexOf(ext)   !== -1) return 'image';
        if (PDF_EXT.indexOf(ext)   !== -1) return 'pdf';
        if (VIDEO_EXT.indexOf(ext) !== -1) return 'video';
        if (AUDIO_EXT.indexOf(ext) !== -1) return 'audio';
        return 'other';
    }

    /**
     * Build a URL for serving a stored file.
     * Uses the /api/files/{path} route in routes/api.php (Laravel prefixes api.php with /api).
     */
    function fileUrl(filePath) {
        return '/api/files/' + filePath;
    }

    // ── Status badge ────────────────────────────────────────────────────
    var STATUS_CFG = {
        submitted:      { label: 'Submitted',      cls: 'pf-badge-submitted' },
        approved:       { label: 'Approved',        cls: 'pf-badge-approved' },
        rejected:       { label: 'Rejected',         cls: 'pf-badge-revision' },
        deleted:        { label: 'Deleted',          cls: 'pf-badge-deleted' },
    };

    function statusBadge(status) {
        var cfg = STATUS_CFG[status] || { label: status, cls: 'pf-badge-default' };
        return '<span class="pf-badge ' + cfg.cls + '">' + escHtml(cfg.label) + '</span>';
    }

    // ── Utility ─────────────────────────────────────────────────────────
    function escHtml(str) {
        if (!str) return '';
        return String(str)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;');
    }

    function formatDate(iso) {
        if (!iso) return '—';
        var d = new Date(iso);
        if (isNaN(d)) return iso;
        return d.toLocaleDateString('en-PH', { year: 'numeric', month: 'short', day: 'numeric' }) +
               ' ' + d.toLocaleTimeString('en-PH', { hour: '2-digit', minute: '2-digit' });
    }

    function avatarInitials(name, username) {
        var display = (name && name.trim()) ? name.trim() : (username || '?');
        var parts   = display.split(' ');
        return (parts[0][0] + (parts[1] ? parts[1][0] : '')).toUpperCase();
    }

    // ── State helpers ───────────────────────────────────────────────────
    function showLoading() {
        feedLoading.classList.remove('hidden');
        feedList.classList.add('hidden');
        feedEmpty.classList.add('hidden');
        feedError.classList.add('hidden');
        feedPagination.innerHTML = '';
        resultsCount.textContent = '';
    }

    function showEmpty() {
        feedLoading.classList.add('hidden');
        feedList.classList.add('hidden');
        feedEmpty.classList.remove('hidden');
        feedError.classList.add('hidden');
    }

    function showError() {
        feedLoading.classList.add('hidden');
        feedList.classList.add('hidden');
        feedEmpty.classList.add('hidden');
        feedError.classList.remove('hidden');
    }

    function showFeed() {
        feedLoading.classList.add('hidden');
        feedList.classList.remove('hidden');
        feedEmpty.classList.add('hidden');
        feedError.classList.add('hidden');
    }

    // ── Thumbnail strip (max 4 + overflow badge) ────────────────────────
    function buildThumbnailStrip(files, onClickIndex) {
        if (!files || files.length === 0) return '';
        var MAX_VISIBLE = 4;
        var html  = '<div class="pf-thumb-strip">';
        var shown = Math.min(files.length, MAX_VISIBLE);

        for (var i = 0; i < shown; i++) {
            var f   = files[i];
            var idx = i;  // closure capture
            if (isImage(f.original_name || f.file_path)) {
                html += '<div class="pf-thumb pf-thumb-img" data-file-index="' + idx + '">' +
                        '<img src="' + escHtml(fileUrl(f.file_path)) + '" alt="' + escHtml(f.original_name) + '" loading="lazy">' +
                        '</div>';
            } else {
                var ext = (f.original_name || 'file').split('.').pop().toUpperCase().slice(0, 4);
                html += '<div class="pf-thumb pf-thumb-doc" data-file-index="' + idx + '">' +
                        '<i class="fi fi-rr-file-pdf pf-doc-icon"></i>' +
                        '<span class="pf-doc-ext">' + escHtml(ext) + '</span>' +
                        '</div>';
            }
        }

        if (files.length > MAX_VISIBLE) {
            html += '<div class="pf-thumb pf-thumb-overflow" data-file-index="0">+' + (files.length - MAX_VISIBLE) + '</div>';
        }
        html += '</div>';
        return html;
    }

    // ── Build a single feed card ─────────────────────────────────────────
    function buildCard(report) {
        var name     = (report.contractor_name && report.contractor_name.trim())
                        ? report.contractor_name : report.contractor_username || 'Unknown';
        var initials = avatarInitials(report.contractor_name, report.contractor_username);
        var hasPic   = report.contractor_pic && report.contractor_pic.trim();
        var fileCount = (report.files || []).length;

        return '<div class="pf-card" data-progress-id="' + report.progress_id + '">' +

            // ── Card header ──────────────────────────────────────────────
            '<div class="pf-card-header">' +
              '<div class="pf-avatar">' +
                (hasPic
                    ? '<img src="' + escHtml(fileUrl(report.contractor_pic)) + '" alt="avatar">'
                    : '<span>' + escHtml(initials) + '</span>'
                ) +
              '</div>' +
              '<div class="pf-card-meta">' +
                '<p class="pf-contractor-name">' + escHtml(name) + '</p>' +
                '<p class="pf-timestamp">' + escHtml(formatDate(report.submitted_at)) + '</p>' +
              '</div>' +
              '<div class="pf-card-badges">' +
                statusBadge(report.progress_status) +
              '</div>' +
            '</div>' +

            // ── Breadcrumb: project → milestone → item ───────────────────
            '<div class="pf-breadcrumb">' +
              '<span class="pf-bc-project">' +
                '<i class="fi fi-rr-folder-open"></i> ' + escHtml(report.project_title) +
              '</span>' +
              '<span class="pf-bc-sep">›</span>' +
              '<span class="pf-bc-milestone">' + escHtml(report.milestone_name) + '</span>' +
              '<span class="pf-bc-sep">›</span>' +
              '<span class="pf-bc-item">Item #' + report.sequence_order + ': ' + escHtml(report.item_title) + '</span>' +
            '</div>' +

            // ── Purpose / description ────────────────────────────────────
            (report.purpose
                ? '<p class="pf-purpose">' + escHtml(report.purpose) + '</p>'
                : '') +

            // ── Evidence thumbnails ──────────────────────────────────────
            (fileCount > 0
                ? '<div class="pf-evidence-row">' +
                    '<span class="pf-evidence-label"><i class="fi fi-rr-picture"></i> Evidence (' + fileCount + ')</span>' +
                    buildThumbnailStrip(report.files) +
                  '</div>'
                : '<p class="pf-no-evidence">No evidence files attached.</p>'
            ) +

            // ── View All Evidence button ─────────────────────────────────
            (fileCount > 0
                ? '<div class="pf-card-footer">' +
                    '<button class="pf-view-evidence-btn" data-progress-id="' + report.progress_id + '">View All Evidence</button>' +
                  '</div>'
                : '') +

        '</div>';
    }

    // ── Render the full feed ─────────────────────────────────────────────
    function renderFeed(data, meta) {
        if (!data || data.length === 0) {
            showEmpty();
            return;
        }
        showFeed();

        feedList.innerHTML = data.map(function (r) { return buildCard(r); }).join('');
        resultsCount.textContent = meta.total + ' report' + (meta.total !== 1 ? 's' : '');

        // Bind thumbnail clicks → open Universal File Viewer
        feedList.querySelectorAll('.pf-thumb').forEach(function (el) {
            el.addEventListener('click', function () {
                var card       = el.closest('.pf-card');
                var progressId = card.dataset.progressId;
                var fileIndex  = parseInt(el.dataset.fileIndex || '0', 10);
                var report     = data.find(function (r) { return String(r.progress_id) === String(progressId); });
                if (report && report.files && report.files.length > 0) {
                    openUFV(report.files, fileIndex);
                }
            });
        });

        // Bind "View All Evidence" buttons
        feedList.querySelectorAll('.pf-view-evidence-btn').forEach(function (btn) {
            btn.addEventListener('click', function () {
                var progressId = btn.dataset.progressId;
                var report     = data.find(function (r) { return String(r.progress_id) === String(progressId); });
                if (report && report.files && report.files.length > 0) {
                    openUFV(report.files, 0);
                }
            });
        });

        renderPagination(meta);
    }

    // ── Pagination ───────────────────────────────────────────────────────
    function renderPagination(meta) {
        if (meta.last_page <= 1) {
            feedPagination.innerHTML = '';
            return;
        }
        var html  = '';
        var cur   = meta.current_page;
        var last  = meta.last_page;

        // Prev
        html += '<button class="pf-page-btn' + (cur === 1 ? ' pf-page-disabled' : '') + '" data-page="' + (cur - 1) + '" ' + (cur === 1 ? 'disabled' : '') + '>' +
                '<i class="fi fi-rr-angle-left"></i></button>';

        // Pages (window of 5)
        var start = Math.max(1, cur - 2);
        var end   = Math.min(last, cur + 2);

        if (start > 1) {
            html += '<button class="pf-page-btn" data-page="1">1</button>';
            if (start > 2) html += '<span class="pf-page-ellipsis">…</span>';
        }

        for (var p = start; p <= end; p++) {
            html += '<button class="pf-page-btn' + (p === cur ? ' pf-page-active' : '') + '" data-page="' + p + '">' + p + '</button>';
        }

        if (end < last) {
            if (end < last - 1) html += '<span class="pf-page-ellipsis">…</span>';
            html += '<button class="pf-page-btn" data-page="' + last + '">' + last + '</button>';
        }

        // Next
        html += '<button class="pf-page-btn' + (cur === last ? ' pf-page-disabled' : '') + '" data-page="' + (cur + 1) + '" ' + (cur === last ? 'disabled' : '') + '>' +
                '<i class="fi fi-rr-angle-right"></i></button>';

        feedPagination.innerHTML = html;

        feedPagination.querySelectorAll('.pf-page-btn:not([disabled])').forEach(function (btn) {
            btn.addEventListener('click', function () {
                currentPage = parseInt(btn.dataset.page, 10);
                fetchFeed();
                window.scrollTo({ top: 0, behavior: 'smooth' });
            });
        });
    }

    // ── Universal File Viewer (UFV) ──────────────────────────────────────
    var ufvCurrentFiles = [];
    var ufvCurrentIndex = 0;

    function openUFV(files, startIndex) {
        if (!files || files.length === 0) return;
        ufvCurrentFiles = files;
        ufvCurrentIndex = Math.max(0, Math.min(startIndex, files.length - 1));
        renderUFV();
        if (ufvModal) {
            ufvModal.classList.remove('hidden');
            ufvModal.classList.add('flex');
        }
        document.body.style.overflow = 'hidden';
    }

    function closeUFV() {
        if (ufvModal) {
            ufvModal.classList.add('hidden');
            ufvModal.classList.remove('flex');
        }
        if (ufvViewport)  ufvViewport.innerHTML  = '';
        if (ufvFilmstrip) ufvFilmstrip.innerHTML = '';
        ufvCurrentFiles = [];
        ufvCurrentIndex = 0;
        document.body.style.overflow = '';
    }

    function renderUFV() {
        if (!ufvCurrentFiles.length) return;
        var f     = ufvCurrentFiles[ufvCurrentIndex];
        var name  = f.original_name || ('File ' + (ufvCurrentIndex + 1));
        var url   = fileUrl(f.file_path);
        var type  = getFileType(f.original_name || f.file_path);
        var total = ufvCurrentFiles.length;

        // Header
        if (ufvFileName) ufvFileName.textContent = name;
        if (ufvCounter)  ufvCounter.textContent  = (ufvCurrentIndex + 1) + ' / ' + total;
        if (ufvDownload) { ufvDownload.href = url; ufvDownload.download = name; }

        // Nav arrow visibility
        var showNav = total > 1;
        if (ufvPrev) ufvPrev.style.visibility = showNav ? 'visible' : 'hidden';
        if (ufvNext) ufvNext.style.visibility = showNav ? 'visible' : 'hidden';

        // Viewport content
        var html = '';
        if (type === 'image') {
            html = '<img src="' + escHtml(url) + '" alt="' + escHtml(name) + '" class="ufv-image" loading="lazy">';
        } else if (type === 'pdf') {
            html = '<iframe src="' + escHtml(url) + '" class="ufv-iframe" title="' + escHtml(name) + '"></iframe>';
        } else if (type === 'video') {
            html = '<video class="ufv-video" controls>' +
                   '<source src="' + escHtml(url) + '">' +
                   '<p class="ufv-media-name">Your browser cannot play this video. ' +
                   '<a href="' + escHtml(url) + '" download class="ufv-fallback-btn" style="display:inline;padding:.3rem .7rem">Download</a></p>' +
                   '</video>';
        } else if (type === 'audio') {
            html = '<div class="ufv-audio-wrap">' +
                   '<i class="fi fi-rr-music ufv-media-icon"></i>' +
                   '<p class="ufv-media-name">' + escHtml(name) + '</p>' +
                   '<audio controls class="ufv-audio"><source src="' + escHtml(url) + '"></audio>' +
                   '</div>';
        } else {
            var ext = name.split('.').pop().toUpperCase().slice(0, 8);
            html = '<div class="ufv-fallback">' +
                   '<i class="fi fi-rr-file ufv-fallback-icon"></i>' +
                   '<p class="ufv-fallback-name">' + escHtml(name) + '</p>' +
                   '<p class="ufv-fallback-ext">' + escHtml(ext) + ' file</p>' +
                   '<a href="' + escHtml(url) + '" download class="ufv-fallback-btn">Download File</a>' +
                   '</div>';
        }
        if (ufvViewport) ufvViewport.innerHTML = html;

        // Filmstrip
        if (ufvFilmstrip) {
            var filmHtml = '';
            ufvCurrentFiles.forEach(function (ff, i) {
                var fName = ff.original_name || ff.file_path || '';
                var fType = getFileType(fName);
                var fUrl  = fileUrl(ff.file_path);
                filmHtml += '<div class="ufv-film-thumb' + (i === ufvCurrentIndex ? ' ufv-film-active' : '') + '" data-ufv-idx="' + i + '">';
                if (fType === 'image') {
                    filmHtml += '<img src="' + escHtml(fUrl) + '" alt="" loading="lazy">';
                } else if (fType === 'pdf') {
                    filmHtml += '<i class="fi fi-rr-file-pdf ufv-film-icon"></i><span class="ufv-film-ext">PDF</span>';
                } else if (fType === 'video') {
                    filmHtml += '<i class="fi fi-rr-play-alt ufv-film-icon"></i><span class="ufv-film-ext">VID</span>';
                } else if (fType === 'audio') {
                    filmHtml += '<i class="fi fi-rr-music ufv-film-icon"></i><span class="ufv-film-ext">AUD</span>';
                } else {
                    var e2 = fName.split('.').pop().toUpperCase().slice(0, 4);
                    filmHtml += '<i class="fi fi-rr-file ufv-film-icon"></i><span class="ufv-film-ext">' + escHtml(e2) + '</span>';
                }
                filmHtml += '</div>';
            });
            ufvFilmstrip.innerHTML = filmHtml;

            ufvFilmstrip.querySelectorAll('.ufv-film-thumb').forEach(function (el) {
                el.addEventListener('click', function () {
                    ufvCurrentIndex = parseInt(el.dataset.ufvIdx, 10);
                    renderUFV();
                });
            });

            var activeThumb = ufvFilmstrip.querySelector('.ufv-film-active');
            if (activeThumb) {
                setTimeout(function () {
                    activeThumb.scrollIntoView({ behavior: 'smooth', block: 'nearest', inline: 'center' });
                }, 80);
            }
        }
    }

    // UFV navigation
    if (ufvPrev) {
        ufvPrev.addEventListener('click', function () {
            if (ufvCurrentFiles.length > 1) {
                ufvCurrentIndex = (ufvCurrentIndex - 1 + ufvCurrentFiles.length) % ufvCurrentFiles.length;
                renderUFV();
            }
        });
    }
    if (ufvNext) {
        ufvNext.addEventListener('click', function () {
            if (ufvCurrentFiles.length > 1) {
                ufvCurrentIndex = (ufvCurrentIndex + 1) % ufvCurrentFiles.length;
                renderUFV();
            }
        });
    }

    // UFV close
    if (ufvClose) { ufvClose.addEventListener('click', closeUFV); }
    if (ufvModal) {
        ufvModal.addEventListener('click', function (e) {
            if (e.target === ufvModal) closeUFV();
        });
    }
    document.addEventListener('keydown', function (e) {
        var isOpen = ufvModal && !ufvModal.classList.contains('hidden');
        if (e.key === 'Escape' && isOpen) { closeUFV(); return; }
        if (!isOpen) return;
        if (e.key === 'ArrowLeft'  && ufvCurrentFiles.length > 1) {
            ufvCurrentIndex = (ufvCurrentIndex - 1 + ufvCurrentFiles.length) % ufvCurrentFiles.length;
            renderUFV();
        }
        if (e.key === 'ArrowRight' && ufvCurrentFiles.length > 1) {
            ufvCurrentIndex = (ufvCurrentIndex + 1) % ufvCurrentFiles.length;
            renderUFV();
        }
    });

    // ── API Fetch ────────────────────────────────────────────────────────
    function fetchFeed(resetPage) {
        if (resetPage) currentPage = 1;

        showLoading();

        var params = new URLSearchParams({
            page:      currentPage,
            per_page:  15,
            status:    statusFilter ? statusFilter.value : 'all',
            search:    searchInput  ? searchInput.value.trim() : '',
            date_from: dateFrom     ? dateFrom.value : '',
            date_to:   dateTo       ? dateTo.value   : '',
            company:   selectedCompany,
        });

        fetch('/admin/progress-feed/data?' + params.toString(), {
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        })
            .then(function (r) {
                if (!r.ok) throw new Error('HTTP ' + r.status);
                return r.json();
            })
            .then(function (json) {
                renderFeed(json.data, json.meta);
            })
            .catch(function (err) {
                console.error('Progress feed fetch error:', err);
                showError();
            });
    }

    // ── Filter wiring ────────────────────────────────────────────────────
    if (searchInput) {
        searchInput.addEventListener('input', function () {
            clearTimeout(searchTimeout);
            searchTimeout = setTimeout(function () { fetchFeed(true); }, 380);
        });
    }

    if (statusFilter) {
        statusFilter.addEventListener('change', function () { fetchFeed(true); });
    }

    if (dateFrom) {
        dateFrom.addEventListener('change', function () {
            if (dateFrom.value && dateTo && dateTo.value && dateTo.value < dateFrom.value) {
                dateTo.value = dateFrom.value;
            }
            fetchFeed(true);
        });
    }

    if (dateTo) {
        dateTo.addEventListener('change', function () {
            if (dateTo.value && dateFrom && dateFrom.value && dateFrom.value > dateTo.value) {
                dateFrom.value = dateTo.value;
            }
            fetchFeed(true);
        });
    }

    if (resetBtn) {
        resetBtn.addEventListener('click', function () {
            if (searchInput)  searchInput.value  = '';
            if (statusFilter) statusFilter.value  = 'all';
            if (dateFrom)     dateFrom.value       = '';
            if (dateTo)       dateTo.value         = '';
            selectedCompany = '';
            if (companyFilter) companyFilter.value = '';
            fetchFeed(true);
        });
    }

    // ── Company / Contractor filter ──────────────────────────────────────
    function loadCompanies() {
        fetch('/admin/progress-feed/contractors', {
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        })
            .then(function (r) { return r.json(); })
            .then(function (list) { allCompanies = list || []; })
            .catch(function () { allCompanies = []; });
    }

    function renderCompanyDropdown(filter) {
        if (!companyDropdown) return;
        var term = (filter || '').toLowerCase();
        var matches = allCompanies.filter(function (name) {
            return name.toLowerCase().indexOf(term) !== -1;
        });

        if (matches.length === 0) {
            companyDropdown.innerHTML = '<div class="px-3 py-2 text-sm text-gray-400">No matches</div>';
        } else {
            companyDropdown.innerHTML = matches.map(function (name) {
                return '<div class="px-3 py-2 text-sm cursor-pointer hover:bg-indigo-50 transition" data-company="' + escHtml(name) + '">'
                     + escHtml(name) + '</div>';
            }).join('');
        }

        companyDropdown.classList.remove('hidden');

        companyDropdown.querySelectorAll('[data-company]').forEach(function (el) {
            el.addEventListener('mousedown', function (e) {
                e.preventDefault();
                selectedCompany = el.dataset.company;
                if (companyFilter) companyFilter.value = selectedCompany;
                companyDropdown.classList.add('hidden');
                fetchFeed(true);
            });
        });
    }

    if (companyFilter) {
        companyFilter.addEventListener('focus', function () {
            renderCompanyDropdown(companyFilter.value);
        });
        companyFilter.addEventListener('input', function () {
            if (companyFilter.value.trim() === '') {
                selectedCompany = '';
            }
            renderCompanyDropdown(companyFilter.value);
        });
        companyFilter.addEventListener('blur', function () {
            setTimeout(function () { if (companyDropdown) companyDropdown.classList.add('hidden'); }, 150);
        });
        companyFilter.addEventListener('keydown', function (e) {
            if (e.key === 'Escape') {
                companyDropdown.classList.add('hidden');
                companyFilter.blur();
            }
            if (e.key === 'Backspace' && companyFilter.value === '') {
                selectedCompany = '';
                fetchFeed(true);
            }
        });
    }

    loadCompanies();

    // ── Initial load ─────────────────────────────────────────────────────
    fetchFeed(true);
});
