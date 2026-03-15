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
  <link rel="stylesheet" href="{{ asset('css/admin/projectManagement/disputesReports.css') }}">

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
      @include('admin.layouts.topnav', ['pageTitle' => 'Disputes/Reports'])

      <!-- Reject Confirmation Modal -->
      <div id="rejectConfirmModal" class="modal-overlay fixed inset-0 bg-black/50 backdrop-blur-sm hidden items-center justify-center z-50 p-4">
        <div class="modal-content bg-white rounded-2xl shadow-2xl max-w-md w-full overflow-hidden">
          <div class="px-6 py-5 bg-gradient-to-r from-red-600 to-rose-600">
            <div class="flex items-center justify-between">
              <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-full bg-white/20 backdrop-blur-sm flex items-center justify-center">
                  <i class="fi fi-sr-times-circle text-white text-xl"></i>
                </div>
                <h3 class="text-xl font-bold text-white">Reject Case?</h3>
              </div>
              <button class="modal-close text-white/80 hover:text-white transition text-2xl leading-none">&times;</button>
            </div>
          </div>
          <div class="p-6 space-y-5">
            <div class="flex items-start gap-4 p-4 bg-red-50 border-l-4 border-red-500 rounded-lg">
              <i class="fi fi-rr-info-circle text-red-600 text-xl mt-0.5"></i>
              <div class="flex-1">
                <p class="text-sm text-red-900 font-semibold mb-1">Reject this case</p>
                <p class="text-xs text-red-800">This will reject the dispute and notify the reporter.</p>
              </div>
            </div>
            <div>
              <label class="block text-sm font-semibold text-gray-800 mb-2">Rejection Reason *</label>
              <textarea id="rejectionReason" rows="4" class="w-full px-4 py-2.5 border-2 border-gray-200 rounded-xl focus:ring-2 focus:ring-red-300 focus:border-red-300 transition resize-none" placeholder="Provide reason for rejecting this dispute..."></textarea>
            </div>

            <!-- Penalty Panel -->
            <div class="border-t border-gray-200 pt-4">
              <div class="flex items-center gap-3 mb-3">
                <input type="checkbox" id="rejectApplyPenalty" class="w-4 h-4 rounded border-gray-300 text-red-600 focus:ring-red-500">
                <label for="rejectApplyPenalty" class="text-sm font-semibold text-gray-800">Apply penalty to the reported user</label>
              </div>
              <div id="rejectPenaltyFields" class="hidden space-y-3 pl-7">
                <div>
                  <label class="block text-xs font-semibold text-gray-600 mb-1">Reported User</label>
                  <p class="text-sm font-semibold text-gray-800" id="rejectPenaltyUserName">-</p>
                  <p class="text-xs text-gray-500" id="rejectPenaltyUserType">-</p>
                </div>
                <div>
                  <label class="block text-xs font-semibold text-gray-600 mb-1">Penalty Type *</label>
                  <select id="rejectPenaltyType" class="w-full px-3 py-2 border-2 border-gray-200 rounded-xl text-sm focus:ring-2 focus:ring-red-300 focus:border-red-300">
                    <option value="">Select penalty...</option>
                    <option value="temporary_ban">Temporary Ban</option>
                    <option value="permanent_ban">Permanent Ban / Terminate</option>
                  </select>
                </div>
                <div id="rejectBanDurationWrap" class="hidden">
                  <label class="block text-xs font-semibold text-gray-600 mb-1">Ban Duration *</label>
                  <select id="rejectBanDuration" class="w-full px-3 py-2 border-2 border-gray-200 rounded-xl text-sm focus:ring-2 focus:ring-red-300 focus:border-red-300">
                    <option value="7">7 Days</option>
                    <option value="14">14 Days</option>
                    <option value="30" selected>30 Days</option>
                    <option value="60">60 Days</option>
                    <option value="90">90 Days</option>
                  </select>
                </div>
              </div>
            </div>

            <div class="flex items-center justify-end gap-3 pt-3 border-t border-gray-200">
              <button class="modal-close px-6 py-2.5 rounded-xl border-2 border-gray-300 text-gray-700 font-semibold hover:bg-gray-50 transition">Cancel</button>
              <button id="confirmRejectBtn" class="px-6 py-2.5 rounded-xl bg-gradient-to-r from-red-600 to-rose-600 hover:from-red-700 hover:to-rose-700 text-white font-semibold shadow-md hover:shadow-lg transition">
                Confirm Reject
              </button>
            </div>
          </div>
        </div>
      </div>

      <!-- Approve for Review Confirmation Modal -->
      <div id="approveConfirmModal" class="modal-overlay fixed inset-0 bg-black/50 backdrop-blur-sm hidden items-center justify-center z-50 p-4">
        <div class="modal-content bg-white rounded-2xl shadow-2xl max-w-md w-full overflow-hidden">
          <div class="px-6 py-5 bg-gradient-to-r from-indigo-600 to-purple-600">
            <div class="flex items-center justify-between">
              <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-full bg-white/20 backdrop-blur-sm flex items-center justify-center">
                  <i class="fi fi-sr-check-circle text-white text-xl"></i>
                </div>
                <h3 class="text-xl font-bold text-white">Approve for Review?</h3>
              </div>
              <button class="modal-close text-white/80 hover:text-white transition text-2xl leading-none">&times;</button>
            </div>
          </div>
          <div class="p-6 space-y-5">
            <div class="flex items-start gap-4 p-4 bg-indigo-50 border-l-4 border-indigo-500 rounded-lg">
              <i class="fi fi-rr-info-circle text-indigo-600 text-xl mt-0.5"></i>
              <div class="flex-1">
                <p class="text-sm text-indigo-900 font-semibold mb-1">Approve this dispute for review</p>
                <p class="text-xs text-indigo-800">This will notify both parties and request resubmission as needed.</p>
              </div>
            </div>

            <div class="p-2 text-sm text-gray-700">Are you sure you want to approve this dispute for review?</div>

            <div class="flex items-center justify-end gap-3 pt-3 border-t border-gray-200">
              <button class="modal-close px-6 py-2.5 rounded-xl border-2 border-gray-300 text-gray-700 font-semibold hover:bg-gray-50 transition">Cancel</button>
              <button id="confirmApproveBtn" class="px-6 py-2.5 rounded-xl bg-gradient-to-r from-emerald-600 to-teal-600 hover:from-emerald-700 hover:to-teal-700 text-white font-semibold shadow-md hover:shadow-lg transition">
                Confirm Approve
              </button>
            </div>
          </div>
        </div>
      </div>

      <!-- Halt Project Confirmation Modal -->
      <div id="haltConfirmModal" class="modal-overlay fixed inset-0 bg-black/50 backdrop-blur-sm hidden items-center justify-center z-50 p-4">
        <div class="modal-content bg-white rounded-2xl shadow-2xl max-w-md w-full overflow-hidden">
          <div class="px-6 py-5 bg-gradient-to-r from-amber-600 to-orange-600">
            <div class="flex items-center justify-between">
              <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-full bg-white/20 backdrop-blur-sm flex items-center justify-center">
                  <i class="fi fi-sr-pause-circle text-white text-xl"></i>
                </div>
                <h3 class="text-xl font-bold text-white">Halt Project?</h3>
              </div>
              <button class="modal-close text-white/80 hover:text-white transition text-2xl leading-none">&times;</button>
            </div>
          </div>
          <div class="p-6 space-y-5">
            <div class="flex items-start gap-4 p-4 bg-amber-50 border-l-4 border-amber-500 rounded-lg">
              <i class="fi fi-rr-info-circle text-amber-600 text-xl mt-0.5"></i>
              <div class="flex-1">
                <p class="text-sm text-amber-900 font-semibold mb-1">Halt the project and resolve dispute</p>
                <p class="text-xs text-amber-800">This will halt the project, resolve the dispute, and notify all parties.</p>
              </div>
            </div>
            <div>
              <label class="block text-sm font-semibold text-gray-800 mb-2">Halt Reason *</label>
              <textarea id="haltReason" rows="4" class="w-full px-4 py-2.5 border-2 border-gray-200 rounded-xl focus:ring-2 focus:ring-amber-300 focus:border-amber-300 transition resize-none" placeholder="Provide reason for halting this project..."></textarea>
            </div>

            <div class="flex items-center justify-end gap-3 pt-3 border-t border-gray-200">
              <button class="modal-close px-6 py-2.5 rounded-xl border-2 border-gray-300 text-gray-700 font-semibold hover:bg-gray-50 transition">Cancel</button>
              <button id="confirmHaltBtn" class="px-6 py-2.5 rounded-xl bg-gradient-to-r from-amber-600 to-orange-600 hover:from-amber-700 hover:to-orange-700 text-white font-semibold shadow-md hover:shadow-lg transition">
                Confirm Halt
              </button>
            </div>
          </div>
        </div>
      </div>

      <!-- Content -->
      <section class="px-8 py-8 space-y-8">
        <!-- Statistics Cards -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
          <!-- Total Reports -->
          <div class="stat-card bg-white rounded-2xl shadow-md border border-gray-200 p-6 hover:shadow-xl transition-all duration-300">
            <div class="flex items-start justify-between mb-4">
              <div>
                <p class="text-sm text-gray-500 font-medium mb-1">Total Reports</p>
                <h3 class="text-4xl font-bold text-gray-800">{{ $totalReports ?? 0 }}</h3>
              </div>
              <div class="w-14 h-14 rounded-full bg-gradient-to-br from-blue-100 to-indigo-200 flex items-center justify-center">
                <i class="fi fi-sr-file-invoice text-2xl text-blue-600"></i>
              </div>
            </div>
            <div class="flex items-center gap-2 text-sm">
              <span class="flex items-center gap-1 text-emerald-600 font-semibold">
                <i class="fi fi-rr-arrow-trend-up"></i>
                <span>{{ ($totalChangePercent ?? 0) }}%</span>
              </span>
              <span class="text-gray-400">vs last week</span>
            </div>
          </div>

          <!-- Pending Verifications -->
          <div class="stat-card bg-white rounded-2xl shadow-md border border-gray-200 p-6 hover:shadow-xl transition-all duration-300">
            <div class="flex items-start justify-between mb-4">
              <div>
                <p class="text-sm text-gray-500 font-medium mb-1">Pending Disputes</p>
                <h3 class="text-4xl font-bold text-gray-800">{{ $pendingCount ?? 0 }}</h3>
              </div>
              <div class="w-14 h-14 rounded-full bg-gradient-to-br from-amber-100 to-orange-200 flex items-center justify-center">
                <i class="fi fi-sr-hourglass-end text-2xl text-amber-600"></i>
              </div>
            </div>
            <div class="flex items-center gap-2 text-sm">
              <span class="flex items-center gap-1 text-red-600 font-semibold">
                <i class="fi fi-rr-arrow-trend-down"></i>
                <span>{{ ($pendingPercent ?? 0) }}%</span>
              </span>
              <span class="text-gray-400">of total</span>
            </div>
          </div>

          <!-- Active Disputes -->
          <div class="stat-card bg-white rounded-2xl shadow-md border border-gray-200 p-6 hover:shadow-xl transition-all duration-300">
            <div class="flex items-start justify-between mb-4">
              <div>
                <p class="text-sm text-gray-500 font-medium mb-1">Active Disputes</p>
                <h3 class="text-4xl font-bold text-gray-800">{{ $activeCount ?? 0 }}</h3>
              </div>
              <div class="w-14 h-14 rounded-full bg-gradient-to-br from-red-100 to-rose-200 flex items-center justify-center">
                <i class="fi fi-sr-shield-exclamation text-2xl text-red-600"></i>
              </div>
            </div>
            <div class="flex items-center gap-2 text-sm">
              <span class="flex items-center gap-1 text-emerald-600 font-semibold">
                <i class="fi fi-rr-arrow-trend-up"></i>
                <span>{{ ($activePercent ?? 0) }}%</span>
              </span>
              <span class="text-gray-400">of total</span>
            </div>
          </div>

          <!-- Resolved Cases -->
          <div class="stat-card bg-white rounded-2xl shadow-md border border-gray-200 p-6 hover:shadow-xl transition-all duration-300">
            <div class="flex items-start justify-between mb-4">
              <div>
                <p class="text-sm text-gray-500 font-medium mb-1">Resolved Cases</p>
                <h3 class="text-4xl font-bold text-gray-800">{{ $resolvedCount ?? 0 }}</h3>
              </div>
              <div class="w-14 h-14 rounded-full bg-gradient-to-br from-emerald-100 to-teal-200 flex items-center justify-center">
                <i class="fi fi-sr-check-circle text-2xl text-emerald-600"></i>
              </div>
            </div>
            <div class="flex items-center gap-2 text-sm">
              <span class="flex items-center gap-1 text-emerald-600 font-semibold">
                <i class="fi fi-rr-arrow-trend-up"></i>
                <span>{{ ($resolvedPercent ?? 0) }}%</span>
              </span>
              <span class="text-gray-400">of total</span>
            </div>
          </div>
        </div>

        <!-- Filter Tabs & Table -->
        <div class="bg-white rounded-2xl shadow-md border border-gray-200 overflow-hidden">
          <!-- Filter Tabs -->
          <div class="px-6 py-4 border-b border-gray-200 bg-gradient-to-r from-indigo-50 to-purple-50">
            <div class="flex items-center justify-between">
              <div class="flex items-center gap-2 bg-white rounded-xl p-1 shadow-sm">
                <button class="filter-tab active" data-filter="all">
                  All <span class="ml-1 text-xs bg-indigo-100 text-indigo-700 px-2 py-0.5 rounded-full">{{ $totalReports ?? 0 }}</span>
                </button>
                <button class="filter-tab" data-filter="pending">
                  Pending <span class="ml-1 text-xs bg-amber-100 text-amber-700 px-2 py-0.5 rounded-full">{{ $pendingCount ?? 0 }}</span>
                </button>
                <button class="filter-tab" data-filter="disputes">
                  Disputes <span class="ml-1 text-xs bg-red-100 text-red-700 px-2 py-0.5 rounded-full">{{ $activeCount ?? 0 }}</span>
                </button>
                <button class="filter-tab" data-filter="resolved">
                  Resolved <span class="ml-1 text-xs bg-emerald-100 text-emerald-700 px-2 py-0.5 rounded-full">{{ $resolvedCount ?? 0 }}</span>
                </button>
              </div>
              <div class="flex items-center gap-3">
                <button id="resetFilters" class="flex items-center gap-2 text-red-600 hover:text-red-700 text-sm font-semibold px-3 py-2 rounded-lg hover:bg-red-50 transition">
                  <i class="fi fi-rr-rotate-left"></i>
                  <span>Reset Filter</span>
                </button>
              </div>
            </div>
            <div class="mt-3 flex items-center gap-3">
              <input type="date" id="dateFrom" class="px-3 py-2 border border-gray-300 rounded-lg text-sm">
                <input type="date" id="dateTo" class="px-3 py-2 border border-gray-300 rounded-lg text-sm">
            </div>
          </div>

          <!-- Table -->
          <div class="overflow-x-auto">
            <table class="w-full">
              <thead class="bg-gray-50 border-b border-gray-200">
                <tr>
                  <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Dispute ID</th>
                  <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Reporter</th>
                  <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Type</th>
                  <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Subject</th>
                  <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Status</th>
                  <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Date</th>
                  <th class="px-6 py-4 text-center text-xs font-semibold text-gray-600 uppercase tracking-wider">Actions</th>
                </tr>
              </thead>
              <tbody id="reportsTableBody" class="divide-y divide-gray-200">
                @include('admin.projectManagement.partials.disputeTable')
              </tbody>
            </table>
          </div>

          <!-- Pagination -->
          <div class="px-6 py-4 bg-gray-50 border-t border-gray-200 flex items-center justify-between">
            <div class="text-sm text-gray-600">
              Showing <span class="font-semibold">{{ $disputes->firstItem() ?? 0 }}-{{ $disputes->lastItem() ?? 0 }}</span> of <span class="font-semibold">{{ $disputes->total() ?? 0 }}</span> reports
            </div>
            <div class="flex items-center gap-2" id="paginationLinks">
              {{ $disputes->links() }}
            </div>
          </div>
        </div>
      </section>

      <!-- View Details Modal -->
      <div id="viewDetailsModal" class="modal-overlay fixed inset-0 bg-black/50 backdrop-blur-sm hidden items-center justify-center z-50 p-4">
        <div class="modal-content bg-white rounded-2xl shadow-2xl max-w-3xl w-full overflow-hidden max-h-[90vh] overflow-y-auto">
          <div class="px-6 py-5 bg-gradient-to-r from-indigo-600 to-purple-600 sticky top-0 z-10">
            <div class="flex items-center justify-between">
              <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-full bg-white/20 backdrop-blur-sm flex items-center justify-center">
                  <i class="fi fi-sr-file-invoice text-white text-xl"></i>
                </div>
                <div>
                  <h3 class="text-xl font-bold text-white">Case Details</h3>
                  <p class="text-indigo-100 text-sm" id="modalCaseId">Case #DR-2025-001</p>
                </div>
              </div>
              <button class="modal-close text-white/80 hover:text-white transition text-2xl leading-none">&times;</button>
            </div>
          </div>

          <div class="p-6 space-y-6">
            <!-- Case Info -->
            <div class="grid grid-cols-2 gap-4">
              <div class="space-y-3">
                <div>
                  <label class="text-xs font-semibold text-gray-500 uppercase">Reporter</label>
                  <p class="text-sm font-semibold text-gray-800" id="modalReporter">-</p>
                </div>
                <div>
                  <label class="text-xs font-semibold text-gray-500 uppercase">Type</label>
                  <p class="text-sm font-semibold text-gray-800" id="modalType">-</p>
                </div>
                <div>
                  <label class="text-xs font-semibold text-gray-500 uppercase">Against</label>
                  <p class="text-sm font-semibold text-gray-800" id="modalAgainst">-</p>
                </div>
              </div>
              <div class="space-y-3">
                <div>
                  <label class="text-xs font-semibold text-gray-500 uppercase">Date Submitted</label>
                  <p class="text-sm font-semibold text-gray-800" id="modalDate">-</p>
                </div>
                <div>
                  <label class="text-xs font-semibold text-gray-500 uppercase">Status</label>
                  <div id="modalStatus"></div>
                </div>
                <div>
                  <label class="text-xs font-semibold text-gray-500 uppercase">Project</label>
                  <p class="text-sm font-semibold text-gray-800" id="modalProject">-</p>
                </div>
              </div>
            </div>

            <!-- Subject/Description moved to Dispute Details to avoid duplication -->

            <!-- Dispute Details Section (always visible) -->
            <div class="border-t border-gray-200 pt-6">
              <h4 class="text-lg font-bold text-gray-800 mb-4 flex items-center gap-2">
                <i class="fi fi-sr-document text-indigo-600"></i>
                Dispute Details
              </h4>

              <div class="space-y-4">
                <!-- Subject (moved earlier visually but keep here for completeness) -->
                <div class="bg-white border border-gray-200 rounded-xl p-4">
                  <label class="text-xs font-semibold text-gray-500 uppercase block mb-2">Subject</label>
                  <p class="text-sm text-gray-800 font-medium" id="modalSubject">-</p>
                </div>

                <!-- Description -->
                <div class="bg-white border border-gray-200 rounded-xl p-4">
                  <label class="text-xs font-semibold text-gray-500 uppercase block mb-2">Description</label>
                  <p class="text-sm text-gray-700 leading-relaxed" id="modalDescription">-</p>
                </div>

                <!-- Requested Action -->
                <div class="bg-white border border-gray-200 rounded-xl p-4">
                  <label class="text-xs font-semibold text-gray-500 uppercase block mb-2">Requested Action</label>
                  <p class="text-sm text-gray-800 font-medium" id="modalRequestedAction">-</p>
                </div>
              </div>
            </div>

            <!-- Linked Progress Report removed per request -->

            <!-- Initial Proofs (Supporting Documents) -->
            <div class="border-t border-gray-200 pt-6">
              <h4 class="text-lg font-bold text-gray-800 mb-4 flex items-center gap-2">
                <i class="fi fi-sr-folder-open text-indigo-600"></i>
                Initial Proofs
              </h4>

              <div id="modalDocumentsSection">
                <div class="bg-gray-50 rounded-xl border border-gray-200 overflow-hidden">
                  <table class="w-full text-sm">
                    <thead class="bg-gradient-to-r from-indigo-50 to-purple-50 border-b border-gray-200">
                      <tr>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase">File</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Date Submitted</th>
                        <th class="px-4 py-3 text-center text-xs font-semibold text-gray-600 uppercase">Action</th>
                      </tr>
                    </thead>
                    <tbody id="modalDocumentsTable" class="divide-y divide-gray-200 bg-white">
                      <!-- Documents will be rendered here -->
                    </tbody>
                  </table>
                </div>
              </div>
            </div>

            <!-- Additional Attachments removed: use Supporting Documents (dispute_files) -->

            <!-- Resubmission Panel (hidden by default; will include header + table) -->
            <div id="sectionResubmission" class="hidden border-t pt-6">
              <h4 class="text-lg font-bold text-gray-800 mb-3 flex items-center gap-2">
                <i class="fi fi-sr-refresh text-indigo-600"></i>
                Resubmitted Report Panel
              </h4>
              <p class="text-xs text-gray-500 mb-4">This section contains uploaded receipts and payment confirmations related to completed milestones</p>

              <div id="modalResubmittedSection">
                <div class="bg-gray-50 rounded-xl border border-gray-200 overflow-hidden">
                  <table class="w-full text-sm">
                    <thead class="bg-gradient-to-r from-indigo-50 to-purple-50 border-b border-gray-200">
                      <tr>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Resubmitted By</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Project ID</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Date Resubmitted</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Status</th>
                        <th class="px-4 py-3 text-center text-xs font-semibold text-gray-600 uppercase">Action</th>
                      </tr>
                    </thead>
                    <tbody id="modalResubmittedTable" class="divide-y divide-gray-200 bg-white">
                      <!-- Resubmitted reports will be rendered here -->
                    </tbody>
                  </table>
                </div>
              </div>
            </div>

            <!-- Feedback Monitoring Panel (hidden by default) -->
            <div id="sectionFeedback" class="hidden border-t pt-6">
              <h4 class="text-lg font-bold text-gray-800 mb-4 flex items-center gap-2">
                <i class="fi fi-sr-comment-alt text-indigo-600"></i>
                Feedback Monitoring Panel
              </h4>

              <div id="modalFeedbackSection" class="bg-gradient-to-br from-gray-50 to-indigo-50 rounded-xl border border-gray-200 p-5">
                <div class="grid grid-cols-2 gap-6">
                  <!-- Left Column: Feedback Info -->
                  <div class="space-y-3">
                    <div>
                      <label class="text-xs font-semibold text-gray-500 uppercase block mb-1">Feedback From</label>
                      <p class="text-sm font-semibold text-gray-800" id="modalFeedbackFrom">-</p>
                    </div>
                    <div>
                      <label class="text-xs font-semibold text-gray-500 uppercase block mb-1">Project ID</label>
                        <p class="text-sm font-semibold text-indigo-600" id="modalResubmissionId">-</p>
                    </div>
                    <div>
                      <label class="text-xs font-semibold text-gray-500 uppercase block mb-1">Response</label>
                      <span id="modalFeedbackResponse" class="inline-flex px-3 py-1 rounded-full text-xs font-semibold bg-emerald-100 text-emerald-700">
                        -
                      </span>
                    </div>
                    <div>
                      <label class="text-xs font-semibold text-gray-500 uppercase block mb-1">Date Submitted</label>
                      <p class="text-sm text-gray-700" id="modalFeedbackDate">-</p>
                    </div>
                  </div>

                  <!-- Right Column: Remarks -->
                  <div>
                    <label class="text-xs font-semibold text-gray-500 uppercase block mb-2">Remarks</label>
                    <div class="bg-white rounded-lg border border-gray-200 p-4 h-[calc(100%-28px)]">
                      <p class="text-sm text-gray-700 leading-relaxed" id="modalFeedbackRemarks">-</p>
                    </div>
                  </div>
                </div>
              </div>
            </div>

            <!-- Project Action Context (shown when case has a linked project) -->
            <div id="sectionProjectContext" class="hidden border-t pt-6">
              <h4 class="text-lg font-bold text-gray-800 mb-4 flex items-center gap-2">
                <i class="fi fi-sr-briefcase text-indigo-600"></i>
                Related Project Details
              </h4>

              <div class="bg-gradient-to-br from-gray-50 to-indigo-50 rounded-xl border border-gray-200 p-5 space-y-4">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                  <div>
                    <label class="text-xs font-semibold text-gray-500 uppercase block mb-1">Project</label>
                    <p class="text-sm font-semibold text-gray-800" id="modalLinkedProjectTitle">-</p>
                    <p class="text-xs text-gray-500" id="modalLinkedProjectId">-</p>
                  </div>
                  <div>
                    <label class="text-xs font-semibold text-gray-500 uppercase block mb-1">Current Status</label>
                    <p class="text-sm font-semibold text-gray-800" id="modalLinkedProjectStatus">-</p>
                  </div>
                  <div>
                    <label class="text-xs font-semibold text-gray-500 uppercase block mb-1">Property Owner</label>
                    <p class="text-sm text-gray-700" id="modalLinkedProjectOwner">-</p>
                  </div>
                  <div>
                    <label class="text-xs font-semibold text-gray-500 uppercase block mb-1">Contractor</label>
                    <p class="text-sm text-gray-700" id="modalLinkedProjectContractor">-</p>
                  </div>
                  <div>
                    <label class="text-xs font-semibold text-gray-500 uppercase block mb-1">Budget</label>
                    <p class="text-sm text-gray-700" id="modalLinkedProjectBudget">-</p>
                  </div>
                  <div>
                    <label class="text-xs font-semibold text-gray-500 uppercase block mb-1">Timeline</label>
                    <p class="text-sm text-gray-700" id="modalLinkedProjectTimeline">-</p>
                  </div>
                </div>

                <div id="modalProjectActionWrap" class="hidden border-t border-gray-200 pt-4 space-y-3">
                  <label class="text-xs font-semibold text-gray-500 uppercase block">Required Case Action</label>
                  <p class="text-sm text-gray-700" id="modalProjectActionHint">-</p>

                  <div>
                    <label class="block text-sm font-semibold text-gray-800 mb-2">Action Reason *</label>
                    <textarea id="modalProjectActionReason" rows="3" class="w-full px-4 py-2.5 border-2 border-gray-200 rounded-xl focus:ring-2 focus:ring-indigo-300 focus:border-indigo-300 transition resize-none" placeholder="Provide action reason (for example: why this project should be halted)..."></textarea>
                  </div>

                  <div>
                    <label class="block text-sm font-semibold text-gray-800 mb-2">Project Remarks (Optional)</label>
                    <textarea id="modalProjectRemarks" rows="2" class="w-full px-4 py-2.5 border-2 border-gray-200 rounded-xl focus:ring-2 focus:ring-indigo-300 focus:border-indigo-300 transition resize-none" placeholder="Additional internal remarks for this project action..."></textarea>
                  </div>

                  <div class="flex items-center justify-end">
                    <button id="modalProjectActionBtn" class="px-6 py-2.5 rounded-xl bg-gradient-to-r from-indigo-600 to-purple-600 hover:from-indigo-700 hover:to-purple-700 text-white font-semibold shadow-md hover:shadow-lg transition">
                      Apply Project Action
                    </button>
                  </div>
                </div>

                <div id="modalResolvedProjectActions" class="hidden border-t border-gray-200 pt-4 space-y-3">
                  <label class="text-xs font-semibold text-gray-500 uppercase block">Post-Resolution Project Decision</label>
                  <p class="text-sm text-gray-700">This dispute is resolved and the project is halted. Choose the final project outcome.</p>
                  <div class="flex items-center justify-end gap-3">
                    <button id="modalResumeProjectBtn" class="px-5 py-2.5 rounded-xl bg-gradient-to-r from-emerald-600 to-teal-600 hover:from-emerald-700 hover:to-teal-700 text-white font-semibold shadow-md hover:shadow-lg transition">
                      Resume Project
                    </button>
                    <button id="modalTerminateProjectBtn" class="px-5 py-2.5 rounded-xl bg-gradient-to-r from-red-600 to-rose-600 hover:from-red-700 hover:to-rose-700 text-white font-semibold shadow-md hover:shadow-lg transition">
                      Terminate Project
                    </button>
                  </div>
                </div>
              </div>
            </div>

            <!-- Action Containers - REMOVED ACTION BUTTONS FROM VIEW MODAL -->
            <div class="flex items-center justify-between gap-3 pt-6 border-t border-gray-200">
              <div class="flex items-center gap-2 text-xs text-gray-500">
                <i class="fi fi-rr-shield-check text-indigo-500"></i>
                <span>All actions are logged and monitored for compliance.</span>
              </div>
              <div class="flex items-center gap-3">
                <button class="modal-close px-6 py-2.5 rounded-xl border-2 border-gray-300 text-gray-700 font-semibold hover:bg-gray-50 transition">Close</button>
                <!-- Action buttons removed - View modal is for viewing only -->
              </div>
            </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Project Decision Confirmation Modal -->
      <div id="projectDecisionModal" class="modal-overlay fixed inset-0 bg-black/50 backdrop-blur-sm hidden items-center justify-center z-50 p-4">
        <div class="modal-content bg-white rounded-2xl shadow-2xl max-w-md w-full overflow-hidden">
          <div class="px-6 py-5 bg-gradient-to-r from-indigo-600 to-purple-600">
            <div class="flex items-center justify-between">
              <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-full bg-white/20 backdrop-blur-sm flex items-center justify-center">
                  <i class="fi fi-sr-briefcase text-white text-xl"></i>
                </div>
                <h3 class="text-xl font-bold text-white" id="projectDecisionTitle">Confirm Project Decision</h3>
              </div>
              <button class="modal-close text-white/80 hover:text-white transition text-2xl leading-none">&times;</button>
            </div>
          </div>
          <div class="p-6 space-y-5">
            <div class="flex items-start gap-4 p-4 bg-indigo-50 border-l-4 border-indigo-500 rounded-lg">
              <i class="fi fi-rr-info-circle text-indigo-600 text-xl mt-0.5"></i>
              <div class="flex-1">
                <p class="text-sm text-indigo-900 font-semibold mb-1" id="projectDecisionLabel">Proceed with selected project decision</p>
                <p class="text-xs text-indigo-800">This action will be logged and applied immediately.</p>
              </div>
            </div>

            <div>
              <label class="block text-sm font-semibold text-gray-800 mb-2">Admin Reason *</label>
              <textarea id="projectDecisionReason" rows="4" class="w-full px-4 py-2.5 border-2 border-gray-200 rounded-xl focus:ring-2 focus:ring-indigo-300 focus:border-indigo-300 transition resize-none" placeholder="Provide reason for this decision..."></textarea>
            </div>

            <div>
              <label class="block text-sm font-semibold text-gray-800 mb-2">Project Remarks (Optional)</label>
              <textarea id="projectDecisionRemarks" rows="2" class="w-full px-4 py-2.5 border-2 border-gray-200 rounded-xl focus:ring-2 focus:ring-indigo-300 focus:border-indigo-300 transition resize-none" placeholder="Optional internal remarks..."></textarea>
            </div>

            <div class="flex items-center justify-end gap-3 pt-3 border-t border-gray-200">
              <button class="modal-close px-6 py-2.5 rounded-xl border-2 border-gray-300 text-gray-700 font-semibold hover:bg-gray-50 transition">Cancel</button>
              <button id="confirmProjectDecisionBtn" class="px-6 py-2.5 rounded-xl bg-gradient-to-r from-indigo-600 to-purple-600 hover:from-indigo-700 hover:to-purple-700 text-white font-semibold shadow-md hover:shadow-lg transition">
                Confirm
              </button>
            </div>
          </div>
        </div>
      </div>

      <!-- Resolve Confirmation Modal -->
      <div id="resolveConfirmModal" class="modal-overlay fixed inset-0 bg-black/50 backdrop-blur-sm hidden items-center justify-center z-50 p-4">
        <div class="modal-content bg-white rounded-2xl shadow-2xl max-w-md w-full overflow-hidden">
          <div class="px-6 py-5 bg-gradient-to-r from-emerald-600 to-teal-600">
            <div class="flex items-center justify-between">
              <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-full bg-white/20 backdrop-blur-sm flex items-center justify-center">
                  <i class="fi fi-sr-check-circle text-white text-xl"></i>
                </div>
                <h3 class="text-xl font-bold text-white">Resolve Case?</h3>
              </div>
              <button class="modal-close text-white/80 hover:text-white transition text-2xl leading-none">&times;</button>
            </div>
          </div>
          <div class="p-6 space-y-5">
            <div class="flex items-start gap-4 p-4 bg-emerald-50 border-l-4 border-emerald-500 rounded-lg">
              <i class="fi fi-rr-info-circle text-emerald-600 text-xl mt-0.5"></i>
              <div class="flex-1">
                <p class="text-sm text-emerald-900 font-semibold mb-1">Mark this case as resolved</p>
                <p class="text-xs text-emerald-800">This will close the case and notify all parties involved.</p>
              </div>
            </div>

            <div>
              <label class="block text-sm font-semibold text-gray-800 mb-2">Resolution Notes *</label>
              <textarea id="resolutionNotes" rows="4" class="w-full px-4 py-2.5 border-2 border-gray-200 rounded-xl focus:ring-2 focus:ring-emerald-300 focus:border-emerald-300 transition resize-none" placeholder="Provide details about how this case was resolved..."></textarea>
            </div>

            <!-- Penalty Panel -->
            <div class="border-t border-gray-200 pt-4">
              <div class="flex items-center gap-3 mb-3">
                <input type="checkbox" id="resolveApplyPenalty" class="w-4 h-4 rounded border-gray-300 text-emerald-600 focus:ring-emerald-500">
                <label for="resolveApplyPenalty" class="text-sm font-semibold text-gray-800">Apply penalty to the reported user</label>
              </div>
              <div id="resolvePenaltyFields" class="hidden space-y-3 pl-7">
                <div>
                  <label class="block text-xs font-semibold text-gray-600 mb-1">Reported User</label>
                  <p class="text-sm font-semibold text-gray-800" id="resolvePenaltyUserName">-</p>
                  <p class="text-xs text-gray-500" id="resolvePenaltyUserType">-</p>
                </div>
                <div>
                  <label class="block text-xs font-semibold text-gray-600 mb-1">Penalty Type *</label>
                  <select id="resolvePenaltyType" class="w-full px-3 py-2 border-2 border-gray-200 rounded-xl text-sm focus:ring-2 focus:ring-emerald-300 focus:border-emerald-300">
                    <option value="">Select penalty...</option>
                    <option value="temporary_ban">Temporary Ban</option>
                    <option value="permanent_ban">Permanent Ban / Terminate</option>
                  </select>
                </div>
                <div id="resolveBanDurationWrap" class="hidden">
                  <label class="block text-xs font-semibold text-gray-600 mb-1">Ban Duration *</label>
                  <select id="resolveBanDuration" class="w-full px-3 py-2 border-2 border-gray-200 rounded-xl text-sm focus:ring-2 focus:ring-emerald-300 focus:border-emerald-300">
                    <option value="7">7 Days</option>
                    <option value="14">14 Days</option>
                    <option value="30" selected>30 Days</option>
                    <option value="60">60 Days</option>
                    <option value="90">90 Days</option>
                  </select>
                </div>
              </div>
            </div>

            <div class="flex items-center justify-end gap-3 pt-3 border-t border-gray-200">
              <button class="modal-close px-6 py-2.5 rounded-xl border-2 border-gray-300 text-gray-700 font-semibold hover:bg-gray-50 transition">Cancel</button>
              <button id="confirmResolveBtn" class="px-6 py-2.5 rounded-xl bg-gradient-to-r from-emerald-600 to-teal-600 hover:from-emerald-700 hover:to-teal-700 text-white font-semibold shadow-md hover:shadow-lg transition">
                Confirm Resolution
              </button>
            </div>
          </div>
        </div>
      </div>

      <!-- Download Confirmation Modal -->
      <div id="downloadConfirmModal" class="modal-overlay fixed inset-0 bg-black/50 backdrop-blur-sm hidden items-center justify-center z-50 p-4">
        <div class="modal-content bg-white rounded-2xl shadow-2xl max-w-md w-full overflow-hidden">
          <div class="px-6 py-5 bg-gradient-to-r from-blue-600 to-indigo-600">
            <div class="flex items-center justify-between">
              <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-full bg-white/20 backdrop-blur-sm flex items-center justify-center">
                  <i class="fi fi-sr-download text-white text-xl"></i>
                </div>
                <h3 class="text-xl font-bold text-white">Download File?</h3>
              </div>
              <button class="modal-close text-white/80 hover:text-white transition text-2xl leading-none">&times;</button>
            </div>
          </div>
          <div class="p-6 space-y-5">
            <div class="flex items-start gap-4 p-4 bg-blue-50 border-l-4 border-blue-500 rounded-lg">
              <i class="fi fi-rr-info-circle text-blue-600 text-xl mt-0.5"></i>
              <div class="flex-1">
                <p class="text-sm text-blue-900 font-semibold mb-1">Download this file</p>
                <p class="text-xs text-blue-800" id="downloadFileName">File: document.pdf</p>
              </div>
            </div>

            <div class="bg-gray-50 rounded-xl border border-gray-200 p-4">
              <div class="flex items-center gap-3">
                <i class="fi fi-sr-file-pdf text-red-500 text-3xl"></i>
                <div class="flex-1">
                  <p class="text-sm font-semibold text-gray-800" id="downloadFileNameDisplay">document.pdf</p>
                  <p class="text-xs text-gray-500">Click confirm to download this file to your device</p>
                </div>
              </div>
            </div>

            <div class="flex items-center justify-end gap-3 pt-3 border-t border-gray-200">
              <button class="modal-close px-6 py-2.5 rounded-xl border-2 border-gray-300 text-gray-700 font-semibold hover:bg-gray-50 transition">Cancel</button>
              <button id="confirmDownloadBtn" class="px-6 py-2.5 rounded-xl bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-700 hover:to-indigo-700 text-white font-semibold shadow-md hover:shadow-lg transition">
                <i class="fi fi-sr-download mr-2"></i>
                Download
              </button>
            </div>
          </div>
        </div>
      </div>

      <!-- Delete Confirmation Modal -->
      <div id="deleteConfirmModal" class="modal-overlay fixed inset-0 bg-black/50 backdrop-blur-sm hidden items-center justify-center z-50 p-4">
        <div class="modal-content bg-white rounded-2xl shadow-2xl max-w-md w-full overflow-hidden">
          <div class="px-6 py-5 bg-gradient-to-r from-red-600 to-rose-600">
            <div class="flex items-center justify-between">
              <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-full bg-white/20 backdrop-blur-sm flex items-center justify-center">
                  <i class="fi fi-sr-trash text-white text-xl"></i>
                </div>
                <h3 class="text-xl font-bold text-white">Delete File?</h3>
              </div>
              <button class="modal-close text-white/80 hover:text-white transition text-2xl leading-none">&times;</button>
            </div>
          </div>
          <div class="p-6 space-y-5">
            <div class="flex items-start gap-4 p-4 bg-red-50 border-l-4 border-red-500 rounded-lg">
              <i class="fi fi-rr-triangle-warning text-red-600 text-xl mt-0.5"></i>
              <div class="flex-1">
                <p class="text-sm text-red-900 font-semibold mb-1">This action cannot be undone</p>
                <p class="text-xs text-red-800">The file will be permanently removed from the system.</p>
              </div>
            </div>

            <div class="bg-gray-50 rounded-xl border border-gray-200 p-4">
              <div class="flex items-center gap-3">
                <i class="fi fi-sr-file-pdf text-red-500 text-3xl"></i>
                <div class="flex-1">
                  <p class="text-sm font-semibold text-gray-800" id="deleteFileNameDisplay">document.pdf</p>
                  <p class="text-xs text-gray-500">Uploaded by <span id="deleteFileUploader">John Doe</span> on <span id="deleteFileDate">Nov 20, 2025</span></p>
                </div>
              </div>
            </div>

            <div>
              <label class="block text-sm font-semibold text-gray-800 mb-2">Reason for Deletion (Optional)</label>
              <textarea id="deleteReason" rows="3" class="w-full px-4 py-2.5 border-2 border-gray-200 rounded-xl focus:ring-2 focus:ring-red-300 focus:border-red-300 transition resize-none" placeholder="Provide a reason for deleting this file..."></textarea>
            </div>

            <div class="flex items-center justify-end gap-3 pt-3 border-t border-gray-200">
              <button class="modal-close px-6 py-2.5 rounded-xl border-2 border-gray-300 text-gray-700 font-semibold hover:bg-gray-50 transition">Cancel</button>
              <button id="confirmDeleteBtn" class="px-6 py-2.5 rounded-xl bg-gradient-to-r from-red-600 to-rose-600 hover:from-red-700 hover:to-rose-700 text-white font-semibold shadow-md hover:shadow-lg transition">
                <i class="fi fi-sr-trash mr-2"></i>
                Delete File
              </button>
            </div>
          </div>
        </div>
      </div>



      <!-- Resubmitted Report Details Modal -->
      <div id="resubmittedReportModal" class="modal-overlay fixed inset-0 bg-black/50 backdrop-blur-sm hidden items-center justify-center z-50 p-4">
        <div class="modal-content bg-white rounded-2xl shadow-2xl max-w-2xl w-full overflow-hidden max-h-[90vh] overflow-y-auto">
          <div class="px-6 py-5 bg-gradient-to-r from-indigo-600 to-purple-600 sticky top-0 z-10">
            <div class="flex items-center justify-between">
              <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-full bg-white/20 backdrop-blur-sm flex items-center justify-center">
                  <i class="fi fi-sr-refresh text-white text-xl"></i>
                </div>
                <h3 class="text-xl font-bold text-white">Resubmitted Report</h3>
              </div>
              <button class="modal-close text-white/80 hover:text-white transition text-2xl leading-none">&times;</button>
            </div>
          </div>

          <div class="p-6 space-y-6">
            <!-- Status Badge -->
            <div>
              <label class="text-xs font-semibold text-gray-500 uppercase block mb-2">Status:</label>
              <span id="resubmittedStatus" class="inline-flex px-4 py-2 rounded-full text-sm font-semibold bg-amber-100 text-amber-700 border border-amber-300">
                Under Review
              </span>
            </div>

            <!-- Report Details Grid -->
            <div class="grid grid-cols-2 gap-4">
              <div class="bg-gray-50 rounded-xl border border-gray-200 p-4">
                <label class="text-xs font-semibold text-gray-500 uppercase block mb-2">Resubmission ID</label>
                <p class="text-sm font-semibold text-gray-800" id="resubmittedId">-</p>
              </div>
              <div class="bg-gray-50 rounded-xl border border-gray-200 p-4">
                <label class="text-xs font-semibold text-gray-500 uppercase block mb-2">Resubmitted By</label>
                <p class="text-sm font-semibold text-gray-800" id="resubmittedBy">-</p>
              </div>
              <div class="bg-gray-50 rounded-xl border border-gray-200 p-4">
                <label class="text-xs font-semibold text-gray-500 uppercase block mb-2">Resubmission Type</label>
                <p class="text-sm font-semibold text-gray-800" id="resubmittedType">-</p>
              </div>
              <div class="bg-gray-50 rounded-xl border border-gray-200 p-4">
                <label class="text-xs font-semibold text-gray-500 uppercase block mb-2">Date Resubmitted</label>
                <p class="text-sm font-semibold text-gray-800" id="resubmittedDate">-</p>
              </div>
            </div>

            <!-- Remarks Section -->
            <div>
              <label class="text-sm font-semibold text-gray-700 block mb-2">Remarks</label>
              <div class="bg-gradient-to-br from-gray-50 to-indigo-50 rounded-xl border border-gray-200 p-4">
                <textarea id="resubmittedRemarks" rows="4" class="w-full px-4 py-3 border-2 border-gray-200 rounded-xl focus:ring-2 focus:ring-indigo-300 focus:border-indigo-300 transition resize-none bg-white" placeholder="Write a compelling message to the client. Tell them about your expertise and why you're a great fit."></textarea>
              </div>
            </div>

            <!-- Uploaded Files Section -->
            <div class="border-t border-gray-200 pt-6">
              <h4 class="text-lg font-bold text-gray-800 mb-4 flex items-center gap-2">
                <i class="fi fi-sr-folder-open text-indigo-600"></i>
                Uploaded Files
              </h4>

              <div class="bg-gray-50 rounded-xl border border-gray-200 overflow-hidden">
                <table class="w-full text-sm">
                  <thead class="bg-gradient-to-r from-indigo-50 to-purple-50 border-b border-gray-200">
                    <tr>
                      <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase w-12">
                        <input type="checkbox" class="w-4 h-4 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                      </th>
                      <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Files</th>
                      <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Date Submitted</th>
                      <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Uploaded By</th>
                      <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Position</th>
                      <th class="px-4 py-3 text-center text-xs font-semibold text-gray-600 uppercase">Action</th>
                    </tr>
                  </thead>
                  <tbody id="resubmittedFilesTable" class="divide-y divide-gray-200 bg-white">
                    <!-- Files will be rendered here -->
                  </tbody>
                </table>
              </div>
            </div>

            <!-- Actions -->
            <div class="flex items-center justify-end gap-3 pt-4 border-t border-gray-200">
              <button class="modal-close px-6 py-2.5 rounded-xl border-2 border-gray-300 text-gray-700 font-semibold hover:bg-gray-50 transition">Close</button>
              <button id="approveResubmittedBtn" class="px-6 py-2.5 rounded-xl bg-gradient-to-r from-emerald-600 to-teal-600 hover:from-emerald-700 hover:to-teal-700 text-white font-semibold shadow-md hover:shadow-lg transition">
                Approve
              </button>
              <button id="rejectResubmittedBtn" class="px-6 py-2.5 rounded-xl bg-gradient-to-r from-red-600 to-rose-600 hover:from-red-700 hover:to-rose-700 text-white font-semibold shadow-md hover:shadow-lg transition">
                Reject
              </button>
            </div>
          </div>
        </div>
      </div>

      <!-- Approve Resubmitted Report Confirmation Modal -->
      <div id="approveResubmittedConfirmModal" class="modal-overlay fixed inset-0 bg-black/50 backdrop-blur-sm hidden items-center justify-center z-50 p-4">
        <div class="modal-content bg-white rounded-2xl shadow-2xl max-w-md w-full overflow-hidden">
          <div class="px-6 py-5 bg-gradient-to-r from-emerald-600 to-teal-600">
            <div class="flex items-center justify-between">
              <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-full bg-white/20 backdrop-blur-sm flex items-center justify-center">
                  <i class="fi fi-sr-check-circle text-white text-xl"></i>
                </div>
                <h3 class="text-xl font-bold text-white">Approve Report?</h3>
              </div>
              <button class="modal-close text-white/80 hover:text-white transition text-2xl leading-none">&times;</button>
            </div>
          </div>
          <div class="p-6 space-y-5">
            <div class="flex items-start gap-4 p-4 bg-emerald-50 border-l-4 border-emerald-500 rounded-lg">
              <i class="fi fi-rr-info-circle text-emerald-600 text-xl mt-0.5"></i>
              <div class="flex-1">
                <p class="text-sm text-emerald-900 font-semibold mb-1">Approve this resubmitted report</p>
                <p class="text-xs text-emerald-800">This will mark the report as approved and notify all parties involved.</p>
              </div>
            </div>

            <div class="bg-gray-50 rounded-xl border border-gray-200 p-4">
              <div class="space-y-2">
                <div class="flex items-center justify-between">
                  <span class="text-xs font-semibold text-gray-500 uppercase">Resubmission ID</span>
                  <span class="text-sm font-bold text-gray-800" id="approveResubmissionId">-</span>
                </div>
                <div class="flex items-center justify-between">
                  <span class="text-xs font-semibold text-gray-500 uppercase">Resubmitted By</span>
                  <span class="text-sm font-semibold text-gray-800" id="approveResubmittedBy">-</span>
                </div>
                <div class="flex items-center justify-between">
                  <span class="text-xs font-semibold text-gray-500 uppercase">Type</span>
                  <span class="text-sm text-gray-600" id="approveResubmissionType">-</span>
                </div>
              </div>
            </div>

            <div>
              <label class="block text-sm font-semibold text-gray-800 mb-2">Approval Notes (Optional)</label>
              <textarea id="approveNotes" rows="3" class="w-full px-4 py-2.5 border-2 border-gray-200 rounded-xl focus:ring-2 focus:ring-emerald-300 focus:border-emerald-300 transition resize-none" placeholder="Add any approval notes or comments..."></textarea>
            </div>

            <div class="flex items-center justify-end gap-3 pt-3 border-t border-gray-200">
              <button class="modal-close px-6 py-2.5 rounded-xl border-2 border-gray-300 text-gray-700 font-semibold hover:bg-gray-50 transition">Cancel</button>
              <button id="confirmApproveResubmittedBtn" class="px-6 py-2.5 rounded-xl bg-gradient-to-r from-emerald-600 to-teal-600 hover:from-emerald-700 hover:to-teal-700 text-white font-semibold shadow-md hover:shadow-lg transition">
                <i class="fi fi-sr-check-circle mr-2"></i>
                Approve Report
              </button>
            </div>
          </div>
        </div>
      </div>

      <!-- Reject Resubmitted Report Confirmation Modal -->
      <div id="rejectResubmittedConfirmModal" class="modal-overlay fixed inset-0 bg-black/50 backdrop-blur-sm hidden items-center justify-center z-50 p-4">
        <div class="modal-content bg-white rounded-2xl shadow-2xl max-w-md w-full overflow-hidden">
          <div class="px-6 py-5 bg-gradient-to-r from-red-600 to-rose-600">
            <div class="flex items-center justify-between">
              <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-full bg-white/20 backdrop-blur-sm flex items-center justify-center">
                  <i class="fi fi-sr-cross-circle text-white text-xl"></i>
                </div>
                <h3 class="text-xl font-bold text-white">Reject Report?</h3>
              </div>
              <button class="modal-close text-white/80 hover:text-white transition text-2xl leading-none">&times;</button>
            </div>
          </div>
          <div class="p-6 space-y-5">
            <div class="flex items-start gap-4 p-4 bg-red-50 border-l-4 border-red-500 rounded-lg">
              <i class="fi fi-rr-triangle-warning text-red-600 text-xl mt-0.5"></i>
              <div class="flex-1">
                <p class="text-sm text-red-900 font-semibold mb-1">Reject this resubmitted report</p>
                <p class="text-xs text-red-800">The submitter will be notified and may need to resubmit with corrections.</p>
              </div>
            </div>

            <div class="bg-gray-50 rounded-xl border border-gray-200 p-4">
              <div class="space-y-2">
                <div class="flex items-center justify-between">
                  <span class="text-xs font-semibold text-gray-500 uppercase">Resubmission ID</span>
                  <span class="text-sm font-bold text-gray-800" id="rejectResubmissionId">-</span>
                </div>
                <div class="flex items-center justify-between">
                  <span class="text-xs font-semibold text-gray-500 uppercase">Resubmitted By</span>
                  <span class="text-sm font-semibold text-gray-800" id="rejectResubmittedBy">-</span>
                </div>
                <div class="flex items-center justify-between">
                  <span class="text-xs font-semibold text-gray-500 uppercase">Type</span>
                  <span class="text-sm text-gray-600" id="rejectResubmissionType">-</span>
                </div>
              </div>
            </div>

            <div>
              <label class="block text-sm font-semibold text-gray-800 mb-2">Rejection Reason *</label>
              <textarea id="rejectReason" rows="4" class="w-full px-4 py-2.5 border-2 border-gray-200 rounded-xl focus:ring-2 focus:ring-red-300 focus:border-red-300 transition resize-none" placeholder="Explain why this report is being rejected and what needs to be corrected..."></textarea>
            </div>

            <div class="flex items-center justify-end gap-3 pt-3 border-t border-gray-200">
              <button class="modal-close px-6 py-2.5 rounded-xl border-2 border-gray-300 text-gray-700 font-semibold hover:bg-gray-50 transition">Cancel</button>
              <button id="confirmRejectResubmittedBtn" class="px-6 py-2.5 rounded-xl bg-gradient-to-r from-red-600 to-rose-600 hover:from-red-700 hover:to-rose-700 text-white font-semibold shadow-md hover:shadow-lg transition">
                <i class="fi fi-sr-cross-circle mr-2"></i>
                Reject Report
              </button>
            </div>
          </div>
        </div>
      </div>

      <!-- Download Resubmitted File Confirmation Modal -->
      <div id="downloadResubmittedFileModal" class="modal-overlay fixed inset-0 bg-black/50 backdrop-blur-sm hidden items-center justify-center z-50 p-4">
        <div class="modal-content bg-white rounded-2xl shadow-2xl max-w-md w-full overflow-hidden">
          <div class="px-6 py-5 bg-gradient-to-r from-indigo-600 to-purple-600">
            <div class="flex items-center justify-between">
              <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-full bg-white/20 backdrop-blur-sm flex items-center justify-center">
                  <i class="fi fi-sr-download text-white text-xl"></i>
                </div>
                <h3 class="text-xl font-bold text-white">Download File?</h3>
              </div>
              <button class="modal-close text-white/80 hover:text-white transition text-2xl leading-none">&times;</button>
            </div>
          </div>
          <div class="p-6 space-y-5">
            <div class="flex items-start gap-4 p-4 bg-indigo-50 border-l-4 border-indigo-500 rounded-lg">
              <i class="fi fi-rr-info-circle text-indigo-600 text-xl mt-0.5"></i>
              <div class="flex-1">
                <p class="text-sm text-indigo-900 font-semibold mb-1">Download resubmitted file</p>
                <p class="text-xs text-indigo-800" id="downloadResubmittedFileName">File: document.pdf</p>
              </div>
            </div>

            <div class="bg-gray-50 rounded-xl border border-gray-200 p-4">
              <div class="flex items-center gap-3">
                <i class="fi fi-sr-file-pdf text-red-500 text-3xl"></i>
                <div class="flex-1">
                  <p class="text-sm font-semibold text-gray-800" id="downloadResubmittedFileNameDisplay">document.pdf</p>
                  <p class="text-xs text-gray-500">From resubmitted report: <span id="downloadResubmittedReportId" class="font-semibold text-indigo-600">RSB-1234</span></p>
                </div>
              </div>
            </div>

            <div class="flex items-center justify-end gap-3 pt-3 border-t border-gray-200">
              <button class="modal-close px-6 py-2.5 rounded-xl border-2 border-gray-300 text-gray-700 font-semibold hover:bg-gray-50 transition">Cancel</button>
              <button id="confirmDownloadResubmittedBtn" class="px-6 py-2.5 rounded-xl bg-gradient-to-r from-indigo-600 to-purple-600 hover:from-indigo-700 hover:to-purple-700 text-white font-semibold shadow-md hover:shadow-lg transition">
                <i class="fi fi-sr-download mr-2"></i>
                Download
              </button>
            </div>
          </div>
        </div>
      </div>

    </main>


  <script src="{{ asset('js/admin/projectManagement/disputesReports.js') }}" defer></script>

</body>

</html>
