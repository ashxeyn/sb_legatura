{{-- ═══════════════════════════════════════════════════════════════════════════
Admin Sidebar — extracted partial
Usage: @include('admin.layouts.sidebar')
Active links are auto-detected via the current route name.
User card reads from session('user').
═══════════════════════════════════════════════════════════════════════════ --}}

<aside class="bg-white shadow-xl flex flex-col">

  <div class="flex justify-center items-center">
    <img src="{{ asset('img/logo.svg') }}" alt="Legatura Logo" class="logo-img">
  </div>

  <nav class="flex-1 px-3 py-4 space-y-1">

    {{-- ── Home ── --}}
    <div class="nav-group">
      <button class="nav-btn">
        <div class="flex items-center gap-3">
          <i class="fi fi-ss-home" style="font-size: 20px;"></i>
          <span>Home</span>
        </div>
        <span class="arrow">▼</span>
      </button>
      <div class="nav-submenu">
        <a href="{{ route('admin.dashboard') }}"
          class="submenu-link {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">Dashboard</a>
        <div class="submenu-nested">
          <button class="submenu-link submenu-nested-btn">
            <span>Analytics</span>
            <span class="arrow-small">▼</span>
          </button>
          <div class="submenu-nested-content">
            <a href="{{ route('admin.analytics') }}"
              class="submenu-nested-link {{ request()->routeIs('admin.analytics') && !request()->routeIs('admin.analytics.*') ? 'active' : '' }}">Project
              Performance Analytics</a>
            <a href="{{ route('admin.analytics.subscription') }}"
              class="submenu-nested-link {{ request()->routeIs('admin.analytics.subscription') ? 'active' : '' }}">Subscription
              Analytics</a>
            <a href="{{ route('admin.analytics.userActivity') }}"
              class="submenu-nested-link {{ request()->routeIs('admin.analytics.userActivity') ? 'active' : '' }}">User
              Activity Analytics</a>
            <!--<a href="{{ route('admin.analytics.projectPerformance') }}"
               class="submenu-nested-link {{ request()->routeIs('admin.analytics.projectPerformance') ? 'active' : '' }}">Project Performance Analytics</a>-->
            <a href="{{ route('admin.analytics.bidCompletion') }}"
              class="submenu-nested-link {{ request()->routeIs('admin.analytics.bidCompletion') ? 'active' : '' }}">Bid
              Completion Analytics</a>
            <!--<a href="{{ route('admin.analytics.reports') }}"
               class="submenu-nested-link {{ request()->routeIs('admin.analytics.reports') ? 'active' : '' }}">Reports and Analytics</a>-->
          </div>
        </div>
      </div>
    </div>

    {{-- ── User Management ── --}}
    <div class="nav-group">
      <button class="nav-btn">
        <div class="flex items-center gap-3">
          <i class="fi fi-ss-users-alt" style="font-size: 20px;"></i>
          <span>User Management</span>
        </div>
        <span class="arrow">▼</span>
      </button>
      <div class="nav-submenu">
        <a href="{{ route('admin.userManagement.propertyOwner') }}"
          class="submenu-link {{ request()->routeIs('admin.userManagement.propertyOwner*') ? 'active' : '' }}">Property
          Owner</a>
        <a href="{{ route('admin.userManagement.contractor') }}"
          class="submenu-link {{ request()->routeIs('admin.userManagement.contractor*') ? 'active' : '' }}">Contractor</a>
        <a href="{{ route('admin.userManagement.verificationRequest') }}"
          class="submenu-link {{ request()->routeIs('admin.userManagement.verificationRequest') ? 'active' : '' }}">Verification
          Request</a>
        <a href="{{ route('admin.userManagement.suspendedAccounts') }}"
          class="submenu-link {{ request()->routeIs('admin.userManagement.suspendedAccounts') ? 'active' : '' }}">Suspended
          Accounts</a>
      </div>
    </div>

    {{-- ── Global Management ── --}}
    <div class="nav-group">
      <button class="nav-btn">
        <div class="flex items-center gap-3">
          <i class="fi fi-ss-globe" style="font-size: 20px;"></i>
          <span>Global Management</span>
        </div>
        <span class="arrow">▼</span>
      </button>
      <div class="nav-submenu">
        <a href="{{ route('admin.globalManagement.reviewManagement') }}"
          class="submenu-link {{ request()->routeIs('admin.globalManagement.reviewManagement') ? 'active' : '' }}">Review
          & Rating</a>
        <a href="{{ route('admin.globalManagement.bidManagement') }}"
          class="submenu-link {{ request()->routeIs('admin.globalManagement.bidManagement') ? 'active' : '' }}">Bid
          Management</a>
        <a href="{{ route('admin.globalManagement.proofOfpayments') }}"
          class="submenu-link {{ request()->routeIs('admin.globalManagement.proofOfpayments') ? 'active' : '' }}">Proof
          of Payments</a>
        <a href="{{ route('admin.globalManagement.aiManagement') }}"
          class="submenu-link {{ request()->routeIs('admin.globalManagement.aiManagement') ? 'active' : '' }}">AI
          Management</a>
        <a href="{{ route('admin.globalManagement.postingManagement') }}"
          class="submenu-link {{ request()->routeIs('admin.globalManagement.postingManagement') ? 'active' : '' }}">Posting
          Management</a>
        <a href="{{ route('admin.globalManagement.reportManagement') }}"
          class="submenu-link {{ request()->routeIs('admin.globalManagement.reportManagement') ? 'active' : '' }}">Report
          Management</a>
      </div>
    </div>

    {{-- ── Project Management ── --}}
    <div class="nav-group">
      <button class="nav-btn">
        <div class="flex items-center gap-3">
          <i class="fi fi-sr-master-plan" style="font-size: 20px;"></i>
          <span>Project Management</span>
        </div>
        <span class="arrow">▼</span>
      </button>
      <div class="nav-submenu">
        <a href="{{ route('admin.projectManagement.listOfProjects') }}"
          class="submenu-link {{ request()->routeIs('admin.projectManagement.listOfProjects') ? 'active' : '' }}">List
          of Projects</a>
        <a href="{{ route('admin.projectManagement.disputesReports') }}"
          class="submenu-link {{ request()->routeIs('admin.projectManagement.disputesReports') ? 'active' : '' }}">Disputes/Reports</a>
        <a href="{{ route('admin.projectManagement.messages') }}"
          class="submenu-link {{ request()->routeIs('admin.projectManagement.messages') ? 'active' : '' }}">Messages</a>
        <a href="{{ route('admin.projectManagement.subscriptions') }}"
          class="submenu-link {{ request()->routeIs('admin.projectManagement.subscriptions') ? 'active' : '' }}">Subscriptions
          & Boosts</a>
        <a href="{{ route('admin.projectManagement.showcaseManagement') }}"
          class="submenu-link {{ request()->routeIs('admin.projectManagement.showcaseManagement') ? 'active' : '' }}">Showcase
          Management</a>
        <a href="{{ route('admin.progressFeed') }}"
          class="submenu-link {{ request()->routeIs('admin.progressFeed') ? 'active' : '' }}">Progress Feed</a>
      </div>
    </div>

    {{-- ── Settings ── --}}
    <div class="nav-group">
      <button class="nav-btn">
        <div class="flex items-center gap-3">
          <i class="fi fi-br-settings-sliders" style="font-size: 20px;"></i>
          <span>Settings</span>
        </div>
        <span class="arrow">▼</span>
      </button>
      <div class="nav-submenu">
        <a href="{{ route('admin.settings.notifications') }}"
          class="submenu-link {{ request()->routeIs('admin.settings.notifications') ? 'active' : '' }}">Notifications</a>
        <a href="{{ route('admin.settings.security') }}"
          class="submenu-link {{ request()->routeIs('admin.settings.security') ? 'active' : '' }}">Security</a>
      </div>
    </div>

  </nav>

  {{-- ── User card ── --}}
  @php
    $user = session('user');
    $initials = $user
      ? strtoupper(substr($user->first_name ?? '', 0, 1) . substr($user->last_name ?? '', 0, 1))
      : 'AD';
    $fullName = $user ? trim(($user->first_name ?? '') . ' ' . ($user->last_name ?? '')) : 'Admin';
    $userEmail = $user->email ?? 'admin@legatura.com';
  @endphp

  <div class="mt-auto p-4">
    <div class="user-card flex items-center gap-3 p-3 rounded-lg shadow-md text-white">
      <div
        class="w-10 h-10 rounded-full bg-white text-indigo-900 flex items-center justify-center font-bold shadow flex-shrink-0">
        {{ $initials }}
      </div>
      <div class="flex-1 min-w-0">
        <div class="font-semibold text-sm truncate">{{ $fullName }}</div>
        <div class="text-xs opacity-80 truncate">{{ $userEmail }}</div>
      </div>
      <div class="relative">
        <button id="userMenuBtn"
          class="text-white opacity-80 hover:opacity-100 transition text-2xl w-8 h-8 flex items-center justify-center rounded-full">⋮</button>
        <div id="userMenuDropdown"
          class="absolute right-0 bottom-full mb-2 w-44 bg-white text-gray-800 rounded-xl shadow-2xl border border-gray-200 hidden">
          <div class="px-4 py-3 border-b border-gray-100">
            <div class="text-sm font-semibold truncate">{{ $fullName }}</div>
            <div class="text-xs text-gray-500 truncate">{{ $userEmail }}</div>
          </div>
          <ul class="py-1">
            <li>
              <button id="logoutBtn"
                class="w-full text-left flex items-center gap-2 px-4 py-2 text-sm hover:bg-gray-50 text-red-600">
                <i class="fi fi-ss-exit"></i>
                <span>Logout</span>
              </button>
            </li>
          </ul>
        </div>
      </div>
    </div>
    <form id="logoutForm" method="POST" action="/admin/logout" class="hidden">@csrf</form>
  </div>

</aside>

{{-- ── Logout Confirmation Modal ── --}}
<div id="logoutModal"
  class="fixed inset-0 bg-black/50 backdrop-blur-sm hidden items-center justify-center z-[100] transition-opacity duration-200">
  <div
    class="bg-white rounded-2xl shadow-2xl max-w-sm w-full mx-4 overflow-hidden transform transition-all duration-200 scale-95"
    id="logoutModalContent">
    <div class="px-6 py-5 bg-gradient-to-r from-red-600 to-rose-600">
      <div class="flex items-center justify-between">
        <div class="flex items-center gap-3">
          <div class="w-10 h-10 rounded-full bg-white/20 backdrop-blur-sm flex items-center justify-center">
            <i class="fi fi-ss-exit text-white text-xl"></i>
          </div>
          <h3 class="text-xl font-bold text-white">Logout</h3>
        </div>
        <button id="logoutModalClose"
          class="text-white/80 hover:text-white transition text-2xl leading-none cursor-pointer">&times;</button>
      </div>
    </div>
    <div class="p-6 space-y-5">
      <div class="flex items-start gap-4 p-4 bg-red-50 border-l-4 border-red-500 rounded-lg">
        <i class="fi fi-rr-info-circle text-red-600 text-xl mt-0.5"></i>
        <div class="flex-1">
          <p class="text-sm text-red-900 font-semibold mb-1">Are you sure you want to logout?</p>
          <p class="text-xs text-red-800">You will be redirected to the login page.</p>
        </div>
      </div>
      <div class="flex items-center justify-end gap-3 pt-3 border-t border-gray-200">
        <button id="logoutModalCancel"
          class="px-6 py-2.5 rounded-xl border-2 border-gray-300 text-gray-700 font-semibold hover:bg-gray-50 transition cursor-pointer">Cancel</button>
        <button id="logoutModalConfirm"
          class="px-6 py-2.5 rounded-xl bg-gradient-to-r from-red-600 to-rose-600 hover:from-red-700 hover:to-rose-700 text-white font-semibold shadow-md hover:shadow-lg transition cursor-pointer">Confirm
          Logout</button>
      </div>
    </div>
  </div>
</div>