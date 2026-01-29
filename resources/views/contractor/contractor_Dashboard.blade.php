@extends('layouts.appContractor')

@section('title', 'Contractor Dashboard - Legatura')

@section('content')
    <div class="contractor-dashboard">
        <!-- Top Header Section with Orange Background -->
        <div class="dashboard-header">
            <div class="dashboard-header-content">
                <!-- Profile Section -->
                <div class="profile-section">
                    <div class="profile-avatar-container">
                        <div class="profile-avatar" id="profileAvatar">
                            <span class="profile-initials">BC</span>
                        </div>
                        <div class="online-indicator"></div>
                    </div>
                    <div class="profile-info">
                        <p class="greeting" id="greeting">Good Evening</p>
                        <div class="profile-user-info">
                            <span class="profile-user-name" id="profileUserName">BuildRight Construction</span>
                            <span class="profile-user-role" id="profileUserRole">@buildRight_Construction</span>
                        </div>
                    </div>
                </div>

                <!-- Statistics Card -->
                <div class="stats-card">
                    <div class="stat-item">
                        <i class="fi fi-rr-chart-histogram stat-icon"></i>
                        <div class="stat-number" id="statTotal">10</div>
                        <div class="stat-label">Total Bids</div>
                    </div>
                    <div class="stat-item">
                        <i class="fi fi-rr-clock stat-icon"></i>
                        <div class="stat-number" id="statPending">6</div>
                        <div class="stat-label">Pending</div>
                    </div>
                    <div class="stat-item">
                        <i class="fi fi-rr-check-circle stat-icon"></i>
                        <div class="stat-number" id="statActive">2</div>
                        <div class="stat-label">Won Bids</div>
                    </div>
                    <div class="stat-item">
                        <i class="fi fi-rr-settings stat-icon"></i>
                        <div class="stat-number" id="statInProgress">3</div>
                        <div class="stat-label">Active</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Main Content -->
        <div class="dashboard-content">
            <div class="dashboard-container">
                <!-- Pinned Project Section -->
                <div class="section-pinned">
                    <div class="section-header">
                        <h2 class="section-title">Pinned Projects</h2>
                        <button class="add-pinned-btn" id="addPinnedBtn" title="Add pinned project">
                            <i class="fi fi-rr-plus"></i>
                            <span>Add Pin</span>
                        </button>
                    </div>
                    <div class="pinned-projects-container">
                        <!-- Empty State -->
                        <div class="pinned-card has-empty-state" id="pinnedEmptyCard">
                            <div class="pinned-empty" id="pinnedEmpty">
                                <i class="fi fi-rr-bookmark pinned-icon"></i>
                                <p class="pinned-text">No pinned project</p>
                                <p class="pinned-hint">Click "Add Pin" or tap here to pin a project for quick access</p>
                                <button class="pinned-add-btn" id="pinnedAddBtn">
                                    <i class="fi fi-rr-plus"></i>
                                    <span>Pin a Project</span>
                                </button>
                            </div>
                        </div>
                        
                        <!-- Pinned Projects List -->
                        <div id="pinnedProjectsList" class="pinned-projects-list"></div>
                        
                        <!-- Template for Pinned Project Card -->
                        <template id="pinnedProjectTemplate">
                            <div class="pinned-content project-card bg-white rounded-xl shadow-md hover:shadow-xl transition-all duration-300 overflow-hidden border border-gray-200" data-project-id="">
                                <!-- Card Header -->
                                <div class="project-card-header p-5 border-b border-gray-100">
                                    <div class="flex items-start justify-between gap-4">
                                        <!-- Left Side: Title and Contractor Type -->
                                        <div class="flex-1 min-w-0">
                                            <h3 class="project-title text-xl font-bold text-gray-900 mb-2"></h3>
                                            <div class="flex items-center gap-2 text-sm text-gray-600">
                                                <i class="fi fi-rr-briefcase"></i>
                                                <span class="project-type"></span>
                                            </div>
                                        </div>
                                        <!-- Right Side: Status Badge and Unpin Button -->
                                        <div class="flex items-center gap-2 flex-shrink-0">
                                            <span class="status-badge px-3 py-1 rounded-full text-xs font-semibold flex items-center gap-1.5 whitespace-nowrap">
                                                <span class="status-dot w-2 h-2 rounded-full"></span>
                                                <span class="status-text"></span>
                                            </span>
                                            <button class="pinned-unpin-btn" title="Unpin project">
                                                <i class="fi fi-rr-bookmark-slash"></i>
                                            </button>
                                        </div>
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

                                    <!-- Property Owner Info -->
                                    <div class="contractor-section mb-4 p-3 bg-gray-50 rounded-lg">
                                        <div class="flex items-center gap-3">
                                            <div class="contractor-avatar w-10 h-10 rounded-full bg-gradient-to-br from-orange-400 to-orange-600 flex items-center justify-center text-white font-semibold text-sm flex-shrink-0"></div>
                                            <div class="flex-1 min-w-0">
                                                <p class="contractor-name font-medium text-gray-900 text-sm truncate"></p>
                                                <p class="contractor-role text-xs text-gray-500">Property Owner</p>
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
                                    <button class="view-details-btn w-full px-4 py-2 bg-orange-500 text-white rounded-lg hover:bg-orange-600 transition-colors font-medium text-sm flex items-center justify-center gap-2">
                                        <i class="fi fi-rr-eye"></i>
                                        <span>View Details</span>
                                    </button>
                                </div>
                            </div>
                        </template>
                </div>

                <!-- My Projects Section -->
                <div class="section-projects">
                    <h2 class="section-title">My Projects</h2>
                    <div class="projects-list">
                        <!-- My Projects Card -->
                        <div class="project-card" id="allProjectsCard">
                            <div class="project-card-left">
                                <div class="project-icon project-icon-folder">
                                    <i class="fi fi-rr-briefcase"></i>
                                </div>
                                <div class="project-info">
                                    <h3 class="project-title">My Projects</h3>
                                    <p class="project-subtitle"><span id="allProjectsCount">5</span> projects total</p>
                                </div>
                            </div>
                            <div class="project-card-right">
                                <i class="fi fi-rr-angle-right project-arrow"></i>
                            </div>
                        </div>

                        <!-- My Bids Card -->
                        <div class="project-card" id="finishedProjectsCard">
                            <div class="project-card-left">
                                <div class="project-icon project-icon-check">
                                    <i class="fi fi-rr-file-invoice"></i>
                                </div>
                                <div class="project-info">
                                    <h3 class="project-title">My Bids</h3>
                                    <p class="project-subtitle"><span id="finishedProjectsCount">10</span> bids submitted</p>
                                </div>
                            </div>
                            <div class="project-card-right">
                                <i class="fi fi-rr-angle-right project-arrow"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Pin Project Selection Modal -->
    <div id="pinProjectModal" class="pin-project-modal">
        <div class="modal-overlay" id="pinProjectOverlay"></div>
        <div class="modal-container">
            <!-- Modal Header -->
            <div class="modal-header">
                <div class="modal-header-content">
                    <h2 class="modal-title">
                        <i class="fi fi-rr-bookmark"></i>
                        Pin a Project
                    </h2>
                    <button class="modal-close-btn" id="closePinModalBtn">
                        <i class="fi fi-rr-cross"></i>
                    </button>
                </div>
                <p class="modal-subtitle">Select a project from your active projects to pin to your dashboard</p>
            </div>

            <!-- Modal Body -->
            <div class="modal-body">
                <!-- Search and Filter -->
                <div class="pin-modal-search">
                    <div class="search-input-wrapper">
                        <i class="fi fi-rr-search"></i>
                        <input type="text" 
                               id="pinProjectSearch" 
                               class="search-input" 
                               placeholder="Search projects...">
                    </div>
                    <select id="pinProjectFilter" class="filter-select">
                        <option value="all">All Projects</option>
                        <option value="needs_setup">Needs Setup</option>
                        <option value="in_progress">In Progress</option>
                        <option value="completed">Completed</option>
                    </select>
                </div>

                <!-- Projects List -->
                <div id="pinProjectsList" class="pin-projects-list">
                    <!-- Projects will be loaded here -->
                </div>

                <!-- Empty State -->
                <div id="pinProjectsEmpty" class="pin-projects-empty hidden">
                    <i class="fi fi-rr-folder-open"></i>
                    <p>No projects found</p>
                    <span class="text-sm text-gray-500">Try adjusting your search or filters</span>
                </div>

                <!-- Loading State -->
                <div id="pinProjectsLoading" class="pin-projects-loading">
                    <div class="loading-spinner"></div>
                    <p>Loading projects...</p>
                </div>
            </div>

            <!-- Modal Footer -->
            <div class="modal-footer">
                <button class="modal-btn secondary-btn" id="cancelPinBtn">
                    <i class="fi fi-rr-cross"></i>
                    Cancel
                </button>
            </div>
        </div>
    </div>

    <!-- Project Item Template -->
    <template id="pinProjectItemTemplate">
        <div class="pin-project-item" data-project-id="">
            <div class="pin-project-image-wrapper">
                <img class="pin-project-image" src="" alt="Project">
            </div>
            <div class="pin-project-info">
                <h4 class="pin-project-title"></h4>
                <div class="pin-project-meta">
                    <span class="pin-project-type">
                        <i class="fi fi-rr-briefcase"></i>
                        <span class="type-text"></span>
                    </span>
                    <span class="pin-project-status">
                        <span class="status-dot"></span>
                        <span class="status-text"></span>
                    </span>
                </div>
                <p class="pin-project-location">
                    <i class="fi fi-rr-marker"></i>
                    <span class="location-text"></span>
                </p>
            </div>
            <button class="pin-project-btn" title="Pin this project">
                <i class="fi fi-rr-bookmark"></i>
                <span>Pin</span>
            </button>
        </div>
    </template>
@endsection

@section('extra_css')
    <link rel="stylesheet" href="{{ asset('css/contractor/contractor_Dashboard.css') }}?v={{ time() }}">
@endsection

@section('extra_js')
    <script src="{{ asset('js/contractor/contractor_Dashboard.js') }}?v={{ time() }}"></script>
    <script>
        // Set Dashboard link as active when on contractor dashboard
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
                navbarSearchInput.placeholder = 'Search...';
            }
        });

        // Pass route URLs to JavaScript
        window.contractorRoutes = {
            projects: '{{ route("contractor.projects") }}',
            bids: '{{ route("contractor.mybids") }}'
        };
    </script>
@endsection
