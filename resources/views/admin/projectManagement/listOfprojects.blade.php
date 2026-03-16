<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <title>List of Projects - Legatura Admin</title>
  <link rel="icon" type="image/svg+xml" href="{{ asset('img/logo2.0-favicon.svg') }}">

  <script src="https://cdn.tailwindcss.com"></script>
  <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.js"></script>
  <link rel="stylesheet" href="{{ asset('css/admin/home/mainComponents.css') }}">
  <link rel="stylesheet" href="{{ asset('css/admin/projectManagement/listOfProjects.css') }}">

  <link rel='stylesheet' href='https://cdn-uicons.flaticon.com/3.0.0/uicons-solid-straight/css/uicons-solid-straight.css'>
  <link rel='stylesheet' href='https://cdn-uicons.flaticon.com/3.0.0/uicons-solid-rounded/css/uicons-solid-rounded.css'>
  <link rel='stylesheet' href='https://cdn-uicons.flaticon.com/3.0.0/uicons-bold-rounded/css/uicons-bold-rounded.css'>
  <link rel='stylesheet' href='https://cdn-uicons.flaticon.com/3.0.0/uicons-regular-rounded/css/uicons-regular-rounded.css'>


  <script src="{{ asset('js/admin/home/mainComponents.js') }}" defer></script>

  <style>
    .action-btn {
      position: relative;
      overflow: hidden;
    }

    .action-btn i {
      position: relative;
      z-index: 1;
      display: block;
      line-height: 1;
      pointer-events: none;
      flex-shrink: 0;
    }

    .action-btn .ripple,
    .action-btn .ripple-effect {
      position: absolute;
      border-radius: 9999px;
      background: rgba(255, 255, 255, 0.45);
      pointer-events: none;
      z-index: 0;
    }

    .date-pill input[type="date"]::-webkit-calendar-picker-indicator {
      opacity: 0.5;
      cursor: pointer;
      filter: invert(30%) sepia(80%) saturate(400%) hue-rotate(210deg);
    }

    .date-pill input[type="date"]::-webkit-calendar-picker-indicator:hover {
      opacity: 1;
    }
  </style>

<body class="bg-gray-50 text-gray-800 font-sans">
  <div class="flex min-h-screen">

    @include('admin.layouts.sidebar')

    <main class="flex-1">
      @include('admin.layouts.topnav', ['pageTitle' => 'List of Projects'])

      <div class="p-8 space-y-6">
        <!-- Controls Section -->
        <div class="controls-wrapper bg-white rounded-xl shadow-sm border border-gray-200 p-4 mb-4 flex flex-wrap items-center justify-between gap-3">
          <div class="flex flex-wrap items-center gap-2.5">
            <div class="flex items-center gap-2 px-3 py-2 border border-gray-300 rounded-lg text-sm text-gray-700">
              <i class="fi fi-rr-filter text-gray-500"></i>
              <span>Filter By</span>
            </div>

            <!-- Verification Status Filter -->
            <div class="relative">
              <select id="verificationFilter" class="appearance-none bg-white border border-indigo-200 rounded-lg px-3 py-2 pr-8 text-xs font-medium text-gray-700 hover:border-indigo-300 focus:outline-none focus:ring-2 focus:ring-indigo-400 focus:border-indigo-400 transition cursor-pointer shadow-sm">
                <option value="">All Verification</option>
                <option value="approved" {{ request('verification') == 'approved' ? 'selected' : '' }}>Approved</option>
                <option value="under_review" {{ request('verification') == 'under_review' ? 'selected' : '' }}>Under Review</option>
                <option value="rejected" {{ request('verification') == 'rejected' ? 'selected' : '' }}>Rejected</option>
              </select>
              <i class="fi fi-rr-angle-small-down absolute right-2.5 top-1/2 -translate-y-1/2 text-gray-500 pointer-events-none text-[11px]"></i>
            </div>

            <!-- Progress Status Filter -->
            <div class="relative">
              <select id="progressFilter" class="appearance-none bg-white border border-indigo-200 rounded-lg px-3 py-2 pr-8 text-xs font-medium text-gray-700 hover:border-indigo-300 focus:outline-none focus:ring-2 focus:ring-indigo-400 focus:border-indigo-400 transition cursor-pointer shadow-sm">
                <option value="">All Progress</option>
                <option value="completed" {{ request('progress') == 'completed' ? 'selected' : '' }}>Completed</option>
                <option value="in_progress" {{ request('progress') == 'in_progress' ? 'selected' : '' }}>Ongoing</option>
                <option value="open" {{ request('progress') == 'open' ? 'selected' : '' }}>Open</option>
                <option value="bidding_closed" {{ request('progress') == 'bidding_closed' ? 'selected' : '' }}>In Bidding</option>
                <option value="halt" {{ request('progress') == 'halt' ? 'selected' : '' }}>Halted</option>
                <option value="terminated" {{ request('progress') == 'terminated' ? 'selected' : '' }}>Terminated</option>
              </select>
              <i class="fi fi-rr-angle-small-down absolute right-2.5 top-1/2 -translate-y-1/2 text-gray-500 pointer-events-none text-[11px]"></i>
            </div>

            <!-- Date Range -->
            <div class="flex flex-wrap items-center gap-2">
              <!-- From -->
              <div class="date-pill flex items-center gap-0 rounded-xl border border-indigo-200 bg-white shadow-sm overflow-hidden focus-within:ring-2 focus-within:ring-indigo-400 focus-within:border-indigo-400 transition">
                <div class="flex items-center gap-1.5 bg-gradient-to-br from-indigo-500 to-indigo-600 px-2.5 py-2 self-stretch">
                  <i class="fi fi-rr-calendar text-white text-[11px]"></i>
                </div>
                <input type="date" id="dateFrom" class="px-2.5 py-1.5 text-xs border-none focus:outline-none focus:ring-0 bg-white" value="{{ request('date_from') }}">
              </div>

              <span class="text-gray-300 font-bold text-lg">→</span>

              <!-- To -->
              <div class="date-pill flex items-center gap-0 rounded-xl border border-indigo-200 bg-white shadow-sm overflow-hidden focus-within:ring-2 focus-within:ring-indigo-400 focus-within:border-indigo-400 transition">
                <div class="flex items-center gap-1.5 bg-gradient-to-br from-indigo-500 to-indigo-600 px-2.5 py-2 self-stretch">
                  <i class="fi fi-rr-calendar text-white text-[11px]"></i>
                </div>
                <input type="date" id="dateTo" class="px-2.5 py-1.5 text-xs border-none focus:outline-none focus:ring-0 bg-white" value="{{ request('date_to') }}">
              </div>
            </div>
          </div>

          <div class="flex items-center gap-2">
            <button id="resetFilterBtn" class="flex items-center gap-2 text-red-600 hover:text-red-700 text-sm font-semibold px-3 py-2 rounded-lg hover:bg-red-50 transition">
              <i class="fi fi-rr-rotate-left"></i>
              <span>Reset Filter</span>
            </button>
          </div>
        </div>

        <!-- Projects Table -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden" id="projectsTableWrap">
          @include('admin.projectManagement.partials.projectTable')
        </div>
      </div>

    </main>

  {{-- Modals --}}
  @include('admin.projectManagement.partials.biddingDetailsModal')
  @include('admin.projectManagement.partials.bidStatusModal')
  @include('admin.projectManagement.partials.acceptBidModal')
  @include('admin.projectManagement.partials.rejectBidModal')
  @include('admin.projectManagement.partials.ongoingProjectModal')
  @include('admin.projectManagement.partials.progressReportModal')
  @include('admin.projectManagement.partials.completedProjectModal')
  @include('admin.projectManagement.partials.haltedProjectModal')
  @include('admin.projectManagement.partials.haltDetailsModal')
  @include('admin.projectManagement.partials.cancelHaltConfirmModal')
  @include('admin.projectManagement.partials.resumeHaltConfirmModal')
  @include('admin.projectManagement.partials.completionDetailsModal')
  @include('admin.projectManagement.partials.editProgressReportModal')
  @include('admin.projectManagement.partials.cancelledProjectModal')
  @include('admin.projectManagement.partials.extendTimelineModal')
  @include('admin.projectManagement.partials.bulkAdjustDatesModal')
  @include('admin.projectManagement.partials.projectSummaryModal')

  <!-- Edit Milestone Modal Container -->
  <div id="editMilestoneModalContainer">
    <!-- Modal content loaded via AJAX -->
  </div>

  <!-- Edit Project Modal -->
  <div id="editProjectModal" class="fixed inset-0 bg-black/50 backdrop-blur-sm z-50 hidden transition-opacity duration-300">
    <div class="absolute inset-0 flex items-center justify-center p-4">
      <!-- Content loaded via AJAX -->
    </div>
  </div>

  @include('admin.projectManagement.partials.editProjectConfirmModal')
  @include('admin.projectManagement.partials.changeBidderModal')

  <!-- Delete Project Modal Container -->
  <div id="deleteProjectModalContainer">
    <!-- Modal content loaded via AJAX -->
  </div>

  <!-- Restore Project Modal Container -->
  <div id="restoreProjectModalContainer">
    <!-- Modal content loaded via AJAX -->
  </div>

  <!-- Halt Project Modal Container -->
  <div id="haltProjectModalContainer">
    <!-- Modal content loaded via AJAX -->
  </div>

  @include('admin.projectManagement.partials.paymentHistoryModal')

  <!-- Document Viewer Modal (Dark Theme) -->
  <div id="documentViewerModal" class="fixed inset-0 bg-black/50 backdrop-blur-sm z-[9999] hidden transition-opacity duration-300">
    <div class="absolute inset-0 flex items-center justify-center p-4">
      <div class="bg-[#1e1e2e] w-full max-w-5xl rounded-2xl shadow-2xl relative transform transition-all duration-300 scale-100 max-h-[90vh] flex flex-col">
        <!-- Header -->
        <div class="flex items-center justify-between px-6 py-4 border-b border-gray-700">
          <h3 id="docViewerTitle" class="text-lg font-bold text-white">Document Viewer</h3>
          <div class="flex items-center gap-2">
            <a id="docViewerDownload" href="#" download class="px-3 py-1.5 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium rounded-lg transition-all duration-200 flex items-center gap-2">
              <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
              </svg>
              Download
            </a>
            <button type="button" onclick="closeDocumentViewer()" class="w-10 h-10 rounded-xl hover:bg-white/10 flex items-center justify-center transition-all duration-200 text-gray-300 hover:text-white">
              <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
              </svg>
            </button>
          </div>
        </div>

        <!-- Content -->
        <div class="flex-1 overflow-hidden p-6">
          <div id="docViewerContent" class="w-full h-full flex items-center justify-center bg-gray-900 rounded-lg overflow-auto">
            <!-- Content will be dynamically inserted here -->
          </div>
        </div>
      </div>
    </div>
  </div>

  <script src="{{ asset('js/admin/reusables/filters.js') }}"></script>
  <script src="{{ asset('js/admin/projectManagement/listOfprojects.js') }}"></script>
  <script src="{{ asset('js/admin/projectManagement/paymentHistoryModal.js') }}"></script>

</body>

</html>
