@extends('layouts.app')

@section('title', 'Property Owner Dashboard - Legatura')

@section('content')
    <div class="property-owner-dashboard">
        <!-- Top Header Section with Orange Background -->
        <div class="dashboard-header">
            <div class="dashboard-header-content">
                <!-- Profile Section -->
                @php
                    $dashSessionUser  = session('user');
                    $dashUserId       = $dashSessionUser->user_id ?? ($dashSessionUser->id ?? null);
                    $dashOwnerRecord  = $dashUserId ? \DB::table('property_owners')->where('user_id', $dashUserId)->first() : null;
                    $dashFreshUser    = $dashUserId ? \DB::table('users')->where('user_id', $dashUserId)->first() : null;
                    $dashFirstName    = $dashOwnerRecord->first_name ?? '';
                    $dashLastName     = $dashOwnerRecord->last_name  ?? '';
                    $dashFullName     = trim($dashFirstName . ' ' . $dashLastName) ?: ($dashSessionUser->username ?? 'User');
                    $dashUsername     = $dashSessionUser->username ?? 'user';
                    $dashProfilePic   = $dashFreshUser->profile_pic ?? ($dashSessionUser->profile_pic ?? null);
                    $dashParts        = preg_split('/\s+/', trim($dashFullName));
                    $dashInitials     = strtoupper(substr($dashParts[0], 0, 1) . (isset($dashParts[1]) ? substr($dashParts[1], 0, 1) : ''));
                @endphp
                <div class="profile-section">
                    <div class="profile-avatar-container">
                        <div class="profile-avatar" id="profileAvatar">
                            @if($dashProfilePic)
                                <img src="{{ asset('storage/' . $dashProfilePic) }}" alt="{{ $dashFullName }}"
                                    style="width:100%;height:100%;object-fit:cover;display:block;"
                                    onerror="this.style.display='none'; this.parentElement.innerHTML='<span class=\'profile-initials\'>{{ $dashInitials }}</span>';">
                            @else
                                <span class="profile-initials">{{ $dashInitials }}</span>
                            @endif
                        </div>
                        <div class="online-indicator"></div>
                    </div>
                    <div class="profile-info">
                        <p class="greeting" id="greeting">Good Morning</p>
                        <div class="profile-user-info">
                            <span class="profile-user-name" id="profileUserName">{{ $dashFullName }}</span>
                            <span class="profile-user-role" id="profileUserRole">{{ '@' . $dashUsername }}</span>
                        </div>
                    </div>
                </div>

                <!-- Statistics Card -->
                <div class="stats-card">
                    <div class="stat-item">
                        <i class="fi fi-rr-chart-histogram stat-icon"></i>
                        <div class="stat-number" id="statTotal">{{ $stats['total'] }}</div>
                        <div class="stat-label">Total</div>
                    </div>
                    <div class="stat-item">
                        <i class="fi fi-rr-clock stat-icon"></i>
                        <div class="stat-number" id="statPending">{{ $stats['pending'] }}</div>
                        <div class="stat-label">Pending</div>
                    </div>
                    <div class="stat-item">
                        <i class="fi fi-rr-check-circle stat-icon"></i>
                        <div class="stat-number" id="statActive">{{ $stats['active'] }}</div>
                        <div class="stat-label">Active</div>
                    </div>
                    <div class="stat-item">
                        <i class="fi fi-rr-settings stat-icon"></i>
                        <div class="stat-number" id="statInProgress">{{ $stats['inProgress'] }}</div>
                        <div class="stat-label">In Progress</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Main Content -->
        <div class="dashboard-content">
            <div class="dashboard-container">
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
                                    <p class="project-subtitle"><span id="allProjectsCount">{{ $stats['total'] }}</span> projects total</p>
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
                                    <p class="project-subtitle"><span id="finishedProjectsCount">{{ $stats['completed'] }}</span> completed</p>
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
