(function () {
    const aside = document.querySelector('aside');
    const main = document.querySelector('main');
    if (!aside || !main) return;

    const handle = document.createElement('div');
    handle.className = 'resize-handle';
    document.body.appendChild(handle);

    const stored = localStorage.getItem('legatura_sidebar_width');
    const minWidth = 160;
    const maxWidth = 520;
    const defaultWidth = aside.getBoundingClientRect().width || 208;
    let sidebarWidth = stored ? parseInt(stored, 10) : defaultWidth;

    function applyWidth(w) {
        sidebarWidth = Math.max(minWidth, Math.min(maxWidth, w));
        aside.style.width = sidebarWidth + 'px';
        main.style.marginLeft = sidebarWidth + 'px';
        handle.style.left = sidebarWidth + 'px';
    }

    applyWidth(sidebarWidth);

    let dragging = false;

    function startDrag(e) {
        e.preventDefault();
        dragging = true;
        handle.classList.add('active');
        document.addEventListener('mousemove', onDrag);
        document.addEventListener('mouseup', stopDrag);
        document.addEventListener('touchmove', onDrag, { passive: false });
        document.addEventListener('touchend', stopDrag);
    }

    function onDrag(e) {
        if (!dragging) return;
        const clientX = e.touches ? e.touches[0].clientX : e.clientX;
        const newW = Math.max(minWidth, Math.min(maxWidth, clientX));
        applyWidth(newW);
    }

    function stopDrag() {
        if (!dragging) return;
        dragging = false;
        handle.classList.remove('active');
        document.removeEventListener('mousemove', onDrag);
        document.removeEventListener('mouseup', stopDrag);
        document.removeEventListener('touchmove', onDrag);
        document.removeEventListener('touchend', stopDrag);
        localStorage.setItem('legatura_sidebar_width', String(sidebarWidth));
    }

    handle.addEventListener('mousedown', startDrag);
    handle.addEventListener('touchstart', startDrag, { passive: false });

    handle.addEventListener('dblclick', function () {
        localStorage.removeItem('legatura_sidebar_width');
        applyWidth(defaultWidth);
    });

    // ================= Sidebar Nav State Persistence =================
    const STORAGE_KEY_NAV = 'legatura_sidebar_nav_state';
    const STORAGE_KEY_NESTED = 'legatura_sidebar_nested_state';

    function getNavState() {
        try { return JSON.parse(localStorage.getItem(STORAGE_KEY_NAV)) || {}; } catch { return {}; }
    }
    function getNestedState() {
        try { return JSON.parse(localStorage.getItem(STORAGE_KEY_NESTED)) || {}; } catch { return {}; }
    }
    function saveNavState(state) {
        localStorage.setItem(STORAGE_KEY_NAV, JSON.stringify(state));
    }
    function saveNestedState(state) {
        localStorage.setItem(STORAGE_KEY_NESTED, JSON.stringify(state));
    }

    const navGroups = document.querySelectorAll('.nav-group');
    const navState = getNavState();
    const nestedState = getNestedState();

    // Restore nav group open/closed state on page load
    navGroups.forEach((group, index) => {
        const submenu = group.querySelector('.nav-submenu');
        const btn = group.querySelector('.nav-btn');
        const arrow = btn?.querySelector('.arrow');

        if (navState[index]) {
            btn?.classList.add('active');
            submenu?.classList.add('block');
            arrow?.classList.add('rotate-180');
        }
    });

    // Restore nested submenu state on page load
    document.querySelectorAll('.submenu-nested-btn').forEach((button, index) => {
        if (nestedState[index]) {
            const nestedContent = button.nextElementSibling;
            const arrow = button.querySelector('.arrow-small');
            button.classList.add('active');
            nestedContent?.classList.add('block');
            arrow?.classList.add('rotate-180');
        }
    });

    navGroups.forEach((group, index) => {
        const btn = group.querySelector('.nav-btn');
        if (!btn) return;

        btn.addEventListener('click', () => {
            const submenu = group.querySelector('.nav-submenu');
            const arrow = btn.querySelector('.arrow');
            const isCurrentlyOpen = btn.classList.contains('active');

            if (isCurrentlyOpen) {
                // Close the current group
                btn.classList.remove('active');
                submenu?.classList.remove('block');
                arrow?.classList.remove('rotate-180');
            } else {
                // Close all other groups first
                navGroups.forEach((otherGroup, otherIndex) => {
                    if (otherIndex !== index) {
                        const otherBtn = otherGroup.querySelector('.nav-btn');
                        const otherSubmenu = otherGroup.querySelector('.nav-submenu');
                        const otherArrow = otherBtn?.querySelector('.arrow');

                        otherBtn?.classList.remove('active');
                        otherSubmenu?.classList.remove('block');
                        otherArrow?.classList.remove('rotate-180');
                    }
                });

                // Open the clicked group
                btn.classList.add('active');
                submenu?.classList.add('block');
                arrow?.classList.add('rotate-180');
            }

            // Persist state - only store which group is open (if any)
            const state = {};
            navGroups.forEach((g, i) => {
                const groupBtn = g.querySelector('.nav-btn');
                state[i] = groupBtn?.classList.contains('active') || false;
            });
            saveNavState(state);
        });
    });

    // Nested submenu toggle — persists state, no auto-close of other nested items
    document.querySelectorAll('.submenu-nested-btn').forEach((button, index) => {
        button.addEventListener('click', (e) => {
            e.preventDefault();
            e.stopPropagation();

            const nestedContent = button.nextElementSibling;
            const arrow = button.querySelector('.arrow-small');

            button.classList.toggle('active');
            nestedContent?.classList.toggle('block');
            arrow?.classList.toggle('rotate-180');

            // Persist
            const state = getNestedState();
            state[index] = button.classList.contains('active');
            saveNestedState(state);
        });
    });

    // ================= Notifications Dropdown (global) =================
    const notificationBell = document.getElementById('notificationBell');
    const notificationDropdown = document.getElementById('notificationDropdown');
    const markNotificationsRead = document.getElementById('markNotificationsRead');
    const notificationList = document.getElementById('notificationList');
    const notificationUnreadCount = document.getElementById('notificationUnreadCount');
    let currentNotificationIds = [];

    function getCsrfToken() {
        const meta = document.querySelector('meta[name="csrf-token"]');
        return meta ? meta.getAttribute('content') : '';
    }

    function escapeHtml(value) {
        return String(value ?? '')
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#39;');
    }

    function renderRelativeTime(dateString) {
        if (!dateString) return '-';
        const then = new Date(dateString).getTime();
        if (Number.isNaN(then)) return dateString;
        const diffMs = Date.now() - then;
        const diffMin = Math.floor(diffMs / 60000);
        if (diffMin < 1) return 'just now';
        if (diffMin < 60) return diffMin + ' min ago';
        const diffHr = Math.floor(diffMin / 60);
        if (diffHr < 24) return diffHr + ' hr ago';
        const diffDay = Math.floor(diffHr / 24);
        if (diffDay < 7) return diffDay + ' day' + (diffDay > 1 ? 's' : '') + ' ago';
        return new Date(dateString).toLocaleString();
    }

    function iconForType(type) {
        const map = {
            user_registered: ['fi fi-ss-user-add', 'bg-indigo-100 text-indigo-600'],
            failed_login_attempt: ['fi fi-ss-exclamation', 'bg-red-100 text-red-700'],
            project_reported: ['fi fi-ss-warning', 'bg-yellow-100 text-yellow-700'],
            profile_updated: ['fi fi-ss-user-pen', 'bg-blue-100 text-blue-700'],
            password_reset: ['fi fi-ss-key', 'bg-purple-100 text-purple-700'],
            email_verified: ['fi fi-ss-check-circle', 'bg-green-100 text-green-700'],
            account_status_changed: ['fi fi-ss-shield-check', 'bg-orange-100 text-orange-700']
        };
        return map[type] || ['fi fi-ss-bell', 'bg-gray-100 text-gray-600'];
    }

    function renderNotificationRows(rows) {
        if (!notificationList) return;
        currentNotificationIds = rows.map(item => item.id).filter(Boolean);

        if (!rows.length) {
            notificationList.innerHTML = '<li class="px-4 py-4 text-sm text-gray-500">No notifications found.</li>';
            return;
        }

        notificationList.innerHTML = rows.map(item => {
            const icon = iconForType(item.activity_type);
            const unreadBadge = item.is_read
                ? '<span class="inline-block px-2 py-1 text-xs rounded-full bg-gray-100 text-gray-600">Read</span>'
                : '<span class="inline-block px-2 py-1 text-xs rounded-full bg-yellow-100 text-yellow-700">Unread</span>';

            const userName = item.user && item.user.name ? item.user.name : 'Unknown User';
            const sourceLabel = item.source === 'mobile' ? 'Mobile' : 'Web';

            return '<li class="px-4 py-3 hover:bg-gray-50 transition" data-id="' + item.id + '">' +
                '<div class="flex items-start gap-3">' +
                '<div class="w-8 h-8 rounded-full ' + icon[1] + ' flex items-center justify-center">' +
                '<i class="' + icon[0] + '"></i>' +
                '</div>' +
                '<div class="flex-1 min-w-0">' +
                '<p class="text-sm font-semibold text-gray-800 truncate">' + escapeHtml(item.title) + '</p>' +
                '<p class="text-xs text-gray-700 mt-0.5">' + escapeHtml(item.message) + '</p>' +
                '<p class="text-xs text-gray-500 mt-1">User: ' + escapeHtml(userName) + ' • Source: ' + escapeHtml(sourceLabel) + '</p>' +
                '<p class="text-xs text-gray-500">' + escapeHtml(renderRelativeTime(item.created_at)) + '</p>' +
                '</div>' +
                unreadBadge +
                '</div>' +
                '</li>';
        }).join('');
    }

    function renderUnreadCount(count) {
        if (!notificationUnreadCount) return;
        if (!count || count < 1) {
            notificationUnreadCount.classList.add('hidden');
            notificationUnreadCount.classList.remove('flex');
            notificationUnreadCount.textContent = '0';
            return;
        }
        notificationUnreadCount.textContent = String(Math.min(99, count));
        notificationUnreadCount.classList.remove('hidden');
        notificationUnreadCount.classList.add('flex');
    }

    async function fetchNotifications() {
        try {
            const res = await fetch('/admin/notifications?limit=5', {
                headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' }
            });
            if (!res.ok) return;
            const payload = await res.json();
            if (!payload || !payload.success) return;
            const rows = payload.data?.notifications || [];
            const unreadCount = payload.data?.unread_count || 0;
            renderNotificationRows(rows);
            renderUnreadCount(unreadCount);
        } catch {
            // Keep dropdown non-blocking if API is unavailable.
        }
    }

    async function markNotificationsAsRead(all = true) {
        const ids = all ? [] : currentNotificationIds;
        try {
            await fetch('/admin/notifications/read', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': getCsrfToken(),
                    'Accept': 'application/json'
                },
                body: JSON.stringify({ all, ids })
            });
            fetchNotifications();
        } catch {
            // Silent fail for UX continuity.
        }
    }

    if (notificationBell && notificationDropdown) {
        const openNotificationDropdown = () => {
            notificationDropdown.classList.remove('hidden');
            requestAnimationFrame(() => {
                notificationDropdown.classList.add('is-open');
            });
        };

        const closeNotificationDropdown = () => {
            notificationDropdown.classList.remove('is-open');
            window.setTimeout(() => {
                if (!notificationDropdown.classList.contains('is-open')) {
                    notificationDropdown.classList.add('hidden');
                }
            }, 180);
        };

        notificationBell.addEventListener('click', function (e) {
            e.stopPropagation();
            notificationDropdown.classList.toggle('hidden');
            fetchNotifications();
        });

        // Close on outside click
        document.addEventListener('click', function (e) {
            if (!notificationDropdown.contains(e.target) && !notificationBell.contains(e.target)) {
                closeNotificationDropdown();
            }
        });

        // ESC to close
        document.addEventListener('keydown', function (e) {
            if (e.key === 'Escape') {
                closeNotificationDropdown();
            }
        });

        // Mark all notifications as read.
        if (markNotificationsRead) {
            markNotificationsRead.addEventListener('click', function (e) {
                e.preventDefault();
                markNotificationsAsRead(true);
            });
        }

        // Polling fallback for near real-time updates.
        fetchNotifications();
        setInterval(fetchNotifications, 8000);
    }
    // =============== End Notifications Dropdown ===============

    // ================= User Menu Dropdown (3-dots) =================
    const userMenuBtn = document.getElementById('userMenuBtn');
    const userMenuDropdown = document.getElementById('userMenuDropdown');

    if (userMenuBtn && userMenuDropdown) {
        userMenuBtn.addEventListener('click', function (e) {
            e.stopPropagation();
            e.preventDefault();
            
            // Toggle visibility
            const isHidden = userMenuDropdown.classList.contains('hidden');
            
            if (isHidden) {
                // Position the dropdown next to the button
                const rect = userMenuBtn.getBoundingClientRect();
                userMenuDropdown.style.left = (rect.right + -20) + 'px'; // 8px gap from button
                userMenuDropdown.style.top = (rect.top - 120) + 'px'; // Move up 40px for better positioning
                
                // Show dropdown
                userMenuDropdown.classList.remove('hidden');
            } else {
                // Hide dropdown
                userMenuDropdown.classList.add('hidden');
            }
        });

        document.addEventListener('click', function (e) {
            if (!userMenuDropdown.contains(e.target) && !userMenuBtn.contains(e.target)) {
                userMenuDropdown.classList.add('hidden');
            }
        });

        document.addEventListener('keydown', function (e) {
            if (e.key === 'Escape') {
                userMenuDropdown.classList.add('hidden');
            }
        });
    }
    // =============== End User Menu Dropdown ===============

    // ================= Logout Confirmation Modal =================
    const logoutBtn = document.getElementById('logoutBtn');
    const logoutModal = document.getElementById('logoutModal');
    const logoutModalContent = document.getElementById('logoutModalContent');
    const logoutModalClose = document.getElementById('logoutModalClose');
    const logoutModalCancel = document.getElementById('logoutModalCancel');
    const logoutModalConfirm = document.getElementById('logoutModalConfirm');
    const logoutForm = document.getElementById('logoutForm');

    function openLogoutModal() {
        if (!logoutModal) return;
        logoutModal.classList.remove('hidden');
        logoutModal.classList.add('flex');
        setTimeout(() => {
            if (logoutModalContent) logoutModalContent.classList.remove('scale-95');
        }, 10);
    }

    function closeLogoutModal() {
        if (!logoutModal || !logoutModalContent) return;
        logoutModalContent.classList.add('scale-95');
        setTimeout(() => {
            logoutModal.classList.add('hidden');
            logoutModal.classList.remove('flex');
        }, 150);
    }

    if (logoutBtn) {
        logoutBtn.addEventListener('click', function (e) {
            e.preventDefault();
            openLogoutModal();
        });
    }

    if (logoutModalClose) logoutModalClose.addEventListener('click', closeLogoutModal);
    if (logoutModalCancel) logoutModalCancel.addEventListener('click', closeLogoutModal);

    if (logoutModal) {
        logoutModal.addEventListener('click', function (e) {
            if (e.target === logoutModal) closeLogoutModal();
        });
        document.addEventListener('keydown', function (e) {
            if (e.key === 'Escape' && !logoutModal.classList.contains('hidden')) closeLogoutModal();
        });
    }

    if (logoutModalConfirm) {
        logoutModalConfirm.addEventListener('click', function () {
            if (logoutForm) {
                logoutForm.submit();
            } else {
                window.location.href = '/';
            }
        });
    }
    // =============== End Logout Confirmation Modal ===============
 })();
