<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <title>Admin Dashboard - Legatura</title>

  <script src="https://cdn.tailwindcss.com"></script>
  <link rel="stylesheet" href="{{ asset('css/admin/home/mainComponents.css') }}">
  <link rel="stylesheet" href="{{ asset('css/admin/progressFeed.css') }}">

  <link rel='stylesheet' href='https://cdn-uicons.flaticon.com/3.0.0/uicons-solid-straight/css/uicons-solid-straight.css'>
  <link rel='stylesheet' href='https://cdn-uicons.flaticon.com/3.0.0/uicons-solid-rounded/css/uicons-solid-rounded.css'>
  <link rel='stylesheet' href='https://cdn-uicons.flaticon.com/3.0.0/uicons-bold-rounded/css/uicons-bold-rounded.css'>
  <link rel='stylesheet' href='https://cdn-uicons.flaticon.com/3.0.0/uicons-regular-rounded/css/uicons-regular-rounded.css'>

  <script src="{{ asset('js/admin/home/mainComponents.js') }}" defer></script>
  <script src="{{ asset('js/admin/progressFeed.js') }}" defer></script>
</head>

<body class="bg-gray-50 text-gray-800 font-sans">
  <div class="flex min-h-screen">

    @include('admin.layouts.sidebar')

    <main class="flex-1 overflow-x-hidden">
      @include('admin.layouts.topnav', [
        'pageTitle'         => 'Progress Feed',
        'pageSubtitle'      => 'System-wide contractor milestone evidence, live.',
        'searchPlaceholder' => 'Search project, milestone, contractor…',
      ])

      {{-- ── Filter Bar ───────────────────────────────────────────────── --}}
      <div class="px-8 pt-6 pb-2 flex flex-wrap gap-3 items-center">

        {{-- Status filter --}}
        <select id="statusFilter"
          class="border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-400 focus:outline-none bg-white">
          <option value="all">All Statuses</option>
          <option value="submitted">Submitted</option>
          <option value="approved">Approved</option>
          <option value="rejected">Rejected</option>
          <option value="deleted">Deleted</option>
        </select>

        {{-- Company / Contractor filter --}}
        <div class="relative" id="companyFilterWrap">
          <input type="text" id="companyFilter" autocomplete="off"
            class="border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-400 focus:outline-none bg-white w-56"
            placeholder="Filter by company…">
          <div id="companyDropdown"
            class="hidden absolute z-30 mt-1 w-full max-h-52 overflow-y-auto bg-white border border-gray-200 rounded-lg shadow-lg">
          </div>
        </div>

        {{-- Date range --}}
        <input type="date" id="dateFrom"
          class="border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-400 focus:outline-none bg-white"
          placeholder="From">
        <span class="text-gray-400 text-sm">–</span>
        <input type="date" id="dateTo"
          class="border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-400 focus:outline-none bg-white"
          placeholder="To">

        {{-- Reset --}}
        <button id="resetFilters"
          class="px-4 py-2 text-sm rounded-lg border border-gray-300 text-gray-600 hover:bg-gray-100 transition bg-white">
          Reset
        </button>

        {{-- Results count --}}
        <span id="resultsCount" class="ml-auto text-sm text-gray-400"></span>
      </div>

      {{-- ── Feed Cards ───────────────────────────────────────────────── --}}
      <div class="px-8 py-4">

        {{-- Loading skeleton --}}
        <div id="feedLoading" class="hidden">
          <div class="pf-skeleton mb-4"></div>
          <div class="pf-skeleton mb-4"></div>
          <div class="pf-skeleton mb-4"></div>
        </div>

        {{-- Empty state --}}
        <div id="feedEmpty" class="hidden flex flex-col items-center justify-center py-24 text-gray-400">
          <i class="fi fi-rr-picture text-5xl mb-4 opacity-30"></i>
          <p class="text-lg font-medium">No progress reports found</p>
          <p class="text-sm mt-1">Try adjusting your filters.</p>
        </div>

        {{-- Error state --}}
        <div id="feedError" class="hidden text-center py-16">
          <i class="fi fi-rr-exclamation text-4xl text-red-400 mb-3"></i>
          <p class="text-red-500 font-medium">Failed to load feed. Please refresh.</p>
        </div>

        {{-- Card list --}}
        <div id="feedList" class="space-y-5"></div>

        {{-- Pagination --}}
        <div id="feedPagination" class="flex items-center justify-center gap-2 pt-8 pb-4"></div>
      </div>

    </main>
  </div>

  {{-- ── Universal File Viewer (UFV) ─────────────────────────────────── --}}
  <div id="ufvModal" class="ufv-overlay hidden">
    <div class="ufv-shell">

      {{-- Header bar --}}
      <div class="ufv-header">
        <div class="ufv-header-left">
          <span class="ufv-file-name" id="ufvFileName"></span>
          <span class="ufv-counter"  id="ufvCounter"></span>
        </div>
        <div class="ufv-header-right">
          <a id="ufvDownload" href="#" download class="ufv-dl-btn" title="Download file">
            <i class="fi fi-rr-download"></i>
          </a>
          <button id="ufvClose" class="ufv-close-btn" title="Close (Esc)">&times;</button>
        </div>
      </div>

      {{-- Content area: prev arrow · viewport · next arrow --}}
      <div class="ufv-content">
        <button id="ufvPrev" class="ufv-nav-btn" title="Previous (←)">
          <i class="fi fi-rr-angle-left"></i>
        </button>
        <div id="ufvViewport" class="ufv-viewport"></div>
        <button id="ufvNext" class="ufv-nav-btn" title="Next (→)">
          <i class="fi fi-rr-angle-right"></i>
        </button>
      </div>

      {{-- Filmstrip --}}
      <div id="ufvFilmstrip" class="ufv-filmstrip"></div>

    </div>
  </div>

</body>
</html>