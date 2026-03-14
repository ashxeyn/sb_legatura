{{-- Project Summary Content Partial --}}
{{-- $summary = result of SummaryService::getProjectSummary()['data'] --}}
@php
  $header          = $summary['header']          ?? [];
  $overview        = $summary['overview']        ?? [];
  $milestones      = $summary['milestones']      ?? [];
  $budgetHistory   = $summary['budget_history']  ?? [];
  $changeHistory   = $summary['change_history']  ?? [];
  $payments        = $summary['payments']        ?? [];
  $progressReports = $summary['progress_reports'] ?? [];
  $generatedAt     = $summary['generated_at']    ?? null;

  // Helper: safely get value from array or object
  $get = function($item, $key, $default = null) {
    if (is_array($item))  return $item[$key] ?? $default;
    if (is_object($item)) return $item->{$key} ?? $default;
    return $default;
  };

  $statusColors = [
    'completed'          => 'bg-green-100 text-green-700',
    'approved'           => 'bg-green-100 text-green-700',
    'in_progress'        => 'bg-blue-100 text-blue-700',
    'active'             => 'bg-blue-100 text-blue-700',
    'pending'            => 'bg-yellow-100 text-yellow-700',
    'submitted'          => 'bg-yellow-100 text-yellow-700',
    'rejected'           => 'bg-red-100 text-red-700',
    'terminated'         => 'bg-gray-100 text-gray-700',
    'halt'               => 'bg-rose-100 text-rose-700',
    'revision_requested' => 'bg-orange-100 text-orange-700',
    'withdrawn'          => 'bg-gray-100 text-gray-500',
  ];
  $statusClass = fn($s) => $statusColors[strtolower((string)($s ?? ''))] ?? 'bg-gray-100 text-gray-600';
  $fmt     = fn($n) => '₱' . number_format((float)($n ?? 0), 2);
  $fmtDate = fn($d) => $d ? \Carbon\Carbon::parse($d)->format('M j, Y') : '—';
  $fmtDt   = fn($d) => $d ? \Carbon\Carbon::parse($d)->format('M j, Y g:i A') : '—';
@endphp

{{-- ═══ A. PROJECT HEADER ═══ --}}
<div class="bg-white border border-gray-200 rounded-xl p-5 space-y-3">
  <div class="flex items-start justify-between gap-3">
    <div class="flex-1">
      <h3 class="text-base font-bold text-gray-900">{{ $header['project_title'] ?? '—' }}</h3>
      @if(!empty($header['project_description']))
        <p class="text-sm text-gray-500 mt-1">{{ $header['project_description'] }}</p>
      @endif
      <div class="flex items-center gap-1 mt-2 text-xs text-gray-500">
        <svg class="w-3.5 h-3.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
        </svg>
        {{ $header['project_location'] ?? '—' }}
      </div>
    </div>
    <span class="inline-flex px-2.5 py-1 rounded-full text-xs font-semibold {{ $statusClass($header['status'] ?? '') }} flex-shrink-0">
      {{ strtoupper(str_replace('_', ' ', $header['status'] ?? '—')) }}
    </span>
  </div>
  <div class="border-t border-gray-100 pt-3 grid sm:grid-cols-2 gap-3 text-sm">
    <div>
      <p class="text-xs font-semibold text-gray-400 uppercase tracking-wide mb-1">Property Owner</p>
      <p class="font-semibold text-gray-900">{{ $header['owner_name'] ?? '—' }}</p>
      @if(!empty($header['owner_email']))<p class="text-xs text-gray-500">{{ $header['owner_email'] }}</p>@endif
    </div>
    <div>
      <p class="text-xs font-semibold text-gray-400 uppercase tracking-wide mb-1">Contractor</p>
      <p class="font-semibold text-gray-900">{{ $header['contractor_name'] ?? '—' }}</p>
      @if(!empty($header['contractor_company']))<p class="text-xs text-gray-500">{{ $header['contractor_company'] }}</p>@endif
    </div>
  </div>
  <div class="border-t border-gray-100 pt-3 flex items-center gap-3 text-sm flex-wrap">
    <div>
      <span class="text-xs text-gray-400 uppercase tracking-wide">Start</span>
      <p class="font-semibold text-gray-900">{{ $fmtDate($header['original_start_date'] ?? null) }}</p>
    </div>
    <svg class="w-4 h-4 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
    </svg>
    <div>
      <span class="text-xs text-gray-400 uppercase tracking-wide">{{ !empty($header['was_extended']) ? 'Current End' : 'End' }}</span>
      <p class="font-semibold text-gray-900">{{ $fmtDate($header['current_end_date'] ?? null) }}</p>
    </div>
    @if(!empty($header['was_extended']))
      <span class="inline-flex items-center gap-1 px-2 py-0.5 bg-yellow-100 text-yellow-700 text-xs font-semibold rounded-full">Extended</span>
    @endif
  </div>
</div>

{{-- ═══ B. EXECUTIVE OVERVIEW (collapsible) ═══ --}}
@php
  $totalMilestones     = (int)($overview['total_milestones'] ?? 0);
  $completedMilestones = (int)($overview['completed_milestones'] ?? 0);
  $progressPct   = $totalMilestones > 0 ? round(($completedMilestones / $totalMilestones) * 100) : 0;
  $currentBudget = (float)($overview['current_budget'] ?? 0);
  $totalPaid     = (float)($overview['total_paid'] ?? 0);
  $budgetPct     = $currentBudget > 0 ? min(round(($totalPaid / $currentBudget) * 100), 100) : 0;
@endphp
<div class="border border-gray-200 rounded-xl overflow-hidden">
  <button type="button" onclick="psmToggle('psmOverview','psmOverviewChevron')" class="w-full px-5 py-3.5 flex items-center justify-between hover:bg-gray-50 transition-colors">
    <div class="flex items-center gap-2 text-sm font-bold text-gray-900">
      <svg class="w-4 h-4 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
      </svg>
      Executive Overview
    </div>
    <svg id="psmOverviewChevron" class="w-4 h-4 text-gray-400 transition-transform duration-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
    </svg>
  </button>
  <div id="psmOverview" class="border-t border-gray-100 p-5 space-y-4">
    <div>
      <div class="flex justify-between text-xs font-semibold text-gray-700 mb-1">
        <span>Milestone Progress</span><span>{{ $progressPct }}%</span>
      </div>
      <div class="h-2 bg-gray-100 rounded-full overflow-hidden">
        <div class="h-full bg-green-500 rounded-full" style="width:{{ $progressPct }}%"></div>
      </div>
      <p class="text-xs text-gray-500 mt-1">{{ $completedMilestones }} of {{ $totalMilestones }} milestones completed</p>
    </div>
    <div>
      <div class="flex justify-between text-xs font-semibold text-gray-700 mb-1">
        <span>Budget Utilization</span><span>{{ $budgetPct }}%</span>
      </div>
      <div class="h-2 bg-gray-100 rounded-full overflow-hidden">
        <div class="h-full rounded-full {{ $budgetPct >= 100 ? 'bg-red-500' : 'bg-blue-500' }}" style="width:{{ $budgetPct }}%"></div>
      </div>
    </div>
    <div class="grid grid-cols-2 sm:grid-cols-3 gap-3">
      @php
        $ovItems = [
          ['Original Budget',   $fmt($overview['original_budget']   ?? 0), false, ''],
          ['Current Budget',    $fmt($overview['current_budget']    ?? 0), ($overview['current_budget'] ?? 0) != ($overview['original_budget'] ?? 0), ''],
          ['Total Paid',        $fmt($overview['total_paid']        ?? 0), false, 'text-green-600'],
          ['Pending',           $fmt($overview['total_pending']     ?? 0), false, 'text-yellow-600'],
          ['Remaining Balance', $fmt($overview['remaining_balance'] ?? 0), false, ''],
          ['Payment Mode',      ucwords(str_replace('_', ' ', $overview['payment_mode'] ?? '—')), false, ''],
        ];
      @endphp
      @foreach($ovItems as [$label, $value, $highlight, $color])
        <div class="bg-gray-50 rounded-lg p-3 border {{ $highlight ? 'border-blue-200 bg-blue-50' : 'border-gray-200' }}">
          <p class="text-xs text-gray-500 mb-1">{{ $label }}</p>
          <p class="text-sm font-bold {{ $color ?: 'text-gray-900' }}">{{ $value }}</p>
        </div>
      @endforeach
    </div>
  </div>
</div>

{{-- ═══ C. MILESTONE BREAKDOWN (collapsible) ═══ --}}
<div class="border border-gray-200 rounded-xl overflow-hidden">
  <button type="button" onclick="psmToggle('psmMilestones','psmMilestonesChevron')" class="w-full px-5 py-3.5 flex items-center justify-between hover:bg-gray-50 transition-colors">
    <div class="flex items-center gap-2 text-sm font-bold text-gray-900">
      <svg class="w-4 h-4 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
      </svg>
      Milestones ({{ count($milestones) }})
    </div>
    <svg id="psmMilestonesChevron" class="w-4 h-4 text-gray-400 transition-transform duration-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
    </svg>
  </button>
  <div id="psmMilestones" class="border-t border-gray-100 p-4 space-y-3">
    @forelse($milestones as $m)
      @php
        $mAlloc = (float)$get($m, 'current_allocation', 0);
        $mPaid  = (float)$get($m, 'total_paid', 0);
        $mPct   = $mAlloc > 0 ? min(round(($mPaid / $mAlloc) * 100), 100) : ($get($m,'status') === 'completed' ? 100 : 0);
        $mStatus = $get($m, 'status', '');
        $mTitle  = $get($m, 'title', '—');
        $mMilestone = $get($m, 'milestone_name', '');
        $mSeq    = $get($m, 'sequence_order', '?');
        $mDue    = $get($m, 'current_due_date');
        $mOrigDue = $get($m, 'original_due_date');
        $mExtended = (bool)$get($m, 'was_extended', false);
        $mExtCount = (int)$get($m, 'extension_count', 0);
      @endphp
      <div class="bg-gray-50 rounded-lg p-4 border border-gray-200">
        <div class="flex items-start gap-3">
          <div class="w-7 h-7 rounded-full bg-indigo-100 text-indigo-700 text-xs font-bold flex items-center justify-center flex-shrink-0">
            {{ $mSeq }}
          </div>
          <div class="flex-1 min-w-0">
            <div class="flex items-start justify-between gap-2">
              <div class="min-w-0">
                <p class="text-sm font-semibold text-gray-900 truncate">{{ $mTitle }}</p>
                @if($mMilestone)<p class="text-xs text-gray-500">{{ $mMilestone }}</p>@endif
              </div>
              <span class="inline-flex px-2 py-0.5 rounded-full text-xs font-semibold {{ $statusClass($mStatus) }} flex-shrink-0">
                {{ str_replace('_', ' ', $mStatus) }}
              </span>
            </div>
            <div class="grid grid-cols-3 gap-2 mt-2 text-xs">
              <div><span class="text-gray-400">Budget</span><p class="font-semibold text-gray-800">{{ $fmt($mAlloc) }}</p></div>
              <div><span class="text-gray-400">Paid</span><p class="font-semibold text-green-600">{{ $fmt($mPaid) }}</p></div>
              <div><span class="text-gray-400">Due</span><p class="font-semibold text-gray-800">{{ $fmtDate($mDue) }}</p></div>
            </div>
            @if($mExtended)
              <p class="text-xs text-yellow-600 mt-1.5">Extended {{ $mExtCount }}× (was {{ $fmtDate($mOrigDue) }})</p>
            @endif
            <div class="mt-2 h-1.5 bg-gray-200 rounded-full overflow-hidden">
              <div class="h-full bg-blue-500 rounded-full" style="width:{{ $mPct }}%"></div>
            </div>
          </div>
        </div>
      </div>
    @empty
      <p class="text-sm text-gray-400 text-center py-4">No milestones found.</p>
    @endforelse
  </div>
</div>

{{-- ═══ D. BUDGET HISTORY (collapsible) ═══ --}}
@if(count($budgetHistory) > 0)
<div class="border border-gray-200 rounded-xl overflow-hidden">
  <button type="button" onclick="psmToggle('psmBudget','psmBudgetChevron')" class="w-full px-5 py-3.5 flex items-center justify-between hover:bg-gray-50 transition-colors">
    <div class="flex items-center gap-2 text-sm font-bold text-gray-900">
      <svg class="w-4 h-4 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/>
      </svg>
      Budget History ({{ count($budgetHistory) }})
    </div>
    <svg id="psmBudgetChevron" class="w-4 h-4 text-gray-400 transition-transform duration-200 rotate-180" fill="none" stroke="currentColor" viewBox="0 0 24 24">
      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
    </svg>
  </button>
  <div id="psmBudget" class="border-t border-gray-100 p-4 space-y-3 hidden">
    @foreach($budgetHistory as $bh)
      @php
        $bhStatus   = $get($bh, 'status', '');
        $bhType     = $get($bh, 'change_type', 'Update');
        $bhPrev     = $get($bh, 'previous_budget', 0);
        $bhNew      = $get($bh, 'updated_budget', 0);
        $bhReason   = $get($bh, 'reason', '');
        $bhDate     = $get($bh, 'date_proposed');
      @endphp
      <div class="flex gap-3">
        <div class="w-2 h-2 rounded-full bg-indigo-400 mt-1.5 flex-shrink-0"></div>
        <div class="flex-1">
          <div class="flex items-center justify-between gap-2">
            <p class="text-sm font-semibold text-gray-900">Budget {{ ucfirst($bhType) }}</p>
            <span class="inline-flex px-2 py-0.5 rounded-full text-xs font-semibold {{ $statusClass($bhStatus) }}">{{ $bhStatus }}</span>
          </div>
          @if($bhPrev || $bhNew)
            <p class="text-xs text-gray-600 mt-0.5">{{ $fmt($bhPrev) }} → {{ $fmt($bhNew) }}</p>
          @endif
          @if($bhReason)<p class="text-xs text-gray-500 italic mt-0.5">"{{ $bhReason }}"</p>@endif
          <p class="text-xs text-gray-400 mt-0.5">{{ $fmtDate($bhDate) }}</p>
        </div>
      </div>
    @endforeach
  </div>
</div>
@endif

{{-- ═══ E. CHANGE LOG (collapsible) ═══ --}}
@if(count($changeHistory) > 0)
<div class="border border-gray-200 rounded-xl overflow-hidden">
  <button type="button" onclick="psmToggle('psmChangelog','psmChangelogChevron')" class="w-full px-5 py-3.5 flex items-center justify-between hover:bg-gray-50 transition-colors">
    <div class="flex items-center gap-2 text-sm font-bold text-gray-900">
      <svg class="w-4 h-4 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
      </svg>
      Change Log ({{ count($changeHistory) }})
    </div>
    <svg id="psmChangelogChevron" class="w-4 h-4 text-gray-400 transition-transform duration-200 rotate-180" fill="none" stroke="currentColor" viewBox="0 0 24 24">
      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
    </svg>
  </button>
  <div id="psmChangelog" class="border-t border-gray-100 p-4 space-y-3 hidden">
    @foreach($changeHistory as $evt)
      @php
        $evtAction = $get($evt, 'action', '—');
        $evtBy     = $get($evt, 'performed_by', '');
        $evtNotes  = $get($evt, 'notes', '');
        $evtRef    = $get($evt, 'reference', '');
        $evtDate   = $get($evt, 'date');
      @endphp
      <div class="flex gap-3">
        <div class="w-2 h-2 rounded-full bg-blue-400 mt-1.5 flex-shrink-0"></div>
        <div class="flex-1">
          <p class="text-sm font-semibold text-gray-900">{{ $evtAction }}</p>
          @if($evtBy)<p class="text-xs text-gray-500">by {{ $evtBy }}</p>@endif
          @if($evtNotes)<p class="text-xs text-gray-500 italic">"{{ $evtNotes }}"</p>@endif
          @if($evtRef)<p class="text-xs text-gray-400">Ref: {{ $evtRef }}</p>@endif
          <p class="text-xs text-gray-400 mt-0.5">{{ $fmtDt($evtDate) }}</p>
        </div>
      </div>
    @endforeach
  </div>
</div>
@endif

{{-- ═══ F. PAYMENTS (collapsible) ═══ --}}
@php
  $payRecords    = $payments['records']        ?? [];
  $payApproved   = (float)($payments['total_approved'] ?? 0);
  $payPending    = (float)($payments['total_pending']  ?? 0);
  $payRejected   = (float)($payments['total_rejected'] ?? 0);
  $paymentMode   = $overview['payment_mode']   ?? 'full_payment';
  $isStaggered   = $paymentMode === 'staggered';
  $dpCleared     = (bool)($overview['downpayment_cleared'] ?? false);
  $dpRequired    = (float)($overview['downpayment'] ?? 0);

  // Downpayment paid/pending from records
  $dpPaid = 0; $dpPendingAmt = 0;
  foreach ($payRecords as $pr) {
    if (strtolower((string)$get($pr,'milestone','')) === 'downpayment') {
      $s = strtolower((string)$get($pr,'status',''));
      if ($s === 'approved') $dpPaid += (float)$get($pr,'amount',0);
      elseif (in_array($s, ['submitted','pending'])) $dpPendingAmt += (float)$get($pr,'amount',0);
    }
  }
  $dpRemaining = max(0, $dpRequired - $dpPaid);
  $dpOver      = max(0, $dpPaid - $dpRequired);
  $dpProgress  = $dpRequired > 0 ? min(100, round(($dpPaid / $dpRequired) * 100)) : ($dpCleared ? 100 : 0);
  $dpStatus    = $dpCleared ? 'Verified'
               : ($dpPaid > 0 ? 'Partially Paid'
               : ($dpPendingAmt > 0 ? 'Pending Review' : 'Awaiting Payment'));

  // Per-milestone payment status analysis
  $allMilestoneDone   = count($milestones) > 0 && collect($milestones)->every(fn($m) => $get($m,'status') === 'completed');
  $overdueCount       = 0;
  $pendingPayCount    = 0;
  $allFullyPaid       = true;
  $today              = \Carbon\Carbon::today();

  foreach ($milestones as $m) {
    $mPayStatus = $get($m, 'payment_status', 'Unpaid');
    $mSettleDue = $get($m, 'settlement_due_date');
    if ($mPayStatus !== 'Fully Paid') $allFullyPaid = false;
    if (in_array($mPayStatus, ['Unpaid','Partially Paid'])) {
      $pendingPayCount++;
      if ($mSettleDue && \Carbon\Carbon::parse($mSettleDue)->lt($today)) $overdueCount++;
    }
  }

  // Payment status badge helper
  $payStatusBadge = function(string $s): string {
    return match($s) {
      'Fully Paid'    => 'bg-green-100 text-green-700 border border-green-200',
      'Partially Paid'=> 'bg-yellow-100 text-yellow-700 border border-yellow-200',
      'Overdue'       => 'bg-red-100 text-red-700 border border-red-200',
      default         => 'bg-gray-100 text-gray-500 border border-gray-200',
    };
  };

  // Due date urgency helper
  $dueDateUrgency = function(?string $dateStr) use ($today): ?array {
    if (!$dateStr) return null;
    $due = \Carbon\Carbon::parse($dateStr);
    $diff = $today->diffInDays($due, false); // negative = overdue
    if ($diff < 0)  return ['label' => abs((int)$diff).'d overdue', 'class' => 'text-red-600 bg-red-50'];
    if ($diff === 0) return ['label' => 'Due today',                'class' => 'text-red-600 bg-red-50'];
    if ($diff <= 3)  return ['label' => $diff.'d left',             'class' => 'text-orange-600 bg-orange-50'];
    if ($diff <= 7)  return ['label' => $diff.'d left',             'class' => 'text-yellow-600 bg-yellow-50'];
    return ['label' => $diff.'d left', 'class' => 'text-green-600 bg-green-50'];
  };
@endphp
<div class="border border-gray-200 rounded-xl overflow-hidden">
  <button type="button" onclick="psmToggle('psmPayments','psmPaymentsChevron')" class="w-full px-5 py-3.5 flex items-center justify-between hover:bg-gray-50 transition-colors">
    <div class="flex items-center gap-2 text-sm font-bold text-gray-900">
      <svg class="w-4 h-4 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/>
      </svg>
      Payments ({{ count($payRecords) }})
    </div>
    <svg id="psmPaymentsChevron" class="w-4 h-4 text-gray-400 transition-transform duration-200 rotate-180" fill="none" stroke="currentColor" viewBox="0 0 24 24">
      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
    </svg>
  </button>
  <div id="psmPayments" class="border-t border-gray-100 p-4 space-y-4 hidden">

    {{-- ── Status Alert Banners ── --}}
    @if($allMilestoneDone && $allFullyPaid)
      <div class="flex items-center gap-2.5 px-3 py-2.5 rounded-lg bg-green-50 border border-green-200 text-sm font-semibold text-green-700">
        <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
        All milestones completed and fully paid
      </div>
    @elseif($allMilestoneDone && $pendingPayCount > 0)
      <div class="flex items-center gap-2.5 px-3 py-2.5 rounded-lg bg-yellow-50 border border-yellow-200 text-sm font-semibold text-yellow-700">
        <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
        All milestones completed but {{ $pendingPayCount }} payment{{ $pendingPayCount !== 1 ? 's' : '' }} still pending
      </div>
    @endif
    @if($overdueCount > 0)
      <div class="flex items-center gap-2.5 px-3 py-2.5 rounded-lg bg-red-50 border border-red-200 text-sm font-semibold text-red-700">
        <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
        {{ $overdueCount }} milestone{{ $overdueCount !== 1 ? 's' : '' }} with overdue payments
      </div>
    @endif
    @if($isStaggered && !$dpCleared && $dpPendingAmt > 0)
      <div class="flex items-center gap-2.5 px-3 py-2.5 rounded-lg bg-blue-50 border border-blue-200 text-sm font-semibold text-blue-700">
        <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
        Downpayment pending review — milestones locked until verified
      </div>
    @endif

    {{-- ── Totals Strip ── --}}
    <div class="flex flex-wrap gap-2">
      <span class="px-3 py-1.5 rounded-lg border border-green-200 bg-green-50 text-xs font-semibold text-green-700">Approved: {{ $fmt($payApproved) }}</span>
      <span class="px-3 py-1.5 rounded-lg border border-yellow-200 bg-yellow-50 text-xs font-semibold text-yellow-700">Pending: {{ $fmt($payPending) }}</span>
      <span class="px-3 py-1.5 rounded-lg border border-red-200 bg-red-50 text-xs font-semibold text-red-700">Rejected: {{ $fmt($payRejected) }}</span>
    </div>

    {{-- ── Downpayment Block (staggered only) ── --}}
    @if($isStaggered && $dpRequired > 0)
      <div class="rounded-lg border {{ $dpCleared ? 'border-green-200 bg-green-50' : 'border-gray-200 bg-gray-50' }} p-4 space-y-2">
        <div class="flex items-center justify-between gap-2">
          <div class="flex items-center gap-2">
            <svg class="w-4 h-4 {{ $dpCleared ? 'text-green-600' : 'text-gray-500' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/>
            </svg>
            <span class="text-sm font-bold {{ $dpCleared ? 'text-green-800' : 'text-gray-800' }}">Downpayment</span>
          </div>
          <span class="inline-flex px-2.5 py-1 rounded-full text-xs font-bold
            {{ $dpCleared ? 'bg-green-100 text-green-700' : ($dpPaid > 0 ? 'bg-yellow-100 text-yellow-700' : ($dpPendingAmt > 0 ? 'bg-blue-100 text-blue-700' : 'bg-gray-100 text-gray-500')) }}">
            {{ $dpStatus }}
          </span>
        </div>
        <div class="grid grid-cols-3 gap-2 text-xs">
          <div><span class="text-gray-400">Required</span><p class="font-semibold text-gray-800">{{ $fmt($dpRequired) }}</p></div>
          <div><span class="text-gray-400">Paid</span><p class="font-semibold text-green-600">{{ $fmt($dpPaid) }}</p></div>
          <div>
            <span class="text-gray-400">{{ $dpOver > 0 ? 'Over' : 'Remaining' }}</span>
            <p class="font-semibold {{ $dpOver > 0 ? 'text-red-600' : ($dpRemaining > 0 ? 'text-orange-600' : 'text-green-600') }}">
              {{ $dpOver > 0 ? '+' . $fmt($dpOver) : $fmt($dpRemaining) }}
            </p>
          </div>
        </div>
        <div class="h-1.5 bg-gray-200 rounded-full overflow-hidden">
          <div class="h-full rounded-full {{ $dpCleared ? 'bg-green-500' : 'bg-blue-500' }}" style="width:{{ $dpProgress }}%"></div>
        </div>
        @if($dpCleared)
          <p class="text-xs text-green-600 font-semibold">✓ Downpayment verified — milestones unlocked</p>
        @elseif($dpPendingAmt > 0)
          <p class="text-xs text-blue-600">{{ $fmt($dpPendingAmt) }} pending review</p>
        @endif
      </div>
    @endif

    {{-- ── Per-Milestone Payment Status ── --}}
    @if(count($milestones) > 0)
      <div class="space-y-2">
        <p class="text-xs font-bold text-gray-500 uppercase tracking-wide">Milestone Payments</p>
        @foreach($milestones as $m)
          @php
            $mTitle      = $get($m, 'title', '—');
            $mSeq        = $get($m, 'sequence_order', '?');
            $mAlloc      = (float)$get($m, 'current_allocation', 0);
            $mPaid       = (float)$get($m, 'total_paid', 0);
            $mRemaining  = (float)$get($m, 'remaining', 0);
            $mCarry      = (float)$get($m, 'carry_forward_amount', 0);
            $mPayStatus  = $get($m, 'payment_status', 'Unpaid');
            $mSettleDue  = $get($m, 'settlement_due_date');
            $mStatus     = $get($m, 'status', '');
            $mOver       = max(0, $mPaid - $mAlloc);
            $mPct        = $mAlloc > 0 ? min(100, round(($mPaid / $mAlloc) * 100)) : ($mStatus === 'completed' ? 100 : 0);

            // Determine actual payment status (check overdue)
            $effectivePayStatus = $mPayStatus;
            if (in_array($mPayStatus, ['Unpaid','Partially Paid']) && $mSettleDue) {
              if (\Carbon\Carbon::parse($mSettleDue)->lt($today)) $effectivePayStatus = 'Overdue';
            }

            $urgency = $dueDateUrgency($mSettleDue);
          @endphp
          <div class="rounded-lg border border-gray-200 bg-white p-3 space-y-2">
            <div class="flex items-center justify-between gap-2">
              <div class="flex items-center gap-2 min-w-0">
                <span class="w-5 h-5 rounded-full bg-indigo-100 text-indigo-700 text-xs font-bold flex items-center justify-center flex-shrink-0">{{ $mSeq }}</span>
                <span class="text-xs font-semibold text-gray-800 truncate">{{ $mTitle }}</span>
              </div>
              <span class="inline-flex px-2 py-0.5 rounded-full text-xs font-semibold flex-shrink-0 {{ $payStatusBadge($effectivePayStatus) }}">
                {{ $effectivePayStatus }}
              </span>
            </div>
            <div class="grid grid-cols-3 gap-2 text-xs">
              <div><span class="text-gray-400">Allocated</span><p class="font-semibold text-gray-800">{{ $fmt($mAlloc) }}</p></div>
              <div><span class="text-gray-400">Paid</span><p class="font-semibold text-green-600">{{ $fmt($mPaid) }}</p></div>
              <div>
                <span class="text-gray-400">{{ $mOver > 0 ? 'Over' : 'Remaining' }}</span>
                <p class="font-semibold {{ $mOver > 0 ? 'text-red-600' : 'text-gray-700' }}">
                  {{ $mOver > 0 ? '+' . $fmt($mOver) : $fmt($mRemaining) }}
                </p>
              </div>
            </div>
            @if($mOver > 0)
              <p class="text-xs font-bold text-red-600">⚠ Over-budget payment</p>
            @endif
            @if($mCarry > 0)
              <p class="text-xs text-indigo-600">↩ Carry-forward: {{ $fmt($mCarry) }}</p>
            @endif
            <div class="h-1.5 bg-gray-100 rounded-full overflow-hidden">
              <div class="h-full rounded-full {{ $effectivePayStatus === 'Fully Paid' ? 'bg-green-500' : ($effectivePayStatus === 'Overdue' ? 'bg-red-500' : 'bg-blue-500') }}" style="width:{{ $mPct }}%"></div>
            </div>
            @if($mSettleDue)
              <div class="flex items-center justify-between text-xs">
                <span class="text-gray-400">Settlement due: {{ $fmtDate($mSettleDue) }}</span>
                @if($urgency && $effectivePayStatus !== 'Fully Paid')
                  <span class="px-1.5 py-0.5 rounded text-xs font-semibold {{ $urgency['class'] }}">{{ $urgency['label'] }}</span>
                @endif
              </div>
            @endif
          </div>
        @endforeach
      </div>
    @endif

    {{-- ── Payment Records Table ── --}}
    @if(count($payRecords) > 0)
      <div>
        <p class="text-xs font-bold text-gray-500 uppercase tracking-wide mb-2">Transaction History</p>
        <div class="rounded-lg border border-gray-200 overflow-hidden">
          <table class="w-full text-xs">
            <thead class="bg-gray-50 border-b border-gray-200">
              <tr>
                <th class="text-left px-3 py-2 font-semibold text-gray-600">Milestone</th>
                <th class="text-left px-3 py-2 font-semibold text-gray-600">Type</th>
                <th class="text-left px-3 py-2 font-semibold text-gray-600">Amount</th>
                <th class="text-left px-3 py-2 font-semibold text-gray-600">Status</th>
                <th class="text-left px-3 py-2 font-semibold text-gray-600">Date</th>
                <th class="text-left px-3 py-2 font-semibold text-gray-600">Ref #</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
              @foreach($payRecords as $p)
                @php
                  $pMilestone = $get($p, 'milestone', '');
                  $pType      = $get($p, 'payment_type', '');
                  $pAmount    = $get($p, 'amount', 0);
                  $pStatus    = $get($p, 'status', '');
                  $pDate      = $get($p, 'transaction_date');
                  $pRef       = $get($p, 'transaction_number', '');
                @endphp
                <tr class="hover:bg-gray-50">
                  <td class="px-3 py-2 text-gray-800">{{ $pMilestone }}</td>
                  <td class="px-3 py-2 text-gray-600">{{ ucwords(str_replace('_', ' ', $pType)) }}</td>
                  <td class="px-3 py-2 font-semibold text-gray-900">{{ $fmt($pAmount) }}</td>
                  <td class="px-3 py-2">
                    <span class="inline-flex px-2 py-0.5 rounded-full text-xs font-semibold {{ $statusClass($pStatus) }}">{{ $pStatus }}</span>
                  </td>
                  <td class="px-3 py-2 text-gray-500">{{ $fmtDate($pDate) }}</td>
                  <td class="px-3 py-2 text-gray-400">{{ $pRef }}</td>
                </tr>
              @endforeach
            </tbody>
          </table>
        </div>
      </div>
    @else
      <p class="text-sm text-gray-400 text-center py-4">No payment records yet.</p>
    @endif
  </div>
</div>

{{-- ═══ G. PROGRESS REPORTS (collapsible) ═══ --}}
@if(count($progressReports) > 0)
<div class="border border-gray-200 rounded-xl overflow-hidden">
  <button type="button" onclick="psmToggle('psmProgress','psmProgressChevron')" class="w-full px-5 py-3.5 flex items-center justify-between hover:bg-gray-50 transition-colors">
    <div class="flex items-center gap-2 text-sm font-bold text-gray-900">
      <svg class="w-4 h-4 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
      </svg>
      Progress Reports ({{ count($progressReports) }})
    </div>
    <svg id="psmProgressChevron" class="w-4 h-4 text-gray-400 transition-transform duration-200 rotate-180" fill="none" stroke="currentColor" viewBox="0 0 24 24">
      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
    </svg>
  </button>
  <div id="psmProgress" class="border-t border-gray-100 p-4 space-y-2 hidden">
    @foreach($progressReports as $pr)
      @php
        $prTitle    = $get($pr, 'report_title', 'Progress Report');
        $prMilestone = $get($pr, 'milestone', '');
        $prUploader = $get($pr, 'uploader_name', '');
        $prRole     = $get($pr, 'uploader_role', '');
        $prStatus   = $get($pr, 'status', '');
        $prDate     = $get($pr, 'submitted_at');
      @endphp
      <div class="flex items-start justify-between gap-3 py-2 border-b border-gray-100 last:border-0">
        <div class="flex-1 min-w-0">
          <p class="text-sm font-semibold text-gray-900 truncate">{{ $prTitle }}</p>
          @if($prMilestone)<p class="text-xs text-gray-500">{{ $prMilestone }}</p>@endif
          @if($prUploader)
            <p class="text-xs text-gray-400">Uploaded by: {{ $prUploader }}{{ $prRole ? ' (' . ucwords(str_replace('_', ' ', $prRole)) . ')' : '' }}</p>
          @endif
        </div>
        <div class="text-right flex-shrink-0">
          <span class="inline-flex px-2 py-0.5 rounded-full text-xs font-semibold {{ $statusClass($prStatus) }}">
            {{ str_replace('_', ' ', $prStatus) }}
          </span>
          <p class="text-xs text-gray-400 mt-1">{{ $fmtDate($prDate) }}</p>
        </div>
      </div>
    @endforeach
  </div>
</div>
@endif

<p class="text-xs text-gray-400 text-center pt-2">Report generated {{ $fmtDt($generatedAt) }}</p>
