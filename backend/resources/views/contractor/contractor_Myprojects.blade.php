@extends('layouts.appContractor')

@section('title', 'My Projects - Legatura')

@section('content')
    <div class="contractor-myprojects min-h-screen bg-gray-50">
        <!-- Header Section -->
        <div class="allprojects-header bg-white shadow-md">
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
                            <span class="breadcrumb-current">My Projects</span>
                        </div>
                    </div>
                </div>

                <!-- Title and Filter -->
                <div class="section-header">
                    <div>
                        <h1 class="text-3xl font-bold text-gray-900">My Projects</h1>
                        <p class="text-gray-600 mt-1">Manage and track all your projects</p>
                    </div>
                    <div class="filter-container">
                        <button type="button" class="filter-icon-btn" id="filterBtn" aria-label="Filter projects">
                            <i class="fi fi-rr-filter"></i>
                            <span class="filter-badge hidden" id="filterBadge"></span>
                        </button>
                        <div class="filter-dropdown" id="filterDropdown">
                            <div class="filter-dropdown-content">
                                <div class="filter-dropdown-header">
                                    <h3 class="filter-dropdown-title">
                                        <i class="fi fi-rr-filter"></i>
                                        Filter Projects
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
                                            <option value="active">Active</option>
                                            <option value="in_progress">In Progress</option>
                                            <option value="completed">Completed</option>
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
                                            <option value="title">Title A-Z</option>
                                            <option value="status">Status</option>
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
                    <button class="tab-filter-btn active" data-filter="needs_setup">
                        <span class="tab-filter-text">Needs Setup</span>
                        <span class="tab-filter-badge" id="needsSetupBadge">0</span>
                    </button>
                    <button class="tab-filter-btn" data-filter="in_progress">
                        <span class="tab-filter-text">In Progress</span>
                        <span class="tab-filter-badge" id="inProgressBadge">0</span>
                    </button>
                    <button class="tab-filter-btn" data-filter="completed">
                        <span class="tab-filter-text">Completed</span>
                        <span class="tab-filter-badge" id="completedBadge">0</span>
                    </button>
                </div>
            </div>
        </div>

        <!-- Projects Grid -->
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
            <div id="projectsContainer" class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <!-- Project Cards will be dynamically inserted here -->
            </div>

            <!-- Empty State -->
            <div id="emptyState" class="hidden text-center py-16">
                <i class="fi fi-rr-folder-open text-6xl text-gray-300 mb-4"></i>
                <h3 class="text-xl font-semibold text-gray-700 mb-2">No projects found</h3>
                <p class="text-gray-500">Try adjusting your filters</p>
            </div>
        </div>
    </div>

    <!-- Project Card Template -->
    <template id="projectCardTemplate">
        <div class="project-card bg-white rounded-xl shadow-md hover:shadow-xl transition-all duration-300 overflow-hidden border border-gray-200">
            <!-- Milestone Warning Banner (conditionally shown) -->
            <div class="milestone-warning-banner hidden">
                <i class="fi fi-rr-triangle-warning"></i>
                <span>Tap to setup milestones</span>
            </div>

            <!-- Card Header -->
            <div class="project-card-header p-5 border-b border-gray-100">
                <div class="flex gap-4">
                    <!-- Project Image -->
                    <div class="project-image-wrapper">
                        <img src="" alt="Project" class="project-card-image">
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
            <div class="project-card-body p-5">
                <!-- Property Owner Info -->
                <div class="contractor-section mb-4 p-3 bg-gray-50 rounded-lg">
                    <div class="flex items-start justify-between">
                        <div class="flex items-center gap-3 flex-1">
                            <div class="contractor-avatar w-10 h-10 rounded-full bg-gradient-to-br from-orange-400 to-orange-600 flex items-center justify-center text-white font-semibold text-sm flex-shrink-0"></div>
                            <div class="flex-1 min-w-0">
                                <p class="contractor-label text-xs text-gray-500 mb-1">PROPERTY OWNER</p>
                                <p class="contractor-name font-medium text-gray-900 text-sm truncate"></p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Project Details -->
                <div class="project-details space-y-2 mb-4">
                    <div class="detail-item flex items-center gap-2 text-sm text-gray-600">
                        <i class="fi fi-rr-marker text-gray-400"></i>
                        <span class="project-location truncate"></span>
                    </div>
                    <div class="detail-item flex items-center gap-2 text-sm text-gray-600">
                        <i class="fi fi-rr-money text-green-500"></i>
                        <span class="project-budget"></span>
                    </div>
                </div>

                <!-- Project Status Info -->
                <div class="project-status-info">
                    <div class="flex items-center gap-2 text-sm">
                        <i class="fi fi-rr-clock status-info-icon"></i>
                        <span class="status-info-text"></span>
                    </div>
                </div>
            </div>

            <!-- Card Footer -->
            <div class="project-card-footer p-5 bg-gray-50 border-t border-gray-100">
                <button class="action-btn w-full px-4 py-2 rounded-lg transition-colors font-medium text-sm flex items-center justify-center gap-2">
                    <i class="fi fi-rr-settings action-btn-icon"></i>
                    <span class="action-btn-text">Setup</span>
                </button>
            </div>
        </div>
    </template>

    <!-- Milestone Setup Modal -->
    @include('contractor.contractor_Modals.contractorMilestonesetup_modal')
    
    <!-- Project Details Modal -->
    @include('contractor.contractor_Modals.contractorProjectdetails_Modal')
@endsection

@section('extra_css')
    <link rel="stylesheet" href="{{ asset('css/contractor/contractor_Myprojects.css') }}">
    <link rel="stylesheet" href="{{ asset('css/contractor/contractor_Modals/contractorMilestonesetup_modal.css') }}">
    <link rel="stylesheet" href="{{ asset('css/contractor/contractor_Modals/contractorProjectdetails_Modal.css') }}">
@endsection

@section('extra_js')
    <script src="{{ asset('js/contractor/contractor_Modals/contractorMilestonesetup_modal.js') }}"></script>
    <script src="{{ asset('js/contractor/contractor_Modals/contractorProjectdetails_Modal.js') }}"></script>
    <script src="{{ asset('js/contractor/contractor_Myprojects.js') }}"></script>
    <script>
        // Set Dashboard link as active when on my projects page
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
                navbarSearchInput.placeholder = 'Search projects...';
            }
        });
    </script>
@endsection
