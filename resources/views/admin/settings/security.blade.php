<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <title>Security Settings - Legatura</title>
  <link rel="icon" type="image/svg+xml" href="{{ asset('img/logo2.0-favicon.svg') }}">

  <script src="https://cdn.tailwindcss.com"></script>
  <link rel="stylesheet" href="{{ asset('css/admin/home/mainComponents.css') }}">
  <link rel="stylesheet" href="https://cdn-uicons.flaticon.com/3.0.0/uicons-solid-straight/css/uicons-solid-straight.css">
  <link rel="stylesheet" href="https://cdn-uicons.flaticon.com/3.0.0/uicons-solid-rounded/css/uicons-solid-rounded.css">
  <link rel="stylesheet" href="https://cdn-uicons.flaticon.com/3.0.0/uicons-bold-rounded/css/uicons-bold-rounded.css">
  <link rel="stylesheet" href="https://cdn-uicons.flaticon.com/3.0.0/uicons-regular-rounded/css/uicons-regular-rounded.css">
  <script src="{{ asset('js/admin/home/mainComponents.js') }}" defer></script>
  <script src="{{ asset('js/admin/settings/security.js') }}" defer></script>

  <style>
    :root {
      --sec-blue-50: #eff6ff;
      --sec-blue-100: #dbeafe;
      --sec-blue-200: #bfdbfe;
      --sec-blue-600: #2563eb;
      --sec-blue-700: #1d4ed8;
      --sec-slate-200: #e2e8f0;
      --sec-slate-500: #64748b;
      --sec-slate-700: #334155;
    }

    .date-pill input[type="date"]::-webkit-calendar-picker-indicator {
      opacity: 0.5;
      cursor: pointer;
      filter: invert(30%) sepia(80%) saturate(400%) hue-rotate(210deg);
    }

    .date-pill input[type="date"]::-webkit-calendar-picker-indicator:hover {
      opacity: 1;
    }

    #toast {
      position: fixed;
      top: 80px;
      right: 16px;
      padding:.75rem 1.25rem; border-radius:.75rem;
      font-size:.875rem; font-weight:500;
      box-shadow:0 4px 20px rgba(0,0,0,.15);
      z-index:9999; opacity:0; transform:translateX(16px);
      transition:opacity .28s ease, transform .28s ease; pointer-events:none;
    }
    #toast.show    { opacity:1; transform:translateX(0); }
    #toast.success { background:#dcfce7; color:#166534; border:1px solid #bbf7d0; }
    #toast.error   { background:#fee2e2; color:#991b1b; border:1px solid #fecaca; }
    #toast.warn    { background:#fef9c3; color:#713f12; border:1px solid #fde68a; }

    .modal-backdrop { display:none; position:fixed; inset:0; background:rgba(2, 6, 23, .48); backdrop-filter: blur(2px); z-index:1000; align-items:center; justify-content:center; }
    .modal-backdrop.open { display:flex; }

    .modal-surface {
      border: 1px solid var(--sec-blue-100);
      border-radius: 1rem;
      background: #ffffff;
      box-shadow: 0 24px 52px rgba(15, 23, 42, .24);
    }

    .modal-head {
      background: linear-gradient(135deg, #2563eb 0%, #1d4ed8 58%, #1e40af 100%);
      color: #ffffff;
      border-bottom: 1px solid rgba(255, 255, 255, .2);
    }

    .modal-head-icon {
      width: 34px;
      height: 34px;
      border-radius: 10px;
      display: inline-flex;
      align-items: center;
      justify-content: center;
      background: rgba(255, 255, 255, .16);
      border: 1px solid rgba(255, 255, 255, .28);
      flex-shrink: 0;
    }

    .modal-head-sub {
      color: rgba(219, 234, 254, .95);
      font-size: .69rem;
      line-height: 1.2;
    }

    .modal-close-light {
      color: rgba(255, 255, 255, .9);
      background: transparent;
    }

    .modal-close-light:hover {
      background: rgba(255, 255, 255, .16);
      color: #ffffff;
    }

    .modal-body-soft {
      background: linear-gradient(180deg, #ffffff 0%, #f8fbff 100%);
    }

    .modal-callout {
      border: 1px solid var(--sec-blue-100);
      background: linear-gradient(180deg, #f8fbff 0%, #eef5ff 100%);
      border-radius: .85rem;
      padding: .85rem .95rem;
    }

    .modal-footer-soft {
      border-top: 1px solid var(--sec-blue-100);
      background: #fbfdff;
    }

    .member-profile-banner {
      border: 1px solid var(--sec-blue-100);
      background: linear-gradient(135deg, #f9fbff 0%, #eef5ff 65%, #ffffff 100%);
      border-radius: .85rem;
      padding: .85rem .95rem;
    }

    .member-log-shell {
      border: 1px solid var(--sec-blue-100);
      border-radius: .85rem;
      overflow: hidden;
      background: #ffffff;
    }

    .member-log-table thead th {
      background: linear-gradient(180deg, #f8fbff 0%, #eef5ff 100%);
      color: #1e40af;
      border-bottom: 1px solid var(--sec-blue-100);
      text-transform: uppercase;
      letter-spacing: .05em;
      font-size: .66rem;
    }

    .security-top-shell {
      border: 1px solid var(--sec-blue-100);
      border-radius: 14px;
      overflow: hidden;
      background: linear-gradient(180deg, #fbfdff 0%, #f5f9ff 100%);
      box-shadow: 0 6px 18px rgba(15, 23, 42, .06);
    }

    .security-top-head {
      padding: .85rem 1rem;
      border-bottom: 1px solid var(--sec-blue-100);
      background: linear-gradient(135deg, #eff6ff 0%, #f8fbff 70%);
    }

    .security-top-tabs {
      padding: .58rem .7rem .68rem;
    }

    .security-top-shell .security-tab-shell {
      border: 0;
      border-radius: 0;
      background: transparent;
      box-shadow: none;
      padding: 0;
    }

    .security-tab-shell {
      display: flex;
      gap: 8px;
      flex-wrap: wrap;
      padding: 6px;
      border: 1px solid var(--sec-blue-100);
      border-radius: 12px;
      background: linear-gradient(180deg, #f8fbff 0%, var(--sec-blue-50) 100%);
      box-shadow: 0 1px 3px rgba(15, 23, 42, .06);
    }

    .tab-btn {
      display: inline-flex;
      align-items: center;
      gap: 8px;
      padding: 8px 14px;
      font-size: .82rem;
      font-weight: 700;
      border-radius: 9px;
      color: #475569;
      white-space: nowrap;
      transition: background-color .28s ease, color .28s ease, border-color .28s ease, box-shadow .28s ease, transform .28s ease;
      background: transparent;
      border: 1px solid transparent;
      cursor: pointer;
    }

    .tab-btn i {
      width: 24px;
      height: 24px;
      border-radius: 7px;
      display: inline-flex;
      align-items: center;
      justify-content: center;
      background: #ffffff;
      border: 1px solid var(--sec-slate-200);
      color: #64748b;
      font-size: .76rem;
      transition: inherit;
      margin-right: 0 !important;
    }

    .tab-btn:hover:not(.active) {
      background: #ffffff;
      color: #1e3a8a;
      border-color: var(--sec-blue-200);
      box-shadow: 0 2px 8px rgba(37, 99, 235, .12);
      transform: translateY(-1px);
    }

    .tab-btn:hover:not(.active) i {
      background: var(--sec-blue-50);
      color: var(--sec-blue-600);
      border-color: var(--sec-blue-200);
    }

    .tab-btn.active {
      background: var(--sec-blue-600);
      color: #ffffff;
      border-color: var(--sec-blue-700);
      box-shadow: 0 6px 14px rgba(37, 99, 235, .28);
    }

    .tab-btn.active i {
      background: rgba(255, 255, 255, .16);
      color: #ffffff;
      border-color: rgba(255, 255, 255, .28);
    }

    .tab-btn:focus-visible {
      outline: none;
      box-shadow: 0 0 0 3px rgba(59, 130, 246, .28);
    }

    .tab-panel { display:none; }
    .tab-panel.active { display:block; }

    #tab-profile > .bg-white.rounded-2xl.shadow.border,
    #tab-profile > .bg-white.rounded-2xl.shadow.border.border-red-100,
    #tab-members > .bg-white.rounded-2xl.shadow.border,
    #tab-team > .bg-white.rounded-2xl.shadow.border {
      border-color: var(--sec-slate-200);
      box-shadow: 0 1px 3px rgba(15, 23, 42, .06);
      transition: box-shadow .32s cubic-bezier(0.22, 1, 0.36, 1), transform .32s cubic-bezier(0.22, 1, 0.36, 1);
    }

    #tab-profile > .bg-white.rounded-2xl.shadow.border:hover,
    #tab-profile > .bg-white.rounded-2xl.shadow.border.border-red-100:hover,
    #tab-members > .bg-white.rounded-2xl.shadow.border:hover,
    #tab-team > .bg-white.rounded-2xl.shadow.border:hover {
      transform: translateY(-2px);
      box-shadow: 0 10px 24px rgba(2, 38, 68, .10);
    }

    #tab-profile input:not([type="checkbox"]),
    #tab-profile select,
    #tab-members input,
    #tab-members select,
    #tab-team input,
    #tab-team select,
    #profileForm input,
    #createAdminForm input,
    #memberEditForm input {
      border-color: var(--sec-slate-200);
      border-radius: .65rem;
    }

    #tab-profile input:focus,
    #tab-profile select:focus,
    #tab-members input:focus,
    #tab-members select:focus,
    #tab-team input:focus,
    #tab-team select:focus,
    #profileForm input:focus,
    #createAdminForm input:focus,
    #memberEditForm input:focus {
      outline: none;
      border-color: #93c5fd;
      box-shadow: 0 0 0 3px rgba(191, 219, 254, .75);
    }

    #openEditBtn,
    #openCreateAdminBtn,
    #saveProfileBtn,
    #createAdminSubmitBtn,
    #memberEditSaveBtn,
    #passwordForm button[type="submit"] {
      background: var(--sec-blue-600) !important;
      color: #fff !important;
      border: 1px solid var(--sec-blue-600);
    }

    #openEditBtn:hover,
    #openCreateAdminBtn:hover,
    #saveProfileBtn:hover,
    #createAdminSubmitBtn:hover,
    #memberEditSaveBtn:hover,
    #passwordForm button[type="submit"]:hover {
      background: var(--sec-blue-700) !important;
      border-color: var(--sec-blue-700);
    }

    #refreshTeamLogBtn {
      display: inline-flex;
      align-items: center;
      gap: .35rem;
      color: var(--sec-blue-700);
      background: #ffffff;
      border: 1px solid var(--sec-blue-200);
      border-radius: .55rem;
      padding: .42rem .72rem;
      font-size: .68rem;
      font-weight: 700;
      line-height: 1;
    }

    #refreshTeamLogBtn:hover {
      color: #1e3a8a;
      background: var(--sec-blue-50);
      border-color: var(--sec-blue-300, #93c5fd);
    }

    .view-member-btn {
      background: var(--sec-blue-50) !important;
      color: var(--sec-blue-700) !important;
      border: 1px solid var(--sec-blue-200) !important;
    }

    .view-member-btn:hover {
      background: var(--sec-blue-100) !important;
    }

    #memberModalAvatar {
      background: var(--sec-blue-600) !important;
    }

    #profileAvatar,
    #modalAvatar {
      border-color: var(--sec-blue-100) !important;
    }

    #tab-profile table thead,
    #tab-members table thead,
    #tab-team table thead,
    #memberModal table thead {
      background: var(--sec-blue-50) !important;
      color: #1e40af !important;
      border-bottom: 1px solid var(--sec-blue-100) !important;
    }

    #tab-profile table tbody tr:hover td,
    #tab-members table tbody tr:hover td,
    #tab-team table tbody tr:hover td,
    #memberModal table tbody tr:hover td {
      background: #f8fbff;
    }

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

    .field-label { display:block; font-size:.7rem; font-weight:700; text-transform:uppercase; letter-spacing:.05em; color:#94a3b8; margin-bottom:.25rem; }
    .field-value { background:#f9fbff; border:1px solid #e2e8f0; border-radius:.65rem; padding:.55rem 1rem; color:#374151; font-size:.875rem; min-height:2.5rem; }

    .activity-log-card {
      border-color: var(--sec-blue-100) !important;
      box-shadow: 0 8px 22px rgba(2, 38, 68, .08) !important;
    }

    .activity-log-head {
      background: linear-gradient(135deg, #f9fbff 0%, #eef5ff 60%, #ffffff 100%);
    }

    .activity-log-count {
      background: #eef4ff;
      color: #1e40af;
      border: 1px solid var(--sec-blue-200);
      border-radius: 9999px;
      font-size: .68rem;
      font-weight: 700;
      letter-spacing: .01em;
      padding: .28rem .7rem;
      line-height: 1;
      text-transform: uppercase;
    }

    .activity-log-filter-grid {
      display: grid;
      grid-template-columns: repeat(12, minmax(0, 1fr));
      gap: .6rem;
      align-items: end;
    }

    .activity-log-filter-field {
      display: flex;
      flex-direction: column;
      gap: .25rem;
      min-width: 0;
    }

    .activity-log-filter-field label {
      font-size: .66rem;
      font-weight: 700;
      text-transform: uppercase;
      letter-spacing: .05em;
      color: #64748b;
    }

    .activity-log-filter-field input,
    .activity-log-filter-field select {
      width: 100%;
      border: 1px solid var(--sec-slate-200);
      border-radius: .55rem;
      background: #ffffff;
      color: #475569;
      font-size: .72rem;
      padding: .46rem .58rem;
      line-height: 1.2;
    }

    .activity-log-filter-field input:focus,
    .activity-log-filter-field select:focus {
      outline: none;
      border-color: #93c5fd;
      box-shadow: 0 0 0 3px rgba(191, 219, 254, .75);
    }

    .activity-log-from { grid-column: span 2 / span 2; }
    .activity-log-to { grid-column: span 2 / span 2; }
    .activity-log-action { grid-column: span 3 / span 3; }
    .activity-log-search { grid-column: span 4 / span 4; }
    .activity-log-reset-wrap {
      grid-column: span 1 / span 1;
      display: flex;
      justify-content: flex-end;
    }

    .activity-log-reset-btn {
      border: 1px solid var(--sec-blue-200);
      background: #ffffff;
      color: var(--sec-blue-700);
      border-radius: .55rem;
      font-size: .68rem;
      font-weight: 700;
      padding: .43rem .7rem;
      transition: all .2s ease;
      white-space: nowrap;
    }

    .activity-log-reset-btn:hover {
      background: var(--sec-blue-50);
      color: #1e3a8a;
      border-color: var(--sec-blue-300, #93c5fd);
    }

    .activity-log-table thead th {
      background: linear-gradient(180deg, #f8fbff 0%, #eef5ff 100%);
      color: #1e40af;
      border-bottom: 1px solid var(--sec-blue-100);
      text-transform: uppercase;
      letter-spacing: .05em;
      font-size: .68rem;
    }

    .activity-log-table tbody tr {
      border-top: 1px solid #eff6ff;
      transition: background-color .2s ease;
    }

    .activity-log-table tbody td {
      padding: .72rem .9rem;
      font-size: .74rem;
      color: #475569;
      vertical-align: top;
    }

    .activity-log-pagination-meta {
      font-size: .69rem;
      color: #64748b;
    }

    .activity-log-nav-btn {
      padding: .38rem .66rem;
      border-radius: .5rem;
      border: 1px solid #dbe3ef;
      background: #ffffff;
      color: #475569;
      font-size: .7rem;
      font-weight: 600;
      transition: all .2s ease;
    }

    .activity-log-nav-btn:hover {
      background: #f8fbff;
      border-color: #c6d8fb;
      color: #1e3a8a;
    }

    .activity-log-nav-btn:disabled {
      opacity: .45;
      cursor: not-allowed;
    }

    .members-card {
      border-color: var(--sec-blue-100) !important;
      box-shadow: 0 8px 22px rgba(2, 38, 68, .08) !important;
    }

    .members-head {
      background: linear-gradient(135deg, #f9fbff 0%, #eef5ff 60%, #ffffff 100%);
    }

    .members-count-badge {
      background: #eef4ff;
      color: #1e40af;
      border: 1px solid var(--sec-blue-200);
      border-radius: 9999px;
      font-size: .68rem;
      font-weight: 700;
      letter-spacing: .01em;
      padding: .28rem .7rem;
      line-height: 1;
      text-transform: uppercase;
      white-space: nowrap;
    }

    .members-table thead th {
      background: linear-gradient(180deg, #f8fbff 0%, #eef5ff 100%);
      color: #1e40af;
      border-bottom: 1px solid var(--sec-blue-100);
      text-transform: uppercase;
      letter-spacing: .05em;
      font-size: .68rem;
    }

    .members-table tbody tr {
      border-top: 1px solid #eff6ff;
      transition: background-color .2s ease;
    }

    .members-table tbody tr:hover {
      background: #f8fbff;
    }

    .members-table tbody td {
      padding: .72rem .9rem;
      font-size: .74rem;
      color: #475569;
      vertical-align: middle;
    }

    .delete-member-btn {
      background: #fff1f2 !important;
      color: #be123c !important;
      border: 1px solid #fecdd3 !important;
      border-radius: .55rem;
      transition: all .2s ease;
      font-size: .7rem;
      font-weight: 600;
      padding: .38rem .7rem;
    }

    .delete-member-btn:hover {
      background: #ffe4e6 !important;
      color: #9f1239 !important;
      border-color: #fda4af !important;
    }

    .team-log-card {
      border-color: var(--sec-blue-100) !important;
      box-shadow: 0 8px 22px rgba(2, 38, 68, .08) !important;
    }

    .team-log-head {
      background: linear-gradient(135deg, #f9fbff 0%, #eef5ff 60%, #ffffff 100%);
    }

    .team-log-count {
      background: #eef4ff;
      color: #1e40af;
      border: 1px solid var(--sec-blue-200);
      border-radius: 9999px;
      font-size: .68rem;
      font-weight: 700;
      letter-spacing: .01em;
      padding: .28rem .7rem;
      line-height: 1;
      text-transform: uppercase;
      white-space: nowrap;
    }

    .team-log-filter-grid {
      display: grid;
      grid-template-columns: repeat(12, minmax(0, 1fr));
      gap: .6rem;
      align-items: end;
    }

    .team-log-filter-field {
      display: flex;
      flex-direction: column;
      gap: .25rem;
      min-width: 0;
    }

    .team-log-filter-field label {
      font-size: .66rem;
      font-weight: 700;
      text-transform: uppercase;
      letter-spacing: .05em;
      color: #64748b;
    }

    .team-log-filter-field input,
    .team-log-filter-field select {
      width: 100%;
      border: 1px solid var(--sec-slate-200);
      border-radius: .55rem;
      background: #ffffff;
      color: #475569;
      font-size: .72rem;
      padding: .46rem .58rem;
      line-height: 1.2;
    }

    .team-log-filter-field input:focus,
    .team-log-filter-field select:focus {
      outline: none;
      border-color: #93c5fd;
      box-shadow: 0 0 0 3px rgba(191, 219, 254, .75);
    }

    .team-log-from { grid-column: span 2 / span 2; }
    .team-log-to { grid-column: span 2 / span 2; }
    .team-log-action { grid-column: span 3 / span 3; }
    .team-log-search { grid-column: span 4 / span 4; }
    .team-log-reset-wrap {
      grid-column: span 1 / span 1;
      display: flex;
      justify-content: flex-end;
    }

    .team-log-reset-btn {
      border: 1px solid var(--sec-blue-200);
      background: #ffffff;
      color: var(--sec-blue-700);
      border-radius: .55rem;
      font-size: .68rem;
      font-weight: 700;
      padding: .43rem .7rem;
      transition: all .2s ease;
      white-space: nowrap;
    }

    .team-log-reset-btn:hover {
      background: var(--sec-blue-50);
      color: #1e3a8a;
      border-color: var(--sec-blue-300, #93c5fd);
    }

    .team-log-table thead th {
      background: linear-gradient(180deg, #f8fbff 0%, #eef5ff 100%);
      color: #1e40af;
      border-bottom: 1px solid var(--sec-blue-100);
      text-transform: uppercase;
      letter-spacing: .05em;
      font-size: .68rem;
    }

    .team-log-table tbody tr {
      border-top: 1px solid #eff6ff;
      transition: background-color .2s ease;
    }

    .team-log-table tbody tr:hover {
      background: #f8fbff;
    }

    .team-log-table tbody td {
      padding: .72rem .9rem;
      font-size: .74rem;
      color: #475569;
      vertical-align: top;
    }

    .team-log-pagination-meta {
      font-size: .69rem;
      color: #64748b;
    }

    .team-log-nav-btn {
      padding: .38rem .66rem;
      border-radius: .5rem;
      border: 1px solid #dbe3ef;
      background: #ffffff;
      color: #475569;
      font-size: .7rem;
      font-weight: 600;
      transition: all .2s ease;
    }

    .team-log-nav-btn:hover {
      background: #f8fbff;
      border-color: #c6d8fb;
      color: #1e3a8a;
    }

    .team-log-nav-btn:disabled {
      opacity: .45;
      cursor: not-allowed;
    }

    @media (max-width: 1024px) {
      .activity-log-from,
      .activity-log-to { grid-column: span 3 / span 3; }

      .activity-log-action { grid-column: span 3 / span 3; }

      .activity-log-search { grid-column: span 4 / span 4; }

      .activity-log-reset-wrap { grid-column: span 2 / span 2; }

      .team-log-from,
      .team-log-to { grid-column: span 3 / span 3; }

      .team-log-action { grid-column: span 3 / span 3; }

      .team-log-search { grid-column: span 4 / span 4; }

      .team-log-reset-wrap { grid-column: span 2 / span 2; }
    }

    @media (max-width: 768px) {
      .modal-head {
        padding: .9rem .95rem;
      }

      .modal-head-icon {
        width: 30px;
        height: 30px;
        border-radius: 9px;
      }

      .security-top-head {
        padding: .75rem .8rem;
      }

      .security-top-tabs {
        padding: .5rem .55rem .6rem;
      }

      .activity-log-from,
      .activity-log-to,
      .activity-log-action,
      .activity-log-search,
      .activity-log-reset-wrap {
        grid-column: span 12 / span 12;
      }

      .activity-log-reset-wrap {
        justify-content: stretch;
      }

      .activity-log-reset-btn {
        width: 100%;
      }

      .team-log-from,
      .team-log-to,
      .team-log-action,
      .team-log-search,
      .team-log-reset-wrap {
        grid-column: span 12 / span 12;
      }

      .team-log-reset-wrap {
        justify-content: stretch;
      }

      .team-log-reset-btn {
        width: 100%;
      }

      .members-head {
        padding-top: 1rem;
        padding-bottom: 1rem;
      }

      .members-actions-wrap {
        width: 100%;
        display: flex;
        justify-content: space-between;
        align-items: center;
      }

      #openCreateAdminBtn {
        white-space: nowrap;
      }

      .tab-btn {
        flex: 1 1 calc(50% - 8px);
        justify-content: center;
      }
    }

    /* Hide scrollbars inside modals */
    .modal-backdrop *::-webkit-scrollbar { display: none; }
    .modal-backdrop * { -ms-overflow-style: none; scrollbar-width: none; }
  </style>
</head>
<body class="bg-gray-50 text-gray-800 font-sans">
<div class="flex min-h-screen">
  @include('admin.layouts.sidebar')
  <main class="flex-1 h-screen overflow-y-auto overflow-x-hidden">
    @include('admin.layouts.topnav', ['pageTitle' => 'Security Settings'])
    <section class="px-6 py-6 space-y-6 max-w-screen-xl mx-auto">

      <div class="security-top-shell mb-8">
        <div class="security-top-head">
          <div class="flex items-start justify-between gap-3 flex-wrap">
            <div class="min-w-0">
              <div class="flex items-center gap-2.5">
                <div class="w-9 h-9 rounded-lg bg-blue-100 border border-blue-200 text-blue-600 flex items-center justify-center flex-shrink-0">
                  <i class="fi fi-rr-shield-check text-base"></i>
                </div>
                <div>
                  <h2 class="text-sm font-semibold text-gray-800 leading-tight">Security Center</h2>
                  <p class="text-[11px] text-gray-500 mt-0.5">Manage your profile, password, team members, and complete admin audit activity.</p>
                </div>
              </div>
            </div>
            <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full bg-white border border-blue-200 text-[11px] font-semibold text-blue-700">
              <i class="fi fi-rr-shield text-[10px]"></i> Security Hub
            </span>
          </div>
        </div>

        <div class="security-top-tabs">
          <div class="security-tab-shell">
            <button class="tab-btn active" data-tab="profile">
              <i class="fi fi-sr-user"></i>My Profile
            </button>
            <button class="tab-btn" data-tab="members">
              <i class="fi fi-sr-users-alt"></i>Add Members
            </button>
            <button class="tab-btn" data-tab="team">
              <i class="fi fi-sr-list-check"></i>Global Team Activity Tracking
            </button>
          </div>
        </div>
      </div>

      {{-- ===== TAB 1: MY PROFILE ===== --}}
      <div id="tab-profile" class="tab-panel active space-y-6">
        <div id="globalError" class="hidden bg-red-50 border border-red-200 text-red-700 text-sm rounded-xl px-4 py-3 flex items-start gap-2.5">
          <i class="fi fi-ss-exclamation mt-0.5 flex-shrink-0 text-sm"></i>
          <div>
            <p class="font-semibold">Failed to load account data</p>
            <p id="globalErrorMsg" class="text-xs text-red-600 mt-1 font-mono"></p>
          </div>
        </div>

        {{-- ACCOUNT INFORMATION + CHANGE PASSWORD --}}
        <div class="bg-white rounded-2xl shadow border p-5 space-y-6">
          <div class="space-y-4">
            <div class="flex items-start justify-between flex-wrap gap-3">
              <div class="min-w-0">
                <p class="text-[11px] font-semibold uppercase tracking-[0.08em] text-blue-700">Profile Workspace</p>
                <h2 class="text-base font-semibold text-gray-800 mt-0.5">Account Information</h2>
                <p class="text-xs text-gray-500 mt-1">View and maintain your admin identity details.</p>
              </div>
              <button id="openEditBtn" class="inline-flex items-center gap-1.5 bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-semibold px-4 py-1.5 rounded-md transition">
                <i class="fi fi-sr-pencil" style="font-size:13px"></i> Edit Profile
              </button>
            </div>
            <div id="profileSkeleton" class="flex gap-4 items-start animate-pulse">
              <div class="w-24 h-24 rounded-full bg-gray-200 flex-shrink-0"></div>
              <div class="flex-1 grid grid-cols-2 md:grid-cols-3 gap-3 mt-1">
                <div class="h-10 bg-gray-200 rounded-lg"></div>
                <div class="h-10 bg-gray-200 rounded-lg"></div>
                <div class="h-10 bg-gray-200 rounded-lg"></div>
                <div class="h-10 bg-gray-200 rounded-lg col-span-1"></div>
                <div class="h-10 bg-gray-200 rounded-lg col-span-1"></div>
              </div>
            </div>
            <div id="profileDisplay" class="hidden flex flex-col sm:flex-row gap-4 items-start">
              <div class="flex-shrink-0 text-center">
                <div class="relative inline-block">
                  <img id="profileAvatar" src="" class="w-24 h-24 rounded-full object-cover shadow border-2 border-indigo-100" alt="Avatar">
                </div>
                <p id="profileMemberSince" class="text-xs text-gray-400 mt-2"></p>
              </div>
              <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-3 gap-3 flex-1 w-full">
                <div><span class="field-label">First Name</span><div id="dispFirstName" class="field-value">-</div></div>
                <div><span class="field-label">Middle Name</span><div id="dispMiddleName" class="field-value">-</div></div>
                <div><span class="field-label">Last Name</span><div id="dispLastName" class="field-value">-</div></div>
                <div><span class="field-label">Email</span><div id="dispEmail" class="field-value">-</div></div>
                <div><span class="field-label">Username</span><div id="dispUsername" class="field-value">-</div></div>
              </div>
            </div>
          </div>

          <div class="border-t border-blue-100 pt-6">
            <div class="rounded-xl border border-blue-100 bg-blue-50/40 p-4">
              <div class="flex items-start justify-between gap-3 mb-4 flex-wrap">
                <div>
                  <h2 class="text-base font-semibold text-gray-800">Change Password</h2>
                  <p class="text-xs text-gray-500 mt-0.5">Use a strong password to keep your account secure.</p>
                </div>
                <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full bg-white border border-blue-200 text-[11px] font-semibold text-blue-700">
                  <i class="fi fi-sr-shield-check" style="font-size:10px"></i> Protected
                </span>
              </div>

              <form id="passwordForm" class="space-y-3">
                @csrf
                <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
                  <div>
                    <label class="field-label">Current Password</label>
                    <input id="currentPassword" name="current_password" type="password" placeholder="Enter current password" class="border rounded-lg px-3.5 py-1.5 w-full focus:ring-2 focus:ring-indigo-400 focus:outline-none text-sm" required>
                  </div>
                  <div>
                    <label class="field-label">New Password</label>
                    <input id="newPassword" name="new_password" type="password" placeholder="Min. 8 characters" class="border rounded-lg px-3.5 py-1.5 w-full focus:ring-2 focus:ring-indigo-400 focus:outline-none text-sm" required>
                    <div id="pwStrengthBar" class="hidden mt-1.5 h-1.5 rounded-full bg-gray-200 overflow-hidden"><div id="pwStrengthFill" class="h-full rounded-full transition-all duration-300 w-0"></div></div>
                    <p id="pwStrengthLabel" class="hidden text-xs mt-1 text-gray-400"></p>
                  </div>
                  <div>
                    <label class="field-label">Confirm Password</label>
                    <input id="confirmPassword" name="new_password_confirmation" type="password" placeholder="Re-enter new password" class="border rounded-lg px-3.5 py-1.5 w-full focus:ring-2 focus:ring-indigo-400 focus:outline-none text-sm" required>
                    <p id="pwMatchMsg" class="hidden text-xs text-red-500 mt-1">Passwords do not match.</p>
                  </div>
                </div>
                <div id="pwError" class="hidden text-xs text-red-600 bg-red-50 border border-red-200 rounded-lg px-3 py-2"></div>
                <div class="flex justify-end">
                  <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white text-xs font-semibold px-5 py-1.5 rounded-md transition flex items-center gap-2">
                    <span>Update Password</span>
                    <svg id="pwSpinner" class="hidden animate-spin h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"/></svg>
                  </button>
                </div>
              </form>
            </div>
          </div>

          <div class="border-t border-red-100 pt-5">
            <div class="rounded-xl border border-red-200 bg-gradient-to-r from-red-50/90 via-red-50/70 to-white p-4">
              <div class="flex items-start justify-between gap-3 flex-wrap">
                <div class="min-w-0">
                  <p class="text-[11px] font-semibold uppercase tracking-[0.08em] text-red-600">Critical Action</p>
                  <h3 class="text-sm font-semibold text-red-700 mt-0.5">Danger Zone</h3>
                  <p class="text-xs text-gray-600 mt-1 max-w-xl">Deleting your account is permanent and will immediately remove your admin access.</p>
                </div>
                <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full border border-red-200 bg-white text-[11px] font-semibold text-red-600">
                  <i class="fi fi-ss-trash" style="font-size:10px"></i> Irreversible
                </span>
              </div>
              <div class="mt-3 flex justify-end">
                <button id="deleteAccountBtn" class="inline-flex items-center gap-1.5 bg-red-600 hover:bg-red-700 text-white text-xs font-semibold px-5 py-1.5 rounded-md transition shadow-sm shadow-red-200/60">
                  <i class="fi fi-ss-trash" style="font-size:13px"></i> Delete Account
                </button>
              </div>
            </div>
          </div>
        </div>

        {{-- MY ACTIVITY LOGS --}}
        <div class="activity-log-card bg-white rounded-2xl shadow border overflow-hidden">
          <div class="activity-log-head px-5 py-4 border-b border-blue-100">
            <div class="flex items-start justify-between gap-3 flex-wrap">
              <div class="flex items-start gap-2.5 min-w-0">
                <span class="w-8 h-8 rounded-lg bg-blue-100 border border-blue-200 text-blue-600 inline-flex items-center justify-center flex-shrink-0">
                  <i class="fi fi-sr-time-check text-[13px]"></i>
                </span>
                <div>
                  <h2 class="text-sm font-semibold text-gray-800 leading-tight">My Activity Logs</h2>
                  <p class="text-[11px] text-gray-500 mt-0.5">Monitor your security updates and all admin actions from one timeline.</p>
                </div>
              </div>
              <span id="logCount" class="activity-log-count">loading...</span>
            </div>
          </div>

          <div class="px-5 py-4 border-b border-gray-100 bg-gradient-to-b from-blue-50/40 to-white">
            <div class="controls-wrapper bg-white rounded-xl shadow-sm border border-gray-200 p-4 flex flex-wrap items-center justify-between gap-3">
              <div class="flex flex-wrap items-center gap-2.5">
                <div class="flex items-center gap-2 px-3 py-2 border border-gray-300 rounded-lg text-sm text-gray-700">
                  <i class="fi fi-rr-filter text-gray-500"></i>
                  <span>Filter By</span>
                </div>

                <div class="relative">
                  <select id="myLogActionFilter" aria-label="Filter activity log by action" class="appearance-none bg-white border border-indigo-200 rounded-lg px-3 py-2 pr-8 text-xs font-medium text-gray-700 hover:border-indigo-300 focus:outline-none focus:ring-2 focus:ring-indigo-400 focus:border-indigo-400 transition cursor-pointer shadow-sm min-w-[170px]">
                    <option value="">All Actions</option>
                  </select>
                  <i class="fi fi-rr-angle-small-down absolute right-2.5 top-1/2 -translate-y-1/2 text-gray-500 pointer-events-none text-[11px]"></i>
                </div>

                <div class="flex flex-wrap items-center gap-2">
                  <div class="date-pill flex items-center gap-0 rounded-xl border border-indigo-200 bg-white shadow-sm overflow-hidden focus-within:ring-2 focus-within:ring-indigo-400 focus-within:border-indigo-400 transition">
                    <div class="flex items-center gap-1.5 bg-gradient-to-br from-indigo-500 to-indigo-600 px-2.5 py-2 self-stretch">
                      <i class="fi fi-rr-calendar text-white text-[11px]"></i>
                    </div>
                    <input type="date" id="myLogDateFrom" aria-label="From date" class="px-2.5 py-1.5 text-xs border-none focus:outline-none focus:ring-0 bg-white">
                  </div>

                  <span class="text-gray-300 font-bold text-lg">&rarr;</span>

                  <div class="date-pill flex items-center gap-0 rounded-xl border border-indigo-200 bg-white shadow-sm overflow-hidden focus-within:ring-2 focus-within:ring-indigo-400 focus-within:border-indigo-400 transition">
                    <div class="flex items-center gap-1.5 bg-gradient-to-br from-indigo-500 to-indigo-600 px-2.5 py-2 self-stretch">
                      <i class="fi fi-rr-calendar text-white text-[11px]"></i>
                    </div>
                    <input type="date" id="myLogDateTo" aria-label="To date" class="px-2.5 py-1.5 text-xs border-none focus:outline-none focus:ring-0 bg-white">
                  </div>
                </div>

                <div class="relative min-w-[230px]">
                  <i class="fi fi-rr-search absolute left-2.5 top-1/2 -translate-y-1/2 text-gray-500 pointer-events-none text-[11px]"></i>
                  <input type="text" id="myLogSearch" placeholder="Action, details, IP..." aria-label="Search activity logs" class="w-full appearance-none bg-white border border-indigo-200 rounded-lg px-3 py-2 pl-8 text-xs font-medium text-gray-700 placeholder-gray-400 hover:border-indigo-300 focus:outline-none focus:ring-2 focus:ring-indigo-400 focus:border-indigo-400 transition">
                </div>
              </div>

              <div class="flex items-center gap-2">
                <button id="myLogResetBtn" class="flex items-center gap-2 text-red-600 hover:text-red-700 text-sm font-semibold px-3 py-2 rounded-lg hover:bg-red-50 transition" type="button">
                  <i class="fi fi-rr-rotate-left"></i>
                  <span>Reset Filter</span>
                </button>
              </div>
            </div>
          </div>

          <div class="px-5 py-4">
            <div class="overflow-x-auto rounded-xl border border-blue-100">
              <table class="activity-log-table w-full">
                <thead>
                  <tr>
                    <th class="px-4 py-2.5 text-left font-semibold">Action</th>
                    <th class="px-4 py-2.5 text-left font-semibold">Details</th>
                    <th class="px-4 py-2.5 text-left font-semibold">IP Address</th>
                    <th class="px-4 py-2.5 text-left font-semibold">Date &amp; Time</th>
                  </tr>
                </thead>
                <tbody id="activityTableBody">
                  <tr><td colspan="4" class="px-4 py-7 text-center text-gray-400 text-sm">Loading logs...</td></tr>
                </tbody>
              </table>
            </div>

            <div class="flex items-center justify-between mt-3 gap-2 flex-wrap">
              <p id="myLogPageMeta" class="activity-log-pagination-meta">Showing 0-0</p>
              <div class="flex items-center gap-2">
                <button id="myLogPrevBtn" class="activity-log-nav-btn" disabled>Previous</button>
                <button id="myLogNextBtn" class="activity-log-nav-btn" disabled>Next</button>
              </div>
            </div>
          </div>
        </div>
      </div>{{-- /tab-profile --}}

      {{-- ===== TAB 2: ADD MEMBERS ===== --}}
      <div id="tab-members" class="tab-panel space-y-6">
        <div class="members-card bg-white rounded-2xl shadow border overflow-hidden">
          <div class="members-head px-5 py-4 border-b border-blue-100">
            <div class="flex items-start justify-between gap-3 flex-wrap">
              <div class="flex items-start gap-2.5 min-w-0">
                <span class="w-8 h-8 rounded-lg bg-blue-100 border border-blue-200 text-blue-600 inline-flex items-center justify-center flex-shrink-0">
                  <i class="fi fi-sr-users-alt text-[13px]"></i>
                </span>
                <div>
                  <h2 class="text-sm font-semibold text-gray-800 leading-tight">Admin Members</h2>
                  <p class="text-[11px] text-gray-500 mt-0.5">Manage all administrator accounts and keep access properly controlled.</p>
                </div>
              </div>
              <div class="members-actions-wrap flex items-center gap-2">
                <span id="memberCountBadge" class="members-count-badge">loading...</span>
                <button id="openCreateAdminBtn" class="inline-flex items-center gap-1.5 bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-semibold px-4 py-1.5 rounded-md transition">
                  <i class="fi fi-sr-user-add" style="font-size:13px"></i> Create New Admin
                </button>
              </div>
            </div>
          </div>

          <div id="memberError" class="hidden mx-5 mt-4 bg-red-50 border border-red-200 text-red-700 text-sm rounded-xl px-4 py-3"></div>

          <div class="px-5 py-4">
            <div class="overflow-x-auto rounded-xl border border-blue-100">
              <table class="members-table w-full">
                <thead>
                  <tr>
                    <th class="px-4 py-2.5 text-left font-semibold">Admin</th>
                    <th class="px-4 py-2.5 text-left font-semibold">Username</th>
                    <th class="px-4 py-2.5 text-left font-semibold">Email</th>
                    <th class="px-4 py-2.5 text-left font-semibold">Status</th>
                    <th class="px-4 py-2.5 text-left font-semibold">Joined</th>
                    <th class="px-4 py-2.5 text-center font-semibold">Actions</th>
                  </tr>
                </thead>
                <tbody id="membersTableBody">
                  <tr><td colspan="6" class="px-4 py-8 text-center text-gray-400 text-sm">Loading members...</td></tr>
                </tbody>
              </table>
            </div>
          </div>
        </div>
      </div>{{-- /tab-members --}}

      {{-- ===== TAB 3: GLOBAL TEAM ACTIVITY ===== --}}
      <div id="tab-team" class="tab-panel space-y-6">
        <div class="team-log-card bg-white rounded-2xl shadow border overflow-hidden">
          <div class="team-log-head px-5 py-4 border-b border-blue-100">
            <div class="flex items-start justify-between gap-3 flex-wrap">
              <div class="flex items-start gap-2.5 min-w-0">
                <span class="w-8 h-8 rounded-lg bg-blue-100 border border-blue-200 text-blue-600 inline-flex items-center justify-center flex-shrink-0">
                  <i class="fi fi-sr-list-check text-[13px]"></i>
                </span>
                <div>
                  <h2 class="text-sm font-semibold text-gray-800 leading-tight">Global Team Activity Tracking</h2>
                  <p class="text-[11px] text-gray-500 mt-0.5">Monitor every admin action across the platform in one unified audit stream.</p>
                </div>
              </div>
              <div class="team-actions-wrap flex items-center gap-2">
                <span id="teamLogCount" class="team-log-count">loading...</span>
                <button id="refreshTeamLogBtn" type="button">
                  <i class="fi fi-sr-refresh" style="font-size:.7rem"></i> Refresh
                </button>
              </div>
            </div>
          </div>

          <div class="px-5 py-4 border-b border-gray-100 bg-gradient-to-b from-blue-50/40 to-white">
            <div class="controls-wrapper bg-white rounded-xl shadow-sm border border-gray-200 p-4 flex flex-wrap items-center justify-between gap-3">
              <div class="flex flex-wrap items-center gap-2.5">
                <div class="flex items-center gap-2 px-3 py-2 border border-gray-300 rounded-lg text-sm text-gray-700">
                  <i class="fi fi-rr-filter text-gray-500"></i>
                  <span>Filter By</span>
                </div>

                <div class="relative">
                  <select id="teamLogActionFilter" aria-label="Filter team log by action" class="appearance-none bg-white border border-indigo-200 rounded-lg px-3 py-2 pr-8 text-xs font-medium text-gray-700 hover:border-indigo-300 focus:outline-none focus:ring-2 focus:ring-indigo-400 focus:border-indigo-400 transition cursor-pointer shadow-sm min-w-[170px]">
                    <option value="">All Actions</option>
                  </select>
                  <i class="fi fi-rr-angle-small-down absolute right-2.5 top-1/2 -translate-y-1/2 text-gray-500 pointer-events-none text-[11px]"></i>
                </div>

                <div class="flex flex-wrap items-center gap-2">
                  <div class="date-pill flex items-center gap-0 rounded-xl border border-indigo-200 bg-white shadow-sm overflow-hidden focus-within:ring-2 focus-within:ring-indigo-400 focus-within:border-indigo-400 transition">
                    <div class="flex items-center gap-1.5 bg-gradient-to-br from-indigo-500 to-indigo-600 px-2.5 py-2 self-stretch">
                      <i class="fi fi-rr-calendar text-white text-[11px]"></i>
                    </div>
                    <input type="date" id="teamLogDateFrom" aria-label="From date" class="px-2.5 py-1.5 text-xs border-none focus:outline-none focus:ring-0 bg-white">
                  </div>

                  <span class="text-gray-300 font-bold text-lg">&rarr;</span>

                  <div class="date-pill flex items-center gap-0 rounded-xl border border-indigo-200 bg-white shadow-sm overflow-hidden focus-within:ring-2 focus-within:ring-indigo-400 focus-within:border-indigo-400 transition">
                    <div class="flex items-center gap-1.5 bg-gradient-to-br from-indigo-500 to-indigo-600 px-2.5 py-2 self-stretch">
                      <i class="fi fi-rr-calendar text-white text-[11px]"></i>
                    </div>
                    <input type="date" id="teamLogDateTo" aria-label="To date" class="px-2.5 py-1.5 text-xs border-none focus:outline-none focus:ring-0 bg-white">
                  </div>
                </div>

                <div class="relative min-w-[260px]">
                  <i class="fi fi-rr-search absolute left-2.5 top-1/2 -translate-y-1/2 text-gray-500 pointer-events-none text-[11px]"></i>
                  <input type="text" id="teamLogSearch" placeholder="Admin, action, details, IP..." aria-label="Search team activity logs" class="w-full appearance-none bg-white border border-indigo-200 rounded-lg px-3 py-2 pl-8 text-xs font-medium text-gray-700 placeholder-gray-400 hover:border-indigo-300 focus:outline-none focus:ring-2 focus:ring-indigo-400 focus:border-indigo-400 transition">
                </div>
              </div>

              <div class="flex items-center gap-2">
                <button id="teamLogResetBtn" class="flex items-center gap-2 text-red-600 hover:text-red-700 text-sm font-semibold px-3 py-2 rounded-lg hover:bg-red-50 transition" type="button">
                  <i class="fi fi-rr-rotate-left"></i>
                  <span>Reset Filter</span>
                </button>
              </div>
            </div>
          </div>

          <div class="px-5 py-4">
            <div class="overflow-x-auto rounded-xl border border-blue-100">
              <table class="team-log-table w-full">
                <thead>
                  <tr>
                    <th class="px-4 py-2.5 text-left font-semibold">Admin Name</th>
                    <th class="px-4 py-2.5 text-left font-semibold">Action</th>
                    <th class="px-4 py-2.5 text-left font-semibold">Details</th>
                    <th class="px-4 py-2.5 text-left font-semibold">IP Address</th>
                    <th class="px-4 py-2.5 text-left font-semibold">Date &amp; Time</th>
                  </tr>
                </thead>
                <tbody id="teamActivityBody">
                  <tr><td colspan="5" class="px-4 py-8 text-center text-gray-400 text-sm">Loading activity...</td></tr>
                </tbody>
              </table>
            </div>

            <div class="flex items-center justify-between mt-3 gap-2 flex-wrap">
              <p id="teamLogPageMeta" class="team-log-pagination-meta">Showing 0-0</p>
              <div class="flex items-center gap-2">
                <button id="teamLogPrevBtn" class="team-log-nav-btn" disabled>Previous</button>
                <button id="teamLogNextBtn" class="team-log-nav-btn" disabled>Next</button>
              </div>
            </div>
          </div>
        </div>
      </div>{{-- /tab-team --}}

    </section>
  </main>
</div>

{{-- MODAL: EDIT MY PROFILE --}}
<div id="editModal" class="modal-backdrop">
  <div class="modal-surface w-full max-w-2xl mx-4 overflow-hidden">
    <div class="modal-head px-5 py-4">
      <div class="flex items-start justify-between gap-3">
        <div class="flex items-start gap-3 min-w-0">
          <span class="modal-head-icon">
            <i class="fi fi-sr-pencil text-sm"></i>
          </span>
          <div>
            <h3 class="text-sm font-semibold leading-tight">Edit Profile</h3>
            <p class="modal-head-sub mt-0.5">Update your personal identity and account details.</p>
          </div>
        </div>
        <button id="closeEditBtn" class="modal-close-light w-8 h-8 flex items-center justify-center rounded-full text-xl transition" aria-label="Close edit profile modal">&times;</button>
      </div>
    </div>

    <form id="profileForm" enctype="multipart/form-data" class="modal-body-soft px-5 py-5 space-y-4">
      @csrf
      <div class="modal-callout flex items-center justify-between gap-4 flex-wrap">
        <div class="flex items-center gap-4">
          <div class="relative">
            <img id="modalAvatar" src="" class="w-20 h-20 rounded-full object-cover border-2 border-indigo-200 shadow" alt="">
            <label for="avatarInput" class="absolute -bottom-1 -right-1 w-7 h-7 bg-indigo-600 hover:bg-indigo-700 rounded-full flex items-center justify-center cursor-pointer shadow transition">
              <i class="fi fi-ss-camera text-white" style="font-size:11px"></i>
            </label>
            <input id="avatarInput" name="avatar" type="file" accept="image/*" class="hidden">
          </div>
          <div>
            <p class="text-xs font-semibold text-gray-700">Profile Picture</p>
            <p class="text-xs text-gray-500 mt-0.5">Upload a clear photo for easier team identification.</p>
          </div>
        </div>

        <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full bg-white border border-blue-200 text-[11px] font-semibold text-blue-700">
          <i class="fi fi-sr-shield-check" style="font-size:10px"></i> JPG/PNG up to 2 MB
        </span>
      </div>

      <div id="profileEditErrorAlert" class="hidden mb-4 p-3 bg-red-50 border border-red-200 rounded-lg">
        <div class="flex items-start gap-3">
          <div class="flex-shrink-0">
            <i class="fi fi-rr-alert text-red-500 text-base"></i>
          </div>
          <div class="flex-1">
            <h3 class="text-xs font-semibold text-red-800">Validation Errors</h3>
            <ul id="profileEditErrorList" class="text-xs text-red-700 mt-2 space-y-1 list-disc list-inside"></ul>
          </div>
          <button type="button" id="closeProfileEditErrorAlert" class="text-red-500 hover:text-red-700 transition p-1">
            <i class="fi fi-rr-cross"></i>
          </button>
        </div>
      </div>

      <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
        <div><label class="field-label">First Name <span class="text-red-500">*</span></label><input id="editFirstName" name="first_name" type="text" class="border rounded-lg px-3 py-2 w-full focus:ring-2 focus:ring-indigo-400 focus:outline-none text-sm" required></div>
        <div><label class="field-label">Middle Name</label><input id="editMiddleName" name="middle_name" type="text" class="border rounded-lg px-3 py-2 w-full focus:ring-2 focus:ring-indigo-400 focus:outline-none text-sm"></div>
        <div><label class="field-label">Last Name <span class="text-red-500">*</span></label><input id="editLastName" name="last_name" type="text" class="border rounded-lg px-3 py-2 w-full focus:ring-2 focus:ring-indigo-400 focus:outline-none text-sm" required></div>
      </div>

      <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
        <div><label class="field-label">Email <span class="text-red-500">*</span></label><input id="editEmail" name="email" type="email" class="border rounded-lg px-3 py-2 w-full focus:ring-2 focus:ring-indigo-400 focus:outline-none text-sm" required></div>
        <div><label class="field-label">Username <span class="text-red-500">*</span></label><input id="editUsername" name="username" type="text" class="border rounded-lg px-3 py-2 w-full focus:ring-2 focus:ring-indigo-400 focus:outline-none text-sm" required></div>
      </div>

      <div class="modal-footer-soft -mx-5 mt-1 px-5 py-4 flex justify-end gap-2">
        <button type="button" id="cancelEditBtn" class="px-5 py-2 text-sm rounded-lg border border-gray-200 hover:bg-gray-50 transition text-gray-600 bg-white">Cancel</button>
        <button type="submit" id="saveProfileBtn" class="px-6 py-2 text-sm bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg transition flex items-center gap-2 font-semibold">
          <span>Save Changes</span>
          <svg id="savingSpinner" class="hidden animate-spin h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"/></svg>
        </button>
      </div>
    </form>
  </div>
</div>

{{-- MODAL: CREATE NEW ADMIN --}}
<div id="createAdminModal" class="modal-backdrop">
  <div class="modal-surface w-full max-w-lg mx-4 overflow-hidden">
    <div class="modal-head px-5 py-4">
      <div class="flex items-start justify-between gap-3">
        <div class="flex items-start gap-3 min-w-0">
          <span class="modal-head-icon">
            <i class="fi fi-sr-user-add text-sm"></i>
          </span>
          <div>
            <h3 class="text-sm font-semibold leading-tight">Create New Admin</h3>
            <p class="modal-head-sub mt-0.5">Provision a new administrator with controlled access credentials.</p>
          </div>
        </div>
        <button class="modal-close-btn modal-close-light w-8 h-8 flex items-center justify-center rounded-full text-xl transition" data-modal="createAdminModal" aria-label="Close create admin modal">&times;</button>
      </div>
    </div>

    <form id="createAdminForm" class="modal-body-soft px-5 py-5 space-y-4">
      @csrf
      <div class="modal-callout flex items-start gap-2.5">
        <i class="fi fi-sr-shield-check text-blue-600 text-[13px] mt-0.5"></i>
        <p class="text-xs text-slate-600 leading-relaxed">Use a strong temporary password. The new admin can update it after first login for better account security.</p>
      </div>

      <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
        <div>
          <label class="field-label">First Name <span class="text-red-500">*</span></label>
          <input id="createFirstName" name="first_name" type="text" class="border rounded-lg px-3 py-2 w-full focus:ring-2 focus:ring-indigo-400 focus:outline-none text-sm">
          <p class="field-error hidden text-xs text-red-600 mt-1" data-for="createFirstName">First name is required.</p>
        </div>
        <div>
          <label class="field-label">Middle Name <span class="text-[10px] text-gray-400 font-normal">(optional)</span></label>
          <input id="createMiddleName" name="middle_name" type="text" class="border rounded-lg px-3 py-2 w-full focus:ring-2 focus:ring-indigo-400 focus:outline-none text-sm" placeholder="Optional">
        </div>
        <div>
          <label class="field-label">Last Name <span class="text-red-500">*</span></label>
          <input id="createLastName" name="last_name" type="text" class="border rounded-lg px-3 py-2 w-full focus:ring-2 focus:ring-indigo-400 focus:outline-none text-sm">
          <p class="field-error hidden text-xs text-red-600 mt-1" data-for="createLastName">Last name is required.</p>
        </div>
      </div>

      <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
        <div>
          <label class="field-label">Email <span class="text-red-500">*</span></label>
          <input id="createEmail" name="email" type="email" class="border rounded-lg px-3 py-2 w-full focus:ring-2 focus:ring-indigo-400 focus:outline-none text-sm">
          <p class="field-error hidden text-xs text-red-600 mt-1" data-for="createEmail">A valid email is required.</p>
        </div>
        <div>
          <label class="field-label">Username <span class="text-red-500">*</span></label>
          <input id="createUsername" name="username" type="text" class="border rounded-lg px-3 py-2 w-full focus:ring-2 focus:ring-indigo-400 focus:outline-none text-sm">
          <p class="field-error hidden text-xs text-red-600 mt-1" data-for="createUsername">Username is required.</p>
        </div>
      </div>

      <div>
        <label class="field-label">Temporary Password <span class="text-red-500">*</span></label>
        <input id="createPassword" name="password" type="password" minlength="8" placeholder="Min. 8 characters" class="border rounded-lg px-3 py-2 w-full focus:ring-2 focus:ring-indigo-400 focus:outline-none text-sm">
        <p class="field-error hidden text-xs text-red-600 mt-1" data-for="createPassword">Password must be at least 8 characters.</p>
      </div>
      <div id="createAdminError" class="hidden text-sm text-red-700 bg-red-50 border border-red-200 rounded-lg px-4 py-2"></div>

      <div class="modal-footer-soft -mx-5 mt-1 px-5 py-4 flex justify-end gap-2">
        <button type="button" class="modal-close-btn px-5 py-2 text-sm rounded-lg border border-gray-200 hover:bg-gray-50 transition text-gray-600 bg-white" data-modal="createAdminModal">Cancel</button>
        <button type="submit" id="createAdminSubmitBtn" class="px-6 py-2 text-sm bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg transition flex items-center gap-2 font-semibold">
          <span>Create Admin</span>
          <svg id="createAdminSpinner" class="hidden animate-spin h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"/></svg>
        </button>
      </div>
    </form>
  </div>
</div>

{{-- MODAL: VIEW / EDIT MEMBER --}}
<div id="memberModal" class="modal-backdrop">
  <div class="modal-surface w-full max-w-4xl mx-4 overflow-hidden flex flex-col max-h-[90vh]">
    <div class="modal-head px-5 py-4 flex-shrink-0">
      <div class="flex items-start justify-between gap-3">
        <div class="flex items-start gap-3 min-w-0">
          <span class="modal-head-icon">
            <i class="fi fi-sr-users-alt text-sm"></i>
          </span>
          <div>
            <h3 class="text-sm font-semibold leading-tight" id="memberModalTitle">Admin Details</h3>
            <p class="modal-head-sub mt-0.5">Review profile info, update credentials, and audit recent actions.</p>
          </div>
        </div>
        <button class="modal-close-btn modal-close-light w-8 h-8 flex items-center justify-center rounded-full text-xl transition" data-modal="memberModal" aria-label="Close member modal">&times;</button>
      </div>
    </div>

    <div class="modal-body-soft overflow-y-auto flex-1 px-5 py-5 space-y-5">
      <div class="member-profile-banner flex items-center justify-between gap-4 flex-wrap">
        <div class="flex items-center gap-4">
          <div id="memberModalAvatar" class="w-14 h-14 rounded-full bg-indigo-600 text-white font-bold flex items-center justify-center text-xl flex-shrink-0">?</div>
          <div>
            <p class="font-semibold text-gray-800" id="memberModalName">-</p>
            <p class="text-xs text-gray-500" id="memberModalEmail">-</p>
            <p class="text-xs text-gray-400" id="memberModalJoined">-</p>
          </div>
        </div>

        <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full bg-white border border-blue-200 text-[11px] font-semibold text-blue-700">Team Member</span>
      </div>

      <div id="memberEditErrorAlert" class="hidden -mt-1 p-3 bg-red-50 border border-red-200 rounded-lg">
        <div class="flex items-start gap-3">
          <div class="flex-shrink-0">
            <i class="fi fi-rr-alert text-red-500 text-base"></i>
          </div>
          <div class="flex-1">
            <h3 class="text-xs font-semibold text-red-800">Validation Errors</h3>
            <ul id="memberEditErrorList" class="text-xs text-red-700 mt-2 space-y-1 list-disc list-inside"></ul>
          </div>
          <button type="button" id="closeMemberEditErrorAlert" class="text-red-500 hover:text-red-700 transition p-1">
            <i class="fi fi-rr-cross"></i>
          </button>
        </div>
      </div>

      <form id="memberEditForm" class="space-y-3">
        @csrf
        <input type="hidden" id="memberEditId" name="_target_id" value="">
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
          <div><label class="field-label">First Name <span class="text-red-500">*</span></label><input id="memberEditFirstName" name="first_name" type="text" required class="border rounded-lg px-3 py-2 w-full focus:ring-2 focus:ring-indigo-400 focus:outline-none text-sm"></div>
          <div><label class="field-label">Last Name <span class="text-red-500">*</span></label><input id="memberEditLastName" name="last_name" type="text" required class="border rounded-lg px-3 py-2 w-full focus:ring-2 focus:ring-indigo-400 focus:outline-none text-sm"></div>
        </div>
        <div><label class="field-label">Middle Name <span class="text-xs text-gray-400 font-normal">(optional)</span></label><input id="memberEditMiddleName" name="middle_name" type="text" class="border rounded-lg px-3 py-2 w-full focus:ring-2 focus:ring-indigo-400 focus:outline-none text-sm" placeholder="Optional"></div>
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
          <div><label class="field-label">Email <span class="text-red-500">*</span></label><input id="memberEditEmail" name="email" type="email" required class="border rounded-lg px-3 py-2 w-full focus:ring-2 focus:ring-indigo-400 focus:outline-none text-sm"></div>
          <div><label class="field-label">Username <span class="text-red-500">*</span></label><input id="memberEditUsername" name="username" type="text" required class="border rounded-lg px-3 py-2 w-full focus:ring-2 focus:ring-indigo-400 focus:outline-none text-sm"></div>
        </div>
        <div><label class="field-label">Reset Password <span class="text-gray-300 font-normal">(leave blank to keep unchanged)</span></label><input id="memberEditPassword" name="password" type="password" minlength="8" placeholder="New password (min. 8 chars)" class="border rounded-lg px-3 py-2 w-full focus:ring-2 focus:ring-indigo-400 focus:outline-none text-sm"></div>

        <div class="modal-footer-soft -mx-5 mt-1 px-5 py-4 flex justify-end">
          <button type="submit" id="memberEditSaveBtn" class="px-6 py-2 text-sm bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg transition flex items-center gap-2 font-semibold">
            <span>Save Changes</span>
            <svg id="memberEditSpinner" class="hidden animate-spin h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"/></svg>
          </button>
        </div>
      </form>

      <div class="member-log-shell">
        <div class="px-4 py-3 border-b border-blue-100 bg-gradient-to-r from-blue-50/95 via-blue-50/70 to-white">
          <h4 class="text-sm font-semibold text-gray-800">Activity Logs</h4>
          <p class="text-[11px] text-gray-500 mt-0.5">Recent security and management actions for this admin account.</p>
        </div>

        <div class="overflow-x-auto">
          <table class="w-full text-sm member-log-table">
            <thead>
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

{{-- MODAL: DELETE ACCOUNT CONFIRMATION --}}
<div id="deleteMemberModal" class="modal-backdrop">
  <div class="bg-white rounded-2xl shadow-2xl w-full max-w-md mx-4 overflow-hidden">
    <div class="flex items-center justify-between px-6 py-4 border-b border-red-200 bg-red-600 text-white">
      <div class="flex items-center gap-2.5">
        <span class="w-8 h-8 rounded-lg bg-white/20 border border-white/20 inline-flex items-center justify-center">
          <i class="fi fi-ss-trash text-sm"></i>
        </span>
        <div>
          <h3 class="text-sm font-semibold leading-tight">Delete Admin Account</h3>
          <p class="text-[11px] text-red-100 mt-0.5">This action cannot be undone.</p>
        </div>
      </div>
      <button class="modal-close-btn w-8 h-8 flex items-center justify-center rounded-full hover:bg-white/20 text-white/90 text-xl transition" data-modal="deleteMemberModal">&times;</button>
    </div>

    <div class="px-6 py-5 space-y-4">
      <div class="rounded-lg border border-red-200 bg-red-50 px-4 py-3">
        <p class="text-xs font-semibold text-red-700 uppercase tracking-wide">Critical Warning</p>
        <p class="text-sm text-red-800 mt-1">You are about to permanently delete <span id="deleteMemberName" class="font-semibold"></span>. This will remove their access and mark the account as deleted.</p>
      </div>
      <div class="rounded-lg border border-gray-200 bg-gray-50 px-4 py-3">
        <p class="text-xs text-gray-600">Once deleted, the deactivate and delete buttons will no longer appear for this account.</p>
      </div>
      <div class="space-y-1">
        <label for="deleteMemberConfirmInput" class="text-xs font-semibold text-gray-700">
          Type <span class="font-bold text-red-600">Confirm Delete</span> to proceed
        </label>
        <input type="text" id="deleteMemberConfirmInput" placeholder="Confirm Delete"
          class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-red-400 focus:border-red-400 transition" />
      </div>
      <div id="deleteMemberError" class="hidden text-sm text-red-700 bg-red-50 border border-red-200 rounded-lg px-4 py-2"></div>
    </div>

    <div class="flex items-center justify-end gap-2 px-6 py-4 border-t border-gray-100 bg-white">
      <button type="button" class="modal-close-btn px-4 py-2 text-sm rounded-lg border border-gray-200 text-gray-600 hover:bg-gray-50 transition" data-modal="deleteMemberModal">Cancel</button>
      <button type="button" id="confirmDeleteMemberBtn" disabled
        class="px-4 py-2 text-sm rounded-lg bg-red-600 hover:bg-red-700 text-white font-semibold transition inline-flex items-center gap-2 disabled:opacity-40 disabled:cursor-not-allowed">
        <span>Delete Account</span>
        <svg id="deleteMemberSpinner" class="hidden animate-spin h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"/></svg>
      </button>
    </div>
  </div>
</div>

<div id="reactivateMemberModal" class="modal-backdrop">
  <div class="bg-white rounded-2xl shadow-2xl w-full max-w-md mx-4 overflow-hidden">
    <div class="flex items-center justify-between px-6 py-4 border-b border-green-200 bg-green-600 text-white">
      <div class="flex items-center gap-2.5">
        <span class="w-8 h-8 rounded-lg bg-white/20 border border-white/20 inline-flex items-center justify-center">
          <i class="fi fi-ss-user-check text-sm"></i>
        </span>
        <div>
          <h3 class="text-sm font-semibold leading-tight">Reactivate Admin Account</h3>
          <p class="text-[11px] text-green-100 mt-0.5">The admin will regain access immediately.</p>
        </div>
      </div>
      <button class="modal-close-btn w-8 h-8 flex items-center justify-center rounded-full hover:bg-white/20 text-white/90 text-xl transition" data-modal="reactivateMemberModal">&times;</button>
    </div>

    <div class="px-6 py-5 space-y-4">
      <div class="rounded-lg border border-green-200 bg-green-50 px-4 py-3">
        <p class="text-xs font-semibold text-green-700 uppercase tracking-wide">Confirm Reactivation</p>
        <p class="text-sm text-green-800 mt-1">You are about to reactivate <span id="reactivateMemberName" class="font-semibold"></span>. They will be able to log in again.</p>
      </div>
      <div id="reactivateMemberError" class="hidden text-sm text-red-700 bg-red-50 border border-red-200 rounded-lg px-4 py-2"></div>
    </div>

    <div class="flex items-center justify-end gap-2 px-6 py-4 border-t border-gray-100 bg-white">
      <button type="button" class="modal-close-btn px-4 py-2 text-sm rounded-lg border border-gray-200 text-gray-600 hover:bg-gray-50 transition" data-modal="reactivateMemberModal">Cancel</button>
      <button type="button" id="confirmReactivateMemberBtn" class="px-4 py-2 text-sm rounded-lg bg-green-600 hover:bg-green-700 text-white font-semibold transition inline-flex items-center gap-2">
        <span>Reactivate</span>
        <svg id="reactivateMemberSpinner" class="hidden animate-spin h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"/></svg>
      </button>
    </div>
  </div>
</div>

<div id="deactivateMemberModal" class="modal-backdrop">
  <div class="bg-white rounded-2xl shadow-2xl w-full max-w-md mx-4 overflow-hidden">
    <div class="flex items-center justify-between px-6 py-4 border-b border-red-200 bg-red-600 text-white">
      <div class="flex items-center gap-2.5">
        <span class="w-8 h-8 rounded-lg bg-white/20 border border-white/20 inline-flex items-center justify-center">
          <i class="fi fi-ss-user-slash text-sm"></i>
        </span>
        <div>
          <h3 class="text-sm font-semibold leading-tight">Deactivate Admin Account</h3>
          <p class="text-[11px] text-red-100 mt-0.5">The admin will lose access immediately.</p>
        </div>
      </div>
      <button class="modal-close-btn w-8 h-8 flex items-center justify-center rounded-full hover:bg-white/20 text-white/90 text-xl transition" data-modal="deactivateMemberModal">&times;</button>
    </div>

    <div class="px-6 py-5 space-y-4">
      <div class="rounded-lg border border-red-200 bg-red-50 px-4 py-3">
        <p class="text-xs font-semibold text-red-700 uppercase tracking-wide">Confirm Deactivation</p>
        <p class="text-sm text-red-800 mt-1">You are about to deactivate <span id="deactivateMemberName" class="font-semibold"></span>. They will no longer be able to log in.</p>
      </div>
      <div class="rounded-lg border border-gray-200 bg-gray-50 px-4 py-3">
        <p class="text-xs text-gray-600">This can be reversed by re-activating the account later.</p>
      </div>
      <div id="deactivateMemberError" class="hidden text-sm text-red-700 bg-red-50 border border-red-200 rounded-lg px-4 py-2"></div>
    </div>

    <div class="flex items-center justify-end gap-2 px-6 py-4 border-t border-gray-100 bg-white">
      <button type="button" class="modal-close-btn px-4 py-2 text-sm rounded-lg border border-gray-200 text-gray-600 hover:bg-gray-50 transition" data-modal="deactivateMemberModal">Cancel</button>
      <button type="button" id="confirmDeactivateMemberBtn" class="px-4 py-2 text-sm rounded-lg bg-red-600 hover:bg-red-700 text-white font-semibold transition inline-flex items-center gap-2">
        <span>Deactivate</span>
        <svg id="deactivateMemberSpinner" class="hidden animate-spin h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"/></svg>
      </button>
    </div>
  </div>
</div>

<div id="deleteAccountModal" class="modal-backdrop">
  <div class="bg-white rounded-2xl shadow-2xl w-full max-w-md mx-4 overflow-hidden">
    <div class="flex items-center justify-between px-6 py-4 border-b border-red-200 bg-gradient-to-r from-red-600 to-rose-600 text-white">
      <div class="flex items-center gap-2.5">
        <span class="w-8 h-8 rounded-lg bg-white/20 border border-white/20 inline-flex items-center justify-center">
          <i class="fi fi-ss-trash text-sm"></i>
        </span>
        <div>
          <h3 class="text-sm font-semibold leading-tight">Confirm Account Deletion</h3>
          <p class="text-[11px] text-red-100 mt-0.5">This action cannot be undone.</p>
        </div>
      </div>
      <button class="modal-close-btn w-8 h-8 flex items-center justify-center rounded-full hover:bg-white/20 text-white/90 text-xl transition" data-modal="deleteAccountModal">&times;</button>
    </div>

    <div class="px-6 py-5 space-y-4">
      <div class="rounded-lg border border-red-200 bg-red-50 px-4 py-3">
        <p class="text-xs font-semibold text-red-700 uppercase tracking-wide">Critical Warning</p>
        <p class="text-sm text-red-800 mt-1">You are about to permanently deactivate your admin account and lose access to the dashboard.</p>
      </div>

      <div class="rounded-lg border border-gray-200 bg-gray-50 px-4 py-3">
        <p class="text-xs text-gray-600">If you continue, you will be signed out immediately after completion.</p>
      </div>

      <div class="space-y-1">
        <label for="deleteAccountConfirmInput" class="text-xs font-semibold text-gray-700">
          Type <span class="font-bold text-red-600">Confirm Delete</span> to proceed
        </label>
        <input type="text" id="deleteAccountConfirmInput" placeholder="Confirm Delete"
          class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-red-400 focus:border-red-400 transition" />
      </div>

      <div id="deleteAccountError" class="hidden text-sm text-red-700 bg-red-50 border border-red-200 rounded-lg px-4 py-2"></div>
    </div>

    <div class="flex items-center justify-end gap-2 px-6 py-4 border-t border-gray-100 bg-white">
      <button type="button" class="modal-close-btn px-4 py-2 text-sm rounded-lg border border-gray-200 text-gray-600 hover:bg-gray-50 transition" data-modal="deleteAccountModal">Cancel</button>
      <button type="button" id="confirmDeleteAccountBtn" disabled
        class="px-4 py-2 text-sm rounded-lg bg-red-600 hover:bg-red-700 text-white font-semibold transition inline-flex items-center gap-2 disabled:opacity-40 disabled:cursor-not-allowed">
        <span>Delete Account</span>
        <svg id="deleteAccountSpinner" class="hidden animate-spin h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"/></svg>
      </button>
    </div>
  </div>
</div>

<div id="toast"></div>

</body>
</html>
