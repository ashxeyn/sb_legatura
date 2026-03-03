document.addEventListener('DOMContentLoaded', () => {

    const csrf = document.querySelector('meta[name="csrf-token"]').content;

    loadData();

    async function loadData() {
        const res  = await fetch('/admin/settings/security/data', {
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': csrf,
            }
        });
        const json = await res.json();

        if (!json.success) {
            console.error('Failed to load account data:', json.message);
            return;
        }

        // AccountController returns data under json.data.admin (not json.data.user)
        const u = json.data.admin;

        document.getElementById('accountEmail').value    = u.email    ?? '';
        document.getElementById('accountUsername').value = u.username ?? '';

        if (u.profile_pic)
            document.getElementById('profileAvatar').src = '/storage/' + u.profile_pic;
        else
            document.getElementById('profileAvatar').src = '/img/default-avatar.png';

        renderLogs(json.data.logs);
    }

    function renderLogs(logs) {
        const tbody = document.getElementById('activityTableBody');
        tbody.innerHTML = '';

        if (!logs || !logs.length) {
            tbody.innerHTML = `<tr>
                <td colspan="3" class="px-4 py-4 text-gray-400">No activity yet.</td>
            </tr>`;
            return;
        }

        logs.forEach(log => {
            tbody.innerHTML += `
                <tr class="border-t">
                    <td class="px-4 py-3 font-medium">${log.action}</td>
                    <td class="px-4 py-3">${log.details ?? '-'}</td>
                    <td class="px-4 py-3 text-gray-500">${log.created_at}</td>
                </tr>
            `;
        });
    }

    // Profile Update
    document.getElementById('profileForm').addEventListener('submit', async e => {
        e.preventDefault();
        const fd = new FormData(e.target);

        const res  = await fetch('/admin/settings/security/update', {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': csrf, 'X-Requested-With': 'XMLHttpRequest' },
            body: fd
        });
        const json = await res.json();
        alert(json.success ? 'Profile updated' : json.message);
        if (json.success) loadData();
    });

    // Password Change
    document.getElementById('passwordForm').addEventListener('submit', async e => {
        e.preventDefault();
        const fd = new FormData(e.target);

        const res  = await fetch('/admin/settings/security/change-password', {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': csrf, 'X-Requested-With': 'XMLHttpRequest' },
            body: fd
        });
        const json = await res.json();
        alert(json.success ? 'Password changed' : json.message);
        if (json.success) e.target.reset();
    });

    // Delete
    document.getElementById('deleteAccountBtn').addEventListener('click', async () => {
        if (!confirm('Are you sure?')) return;

        const res  = await fetch('/admin/settings/security/delete', {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': csrf, 'X-Requested-With': 'XMLHttpRequest' }
        });
        const json = await res.json();
        if (json.success) window.location.href = '/login';
    });

});