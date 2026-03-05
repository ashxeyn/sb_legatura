{{-- ═══════════════════════════════════════════════════════════════════════════
     Admin Top Navigation Bar — extracted partial
     Usage:
       @include('admin.layouts.topnav', ['pageTitle' => 'Dashboard'])

     Optional variables:
       $pageSubtitle          — small text under the title  (string|null)
       $hideSearch            — set true to omit the default search input
       $searchPlaceholder     — custom placeholder text (default "Search...")
       $beforeNotifications   — raw HTML inserted before the notification bell
       $afterNotifications    — raw HTML inserted after the notification bell
═══════════════════════════════════════════════════════════════════════════ --}}

<header class="bg-white shadow-sm border-b border-gray-200 flex items-center justify-between px-8 py-4 sticky top-0 z-30">

  {{-- Left: Title --}}
  <div>
    <h1 class="text-2xl font-semibold text-gray-800">{{ $pageTitle }}</h1>
    @if(!empty($pageSubtitle))
      <p class="text-gray-400 text-sm">{{ $pageSubtitle }}</p>
    @endif
  </div>

  {{-- Right: search / extras / notifications --}}
  <div class="flex items-center gap-6">

    {{-- Before-notifications slot (e.g. AI status badge, proof-of-payments form) --}}
    @if(!empty($beforeNotifications))
      {!! $beforeNotifications !!}
    @elseif(empty($hideSearch))
      {{-- Default search input --}}
      <div class="relative w-64" style="width: 600px;">
        <input
          type="text"
          placeholder="{{ $searchPlaceholder ?? 'Search...' }}"
          class="border border-gray-300 rounded-lg px-4 py-2 pr-10 focus:ring-2 focus:ring-indigo-400 focus:outline-none w-full"
        >
        <i class="fi fi-rr-search absolute right-3 top-1/2 transform -translate-y-1/2 text-gray-400"></i>
      </div>
    @endif

    {{-- Notification bell + dropdown --}}
    <div class="relative">
      <button id="notificationBell" class="cursor-pointer w-10 h-10 flex items-center justify-center rounded-full hover:bg-gray-100 transition">
        <i class="fi fi-ss-bell-notification-social-media" style="font-size: 20px;"></i>
      </button>
      <span class="absolute -top-1 -right-2 bg-red-500 text-white text-xs rounded-full w-5 h-5 flex items-center justify-center">3</span>

      <!-- Notifications Dropdown -->
      <div id="notificationDropdown" class="absolute right-0 mt-3 w-80 bg-white rounded-xl shadow-2xl border border-gray-200 hidden z-50">
        <div class="px-4 py-3 border-b border-gray-100 flex items-center justify-between">
          <span class="text-sm font-semibold text-gray-800">Notifications</span>
          <button id="clearNotifications" class="text-xs text-indigo-600 hover:text-indigo-700">Clear all</button>
        </div>
        <ul class="max-h-80 overflow-y-auto" id="notificationList">
          <li class="px-4 py-3 hover:bg-gray-50 transition">
            <div class="flex items-start gap-3">
              <div class="w-8 h-8 rounded-full bg-indigo-100 text-indigo-600 flex items-center justify-center">
                <i class="fi fi-ss-bell"></i>
              </div>
              <div class="flex-1 min-w-0">
                <p class="text-sm text-gray-800 truncate">New bid submitted on "GreenBelt Building".</p>
                <p class="text-xs text-gray-500">2 mins ago</p>
              </div>
              <span class="inline-block px-2 py-1 text-xs rounded-full bg-yellow-100 text-yellow-700">New</span>
            </div>
          </li>
          <li class="px-4 py-3 hover:bg-gray-50 transition">
            <div class="flex items-start gap-3">
              <div class="w-8 h-8 rounded-full bg-green-100 text-green-700 flex items-center justify-center">
                <i class="fi fi-ss-check-circle"></i>
              </div>
              <div class="flex-1 min-w-0">
                <p class="text-sm text-gray-800 truncate">Verification request approved for Cabonting Architects.</p>
                <p class="text-xs text-gray-500">1 hour ago</p>
              </div>
            </div>
          </li>
          <li class="px-4 py-3 hover:bg-gray-50 transition">
            <div class="flex items-start gap-3">
              <div class="w-8 h-8 rounded-full bg-red-100 text-red-700 flex items-center justify-center">
                <i class="fi fi-ss-exclamation"></i>
              </div>
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

    {{-- After-notifications slot (e.g. Compose button on Messages page) --}}
    @if(!empty($afterNotifications))
      {!! $afterNotifications !!}
    @endif

  </div>
</header>
