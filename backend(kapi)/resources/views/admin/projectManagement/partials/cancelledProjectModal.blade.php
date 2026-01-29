  <!-- Cancelled/Terminated Project Modal -->
<div id="cancelledProjectModal" class="fixed inset-0 bg-black/50 backdrop-blur-sm z-50 hidden transition-opacity duration-300">
  <div class="absolute inset-0 flex items-center justify-center p-4">
    <div class="bg-white w-full max-w-5xl rounded-2xl shadow-2xl relative transform transition-all duration-300 scale-100 max-h-[90vh] overflow-hidden flex flex-col">
      @isset($project)
      <!-- Header -->
      <div class="relative px-6 py-10 bg-gradient-to-r from-gray-600 to-gray-700 text-white flex-shrink-0">
        <button onclick="hideCancelledProjectModal()" class="absolute top-6 right-6 w-8 h-8 rounded-lg bg-white/10 hover:bg-white/20 backdrop-blur-sm flex items-center justify-center transition-all duration-200">
          <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
          </svg>
        </button>
        <div class="flex items-start gap-4">
          <div class="w-14 h-14 rounded-xl bg-white/10 backdrop-blur-sm flex items-center justify-center flex-shrink-0">
            <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
          </div>
          <div class="flex-1">
            <h2 class="text-2xl font-bold">{{ $project->project_title }}</h2>
            <p class="text-gray-100 text-sm mt-1">{{ $project->project_location }}</p>
            <div class="flex items-center gap-2 mt-3">
              <span class="px-3 py-1 rounded-full text-xs font-semibold bg-gray-500/20 backdrop-blur-sm">Cancelled</span>
              <span class="text-xs text-gray-200">Terminated on <span class="font-medium">{{ \Carbon\Carbon::parse($project->terminated_at)->format('F j, Y') }}</span></span>
            </div>
          </div>
        </div>
      </div>

        <!-- Content -->
        <div class="p-6 overflow-y-auto flex-1 space-y-6">
          <!-- Termination Information Section -->
          <div class="bg-gray-50 rounded-xl p-6 border border-gray-200">
            <h3 class="text-sm font-bold text-gray-900 mb-4 flex items-center gap-2">
              <svg class="w-4 h-4 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
              </svg>
              Termination Details
            </h3>
            <div class="grid grid-cols-2 gap-6">
              <!-- Left Column -->
              <div class="space-y-4">
                <div>
                  <p class="text-xs font-semibold text-gray-500 mb-1">Terminated By</p>
                  <p class="text-sm text-gray-900 font-medium">{{ $project->terminated_by ?? 'N/A' }}</p>
                </div>
                <div>
                  <p class="text-xs font-semibold text-gray-500 mb-1">Reason</p>
                  <p class="text-sm text-gray-900">{{ $project->reason ?? 'N/A' }}</p>
                </div>
                <div>
                  <p class="text-xs font-semibold text-gray-500 mb-1">Remarks</p>
                  <p class="text-sm text-gray-900">{{ $project->remarks ?? 'N/A' }}</p>
                </div>
              </div>

              <!-- Right Column -->
              <div class="space-y-4">
                <div>
                  <p class="text-xs font-semibold text-gray-500 mb-1">Notice Sent On</p>
                  <p class="text-sm text-gray-900 font-medium">{{ \Carbon\Carbon::parse($project->terminated_at)->format('F j, Y') }}</p>
                </div>
                <div>
                  <p class="text-xs font-semibold text-gray-500 mb-1">Last Active Milestone</p>
                  <p class="text-sm text-gray-900 font-medium">{{ $project->last_active_milestone }}</p>
                </div>
                <!-- Supporting Files -->
                <div>
                  <p class="text-xs font-semibold text-gray-500 mb-2">Supporting Files</p>
                  <div class="space-y-2">
                    @forelse($project->termination_files as $file)
                      <a href="{{ asset('storage/' . $file->file_path) }}" target="_blank" class="flex items-center gap-2 text-xs text-blue-600 hover:text-blue-700 hover:underline">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/>
                        </svg>
                        {{ basename($file->file_path) }}
                      </a>
                    @empty
                      <p class="text-xs text-gray-400">No files uploaded</p>
                    @endforelse
                  </div>
                </div>
              </div>
            </div>
          </div>

          <!-- Project and Contractor Information in 2-Column Layout -->
          <div class="grid grid-cols-2 gap-6">
            <!-- Project Information Card -->
            <div class="bg-gradient-to-br from-orange-50 to-white rounded-xl border border-orange-200/50 overflow-hidden">
              <div class="px-4 py-3 bg-gradient-to-r from-orange-500/10 to-orange-600/5 border-b border-orange-200/30">
                <h3 class="text-sm font-bold text-orange-900 flex items-center gap-2">
                  <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                  </svg>
                  Project Information
                </h3>
              </div>
              <div class="p-4 space-y-3">
                <div>
                  <p class="text-xs font-semibold text-gray-500 mb-1">Project Title</p>
                  <p class="text-sm text-gray-900 font-medium">{{ $project->project_title }}</p>
                </div>
                <div>
                  <p class="text-xs font-semibold text-gray-500 mb-1">Property Type</p>
                  <p class="text-sm text-gray-900">{{ $project->property_type ?? 'N/A' }}</p>
                </div>
                <div>
                  <p class="text-xs font-semibold text-gray-500 mb-1">Property Address</p>
                  <p class="text-sm text-gray-900">{{ $project->project_location }}</p>
                </div>
                <div>
                  <p class="text-xs font-semibold text-gray-500 mb-1">Lot Size (sqm)</p>
                  <p class="text-sm text-gray-900">{{ $project->lot_size ? number_format($project->lot_size, 2) : 'N/A' }}</p>
                </div>
                <div>
                  <p class="text-xs font-semibold text-gray-500 mb-1">Floor Area (sqm)</p>
                  <p class="text-sm text-gray-900">{{ $project->floor_area ? number_format($project->floor_area, 2) : 'N/A' }}</p>
                </div>
                <div>
                  <p class="text-xs font-semibold text-gray-500 mb-1">Timeline</p>
                  <p class="text-sm text-gray-900">{{ $project->timeline }}</p>
                </div>
                <div>
                  <p class="text-xs font-semibold text-gray-500 mb-1">Project Cost</p>
                  <p class="text-sm text-gray-900 font-semibold text-orange-600">₱{{ number_format($project->project_cost, 2) }}</p>
                </div>
                <div>
                  <p class="text-xs font-semibold text-gray-500 mb-1">Deadline</p>
                  <p class="text-sm text-gray-900">{{ $project->deadline }}</p>
                </div>
                <div>
                  <p class="text-xs font-semibold text-gray-500 mb-2">Uploaded Files</p>
                  <div class="space-y-1">
                    @forelse($project->project_files as $file)
                      <a href="{{ asset('storage/' . $file->file_path) }}" target="_blank" class="flex items-center gap-2 text-xs text-blue-600 hover:text-blue-700 hover:underline">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/>
                        </svg>
                        {{ ucfirst(str_replace('_', ' ', $file->file_type)) }}
                      </a>
                    @empty
                      <p class="text-xs text-gray-400">No files uploaded</p>
                    @endforelse
                  </div>
                </div>
              </div>
            </div>

            <!-- Contractor Information Card -->
            <div class="bg-gradient-to-br from-blue-50 to-white rounded-xl border border-blue-200/50 overflow-hidden">
              <div class="px-4 py-3 bg-gradient-to-r from-blue-500/10 to-blue-600/5 border-b border-blue-200/30">
                <h3 class="text-sm font-bold text-blue-900 flex items-center gap-2">
                  <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                  </svg>
                  Contractor Information
                </h3>
              </div>
              <div class="p-4 space-y-3">
                <div>
                  <p class="text-xs font-semibold text-gray-500 mb-1">Contractor Name</p>
                  <p class="text-sm text-gray-900 font-medium">{{ $project->contractor_name ?? 'N/A' }}</p>
                </div>
                <div>
                  <p class="text-xs font-semibold text-gray-500 mb-1">Company</p>
                  <p class="text-sm text-gray-900">{{ $project->company_name ?? 'N/A' }}</p>
                </div>
                <div>
                  <p class="text-xs font-semibold text-gray-500 mb-1">Email Address</p>
                  <p class="text-sm text-gray-900">{{ $project->contractor_email ?? 'N/A' }}</p>
                </div>
                <div>
                  <p class="text-xs font-semibold text-gray-500 mb-1">PCAB No.</p>
                  <p class="text-sm text-gray-900">{{ $project->contractor_pcab ?? 'N/A' }}</p>
                </div>
                <div>
                  <p class="text-xs font-semibold text-gray-500 mb-1">PCAB Category</p>
                  <p class="text-sm text-gray-900">{{ $project->contractor_category ?? 'N/A' }}</p>
                </div>
                <div>
                  <p class="text-xs font-semibold text-gray-500 mb-1">PCAB Expiration Date</p>
                  <p class="text-sm text-gray-900">{{ $project->contractor_pcab_expiry ? \Carbon\Carbon::parse($project->contractor_pcab_expiry)->format('M d, Y') : 'N/A' }}</p>
                </div>
                <div>
                  <p class="text-xs font-semibold text-gray-500 mb-1">Business Permit No.</p>
                  <p class="text-sm text-gray-900">{{ $project->contractor_permit ?? 'N/A' }}</p>
                </div>
                <div>
                  <p class="text-xs font-semibold text-gray-500 mb-1">Permit City</p>
                  <p class="text-sm text-gray-900">{{ $project->contractor_city ?? 'N/A' }}</p>
                </div>
                <div>
                  <p class="text-xs font-semibold text-gray-500 mb-1">Business Permit Expiration</p>
                  <p class="text-sm text-gray-900">{{ $project->contractor_permit_expiry ? \Carbon\Carbon::parse($project->contractor_permit_expiry)->format('M d, Y') : 'N/A' }}</p>
                </div>
                <div>
                  <p class="text-xs font-semibold text-gray-500 mb-1">TIN Registration Number</p>
                  <p class="text-sm text-gray-900">{{ $project->contractor_tin ?? 'N/A' }}</p>
                </div>
              </div>
            </div>
          </div>

          <!-- Milestone Timeline Panel -->
          <div class="bg-gray-50 rounded-xl p-4 border border-gray-200">
            <h3 class="text-sm font-bold text-gray-900 mb-3 flex items-center gap-2">
              <svg class="w-4 h-4 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
              </svg>
              Milestone Timeline
            </h3>
            <div class="grid grid-cols-2 gap-4">
              <!-- Left Panel: Milestone List -->
              <div class="space-y-2">
                @php $cumulative = 0; @endphp
                @forelse($project->milestone_items as $item)
                  @php
                    $statusColors = [
                      'completed' => 'bg-green-100 text-green-700 border-green-300',
                      'in_progress' => 'bg-blue-100 text-blue-700 border-blue-300',
                      'delayed' => 'bg-red-100 text-red-700 border-red-300',
                      'cancelled' => 'bg-gray-100 text-gray-700 border-gray-300',
                      'not_started' => 'bg-gray-50 text-gray-600 border-gray-200'
                    ];
                    $statusColor = $statusColors[$item->item_status] ?? 'bg-gray-50 text-gray-600 border-gray-200';

                    // Calculate cumulative percentage from milestone_items table
                    $cumulative += $item->percentage_progress;
                  @endphp

                  <div onclick="showTerminatedMilestoneDetail({{ $item->item_id }})" class="flex items-center gap-3 p-3 rounded-lg border border-gray-200 hover:bg-white cursor-pointer transition-all duration-200 hover:shadow-md">
                    <div class="w-10 h-10 rounded-full {{ $statusColor }} border-2 flex items-center justify-center flex-shrink-0 font-bold text-sm">
                      {{ round($cumulative) }}%
                    </div>
                    <div class="flex-1">
                      <p class="text-sm font-medium text-gray-900">{{ $item->milestone_item_title }}</p>
                      <p class="text-xs text-gray-500">{{ \Carbon\Carbon::parse($item->date_to_finish)->format('M j, Y') }}</p>
                    </div>
                    <span class="px-2 py-1 rounded-full text-xs font-medium {{ $statusColor }} border">
                      {{ ucfirst(str_replace('_', ' ', $item->item_status)) }}
                    </span>
                  </div>
                @empty
                  <p class="text-sm text-gray-500 text-center py-8">No milestone items available</p>
                @endforelse
              </div>

              <!-- Right Panel: Details -->
              <div class="space-y-4">
                <div class="text-sm text-gray-500 text-center py-8">Select a milestone to view details</div>

                <!-- Hidden divs for each milestone item detail -->
                @foreach($project->milestone_items as $item)
                <div id="term-detail-{{ $item->item_id }}" class="hidden space-y-3">
                  <h4 class="text-sm font-bold text-gray-900 border-b pb-2">{{ $item->milestone_item_title }}</h4>
                  <div class="space-y-2 text-sm">
                    <div class="flex justify-between">
                      <span class="text-gray-600">Status:</span>
                      <span class="font-semibold text-gray-900">{{ ucfirst(str_replace('_', ' ', $item->item_status)) }}</span>
                    </div>
                    <div class="flex justify-between">
                      <span class="text-gray-600">Target Completion:</span>
                      <span class="font-semibold text-gray-900">{{ \Carbon\Carbon::parse($item->date_to_finish)->format('F j, Y') }}</span>
                    </div>
                  </div>

                  @if(!empty($item->progress_reports))
                    <div class="mt-4">
                      <p class="text-xs font-semibold text-gray-700 mb-2">Progress Reports:</p>
                      <div class="space-y-2">
                        @foreach($item->progress_reports as $progress)
                          <div class="bg-white p-3 rounded-lg border border-gray-200">
                            <div class="flex justify-between items-start mb-1">
                              <span class="text-xs font-semibold text-gray-700">
                                @php
                                  $statusColors = [
                                    'submitted' => 'bg-blue-100 text-blue-700',
                                    'approved' => 'bg-green-100 text-green-700',
                                    'rejected' => 'bg-red-100 text-red-700',
                                    'deleted' => 'bg-gray-100 text-gray-700'
                                  ];
                                  $statusColor = $statusColors[$progress['progress_status']] ?? 'bg-gray-100 text-gray-700';
                                @endphp
                                <span class="px-2 py-0.5 rounded-full text-xs font-medium {{ $statusColor }}">
                                  {{ ucfirst($progress['progress_status']) }}
                                </span>
                              </span>
                              <span class="text-xs text-gray-500">{{ \Carbon\Carbon::parse($progress['submitted_at'])->format('M j, Y') }}</span>
                            </div>
                            <p class="text-xs text-gray-600">{{ $progress['purpose'] }}</p>
                          </div>
                        @endforeach
                      </div>
                    </div>
                  @endif
                </div>
                @endforeach
              </div>
            </div>
          </div>

          <!-- Payment Summary Panel -->
          <div class="bg-gray-50 rounded-xl p-4 border border-gray-200">
            <h3 class="text-sm font-bold text-gray-900 mb-3 flex items-center gap-2">
              <svg class="w-4 h-4 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/>
              </svg>
              Payment Summary
            </h3>
            <div class="overflow-x-auto">
              <table class="w-full text-sm">
                <thead>
                  <tr class="border-b border-gray-200">
                    <th class="text-left py-2 px-3 text-xs font-semibold text-gray-600">Milestone</th>
                    <th class="text-left py-2 px-3 text-xs font-semibold text-gray-600">Payment Amount</th>
                    <th class="text-left py-2 px-3 text-xs font-semibold text-gray-600">Status</th>
                    <th class="text-left py-2 px-3 text-xs font-semibold text-gray-600">Paid On</th>
                  </tr>
                </thead>
                <tbody>
                  @forelse($project->payments as $payment)
                    <tr class="border-b border-gray-100">
                      <td class="py-2 px-3 text-gray-900">{{ $payment->milestone_item_title ?? 'N/A' }}</td>
                      <td class="py-2 px-3 text-gray-900 font-medium">₱{{ number_format($payment->amount, 2) }}</td>
                      <td class="py-2 px-3">
                        <span class="px-2 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-700">
                          {{ ucfirst($payment->payment_status) }}
                        </span>
                      </td>
                      <td class="py-2 px-3 text-gray-900">
                        {{ $payment->transaction_date ? \Carbon\Carbon::parse($payment->transaction_date)->format('M j, Y') : 'N/A' }}
                      </td>
                    </tr>
                  @empty
                    <tr>
                      <td colspan="4" class="py-4 text-center text-sm text-gray-400">No payment records available</td>
                    </tr>
                  @endforelse
                </tbody>
              </table>
            </div>
          </div>
        </div>

        <!-- Footer -->
        <div class="border-t border-gray-200 px-6 py-4 bg-gray-50 rounded-b-2xl flex justify-end flex-shrink-0">
          <button onclick="hideCancelledProjectModal()" class="px-6 py-2.5 text-sm font-semibold rounded-lg bg-gray-200 hover:bg-gray-300 text-gray-700 transition">
            Close
          </button>
        </div>
      @else
        <!-- Empty state when no project data -->
        <div class="p-8 text-center text-gray-500">
          <p>Loading project details...</p>
        </div>
      @endisset
    </div>
  </div>
</div>
