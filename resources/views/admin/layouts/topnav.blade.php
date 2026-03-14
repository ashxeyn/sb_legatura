{{-- ═══════════════════════════════════════════════════════════════════════════
Admin Top Navigation Bar — extracted partial
Usage:
@include('admin.layouts.topnav', ['pageTitle' => 'Dashboard'])

Optional variables:
$pageSubtitle — small text under the title (string|null)
$hideSearch — set true to omit the default search input
$searchPlaceholder — custom placeholder text (default "Search...")
$beforeNotifications — raw HTML inserted before the notification bell
$afterNotifications — raw HTML inserted after the notification bell
═══════════════════════════════════════════════════════════════════════════ --}}

<header
  class="topnav-white-bar relative overflow-visible bg-white border-b border-slate-200 flex items-center justify-between px-4 lg:px-6 sticky top-0 z-30"
  style="box-shadow: 0 10px 26px rgba(2, 38, 68, 0.10), 0 2px 8px rgba(2, 38, 68, 0.06);">

  {{-- Left: Title --}}
  <div>
    <h1 class="text-xl lg:text-2xl font-bold leading-tight" style="color: #1E1E1E;">{{ $pageTitle }}</h1>
    @if(!empty($pageSubtitle))
      <p class="text-xs mt-0.5" style="color: #64748b;">{{ $pageSubtitle }}</p>
    @endif
  </div>

  {{-- Right: search / extras / notifications --}}
  <div class="flex items-center gap-3 lg:gap-4">

    {{-- Before-notifications slot (e.g. AI status badge, proof-of-payments form) --}}
    @if(!empty($beforeNotifications))
      {!! $beforeNotifications !!}
    @elseif(empty($hideSearch))
      {{-- Default search input --}}
      <div class="relative w-56 lg:w-[32rem] topnav-search-wrap" style="width: min(520px, 42vw);">
        <input type="text" id="topNavSearch" placeholder="{{ $searchPlaceholder ?? 'Search...' }}"
          class="w-full rounded-lg border border-slate-200 bg-slate-50 px-3.5 py-2 pr-9 text-sm placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-slate-300 focus:border-slate-300 transition-all duration-200 topnav-search-input"
          style="color: #1E1E1E;">
        <i class="fi fi-rr-search absolute right-2.5 top-1/2 transform -translate-y-1/2 text-sm transition-all duration-200 topnav-search-icon" style="color: #64748b;"></i>
      </div>
    @endif

    {{-- Notification bell + dropdown --}}
    <div class="relative">
      <button id="notificationBell"
        class="topnav-bell group cursor-pointer w-9 h-9 flex items-center justify-center rounded-lg border border-slate-200 bg-slate-50 hover:bg-white hover:border-slate-300 transition-all duration-200">
        <i class="fi fi-ss-bell-notification-social-media transition-transform duration-200 group-hover:scale-110" style="font-size: 16px; color: #022644;"></i>
      </button>
      <span id="notificationUnreadCount"
        class="absolute -top-1 -right-2 bg-red-500 text-white text-xs rounded-full w-5 h-5 hidden items-center justify-center">0</span>

      <!-- Notifications Dropdown -->
      <div id="notificationDropdown"
        class="absolute right-0 mt-3 w-80 bg-white rounded-xl shadow-2xl border border-gray-200 hidden z-50">
        <div class="px-4 py-3 border-b border-gray-100 flex items-center justify-between">
          <span class="text-sm font-semibold text-gray-800">Notifications</span>
          <button id="markNotificationsRead" class="text-xs text-indigo-600 hover:text-indigo-700">Mark all read</button>
        </div>
        <ul class="max-h-80 overflow-y-auto" id="notificationList">
          <li class="px-4 py-4 text-sm text-gray-500">Loading notifications...</li>
        </ul>
        <div class="px-4 py-3 border-t border-gray-100 flex items-center justify-between">
          <a href="{{ route('admin.settings.notifications') }}"
            class="text-sm text-indigo-600 hover:text-indigo-700">View All Notifications</a>
          <a href="{{ route('admin.settings.notifications') }}"
            class="topnav-settings-link text-sm font-medium transition-all duration-200" style="color: #022644;">Notification settings</a>
        </div>
      </div>
    </div>

    {{-- After-notifications slot (e.g. Compose button on Messages page) --}}
    @if(!empty($afterNotifications))
      {!! $afterNotifications !!}
    @endif

  </div>

  <div class="topnav-accent-line pointer-events-none absolute inset-x-0 bottom-0"></div>
</header>