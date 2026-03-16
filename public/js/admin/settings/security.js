document.addEventListener('DOMContentLoaded', () => {
  const csrf = document.querySelector('meta[name="csrf-token"]').content;

  // --- Helpers ---
  function esc(str) {
    return String(str ?? '').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;');
  }
  function formatDate(str) {
    if (!str) return '-';
    try { return new Date(str).toLocaleString('en-PH', { year:'numeric', month:'short', day:'numeric', hour:'2-digit', minute:'2-digit' }); }
    catch { return str; }
  }
  function initials(first, last) { return ((first?.[0] ?? '') + (last?.[0] ?? '')).toUpperCase() || '?'; }
  function toast(msg, type = 'success', duration = 3500) {
    const el = document.getElementById('toast');
    el.textContent = msg; el.className = 'show ' + type;
    setTimeout(() => { el.className = ''; }, duration);
  }
  function badgeFor(action) {
    const map = {
      // Security / account
      admin_login:            ['badge-login',           'Login'],
      profile_updated:        ['badge-profile',         'Profile Updated'],
      password_changed:       ['badge-password',        'Password Changed'],
      account_deleted:        ['badge-deleted',         'Account Deleted'],
      // Admin member management
      member_created:         ['badge-member-create',   'Admin Created'],
      member_updated:         ['badge-member-update',   'Admin Updated'],
      member_deleted:         ['badge-member-delete',   'Admin Deactivated'],
      // Bid management
      bid_approved:           ['badge-approve',         'Bid Approved'],
      bid_rejected:           ['badge-reject',          'Bid Rejected'],
      bid_updated:            ['badge-update',          'Bid Updated'],
      bid_deleted:            ['badge-deleted',         'Bid Deleted'],
      // Payment management
      payment_verified:       ['badge-approve',         'Payment Verified'],
      payment_rejected:       ['badge-reject',          'Payment Rejected'],
      payment_updated:        ['badge-update',          'Payment Updated'],
      payment_deleted:        ['badge-deleted',         'Payment Deleted'],
      // Posting management
      posting_approved:       ['badge-approve',         'Posting Approved'],
      posting_rejected:       ['badge-reject',          'Posting Rejected'],
      // Review management
      review_deleted:         ['badge-deleted',         'Review Deleted'],
      // User management
      property_owner_created: ['badge-user-create',     'Owner Added'],
      property_owner_updated: ['badge-update',          'Owner Updated'],
      property_owner_deleted: ['badge-deleted',         'Owner Deleted'],
      contractor_created:     ['badge-user-create',     'Contractor Added'],
      contractor_updated:     ['badge-update',          'Contractor Updated'],
      contractor_deleted:     ['badge-deleted',         'Contractor Deleted'],
      // Verification
      verification_approved:  ['badge-approve',         'Verification Approved'],
      verification_rejected:  ['badge-reject',          'Verification Rejected'],
      // Project management
      project_created:             ['badge-user-create', 'Project Created'],
      project_updated:             ['badge-update',      'Project Updated'],
      project_deleted:             ['badge-deleted',     'Project Deleted'],
      project_approved:            ['badge-approve',     'Project Approved'],
      project_rejected:            ['badge-reject',      'Project Rejected'],
      contractor_assigned:         ['badge-update',      'Contractor Assigned'],
      // Disputes
      dispute_approved_for_review: ['badge-approve',     'Dispute Reviewed'],
      dispute_rejected:            ['badge-reject',      'Dispute Rejected'],
      dispute_resolved:            ['badge-approve',     'Dispute Resolved'],
      dispute_finalized:           ['badge-approve',     'Dispute Finalized'],
      // Subscriptions
      subscription_plan_created:   ['badge-user-create', 'Plan Created'],
      subscription_plan_updated:   ['badge-update',      'Plan Updated'],
      subscription_plan_deleted:   ['badge-deleted',     'Plan Deleted'],
      // Bids (project-level)
      bid_accepted:                ['badge-approve',     'Bid Accepted'],
      bid_rejected_by_admin:       ['badge-reject',      'Bid Rejected'],
      bid_created:                 ['badge-user-create', 'Bid Created'],
      bid_updated_direct:          ['badge-update',      'Bid Updated'],
      bid_deleted_direct:          ['badge-deleted',     'Bid Deleted'],
      // Milestones
      milestone_created:           ['badge-user-create', 'Milestone Created'],
      milestone_updated:           ['badge-update',      'Milestone Updated'],
      milestone_deleted:           ['badge-deleted',     'Milestone Deleted'],
      // Milestone payments
      milestone_payment_created:   ['badge-user-create', 'Payment Created'],
      milestone_payment_updated:   ['badge-update',      'Payment Updated'],
      milestone_payment_deleted:   ['badge-deleted',     'Payment Deleted'],
      // User suspension / reactivation
      property_owner_suspended:    ['badge-reject',      'Owner Suspended'],
      contractor_suspended:        ['badge-reject',      'Contractor Suspended'],
      user_reactivated:            ['badge-approve',     'User Reactivated'],
      // Team member management
      team_member_created:         ['badge-user-create', 'Team Member Added'],
      team_member_updated:         ['badge-update',      'Team Member Updated'],
      team_member_deactivated:     ['badge-reject',      'Team Member Deactivated'],
      team_member_reactivated:     ['badge-approve',     'Team Member Reactivated'],
      representative_changed:      ['badge-update',      'Rep Changed'],
      // AI
      ai_analysis_run:             ['badge-login',       'AI Analyzed'],
      // Subscriptions
      subscription_deactivated:    ['badge-reject',      'Sub Deactivated'],
      subscription_reactivated:    ['badge-approve',     'Sub Reactivated'],
      // Showcase
      showcase_approved:           ['badge-approve',     'Showcase Approved'],
      showcase_rejected:           ['badge-reject',      'Showcase Rejected'],
      showcase_deleted:            ['badge-deleted',     'Showcase Deleted'],
      showcase_restored:           ['badge-approve',     'Showcase Restored'],
      // Timeline & extensions
      timeline_extended:           ['badge-update',      'Timeline Extended'],
      extension_approved:          ['badge-approve',     'Extension Approved'],
      extension_rejected:          ['badge-reject',      'Extension Rejected'],
      extension_revision_requested:['badge-update',      'Revision Requested'],
    };
    const [cls, label] = map[action] ?? ['badge-default', esc(action).replace(/_/g,' ')];
    return '<span class="action-badge ' + cls + '">' + label + '</span>';
  }
  function prettyDetails(raw) {
    if (!raw) return '<span class="text-gray-300">-</span>';
    try {
      const obj = JSON.parse(raw);
      return Object.entries(obj).map(([k,v]) => '<span class="font-medium text-gray-500">' + esc(k) + ':</span> ' + esc(v)).join(' &nbsp;&middot;&nbsp; ');
    } catch { return esc(raw); }
  }
  function setAvatar(imgId, picPath, firstName, lastName, szCls) {
    const img = document.getElementById(imgId); if (!img) return;
    if (picPath) {
      img.onload  = () => { img.classList.remove('hidden'); };
      img.onerror = () => { img.classList.add('hidden'); _initPh(img, firstName, lastName, szCls); };
      img.src = '/storage/' + picPath;
    } else { img.classList.add('hidden'); _initPh(img, firstName, lastName, szCls); }
  }
  function _initPh(img, firstName, lastName, szCls) {
    let ph = img.parentElement?.querySelector('[data-initials]');
    if (!ph) { ph = document.createElement('div'); ph.setAttribute('data-initials','1'); ph.className = szCls + ' rounded-full bg-indigo-600 text-white font-bold flex items-center justify-center flex-shrink-0'; img.parentElement?.insertBefore(ph, img); }
    ph.textContent = initials(firstName, lastName); ph.classList.remove('hidden');
  }
  async function apiFetch(url, options = {}) {
    const headers = { 'X-CSRF-TOKEN': csrf, 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json', ...(options.headers || {}) };
    if (options.body instanceof FormData) delete headers['Content-Type'];
    try {
      const res = await fetch(url, { ...options, headers });
      const ct  = res.headers.get('content-type') || '';
      if (ct.includes('application/json')) { const data = await res.json(); return { ok: res.ok, status: res.status, data }; }
      const text = await res.text();
      if (res.status === 302 || text.includes('<form') || text.includes('login')) return { ok:false, status:res.status, data:{ success:false, message:'Session expired.' }, redirect:true };
      return { ok:false, status:res.status, data:{ success:false, message:'Server error (' + res.status + ').' } };
    } catch(err) { return { ok:false, status:0, data:{ success:false, message:'Network error: ' + err.message } }; }
  }

  // --- Tabs ---
  let membersLoaded = false, teamLogLoaded = false;
  document.querySelectorAll('.tab-btn').forEach(btn => {
    btn.addEventListener('click', () => {
      document.querySelectorAll('.tab-btn').forEach(b => b.classList.remove('active'));
      document.querySelectorAll('.tab-panel').forEach(p => p.classList.remove('active'));
      btn.classList.add('active');
      document.getElementById('tab-' + btn.dataset.tab).classList.add('active');
      if (btn.dataset.tab === 'members' && !membersLoaded) { membersLoaded = true; loadMembers(); }
      if (btn.dataset.tab === 'team'    && !teamLogLoaded) { teamLogLoaded = true; loadTeamActivity(); }
    });
  });

  // --- Modal helpers ---
  function openModal(id)  { document.getElementById(id).classList.add('open'); }
  function closeModal(id) { document.getElementById(id).classList.remove('open'); }
  document.querySelectorAll('.modal-backdrop').forEach(m => m.addEventListener('click', e => { if (e.target === m) m.classList.remove('open'); }));
  document.querySelectorAll('.modal-close-btn').forEach(btn => btn.addEventListener('click', () => closeModal(btn.dataset.modal)));

  // =========================================================
  // TAB 1: MY PROFILE — Personal Activity Log
  // Fetches ALL admin_activity_logs for the logged-in admin.
  // This includes security actions (login, profile update, password
  // change) AND every CRUD operation they performed across the platform
  // (managing users, bids, disputes, subscriptions, etc.).
  // =========================================================
  async function loadData() {
    const { ok, data, redirect } = await apiFetch('/admin/settings/security/data');
    if (redirect) { window.location.href = '/login'; return; }
    if (!ok || !data.success) {
      document.getElementById('globalError').classList.remove('hidden');
      document.getElementById('globalErrorMsg').textContent = data.message || 'Unknown error.';
      document.getElementById('profileSkeleton').classList.add('hidden');
      document.getElementById('logCount').textContent = 'error';
      document.getElementById('activityTableBody').innerHTML = '<tr><td colspan="4" class="px-4 py-7 text-center text-red-500 text-sm">' + esc(data.message || 'Could not load.') + '</td></tr>';
      return;
    }
    const a = data.data.admin;
    setAvatar('profileAvatar', a.profile_pic, a.first_name, a.last_name, 'w-28 h-28 text-2xl');
    setAvatar('modalAvatar',   a.profile_pic, a.first_name, a.last_name, 'w-20 h-20 text-xl');
    document.getElementById('dispFirstName').textContent  = a.first_name  || '-';
    document.getElementById('dispMiddleName').textContent = a.middle_name || '-';
    document.getElementById('dispLastName').textContent   = a.last_name   || '-';
    document.getElementById('dispEmail').textContent      = a.email       || '-';
    document.getElementById('dispUsername').textContent    = a.username    || '-';
    if (a.created_at) document.getElementById('profileMemberSince').textContent = 'Member since ' + new Date(a.created_at).toLocaleDateString('en-PH', { year:'numeric', month:'long', day:'numeric' });
    document.getElementById('editFirstName').value  = a.first_name  || '';
    document.getElementById('editMiddleName').value = a.middle_name || '';
    document.getElementById('editLastName').value   = a.last_name   || '';
    document.getElementById('editEmail').value      = a.email       || '';
    document.getElementById('editUsername').value    = a.username    || '';
    document.getElementById('profileSkeleton').classList.add('hidden');
    document.getElementById('profileDisplay').classList.remove('hidden');

    // Hide Add Members tab for non-super admins
    const membersTabBtn = document.querySelector('[data-tab="members"]');
    if (membersTabBtn) membersTabBtn.style.display = a.is_super ? '' : 'none';

    renderMyLogs(data.data.logs);
  }

  // --- Personal Activity Log rendering (4 columns: Action, Details, IP, Date) ---
  const LOGS_PER_PAGE = 5;
  let allMyLogs = [];
  let myLogsPage = 1;
  function renderMyLogs(logs) {
    allMyLogs = Array.isArray(logs) ? logs : [];
    myLogsPage = 1;
    populateActionFilter(allMyLogs, 'myLogActionFilter');
    applyMyLogFilter();
  }
  function applyMyLogFilter() {
    const from = document.getElementById('myLogDateFrom').value;
    const to   = document.getElementById('myLogDateTo').value;
    const action = document.getElementById('myLogActionFilter').value;
    const term = (document.getElementById('myLogSearch').value || '').trim().toLowerCase();
    let filtered = allMyLogs;
    if (from) filtered = filtered.filter(l => l.created_at && l.created_at.slice(0,10) >= from);
    if (to)   filtered = filtered.filter(l => l.created_at && l.created_at.slice(0,10) <= to);
    if (action) filtered = filtered.filter(l => (l.action || '') === action);
    if (term) {
      filtered = filtered.filter(l => {
        const detailText = (() => { try { return JSON.stringify(JSON.parse(l.details || '{}')); } catch { return String(l.details || ''); } })();
        const haystack = [l.action || '', detailText, l.ip_address || '', l.created_at || ''].join(' ').toLowerCase();
        return haystack.includes(term);
      });
    }
    const tbody   = document.getElementById('activityTableBody');
    const countEl = document.getElementById('logCount');
    const pageMeta = document.getElementById('myLogPageMeta');
    const prevBtn = document.getElementById('myLogPrevBtn');
    const nextBtn = document.getElementById('myLogNextBtn');
    countEl.textContent = filtered.length ? filtered.length + ' records' : '0 records';
    const totalPages = Math.max(1, Math.ceil(filtered.length / LOGS_PER_PAGE));
    if (myLogsPage > totalPages) myLogsPage = totalPages;
    const startIdx = (myLogsPage - 1) * LOGS_PER_PAGE;
    const pageRows = filtered.slice(startIdx, startIdx + LOGS_PER_PAGE);
    prevBtn.disabled = myLogsPage <= 1;
    nextBtn.disabled = myLogsPage >= totalPages;

    if (!filtered.length) {
      tbody.innerHTML = '<tr><td colspan="4" class="px-4 py-8 text-center text-gray-400 text-sm">' + (allMyLogs.length ? 'No logs match the selected filters.' : 'No activity recorded yet. Actions like editing your profile or changing your password will appear here.') + '</td></tr>';
      pageMeta.textContent = 'Showing 0-0 of 0';
      return;
    }
    tbody.innerHTML = pageRows.map(function(log) {
      return '<tr class="activity-log-row">'
        + '<td class="px-4 py-3">' + badgeFor(log.action) + '</td>'
        + '<td class="px-4 py-3 text-slate-500 text-xs max-w-xs">' + prettyDetails(log.details) + '</td>'
        + '<td class="px-4 py-3 font-mono text-xs text-slate-400">' + esc(log.ip_address ?? '-') + '</td>'
        + '<td class="px-4 py-3 text-xs text-slate-400">' + formatDate(log.created_at) + '</td>'
        + '</tr>';
    }).join('');
    pageMeta.textContent = 'Showing ' + (startIdx + 1) + '-' + (startIdx + pageRows.length) + ' of ' + filtered.length;
  }

  function populateActionFilter(logs, selectId) {
    const select = document.getElementById(selectId);
    if (!select) return;
    const prev = select.value || '';
    const actions = [...new Set((logs || []).map(l => l.action).filter(Boolean))].sort();
    select.innerHTML = '<option value="">All Actions</option>' + actions.map(a => '<option value="' + esc(a) + '">' + esc(a.replace(/_/g, ' ')) + '</option>').join('');
    if (actions.includes(prev)) {
      select.value = prev;
    }
  }

  // --- Profile edit modal ---
  let initialProfileFormState = null;

  function normalizeProfileValue(v) {
    return String(v || '').trim();
  }

  function captureProfileFormState() {
    return {
      first_name: normalizeProfileValue(document.getElementById('editFirstName')?.value),
      middle_name: normalizeProfileValue(document.getElementById('editMiddleName')?.value),
      last_name: normalizeProfileValue(document.getElementById('editLastName')?.value),
      email: normalizeProfileValue(document.getElementById('editEmail')?.value),
      username: normalizeProfileValue(document.getElementById('editUsername')?.value),
    };
  }

  function clearProfileEditErrors() {
    const alertEl = document.getElementById('profileEditErrorAlert');
    const listEl = document.getElementById('profileEditErrorList');
    if (listEl) listEl.innerHTML = '';
    if (alertEl) alertEl.classList.add('hidden');
  }

  function showProfileEditErrors(errors) {
    const alertEl = document.getElementById('profileEditErrorAlert');
    const listEl = document.getElementById('profileEditErrorList');
    if (!alertEl || !listEl) return;

    const validErrors = (errors || []).filter(Boolean);
    if (!validErrors.length) return;

    listEl.innerHTML = validErrors.map(function(msg) {
      return '<li>' + esc(msg) + '</li>';
    }).join('');

    alertEl.classList.remove('hidden');
  }

  function hasProfileFormChanged() {
    if (!initialProfileFormState) return true;

    const currentState = captureProfileFormState();
    const avatarInput = document.getElementById('avatarInput');
    const hasNewAvatar = Boolean(avatarInput && avatarInput.files && avatarInput.files.length);

    if (hasNewAvatar) return true;

    return Object.keys(initialProfileFormState).some(function(key) {
      return currentState[key] !== initialProfileFormState[key];
    });
  }

  document.getElementById('openEditBtn').addEventListener('click', () => {
    clearProfileEditErrors();
    const avatarInput = document.getElementById('avatarInput');
    if (avatarInput) avatarInput.value = '';
    initialProfileFormState = captureProfileFormState();
    openModal('editModal');
  });
  document.getElementById('closeEditBtn').addEventListener('click',  () => closeModal('editModal'));
  document.getElementById('cancelEditBtn').addEventListener('click', () => closeModal('editModal'));

  const closeProfileEditErrorAlertBtn = document.getElementById('closeProfileEditErrorAlert');
  if (closeProfileEditErrorAlertBtn) {
    closeProfileEditErrorAlertBtn.addEventListener('click', clearProfileEditErrors);
  }

  ['editFirstName', 'editMiddleName', 'editLastName', 'editEmail', 'editUsername'].forEach(function(id) {
    const field = document.getElementById(id);
    if (!field) return;
    field.addEventListener('input', clearProfileEditErrors);
  });

  const avatarInputEl = document.getElementById('avatarInput');
  if (avatarInputEl) {
    avatarInputEl.addEventListener('change', clearProfileEditErrors);
  }

  document.getElementById('avatarInput').addEventListener('change', e => {
    const file = e.target.files[0]; if (!file) return;
    const reader = new FileReader();
    reader.onload = ev => { ['modalAvatar','profileAvatar'].forEach(id => { const img = document.getElementById(id); if (img) { img.classList.remove('hidden'); img.src = ev.target.result; } }); };
    reader.readAsDataURL(file);
  });

  document.getElementById('profileForm').addEventListener('submit', async e => {
    e.preventDefault();
    const spinner = document.getElementById('savingSpinner'); const saveBtn = document.getElementById('saveProfileBtn');
    clearProfileEditErrors();

    if (!hasProfileFormChanged()) {
      showProfileEditErrors(['No changes detected. Please modify at least one field before saving.']);
      return;
    }

    spinner.classList.remove('hidden'); saveBtn.disabled = true;
    const { ok, data, redirect } = await apiFetch('/admin/settings/security/update', { method:'POST', body:new FormData(e.target) });
    spinner.classList.add('hidden'); saveBtn.disabled = false;
    if (redirect) { window.location.href = '/login'; return; }
    if (ok && data.success) { closeModal('editModal'); toast('Profile updated successfully.'); loadData(); }
    else { showProfileEditErrors([data.message || 'Update failed.']); }
  });

  // --- Password strength ---
  document.getElementById('newPassword').addEventListener('input', e => {
    const v = e.target.value;
    const bar = document.getElementById('pwStrengthBar'); const fill = document.getElementById('pwStrengthFill'); const lbl = document.getElementById('pwStrengthLabel');
    if (!v) { bar.classList.add('hidden'); lbl.classList.add('hidden'); return; }
    bar.classList.remove('hidden'); lbl.classList.remove('hidden');
    const score = (v.length >= 8 ? 1 : 0) + (/[A-Z]/.test(v) ? 1 : 0) + (/[0-9]/.test(v) ? 1 : 0) + (/[^A-Za-z0-9]/.test(v) ? 1 : 0);
    const lvls = [['25%','bg-red-400','Weak','text-red-500'],['25%','bg-red-400','Weak','text-red-500'],['55%','bg-yellow-400','Fair','text-yellow-500'],['75%','bg-blue-400','Good','text-blue-500'],['100%','bg-green-500','Strong','text-green-600']];
    const [w,fc,txt,tc] = lvls[score];
    fill.style.width = w; fill.className = 'h-full rounded-full transition-all duration-300 ' + fc; lbl.textContent = txt; lbl.className = 'text-xs mt-1 ' + tc;
  });
  document.getElementById('confirmPassword').addEventListener('input', () => {
    const match = document.getElementById('newPassword').value === document.getElementById('confirmPassword').value;
    document.getElementById('pwMatchMsg').classList.toggle('hidden', match || !document.getElementById('confirmPassword').value);
  });

  document.getElementById('passwordForm').addEventListener('submit', async e => {
    e.preventDefault();
    const errEl = document.getElementById('pwError'); const spinner = document.getElementById('pwSpinner'); const btn = e.target.querySelector('button[type=submit]');
    errEl.classList.add('hidden');
    if (document.getElementById('newPassword').value !== document.getElementById('confirmPassword').value) { errEl.textContent = 'Passwords do not match.'; errEl.classList.remove('hidden'); return; }
    spinner.classList.remove('hidden'); btn.disabled = true;
    const { ok, data, redirect } = await apiFetch('/admin/settings/security/change-password', { method:'POST', body:new FormData(e.target) });
    spinner.classList.add('hidden'); btn.disabled = false;
    if (redirect) { window.location.href = '/login'; return; }
    if (ok && data.success) { toast('Password changed successfully.'); e.target.reset(); document.getElementById('pwStrengthBar').classList.add('hidden'); document.getElementById('pwStrengthLabel').classList.add('hidden'); loadData(); }
    else { errEl.textContent = data.message || 'Failed.'; errEl.classList.remove('hidden'); }
  });

  document.getElementById('deleteAccountBtn').addEventListener('click', () => {
    document.getElementById('deleteAccountError').classList.add('hidden');
    const input = document.getElementById('deleteAccountConfirmInput');
    input.value = '';
    document.getElementById('confirmDeleteAccountBtn').disabled = true;
    openModal('deleteAccountModal');
  });

  document.getElementById('deleteAccountConfirmInput').addEventListener('input', function () {
    document.getElementById('confirmDeleteAccountBtn').disabled = this.value !== 'Confirm Delete';
  });

  document.getElementById('confirmDeleteAccountBtn').addEventListener('click', async () => {
    const errEl = document.getElementById('deleteAccountError');
    const spinner = document.getElementById('deleteAccountSpinner');
    const btn = document.getElementById('confirmDeleteAccountBtn');
    errEl.classList.add('hidden');
    spinner.classList.remove('hidden');
    btn.disabled = true;

    const { ok, data, redirect } = await apiFetch('/admin/settings/security/delete', { method:'POST' });

    spinner.classList.add('hidden');
    btn.disabled = false;

    if (redirect) { window.location.href = '/login'; return; }
    if (ok && data.success) {
      closeModal('deleteAccountModal');
      window.location.href = '/login';
      return;
    }

    errEl.textContent = (data && data.message) ? data.message : 'Could not delete account.';
    errEl.classList.remove('hidden');
  });

  // =========================================================
  // TAB 2: ADD MEMBERS
  // =========================================================
  async function loadMembers() {
    const { ok, data, redirect } = await apiFetch('/admin/settings/security/members');
    if (redirect) { window.location.href = '/login'; return; }
    if (!ok || !data.success) {
      document.getElementById('membersTableBody').innerHTML = '<tr><td colspan="6" class="px-4 py-8 text-center text-red-500 text-sm">' + esc(data.message) + '</td></tr>';
      const countBadgeErr = document.getElementById('memberCountBadge');
      if (countBadgeErr) countBadgeErr.textContent = 'error';
      return;
    }
    renderMembersTable(data.data.members);
  }

  function renderMembersTable(members) {
    const tbody = document.getElementById('membersTableBody');
    const countBadge = document.getElementById('memberCountBadge');
    if (countBadge) countBadge.textContent = members.length ? (members.length + ' admins') : '0 admins';

    if (!members.length) {
      tbody.innerHTML = '<tr><td colspan="6" class="px-4 py-10 text-center text-gray-400 text-sm">No other admin accounts found.</td></tr>';
      return;
    }

    tbody.innerHTML = members.map(function(m) {
      var name = esc(((m.first_name || '') + (m.middle_name ? ' ' + m.middle_name : '') + ' ' + (m.last_name || '')).trim()) || '-';
      var statusBadge = m.is_active
        ? '<span class="inline-flex items-center gap-1 text-[11px] font-semibold text-green-700 bg-green-50 border border-green-200 px-2 py-0.5 rounded-full">&#9679; Active</span>'
        : '<span class="inline-flex items-center gap-1 text-[11px] font-semibold text-rose-600 bg-rose-50 border border-rose-200 px-2 py-0.5 rounded-full">&#9679; Inactive</span>';
      return '<tr class="member-row">'
        + '<td class="px-4 py-3"><div class="flex items-center gap-2.5"><div class="w-9 h-9 rounded-full bg-blue-600 text-white text-xs font-bold flex items-center justify-center flex-shrink-0">' + esc(initials(m.first_name, m.last_name)) + '</div><span class="font-medium text-slate-700 text-xs">' + name + '</span></div></td>'
        + '<td class="px-4 py-3 text-slate-500 text-xs">@' + esc(m.username) + '</td>'
        + '<td class="px-4 py-3 text-slate-500 text-xs">'  + esc(m.email) + '</td>'
        + '<td class="px-4 py-3">' + statusBadge + '</td>'
        + '<td class="px-4 py-3 text-xs text-slate-400">' + formatDate(m.created_at) + '</td>'
        + '<td class="px-4 py-3 text-center">'
        +   '<div class="flex items-center justify-center gap-1.5">'
        +   '<button class="view-member-btn w-8 h-8 inline-flex items-center justify-center p-1.5 rounded-xl border border-indigo-200 bg-indigo-50 text-indigo-600 shadow-[0_2px_8px_rgba(0,0,0,0.06)] hover:bg-indigo-100 hover:shadow-sm hover:border-indigo-300 hover:-translate-y-0.5 transition-all active:scale-95" data-id="' + esc(m.admin_id) + '" title="View &amp; Edit"><span class="inline-flex items-center gap-0.5"><i class="fi fi-rr-eye text-[11px] leading-none" style="pointer-events:none"></i><i class="fi fi-rr-pencil text-[11px] leading-none" style="pointer-events:none"></i></span></button>'
        +   (!m.is_deleted
              ? '<button class="toggle-member-btn ' + (m.is_active ? 'delete-member-btn w-8 h-8 inline-flex items-center justify-center p-1.5 rounded-xl border border-red-200 bg-red-50 text-red-600 shadow-[0_2px_8px_rgba(0,0,0,0.06)] hover:bg-red-100 hover:shadow-sm hover:border-red-300 hover:-translate-y-0.5 transition-all active:scale-95' : 'reactivate-member-btn w-8 h-8 inline-flex items-center justify-center p-1.5 rounded-xl border border-green-200 bg-green-50 text-green-600 shadow-[0_2px_8px_rgba(0,0,0,0.06)] hover:bg-green-100 hover:shadow-sm hover:border-green-300 hover:-translate-y-0.5 transition-all active:scale-95') + '" data-id="' + esc(m.admin_id) + '" data-name="' + name + '" data-active="' + (m.is_active ? '1' : '0') + '" title="' + (m.is_active ? 'Deactivate' : 'Reactivate') + '"><i class="' + (m.is_active ? 'fi fi-sr-user-slash' : 'fi fi-sr-user-check') + ' text-[13px] leading-none" style="pointer-events:none"></i></button>'
              + '<button class="hard-delete-member-btn w-8 h-8 inline-flex items-center justify-center p-1.5 rounded-xl border border-gray-300 bg-gray-50 text-gray-500 shadow-[0_2px_8px_rgba(0,0,0,0.06)] hover:bg-gray-100 hover:shadow-sm hover:border-gray-400 hover:-translate-y-0.5 transition-all active:scale-95" data-id="' + esc(m.admin_id) + '" data-name="' + name + '" title="Delete"><i class="fi fi-sr-trash text-[13px] leading-none" style="pointer-events:none"></i></button>'
              : '')
        +   '</div>'
        + '</td>'
        + '</tr>';
    }).join('');
    document.querySelectorAll('.view-member-btn').forEach(btn => btn.addEventListener('click', () => openMemberModal(btn.dataset.id)));
    document.querySelectorAll('.toggle-member-btn').forEach(btn => btn.addEventListener('click', () => {
      if (btn.dataset.active === '1') deleteMember(btn.dataset.id, btn.dataset.name);
      else reactivateMember(btn.dataset.id, btn.dataset.name);
    }));
    document.querySelectorAll('.hard-delete-member-btn').forEach(btn => btn.addEventListener('click', () => hardDeleteMember(btn.dataset.id, btn.dataset.name)));
  }

  document.getElementById('openCreateAdminBtn').addEventListener('click', () => {
    document.getElementById('createAdminForm').reset();
    document.getElementById('createAdminError').classList.add('hidden');
    document.querySelectorAll('#createAdminForm .field-error').forEach(el => el.classList.add('hidden'));
    document.querySelectorAll('#createAdminForm input').forEach(el => el.classList.remove('border-red-400'));
    openModal('createAdminModal');
  });

  function validateCreateAdminForm() {
    const fields = [
      { id: 'createFirstName',  check: v => v.trim() !== '' },
      { id: 'createLastName',   check: v => v.trim() !== '' },
      { id: 'createEmail',      check: v => /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(v.trim()) },
      { id: 'createUsername',   check: v => v.trim() !== '' },
      { id: 'createPassword',   check: v => v.length >= 8 },
    ];
    let valid = true;
    fields.forEach(({ id, check }) => {
      const input = document.getElementById(id);
      const errEl = document.querySelector(`[data-for="${id}"]`);
      if (!check(input.value)) {
        input.classList.add('border-red-400');
        if (errEl) errEl.classList.remove('hidden');
        valid = false;
      } else {
        input.classList.remove('border-red-400');
        if (errEl) errEl.classList.add('hidden');
      }
    });
    return valid;
  }

  // Clear field error on input
  ['createFirstName','createLastName','createEmail','createUsername','createPassword'].forEach(id => {
    document.getElementById(id)?.addEventListener('input', function () {
      this.classList.remove('border-red-400');
      const errEl = document.querySelector(`[data-for="${id}"]`);
      if (errEl) errEl.classList.add('hidden');
    });
  });

  document.getElementById('createAdminForm').addEventListener('submit', async e => {
    e.preventDefault();
    if (!validateCreateAdminForm()) return;
    const errEl = document.getElementById('createAdminError'); const spinner = document.getElementById('createAdminSpinner'); const btn = document.getElementById('createAdminSubmitBtn');
    errEl.classList.add('hidden'); spinner.classList.remove('hidden'); btn.disabled = true;
    const { ok, data, redirect } = await apiFetch('/admin/settings/security/members/create', { method:'POST', body:new FormData(e.target) });
    spinner.classList.add('hidden'); btn.disabled = false;
    if (redirect) { window.location.href = '/login'; return; }
    if (ok && data.success) { closeModal('createAdminModal'); toast('Admin account created successfully.'); loadMembers(); if (teamLogLoaded) loadTeamActivity(); }
    else { errEl.textContent = data.message || 'Failed to create admin.'; errEl.classList.remove('hidden'); }
  });

  let initialMemberEditFormState = null;

  function normalizeMemberEditValue(v) {
    return String(v || '').trim();
  }

  function captureMemberEditFormState() {
    return {
      first_name: normalizeMemberEditValue(document.getElementById('memberEditFirstName')?.value),
      middle_name: normalizeMemberEditValue(document.getElementById('memberEditMiddleName')?.value),
      last_name: normalizeMemberEditValue(document.getElementById('memberEditLastName')?.value),
      email: normalizeMemberEditValue(document.getElementById('memberEditEmail')?.value),
      username: normalizeMemberEditValue(document.getElementById('memberEditUsername')?.value),
    };
  }

  function clearMemberEditErrors() {
    const alertEl = document.getElementById('memberEditErrorAlert');
    const listEl = document.getElementById('memberEditErrorList');
    if (listEl) listEl.innerHTML = '';
    if (alertEl) alertEl.classList.add('hidden');
  }

  function showMemberEditErrors(errors) {
    const alertEl = document.getElementById('memberEditErrorAlert');
    const listEl = document.getElementById('memberEditErrorList');
    if (!alertEl || !listEl) return;

    const validErrors = (errors || []).filter(Boolean);
    if (!validErrors.length) return;

    listEl.innerHTML = validErrors.map(function(msg) {
      return '<li>' + esc(msg) + '</li>';
    }).join('');

    alertEl.classList.remove('hidden');
  }

  function hasMemberEditFormChanged() {
    if (!initialMemberEditFormState) return true;

    const currentState = captureMemberEditFormState();
    const passwordInput = document.getElementById('memberEditPassword');
    const hasNewPassword = normalizeMemberEditValue(passwordInput?.value).length > 0;

    if (hasNewPassword) return true;

    return Object.keys(initialMemberEditFormState).some(function(key) {
      return currentState[key] !== initialMemberEditFormState[key];
    });
  }

  const closeMemberEditErrorAlertBtn = document.getElementById('closeMemberEditErrorAlert');
  if (closeMemberEditErrorAlertBtn) {
    closeMemberEditErrorAlertBtn.addEventListener('click', clearMemberEditErrors);
  }

  ['memberEditFirstName', 'memberEditMiddleName', 'memberEditLastName', 'memberEditEmail', 'memberEditUsername', 'memberEditPassword'].forEach(function(id) {
    const field = document.getElementById(id);
    if (!field) return;
    field.addEventListener('input', clearMemberEditErrors);
  });

  async function openMemberModal(adminId) {
    initialMemberEditFormState = null;
    clearMemberEditErrors();
    document.getElementById('memberLogsBody').innerHTML = '<tr><td colspan="4" class="px-4 py-6 text-center text-gray-300 text-xs">Loading...</td></tr>';
    openModal('memberModal');
    const { ok, data, redirect } = await apiFetch('/admin/settings/security/members/' + encodeURIComponent(adminId) + '/data');
    if (redirect) { window.location.href = '/login'; return; }
    if (!ok || !data.success) { document.getElementById('memberLogsBody').innerHTML = '<tr><td colspan="4" class="px-4 py-6 text-center text-red-400 text-xs">' + esc(data.message) + '</td></tr>'; return; }
    const a = data.data.admin;
    document.getElementById('memberModalTitle').textContent = ((a.first_name || '') + ' ' + (a.last_name || '')).trim() || 'Admin Details';
    document.getElementById('memberModalAvatar').textContent = initials(a.first_name, a.last_name);
    document.getElementById('memberModalName').textContent   = ((a.first_name || '') + ' ' + (a.last_name || '')).trim() || '-';
    document.getElementById('memberModalEmail').textContent  = a.email || '-';
    document.getElementById('memberModalJoined').textContent = a.created_at ? 'Joined ' + formatDate(a.created_at) : '';
    document.getElementById('memberEditId').value         = a.admin_id;
    document.getElementById('memberEditFirstName').value  = a.first_name  || '';
    document.getElementById('memberEditMiddleName').value = a.middle_name || '';
    document.getElementById('memberEditLastName').value   = a.last_name   || '';
    document.getElementById('memberEditEmail').value      = a.email       || '';
    document.getElementById('memberEditUsername').value   = a.username    || '';
    const memberEditPasswordInput = document.getElementById('memberEditPassword');
    if (memberEditPasswordInput) memberEditPasswordInput.value = '';
    initialMemberEditFormState = captureMemberEditFormState();
    const logs = data.data.logs;
    const logsBody = document.getElementById('memberLogsBody');
    if (!logs || !logs.length) { logsBody.innerHTML = '<tr><td colspan="4" class="px-4 py-6 text-center text-gray-300 text-xs">No activity yet.</td></tr>'; }
    else { logsBody.innerHTML = logs.map(function(log) { return '<tr class="border-t border-blue-50 hover:bg-blue-50/50"><td class="px-4 py-2.5">' + badgeFor(log.action) + '</td><td class="px-4 py-2.5 text-slate-500 text-xs">' + prettyDetails(log.details) + '</td><td class="px-4 py-2.5 font-mono text-xs text-slate-400">' + esc(log.ip_address ?? '-') + '</td><td class="px-4 py-2.5 text-xs text-slate-400">' + formatDate(log.created_at) + '</td></tr>'; }).join(''); }
  }

  document.getElementById('memberEditForm').addEventListener('submit', async e => {
    e.preventDefault();
    const spinner = document.getElementById('memberEditSpinner'); const btn = document.getElementById('memberEditSaveBtn');
    clearMemberEditErrors();

    if (!hasMemberEditFormChanged()) {
      showMemberEditErrors(['No changes detected. Please modify at least one field before saving.']);
      return;
    }

    const targetId = document.getElementById('memberEditId').value;
    spinner.classList.remove('hidden'); btn.disabled = true;
    const { ok, data, redirect } = await apiFetch('/admin/settings/security/members/' + encodeURIComponent(targetId) + '/update', { method:'POST', body:new FormData(e.target) });
    spinner.classList.add('hidden'); btn.disabled = false;
    if (redirect) { window.location.href = '/login'; return; }
    if (ok && data.success) { toast('Admin account updated.'); closeModal('memberModal'); loadMembers(); if (teamLogLoaded) loadTeamActivity(); }
    else { showMemberEditErrors([data.message || 'Update failed.']); }
  });

  let _deactivateTargetId = null;

  async function deleteMember(adminId, name) {
    _deactivateTargetId = adminId;
    document.getElementById('deactivateMemberName').textContent = name;
    document.getElementById('deactivateMemberError').classList.add('hidden');
    document.getElementById('deactivateMemberSpinner').classList.add('hidden');
    document.getElementById('confirmDeactivateMemberBtn').disabled = false;
    openModal('deactivateMemberModal');
  }

  document.getElementById('confirmDeactivateMemberBtn').addEventListener('click', async () => {
    const errEl   = document.getElementById('deactivateMemberError');
    const spinner = document.getElementById('deactivateMemberSpinner');
    const btn     = document.getElementById('confirmDeactivateMemberBtn');
    errEl.classList.add('hidden');
    spinner.classList.remove('hidden');
    btn.disabled = true;

    const { ok, data, redirect } = await apiFetch('/admin/settings/security/members/' + encodeURIComponent(_deactivateTargetId) + '/delete', { method: 'POST' });

    spinner.classList.add('hidden');
    btn.disabled = false;

    if (redirect) { window.location.href = '/login'; return; }
    if (ok && data.success) {
      closeModal('deactivateMemberModal');
      toast('Admin account deactivated.', 'warn');
      loadMembers();
      if (teamLogLoaded) loadTeamActivity();
    } else {
      errEl.textContent = data.message || 'Could not deactivate.';
      errEl.classList.remove('hidden');
    }
  });

  let _reactivateTargetId = null;

  async function reactivateMember(adminId, name) {
    _reactivateTargetId = adminId;
    document.getElementById('reactivateMemberName').textContent = name;
    document.getElementById('reactivateMemberError').classList.add('hidden');
    document.getElementById('reactivateMemberSpinner').classList.add('hidden');
    document.getElementById('confirmReactivateMemberBtn').disabled = false;
    openModal('reactivateMemberModal');
  }

  document.getElementById('confirmReactivateMemberBtn').addEventListener('click', async () => {
    const errEl   = document.getElementById('reactivateMemberError');
    const spinner = document.getElementById('reactivateMemberSpinner');
    const btn     = document.getElementById('confirmReactivateMemberBtn');
    errEl.classList.add('hidden');
    spinner.classList.remove('hidden');
    btn.disabled = true;

    const { ok, data, redirect } = await apiFetch('/admin/settings/security/members/' + encodeURIComponent(_reactivateTargetId) + '/reactivate', { method: 'POST' });

    spinner.classList.add('hidden');
    btn.disabled = false;

    if (redirect) { window.location.href = '/login'; return; }
    if (ok && data.success) {
      closeModal('reactivateMemberModal');
      toast('Admin account reactivated.', 'success');
      loadMembers();
      if (teamLogLoaded) loadTeamActivity();
    } else {
      errEl.textContent = data.message || 'Could not reactivate.';
      errEl.classList.remove('hidden');
    }
  });

  let _hardDeleteTargetId = null;

  function hardDeleteMember(adminId, name) {
    _hardDeleteTargetId = adminId;
    document.getElementById('deleteMemberName').textContent = name;
    document.getElementById('deleteMemberConfirmInput').value = '';
    document.getElementById('confirmDeleteMemberBtn').disabled = true;
    document.getElementById('deleteMemberError').classList.add('hidden');
    document.getElementById('deleteMemberSpinner').classList.add('hidden');
    openModal('deleteMemberModal');
  }

  document.getElementById('deleteMemberConfirmInput').addEventListener('input', function () {
    document.getElementById('confirmDeleteMemberBtn').disabled = this.value !== 'Confirm Delete';
  });

  document.getElementById('confirmDeleteMemberBtn').addEventListener('click', async () => {
    const errEl   = document.getElementById('deleteMemberError');
    const spinner = document.getElementById('deleteMemberSpinner');
    const btn     = document.getElementById('confirmDeleteMemberBtn');
    errEl.classList.add('hidden');
    spinner.classList.remove('hidden');
    btn.disabled = true;

    const { ok, data, redirect } = await apiFetch('/admin/settings/security/members/' + encodeURIComponent(_hardDeleteTargetId) + '/hard-delete', { method: 'POST' });

    spinner.classList.add('hidden');
    btn.disabled = false;

    if (redirect) { window.location.href = '/login'; return; }
    if (ok && data.success) {
      closeModal('deleteMemberModal');
      toast('Admin account deleted.', 'warn');
      loadMembers();
      if (teamLogLoaded) loadTeamActivity();
    } else {
      errEl.textContent = data.message || 'Could not delete account.';
      errEl.classList.remove('hidden');
    }
  });

  // =========================================================
  // TAB 3: GLOBAL TEAM ACTIVITY TRACKING
  // Master audit trail showing ALL admin_activity_logs system-wide,
  // joined with admin_users to display Admin Name.
  // Columns: Admin Name, Action, Details, IP Address, Date & Time.
  // =========================================================
  let allTeamLogs = [];
  let teamLogsPage = 1;
  async function loadTeamActivity() {
    const teamCountEl = document.getElementById('teamLogCount');
    const tbody = document.getElementById('teamActivityBody');
    teamCountEl.textContent = 'loading...';
    tbody.innerHTML = '<tr><td colspan="5" class="px-4 py-8 text-center text-gray-400 text-sm">Loading...</td></tr>';
    const { ok, data, redirect } = await apiFetch('/admin/settings/security/team-activity');
    if (redirect) { window.location.href = '/login'; return; }
    if (!ok || !data.success) { tbody.innerHTML = '<tr><td colspan="5" class="px-4 py-8 text-center text-red-500 text-sm">' + esc(data.message) + '</td></tr>'; teamCountEl.textContent = 'error'; return; }
    allTeamLogs = Array.isArray(data.data.logs) ? data.data.logs : [];
    teamLogsPage = 1;
    populateActionFilter(allTeamLogs, 'teamLogActionFilter');
    applyTeamLogFilter();
  }
  function applyTeamLogFilter() {
    const from = document.getElementById('teamLogDateFrom').value;
    const to   = document.getElementById('teamLogDateTo').value;
    const action = document.getElementById('teamLogActionFilter').value;
    const term = (document.getElementById('teamLogSearch').value || '').trim().toLowerCase();
    let filtered = allTeamLogs;
    if (from) filtered = filtered.filter(l => l.created_at && l.created_at.slice(0,10) >= from);
    if (to)   filtered = filtered.filter(l => l.created_at && l.created_at.slice(0,10) <= to);
    if (action) filtered = filtered.filter(l => (l.action || '') === action);
    if (term) {
      filtered = filtered.filter(l => {
        const detailText = (() => { try { return JSON.stringify(JSON.parse(l.details || '{}')); } catch { return String(l.details || ''); } })();
        const haystack = [l.admin_name || '', l.admin_id || '', l.action || '', detailText, l.ip_address || '', l.created_at || ''].join(' ').toLowerCase();
        return haystack.includes(term);
      });
    }
    const teamCountEl = document.getElementById('teamLogCount');
    const tbody = document.getElementById('teamActivityBody');
    const pageMeta = document.getElementById('teamLogPageMeta');
    const prevBtn = document.getElementById('teamLogPrevBtn');
    const nextBtn = document.getElementById('teamLogNextBtn');
    teamCountEl.textContent = filtered.length ? filtered.length + ' records' : '0 records';
    const totalPages = Math.max(1, Math.ceil(filtered.length / LOGS_PER_PAGE));
    if (teamLogsPage > totalPages) teamLogsPage = totalPages;
    const startIdx = (teamLogsPage - 1) * LOGS_PER_PAGE;
    const pageRows = filtered.slice(startIdx, startIdx + LOGS_PER_PAGE);
    prevBtn.disabled = teamLogsPage <= 1;
    nextBtn.disabled = teamLogsPage >= totalPages;
    if (!filtered.length) {
      tbody.innerHTML = '<tr><td colspan="5" class="px-4 py-8 text-center text-gray-400 text-sm">' + (allTeamLogs.length ? 'No logs match the selected filters.' : 'No team activity recorded yet.') + '</td></tr>';
      pageMeta.textContent = 'Showing 0-0 of 0';
      return;
    }
    tbody.innerHTML = pageRows.map(function(log) {
      return '<tr class="team-log-row">'
        + '<td class="px-4 py-3 font-medium text-slate-700 text-xs">' + esc((log.admin_name || '').trim() || log.admin_id) + '</td>'
        + '<td class="px-4 py-3">' + badgeFor(log.action) + '</td>'
        + '<td class="px-4 py-3 text-slate-500 text-xs max-w-xs">' + prettyDetails(log.details) + '</td>'
        + '<td class="px-4 py-3 font-mono text-xs text-slate-400">' + esc(log.ip_address ?? '-') + '</td>'
        + '<td class="px-4 py-3 text-xs text-slate-400">' + formatDate(log.created_at) + '</td>'
        + '</tr>';
    }).join('');
    pageMeta.textContent = 'Showing ' + (startIdx + 1) + '-' + (startIdx + pageRows.length) + ' of ' + filtered.length;
  }

  // --- Date filter listeners ---
  document.getElementById('myLogDateFrom').addEventListener('change', applyMyLogFilter);
  document.getElementById('myLogDateTo').addEventListener('change', applyMyLogFilter);
  document.getElementById('myLogActionFilter').addEventListener('change', () => { myLogsPage = 1; applyMyLogFilter(); });
  document.getElementById('myLogSearch').addEventListener('input', () => { myLogsPage = 1; applyMyLogFilter(); });
  document.getElementById('myLogPrevBtn').addEventListener('click', () => { if (myLogsPage > 1) { myLogsPage--; applyMyLogFilter(); } });
  document.getElementById('myLogNextBtn').addEventListener('click', () => { myLogsPage++; applyMyLogFilter(); });
  document.getElementById('myLogResetBtn').addEventListener('click', () => {
    document.getElementById('myLogDateFrom').value = '';
    document.getElementById('myLogDateTo').value   = '';
    document.getElementById('myLogActionFilter').value = '';
    document.getElementById('myLogSearch').value = '';
    myLogsPage = 1;
    applyMyLogFilter();
  });

  document.getElementById('teamLogDateFrom').addEventListener('change', applyTeamLogFilter);
  document.getElementById('teamLogDateTo').addEventListener('change', applyTeamLogFilter);
  document.getElementById('teamLogActionFilter').addEventListener('change', () => { teamLogsPage = 1; applyTeamLogFilter(); });
  document.getElementById('teamLogSearch').addEventListener('input', () => { teamLogsPage = 1; applyTeamLogFilter(); });
  document.getElementById('teamLogPrevBtn').addEventListener('click', () => { if (teamLogsPage > 1) { teamLogsPage--; applyTeamLogFilter(); } });
  document.getElementById('teamLogNextBtn').addEventListener('click', () => { teamLogsPage++; applyTeamLogFilter(); });
  document.getElementById('teamLogResetBtn').addEventListener('click', () => {
    document.getElementById('teamLogDateFrom').value = '';
    document.getElementById('teamLogDateTo').value   = '';
    document.getElementById('teamLogActionFilter').value = '';
    document.getElementById('teamLogSearch').value = '';
    teamLogsPage = 1;
    applyTeamLogFilter();
  });

  document.getElementById('refreshTeamLogBtn').addEventListener('click', loadTeamActivity);

  // Boot
  loadData();
});