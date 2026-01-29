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

    document.querySelectorAll('.nav-btn').forEach(button => {
        button.addEventListener('click', () => {
            const group = button.closest('.nav-group');
            const submenu = group?.querySelector('.nav-submenu');
            const arrow = button.querySelector('.arrow');

            document.querySelectorAll('.nav-group').forEach(otherGroup => {
                if (otherGroup !== group) {
                    otherGroup.querySelector('.nav-submenu')?.classList.remove('block');
                    otherGroup.querySelector('.nav-btn')?.classList.remove('active');
                    otherGroup.querySelector('.arrow')?.classList.remove('rotate-180');
                }
            });

            button.classList.toggle('active');

            if (submenu) {
                submenu.classList.toggle('block');
            }

            if (arrow) {
                arrow.classList.toggle('rotate-180');
            }
        });
    });

    // Nested submenu toggle
    document.querySelectorAll('.submenu-nested-btn').forEach(button => {
        button.addEventListener('click', (e) => {
            e.preventDefault();
            e.stopPropagation();
            
            const nestedContent = button.nextElementSibling;
            const arrow = button.querySelector('.arrow-small');

            // Close all other nested items
            document.querySelectorAll('.submenu-nested-btn').forEach(btn => {
                if (btn !== button) {
                    btn.classList.remove('active');
                    btn.nextElementSibling?.classList.remove('block');
                    btn.querySelector('.arrow-small')?.classList.remove('rotate-180');
                }
            });

            // Toggle current nested item
            button.classList.toggle('active');
            if (nestedContent) {
                nestedContent.classList.toggle('block');
            }
            if (arrow) {
                arrow.classList.toggle('rotate-180');
            }
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

    // ================= Logout Confirmation =================
    const logoutBtn = document.getElementById('logoutBtn');
    if (logoutBtn) {
        logoutBtn.addEventListener('click', function (e) {
            e.preventDefault();
            if (confirm('Are you sure you want to logout?')) {
                // Redirect to login page or perform logout
                window.location.href = '/';
            }
        });
    }
    // =============== End Logout Confirmation ===============
 })();
