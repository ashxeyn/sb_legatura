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
    .setting-card {
      transition: box-shadow .32s cubic-bezier(0.22, 1, 0.36, 1),
                  transform .32s cubic-bezier(0.22, 1, 0.36, 1),
                  border-color .28s ease;
    }

    .compose-card {
      border: 1px solid #dbeafe;
      border-radius: 14px;
      background: linear-gradient(180deg, #ffffff 0%, #f8fbff 100%);
      box-shadow: 0 8px 22px rgba(2, 38, 68, .08);
      transition: box-shadow .32s cubic-bezier(0.22, 1, 0.36, 1),
                  transform .32s cubic-bezier(0.22, 1, 0.36, 1),
                  border-color .28s ease;
      overflow: hidden;
    }

    .compose-card:hover {
      transform: translateY(-2px);
      border-color: #bfdbfe;
      box-shadow: 0 12px 28px rgba(37, 99, 235, .14);
    }

    .compose-card-target {
      overflow: visible;
    }

    .compose-head {
      padding: 14px 16px;
      border-bottom: 1px solid #dbeafe;
      background: linear-gradient(135deg, #edf4ff 0%, #f8fbff 55%, #ffffff 100%);
    }

    .compose-head.is-target {
      background: linear-gradient(135deg, #ecfeff 0%, #eff6ff 58%, #ffffff 100%);
    }

    .compose-icon-shell {
      width: 38px;
      height: 38px;
      border-radius: 11px;
      display: inline-flex;
      align-items: center;
      justify-content: center;
      flex-shrink: 0;
      background: #dbeafe;
      border: 1px solid #bfdbfe;
      color: #2563eb;
    }

    .compose-kicker {
      font-size: .64rem;
      font-weight: 700;
      text-transform: uppercase;
      letter-spacing: .06em;
      color: #2563eb;
      margin-bottom: .15rem;
      line-height: 1;
    }

    .compose-badge {
      display: inline-flex;
      align-items: center;
      gap: 5px;
      padding: .28rem .55rem;
      border-radius: .55rem;
      font-size: .62rem;
      font-weight: 700;
      letter-spacing: .05em;
      text-transform: uppercase;
      border: 1px solid #bfdbfe;
      background: #ffffff;
      color: #1d4ed8;
      white-space: nowrap;
    }

    .compose-body {
      padding: 14px 16px;
    }

    .compose-chip {
      display: inline-flex;
      align-items: center;
      padding: .22rem .55rem;
      border-radius: 999px;
      background: #f1f5f9;
      color: #475569;
      border: 1px solid #e2e8f0;
      font-size: .64rem;
      font-weight: 700;
      letter-spacing: .01em;
    }

    .compose-callout {
      border: 1px solid #bfdbfe;
      background: linear-gradient(180deg, #eff6ff 0%, #f8fbff 100%);
      border-radius: .7rem;
      color: #1e40af;
      font-size: .72rem;
      padding: .62rem .75rem;
      line-height: 1.4;
    }

    .compose-field label {
      display: block;
      margin-bottom: .3rem;
      font-size: .64rem;
      font-weight: 700;
      letter-spacing: .05em;
      text-transform: uppercase;
      color: #64748b;
    }

    .compose-input {
      width: 100%;
      border: 1px solid #e2e8f0;
      border-radius: .65rem;
      padding: .55rem .72rem;
      font-size: .82rem;
      color: #334155;
      background: #ffffff;
      transition: border-color .24s ease, box-shadow .24s ease;
    }

    .compose-input:focus {
      outline: none;
      border-color: #93c5fd;
      box-shadow: 0 0 0 3px rgba(191, 219, 254, .78);
    }

    .compose-recipient-panel {
      border: 1px solid #dbeafe;
      background: #f9fbff;
      border-radius: .75rem;
      padding: .7rem .75rem;
    }

    .recipient-count {
      display: inline-flex;
      align-items: center;
      padding: .22rem .5rem;
      border-radius: 999px;
      font-size: .62rem;
      font-weight: 700;
      letter-spacing: .04em;
      text-transform: uppercase;
      border: 1px solid #dbeafe;
      color: #64748b;
      background: #ffffff;
      line-height: 1;
    }

    .recipient-count.has-users {
      border-color: #bfdbfe;
      color: #1d4ed8;
      background: #eff6ff;
    }

    .compose-actions {
      margin-top: .1rem;
      border-top: 1px solid #e2e8f0;
      padding-top: .75rem;
    }

    .compose-action-note {
      font-size: .68rem;
      color: #64748b;
      line-height: 1.3;
    }

    .compose-submit-btn {
      padding: .5rem 1rem;
      border-radius: .6rem;
      background: #2563eb;
      color: #ffffff;
      border: 1px solid #2563eb;
      font-size: .72rem;
      font-weight: 700;
      display: inline-flex;
      align-items: center;
      gap: .38rem;
      box-shadow: 0 4px 10px rgba(37, 99, 235, .24);
    }

    .compose-submit-btn:hover:not(:disabled) {
      background: #1d4ed8;
      border-color: #1d4ed8;
      box-shadow: 0 8px 14px rgba(37, 99, 235, .28);
    }

    .activity-feed-card {
      border: 1px solid #dbeafe;
      border-radius: 14px;
      background: linear-gradient(180deg, #ffffff 0%, #f8fbff 100%);
      box-shadow: 0 8px 22px rgba(2, 38, 68, .08);
      overflow: hidden;
      transition: box-shadow .32s cubic-bezier(0.22, 1, 0.36, 1),
                  transform .32s cubic-bezier(0.22, 1, 0.36, 1),
                  border-color .28s ease;
    }

    .activity-feed-card:hover {
      transform: translateY(-2px);
      border-color: #bfdbfe;
      box-shadow: 0 12px 28px rgba(37, 99, 235, .14);
    }

    .activity-feed-head {
      background: linear-gradient(135deg, #edf4ff 0%, #f8fbff 56%, #ffffff 100%);
    }

    .activity-kicker {
      font-size: .64rem;
      font-weight: 700;
      text-transform: uppercase;
      letter-spacing: .06em;
      color: #2563eb;
      margin-bottom: .12rem;
      line-height: 1;
    }

    .activity-head-actions {
      display: flex;
      align-items: center;
      flex-wrap: wrap;
      gap: .45rem;
    }

    .activity-head-chip {
      display: inline-flex;
      align-items: center;
      gap: 4px;
      padding: .25rem .52rem;
      border-radius: 999px;
      border: 1px solid #bfdbfe;
      background: #ffffff;
      color: #1d4ed8;
      font-size: .62rem;
      font-weight: 700;
      text-transform: uppercase;
      letter-spacing: .05em;
      line-height: 1;
    }

    .activity-head-btn {
      display: inline-flex;
      align-items: center;
      gap: .34rem;
      padding: .45rem .72rem;
      border-radius: .58rem;
      border: 1px solid #bfdbfe;
      background: #ffffff;
      color: #1d4ed8;
      font-size: .7rem;
      font-weight: 700;
      line-height: 1;
    }

    .activity-head-btn:hover:not(:disabled) {
      background: #eff6ff;
      border-color: #93c5fd;
      color: #1e3a8a;
    }

    .activity-filter-shell {
      background: linear-gradient(180deg, #fbfdff 0%, #f8fbff 100%);
    }

    .activity-filter-grid {
      display: grid;
      grid-template-columns: repeat(12, minmax(0, 1fr));
      gap: .6rem;
      align-items: end;
    }

    .activity-filter-field {
      display: flex;
      flex-direction: column;
      gap: .3rem;
      min-width: 0;
    }

    .activity-filter-field label {
      font-size: .66rem;
      font-weight: 700;
      text-transform: uppercase;
      letter-spacing: .05em;
      color: #64748b;
    }

    .activity-filter-input {
      width: 100%;
      border: 1px solid #e2e8f0;
      border-radius: .6rem;
      background: #ffffff;
      color: #475569;
      font-size: .74rem;
      padding: .45rem .62rem;
      line-height: 1.2;
      transition: border-color .24s ease, box-shadow .24s ease;
    }

    .activity-filter-input:focus {
      outline: none;
      border-color: #93c5fd;
      box-shadow: 0 0 0 3px rgba(191, 219, 254, .75);
    }

    .activity-filter-type { grid-column: span 3 / span 3; }
    .activity-filter-read { grid-column: span 3 / span 3; }
    .activity-filter-search { grid-column: span 4 / span 4; }

    .activity-filter-total-wrap {
      grid-column: span 2 / span 2;
      display: flex;
      justify-content: flex-end;
      align-items: center;
    }

    .activity-total-badge {
      display: inline-flex;
      align-items: center;
      justify-content: center;
      min-height: 1.75rem;
      padding: .3rem .7rem;
      border-radius: 999px;
      border: 1px solid #bfdbfe;
      background: #eff6ff;
      color: #1e40af;
      font-size: .68rem;
      font-weight: 700;
      letter-spacing: .01em;
      white-space: nowrap;
      text-align: center;
    }

    .activity-table-shell {
      border: 1px solid #dbeafe;
      border-radius: .85rem;
      overflow: hidden;
      background: #ffffff;
    }

    .activity-pagination {
      display: flex;
      align-items: center;
      justify-content: space-between;
      gap: .6rem;
      flex-wrap: wrap;
      margin-top: .75rem;
    }

    .activity-nav-btn {
      padding: .42rem .72rem;
      border-radius: .58rem;
      border: 1px solid #dbe3ef;
      background: #ffffff;
      color: #475569;
      font-size: .72rem;
      font-weight: 700;
      line-height: 1;
      transition: all .2s ease;
    }

    .activity-nav-btn:hover:not(:disabled) {
      background: #f8fbff;
      border-color: #c6d8fb;
      color: #1e3a8a;
    }

    .activity-nav-btn:disabled {
      opacity: .45;
      cursor: not-allowed;
    }

    .activity-page-meta {
      font-size: .69rem;
      color: #64748b;
      font-weight: 600;
    }

    .pref-suite-card {
      border: 1px solid #dbeafe;
      border-radius: 14px;
      overflow: hidden;
      background: linear-gradient(180deg, #ffffff 0%, #f8fbff 100%);
      box-shadow: 0 8px 22px rgba(2, 38, 68, .08);
      transition: box-shadow .32s cubic-bezier(0.22, 1, 0.36, 1),
                  transform .32s cubic-bezier(0.22, 1, 0.36, 1),
                  border-color .28s ease;
    }

    .pref-suite-card:hover {
      transform: translateY(-2px);
      border-color: #bfdbfe;
      box-shadow: 0 12px 28px rgba(37, 99, 235, .14);
    }

    .pref-suite-head {
      padding: 14px 16px;
      border-bottom: 1px solid #dbeafe;
      background: linear-gradient(135deg, #edf4ff 0%, #f8fbff 58%, #ffffff 100%);
    }

    .pref-kicker {
      font-size: .64rem;
      font-weight: 700;
      text-transform: uppercase;
      letter-spacing: .06em;
      color: #2563eb;
      margin-bottom: .12rem;
      line-height: 1;
    }

    .pref-head-chip {
      display: inline-flex;
      align-items: center;
      gap: 4px;
      padding: .26rem .52rem;
      border-radius: 999px;
      border: 1px solid #bfdbfe;
      background: #ffffff;
      color: #1d4ed8;
      font-size: .62rem;
      font-weight: 700;
      text-transform: uppercase;
      letter-spacing: .05em;
      line-height: 1;
      white-space: nowrap;
    }

    .pref-reset-btn {
      display: inline-flex;
      align-items: center;
      gap: .34rem;
      padding: .46rem .76rem;
      border-radius: .58rem;
      border: 1px solid #bfdbfe;
      background: #ffffff;
      color: #1d4ed8;
      font-size: .72rem;
      font-weight: 700;
      line-height: 1;
    }

    .pref-reset-btn:hover {
      background: #eff6ff;
      border-color: #93c5fd;
      color: #1e3a8a;
    }

    .pref-suite-body {
      padding: 14px 16px;
    }

    .pref-suite-grid {
      display: grid;
      grid-template-columns: repeat(12, minmax(0, 1fr));
      gap: .8rem;
    }

    .pref-panel {
      border: 1px solid #dbeafe;
      border-radius: 12px;
      overflow: hidden;
      background: #ffffff;
      box-shadow: 0 4px 10px rgba(15, 23, 42, .06);
      display: flex;
      flex-direction: column;
      min-width: 0;
    }

    .pref-panel-activity { grid-column: span 8 / span 8; }
    .pref-panel-channels { grid-column: span 4 / span 4; }

    .pref-panel-head {
      padding: 12px 14px;
      border-bottom: 1px solid #dbeafe;
      background: linear-gradient(135deg, #f3f8ff 0%, #f9fbff 100%);
      display: flex;
      align-items: flex-start;
      justify-content: space-between;
      gap: .65rem;
      flex-wrap: wrap;
    }

    .pref-panel-icon {
      width: 32px;
      height: 32px;
      border-radius: 9px;
      display: inline-flex;
      align-items: center;
      justify-content: center;
      background: #dbeafe;
      border: 1px solid #bfdbfe;
      color: #2563eb;
      flex-shrink: 0;
    }

    .pref-panel-chip {
      display: inline-flex;
      align-items: center;
      padding: .23rem .5rem;
      border-radius: 999px;
      border: 1px solid #dbeafe;
      background: #eff6ff;
      color: #1e40af;
      font-size: .64rem;
      font-weight: 700;
      letter-spacing: .01em;
      line-height: 1;
      white-space: nowrap;
    }

    .pref-panel-body {
      padding: 12px 14px;
    }

    .pref-panel-note {
      border: 1px solid #dbeafe;
      background: linear-gradient(180deg, #f8fbff 0%, #eef5ff 100%);
      border-radius: .7rem;
      color: #1e40af;
      font-size: .72rem;
      line-height: 1.4;
      padding: .58rem .68rem;
      margin-bottom: .55rem;
    }

    .pref-panel .setting-row {
      padding: .72rem 0;
      gap: .8rem;
    }

    .pref-panel .setting-row + .setting-row {
      border-top: 1px solid #eff6ff;
    }

    .pref-panel .setting-row > div {
      min-width: 0;
    }

    .pref-panel .setting-row > div > div:first-child {
      color: #334155;
      font-weight: 600;
      font-size: .79rem;
      line-height: 1.35;
    }

    .pref-panel .setting-row > div > div:last-child {
      color: #64748b;
      font-size: .7rem;
      line-height: 1.35;
      margin-top: .15rem;
    }

    .pref-save-shell {
      display: flex;
      align-items: center;
      gap: .72rem;
      border: 1px solid #bfdbfe;
      background: linear-gradient(135deg, #eff6ff 0%, #f8fbff 100%);
      border-radius: .85rem;
      box-shadow: 0 12px 28px rgba(37, 99, 235, .18);
      padding: .62rem .72rem;
      min-width: min(92vw, 460px);
    }

    .pref-save-indicator {
      width: 28px;
      height: 28px;
      border-radius: 8px;
      display: inline-flex;
      align-items: center;
      justify-content: center;
      background: #2563eb;
      color: #ffffff;
      border: 1px solid #1d4ed8;
      box-shadow: 0 4px 10px rgba(37, 99, 235, .24);
      flex-shrink: 0;
    }

    .pref-save-title {
      font-size: .75rem;
      color: #1e3a8a;
      font-weight: 700;
      line-height: 1.2;
    }

    .pref-save-sub {
      font-size: .67rem;
      color: #64748b;
      line-height: 1.25;
      margin-top: .12rem;
    }

    .pref-save-btn {
      margin-left: auto;
      padding: .48rem .88rem;
      border-radius: .62rem;
      border: 1px solid #2563eb;
      background: #2563eb;
      color: #ffffff;
      font-size: .72rem;
      font-weight: 700;
      line-height: 1;
      box-shadow: 0 4px 10px rgba(37, 99, 235, .24);
      white-space: nowrap;
    }

    .pref-save-btn:hover {
      background: #1d4ed8;
      border-color: #1d4ed8;
      box-shadow: 0 8px 14px rgba(37, 99, 235, .28);
    }

    .sent-log-card {
      border: 1px solid #dbeafe;
      border-radius: 14px;
      background: linear-gradient(180deg, #ffffff 0%, #f8fbff 100%);
      box-shadow: 0 8px 22px rgba(2, 38, 68, .08);
      overflow: hidden;
      transition: box-shadow .32s cubic-bezier(0.22, 1, 0.36, 1),
                  transform .32s cubic-bezier(0.22, 1, 0.36, 1),
                  border-color .28s ease;
    }

    .sent-log-card:hover {
      transform: translateY(-2px);
      border-color: #bfdbfe;
      box-shadow: 0 12px 28px rgba(37, 99, 235, .14);
    }

    .sent-log-head {
      background: linear-gradient(135deg, #edf4ff 0%, #f8fbff 58%, #ffffff 100%);
    }

    .sent-log-kicker {
      font-size: .64rem;
      font-weight: 700;
      text-transform: uppercase;
      letter-spacing: .06em;
      color: #2563eb;
      margin-bottom: .12rem;
      line-height: 1;
    }

    .sent-log-head-actions {
      display: flex;
      align-items: center;
      gap: .45rem;
      flex-wrap: wrap;
    }

    .sent-log-head-chip {
      display: inline-flex;
      align-items: center;
      gap: 4px;
      padding: .25rem .52rem;
      border-radius: 999px;
      border: 1px solid #bfdbfe;
      background: #ffffff;
      color: #1d4ed8;
      font-size: .62rem;
      font-weight: 700;
      text-transform: uppercase;
      letter-spacing: .05em;
      line-height: 1;
    }

    .sent-log-refresh-btn {
      display: inline-flex;
      align-items: center;
      gap: .34rem;
      padding: .45rem .72rem;
      border-radius: .58rem;
      border: 1px solid #bfdbfe;
      background: #ffffff;
      color: #1d4ed8;
      font-size: .7rem;
      font-weight: 700;
      line-height: 1;
    }

    .sent-log-refresh-btn:hover {
      background: #eff6ff;
      border-color: #93c5fd;
      color: #1e3a8a;
    }

    .sent-log-filter-shell {
      background: linear-gradient(180deg, #fbfdff 0%, #f8fbff 100%);
    }

    .sent-log-filter-grid {
      display: grid;
      grid-template-columns: repeat(12, minmax(0, 1fr));
      gap: .6rem;
      align-items: end;
    }

    .sent-log-filter-field {
      display: flex;
      flex-direction: column;
      gap: .3rem;
      min-width: 0;
    }

    .sent-log-filter-field label {
      font-size: .66rem;
      font-weight: 700;
      text-transform: uppercase;
      letter-spacing: .05em;
      color: #64748b;
    }

    .sent-log-filter-input {
      width: 100%;
      border: 1px solid #e2e8f0;
      border-radius: .6rem;
      background: #ffffff;
      color: #475569;
      font-size: .74rem;
      padding: .45rem .62rem;
      line-height: 1.2;
      transition: border-color .24s ease, box-shadow .24s ease;
    }

    .sent-log-filter-input:focus {
      outline: none;
      border-color: #93c5fd;
      box-shadow: 0 0 0 3px rgba(191, 219, 254, .75);
    }

    .sent-log-filter-type { grid-column: span 4 / span 4; }
    .sent-log-filter-delivery { grid-column: span 4 / span 4; }

    .sent-log-filter-total-wrap {
      grid-column: span 4 / span 4;
      display: flex;
      justify-content: flex-end;
      align-items: center;
    }

    .sent-log-total-badge {
      display: inline-flex;
      align-items: center;
      justify-content: center;
      min-height: 1.75rem;
      padding: .3rem .7rem;
      border-radius: 999px;
      border: 1px solid #bfdbfe;
      background: #eff6ff;
      color: #1e40af;
      font-size: .68rem;
      font-weight: 700;
      letter-spacing: .01em;
      white-space: nowrap;
      text-align: center;
    }

    .sent-log-table-shell {
      border: 1px solid #dbeafe;
      border-radius: .85rem;
      overflow: hidden;
      background: #ffffff;
    }

    .sent-log-pagination {
      display: flex;
      align-items: center;
      justify-content: space-between;
      gap: .6rem;
      flex-wrap: wrap;
      margin-top: .75rem;
    }

    .sent-log-nav-btn {
      padding: .42rem .72rem;
      border-radius: .58rem;
      border: 1px solid #dbe3ef;
      background: #ffffff;
      color: #475569;
      font-size: .72rem;
      font-weight: 700;
      line-height: 1;
      transition: all .2s ease;
    }

    .sent-log-nav-btn:hover:not(:disabled) {
      background: #f8fbff;
      border-color: #c6d8fb;
      color: #1e3a8a;
    }

    .sent-log-nav-btn:disabled {
      opacity: .45;
      cursor: not-allowed;
    }

    .sent-log-page-meta {
      font-size: .69rem;
      color: #64748b;
      font-weight: 600;
    }

    .setting-row  { display:flex; align-items:center; justify-content:space-between; gap:12px; padding:10px 0; }
    .setting-row + .setting-row { border-top:1px solid #f1f5f9; }

    /* Smooth hover interactions */
    #section-send button,
    #section-activity button,
    #section-preferences button,
    #section-log button {
      transition: background-color .28s ease,
                  border-color .28s ease,
                  color .28s ease,
                  box-shadow .28s ease,
                  transform .28s ease;
    }

    #section-send button:hover:not(:disabled),
    #section-activity button:hover:not(:disabled),
    #section-preferences button:hover:not(:disabled),
    #section-log button:hover:not(:disabled) {
      transform: translateY(-1px);
    }

    #section-send .compose-card,
    #section-send .bg-white.rounded-xl.border,
    #section-activity .activity-feed-card,
    #section-preferences .pref-suite-card,
    #section-preferences .pref-panel,
    #section-log .sent-log-card,
    #section-activity .bg-white.rounded-xl,
    #section-preferences .setting-card,
    #section-log .bg-white.rounded-xl {
      transition: box-shadow .32s cubic-bezier(0.22, 1, 0.36, 1),
                  transform .32s cubic-bezier(0.22, 1, 0.36, 1),
                  border-color .28s ease;
    }

    #section-send .compose-card:hover,
    #section-send .bg-white.rounded-xl.border:hover,
    #section-activity .activity-feed-card:hover,
    #section-preferences .pref-suite-card:hover,
    #section-log .sent-log-card:hover,
    #section-activity .bg-white.rounded-xl:hover,
    #section-preferences .setting-card:hover,
    #section-log .bg-white.rounded-xl:hover {
      transform: translateY(-2px);
    }

    /* Toggle switch */
    .switch        { position:relative; display:inline-block; width:44px; height:24px; flex-shrink:0; }
    .switch input  { opacity:0; width:0; height:0; }
    .slider {
      position:absolute;
      inset:0;
      background:#DBEAFE;
      border:1px solid #BFDBFE;
      border-radius:24px;
      cursor:pointer;
      transition: background-color .28s ease, border-color .28s ease, box-shadow .28s ease;
    }
    .slider:before {
      content:'';
      position:absolute;
      width:18px;
      height:18px;
      left:2px;
      top:2px;
      background:#fff;
      border:1px solid #DBEAFE;
      border-radius:50%;
      box-shadow:0 1px 2px rgba(15, 23, 42, .18);
      transition: transform .28s cubic-bezier(0.22, 1, 0.36, 1), border-color .28s ease;
    }
    .switch:hover .slider { border-color:#93C5FD; }
    .switch input:focus-visible + .slider { box-shadow:0 0 0 3px rgba(59, 130, 246, .28); }
    .switch input:checked + .slider {
      background:#2563EB;
      border-color:#1D4ED8;
      box-shadow:inset 0 0 0 1px rgba(255, 255, 255, .08);
    }
    .switch input:checked + .slider:before {
      transform:translateX(20px);
      border-color:#BFDBFE;
    }

    /* Toast */
    #toastBar {
      position:fixed; top:80px; right:16px;
      z-index:9999; display:flex; gap:8px; flex-direction:column; align-items:flex-end;
      pointer-events:none;
    }
    .toast {
      padding:10px 20px; border-radius:10px; font-size:.875rem; font-weight:600;
      color:#fff; box-shadow:0 4px 20px rgba(0,0,0,.15);
      animation: toastIn .25s ease;
    }
    .toast.success { background:#10B981; }
    .toast.error   { background:#EF4444; }
    @keyframes toastIn { from{opacity:0;transform:translateX(16px)} to{opacity:1;transform:translateX(0)} }

    /* Save bar */
    #saveBar {
      position:fixed; left:50%; bottom:24px; transform:translateX(-50%);
      z-index:9000; transition: opacity .25s;
    }
    #saveBar.hidden { opacity:0; pointer-events:none; }

    /* Tag pills for targeted users */
    .user-tag {
      display:inline-flex;
      align-items:center;
      gap:4px;
      padding:3px 10px 3px 8px;
      background:linear-gradient(180deg, #eff6ff 0%, #e7f0ff 100%);
      color:#1d4ed8;
      border-radius:999px;
      font-size:.72rem;
      font-weight:700;
      border:1px solid #bfdbfe;
    }
    .user-tag button {
      background:none;
      border:none;
      cursor:pointer;
      color:#1d4ed8;
      font-size:.9rem;
      line-height:1;
      padding:0;
    }

    /* Sent log table */
    .log-table { width:100%; border-collapse:collapse; font-size:.82rem; }
    .log-table th { background:#f8fafc; color:#64748b; font-weight:700; text-transform:uppercase; letter-spacing:.04em; font-size:.7rem; padding:10px 12px; text-align:left; border-bottom:1px solid #e2e8f0; }
    .log-table td { padding:10px 12px; border-bottom:1px solid #f1f5f9; color:#334155; vertical-align:top; transition: background-color .24s ease, color .24s ease; }
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
    .section-tabs-wrap {
      display:flex;
      gap:8px;
      flex-wrap:wrap;
      padding:6px;
      border:1px solid #DBEAFE;
      border-radius:12px;
      background:linear-gradient(180deg, #F8FBFF 0%, #EFF6FF 100%);
      box-shadow:0 1px 3px rgba(15, 23, 42, .06);
    }

    .section-tab {
      display:inline-flex;
      align-items:center;
      gap:8px;
      padding:8px 14px;
      border-radius:9px;
      font-size:.82rem;
      font-weight:700;
      color:#475569;
      cursor:pointer;
      border:1px solid transparent;
      background:transparent;
      transition:background-color .28s ease, color .28s ease, transform .28s ease, border-color .28s ease, box-shadow .28s ease;
    }

    .section-tab .tab-icon {
      width:24px;
      height:24px;
      border-radius:7px;
      display:inline-flex;
      align-items:center;
      justify-content:center;
      background:#FFFFFF;
      border:1px solid #E2E8F0;
      color:#64748B;
      font-size:.78rem;
      transition:inherit;
      flex-shrink:0;
    }

    .section-tab:hover:not(.active) {
      background:#FFFFFF;
      color:#1E3A8A;
      border-color:#BFDBFE;
      box-shadow:0 2px 8px rgba(37, 99, 235, .12);
      transform: translateY(-1px);
    }

    .section-tab:hover:not(.active) .tab-icon {
      background:#EFF6FF;
      color:#2563EB;
      border-color:#BFDBFE;
    }

    .section-tab.active {
      background:#2563EB;
      color:#FFFFFF;
      border-color:#1D4ED8;
      box-shadow:0 6px 14px rgba(37, 99, 235, .28);
    }

    .section-tab.active .tab-icon {
      background:rgba(255, 255, 255, .16);
      color:#FFFFFF;
      border-color:rgba(255, 255, 255, .28);
    }

    .section-tab:focus-visible {
      outline:none;
      box-shadow:0 0 0 3px rgba(59, 130, 246, .28);
    }

    #activityUnreadBadge {
      min-width:18px;
      height:18px;
      padding:0 5px;
      border-radius:999px;
      box-shadow:0 0 0 2px #FFFFFF;
    }

    .section-tab.active #activityUnreadBadge {
      background:#FFFFFF;
      color:#1D4ED8;
      box-shadow:none;
    }

    @media (max-width: 768px) {
      .section-tab {
        flex:1 1 calc(50% - 8px);
        justify-content:center;
      }

      .activity-filter-type,
      .activity-filter-read,
      .activity-filter-search,
      .activity-filter-total-wrap {
        grid-column: span 12 / span 12;
      }

      .activity-filter-total-wrap {
        justify-content: flex-start;
      }

      .activity-total-badge {
        width: 100%;
        justify-content: flex-start;
      }

      .activity-head-actions {
        width: 100%;
      }

      .activity-head-chip {
        margin-right: auto;
      }

      .activity-head-btn {
        flex: 1 1 calc(50% - .4rem);
        justify-content: center;
      }

      .pref-panel-activity,
      .pref-panel-channels {
        grid-column: span 12 / span 12;
      }

      .pref-head-chip {
        order: 1;
      }

      .pref-reset-btn {
        order: 2;
      }

      .sent-log-head-actions {
        width: 100%;
      }

      .sent-log-head-chip {
        margin-right: auto;
      }

      .sent-log-refresh-btn {
        flex: 1 1 auto;
        justify-content: center;
      }

      .sent-log-filter-type,
      .sent-log-filter-delivery,
      .sent-log-filter-total-wrap {
        grid-column: span 12 / span 12;
      }

      .sent-log-filter-total-wrap {
        justify-content: flex-start;
      }

      .sent-log-total-badge {
        width: 100%;
        justify-content: flex-start;
      }

      .pref-save-shell {
        min-width: min(92vw, 520px);
        flex-wrap: wrap;
      }

      .pref-save-btn {
        width: 100%;
      }
    }

    @media (max-width: 1024px) {
      .activity-filter-type,
      .activity-filter-read { grid-column: span 4 / span 4; }

      .activity-filter-search { grid-column: span 4 / span 4; }

      .activity-filter-total-wrap { grid-column: span 12 / span 12; justify-content: flex-start; }

      .sent-log-filter-type,
      .sent-log-filter-delivery {
        grid-column: span 6 / span 6;
      }

      .sent-log-filter-total-wrap {
        grid-column: span 12 / span 12;
        justify-content: flex-start;
      }
    }

    /* User search dropdown */
    #userSearchDropdown { max-height:220px; overflow-y:auto; }
    .user-option { padding:8px 12px; cursor:pointer; font-size:.82rem; border-bottom:1px solid #f1f5f9; transition: background-color .24s ease, color .24s ease; }
    .user-option:hover { background:#EFF6FF; }
    .user-option .meta  { font-size:.7rem; color:#94a3b8; }

    /* ── User Activity table ── */
    .act-dot { width:8px; height:8px; border-radius:50%; background:#2563EB; display:inline-block; flex-shrink:0; }
    .act-row-unread td { background:#f8fbff; }
    .act-row-unread td:first-child { border-left:3px solid #2563EB; }
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
      background:#EFF6FF; color:#2563EB; border:1px solid #BFDBFE;
      cursor:pointer; text-decoration:none; transition:background-color .24s ease, color .24s ease, border-color .24s ease, transform .24s ease; white-space:nowrap;
    }
    .act-view-btn:hover { background:#DBEAFE; color:#1D4ED8; transform: translateY(-1px); }

    #activityTable thead th {
      background: linear-gradient(180deg, #f8fbff 0%, #eef5ff 100%);
      color:#1E40AF;
      border-bottom:1px solid #DBEAFE;
      text-transform: uppercase;
      letter-spacing: .05em;
      font-size: .68rem;
    }

    #activityTable tbody tr {
      border-top: 1px solid #eff6ff;
      transition: background-color .2s ease;
    }

    #activityTable tbody td {
      padding: .72rem .9rem;
      font-size: .74rem;
      color: #475569;
      vertical-align: top;
    }

    #activityTable tbody tr:hover td { background:#F8FBFF; }

    #sentLogTable thead th {
      background: linear-gradient(180deg, #f8fbff 0%, #eef5ff 100%);
      color:#1E40AF;
      border-bottom:1px solid #DBEAFE;
      text-transform: uppercase;
      letter-spacing: .05em;
      font-size: .68rem;
    }

    #sentLogTable tbody tr {
      border-top: 1px solid #eff6ff;
      transition: background-color .2s ease;
    }

    #sentLogTable tbody td {
      padding: .72rem .9rem;
      font-size: .74rem;
      color: #475569;
      vertical-align: top;
    }

    #sentLogTable tbody tr:hover td { background:#F8FBFF; }
  </style>
</head>

<body class="bg-gray-50 text-gray-800 font-sans">
<div class="flex min-h-screen">

  @include('admin.layouts.sidebar')

  <main class="flex-1 h-screen overflow-y-auto overflow-x-hidden">
    @include('admin.layouts.topnav', ['pageTitle' => 'Notifications'])

    <section class="px-6 py-6 space-y-6 max-w-screen-xl mx-auto">

      {{-- ── Section Tabs ──────────────────────────────────────────── --}}
      <div class="section-tabs-wrap">
        <button class="section-tab active" data-section="send">
          <span class="tab-icon"><i class="fi fi-rr-paper-plane"></i></span>
          <span>Send Notifications</span>
        </button>
        <button class="section-tab" data-section="activity">
          <span class="tab-icon"><i class="fi fi-rr-bell"></i></span>
          <span>User Activity</span>
          <span id="activityUnreadBadge" class="hidden ml-1 inline-flex items-center justify-center rounded-full bg-red-500 text-white text-[10px] font-bold leading-none"></span>
        </button>
        <button class="section-tab" data-section="preferences">
          <span class="tab-icon"><i class="fi fi-rr-settings"></i></span>
          <span>My Preferences</span>
        </button>
        <button class="section-tab" data-section="log">
          <span class="tab-icon"><i class="fi fi-rr-list"></i></span>
          <span>Sent Log</span>
        </button>
      </div>

      {{-- ════════════════════════════════════════════════════════════
           SECTION 1 – SEND NOTIFICATIONS
      ════════════════════════════════════════════════════════════ --}}
      <div id="section-send" class="section-content">
        <div class="grid grid-cols-1 xl:grid-cols-2 gap-4">

          {{-- ── Mass Announcement ──────────────────────────────── --}}
          <div class="compose-card compose-card-mass h-full flex flex-col">
            <div class="compose-head">
              <div class="flex items-center gap-2.5 min-w-0">
                <div class="compose-icon-shell">
                  <i class="fi fi-rr-megaphone text-base"></i>
                </div>
                <div class="min-w-0">
                  <p class="compose-kicker">Broadcast Desk</p>
                  <h2 class="font-semibold text-sm leading-tight text-gray-800">Mass Announcement</h2>
                  <p class="text-[11px] text-gray-500 truncate">Broadcast to all property owners and contractors</p>
                </div>
              </div>
              <span class="compose-badge flex-shrink-0">
                <i class="fi fi-rr-users text-[10px]"></i>
                All Users
              </span>
            </div>

            <div class="compose-body space-y-3.5 flex-1">
              <div class="flex flex-wrap gap-1.5">
                <span class="compose-chip">Property Owners</span>
                <span class="compose-chip">Contractors</span>
                <span class="compose-chip">Platform-wide update</span>
              </div>

              <p class="compose-callout">
                Best for platform-wide updates such as maintenance notices, policy reminders, and urgent announcements.
              </p>

              <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                <div class="sm:col-span-2 compose-field">
                  <label>Title <span class="text-red-500">*</span></label>
                  <input id="ann-title" type="text" maxlength="255" placeholder="Announcement title..."
                    class="compose-input">
                </div>
                <div class="compose-field">
                  <label>Delivery <span class="text-red-500">*</span></label>
                  <select id="ann-delivery"
                    class="compose-input">
                    <option value="in-app">In-App Only</option>
                    <option value="email">Email Only</option>
                    <option value="both" selected>Both</option>
                  </select>
                </div>
              </div>

              <div class="compose-field">
                <label>Message <span class="text-red-500">*</span></label>
                <textarea id="ann-message" rows="3" placeholder="Write your announcement here..."
                  class="compose-input resize-none"></textarea>
              </div>

              <div class="compose-actions flex items-center justify-between gap-2.5 flex-wrap">
                <p class="compose-action-note">This notification will appear instantly on user dashboards based on selected delivery.</p>
                <button id="btnSendAnnouncement"
                  class="compose-submit-btn transition">
                  <i class="fi fi-rr-paper-plane"></i>
                  Send to All Users
                </button>
                <span id="ann-sending" class="hidden text-xs text-gray-500 flex items-center gap-1">
                  <i class="fi fi-rr-spinner animate-spin"></i>
                  Sending...
                </span>
              </div>
            </div>
          </div>

          {{-- ── Targeted Notification ──────────────────────────── --}}
          <div class="compose-card compose-card-target h-full flex flex-col">
            <div class="compose-head is-target rounded-t-xl">
              <div class="flex items-center gap-2.5 min-w-0">
                <div class="compose-icon-shell">
                  <i class="fi fi-rr-target text-base"></i>
                </div>
                <div class="min-w-0">
                  <p class="compose-kicker">Direct Messaging</p>
                  <h2 class="font-semibold text-sm leading-tight text-gray-800">Targeted Notification</h2>
                  <p class="text-[11px] text-gray-500 truncate">Notify selected users by name or email</p>
                </div>
              </div>
              <span class="compose-badge flex-shrink-0">
                <i class="fi fi-rr-target text-[10px]"></i>
                Selected Users
              </span>
            </div>

            <div class="compose-body space-y-3.5 flex-1">
              <div class="flex flex-wrap gap-1.5">
                <span class="compose-chip">Account reminders</span>
                <span class="compose-chip">Follow-up notices</span>
                <span class="compose-chip">Case-specific communication</span>
              </div>

              <p class="compose-callout">
                Use this for reminders, follow-ups, and user-specific account communications.
              </p>

              {{-- User search + tags --}}
              <div class="compose-recipient-panel">
                <div class="flex items-center justify-between gap-2 mb-1.5">
                  <label class="text-[11px] font-semibold text-gray-600 uppercase tracking-wide">Select Recipients <span class="text-red-500">*</span></label>
                  <span id="selectedUsersCount" class="recipient-count">0 selected</span>
                </div>

                <div class="relative">
                  <input id="userSearchInput" type="text" autocomplete="off"
                    placeholder="Search by username or email..."
                    class="compose-input">
                  <div id="userSearchDropdown"
                    class="absolute z-50 w-full bg-white border border-gray-200 rounded-lg shadow-lg hidden mt-1"></div>
                </div>

                <div id="selectedUsersContainer" class="flex flex-wrap gap-1.5 mt-2 min-h-[24px]"></div>
                <input type="hidden" id="target-user-ids" value="">
              </div>

              <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                <div class="sm:col-span-2 compose-field">
                  <label>Title <span class="text-red-500">*</span></label>
                  <input id="tgt-title" type="text" maxlength="255" placeholder="Notification title..."
                    class="compose-input">
                </div>
                <div class="compose-field">
                  <label>Delivery <span class="text-red-500">*</span></label>
                  <select id="tgt-delivery"
                    class="compose-input">
                    <option value="in-app">In-App Only</option>
                    <option value="email">Email Only</option>
                    <option value="both" selected>Both</option>
                  </select>
                </div>
              </div>

              <div class="compose-field">
                <label>Message <span class="text-red-500">*</span></label>
                <textarea id="tgt-message" rows="3" placeholder="Write your message here..."
                  class="compose-input resize-none"></textarea>
              </div>

              <div class="compose-actions flex items-center gap-2.5 flex-wrap">
                <p class="compose-action-note">Only selected recipients will receive this message based on chosen delivery channel.</p>
                <div class="ml-auto w-full sm:w-auto flex items-center justify-end gap-2.5">
                  <button id="btnSendTargeted"
                    class="compose-submit-btn transition flex items-center gap-1.5">
                    <i class="fi fi-rr-target"></i>
                    Send to Selected Users
                  </button>
                  <span id="tgt-sending" class="hidden text-xs text-gray-500 flex items-center gap-1">
                    <i class="fi fi-rr-spinner animate-spin"></i>
                    Sending...
                  </span>
                </div>
              </div>
            </div>
          </div>

        </div>
      </div>

      {{-- ════════════════════════════════════════════════════════════
           SECTION 2 – USER ACTIVITY FEED
      ════════════════════════════════════════════════════════════ --}}
      <div id="section-activity" class="section-content hidden">
        <div class="activity-feed-card">

          {{-- Header --}}
          <div class="activity-feed-head px-5 py-4 border-b border-blue-100 flex items-start justify-between flex-wrap gap-3">
            <div class="min-w-0">
              <div class="flex items-center gap-2.5">
                <div class="w-9 h-9 rounded-lg bg-blue-100 border border-blue-200 text-blue-600 flex items-center justify-center flex-shrink-0">
                  <i class="fi fi-ss-bell text-base"></i>
                </div>
                <div>
                  <p class="activity-kicker">Live Monitoring</p>
                  <h2 class="font-semibold text-sm text-gray-800 leading-tight">User Activity Notifications</h2>
                  <p class="text-[11px] text-gray-500 mt-0.5">Latest account and security events based on your notification preferences.</p>
                </div>
              </div>
            </div>

            <div class="activity-head-actions">
              <span class="activity-head-chip"><i class="fi fi-rr-pulse text-[10px]"></i> Real-Time Feed</span>
              <button id="actMarkAllRead"
                class="activity-head-btn">
                <i class="fi fi-rr-check-double"></i> Mark all read
              </button>
              <button id="actRefresh"
                class="activity-head-btn">
                <i class="fi fi-rr-refresh"></i> Refresh
              </button>
            </div>
          </div>

          {{-- Filters --}}
          <div class="activity-filter-shell px-5 py-4 border-b border-blue-100">
            <div class="activity-filter-grid">
              <div class="activity-filter-field activity-filter-type">
                <label for="actFilterType">Activity Type</label>
                <select id="actFilterType" class="activity-filter-input">
                  <option value="">All activity types</option>
                  <option value="user_registered">New User Registration</option>
                  <option value="failed_login_attempt">Failed Login Attempt</option>
                  <option value="project_reported">Project Reported</option>
                  <option value="profile_updated">Profile Updated</option>
                  <option value="password_reset">Password Reset Requested</option>
                  <option value="email_verified">Email Verified</option>
                  <option value="account_status_changed">Account Suspended/Unsuspended</option>
                </select>
              </div>

              <div class="activity-filter-field activity-filter-read">
                <label for="actFilterRead">Read Status</label>
                <select id="actFilterRead" class="activity-filter-input">
                  <option value="">All</option>
                  <option value="0">Unread</option>
                  <option value="1">Read</option>
                </select>
              </div>

              <div class="activity-filter-field activity-filter-search">
                <label for="actSearch">Search User</label>
                <div class="relative">
                  <i class="fi fi-rr-search absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-xs"></i>
                  <input id="actSearch" type="text" placeholder="Search username or email…" class="activity-filter-input pl-8">
                </div>
              </div>

              <div class="activity-filter-total-wrap">
                <span id="actTotalBadge" class="activity-total-badge">Loading...</span>
              </div>
            </div>
          </div>

          {{-- Table --}}
          <div class="px-5 py-4">
            <div class="activity-table-shell overflow-x-auto">
              <table class="log-table activity-table" id="activityTable">
                <thead>
                  <tr>
                    <th style="width:18px"></th>{{-- unread dot --}}
                    <th>Activity</th>
                    <th>User</th>
                    <th>Details</th>
                    <th>Date &amp; Time</th>
                    <!-- <th style="width:80px">Action</th>-->
                  </tr>
                </thead>
                <tbody id="activityTableBody">
                  <tr><td colspan="5" class="text-center py-10 text-gray-400 text-sm">Loading…</td></tr>
                </tbody>
              </table>
            </div>

            {{-- Pagination --}}
            <div class="activity-pagination">
              <button id="actPrevBtn" class="activity-nav-btn" disabled>← Prev</button>
              <span id="actPageInfo" class="activity-page-meta"></span>
              <button id="actNextBtn" class="activity-nav-btn" disabled>Next →</button>
            </div>
          </div>
        </div>
      </div>

      {{-- ════════════════════════════════════════════════════════════
           SECTION 3 – PREFERENCES
      ════════════════════════════════════════════════════════════ --}}
      <div id="section-preferences" class="section-content hidden space-y-6">
        <div class="pref-suite-card">
          <div class="pref-suite-head">
            <div class="flex items-start justify-between gap-3 flex-wrap">
              <div class="min-w-0">
                <div class="flex items-center gap-2.5">
                  <div class="w-9 h-9 rounded-lg bg-blue-100 border border-blue-200 text-blue-600 flex items-center justify-center flex-shrink-0">
                    <i class="fi fi-rr-settings text-base"></i>
                  </div>
                  <div>
                    <p class="pref-kicker">Preferences Workspace</p>
                    <h2 class="text-sm font-semibold text-gray-800 leading-tight">Notification Preferences</h2>
                    <p class="text-[11px] text-gray-500 mt-0.5">Configure activity alerts and channels from one professional control center.</p>
                  </div>
                </div>
              </div>

              <div class="flex items-center gap-2 flex-wrap">
                <span class="pref-head-chip">
                  <i class="fi fi-rr-shield-check text-[10px]"></i>
                  8 Active Controls
                </span>
                <button id="resetDefaultsBtn" class="pref-reset-btn" type="button">
                  <i class="fi fi-rr-rotate-right text-[12px]"></i>
                  Reset to defaults
                </button>
              </div>
            </div>
          </div>

          <div class="pref-suite-body">
            <div class="pref-suite-grid">

              {{-- User Activity --}}
              <div class="pref-panel pref-panel-activity">
                <div class="pref-panel-head">
                  <div class="flex items-center gap-2.5 min-w-0">
                    <span class="pref-panel-icon"><i class="fi fi-ss-users text-sm"></i></span>
                    <div>
                      <div class="font-semibold text-sm text-gray-800">User Activity</div>
                      <p class="text-[11px] text-gray-500 mt-0.5">Account and security related updates.</p>
                    </div>
                  </div>
                  <span class="pref-panel-chip">7 Event Rules</span>
                </div>

                <div class="pref-panel-body">
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
                      <div>{{ $label }}</div>
                      <div>{{ $desc }}</div>
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
              <div class="pref-panel pref-panel-channels">
                <div class="pref-panel-head">
                  <div class="flex items-center gap-2.5 min-w-0">
                    <span class="pref-panel-icon"><i class="fi fi-ss-megaphone text-sm"></i></span>
                    <div>
                      <div class="font-semibold text-sm text-gray-800">Notification Channels</div>
                      <p class="text-[11px] text-gray-500 mt-0.5">Choose how alerts are delivered.</p>
                    </div>
                  </div>
                  <span class="pref-panel-chip">Delivery</span>
                </div>

                <div class="pref-panel-body">
                  <p class="pref-panel-note">Channel settings control where alerts appear after activity rules are triggered.</p>
                  <div class="setting-row">
                    <div>
                      <div>Email Notifications</div>
                      <div>Receive important updates in your inbox.</div>
                    </div>
                    <label class="switch">
                      <input type="checkbox" class="setting-toggle" data-setting="channel_email" checked>
                      <span class="slider"></span>
                    </label>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>

        {{-- Sticky Save Bar --}}
        <div id="saveBar" class="hidden">
          <div class="pref-save-shell">
            <span class="pref-save-indicator"><i class="fi fi-rr-disk text-[12px]"></i></span>
            <div class="min-w-0">
              <p class="pref-save-title">Unsaved Preference Changes</p>
              <p class="pref-save-sub">Apply your latest alert and delivery updates.</p>
            </div>
            <button id="saveSettingsBtn" class="pref-save-btn" type="button">
              Save changes
            </button>
          </div>
        </div>
      </div>

      {{-- ════════════════════════════════════════════════════════════
           SECTION 4 – SENT LOG
      ════════════════════════════════════════════════════════════ --}}
      <div id="section-log" class="section-content hidden">
        <div class="sent-log-card">
          <div class="sent-log-head px-5 py-4 border-b border-blue-100 flex items-start justify-between flex-wrap gap-3">
            <div class="min-w-0">
              <div class="flex items-center gap-2.5">
                <div class="w-9 h-9 rounded-lg bg-blue-100 border border-blue-200 text-blue-600 flex items-center justify-center flex-shrink-0">
                  <i class="fi fi-rr-list text-base"></i>
                </div>
                <div>
                  <p class="sent-log-kicker">Delivery History</p>
                  <h2 class="font-semibold text-sm text-gray-800 leading-tight">Sent Notifications</h2>
                  <p class="text-[11px] text-gray-500 mt-0.5">History of all announcements and targeted messages you have sent.</p>
                </div>
              </div>
            </div>

            <div class="sent-log-head-actions">
              <span class="sent-log-head-chip"><i class="fi fi-rr-time-past text-[10px]"></i> Audit Trail</span>
              <button id="refreshLog" class="sent-log-refresh-btn" type="button">
                <i class="fi fi-rr-refresh"></i> Refresh
              </button>
            </div>
          </div>

          {{-- Filters --}}
          <div class="sent-log-filter-shell px-5 py-4 border-b border-blue-100">
            <div class="sent-log-filter-grid">
              <div class="sent-log-filter-field sent-log-filter-type">
                <label for="logFilterType">Notification Type</label>
                <select id="logFilterType" class="sent-log-filter-input">
                  <option value="">All types</option>
                  <option value="all">Mass Announcement</option>
                  <option value="targeted">Targeted</option>
                </select>
              </div>

              <div class="sent-log-filter-field sent-log-filter-delivery">
                <label for="logFilterDelivery">Delivery Channel</label>
                <select id="logFilterDelivery" class="sent-log-filter-input">
                  <option value="">All delivery</option>
                  <option value="in-app">In-App</option>
                  <option value="email">Email</option>
                  <option value="both">Both</option>
                </select>
              </div>

              <div class="sent-log-filter-total-wrap">
                <span id="logTotalBadge" class="sent-log-total-badge">Loading...</span>
              </div>
            </div>
          </div>

          {{-- Table --}}
          <div class="px-5 py-4">
            <div class="sent-log-table-shell overflow-x-auto">
              <table class="log-table sent-log-table" id="sentLogTable">
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
            <div class="sent-log-pagination">
              <button id="logPrevBtn" class="sent-log-nav-btn" disabled>← Prev</button>
              <span id="logPageInfo" class="sent-log-page-meta"></span>
              <button id="logNextBtn" class="sent-log-nav-btn" disabled>Next →</button>
            </div>
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
  const countEl = document.getElementById('selectedUsersCount');
  if (countEl) {
    countEl.textContent = `${ids.length} selected`;
    countEl.classList.toggle('has-users', ids.length > 0);
  }
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
    tbody.innerHTML = '<tr><td colspan="5" class="text-center py-10 text-gray-400 text-sm"><i class="fi fi-rr-spinner animate-spin mr-1"></i>Loading…</td></tr>';

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
          tbody.innerHTML = '<tr><td colspan="5" class="text-center py-10 text-gray-400 text-sm">No activity found.</td></tr>';
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
            </tr>`;
        }).join('');

        // Mark rows as read when rendered (auto-mark visible rows)
        const unreadIds = activities.filter(r => !r.is_read).map(r => r.id);
        if (unreadIds.length) {
            api('/admin/notifications/activity/mark-read', 'POST', { ids: unreadIds }).catch(() => {});
        }

    } catch (e) {
      tbody.innerHTML = '<tr><td colspan="5" class="text-center py-10 text-red-400 text-sm">Failed to load activities. Please refresh.</td></tr>';
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