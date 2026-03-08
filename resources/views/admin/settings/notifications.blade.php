<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <title>Notifications – Legatura Admin</title>

  <script src="https://cdn.tailwindcss.com"></script>
  <link rel="stylesheet" href="{{ asset('css/admin/home/mainComponents.css') }}">
  <link rel="stylesheet" href="{{ asset('css/admin/settings/notifications.css') }}">

  <link rel='stylesheet' href='https://cdn-uicons.flaticon.com/3.0.0/uicons-solid-straight/css/uicons-solid-straight.css'>
  <link rel='stylesheet' href='https://cdn-uicons.flaticon.com/3.0.0/uicons-solid-rounded/css/uicons-solid-rounded.css'>
  <link rel='stylesheet' href='https://cdn-uicons.flaticon.com/3.0.0/uicons-bold-rounded/css/uicons-bold-rounded.css'>
  <link rel='stylesheet' href='https://cdn-uicons.flaticon.com/3.0.0/uicons-regular-rounded/css/uicons-regular-rounded.css'>

  <script src="{{ asset('js/admin/home/mainComponents.js') }}" defer></script>

  <style>
    /* ── Shared card & row helpers ────────────────────────────────── */
    .setting-card { transition: box-shadow .2s; }
    .setting-row  { display:flex; align-items:center; justify-content:space-between; gap:12px; padding:4px 0; }

    /* Toggle switch */
    .switch        { position:relative; display:inline-block; width:44px; height:24px; flex-shrink:0; }
    .switch input  { opacity:0; width:0; height:0; }
    .slider        { position:absolute; inset:0; background:#cbd5e1; border-radius:24px; cursor:pointer; transition:.3s; }
    .slider:before { content:''; position:absolute; width:18px; height:18px; left:3px; bottom:3px; background:#fff; border-radius:50%; transition:.3s; }
    input:checked + .slider            { background:#EC7E00; }
    input:checked + .slider:before     { transform:translateX(20px); }

    /* Toast */
    #toastBar {
      position:fixed; bottom:24px; left:50%; transform:translateX(-50%);
      z-index:9999; display:flex; gap:8px; flex-direction:column; align-items:center;
      pointer-events:none;
    }
    .toast {
      padding:10px 20px; border-radius:10px; font-size:.875rem; font-weight:600;
      color:#fff; box-shadow:0 4px 20px rgba(0,0,0,.15);
      animation: toastIn .25s ease;
    }
    .toast.success { background:#10B981; }
    .toast.error   { background:#EF4444; }
    @keyframes toastIn { from{opacity:0;transform:translateY(12px)} to{opacity:1;transform:translateY(0)} }

    /* Save bar */
    #saveBar {
      position:fixed; left:50%; bottom:24px; transform:translateX(-50%);
      z-index:9000; transition: opacity .25s;
    }
    #saveBar.hidden { opacity:0; pointer-events:none; }

    /* Tag pills for targeted users */
    .user-tag {
      display:inline-flex; align-items:center; gap:4px; padding:3px 10px 3px 8px;
      background:#FFF3E6; color:#EC7E00; border-radius:20px; font-size:.75rem; font-weight:600;
      border:1px solid #FDDCB0;
    }
    .user-tag button { background:none; border:none; cursor:pointer; color:#EC7E00; font-size:.9rem; line-height:1; padding:0; }

    /* Sent log table */
    .log-table { width:100%; border-collapse:collapse; font-size:.82rem; }
    .log-table th { background:#f8fafc; color:#64748b; font-weight:700; text-transform:uppercase; letter-spacing:.04em; font-size:.7rem; padding:10px 12px; text-align:left; border-bottom:1px solid #e2e8f0; }
    .log-table td { padding:10px 12px; border-bottom:1px solid #f1f5f9; color:#334155; vertical-align:top; }
    .log-table tr:last-child td { border-bottom:none; }
    .log-table tr:hover td { background:#fafbfc; }

    /* Badge */
    .badge { display:inline-flex; align-items:center; padding:2px 8px; border-radius:10px; font-size:.68rem; font-weight:700; }
    .badge-inapp  { background:#DBEAFE; color:#3B82F6; }
    .badge-email  { background:#D1FAE5; color:#10B981; }
    .badge-both   { background:#FFF3E6; color:#EC7E00; }
    .badge-all    { background:#F1F5F9; color:#64748B; }
    .badge-target { background:#FEF3C7; color:#F59E0B; }

    /* Section tabs */
    .section-tab { padding:8px 18px; border-radius:8px; font-size:.85rem; font-weight:600; color:#64748b; cursor:pointer; border:none; background:transparent; transition:all .2s; }
    .section-tab.active { background:#EC7E00; color:#fff; }
    .section-tab:hover:not(.active) { background:#f1f5f9; color:#334155; }

    /* User search dropdown */
    #userSearchDropdown { max-height:220px; overflow-y:auto; }
    .user-option { padding:8px 12px; cursor:pointer; font-size:.82rem; border-bottom:1px solid #f1f5f9; }
    .user-option:hover { background:#FFF3E6; }
    .user-option .meta  { font-size:.7rem; color:#94a3b8; }

    /* ── User Activity table ── */
    .act-dot { width:8px; height:8px; border-radius:50%; background:#EC7E00; display:inline-block; flex-shrink:0; }
    .act-row-unread td { background:#fffaf5; }
    .act-row-unread td:first-child { border-left:3px solid #EC7E00; }
    .act-type-badge {
      display:inline-flex; align-items:center; gap:5px;
      padding:3px 10px; border-radius:20px; font-size:.7rem; font-weight:700; white-space:nowrap;
    }
    .act-registered   { background:#DCFCE7; color:#16A34A; }
    .act-failed-login { background:#FEE2E2; color:#DC2626; }
    .act-reported     { background:#FEF9C3; color:#CA8A04; }
    .act-profile      { background:#DBEAFE; color:#2563EB; }
    .act-password     { background:#F3E8FF; color:#7C3AED; }
    .act-email-ver    { background:#CCFBF1; color:#0D9488; }
    .act-suspended    { background:#FFE4E6; color:#BE123C; }
    .act-view-btn {
      display:inline-flex; align-items:center; gap:4px;
      padding:4px 10px; border-radius:8px; font-size:.72rem; font-weight:700;
      background:#FFF3E6; color:#EC7E00; border:1px solid #FDDCB0;
      cursor:pointer; text-decoration:none; transition:background .15s; white-space:nowrap;
    }
    .act-view-btn:hover { background:#FDDCB0; color:#c56a00; }
  </style>
</head>

<body class="bg-gray-50 text-gray-800 font-sans">
<div class="flex min-h-screen">

  @include('admin.layouts.sidebar')

  <main class="flex-1 overflow-auto">
    @include('admin.layouts.topnav', ['pageTitle' => 'Notifications'])

    <section class="px-6 py-6 space-y-6 max-w-screen-xl mx-auto">

      {{-- ── Section Tabs ──────────────────────────────────────────── --}}
      <div class="flex gap-2 flex-wrap">
        <button class="section-tab active" data-section="send">
          <i class="fi fi-rr-paper-plane mr-1"></i> Send Notifications
        </button>
        <button class="section-tab" data-section="activity">
          <i class="fi fi-rr-bell mr-1"></i> User Activity
          <span id="activityUnreadBadge" class="hidden ml-1.5 inline-flex items-center justify-center w-5 h-5 rounded-full bg-red-500 text-white text-[10px] font-bold leading-none"></span>
        </button>
        <button class="section-tab" data-section="preferences">
          <i class="fi fi-rr-settings mr-1"></i> My Preferences
        </button>
        <button class="section-tab" data-section="log">
          <i class="fi fi-rr-list mr-1"></i> Sent Log
        </button>
      </div>

      {{-- ════════════════════════════════════════════════════════════
           SECTION 1 – SEND NOTIFICATIONS
      ════════════════════════════════════════════════════════════ --}}
      <div id="section-send" class="section-content space-y-6">

        {{-- ── Mass Announcement ──────────────────────────────── --}}
        <div class="bg-white rounded-2xl shadow-md border border-gray-200 overflow-hidden">
          <div class="px-6 py-4 bg-gradient-to-r from-indigo-500 to-violet-600 text-white flex items-center gap-2">
            <i class="fi fi-rr-megaphone text-lg"></i>
            <div>
              <h2 class="font-semibold text-base">Mass Announcement</h2>
              <p class="text-xs opacity-80">Send to ALL property owners & contractors at once</p>
            </div>
          </div>
          <div class="p-6 space-y-4">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
              <div>
                <label class="block text-xs font-semibold text-gray-600 mb-1">Title <span class="text-red-500">*</span></label>
                <input id="ann-title" type="text" maxlength="255" placeholder="Announcement title…"
                  class="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-300">
              </div>
              <div>
                <label class="block text-xs font-semibold text-gray-600 mb-1">Delivery Method <span class="text-red-500">*</span></label>
                <select id="ann-delivery"
                  class="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-300">
                  <option value="in-app">In-App Only</option>
                  <option value="email">Email Only</option>
                  <option value="both" selected>Both (In-App + Email)</option>
                </select>
              </div>
            </div>
            <div>
              <label class="block text-xs font-semibold text-gray-600 mb-1">Message <span class="text-red-500">*</span></label>
              <textarea id="ann-message" rows="4" placeholder="Write your announcement here…"
                class="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-300 resize-none"></textarea>
            </div>
            <div class="flex items-center gap-3">
              <button id="btnSendAnnouncement"
                class="px-5 py-2 rounded-lg bg-gradient-to-r from-indigo-600 to-violet-600 hover:from-indigo-700 hover:to-violet-700 text-white text-sm font-semibold shadow transition flex items-center gap-2">
                <i class="fi fi-rr-paper-plane"></i> Send to All Users
              </button>
              <span id="ann-sending" class="hidden text-sm text-gray-400 flex items-center gap-1">
                <i class="fi fi-rr-spinner animate-spin"></i> Sending…
              </span>
            </div>
          </div>
        </div>

        {{-- ── Targeted Notification ──────────────────────────── --}}
        <div class="bg-white rounded-2xl shadow-md border border-gray-200 overflow-hidden">
          <div class="px-6 py-4 bg-gradient-to-r from-emerald-500 to-teal-600 text-white flex items-center gap-2">
            <i class="fi fi-rr-target text-lg"></i>
            <div>
              <h2 class="font-semibold text-base">Targeted Notification</h2>
              <p class="text-xs opacity-80">Send to specific users by name or email</p>
            </div>
          </div>
          <div class="p-6 space-y-4">

            {{-- User search + tags --}}
            <div>
              <label class="block text-xs font-semibold text-gray-600 mb-1">Select Recipients <span class="text-red-500">*</span></label>
              <div class="relative">
                <input id="userSearchInput" type="text" autocomplete="off"
                  placeholder="Search by username or email…"
                  class="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-emerald-300">
                <div id="userSearchDropdown"
                  class="absolute z-50 w-full bg-white border border-gray-200 rounded-lg shadow-lg hidden mt-1"></div>
              </div>
              {{-- Selected user tags --}}
              <div id="selectedUsersContainer" class="flex flex-wrap gap-2 mt-2 min-h-[28px]"></div>
              <input type="hidden" id="target-user-ids" value="">
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
              <div>
                <label class="block text-xs font-semibold text-gray-600 mb-1">Title <span class="text-red-500">*</span></label>
                <input id="tgt-title" type="text" maxlength="255" placeholder="Notification title…"
                  class="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-emerald-300">
              </div>
              <div>
                <label class="block text-xs font-semibold text-gray-600 mb-1">Delivery Method <span class="text-red-500">*</span></label>
                <select id="tgt-delivery"
                  class="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-emerald-300">
                  <option value="in-app">In-App Only</option>
                  <option value="email">Email Only</option>
                  <option value="both" selected>Both (In-App + Email)</option>
                </select>
              </div>
            </div>
            <div>
              <label class="block text-xs font-semibold text-gray-600 mb-1">Message <span class="text-red-500">*</span></label>
              <textarea id="tgt-message" rows="4" placeholder="Write your message here…"
                class="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-emerald-300 resize-none"></textarea>
            </div>
            <div class="flex items-center gap-3">
              <button id="btnSendTargeted"
                class="px-5 py-2 rounded-lg bg-gradient-to-r from-emerald-600 to-teal-600 hover:from-emerald-700 hover:to-teal-700 text-white text-sm font-semibold shadow transition flex items-center gap-2">
                <i class="fi fi-rr-target"></i> Send to Selected Users
              </button>
              <span id="tgt-sending" class="hidden text-sm text-gray-400 flex items-center gap-1">
                <i class="fi fi-rr-spinner animate-spin"></i> Sending…
              </span>
            </div>
          </div>
        </div>
      </div>

      {{-- ════════════════════════════════════════════════════════════
           SECTION 2 – USER ACTIVITY FEED
      ════════════════════════════════════════════════════════════ --}}
      <div id="section-activity" class="section-content hidden">
        <div class="bg-white rounded-2xl shadow-md border border-gray-200 overflow-hidden">

          {{-- Header --}}
          <div class="px-6 py-4 border-b flex items-center justify-between flex-wrap gap-3">
            <div>
              <h2 class="font-semibold text-gray-800 flex items-center gap-2">
                <i class="fi fi-ss-bell text-orange-500"></i>
                User Activity Notifications
              </h2>
              <p class="text-xs text-gray-500 mt-0.5">Latest account & security events triggered by users. Only activities you have enabled in <strong>My Preferences</strong> are shown.</p>
            </div>
            <div class="flex items-center gap-2">
              <button id="actMarkAllRead"
                class="px-3 py-1.5 text-xs rounded-lg border border-gray-200 text-gray-600 font-semibold hover:bg-gray-50 transition flex items-center gap-1">
                <i class="fi fi-rr-check-double"></i> Mark all read
              </button>
              <button id="actRefresh"
                class="px-3 py-1.5 text-xs rounded-lg border border-gray-200 text-indigo-600 font-semibold hover:bg-indigo-50 transition flex items-center gap-1">
                <i class="fi fi-rr-refresh"></i> Refresh
              </button>
            </div>
          </div>

          {{-- Filters --}}
          <div class="px-6 py-3 border-b bg-gray-50 flex flex-wrap gap-3 items-center">
            <select id="actFilterType"
              class="rounded-lg border border-gray-200 px-3 py-1.5 text-sm focus:outline-none focus:ring-2 focus:ring-orange-300 min-w-[190px]">
              <option value="">All activity types</option>
              <option value="user_registered">New User Registration</option>
              <option value="failed_login_attempt">Failed Login Attempt</option>
              <option value="project_reported">Project Reported</option>
              <option value="profile_updated">Profile Updated</option>
              <option value="password_reset">Password Reset Requested</option>
              <option value="email_verified">Email Verified</option>
              <option value="account_status_changed">Account Suspended/Unsuspended</option>
            </select>
            <select id="actFilterRead"
              class="rounded-lg border border-gray-200 px-3 py-1.5 text-sm focus:outline-none focus:ring-2 focus:ring-orange-300">
              <option value="">All</option>
              <option value="0">Unread</option>
              <option value="1">Read</option>
            </select>
            <div class="relative flex-1 min-w-[180px] max-w-xs">
              <i class="fi fi-rr-search absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-xs"></i>
              <input id="actSearch" type="text" placeholder="Search username or email…"
                class="w-full pl-8 pr-3 py-1.5 rounded-lg border border-gray-200 text-sm focus:outline-none focus:ring-2 focus:ring-orange-300">
            </div>
            <span id="actTotalBadge" class="ml-auto text-xs text-gray-400 font-semibold"></span>
          </div>

          {{-- Table --}}
          <div class="overflow-x-auto">
            <table class="log-table" id="activityTable">
              <thead>
                <tr>
                  <th style="width:18px"></th>{{-- unread dot --}}
                  <th>Activity</th>
                  <th>User</th>
                  <th>Details</th>
                  <th>Date & Time</th>
                  <!-- <th style="width:80px">Action</th>-->
                </tr>
              </thead>
              <tbody id="activityTableBody">
                <tr><td colspan="6" class="text-center py-10 text-gray-400 text-sm">Loading…</td></tr>
              </tbody>
            </table>
          </div>

          {{-- Pagination --}}
          <div class="px-6 py-3 border-t flex items-center justify-between gap-3">
            <button id="actPrevBtn"
              class="px-3 py-1.5 rounded-lg border text-sm font-semibold text-gray-600 hover:bg-gray-50 disabled:opacity-40 disabled:cursor-not-allowed">
              ← Prev
            </button>
            <span id="actPageInfo" class="text-xs text-gray-400"></span>
            <button id="actNextBtn"
              class="px-3 py-1.5 rounded-lg border text-sm font-semibold text-gray-600 hover:bg-gray-50 disabled:opacity-40 disabled:cursor-not-allowed">
              Next →
            </button>
          </div>
        </div>
      </div>

      {{-- ════════════════════════════════════════════════════════════
           SECTION 3 – PREFERENCES
      ════════════════════════════════════════════════════════════ --}}
      <div id="section-preferences" class="section-content hidden space-y-6">

        <div class="flex items-center justify-between flex-wrap gap-3">
          <div>
            <h2 class="text-lg font-semibold text-gray-800">Notification Preferences</h2>
            <p class="text-sm text-gray-500">Choose which system events you want to be notified about in your admin panel.</p>
          </div>
          <button id="resetDefaultsBtn"
            class="px-4 py-2 rounded-lg border-2 border-gray-300 text-gray-700 text-sm font-semibold hover:bg-gray-50 transition">
            Reset to defaults
          </button>
        </div>

        <div class="grid grid-cols-1 xl:grid-cols-2 gap-6">

          {{-- User Activity --}}
          <div class="setting-card bg-white rounded-2xl shadow-md border border-gray-200 overflow-hidden">
            <div class="px-6 py-4 border-b bg-gradient-to-r from-indigo-500 to-violet-600 text-white">
              <div class="flex items-center gap-2 font-semibold"><i class="fi fi-ss-users"></i><span>User Activity</span></div>
              <p class="text-xs opacity-80 mt-0.5">Account and security related updates</p>
            </div>
            <div class="p-6 space-y-4">
              @php
                $prefRows = [
                  ['user_registered',       'New User Registration',           'Get notified when new users sign up.'],
                  ['failed_login_attempt',  'Failed Login Attempt',            'Security alert for repeated failed attempts.'],
                  ['project_reported',      'Project Reported',                'Alert when a project is reported by users.'],
                  ['profile_updated',       'Profile Updated',                 'Notify when a user changes account details.'],
                  ['password_reset',        'Password Reset Requested',        'Alert for password reset requests and completions.'],
                  ['email_verified',        'Email Verified',                  'Notify when a user verifies their email address.'],
                  ['account_status_changed','Account Suspended/Unsuspended',   'Alert when moderation changes account status.'],
                ];
              @endphp
              @foreach($prefRows as [$key, $label, $desc])
              <div class="setting-row">
                <div>
                  <div class="font-medium text-gray-800 text-sm">{{ $label }}</div>
                  <div class="text-xs text-gray-500">{{ $desc }}</div>
                </div>
                <label class="switch">
                  <input type="checkbox" class="setting-toggle" data-setting="{{ $key }}" checked>
                  <span class="slider"></span>
                </label>
              </div>
              @endforeach
            </div>
          </div>

          {{-- Channels --}}
          <div class="setting-card bg-white rounded-2xl shadow-md border border-gray-200 overflow-hidden">
            <div class="px-6 py-4 border-b bg-gradient-to-r from-emerald-500 to-teal-600 text-white">
              <div class="flex items-center gap-2 font-semibold"><i class="fi fi-ss-megaphone"></i><span>Notification Channels</span></div>
              <p class="text-xs opacity-80 mt-0.5">Choose how you receive alerts</p>
            </div>
            <div class="p-6 space-y-4">
              <div class="setting-row">
                <div>
                  <div class="font-medium text-gray-800 text-sm">Email Notifications</div>
                  <div class="text-xs text-gray-500">Receive important updates in your inbox.</div>
                </div>
                <label class="switch">
                  <input type="checkbox" class="setting-toggle" data-setting="channel_email" checked>
                  <span class="slider"></span>
                </label>
              </div>
            </div>
          </div>
        </div>

        {{-- Sticky Save Bar --}}
        <div id="saveBar" class="hidden">
          <div class="bg-white border border-gray-200 rounded-xl shadow-2xl px-4 py-3 flex items-center gap-3">
            <span class="text-sm text-gray-700">You have unsaved changes</span>
            <button id="saveSettingsBtn"
              class="px-4 py-2 text-sm rounded-lg bg-gradient-to-r from-indigo-600 to-purple-600 hover:from-indigo-700 hover:to-purple-700 text-white font-semibold shadow">
              Save changes
            </button>
          </div>
        </div>
      </div>

      {{-- ════════════════════════════════════════════════════════════
           SECTION 4 – SENT LOG
      ════════════════════════════════════════════════════════════ --}}
      <div id="section-log" class="section-content hidden">
        <div class="bg-white rounded-2xl shadow-md border border-gray-200 overflow-hidden">
          <div class="px-6 py-4 border-b flex items-center justify-between">
            <div>
              <h2 class="font-semibold text-gray-800">Sent Notifications</h2>
              <p class="text-xs text-gray-500 mt-0.5">History of all announcements and targeted messages you have sent.</p>
            </div>
            <button id="refreshLog" class="text-sm text-indigo-600 font-semibold hover:underline flex items-center gap-1">
              <i class="fi fi-rr-refresh"></i> Refresh
            </button>
          </div>

          {{-- Filters --}}
          <div class="px-6 py-3 border-b bg-gray-50 flex flex-wrap gap-3 items-center">
            <select id="logFilterType" class="rounded-lg border border-gray-200 px-3 py-1.5 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-300">
              <option value="">All types</option>
              <option value="all">Mass Announcement</option>
              <option value="targeted">Targeted</option>
            </select>
            <select id="logFilterDelivery" class="rounded-lg border border-gray-200 px-3 py-1.5 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-300">
              <option value="">All delivery</option>
              <option value="in-app">In-App</option>
              <option value="email">Email</option>
              <option value="both">Both</option>
            </select>
            <span id="logTotalBadge" class="ml-auto text-xs text-gray-400 font-semibold"></span>
          </div>

          {{-- Table --}}
          <div class="overflow-x-auto">
            <table class="log-table" id="sentLogTable">
              <thead>
                <tr>
                  <th style="width:40%">Title / Message</th>
                  <th>Type</th>
                  <th>Delivery</th>
                  <th>Recipients</th>
                  <th>Sent At</th>
                </tr>
              </thead>
              <tbody id="sentLogBody">
                <tr><td colspan="5" class="text-center py-8 text-gray-400 text-sm">Loading…</td></tr>
              </tbody>
            </table>
          </div>

          {{-- Pagination --}}
          <div class="px-6 py-3 border-t flex items-center justify-between gap-3">
            <button id="logPrevBtn"
              class="px-3 py-1.5 rounded-lg border text-sm font-semibold text-gray-600 hover:bg-gray-50 disabled:opacity-40 disabled:cursor-not-allowed">
              ← Prev
            </button>
            <span id="logPageInfo" class="text-xs text-gray-400"></span>
            <button id="logNextBtn"
              class="px-3 py-1.5 rounded-lg border text-sm font-semibold text-gray-600 hover:bg-gray-50 disabled:opacity-40 disabled:cursor-not-allowed">
              Next →
            </button>
          </div>
        </div>
      </div>

    </section>
  </main>
</div>

{{-- ── Toast container ────────────────────────────────────────────────── --}}
<div id="toastBar"></div>

<script>
// ─────────────────────────────────────────────────────────────────────────────
//  Legatura Admin – Notifications JS
// ─────────────────────────────────────────────────────────────────────────────
const CSRF = document.querySelector('meta[name="csrf-token"]')?.content ?? '';

// ── Toast helper ──────────────────────────────────────────────────────────────
function toast(msg, type = 'success') {
    const bar  = document.getElementById('toastBar');
    const el   = document.createElement('div');
    el.className = `toast ${type}`;
    el.textContent = msg;
    bar.appendChild(el);
    setTimeout(() => el.remove(), 3500);
}

// ── API helper ─────────────────────────────────────────────────────────────
async function api(url, method = 'GET', body = null) {
    const opts = {
        method,
        headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': CSRF },
        credentials: 'same-origin',
    };
    if (body) opts.body = JSON.stringify(body);
    const res  = await fetch(url, opts);
    const data = await res.json();
    return { ok: res.ok, data };
}

// ─────────────────────────────────────────────────────────────────────────────
//  SECTION TABS
// ─────────────────────────────────────────────────────────────────────────────
document.querySelectorAll('.section-tab').forEach(tab => {
    tab.addEventListener('click', () => {
        document.querySelectorAll('.section-tab').forEach(t => t.classList.remove('active'));
        document.querySelectorAll('.section-content').forEach(s => s.classList.add('hidden'));
        tab.classList.add('active');
        const sec = document.getElementById('section-' + tab.dataset.section);
        if (sec) {
            sec.classList.remove('hidden');
            if (tab.dataset.section === 'preferences') loadPreferences();
            if (tab.dataset.section === 'log') loadLog(1);
        }
    });
});

// ─────────────────────────────────────────────────────────────────────────────
//  SEND ANNOUNCEMENT
// ─────────────────────────────────────────────────────────────────────────────
document.getElementById('btnSendAnnouncement').addEventListener('click', async () => {
    const title    = document.getElementById('ann-title').value.trim();
    const message  = document.getElementById('ann-message').value.trim();
    const delivery = document.getElementById('ann-delivery').value;

    if (!title || !message) { toast('Title and message are required.', 'error'); return; }

    const btn     = document.getElementById('btnSendAnnouncement');
    const spinner = document.getElementById('ann-sending');
    btn.disabled  = true; spinner.classList.remove('hidden');

    try {
        const { ok, data } = await api('/admin/notifications/send-announcement', 'POST', { title, message, delivery_method: delivery });
        if (ok && data.success) {
            toast(`✓ ${data.message}`, 'success');
            document.getElementById('ann-title').value   = '';
            document.getElementById('ann-message').value = '';
        } else {
            toast(data.message ?? 'Failed to send announcement.', 'error');
        }
    } catch (e) {
        toast('Network error. Please try again.', 'error');
    } finally {
        btn.disabled = false; spinner.classList.add('hidden');
    }
});

// ─────────────────────────────────────────────────────────────────────────────
//  TARGETED – user search & tags
// ─────────────────────────────────────────────────────────────────────────────
let selectedUsers = {}; // { user_id: { username, email } }
let searchTimeout = null;

const searchInput    = document.getElementById('userSearchInput');
const dropdown       = document.getElementById('userSearchDropdown');
const tagsContainer  = document.getElementById('selectedUsersContainer');

function renderTags() {
    tagsContainer.innerHTML = '';
    const ids = Object.keys(selectedUsers);
    document.getElementById('target-user-ids').value = ids.join(',');
    ids.forEach(id => {
        const u   = selectedUsers[id];
        const tag = document.createElement('div');
        tag.className = 'user-tag';
        tag.innerHTML = `<span>${escHtml(u.username)}</span><button data-id="${id}" title="Remove">×</button>`;
        tag.querySelector('button').addEventListener('click', () => {
            delete selectedUsers[id];
            renderTags();
        });
        tagsContainer.appendChild(tag);
    });
}

function escHtml(str) {
    const d = document.createElement('div');
    d.textContent = str;
    return d.innerHTML;
}

searchInput.addEventListener('input', () => {
    clearTimeout(searchTimeout);
    const q = searchInput.value.trim();
    if (q.length < 2) { dropdown.classList.add('hidden'); return; }
    searchTimeout = setTimeout(() => fetchUsers(q), 280);
});

searchInput.addEventListener('focus', () => {
    if (searchInput.value.trim().length >= 2) dropdown.classList.remove('hidden');
});

document.addEventListener('click', e => {
    if (!searchInput.contains(e.target) && !dropdown.contains(e.target)) {
        dropdown.classList.add('hidden');
    }
});

document.addEventListener('touchstart', e => {
    if (!searchInput.contains(e.target) && !dropdown.contains(e.target)) {
        dropdown.classList.add('hidden');
    }
});

async function fetchUsers(q) {
    try {
        const res  = await fetch(`/admin/notifications/users?search=${encodeURIComponent(q)}`, {
            headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': CSRF },
            credentials: 'same-origin'
        });
        const data = await res.json();
        if (!data.success) return;
        renderDropdown(data.data);
    } catch (e) {}
}

function renderDropdown(users) {
    dropdown.innerHTML = '';
    if (!users.length) {
        dropdown.innerHTML = '<div class="user-option text-gray-400">No users found.</div>';
        dropdown.classList.remove('hidden');
        return;
    }
    users.forEach(u => {
        const el = document.createElement('div');
        el.className = 'user-option';
        el.setAttribute('data-user-id', u.user_id);
        el.setAttribute('data-username', u.username);
        el.setAttribute('data-email', u.email);
        el.innerHTML = `<div class="font-semibold text-gray-800">${escHtml(u.username)} <span class="badge badge-all ml-1">${escHtml(u.user_type)}</span></div>
                        <div class="meta">${escHtml(u.email)}</div>`;
        // Use mousedown + preventDefault to fire BEFORE the input blur hides the dropdown
        el.addEventListener('mousedown', (e) => {
            e.preventDefault(); // prevent input from losing focus and closing dropdown early
            const uid      = el.getAttribute('data-user-id');
            const uname    = el.getAttribute('data-username');
            const uemail   = el.getAttribute('data-email');
            selectedUsers[uid] = { username: uname, email: uemail };
            renderTags();
            dropdown.classList.add('hidden');
            searchInput.value = '';
        });
        dropdown.appendChild(el);
    });
    dropdown.classList.remove('hidden');
}

// ── Send targeted ─────────────────────────────────────────────────────────
document.getElementById('btnSendTargeted').addEventListener('click', async () => {
    const userIds = Object.keys(selectedUsers).map(Number);
    if (!userIds.length) { toast('Please select at least one recipient.', 'error'); return; }

    const title    = document.getElementById('tgt-title').value.trim();
    const message  = document.getElementById('tgt-message').value.trim();
    const delivery = document.getElementById('tgt-delivery').value;

    if (!title || !message) { toast('Title and message are required.', 'error'); return; }

    const btn     = document.getElementById('btnSendTargeted');
    const spinner = document.getElementById('tgt-sending');
    btn.disabled  = true; spinner.classList.remove('hidden');

    try {
        const { ok, data } = await api('/admin/notifications/send-targeted', 'POST', {
            user_ids: userIds, title, message, delivery_method: delivery
        });
        if (ok && data.success) {
            toast(`✓ ${data.message}`, 'success');
            selectedUsers = {};
            renderTags();
            document.getElementById('tgt-title').value   = '';
            document.getElementById('tgt-message').value = '';
        } else {
            toast(data.message ?? 'Failed to send notification.', 'error');
        }
    } catch (e) {
        toast('Network error. Please try again.', 'error');
    } finally {
        btn.disabled = false; spinner.classList.add('hidden');
    }
});

// ─────────────────────────────────────────────────────────────────────────────
//  PREFERENCES
// ─────────────────────────────────────────────────────────────────────────────
let prefsDirty = false;

async function loadPreferences() {
    try {
        const { ok, data } = await api('/admin/notifications/preferences');
        if (!ok || !data.success) return;
        const prefs = data.data;
        document.querySelectorAll('.setting-toggle').forEach(toggle => {
            const key = toggle.dataset.setting;
            if (key in prefs) toggle.checked = !!prefs[key];
        });
        prefsDirty = false;
        updateSaveBar();
    } catch (e) {}
}

document.querySelectorAll('.setting-toggle').forEach(toggle => {
    toggle.addEventListener('change', () => {
        prefsDirty = true;
        updateSaveBar();
    });
});

function updateSaveBar() {
    const bar = document.getElementById('saveBar');
    if (prefsDirty) bar.classList.remove('hidden');
    else            bar.classList.add('hidden');
}

document.getElementById('saveSettingsBtn').addEventListener('click', async () => {
    const settings = {};
    document.querySelectorAll('.setting-toggle').forEach(t => {
        settings[t.dataset.setting] = t.checked;
    });
    try {
        const { ok, data } = await api('/admin/notifications/preferences', 'POST', { settings });
        if (ok && data.success) {
            toast('Preferences saved!', 'success');
            prefsDirty = false;
            updateSaveBar();
        } else {
            toast('Failed to save preferences.', 'error');
        }
    } catch (e) {
        toast('Network error.', 'error');
    }
});

document.getElementById('resetDefaultsBtn').addEventListener('click', () => {
    document.querySelectorAll('.setting-toggle').forEach(t => { t.checked = true; });
    prefsDirty = true;
    updateSaveBar();
});

// ─────────────────────────────────────────────────────────────────────────────
//  SENT LOG
// ─────────────────────────────────────────────────────────────────────────────
let logPage = 1;
let logLastPage = 1;

async function loadLog(page = 1) {
    logPage = page;
    const typeFilter     = document.getElementById('logFilterType').value;
    const deliveryFilter = document.getElementById('logFilterDelivery').value;

    document.getElementById('sentLogBody').innerHTML =
        '<tr><td colspan="5" class="text-center py-8 text-gray-400 text-sm"><i class="fi fi-rr-spinner animate-spin mr-1"></i>Loading…</td></tr>';

    try {
        let url = `/admin/notifications/sent-log?page=${page}&per_page=20`;
        const { ok, data } = await api(url);
        if (!ok || !data.success) throw new Error();

        const { notifications, total, current_page, last_page } = data.data;
        logLastPage = last_page;

        // Client-side filter (server returns all for this admin — lightweight)
        const filtered = notifications.filter(n => {
            if (typeFilter     && n.target_type     !== typeFilter)     return false;
            if (deliveryFilter && n.delivery_method !== deliveryFilter) return false;
            return true;
        });

        document.getElementById('logTotalBadge').textContent = `${total} total`;
        document.getElementById('logPageInfo').textContent   = `Page ${current_page} of ${last_page}`;
        document.getElementById('logPrevBtn').disabled       = current_page <= 1;
        document.getElementById('logNextBtn').disabled       = current_page >= last_page;

        const tbody = document.getElementById('sentLogBody');
        if (!filtered.length) {
            tbody.innerHTML = '<tr><td colspan="5" class="text-center py-8 text-gray-400 text-sm">No notifications found.</td></tr>';
            return;
        }

        tbody.innerHTML = filtered.map(n => {
            const delivBadge = { 'in-app':'badge-inapp', email:'badge-email', both:'badge-both' }[n.delivery_method] ?? '';
            const typeBadge  = n.target_type === 'all' ? 'badge-all' : 'badge-target';
            const typeLabel  = n.target_type === 'all' ? 'Mass' : 'Targeted';
            const date       = new Date(n.sent_at.replace(' ','T')).toLocaleString('en-US',{month:'short',day:'numeric',year:'numeric',hour:'2-digit',minute:'2-digit'});
            const shortMsg   = n.message.length > 80 ? n.message.slice(0,80)+'…' : n.message;
            return `<tr>
              <td>
                <div class="font-semibold text-gray-900 text-sm">${escHtml(n.title)}</div>
                <div class="text-gray-500 mt-0.5">${escHtml(shortMsg)}</div>
              </td>
              <td><span class="badge ${typeBadge}">${typeLabel}</span></td>
              <td><span class="badge ${delivBadge}">${escHtml(n.delivery_method)}</span></td>
              <td class="font-semibold text-gray-700">${n.recipient_count}</td>
              <td class="text-gray-500 whitespace-nowrap">${date}</td>
            </tr>`;
        }).join('');

    } catch (e) {
        document.getElementById('sentLogBody').innerHTML =
            '<tr><td colspan="5" class="text-center py-8 text-red-400 text-sm">Failed to load. Please refresh.</td></tr>';
    }
}

document.getElementById('refreshLog').addEventListener('click', () => loadLog(logPage));
document.getElementById('logPrevBtn').addEventListener('click', () => { if (logPage > 1) loadLog(logPage - 1); });
document.getElementById('logNextBtn').addEventListener('click', () => { if (logPage < logLastPage) loadLog(logPage + 1); });
document.getElementById('logFilterType').addEventListener('change', () => loadLog(1));
document.getElementById('logFilterDelivery').addEventListener('change', () => loadLog(1));

// ─────────────────────────────────────────────────────────────────────────────
//  USER ACTIVITY FEED
// ─────────────────────────────────────────────────────────────────────────────
let actPage     = 1;
let actLastPage = 1;
let actSearchTO = null;

// Activity type config: { label, icon (flaticon class), cssClass, viewUrl(row) }
const ACT_CONFIG = {
    user_registered:        { label:'New User Registration',         icon:'fi-rr-user-add',        css:'act-registered',   url: (r) => `/admin/user-management/view/${r.user_id}` },
    failed_login_attempt:   { label:'Failed Login Attempt',          icon:'fi-rr-shield-exclamation',css:'act-failed-login', url: (r) => `/admin/user-management/view/${r.user_id}` },
    project_reported:       { label:'Project Reported',              icon:'fi-rr-flag',             css:'act-reported',     url: (r) => r.subject_id ? `/admin/disputes/view/${r.subject_id}` : `/admin/disputes` },
    profile_updated:        { label:'Profile Updated',               icon:'fi-rr-user-pen',         css:'act-profile',      url: (r) => `/admin/user-management/view/${r.user_id}` },
    password_reset:         { label:'Password Reset Requested',      icon:'fi-rr-lock',             css:'act-password',     url: (r) => `/admin/user-management/view/${r.user_id}` },
    email_verified:         { label:'Email Verified',                icon:'fi-rr-envelope-check',   css:'act-email-ver',    url: (r) => `/admin/user-management/view/${r.user_id}` },
    account_status_changed: { label:'Account Suspended/Unsuspended', icon:'fi-rr-user-slash',       css:'act-suspended',    url: (r) => `/admin/user-management/view/${r.user_id}` },
};

async function loadActivity(page = 1) {
    actPage = page;
    const type   = document.getElementById('actFilterType').value;
    const isRead = document.getElementById('actFilterRead').value;
    const search = document.getElementById('actSearch').value.trim();

    const tbody = document.getElementById('activityTableBody');
    tbody.innerHTML = '<tr><td colspan="6" class="text-center py-10 text-gray-400 text-sm"><i class="fi fi-rr-spinner animate-spin mr-1"></i>Loading…</td></tr>';

    try {
        let url = `/admin/notifications/activity?page=${page}&per_page=20`;
        if (type)   url += `&type=${encodeURIComponent(type)}`;
        if (isRead !== '') url += `&is_read=${encodeURIComponent(isRead)}`;
        if (search) url += `&search=${encodeURIComponent(search)}`;

        const { ok, data } = await api(url);
        if (!ok || !data.success) throw new Error();

        const { activities, total, current_page, last_page, unread_count } = data.data;
        actLastPage = last_page;

        // Update unread badge on tab
        const badge = document.getElementById('activityUnreadBadge');
        if (unread_count > 0) {
            badge.textContent = unread_count > 99 ? '99+' : unread_count;
            badge.classList.remove('hidden');
        } else {
            badge.classList.add('hidden');
        }

        document.getElementById('actTotalBadge').textContent   = `${total} total · ${unread_count} unread`;
        document.getElementById('actPageInfo').textContent      = `Page ${current_page} of ${last_page}`;
        document.getElementById('actPrevBtn').disabled          = current_page <= 1;
        document.getElementById('actNextBtn').disabled          = current_page >= last_page;

        if (!activities.length) {
            tbody.innerHTML = '<tr><td colspan="6" class="text-center py-10 text-gray-400 text-sm">No activity found.</td></tr>';
            return;
        }

        tbody.innerHTML = activities.map(row => {
            const cfg      = ACT_CONFIG[row.activity_type] ?? { label: row.activity_type, icon:'fi-rr-bell', css:'badge-all', url:() => '#' };
            const unread   = !row.is_read;
            const rowClass = unread ? 'act-row-unread' : '';
            const dot      = unread ? '<span class="act-dot"></span>' : '';

            // Human-readable details from meta
            const meta     = row.meta ?? {};
            let details    = '';
            if (row.activity_type === 'failed_login_attempt' && meta.attempts) {
                details = `<span class="text-red-500 font-semibold">${escHtml(String(meta.attempts))} attempts</span>` + (meta.ip ? ` from <code class="text-xs bg-gray-100 px-1 rounded">${escHtml(meta.ip)}</code>` : '');
            } else if (row.activity_type === 'account_status_changed') {
                const ns = meta.new_status ?? meta.status ?? '';
                const isSuspended = ['suspended','inactive'].includes(ns.toLowerCase());
                details = ns ? `Status set to <span class="font-semibold ${isSuspended ? 'text-red-600' : 'text-green-600'}">${escHtml(ns)}</span>` + (meta.reason ? ` — ${escHtml(meta.reason)}` : '') : '';
            } else if (row.activity_type === 'project_reported' && row.subject_id) {
                details = `Dispute / Project ID: <strong>#${escHtml(String(row.subject_id))}</strong>`;
            } else if (row.activity_type === 'password_reset' && meta.stage) {
                details = `Stage: <span class="font-semibold">${escHtml(meta.stage)}</span>`;
            } else if (meta.field) {
                details = `Field: <span class="font-semibold">${escHtml(meta.field)}</span>`;
            }

            const viewUrl  = cfg.url(row);
            const username = row.username ? escHtml(row.username) : '<span class="text-gray-400 italic">Unknown</span>';
            const email    = row.email    ? `<div class="text-xs text-gray-400">${escHtml(row.email)}</div>` : '';
            const userType = row.user_type ? `<span class="badge badge-all ml-1 text-[10px]">${escHtml(row.user_type)}</span>` : '';

            const dateObj  = new Date((row.created_at ?? '').replace(' ', 'T'));
            const dateStr  = isNaN(dateObj) ? row.created_at : dateObj.toLocaleString('en-US', { month:'short', day:'numeric', year:'numeric', hour:'2-digit', minute:'2-digit' });

            return `<tr class="${rowClass}" data-id="${row.id}">
              <td class="text-center">${dot}</td>
              <td>
                <span class="act-type-badge ${cfg.css}">
                  <i class="fi ${cfg.icon}"></i>
                  ${escHtml(cfg.label)}
                </span>
              </td>
              <td>
                <div class="font-semibold text-gray-800 text-sm">${username}${userType}</div>
                ${email}
              </td>
              <td class="text-sm text-gray-600">${details || '<span class="text-gray-300">—</span>'}</td>
              <td class="text-gray-500 text-xs whitespace-nowrap">${escHtml(dateStr)}</td>
                <td>
                  {{-- <a href="${escHtml(viewUrl)}" class="act-view-btn">
                    <i class="fi fi-rr-eye"></i> View
                  </a> --}}
                </td>
            </tr>`;
        }).join('');

        // Mark rows as read when rendered (auto-mark visible rows)
        const unreadIds = activities.filter(r => !r.is_read).map(r => r.id);
        if (unreadIds.length) {
            api('/admin/notifications/activity/mark-read', 'POST', { ids: unreadIds }).catch(() => {});
        }

    } catch (e) {
        tbody.innerHTML = '<tr><td colspan="6" class="text-center py-10 text-red-400 text-sm">Failed to load activities. Please refresh.</td></tr>';
    }
}

// Mark ALL as read
document.getElementById('actMarkAllRead').addEventListener('click', async () => {
    try {
        await api('/admin/notifications/activity/mark-read', 'POST', {});
        toast('All activity marked as read.', 'success');
        loadActivity(actPage);
    } catch(e) { toast('Failed to mark as read.', 'error'); }
});

document.getElementById('actRefresh').addEventListener('click', () => loadActivity(actPage));
document.getElementById('actPrevBtn').addEventListener('click', () => { if (actPage > 1) loadActivity(actPage - 1); });
document.getElementById('actNextBtn').addEventListener('click', () => { if (actPage < actLastPage) loadActivity(actPage + 1); });
document.getElementById('actFilterType').addEventListener('change', () => loadActivity(1));
document.getElementById('actFilterRead').addEventListener('change', () => loadActivity(1));
document.getElementById('actSearch').addEventListener('input', () => {
    clearTimeout(actSearchTO);
    actSearchTO = setTimeout(() => loadActivity(1), 320);
});

// Hook into tab switching for activity
// (override tab switch handler to also handle 'activity')
document.querySelectorAll('.section-tab').forEach(tab => {
    tab.addEventListener('click', () => {
        if (tab.dataset.section === 'activity') loadActivity(1);
    });
});
</script>
</body>
</html>