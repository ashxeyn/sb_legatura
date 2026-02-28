@extends('layouts.app')

@section('title', 'All Projects - Legatura')

@section('content')
    <div class="property-owner-allprojects min-h-screen bg-gray-50">
        <!-- Header Section -->
        <div class="allprojects-header bg-white shadow-md">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
                <!-- Back Button and Breadcrumb -->
                <div class="mb-6">
                    <div class="flex items-center gap-4 mb-3">
                        <a href="{{ route('owner.dashboard') }}" class="back-button">
                            <i class="fi fi-rr-arrow-left"></i>
                            <span>Back</span>
                        </a>
                        <div class="breadcrumb">
                            <a href="{{ route('owner.dashboard') }}" class="breadcrumb-link">Dashboard</a>
                            <i class="fi fi-rr-angle-right breadcrumb-separator"></i>
                            <span class="breadcrumb-current">My Projects</span>
                        </div>
                    </div>
                </div>

                @php
                    $countAll        = $projects->count();
                    $countPending    = $projects->where('project_post_status', 'under_review')->count();
                    $countActive     = $projects->filter(fn($p) => $p->project_post_status === 'approved' && $p->project_status === 'open')->count();
                    $countInProgress = $projects->filter(fn($p) => in_array($p->project_status, ['bidding_closed', 'in_progress', 'waiting_milestone_setup']))->count();
                    $countCompleted  = $projects->where('project_status', 'completed')->count();
                @endphp

                <!-- Title and Sort -->
                <div class="section-header">
                    <div>
                        <h1 class="text-3xl font-bold text-gray-900">My Projects</h1>
                        <p class="text-gray-600 mt-1">Manage and track all your projects</p>
                    </div>
                    <div class="sort-wrapper" id="sortWrapper">
                        <button type="button" class="sort-btn" id="sortBtn" aria-haspopup="listbox" aria-expanded="false">
                            <i class="fi fi-rr-sort sort-icon"></i>
                            <span class="sort-label" id="sortLabel">Newest</span>
                            <i class="fi fi-rr-angle-small-down sort-chevron"></i>
                        </button>
                        <div class="sort-dropdown" id="sortDropdown" role="listbox">
                            <button type="button" class="sort-option active" data-value="newest">Newest</button>
                            <button type="button" class="sort-option" data-value="oldest">Oldest</button>
                            <button type="button" class="sort-option" data-value="title">A–Z Title</button>
                            <button type="button" class="sort-option" data-value="z_title">Z–A Title</button>
                            <button type="button" class="sort-option" data-value="budget_high">Budget: High to Low</button>
                            <button type="button" class="sort-option" data-value="budget_low">Budget: Low to High</button>
                        </div>
                        <input type="hidden" id="sortFilter" value="newest">
                    </div>
                </div>

                <!-- Filter Chips -->
                <div class="filter-bar">
                    <div class="filter-chips" id="filterChips">
                        <button type="button" class="filter-chip active" data-filter="all">
                            All <span class="chip-count">{{ $countAll }}</span>
                        </button>
                        <button type="button" class="filter-chip" data-filter="pending">
                            Pending <span class="chip-count">{{ $countPending }}</span>
                        </button>
                        <button type="button" class="filter-chip" data-filter="active">
                            Active <span class="chip-count">{{ $countActive }}</span>
                        </button>
                        <button type="button" class="filter-chip" data-filter="in_progress">
                            In Progress <span class="chip-count">{{ $countInProgress }}</span>
                        </button>
                        <button type="button" class="filter-chip" data-filter="completed">
                            Completed <span class="chip-count">{{ $countCompleted }}</span>
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Projects Grid -->
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
            <div id="projectsContainer" class="grid grid-cols-1 lg:grid-cols-2 gap-6 {{ $projects->isEmpty() ? 'hidden' : '' }}">

                @foreach ($projects as $project)
                    @php
                        $ps  = $project->project_status       ?? '';
                        $pps = $project->project_post_status  ?? '';

                        if ($pps === 'under_review') {
                            $sLabel = 'Under Review';     $sIcon = 'fi fi-rr-clock';        $sBg = '#FEF3C7'; $sCol = '#D97706';
                        } elseif ($ps === 'open') {
                            $sLabel = 'Open for Bidding';  $sIcon = 'fi fi-rr-check-circle'; $sBg = '#D1FAE5'; $sCol = '#059669';
                        } elseif ($ps === 'bidding_closed') {
                            $sLabel = 'Bidding Closed';    $sIcon = 'fi fi-rr-lock';         $sBg = '#DBEAFE'; $sCol = '#2563EB';
                        } elseif (in_array($ps, ['in_progress', 'waiting_milestone_setup'])) {
                            $sLabel = 'In Progress';       $sIcon = 'fi fi-rr-hammer';       $sBg = '#DBEAFE'; $sCol = '#2563EB';
                        } elseif ($ps === 'completed') {
                            $sLabel = 'Completed';         $sIcon = 'fi fi-rr-badge-check';  $sBg = '#D1FAE5'; $sCol = '#059669';
                        } else {
                            $sLabel = $ps ?: 'Pending';    $sIcon = 'fi fi-rr-circle';       $sBg = '#F1F5F9'; $sCol = '#94A3B8';
                        }

                        $bMin   = $project->budget_range_min ? '₱' . number_format($project->budget_range_min) : '₱0';
                        $bMax   = $project->budget_range_max ? '₱' . number_format($project->budget_range_max) : '₱0';
                        $budget = "$bMin – $bMax";
                    @endphp

                    <div class="project-card"
                         data-project-id="{{ $project->project_id }}"
                         data-title="{{ $project->project_title }}"
                         data-type="{{ $project->type_name }}"
                         data-description="{{ $project->project_description }}"
                         data-location="{{ $project->project_location }}"
                         data-status="{{ $ps }}"
                         data-post-status="{{ $pps }}"
                         data-rawdate="{{ $project->created_at }}"
                         data-bids-count="{{ $project->bids_count }}"
                         data-bidding-due="{{ $project->bidding_due }}"
                         data-budget-min="{{ $project->budget_range_min ?? 0 }}"
                         data-budget-max="{{ $project->budget_range_max ?? 0 }}"
                         data-project="{{ htmlspecialchars(json_encode($project), ENT_QUOTES, 'UTF-8') }}">

                        <!-- Left accent bar -->
                        <div class="project-card-accent"></div>

                        <!-- Header: type + status -->
                        <div class="project-card-header">
                            <div class="project-type-tag">
                                <i class="fi fi-rr-briefcase"></i>
                                <span>{{ $project->type_name }}</span>
                            </div>
                            <span class="status-badge" style="background-color: {{ $sBg }}; color: {{ $sCol }};">
                                <i class="{{ $sIcon }} status-icon" style="color: {{ $sCol }};"></i>
                                <span>{{ $sLabel }}</span>
                            </span>
                        </div>

                        <!-- Content -->
                        <div class="project-card-body">
                            <h3 class="project-title">{{ $project->project_title }}</h3>
                            <p class="project-description">{{ $project->project_description }}</p>

                            <div class="project-meta">
                                <div class="meta-row">
                                    <i class="fi fi-rr-marker meta-icon"></i>
                                    <span>{{ $project->project_location }}</span>
                                </div>
                                <div class="meta-row">
                                    <i class="fi fi-rr-peso-sign meta-icon"></i>
                                    <span class="project-budget">{{ $budget }}</span>
                                </div>
                            </div>
                        </div>

                        <!-- Footer -->
                        <div class="project-card-footer">
                            <div class="footer-left">
                                @if ($project->bids_count > 0)
                                    <div class="bids-info" style="display: flex;">
                                        <i class="fi fi-rr-users"></i>
                                        <span>{{ $project->bids_count }} bid{{ $project->bids_count !== 1 ? 's' : '' }}</span>
                                    </div>
                                @endif
                                <div class="deadline-info">
                                    <i class="fi fi-rr-clock deadline-clock-icon"></i>
                                    <span class="deadline-text"></span>
                                </div>
                            </div>
                            <div class="footer-right">
                                <button class="pin-project-btn">
                                    <i class="fi fi-rr-bookmark"></i>
                                    <span>Pin</span>
                                </button>
                                <button class="view-details-btn">
                                    <span>View Details</span>
                                    <i class="fi fi-rr-arrow-right arrow-icon"></i>
                                </button>
                            </div>
                        </div>

                    </div>
                @endforeach

            </div>

            <!-- Empty State -->
            <div id="emptyState" class="{{ $projects->isEmpty() ? '' : 'hidden' }} text-center py-16">
                <i class="fi fi-rr-folder-open text-6xl text-gray-300 mb-4"></i>
                <h3 class="text-xl font-semibold text-gray-700 mb-2">No projects found</h3>
                <p class="text-gray-500">Try adjusting your filters</p>
            </div>
        </div>
    </div>

    <!-- Project Details Modal -->
    @include('owner.propertyOwner_Modals.ownerProjectdetails_modal')
@endsection

@section('extra_css')
    <link rel="stylesheet" href="{{ asset('css/owner/propertyOwner_Allprojects.css') }}?v={{ time() }}">
    <link rel="stylesheet" href="{{ asset('css/owner/propertyOwner_Modals/ownerProjectdetails_modal.css') }}?v={{ time() }}">
@endsection

@section('extra_js')
    @php
        $sessionUser = session('user');
        $authUserId  = $sessionUser ? ($sessionUser->user_id ?? ($sessionUser->id ?? null)) : null;
    @endphp
    <script>
        window.authUserId   = {{ $authUserId ?? 'null' }};
        window.projectsData = {!! json_encode($projects->values()) !!};
    </script>
    <script src="{{ asset('js/owner/propertyOwner_Modals/ownerProjectdetails_modal.js') }}?v={{ time() }}"></script>
    <script src="{{ asset('js/owner/propertyOwner_Allprojects.js') }}?v={{ time() }}"></script>
    <script>
        // Set Dashboard link as active when on all projects page
        document.addEventListener('DOMContentLoaded', () => {
            const navbarLinks = document.querySelectorAll('.navbar-link');
            navbarLinks.forEach(link => {
                link.classList.remove('active');
                if (link.textContent.trim() === 'Dashboard' || link.getAttribute('href') === '{{ route("owner.dashboard") }}') {
                    link.classList.add('active');
                }
            });
            
            // Update navbar search placeholder
            const navbarSearchInput = document.querySelector('.navbar-search-input');
            if (navbarSearchInput) {
                navbarSearchInput.placeholder = 'Search projects...';
            }
        });
    </script>
@endsection
