{{-- ═══════════════════════════════════════════════════════════════════════════
Admin Sidebar — extracted partial
Usage: @include('admin.layouts.sidebar')
Active links are auto-detected via the current route name.
User card reads from session('user').
═══════════════════════════════════════════════════════════════════════════ --}}

<aside class="flex flex-col">

  {{-- ── Logo ── --}}
  <div class="sidebar-logo-wrap">
    <img src="{{ asset('img/logo2.0.svg') }}" alt="Legatura Logo" class="logo-img">
    <img src="{{ asset('img/LEGATURA.svg') }}" alt="Legatura" class="logo-text-img">
  </div>

  <nav class="flex-1 px-2 py-2 overflow-y-auto">

    {{-- ── Overview ── --}}
    <p class="nav-section-label">Overview</p>

    <div class="nav-group">
      <button class="nav-btn">
        <div class="flex items-center gap-2">
          <i class="fi fi-br-home sidebar-icon"></i>
          <span>Home</span>
        </div>
        <svg class="arrow w-3 h-3 transition-transform duration-300" viewBox="0 0 20 20" fill="currentColor">
          <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd"/>
        </svg>
      </button>
      <div class="nav-submenu">
        <a href="{{ route('admin.dashboard') }}"
          class="submenu-link {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">Dashboard</a>
        <div class="submenu-nested">
          <button class="submenu-link submenu-nested-btn">
            <span>Analytics</span>
            <svg class="arrow-small w-2.5 h-2.5 transition-transform duration-300" viewBox="0 0 20 20" fill="currentColor">
              <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd"/>
            </svg>
          </button>
          <div class="submenu-nested-content">
            <a href="{{ route('admin.analytics') }}"
              class="submenu-nested-link {{ request()->routeIs('admin.analytics') && !request()->routeIs('admin.analytics.*') ? 'active' : '' }}">Project Performance</a>
            <a href="{{ route('admin.analytics.subscription') }}"
              class="submenu-nested-link {{ request()->routeIs('admin.analytics.subscription') ? 'active' : '' }}">Subscription</a>
            <a href="{{ route('admin.analytics.userActivity') }}"
              class="submenu-nested-link {{ request()->routeIs('admin.analytics.userActivity') ? 'active' : '' }}">User Activity</a>
            <a href="{{ route('admin.analytics.bidCompletion') }}"
              class="submenu-nested-link {{ request()->routeIs('admin.analytics.bidCompletion') ? 'active' : '' }}">Bid Completion</a>
          </div>
        </div>
      </div>
    </div>

    {{-- ── Management ── --}}
    <p class="nav-section-label">Management</p>

    {{-- ── User Management ── --}}
    <div class="nav-group">
      <button class="nav-btn">
        <div class="flex items-center gap-2">
          <i class="fi fi-br-users-alt sidebar-icon"></i>
          <span>Users</span>
        </div>
        <svg class="arrow w-3 h-3 transition-transform duration-300" viewBox="0 0 20 20" fill="currentColor">
          <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd"/>
        </svg>
      </button>
      <div class="nav-submenu">
        <a href="{{ route('admin.userManagement.propertyOwner') }}"
          class="submenu-link {{ request()->routeIs('admin.userManagement.propertyOwner*') ? 'active' : '' }}">Property Owners</a>
        <a href="{{ route('admin.userManagement.contractor') }}"
          class="submenu-link {{ request()->routeIs('admin.userManagement.contractor*') ? 'active' : '' }}">Contractors</a>
        <a href="{{ route('admin.userManagement.verificationRequest') }}"
          class="submenu-link {{ request()->routeIs('admin.userManagement.verificationRequest') ? 'active' : '' }}">Verification</a>
        <a href="{{ route('admin.userManagement.suspendedAccounts') }}"
          class="submenu-link {{ request()->routeIs('admin.userManagement.suspendedAccounts') ? 'active' : '' }}">Suspended</a>
      </div>
    </div>

    {{-- ── Global Management ── --}}
    <div class="nav-group">
      <button class="nav-btn">
        <div class="flex items-center gap-2">
          <i class="fi fi-br-globe sidebar-icon"></i>
          <span>Global</span>
        </div>
        <svg class="arrow w-3 h-3 transition-transform duration-300" viewBox="0 0 20 20" fill="currentColor">
          <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd"/>
        </svg>
      </button>
      <div class="nav-submenu">
        <a href="{{ route('admin.globalManagement.reviewManagement') }}"
          class="submenu-link {{ request()->routeIs('admin.globalManagement.reviewManagement') ? 'active' : '' }}">Review & Rating</a>
        <a href="{{ route('admin.globalManagement.proofOfpayments') }}"
          class="submenu-link {{ request()->routeIs('admin.globalManagement.proofOfpayments') ? 'active' : '' }}">Proof of Payments</a>
        <a href="{{ route('admin.projectManagement.subscriptions') }}"
          class="submenu-link {{ request()->routeIs('admin.projectManagement.subscriptions') ? 'active' : '' }}">Subscriptions & Boosts</a>
        <a href="{{ route('admin.globalManagement.postingManagement') }}"
          class="submenu-link {{ request()->routeIs('admin.globalManagement.postingManagement') ? 'active' : '' }}">Posting</a>
        <a href="{{ route('admin.globalManagement.reportManagement') }}"
          class="submenu-link {{ request()->routeIs('admin.globalManagement.reportManagement') ? 'active' : '' }}">Report Management</a>
      </div>
    </div>

    {{-- ── Project Management ── --}}
    <div class="nav-group">
      <button class="nav-btn">
        <div class="flex items-center gap-2">
          <i class="fi fi-br-briefcase sidebar-icon"></i>
          <span>Projects</span>
        </div>
        <svg class="arrow w-3 h-3 transition-transform duration-300" viewBox="0 0 20 20" fill="currentColor">
          <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd"/>
        </svg>
      </button>
      <div class="nav-submenu">
        <a href="{{ route('admin.projectManagement.listOfProjects') }}"
          class="submenu-link {{ request()->routeIs('admin.projectManagement.listOfProjects') ? 'active' : '' }}">List
          of Projects</a>
        <a href="{{ route('admin.globalManagement.bidManagement') }}"
          class="submenu-link {{ request()->routeIs('admin.globalManagement.bidManagement') ? 'active' : '' }}">Bid Management</a>
        <a href="{{ route('admin.projectManagement.disputesReports') }}"
         class="submenu-link {{ request()->routeIs('admin.projectManagement.disputesReports') ? 'active' : '' }}">Disputes/Reports</a>
        <a href="{{ route('admin.projectManagement.messages') }}"
          class="submenu-link {{ request()->routeIs('admin.projectManagement.messages') ? 'active' : '' }}">Messages</a>
        <a href="{{ route('admin.projectManagement.subscriptions') }}"
          class="submenu-link {{ request()->routeIs('admin.projectManagement.subscriptions') ? 'active' : '' }}">Subscriptions
          & Boosts</a>
        <a href="{{ route('admin.projectManagement.showcaseManagement') }}"
          class="submenu-link {{ request()->routeIs('admin.projectManagement.showcaseManagement') ? 'active' : '' }}">Showcase</a>
        <a href="{{ route('admin.progressFeed') }}"
          class="submenu-link {{ request()->routeIs('admin.progressFeed') ? 'active' : '' }}">Progress Feed</a>
      </div>
    </div>

    {{-- ── AI Management ── --}}
    <div class="nav-group">
      <a href="{{ route('admin.globalManagement.aiManagement') }}"
        class="nav-btn {{ request()->routeIs('admin.globalManagement.aiManagement') ? 'active' : '' }}">
        <div class="flex items-center gap-2">
          <i class="fi fi-br-robot sidebar-icon"></i>
          <span>AI Management</span>
        </div>
      </a>
    </div>

    {{-- ── Messages ── --}}
    <div class="nav-group">
      <a href="{{ route('admin.projectManagement.messages') }}"
        class="nav-btn {{ request()->routeIs('admin.projectManagement.messages') ? 'active' : '' }}">
        <div class="flex items-center gap-2">
          <i class="fi fi-br-envelope sidebar-icon"></i>
          <span>Messages</span>
        </div>
      </a>
    </div>

    {{-- ── System ── --}}
    <p class="nav-section-label">System</p>

    {{-- ── Settings ── --}}
    <div class="nav-group">
      <button class="nav-btn">
        <div class="flex items-center gap-2">
          <i class="fi fi-br-settings sidebar-icon"></i>
          <span>Settings</span>
        </div>
        <svg class="arrow w-3 h-3 transition-transform duration-300" viewBox="0 0 20 20" fill="currentColor">
          <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd"/>
        </svg>
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
    $profilePic = null;
    
    // If user is in session, check for profile_pic
    if ($user) {
      $profilePic = $user->profile_pic ?? null;
      
      // If profile_pic is not in session object, fetch from database
      if (!$profilePic && isset($user->admin_id)) {
        $adminFromDb = DB::table('admin_users')->where('admin_id', $user->admin_id)->first();
        if ($adminFromDb && $adminFromDb->profile_pic) {
          $profilePic = $adminFromDb->profile_pic;
        }
      }
    }
    
    $initials = $user
      ? strtoupper(substr($user->first_name ?? '', 0, 1) . substr($user->last_name ?? '', 0, 1))
      : 'AD';
    $fullName = $user ? trim(($user->first_name ?? '') . ' ' . ($user->last_name ?? '')) : 'Admin';
    $userEmail = $user->email ?? 'admin@legatura.com';
  @endphp

  <div class="p-2.5 border-t border-slate-200">
    <div class="user-card flex items-center gap-2 p-2 rounded-lg" style="background: linear-gradient(135deg, #F9A600 0%, #C97700 100%);">
      <div class="w-7 h-7 rounded-full flex-shrink-0 overflow-hidden" style="background-color: #ffffff;">
        @if($profilePic)
          <img src="{{ asset('storage/' . $profilePic) }}" alt="{{ $initials }}" class="w-full h-full object-cover">
        @else
          <div class="w-full h-full flex items-center justify-center text-xs font-bold" style="color: #E48F00;">{{ $initials }}</div>
        @endif
      </div>
      <div class="flex-1 min-w-0">
        <div class="font-semibold text-xs truncate" style="color: #ffffff;">{{ $fullName }}</div>
        <div class="text-xs truncate" style="color: rgba(255, 255, 255, 0.82);">{{ $userEmail }}</div>
      </div>
      <div class="relative user-menu-container">
        <button id="userMenuBtn"
          class="text-white/80 hover:text-white transition text-sm w-6 h-6 flex items-center justify-center rounded hover:bg-white/10">⋮</button>
      </div>
    </div>
    <form id="logoutForm" method="POST" action="/admin/logout" class="hidden">@csrf</form>
  </div>

  {{-- User Menu Dropdown (outside sidebar to avoid overflow issues) --}}
  <div id="userMenuDropdown"
    class="fixed w-48 bg-white rounded-xl shadow-2xl border hidden z-[60]" style="border-color: rgba(228, 143, 0, 0.24);">
    {{-- Header --}}
    <div class="px-3 py-2 border-b" style="background: linear-gradient(135deg, #F9A600 0%, #C97700 100%); border-color: rgba(228, 143, 0, 0.24);">
      <div class="flex items-center gap-2">
        <div class="w-7 h-7 rounded-lg flex-shrink-0 overflow-hidden" style="background-color: rgba(255,255,255,0.25);">
          @if($profilePic)
            <img src="{{ asset('storage/' . $profilePic) }}" alt="{{ $initials }}" class="w-full h-full object-cover">
          @else
            <div class="w-full h-full flex items-center justify-center text-xs font-bold" style="color: #ffffff;">{{ $initials }}</div>
          @endif
        </div>
        <div class="flex-1 min-w-0">
          <div class="text-xs font-semibold truncate" style="color: #ffffff;">{{ $fullName }}</div>
          <div class="text-[10px] truncate" style="color: rgba(255, 255, 255, 0.82);">{{ $userEmail }}</div>
        </div>
      </div>
    </div>
    {{-- Menu Items --}}
    <ul class="py-1.5">
      <li>
        <button id="logoutBtn"
          class="w-full text-left flex items-center gap-2 px-3 py-2 text-xs font-medium transition-all group"
          style="color: #dc2626;">
          <div class="w-6 h-6 rounded-lg flex items-center justify-center transition-all" style="background-color: rgba(220, 38, 38, 0.08);">
            <i class="fi fi-br-sign-out-alt text-xs" style="color: #dc2626;"></i>
          </div>
          <span class="group-hover:translate-x-0.5 transition-transform">Logout</span>
        </button>
      </li>
    </ul>
  </div>

</aside>

{{-- ── Logout Confirmation Modal ── --}}
<div id="logoutModal"
  class="fixed inset-0 bg-black/60 backdrop-blur-sm hidden items-center justify-center z-[100] transition-opacity duration-300">
  <div
    class="bg-white rounded-xl shadow-2xl max-w-sm w-full mx-3 overflow-hidden transform transition-all duration-300 scale-95"
    id="logoutModalContent">
    {{-- Header --}}
    <div class="px-5 py-4" style="background: linear-gradient(135deg, #F9A600 0%, #C97700 100%);">
      <div class="flex items-center justify-between">
        <div class="flex items-center gap-2.5">
          <div class="w-9 h-9 rounded-lg bg-white/15 backdrop-blur-sm flex items-center justify-center shadow-lg">
            <i class="fi fi-br-sign-out-alt text-white text-lg"></i>
          </div>
          <div>
            <h3 class="text-lg font-bold text-white leading-tight">Confirm Logout</h3>
            <p class="text-[11px] text-white/70">End your current session</p>
          </div>
        </div>
        <button id="logoutModalClose"
          class="text-white/70 hover:text-white hover:bg-white/10 transition-all rounded-md w-7 h-7 flex items-center justify-center text-xl leading-none cursor-pointer">&times;</button>
      </div>
    </div>
    
    {{-- Content --}}
    <div class="p-4">
      <div class="flex items-start gap-3 p-3 bg-slate-50 border border-slate-200 rounded-lg mb-4">
        <div class="w-8 h-8 rounded-md flex items-center justify-center flex-shrink-0" style="background-color: rgba(2, 38, 68, 0.1);">
          <i class="fi fi-rr-info text-lg" style="color: #022644;"></i>
        </div>
        <div class="flex-1 pt-0.5">
          <p class="text-xs font-semibold mb-1" style="color: #1E1E1E;">Are you sure you want to logout?</p>
          <p class="text-xs leading-relaxed" style="color: #64748b;">You will be securely logged out and redirected to the landing page.</p>
        </div>
      </div>
      
      {{-- Actions --}}
      <div class="flex items-center justify-end gap-2">
        <button id="logoutModalCancel"
          class="px-4 py-2 rounded-lg border border-slate-300 font-semibold hover:bg-slate-50 hover:border-slate-400 transition-all text-xs cursor-pointer" style="color: #64748b;">Cancel</button>
        <button id="logoutModalConfirm"
          class="px-4 py-2 rounded-lg text-white font-semibold shadow-md hover:shadow-lg transition-all text-xs cursor-pointer" style="background: linear-gradient(135deg, #dc2626 0%, #ef4444 100%);">
          <span class="flex items-center gap-1.5">
            <i class="fi fi-br-sign-out-alt text-xs"></i>
            <span>Logout</span>
          </span>
        </button>
      </div>
    </div>
  </div>
</div>