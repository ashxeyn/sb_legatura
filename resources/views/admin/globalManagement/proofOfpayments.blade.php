<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <title>Admin Dashboard - Legatura</title>

  <script src="https://cdn.tailwindcss.com"></script>
  <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.js"></script>
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

    .action-btn--approve {
      background: #f0fdf4 !important;
      color: #16a34a !important;
      border-color: #bbf7d0 !important;
    }
    .action-btn--approve:hover { background: #dcfce7 !important; }

    .action-btn--reject {
      background: #fffbeb !important;
      color: #d97706 !important;
      border-color: #fde68a !important;
    }
    .action-btn--reject:hover { background: #fef3c7 !important; }
  </style>
</head>

<body class="bg-gray-50 text-gray-800 font-sans">
  <div class="flex min-h-screen">

    {{-- ═══════════════════════════════ SIDEBAR ═══════════════════════════════ --}}
    <aside class="bg-white shadow-xl flex flex-col">

      <div class="flex justify-center items-center">
        <img src="{{ asset('img/logo.svg') }}" alt="Legatura Logo" class="logo-img">
      </div>

      <nav class="flex-1 px-3 py-4 space-y-1">
        <div class="nav-group">
          <button class="nav-btn">
            <div class="flex items-center gap-3">
              <i class="fi fi-ss-home" style="font-size: 20px;"></i>
              <span>Home</span>
            </div>
            <span class="arrow">▼</span>
          </button>
          <div class="nav-submenu">
            <a href="{{ route('admin.dashboard') }}" class="submenu-link">Dashboard</a>
            <div class="submenu-nested">
              <button class="submenu-link submenu-nested-btn">
                <span>Analytics</span>
                <span class="arrow-small">▼</span>
              </button>
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
            <div class="flex items-center gap-3">
              <i class="fi fi-ss-users-alt" style="font-size: 20px;"></i>
              <span>User Management</span>
            </div>
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
            <div class="flex items-center gap-3">
              <i class="fi fi-ss-globe" style="font-size: 20px;"></i>
              <span>Global Management</span>
            </div>
            <span class="arrow">▼</span>
          </button>
          <div class="nav-submenu">
            <a href="{{ route('admin.globalManagement.bidManagement') }}" class="submenu-link">Bid Management</a>
            <a href="{{ route('admin.globalManagement.proofOfpayments') }}" class="submenu-link active">Proof of Payments</a>
            <a href="{{ route('admin.globalManagement.aiManagement') }}" class="submenu-link">AI Management</a>
            <a href="{{ route('admin.globalManagement.postingManagement') }}" class="submenu-link">Posting Management</a>
          </div>
        </div>

        <div class="nav-group">
          <button class="nav-btn">
            <div class="flex items-center gap-3">
              <i class="fi fi-sr-master-plan" style="font-size: 20px;"></i>
              <span>Project Management</span>
            </div>
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
            <div class="flex items-center gap-3">
              <i class="fi fi-br-settings-sliders" style="font-size: 20px;"></i>
              <span>Settings</span>
            </div>
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
                <div class="text-sm font-semibold truncate">Emmanuelle Santos</div>
                <div class="text-xs text-gray-500 truncate">santos@Legatura.com</div>
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

    {{-- ═══════════════════════════════ MAIN ═══════════════════════════════ --}}
    <main class="flex-1">

      {{-- Header --}}
      <header class="bg-white shadow-sm border-b border-gray-200 flex items-center justify-between px-8 py-4 sticky top-0 z-30">
        <h1 class="text-2xl font-semibold text-gray-800">Proof of Payments</h1>

        <div class="flex items-center gap-6">
          <form class="flex items-center gap-2" method="GET" action="{{ route('admin.globalManagement.proofOfpayments') }}" style="width: 600px;">
            <div class="relative flex-1">
              <input
                type="text"
                name="search"
                value="{{ request('search') }}"
                placeholder="Search project or contractor..."
                class="border border-gray-300 rounded-lg px-4 py-2 pr-10 focus:ring-2 focus:ring-indigo-400 focus:outline-none w-full"
              >
              <i class="fi fi-rr-search absolute right-3 top-1/2 transform -translate-y-1/2 text-gray-400"></i>
            </div>
            <select name="status" class="border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-400 focus:outline-none bg-white">
              <option value="">All Status</option>
              <option value="submitted" {{ request('status') === 'submitted' ? 'selected' : '' }}>Pending</option>
              <option value="approved"  {{ request('status') === 'approved'  ? 'selected' : '' }}>Approved</option>
              <option value="rejected"  {{ request('status') === 'rejected'  ? 'selected' : '' }}>Rejected</option>
            </select>
            <button type="submit" class="px-4 py-2 bg-indigo-600 text-white rounded-lg text-sm font-medium hover:bg-indigo-700 transition">Filter</button>
            @if(request('search') || request('status'))
              <a href="{{ route('admin.globalManagement.proofOfpayments') }}"
                 class="px-3 py-2 border border-gray-300 text-gray-600 rounded-lg text-sm hover:bg-gray-50 transition">Clear</a>
            @endif
          </form>

          <div class="relative">
            <button id="notificationBell" class="cursor-pointer w-10 h-10 flex items-center justify-center rounded-full hover:bg-gray-100 transition">
              <i class="fi fi-ss-bell-notification-social-media" style="font-size: 20px;"></i>
            </button>
            <span class="absolute -top-1 -right-2 bg-red-500 text-white text-xs rounded-full w-5 h-5 flex items-center justify-center">3</span>

            <div id="notificationDropdown" class="absolute right-0 mt-3 w-80 bg-white rounded-xl shadow-2xl border border-gray-200 hidden z-50">
              <div class="px-4 py-3 border-b border-gray-100 flex items-center justify-between">
                <span class="text-sm font-semibold text-gray-800">Notifications</span>
                <button id="clearNotifications" class="text-xs text-indigo-600 hover:text-indigo-700">Clear all</button>
              </div>
              <ul class="max-h-80 overflow-y-auto" id="notificationList">
                <li class="px-4 py-3 hover:bg-gray-50 transition">
                  <div class="flex items-start gap-3">
                    <div class="w-8 h-8 rounded-full bg-indigo-100 text-indigo-600 flex items-center justify-center"><i class="fi fi-ss-bell"></i></div>
                    <div class="flex-1 min-w-0">
                      <p class="text-sm text-gray-800 truncate">New bid submitted on "GreenBelt Building".</p>
                      <p class="text-xs text-gray-500">2 mins ago</p>
                    </div>
                    <span class="inline-block px-2 py-1 text-xs rounded-full bg-yellow-100 text-yellow-700">New</span>
                  </div>
                </li>
                <li class="px-4 py-3 hover:bg-gray-50 transition">
                  <div class="flex items-start gap-3">
                    <div class="w-8 h-8 rounded-full bg-green-100 text-green-700 flex items-center justify-center"><i class="fi fi-ss-check-circle"></i></div>
                    <div class="flex-1 min-w-0">
                      <p class="text-sm text-gray-800 truncate">Verification request approved for Cabonting Architects.</p>
                      <p class="text-xs text-gray-500">1 hour ago</p>
                    </div>
                  </div>
                </li>
                <li class="px-4 py-3 hover:bg-gray-50 transition">
                  <div class="flex items-start gap-3">
                    <div class="w-8 h-8 rounded-full bg-red-100 text-red-700 flex items-center justify-center"><i class="fi fi-ss-exclamation"></i></div>
                    <div class="flex-1 min-w-0">
                      <p class="text-sm text-gray-800 truncate">High-risk flag: Duplex Housing requires review.</p>
                      <p class="text-xs text-gray-500">Yesterday</p>
                    </div>
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

        {{-- ═══ Statistics Cards ═══ --}}
        @php
          $totalStat     = $stats['total']     ?? 0;
          $pendingStat   = $stats['pending']   ?? 0;
          $failedStat    = $stats['failed']    ?? 0;
          $completedStat = $stats['completed'] ?? 0;

          $pendingPct   = $totalStat > 0 ? min(100, round(($pendingStat   / $totalStat) * 100)) : 0;
          $failedPct    = $totalStat > 0 ? min(100, round(($failedStat    / $totalStat) * 100)) : 0;
          $completedPct = $totalStat > 0 ? min(100, round(($completedStat / $totalStat) * 100)) : 0;
        @endphp

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">

          <div class="stat-card bg-white rounded-xl shadow-md p-6 hover:shadow-xl transition-all duration-300 border-l-4 border-blue-500">
            <div class="flex justify-between items-start mb-4">
              <div>
                <p class="text-gray-500 text-sm font-medium mb-2">Total Proof Collected</p>
                <div class="flex items-center gap-2 mb-1">
                  <h2 class="text-4xl font-bold text-orange-500 stat-number">{{ number_format($totalStat) }}</h2>
                </div>
                <div class="flex items-center gap-1.5 mt-2">
                  <div class="flex items-center gap-1 px-2 py-0.5 rounded-full bg-blue-100">
                    <i class="fi fi-sr-database text-blue-600 text-xs"></i>
                    <span class="text-blue-600 text-xs font-semibold">All records</span>
                  </div>
                </div>
              </div>
              <div class="bg-blue-100 p-3 rounded-lg">
                <i class="fi fi-sr-document text-blue-600 text-2xl"></i>
              </div>
            </div>
            <p class="text-xs text-gray-400">All time</p>
            <div class="mt-4 h-1 bg-gray-200 rounded-full overflow-hidden">
              <div class="h-full bg-blue-500 rounded-full animate-pulse" style="width: 100%"></div>
            </div>
          </div>

          <div class="stat-card bg-white rounded-xl shadow-md p-6 hover:shadow-xl transition-all duration-300 border-l-4 border-orange-500">
            <div class="flex justify-between items-start mb-4">
              <div>
                <p class="text-gray-500 text-sm font-medium mb-2">Pending Verifications</p>
                <div class="flex items-center gap-2 mb-1">
                  <h2 class="text-4xl font-bold text-orange-500 stat-number">{{ number_format($pendingStat) }}</h2>
                </div>
                <div class="flex items-center gap-1.5 mt-2">
                  <div class="flex items-center gap-1 px-2 py-0.5 rounded-full bg-orange-100">
                    <i class="fi fi-sr-time-check text-orange-600 text-xs"></i>
                    <span class="text-orange-600 text-xs font-semibold">Awaiting review</span>
                  </div>
                </div>
              </div>
              <div class="bg-orange-100 p-3 rounded-lg">
                <i class="fi fi-sr-time-check text-orange-600 text-2xl"></i>
              </div>
            </div>
            <p class="text-xs text-gray-400">Submitted status</p>
            <div class="mt-4 h-1 bg-gray-200 rounded-full overflow-hidden">
              <div class="h-full bg-orange-500 rounded-full animate-pulse" style="width: {{ $pendingPct }}%"></div>
            </div>
          </div>

          <div class="stat-card bg-white rounded-xl shadow-md p-6 hover:shadow-xl transition-all duration-300 border-l-4 border-red-500">
            <div class="flex justify-between items-start mb-4">
              <div>
                <p class="text-gray-500 text-sm font-medium mb-2">Failed Transaction</p>
                <div class="flex items-center gap-2 mb-1">
                  <h2 class="text-4xl font-bold text-orange-500 stat-number">{{ number_format($failedStat) }}</h2>
                </div>
                <div class="flex items-center gap-1.5 mt-2">
                  <div class="flex items-center gap-1 px-2 py-0.5 rounded-full bg-red-100">
                    <i class="fi fi-sr-cross-circle text-red-600 text-xs"></i>
                    <span class="text-red-600 text-xs font-semibold">Marked invalid</span>
                  </div>
                </div>
              </div>
              <div class="bg-red-100 p-3 rounded-lg">
                <i class="fi fi-sr-cross-circle text-red-600 text-2xl"></i>
              </div>
            </div>
            <p class="text-xs text-gray-400">Rejected status</p>
            <div class="mt-4 h-1 bg-gray-200 rounded-full overflow-hidden">
              <div class="h-full bg-red-500 rounded-full animate-pulse" style="width: {{ $failedPct }}%"></div>
            </div>
          </div>

          <div class="stat-card bg-white rounded-xl shadow-md p-6 hover:shadow-xl transition-all duration-300 border-l-4 border-green-500">
            <div class="flex justify-between items-start mb-4">
              <div>
                <p class="text-gray-500 text-sm font-medium mb-2">Completed Transactions</p>
                <div class="flex items-center gap-2 mb-1">
                  <h2 class="text-4xl font-bold text-orange-500 stat-number">{{ number_format($completedStat) }}</h2>
                </div>
                <div class="flex items-center gap-1.5 mt-2">
                  <div class="flex items-center gap-1 px-2 py-0.5 rounded-full bg-green-100">
                    <i class="fi fi-sr-check-circle text-green-600 text-xs"></i>
                    <span class="text-green-600 text-xs font-semibold">Approved payments</span>
                  </div>
                </div>
              </div>
              <div class="bg-green-100 p-3 rounded-lg">
                <i class="fi fi-sr-check-circle text-green-600 text-2xl"></i>
              </div>
            </div>
            <p class="text-xs text-gray-400">Approved status</p>
            <div class="mt-4 h-1 bg-gray-200 rounded-full overflow-hidden">
              <div class="h-full bg-green-500 rounded-full animate-pulse" style="width: {{ $completedPct }}%"></div>
            </div>
          </div>

        </div>{{-- /stats --}}

        {{-- ═══ Payments Table ═══ --}}
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
          <div class="overflow-x-auto">
            <table class="w-full">
              <thead>
                <tr class="bg-gradient-to-r from-indigo-50 to-blue-50 border-b border-gray-200">
                  <th class="px-3 py-3 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider w-20">ID</th>
                  <th class="px-3 py-3 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">Project</th>
                  <th class="px-3 py-3 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">Contractor</th>
                  <th class="px-3 py-3 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider w-28">Amount</th>
                  <th class="px-3 py-3 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider w-24">Date</th>
                  <th class="px-3 py-3 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider w-20">Method</th>
                  <th class="px-3 py-3 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider w-24">Status</th>
                  <th class="px-3 py-3 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider w-28">Action</th>
                </tr>
              </thead>
              <tbody class="divide-y divide-gray-200" id="paymentsTable">

                @forelse($payments as $payment)
                @php
                  $words    = array_values(array_filter(explode(' ', trim($payment->company_name ?? ''))));
                  $initials = strtoupper(substr($words[0] ?? '?', 0, 1) . substr($words[1] ?? '', 0, 1)) ?: '?';

                  $palette = [
                    'from-blue-500 to-indigo-600',
                    'from-gray-700 to-gray-900',
                    'from-emerald-500 to-teal-600',
                    'from-fuchsia-500 to-purple-600',
                    'from-rose-500 to-red-600',
                    'from-amber-500 to-orange-600',
                    'from-cyan-500 to-sky-600',
                    'from-lime-600 to-green-700',
                  ];
                  $avatarColor = $palette[$payment->payment_id % count($palette)];

                  $statusConfig = [
                    'submitted' => ['label' => 'Pending',   'cls' => 'bg-amber-100 text-amber-700 border-amber-200'],
                    'approved'  => ['label' => 'Completed', 'cls' => 'bg-green-100 text-green-700 border-green-200'],
                    'rejected'  => ['label' => 'Invalid',   'cls' => 'bg-red-100   text-red-700   border-red-200'],
                    'deleted'   => ['label' => 'Deleted',   'cls' => 'bg-gray-100  text-gray-500  border-gray-200'],
                  ];
                  $sc = $statusConfig[$payment->payment_status]
                      ?? ['label' => ucfirst($payment->payment_status), 'cls' => 'bg-gray-100 text-gray-600 border-gray-200'];

                  $methodLabels = [
                    'cash'           => 'Cash',
                    'check'          => 'Check',
                    'bank_transfer'  => 'Bank',
                    'online_payment' => 'Online',
                  ];
                  $methodLabel = $methodLabels[$payment->payment_type]
                               ?? ucfirst(str_replace('_', ' ', $payment->payment_type ?? ''));
                @endphp

                <tr class="hover:bg-indigo-50/60 transition-colors"
                    data-id="{{ $payment->payment_id }}"
                    data-status="{{ $payment->payment_status }}">

                  <td class="px-3 py-3 whitespace-nowrap text-gray-700 font-medium text-sm">
                    #{{ $payment->payment_id }}
                  </td>

                  <td class="px-3 py-3 text-gray-700 text-sm max-w-[160px]">
                    <span class="block truncate" title="{{ $payment->project_title }}">
                      {{ $payment->project_title ?? '—' }}
                    </span>
                  </td>

                  <td class="px-3 py-3">
                    <div class="flex items-center gap-2">
                      <div class="w-8 h-8 rounded-full bg-gradient-to-br {{ $avatarColor }} flex items-center justify-center text-white text-xs font-bold shadow flex-shrink-0">
                        {{ $initials }}
                      </div>
                      <div class="min-w-0">
                        <div class="font-semibold text-gray-800 leading-tight text-sm truncate max-w-[140px]"
                             title="{{ $payment->company_name }}">
                          {{ $payment->company_name ?? '—' }}
                        </div>
                      </div>
                    </div>
                  </td>

                  <td class="px-3 py-3 whitespace-nowrap text-gray-700 font-semibold text-sm">
                    ₱{{ number_format($payment->amount, 2) }}
                  </td>

                  <td class="px-3 py-3 whitespace-nowrap text-gray-700 text-xs">
                    {{ \Carbon\Carbon::parse($payment->payment_date)->format('m/d/y') }}
                  </td>

                  <td class="px-3 py-3 whitespace-nowrap text-gray-700 text-xs">
                    {{ $methodLabel }}
                  </td>

                  <td class="px-3 py-3 whitespace-nowrap">
                    <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold border {{ $sc['cls'] }}">
                      {{ $sc['label'] }}
                    </span>
                  </td>

                  {{--
                    FIX: pass ALL editable fields as data-* attributes so the edit modal
                    can pre-fill every field without a separate AJAX call.
                    Added: data-txn (transaction_number), data-amount, data-method, data-status,
                    data-project. data-reason is new for the remarks field.
                  --}}
                  <td class="px-3 py-3 whitespace-nowrap">
                    <div class="flex items-center gap-1.5">
                      <button class="action-btn action-btn--view rounded-2xl btn-view"
                              title="View"
                              data-id="{{ $payment->payment_id }}"
                              data-status="{{ $payment->payment_status }}">
                        <i class="fi fi-rr-eye"></i>
                      </button>
                      <button class="action-btn action-btn--edit rounded-2xl btn-edit"
                              title="Edit"
                              data-id="{{ $payment->payment_id }}"
                              data-project="{{ $payment->project_title }}"
                              data-method="{{ $payment->payment_type }}"
                              data-amount="{{ $payment->amount }}"
                              data-status="{{ $payment->payment_status }}"
                              data-txn="{{ $payment->transaction_number }}"
                              data-reason="{{ $payment->reason }}">
                        <i class="fi fi-rr-edit"></i>
                      </button>
                      <button class="action-btn action-btn--delete rounded-2xl btn-delete"
                              title="Delete"
                              data-id="{{ $payment->payment_id }}"
                              data-project="{{ $payment->project_title }}"
                              data-contractor="{{ $payment->company_name }}"
                              data-amount="₱{{ number_format($payment->amount, 2) }}">
                        <i class="fi fi-rr-trash"></i>
                      </button>
                    </div>
                  </td>

                </tr>

                @empty
                <tr>
                  <td colspan="8" class="px-6 py-16 text-center text-gray-400">
                    <i class="fi fi-sr-document text-4xl block mb-3"></i>
                    <p class="text-lg font-medium text-gray-500">No payment proofs found</p>
                    <p class="text-sm mt-1">
                      @if(request('search') || request('status'))
                        Try adjusting your search or filter criteria.
                      @else
                        No payment proofs have been submitted yet.
                      @endif
                    </p>
                  </td>
                </tr>
                @endforelse

              </tbody>
            </table>
          </div>

          @if($payments->hasPages())
          <div class="px-6 py-4 border-t border-gray-200 flex items-center justify-between flex-wrap gap-3">
            <p class="text-sm text-gray-500">
              Showing <strong>{{ $payments->firstItem() }}</strong>–<strong>{{ $payments->lastItem() }}</strong>
              of <strong>{{ $payments->total() }}</strong> results
            </p>
            <div class="flex items-center gap-1">
              @if($payments->onFirstPage())
                <span class="px-3 py-1.5 rounded-lg text-sm text-gray-400 border border-gray-200 cursor-not-allowed">‹ Prev</span>
              @else
                <a href="{{ $payments->previousPageUrl() }}&{{ http_build_query(request()->except('page')) }}"
                   class="px-3 py-1.5 rounded-lg text-sm border border-gray-200 hover:bg-gray-50 transition">‹ Prev</a>
              @endif

              @foreach($payments->getUrlRange(max(1, $payments->currentPage()-2), min($payments->lastPage(), $payments->currentPage()+2)) as $page => $url)
                @if($page == $payments->currentPage())
                  <span class="px-3 py-1.5 rounded-lg text-sm bg-indigo-600 text-white font-semibold">{{ $page }}</span>
                @else
                  <a href="{{ $url }}&{{ http_build_query(request()->except('page')) }}"
                     class="px-3 py-1.5 rounded-lg text-sm border border-gray-200 hover:bg-gray-50 transition">{{ $page }}</a>
                @endif
              @endforeach

              @if($payments->hasMorePages())
                <a href="{{ $payments->nextPageUrl() }}&{{ http_build_query(request()->except('page')) }}"
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

    {{-- ═══════════════════════════════════════════════════════════
         MODALS
    ═══════════════════════════════════════════════════════════ --}}

    {{-- ── 1. PENDING PAYMENT MODAL ── --}}
    <div id="pendingPaymentModal" class="fixed inset-0 z-[100] hidden items-center justify-center">
      <div class="absolute inset-0 bg-black/40 backdrop-blur-sm transition-opacity"></div>
      <div class="relative bg-white w-full max-w-5xl mx-4 rounded-2xl shadow-2xl border border-gray-200 overflow-hidden">
        <div class="flex items-center justify-between px-7 py-5 bg-gradient-to-r from-indigo-50 via-blue-50 to-cyan-50 border-b">
          <div class="flex items-center gap-3">
            <div class="w-11 h-11 rounded-xl bg-gradient-to-br from-indigo-500 to-blue-600 flex items-center justify-center shadow">
              <i class="fi fi-ss-bolt text-white text-lg"></i>
            </div>
            <div>
              <h3 class="text-lg font-bold text-gray-800">Proof of Payment (Pending)</h3>
              <p class="text-xs text-gray-500">Awaiting verification</p>
            </div>
          </div>
          <button data-close-modal class="p-2 rounded-xl hover:bg-white/80 text-gray-500 hover:text-gray-700 transition">
            <i class="fi fi-rr-cross-small text-xl"></i>
          </button>
        </div>

        <div id="pp-loading" class="py-14 text-center text-gray-400">
          <i class="fi fi-rr-spinner text-3xl fi-spin block mb-3"></i>
          <p class="text-sm">Loading payment details…</p>
        </div>

        <div id="pp-body" class="p-7 space-y-6 max-h-[72vh] overflow-y-auto hidden">
          <div class="flex items-center gap-3">
            <span id="pp-status" class="inline-flex items-center px-3 py-1.5 rounded-full text-xs font-semibold bg-amber-100 text-amber-700 border border-amber-200">Pending</span>
            <span class="text-xs text-gray-500">Submitted and under review</span>
          </div>

          <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
            <div class="space-y-3 text-sm">
              <div class="flex items-start justify-between gap-6">
                <span class="text-gray-500">Payment ID</span>
                <span id="pp-payment-id" class="font-semibold text-gray-800">#—</span>
              </div>
              <div class="flex items-start justify-between gap-6">
                <span class="text-gray-500">Contractor</span>
                <span id="pp-contractor" class="font-semibold text-gray-800 text-right truncate max-w-[240px]">—</span>
              </div>
              <div class="flex items-start justify-between gap-6">
                <span class="text-gray-500">Milestone Paid</span>
                <span id="pp-milestone" class="text-gray-800">—</span>
              </div>
              <div class="flex items-start justify-between gap-6">
                <span class="text-gray-500">Payment Reference No.</span>
                <span id="pp-reference" class="text-gray-800">—</span>
              </div>
              <div class="flex items-start justify-between gap-6">
                <span class="text-gray-500">Payment Date</span>
                <span id="pp-date" class="text-gray-800">—</span>
              </div>
              <div class="flex items-start justify-between gap-6">
                <span class="text-gray-500">Payment Method</span>
                <span id="pp-method" class="text-gray-800">—</span>
              </div>
              <div class="flex items-start justify-between gap-6">
                <span class="text-gray-500">Amount Paid</span>
                <span id="pp-amount" class="text-gray-800 font-semibold">—</span>
              </div>
              <div class="flex items-start justify-between gap-6">
                <span class="text-gray-500">Date Verified</span>
                <span class="text-gray-400">—</span>
              </div>
            </div>

            <div class="space-y-3 text-sm">
              <div class="flex items-start justify-between gap-6">
                <span class="text-gray-500">Property Owner</span>
                <span id="pp-owner" class="text-gray-800">—</span>
              </div>
              <div class="flex items-start justify-between gap-6">
                <span class="text-gray-500">Project Title</span>
                <span id="pp-project" class="text-gray-800 text-right truncate max-w-[260px]">—</span>
              </div>
              <div class="flex flex-col gap-2">
                <span class="text-gray-500">Description</span>
                <p id="pp-description" class="text-gray-700 leading-relaxed text-justify">—</p>
              </div>
            </div>
          </div>

          <div id="pp-files-section" class="pt-2 hidden">
            <div class="flex items-center justify-between">
              <h4 class="font-semibold text-gray-800">Uploaded Files</h4>
              <span class="text-xs text-gray-500">Preview or download</span>
            </div>
            <div class="mt-3 border-t"></div>
            <div class="mt-4 space-y-3" id="pp-files"></div>
          </div>
        </div>

        <div class="flex items-center justify-end gap-3 px-6 py-4 bg-gray-50 border-t">
          <button data-close-modal class="px-4 py-2 rounded-lg border border-gray-300 text-gray-700 hover:bg-gray-100 transition text-sm">Close</button>
          <button id="pp-reject" class="px-4 py-2 rounded-lg bg-red-50 text-red-600 border border-red-200 hover:bg-red-100 hover:border-red-300 transition text-sm">Reject</button>
          <button id="pp-approve" class="px-4 py-2 rounded-lg bg-green-50 text-green-600 border border-green-200 hover:bg-green-100 hover:border-green-300 transition text-sm">Approve</button>
        </div>
      </div>
    </div>

    {{-- ── 2. APPROVE CONFIRMATION MODAL ── --}}
    <div id="confirmApproveModal" class="fixed inset-0 z-[110] hidden items-center justify-center">
      <div class="absolute inset-0 bg-black/40 backdrop-blur-sm"></div>
      <div class="relative bg-white w-full max-w-md mx-4 rounded-2xl shadow-2xl border border-gray-200 overflow-hidden modal-enter">
        <div class="px-8 pt-8 pb-6 text-center">
          <div class="confirm-hero bg-amber-100 text-amber-600 mx-auto mb-4"><i class="fi fi-ss-question"></i></div>
          <h4 class="text-2xl font-semibold text-gray-900">Approve Payment?</h4>
          <p class="mt-2 text-gray-600">Are you sure you want to approve this proof of payment?</p>
          <p class="text-gray-600">This action will update the payment status to <strong>Approved</strong>.</p>
          <p class="mt-2 text-xs text-gray-500">Reference: <span id="approveSummary" class="font-medium text-gray-700">—</span></p>
        </div>
        <div class="px-8 pb-8 flex items-center justify-center gap-4">
          <button data-close-modal class="btn-outline-neutral">Cancel</button>
          <button id="confirmApproveBtn" class="btn-gradient-orange">Yes, Approve</button>
        </div>
      </div>
    </div>

    {{-- ── REJECT CONFIRMATION MODAL ── --}}
    <div id="confirmRejectModal" class="fixed inset-0 z-[110] hidden items-center justify-center">
      <div class="absolute inset-0 bg-black/40 backdrop-blur-sm"></div>
      <div class="relative bg-white w-full max-w-md mx-4 rounded-2xl shadow-2xl border border-gray-200 overflow-hidden modal-enter">
        <div class="px-8 pt-8 pb-6 text-center">
          <div class="confirm-hero bg-rose-100 text-rose-600 mx-auto mb-4"><i class="fi fi-ss-question"></i></div>
          <h4 class="text-2xl font-semibold text-gray-900">Reject Payment?</h4>
          <p class="mt-2 text-gray-600">Are you sure you want to reject this proof of payment?</p>
          <p class="text-gray-600">This action will mark the proof as <strong>Invalid</strong>.</p>
          <p class="mt-2 text-xs text-gray-500">Reference: <span id="rejectSummary" class="font-medium text-gray-700">—</span></p>
          <div class="mt-4 text-left">
            <label class="block text-sm font-medium text-gray-600 mb-1">Reason <span class="text-gray-400 font-normal">(optional)</span></label>
            <textarea id="rejectReasonInput" rows="2"
              class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:ring-2 focus:ring-rose-400 focus:outline-none resize-none"
              placeholder="Enter reason for rejection…"></textarea>
          </div>
        </div>
        <div class="px-8 pb-8 flex items-center justify-center gap-4">
          <button data-close-modal class="btn-outline-neutral">Cancel</button>
          <button id="confirmRejectBtn" class="btn-gradient-rose">Yes, Reject</button>
        </div>
      </div>
    </div>

    {{-- ── COMPLETED / APPROVED PAYMENT MODAL ── --}}
    <div id="completedPaymentModal" class="fixed inset-0 z-[100] hidden items-center justify-center">
      <div class="absolute inset-0 bg-black/40 backdrop-blur-sm transition-opacity completed-backdrop"></div>
      <div class="relative bg-white w-full max-w-6xl mx-4 rounded-2xl shadow-2xl border border-gray-200 overflow-hidden h-[92vh] flex flex-col completed-modal-panel">
        <div class="flex items-center justify-between px-8 py-5 bg-gradient-to-r from-emerald-50 via-green-50 to-teal-50 border-b flex-shrink-0">
          <div class="flex items-center gap-4">
            <div class="w-12 h-12 rounded-xl bg-gradient-to-br from-emerald-500 to-teal-600 flex items-center justify-center shadow completed-icon-badge">
              <i class="fi fi-sr-check-circle text-white text-xl"></i>
            </div>
            <div>
              <h3 class="text-lg font-bold text-gray-800">Proof of Payment (Completed)</h3>
              <p class="text-xs text-gray-500">Transaction has been verified</p>
            </div>
          </div>
          <button data-close-modal class="p-2 rounded-xl hover:bg-white/80 text-gray-500 hover:text-gray-700 transition completed-close-btn">
            <i class="fi fi-rr-cross-small text-xl"></i>
          </button>
        </div>

        <div id="cp-loading" class="py-14 text-center text-gray-400 flex-shrink-0">
          <i class="fi fi-rr-spinner text-3xl fi-spin block mb-3"></i>
          <p class="text-sm">Loading payment details…</p>
        </div>

        <div id="cp-body" class="flex-1 overflow-y-auto p-8 space-y-6 completed-scrollbar hidden">
          <div class="rounded-xl border border-emerald-200 bg-gradient-to-r from-emerald-50 to-teal-50 p-5">
            <div class="flex items-center gap-3">
              <span class="inline-flex items-center px-3 py-1.5 rounded-full text-xs font-semibold bg-green-100 text-green-700 border border-green-200">Approved</span>
              <span class="text-xs text-gray-500">Verified and recorded</span>
            </div>
          </div>

          <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
            <div class="space-y-3 text-sm">
              <div class="flex items-start justify-between gap-6"><span class="text-gray-500">Payment ID</span><span id="cp-payment-id" class="font-semibold text-gray-800">#—</span></div>
              <div class="flex items-start justify-between gap-6"><span class="text-gray-500">Contractor</span><span id="cp-contractor" class="font-semibold text-gray-800 text-right truncate max-w-[240px]">—</span></div>
              <div class="flex items-start justify-between gap-6"><span class="text-gray-500">Milestone Paid</span><span id="cp-milestone" class="text-gray-800">—</span></div>
              <div class="flex items-start justify-between gap-6"><span class="text-gray-500">Payment Reference No.</span><span id="cp-reference" class="text-gray-800">—</span></div>
              <div class="flex items-start justify-between gap-6"><span class="text-gray-500">Payment Date</span><span id="cp-date" class="text-gray-800">—</span></div>
              <div class="flex items-start justify-between gap-6"><span class="text-gray-500">Payment Method</span><span id="cp-method" class="text-gray-800">—</span></div>
              <div class="flex items-start justify-between gap-6"><span class="text-gray-500">Amount Paid</span><span id="cp-amount" class="text-gray-800 font-semibold">—</span></div>
              <div class="flex items-start justify-between gap-6"><span class="text-gray-500">Date Verified</span><span id="cp-verified" class="text-gray-800">—</span></div>
            </div>
            <div class="space-y-3 text-sm">
              <div class="flex items-start justify-between gap-6"><span class="text-gray-500">Property Owner</span><span id="cp-owner" class="text-gray-800">—</span></div>
              <div class="flex items-start justify-between gap-6"><span class="text-gray-500">Project Title</span><span id="cp-project" class="text-gray-800 text-right truncate max-w-[260px]">—</span></div>
              <div class="flex flex-col gap-2">
                <span class="text-gray-500">Description</span>
                <p id="cp-description" class="text-gray-700 leading-relaxed text-justify">—</p>
              </div>
            </div>
          </div>

          <div id="cp-files-section" class="hidden">
            <h4 class="font-semibold text-gray-800 mb-3">Uploaded Files</h4>
            <div class="rounded-xl border border-gray-200 overflow-hidden">
              <div class="grid grid-cols-12 bg-gray-50 text-xs font-semibold text-gray-600 px-4 py-2 sticky top-0 z-10">
                <div class="col-span-7">File</div>
                <div class="col-span-4">Uploaded</div>
                <div class="col-span-1 text-right"> </div>
              </div>
              <div class="divide-y" id="cp-files"></div>
            </div>
          </div>
        </div>
      </div>
    </div>

    {{-- ── EDIT PAYMENT MODAL ── --}}
    <div id="editPaymentModal" class="fixed inset-0 z-[100] hidden items-center justify-center">
      <div class="absolute inset-0 bg-black/40 backdrop-blur-sm transition-opacity"></div>
      <div class="relative bg-white w-full max-w-2xl mx-4 rounded-2xl shadow-2xl border border-gray-200 overflow-hidden modal-enter">
        <div class="flex items-center justify-between px-6 py-4 bg-gradient-to-r from-indigo-50 to-purple-50 border-b">
          <div class="flex items-center gap-2">
            <div class="w-8 h-8 rounded-lg bg-gradient-to-br from-indigo-500 to-purple-600 flex items-center justify-center">
              <i class="fi fi-rr-edit text-white text-sm"></i>
            </div>
            <h3 class="text-lg font-semibold text-gray-800">Edit Payment Details</h3>
          </div>
          <button data-close-modal class="p-2 rounded-full hover:bg-gray-100 text-gray-500 transition">
            <i class="fi fi-rr-cross-small text-xl"></i>
          </button>
        </div>

        <div class="p-6 space-y-5 max-h-[75vh] overflow-y-auto">
          <div class="bg-blue-50 border border-blue-200 rounded-lg p-3 flex items-start gap-2">
            <i class="fi fi-rr-info text-blue-600 text-sm mt-0.5"></i>
            <div class="text-xs text-blue-700">
              <p class="font-semibold">Edit payment record</p>
              <p class="text-blue-600">All fields below can be freely updated.</p>
            </div>
          </div>

          {{-- Payment Reference No. --}}
          <div class="form-group">
            <label class="form-label">Payment Reference No.</label>
            <input type="text" id="edit-reference" class="form-input" placeholder="Enter payment reference number">
          </div>

          {{-- Project Title (read-only — belongs to the project, not the payment) --}}
          <div class="form-group">
            <label class="form-label">Project Title</label>
            <input type="text" id="edit-project" class="form-input bg-gray-50 cursor-not-allowed" placeholder="Project title" readonly>
          </div>

          {{-- Payment Method --}}
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

          {{-- Amount Paid --}}
          <div class="form-group">
            <label class="form-label">Amount Paid</label>
            <div class="relative">
              <span class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-500 font-semibold">₱</span>
              <input type="number" step="0.01" min="0" id="edit-amount" class="form-input pl-8" placeholder="0.00">
            </div>
          </div>

          {{-- Status --}}
          <div class="form-group">
            <label class="form-label">Status</label>
            <select id="edit-status" class="form-input">
              <option value="submitted">Pending</option>
              <option value="approved">Completed</option>
              <option value="rejected">Invalid</option>
            </select>
          </div>

          {{-- Remarks / Reason --}}
          <div class="form-group">
            <label class="form-label">Remarks / Reason</label>
            <textarea id="edit-remarks" rows="3" class="form-input" placeholder="Add any additional notes or remarks..."></textarea>
          </div>

        </div>

        <div class="flex items-center justify-end gap-3 px-6 py-4 bg-gray-50 border-t">
          <button data-close-modal class="px-5 py-2.5 rounded-lg border border-gray-300 text-gray-700 font-medium hover:bg-gray-100 transition">Cancel</button>
          <button id="saveEditBtn" class="px-5 py-2.5 rounded-lg bg-gradient-to-r from-indigo-600 to-purple-600 text-white font-semibold hover:from-indigo-700 hover:to-purple-700 transition shadow-lg shadow-indigo-500/30">Save Changes</button>
        </div>
      </div>
    </div>

    {{-- ── INVALID / REJECTED PAYMENT MODAL ── --}}
    <div id="invalidPaymentModal" class="fixed inset-0 z-[100] hidden items-center justify-center">
      <div class="absolute inset-0 bg-black/40 backdrop-blur-sm transition-opacity"></div>
      <div class="relative bg-white w-full max-w-5xl mx-4 rounded-2xl shadow-2xl border border-gray-200 overflow-hidden">
        <div class="flex items-center justify-between px-7 py-5 bg-gradient-to-r from-rose-50 via-red-50 to-orange-50 border-b">
          <div class="flex items-center gap-3">
            <div class="w-11 h-11 rounded-xl bg-gradient-to-br from-rose-500 to-red-600 flex items-center justify-center shadow">
              <i class="fi fi-sr-triangle-warning text-white text-lg"></i>
            </div>
            <div>
              <h3 class="text-lg font-bold text-gray-800">Proof of Payment (Invalid)</h3>
              <p class="text-xs text-gray-500">Receipt flagged as invalid</p>
            </div>
          </div>
          <button data-close-modal class="p-2 rounded-xl hover:bg-white/80 text-gray-500 hover:text-gray-700 transition">
            <i class="fi fi-rr-cross-small text-xl"></i>
          </button>
        </div>

        <div id="ip-loading" class="py-14 text-center text-gray-400">
          <i class="fi fi-rr-spinner text-3xl fi-spin block mb-3"></i>
          <p class="text-sm">Loading payment details…</p>
        </div>

        <div id="ip-body" class="p-7 space-y-6 max-h-[72vh] overflow-y-auto hidden">
          <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div class="space-y-2">
              <span id="ip-status" class="inline-flex items-center px-3 py-1.5 rounded-full text-xs font-semibold bg-rose-100 text-rose-700 border border-rose-200">Invalid Receipt</span>
            </div>
            <div class="space-y-2">
              <label class="block text-sm text-gray-600">Remarks:</label>
              <textarea id="ip-remarks" rows="2"
                class="w-full rounded-lg border-gray-300 focus:ring-2 focus:ring-rose-400 focus:border-rose-400 text-sm bg-rose-50"
                readonly placeholder="No remarks provided."></textarea>
            </div>
          </div>

          <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
            <div class="space-y-3 text-sm">
              <div class="flex items-start justify-between gap-6"><span class="text-gray-500">Payment ID</span><span id="ip-payment-id" class="font-semibold text-gray-800">#—</span></div>
              <div class="flex items-start justify-between gap-6"><span class="text-gray-500">Contractor</span><span id="ip-contractor" class="font-semibold text-gray-800 text-right truncate max-w-[240px]">—</span></div>
              <div class="flex items-start justify-between gap-6"><span class="text-gray-500">Milestone Paid</span><span id="ip-milestone" class="text-gray-800">—</span></div>
              <div class="flex items-start justify-between gap-6"><span class="text-gray-500">Payment Reference No.</span><span id="ip-reference" class="text-gray-800">—</span></div>
              <div class="flex items-start justify-between gap-6"><span class="text-gray-500">Payment Date</span><span id="ip-date" class="text-gray-800">—</span></div>
              <div class="flex items-start justify-between gap-6"><span class="text-gray-500">Payment Method</span><span id="ip-method" class="text-gray-800">—</span></div>
              <div class="flex items-start justify-between gap-6"><span class="text-gray-500">Amount Paid</span><span id="ip-amount" class="text-gray-800 font-semibold">—</span></div>
              <div class="flex items-start justify-between gap-6"><span class="text-gray-500">Date Verified</span><span id="ip-verified" class="text-gray-800">—</span></div>
            </div>
            <div class="space-y-3 text-sm">
              <div class="flex items-start justify-between gap-6"><span class="text-gray-500">Property Owner</span><span id="ip-owner" class="text-gray-800">—</span></div>
              <div class="flex items-start justify-between gap-6"><span class="text-gray-500">Project Title</span><span id="ip-project" class="text-gray-800 text-right truncate max-w-[260px]">—</span></div>
              <div class="flex flex-col gap-2">
                <span class="text-gray-500">Description</span>
                <p id="ip-description" class="text-gray-700 leading-relaxed text-justify">—</p>
              </div>
            </div>
          </div>

          <div id="ip-files-section" class="hidden">
            <h4 class="font-semibold text-gray-800 mb-3">Uploaded Files</h4>
            <div class="rounded-xl border border-gray-200 overflow-hidden">
              <div class="grid grid-cols-12 bg-gray-50 text-xs font-semibold text-gray-600 px-4 py-2">
                <div class="col-span-7">File</div>
                <div class="col-span-4">Uploaded</div>
                <div class="col-span-1 text-right"> </div>
              </div>
              <div class="divide-y" id="ip-files"></div>
            </div>
          </div>
        </div>
      </div>
    </div>

    {{-- ── DELETE CONFIRMATION MODAL ── --}}
    <div id="deletePaymentModal" class="fixed inset-0 bg-black/60 backdrop-blur-sm hidden items-center justify-center z-[110] p-4">
      <div class="bg-white rounded-2xl shadow-2xl w-full max-w-lg transform transition-all scale-95 delete-modal-panel">
        <div class="p-6 sm:p-8">
          <div class="flex items-center justify-center w-20 h-20 rounded-full bg-gradient-to-br from-red-100 to-rose-100 mx-auto mb-5 delete-icon-container">
            <i class="fi fi-sr-triangle-warning text-red-600 text-4xl delete-warning-icon"></i>
          </div>
          <h3 class="text-2xl font-bold text-gray-800 text-center mb-3">Delete Payment?</h3>
          <p class="text-gray-600 text-center mb-6 leading-relaxed">
            Are you sure you want to permanently delete this proof of payment? This action cannot be undone and all associated data will be lost.
          </p>
          <div class="bg-gradient-to-br from-red-50 to-rose-50 border-2 border-red-200 rounded-xl p-5 mb-6">
            <div class="space-y-3">
              <div class="flex items-center justify-between text-sm">
                <span class="text-gray-600 font-medium">Payment ID:</span>
                <span id="delete-payment-id" class="font-bold text-gray-800">#—</span>
              </div>
              <div class="flex items-center justify-between text-sm">
                <span class="text-gray-600 font-medium">Project:</span>
                <span id="delete-project" class="font-bold text-gray-800">—</span>
              </div>
              <div class="flex items-center justify-between text-sm">
                <span class="text-gray-600 font-medium">Contractor:</span>
                <span id="delete-contractor" class="font-bold text-gray-800">—</span>
              </div>
              <div class="flex items-center justify-between text-sm">
                <span class="text-gray-600 font-medium">Amount:</span>
                <span id="delete-amount" class="font-bold text-red-600">—</span>
              </div>
            </div>
          </div>
          <div class="flex items-start gap-3 p-4 bg-red-50 border-l-4 border-red-500 rounded-lg mb-6">
            <i class="fi fi-rr-info text-red-600 text-lg mt-0.5"></i>
            <div>
              <p class="text-sm font-semibold text-red-800 mb-1">Warning: This action is irreversible!</p>
              <p class="text-xs text-red-700">All payment records, uploaded documents, and transaction history associated with this payment will be permanently removed from the system.</p>
            </div>
          </div>
          <div class="flex items-center gap-3">
            <button id="cancelDeletePaymentBtn"
              class="flex-1 px-5 py-3.5 rounded-xl border-2 border-gray-300 text-gray-700 font-semibold
                     hover:bg-gray-100 hover:border-gray-400 transition-all flex items-center justify-center gap-2">
              <i class="fi fi-rr-cross-small"></i><span>Cancel</span>
            </button>
            <button id="confirmDeletePaymentBtn"
              class="flex-1 px-5 py-3.5 rounded-xl bg-gradient-to-r from-red-600 to-rose-600 text-white
                     font-semibold hover:from-red-700 hover:to-rose-700 shadow-lg hover:shadow-xl
                     transition-all flex items-center justify-center gap-2 delete-confirm-btn">
              <i class="fi fi-rr-trash"></i><span>Delete</span>
            </button>
          </div>
        </div>
      </div>
    </div>

    {{-- Toast --}}
    <div id="toast" class="fixed bottom-6 right-6 z-[120] hidden">
      <div class="toast-card"></div>
    </div>

  </div>{{-- /flex min-h-screen --}}

  <script>
  (function () {
    'use strict';

    var CSRF = document.querySelector('meta[name="csrf-token"]').content;

    var METHOD_LABELS = {
      cash:           'Cash',
      check:          'Check',
      bank_transfer:  'Bank Transfer',
      online_payment: 'Online Payment'
    };

    /* ── Helpers ─────────────────────────────────────────────── */
    function esc(v) {
      if (v == null) return '—';
      return String(v)
        .replace(/&/g, '&amp;').replace(/</g, '&lt;')
        .replace(/>/g, '&gt;').replace(/"/g, '&quot;');
    }
    function peso(v) {
      return '₱' + Number(v).toLocaleString('en-PH', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
    }
    function fmtDate(s) {
      if (!s) return '—';
      try {
        return new Date(s).toLocaleDateString('en-PH', { month: 'short', day: 'numeric', year: 'numeric' });
      } catch (e) { return s; }
    }
    function setText(id, val) {
      var el = document.getElementById(id);
      if (el) el.textContent = val == null ? '—' : val;
    }
    function setVal(id, val) {
      var el = document.getElementById(id);
      if (el) el.value = val == null ? '' : val;
    }

    /* ── Modal open / close ─────────────────────────────────── */
    function openModal(id) {
      var el = document.getElementById(id);
      if (!el) return;
      el.classList.remove('hidden');
      el.classList.add('flex');
    }
    function closeModal(id) {
      var el = document.getElementById(id);
      if (!el) return;
      el.classList.add('hidden');
      el.classList.remove('flex');
    }

    document.addEventListener('click', function (e) {
      var btn = e.target.closest('[data-close-modal]');
      if (!btn) return;
      var modal = btn.closest('.fixed');
      if (modal) { modal.classList.add('hidden'); modal.classList.remove('flex'); }
    });

    /* ── Toast ──────────────────────────────────────────────── */
    var _toastTimer = null;
    function showToast(msg, type) {
      clearTimeout(_toastTimer);
      type = type || 'success';
      var wrap = document.getElementById('toast');
      var card = wrap.querySelector('.toast-card');
      var ok   = type === 'success';
      card.className = 'toast-card ' + (ok
        ? 'flex items-center gap-3 bg-green-50 border border-green-200 text-green-800 px-5 py-4 rounded-xl shadow-lg min-w-[260px]'
        : 'flex items-center gap-3 bg-red-50 border border-red-200 text-red-800 px-5 py-4 rounded-xl shadow-lg min-w-[260px]');
      card.innerHTML =
        '<i class="fi ' + (ok ? 'fi-sr-check-circle text-green-600' : 'fi-sr-cross-circle text-red-600') + ' text-xl flex-shrink-0"></i>' +
        '<span class="text-sm font-medium">' + esc(msg) + '</span>';
      wrap.classList.remove('hidden');
      _toastTimer = setTimeout(function () { wrap.classList.add('hidden'); }, 3500);
    }

    /* ── Fetch payment detail ───────────────────────────────── */
    function fetchDetail(id, callback) {
      fetch('/admin/global-management/proof-of-payments/' + id, {
        headers: { 'Accept': 'application/json' }
      })
        .then(function (r) { return r.json(); })
        .then(function (res) {
          if (!res.success) {
            showToast(res.message || 'Failed to load payment details.', 'error');
            return;
          }
          callback(res.data);
        })
        .catch(function () {
          showToast('Network error. Please try again.', 'error');
        });
    }

    /* ── File row builders ──────────────────────────────────── */
    function pendingFileRow(path) {
      var name = esc(path.split('/').pop());
      var ext  = name.split('.').pop().toUpperCase().slice(0, 4);
      var url  = '/storage/' + path;
      return '<div class="file-row">' +
        '<div class="flex items-center gap-3">' +
          '<span class="file-type">' + ext + '</span>' +
          '<span class="text-gray-800">' + name + '</span>' +
        '</div>' +
        '<div class="flex items-center gap-2">' +
          '<a href="' + esc(url) + '" target="_blank" class="icon-btn" title="Preview"><i class="fi fi-rr-eye"></i></a>' +
          '<a href="' + esc(url) + '" download class="icon-btn pending-download" title="Download"><i class="fi fi-rr-download"></i></a>' +
        '</div>' +
      '</div>';
    }

    function completedFileRow(path) {
      var name = esc(path.split('/').pop());
      var ext  = name.split('.').pop().toUpperCase().slice(0, 4);
      var url  = '/storage/' + path;
      return '<div class="grid grid-cols-12 items-center px-4 py-3 hover:bg-gray-50">' +
        '<div class="col-span-7 flex items-center gap-3">' +
          '<input type="checkbox" class="rounded border-gray-300 completed-checkbox">' +
          '<span class="file-type">' + ext + '</span>' +
          '<span class="text-gray-800">' + name + '</span>' +
        '</div>' +
        '<div class="col-span-4 text-sm text-gray-600">Receipt file</div>' +
        '<div class="col-span-1 flex justify-end">' +
          '<a href="' + esc(url) + '" download class="icon-btn completed-download-btn" title="Download"><i class="fi fi-rr-download"></i></a>' +
        '</div>' +
      '</div>';
    }

    function invalidFileRow(path) {
      var name = esc(path.split('/').pop());
      var ext  = name.split('.').pop().toUpperCase().slice(0, 4);
      var url  = '/storage/' + path;
      return '<div class="grid grid-cols-12 items-center px-4 py-3 hover:bg-gray-50">' +
        '<div class="col-span-7 flex items-center gap-3">' +
          '<input type="checkbox" class="form-checkbox rounded border-gray-300 invalid-checkbox">' +
          '<div class="w-8 h-8 rounded-lg bg-gray-100 flex items-center justify-center text-gray-600 text-xs font-bold">' + ext + '</div>' +
          '<div><div class="text-sm font-medium text-gray-800">' + name + '</div></div>' +
        '</div>' +
        '<div class="col-span-4 text-xs text-gray-600">Receipt file</div>' +
        '<div class="col-span-1 flex justify-end">' +
          '<a href="' + esc(url) + '" download class="icon-btn invalid-download" title="Download"><i class="fi fi-rr-download"></i></a>' +
        '</div>' +
      '</div>';
    }

    /* ── Populate modal helpers ─────────────────────────────── */
    function showLoading(loadingId, bodyId) {
      var l = document.getElementById(loadingId), b = document.getElementById(bodyId);
      if (l) l.classList.remove('hidden');
      if (b) b.classList.add('hidden');
    }
    function showBody(loadingId, bodyId) {
      var l = document.getElementById(loadingId), b = document.getElementById(bodyId);
      if (l) l.classList.add('hidden');
      if (b) b.classList.remove('hidden');
    }

    function populatePending(d) {
      setText('pp-payment-id', '#' + d.payment_id);
      setText('pp-contractor',  d.company_name);
      setText('pp-milestone',   d.milestone_item_title);
      setText('pp-reference',   d.transaction_number);
      setText('pp-date',        fmtDate(d.transaction_date));
      setText('pp-method',      METHOD_LABELS[d.payment_type] || d.payment_type);
      setText('pp-amount',      peso(d.amount));
      setText('pp-owner',       d.owner_name);
      setText('pp-project',     d.project_title);
      setText('pp-description', d.project_description);

      var fs = document.getElementById('pp-files-section'),
          fc = document.getElementById('pp-files');
      if (d.receipt_photo) {
        fc.innerHTML = pendingFileRow(d.receipt_photo);
        fs.classList.remove('hidden');
      } else { fs.classList.add('hidden'); }

      document.getElementById('pp-approve').dataset.id      = d.payment_id;
      document.getElementById('pp-approve').dataset.project = d.project_title || '';
      document.getElementById('pp-reject').dataset.id       = d.payment_id;
      document.getElementById('pp-reject').dataset.project  = d.project_title || '';
    }

    function populateCompleted(d) {
      setText('cp-payment-id', '#' + d.payment_id);
      setText('cp-contractor',  d.company_name);
      setText('cp-milestone',   d.milestone_item_title);
      setText('cp-reference',   d.transaction_number);
      setText('cp-date',        fmtDate(d.transaction_date));
      setText('cp-method',      METHOD_LABELS[d.payment_type] || d.payment_type);
      setText('cp-amount',      peso(d.amount));
      setText('cp-verified',    fmtDate(d.updated_at));
      setText('cp-owner',       d.owner_name);
      setText('cp-project',     d.project_title);
      setText('cp-description', d.project_description);

      var fs = document.getElementById('cp-files-section'),
          fc = document.getElementById('cp-files');
      if (d.receipt_photo) {
        fc.innerHTML = completedFileRow(d.receipt_photo);
        fs.classList.remove('hidden');
      } else { fs.classList.add('hidden'); }
    }

    function populateInvalid(d) {
      setText('ip-payment-id', '#' + d.payment_id);
      setText('ip-contractor',  d.company_name);
      setText('ip-milestone',   d.milestone_item_title);
      setText('ip-reference',   d.transaction_number);
      setText('ip-date',        fmtDate(d.transaction_date));
      setText('ip-method',      METHOD_LABELS[d.payment_type] || d.payment_type);
      setText('ip-amount',      peso(d.amount));
      setText('ip-verified',    fmtDate(d.updated_at));
      setText('ip-owner',       d.owner_name);
      setText('ip-project',     d.project_title);
      setText('ip-description', d.project_description);
      setVal('ip-remarks',      d.reason);

      var fs = document.getElementById('ip-files-section'),
          fc = document.getElementById('ip-files');
      if (d.receipt_photo) {
        fc.innerHTML = invalidFileRow(d.receipt_photo);
        fs.classList.remove('hidden');
      } else { fs.classList.add('hidden'); }
    }

    /* ════════════════════════════════════════════════════
       VIEW BUTTON
    ════════════════════════════════════════════════════ */
    document.querySelectorAll('.btn-view').forEach(function (btn) {
      btn.addEventListener('click', function () {
        var id     = btn.dataset.id;
        var status = btn.dataset.status;

        if (status === 'submitted') {
          showLoading('pp-loading', 'pp-body');
          openModal('pendingPaymentModal');
          fetchDetail(id, function (d) { populatePending(d); showBody('pp-loading', 'pp-body'); });
        } else if (status === 'approved') {
          showLoading('cp-loading', 'cp-body');
          openModal('completedPaymentModal');
          fetchDetail(id, function (d) { populateCompleted(d); showBody('cp-loading', 'cp-body'); });
        } else {
          showLoading('ip-loading', 'ip-body');
          openModal('invalidPaymentModal');
          fetchDetail(id, function (d) { populateInvalid(d); showBody('ip-loading', 'ip-body'); });
        }
      });
    });

    /* ════════════════════════════════════════════════════
       EDIT BUTTON — pre-fill all fields from data-* attrs
       FIX: now populates reference, amount, method, status,
            AND remarks (reason). All fields are freely editable.
    ════════════════════════════════════════════════════ */
    document.querySelectorAll('.btn-edit').forEach(function (btn) {
      btn.addEventListener('click', function () {
        var id = btn.dataset.id;

        document.getElementById('edit-project').value   = btn.dataset.project  || '';
        document.getElementById('edit-method').value    = btn.dataset.method   || '';
        document.getElementById('edit-amount').value    = btn.dataset.amount   || '';
        document.getElementById('edit-status').value    = btn.dataset.status   || 'submitted';
        document.getElementById('edit-reference').value = btn.dataset.txn      || '';
        document.getElementById('edit-remarks').value   = btn.dataset.reason   || '';

        document.getElementById('saveEditBtn').dataset.id = id;
        openModal('editPaymentModal');
      });
    });

    /* ── Save edit — calls the new updatePayment (PUT) endpoint ── */
    document.getElementById('saveEditBtn').addEventListener('click', function () {
      var id = this.dataset.id;
      if (!id) return;

      var btn     = this;
      var payload = {
        payment_status:     document.getElementById('edit-status').value    || null,
        payment_type:       document.getElementById('edit-method').value    || null,
        amount:             document.getElementById('edit-amount').value    || null,
        transaction_number: document.getElementById('edit-reference').value,
        reason:             document.getElementById('edit-remarks').value
      };

      // Remove truly empty optional fields so the controller doesn't wipe them
      if (payload.payment_type === '')  delete payload.payment_type;
      if (payload.amount === '')        delete payload.amount;

      btn.disabled    = true;
      btn.textContent = 'Saving…';

      fetch('/admin/global-management/proof-of-payments/' + id, {
        method:  'PUT',
        headers: { 'X-CSRF-TOKEN': CSRF, 'Content-Type': 'application/json' },
        body:    JSON.stringify(payload)
      })
        .then(function (r) { return r.json(); })
        .then(function (res) {
          closeModal('editPaymentModal');
          if (res.success) {
            showToast('Payment updated successfully.');
            setTimeout(function () { location.reload(); }, 1200);
          } else {
            showToast(res.message || 'Failed to update payment.', 'error');
            btn.disabled    = false;
            btn.textContent = 'Save Changes';
          }
        })
        .catch(function () {
          closeModal('editPaymentModal');
          showToast('Request failed. Please try again.', 'error');
          btn.disabled    = false;
          btn.textContent = 'Save Changes';
        });
    });

    /* ════════════════════════════════════════════════════
       APPROVE FLOW
    ════════════════════════════════════════════════════ */
    var _approveId = null;

    document.getElementById('pp-approve').addEventListener('click', function () {
      _approveId = this.dataset.id;
      var project = this.dataset.project;
      document.getElementById('approveSummary').textContent = '#' + _approveId + (project ? ' — ' + project : '');
      closeModal('pendingPaymentModal');
      openModal('confirmApproveModal');
    });

    document.getElementById('confirmApproveBtn').addEventListener('click', function () {
      if (!_approveId) return;
      var btn = this;
      btn.disabled = true; btn.textContent = 'Approving…';

      fetch('/admin/global-management/proof-of-payments/' + _approveId + '/verify', {
        method: 'POST',
        headers: { 'X-CSRF-TOKEN': CSRF, 'Content-Type': 'application/json' }
      })
        .then(function (r) { return r.json(); })
        .then(function (res) {
          closeModal('confirmApproveModal');
          if (res.success) {
            showToast('Payment approved successfully.');
            setTimeout(function () { location.reload(); }, 1200);
          } else {
            showToast(res.message || 'Failed to approve payment.', 'error');
            btn.disabled = false; btn.textContent = 'Yes, Approve';
          }
        })
        .catch(function () {
          closeModal('confirmApproveModal');
          showToast('Request failed. Please try again.', 'error');
          btn.disabled = false; btn.textContent = 'Yes, Approve';
        });
    });

    /* ════════════════════════════════════════════════════
       REJECT FLOW
    ════════════════════════════════════════════════════ */
    var _rejectId = null;

    document.getElementById('pp-reject').addEventListener('click', function () {
      _rejectId = this.dataset.id;
      var project = this.dataset.project;
      document.getElementById('rejectSummary').textContent = '#' + _rejectId + (project ? ' — ' + project : '');
      document.getElementById('rejectReasonInput').value = '';
      closeModal('pendingPaymentModal');
      openModal('confirmRejectModal');
    });

    document.getElementById('confirmRejectBtn').addEventListener('click', function () {
      if (!_rejectId) return;
      var reason = document.getElementById('rejectReasonInput').value.trim();
      var btn    = this;
      btn.disabled = true; btn.textContent = 'Rejecting…';

      fetch('/admin/global-management/proof-of-payments/' + _rejectId + '/reject', {
        method: 'POST',
        headers: { 'X-CSRF-TOKEN': CSRF, 'Content-Type': 'application/json' },
        body: JSON.stringify({ reason: reason })
      })
        .then(function (r) { return r.json(); })
        .then(function (res) {
          closeModal('confirmRejectModal');
          if (res.success) {
            showToast('Payment rejected.');
            setTimeout(function () { location.reload(); }, 1200);
          } else {
            showToast(res.message || 'Failed to reject payment.', 'error');
            btn.disabled = false; btn.textContent = 'Yes, Reject';
          }
        })
        .catch(function () {
          closeModal('confirmRejectModal');
          showToast('Request failed. Please try again.', 'error');
          btn.disabled = false; btn.textContent = 'Yes, Reject';
        });
    });

    /* ════════════════════════════════════════════════════
       DELETE FLOW
    ════════════════════════════════════════════════════ */
    var _deleteId = null;

    document.querySelectorAll('.btn-delete').forEach(function (btn) {
      btn.addEventListener('click', function () {
        _deleteId = btn.dataset.id;
        document.getElementById('delete-payment-id').textContent = '#' + _deleteId;
        document.getElementById('delete-project').textContent    = btn.dataset.project    || '—';
        document.getElementById('delete-contractor').textContent = btn.dataset.contractor || '—';
        document.getElementById('delete-amount').textContent     = btn.dataset.amount     || '—';
        openModal('deletePaymentModal');
      });
    });

    document.getElementById('cancelDeletePaymentBtn').addEventListener('click', function () {
      closeModal('deletePaymentModal');
    });

    document.getElementById('confirmDeletePaymentBtn').addEventListener('click', function () {
      if (!_deleteId) return;
      var btn = this;
      btn.disabled = true;
      btn.innerHTML = '<i class="fi fi-rr-spinner fi-spin"></i>&nbsp;Deleting…';

      fetch('/admin/global-management/proof-of-payments/' + _deleteId, {
        method: 'DELETE',
        headers: { 'X-CSRF-TOKEN': CSRF }
      })
        .then(function (r) { return r.json(); })
        .then(function (res) {
          closeModal('deletePaymentModal');
          if (res.success) {
            showToast('Payment record deleted.');
            setTimeout(function () { location.reload(); }, 1200);
          } else {
            showToast(res.message || 'Failed to delete payment.', 'error');
            btn.disabled = false;
            btn.innerHTML = '<i class="fi fi-rr-trash"></i><span>Delete</span>';
          }
        })
        .catch(function () {
          closeModal('deletePaymentModal');
          showToast('Request failed. Please try again.', 'error');
          btn.disabled = false;
          btn.innerHTML = '<i class="fi fi-rr-trash"></i><span>Delete</span>';
        });
    });

  }());
  </script>

  <script src="{{ asset('js/admin/globalManagement/proofOfpayments.js') }}" defer></script>

</body>
</html>