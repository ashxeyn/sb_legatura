<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <title>Bid Management - Legatura Admin</title>

  <script src="https://cdn.tailwindcss.com"></script>
  <link rel="stylesheet" href="{{ asset('css/admin/home/mainComponents.css') }}">
  <link rel="stylesheet" href="{{ asset('css/admin/globalManagement/bidManagement.css') }}">

  <link rel='stylesheet' href='https://cdn-uicons.flaticon.com/3.0.0/uicons-solid-straight/css/uicons-solid-straight.css'>
  <link rel='stylesheet' href='https://cdn-uicons.flaticon.com/3.0.0/uicons-solid-rounded/css/uicons-solid-rounded.css'>
  <link rel='stylesheet' href='https://cdn-uicons.flaticon.com/3.0.0/uicons-bold-rounded/css/uicons-bold-rounded.css'>
  <link rel='stylesheet' href='https://cdn-uicons.flaticon.com/3.0.0/uicons-regular-rounded/css/uicons-regular-rounded.css'>

  <script src="{{ asset('js/admin/home/mainComponents.js') }}" defer></script>
  <style>
    .modal-active { display: flex !important; }

    /* Hide scrollbars but keep scrolling enabled inside Bid Details and Edit modals */
    #modal-view .scrollbar-hidden,
    #modal-edit .scrollbar-hidden {
      -ms-overflow-style: none;
      scrollbar-width: none;
    }

    #modal-view .scrollbar-hidden::-webkit-scrollbar,
    #modal-edit .scrollbar-hidden::-webkit-scrollbar {
      display: none;
      width: 0;
      height: 0;
    }

    /* Modal scale and opacity transitions */
    .modal-content {
      transition: all 300ms ease-in-out;
    }
  </style>
</head>

<body class="bg-gray-50 text-gray-800 font-sans">
<div class="flex min-h-screen">

  @include('admin.layouts.sidebar')

  <main class="flex-1">

    @include('admin.layouts.topnav', ['pageTitle' => 'Bid Management'])

    <div class="p-8">

      {{-- ── STAT CARDS ── --}}
      @php
        $totalBids    = \Illuminate\Support\Facades\DB::table('bids')->count();
        $pendingBids  = \Illuminate\Support\Facades\DB::table('bids')->whereIn('bid_status',['submitted','under_review'])->count();
        $approvedBids = \Illuminate\Support\Facades\DB::table('bids')->where('bid_status','accepted')->count();
        $rejectedBids = \Illuminate\Support\Facades\DB::table('bids')->where('bid_status','rejected')->count();
        $pendingPct   = $totalBids > 0 ? round(($pendingBids  / $totalBids) * 100) : 0;
        $approvedPct  = $totalBids > 0 ? round(($approvedBids / $totalBids) * 100) : 0;
        $rejectedPct  = $totalBids > 0 ? round(($rejectedBids / $totalBids) * 100) : 0;
      @endphp

      <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">

        <div class="stat-card bg-white rounded-xl shadow-sm p-4">
          <div class="flex justify-between items-start gap-3 mb-3">
            <div>
              <p class="text-[11px] font-semibold uppercase tracking-[0.14em] text-gray-500 mb-1.5">Total Bids</p>
              <h2 class="text-3xl font-bold leading-none text-orange-500 stat-number">{{ number_format($totalBids) }}</h2>
              <div class="stat-badge flex items-center gap-1.5 px-2 py-1 rounded-full bg-blue-100 mt-2 w-fit">
                <i class="fi fi-sr-inbox-in text-[10px] text-blue-600"></i>
                <span class="text-[11px] font-semibold text-blue-600">All records</span>
              </div>
            </div>
            <div class="stat-icon-wrap bg-blue-100 p-2.5 rounded-lg">
              <i class="fi fi-sr-inbox-in text-lg text-blue-600"></i>
            </div>
          </div>
          <p class="text-[11px] text-gray-400">All time</p>
        </div>

        <div class="stat-card bg-white rounded-xl shadow-sm p-4">
          <div class="flex justify-between items-start gap-3 mb-3">
            <div>
              <p class="text-[11px] font-semibold uppercase tracking-[0.14em] text-gray-500 mb-1.5">Pending Reviews</p>
              <h2 class="text-3xl font-bold leading-none text-orange-500 stat-number">{{ number_format($pendingBids) }}</h2>
              <div class="stat-badge flex items-center gap-1.5 px-2 py-1 rounded-full bg-orange-100 mt-2 w-fit">
                <i class="fi fi-sr-hourglass-end text-[10px] text-orange-600"></i>
                <span class="text-[11px] font-semibold text-orange-600">Awaiting review</span>
              </div>
            </div>
            <div class="stat-icon-wrap bg-orange-100 p-2.5 rounded-lg">
              <i class="fi fi-sr-hourglass-end text-lg text-orange-600"></i>
            </div>
          </div>
          <p class="text-[11px] text-gray-400">Submitted + under review</p>
        </div>

        <div class="stat-card bg-white rounded-xl shadow-sm p-4">
          <div class="flex justify-between items-start gap-3 mb-3">
            <div>
              <p class="text-[11px] font-semibold uppercase tracking-[0.14em] text-gray-500 mb-1.5">Approved Bids</p>
              <h2 class="text-3xl font-bold leading-none text-orange-500 stat-number">{{ number_format($approvedBids) }}</h2>
              <div class="stat-badge flex items-center gap-1.5 px-2 py-1 rounded-full bg-green-100 mt-2 w-fit">
                <i class="fi fi-sr-check-circle text-[10px] text-green-600"></i>
                <span class="text-[11px] font-semibold text-green-600">Accepted bids</span>
              </div>
            </div>
            <div class="stat-icon-wrap bg-green-100 p-2.5 rounded-lg">
              <i class="fi fi-sr-check-circle text-lg text-green-600"></i>
            </div>
          </div>
          <p class="text-[11px] text-gray-400">Accepted status</p>
        </div>

        <div class="stat-card bg-white rounded-xl shadow-sm p-4">
          <div class="flex justify-between items-start gap-3 mb-3">
            <div>
              <p class="text-[11px] font-semibold uppercase tracking-[0.14em] text-gray-500 mb-1.5">Rejected Bids</p>
              <h2 class="text-3xl font-bold leading-none text-orange-500 stat-number">{{ number_format($rejectedBids) }}</h2>
              <div class="stat-badge flex items-center gap-1.5 px-2 py-1 rounded-full bg-red-100 mt-2 w-fit">
                <i class="fi fi-sr-cross-circle text-[10px] text-red-600"></i>
                <span class="text-[11px] font-semibold text-red-600">Marked rejected</span>
              </div>
            </div>
            <div class="stat-icon-wrap bg-red-100 p-2.5 rounded-lg">
              <i class="fi fi-sr-cross-circle text-lg text-red-600"></i>
            </div>
          </div>
          <p class="text-[11px] text-gray-400">Rejected status</p>
        </div>

      </div>

      {{-- ══ FILTER BAR (below stats, above table — same as proofOfPayments) ══ --}}
      <form id="bidsFilterForm" method="GET" action="{{ route('admin.globalManagement.bidManagement') }}">
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-4 mb-6 flex flex-wrap items-center gap-3">
          <div class="flex flex-wrap items-center gap-3 flex-1 min-w-[260px]">
            <div class="flex items-center gap-2 px-3 py-2 border border-gray-300 rounded-lg text-sm text-gray-700">
              <i class="fi fi-rr-filter text-gray-500"></i>
              <span>Filter By</span>
            </div>

            <div class="relative flex-1 min-w-[220px] max-w-sm">
              <input id="bidSearch" name="search" type="text"
                placeholder="Search project or contractor…"
                value="{{ request('search') }}"
                class="w-full px-4 py-2 pr-10 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-indigo-400 focus:outline-none">
              <i class="fi fi-rr-search absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 pointer-events-none"></i>
            </div>
          </div>

          <div class="ml-auto flex flex-wrap items-center justify-end gap-3 w-full sm:w-auto">
            <select id="bidStatusFilter" name="status"
              class="px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-indigo-400 focus:outline-none bg-white text-gray-700">
              <option value="">All Statuses</option>
              <option value="submitted"    {{ request('status') === 'submitted'    ? 'selected' : '' }}>Submitted</option>
              <option value="under_review" {{ request('status') === 'under_review' ? 'selected' : '' }}>Under Review</option>
              <option value="accepted"     {{ request('status') === 'accepted'     ? 'selected' : '' }}>Approved</option>
              <option value="rejected"     {{ request('status') === 'rejected'     ? 'selected' : '' }}>Rejected</option>
              <option value="cancelled"    {{ request('status') === 'cancelled'    ? 'selected' : '' }}>Cancelled</option>
            </select>

            <button type="submit"
              class="px-4 py-2 bg-indigo-600 text-white rounded-lg text-sm font-medium hover:bg-indigo-700 transition">
              Filter
            </button>

            @if(request('search') || request('status'))
              <a href="{{ route('admin.globalManagement.bidManagement') }}"
                 class="clear-bids-filters px-3 py-2 border border-gray-300 text-gray-600 rounded-lg text-sm hover:bg-gray-50 transition">
                Clear
              </a>
            @endif
          </div>
        </div>
      </form>

      {{-- ══ TABLE CARD ══ --}}
      <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden" id="bidsTableWrap">
        @include('admin.globalManagement.partials.bidManagementTable', ['bids' => $bids])
      </div>{{-- /table card --}}
    </div>{{-- /p-8 --}}
  </main>
</div>

{{-- ══════════════════ VIEW MODAL ══════════════════ --}}
<div id="modal-view" class="fixed inset-0 bg-black/45 backdrop-blur-sm items-center justify-center z-[100] p-3 sm:p-4" style="display:none;">
  <div class="relative bg-white w-full max-w-2xl rounded-2xl shadow-2xl border border-gray-200 overflow-hidden max-h-[84vh] flex flex-col">
    <div id="v-modal-header" class="flex items-center justify-between px-4 py-3.5 bg-gradient-to-r from-indigo-600 to-blue-600 border-b border-indigo-700 text-white flex-shrink-0">
      <div class="flex items-center gap-2.5">
        <div id="v-modal-icon-wrap" class="w-9 h-9 rounded-xl bg-white/20 border border-white/20 flex items-center justify-center shadow">
          <i class="fi fi-ss-document text-white text-base"></i>
        </div>
        <div>
          <h2 class="text-[15px] font-bold leading-tight">Bid Details</h2>
          <p id="v-modal-subtitle" class="text-[10px] text-indigo-100 mt-0.5">
            Bid <span id="v-bid-id" class="font-semibold text-white">—</span>
            &nbsp;·&nbsp;
            <span id="v-status-badge" class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-semibold border border-white/20 bg-white/15 text-white">—</span>
          </p>
        </div>
      </div>
      <button id="closeViewModal" class="p-1.5 rounded-xl hover:bg-white/10 text-white/80 hover:text-white transition">
        <i class="fi fi-rr-cross-small text-lg"></i>
      </button>
    </div>

    <div class="flex-1 overflow-y-auto scrollbar-hidden p-4 space-y-3.5">
      <div class="rounded-xl border border-indigo-200 bg-indigo-50/60 p-3.5">
        <div class="flex items-center gap-2 mb-2.5">
          <i class="fi fi-ss-user text-indigo-600 text-sm"></i>
          <h3 class="text-[13px] font-semibold text-gray-800">Bidder Information</h3>
        </div>
        <dl class="space-y-1.5 text-[12px] leading-5">
          <div class="flex justify-between gap-3"><dt class="text-gray-500 shrink-0">Company Name</dt><dd id="v-company-name" class="font-semibold text-gray-800 text-right">—</dd></div>
          <div class="flex justify-between gap-3"><dt class="text-gray-500 shrink-0">Email Address</dt><dd id="v-company-email" class="font-medium text-gray-800 text-right break-all">—</dd></div>
          <div class="border-t border-indigo-200 pt-2">
            <p class="text-[10px] font-semibold uppercase tracking-[0.12em] text-indigo-500 mb-1.5">Licenses &amp; Registrations</p>
            <div class="space-y-1.5">
              <div class="flex justify-between gap-3"><dt class="text-gray-500 shrink-0">PCAB No.</dt><dd id="v-pcab" class="font-medium text-gray-800 text-right">—</dd></div>
              <div class="flex justify-between gap-3"><dt class="text-gray-500 shrink-0">PCAB Category</dt><dd id="v-pcab-category" class="font-medium text-gray-800 text-right">—</dd></div>
              <div class="flex justify-between gap-3"><dt class="text-gray-500 shrink-0">PCAB Expiry</dt><dd id="v-pcab-expiry" class="font-medium text-gray-800 text-right">—</dd></div>
              <div class="flex justify-between gap-3"><dt class="text-gray-500 shrink-0">Business Permit No.</dt><dd id="v-bp-number" class="font-medium text-gray-800 text-right">—</dd></div>
              <div class="flex justify-between gap-3"><dt class="text-gray-500 shrink-0">Permit City</dt><dd id="v-bp-city" class="font-medium text-gray-800 text-right">—</dd></div>
              <div class="flex justify-between gap-3"><dt class="text-gray-500 shrink-0">Permit Expiry</dt><dd id="v-bp-expiry" class="font-medium text-gray-800 text-right">—</dd></div>
              <div class="flex justify-between gap-3"><dt class="text-gray-500 shrink-0">TIN / Business Reg.</dt><dd id="v-tin" class="font-medium text-gray-800 text-right">—</dd></div>
            </div>
          </div>
        </dl>
      </div>

      <div class="rounded-xl border border-purple-200 bg-purple-50/60 p-3.5">
        <div class="flex items-center gap-2 mb-2.5">
          <i class="fi fi-ss-building text-purple-600 text-sm"></i>
          <h3 class="text-[13px] font-semibold text-gray-800">Project &amp; Bid Details</h3>
        </div>
        <dl class="space-y-1.5 text-[12px] leading-5">
          <div class="flex justify-between gap-3"><dt class="text-gray-500 shrink-0">Project Title</dt><dd id="v-project-title" class="font-semibold text-gray-800 text-right">—</dd></div>
          <div class="flex justify-between gap-3"><dt class="text-gray-500 shrink-0">Proposed Cost</dt><dd id="v-proposed-cost" class="font-bold text-green-700 text-right">—</dd></div>
          <div class="flex justify-between gap-3"><dt class="text-gray-500 shrink-0">Estimated Timeline</dt><dd id="v-timeline" class="font-medium text-gray-800 text-right">—</dd></div>
          <div class="flex justify-between gap-3"><dt class="text-gray-500 shrink-0">Submitted</dt><dd id="v-submitted-at" class="font-medium text-gray-800 text-right">—</dd></div>
          <div class="flex justify-between gap-3"><dt class="text-gray-500 shrink-0">Decision Date</dt><dd id="v-decision-date" class="font-medium text-gray-800 text-right">—</dd></div>
        </dl>

        <div class="mt-3">
          <label class="block text-[10px] font-semibold uppercase tracking-[0.12em] text-purple-500 mb-1.5">Contractor Notes</label>
          <textarea id="v-notes" rows="3" readonly class="w-full text-[12px] border border-purple-200 rounded-lg px-3 py-2 bg-white resize-none focus:outline-none"></textarea>
        </div>
      </div>

      <div id="v-reason-block" class="hidden rounded-xl border border-gray-200 bg-gray-50 p-3.5">
        <div class="flex items-center gap-2 mb-2.5">
          <div id="v-reason-icon-wrap" class="flex items-center justify-center w-8 h-8 rounded-lg"><i id="v-reason-icon" class="text-white text-sm"></i></div>
          <h3 id="v-reason-label" class="text-[13px] font-semibold text-gray-800">Remarks</h3>
        </div>
        <textarea id="v-reason-text" rows="3" readonly class="w-full text-[12px] border rounded-lg px-3 py-2 bg-white resize-none focus:outline-none"></textarea>
      </div>

      <div class="rounded-xl border border-gray-200 overflow-hidden">
        <div class="flex items-center gap-2 px-3.5 py-2.5 bg-gray-50 border-b border-gray-200">
          <i class="fi fi-ss-folder text-blue-600 text-sm"></i>
          <h3 class="text-[13px] font-semibold text-gray-800">Supporting Files</h3>
        </div>
        <div id="v-files-container" class="p-4 text-center text-sm text-gray-400">Loading files…</div>
      </div>
    </div>
  </div>
</div>

{{-- ══════════════════ EDIT MODAL ══════════════════ --}}
<div id="modal-edit" class="fixed inset-0 bg-black/50 backdrop-blur-sm items-center justify-center z-[100] p-3 sm:p-4" style="display:none;">
  <div class="bg-white rounded-3xl shadow-2xl w-full max-w-2xl max-h-[84vh] flex flex-col">
    <div class="flex items-center justify-between px-4 py-3.5 border-b border-gray-200 bg-gradient-to-r from-amber-500 to-orange-600 text-white flex-shrink-0 rounded-t-3xl">
      <div class="flex items-center gap-2.5">
        <div class="w-9 h-9 rounded-xl bg-white/20 border border-white/20 flex items-center justify-center shadow">
          <i class="fi fi-sr-edit text-white text-base"></i>
        </div>
        <div>
          <h2 class="text-[15px] font-bold leading-tight">Edit Bid</h2>
          <p class="text-[10px] text-orange-100 mt-0.5">Bid <span id="e-bid-id" class="font-semibold text-white">—</span></p>
        </div>
      </div>
      <button id="closeEditModal" class="p-1.5 rounded-xl hover:bg-white/10 text-white/80 hover:text-white transition">
        <i class="fi fi-rr-cross-small text-lg"></i>
      </button>
    </div>

    <div class="flex-1 overflow-y-auto scrollbar-hidden p-4 space-y-3.5">
      <!-- Validation Error Section -->
      <div id="editBidErrorAlert" class="hidden bg-red-50 border-l-4 border-red-500 p-3 rounded-r-lg text-left">
        <div class="flex items-start gap-2">
          <i class="fi fi-rr-exclamation text-red-600 text-sm flex-shrink-0 mt-0.5"></i>
          <div class="flex-1">
            <p class="text-xs font-semibold text-red-800 mb-1">Validation Error</p>
            <ul id="editBidErrorList" class="text-xs text-red-700 space-y-0.5 list-disc list-inside">
              <!-- Error messages populated by JS -->
            </ul>
          </div>
        </div>
      </div>
      <div class="rounded-xl border border-gray-200 bg-gray-50 p-3.5">
        <p class="text-[10px] font-semibold uppercase tracking-[0.12em] text-gray-500 mb-2.5">Read-only Information</p>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
          <div><label class="block text-[12px] font-semibold text-gray-600 mb-1">Company Name</label><input type="text" id="e-company-name" class="w-full text-[12px] border border-gray-200 rounded-lg px-3 py-2 bg-white text-gray-600 cursor-not-allowed" readonly></div>
          <div><label class="block text-[12px] font-semibold text-gray-600 mb-1">Email Address</label><input type="text" id="e-company-email" class="w-full text-[12px] border border-gray-200 rounded-lg px-3 py-2 bg-white text-gray-600 cursor-not-allowed" readonly></div>
          <div><label class="block text-[12px] font-semibold text-gray-600 mb-1">Project Title</label><input type="text" id="e-project-title" class="w-full text-[12px] border border-gray-200 rounded-lg px-3 py-2 bg-white text-gray-600 cursor-not-allowed" readonly></div>
          <div><label class="block text-[12px] font-semibold text-gray-600 mb-1">Estimated Timeline</label><input type="text" id="e-timeline" class="w-full text-[12px] border border-gray-200 rounded-lg px-3 py-2 bg-white text-gray-600 cursor-not-allowed" readonly></div>
        </div>
      </div>

      <div class="space-y-3">
        <div>
          <label class="block text-[12px] font-semibold text-gray-700 mb-1">Proposed Cost (₱)</label>
          <div class="relative"><span class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-500 font-semibold text-[12px]">₱</span><input type="text" id="e-proposed-cost" class="w-full text-[12px] border-2 border-gray-300 rounded-lg pl-7 pr-4 py-2 focus:ring-2 focus:ring-amber-400 focus:border-amber-400"></div>
        </div>
        <div>
          <label class="block text-[12px] font-semibold text-gray-700 mb-1">Status</label>
          <select id="e-status" class="w-full text-[12px] border-2 border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-amber-400 focus:border-amber-400">
            <option value="submitted">Submitted</option>
            <option value="under_review">Under Review</option>
            <option value="accepted">Approved</option>
            <option value="rejected">Rejected</option>
            <option value="cancelled">Cancelled</option>
          </select>
        </div>
        <div>
          <label class="block text-[12px] font-semibold text-gray-700 mb-1">Contractor Notes</label>
          <textarea id="e-notes" rows="4" class="w-full text-[12px] border-2 border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-amber-400 focus:border-amber-400 resize-none" placeholder="Contractor notes…"></textarea>
        </div>
      </div>
    </div>

    <div class="flex items-center justify-end gap-3 px-4 py-3 border-t border-gray-200 bg-gray-50 flex-shrink-0 rounded-b-3xl">
      <button id="cancelEditBtn" class="px-4 py-2 rounded-xl border-2 border-gray-300 text-gray-700 font-semibold text-[12px] hover:bg-gray-100 transition">Cancel</button>
      <button id="saveChangesBtn" class="px-4 py-2 rounded-xl bg-gradient-to-r from-amber-500 to-orange-600 text-white font-semibold text-[12px] hover:from-amber-600 hover:to-orange-700 shadow transition flex items-center gap-2"><i class="fi fi-rr-disk"></i> Save Changes</button>
    </div>
  </div>
</div>

{{-- ══════════════════ SAVE CONFIRM ══════════════════ --}}
<div id="modal-save-confirm" class="fixed inset-0 bg-black/60 backdrop-blur-sm items-center justify-center z-[110] p-4" style="display:none;">
  <div class="bg-white rounded-2xl shadow-2xl w-full max-w-md p-7 text-center">
    <div class="flex items-center justify-center w-16 h-16 rounded-full bg-amber-100 mx-auto mb-4"><i class="fi fi-rr-interrogation text-amber-600 text-3xl"></i></div>
    <h3 class="text-xl font-bold text-gray-800 mb-2">Save Changes?</h3>
    <p class="text-gray-500 text-sm mb-6">This will update the bid record in the database.</p>
    <div class="flex items-center gap-3">
      <button id="cancelSaveBtn" class="flex-1 px-4 py-3 rounded-xl border-2 border-gray-300 text-gray-700 font-semibold text-sm hover:bg-gray-100">Cancel</button>
      <button id="confirmSaveBtn" class="flex-1 px-4 py-3 rounded-xl bg-gradient-to-r from-amber-500 to-orange-600 text-white font-semibold text-sm hover:from-amber-600 hover:to-orange-700 shadow">Yes, Save</button>
    </div>
  </div>
</div>

{{-- ══════════════════ DELETE CONFIRM ══════════════════ --}}
<div id="modal-delete-confirm" class="fixed inset-0 bg-black/50 backdrop-blur-sm z-[110] hidden items-center justify-center p-2" style="display:none;">
  <div class="bg-white rounded-2xl shadow-lg max-w-xs w-full transform transition-all duration-300 scale-95 opacity-0 modal-content relative">
    <button id="closeDeleteConfirmBtn" type="button" class="absolute top-2 right-2 w-6 h-6 rounded-lg border border-gray-200 text-gray-400 hover:text-gray-600 hover:bg-gray-100 transition flex items-center justify-center">
      <i class="fi fi-rr-cross text-[9px]"></i>
    </button>

    <!-- Icon Section -->
    <div class="flex justify-center pt-3 pb-1.5">
      <div class="w-12 h-12 bg-red-100 rounded-full flex items-center justify-center relative">
        <div class="absolute inset-0 bg-red-200 rounded-full animate-ping opacity-60"></div>
        <div class="relative w-8 h-8 bg-red-500 rounded-full flex items-center justify-center">
          <i class="fi fi-rr-trash text-white text-sm"></i>
        </div>
      </div>
    </div>

    <!-- Content Section -->
    <div class="px-3 pb-3 text-center">
      <h2 class="text-xs font-bold text-gray-800 mb-1">Delete Bid?</h2>
      <p class="text-[10px] text-gray-600 leading-relaxed mb-2.5">
        Permanently delete Bid <span class="font-bold text-gray-800" id="d2-bid-id">—</span>? This cannot be undone.
      </p>

      <div class="text-left space-y-1 text-[10px] mb-2.5 bg-gray-50 p-2 rounded-lg">
        <div class="flex justify-between gap-2"><span class="text-gray-500 font-medium shrink-0">Project:</span><span id="d2-project-title" class="font-semibold text-gray-800 text-right truncate">--</span></div>
        <div class="flex justify-between gap-2"><span class="text-gray-500 font-medium shrink-0">Contractor:</span><span id="d2-company-name" class="font-semibold text-gray-800 text-right truncate">--</span></div>
      </div>
    </div>

    <!-- Action Buttons -->
    <div class="px-3 pb-3 space-y-2">
      <button id="confirmDeleteBtn" 
        class="w-full px-2.5 py-1.5 bg-gradient-to-r from-red-500 to-red-600 hover:from-red-600 hover:to-red-700 text-white rounded-md transition-all text-[11px] font-semibold shadow-sm hover:shadow-md transform hover:scale-[1.01] active:scale-95 flex items-center justify-center gap-1">
        <i class="fi fi-rr-trash text-[10px]"></i>
        Delete
      </button>
      <button id="cancelDeleteBtn"
        class="w-full px-2.5 py-1.5 border border-gray-300 text-gray-700 rounded-md hover:bg-gray-50 transition-all text-[11px] font-semibold transform hover:scale-[1.01] active:scale-95">
        Cancel
      </button>
    </div>
  </div>
</div>

{{-- ══════════════════ DELETE CONFIRMATION (Final) ══════════════════ --}}
<div id="modal-delete-final-confirm" class="fixed inset-0 bg-black/60 backdrop-blur-sm items-center justify-center z-[120] p-4" style="display:none;">
  <div class="bg-white rounded-2xl shadow-2xl w-full max-w-sm p-5 text-center transform transition-all duration-300 scale-95 opacity-0 modal-content-final relative">
    <div class="flex items-center justify-center w-14 h-14 rounded-full bg-red-100 mx-auto mb-3"><i class="fi fi-rr-trash text-red-600 text-2xl"></i></div>
    <h3 class="text-sm font-bold text-gray-800 mb-1">Confirm Deletion</h3>
    <p class="text-gray-600 text-xs mb-4">This will permanently delete the bid.</p>
    <div class="space-y-2">
      <button id="confirmDeleteFinalBtn" class="w-full px-3 py-2 rounded-md bg-gradient-to-r from-red-500 to-red-600 text-white font-semibold text-[11px] hover:from-red-600 hover:to-red-700 shadow transition flex items-center justify-center gap-1.5"><i class="fi fi-rr-trash text-[10px]"></i> Delete</button>
       <button id="cancelDeleteFinalBtn" class="w-full px-3 py-2 rounded-md border border-gray-300 text-gray-700 font-semibold text-[11px] hover:bg-gray-50 transition">Cancel</button>
    </div>
  </div>
</div>

{{-- ══════════════════ DOCUMENT VIEWER MODAL (Dark Theme) ══════════════════ --}}
<div id="documentViewerModal" class="hidden fixed inset-0 bg-black/50 backdrop-blur-sm z-[110] items-center justify-center p-4">
  <div class="relative w-full max-w-5xl h-[90vh] bg-[#1e1e2e] rounded-2xl shadow-2xl flex flex-col overflow-hidden">
    <div class="flex items-center justify-between px-6 py-4 bg-[#2a2a3e] border-b border-gray-700">
      <h3 id="docViewerTitle" class="text-lg font-semibold text-white">Document Viewer</h3>
      <button id="closeDocViewer" class="text-gray-400 hover:text-white transition-colors p-2 hover:bg-white/10 rounded-lg">
        <i class="fi fi-rr-cross text-xl"></i>
      </button>
    </div>
    <div class="flex-1 overflow-auto p-6 bg-[#1e1e2e]">
      <div id="docViewerContent" class="flex items-center justify-center h-full">
        <p class="text-gray-400">Loading document...</p>
      </div>
    </div>
  </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
  var csrf = document.querySelector('meta[name="csrf-token"]').content;
  var bidsFilterForm = document.getElementById('bidsFilterForm');
  var bidsTableWrap = document.getElementById('bidsTableWrap');
  var bidSearchInput = document.getElementById('bidSearch');
  var bidStatusFilter = document.getElementById('bidStatusFilter');
  var bidSearchTimer = null;

  function openModal(id) { var el = document.getElementById('modal-' + id); if (!el) return; el.classList.add('modal-active'); document.body.style.overflow = 'hidden'; }
  function closeModal(id) { var el = document.getElementById('modal-' + id); if (!el) return; el.classList.remove('modal-active'); if (!document.querySelector('.modal-active')) document.body.style.overflow = ''; }

  ['view','edit','save-confirm','delete-confirm'].forEach(function(id) {
    var el = document.getElementById('modal-' + id);
    if (!el) return;
    el.addEventListener('click', function(e) { if (e.target === el) closeModal(id); });
  });
  document.addEventListener('keydown', function(e) { 
    if (e.key === 'Escape') {
      ['view','edit','save-confirm'].forEach(closeModal);
      closeDeleteModal();
      closeDeleteFinalConfirm();
    }
  });

  // Backdrop click for delete confirm modal
  if (deleteConfirmModal) {
    deleteConfirmModal.addEventListener('click', function(e) { if (e.target === deleteConfirmModal) closeDeleteModal(); });
  }

  // Backdrop click for delete final confirm modal
  if (deleteFinalConfirmModal) {
    deleteFinalConfirmModal.addEventListener('click', function(e) { if (e.target === deleteFinalConfirmModal) closeDeleteFinalConfirm(); });
  }

  var statusCfg = {
    submitted: {
      label: 'Submitted',
      cls: 'bg-blue-100 text-blue-700 border-blue-200',
      header: 'bg-gradient-to-r from-blue-600 to-cyan-600 border-b border-blue-700',
      subtitle: 'text-blue-100'
    },
    under_review: {
      label: 'Under Evaluation',
      cls: 'bg-amber-100 text-amber-700 border-amber-200',
      header: 'bg-gradient-to-r from-amber-500 to-orange-600 border-b border-amber-700',
      subtitle: 'text-amber-100'
    },
    accepted: {
      label: 'Approved',
      cls: 'bg-green-100 text-green-700 border-green-200',
      header: 'bg-gradient-to-r from-emerald-600 to-teal-600 border-b border-emerald-700',
      subtitle: 'text-emerald-100'
    },
    rejected: {
      label: 'Rejected',
      cls: 'bg-red-100 text-red-700 border-red-200',
      header: 'bg-gradient-to-r from-rose-600 to-red-600 border-b border-rose-700',
      subtitle: 'text-rose-100'
    },
    cancelled: {
      label: 'Cancelled',
      cls: 'bg-gray-100 text-gray-500 border-gray-200',
      header: 'bg-gradient-to-r from-slate-600 to-gray-700 border-b border-slate-700',
      subtitle: 'text-slate-100'
    },
  };
  function fmtCost(val) { var n = parseFloat(String(val).replace(/[^0-9.]/g, '')); return isNaN(n) ? '—' : '₱' + n.toLocaleString('en-PH', { minimumFractionDigits: 2 }); }
  function escHtml(str) { return String(str).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;').replace(/'/g,'&#039;'); }

  function syncFilterInputs(url) {
    var parsed = new URL(url, window.location.origin);
    if (bidSearchInput) bidSearchInput.value = parsed.searchParams.get('search') || '';
    if (bidStatusFilter) bidStatusFilter.value = parsed.searchParams.get('status') || '';
  }

  function buildFilterUrl() {
    var url = new URL(bidsFilterForm ? bidsFilterForm.action : window.location.href, window.location.origin);
    var params = new URLSearchParams(window.location.search);
    var searchValue = bidSearchInput ? bidSearchInput.value.trim() : '';
    var statusValue = bidStatusFilter ? bidStatusFilter.value : '';

    if (searchValue) params.set('search', searchValue); else params.delete('search');
    if (statusValue) params.set('status', statusValue); else params.delete('status');
    params.delete('page');

    url.search = params.toString();
    return url.toString();
  }

  function fetchBidsTable(url, shouldPushState) {
    if (!bidsTableWrap) return Promise.resolve();

    return fetch(url, {
      headers: {
        'X-Requested-With': 'XMLHttpRequest',
        'Accept': 'application/json'
      }
    })
    .then(function (response) {
      if (!response.ok) throw new Error('Failed to load bids.');
      return response.json();
    })
    .then(function (data) {
      if (!data.bids_html) throw new Error('Missing bids table payload.');
      bidsTableWrap.innerHTML = data.bids_html;
      if (shouldPushState !== false) window.history.pushState({}, '', url);
      syncFilterInputs(url);
    })
    .catch(function () {
      showToast('Failed to update bids.', 'error');
    });
  }

  if (bidsFilterForm) {
    bidsFilterForm.addEventListener('submit', function (e) {
      e.preventDefault();
      fetchBidsTable(buildFilterUrl());
    });
  }

  if (bidSearchInput) {
    bidSearchInput.addEventListener('input', function () {
      clearTimeout(bidSearchTimer);
      bidSearchTimer = setTimeout(function () {
        fetchBidsTable(buildFilterUrl());
      }, 300);
    });
  }

  if (bidStatusFilter) {
    bidStatusFilter.addEventListener('change', function () {
      fetchBidsTable(buildFilterUrl());
    });
  }

  window.addEventListener('popstate', function () {
    fetchBidsTable(window.location.href, false);
  });

  function openViewFromButton(btn) {
    var d = btn.dataset;
    document.getElementById('v-bid-id').textContent = '#' + d.bidId;
    var badge = document.getElementById('v-status-badge');
    var cfg = statusCfg[d.bidStatus] || {
      label: d.bidStatus,
      cls: 'bg-gray-100 text-gray-600 border-gray-200',
      header: 'bg-gradient-to-r from-slate-600 to-gray-700 border-b border-slate-700',
      subtitle: 'text-slate-100'
    };
    badge.textContent = cfg.label;
    badge.className = 'inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold border ' + cfg.cls;

    var header = document.getElementById('v-modal-header');
    if (header) {
      header.className = 'flex items-center justify-between px-4 py-3.5 text-white flex-shrink-0 ' + cfg.header;
    }

    var subtitle = document.getElementById('v-modal-subtitle');
    if (subtitle) {
      subtitle.className = 'text-[10px] mt-0.5 ' + cfg.subtitle;
    }

    var iconWrap = document.getElementById('v-modal-icon-wrap');
    if (iconWrap) {
      iconWrap.className = 'w-9 h-9 rounded-xl bg-white/20 border border-white/20 flex items-center justify-center shadow';
    }

    document.getElementById('v-company-name').textContent = d.companyName || '—';
    document.getElementById('v-company-email').textContent = d.companyEmail || '—';
    document.getElementById('v-pcab').textContent = d.pcab || 'N/A';
    document.getElementById('v-pcab-category').textContent = d.pcabCategory || 'N/A';
    document.getElementById('v-pcab-expiry').textContent = d.pcabExpiry || 'N/A';
    document.getElementById('v-bp-number').textContent = d.bpNumber || 'N/A';
    document.getElementById('v-bp-city').textContent = d.bpCity || 'N/A';
    document.getElementById('v-bp-expiry').textContent = d.bpExpiry || 'N/A';
    document.getElementById('v-tin').textContent = d.tin || 'N/A';
    document.getElementById('v-project-title').textContent = d.projectTitle || '—';
    document.getElementById('v-proposed-cost').textContent = fmtCost(d.proposedCost);
    document.getElementById('v-timeline').textContent = d.timeline ? d.timeline + ' month(s)' : '—';
    document.getElementById('v-submitted-at').textContent = d.submittedAt || '—';
    document.getElementById('v-decision-date').textContent = d.decisionDate || '—';
    document.getElementById('v-notes').value = d.notes || '';
    var rb = document.getElementById('v-reason-block');
    if (d.bidStatus === 'accepted' || d.bidStatus === 'rejected') {
      rb.classList.remove('hidden');
      var iw = document.getElementById('v-reason-icon-wrap'), ic = document.getElementById('v-reason-icon'), lbl = document.getElementById('v-reason-label'), ta = document.getElementById('v-reason-text');
      if (d.bidStatus === 'accepted') { rb.className='rounded-xl border border-green-200 bg-green-50 p-3.5'; iw.className='flex items-center justify-center w-8 h-8 rounded-lg bg-green-500'; ic.className='fi fi-sr-check-circle text-white text-sm'; lbl.textContent='Approval Remarks'; ta.className='w-full text-[12px] border border-green-200 rounded-lg px-3 py-2 bg-white resize-none focus:outline-none'; }
      else { rb.className='rounded-xl border border-red-200 bg-red-50 p-3.5'; iw.className='flex items-center justify-center w-8 h-8 rounded-lg bg-red-500'; ic.className='fi fi-sr-cross-circle text-white text-sm'; lbl.textContent='Reason for Rejection'; ta.className='w-full text-[12px] border border-red-200 rounded-lg px-3 py-2 bg-white resize-none focus:outline-none'; }
      ta.value = d.reason || 'No reason provided.';
    } else {
      rb.classList.add('hidden');
    }
    loadBidFiles(d.bidId);
    openModal('view');
  }
  document.getElementById('closeViewModal').addEventListener('click', function() { closeModal('view'); });

  function loadBidFiles(bidId) {
    var container = document.getElementById('v-files-container');
    container.innerHTML = '<p class="py-4 text-sm text-gray-400 animate-pulse text-center">Loading files…</p>';
    fetch('/admin/global-management/bid-management/files/' + bidId, { headers: { 'X-CSRF-TOKEN': csrf, 'Accept': 'application/json' } })
    .then(function(r) { return r.json(); })
    .then(function(files) {
      if (!Array.isArray(files) || !files.length) { container.innerHTML = '<p class="py-6 text-sm text-gray-400 text-center">No files attached.</p>'; return; }
      var rows = files.map(function(f) {
        var ext = (f.file_name||'').split('.').pop().toUpperCase();
        var ec = {PDF:'bg-red-100 text-red-700',JPG:'bg-yellow-100 text-yellow-700',JPEG:'bg-yellow-100 text-yellow-700',PNG:'bg-blue-100 text-blue-700'}[ext]||'bg-gray-100 text-gray-600';
        return '<tr class="hover:bg-gray-50"><td class="px-4 py-3 w-[35%]"><div class="flex items-center gap-2 min-w-0"><span class="inline-flex items-center justify-center w-8 h-8 rounded text-xs font-bold flex-shrink-0 '+ec+'">'+ext+'</span><span class="text-sm text-gray-800 break-all overflow-hidden">'+escHtml(f.file_name)+'</span></div></td><td class="px-4 py-3 text-sm text-gray-500 w-[30%] break-all overflow-hidden">'+escHtml(f.description||'—')+'</td><td class="px-4 py-3 text-sm text-gray-500 whitespace-nowrap w-[20%] overflow-hidden">'+escHtml(f.uploaded_at)+'</td><td class="px-4 py-3 w-[15%] text-center"><a href="#" class="open-doc-btn inline-flex items-center justify-center w-8 h-8 rounded-full bg-blue-50 hover:bg-blue-100 transition" data-doc-src="/storage/'+escHtml(f.file_path)+'" data-doc-title="'+escHtml(f.file_name)+'"><i class="fi fi-rr-eye text-blue-600"></i></a></td></tr>';
      }).join('');
      container.innerHTML = '<table class="w-full text-sm table-fixed"><thead><tr class="bg-gray-50 text-xs text-gray-500 uppercase border-b border-gray-200"><th class="px-4 py-2 text-left w-[35%] break-words overflow-hidden">File Name</th><th class="px-4 py-2 text-left w-[30%] break-words overflow-hidden">Description</th><th class="px-4 py-2 text-left w-[20%] overflow-hidden">Uploaded</th><th class="px-4 py-2 text-center w-[15%]">Action</th></tr></thead><tbody class="divide-y divide-gray-100">'+rows+'</tbody></table>';
    })
    .catch(function() { container.innerHTML = '<p class="py-4 text-sm text-red-400 text-center">Could not load files.</p>'; });
  }

  // EDIT
  var currentEditBidId = null;
  var initialEditValues = {};

  function showEditBidErrors(errors) {
    var errorAlert = document.getElementById('editBidErrorAlert');
    var errorList = document.getElementById('editBidErrorList');
    
    errorList.innerHTML = '';
    errors.forEach(function(error) {
      var li = document.createElement('li');
      li.textContent = error;
      errorList.appendChild(li);
    });
    
    errorAlert.classList.remove('hidden');
  }

  function clearEditBidErrors() {
    var errorAlert = document.getElementById('editBidErrorAlert');
    errorAlert.classList.add('hidden');
  }

  function openEditFromButton(btn) {
    var d = btn.dataset;
    currentEditBidId = d.bidId;
    clearEditBidErrors();
    document.getElementById('e-bid-id').textContent = '#' + d.bidId;
    document.getElementById('e-company-name').value = d.companyName || '';
    document.getElementById('e-company-email').value = d.companyEmail || '';
    document.getElementById('e-project-title').value = d.projectTitle || '';
    document.getElementById('e-timeline').value = d.timeline ? d.timeline + ' month(s)' : '';
    var costValue = d.proposedCost ? parseFloat(d.proposedCost).toLocaleString('en-PH', { minimumFractionDigits: 2 }) : '';
    document.getElementById('e-proposed-cost').value = costValue;
    document.getElementById('e-status').value = d.bidStatus || 'submitted';
    document.getElementById('e-notes').value = d.notes || '';
    
    // Store initial values for change detection
    initialEditValues = {
      cost: costValue,
      status: document.getElementById('e-status').value,
      notes: document.getElementById('e-notes').value
    };
    
    openModal('edit');
  }

  function hasEditChanges() {
    var currentCost = document.getElementById('e-proposed-cost').value;
    var currentStatus = document.getElementById('e-status').value;
    var currentNotes = document.getElementById('e-notes').value;
    
    return currentCost !== initialEditValues.cost || 
           currentStatus !== initialEditValues.status || 
           currentNotes !== initialEditValues.notes;
  }

  document.getElementById('closeEditModal').addEventListener('click', function() { closeModal('edit'); });
  document.getElementById('cancelEditBtn').addEventListener('click', function() { closeModal('edit'); });
  document.getElementById('saveChangesBtn').addEventListener('click', function() { 
    clearEditBidErrors();
    if (!hasEditChanges()) {
      showEditBidErrors(['No changes detected. Please modify a field before saving.']);
      return;
    }
    closeModal('edit'); 
    openModal('save-confirm'); 
  });
  document.getElementById('cancelSaveBtn').addEventListener('click', function() { closeModal('save-confirm'); openModal('edit'); });
  document.getElementById('confirmSaveBtn').addEventListener('click', function() {
    var payload = { bid_status: document.getElementById('e-status').value, proposed_cost: document.getElementById('e-proposed-cost').value.replace(/[^0-9.]/g,''), contractor_notes: document.getElementById('e-notes').value };
    fetch('/admin/global-management/bid-management/' + currentEditBidId, { method:'PUT', headers:{'Content-Type':'application/json','X-CSRF-TOKEN':csrf,'Accept':'application/json'}, body:JSON.stringify(payload) })
    .then(function(r){return r.json();}).then(function(data){ closeModal('save-confirm'); if(data.success){showToast('Bid updated successfully.','success');setTimeout(function(){location.reload();},1200);}else{showToast(data.message||'Failed to update bid.','error');openModal('edit');}})
    .catch(function(){closeModal('save-confirm');showToast('Server error. Please try again.','error');openModal('edit');});
  });

  // DELETE
  var currentDeleteBidId = null;
  var deleteConfirmModal = document.getElementById('modal-delete-confirm');
  var deleteConfirmContent = deleteConfirmModal ? deleteConfirmModal.querySelector('.modal-content') : null;
  var deleteFinalConfirmModal = document.getElementById('modal-delete-final-confirm');

  function openDeleteFromButton(btn) {
    var d = btn.dataset;
    currentDeleteBidId = d.bidId;
    document.getElementById('d2-bid-id').textContent = '#' + d.bidId;
    document.getElementById('d2-project-title').textContent = d.projectTitle || '—';
    document.getElementById('d2-company-name').textContent = d.companyName || '—';
    
    if (deleteConfirmModal && deleteConfirmContent) {
      deleteConfirmModal.classList.remove('hidden');
      deleteConfirmModal.classList.add('flex');
      deleteConfirmModal.style.display = 'flex';
      document.body.style.overflow = 'hidden';
      setTimeout(function() {
        deleteConfirmContent.classList.remove('scale-95', 'opacity-0');
        deleteConfirmContent.classList.add('scale-100', 'opacity-100');
      }, 10);
    }
  }

  function closeDeleteModal() {
    if (deleteConfirmModal && deleteConfirmContent) {
      deleteConfirmContent.classList.remove('scale-100', 'opacity-100');
      deleteConfirmContent.classList.add('scale-95', 'opacity-0');
      setTimeout(function() {
        deleteConfirmModal.classList.add('hidden');
        deleteConfirmModal.classList.remove('flex');
        deleteConfirmModal.style.display = 'none';
        if (!document.querySelector('.modal-active:not(#modal-delete-confirm)')) {
          document.body.style.overflow = '';
        }
      }, 300);
    }
  }

  function openDeleteFinalConfirm() {
    closeDeleteModal();
    if (deleteFinalConfirmModal) {
      var content = deleteFinalConfirmModal.querySelector('.modal-content-final');
      deleteFinalConfirmModal.classList.add('modal-active');
      document.body.style.overflow = 'hidden';
      if (content) {
        setTimeout(function() {
          content.classList.remove('scale-95', 'opacity-0');
          content.classList.add('scale-100', 'opacity-100');
        }, 10);
      }
    }
  }

  function closeDeleteFinalConfirm() {
    if (deleteFinalConfirmModal) {
      var content = deleteFinalConfirmModal.querySelector('.modal-content-final');
      if (content) {
        content.classList.remove('scale-100', 'opacity-100');
        content.classList.add('scale-95', 'opacity-0');
        setTimeout(function() {
          deleteFinalConfirmModal.classList.remove('modal-active');
          if (!document.querySelector('.modal-active')) {
            document.body.style.overflow = '';
          }
        }, 300);
      } else {
        deleteFinalConfirmModal.classList.remove('modal-active');
      }
    }
  }

  document.addEventListener('click', function (e) {
    var paginationLink = e.target.closest('.bid-page-link');
    if (paginationLink) {
      e.preventDefault();
      fetchBidsTable(paginationLink.href);
      return;
    }

    var clearFiltersLink = e.target.closest('.clear-bids-filters');
    if (clearFiltersLink) {
      e.preventDefault();
      fetchBidsTable(clearFiltersLink.href);
      return;
    }

    var viewBtn = e.target.closest('.btn-view-bid');
    if (viewBtn) {
      e.preventDefault();
      openViewFromButton(viewBtn);
      return;
    }

    var editBtn = e.target.closest('.btn-edit-bid');
    if (editBtn) {
      e.preventDefault();
      openEditFromButton(editBtn);
      return;
    }

    var deleteBtn = e.target.closest('.btn-delete-bid');
    if (deleteBtn) {
      e.preventDefault();
      openDeleteFromButton(deleteBtn);
    }
  });

  document.getElementById('closeDeleteConfirmBtn').addEventListener('click', function() { closeDeleteModal(); });
  document.getElementById('cancelDeleteBtn').addEventListener('click', function() { closeDeleteModal(); });
  document.getElementById('confirmDeleteBtn').addEventListener('click', function() { 
    openDeleteFinalConfirm(); 
  });
  
  document.getElementById('cancelDeleteFinalBtn').addEventListener('click', function() { 
    closeDeleteFinalConfirm(); 
    if (deleteConfirmModal && deleteConfirmContent) {
      deleteConfirmModal.classList.remove('hidden');
      deleteConfirmModal.classList.add('flex');
      deleteConfirmModal.style.display = 'flex';
      document.body.style.overflow = 'hidden';
      setTimeout(function() {
        deleteConfirmContent.classList.remove('scale-95', 'opacity-0');
        deleteConfirmContent.classList.add('scale-100', 'opacity-100');
      }, 310);
    }
  });
  document.getElementById('confirmDeleteFinalBtn').addEventListener('click', function() {
    var content = deleteFinalConfirmModal ? deleteFinalConfirmModal.querySelector('.modal-content-final') : null;
    if (content) {
      content.classList.remove('scale-100', 'opacity-100');
      content.classList.add('scale-95', 'opacity-0');
    }
    setTimeout(function() {
      fetch('/admin/global-management/bid-management/' + currentDeleteBidId, { method:'DELETE', headers:{'X-CSRF-TOKEN':csrf,'Accept':'application/json'} })
      .then(function(r){return r.json();}).then(function(data){closeDeleteFinalConfirm();if(data.success){showToast('Bid deleted.','success');setTimeout(function(){location.reload();},1200);}else{showToast(data.message||'Failed to delete bid.','error');}})
      .catch(function(){closeDeleteFinalConfirm();showToast('Server error. Please try again.','error');});
    }, 150);
  });

  function showToast(message, type) {
    var notification = document.createElement('div');
    notification.className = 'fixed top-20 right-4 z-[60] max-w-[280px] px-3 py-2 rounded-md shadow-lg transform transition-all duration-500 translate-x-full ' + (type === 'success' ? 'bg-green-500' : 'bg-red-500') + ' text-white text-xs font-semibold leading-tight flex items-center gap-1.5';
    notification.innerHTML = '<i class="fi fi-rr-' + (type === 'success' ? 'check-circle' : 'cross-circle') + ' text-base"></i><span>' + message + '</span>';
    document.body.appendChild(notification);
    
    setTimeout(function() {
      notification.style.transform = 'translateX(0)';
    }, 10);
    
    setTimeout(function() {
      notification.style.transform = 'translateX(150%)';
      setTimeout(function() { notification.remove(); }, 500);
    }, 3000);
  }

  // ══════════════════ UNIVERSAL FILE VIEWER (UFV) - Dark Theme ══════════════════
  document.addEventListener('click', function(e) {
    const trigger = e.target.closest('.open-doc-btn');
    if (!trigger) return;
    
    e.preventDefault();
    e.stopPropagation();
    
    const docSrc = trigger.getAttribute('data-doc-src');
    const docTitle = trigger.getAttribute('data-doc-title') || 'Document';
    
    if (!docSrc) return;
    
    const modal = document.getElementById('documentViewerModal');
    const titleEl = document.getElementById('docViewerTitle');
    const contentEl = document.getElementById('docViewerContent');
    
    if (!modal || !titleEl || !contentEl) return;
    
    titleEl.textContent = docTitle;
    
    const ext = docSrc.split('.').pop().toLowerCase();
    let content = '';
    
    if (ext === 'pdf') {
      content = '<iframe src="' + docSrc + '" class="w-full h-full min-h-[70vh] rounded-lg border-0"></iframe>';
    } else if (['jpg', 'jpeg', 'png', 'gif', 'webp', 'svg'].includes(ext)) {
      content = '<img src="' + docSrc + '" alt="' + docTitle + '" class="max-w-full h-auto rounded-lg shadow-lg mx-auto">';
    } else {
      content = '<div class="text-center text-gray-400"><p class="mb-4">Preview not available for this file type.</p><a href="' + docSrc + '" target="_blank" class="inline-flex items-center gap-2 px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition"><i class="fi fi-rr-download"></i> Download File</a></div>';
    }
    
    contentEl.innerHTML = content;
    modal.classList.remove('hidden');
    modal.classList.add('flex');
  });
  
  const closeDocViewerBtn = document.getElementById('closeDocViewer');
  if (closeDocViewerBtn) {
    closeDocViewerBtn.addEventListener('click', function() {
      const modal = document.getElementById('documentViewerModal');
      if (modal) {
        modal.classList.add('hidden');
        modal.classList.remove('flex');
      }
    });
  }
  
  const docViewerModal = document.getElementById('documentViewerModal');
  if (docViewerModal) {
    docViewerModal.addEventListener('click', function(e) {
      if (e.target === docViewerModal) {
        docViewerModal.classList.add('hidden');
        docViewerModal.classList.remove('flex');
      }
    });
  }
});
</script>
<script src="{{ asset('js/admin/globalManagement/bidManagement.js') }}" defer></script>
</body>
</html>