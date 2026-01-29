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
            <!-- Card Header -->
            <div class="project-card-header p-5 border-b border-gray-100">
                <div class="flex items-start justify-between mb-3">
                    <h3 class="project-title text-xl font-bold text-gray-900 flex-1 pr-2"></h3>
                    <span class="status-badge px-3 py-1 rounded-full text-xs font-semibold flex items-center gap-1.5 whitespace-nowrap">
                        <span class="status-dot w-2 h-2 rounded-full"></span>
                        <span class="status-text"></span>
                    </span>
                </div>
                <div class="flex items-center gap-2 text-sm text-gray-600">
                    <i class="fi fi-rr-briefcase"></i>
                    <span class="project-type"></span>
                </div>
            </div>

            <!-- Card Body -->
            <div class="project-card-body p-5">
                <!-- Project Image and Description -->
                <div class="flex gap-4 mb-4">
                    <div class="project-image-container flex-shrink-0">
                        <img class="project-image rounded-lg object-cover" src="" alt="Project image" onerror="this.onerror=null; this.src='https://via.placeholder.com/120x120/EEA24B/ffffff?text=Project'">
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="project-description text-gray-600 text-sm line-clamp-2"></p>
                    </div>
                </div>

                <!-- Contractor Info -->
                <div class="contractor-section mb-4 p-3 bg-gray-50 rounded-lg">
                    <div class="flex items-center gap-3">
                        <div class="contractor-avatar w-10 h-10 rounded-full bg-gradient-to-br from-orange-400 to-orange-600 flex items-center justify-center text-white font-semibold text-sm flex-shrink-0"></div>
                        <div class="flex-1 min-w-0">
                            <p class="contractor-name font-medium text-gray-900 text-sm truncate"></p>
                            <p class="contractor-role text-xs text-gray-500"></p>
                        </div>
                        <div class="contractor-rating flex items-center gap-1 text-xs text-gray-600">
                            <i class="fi fi-rr-star text-yellow-400"></i>
                            <span class="rating-value"></span>
                        </div>
                    </div>
                </div>

                <!-- Project Details -->
                <div class="project-details space-y-2 mb-4">
                    <div class="detail-item flex items-center gap-2 text-sm text-gray-600">
                        <i class="fi fi-rr-marker text-orange-500"></i>
                        <span class="project-location truncate"></span>
                    </div>
                    <div class="detail-item flex items-center gap-2 text-sm text-gray-600">
                        <i class="fi fi-rr-money text-orange-500"></i>
                        <span class="project-budget"></span>
                    </div>
                    <div class="detail-item flex items-center gap-2 text-sm text-gray-600">
                        <i class="fi fi-rr-calendar text-orange-500"></i>
                        <span class="project-date"></span>
                    </div>
                </div>

                <!-- Progress Bar -->
                <div class="progress-section mb-4">
                    <div class="flex items-center justify-between mb-2">
                        <span class="text-xs font-medium text-gray-700">Progress</span>
                        <span class="progress-percentage text-xs font-semibold text-orange-600"></span>
                    </div>
                    <div class="w-full bg-gray-200 rounded-full h-2">
                        <div class="progress-bar h-2 rounded-full transition-all duration-500 bg-gradient-to-r from-orange-400 to-orange-600"></div>
                    </div>
                </div>
            </div>

                    <!-- Card Footer -->
                    <div class="project-card-footer p-5 bg-gray-50 border-t border-gray-100">
                        <div class="flex gap-2">
                            <button class="pin-project-btn px-4 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 transition-colors font-medium text-sm flex items-center justify-center gap-2 hidden">
                                <i class="fi fi-rr-bookmark"></i>
                                <span>Pin Project</span>
                            </button>
                        <button class="view-details-btn w-full px-4 py-2 bg-orange-500 text-white rounded-lg hover:bg-orange-600 transition-colors font-medium text-sm flex items-center justify-center gap-2">
                            <i class="fi fi-rr-eye"></i>
                            <span>View Details</span>
                        </button>
                        </div>
                    </div>
        </div>
    </template>

    <!-- Project Details Modal -->
    @include('owner.propertyOwner_Modals.ownerProjectdetails_modal')
@endsection

@section('extra_css')
    <link rel="stylesheet" href="{{ asset('css/owner/propertyOwner_Allprojects.css') }}">
    <link rel="stylesheet" href="{{ asset('css/owner/propertyOwner_Modals/ownerProjectdetails_modal.css') }}">
@endsection

@section('extra_js')
    <script src="{{ asset('js/owner/propertyOwner_Modals/ownerProjectdetails_modal.js') }}"></script>
    <script src="{{ asset('js/owner/propertyOwner_Allprojects.js') }}"></script>
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
