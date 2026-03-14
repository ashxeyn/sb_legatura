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
  <link rel="stylesheet" href="{{ asset('css/admin/projectManagement/listOfProjects.css') }}">

  <link rel='stylesheet' href='https://cdn-uicons.flaticon.com/3.0.0/uicons-solid-straight/css/uicons-solid-straight.css'>
  <link rel='stylesheet' href='https://cdn-uicons.flaticon.com/3.0.0/uicons-solid-rounded/css/uicons-solid-rounded.css'>
  <link rel='stylesheet' href='https://cdn-uicons.flaticon.com/3.0.0/uicons-bold-rounded/css/uicons-bold-rounded.css'>
  <link rel='stylesheet' href='https://cdn-uicons.flaticon.com/3.0.0/uicons-regular-rounded/css/uicons-regular-rounded.css'>


  <script src="{{ asset('js/admin/home/mainComponents.js') }}" defer></script>


</head>

<body class="bg-gray-50 text-gray-800 font-sans">
  <div class="flex min-h-screen">

    @include('admin.layouts.sidebar')

    <main class="flex-1">
      @include('admin.layouts.topnav', ['pageTitle' => 'List of Projects'])

      <div class="p-8 space-y-6">
        <!-- Controls Section -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-4 flex flex-wrap items-center justify-between gap-4">
          <div class="flex items-center gap-4 flex-wrap">
            <!-- Verification Status Filter -->
            <div class="relative">
              <select id="verificationFilter" class="appearance-none bg-white border border-gray-300 rounded-lg px-4 py-2 pr-10 text-sm font-medium text-gray-700 hover:border-orange-400 focus:outline-none focus:ring-2 focus:ring-orange-400 focus:border-transparent transition cursor-pointer">
                <option value="">All Verification Status</option>
                <option value="approved" {{ request('verification') == 'approved' ? 'selected' : '' }}>Approved</option>
                <option value="under_review" {{ request('verification') == 'under_review' ? 'selected' : '' }}>Under Review</option>
                <option value="rejected" {{ request('verification') == 'rejected' ? 'selected' : '' }}>Rejected</option>
              </select>
              <i class="fi fi-rr-angle-small-down absolute right-3 top-1/2 -translate-y-1/2 text-gray-500 pointer-events-none"></i>
            </div>

            <!-- Progress Status Filter -->
            <div class="relative">
              <select id="progressFilter" class="appearance-none bg-white border border-gray-300 rounded-lg px-4 py-2 pr-10 text-sm font-medium text-gray-700 hover:border-orange-400 focus:outline-none focus:ring-2 focus:ring-orange-400 focus:border-transparent transition cursor-pointer">
                <option value="">All Progress Status</option>
                <option value="completed" {{ request('progress') == 'completed' ? 'selected' : '' }}>Completed</option>
                <option value="in_progress" {{ request('progress') == 'in_progress' ? 'selected' : '' }}>Ongoing</option>
                <option value="open" {{ request('progress') == 'open' ? 'selected' : '' }}>Open</option>
                <option value="bidding_closed" {{ request('progress') == 'bidding_closed' ? 'selected' : '' }}>In Bidding</option>
                <option value="halt" {{ request('progress') == 'halt' ? 'selected' : '' }}>Halted</option>
                <option value="terminated" {{ request('progress') == 'terminated' ? 'selected' : '' }}>Terminated</option>
              </select>
              <i class="fi fi-rr-angle-small-down absolute right-3 top-1/2 -translate-y-1/2 text-gray-500 pointer-events-none"></i>
            </div>

            <!-- Date From -->
            <div class="relative">
              <input
                type="date"
                id="dateFrom"
                class="border border-gray-300 rounded-lg px-4 py-2 text-sm focus:ring-2 focus:ring-orange-400 focus:outline-none"
                value="{{ request('date_from') }}"
              >
            </div>

            <!-- Date To -->
            <div class="relative">
              <input
                type="date"
                id="dateTo"
                class="border border-gray-300 rounded-lg px-4 py-2 text-sm focus:ring-2 focus:ring-orange-400 focus:outline-none"
                value="{{ request('date_to') }}"
              >
            </div>
          </div>

          <!-- Reset Button -->
          <button id="resetFilterBtn" class="flex items-center gap-2 text-red-600 hover:text-red-700 text-sm font-semibold px-3 py-2 rounded-lg hover:bg-red-50 transition">
            <i class="fi fi-rr-rotate-left"></i>
            <span>Reset Filter</span>
          </button>
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

  <script src="{{ asset('js/admin/reusables/filters.js') }}"></script>
  <script src="{{ asset('js/admin/projectManagement/listOfprojects.js') }}"></script>
  <script src="{{ asset('js/admin/projectManagement/paymentHistoryModal.js') }}"></script>

</body>

</html>
