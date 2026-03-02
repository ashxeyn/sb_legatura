<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <title>Admin Dashboard - Legatura</title>

  <script src="https://cdn.tailwindcss.com"></script>
  <link rel="stylesheet" href="{{ asset('css/admin/home/mainComponents.css') }}">
  <link rel="stylesheet" href="{{ asset('css/admin/globalManagement/bidManagement.css') }}">

  <link rel='stylesheet' href='https://cdn-uicons.flaticon.com/3.0.0/uicons-solid-straight/css/uicons-solid-straight.css'>
  <link rel='stylesheet' href='https://cdn-uicons.flaticon.com/3.0.0/uicons-solid-rounded/css/uicons-solid-rounded.css'>
  <link rel='stylesheet' href='https://cdn-uicons.flaticon.com/3.0.0/uicons-bold-rounded/css/uicons-bold-rounded.css'>
  <link rel='stylesheet' href='https://cdn-uicons.flaticon.com/3.0.0/uicons-regular-rounded/css/uicons-regular-rounded.css'>

  <script src="{{ asset('js/admin/home/mainComponents.js') }}" defer></script>

  {{--
    CRITICAL: Our modals MUST NOT use Tailwind's `hidden` class because
    bidManagement.css may also target `.view-modal-overlay` or similar classes.
    Instead we use a dedicated [data-modal] attribute toggled by JS.
    The style below is the ONLY thing controlling modal visibility.
  --}}
  <style>
    [data-modal]               { display: none; }
    [data-modal].modal-active  { display: flex; }
  </style>
</head>

<body class="bg-gray-50 text-gray-800 font-sans">
<div class="flex min-h-screen">

{{-- ============================================================
     SIDEBAR
============================================================ --}}
<aside class="bg-white shadow-xl flex flex-col">
  <div class="flex justify-center items-center">
    <img src="{{ asset('img/logo.svg') }}" alt="Legatura Logo" class="logo-img">
  </div>

  <nav class="flex-1 px-3 py-4 space-y-1">

    <div class="nav-group">
      <button class="nav-btn">
        <div class="flex items-center gap-3"><i class="fi fi-ss-home" style="font-size:20px;"></i><span>Home</span></div>
        <span class="arrow">▼</span>
      </button>
      <div class="nav-submenu">
        <a href="{{ route('admin.dashboard') }}" class="submenu-link">Dashboard</a>
        <div class="submenu-nested">
          <button class="submenu-link submenu-nested-btn"><span>Analytics</span><span class="arrow-small">▼</span></button>
          <div class="submenu-nested-content">
            <a href="{{ route('admin.analytics') }}" class="submenu-nested-link">Project Analytics</a>
            <a href="{{ route('admin.analytics.subscription') }}" class="submenu-nested-link">Subscription Analytics</a>
            <a href="{{ route('admin.analytics.userActivity') }}" class="submenu-nested-link">User Activity Analytics</a>
            <a href="{{ route('admin.analytics.projectPerformance') }}" class="submenu-nested-link">Project Performance Analytics</a>
            <a href="{{ route('admin.analytics.bidCompletion') }}" class="submenu-nested-link">Bid Completion Analytics</a>
            <a href="{{ route('admin.analytics.reports') }}" class="submenu-nested-link">Reports and Analytics</a>
          </div>
        </div>
      </div>
    </div>

    <div class="nav-group">
      <button class="nav-btn">
        <div class="flex items-center gap-3"><i class="fi fi-ss-users-alt" style="font-size:20px;"></i><span>User Management</span></div>
        <span class="arrow">▼</span>
      </button>
      <div class="nav-submenu">
        <a href="{{ route('admin.userManagement.propertyOwner') }}" class="submenu-link">Property Owner</a>
        <a href="{{ route('admin.userManagement.contractor') }}" class="submenu-link">Contractor</a>
        <a href="{{ route('admin.userManagement.verificationRequest') }}" class="submenu-link">Verification Request</a>
        <a href="{{ route('admin.userManagement.suspendedAccounts') }}" class="submenu-link">Suspended Accounts</a>
      </div>
    </div>

    <div class="nav-group">
      <button class="nav-btn">
        <div class="flex items-center gap-3"><i class="fi fi-ss-globe" style="font-size:20px;"></i><span>Global Management</span></div>
        <span class="arrow">▼</span>
      </button>
      <div class="nav-submenu">
        <a href="{{ route('admin.globalManagement.bidManagement') }}" class="submenu-link active">Bid Management</a>
        <a href="{{ route('admin.globalManagement.proofOfpayments') }}" class="submenu-link">Proof of Payments</a>
        <a href="{{ route('admin.globalManagement.aiManagement') }}" class="submenu-link">AI Management</a>
        <a href="{{ route('admin.globalManagement.postingManagement') }}" class="submenu-link">Posting Management</a>
      </div>
    </div>

    <div class="nav-group">
      <button class="nav-btn">
        <div class="flex items-center gap-3"><i class="fi fi-sr-master-plan" style="font-size:20px;"></i><span>Project Management</span></div>
        <span class="arrow">▼</span>
      </button>
      <div class="nav-submenu">
        <a href="{{ route('admin.projectManagement.listOfProjects') }}" class="submenu-link">List of Projects</a>
        <a href="{{ route('admin.projectManagement.disputesReports') }}" class="submenu-link">Disputes/Reports</a>
        <a href="{{ route('admin.projectManagement.messages') }}" class="submenu-link">Messages</a>
        <a href="{{ route('admin.projectManagement.subscriptions') }}" class="submenu-link">Subscriptions & Boosts</a>
      </div>
    </div>

    <div class="nav-group">
      <button class="nav-btn">
        <div class="flex items-center gap-3"><i class="fi fi-br-settings-sliders" style="font-size:20px;"></i><span>Settings</span></div>
        <span class="arrow">▼</span>
      </button>
      <div class="nav-submenu">
        <a href="{{ route('admin.settings.notifications') }}" class="submenu-link">Notifications</a>
        <a href="{{ route('admin.settings.security') }}" class="submenu-link">Security</a>
      </div>
    </div>

  </nav>

  <div class="mt-auto p-4">
    <div class="user-card flex items-center gap-3 p-3 rounded-lg shadow-md text-white">
      <div class="w-10 h-10 rounded-full bg-white text-indigo-900 flex items-center justify-center font-bold shadow flex-shrink-0">ES</div>
      <div class="flex-1 min-w-0">
        <div class="font-semibold text-sm truncate">Emmanuelle Santos</div>
        <div class="text-xs opacity-80 truncate">santos@Legatura.com</div>
      </div>
      <div class="relative">
        <button id="userMenuBtn" class="text-white opacity-80 hover:opacity-100 transition text-2xl w-8 h-8 flex items-center justify-center rounded-full">⋮</button>
        <div id="userMenuDropdown" class="absolute right-0 bottom-full mb-2 w-44 bg-white text-gray-800 rounded-xl shadow-2xl border border-gray-200 hidden">
          <div class="px-4 py-3 border-b border-gray-100">
            <div class="text-sm font-semibold">Emmanuelle Santos</div>
            <div class="text-xs text-gray-500">santos@Legatura.com</div>
          </div>
          <ul class="py-1">
            <li>
              <a href="{{ route('admin.settings.security') }}" class="flex items-center gap-2 px-4 py-2 text-sm hover:bg-gray-50">
                <i class="fi fi-br-settings-sliders"></i><span>Account settings</span>
              </a>
            </li>
            <li>
              <button id="logoutBtn" class="w-full text-left flex items-center gap-2 px-4 py-2 text-sm hover:bg-gray-50 text-red-600">
                <i class="fi fi-ss-exit"></i><span>Logout</span>
              </button>
            </li>
          </ul>
        </div>
      </div>
    </div>
  </div>
</aside>

{{-- ============================================================
     MAIN CONTENT
============================================================ --}}
<main class="flex-1">

  <header class="bg-white shadow-sm border-b border-gray-200 flex items-center justify-between px-8 py-4 sticky top-0 z-30">
    <h1 class="text-2xl font-semibold text-gray-800">Bid Management</h1>
    <div class="flex items-center gap-6">
      <div class="relative" style="width:600px;">
        <input id="searchInput" type="text" placeholder="Search by project or company..."
          class="border border-gray-300 rounded-lg px-4 py-2 pr-10 focus:ring-2 focus:ring-indigo-400 focus:outline-none w-full">
        <i class="fi fi-rr-search absolute right-3 top-1/2 -translate-y-1/2 text-gray-400"></i>
      </div>
      <div class="relative">
        <button id="notificationBell" class="cursor-pointer w-10 h-10 flex items-center justify-center rounded-full hover:bg-gray-100 transition">
          <i class="fi fi-ss-bell-notification-social-media" style="font-size:20px;"></i>
        </button>
        <span class="absolute -top-1 -right-2 bg-red-500 text-white text-xs rounded-full w-5 h-5 flex items-center justify-center">3</span>
        <div id="notificationDropdown" class="absolute right-0 mt-3 w-80 bg-white rounded-xl shadow-2xl border border-gray-200 hidden">
          <div class="px-4 py-3 border-b border-gray-100 flex items-center justify-between">
            <span class="text-sm font-semibold">Notifications</span>
            <button id="clearNotifications" class="text-xs text-indigo-600 hover:text-indigo-700">Clear all</button>
          </div>
          <ul class="max-h-80 overflow-y-auto" id="notificationList">
            <li class="px-4 py-3 hover:bg-gray-50">
              <div class="flex items-start gap-3">
                <div class="w-8 h-8 rounded-full bg-indigo-100 text-indigo-600 flex items-center justify-center"><i class="fi fi-ss-bell"></i></div>
                <div class="flex-1 min-w-0"><p class="text-sm text-gray-800 truncate">New bid submitted.</p><p class="text-xs text-gray-500">2 mins ago</p></div>
                <span class="px-2 py-1 text-xs rounded-full bg-yellow-100 text-yellow-700">New</span>
              </div>
            </li>
          </ul>
          <div class="px-4 py-3 border-t border-gray-100">
            <a href="{{ route('admin.settings.notifications') }}" class="text-sm text-indigo-600 hover:text-indigo-700">Notification settings</a>
          </div>
        </div>
      </div>
    </div>
  </header>

  <div class="p-8">

    {{-- ===================== STAT CARDS ===================== --}}
    @php
      $totalBids    = $bids->total();
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

    {{-- ===================== FILTER BAR ===================== --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-4 mb-6 flex flex-wrap items-center justify-between gap-4">
      <div class="flex items-center gap-3 flex-wrap">
        <div class="flex items-center gap-2 px-3 py-2 border border-gray-300 rounded-lg text-sm text-gray-700">
          <i class="fi fi-rr-filter text-gray-500"></i><span>Filter By</span>
        </div>
        <select id="statusFilter" class="px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-indigo-400 focus:outline-none bg-white text-gray-700">
          <option value="">All Statuses</option>
          <option value="submitted">Submitted</option>
          <option value="under_review">Under Review</option>
          <option value="accepted">Approved</option>
          <option value="rejected">Rejected</option>
          <option value="cancelled">Cancelled</option>
        </select>
      </div>
      <button id="resetFilters" class="flex items-center gap-2 text-red-600 hover:text-red-700 text-sm font-semibold px-3 py-2 rounded-lg hover:bg-red-50 transition">
        <i class="fi fi-rr-rotate-left"></i><span>Reset Filter</span>
      </button>
    </div>

    {{-- ===================== TABLE ===================== --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
      <div class="overflow-x-auto">
        <table class="w-full">
          <thead>
            <tr class="bg-gray-50">
              <th class="text-left px-6 py-4 text-sm font-semibold text-gray-600 tracking-wide">Bid ID</th>
              <th class="text-left px-6 py-4 text-sm font-semibold text-gray-600 tracking-wide">Project Title</th>
              <th class="text-left px-6 py-4 text-sm font-semibold text-gray-600 tracking-wide">Contractor Company</th>
              <th class="text-left px-6 py-4 text-sm font-semibold text-gray-600 tracking-wide">Bid Amount</th>
              <th class="text-left px-6 py-4 text-sm font-semibold text-gray-600 tracking-wide">Submitted</th>
              <th class="text-left px-6 py-4 text-sm font-semibold text-gray-600 tracking-wide">Status</th>
              <th class="text-left px-6 py-4 text-sm font-semibold text-gray-600 tracking-wide">Action</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-gray-200" id="bidsTable">

            @forelse ($bids as $bid)
              @php
                $statusMap = [
                  'submitted'    => ['label' => 'Submitted',         'class' => 'bg-blue-100 text-blue-700 border-blue-200'],
                  'under_review' => ['label' => 'Under Evaluation',  'class' => 'bg-amber-100 text-amber-700 border-amber-200'],
                  'accepted'     => ['label' => 'Approved',          'class' => 'bg-green-100 text-green-700 border-green-200'],
                  'rejected'     => ['label' => 'Rejected',          'class' => 'bg-red-100 text-red-700 border-red-200'],
                  'cancelled'    => ['label' => 'Cancelled',         'class' => 'bg-gray-100 text-gray-500 border-gray-200'],
                ];
                $s = $statusMap[$bid->bid_status] ?? ['label' => ucfirst($bid->bid_status), 'class' => 'bg-gray-100 text-gray-600 border-gray-200'];

                $words    = explode(' ', trim($bid->company_name ?? 'UN'));
                $initials = strtoupper(substr($words[0],0,1).(isset($words[1]) ? substr($words[1],0,1) : substr($words[0],1,1)));

                $gradients = [
                  'from-blue-500 to-indigo-600','from-emerald-500 to-teal-600',
                  'from-fuchsia-500 to-purple-600','from-orange-500 to-rose-500',
                  'from-sky-500 to-blue-600','from-cyan-500 to-teal-500','from-gray-700 to-gray-900',
                ];
                $grad = $gradients[$bid->bid_id % count($gradients)];
              @endphp

              <tr class="hover:bg-indigo-50/60 transition-colors bid-row"
                  data-status="{{ $bid->bid_status }}"
                  data-search-project="{{ strtolower($bid->project_title) }}"
                  data-search-company="{{ strtolower($bid->company_name) }}">

                <td class="px-6 py-4 whitespace-nowrap text-gray-700 font-medium">#{{ $bid->bid_id }}</td>

                <td class="px-6 py-4 text-gray-700 max-w-xs truncate">{{ $bid->project_title }}</td>

                <td class="px-6 py-4">
                  <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-full bg-gradient-to-br {{ $grad }} flex items-center justify-center text-white text-sm font-bold shadow flex-shrink-0">
                      {{ $initials }}
                    </div>
                    <div>
                      <div class="font-semibold text-gray-800 text-sm leading-5">{{ $bid->company_name }}</div>
                      <div class="text-gray-500 text-xs">{{ $bid->company_email }}</div>
                    </div>
                  </div>
                </td>

                <td class="px-6 py-4 whitespace-nowrap text-gray-700 text-sm font-medium">
                  ₱{{ number_format($bid->bid_amount, 2) }}
                </td>

                <td class="px-6 py-4 whitespace-nowrap text-gray-600 text-sm">
                  {{ \Carbon\Carbon::parse($bid->bid_date)->format('M d, Y') }}
                </td>

                <td class="px-6 py-4 whitespace-nowrap">
                  <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold border {{ $s['class'] }}">
                    {{ $s['label'] }}
                  </span>
                </td>

                <td class="px-6 py-4 whitespace-nowrap">
                  <div class="flex items-center gap-2">

                    {{--
                      VIEW button
                      Each data-* attribute name maps to JS dataset via camelCase:
                        data-bid-id          → d.bidId
                        data-bid-status      → d.bidStatus
                        data-company-name    → d.companyName
                        data-company-email   → d.companyEmail
                        data-project-title   → d.projectTitle
                        data-proposed-cost   → d.proposedCost
                        data-timeline        → d.timeline
                        data-submitted-at    → d.submittedAt
                        data-decision-date   → d.decisionDate
                        data-notes           → d.notes
                        data-reason          → d.reason
                        data-pcab            → d.pcab
                        data-pcab-category   → d.pcabCategory
                        data-pcab-expiry     → d.pcabExpiry
                        data-bp-number       → d.bpNumber
                        data-bp-city         → d.bpCity
                        data-bp-expiry       → d.bpExpiry
                        data-tin             → d.tin
                    --}}
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

                    {{--
                      EDIT button — only editable fields: proposed_cost, bid_status, contractor_notes
                      Read-only display fields: company, email, project, timeline
                        data-bid-id          → d.bidId
                        data-bid-status      → d.bidStatus
                        data-company-name    → d.companyName
                        data-company-email   → d.companyEmail
                        data-project-title   → d.projectTitle
                        data-proposed-cost   → d.proposedCost
                        data-timeline        → d.timeline
                        data-notes           → d.notes
                    --}}
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

                    {{-- DELETE button --}}
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
                <td colspan="7" class="px-6 py-12 text-center text-gray-400">
                  <i class="fi fi-rr-inbox text-4xl mb-3 block"></i>
                  No bids found.
                </td>
              </tr>
            @endforelse

          </tbody>
        </table>
      </div>

      @if ($bids->hasPages())
        <div class="px-6 py-4 border-t border-gray-200">
          {{ $bids->links() }}
        </div>
      @endif

    </div>
  </div>
</main>

</div>{{-- end .flex.min-h-screen --}}

{{-- ============================================================
     VIEW BID MODAL
     NOTE: uses [data-modal="view"] + .modal-active — NOT hidden/flex
============================================================ --}}
<div data-modal="view"
  class="modal-active fixed inset-0 bg-black/50 backdrop-blur-sm items-center justify-center z-[100] p-4"
  style="display:none;">
  <div class="bg-white rounded-2xl shadow-2xl w-full max-w-6xl max-h-[92vh] overflow-hidden flex flex-col">

    {{-- Header --}}
    <div class="flex items-start justify-between px-6 sm:px-8 py-5 border-b border-gray-200 bg-gradient-to-r from-indigo-50 to-blue-50 flex-shrink-0">
      <div class="flex items-center gap-4">
        <div class="flex items-center justify-center w-12 h-12 rounded-xl bg-gradient-to-br from-indigo-500 to-blue-600 shadow-lg">
          <i class="fi fi-ss-document text-white text-xl"></i>
        </div>
        <div>
          <h2 class="text-xl font-bold text-gray-800">Bid Details</h2>
          <p class="text-xs text-gray-500 mt-0.5">
            Bid <span id="v-bid-id" class="font-semibold text-gray-700">—</span>
            &nbsp;·&nbsp;
            <span id="v-status-badge" class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold border">—</span>
          </p>
        </div>
      </div>
      <button id="closeViewModal" class="p-2 rounded-xl text-gray-400 hover:text-gray-600 hover:bg-white transition-all">
        <i class="fi fi-rr-cross text-lg"></i>
      </button>
    </div>

    {{-- Body --}}
    <div class="overflow-y-auto flex-1 px-6 sm:px-8 py-6 space-y-6">

      {{-- Row 1: Bidder Info + Project Info --}}
      <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

        {{-- Bidder Information --}}
        <div class="rounded-xl border border-indigo-200 bg-indigo-50/50 p-5">
          <div class="flex items-center gap-2 mb-4">
            <i class="fi fi-ss-user text-indigo-600"></i>
            <h3 class="font-bold text-gray-800">Bidder Information</h3>
          </div>
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

        {{-- Project & Bid Details --}}
        <div class="rounded-xl border border-purple-200 bg-purple-50/50 p-5">
          <div class="flex items-center gap-2 mb-4">
            <i class="fi fi-ss-building text-purple-600"></i>
            <h3 class="font-bold text-gray-800">Project &amp; Bid Details</h3>
          </div>
          <dl class="space-y-2.5 text-sm">
            <div class="flex justify-between gap-4"><dt class="text-gray-500 shrink-0">Project Title</dt><dd id="v-project-title" class="font-semibold text-gray-800 text-right">—</dd></div>
            <div class="flex justify-between gap-4"><dt class="text-gray-500 shrink-0">Proposed Cost</dt><dd id="v-proposed-cost" class="font-bold text-green-700 text-right">—</dd></div>
            <div class="flex justify-between gap-4"><dt class="text-gray-500 shrink-0">Estimated Timeline</dt><dd id="v-timeline" class="font-medium text-gray-800 text-right">—</dd></div>
            <div class="flex justify-between gap-4"><dt class="text-gray-500 shrink-0">Submitted</dt><dd id="v-submitted-at" class="font-medium text-gray-800 text-right">—</dd></div>
            <div class="flex justify-between gap-4"><dt class="text-gray-500 shrink-0">Decision Date</dt><dd id="v-decision-date" class="font-medium text-gray-800 text-right">—</dd></div>
          </dl>
          <div class="mt-4">
            <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wide mb-2">Contractor Notes</label>
            <textarea id="v-notes" rows="5" readonly
              class="w-full text-sm border border-purple-200 rounded-lg px-3 py-2.5 bg-white resize-none focus:outline-none"></textarea>
          </div>
        </div>

      </div>

      {{-- Rejection / Approval Reason (only for accepted / rejected) --}}
      <div id="v-reason-block" class="hidden rounded-xl border p-5">
        <div class="flex items-center gap-2 mb-3">
          <div id="v-reason-icon-wrap" class="flex items-center justify-center w-9 h-9 rounded-lg">
            <i id="v-reason-icon" class="text-white text-base"></i>
          </div>
          <h3 id="v-reason-label" class="font-bold text-gray-800">Remarks</h3>
        </div>
        <textarea id="v-reason-text" rows="4" readonly
          class="w-full text-sm border rounded-lg px-3 py-2.5 bg-white resize-none focus:outline-none"></textarea>
      </div>

      {{-- Supporting Files --}}
      <div>
        <div class="flex items-center gap-2 mb-3">
          <i class="fi fi-ss-folder text-blue-600"></i>
          <h3 class="font-bold text-gray-800">Supporting Files</h3>
        </div>
        <div class="rounded-xl border border-gray-200 overflow-hidden">
          <div id="v-files-container" class="p-6 text-center text-sm text-gray-400">
            Loading files…
          </div>
        </div>
      </div>

    </div>{{-- end body --}}
  </div>
</div>

{{-- ============================================================
     EDIT BID MODAL
============================================================ --}}
<div data-modal="edit"
  class="fixed inset-0 bg-black/50 backdrop-blur-sm items-center justify-center z-[100] p-4"
  style="display:none;">
  <div class="bg-white rounded-2xl shadow-2xl w-full max-w-3xl max-h-[92vh] flex flex-col">

    <div class="flex items-start justify-between px-6 sm:px-8 py-5 border-b border-gray-200 bg-gradient-to-r from-amber-50 to-orange-50 flex-shrink-0">
      <div class="flex items-center gap-4">
        <div class="flex items-center justify-center w-12 h-12 rounded-xl bg-gradient-to-br from-amber-500 to-orange-600 shadow-lg">
          <i class="fi fi-sr-edit text-white text-xl"></i>
        </div>
        <div>
          <h2 class="text-xl font-bold text-gray-800">Edit Bid</h2>
          <p class="text-xs text-gray-500 mt-0.5">Bid <span id="e-bid-id" class="font-semibold text-gray-700">—</span></p>
        </div>
      </div>
      <button id="closeEditModal" class="p-2 rounded-xl text-gray-400 hover:text-gray-600 hover:bg-white transition-all">
        <i class="fi fi-rr-cross text-lg"></i>
      </button>
    </div>

    <div class="overflow-y-auto flex-1 px-6 sm:px-8 py-6 space-y-5">

      {{-- Read-only info (informational, cannot be changed) --}}
      <div class="rounded-xl border border-gray-200 bg-gray-50 p-4">
        <p class="text-xs font-semibold text-gray-400 uppercase tracking-wide mb-3">Read-only Information</p>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
          <div>
            <label class="block text-xs font-semibold text-gray-500 mb-1">Company Name</label>
            <input type="text" id="e-company-name"
              class="w-full text-sm border border-gray-200 rounded-lg px-3 py-2.5 bg-white text-gray-600 cursor-not-allowed" readonly>
          </div>
          <div>
            <label class="block text-xs font-semibold text-gray-500 mb-1">Email Address</label>
            <input type="text" id="e-company-email"
              class="w-full text-sm border border-gray-200 rounded-lg px-3 py-2.5 bg-white text-gray-600 cursor-not-allowed" readonly>
          </div>
          <div>
            <label class="block text-xs font-semibold text-gray-500 mb-1">Project Title</label>
            <input type="text" id="e-project-title"
              class="w-full text-sm border border-gray-200 rounded-lg px-3 py-2.5 bg-white text-gray-600 cursor-not-allowed" readonly>
          </div>
          <div>
            <label class="block text-xs font-semibold text-gray-500 mb-1">Estimated Timeline</label>
            <input type="text" id="e-timeline"
              class="w-full text-sm border border-gray-200 rounded-lg px-3 py-2.5 bg-white text-gray-600 cursor-not-allowed" readonly>
          </div>
        </div>
      </div>

      {{-- Editable fields --}}
      <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
        <div>
          <label class="block text-sm font-semibold text-gray-700 mb-1.5">Proposed Cost (₱)</label>
          <div class="relative">
            <span class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-500 font-semibold text-sm">₱</span>
            <input type="text" id="e-proposed-cost"
              class="w-full text-sm border-2 border-gray-300 rounded-lg pl-7 pr-4 py-2.5 focus:ring-2 focus:ring-amber-400 focus:border-amber-400">
          </div>
        </div>
        <div>
          <label class="block text-sm font-semibold text-gray-700 mb-1.5">Status</label>
          <select id="e-status"
            class="w-full text-sm border-2 border-gray-300 rounded-lg px-3 py-2.5 focus:ring-2 focus:ring-amber-400 focus:border-amber-400">
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
        <textarea id="e-notes" rows="5"
          class="w-full text-sm border-2 border-gray-300 rounded-lg px-4 py-3 focus:ring-2 focus:ring-amber-400 focus:border-amber-400 resize-none"
          placeholder="Contractor notes…"></textarea>
      </div>

    </div>

    <div class="flex items-center justify-end gap-3 px-8 py-4 border-t border-gray-200 bg-gray-50 flex-shrink-0">
      <button id="cancelEditBtn" class="px-5 py-2.5 rounded-xl border-2 border-gray-300 text-gray-700 font-semibold text-sm hover:bg-gray-100 transition">Cancel</button>
      <button id="saveChangesBtn" class="px-5 py-2.5 rounded-xl bg-gradient-to-r from-amber-500 to-orange-600 text-white font-semibold text-sm hover:from-amber-600 hover:to-orange-700 shadow transition flex items-center gap-2">
        <i class="fi fi-rr-disk"></i> Save Changes
      </button>
    </div>

  </div>
</div>

{{-- ============================================================
     SAVE CONFIRM MODAL
============================================================ --}}
<div data-modal="save-confirm"
  class="fixed inset-0 bg-black/60 backdrop-blur-sm items-center justify-center z-[110] p-4"
  style="display:none;">
  <div class="bg-white rounded-2xl shadow-2xl w-full max-w-md p-7 text-center">
    <div class="flex items-center justify-center w-16 h-16 rounded-full bg-amber-100 mx-auto mb-4">
      <i class="fi fi-rr-interrogation text-amber-600 text-3xl"></i>
    </div>
    <h3 class="text-xl font-bold text-gray-800 mb-2">Save Changes?</h3>
    <p class="text-gray-500 text-sm mb-6">This will update the bid record in the database.</p>
    <div class="flex items-center gap-3">
      <button id="cancelSaveBtn" class="flex-1 px-4 py-3 rounded-xl border-2 border-gray-300 text-gray-700 font-semibold text-sm hover:bg-gray-100">Cancel</button>
      <button id="confirmSaveBtn" class="flex-1 px-4 py-3 rounded-xl bg-gradient-to-r from-amber-500 to-orange-600 text-white font-semibold text-sm hover:from-amber-600 hover:to-orange-700 shadow">Yes, Save</button>
    </div>
  </div>
</div>

{{-- ============================================================
     DELETE CONFIRM MODAL
============================================================ --}}
<div data-modal="delete-confirm"
  class="fixed inset-0 bg-black/60 backdrop-blur-sm items-center justify-center z-[110] p-4"
  style="display:none;">
  <div class="bg-white rounded-2xl shadow-2xl w-full max-w-lg p-7">
    <div class="flex items-center justify-center w-20 h-20 rounded-full bg-red-100 mx-auto mb-5">
      <i class="fi fi-sr-triangle-warning text-red-600 text-4xl"></i>
    </div>
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

{{-- ============================================================
     JAVASCRIPT
============================================================ --}}
<script>
document.addEventListener('DOMContentLoaded', function () {

  const csrf = document.querySelector('meta[name="csrf-token"]').content;

  // ─── Modal helpers ────────────────────────────────────────────
  // Uses display:none / display:flex via the [data-modal] attribute.
  // This completely bypasses any CSS class conflicts from bidManagement.css.
  function openModal(name)  {
    const el = document.querySelector('[data-modal="' + name + '"]');
    if (el) { el.style.display = 'flex'; document.body.style.overflow = 'hidden'; }
  }
  function closeModal(name) {
    const el = document.querySelector('[data-modal="' + name + '"]');
    if (el) { el.style.display = 'none'; document.body.style.overflow = ''; }
  }
  // Close on backdrop click
  document.querySelectorAll('[data-modal]').forEach(function(modal) {
    modal.addEventListener('click', function(e) {
      if (e.target === modal) closeModal(modal.dataset.modal);
    });
  });

  // ─── Search + Filter ──────────────────────────────────────────
  const searchInput  = document.getElementById('searchInput');
  const statusFilter = document.getElementById('statusFilter');

  function filterRows() {
    const q      = searchInput.value.toLowerCase().trim();
    const status = statusFilter.value;
    document.querySelectorAll('.bid-row').forEach(function(row) {
      const matchSearch = !q
        || (row.dataset.searchProject || '').includes(q)
        || (row.dataset.searchCompany || '').includes(q);
      const matchStatus = !status || row.dataset.status === status;
      row.style.display = (matchSearch && matchStatus) ? '' : 'none';
    });
  }
  searchInput.addEventListener('input', filterRows);
  statusFilter.addEventListener('change', filterRows);
  document.getElementById('resetFilters').addEventListener('click', function() {
    searchInput.value = ''; statusFilter.value = ''; filterRows();
  });

  // ─── Status config ────────────────────────────────────────────
  const statusCfg = {
    submitted:    { label: 'Submitted',         cls: 'bg-blue-100 text-blue-700 border-blue-200' },
    under_review: { label: 'Under Evaluation',  cls: 'bg-amber-100 text-amber-700 border-amber-200' },
    accepted:     { label: 'Approved',          cls: 'bg-green-100 text-green-700 border-green-200' },
    rejected:     { label: 'Rejected',          cls: 'bg-red-100 text-red-700 border-red-200' },
    cancelled:    { label: 'Cancelled',         cls: 'bg-gray-100 text-gray-500 border-gray-200' },
  };

  // ─── VIEW MODAL ───────────────────────────────────────────────
  document.querySelectorAll('.btn-view-bid').forEach(function(btn) {
    btn.addEventListener('click', function() {
      var d = this.dataset;

      // Header
      document.getElementById('v-bid-id').textContent = '#' + d.bidId;
      var badge = document.getElementById('v-status-badge');
      var cfg = statusCfg[d.bidStatus] || { label: d.bidStatus, cls: 'bg-gray-100 text-gray-600 border-gray-200' };
      badge.textContent  = cfg.label;
      badge.className    = 'inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold border ' + cfg.cls;

      // Bidder info
      document.getElementById('v-company-name').textContent  = d.companyName  || '—';
      document.getElementById('v-company-email').textContent = d.companyEmail || '—';
      document.getElementById('v-pcab').textContent          = d.pcab         || 'N/A';
      document.getElementById('v-pcab-category').textContent = d.pcabCategory || 'N/A';
      document.getElementById('v-pcab-expiry').textContent   = d.pcabExpiry   || 'N/A';
      document.getElementById('v-bp-number').textContent     = d.bpNumber     || 'N/A';
      document.getElementById('v-bp-city').textContent       = d.bpCity       || 'N/A';
      document.getElementById('v-bp-expiry').textContent     = d.bpExpiry     || 'N/A';
      document.getElementById('v-tin').textContent           = d.tin          || 'N/A';

      // Bid details
      document.getElementById('v-project-title').textContent = d.projectTitle || '—';
      document.getElementById('v-proposed-cost').textContent = d.proposedCost
        ? '₱' + parseFloat(d.proposedCost).toLocaleString('en-PH', {minimumFractionDigits:2})
        : '—';
      document.getElementById('v-timeline').textContent    = d.timeline
        ? d.timeline + ' month(s)'
        : '—';
      document.getElementById('v-submitted-at').textContent  = d.submittedAt  || '—';
      document.getElementById('v-decision-date').textContent = d.decisionDate || '—';
      document.getElementById('v-notes').value               = d.notes        || '';

      // Reason block (accepted / rejected only)
      var reasonBlock = document.getElementById('v-reason-block');
      if (d.bidStatus === 'accepted' || d.bidStatus === 'rejected') {
        reasonBlock.classList.remove('hidden');
        var iconWrap = document.getElementById('v-reason-icon-wrap');
        var icon     = document.getElementById('v-reason-icon');
        var label    = document.getElementById('v-reason-label');
        var textarea = document.getElementById('v-reason-text');
        if (d.bidStatus === 'accepted') {
          reasonBlock.className = 'rounded-xl border border-green-200 bg-green-50 p-5';
          iconWrap.className    = 'flex items-center justify-center w-9 h-9 rounded-lg bg-green-500';
          icon.className        = 'fi fi-sr-check-circle text-white text-base';
          label.textContent     = "Property Owner's Approval Remarks";
          textarea.className    = 'w-full text-sm border border-green-200 rounded-lg px-3 py-2.5 bg-white resize-none focus:outline-none';
        } else {
          reasonBlock.className = 'rounded-xl border border-red-200 bg-red-50 p-5';
          iconWrap.className    = 'flex items-center justify-center w-9 h-9 rounded-lg bg-red-500';
          icon.className        = 'fi fi-sr-cross-circle text-white text-base';
          label.textContent     = 'Reason for Rejection';
          textarea.className    = 'w-full text-sm border border-red-200 rounded-lg px-3 py-2.5 bg-white resize-none focus:outline-none';
        }
        textarea.value = d.reason || 'No reason provided.';
      } else {
        reasonBlock.classList.add('hidden');
      }

      // Files
      loadBidFiles(d.bidId);

      openModal('view');
    });
  });

  document.getElementById('closeViewModal').addEventListener('click', function() { closeModal('view'); });

  // Load bid files
  function loadBidFiles(bidId) {
    var container = document.getElementById('v-files-container');
    container.innerHTML = '<p class="py-4 text-sm text-gray-400 animate-pulse">Loading files…</p>';

    fetch('/admin/global-management/bid-management/files/' + bidId, {
      headers: { 'X-CSRF-TOKEN': csrf, 'Accept': 'application/json' }
    })
    .then(function(r) { return r.json(); })
    .then(function(files) {
      if (!Array.isArray(files) || files.length === 0) {
        container.innerHTML = '<p class="py-6 text-sm text-gray-400 text-center">No files attached to this bid.</p>';
        return;
      }
      var rows = files.map(function(f) {
        var ext = f.file_name.split('.').pop().toUpperCase();
        var extColors = { PDF: 'bg-red-100 text-red-700', JPG: 'bg-yellow-100 text-yellow-700', JPEG: 'bg-yellow-100 text-yellow-700', PNG: 'bg-blue-100 text-blue-700' };
        var extCls = extColors[ext] || 'bg-gray-100 text-gray-600';
        return '<tr class="hover:bg-gray-50">'
          + '<td class="px-4 py-3">'
          +   '<div class="flex items-center gap-2">'
          +     '<span class="inline-flex items-center justify-center w-8 h-8 rounded text-xs font-bold flex-shrink-0 ' + extCls + '">' + ext + '</span>'
          +     '<span class="text-sm text-gray-800">' + escHtml(f.file_name) + '</span>'
          +   '</div>'
          + '</td>'
          + '<td class="px-4 py-3 text-sm text-gray-500">' + escHtml(f.description || '—') + '</td>'
          + '<td class="px-4 py-3 text-sm text-gray-500">' + escHtml(f.uploaded_at) + '</td>'
          + '<td class="px-4 py-3">'
          +   '<a href="/storage/' + f.file_path + '" target="_blank"'
          +     ' class="inline-flex items-center justify-center w-8 h-8 rounded-full bg-blue-50 hover:bg-blue-100 transition" title="Download">'
          +     '<i class="fi fi-rr-download text-blue-600"></i>'
          +   '</a>'
          + '</td>'
          + '</tr>';
      }).join('');
      container.innerHTML =
        '<table class="w-full text-sm">'
        + '<thead><tr class="bg-gray-50 text-xs text-gray-500 uppercase border-b border-gray-200">'
        +   '<th class="px-4 py-2 text-left">File Name</th>'
        +   '<th class="px-4 py-2 text-left">Description</th>'
        +   '<th class="px-4 py-2 text-left">Uploaded</th>'
        +   '<th class="px-4 py-2 text-left">Action</th>'
        + '</tr></thead>'
        + '<tbody class="divide-y divide-gray-100">' + rows + '</tbody>'
        + '</table>';
    })
    .catch(function() {
      container.innerHTML = '<p class="py-4 text-sm text-red-400 text-center">Could not load files. Ensure the route is registered.</p>';
    });
  }

  // ─── EDIT MODAL ───────────────────────────────────────────────
  var currentEditBidId = null;

  document.querySelectorAll('.btn-edit-bid').forEach(function(btn) {
    btn.addEventListener('click', function() {
      var d = this.dataset;
      currentEditBidId = d.bidId;

      document.getElementById('e-bid-id').textContent    = '#' + d.bidId;
      document.getElementById('e-company-name').value    = d.companyName  || '';
      document.getElementById('e-company-email').value   = d.companyEmail || '';
      document.getElementById('e-project-title').value   = d.projectTitle || '';
      document.getElementById('e-timeline').value        = d.timeline ? d.timeline + ' month(s)' : '';
      document.getElementById('e-proposed-cost').value   = d.proposedCost
        ? parseFloat(d.proposedCost).toLocaleString('en-PH', {minimumFractionDigits:2})
        : '';
      document.getElementById('e-status').value          = d.bidStatus    || 'submitted';
      document.getElementById('e-notes').value           = d.notes        || '';

      openModal('edit');
    });
  });

  document.getElementById('closeEditModal').addEventListener('click',  function() { closeModal('edit'); });
  document.getElementById('cancelEditBtn').addEventListener('click',   function() { closeModal('edit'); });

  document.getElementById('saveChangesBtn').addEventListener('click', function() {
    closeModal('edit');
    openModal('save-confirm');
  });

  document.getElementById('cancelSaveBtn').addEventListener('click', function() {
    closeModal('save-confirm');
    openModal('edit');
  });

  document.getElementById('confirmSaveBtn').addEventListener('click', function() {
    var costRaw = document.getElementById('e-proposed-cost').value.replace(/[^0-9.]/g, '');
    var payload = {
      bid_status:       document.getElementById('e-status').value,
      proposed_cost:    costRaw,
      contractor_notes: document.getElementById('e-notes').value,
    };

    fetch('/admin/global-management/bid-management/' + currentEditBidId, {
      method:  'PUT',
      headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrf, 'Accept': 'application/json' },
      body:    JSON.stringify(payload),
    })
    .then(function(r) { return r.json(); })
    .then(function(data) {
      closeModal('save-confirm');
      if (data.success) {
        showToast('Bid updated successfully.', 'success');
        setTimeout(function() { location.reload(); }, 1200);
      } else {
        showToast(data.message || 'Failed to update bid.', 'error');
      }
    })
    .catch(function() { closeModal('save-confirm'); showToast('Server error. Please try again.', 'error'); });
  });

  // ─── DELETE MODAL ─────────────────────────────────────────────
  var currentDeleteBidId = null;

  document.querySelectorAll('.btn-delete-bid').forEach(function(btn) {
    btn.addEventListener('click', function() {
      var d = this.dataset;
      currentDeleteBidId = d.bidId;
      document.getElementById('d-bid-id').textContent        = '#' + d.bidId;
      document.getElementById('d-project-title').textContent = d.projectTitle || '—';
      document.getElementById('d-company-name').textContent  = d.companyName  || '—';
      openModal('delete-confirm');
    });
  });

  document.getElementById('cancelDeleteBtn').addEventListener('click', function() { closeModal('delete-confirm'); });

  document.getElementById('confirmDeleteBtn').addEventListener('click', function() {
    fetch('/admin/global-management/bid-management/' + currentDeleteBidId, {
      method:  'DELETE',
      headers: { 'X-CSRF-TOKEN': csrf, 'Accept': 'application/json' },
    })
    .then(function(r) { return r.json(); })
    .then(function(data) {
      closeModal('delete-confirm');
      if (data.success) {
        showToast('Bid deleted.', 'success');
        setTimeout(function() { location.reload(); }, 1200);
      } else {
        showToast(data.message || 'Failed to delete bid.', 'error');
      }
    })
    .catch(function() { closeModal('delete-confirm'); showToast('Server error. Please try again.', 'error'); });
  });

  // ─── Toast ────────────────────────────────────────────────────
  function showToast(message, type) {
    var colors = { success: '#16a34a', error: '#dc2626' };
    var t = document.createElement('div');
    t.style.cssText = 'position:fixed;bottom:24px;right:24px;z-index:9999;padding:12px 20px;border-radius:12px;'
      + 'background:' + (colors[type] || '#374151') + ';color:#fff;font-size:14px;font-weight:600;'
      + 'box-shadow:0 4px 24px rgba(0,0,0,0.18);transition:opacity 0.3s;';
    t.textContent = message;
    document.body.appendChild(t);
    setTimeout(function() { t.style.opacity = '0'; setTimeout(function() { t.remove(); }, 350); }, 2800);
  }

  // ─── HTML escape helper ───────────────────────────────────────
  function escHtml(str) {
    return String(str)
      .replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;')
      .replace(/"/g,'&quot;').replace(/'/g,'&#039;');
  }

});
</script>

<script src="{{ asset('js/admin/globalManagement/bidManagement.js') }}" defer></script>
</body>
</html>