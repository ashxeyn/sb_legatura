@extends('layouts.appContractor')

@section('title', 'My Bids - Legatura')

@section('content')
    @php
        // Compute badge counts from server-provided $bids
        $pendingCount = 0;
        $acceptedCount = 0;
        $rejectedCount = 0;
        $hasBids = isset($bids) && count($bids) > 0;
        if ($hasBids) {
            foreach ($bids as $b) {
                $bid = is_array($b) ? (object)$b : $b;
                $status = $bid->bid_status ?? '';
                if (in_array($status, ['submitted', 'under_review'])) {
                    $pendingCount++;
                } elseif ($status === 'accepted') {
                    $acceptedCount++;
                } elseif ($status === 'rejected') {
                    $rejectedCount++;
                }
            }
        }
    @endphp

    <div class="contractor-mybids min-h-screen bg-gray-50">
        <!-- Header Section -->
        <div class="mybids-header bg-white shadow-md">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
                <!-- Back Button and Breadcrumb -->
                <div class="mb-6">
                    <div class="flex items-center gap-4 mb-3">
                        <a href="{{ route('contractor.dashboard') }}" class="back-button">
                            <i class="fi fi-rr-arrow-left"></i>
                            <span>Back</span>
                        </a>
                        <div class="breadcrumb">
                            <a href="{{ route('contractor.dashboard') }}" class="breadcrumb-link">Dashboard</a>
                            <i class="fi fi-rr-angle-right breadcrumb-separator"></i>
                            <span class="breadcrumb-current">My Bids</span>
                        </div>
                    </div>
                </div>

                <!-- Title and Filter -->
                <div class="section-header">
                    <div>
                        <h1 class="text-3xl font-bold text-gray-900">My Bids</h1>
                        <p class="text-gray-600 mt-1">Track and manage all your submitted bids</p>
                    </div>
                    <div class="filter-container">
                        <button type="button" class="filter-icon-btn" id="filterBtn" aria-label="Filter bids">
                            <i class="fi fi-rr-filter"></i>
                            <span class="filter-badge hidden" id="filterBadge"></span>
                        </button>
                        <div class="filter-dropdown" id="filterDropdown">
                            <div class="filter-dropdown-content">
                                <div class="filter-dropdown-header">
                                    <h3 class="filter-dropdown-title">
                                        <i class="fi fi-rr-filter"></i>
                                        Filter Bids
                                    </h3>
                                    <button type="button" class="filter-close-btn" id="filterCloseBtn" aria-label="Close filter">
                                        <i class="fi fi-rr-cross-small"></i>
                                    </button>
                                </div>
                                <div class="filter-dropdown-body">
                                    <div class="filter-group">
                                        <label class="filter-label">
                                            <i class="fi fi-rr-flag"></i>
                                            Status
                                        </label>
                                        <select class="filter-select" id="statusFilter">
                                            <option value="all">All Status</option>
                                            <option value="pending">Pending</option>
                                            <option value="accepted">Accepted</option>
                                            <option value="rejected">Rejected</option>
                                            <option value="withdrawn">Withdrawn</option>
                                        </select>
                                    </div>
                                    <div class="filter-group">
                                        <label class="filter-label">
                                            <i class="fi fi-rr-sort"></i>
                                            Sort By
                                        </label>
                                        <select class="filter-select" id="sortFilter">
                                            <option value="newest">Newest First</option>
                                            <option value="oldest">Oldest First</option>
                                            <option value="amount_high">Bid Amount (High to Low)</option>
                                            <option value="amount_low">Bid Amount (Low to High)</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="filter-dropdown-footer">
                                    <button type="button" class="btn-filter-clear" id="clearFilters">
                                        <i class="fi fi-rr-cross-small"></i>
                                        Clear
                                    </button>
                                    <button type="button" class="btn-filter-apply" id="filterApplyBtn">
                                        <i class="fi fi-rr-check"></i>
                                        Apply
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Tab Filters -->
                <div class="tab-filters">
                    <button class="tab-filter-btn active" data-filter="pending">
                        <span class="tab-filter-text">Pending</span>
                        <span class="tab-filter-badge" id="pendingBadge">{{ $pendingCount }}</span>
                    </button>
                    <button class="tab-filter-btn" data-filter="accepted">
                        <span class="tab-filter-text">Accepted</span>
                        <span class="tab-filter-badge" id="acceptedBadge">{{ $acceptedCount }}</span>
                    </button>
                    <button class="tab-filter-btn" data-filter="rejected">
                        <span class="tab-filter-text">Rejected</span>
                        <span class="tab-filter-badge" id="rejectedBadge">{{ $rejectedCount }}</span>
                    </button>
                </div>
            </div>
        </div>

        <!-- Bids Grid -->
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
            <div id="bidsContainer" class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                @if($hasBids)
                    @foreach($bids as $bidItem)
                        @php
                            $bid = is_array($bidItem) ? (object)$bidItem : $bidItem;

                            // Determine tab status group
                            $rawStatus = $bid->bid_status ?? '';
                            if (in_array($rawStatus, ['submitted', 'under_review'])) {
                                $tabStatus = 'pending';
                                $statusText = $rawStatus === 'under_review' ? 'Under Review' : 'Pending';
                                $statusInfo = $rawStatus === 'under_review' ? 'Owner is reviewing your bid' : 'Waiting for owner review';
                            } elseif ($rawStatus === 'accepted') {
                                $tabStatus = 'accepted';
                                $statusText = 'Accepted';
                                $statusInfo = 'Bid accepted - Contract pending';
                            } elseif ($rawStatus === 'rejected') {
                                $tabStatus = 'rejected';
                                $statusText = 'Rejected';
                                $statusInfo = 'Owner selected another contractor';
                            } else {
                                $tabStatus = $rawStatus;
                                $statusText = ucfirst(str_replace('_', ' ', $rawStatus));
                                $statusInfo = '';
                            }

                            // Owner info
                            $ownerName = trim($bid->owner_name ?? '');
                            $initials = collect(explode(' ', $ownerName))->filter()->map(function($w){ return strtoupper(substr($w,0,1)); })->take(2)->join('');

                            // Image from project files
                            $imageSrc = '';
                            if (!empty($bid->project_files)) {
                                $files = $bid->project_files;
                                $first = is_object($files) && method_exists($files, 'first') ? $files->first() : (is_array($files) && count($files) > 0 ? $files[0] : null);
                                if ($first) {
                                    $filePath = is_string($first) ? $first : (is_array($first) ? ($first['file_path'] ?? '') : ($first->file_path ?? ''));
                                    if ($filePath) $imageSrc = asset('storage/' . ltrim($filePath, '/'));
                                }
                            }

                            // Submitted date
                            $submittedDate = $bid->submitted_at ? \Carbon\Carbon::parse($bid->submitted_at)->format('F j, Y') : '—';

                            // Budget display
                            $budgetDisplay = '';
                            if (!empty($bid->budget_range_min) && !empty($bid->budget_range_max)) {
                                $budgetDisplay = '₱' . number_format($bid->budget_range_min) . ' - ₱' . number_format($bid->budget_range_max);
                            }
                        @endphp

                        <div class="bid-card bg-white rounded-xl shadow-md hover:shadow-xl transition-all duration-300 overflow-hidden border border-gray-200"
                             data-status="{{ $tabStatus }}"
                             data-bid-id="{{ $bid->bid_id }}"
                             data-submitted="{{ $bid->submitted_at ?? '' }}"
                             data-amount="{{ $bid->proposed_cost ?? 0 }}">
                            <!-- Card Header -->
                            <div class="bid-card-header p-5 border-b border-gray-100">
                                <div class="flex gap-4">
                                    <!-- Project Image -->
                                    <div class="project-image-wrapper">
                                        <img src="{{ $imageSrc }}" alt="Project" class="bid-card-image" onerror="this.style.display='none';">
                                    </div>

                                    <!-- Project Info -->
                                    <div class="flex-1 min-w-0">
                                        <div class="flex items-start justify-between mb-3">
                                            <div class="flex items-center gap-2 flex-wrap">
                                                <span class="project-type-badge">
                                                    <i class="fi fi-rr-briefcase"></i>
                                                    <span class="project-type">{{ $bid->type_name ?? ($bid->property_type ?? '') }}</span>
                                                </span>
                                                <span class="status-badge status-{{ $tabStatus }} px-3 py-1 rounded-full text-xs font-semibold flex items-center gap-1.5 whitespace-nowrap">
                                                    <i class="fi fi-rr-circle-small status-icon"></i>
                                                    <span class="status-text">{{ $statusText }}</span>
                                                </span>
                                            </div>
                                        </div>
                                        <h3 class="project-title text-xl font-bold text-gray-900 mb-2">{{ $bid->project_title ?? '—' }}</h3>
                                        <p class="project-description text-gray-600 text-sm line-clamp-2">{{ Str::limit($bid->project_description ?? '', 120) }}</p>
                                    </div>
                                </div>
                            </div>

                            <!-- Card Body -->
                            <div class="bid-card-body p-5">
                                <!-- Bid Amount Section -->
                                <div class="bid-amount-section mb-4 p-4 bg-gradient-to-br from-orange-50 to-orange-100 rounded-lg">
                                    <p class="text-xs text-gray-600 mb-1">YOUR BID AMOUNT</p>
                                    <p class="text-2xl font-bold text-orange-600 bid-amount">₱{{ number_format($bid->proposed_cost ?? 0, 2) }}</p>
                                </div>

                                <!-- Property Owner Info -->
                                <div class="owner-section mb-4 p-3 bg-gray-50 rounded-lg">
                                    <div class="flex items-start justify-between">
                                        <div class="flex items-center gap-3 flex-1">
                                            <div class="owner-avatar w-10 h-10 rounded-full bg-gradient-to-br from-orange-400 to-orange-600 flex items-center justify-center text-white font-semibold text-sm flex-shrink-0">{{ $initials ?: '—' }}</div>
                                            <div class="flex-1 min-w-0">
                                                <p class="owner-label text-xs text-gray-500 mb-1">PROPERTY OWNER</p>
                                                <p class="owner-name font-medium text-gray-900 text-sm truncate">{{ $ownerName ?: '—' }}</p>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Bid Details -->
                                <div class="bid-details space-y-2 mb-4">
                                    <div class="detail-item flex items-center gap-2 text-sm text-gray-600">
                                        <i class="fi fi-rr-marker text-gray-400"></i>
                                        <span class="project-location truncate">{{ $bid->project_location ?? '—' }}</span>
                                    </div>
                                    <div class="detail-item flex items-center gap-2 text-sm text-gray-600">
                                        <i class="fi fi-rr-money text-green-500"></i>
                                        <span class="project-budget">@if($budgetDisplay) Project Budget: {{ $budgetDisplay }} @else — @endif</span>
                                    </div>
                                    <div class="detail-item flex items-center gap-2 text-sm text-gray-600">
                                        <i class="fi fi-rr-calendar text-blue-500"></i>
                                        <span class="bid-date">Submitted: {{ $submittedDate }}</span>
                                    </div>
                                </div>

                                <!-- Bid Status Info -->
                                <div class="bid-status-info">
                                    <div class="flex items-center gap-2 text-sm">
                                        <i class="fi fi-rr-clock status-info-icon"></i>
                                        <span class="status-info-text">{{ $statusInfo }}</span>
                                    </div>
                                </div>
                            </div>

                            <!-- Card Footer -->
                            <div class="bid-card-footer p-5 bg-gray-50 border-t border-gray-100">
                                <div class="flex gap-3">
                                    <button class="action-btn view-details-btn flex-1 px-4 py-2 rounded-lg transition-colors font-medium text-sm flex items-center justify-center gap-2 bg-orange-500 text-white hover:bg-orange-600"
                                            onclick="window.handleViewBidDetails({{ $bid->bid_id }})">
                                        <i class="fi fi-rr-eye"></i>
                                        <span>View Details</span>
                                    </button>
                                    @if($tabStatus === 'pending')
                                        <button class="action-btn edit-bid-btn px-4 py-2 rounded-lg transition-colors font-medium text-sm flex items-center justify-center gap-2 bg-blue-50 text-blue-600 hover:bg-blue-100 border border-blue-100"
                                                onclick='window.handleEditBid({{ $bid->project_id }}, @json($bid)); event.stopPropagation();'>
                                            <i class="fi fi-rr-pencil"></i>
                                        </button>
                                        <button class="action-btn withdraw-btn px-4 py-2 rounded-lg transition-colors font-medium text-sm flex items-center justify-center gap-2 bg-gray-200 text-gray-700 hover:bg-gray-300"
                                                onclick="window.handleWithdrawBid({{ $bid->bid_id }})">
                                            <i class="fi fi-rr-cross-circle"></i>
                                            <span>Withdraw</span>
                                        </button>
                                    @endif
                                </div>
                            </div>
                        </div>
                    @endforeach
                @endif
            </div>

            <!-- Empty State -->
            <div id="emptyState" class="hidden text-center py-16">
                <i class="fi fi-rr-document text-6xl text-gray-300 mb-4"></i>
                <h3 class="text-xl font-semibold text-gray-700 mb-2">No bids found</h3>
                <p class="text-gray-500">Try adjusting your filters or submit new bids</p>
            </div>
        </div>
    </div>

    <!-- Bid Card Template (kept for JS fallback) -->
    <template id="bidCardTemplate">
        <div class="bid-card bg-white rounded-xl shadow-md hover:shadow-xl transition-all duration-300 overflow-hidden border border-gray-200">
            <!-- Card Header -->
            <div class="bid-card-header p-5 border-b border-gray-100">
                <div class="flex gap-4">
                    <!-- Project Image -->
                    <div class="project-image-wrapper">
                        <img src="" alt="Project" class="bid-card-image">
                    </div>

                    <!-- Project Info -->
                    <div class="flex-1 min-w-0">
                        <div class="flex items-start justify-between mb-3">
                            <div class="flex items-center gap-2 flex-wrap">
                                <span class="project-type-badge">
                                    <i class="fi fi-rr-briefcase"></i>
                                    <span class="project-type"></span>
                                </span>
                                <span class="status-badge px-3 py-1 rounded-full text-xs font-semibold flex items-center gap-1.5 whitespace-nowrap">
                                    <i class="fi fi-rr-circle-small status-icon"></i>
                                    <span class="status-text"></span>
                                </span>
                            </div>
                        </div>
                        <h3 class="project-title text-xl font-bold text-gray-900 mb-2"></h3>
                        <p class="project-description text-gray-600 text-sm line-clamp-2"></p>
                    </div>
                </div>
            </div>

            <!-- Card Body -->
            <div class="bid-card-body p-5">
                <!-- Bid Amount Section -->
                <div class="bid-amount-section mb-4 p-4 bg-gradient-to-br from-orange-50 to-orange-100 rounded-lg">
                    <p class="text-xs text-gray-600 mb-1">YOUR BID AMOUNT</p>
                    <p class="text-2xl font-bold text-orange-600 bid-amount"></p>
                </div>

                <!-- Property Owner Info -->
                <div class="owner-section mb-4 p-3 bg-gray-50 rounded-lg">
                    <div class="flex items-start justify-between">
                        <div class="flex items-center gap-3 flex-1">
                            <div class="owner-avatar w-10 h-10 rounded-full bg-gradient-to-br from-orange-400 to-orange-600 flex items-center justify-center text-white font-semibold text-sm flex-shrink-0"></div>
                            <div class="flex-1 min-w-0">
                                <p class="owner-label text-xs text-gray-500 mb-1">PROPERTY OWNER</p>
                                <p class="owner-name font-medium text-gray-900 text-sm truncate"></p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Bid Details -->
                <div class="bid-details space-y-2 mb-4">
                    <div class="detail-item flex items-center gap-2 text-sm text-gray-600">
                        <i class="fi fi-rr-marker text-gray-400"></i>
                        <span class="project-location truncate"></span>
                    </div>
                    <div class="detail-item flex items-center gap-2 text-sm text-gray-600">
                        <i class="fi fi-rr-money text-green-500"></i>
                        <span class="project-budget"></span>
                    </div>
                    <div class="detail-item flex items-center gap-2 text-sm text-gray-600">
                        <i class="fi fi-rr-calendar text-blue-500"></i>
                        <span class="bid-date"></span>
                    </div>
                </div>

                <!-- Bid Status Info -->
                <div class="bid-status-info">
                    <div class="flex items-center gap-2 text-sm">
                        <i class="fi fi-rr-clock status-info-icon"></i>
                        <span class="status-info-text"></span>
                    </div>
                </div>
            </div>

            <!-- Card Footer -->
            <div class="bid-card-footer p-5 bg-gray-50 border-t border-gray-100">
                <div class="flex gap-3">
                    <button class="action-btn view-details-btn flex-1 px-4 py-2 rounded-lg transition-colors font-medium text-sm flex items-center justify-center gap-2 bg-orange-500 text-white hover:bg-orange-600">
                        <i class="fi fi-rr-eye"></i>
                        <span>View Details</span>
                    </button>
                    <button class="action-btn withdraw-btn px-4 py-2 rounded-lg transition-colors font-medium text-sm flex items-center justify-center gap-2 bg-gray-200 text-gray-700 hover:bg-gray-300 hidden">
                        <i class="fi fi-rr-cross-circle"></i>
                        <span>Withdraw</span>
                    </button>
                </div>
            </div>
        </div>
    </template>

    <!-- Apply Bid Modals -->
    @if($hasBids)
        @foreach($bids as $bidItem)
            @php $bid = is_array($bidItem) ? (object)$bidItem : $bidItem; @endphp
            @include('contractor.contractor_Modals.contractorApplybids_Modal', ['project' => $bid])
        @endforeach
    @endif

    <!-- Individual Budget Warning Modals for each bid -->
    @if($hasBids)
        @foreach($bids as $bidItem)
            @php $bid = is_array($bidItem) ? (object)$bidItem : $bidItem; @endphp
            @include('contractor.contractor_Modals.budgetWarning_Modal', ['project' => $bid])
        @endforeach
    @endif

    <!-- Bid Details Modal -->
    @include('contractor.contractor_Modals.contractorMybidsDetails_Modals')

    <script>
        // Expose server-rendered bids and current user id for the client script
        window.serverRendered = true;
        window.serverBids = @json($bids ?? []);
        window.currentUser = window.currentUser || {};
        window.currentUser.id = @json($userId ?? null);
    </script>
@endsection

@section('extra_css')
    <link rel="stylesheet" href="{{ asset('css/contractor/contractor_Mybids.css') }}">
    <link rel="stylesheet" href="{{ asset('css/contractor/contractor_Modals/contractorApplybids_Modal.css') }}">
    <link rel="stylesheet" href="{{ asset('css/contractor/contractor_Modals/contractorMybidsDetails_Modals.css') }}">
@endsection

@section('extra_js')
    <script src="{{ asset('js/contractor/contractor_Modals/contractorApplybids_Modal.js') }}"></script>
    <script src="{{ asset('js/contractor/contractor_Modals/contractorMybidsDetails_Modals.js') }}"></script>
    <script src="{{ asset('js/contractor/contractor_Mybids.js') }}?v={{ time() }}"></script>
    <script>
        // Set Dashboard link as active when on my bids page
        document.addEventListener('DOMContentLoaded', () => {
            const navbarLinks = document.querySelectorAll('.navbar-link');
            navbarLinks.forEach(link => {
                link.classList.remove('active');
                if (link.textContent.trim() === 'Dashboard' || link.getAttribute('href') === '{{ route("contractor.dashboard") }}') {
                    link.classList.add('active');
                }
            });

            // Update navbar search placeholder
            const navbarSearchInput = document.querySelector('.navbar-search-input');
            if (navbarSearchInput) {
                navbarSearchInput.placeholder = 'Search bids...';
            }
        });
    </script>
@endsection
