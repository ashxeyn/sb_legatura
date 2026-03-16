<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <title>Proof of Payments - Legatura Admin</title>
  <link rel="icon" type="image/svg+xml" href="{{ asset('img/logo2.0-favicon.svg') }}">

  <script src="https://cdn.tailwindcss.com"></script>
  <link rel="stylesheet" href="{{ asset('css/admin/home/mainComponents.css') }}">
  <link rel="stylesheet" href="{{ asset('css/admin/globalManagement/proofOfpayments.css') }}">

  <link rel='stylesheet' href='https://cdn-uicons.flaticon.com/3.0.0/uicons-solid-straight/css/uicons-solid-straight.css'>
  <link rel='stylesheet' href='https://cdn-uicons.flaticon.com/3.0.0/uicons-solid-rounded/css/uicons-solid-rounded.css'>
  <link rel='stylesheet' href='https://cdn-uicons.flaticon.com/3.0.0/uicons-bold-rounded/css/uicons-bold-rounded.css'>
  <link rel='stylesheet' href='https://cdn-uicons.flaticon.com/3.0.0/uicons-regular-rounded/css/uicons-regular-rounded.css'>

  <script src="{{ asset('js/admin/home/mainComponents.js') }}" defer></script>

  <style>
    @keyframes _fi_spin { to { transform: rotate(360deg); } }
    .fi-spin { animation: _fi_spin .7s linear infinite; display: inline-block; }
    .date-pill input[type="date"]::-webkit-calendar-picker-indicator {
      opacity: 0.5; cursor: pointer;
      filter: invert(30%) sepia(80%) saturate(400%) hue-rotate(210deg);
    }
    .date-pill input[type="date"]::-webkit-calendar-picker-indicator:hover { opacity: 1; }

    .payment-view-modal {
      --payment-modal-from: #f97316;
      --payment-modal-to: #ea580c;
      --payment-modal-border: #f97316;
      --payment-modal-muted: rgba(255, 255, 255, 0.82);
      --payment-modal-icon-bg: rgba(255, 255, 255, 0.2);
      --payment-modal-icon-border: rgba(255, 255, 255, 0.24);
    }

    .payment-view-modal[data-status-theme="submitted"] {
      --payment-modal-from: #f97316;
      --payment-modal-to: #ea580c;
      --payment-modal-border: #f97316;
    }

    .payment-view-modal[data-status-theme="approved"] {
      --payment-modal-from: #16a34a;
      --payment-modal-to: #0d9488;
      --payment-modal-border: #16a34a;
    }

    .payment-view-modal[data-status-theme="rejected"] {
      --payment-modal-from: #dc2626;
      --payment-modal-to: #f97316;
      --payment-modal-border: #dc2626;
    }

    .payment-view-modal[data-status-theme="deleted"] {
      --payment-modal-from: #4b5563;
      --payment-modal-to: #6b7280;
      --payment-modal-border: #4b5563;
    }

    .payment-view-modal-header {
      background: linear-gradient(to right, var(--payment-modal-from), var(--payment-modal-to)) !important;
      border-bottom-color: var(--payment-modal-border) !important;
      color: #fff !important;
    }

    .payment-view-modal-header .payment-modal-icon-wrap {
      background: var(--payment-modal-icon-bg) !important;
      border-color: var(--payment-modal-icon-border) !important;
    }

    .payment-view-modal-header .payment-modal-subtitle {
      color: var(--payment-modal-muted) !important;
    }

    .payment-view-modal-header [data-close-modal] {
      color: rgba(255, 255, 255, 0.85) !important;
    }

    .payment-view-modal-header [data-close-modal]:hover {
      color: #fff !important;
      background: rgba(255, 255, 255, 0.14) !important;
    }
  </style>
</head>

<body class="bg-gray-50 text-gray-800 font-sans">
  <div class="flex min-h-screen">

    @include('admin.layouts.sidebar')

    <main class="flex-1">

      {{-- Standard topnav — NO filter injected here --}}
      @include('admin.layouts.topnav', ['pageTitle' => 'Proof of Payments', 'searchPlaceholder' => 'Search project or contractor...'])

      <div class="p-6 lg:p-7">

        {{-- ══ STAT CARDS ══ --}}
        @php
          $totalStat     = $stats['total']     ?? 0;
          $pendingStat   = $stats['pending']   ?? 0;
          $failedStat    = $stats['failed']    ?? 0;
          $completedStat = $stats['completed'] ?? 0;
          $pendingPct    = $totalStat > 0 ? min(100, round(($pendingStat   / $totalStat) * 100)) : 0;
          $failedPct     = $totalStat > 0 ? min(100, round(($failedStat    / $totalStat) * 100)) : 0;
          $completedPct  = $totalStat > 0 ? min(100, round(($completedStat / $totalStat) * 100)) : 0;
        @endphp

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">

          <div class="stat-card bg-white rounded-xl shadow-sm p-4 hover:shadow-md hover:-translate-y-0.5 transition-all duration-200 cursor-pointer">
            <div class="flex justify-between items-start gap-3 mb-3">
              <div>
                <p class="text-[11px] font-semibold uppercase tracking-[0.14em] text-gray-500 mb-1.5">Total Proof Collected</p>
                <h2 class="text-3xl font-bold leading-none text-orange-500 stat-number">{{ number_format($totalStat) }}</h2>
                <div class="stat-badge flex items-center gap-1.5 px-2 py-1 rounded-full bg-blue-100 mt-2 w-fit">
                  <i class="fi fi-sr-database text-[10px] text-blue-600"></i>
                  <span class="text-[11px] font-semibold text-blue-600">All records</span>
                </div>
              </div>
              <div class="stat-icon-wrap bg-blue-100 p-2.5 rounded-lg"><i class="fi fi-sr-document text-lg text-blue-600"></i></div>
            </div>
            <p class="text-[11px] text-gray-400">All time</p>
          </div>

          <div class="stat-card bg-white rounded-xl shadow-sm p-4 hover:shadow-md hover:-translate-y-0.5 transition-all duration-200 cursor-pointer">
            <div class="flex justify-between items-start gap-3 mb-3">
              <div>
                <p class="text-[11px] font-semibold uppercase tracking-[0.14em] text-gray-500 mb-1.5">Pending Verifications</p>
                <h2 class="text-3xl font-bold leading-none text-orange-500 stat-number">{{ number_format($pendingStat) }}</h2>
                <div class="stat-badge flex items-center gap-1.5 px-2 py-1 rounded-full bg-orange-100 mt-2 w-fit">
                  <i class="fi fi-sr-time-check text-[10px] text-orange-600"></i>
                  <span class="text-[11px] font-semibold text-orange-600">Awaiting review</span>
                </div>
              </div>
              <div class="stat-icon-wrap bg-orange-100 p-2.5 rounded-lg"><i class="fi fi-sr-time-check text-lg text-orange-600"></i></div>
            </div>
            <p class="text-[11px] text-gray-400">Submitted status</p>
          </div>

          <div class="stat-card bg-white rounded-xl shadow-sm p-4 hover:shadow-md hover:-translate-y-0.5 transition-all duration-200 cursor-pointer">
            <div class="flex justify-between items-start gap-3 mb-3">
              <div>
                <p class="text-[11px] font-semibold uppercase tracking-[0.14em] text-gray-500 mb-1.5">Failed Transactions</p>
                <h2 class="text-3xl font-bold leading-none text-orange-500 stat-number">{{ number_format($failedStat) }}</h2>
                <div class="stat-badge flex items-center gap-1.5 px-2 py-1 rounded-full bg-red-100 mt-2 w-fit">
                  <i class="fi fi-sr-cross-circle text-[10px] text-red-600"></i>
                  <span class="text-[11px] font-semibold text-red-600">Marked invalid</span>
                </div>
              </div>
              <div class="stat-icon-wrap bg-red-100 p-2.5 rounded-lg"><i class="fi fi-sr-cross-circle text-lg text-red-600"></i></div>
            </div>
            <p class="text-[11px] text-gray-400">Rejected status</p>
          </div>

          <div class="stat-card bg-white rounded-xl shadow-sm p-4 hover:shadow-md hover:-translate-y-0.5 transition-all duration-200 cursor-pointer">
            <div class="flex justify-between items-start gap-3 mb-3">
              <div>
                <p class="text-[11px] font-semibold uppercase tracking-[0.14em] text-gray-500 mb-1.5">Completed Transactions</p>
                <h2 class="text-3xl font-bold leading-none text-orange-500 stat-number">{{ number_format($completedStat) }}</h2>
                <div class="stat-badge flex items-center gap-1.5 px-2 py-1 rounded-full bg-green-100 mt-2 w-fit">
                  <i class="fi fi-sr-check-circle text-[10px] text-green-600"></i>
                  <span class="text-[11px] font-semibold text-green-600">Approved payments</span>
                </div>
              </div>
              <div class="stat-icon-wrap bg-green-100 p-2.5 rounded-lg"><i class="fi fi-sr-check-circle text-lg text-green-600"></i></div>
            </div>
            <p class="text-[11px] text-gray-400">Approved status</p>
          </div>

        </div>{{-- /stats --}}

        <form id="paymentsFilterForm" method="GET" action="{{ route('admin.globalManagement.proofOfpayments') }}">
          <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-4 mb-4 flex flex-wrap items-center justify-between gap-3">
            <div class="flex flex-wrap items-center gap-2.5">
              <div class="flex items-center gap-2 px-3 py-2 border border-gray-300 rounded-lg text-sm text-gray-700">
                <i class="fi fi-rr-filter text-gray-500"></i>
                <span>Filter By</span>
              </div>

              <!-- Date From -->
              <div class="date-pill flex items-center gap-0 rounded-xl border border-indigo-200 bg-white shadow-sm overflow-hidden focus-within:ring-2 focus-within:ring-indigo-400 focus-within:border-indigo-400 transition">
                <div class="flex items-center gap-1.5 bg-gradient-to-br from-indigo-500 to-indigo-600 px-2.5 py-2 self-stretch">
                  <i class="fi fi-rr-calendar text-white text-[11px]"></i>
                </div>
                <input type="date" id="dateFrom" name="date_from" value="{{ request('date_from') }}"
                  class="px-2.5 py-1.5 text-xs border-none focus:outline-none focus:ring-0 bg-white">
              </div>

              <span class="text-gray-300 font-bold text-lg">→</span>

              <!-- Date To -->
              <div class="date-pill flex items-center gap-0 rounded-xl border border-indigo-200 bg-white shadow-sm overflow-hidden focus-within:ring-2 focus-within:ring-indigo-400 focus-within:border-indigo-400 transition">
                <div class="flex items-center gap-1.5 bg-gradient-to-br from-indigo-500 to-indigo-600 px-2.5 py-2 self-stretch">
                  <i class="fi fi-rr-calendar text-white text-[11px]"></i>
                </div>
                <input type="date" id="dateTo" name="date_to" value="{{ request('date_to') }}"
                  class="px-2.5 py-1.5 text-xs border-none focus:outline-none focus:ring-0 bg-white">
              </div>

              <!-- Status Filter -->
              <div class="relative">
                <select id="paymentsStatusFilter" name="status"
                  class="appearance-none bg-white border border-indigo-200 rounded-lg px-3 py-2 pr-8 text-xs font-medium text-gray-700 hover:border-indigo-300 focus:outline-none focus:ring-2 focus:ring-indigo-400 focus:border-indigo-400 transition cursor-pointer shadow-sm min-w-[150px]">
                  <option value="">All Status</option>
                  <option value="submitted" {{ request('status') === 'submitted' ? 'selected' : '' }}>Pending</option>
                  <option value="approved" {{ request('status') === 'approved' ? 'selected' : '' }}>Completed</option>
                  <option value="rejected" {{ request('status') === 'rejected' ? 'selected' : '' }}>Invalid</option>
                </select>
                <i class="fi fi-rr-angle-small-down absolute right-2.5 top-1/2 -translate-y-1/2 text-gray-500 pointer-events-none text-[11px]"></i>
              </div>
            </div>

            <div class="flex items-center gap-2">
              <button type="button" id="resetPaymentsFilter" class="flex items-center gap-2 text-red-600 hover:text-red-700 text-sm font-semibold px-3 py-2 rounded-lg hover:bg-red-50 transition">
                <i class="fi fi-rr-rotate-left"></i>
                <span>Reset Filter</span>
              </button>
            </div>
          </div>
        </form>

        {{-- ══ TABLE CARD ══ --}}
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
          <div id="paymentsTableWrap">
            @include('admin.globalManagement.partials.paymentsTable', ['payments' => $payments])
          </div>

        </div>{{-- /table card --}}
      </div>{{-- /p-8 --}}
    </main>

    {{-- ════════════════════════════════════════
         MODALS (unchanged from original)
    ════════════════════════════════════════ --}}

    {{-- 1. PENDING PAYMENT MODAL --}}
    <div id="pendingPaymentModal" class="fixed inset-0 z-[100] hidden items-center justify-center">
      <div class="absolute inset-0 bg-black/40 backdrop-blur-sm"></div>
      <div class="payment-view-modal relative bg-white w-full max-w-3xl mx-4 rounded-2xl shadow-2xl border border-gray-200 overflow-hidden max-h-[78vh] flex flex-col" data-status-theme="submitted">
        <div class="payment-view-modal-header flex items-center justify-between px-4 py-3.5 border-b text-white flex-shrink-0">
          <div class="flex items-center gap-2.5">
            <div class="payment-modal-icon-wrap w-9 h-9 rounded-xl border flex items-center justify-center shadow"><i class="fi fi-ss-bolt text-white text-base"></i></div>
            <div>
              <h3 class="text-[15px] font-bold text-white leading-tight">Proof of Payment (Pending)</h3>
              <p class="payment-modal-subtitle text-[10px]">Awaiting verification</p>
            </div>
          </div>
          <button data-close-modal class="p-1.5 rounded-xl hover:bg-white/20 text-white/80 hover:text-white transition"><i class="fi fi-rr-cross-small text-lg"></i></button>
        </div>
        <div id="pp-loading" class="py-8 text-center text-gray-400 flex-shrink-0">
          <i class="fi fi-rr-spinner text-2xl fi-spin block mb-2.5"></i><p class="text-sm">Loading payment details…</p>
        </div>
        <div id="pp-body" class="modal-scroll-hidden flex-1 overflow-y-auto p-4 space-y-3 hidden">
          <div class="rounded-xl border border-amber-200 bg-gradient-to-r from-amber-50 to-orange-50 p-3.5">
            <div class="flex flex-wrap items-center justify-between gap-2.5">
              <div class="flex items-center gap-2">
                <span class="inline-flex items-center px-2.5 py-1 rounded-full text-[10px] font-semibold bg-amber-100 text-amber-700 border border-amber-200">Pending</span>
                <span class="text-[10px] text-gray-500">Submitted and under review</span>
              </div>
              <span class="text-[9px] font-semibold uppercase tracking-[0.16em] text-amber-600">Action required</span>
            </div>
          </div>
          <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
            <div class="rounded-xl border border-gray-200 p-3.5">
              <div class="flex items-center gap-2 mb-2.5">
                <div class="w-7 h-7 rounded-lg bg-indigo-100 text-indigo-600 flex items-center justify-center"><i class="fi fi-rr-receipt text-[12px]"></i></div>
                <h4 class="text-[13px] font-semibold text-gray-800">Payment Details</h4>
              </div>
              <div class="space-y-2 text-[12px] leading-5">
                <div class="flex items-start justify-between gap-3"><span class="text-gray-500">Payment ID</span><span id="pp-payment-id" class="font-semibold text-gray-800 text-right">#—</span></div>
                <div class="flex items-start justify-between gap-3"><span class="text-gray-500">Contractor</span><span id="pp-contractor" class="font-semibold text-gray-800 text-right max-w-[180px] truncate">—</span></div>
                <div class="flex items-start justify-between gap-3"><span class="text-gray-500">Milestone Paid</span><span id="pp-milestone" class="text-gray-800 text-right max-w-[180px] truncate">—</span></div>
                <div class="flex items-start justify-between gap-3"><span class="text-gray-500">Reference No.</span><span id="pp-reference" class="text-gray-800 text-right">—</span></div>
                <div class="flex items-start justify-between gap-3"><span class="text-gray-500">Payment Date</span><span id="pp-date" class="text-gray-800 text-right">—</span></div>
                <div class="flex items-start justify-between gap-3"><span class="text-gray-500">Method</span><span id="pp-method" class="text-gray-800 text-right">—</span></div>
                <div class="flex items-start justify-between gap-3 pt-1 border-t border-gray-100"><span class="text-gray-500">Amount Paid</span><span id="pp-amount" class="font-semibold text-amber-700 text-right">—</span></div>
              </div>
            </div>
            <div class="space-y-3">
              <div class="rounded-xl border border-gray-200 p-3.5">
                <div class="flex items-center gap-2 mb-2.5">
                  <div class="w-7 h-7 rounded-lg bg-slate-100 text-slate-600 flex items-center justify-center"><i class="fi fi-rr-building text-[12px]"></i></div>
                  <h4 class="text-[13px] font-semibold text-gray-800">Project Details</h4>
                </div>
                <div class="space-y-2 text-[12px] leading-5">
                  <div class="flex items-start justify-between gap-3"><span class="text-gray-500">Property Owner</span><span id="pp-owner" class="text-gray-800 text-right max-w-[180px] truncate">—</span></div>
                  <div class="flex items-start justify-between gap-3"><span class="text-gray-500">Project Title</span><span id="pp-project" class="text-gray-800 text-right max-w-[180px] truncate">—</span></div>
                </div>
              </div>
              <div class="rounded-xl border border-gray-200 p-3.5">
                <div class="flex items-center gap-2 mb-2">
                  <div class="w-7 h-7 rounded-lg bg-cyan-100 text-cyan-600 flex items-center justify-center"><i class="fi fi-rr-document text-[12px]"></i></div>
                  <h4 class="text-[13px] font-semibold text-gray-800">Description</h4>
                </div>
                <p id="pp-description" class="text-[12px] text-gray-700 leading-5">—</p>
              </div>
            </div>
          </div>
          <div id="pp-files-section" class="hidden">
            <div class="rounded-xl border border-gray-200 overflow-hidden">
              <div class="flex items-center justify-between px-3.5 py-2.5 bg-gray-50 border-b border-gray-200">
                <div>
                  <h4 class="text-[13px] font-semibold text-gray-800">Uploaded Files</h4>
                  <p class="text-[10px] text-gray-500">Preview or download</p>
                </div>
                <span class="text-[10px] text-gray-400">Attachments</span>
              </div>
              <div class="p-3.5 space-y-2.5" id="pp-files"></div>
            </div>
          </div>
        </div>
        <div class="flex items-center justify-end gap-2 px-4 py-3 bg-gray-50 border-t flex-shrink-0">
          <button data-close-modal class="px-3.5 py-2 rounded-lg border border-gray-300 text-gray-700 hover:bg-gray-100 transition text-[12px] font-medium">Close</button>
          <button id="pp-reject" class="px-3.5 py-2 rounded-lg bg-red-50 text-red-600 border border-red-200 hover:bg-red-100 transition text-[12px] font-medium">Reject</button>
          <button id="pp-approve" class="px-3.5 py-2 rounded-lg bg-green-50 text-green-600 border border-green-200 hover:bg-green-100 transition text-[12px] font-medium">Approve</button>
        </div>
      </div>
    </div>

    {{-- 2. APPROVE CONFIRM --}}
    <div id="confirmApproveModal" class="fixed inset-0 z-[110] hidden items-center justify-center">
      <div class="absolute inset-0 bg-black/40 backdrop-blur-sm"></div>
      <div class="relative bg-white w-full max-w-md mx-4 rounded-2xl shadow-2xl border border-gray-200 overflow-hidden">
        <div class="px-6 py-4 bg-gradient-to-r from-green-500 to-green-600 text-white border-b border-green-600">
          <div class="flex items-center gap-3">
            <div class="w-10 h-10 rounded-xl bg-white/20 flex items-center justify-center border border-white/20"><i class="fi fi-ss-question text-white text-lg"></i></div>
            <div>
              <h4 class="text-base font-semibold leading-tight">Approve Payment?</h4>
              <p class="text-[11px] text-green-50">Confirm approval for this payment proof.</p>
            </div>
          </div>
        </div>
        <div class="px-8 pt-6 pb-6 text-center">
          <p class="text-gray-600">This will update the payment status to <strong>Approved</strong>.</p>
          <p class="mt-2 text-xs text-gray-500">Reference: <span id="approveSummary" class="font-medium text-gray-700">—</span></p>
        </div>
        <div class="px-8 pb-8 flex items-center justify-center gap-4">
          <button data-close-modal class="px-5 py-2.5 rounded-xl border-2 border-gray-300 text-gray-700 font-semibold text-sm hover:bg-gray-100">Cancel</button>
          <button id="confirmApproveBtn" class="px-5 py-2.5 rounded-xl bg-gradient-to-r from-green-500 to-green-600 text-white font-semibold text-sm hover:from-green-600 hover:to-green-700 shadow">Yes, Approve</button>
        </div>
      </div>
    </div>

    {{-- 3. REJECT CONFIRM --}}
    <div id="confirmRejectModal" class="fixed inset-0 z-[110] hidden items-center justify-center">
      <div class="absolute inset-0 bg-black/40 backdrop-blur-sm"></div>
      <div class="relative bg-white w-full max-w-md mx-4 rounded-2xl shadow-2xl border border-gray-200 overflow-hidden">
        <div class="px-6 py-4 bg-gradient-to-r from-red-500 to-red-600 text-white border-b border-red-600">
          <div class="flex items-center gap-3">
            <div class="w-10 h-10 rounded-xl bg-white/20 flex items-center justify-center border border-white/20"><i class="fi fi-ss-question text-white text-lg"></i></div>
            <div>
              <h4 class="text-base font-semibold leading-tight">Reject Payment?</h4>
              <p class="text-[11px] text-red-50">Confirm invalidation of this payment proof.</p>
            </div>
          </div>
        </div>
        <div class="px-8 pt-6 pb-6 text-center">
          <p class="text-gray-600">This will mark the proof as <strong>Invalid</strong>.</p>
          <p class="mt-2 text-xs text-gray-500">Reference: <span id="rejectSummary" class="font-medium text-gray-700">—</span></p>
          <div class="mt-4 text-left">
            <label class="block text-sm font-medium text-gray-600 mb-1">Reason <span class="text-gray-400 font-normal">(optional)</span></label>
            <textarea id="rejectReasonInput" rows="2" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:ring-2 focus:ring-rose-400 focus:outline-none resize-none" placeholder="Enter reason for rejection…"></textarea>
          </div>
        </div>
        <div class="px-8 pb-8 flex items-center justify-center gap-4">
          <button data-close-modal class="px-5 py-2.5 rounded-xl border-2 border-gray-300 text-gray-700 font-semibold text-sm hover:bg-gray-100">Cancel</button>
          <button id="confirmRejectBtn" class="px-5 py-2.5 rounded-xl bg-gradient-to-r from-red-500 to-red-600 text-white font-semibold text-sm hover:from-red-600 hover:to-red-700 shadow">Yes, Reject</button>
        </div>
      </div>
    </div>

    {{-- 4. COMPLETED MODAL --}}
    <div id="completedPaymentModal" class="fixed inset-0 z-[100] hidden items-center justify-center">
      <div class="absolute inset-0 bg-black/40 backdrop-blur-sm"></div>
      <div class="payment-view-modal relative bg-white w-full max-w-3xl mx-4 rounded-2xl shadow-2xl border border-gray-200 overflow-hidden max-h-[78vh] flex flex-col" data-status-theme="approved">
        <div class="payment-view-modal-header flex items-center justify-between px-4 py-3.5 border-b text-white flex-shrink-0">
          <div class="flex items-center gap-2.5">
            <div class="payment-modal-icon-wrap w-9 h-9 rounded-xl border flex items-center justify-center shadow"><i class="fi fi-sr-check-circle text-white text-base"></i></div>
            <div>
              <h3 class="text-[15px] font-bold leading-tight">Proof of Payment (Completed)</h3>
              <p class="payment-modal-subtitle text-[10px]">Verified transaction details</p>
            </div>
          </div>
          <button data-close-modal class="p-1.5 rounded-xl hover:bg-white/10 text-white/80 hover:text-white transition"><i class="fi fi-rr-cross-small text-lg"></i></button>
        </div>
        <div id="cp-loading" class="py-8 text-center text-gray-400 flex-shrink-0">
          <i class="fi fi-rr-spinner text-2xl fi-spin block mb-2.5"></i><p class="text-sm">Loading payment details…</p>
        </div>
        <div id="cp-body" class="modal-scroll-hidden flex-1 overflow-y-auto p-4 space-y-3 hidden">
          <div class="rounded-xl border border-emerald-200 bg-gradient-to-r from-emerald-50 to-teal-50 p-3.5">
            <div class="flex flex-wrap items-center justify-between gap-2.5">
              <div class="flex items-center gap-2">
                <span class="inline-flex items-center px-2.5 py-1 rounded-full text-[10px] font-semibold bg-green-100 text-green-700 border border-green-200">Approved</span>
                <span class="text-[10px] text-gray-500">Verified and recorded</span>
              </div>
              <div class="text-left sm:text-right">
                <p class="text-[9px] font-semibold uppercase tracking-[0.16em] text-emerald-600">Date Verified</p>
                <p id="cp-verified" class="text-[13px] font-semibold text-gray-800">—</p>
              </div>
            </div>
          </div>
          <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
            <div class="rounded-xl border border-gray-200 p-3.5">
              <div class="flex items-center gap-2 mb-2.5">
                <div class="w-7 h-7 rounded-lg bg-emerald-100 text-emerald-600 flex items-center justify-center"><i class="fi fi-rr-receipt text-[12px]"></i></div>
                <h4 class="text-[13px] font-semibold text-gray-800">Payment Details</h4>
              </div>
              <div class="space-y-2 text-[12px] leading-5">
                <div class="flex items-start justify-between gap-3"><span class="text-gray-500">Payment ID</span><span id="cp-payment-id" class="font-semibold text-gray-800 text-right">#—</span></div>
                <div class="flex items-start justify-between gap-3"><span class="text-gray-500">Contractor</span><span id="cp-contractor" class="font-semibold text-gray-800 text-right max-w-[180px] truncate">—</span></div>
                <div class="flex items-start justify-between gap-3"><span class="text-gray-500">Milestone Paid</span><span id="cp-milestone" class="text-gray-800 text-right max-w-[180px] truncate">—</span></div>
                <div class="flex items-start justify-between gap-3"><span class="text-gray-500">Reference No.</span><span id="cp-reference" class="text-gray-800 text-right">—</span></div>
                <div class="flex items-start justify-between gap-3"><span class="text-gray-500">Payment Date</span><span id="cp-date" class="text-gray-800 text-right">—</span></div>
                <div class="flex items-start justify-between gap-3"><span class="text-gray-500">Method</span><span id="cp-method" class="text-gray-800 text-right">—</span></div>
                <div class="flex items-start justify-between gap-3 pt-1 border-t border-gray-100"><span class="text-gray-500">Amount Paid</span><span id="cp-amount" class="font-semibold text-emerald-700 text-right">—</span></div>
              </div>
            </div>
            <div class="space-y-3">
              <div class="rounded-xl border border-gray-200 p-3.5">
                <div class="flex items-center gap-2 mb-2.5">
                  <div class="w-7 h-7 rounded-lg bg-slate-100 text-slate-600 flex items-center justify-center"><i class="fi fi-rr-building text-[12px]"></i></div>
                  <h4 class="text-[13px] font-semibold text-gray-800">Project Details</h4>
                </div>
                <div class="space-y-2 text-[12px] leading-5">
                  <div class="flex items-start justify-between gap-3"><span class="text-gray-500">Property Owner</span><span id="cp-owner" class="text-gray-800 text-right max-w-[180px] truncate">—</span></div>
                  <div class="flex items-start justify-between gap-3"><span class="text-gray-500">Project Title</span><span id="cp-project" class="text-gray-800 text-right max-w-[180px] truncate">—</span></div>
                </div>
              </div>
              <div class="rounded-xl border border-gray-200 p-3.5">
                <div class="flex items-center gap-2 mb-2">
                  <div class="w-7 h-7 rounded-lg bg-teal-100 text-teal-600 flex items-center justify-center"><i class="fi fi-rr-document text-[12px]"></i></div>
                  <h4 class="text-[13px] font-semibold text-gray-800">Description</h4>
                </div>
                <p id="cp-description" class="text-[12px] text-gray-700 leading-5">—</p>
              </div>
            </div>
          </div>
          <div id="cp-files-section" class="hidden">
            <div class="rounded-xl border border-gray-200 overflow-hidden">
              <div class="flex items-center justify-between px-3.5 py-2.5 bg-gray-50 border-b border-gray-200">
                <div>
                  <h4 class="text-[13px] font-semibold text-gray-800">Uploaded Files</h4>
                  <p class="text-[10px] text-gray-500">Receipt attachments</p>
                </div>
                <span class="text-[10px] text-gray-400">Download only</span>
              </div>
              <div class="grid grid-cols-12 bg-gray-50/80 text-[10px] font-semibold uppercase tracking-[0.12em] text-gray-500 px-3.5 py-2 border-b border-gray-200"><div class="col-span-7">File</div><div class="col-span-4">Uploaded</div><div class="col-span-1"></div></div>
              <div class="divide-y" id="cp-files"></div>
            </div>
          </div>
        </div>
      </div>
    </div>

    {{-- 5. EDIT MODAL --}}
    <div id="editPaymentModal" class="fixed inset-0 z-[100] hidden items-center justify-center">
      <div class="absolute inset-0 bg-black/40 backdrop-blur-sm"></div>
      <div class="relative bg-white w-full max-w-lg mx-4 rounded-2xl shadow-2xl border border-gray-200 overflow-hidden">
        <div class="flex items-center justify-between px-4 py-3.5 bg-gradient-to-r from-orange-500 to-orange-600 border-b border-orange-600 text-white">
          <div class="flex items-center gap-2">
            <div class="w-8 h-8 rounded-lg bg-white/20 border border-white/20 flex items-center justify-center"><i class="fi fi-rr-edit text-white text-sm"></i></div>
            <h3 class="text-[15px] font-semibold leading-tight">Edit Payment Details</h3>
          </div>
          <button data-close-modal class="p-1.5 rounded-xl hover:bg-white/10 text-white/80 hover:text-white transition"><i class="fi fi-rr-cross-small text-lg"></i></button>
        </div>
        <div class="modal-scroll-hidden p-4 space-y-3.5 max-h-[66vh] overflow-y-auto">
          <!-- Validation Error Section -->
          <div id="editPaymentErrorAlert" class="hidden bg-red-50 border-l-4 border-red-500 p-3 rounded-r-lg text-left">
            <div class="flex items-start gap-2">
              <i class="fi fi-rr-exclamation text-red-600 text-sm flex-shrink-0 mt-0.5"></i>
              <div class="flex-1">
                <p class="text-xs font-semibold text-red-800 mb-1">Validation Error</p>
                <ul id="editPaymentErrorList" class="text-xs text-red-700 space-y-0.5 list-disc list-inside">
                  <!-- Error messages populated by JS -->
                </ul>
              </div>
            </div>
          </div>
          <div class="form-group"><label class="form-label">Payment Reference No.</label><input type="text" id="edit-reference" class="form-input" placeholder="Enter payment reference number"></div>
          <div class="form-group"><label class="form-label">Project Title</label><input type="text" id="edit-project" class="form-input bg-gray-50 cursor-not-allowed" readonly></div>
          <div class="form-group">
            <label class="form-label">Payment Method</label>
            <select id="edit-method" class="form-input">
              <option value="">Select payment method</option>
              <option value="cash">Cash</option>
              <option value="check">Check</option>
              <option value="bank_transfer">Bank Transfer</option>
              <option value="online_payment">Online Payment</option>
            </select>
          </div>
          <div class="form-group">
            <label class="form-label">Amount Paid</label>
            <div class="relative"><span class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-500 font-semibold">₱</span><input type="number" step="0.01" min="0" id="edit-amount" class="form-input pl-8" placeholder="0.00"></div>
          </div>
          <div class="form-group">
            <label class="form-label">Status</label>
            <select id="edit-status" class="form-input">
              <option value="submitted">Pending</option>
              <option value="approved">Completed</option>
              <option value="rejected">Invalid</option>
            </select>
          </div>
          <div class="form-group"><label class="form-label">Remarks / Reason</label><textarea id="edit-remarks" rows="2" class="form-input" placeholder="Add any additional notes…"></textarea></div>
        </div>
        <div class="flex items-center justify-end gap-2 px-4 py-3 bg-gray-50 border-t">
          <button data-close-modal class="px-3.5 py-2 rounded-lg border border-gray-300 text-gray-700 text-[12px] font-medium hover:bg-gray-100 transition">Cancel</button>
          <button id="saveEditBtn" class="px-3.5 py-2 rounded-lg bg-gradient-to-r from-orange-500 to-amber-600 text-white text-[12px] font-semibold hover:from-orange-600 hover:to-amber-700 transition shadow">Save Changes</button>
        </div>
      </div>
    </div>

    {{-- 6. INVALID MODAL --}}
    <div id="invalidPaymentModal" class="fixed inset-0 z-[100] hidden items-center justify-center">
      <div class="absolute inset-0 bg-black/40 backdrop-blur-sm"></div>
      <div class="payment-view-modal relative bg-white w-full max-w-3xl mx-4 rounded-2xl shadow-2xl border border-gray-200 overflow-hidden max-h-[78vh] flex flex-col" data-status-theme="rejected">
        <div class="payment-view-modal-header flex items-center justify-between px-4 py-3.5 border-b text-white flex-shrink-0">
          <div class="flex items-center gap-2.5">
            <div class="payment-modal-icon-wrap w-9 h-9 rounded-xl border flex items-center justify-center shadow"><i class="fi fi-sr-triangle-warning text-white text-base"></i></div>
            <div>
              <h3 class="text-[15px] font-bold text-white leading-tight">Proof of Payment (Invalid)</h3>
              <p class="payment-modal-subtitle text-[10px]">Receipt flagged as invalid</p>
            </div>
          </div>
          <button data-close-modal class="p-1.5 rounded-xl transition"><i class="fi fi-rr-cross-small text-lg"></i></button>
        </div>
        <div id="ip-loading" class="py-8 text-center text-gray-400 flex-shrink-0">
          <i class="fi fi-rr-spinner text-2xl fi-spin block mb-2.5"></i><p class="text-sm">Loading payment details…</p>
        </div>
        <div id="ip-body" class="modal-scroll-hidden flex-1 overflow-y-auto p-4 space-y-3 hidden">
          <div class="rounded-xl border border-rose-200 bg-gradient-to-r from-rose-50 to-orange-50 p-3.5">
            <div class="flex flex-wrap items-center justify-between gap-2.5">
              <div class="flex items-center gap-2">
                <span class="inline-flex items-center px-2.5 py-1 rounded-full text-[10px] font-semibold bg-rose-100 text-rose-700 border border-rose-200">Invalid Receipt</span>
                <span class="text-[10px] text-gray-500">Flagged during verification</span>
              </div>
              <div class="text-left sm:text-right">
                <p class="text-[9px] font-semibold uppercase tracking-[0.16em] text-rose-600">Date Verified</p>
                <p id="ip-verified" class="text-[13px] font-semibold text-gray-800">—</p>
              </div>
            </div>
          </div>
          <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
            <div class="rounded-xl border border-gray-200 p-3.5">
              <div class="flex items-center gap-2 mb-2.5">
                <div class="w-7 h-7 rounded-lg bg-rose-100 text-rose-600 flex items-center justify-center"><i class="fi fi-rr-receipt text-[12px]"></i></div>
                <h4 class="text-[13px] font-semibold text-gray-800">Payment Details</h4>
              </div>
              <div class="space-y-2 text-[12px] leading-5">
                <div class="flex items-start justify-between gap-3"><span class="text-gray-500">Payment ID</span><span id="ip-payment-id" class="font-semibold text-gray-800 text-right">#—</span></div>
                <div class="flex items-start justify-between gap-3"><span class="text-gray-500">Contractor</span><span id="ip-contractor" class="font-semibold text-gray-800 text-right max-w-[180px] truncate">—</span></div>
                <div class="flex items-start justify-between gap-3"><span class="text-gray-500">Milestone Paid</span><span id="ip-milestone" class="text-gray-800 text-right max-w-[180px] truncate">—</span></div>
                <div class="flex items-start justify-between gap-3"><span class="text-gray-500">Reference No.</span><span id="ip-reference" class="text-gray-800 text-right">—</span></div>
                <div class="flex items-start justify-between gap-3"><span class="text-gray-500">Payment Date</span><span id="ip-date" class="text-gray-800 text-right">—</span></div>
                <div class="flex items-start justify-between gap-3"><span class="text-gray-500">Method</span><span id="ip-method" class="text-gray-800 text-right">—</span></div>
                <div class="flex items-start justify-between gap-3 pt-1 border-t border-gray-100"><span class="text-gray-500">Amount Paid</span><span id="ip-amount" class="font-semibold text-rose-700 text-right">—</span></div>
              </div>
            </div>
            <div class="space-y-3">
              <div class="rounded-xl border border-gray-200 p-3.5">
                <div class="flex items-center gap-2 mb-2.5">
                  <div class="w-7 h-7 rounded-lg bg-slate-100 text-slate-600 flex items-center justify-center"><i class="fi fi-rr-building text-[12px]"></i></div>
                  <h4 class="text-[13px] font-semibold text-gray-800">Project Details</h4>
                </div>
                <div class="space-y-2 text-[12px] leading-5">
                  <div class="flex items-start justify-between gap-3"><span class="text-gray-500">Property Owner</span><span id="ip-owner" class="text-gray-800 text-right max-w-[180px] truncate">—</span></div>
                  <div class="flex items-start justify-between gap-3"><span class="text-gray-500">Project Title</span><span id="ip-project" class="text-gray-800 text-right max-w-[180px] truncate">—</span></div>
                </div>
              </div>
              <div class="rounded-xl border border-gray-200 p-3.5">
                <div class="flex items-center gap-2 mb-2">
                  <div class="w-7 h-7 rounded-lg bg-rose-100 text-rose-600 flex items-center justify-center"><i class="fi fi-rr-comment-alt text-[12px]"></i></div>
                  <h4 class="text-[13px] font-semibold text-gray-800">Remarks</h4>
                </div>
                <textarea id="ip-remarks" rows="2" class="w-full rounded-lg border border-rose-200 bg-rose-50 px-3 py-2 text-[12px] text-rose-700 resize-none focus:outline-none" readonly placeholder="No remarks provided."></textarea>
              </div>
              <div class="rounded-xl border border-gray-200 p-3.5">
                <div class="flex items-center gap-2 mb-2">
                  <div class="w-7 h-7 rounded-lg bg-orange-100 text-orange-600 flex items-center justify-center"><i class="fi fi-rr-document text-[12px]"></i></div>
                  <h4 class="text-[13px] font-semibold text-gray-800">Description</h4>
                </div>
                <p id="ip-description" class="text-[12px] text-gray-700 leading-5">—</p>
              </div>
            </div>
          </div>
          <div id="ip-files-section" class="hidden">
            <div class="rounded-xl border border-gray-200 overflow-hidden">
              <div class="flex items-center justify-between px-3.5 py-2.5 bg-gray-50 border-b border-gray-200">
                <div>
                  <h4 class="text-[13px] font-semibold text-gray-800">Uploaded Files</h4>
                  <p class="text-[10px] text-gray-500">Receipt attachments</p>
                </div>
                <span class="text-[10px] text-gray-400">Download only</span>
              </div>
              <div class="grid grid-cols-12 bg-gray-50/80 text-[10px] font-semibold uppercase tracking-[0.12em] text-gray-500 px-3.5 py-2 border-b border-gray-200"><div class="col-span-7">File</div><div class="col-span-4">Uploaded</div><div class="col-span-1"></div></div>
              <div class="divide-y" id="ip-files"></div>
            </div>
          </div>
        </div>
      </div>
    </div>

    {{-- 7. DELETE CONFIRM --}}
    <div id="deletePaymentModal" class="fixed inset-0 bg-black bg-opacity-50 backdrop-blur-sm hidden items-center justify-center z-[110] p-2">
      <div class="bg-white rounded-2xl shadow-lg max-w-xs w-full relative">
        <button data-close-modal type="button" class="absolute top-2 right-2 w-6 h-6 rounded-md border border-gray-200 text-gray-500 hover:text-gray-700 hover:bg-gray-100 transition flex items-center justify-center">
          <i class="fi fi-rr-cross text-[10px]"></i>
        </button>

        <div class="flex justify-center pt-3 pb-2">
          <div class="w-12 h-12 bg-red-100 rounded-full flex items-center justify-center relative">
            <div class="absolute inset-0 bg-red-200 rounded-full animate-ping opacity-60"></div>
            <div class="relative w-8 h-8 bg-red-500 rounded-full flex items-center justify-center">
              <i class="fi fi-rr-trash text-white text-sm"></i>
            </div>
          </div>
        </div>

        <div class="px-3 pb-2.5 text-center">
          <h3 class="text-sm font-bold text-gray-800 mb-1.5">Delete Payment</h3>
          <p class="text-[11px] text-gray-600 leading-relaxed mb-2.5">Permanently delete this payment record? This action cannot be undone.</p>

          <div class="text-left bg-red-50 border border-red-200 rounded-md p-2 space-y-1.5 mb-2.5">
            <div class="flex items-center justify-between text-[11px]"><span class="text-gray-600">Payment ID</span><span id="delete-payment-id" class="font-semibold text-gray-800">#—</span></div>
            <div class="flex items-center justify-between text-[11px]"><span class="text-gray-600">Project</span><span id="delete-project" class="font-semibold text-gray-800 max-w-[120px] truncate text-right">—</span></div>
            <div class="flex items-center justify-between text-[11px]"><span class="text-gray-600">Contractor</span><span id="delete-contractor" class="font-semibold text-gray-800 max-w-[120px] truncate text-right">—</span></div>
            <div class="flex items-center justify-between text-[11px]"><span class="text-gray-600">Amount</span><span id="delete-amount" class="font-semibold text-red-600">—</span></div>
          </div>

          <div class="text-left">
            <label class="block text-[11px] font-semibold text-gray-700 mb-1">Reason for deletion <span class="text-red-500">*</span></label>
            <textarea id="deletePaymentReason" rows="3"
              class="w-full rounded-lg border border-gray-300 px-2.5 py-2 text-[11px] text-gray-700 focus:ring-2 focus:ring-red-300 focus:border-red-400 focus:outline-none resize-none transition"
              placeholder="Enter reason for deleting this payment..."></textarea>
            <p id="deletePaymentReasonError" class="text-[10px] text-red-500 mt-0.5 hidden">Reason is required.</p>
          </div>
        </div>

        <div class="px-3 pb-3 space-y-1.5">
          <button id="confirmDeletePaymentBtn" class="w-full px-3 py-1.5 bg-gradient-to-r from-red-500 to-red-600 hover:from-red-600 hover:to-red-700 text-white rounded-md transition-all text-[11px] font-semibold shadow-sm active:scale-95 flex items-center justify-center gap-1">
            <i class="fi fi-rr-trash"></i>
            <span>Delete</span>
          </button>
          <button id="cancelDeletePaymentBtn" class="w-full px-3 py-1.5 border-2 border-gray-300 text-gray-700 rounded-md hover:bg-gray-50 transition-all text-[11px] font-semibold active:scale-95">
            Cancel
          </button>
        </div>
      </div>
    </div>

  </div>{{-- /flex min-h-screen --}}

  <!-- Universal File Viewer (UFV) -->
  <div id="documentViewerModal" class="fixed inset-0 bg-black/50 backdrop-blur-sm z-[130] hidden items-center justify-center p-4">
    <div class="bg-[#1e1e2e] rounded-[1.25rem] shadow-[0_30px_90px_rgba(0,0,0,0.75)] max-w-5xl w-full h-[90vh] overflow-hidden transform transition-all duration-300 scale-95 opacity-0 flex flex-col modal-shell">
      <!-- Header -->
      <div class="flex items-center justify-between px-5 py-3 bg-[#16162a] border-b border-white/5 gap-4">
        <div class="flex items-center gap-3 min-w-0">
          <i class="fi fi-rr-file-document text-orange-500 text-lg"></i>
          <h3 id="documentViewerTitle" class="text-sm font-semibold text-gray-200 truncate">Document Viewer</h3>
        </div>
        <div class="flex items-center gap-2 flex-shrink-0">
          <a id="documentViewerDownload" href="#" download class="w-9 h-9 flex items-center justify-center rounded-lg bg-white/5 text-gray-400 hover:bg-orange-500/40 hover:text-white transition-all" title="Download">
            <i class="fi fi-rr-download"></i>
          </a>
          <button id="closeDocumentViewerBtn" class="w-9 h-9 flex items-center justify-center rounded-lg bg-white/5 text-gray-400 hover:bg-red-500/40 hover:text-white transition-all" title="Close">
            <i class="fi fi-rr-cross text-sm"></i>
          </button>
        </div>
      </div>
      <!-- Viewport -->
      <div class="flex-1 bg-[#0d0d18] relative flex items-center justify-center overflow-hidden p-4">
        <img id="documentViewerImg" src="" alt="Document" class="max-w-full max-h-full object-contain hidden" />
        <iframe id="documentViewerFrame" src="" class="w-full h-full hidden border-0 bg-white rounded-lg"></iframe>
      </div>
    </div>
  </div>

  <script>
  (function () {
    'use strict';
    var CSRF = document.querySelector('meta[name="csrf-token"]').content;
    var METHOD_LABELS = { cash:'Cash', check:'Check', bank_transfer:'Bank Transfer', online_payment:'Online Payment' };
    var filterForm = document.getElementById('paymentsFilterForm');
    var paymentsTableWrap = document.getElementById('paymentsTableWrap');
    var searchInput = document.getElementById('topNavSearch');
    var statusFilter = document.getElementById('paymentsStatusFilter');
    var searchTimer = null;

    function animatePaymentsRows() {
      if (!paymentsTableWrap) return;
      var rows = document.querySelectorAll('#paymentsTable tr');
      rows.forEach(function(row, index) {
        row.style.opacity = '0';
        row.style.transform = 'translateY(20px)';
        setTimeout(function() {
          row.style.transition = 'all 0.4s ease';
          row.style.opacity = '1';
          row.style.transform = 'translateY(0)';
        }, index * 50);
      });
    }

    function esc(v) { if (v==null) return '—'; return String(v).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;'); }
    function peso(v) { return '₱'+Number(v).toLocaleString('en-PH',{minimumFractionDigits:2,maximumFractionDigits:2}); }
    function fmtDate(s) { if(!s)return'—'; try{return new Date(s).toLocaleDateString('en-PH',{month:'short',day:'numeric',year:'numeric'});}catch(e){return s;} }
    function setText(id,val){var el=document.getElementById(id);if(el)el.textContent=val==null?'—':val;}
    function setVal(id,val){var el=document.getElementById(id);if(el)el.value=val==null?'':val;}

    function openModal(id){var el=document.getElementById(id);if(!el)return;el.classList.remove('hidden');el.classList.add('flex');}
    function closeModal(id){var el=document.getElementById(id);if(!el)return;el.classList.add('hidden');el.classList.remove('flex');}

    function normalizePaymentStatus(status){
      var normalized=String(status||'').toLowerCase();
      if(normalized==='pending')return'submitted';
      if(normalized==='completed'||normalized==='verified')return'approved';
      if(normalized==='invalid'||normalized==='failed')return'rejected';
      return normalized;
    }

    function applyViewModalTheme(modalId,status){
      var modal=document.getElementById(modalId);
      if(!modal)return;
      var modalCard=modal.querySelector('.payment-view-modal');
      if(!modalCard)return;
      var normalized=normalizePaymentStatus(status);
      if(['submitted','approved','rejected','deleted'].indexOf(normalized)===-1){
        normalized='submitted';
      }
      modalCard.setAttribute('data-status-theme',normalized);
    }

    document.addEventListener('click',function(e){
      var btn=e.target.closest('[data-close-modal]');
      if(!btn)return;
      var modal=btn.closest('.fixed');
      if(modal){modal.classList.add('hidden');modal.classList.remove('flex');}
    });

    var _toastTimer=null;
    var _toastEl=null;
    var TOAST_HIDE_DELAY=4500;
    var TOAST_EXIT_MS=500;
    var TOAST_MODAL_CLOSE_DELAY=2400;
    var SUCCESS_RELOAD_DELAY=3000;

    function getVisibleModal(){
      var modals=document.querySelectorAll('div.fixed.inset-0');
      for(var i=modals.length-1;i>=0;i--){
        var m=modals[i];
        if(!m.classList.contains('hidden') && m.classList.contains('flex')) return m;
      }
      return null;
    }

    function clearToast(){
      clearTimeout(_toastTimer);
      if(_toastEl && _toastEl.parentNode) _toastEl.parentNode.removeChild(_toastEl);
      _toastEl=null;
    }

    function showToast(msg,type,modalId){
      clearToast();
      type=type||'success';
      var ok=type==='success';

      var host=null;
      if(modalId){
        var modalHost=document.getElementById(modalId);
        if(modalHost && !modalHost.classList.contains('hidden') && modalHost.classList.contains('flex')) host=modalHost;
      }
      if(!host) host=getVisibleModal()||document.body;

      var toast=document.createElement('div');
      var inModal=host!==document.body;
      var posClass=inModal?'absolute top-3 right-3 z-[130]':'fixed top-20 right-4 z-[120]';
      toast.className=posClass+' max-w-[280px] px-3 py-2 rounded-md shadow-lg transform transition-all duration-500 translate-x-full '+(ok?'bg-green-500':'bg-red-500')+' text-white text-xs font-semibold leading-tight flex items-center gap-1.5 pointer-events-none';
      toast.innerHTML='<i class="fi fi-rr-'+(ok?'check-circle':'cross-circle')+' text-base"></i><span>'+esc(msg)+'</span>';

      host.appendChild(toast);
      _toastEl=toast;

      setTimeout(function(){ if(_toastEl===toast) toast.style.transform='translateX(0)'; },10);

      _toastTimer=setTimeout(function(){
        toast.style.transform='translateX(150%)';
        setTimeout(function(){
          if(toast.parentNode) toast.parentNode.removeChild(toast);
          if(_toastEl===toast) _toastEl=null;
        },TOAST_EXIT_MS);
      },TOAST_HIDE_DELAY);
    }

    function showToastAndClose(msg,type,modalId){
      showToast(msg,type,modalId);
      setTimeout(function(){closeModal(modalId);},TOAST_MODAL_CLOSE_DELAY);
    }

    function syncFilterInputs(url){
      var parsed = new URL(url, window.location.origin);
      if(searchInput)searchInput.value=parsed.searchParams.get('search')||'';
      if(statusFilter)statusFilter.value=parsed.searchParams.get('status')||'';
      var dateFrom=document.getElementById('dateFrom');
      var dateTo=document.getElementById('dateTo');
      if(dateFrom)dateFrom.value=parsed.searchParams.get('date_from')||'';
      if(dateTo)dateTo.value=parsed.searchParams.get('date_to')||'';
    }

    function buildFilterUrl(){
      var url = new URL(filterForm ? filterForm.action : window.location.href, window.location.origin);
      var params = new URLSearchParams(window.location.search);
      var searchValue = searchInput ? searchInput.value.trim() : '';
      var statusValue = statusFilter ? statusFilter.value : '';
      var dateFrom = document.getElementById('dateFrom');
      var dateTo = document.getElementById('dateTo');

      if(searchValue)params.set('search',searchValue);else params.delete('search');
      if(statusValue)params.set('status',statusValue);else params.delete('status');
      if(dateFrom && dateFrom.value)params.set('date_from',dateFrom.value);else params.delete('date_from');
      if(dateTo && dateTo.value)params.set('date_to',dateTo.value);else params.delete('date_to');
      params.delete('page');

      url.search = params.toString();
      return url.toString();
    }

    function fetchPaymentsTable(url,shouldPushState){
      if(!paymentsTableWrap)return Promise.resolve();

      return fetch(url,{
        headers:{
          'X-Requested-With':'XMLHttpRequest',
          'Accept':'application/json'
        }
      })
      .then(function(response){
        if(!response.ok)throw new Error('Failed to load payment proofs.');
        return response.json();
      })
      .then(function(data){
        if(!data.payments_html)throw new Error('Missing payment table payload.');
        paymentsTableWrap.innerHTML=data.payments_html;
        animatePaymentsRows();
        if(shouldPushState!==false)window.history.pushState({},'',url);
        syncFilterInputs(url);
      })
      .catch(function(){
        showToast('Failed to update payment proofs.','error');
      });
    }

    function fetchDetail(id,callback){
      fetch('/admin/global-management/proof-of-payments/'+id,{headers:{'Accept':'application/json'}})
        .then(function(r){return r.json();})
        .then(function(res){if(!res.success){showToast(res.message||'Failed to load.','error');return;}callback(res.data);})
        .catch(function(){showToast('Network error.','error');});
    }

    function pendingFileRow(path){var name=esc(path.split('/').pop()),ext=name.split('.').pop().toUpperCase().slice(0,4),url='/storage/'+path;return'<div class="file-row"><div class="flex items-center gap-3"><span class="file-type">'+ext+'</span><span class="text-gray-800">'+name+'</span></div><div class="flex items-center gap-2"><a href="#" class="icon-btn open-doc-btn" data-doc-src="'+esc(url)+'" data-doc-title="'+esc(name)+'"><i class="fi fi-rr-eye"></i></a><a href="'+esc(url)+'" download class="icon-btn"><i class="fi fi-rr-download"></i></a></div></div>';}
    function completedFileRow(path){var name=esc(path.split('/').pop()),ext=name.split('.').pop().toUpperCase().slice(0,4),url='/storage/'+path;return'<div class="grid grid-cols-12 items-center px-4 py-3 hover:bg-gray-50"><div class="col-span-7 flex items-center gap-3"><span class="file-type">'+ext+'</span><span class="text-gray-800">'+name+'</span></div><div class="col-span-4 text-sm text-gray-600">Receipt file</div><div class="col-span-1 flex justify-end gap-1.5"><a href="#" class="icon-btn open-doc-btn" data-doc-src="'+esc(url)+'" data-doc-title="'+esc(name)+'"><i class="fi fi-rr-eye"></i></a><a href="'+esc(url)+'" download class="icon-btn"><i class="fi fi-rr-download"></i></a></div></div>';}
    function invalidFileRow(path){return completedFileRow(path);}

    function showLoading(lid,bid){var l=document.getElementById(lid),b=document.getElementById(bid);if(l)l.classList.remove('hidden');if(b)b.classList.add('hidden');}
    function showBody(lid,bid){var l=document.getElementById(lid),b=document.getElementById(bid);if(l)l.classList.add('hidden');if(b)b.classList.remove('hidden');}

    function populatePending(d){
      setText('pp-payment-id','#'+d.payment_id);setText('pp-contractor',d.company_name);setText('pp-milestone',d.milestone_item_title);setText('pp-reference',d.transaction_number);setText('pp-date',fmtDate(d.transaction_date));setText('pp-method',METHOD_LABELS[d.payment_type]||d.payment_type);setText('pp-amount',peso(d.amount));setText('pp-owner',d.owner_name);setText('pp-project',d.project_title);setText('pp-description',d.project_description);
      var fs=document.getElementById('pp-files-section'),fc=document.getElementById('pp-files');
      if(d.receipt_photo){fc.innerHTML=pendingFileRow(d.receipt_photo);fs.classList.remove('hidden');}else{fs.classList.add('hidden');}
      document.getElementById('pp-approve').dataset.id=d.payment_id;document.getElementById('pp-approve').dataset.project=d.project_title||'';
      document.getElementById('pp-reject').dataset.id=d.payment_id;document.getElementById('pp-reject').dataset.project=d.project_title||'';
    }
    function populateCompleted(d){
      setText('cp-payment-id','#'+d.payment_id);setText('cp-contractor',d.company_name);setText('cp-milestone',d.milestone_item_title);setText('cp-reference',d.transaction_number);setText('cp-date',fmtDate(d.transaction_date));setText('cp-method',METHOD_LABELS[d.payment_type]||d.payment_type);setText('cp-amount',peso(d.amount));setText('cp-verified',fmtDate(d.updated_at));setText('cp-owner',d.owner_name);setText('cp-project',d.project_title);setText('cp-description',d.project_description);
      var fs=document.getElementById('cp-files-section'),fc=document.getElementById('cp-files');
      if(d.receipt_photo){fc.innerHTML=completedFileRow(d.receipt_photo);fs.classList.remove('hidden');}else{fs.classList.add('hidden');}
    }
    function populateInvalid(d){
      setText('ip-payment-id','#'+d.payment_id);setText('ip-contractor',d.company_name);setText('ip-milestone',d.milestone_item_title);setText('ip-reference',d.transaction_number);setText('ip-date',fmtDate(d.transaction_date));setText('ip-method',METHOD_LABELS[d.payment_type]||d.payment_type);setText('ip-amount',peso(d.amount));setText('ip-verified',fmtDate(d.updated_at));setText('ip-owner',d.owner_name);setText('ip-project',d.project_title);setText('ip-description',d.project_description);setVal('ip-remarks',d.reason);
      var fs=document.getElementById('ip-files-section'),fc=document.getElementById('ip-files');
      if(d.receipt_photo){fc.innerHTML=invalidFileRow(d.receipt_photo);fs.classList.remove('hidden');}else{fs.classList.add('hidden');}
    }

    if(filterForm){
      filterForm.addEventListener('submit',function(e){
        e.preventDefault();
        fetchPaymentsTable(buildFilterUrl());
      });
    }

    if(searchInput){
      searchInput.addEventListener('input',function(){
        clearTimeout(searchTimer);
        searchTimer=setTimeout(function(){
          fetchPaymentsTable(buildFilterUrl());
        },300);
      });
    }

    if(statusFilter){
      statusFilter.addEventListener('change',function(){
        fetchPaymentsTable(buildFilterUrl());
      });
    }

    var resetPaymentsBtn = document.getElementById('resetPaymentsFilter');
    if(resetPaymentsBtn){
      resetPaymentsBtn.addEventListener('click',function(){
        if(searchInput) searchInput.value='';
        if(statusFilter) statusFilter.value='';
        var dateFrom = document.getElementById('dateFrom');
        var dateTo = document.getElementById('dateTo');
        if(dateFrom) dateFrom.value='';
        if(dateTo) dateTo.value='';
        fetchPaymentsTable(filterForm ? filterForm.action : window.location.pathname);
      });
    }

    var dateFromInput = document.getElementById('dateFrom');
    var dateToInput = document.getElementById('dateTo');
    if(dateFromInput) dateFromInput.addEventListener('change', function(){ fetchPaymentsTable(buildFilterUrl()); });
    if(dateToInput) dateToInput.addEventListener('change', function(){ fetchPaymentsTable(buildFilterUrl()); });

    window.addEventListener('popstate',function(){
      fetchPaymentsTable(window.location.href,false);
    });

    document.addEventListener('click',function(e){
      var paginationLink=e.target.closest('.payment-page-link');
      if(paginationLink){
        e.preventDefault();
        fetchPaymentsTable(paginationLink.href);
        return;
      }

      var clearFiltersLink=e.target.closest('.clear-payments-filters');
      if(clearFiltersLink){
        e.preventDefault();
        fetchPaymentsTable(clearFiltersLink.href);
        return;
      }

      var viewBtn=e.target.closest('.btn-view');
      if(viewBtn){
        var viewId=viewBtn.dataset.id;
        var viewStatus=normalizePaymentStatus(viewBtn.dataset.status);
        if(viewStatus==='submitted'){
          applyViewModalTheme('pendingPaymentModal',viewStatus);
          showLoading('pp-loading','pp-body');
          openModal('pendingPaymentModal');
          fetchDetail(viewId,function(d){
            applyViewModalTheme('pendingPaymentModal',d.payment_status||viewStatus);
            populatePending(d);
            showBody('pp-loading','pp-body');
          });
        }
        else if(viewStatus==='approved'){
          applyViewModalTheme('completedPaymentModal',viewStatus);
          showLoading('cp-loading','cp-body');
          openModal('completedPaymentModal');
          fetchDetail(viewId,function(d){
            applyViewModalTheme('completedPaymentModal',d.payment_status||viewStatus);
            populateCompleted(d);
            showBody('cp-loading','cp-body');
          });
        }
        else{
          var invalidTheme=viewStatus==='deleted'?'deleted':'rejected';
          applyViewModalTheme('invalidPaymentModal',invalidTheme);
          showLoading('ip-loading','ip-body');
          openModal('invalidPaymentModal');
          fetchDetail(viewId,function(d){
            applyViewModalTheme('invalidPaymentModal',d.payment_status||invalidTheme);
            populateInvalid(d);
            showBody('ip-loading','ip-body');
          });
        }
        return;
      }

      var editBtn=e.target.closest('.btn-edit');
      if(editBtn){
        document.getElementById('edit-project').value=editBtn.dataset.project||'';
        document.getElementById('edit-method').value=editBtn.dataset.method||'';
        document.getElementById('edit-amount').value=editBtn.dataset.amount||'';
        document.getElementById('edit-status').value=editBtn.dataset.status||'submitted';
        document.getElementById('edit-reference').value=editBtn.dataset.txn||'';
        document.getElementById('edit-remarks').value=editBtn.dataset.reason||'';
        document.getElementById('saveEditBtn').dataset.id=editBtn.dataset.id;
        openModal('editPaymentModal');
        return;
      }

      var deleteBtn=e.target.closest('.btn-delete');
      if(deleteBtn){
        _deleteId=deleteBtn.dataset.id;
        document.getElementById('delete-payment-id').textContent='#'+_deleteId;
        document.getElementById('delete-project').textContent=deleteBtn.dataset.project||'—';
        document.getElementById('delete-contractor').textContent=deleteBtn.dataset.contractor||'—';
        document.getElementById('delete-amount').textContent=deleteBtn.dataset.amount||'—';
        var reasonEl=document.getElementById('deletePaymentReason');
        var errorEl=document.getElementById('deletePaymentReasonError');
        if(reasonEl)reasonEl.value='';
        if(errorEl)errorEl.classList.add('hidden');
        openModal('deletePaymentModal');
      }
    });

    var initialEditValues = {};

    function showEditPaymentErrors(errors){
      var errorAlert = document.getElementById('editPaymentErrorAlert');
      var errorList = document.getElementById('editPaymentErrorList');
      errorList.innerHTML = '';
      errors.forEach(function(error){
        var li = document.createElement('li');
        li.textContent = error;
        errorList.appendChild(li);
      });
      errorAlert.classList.remove('hidden');
    }

    function clearEditPaymentErrors(){
      var errorAlert = document.getElementById('editPaymentErrorAlert');
      errorAlert.classList.add('hidden');
    }

    function hasEditPaymentChanges(){
      var currentRef = document.getElementById('edit-reference').value;
      var currentMethod = document.getElementById('edit-method').value;
      var currentAmount = document.getElementById('edit-amount').value;
      var currentStatus = document.getElementById('edit-status').value;
      var currentRemarks = document.getElementById('edit-remarks').value;
      
      return currentRef !== initialEditValues.reference || 
             currentMethod !== initialEditValues.method || 
             currentAmount !== initialEditValues.amount || 
             currentStatus !== initialEditValues.status || 
             currentRemarks !== initialEditValues.remarks;
    }

    // Store initial edit values when opening modal
    document.addEventListener('click',function(e){
      var editBtn=e.target.closest('.btn-edit');
      if(!editBtn)return;
      setTimeout(function(){
        initialEditValues = {
          reference: document.getElementById('edit-reference').value,
          method: document.getElementById('edit-method').value,
          amount: document.getElementById('edit-amount').value,
          status: document.getElementById('edit-status').value,
          remarks: document.getElementById('edit-remarks').value
        };
        clearEditPaymentErrors();
      }, 100);
    });

    document.getElementById('saveEditBtn').addEventListener('click',function(){
      clearEditPaymentErrors();
      if(!hasEditPaymentChanges()){
        showEditPaymentErrors(['No changes detected. Please modify a field before saving.']);
        return;
      }

      var id=this.dataset.id;if(!id)return;
      var btn=this;
      var status=document.getElementById('edit-status').value;
      var reason=document.getElementById('edit-remarks').value;
      var method=document.getElementById('edit-method').value||null;
      var amount=document.getElementById('edit-amount').value||null;
      var txn=document.getElementById('edit-reference').value;

      btn.disabled=true;btn.textContent='Saving…';
      clearEditPaymentErrors();

      // If status is "rejected", use the dedicated /reject endpoint
      // to satisfy backend validation (PUT doesn't accept "rejected")
      if(status==='rejected'){
        fetch('/admin/global-management/proof-of-payments/'+id+'/reject',{
          method:'POST',
          headers:{'X-CSRF-TOKEN':CSRF,'Content-Type':'application/json'},
          body:JSON.stringify({reason:reason})
        })
        .then(function(r){return r.json();})
        .then(function(res){
          if(res.success){showToastAndClose('Payment marked as invalid.','success','editPaymentModal');setTimeout(function(){location.reload();},SUCCESS_RELOAD_DELAY);}
          else{showToastAndClose(res.message||'Failed to update.','error','editPaymentModal');btn.disabled=false;btn.textContent='Save Changes';}
        })
        .catch(function(){showToastAndClose('Request failed.','error','editPaymentModal');btn.disabled=false;btn.textContent='Save Changes';});
        return;
      }

      // If status is "approved", use the dedicated /verify endpoint
      if(status==='approved'){
        fetch('/admin/global-management/proof-of-payments/'+id+'/verify',{
          method:'POST',
          headers:{'X-CSRF-TOKEN':CSRF,'Content-Type':'application/json'}
        })
        .then(function(r){return r.json();})
        .then(function(res){
          if(res.success){showToastAndClose('Payment approved successfully.','success','editPaymentModal');setTimeout(function(){location.reload();},SUCCESS_RELOAD_DELAY);}
          else{showToastAndClose(res.message||'Failed to update.','error','editPaymentModal');btn.disabled=false;btn.textContent='Save Changes';}
        })
        .catch(function(){showToastAndClose('Request failed.','error','editPaymentModal');btn.disabled=false;btn.textContent='Save Changes';});
        return;
      }

      // For "submitted" (pending) — use normal PUT with allowed fields only
      var payload={payment_status:status,transaction_number:txn};
      if(method)payload.payment_type=method;
      if(amount)payload.amount=amount;
      if(reason)payload.reason=reason;

      fetch('/admin/global-management/proof-of-payments/'+id,{
        method:'PUT',
        headers:{'X-CSRF-TOKEN':CSRF,'Content-Type':'application/json'},
        body:JSON.stringify(payload)
      })
      .then(function(r){return r.json();})
      .then(function(res){
        if(res.success){showToastAndClose('Payment updated successfully.','success','editPaymentModal');setTimeout(function(){location.reload();},SUCCESS_RELOAD_DELAY);}
        else{showToastAndClose(res.message||'Failed to update.','error','editPaymentModal');btn.disabled=false;btn.textContent='Save Changes';}
      })
      .catch(function(){showToastAndClose('Request failed.','error','editPaymentModal');btn.disabled=false;btn.textContent='Save Changes';});
    });

    // APPROVE
    var _approveId=null;
    document.getElementById('pp-approve').addEventListener('click',function(){_approveId=this.dataset.id;document.getElementById('approveSummary').textContent='#'+_approveId+(this.dataset.project?' — '+this.dataset.project:'');closeModal('pendingPaymentModal');openModal('confirmApproveModal');});
    document.getElementById('confirmApproveBtn').addEventListener('click',function(){
      if(!_approveId)return;var btn=this;btn.disabled=true;btn.textContent='Approving…';
      fetch('/admin/global-management/proof-of-payments/'+_approveId+'/verify',{method:'POST',headers:{'X-CSRF-TOKEN':CSRF,'Content-Type':'application/json'}})
        .then(function(r){return r.json();})
        .then(function(res){
          if(res.success){
            showToastAndClose('Payment approved successfully.','success','confirmApproveModal');
            setTimeout(function(){location.reload();},SUCCESS_RELOAD_DELAY);
          }else{
            showToastAndClose(res.message||'Failed.','error','confirmApproveModal');
            btn.disabled=false;
            btn.textContent='Yes, Approve';
          }
        })
        .catch(function(){
          showToastAndClose('Request failed.','error','confirmApproveModal');
          btn.disabled=false;
          btn.textContent='Yes, Approve';
        });
    });

    // REJECT
    var _rejectId=null;
    document.getElementById('pp-reject').addEventListener('click',function(){_rejectId=this.dataset.id;document.getElementById('rejectSummary').textContent='#'+_rejectId+(this.dataset.project?' — '+this.dataset.project:'');document.getElementById('rejectReasonInput').value='';closeModal('pendingPaymentModal');openModal('confirmRejectModal');});
    document.getElementById('confirmRejectBtn').addEventListener('click',function(){
      if(!_rejectId)return;var reason=document.getElementById('rejectReasonInput').value.trim(),btn=this;btn.disabled=true;btn.textContent='Rejecting…';
      fetch('/admin/global-management/proof-of-payments/'+_rejectId+'/reject',{method:'POST',headers:{'X-CSRF-TOKEN':CSRF,'Content-Type':'application/json'},body:JSON.stringify({reason:reason})})
        .then(function(r){return r.json();})
        .then(function(res){
          if(res.success){
            showToastAndClose('Payment rejected.','success','confirmRejectModal');
            setTimeout(function(){location.reload();},SUCCESS_RELOAD_DELAY);
          }else{
            showToastAndClose(res.message||'Failed.','error','confirmRejectModal');
            btn.disabled=false;
            btn.textContent='Yes, Reject';
          }
        })
        .catch(function(){
          showToastAndClose('Request failed.','error','confirmRejectModal');
          btn.disabled=false;
          btn.textContent='Yes, Reject';
        });
    });

    // DELETE
    var _deleteId=null;

    document.getElementById('cancelDeletePaymentBtn').addEventListener('click',function(){closeModal('deletePaymentModal');});
    document.getElementById('confirmDeletePaymentBtn').addEventListener('click',function(){
      if(!_deleteId)return;
      var reasonEl=document.getElementById('deletePaymentReason');
      var errorEl=document.getElementById('deletePaymentReasonError');
      var reason=reasonEl?reasonEl.value.trim():'';
      if(!reason){
        errorEl.classList.remove('hidden');
        reasonEl.focus();
        return;
      }
      errorEl.classList.add('hidden');
      var btn=this;
      btn.disabled=true;
      btn.innerHTML='<i class="fi fi-rr-spinner fi-spin"></i>&nbsp;Deleting…';
      fetch('/admin/global-management/proof-of-payments/'+_deleteId,{
        method:'DELETE',
        headers:{'X-CSRF-TOKEN':CSRF,'Content-Type':'application/json'},
        body:JSON.stringify({reason:reason})
      })
        .then(function(r){return r.json();})
        .then(function(res){
          if(res.success){
            showToastAndClose('Payment record deleted.','success','deletePaymentModal');
            setTimeout(function(){location.reload();},SUCCESS_RELOAD_DELAY);
          }else{
            showToast(res.message||'Failed.','error');
            btn.disabled=false;
            btn.innerHTML='<i class="fi fi-rr-trash"></i>&nbsp;Delete';
          }
        })
        .catch(function(){
          showToast('Request failed.','error');
          btn.disabled=false;
          btn.innerHTML='<i class="fi fi-rr-trash"></i>&nbsp;Delete';
        });
    });

    // ============================================
    // Universal File Viewer (UFV) - Dark Theme
    // ============================================
    (function() {
        const modal = document.getElementById('documentViewerModal');
        const iframe = document.getElementById('documentViewerFrame');
        const img = document.getElementById('documentViewerImg');
        const closeBtn = document.getElementById('closeDocumentViewerBtn');

        if (!modal) {
            console.error('UFV: documentViewerModal not found!');
            return;
        }

        function openDocumentViewer(src, title) {
            console.log('UFV: Opening viewer with src:', src, 'title:', title);
            if (!modal) return;
            const isPdf = /\.pdf(\?|$)/i.test(src);
            const titleEl = document.getElementById('documentViewerTitle');
            const downloadLink = document.getElementById('documentViewerDownload');

            if (titleEl) titleEl.textContent = title || 'Document Viewer';
            if (downloadLink) downloadLink.href = src;

            if (isPdf) {
                if (iframe) {
                    iframe.src = src;
                    iframe.classList.remove('hidden');
                }
                if (img) img.classList.add('hidden');
            } else {
                if (img) {
                    img.src = src;
                    img.classList.remove('hidden');
                }
                if (iframe) iframe.classList.add('hidden');
            }

            modal.classList.remove('hidden');
            modal.classList.add('flex');
            document.body.style.overflow = 'hidden';

            const modalShell = modal.querySelector('.modal-shell');
            if (modalShell) {
                setTimeout(function() {
                    modalShell.classList.remove('scale-95', 'opacity-0');
                    modalShell.classList.add('scale-100', 'opacity-100');
                }, 10);
            }
        }

        function closeDocumentViewer() {
            console.log('UFV: Closing viewer');
            if (!modal) return;
            const modalShell = modal.querySelector('.modal-shell');
            if (modalShell) {
                modalShell.classList.remove('scale-100', 'opacity-100');
                modalShell.classList.add('scale-95', 'opacity-0');
            }
            setTimeout(function() {
                modal.classList.add('hidden');
                modal.classList.remove('flex');
                document.body.style.overflow = 'auto';
                if (iframe) iframe.src = '';
                if (img) img.src = '';
            }, 200);
        }

        // Delegated click handler for open buttons
        document.addEventListener('click', function(e) {
            console.log('UFV: Click detected on:', e.target);
            const btn = e.target.closest && e.target.closest('.open-doc-btn');
            console.log('UFV: Closest .open-doc-btn:', btn);
            if (btn) {
                console.log('UFV: Button clicked!');
                e.preventDefault();
                e.stopPropagation();
                const src = btn.getAttribute('data-doc-src');
                const title = btn.getAttribute('data-doc-title') || 'Document';
                console.log('UFV: src:', src, 'title:', title);
                if (src && src !== '#') {
                    openDocumentViewer(src, title);
                } else {
                    showToast('No document available', 'error');
                }
            }
        }, true); // Use capture phase

        // Close button
        if (closeBtn) {
            closeBtn.addEventListener('click', closeDocumentViewer);
        }

        // Close on backdrop click
        if (modal) {
            modal.addEventListener('click', function(e) {
                if (e.target === modal) {
                    closeDocumentViewer();
                }
            });
        }

        // Close on ESC key
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape' && modal && !modal.classList.contains('hidden')) {
                closeDocumentViewer();
            }
        });

        console.log('UFV: Initialization complete');
    })();

    animatePaymentsRows();

  }());
  </script>

</body>
</html>