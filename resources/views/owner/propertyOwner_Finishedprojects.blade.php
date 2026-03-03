@extends('layouts.app')

@section('title', 'Finished Projects - Legatura')

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
                            <a href="{{ route('owner.projects') }}" class="breadcrumb-link">My Projects</a>
                            <i class="fi fi-rr-angle-right breadcrumb-separator"></i>
                            <span class="breadcrumb-current">Finished Projects</span>
                        </div>
                    </div>
                </div>

                <!-- Title and Sort -->
                <div class="section-header">
                    <div>
                        <h1 class="text-3xl font-bold text-gray-900">Finished Projects</h1>
                        <p class="text-gray-600 mt-1">View all your completed projects</p>
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

                <!-- Count Summary -->
                <div class="filter-bar">
                    <div class="filter-chips">
                        <span class="filter-chip active" style="cursor:default;">
                            <i class="fi fi-rr-badge-check" style="font-size:0.75rem;"></i>
                            Completed <span class="chip-count">{{ $projects->count() }}</span>
                        </span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Projects Grid -->
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
            <div id="projectsContainer" class="grid grid-cols-1 lg:grid-cols-2 gap-6 {{ $projects->isEmpty() ? 'hidden' : '' }}">

                @foreach ($projects as $project)
                    @php
                        $bMin   = $project->budget_range_min ? '₱' . number_format($project->budget_range_min) : '₱0';
                        $bMax   = $project->budget_range_max ? '₱' . number_format($project->budget_range_max) : '₱0';
                        $budget = "$bMin – $bMax";

                        $completedDate = $project->created_at
                            ? \Carbon\Carbon::parse($project->created_at)->format('M j, Y')
                            : '—';
                    @endphp

                    <div class="project-card project-card--completed"
                         data-project-id="{{ $project->project_id }}"
                         data-title="{{ $project->project_title }}"
                         data-type="{{ $project->type_name }}"
                         data-description="{{ $project->project_description }}"
                         data-location="{{ $project->project_location }}"
                         data-status="completed"
                         data-post-status="{{ $project->project_post_status ?? '' }}"
                         data-rawdate="{{ $project->created_at }}"
                         data-bids-count="{{ $project->bids_count }}"
                         data-bidding-due="{{ $project->bidding_due ?? '' }}"
                         data-budget-min="{{ $project->budget_range_min ?? 0 }}"
                         data-budget-max="{{ $project->budget_range_max ?? 0 }}"
                         data-project="{{ htmlspecialchars(json_encode($project), ENT_QUOTES, 'UTF-8') }}">

                        <!-- Completed Banner -->
                        <div class="completed-banner">
                            <i class="fi fi-rr-badge-check"></i>
                            <span>Project Completed</span>
                        </div>

                        <!-- Left accent bar -->
                        <div class="project-card-accent"></div>

                        <!-- Header: type + status -->
                        <div class="project-card-header">
                            <div class="project-type-tag">
                                <i class="fi fi-rr-briefcase"></i>
                                <span>{{ $project->type_name }}</span>
                            </div>
                            <span class="status-badge" style="background-color: #D1FAE5; color: #059669;">
                                <i class="fi fi-rr-badge-check status-icon" style="color: #059669;"></i>
                                <span>Completed</span>
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

                            @if($project->contractor_info)
                            <div style="margin-top:12px; padding:10px 12px; background:#F8FAFC; border-radius:10px; border:1px solid #E2E8F0;">
                                <div style="display:flex; align-items:center; gap:10px;">
                                    <div style="width:36px; height:36px; border-radius:50%; background:linear-gradient(135deg,#EEA24B,#EC7E00); display:flex; align-items:center; justify-content:center; color:#fff; font-weight:700; font-size:0.8rem; flex-shrink:0;">
                                        {{ strtoupper(substr($project->contractor_info->company_name ?? $project->contractor_info->username ?? '?', 0, 1)) }}
                                    </div>
                                    <div style="flex:1; min-width:0;">
                                        <p style="font-size:0.8125rem; font-weight:600; color:#1E293B; margin:0; white-space:nowrap; overflow:hidden; text-overflow:ellipsis;">
                                            {{ $project->contractor_info->company_name ?? $project->contractor_info->username ?? 'Contractor' }}
                                        </p>
                                        <p style="font-size:0.6875rem; color:#94A3B8; margin:0;">Contractor</p>
                                    </div>
                                    @if($project->milestones_count > 0)
                                    <div style="font-size:0.6875rem; color:#64748B; display:flex; align-items:center; gap:4px;">
                                        <i class="fi fi-rr-flag-alt" style="font-size:0.625rem;"></i>
                                        {{ $project->milestones_count }} milestone{{ $project->milestones_count !== 1 ? 's' : '' }}
                                    </div>
                                    @endif
                                </div>
                            </div>
                            @endif

                            <div style="margin-top:10px; display:flex; align-items:center; gap:6px; font-size:0.75rem; color:#059669;">
                                <i class="fi fi-rr-calendar-check" style="font-size:0.6875rem;"></i>
                                <span>Completed {{ $completedDate }}</span>
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
                            </div>
                            <div class="footer-right">
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
                <i class="fi fi-rr-badge-check text-6xl text-gray-300 mb-4" style="display:block;"></i>
                <h3 class="text-xl font-semibold text-gray-700 mb-2">No finished projects yet</h3>
                <p class="text-gray-500">Completed projects will appear here</p>
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
        // Set Dashboard link as active when on finished projects page
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
                navbarSearchInput.placeholder = 'Search finished projects...';
            }
        });
    </script>
@endsection
