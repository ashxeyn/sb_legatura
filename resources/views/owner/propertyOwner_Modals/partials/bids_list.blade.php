{{--
    Bids list partial — rendered server-side, injected into #bidsPanelList via JS.
    Each row carries a data-bid attribute (JSON) so JS can open the detail panel
    without another round-trip.
    Design consistent with propertyOwner_Allprojects.blade.php.
--}}
@foreach ($bids as $index => $bid)
@php
    $status       = $bid->bid_status ?? 'submitted';
    $name         = trim($bid->company_name ?? $bid->username ?? 'Contractor');
    $initials     = strtoupper(substr($name, 0, 2));
    $cost         = $bid->proposed_cost ? '₱' . number_format((float)$bid->proposed_cost, 2) : '—';
    $timeline     = ($bid->estimated_timeline !== null && $bid->estimated_timeline !== '')
                        ? $bid->estimated_timeline . ' mo.'
                        : '—';
    $isTop        = $index === 0;
    $rankEmoji    = $index === 0 ? '🥇' : ($index === 1 ? '🥈' : ($index === 2 ? '🥉' : null));
    $rankNum      = $rankEmoji === null ? ('#' . ($index + 1)) : null;
    $isActionable = in_array($status, ['submitted', 'under_review']);
    $submittedDate = $bid->submitted_at
        ? \Carbon\Carbon::parse($bid->submitted_at)->format('M j, Y')
        : '—';
    $fileCount    = $bid->file_count ?? 0;
    $experience   = $bid->years_of_experience ?? null;
    $completedProjects = $bid->completed_projects ?? null;
    $metaParts    = [];
    if ($experience !== null)        $metaParts[] = $experience . ' yrs exp';
    if ($completedProjects !== null) $metaParts[] = $completedProjects . ' projects';
    if ($bid->picab_category ?? null) $metaParts[] = $bid->picab_category;
    $bidJson = json_encode($bid);
@endphp
<div class="pdm-bid-row{{ $isTop ? ' pdm-bid-row-top' : '' }}"
     data-bid-id="{{ $bid->bid_id }}"
     data-bid="{{ $bidJson }}"
     style="cursor:pointer;position:relative;">

    {{-- ── Rank badge (absolute top-right, circular) ── --}}
    <div class="pdm-bdr-rank-badge{{ $isTop ? ' pdm-bdr-rank-badge--gold' : '' }}">
        @if ($rankEmoji)
            <span class="pdm-bdr-rank-emoji">{{ $rankEmoji }}</span>
        @else
            <span class="pdm-bdr-rank-num">{{ $rankNum }}</span>
        @endif
    </div>

    {{-- ── RECOMMENDED pill (top bid only) ── --}}
    @if ($isTop)
        <div class="pdm-bdr-recommended">
            <i class="fi fi-rr-badge-check"></i>
            <span>RECOMMENDED</span>
        </div>
    @endif

    {{-- ── Contractor row: avatar | info | status ── --}}
    <div class="pdm-bdr-contractor-row">
        {{-- Avatar --}}
        <div class="pdm-bid-avatar">
            <img src="{{ $bid->profile_pic ? '/storage/' . $bid->profile_pic : '/img/defaults/contractor_default.png' }}"
                 alt="{{ $name }}" loading="lazy"
                 onerror="this.onerror=null;this.src='/img/defaults/contractor_default.png'">
        </div>

        {{-- Info --}}
        <div class="pdm-bdr-info">
            <span class="pdm-bid-name">{{ $name }}</span>
            @if (count($metaParts))
                <span class="pdm-bdr-meta">
                    @if ($experience !== null)
                        <i class="fi fi-rr-briefcase"></i>
                    @endif
                    {{ implode(' · ', $metaParts) }}
                </span>
            @endif
        </div>

        {{-- Status badge --}}
        <span class="pdm-bid-status-pill pdm-bid-status-{{ $status }}">
            {{ ucwords(str_replace('_', ' ', $status)) }}
        </span>
    </div>

    {{-- ── Stats row: Proposed Cost | Timeline ── --}}
    <div class="pdm-bid-stats-row">
        <div class="pdm-bid-stat-item">
            <span class="pdm-bid-stat-lbl">Proposed Cost</span>
            <span class="pdm-bid-stat-val">{{ $cost }}</span>
        </div>
        <div class="pdm-bid-stat-divider"></div>
        <div class="pdm-bid-stat-item">
            <span class="pdm-bid-stat-lbl">Timeline</span>
            <span class="pdm-bid-stat-val">{{ $timeline }}</span>
        </div>
    </div>

    {{-- ── Notes ── --}}
    @if (!empty($bid->contractor_notes))
        <div class="pdm-bdr-notes-block">
            <span class="pdm-bdr-notes-label">Notes</span>
            <p class="pdm-bid-notes">{{ $bid->contractor_notes }}</p>
        </div>
    @endif

    {{-- ── Footer: date + file count ── --}}
    <div class="pdm-bdr-footer">
        <span class="pdm-bdr-date">
            <i class="fi fi-rr-clock"></i>{{ $submittedDate }}
        </span>
        <span class="pdm-bdr-files">
            <i class="fi fi-rr-clip"></i>{{ $fileCount }} file{{ $fileCount !== 1 ? 's' : '' }}
        </span>
    </div>

    {{-- ── Action buttons / status badges ── --}}
    @if ($isActionable)
        <div class="pdm-bdr-actions">
            <button class="pdm-bdr-accept-btn" data-bid-id="{{ $bid->bid_id }}" type="button">
                <i class="fi fi-rr-check"></i> Accept
            </button>
            <button class="pdm-bdr-reject-btn" data-bid-id="{{ $bid->bid_id }}" type="button">
                <i class="fi fi-rr-cross"></i> Decline
            </button>
        </div>
    @elseif ($status === 'accepted')
        <div class="pdm-bdr-accepted-badge">
            <i class="fi fi-rr-badge-check"></i> Accepted
        </div>
    @elseif ($status === 'rejected')
        <div class="pdm-bdr-rejected-badge">
            <i class="fi fi-rr-cross-circle"></i> Rejected
        </div>
    @endif

</div>
@endforeach
