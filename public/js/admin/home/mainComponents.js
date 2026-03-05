(function () {
    const aside = document.querySelector('aside');
    const main = document.querySelector('main');
    if (!aside || !main) return;

    const handle = document.createElement('div');
    handle.className = 'resize-handle';
    document.body.appendChild(handle);

    const stored = localStorage.getItem('legatura_sidebar_width');
    const minWidth = 180;
    const maxWidth = 520;
    const defaultWidth = aside.getBoundingClientRect().width || 288;
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

    // Nav group toggle — persists state, no auto-close of other groups
    navGroups.forEach((group, index) => {
        const btn = group.querySelector('.nav-btn');
        if (!btn) return;

        btn.addEventListener('click', () => {
            const submenu = group.querySelector('.nav-submenu');
            const arrow = btn.querySelector('.arrow');

            btn.classList.toggle('active');
            submenu?.classList.toggle('block');
            arrow?.classList.toggle('rotate-180');

            // Persist
            const state = getNavState();
            state[index] = btn.classList.contains('active');
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
    const clearNotifications = document.getElementById('clearNotifications');
    const notificationList = document.getElementById('notificationList');

    if (notificationBell && notificationDropdown) {
        notificationBell.addEventListener('click', function (e) {
            e.stopPropagation();
            notificationDropdown.classList.toggle('hidden');
        });

        // Close on outside click
        document.addEventListener('click', function (e) {
            if (!notificationDropdown.contains(e.target) && !notificationBell.contains(e.target)) {
                notificationDropdown.classList.add('hidden');
            }
        });

        // ESC to close
        document.addEventListener('keydown', function (e) {
            if (e.key === 'Escape') {
                notificationDropdown.classList.add('hidden');
            }
        });

        // Clear notifications (simple UX demo)
        if (clearNotifications && notificationList) {
            clearNotifications.addEventListener('click', function (e) {
                e.preventDefault();
                notificationList.innerHTML = '<li class="px-4 py-3"><p class="text-sm text-gray-500">No notifications</p></li>';
            });
        }
    }
    // =============== End Notifications Dropdown ===============

    // ================= User Menu Dropdown (3-dots) =================
    const userMenuBtn = document.getElementById('userMenuBtn');
    const userMenuDropdown = document.getElementById('userMenuDropdown');

    if (userMenuBtn && userMenuDropdown) {
        userMenuBtn.addEventListener('click', function (e) {
            e.stopPropagation();
            userMenuDropdown.classList.toggle('hidden');
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
