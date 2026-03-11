<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <title>Security Settings - Legatura</title>

  <script src="https://cdn.tailwindcss.com"></script>
  <link rel="stylesheet" href="{{ asset('css/admin/home/mainComponents.css') }}">
  <link rel="stylesheet" href="https://cdn-uicons.flaticon.com/3.0.0/uicons-solid-straight/css/uicons-solid-straight.css">
  <link rel="stylesheet" href="https://cdn-uicons.flaticon.com/3.0.0/uicons-solid-rounded/css/uicons-solid-rounded.css">
  <link rel="stylesheet" href="https://cdn-uicons.flaticon.com/3.0.0/uicons-bold-rounded/css/uicons-bold-rounded.css">
  <link rel="stylesheet" href="https://cdn-uicons.flaticon.com/3.0.0/uicons-regular-rounded/css/uicons-regular-rounded.css">
  <script src="{{ asset('js/admin/home/mainComponents.js') }}" defer></script>
  <script src="{{ asset('js/admin/settings/security.js') }}" defer></script>

  <style>
    #toast {
      position:fixed; bottom:1.5rem; right:1.5rem;
      padding:.75rem 1.25rem; border-radius:.75rem;
      font-size:.875rem; font-weight:500;
      box-shadow:0 4px 20px rgba(0,0,0,.15);
      z-index:9999; opacity:0; transform:translateY(8px);
      transition:opacity .3s, transform .3s; pointer-events:none;
    }
    #toast.show    { opacity:1; transform:translateY(0); }
    #toast.success { background:#dcfce7; color:#166534; border:1px solid #bbf7d0; }
    #toast.error   { background:#fee2e2; color:#991b1b; border:1px solid #fecaca; }
    #toast.warn    { background:#fef9c3; color:#713f12; border:1px solid #fde68a; }

    .modal-backdrop { display:none; position:fixed; inset:0; background:rgba(0,0,0,.5); z-index:1000; align-items:center; justify-content:center; }
    .modal-backdrop.open { display:flex; }

    .tab-btn { padding:.6rem 1.4rem; font-size:.875rem; font-weight:500; border-bottom:3px solid transparent; color:#6b7280; white-space:nowrap; transition:color .2s, border-color .2s; background:none; border-top:none; border-left:none; border-right:none; cursor:pointer; }
    .tab-btn.active { color:#4f46e5; border-bottom-color:#4f46e5; }
    .tab-panel { display:none; }
    .tab-panel.active { display:block; }

    .action-badge { display:inline-flex; align-items:center; gap:.35rem; padding:.25rem .7rem; border-radius:9999px; font-size:.72rem; font-weight:600; text-transform:uppercase; letter-spacing:.04em; }
    .badge-profile       { background:#e0e7ff; color:#4338ca; }
    .badge-password      { background:#fef3c7; color:#92400e; }
    .badge-deleted       { background:#fee2e2; color:#991b1b; }
    .badge-member-create { background:#dcfce7; color:#166534; }
    .badge-member-update { background:#dbeafe; color:#1e40af; }
    .badge-member-delete { background:#fee2e2; color:#991b1b; }
    .badge-login         { background:#f0fdf4; color:#15803d; border:1px solid #bbf7d0; }
    .badge-approve       { background:#dcfce7; color:#166534; }
    .badge-reject        { background:#fee2e2; color:#991b1b; }
    .badge-update        { background:#dbeafe; color:#1e40af; }
    .badge-user-create   { background:#faf5ff; color:#6b21a8; }
    .badge-default       { background:#f3f4f6; color:#374151; }

    .field-label { display:block; font-size:.7rem; font-weight:600; text-transform:uppercase; letter-spacing:.05em; color:#9ca3af; margin-bottom:.25rem; }
    .field-value { background:#f9fafb; border:1px solid #e5e7eb; border-radius:.5rem; padding:.5rem 1rem; color:#374151; font-size:.875rem; min-height:2.5rem; }
  </style>
</head>
<body class="bg-gray-50 text-gray-800 font-sans">
<div class="flex min-h-screen">
  @include('admin.layouts.sidebar')
  <main class="flex-1 overflow-y-auto">
    @include('admin.layouts.topnav', ['pageTitle' => 'Security Settings'])
    <section class="px-8 py-8">

      {{-- TAB BAR --}}
      <div class="flex gap-0 border-b border-gray-200 mb-8">
        <button class="tab-btn active" data-tab="profile">
          <i class="fi fi-sr-user" style="font-size:.8rem;margin-right:.35rem;vertical-align:middle;"></i>My Profile
        </button>
        <button class="tab-btn" data-tab="members">
          <i class="fi fi-sr-users-alt" style="font-size:.8rem;margin-right:.35rem;vertical-align:middle;"></i>Add Members
        </button>
        <button class="tab-btn" data-tab="team">
          <i class="fi fi-sr-list-check" style="font-size:.8rem;margin-right:.35rem;vertical-align:middle;"></i>Global Team Activity Tracking
        </button>
      </div>

      {{-- ===== TAB 1: MY PROFILE ===== --}}
      <div id="tab-profile" class="tab-panel active space-y-8">
        <div id="globalError" class="hidden bg-red-50 border border-red-200 text-red-700 text-sm rounded-xl px-5 py-4 flex items-start gap-3">
          <i class="fi fi-ss-exclamation mt-0.5 flex-shrink-0"></i>
          <div>
            <p class="font-semibold">Failed to load account data</p>
            <p id="globalErrorMsg" class="text-xs text-red-600 mt-1 font-mono"></p>
          </div>
        </div>

        {{-- ACCOUNT INFORMATION --}}
        <div class="bg-white rounded-2xl shadow border p-6">
          <div class="flex items-center justify-between mb-6">
            <h2 class="text-lg font-semibold">Account Information</h2>
            <button id="openEditBtn" class="inline-flex items-center gap-2 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium px-5 py-2 rounded-lg transition">
              <i class="fi fi-sr-pencil" style="font-size:13px"></i> Edit Profile
            </button>
          </div>
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
          <div id="profileDisplay" class="hidden flex gap-6 items-start">
            <div class="flex-shrink-0 text-center">
              <div class="relative inline-block">
                <img id="profileAvatar" src="" class="w-28 h-28 rounded-full object-cover shadow border-2 border-indigo-100" alt="Avatar">
              </div>
              <p id="profileMemberSince" class="text-xs text-gray-400 mt-2"></p>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 flex-1">
              <div><span class="field-label">First Name</span><div id="dispFirstName" class="field-value">-</div></div>
              <div><span class="field-label">Middle Name</span><div id="dispMiddleName" class="field-value">-</div></div>
              <div><span class="field-label">Last Name</span><div id="dispLastName" class="field-value">-</div></div>
              <div><span class="field-label">Email</span><div id="dispEmail" class="field-value">-</div></div>
              <div><span class="field-label">Username</span><div id="dispUsername" class="field-value">-</div></div>
            </div>
          </div>
        </div>

        {{-- CHANGE PASSWORD --}}
        <div class="bg-white rounded-2xl shadow border p-6">
          <h2 class="text-lg font-semibold mb-6">Change Password</h2>
          <form id="passwordForm" class="space-y-4">
            @csrf
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
              <div>
                <label class="field-label">Current Password</label>
                <input id="currentPassword" name="current_password" type="password" placeholder="Enter current password" class="border rounded-lg px-4 py-2 w-full focus:ring-2 focus:ring-indigo-400 focus:outline-none text-sm" required>
              </div>
              <div>
                <label class="field-label">New Password</label>
                <input id="newPassword" name="new_password" type="password" placeholder="Min. 8 characters" class="border rounded-lg px-4 py-2 w-full focus:ring-2 focus:ring-indigo-400 focus:outline-none text-sm" required>
                <div id="pwStrengthBar" class="hidden mt-1.5 h-1.5 rounded-full bg-gray-200 overflow-hidden"><div id="pwStrengthFill" class="h-full rounded-full transition-all duration-300 w-0"></div></div>
                <p id="pwStrengthLabel" class="hidden text-xs mt-1 text-gray-400"></p>
              </div>
              <div>
                <label class="field-label">Confirm Password</label>
                <input id="confirmPassword" name="new_password_confirmation" type="password" placeholder="Re-enter new password" class="border rounded-lg px-4 py-2 w-full focus:ring-2 focus:ring-indigo-400 focus:outline-none text-sm" required>
                <p id="pwMatchMsg" class="hidden text-xs text-red-500 mt-1">Passwords do not match.</p>
              </div>
            </div>
            <div id="pwError" class="hidden text-sm text-red-600 bg-red-50 border border-red-200 rounded-lg px-4 py-2"></div>
            <button type="submit" class="bg-rose-600 hover:bg-rose-700 text-white text-sm font-medium px-6 py-2 rounded-lg transition flex items-center gap-2">
              <span>Update Password</span>
              <svg id="pwSpinner" class="hidden animate-spin h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"/></svg>
            </button>
          </form>
        </div>

        {{-- MY ACTIVITY LOGS --}}
        <div class="bg-white rounded-2xl shadow border p-6">
          <div class="flex items-center justify-between mb-4">
            <h2 class="text-lg font-semibold">My Activity Logs</h2>
            <span id="logCount" class="text-xs text-gray-400 bg-gray-100 px-3 py-1 rounded-full">loading...</span>
          </div>
          <div class="flex flex-wrap items-center gap-3 mb-4">
            <div class="flex items-center gap-2">
              <label class="text-xs text-gray-500 font-medium whitespace-nowrap">From</label>
              <input type="date" id="myLogDateFrom" class="border rounded-lg px-3 py-1.5 text-xs focus:ring-2 focus:ring-indigo-400 focus:outline-none text-gray-600">
            </div>
            <div class="flex items-center gap-2">
              <label class="text-xs text-gray-500 font-medium whitespace-nowrap">To</label>
              <input type="date" id="myLogDateTo" class="border rounded-lg px-3 py-1.5 text-xs focus:ring-2 focus:ring-indigo-400 focus:outline-none text-gray-600">
            </div>
            <button id="myLogResetBtn" class="text-xs text-gray-400 hover:text-gray-600 underline underline-offset-2 transition">Reset</button>
          </div>
          <div class="overflow-x-auto rounded-xl border border-gray-100">
            <table class="w-full text-sm">
              <thead class="bg-gray-50 text-gray-500 text-xs uppercase tracking-wide">
                <tr>
                  <th class="px-5 py-3 text-left font-semibold">Action</th>
                  <th class="px-5 py-3 text-left font-semibold">Details</th>
                  <th class="px-5 py-3 text-left font-semibold">IP Address</th>
                  <th class="px-5 py-3 text-left font-semibold">Date &amp; Time</th>
                </tr>
              </thead>
              <tbody id="activityTableBody">
                <tr><td colspan="4" class="px-5 py-8 text-center text-gray-400">Loading logs...</td></tr>
              </tbody>
            </table>
          </div>
        </div>

        {{-- DANGER ZONE --}}
        <div class="bg-white rounded-2xl shadow border border-red-100 p-6">
          <h2 class="text-base font-semibold text-red-600 mb-1">Danger Zone</h2>
          <p class="text-sm text-gray-400 mb-4">Deleting your account will deactivate it permanently.</p>
          <button id="deleteAccountBtn" class="inline-flex items-center gap-2 bg-red-600 hover:bg-red-700 text-white text-sm font-medium px-6 py-2 rounded-lg transition">
            <i class="fi fi-ss-trash" style="font-size:13px"></i> Delete Account
          </button>
        </div>
      </div>{{-- /tab-profile --}}

      {{-- ===== TAB 2: ADD MEMBERS ===== --}}
      <div id="tab-members" class="tab-panel space-y-6">
        <div class="flex items-center justify-between">
          <div>
            <h2 class="text-lg font-semibold">Admin Members</h2>
            <p class="text-sm text-gray-400 mt-0.5">All other admin accounts on this platform.</p>
          </div>
          <button id="openCreateAdminBtn" class="inline-flex items-center gap-2 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium px-5 py-2 rounded-lg transition">
            <i class="fi fi-sr-user-add" style="font-size:13px"></i> Create New Admin
          </button>
        </div>
        <div id="memberError" class="hidden bg-red-50 border border-red-200 text-red-700 text-sm rounded-xl px-5 py-3"></div>
        <div class="bg-white rounded-2xl shadow border overflow-hidden">
          <table class="w-full text-sm">
            <thead class="bg-gray-50 text-gray-500 text-xs uppercase tracking-wide border-b">
              <tr>
                <th class="px-5 py-3 text-left font-semibold">Admin</th>
                <th class="px-5 py-3 text-left font-semibold">Username</th>
                <th class="px-5 py-3 text-left font-semibold">Email</th>
                <th class="px-5 py-3 text-left font-semibold">Status</th>
                <th class="px-5 py-3 text-left font-semibold">Joined</th>
                <th class="px-5 py-3 text-center font-semibold">Actions</th>
              </tr>
            </thead>
            <tbody id="membersTableBody">
              <tr><td colspan="6" class="px-5 py-10 text-center text-gray-400">Loading members...</td></tr>
            </tbody>
          </table>
        </div>
      </div>{{-- /tab-members --}}

      {{-- ===== TAB 3: GLOBAL TEAM ACTIVITY ===== --}}
      <div id="tab-team" class="tab-panel space-y-6">
        <div>
          <h2 class="text-lg font-semibold">Global Team Activity Tracking</h2>
          <p class="text-sm text-gray-400 mt-0.5">Combined activity log of all admin accounts.</p>
        </div>
        <div class="bg-white rounded-2xl shadow border overflow-hidden">
          <div class="flex flex-wrap items-center gap-3 px-6 py-4 border-b border-gray-100">
            <span id="teamLogCount" class="text-xs text-gray-400 bg-gray-100 px-3 py-1 rounded-full">loading...</span>
            <div class="flex items-center gap-2 ml-auto">
              <label class="text-xs text-gray-500 font-medium whitespace-nowrap">From</label>
              <input type="date" id="teamLogDateFrom" class="border rounded-lg px-3 py-1.5 text-xs focus:ring-2 focus:ring-indigo-400 focus:outline-none text-gray-600">
            </div>
            <div class="flex items-center gap-2">
              <label class="text-xs text-gray-500 font-medium whitespace-nowrap">To</label>
              <input type="date" id="teamLogDateTo" class="border rounded-lg px-3 py-1.5 text-xs focus:ring-2 focus:ring-indigo-400 focus:outline-none text-gray-600">
            </div>
            <button id="teamLogResetBtn" class="text-xs text-gray-400 hover:text-gray-600 underline underline-offset-2 transition">Reset</button>
            <button id="refreshTeamLogBtn" class="inline-flex items-center gap-1.5 text-xs text-indigo-600 hover:text-indigo-800 font-medium transition">
              <i class="fi fi-sr-refresh" style="font-size:.7rem"></i> Refresh
            </button>
          </div>
          <div class="overflow-x-auto">
            <table class="w-full text-sm">
              <thead class="bg-gray-50 text-gray-500 text-xs uppercase tracking-wide border-b">
                <tr>
                  <th class="px-5 py-3 text-left font-semibold">Admin Name</th>
                  <th class="px-5 py-3 text-left font-semibold">Action</th>
                  <th class="px-5 py-3 text-left font-semibold">Details</th>
                  <th class="px-5 py-3 text-left font-semibold">IP Address</th>
                  <th class="px-5 py-3 text-left font-semibold">Date &amp; Time</th>
                </tr>
              </thead>
              <tbody id="teamActivityBody">
                <tr><td colspan="5" class="px-5 py-10 text-center text-gray-400">Loading activity...</td></tr>
              </tbody>
            </table>
          </div>
        </div>
      </div>{{-- /tab-team --}}

    </section>
  </main>
</div>

{{-- MODAL: EDIT MY PROFILE --}}
<div id="editModal" class="modal-backdrop">
  <div class="bg-white rounded-2xl shadow-2xl w-full max-w-lg mx-4 overflow-hidden">
    <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100">
      <h3 class="text-base font-semibold">Edit Profile</h3>
      <button id="closeEditBtn" class="w-8 h-8 flex items-center justify-center rounded-full hover:bg-gray-100 text-gray-400 text-xl transition">&times;</button>
    </div>
    <form id="profileForm" enctype="multipart/form-data" class="px-6 py-5 space-y-4">
      @csrf
      <div class="flex items-center gap-4">
        <div class="relative">
          <img id="modalAvatar" src="" class="w-20 h-20 rounded-full object-cover border-2 border-indigo-200 shadow" alt="">
          <label for="avatarInput" class="absolute -bottom-1 -right-1 w-7 h-7 bg-indigo-600 hover:bg-indigo-700 rounded-full flex items-center justify-center cursor-pointer shadow transition">
            <i class="fi fi-ss-camera text-white" style="font-size:11px"></i>
          </label>
          <input id="avatarInput" name="avatar" type="file" accept="image/*" class="hidden">
        </div>
        <div>
          <p class="text-xs text-gray-500 font-medium">Change profile picture</p>
          <p class="text-xs text-gray-400">JPG, PNG up to 2 MB</p>
        </div>
      </div>
      <div class="grid grid-cols-3 gap-3">
        <div><label class="field-label">First Name <span class="text-red-500">*</span></label><input id="editFirstName" name="first_name" type="text" class="border rounded-lg px-3 py-2 w-full focus:ring-2 focus:ring-indigo-400 focus:outline-none text-sm" required></div>
        <div><label class="field-label">Middle Name</label><input id="editMiddleName" name="middle_name" type="text" class="border rounded-lg px-3 py-2 w-full focus:ring-2 focus:ring-indigo-400 focus:outline-none text-sm"></div>
        <div><label class="field-label">Last Name <span class="text-red-500">*</span></label><input id="editLastName" name="last_name" type="text" class="border rounded-lg px-3 py-2 w-full focus:ring-2 focus:ring-indigo-400 focus:outline-none text-sm" required></div>
      </div>
      <div class="grid grid-cols-2 gap-3">
        <div><label class="field-label">Email <span class="text-red-500">*</span></label><input id="editEmail" name="email" type="email" class="border rounded-lg px-3 py-2 w-full focus:ring-2 focus:ring-indigo-400 focus:outline-none text-sm" required></div>
        <div><label class="field-label">Username <span class="text-red-500">*</span></label><input id="editUsername" name="username" type="text" class="border rounded-lg px-3 py-2 w-full focus:ring-2 focus:ring-indigo-400 focus:outline-none text-sm" required></div>
      </div>
      <div id="profileError" class="hidden text-sm text-red-700 bg-red-50 border border-red-200 rounded-lg px-4 py-2"></div>
      <div class="flex justify-end gap-3 pt-1">
        <button type="button" id="cancelEditBtn" class="px-5 py-2 text-sm rounded-lg border border-gray-200 hover:bg-gray-50 transition text-gray-600">Cancel</button>
        <button type="submit" id="saveProfileBtn" class="px-6 py-2 text-sm bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg transition flex items-center gap-2">
          <span>Save Changes</span>
          <svg id="savingSpinner" class="hidden animate-spin h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"/></svg>
        </button>
      </div>
    </form>
  </div>
</div>

{{-- MODAL: CREATE NEW ADMIN --}}
<div id="createAdminModal" class="modal-backdrop">
  <div class="bg-white rounded-2xl shadow-2xl w-full max-w-md mx-4 overflow-hidden">
    <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100">
      <h3 class="text-base font-semibold">Create New Admin</h3>
      <button class="modal-close-btn w-8 h-8 flex items-center justify-center rounded-full hover:bg-gray-100 text-gray-400 text-xl transition" data-modal="createAdminModal">&times;</button>
    </div>
    <form id="createAdminForm" class="px-6 py-5 space-y-4">
      @csrf
      <div class="grid grid-cols-2 gap-3">
        <div><label class="field-label">First Name <span class="text-red-500">*</span></label><input name="first_name" type="text" required class="border rounded-lg px-3 py-2 w-full focus:ring-2 focus:ring-indigo-400 focus:outline-none text-sm"></div>
        <div><label class="field-label">Last Name <span class="text-red-500">*</span></label><input name="last_name" type="text" required class="border rounded-lg px-3 py-2 w-full focus:ring-2 focus:ring-indigo-400 focus:outline-none text-sm"></div>
      </div>
      <div><label class="field-label">Email <span class="text-red-500">*</span></label><input name="email" type="email" required class="border rounded-lg px-3 py-2 w-full focus:ring-2 focus:ring-indigo-400 focus:outline-none text-sm"></div>
      <div><label class="field-label">Username <span class="text-red-500">*</span></label><input name="username" type="text" required class="border rounded-lg px-3 py-2 w-full focus:ring-2 focus:ring-indigo-400 focus:outline-none text-sm"></div>
      <div><label class="field-label">Temporary Password <span class="text-red-500">*</span></label><input name="password" type="password" required minlength="8" placeholder="Min. 8 characters" class="border rounded-lg px-3 py-2 w-full focus:ring-2 focus:ring-indigo-400 focus:outline-none text-sm"></div>
      <div id="createAdminError" class="hidden text-sm text-red-700 bg-red-50 border border-red-200 rounded-lg px-4 py-2"></div>
      <div class="flex justify-end gap-3 pt-1">
        <button type="button" class="modal-close-btn px-5 py-2 text-sm rounded-lg border border-gray-200 hover:bg-gray-50 transition text-gray-600" data-modal="createAdminModal">Cancel</button>
        <button type="submit" id="createAdminSubmitBtn" class="px-6 py-2 text-sm bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg transition flex items-center gap-2">
          <span>Create Admin</span>
          <svg id="createAdminSpinner" class="hidden animate-spin h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"/></svg>
        </button>
      </div>
    </form>
  </div>
</div>

{{-- MODAL: VIEW / EDIT MEMBER --}}
<div id="memberModal" class="modal-backdrop">
  <div class="bg-white rounded-2xl shadow-2xl w-full max-w-2xl mx-4 overflow-hidden flex flex-col max-h-[90vh]">
    <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100 flex-shrink-0">
      <h3 class="text-base font-semibold" id="memberModalTitle">Admin Details</h3>
      <button class="modal-close-btn w-8 h-8 flex items-center justify-center rounded-full hover:bg-gray-100 text-gray-400 text-xl transition" data-modal="memberModal">&times;</button>
    </div>
    <div class="overflow-y-auto flex-1 px-6 py-5 space-y-5">
      <div class="flex items-center gap-4">
        <div id="memberModalAvatar" class="w-14 h-14 rounded-full bg-indigo-600 text-white font-bold flex items-center justify-center text-xl flex-shrink-0">?</div>
        <div>
          <p class="font-semibold text-gray-800" id="memberModalName">-</p>
          <p class="text-xs text-gray-400" id="memberModalEmail">-</p>
          <p class="text-xs text-gray-300" id="memberModalJoined">-</p>
        </div>
      </div>
      <form id="memberEditForm" class="space-y-3">
        @csrf
        <input type="hidden" id="memberEditId" name="_target_id" value="">
        <div class="grid grid-cols-2 gap-3">
          <div><label class="field-label">First Name <span class="text-red-500">*</span></label><input id="memberEditFirstName" name="first_name" type="text" required class="border rounded-lg px-3 py-2 w-full focus:ring-2 focus:ring-indigo-400 focus:outline-none text-sm"></div>
          <div><label class="field-label">Last Name <span class="text-red-500">*</span></label><input id="memberEditLastName" name="last_name" type="text" required class="border rounded-lg px-3 py-2 w-full focus:ring-2 focus:ring-indigo-400 focus:outline-none text-sm"></div>
        </div>
        <div class="grid grid-cols-2 gap-3">
          <div><label class="field-label">Email <span class="text-red-500">*</span></label><input id="memberEditEmail" name="email" type="email" required class="border rounded-lg px-3 py-2 w-full focus:ring-2 focus:ring-indigo-400 focus:outline-none text-sm"></div>
          <div><label class="field-label">Username <span class="text-red-500">*</span></label><input id="memberEditUsername" name="username" type="text" required class="border rounded-lg px-3 py-2 w-full focus:ring-2 focus:ring-indigo-400 focus:outline-none text-sm"></div>
        </div>
        <div><label class="field-label">Reset Password <span class="text-gray-300 font-normal">(leave blank to keep unchanged)</span></label><input name="password" type="password" minlength="8" placeholder="New password (min. 8 chars)" class="border rounded-lg px-3 py-2 w-full focus:ring-2 focus:ring-indigo-400 focus:outline-none text-sm"></div>
        <div id="memberEditError" class="hidden text-sm text-red-700 bg-red-50 border border-red-200 rounded-lg px-4 py-2"></div>
        <div class="flex justify-end">
          <button type="submit" id="memberEditSaveBtn" class="px-6 py-2 text-sm bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg transition flex items-center gap-2">
            <span>Save Changes</span>
            <svg id="memberEditSpinner" class="hidden animate-spin h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"/></svg>
          </button>
        </div>
      </form>
      <div>
        <h4 class="text-sm font-semibold text-gray-700 mb-3">Activity Logs</h4>
        <div class="overflow-x-auto rounded-xl border border-gray-100">
          <table class="w-full text-sm">
            <thead class="bg-gray-50 text-gray-500 text-xs uppercase tracking-wide">
              <tr>
                <th class="px-4 py-2.5 text-left font-semibold">Action</th>
                <th class="px-4 py-2.5 text-left font-semibold">Details</th>
                <th class="px-4 py-2.5 text-left font-semibold">IP</th>
                <th class="px-4 py-2.5 text-left font-semibold">Date &amp; Time</th>
              </tr>
            </thead>
            <tbody id="memberLogsBody">
              <tr><td colspan="4" class="px-4 py-6 text-center text-gray-300 text-xs">No logs yet.</td></tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>
</div>

<div id="toast"></div>

</body>
</html>
