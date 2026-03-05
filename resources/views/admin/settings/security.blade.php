<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <title>Security Settings – Legatura</title>

  <script src="https://cdn.tailwindcss.com"></script>
  <link rel="stylesheet" href="{{ asset('css/admin/home/mainComponents.css') }}">
  <link rel='stylesheet' href='https://cdn-uicons.flaticon.com/3.0.0/uicons-solid-straight/css/uicons-solid-straight.css'>
  <link rel='stylesheet' href='https://cdn-uicons.flaticon.com/3.0.0/uicons-solid-rounded/css/uicons-solid-rounded.css'>
  <link rel='stylesheet' href='https://cdn-uicons.flaticon.com/3.0.0/uicons-bold-rounded/css/uicons-bold-rounded.css'>
  <link rel='stylesheet' href='https://cdn-uicons.flaticon.com/3.0.0/uicons-regular-rounded/css/uicons-regular-rounded.css'>
  <script src="{{ asset('js/admin/home/mainComponents.js') }}" defer></script>

  <style>
    #toast {
      position: fixed; bottom: 1.5rem; right: 1.5rem;
      padding: .75rem 1.25rem; border-radius: .75rem;
      font-size: .875rem; font-weight: 500;
      box-shadow: 0 4px 20px rgba(0,0,0,.15);
      z-index: 9999; opacity: 0; transform: translateY(8px);
      transition: opacity .3s, transform .3s; pointer-events: none;
    }
    #toast.show   { opacity: 1; transform: translateY(0); }
    #toast.success{ background:#dcfce7; color:#166534; border:1px solid #bbf7d0; }
    #toast.error  { background:#fee2e2; color:#991b1b; border:1px solid #fecaca; }
    #toast.warn   { background:#fef9c3; color:#713f12; border:1px solid #fde68a; }

    #editModal { display:none; position:fixed; inset:0; background:rgba(0,0,0,.5); z-index:1000; align-items:center; justify-content:center; }
    #editModal.open { display:flex; }

    .action-badge { display:inline-flex; align-items:center; gap:.35rem; padding:.25rem .7rem; border-radius:9999px; font-size:.72rem; font-weight:600; text-transform:uppercase; letter-spacing:.04em; }
    .badge-profile  { background:#e0e7ff; color:#4338ca; }
    .badge-password { background:#fef3c7; color:#92400e; }
    .badge-deleted  { background:#fee2e2; color:#991b1b; }
    .badge-default  { background:#f3f4f6; color:#374151; }

    .field-label { display:block; font-size:.7rem; font-weight:600; text-transform:uppercase; letter-spacing:.05em; color:#9ca3af; margin-bottom:.25rem; }
    .field-value { background:#f9fafb; border:1px solid #e5e7eb; border-radius:.5rem; padding:.5rem 1rem; color:#374151; font-size:.875rem; min-height:2.5rem; }
  </style>
</head>

<body class="bg-gray-50 text-gray-800 font-sans">
<div class="flex min-h-screen">

    @include('admin.layouts.sidebar')

  {{-- ── MAIN ── --}}
  <main class="flex-1 overflow-y-auto">

      @include('admin.layouts.topnav', ['pageTitle' => 'Security Settings'])

    <section class="px-8 py-8 space-y-8">

      {{-- ── ERROR BANNER ── --}}
      <div id="globalError" class="hidden bg-red-50 border border-red-200 text-red-700 text-sm rounded-xl px-5 py-4 flex items-start gap-3">
        <i class="fi fi-ss-exclamation mt-0.5 flex-shrink-0"></i>
        <div>
          <p class="font-semibold">Failed to load account data</p>
          <p id="globalErrorMsg" class="text-xs text-red-600 mt-1 font-mono"></p>
        </div>
      </div>

      {{-- ── ACCOUNT INFORMATION ── --}}
      <div class="bg-white rounded-2xl shadow border p-6">
        <div class="flex items-center justify-between mb-6">
          <h2 class="text-lg font-semibold">Account Information</h2>
          <button id="openEditBtn"
                  class="inline-flex items-center gap-2 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium px-5 py-2 rounded-lg transition">
            <i class="fi fi-sr-pencil" style="font-size:13px"></i> Edit Profile
          </button>
        </div>

        {{-- Skeleton --}}
        <div id="profileSkeleton" class="flex gap-6 items-start animate-pulse">
          <div class="w-28 h-28 rounded-full bg-gray-200 flex-shrink-0"></div>
          <div class="flex-1 grid grid-cols-3 gap-4 mt-2">
            <div class="h-10 bg-gray-200 rounded-lg"></div>
            <div class="h-10 bg-gray-200 rounded-lg"></div>
            <div class="h-10 bg-gray-200 rounded-lg"></div>
            <div class="h-10 bg-gray-200 rounded-lg col-span-1"></div>
            <div class="h-10 bg-gray-200 rounded-lg col-span-1"></div>
          </div>
        </div>

        {{-- Real content --}}
        <div id="profileDisplay" class="hidden flex gap-6 items-start">
          <div class="flex-shrink-0 text-center">
            <div class="relative inline-block">
              <img id="profileAvatar" src=""
                   class="w-28 h-28 rounded-full object-cover shadow border-2 border-indigo-100" alt="Avatar">
            </div>
            <p id="profileMemberSince" class="text-xs text-gray-400 mt-2"></p>
          </div>
          <div class="grid grid-cols-1 md:grid-cols-3 gap-4 flex-1">
            <div>
              <span class="field-label">First Name</span>
              <div id="dispFirstName" class="field-value">–</div>
            </div>
            <div>
              <span class="field-label">Middle Name</span>
              <div id="dispMiddleName" class="field-value">–</div>
            </div>
            <div>
              <span class="field-label">Last Name</span>
              <div id="dispLastName" class="field-value">–</div>
            </div>
            <div>
              <span class="field-label">Email</span>
              <div id="dispEmail" class="field-value">–</div>
            </div>
            <div>
              <span class="field-label">Username</span>
              <div id="dispUsername" class="field-value">–</div>
            </div>
          </div>
        </div>
      </div>

      {{-- ── CHANGE PASSWORD ── --}}
      <div class="bg-white rounded-2xl shadow border p-6">
        <h2 class="text-lg font-semibold mb-6">Change Password</h2>
        <form id="passwordForm" class="space-y-4">
          @csrf
          <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div>
              <label class="field-label">Current Password</label>
              <input id="currentPassword" name="current_password" type="password"
                     placeholder="Enter current password"
                     class="border rounded-lg px-4 py-2 w-full focus:ring-2 focus:ring-indigo-400 focus:outline-none text-sm" required>
            </div>
            <div>
              <label class="field-label">New Password</label>
              <input id="newPassword" name="new_password" type="password"
                     placeholder="Min. 8 characters"
                     class="border rounded-lg px-4 py-2 w-full focus:ring-2 focus:ring-indigo-400 focus:outline-none text-sm" required>
              <div id="pwStrengthBar" class="hidden mt-1.5 h-1.5 rounded-full bg-gray-200 overflow-hidden">
                <div id="pwStrengthFill" class="h-full rounded-full transition-all duration-300 w-0"></div>
              </div>
              <p id="pwStrengthLabel" class="hidden text-xs mt-1 text-gray-400"></p>
            </div>
            <div>
              <label class="field-label">Confirm Password</label>
              <input id="confirmPassword" name="new_password_confirmation" type="password"
                     placeholder="Re-enter new password"
                     class="border rounded-lg px-4 py-2 w-full focus:ring-2 focus:ring-indigo-400 focus:outline-none text-sm" required>
              <p id="pwMatchMsg" class="hidden text-xs text-red-500 mt-1">Passwords do not match.</p>
            </div>
          </div>
          <div id="pwError" class="hidden text-sm text-red-600 bg-red-50 border border-red-200 rounded-lg px-4 py-2"></div>
          <button type="submit"
                  class="bg-rose-600 hover:bg-rose-700 text-white text-sm font-medium px-6 py-2 rounded-lg transition flex items-center gap-2">
            <span>Update Password</span>
            <svg id="pwSpinner" class="hidden animate-spin h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
              <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
              <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"/>
            </svg>
          </button>
        </form>
      </div>

      {{-- ── ACTIVITY LOGS ── --}}
      <div class="bg-white rounded-2xl shadow border p-6">
        <div class="flex items-center justify-between mb-5">
          <h2 class="text-lg font-semibold">Activity Logs</h2>
          <span id="logCount" class="text-xs text-gray-400 bg-gray-100 px-3 py-1 rounded-full">loading…</span>
        </div>
        <div class="overflow-x-auto rounded-xl border border-gray-100">
          <table class="w-full text-sm">
            <thead class="bg-gray-50 text-gray-500 text-xs uppercase tracking-wide">
              <tr>
                <th class="px-5 py-3 text-left font-semibold">Action</th>
                <th class="px-5 py-3 text-left font-semibold">Details</th>
                <th class="px-5 py-3 text-left font-semibold">IP Address</th>
                <th class="px-5 py-3 text-left font-semibold">Date & Time</th>
              </tr>
            </thead>
            <tbody id="activityTableBody">
              <tr>
                <td colspan="4" class="px-5 py-8 text-center">
                  <div class="flex justify-center items-center gap-2 text-gray-400">
                    <svg class="animate-spin h-4 w-4 text-indigo-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                      <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                      <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"/>
                    </svg>
                    Loading logs…
                  </div>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>

      {{-- ── DANGER ZONE ── --}}
      <div class="bg-white rounded-2xl shadow border border-red-100 p-6">
        <h2 class="text-base font-semibold text-red-600 mb-1">Danger Zone</h2>
        <p class="text-sm text-gray-400 mb-4">Deleting your account will deactivate it permanently.</p>
        <button id="deleteAccountBtn"
                class="inline-flex items-center gap-2 bg-red-600 hover:bg-red-700 text-white text-sm font-medium px-6 py-2 rounded-lg transition">
          <i class="fi fi-ss-trash" style="font-size:13px"></i> Delete Account
        </button>
      </div>

    </section>
  </main>
</div>

{{-- ── EDIT PROFILE MODAL ── --}}
<div id="editModal">
  <div class="bg-white rounded-2xl shadow-2xl w-full max-w-lg mx-4 overflow-hidden">
    <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100">
      <h3 class="text-base font-semibold">Edit Profile</h3>
      <button id="closeEditBtn" class="w-8 h-8 flex items-center justify-center rounded-full hover:bg-gray-100 text-gray-400 text-xl transition">&times;</button>
    </div>

    <form id="profileForm" enctype="multipart/form-data" class="px-6 py-5 space-y-4">
      @csrf

      {{-- Avatar --}}
      <div class="flex items-center gap-4">
        <div class="relative">
          <img id="modalAvatar" src=""
               class="w-20 h-20 rounded-full object-cover border-2 border-indigo-200 shadow" alt="">
          <label for="avatarInput"
                 class="absolute -bottom-1 -right-1 w-7 h-7 bg-indigo-600 hover:bg-indigo-700 rounded-full flex items-center justify-center cursor-pointer shadow transition">
            <i class="fi fi-ss-camera text-white" style="font-size:11px"></i>
          </label>
          <input id="avatarInput" name="avatar" type="file" accept="image/*" class="hidden">
        </div>
        <div>
          <p class="text-xs text-gray-500 font-medium">Change profile picture</p>
          <p class="text-xs text-gray-400">JPG, PNG up to 2 MB</p>
        </div>
      </div>

      {{-- Names --}}
      <div class="grid grid-cols-3 gap-3">
        <div>
          <label class="field-label">First Name <span class="text-red-500">*</span></label>
          <input id="editFirstName" name="first_name" type="text"
                 class="border rounded-lg px-3 py-2 w-full focus:ring-2 focus:ring-indigo-400 focus:outline-none text-sm" required>
        </div>
        <div>
          <label class="field-label">Middle Name</label>
          <input id="editMiddleName" name="middle_name" type="text"
                 class="border rounded-lg px-3 py-2 w-full focus:ring-2 focus:ring-indigo-400 focus:outline-none text-sm">
        </div>
        <div>
          <label class="field-label">Last Name <span class="text-red-500">*</span></label>
          <input id="editLastName" name="last_name" type="text"
                 class="border rounded-lg px-3 py-2 w-full focus:ring-2 focus:ring-indigo-400 focus:outline-none text-sm" required>
        </div>
      </div>

      {{-- Email / Username --}}
      <div class="grid grid-cols-2 gap-3">
        <div>
          <label class="field-label">Email <span class="text-red-500">*</span></label>
          <input id="editEmail" name="email" type="email"
                 class="border rounded-lg px-3 py-2 w-full focus:ring-2 focus:ring-indigo-400 focus:outline-none text-sm" required>
        </div>
        <div>
          <label class="field-label">Username <span class="text-red-500">*</span></label>
          <input id="editUsername" name="username" type="text"
                 class="border rounded-lg px-3 py-2 w-full focus:ring-2 focus:ring-indigo-400 focus:outline-none text-sm" required>
        </div>
      </div>

      <div id="profileError" class="hidden text-sm text-red-700 bg-red-50 border border-red-200 rounded-lg px-4 py-2"></div>

      <div class="flex justify-end gap-3 pt-1">
        <button type="button" id="cancelEditBtn"
                class="px-5 py-2 text-sm rounded-lg border border-gray-200 hover:bg-gray-50 transition text-gray-600">
          Cancel
        </button>
        <button type="submit" id="saveProfileBtn"
                class="px-6 py-2 text-sm bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg transition flex items-center gap-2">
          <span>Save Changes</span>
          <svg id="savingSpinner" class="hidden animate-spin h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"/>
          </svg>
        </button>
      </div>
    </form>
  </div>
</div>

<div id="toast"></div>

<script>
document.addEventListener('DOMContentLoaded', () => {

  // ── CSRF token (must exist in <head> meta tag) ──
  const csrfMeta = document.querySelector('meta[name="csrf-token"]');
  const csrf = csrfMeta ? csrfMeta.content : '';

  // ── Avatar helper — never loads /img/default-avatar.png ──────
  // Shows a real <img> when a storage path exists, otherwise replaces
  // the element with an initials-based coloured circle so no HTTP
  // request is made for a file that may not exist.
  function setAvatar(imgId, picPath, firstName, lastName, sizeClasses = 'w-16 h-16 text-xl') {
    const img = document.getElementById(imgId);
    if (!img) return;

    if (picPath) {
      img.onload = () => {
        img.classList.remove('hidden');
        // Hide sibling initials placeholder if any
        const sib = img.parentElement?.querySelector('[data-initials]');
        if (sib) sib.classList.add('hidden');
      };
      img.onerror = () => {
        // Storage file missing — swap to initials without any further requests
        img.classList.add('hidden');
        showInitialsPlaceholder(img, firstName, lastName, sizeClasses);
      };
      img.src = '/storage/' + picPath;
    } else {
      img.classList.add('hidden');
      showInitialsPlaceholder(img, firstName, lastName, sizeClasses);
    }
  }

  function showInitialsPlaceholder(img, firstName, lastName, sizeClasses) {
    // Re-use existing placeholder if already injected
    let placeholder = img.parentElement?.querySelector('[data-initials]');
    if (!placeholder) {
      placeholder = document.createElement('div');
      placeholder.setAttribute('data-initials', '1');
      placeholder.className = `${sizeClasses} rounded-full bg-indigo-600 text-white font-bold flex items-center justify-center flex-shrink-0`;
      img.parentElement?.insertBefore(placeholder, img);
    }
    placeholder.textContent = initials(firstName, lastName);
    placeholder.classList.remove('hidden');
  }

  // ── Toast ──────────────────────────────────────
  function toast(msg, type = 'success', duration = 3500) {
    const el = document.getElementById('toast');
    el.textContent = msg;
    el.className = `show ${type}`;
    setTimeout(() => { el.className = ''; }, duration);
  }

  // ── Safe fetch wrapper – returns {ok, status, data} ──
  async function apiFetch(url, options = {}) {
    const defaults = {
      headers: {
        'X-CSRF-TOKEN': csrf,
        'X-Requested-With': 'XMLHttpRequest',
        'Accept': 'application/json',
        ...(options.headers || {}),
      },
    };
    // Don't set Content-Type for FormData – browser sets it with boundary
    if (options.body instanceof FormData) {
      delete defaults.headers['Content-Type'];
    }
    try {
      const res = await fetch(url, { ...options, ...defaults });
      let data;
      const contentType = res.headers.get('content-type') || '';
      if (contentType.includes('application/json')) {
        data = await res.json();
      } else {
        // Got HTML (likely a redirect to login page or a Laravel error page)
        const text = await res.text();
        if (res.status === 302 || text.includes('<form') || text.includes('login')) {
          return { ok: false, status: res.status, data: { success: false, message: 'Session expired. Please log in again.' }, redirect: true };
        }
        return { ok: false, status: res.status, data: { success: false, message: `Server returned non-JSON (${res.status}). Check console.` } };
      }
      return { ok: res.ok, status: res.status, data };
    } catch (err) {
      return { ok: false, status: 0, data: { success: false, message: 'Network error: ' + err.message } };
    }
  }

  // ── Helpers ────────────────────────────────────
  function esc(str) {
    return String(str ?? '').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;');
  }

  function formatDate(str) {
    if (!str) return '–';
    try {
      return new Date(str).toLocaleString('en-PH', { year:'numeric', month:'short', day:'numeric', hour:'2-digit', minute:'2-digit' });
    } catch { return str; }
  }

  function initials(first, last) {
    return ((first?.[0] ?? '') + (last?.[0] ?? '')).toUpperCase() || '?';
  }

  function badgeFor(action) {
    const map = {
      profile_updated:  ['badge-profile',  '👤 Profile Updated'],
      password_changed: ['badge-password', '🔑 Password Changed'],
      account_deleted:  ['badge-deleted',  '🗑 Account Deleted'],
    };
    const [cls, label] = map[action] ?? ['badge-default', esc(action).replace(/_/g,' ')];
    return `<span class="action-badge ${cls}">${label}</span>`;
  }

  function prettyDetails(raw) {
    if (!raw) return '<span class="text-gray-300">–</span>';
    try {
      const obj = JSON.parse(raw);
      return Object.entries(obj)
        .map(([k, v]) => `<span class="font-medium text-gray-500">${esc(k)}:</span> ${esc(v)}`)
        .join(' &nbsp;·&nbsp; ');
    } catch { return esc(raw); }
  }

  // ── Load profile + logs ────────────────────────
  async function loadData() {
    const { ok, data, redirect } = await apiFetch('/admin/settings/security/data');

    if (redirect) { window.location.href = '/login'; return; }

    if (!ok || !data.success) {
      document.getElementById('globalError').classList.remove('hidden');
      document.getElementById('globalErrorMsg').textContent = data.message || 'Unknown error.';
      document.getElementById('profileSkeleton').classList.add('hidden');
      document.getElementById('logCount').textContent = 'error';
      document.getElementById('activityTableBody').innerHTML =
        `<tr><td colspan="4" class="px-5 py-6 text-center text-red-400 text-sm">${esc(data.message || 'Could not load logs.')}</td></tr>`;
      return;
    }

    const a = data.data.admin;

    // ── Sidebar ──
    const fullName = [a.first_name, a.last_name].filter(Boolean).join(' ');
    document.getElementById('sidebarName').textContent   = fullName || 'Admin';
    document.getElementById('sidebarEmail').textContent  = a.email  || '–';
    document.getElementById('dropdownName').textContent  = fullName || 'Admin';
    document.getElementById('dropdownEmail').textContent = a.email  || '–';
    if (a.profile_pic) {
      const img = document.getElementById('sidebarAvatarImg');
      img.onload = () => {
        document.getElementById('sidebarInitials').classList.add('hidden');
        img.classList.remove('hidden');
      };
      img.onerror = () => {
        // Storage file missing — fall back to initials silently
        img.classList.add('hidden');
        document.getElementById('sidebarInitials').textContent = initials(a.first_name, a.last_name);
        document.getElementById('sidebarInitials').classList.remove('hidden');
      };
      img.src = '/storage/' + a.profile_pic;
    } else {
      document.getElementById('sidebarInitials').textContent = initials(a.first_name, a.last_name);
    }

    // ── Profile card ──
    // Set avatars — use initials placeholder if no profile pic (avoids broken-image loop)
    setAvatar('profileAvatar', a.profile_pic, a.first_name, a.last_name, 'w-28 h-28 text-2xl');
    setAvatar('modalAvatar',   a.profile_pic, a.first_name, a.last_name, 'w-20 h-20 text-xl');

    document.getElementById('dispFirstName').textContent  = a.first_name  || '–';
    document.getElementById('dispMiddleName').textContent = a.middle_name || '–';
    document.getElementById('dispLastName').textContent   = a.last_name   || '–';
    document.getElementById('dispEmail').textContent      = a.email       || '–';
    document.getElementById('dispUsername').textContent   = a.username    || '–';

    if (a.created_at) {
      const d = new Date(a.created_at);
      document.getElementById('profileMemberSince').textContent =
        'Member since ' + d.toLocaleDateString('en-PH', { year:'numeric', month:'long', day:'numeric' });
    }

    // Pre-fill modal inputs
    document.getElementById('editFirstName').value  = a.first_name  || '';
    document.getElementById('editMiddleName').value = a.middle_name || '';
    document.getElementById('editLastName').value   = a.last_name   || '';
    document.getElementById('editEmail').value      = a.email       || '';
    document.getElementById('editUsername').value   = a.username    || '';

    // Show real content
    document.getElementById('profileSkeleton').classList.add('hidden');
    document.getElementById('profileDisplay').classList.remove('hidden');

    // ── Logs ──
    renderLogs(data.data.logs);
  }

  function renderLogs(logs) {
    const tbody = document.getElementById('activityTableBody');
    document.getElementById('logCount').textContent = logs.length ? logs.length + ' records' : '0 records';

    if (!logs.length) {
      tbody.innerHTML = `<tr><td colspan="4" class="px-5 py-10 text-center text-gray-400 text-sm">No activity recorded yet. Actions like editing your profile or changing your password will appear here.</td></tr>`;
      return;
    }

    tbody.innerHTML = logs.map(log => `
      <tr class="border-t border-gray-50 hover:bg-gray-50/60 transition">
        <td class="px-5 py-3">${badgeFor(log.action)}</td>
        <td class="px-5 py-3 text-gray-500 text-xs max-w-xs">${prettyDetails(log.details)}</td>
        <td class="px-5 py-3 font-mono text-xs text-gray-400">${esc(log.ip_address ?? '–')}</td>
        <td class="px-5 py-3 text-xs text-gray-400">${formatDate(log.created_at)}</td>
      </tr>
    `).join('');
  }

  // ── Modal open/close ───────────────────────────
  const modal = document.getElementById('editModal');

  document.getElementById('openEditBtn').addEventListener('click', () => {
    document.getElementById('profileError').classList.add('hidden');
    modal.classList.add('open');
  });
  ['closeEditBtn','cancelEditBtn'].forEach(id =>
    document.getElementById(id)?.addEventListener('click', () => modal.classList.remove('open'))
  );
  modal.addEventListener('click', e => { if (e.target === modal) modal.classList.remove('open'); });

  // ── Avatar preview ─────────────────────────────
  document.getElementById('avatarInput').addEventListener('change', e => {
    const file = e.target.files[0];
    if (!file) return;
    const reader = new FileReader();
    reader.onload = ev => {
      const src = ev.target.result;
      const modal   = document.getElementById('modalAvatar');
      const profile = document.getElementById('profileAvatar');
      // Show as <img> when user picks a file (it's a valid data URL)
      modal.classList.remove('hidden');   profile.classList.remove('hidden');
      modal.src = src; profile.src = src;
      // Hide any initials placeholder siblings
      modal.previousElementSibling?.classList?.add('hidden');
    };
    reader.readAsDataURL(file);
  });

  // ── Profile form submit ────────────────────────
  document.getElementById('profileForm').addEventListener('submit', async e => {
    e.preventDefault();
    const errEl  = document.getElementById('profileError');
    const spinner= document.getElementById('savingSpinner');
    const saveBtn= document.getElementById('saveProfileBtn');
    errEl.classList.add('hidden');
    spinner.classList.remove('hidden');
    saveBtn.disabled = true;

    const { ok, data, redirect } = await apiFetch('/admin/settings/security/update', {
      method: 'POST',
      body: new FormData(e.target),
    });

    spinner.classList.add('hidden');
    saveBtn.disabled = false;

    if (redirect) { window.location.href = '/login'; return; }

    if (ok && data.success) {
      modal.classList.remove('open');
      toast('Profile updated successfully.');
      loadData();
    } else {
      errEl.textContent = data.message || 'Update failed. Please try again.';
      errEl.classList.remove('hidden');
    }
  });

  // ── Password strength ──────────────────────────
  document.getElementById('newPassword').addEventListener('input', e => {
    const v = e.target.value;
    const bar   = document.getElementById('pwStrengthBar');
    const fill  = document.getElementById('pwStrengthFill');
    const label = document.getElementById('pwStrengthLabel');
    if (!v) { bar.classList.add('hidden'); label.classList.add('hidden'); return; }
    bar.classList.remove('hidden'); label.classList.remove('hidden');
    const hasUpper  = /[A-Z]/.test(v);
    const hasNum    = /[0-9]/.test(v);
    const hasSymbol = /[^A-Za-z0-9]/.test(v);
    const score = (v.length >= 8 ? 1 : 0) + (hasUpper ? 1 : 0) + (hasNum ? 1 : 0) + (hasSymbol ? 1 : 0);
    if (score <= 1)      { fill.style.width='25%'; fill.className='h-full rounded-full transition-all duration-300 bg-red-400';    label.textContent='Weak'; label.className='text-xs mt-1 text-red-500'; }
    else if (score <= 2) { fill.style.width='55%'; fill.className='h-full rounded-full transition-all duration-300 bg-yellow-400'; label.textContent='Fair'; label.className='text-xs mt-1 text-yellow-500'; }
    else if (score === 3){ fill.style.width='75%'; fill.className='h-full rounded-full transition-all duration-300 bg-blue-400';   label.textContent='Good'; label.className='text-xs mt-1 text-blue-500'; }
    else                 { fill.style.width='100%';fill.className='h-full rounded-full transition-all duration-300 bg-green-500';  label.textContent='Strong'; label.className='text-xs mt-1 text-green-600'; }
  });

  document.getElementById('confirmPassword').addEventListener('input', () => {
    const match = document.getElementById('newPassword').value === document.getElementById('confirmPassword').value;
    document.getElementById('pwMatchMsg').classList.toggle('hidden', match || !document.getElementById('confirmPassword').value);
  });

  // ── Password form ──────────────────────────────
  document.getElementById('passwordForm').addEventListener('submit', async e => {
    e.preventDefault();
    const errEl  = document.getElementById('pwError');
    const spinner= document.getElementById('pwSpinner');
    const btn    = e.target.querySelector('button[type=submit]');
    errEl.classList.add('hidden');

    if (document.getElementById('newPassword').value !== document.getElementById('confirmPassword').value) {
      errEl.textContent = 'New password and confirmation do not match.';
      errEl.classList.remove('hidden');
      return;
    }

    spinner.classList.remove('hidden');
    btn.disabled = true;

    const { ok, data, redirect } = await apiFetch('/admin/settings/security/change-password', {
      method: 'POST',
      body: new FormData(e.target),
    });

    spinner.classList.add('hidden');
    btn.disabled = false;

    if (redirect) { window.location.href = '/login'; return; }

    if (ok && data.success) {
      toast('Password changed successfully.');
      e.target.reset();
      document.getElementById('pwStrengthBar').classList.add('hidden');
      document.getElementById('pwStrengthLabel').classList.add('hidden');
      loadData();
    } else {
      errEl.textContent = data.message || 'Failed to change password.';
      errEl.classList.remove('hidden');
    }
  });

  // ── Delete account ─────────────────────────────
  document.getElementById('deleteAccountBtn').addEventListener('click', async () => {
    if (!confirm('Are you sure? This will permanently deactivate your admin account.')) return;

    const { ok, data } = await apiFetch('/admin/settings/security/delete', { method: 'POST' });
    if (ok && data.success) {
      window.location.href = '/login';
    } else {
      toast(data.message || 'Could not delete account.', 'error');
    }
  });

  // ── Sidebar user menu ─────────────────────────
  const _menuBtn      = document.getElementById('secUserMenuBtn');
  const _menuDropdown = document.getElementById('secUserMenuDropdown');

  _menuBtn?.addEventListener('click', e => {
    e.stopPropagation();
    _menuDropdown.classList.toggle('hidden');
  });

  // Close when clicking OUTSIDE the dropdown — check target, not just any click
  document.addEventListener('click', e => {
    if (_menuDropdown && !_menuDropdown.classList.contains('hidden')) {
      if (!_menuDropdown.contains(e.target) && !_menuBtn.contains(e.target)) {
        _menuDropdown.classList.add('hidden');
      }
    }
  });

  // ── Logout ─────────────────────────────────────
  document.getElementById('secLogoutBtn')?.addEventListener('click', e => {
    e.stopPropagation();
    document.getElementById('secLogoutForm').submit();
  });

  // ── Boot ───────────────────────────────────────
  loadData();
});

</script>
</body>
</html>
