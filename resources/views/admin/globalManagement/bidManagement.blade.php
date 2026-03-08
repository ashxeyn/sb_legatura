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
  <style>.modal-active { display: flex !important; }</style>
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

      <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">

        <div class="stat-card bg-white rounded-xl shadow-md p-6 hover:shadow-xl transition-all duration-300 border-l-4 border-blue-500">
          <div class="flex justify-between items-start mb-4">
            <div><p class="text-gray-500 text-sm font-medium">Total Bids</p><h2 class="text-4xl font-bold text-gray-800 mt-1">{{ number_format($totalBids) }}</h2></div>
            <div class="bg-blue-100 p-3 rounded-lg"><i class="fi fi-sr-inbox-in text-blue-600 text-2xl"></i></div>
          </div>
          <p class="text-xs text-gray-400">All time</p>
          <div class="mt-3 h-1 bg-gray-200 rounded-full"><div class="h-full bg-blue-500 rounded-full" style="width:100%"></div></div>
        </div>

        <div class="stat-card bg-white rounded-xl shadow-md p-6 hover:shadow-xl transition-all duration-300 border-l-4 border-orange-500">
          <div class="flex justify-between items-start mb-4">
            <div><p class="text-gray-500 text-sm font-medium">Pending Reviews</p><h2 class="text-4xl font-bold text-gray-800 mt-1">{{ number_format($pendingBids) }}</h2></div>
            <div class="bg-orange-100 p-3 rounded-lg"><i class="fi fi-sr-hourglass-end text-orange-600 text-2xl"></i></div>
          </div>
          <p class="text-xs text-gray-400">Submitted + Under Review</p>
          <div class="mt-3 h-1 bg-gray-200 rounded-full"><div class="h-full bg-orange-500 rounded-full animate-pulse" style="width:{{ $pendingPct }}%"></div></div>
        </div>

        <div class="stat-card bg-white rounded-xl shadow-md p-6 hover:shadow-xl transition-all duration-300 border-l-4 border-green-500">
          <div class="flex justify-between items-start mb-4">
            <div><p class="text-gray-500 text-sm font-medium">Approved Bids</p><h2 class="text-4xl font-bold text-gray-800 mt-1">{{ number_format($approvedBids) }}</h2></div>
            <div class="bg-green-100 p-3 rounded-lg"><i class="fi fi-sr-check-circle text-green-600 text-2xl"></i></div>
          </div>
          <p class="text-xs text-gray-400">Accepted bids</p>
          <div class="mt-3 h-1 bg-gray-200 rounded-full"><div class="h-full bg-green-500 rounded-full" style="width:{{ $approvedPct }}%"></div></div>
        </div>

        <div class="stat-card bg-white rounded-xl shadow-md p-6 hover:shadow-xl transition-all duration-300 border-l-4 border-red-500">
          <div class="flex justify-between items-start mb-4">
            <div><p class="text-gray-500 text-sm font-medium">Rejected Bids</p><h2 class="text-4xl font-bold text-gray-800 mt-1">{{ number_format($rejectedBids) }}</h2></div>
            <div class="bg-red-100 p-3 rounded-lg"><i class="fi fi-sr-cross-circle text-red-600 text-2xl"></i></div>
          </div>
          <p class="text-xs text-gray-400">Rejected bids</p>
          <div class="mt-3 h-1 bg-gray-200 rounded-full"><div class="h-full bg-red-500 rounded-full" style="width:{{ $rejectedPct }}%"></div></div>
        </div>

      </div>

      {{-- ══ FILTER BAR (below stats, above table — same as proofOfPayments) ══ --}}
      @php
        $filterQuery = http_build_query(request()->only(['search', 'status']));
        $sep = $filterQuery ? '&' : '';
      @endphp

      <form method="GET" action="{{ route('admin.globalManagement.bidManagement') }}">
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-4 mb-6 flex flex-wrap items-center gap-3">
          <div class="flex items-center gap-2 px-3 py-2 border border-gray-300 rounded-lg text-sm text-gray-700">
            <i class="fi fi-rr-filter text-gray-500"></i>
            <span>Filter By</span>
          </div>

          <div class="relative">
            <input name="search" type="text"
              placeholder="Search project or contractor…"
              value="{{ request('search') }}"
              class="px-4 py-2 pr-10 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-indigo-400 focus:outline-none w-64">
            <i class="fi fi-rr-search absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 pointer-events-none"></i>
          </div>

          <select name="status"
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
               class="px-3 py-2 border border-gray-300 text-gray-600 rounded-lg text-sm hover:bg-gray-50 transition">
              Clear
            </a>
          @endif
        </div>
      </form>

      {{-- ══ TABLE CARD ══ --}}
      <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">

        @if(request('search') || request('status'))
          <div class="px-6 py-3 bg-indigo-50 border-b border-indigo-100 flex items-center gap-2 text-sm text-indigo-700">
            <i class="fi fi-rr-filter"></i>
            <span>
              Showing <strong>{{ $bids->total() }}</strong> result(s)
              @if(request('search')) for "<strong>{{ request('search') }}</strong>"@endif
              @if(request('status')) with status "<strong>{{ request('status') }}</strong>"@endif
            </span>
            <a href="{{ route('admin.globalManagement.bidManagement') }}"
               class="ml-auto text-indigo-600 hover:underline text-xs font-semibold">Clear filters</a>
          </div>
        @endif

        <div class="overflow-x-auto">
          <table class="w-full">
            <thead>
              <tr class="bg-gradient-to-r from-indigo-50 to-blue-50 border-b border-gray-200">
                <th class="px-6 py-3 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">Bid ID</th>
                <th class="px-6 py-3 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">Project Title</th>
                <th class="px-6 py-3 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">Contractor Company</th>
                <th class="px-6 py-3 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">Bid Amount</th>
                <th class="px-6 py-3 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">Submitted</th>
                <th class="px-6 py-3 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">Status</th>
                <th class="px-6 py-3 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">Action</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-gray-200" id="bidsTable">

              @forelse ($bids as $bid)
                @php
                  $statusMap = [
                    'submitted'    => ['label' => 'Submitted',        'class' => 'bg-blue-100 text-blue-700 border-blue-200'],
                    'under_review' => ['label' => 'Under Evaluation', 'class' => 'bg-amber-100 text-amber-700 border-amber-200'],
                    'accepted'     => ['label' => 'Approved',         'class' => 'bg-green-100 text-green-700 border-green-200'],
                    'rejected'     => ['label' => 'Rejected',         'class' => 'bg-red-100 text-red-700 border-red-200'],
                    'cancelled'    => ['label' => 'Cancelled',        'class' => 'bg-gray-100 text-gray-500 border-gray-200'],
                  ];
                  $s = $statusMap[$bid->bid_status] ?? ['label' => ucfirst($bid->bid_status), 'class' => 'bg-gray-100 text-gray-600 border-gray-200'];
                  $words    = explode(' ', trim($bid->company_name ?? 'UN'));
                  $initials = strtoupper(substr($words[0],0,1).(isset($words[1]) ? substr($words[1],0,1) : substr($words[0],1,1)));
                  $gradients = ['from-blue-500 to-indigo-600','from-emerald-500 to-teal-600','from-fuchsia-500 to-purple-600','from-orange-500 to-rose-500','from-sky-500 to-blue-600','from-cyan-500 to-teal-500','from-gray-700 to-gray-900'];
                  $grad = $gradients[$bid->bid_id % count($gradients)];
                @endphp

                <tr class="hover:bg-indigo-50/60 transition-colors">

                  <td class="px-6 py-4 whitespace-nowrap text-gray-700 font-medium text-sm">#{{ $bid->bid_id }}</td>

                  <td class="px-6 py-4 text-gray-700 text-sm max-w-xs">
                    <span class="block truncate" title="{{ $bid->project_title }}">{{ $bid->project_title }}</span>
                  </td>

                  <td class="px-6 py-4">
                    <div class="flex items-center gap-3">
                      <div class="w-8 h-8 rounded-full bg-gradient-to-br {{ $grad }} flex items-center justify-center text-white text-xs font-bold shadow flex-shrink-0">{{ $initials }}</div>
                      <div>
                        <div class="font-semibold text-gray-800 text-sm leading-5">{{ $bid->company_name }}</div>
                        <div class="text-gray-500 text-xs">{{ $bid->company_email }}</div>
                      </div>
                    </div>
                  </td>

                  <td class="px-6 py-4 whitespace-nowrap text-gray-700 text-sm font-semibold">₱{{ number_format($bid->bid_amount, 2) }}</td>

                  <td class="px-6 py-4 whitespace-nowrap text-gray-600 text-xs">{{ \Carbon\Carbon::parse($bid->bid_date)->format('M d, Y') }}</td>

                  <td class="px-6 py-4 whitespace-nowrap">
                    <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold border {{ $s['class'] }}">{{ $s['label'] }}</span>
                  </td>

                  <td class="px-6 py-4 whitespace-nowrap">
                    <div class="flex items-center gap-1.5">

                      <button class="action-btn action-btn--view rounded-2xl btn-view-bid" title="View"
                        data-bid-id="{{ $bid->bid_id }}"
                        data-bid-status="{{ $bid->bid_status }}"
                        data-company-name="{{ e($bid->company_name) }}"
                        data-company-email="{{ e($bid->company_email) }}"
                        data-project-title="{{ e($bid->project_title) }}"
                        data-proposed-cost="{{ $bid->bid_amount }}"
                        data-timeline="{{ $bid->estimated_timeline }}"
                        data-submitted-at="{{ \Carbon\Carbon::parse($bid->bid_date)->format('F d, Y') }}"
                        data-decision-date="{{ $bid->decision_date ? \Carbon\Carbon::parse($bid->decision_date)->format('F d, Y') : '' }}"
                        data-notes="{{ e($bid->contractor_notes ?? '') }}"
                        data-reason="{{ e($bid->reason ?? '') }}"
                        data-pcab="{{ e($bid->picab_number ?? 'N/A') }}"
                        data-pcab-category="{{ e($bid->picab_category ?? 'N/A') }}"
                        data-pcab-expiry="{{ $bid->picab_expiration_date ?? 'N/A' }}"
                        data-bp-number="{{ e($bid->business_permit_number ?? 'N/A') }}"
                        data-bp-city="{{ e($bid->business_permit_city ?? 'N/A') }}"
                        data-bp-expiry="{{ $bid->business_permit_expiration ?? 'N/A' }}"
                        data-tin="{{ e($bid->tin_business_reg_number ?? 'N/A') }}"
                      ><i class="fi fi-rr-eye"></i></button>

                      <button class="action-btn action-btn--edit rounded-2xl btn-edit-bid" title="Edit"
                        data-bid-id="{{ $bid->bid_id }}"
                        data-bid-status="{{ $bid->bid_status }}"
                        data-company-name="{{ e($bid->company_name) }}"
                        data-company-email="{{ e($bid->company_email) }}"
                        data-project-title="{{ e($bid->project_title) }}"
                        data-proposed-cost="{{ $bid->bid_amount }}"
                        data-timeline="{{ $bid->estimated_timeline }}"
                        data-notes="{{ e($bid->contractor_notes ?? '') }}"
                      ><i class="fi fi-rr-edit"></i></button>

                      <button class="action-btn action-btn--delete rounded-2xl btn-delete-bid" title="Delete"
                        data-bid-id="{{ $bid->bid_id }}"
                        data-project-title="{{ e($bid->project_title) }}"
                        data-company-name="{{ e($bid->company_name) }}"
                      ><i class="fi fi-rr-trash"></i></button>

                    </div>
                  </td>
                </tr>

              @empty
                <tr>
                  <td colspan="7" class="px-6 py-16 text-center text-gray-400">
                    <i class="fi fi-rr-inbox text-4xl mb-3 block"></i>
                    <p class="text-lg font-medium text-gray-500">No bids found</p>
                    @if(request('search') || request('status'))
                      <p class="text-sm mt-1">Try adjusting your search or filter criteria.</p>
                      <a href="{{ route('admin.globalManagement.bidManagement') }}" class="mt-2 inline-block text-indigo-600 hover:underline text-sm">Clear filters</a>
                    @endif
                  </td>
                </tr>
              @endforelse

            </tbody>
          </table>
        </div>

        {{-- ══ PAGINATION (identical structure to proofOfPayments) ══ --}}
        @if ($bids->hasPages())
          <div class="px-6 py-4 border-t border-gray-200 flex items-center justify-between flex-wrap gap-3">
            <p class="text-sm text-gray-500">
              Showing <strong>{{ $bids->firstItem() }}</strong>–<strong>{{ $bids->lastItem() }}</strong>
              of <strong>{{ $bids->total() }}</strong> bids
            </p>
            <div class="flex items-center gap-1">

              @if ($bids->onFirstPage())
                <span class="px-3 py-1.5 rounded-lg text-sm text-gray-400 border border-gray-200 cursor-not-allowed">‹ Prev</span>
              @else
                <a href="{{ $bids->previousPageUrl() }}{{ $sep }}{{ $filterQuery }}"
                   class="px-3 py-1.5 rounded-lg text-sm border border-gray-200 hover:bg-gray-50 transition">‹ Prev</a>
              @endif

              @foreach ($bids->getUrlRange(max(1, $bids->currentPage()-2), min($bids->lastPage(), $bids->currentPage()+2)) as $page => $url)
                @if ($page == $bids->currentPage())
                  <span class="px-3 py-1.5 rounded-lg text-sm bg-indigo-600 text-white font-semibold">{{ $page }}</span>
                @else
                  <a href="{{ $url }}{{ $sep }}{{ $filterQuery }}"
                     class="px-3 py-1.5 rounded-lg text-sm border border-gray-200 hover:bg-gray-50 transition">{{ $page }}</a>
                @endif
              @endforeach

              @if ($bids->hasMorePages())
                <a href="{{ $bids->nextPageUrl() }}{{ $sep }}{{ $filterQuery }}"
                   class="px-3 py-1.5 rounded-lg text-sm border border-gray-200 hover:bg-gray-50 transition">Next ›</a>
              @else
                <span class="px-3 py-1.5 rounded-lg text-sm text-gray-400 border border-gray-200 cursor-not-allowed">Next ›</span>
              @endif

            </div>
          </div>
        @endif

      </div>{{-- /table card --}}
    </div>{{-- /p-8 --}}
  </main>
</div>

{{-- ══════════════════ VIEW MODAL ══════════════════ --}}
<div id="modal-view" class="fixed inset-0 bg-black/50 backdrop-blur-sm items-center justify-center z-[100] p-4" style="display:none;">
  <div class="bg-white rounded-2xl shadow-2xl w-full max-w-6xl max-h-[92vh] overflow-hidden flex flex-col">
    <div class="flex items-start justify-between px-6 sm:px-8 py-5 border-b border-gray-200 bg-gradient-to-r from-indigo-50 to-blue-50 flex-shrink-0">
      <div class="flex items-center gap-4">
        <div class="flex items-center justify-center w-12 h-12 rounded-xl bg-gradient-to-br from-indigo-500 to-blue-600 shadow-lg"><i class="fi fi-ss-document text-white text-xl"></i></div>
        <div>
          <h2 class="text-xl font-bold text-gray-800">Bid Details</h2>
          <p class="text-xs text-gray-500 mt-0.5">Bid <span id="v-bid-id" class="font-semibold text-gray-700">—</span> &nbsp;·&nbsp; <span id="v-status-badge" class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold border">—</span></p>
        </div>
      </div>
      <button id="closeViewModal" class="p-2 rounded-xl text-gray-400 hover:text-gray-600 hover:bg-white transition-all"><i class="fi fi-rr-cross text-lg"></i></button>
    </div>
    <div class="overflow-y-auto flex-1 px-6 sm:px-8 py-6 space-y-6">
      <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <div class="rounded-xl border border-indigo-200 bg-indigo-50/50 p-5">
          <div class="flex items-center gap-2 mb-4"><i class="fi fi-ss-user text-indigo-600"></i><h3 class="font-bold text-gray-800">Bidder Information</h3></div>
          <dl class="space-y-2.5 text-sm">
            <div class="flex justify-between gap-4"><dt class="text-gray-500 shrink-0">Company Name</dt><dd id="v-company-name" class="font-semibold text-gray-800 text-right">—</dd></div>
            <div class="flex justify-between gap-4"><dt class="text-gray-500 shrink-0">Email Address</dt><dd id="v-company-email" class="font-medium text-gray-800 text-right break-all">—</dd></div>
            <div class="border-t border-indigo-100 pt-2.5">
              <p class="text-xs font-semibold text-gray-500 uppercase tracking-wide mb-2">Licenses &amp; Registrations</p>
              <div class="space-y-1.5">
                <div class="flex justify-between gap-4"><dt class="text-gray-500 shrink-0">PCAB No.</dt><dd id="v-pcab" class="font-medium text-gray-800 text-right">—</dd></div>
                <div class="flex justify-between gap-4"><dt class="text-gray-500 shrink-0">PCAB Category</dt><dd id="v-pcab-category" class="font-medium text-gray-800 text-right">—</dd></div>
                <div class="flex justify-between gap-4"><dt class="text-gray-500 shrink-0">PCAB Expiry</dt><dd id="v-pcab-expiry" class="font-medium text-gray-800 text-right">—</dd></div>
                <div class="flex justify-between gap-4"><dt class="text-gray-500 shrink-0">Business Permit No.</dt><dd id="v-bp-number" class="font-medium text-gray-800 text-right">—</dd></div>
                <div class="flex justify-between gap-4"><dt class="text-gray-500 shrink-0">Permit City</dt><dd id="v-bp-city" class="font-medium text-gray-800 text-right">—</dd></div>
                <div class="flex justify-between gap-4"><dt class="text-gray-500 shrink-0">Permit Expiry</dt><dd id="v-bp-expiry" class="font-medium text-gray-800 text-right">—</dd></div>
                <div class="flex justify-between gap-4"><dt class="text-gray-500 shrink-0">TIN / Business Reg.</dt><dd id="v-tin" class="font-medium text-gray-800 text-right">—</dd></div>
              </div>
            </div>
          </dl>
        </div>
        <div class="rounded-xl border border-purple-200 bg-purple-50/50 p-5">
          <div class="flex items-center gap-2 mb-4"><i class="fi fi-ss-building text-purple-600"></i><h3 class="font-bold text-gray-800">Project &amp; Bid Details</h3></div>
          <dl class="space-y-2.5 text-sm">
            <div class="flex justify-between gap-4"><dt class="text-gray-500 shrink-0">Project Title</dt><dd id="v-project-title" class="font-semibold text-gray-800 text-right">—</dd></div>
            <div class="flex justify-between gap-4"><dt class="text-gray-500 shrink-0">Proposed Cost</dt><dd id="v-proposed-cost" class="font-bold text-green-700 text-right">—</dd></div>
            <div class="flex justify-between gap-4"><dt class="text-gray-500 shrink-0">Estimated Timeline</dt><dd id="v-timeline" class="font-medium text-gray-800 text-right">—</dd></div>
            <div class="flex justify-between gap-4"><dt class="text-gray-500 shrink-0">Submitted</dt><dd id="v-submitted-at" class="font-medium text-gray-800 text-right">—</dd></div>
            <div class="flex justify-between gap-4"><dt class="text-gray-500 shrink-0">Decision Date</dt><dd id="v-decision-date" class="font-medium text-gray-800 text-right">—</dd></div>
          </dl>
          <div class="mt-4">
            <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wide mb-2">Contractor Notes</label>
            <textarea id="v-notes" rows="5" readonly class="w-full text-sm border border-purple-200 rounded-lg px-3 py-2.5 bg-white resize-none focus:outline-none"></textarea>
          </div>
        </div>
      </div>
      <div id="v-reason-block" class="hidden rounded-xl border p-5">
        <div class="flex items-center gap-2 mb-3">
          <div id="v-reason-icon-wrap" class="flex items-center justify-center w-9 h-9 rounded-lg"><i id="v-reason-icon" class="text-white text-base"></i></div>
          <h3 id="v-reason-label" class="font-bold text-gray-800">Remarks</h3>
        </div>
        <textarea id="v-reason-text" rows="4" readonly class="w-full text-sm border rounded-lg px-3 py-2.5 bg-white resize-none focus:outline-none"></textarea>
      </div>
      <div>
        <div class="flex items-center gap-2 mb-3"><i class="fi fi-ss-folder text-blue-600"></i><h3 class="font-bold text-gray-800">Supporting Files</h3></div>
        <div class="rounded-xl border border-gray-200 overflow-hidden">
          <div id="v-files-container" class="p-6 text-center text-sm text-gray-400">Loading files…</div>
        </div>
      </div>
    </div>
  </div>
</div>

{{-- ══════════════════ EDIT MODAL ══════════════════ --}}
<div id="modal-edit" class="fixed inset-0 bg-black/50 backdrop-blur-sm items-center justify-center z-[100] p-4" style="display:none;">
  <div class="bg-white rounded-2xl shadow-2xl w-full max-w-3xl max-h-[92vh] flex flex-col">
    <div class="flex items-start justify-between px-6 sm:px-8 py-5 border-b border-gray-200 bg-gradient-to-r from-amber-50 to-orange-50 flex-shrink-0">
      <div class="flex items-center gap-4">
        <div class="flex items-center justify-center w-12 h-12 rounded-xl bg-gradient-to-br from-amber-500 to-orange-600 shadow-lg"><i class="fi fi-sr-edit text-white text-xl"></i></div>
        <div><h2 class="text-xl font-bold text-gray-800">Edit Bid</h2><p class="text-xs text-gray-500 mt-0.5">Bid <span id="e-bid-id" class="font-semibold text-gray-700">—</span></p></div>
      </div>
      <button id="closeEditModal" class="p-2 rounded-xl text-gray-400 hover:text-gray-600 hover:bg-white transition-all"><i class="fi fi-rr-cross text-lg"></i></button>
    </div>
    <div class="overflow-y-auto flex-1 px-6 sm:px-8 py-6 space-y-5">
      <div class="rounded-xl border border-gray-200 bg-gray-50 p-4">
        <p class="text-xs font-semibold text-gray-400 uppercase tracking-wide mb-3">Read-only Information</p>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
          <div><label class="block text-xs font-semibold text-gray-500 mb-1">Company Name</label><input type="text" id="e-company-name" class="w-full text-sm border border-gray-200 rounded-lg px-3 py-2.5 bg-white text-gray-600 cursor-not-allowed" readonly></div>
          <div><label class="block text-xs font-semibold text-gray-500 mb-1">Email Address</label><input type="text" id="e-company-email" class="w-full text-sm border border-gray-200 rounded-lg px-3 py-2.5 bg-white text-gray-600 cursor-not-allowed" readonly></div>
          <div><label class="block text-xs font-semibold text-gray-500 mb-1">Project Title</label><input type="text" id="e-project-title" class="w-full text-sm border border-gray-200 rounded-lg px-3 py-2.5 bg-white text-gray-600 cursor-not-allowed" readonly></div>
          <div><label class="block text-xs font-semibold text-gray-500 mb-1">Estimated Timeline</label><input type="text" id="e-timeline" class="w-full text-sm border border-gray-200 rounded-lg px-3 py-2.5 bg-white text-gray-600 cursor-not-allowed" readonly></div>
        </div>
      </div>
      <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
        <div>
          <label class="block text-sm font-semibold text-gray-700 mb-1.5">Proposed Cost (₱)</label>
          <div class="relative"><span class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-500 font-semibold text-sm">₱</span><input type="text" id="e-proposed-cost" class="w-full text-sm border-2 border-gray-300 rounded-lg pl-7 pr-4 py-2.5 focus:ring-2 focus:ring-amber-400 focus:border-amber-400"></div>
        </div>
        <div>
          <label class="block text-sm font-semibold text-gray-700 mb-1.5">Status</label>
          <select id="e-status" class="w-full text-sm border-2 border-gray-300 rounded-lg px-3 py-2.5 focus:ring-2 focus:ring-amber-400 focus:border-amber-400">
            <option value="submitted">Submitted</option>
            <option value="under_review">Under Review</option>
            <option value="accepted">Approved</option>
            <option value="rejected">Rejected</option>
            <option value="cancelled">Cancelled</option>
          </select>
        </div>
      </div>
      <div>
        <label class="block text-sm font-semibold text-gray-700 mb-1.5">Contractor Notes</label>
        <textarea id="e-notes" rows="5" class="w-full text-sm border-2 border-gray-300 rounded-lg px-4 py-3 focus:ring-2 focus:ring-amber-400 focus:border-amber-400 resize-none" placeholder="Contractor notes…"></textarea>
      </div>
    </div>
    <div class="flex items-center justify-end gap-3 px-8 py-4 border-t border-gray-200 bg-gray-50 flex-shrink-0">
      <button id="cancelEditBtn" class="px-5 py-2.5 rounded-xl border-2 border-gray-300 text-gray-700 font-semibold text-sm hover:bg-gray-100 transition">Cancel</button>
      <button id="saveChangesBtn" class="px-5 py-2.5 rounded-xl bg-gradient-to-r from-amber-500 to-orange-600 text-white font-semibold text-sm hover:from-amber-600 hover:to-orange-700 shadow transition flex items-center gap-2"><i class="fi fi-rr-disk"></i> Save Changes</button>
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
<div id="modal-delete-confirm" class="fixed inset-0 bg-black/60 backdrop-blur-sm items-center justify-center z-[110] p-4" style="display:none;">
  <div class="bg-white rounded-2xl shadow-2xl w-full max-w-lg p-7">
    <div class="flex items-center justify-center w-20 h-20 rounded-full bg-red-100 mx-auto mb-5"><i class="fi fi-sr-triangle-warning text-red-600 text-4xl"></i></div>
    <h3 class="text-2xl font-bold text-gray-800 text-center mb-2">Delete Bid?</h3>
    <p class="text-gray-500 text-center text-sm mb-6">This cannot be undone. All bid data and files will be permanently removed.</p>
    <div class="bg-red-50 border border-red-200 rounded-xl p-4 mb-6 space-y-2 text-sm">
      <div class="flex justify-between"><span class="text-gray-500">Bid ID</span><span id="d-bid-id" class="font-bold text-gray-800">—</span></div>
      <div class="flex justify-between"><span class="text-gray-500">Project</span><span id="d-project-title" class="font-bold text-gray-800 text-right ml-4 truncate max-w-[240px]">—</span></div>
      <div class="flex justify-between"><span class="text-gray-500">Contractor</span><span id="d-company-name" class="font-bold text-gray-800 text-right ml-4 truncate max-w-[240px]">—</span></div>
    </div>
    <div class="flex items-center gap-3">
      <button id="cancelDeleteBtn" class="flex-1 px-5 py-3 rounded-xl border-2 border-gray-300 text-gray-700 font-semibold text-sm hover:bg-gray-100">Cancel</button>
      <button id="confirmDeleteBtn" class="flex-1 px-5 py-3 rounded-xl bg-gradient-to-r from-red-600 to-rose-600 text-white font-semibold text-sm hover:from-red-700 hover:to-rose-700 shadow">Yes, Delete</button>
    </div>
  </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
  var csrf = document.querySelector('meta[name="csrf-token"]').content;

  function openModal(id) { var el = document.getElementById('modal-' + id); if (!el) return; el.classList.add('modal-active'); document.body.style.overflow = 'hidden'; }
  function closeModal(id) { var el = document.getElementById('modal-' + id); if (!el) return; el.classList.remove('modal-active'); if (!document.querySelector('.modal-active')) document.body.style.overflow = ''; }

  ['view','edit','save-confirm','delete-confirm'].forEach(function(id) {
    var el = document.getElementById('modal-' + id);
    if (!el) return;
    el.addEventListener('click', function(e) { if (e.target === el) closeModal(id); });
  });
  document.addEventListener('keydown', function(e) { if (e.key === 'Escape') ['view','edit','save-confirm','delete-confirm'].forEach(closeModal); });

  var statusCfg = {
    submitted:    { label: 'Submitted',        cls: 'bg-blue-100 text-blue-700 border-blue-200' },
    under_review: { label: 'Under Evaluation', cls: 'bg-amber-100 text-amber-700 border-amber-200' },
    accepted:     { label: 'Approved',         cls: 'bg-green-100 text-green-700 border-green-200' },
    rejected:     { label: 'Rejected',         cls: 'bg-red-100 text-red-700 border-red-200' },
    cancelled:    { label: 'Cancelled',        cls: 'bg-gray-100 text-gray-500 border-gray-200' },
  };
  function fmtCost(val) { var n = parseFloat(String(val).replace(/[^0-9.]/g, '')); return isNaN(n) ? '—' : '₱' + n.toLocaleString('en-PH', { minimumFractionDigits: 2 }); }
  function escHtml(str) { return String(str).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;').replace(/'/g,'&#039;'); }

  // VIEW
  document.querySelectorAll('.btn-view-bid').forEach(function(btn) {
    btn.addEventListener('click', function() {
      var d = this.dataset;
      document.getElementById('v-bid-id').textContent = '#' + d.bidId;
      var badge = document.getElementById('v-status-badge');
      var cfg = statusCfg[d.bidStatus] || { label: d.bidStatus, cls: 'bg-gray-100 text-gray-600 border-gray-200' };
      badge.textContent = cfg.label; badge.className = 'inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold border ' + cfg.cls;
      document.getElementById('v-company-name').textContent  = d.companyName  || '—';
      document.getElementById('v-company-email').textContent = d.companyEmail || '—';
      document.getElementById('v-pcab').textContent          = d.pcab         || 'N/A';
      document.getElementById('v-pcab-category').textContent = d.pcabCategory || 'N/A';
      document.getElementById('v-pcab-expiry').textContent   = d.pcabExpiry   || 'N/A';
      document.getElementById('v-bp-number').textContent     = d.bpNumber     || 'N/A';
      document.getElementById('v-bp-city').textContent       = d.bpCity       || 'N/A';
      document.getElementById('v-bp-expiry').textContent     = d.bpExpiry     || 'N/A';
      document.getElementById('v-tin').textContent           = d.tin          || 'N/A';
      document.getElementById('v-project-title').textContent = d.projectTitle || '—';
      document.getElementById('v-proposed-cost').textContent = fmtCost(d.proposedCost);
      document.getElementById('v-timeline').textContent      = d.timeline ? d.timeline + ' month(s)' : '—';
      document.getElementById('v-submitted-at').textContent  = d.submittedAt  || '—';
      document.getElementById('v-decision-date').textContent = d.decisionDate || '—';
      document.getElementById('v-notes').value               = d.notes        || '';
      var rb = document.getElementById('v-reason-block');
      if (d.bidStatus === 'accepted' || d.bidStatus === 'rejected') {
        rb.classList.remove('hidden');
        var iw = document.getElementById('v-reason-icon-wrap'), ic = document.getElementById('v-reason-icon'), lbl = document.getElementById('v-reason-label'), ta = document.getElementById('v-reason-text');
        if (d.bidStatus === 'accepted') { rb.className='rounded-xl border border-green-200 bg-green-50 p-5'; iw.className='flex items-center justify-center w-9 h-9 rounded-lg bg-green-500'; ic.className='fi fi-sr-check-circle text-white text-base'; lbl.textContent='Approval Remarks'; ta.className='w-full text-sm border border-green-200 rounded-lg px-3 py-2.5 bg-white resize-none focus:outline-none'; }
        else { rb.className='rounded-xl border border-red-200 bg-red-50 p-5'; iw.className='flex items-center justify-center w-9 h-9 rounded-lg bg-red-500'; ic.className='fi fi-sr-cross-circle text-white text-base'; lbl.textContent='Reason for Rejection'; ta.className='w-full text-sm border border-red-200 rounded-lg px-3 py-2.5 bg-white resize-none focus:outline-none'; }
        ta.value = d.reason || 'No reason provided.';
      } else { rb.classList.add('hidden'); }
      loadBidFiles(d.bidId);
      openModal('view');
    });
  });
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
        return '<tr class="hover:bg-gray-50"><td class="px-4 py-3"><div class="flex items-center gap-2"><span class="inline-flex items-center justify-center w-8 h-8 rounded text-xs font-bold flex-shrink-0 '+ec+'">'+ext+'</span><span class="text-sm text-gray-800">'+escHtml(f.file_name)+'</span></div></td><td class="px-4 py-3 text-sm text-gray-500">'+escHtml(f.description||'—')+'</td><td class="px-4 py-3 text-sm text-gray-500">'+escHtml(f.uploaded_at)+'</td><td class="px-4 py-3"><a href="/storage/'+escHtml(f.file_path)+'" target="_blank" class="inline-flex items-center justify-center w-8 h-8 rounded-full bg-blue-50 hover:bg-blue-100 transition"><i class="fi fi-rr-download text-blue-600"></i></a></td></tr>';
      }).join('');
      container.innerHTML = '<table class="w-full text-sm"><thead><tr class="bg-gray-50 text-xs text-gray-500 uppercase border-b border-gray-200"><th class="px-4 py-2 text-left">File Name</th><th class="px-4 py-2 text-left">Description</th><th class="px-4 py-2 text-left">Uploaded</th><th class="px-4 py-2 text-left">Action</th></tr></thead><tbody class="divide-y divide-gray-100">'+rows+'</tbody></table>';
    })
    .catch(function() { container.innerHTML = '<p class="py-4 text-sm text-red-400 text-center">Could not load files.</p>'; });
  }

  // EDIT
  var currentEditBidId = null;
  document.querySelectorAll('.btn-edit-bid').forEach(function(btn) {
    btn.addEventListener('click', function() {
      var d = this.dataset; currentEditBidId = d.bidId;
      document.getElementById('e-bid-id').textContent  = '#' + d.bidId;
      document.getElementById('e-company-name').value  = d.companyName  || '';
      document.getElementById('e-company-email').value = d.companyEmail || '';
      document.getElementById('e-project-title').value = d.projectTitle || '';
      document.getElementById('e-timeline').value      = d.timeline ? d.timeline + ' month(s)' : '';
      document.getElementById('e-proposed-cost').value = d.proposedCost ? parseFloat(d.proposedCost).toLocaleString('en-PH',{minimumFractionDigits:2}) : '';
      document.getElementById('e-status').value        = d.bidStatus || 'submitted';
      document.getElementById('e-notes').value         = d.notes || '';
      openModal('edit');
    });
  });
  document.getElementById('closeEditModal').addEventListener('click', function() { closeModal('edit'); });
  document.getElementById('cancelEditBtn').addEventListener('click', function() { closeModal('edit'); });
  document.getElementById('saveChangesBtn').addEventListener('click', function() { closeModal('edit'); openModal('save-confirm'); });
  document.getElementById('cancelSaveBtn').addEventListener('click', function() { closeModal('save-confirm'); openModal('edit'); });
  document.getElementById('confirmSaveBtn').addEventListener('click', function() {
    var payload = { bid_status: document.getElementById('e-status').value, proposed_cost: document.getElementById('e-proposed-cost').value.replace(/[^0-9.]/g,''), contractor_notes: document.getElementById('e-notes').value };
    fetch('/admin/global-management/bid-management/' + currentEditBidId, { method:'PUT', headers:{'Content-Type':'application/json','X-CSRF-TOKEN':csrf,'Accept':'application/json'}, body:JSON.stringify(payload) })
    .then(function(r){return r.json();}).then(function(data){ closeModal('save-confirm'); if(data.success){showToast('Bid updated successfully.','success');setTimeout(function(){location.reload();},1200);}else{showToast(data.message||'Failed to update bid.','error');openModal('edit');}})
    .catch(function(){closeModal('save-confirm');showToast('Server error. Please try again.','error');openModal('edit');});
  });

  // DELETE
  var currentDeleteBidId = null;
  document.querySelectorAll('.btn-delete-bid').forEach(function(btn) {
    btn.addEventListener('click', function() {
      var d = this.dataset; currentDeleteBidId = d.bidId;
      document.getElementById('d-bid-id').textContent        = '#' + d.bidId;
      document.getElementById('d-project-title').textContent = d.projectTitle || '—';
      document.getElementById('d-company-name').textContent  = d.companyName  || '—';
      openModal('delete-confirm');
    });
  });
  document.getElementById('cancelDeleteBtn').addEventListener('click', function() { closeModal('delete-confirm'); });
  document.getElementById('confirmDeleteBtn').addEventListener('click', function() {
    fetch('/admin/global-management/bid-management/' + currentDeleteBidId, { method:'DELETE', headers:{'X-CSRF-TOKEN':csrf,'Accept':'application/json'} })
    .then(function(r){return r.json();}).then(function(data){closeModal('delete-confirm');if(data.success){showToast('Bid deleted.','success');setTimeout(function(){location.reload();},1200);}else{showToast(data.message||'Failed to delete bid.','error');}})
    .catch(function(){closeModal('delete-confirm');showToast('Server error. Please try again.','error');});
  });

  function showToast(message, type) {
    var t = document.createElement('div');
    t.style.cssText = 'position:fixed;bottom:24px;right:24px;z-index:9999;padding:12px 20px;border-radius:12px;background:'+({success:'#16a34a',error:'#dc2626'}[type]||'#374151')+';color:#fff;font-size:14px;font-weight:600;box-shadow:0 4px 24px rgba(0,0,0,0.18);transition:opacity 0.3s;';
    t.textContent = message; document.body.appendChild(t);
    setTimeout(function(){t.style.opacity='0';setTimeout(function(){t.remove();},350);},2800);
  }
});
</script>
<script src="{{ asset('js/admin/globalManagement/bidManagement.js') }}" defer></script>
</body>
</html>