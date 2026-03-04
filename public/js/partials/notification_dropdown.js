/**
 * ─────────────────────────────────────────────────────────────
 *  Notification Dropdown – Shared JS (Owner + Contractor)
 *  Calls /api/notifications endpoints (session-authenticated)
 * ─────────────────────────────────────────────────────────────
 */
document.addEventListener('DOMContentLoaded', () => {
    // ── DOM refs ──────────────────────────────────────────────
    const bellBtn = document.getElementById('notificationBellBtn');
    const dropdown = document.getElementById('notificationDropdown');
    const closeBtn = document.getElementById('notificationCloseBtn');
    const badge = document.getElementById('notificationBadge');
    const markAllBtn = document.getElementById('markAllReadBtn');
    const listAll = document.getElementById('notificationListAll');
    const listProjects = document.getElementById('notificationListProjects');
    const listBids = document.getElementById('notificationListBids');
    const listPayments = document.getElementById('notificationListPayments');
    const listMessages = document.getElementById('notificationListMessages');
    const tabBtns = document.querySelectorAll('.notification-tab');

    if (!bellBtn || !dropdown) return;

    // ── State ─────────────────────────────────────────────────
    let notifications = [];
    let currentPage = 1;
    let hasMore = false;
    let isLoading = false;
    let activeTab = 'all';
    let isOpen = false;

    // ── CSRF helper ───────────────────────────────────────────
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content || '';

    // ── Notification type → visual style mapping ──────────────
    const notifStyles = {
        bid_accepted: { icon: 'fi fi-rr-hammer', bg: '#D1FAE5', color: '#10B981' },
        bid_rejected: { icon: 'fi fi-rr-hammer', bg: '#FEE2E2', color: '#EF4444' },
        bid_received: { icon: 'fi fi-rr-hammer', bg: '#DBEAFE', color: '#3B82F6' },
        milestone_submitted: { icon: 'fi fi-rr-briefcase', bg: '#DBEAFE', color: '#3B82F6' },
        milestone_approved: { icon: 'fi fi-rr-briefcase', bg: '#D1FAE5', color: '#10B981' },
        milestone_rejected: { icon: 'fi fi-rr-briefcase', bg: '#FEE2E2', color: '#EF4444' },
        milestone_completed: { icon: 'fi fi-rr-briefcase', bg: '#D1FAE5', color: '#10B981' },
        milestone_item_completed: { icon: 'fi fi-rr-briefcase', bg: '#D1FAE5', color: '#10B981' },
        milestone_deleted: { icon: 'fi fi-rr-briefcase', bg: '#FEE2E2', color: '#EF4444' },
        milestone_resubmitted: { icon: 'fi fi-rr-briefcase', bg: '#DBEAFE', color: '#3B82F6' },
        milestone_updated: { icon: 'fi fi-rr-briefcase', bg: '#DBEAFE', color: '#3B82F6' },
        progress_submitted: { icon: 'fi fi-rr-document', bg: '#DBEAFE', color: '#3B82F6' },
        progress_approved: { icon: 'fi fi-rr-check-circle', bg: '#D1FAE5', color: '#10B981' },
        progress_rejected: { icon: 'fi fi-rr-cross-circle', bg: '#FEE2E2', color: '#EF4444' },
        progress_updated: { icon: 'fi fi-rr-refresh', bg: '#FFF3E6', color: '#EC7E00' },
        payment_submitted: { icon: '₱', isText: true, bg: '#DBEAFE', color: '#3B82F6' },
        payment_approved: { icon: '₱', isText: true, bg: '#D1FAE5', color: '#10B981' },
        payment_rejected: { icon: '₱', isText: true, bg: '#FEE2E2', color: '#EF4444' },
        payment_updated: { icon: '₱', isText: true, bg: '#FFF3E6', color: '#EC7E00' },
        payment_deleted: { icon: '₱', isText: true, bg: '#FEE2E2', color: '#EF4444' },
        payment_due: { icon: '₱', isText: true, bg: '#FEF3C7', color: '#F59E0B' },
        payment_overdue: { icon: '₱', isText: true, bg: '#FEF3C7', color: '#F59E0B' },
        payment_fully_paid: { icon: '₱', isText: true, bg: '#D1FAE5', color: '#10B981' },
        payment_overpaid: { icon: '₱', isText: true, bg: '#D1FAE5', color: '#10B981' },
        payment_underpaid_carry: { icon: '₱', isText: true, bg: '#FEF3C7', color: '#F59E0B' },
        dispute_opened: { icon: 'fi fi-rr-triangle-warning', bg: '#FEF3C7', color: '#F59E0B' },
        dispute_updated: { icon: 'fi fi-rr-refresh', bg: '#FFF3E6', color: '#EC7E00' },
        dispute_cancelled: { icon: 'fi fi-rr-ban', bg: '#F1F5F9', color: '#64748B' },
        dispute_under_review: { icon: 'fi fi-rr-refresh', bg: '#FFF3E6', color: '#EC7E00' },
        dispute_resolved: { icon: 'fi fi-rr-check-circle', bg: '#D1FAE5', color: '#10B981' },
        dispute_rejected: { icon: 'fi fi-rr-cross-circle', bg: '#FEE2E2', color: '#EF4444' },
        project_completed: { icon: 'fi fi-rr-check-circle', bg: '#D1FAE5', color: '#10B981' },
        project_halted: { icon: 'fi fi-rr-trash', bg: '#FEE2E2', color: '#EF4444' },
        project_terminated: { icon: 'fi fi-rr-trash', bg: '#FEE2E2', color: '#EF4444' },
        project_update: { icon: 'fi fi-rr-refresh', bg: '#FFF3E6', color: '#EC7E00' },
        team_invite: { icon: 'fi fi-rr-users', bg: '#FFF3E6', color: '#EC7E00' },
        team_removed: { icon: 'fi fi-rr-trash', bg: '#FEE2E2', color: '#EF4444' },
        team_role_changed: { icon: 'fi fi-rr-users', bg: '#FFF3E6', color: '#EC7E00' },
        team_access_changed: { icon: 'fi fi-rr-users', bg: '#FFF3E6', color: '#EC7E00' },
    };
    const defaultStyle = { icon: 'fi fi-rr-bell', bg: '#F1F5F9', color: '#64748B' };

    // ── Category mapping (matches mobile) ─────────────────────
    function getCategory(type) {
        if (type.startsWith('bid_')) return 'bids';
        if (type.startsWith('payment_')) return 'payments';
        if (type.startsWith('project_') || type.startsWith('milestone_') || type.startsWith('progress_')) return 'projects';
        if (type.startsWith('dispute_') || type.startsWith('team_')) return 'messages';
        return 'all';
    }

    // ── Relative time formatter ───────────────────────────────
    function formatRelativeTime(timestamp) {
        if (!timestamp) return '';
        const now = new Date();
        const date = new Date(timestamp.replace(' ', 'T'));
        if (isNaN(date.getTime())) return '';
        const diffMs = now - date;
        const diffMins = Math.floor(diffMs / 60000);
        const diffHrs = Math.floor(diffMs / 3600000);
        const diffDays = Math.floor(diffMs / 86400000);
        if (diffMins < 1) return 'Just now';
        if (diffMins < 60) return `${diffMins}m`;
        if (diffHrs < 24) return `${diffHrs}h`;
        if (diffDays === 1) return 'Yesterday';
        if (diffDays < 7) return `${diffDays}d`;
        return date.toLocaleDateString('en-US', { month: 'short', day: 'numeric' });
    }

    // ── Date group classifier ─────────────────────────────────
    function getDateGroup(timestamp) {
        if (!timestamp) return 'EARLIER';
        const now = new Date(); now.setHours(0, 0, 0, 0);
        const date = new Date(timestamp.replace(' ', 'T'));
        if (isNaN(date.getTime())) return 'EARLIER';
        const d = new Date(date); d.setHours(0, 0, 0, 0);
        const yesterday = new Date(now); yesterday.setDate(yesterday.getDate() - 1);
        if (d.getTime() === now.getTime()) return 'TODAY';
        if (d.getTime() === yesterday.getTime()) return 'YESTERDAY';
        if (d.getTime() > now.getTime() - 7 * 86400000) return 'THIS WEEK';
        return 'EARLIER';
    }

    // ── Type label formatter ──────────────────────────────────
    function getTypeLabel(type) {
        return type.replace(/_/g, ' ').replace(/\b\w/g, c => c.toUpperCase());
    }

    // ── API helpers ───────────────────────────────────────────
    async function apiFetch(url, options = {}) {
        const res = await fetch(url, {
            headers: {
                'Accept': 'application/json',
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken,
                ...options.headers,
            },
            credentials: 'same-origin',
            ...options,
        });
        return res.json();
    }

    async function loadNotifications(page = 1, append = false) {
        if (isLoading) return;
        isLoading = true;

        if (page === 1 && !append) {
            showLoading();
        }

        try {
            const response = await apiFetch(`/notifications/json?page=${page}&per_page=20`);
            if (response.success && response.data) {
                const items = (response.data.notifications || []).map(n => ({
                    id: n.id,
                    type: n.type || 'general',
                    title: n.title || '',
                    message: n.message || '',
                    timestamp: n.created_at || '',
                    is_read: n.is_read,
                    priority: n.priority || 'normal',
                    reference_type: n.reference_type,
                    reference_id: n.reference_id,
                    notification_role: n.notification_role || 'both',
                }));

                if (append) {
                    const existingIds = new Set(notifications.map(n => n.id));
                    const unique = items.filter(n => !existingIds.has(n.id));
                    notifications = [...notifications, ...unique];
                } else {
                    notifications = items;
                }
                currentPage = page;
                const curPg = response.data.current_page ?? page;
                const lastPg = response.data.last_page ?? 1;
                hasMore = curPg < lastPg;

                renderNotifications();
                updateBadge();
            }
        } catch (e) {
            console.error('Error loading notifications:', e);
            showError();
        } finally {
            isLoading = false;
        }
    }

    async function markAsRead(notificationId) {
        // Optimistic UI
        const n = notifications.find(n => n.id === notificationId);
        if (n) n.is_read = true;
        renderNotifications();
        updateBadge();
        try {
            await apiFetch(`/notifications/${notificationId}/read`, { method: 'POST' });
        } catch (e) { console.error('markAsRead failed:', e); }
    }

    async function markAllAsRead() {
        notifications.forEach(n => n.is_read = true);
        renderNotifications();
        updateBadge();
        try {
            await apiFetch('/notifications/read-all', { method: 'POST' });
        } catch (e) { console.error('markAllAsRead failed:', e); }
    }

    async function markGroupAsRead(dateGroup) {
        const filtered = getFilteredNotifications();
        const grouped = groupByDate(filtered);
        const ids = (grouped[dateGroup] || []).map(n => n.id);
        ids.forEach(id => {
            const n = notifications.find(n => n.id === id);
            if (n) n.is_read = true;
        });
        renderNotifications();
        updateBadge();
        for (const id of ids) {
            try { await apiFetch(`/notifications/${id}/read`, { method: 'POST' }); } catch (e) { }
        }
    }

    async function handleNotificationClick(notificationId) {
        const n = notifications.find(n => n.id === notificationId);
        if (n) n.is_read = true;
        renderNotifications();
        updateBadge();

        try {
            // Use the web redirect endpoint — it marks as read + does a 302
            window.location.href = `/notifications/${notificationId}/redirect`;
        } catch (e) {
            console.error('Error navigating:', e);
        }
    }

    async function fetchUnreadCount() {
        try {
            const response = await apiFetch('/notifications/unread-count');
            if (response.success && response.data) {
                const count = response.data.unread_count || 0;
                setBadge(count);
            }
        } catch (e) { /* silent */ }
    }

    // ── Badge ─────────────────────────────────────────────────
    function setBadge(count) {
        if (!badge) return;
        if (count > 0) {
            badge.textContent = count > 99 ? '99+' : count;
            badge.classList.remove('hidden');
        } else {
            badge.classList.add('hidden');
        }
    }

    function updateBadge() {
        const unread = notifications.filter(n => !n.is_read).length;
        setBadge(unread);
    }

    // ── Filtering ─────────────────────────────────────────────
    function getFilteredNotifications() {
        if (activeTab === 'all') return notifications;
        return notifications.filter(n => getCategory(n.type) === activeTab);
    }

    function getUnreadCount(tab) {
        if (tab === 'all') return notifications.filter(n => !n.is_read).length;
        return notifications.filter(n => !n.is_read && getCategory(n.type) === tab).length;
    }

    // ── Grouping ──────────────────────────────────────────────
    function groupByDate(items) {
        const groups = {};
        items.forEach(n => {
            const group = getDateGroup(n.timestamp);
            if (!groups[group]) groups[group] = [];
            groups[group].push(n);
        });
        return groups;
    }

    // ── Rendering ─────────────────────────────────────────────
    function renderNotifications() {
        const filtered = getFilteredNotifications();
        const targetList = getActiveList();
        if (!targetList) return;

        // Hide all lists, show active
        [listAll, listProjects, listBids, listPayments, listMessages].forEach(el => {
            if (el) el.classList.add('hidden');
        });
        targetList.classList.remove('hidden');

        if (filtered.length === 0) {
            targetList.innerHTML = renderEmpty();
            return;
        }

        const grouped = groupByDate(filtered);
        const dateOrder = ['TODAY', 'YESTERDAY', 'THIS WEEK', 'EARLIER'];
        let html = '';

        dateOrder.forEach(dateLabel => {
            const items = grouped[dateLabel];
            if (!items || items.length === 0) return;
            const unreadInGroup = items.filter(n => !n.is_read).length;

            html += `<div class="notif-date-header">
                <span class="notif-date-label">${dateLabel}</span>
                ${unreadInGroup > 0 ? `<button class="notif-date-mark-read" data-group="${dateLabel}">Mark all as read</button>` : ''}
            </div>`;

            items.forEach(n => {
                html += renderNotifItem(n);
            });
        });

        // Load more
        if (hasMore) {
            html += `<div class="notif-load-more">
                <button id="loadMoreNotifs">Load more</button>
            </div>`;
        }

        targetList.innerHTML = html;

        // Bind events
        targetList.querySelectorAll('.notif-item').forEach(el => {
            el.addEventListener('click', () => handleNotificationClick(parseInt(el.dataset.id)));
        });
        targetList.querySelectorAll('.notif-date-mark-read').forEach(el => {
            el.addEventListener('click', (e) => {
                e.stopPropagation();
                markGroupAsRead(el.dataset.group);
            });
        });
        const loadMoreBtn = targetList.querySelector('#loadMoreNotifs');
        if (loadMoreBtn) {
            loadMoreBtn.addEventListener('click', () => loadNotifications(currentPage + 1, true));
        }

        // Update tab badges
        updateTabBadges();
    }

    function renderNotifItem(n) {
        const style = notifStyles[n.type] || defaultStyle;
        const label = getTypeLabel(n.type);
        const time = formatRelativeTime(n.timestamp);
        const unreadClass = n.is_read ? '' : ' unread';

        let iconHtml;
        if (style.isText) {
            iconHtml = `<span class="text-icon" style="color:${style.color}">${style.icon}</span>`;
        } else {
            iconHtml = `<i class="${style.icon}" style="color:${style.color}"></i>`;
        }

        return `<div class="notif-item${unreadClass}" data-id="${n.id}">
            <div class="notif-icon" style="background:${style.bg}">
                ${iconHtml}
            </div>
            <div class="notif-content">
                <div class="notif-header">
                    <span class="notif-title">${escapeHtml(n.title)}</span>
                    <span class="notif-time">${time}</span>
                </div>
                <div class="notif-message">${escapeHtml(n.message)}</div>
                <span class="notif-type-pill" style="background:${style.bg};color:${style.color}">
                    ${style.isText
                ? `<span style="font-weight:800;font-size:0.6rem;margin-right:2px">${style.icon}</span>`
                : `<i class="${style.icon}" style="font-size:0.6rem;margin-right:2px"></i>`}
                    ${label}
                </span>
            </div>
            ${!n.is_read ? '<div class="notif-unread-dot"></div>' : ''}
        </div>`;
    }

    function renderEmpty() {
        const tabLabel = activeTab === 'all' ? '' : ` ${activeTab}`;
        return `<div class="notif-empty">
            <div class="notif-empty-icon">
                <i class="fi fi-rr-bell-slash"></i>
            </div>
            <div class="notif-empty-title">No Notifications</div>
            <div class="notif-empty-message">${activeTab === 'all'
                ? "You're all caught up! Check back later."
                : `No${tabLabel} notifications yet.`}</div>
        </div>`;
    }

    function showLoading() {
        const list = getActiveList();
        if (list) {
            list.innerHTML = `<div class="notif-loading">
                <i class="fi fi-rr-spinner spinner-rotate"></i>
                <span>Loading notifications...</span>
            </div>`;
            list.classList.remove('hidden');
        }
    }

    function showError() {
        const list = getActiveList();
        if (list) {
            list.innerHTML = `<div class="notif-empty">
                <div class="notif-empty-icon"><i class="fi fi-rr-triangle-warning"></i></div>
                <div class="notif-empty-title">Failed to load</div>
                <div class="notif-empty-message">Something went wrong. Try again later.</div>
            </div>`;
        }
    }

    function getActiveList() {
        switch (activeTab) {
            case 'projects': return listProjects;
            case 'bids': return listBids;
            case 'payments': return listPayments;
            case 'messages': return listMessages;
            default: return listAll;
        }
    }

    function updateTabBadges() {
        tabBtns.forEach(btn => {
            const tab = btn.dataset.tab;
            const count = getUnreadCount(tab);
            let badgeEl = btn.querySelector('.tab-badge');
            if (count > 0) {
                if (!badgeEl) {
                    badgeEl = document.createElement('span');
                    badgeEl.className = 'tab-badge';
                    btn.appendChild(badgeEl);
                }
                badgeEl.textContent = count > 99 ? '99+' : count;
            } else if (badgeEl) {
                badgeEl.remove();
            }
        });
    }

    // ── Utility ───────────────────────────────────────────────
    function escapeHtml(str) {
        if (!str) return '';
        const div = document.createElement('div');
        div.textContent = str;
        return div.innerHTML;
    }

    // ── Toggle dropdown ───────────────────────────────────────
    function openDropdown() {
        dropdown.classList.remove('hidden');
        isOpen = true;
        loadNotifications(1, false);
    }

    function closeDropdown() {
        dropdown.classList.add('hidden');
        isOpen = false;
    }

    bellBtn.addEventListener('click', (e) => {
        e.stopPropagation();
        if (isOpen) {
            closeDropdown();
        } else {
            openDropdown();
        }
    });

    if (closeBtn) {
        closeBtn.addEventListener('click', (e) => {
            e.stopPropagation();
            closeDropdown();
        });
    }

    // Close on click outside
    document.addEventListener('click', (e) => {
        if (isOpen && !dropdown.contains(e.target) && !bellBtn.contains(e.target)) {
            closeDropdown();
        }
    });

    // ── Tab switching ─────────────────────────────────────────
    tabBtns.forEach(btn => {
        btn.addEventListener('click', () => {
            activeTab = btn.dataset.tab;
            tabBtns.forEach(b => b.classList.remove('active'));
            btn.classList.add('active');
            renderNotifications();
        });
    });

    // ── Mark all read ─────────────────────────────────────────
    if (markAllBtn) {
        markAllBtn.addEventListener('click', () => markAllAsRead());
    }

    // ── Polling for unread count ──────────────────────────────
    fetchUnreadCount();
    setInterval(fetchUnreadCount, 30000); // every 30s
});
