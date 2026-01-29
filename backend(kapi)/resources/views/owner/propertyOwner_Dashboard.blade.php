@extends('layouts.app')

@section('title', 'Property Owner Dashboard - Legatura')

@section('content')
    <div class="property-owner-dashboard">
        <!-- Top Header Section with Orange Background -->
        <div class="dashboard-header">
            <div class="dashboard-header-content">
                <!-- Profile Section -->
                <div class="profile-section">
                    <div class="profile-avatar-container">
                        <div class="profile-avatar" id="profileAvatar">
                            <span class="profile-initials">ES</span>
                        </div>
                        <div class="online-indicator"></div>
                    </div>
                    <div class="profile-info">
                        <p class="greeting" id="greeting">Good Evening</p>
                        <div class="profile-user-info">
                            <span class="profile-user-name" id="profileUserName">Emmanuelle Santos</span>
                            <span class="profile-user-role" id="profileUserRole">@emmanuellesantos</span>
                        </div>
                    </div>
                </div>

                <!-- Statistics Card -->
                <div class="stats-card">
                    <div class="stat-item">
                        <i class="fi fi-rr-chart-histogram stat-icon"></i>
                        <div class="stat-number" id="statTotal">6</div>
                        <div class="stat-label">Total</div>
                    </div>
                    <div class="stat-item">
                        <i class="fi fi-rr-clock stat-icon"></i>
                        <div class="stat-number" id="statPending">1</div>
                        <div class="stat-label">Pending</div>
                    </div>
                    <div class="stat-item">
                        <i class="fi fi-rr-check-circle stat-icon"></i>
                        <div class="stat-number" id="statActive">1</div>
                        <div class="stat-label">Active</div>
                    </div>
                    <div class="stat-item">
                        <i class="fi fi-rr-settings stat-icon"></i>
                        <div class="stat-number" id="statInProgress">3</div>
                        <div class="stat-label">In Progress</div>
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
                                <div class="project-card-header p-4 border-b border-gray-100">
                                    <div class="flex items-start justify-between gap-3">
                                        <!-- Left Side: Title and Contractor Type -->
                                        <div class="flex-1 min-w-0">
                                            <h3 class="project-title text-lg font-bold text-gray-900 mb-1.5"></h3>
                                            <div class="flex items-center gap-2 text-xs text-gray-600">
                                                <i class="fi fi-rr-briefcase"></i>
                                                <span class="project-type"></span>
                                            </div>
                                        </div>
                                        <!-- Right Side: Status Badge and Unpin Button -->
                                        <div class="flex items-center gap-2 flex-shrink-0">
                                            <span class="status-badge px-2.5 py-1 rounded-full text-xs font-semibold flex items-center gap-1.5 whitespace-nowrap">
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
                                <div class="project-card-body p-3 flex flex-col gap-3 flex-1">
                                    <!-- Project Image and Description -->
                                    <div class="flex gap-2.5">
                                        <div class="project-image-container flex-shrink-0">
                                            <img class="project-image rounded-lg object-cover" src="" alt="Project image" onerror="this.onerror=null; this.src='https://via.placeholder.com/100x100/EEA24B/ffffff?text=Project'">
                                        </div>
                                        <div class="flex-1 min-w-0">
                                            <p class="project-description text-gray-600 text-xs leading-relaxed line-clamp-4"></p>
                                        </div>
                                    </div>

                                    <!-- Contractor Info -->
                                    <div class="contractor-section p-2.5 bg-gray-50 rounded-lg">
                                        <div class="flex items-center gap-2.5">
                                            <div class="contractor-avatar w-9 h-9 rounded-full bg-gradient-to-br from-orange-400 to-orange-600 flex items-center justify-center text-white font-semibold text-xs flex-shrink-0"></div>
                                            <div class="flex-1 min-w-0">
                                                <p class="contractor-name font-medium text-gray-900 text-xs truncate"></p>
                                                <p class="contractor-role text-xs text-gray-500"></p>
                                            </div>
                                            <div class="contractor-rating flex items-center gap-1 text-xs text-gray-600">
                                                <i class="fi fi-rr-star text-yellow-400"></i>
                                                <span class="rating-value"></span>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Project Details -->
                                    <div class="project-details space-y-1.5">
                                        <div class="detail-item flex items-center gap-2 text-xs text-gray-600">
                                            <i class="fi fi-rr-marker text-orange-500 text-sm"></i>
                                            <span class="project-location truncate"></span>
                                        </div>
                                        <div class="detail-item flex items-center gap-2 text-xs text-gray-600">
                                            <i class="fi fi-rr-money text-orange-500 text-sm"></i>
                                            <span class="project-budget"></span>
                                        </div>
                                        <div class="detail-item flex items-center gap-2 text-xs text-gray-600">
                                            <i class="fi fi-rr-calendar text-orange-500 text-sm"></i>
                                            <span class="project-date"></span>
                                        </div>
                                    </div>

                                    <!-- Progress Bar -->
                                    <div class="progress-section mt-auto">
                                        <div class="flex items-center justify-between mb-1.5">
                                            <span class="text-xs font-medium text-gray-700">Progress</span>
                                            <span class="progress-percentage text-xs font-semibold text-orange-600"></span>
                                        </div>
                                        <div class="w-full bg-gray-200 rounded-full h-1.5">
                                            <div class="progress-bar h-1.5 rounded-full transition-all duration-500 bg-gradient-to-r from-orange-400 to-orange-600"></div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Card Footer -->
                                <div class="project-card-footer p-4 bg-gray-50 border-t border-gray-100">
                                    <button class="view-details-btn w-full px-4 py-2.5 bg-orange-500 text-white rounded-lg hover:bg-orange-600 transition-colors font-medium text-sm flex items-center justify-center gap-2">
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
                        <!-- All Projects Card -->
                        <div class="project-card" id="allProjectsCard">
                            <div class="project-card-left">
                                <div class="project-icon project-icon-folder">
                                    <i class="fi fi-rr-folder"></i>
                                </div>
                                <div class="project-info">
                                    <h3 class="project-title">All Projects</h3>
                                    <p class="project-subtitle"><span id="allProjectsCount">6</span> projects total</p>
                                </div>
                            </div>
                            <div class="project-card-right">
                                <i class="fi fi-rr-angle-right project-arrow"></i>
                            </div>
                        </div>

                        <!-- Finished Projects Card -->
                        <div class="project-card" id="finishedProjectsCard">
                            <div class="project-card-left">
                                <div class="project-icon project-icon-check">
                                    <i class="fi fi-rr-check"></i>
                                </div>
                                <div class="project-info">
                                    <h3 class="project-title">Finished Projects</h3>
                                    <p class="project-subtitle"><span id="finishedProjectsCount">6</span> completed</p>
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
@endsection

@section('extra_css')
    <link rel="stylesheet" href="{{ asset('css/owner/propertyOwner_Dashboard.css') }}?v={{ time() }}">
@endsection

@section('extra_js')
    <script src="{{ asset('js/owner/propertyOwner_Dashboard.js') }}?v={{ time() }}"></script>
    <script>
        // Set Dashboard link as active when on property owner dashboard
        document.addEventListener('DOMContentLoaded', () => {
            const navbarLinks = document.querySelectorAll('.navbar-link');
            navbarLinks.forEach(link => {
                link.classList.remove('active');
                if (link.textContent.trim() === 'Dashboard' || link.getAttribute('href') === '{{ route("owner.dashboard") }}') {
                    link.classList.add('active');
                }
            });
        });

        // Pass route URLs to JavaScript
        window.ownerRoutes = {
            projects: '{{ route("owner.projects") }}',
            finishedProjects: '{{ route("owner.projects.finished") }}'
        };
    </script>
@endsection
