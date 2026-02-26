@extends('layouts.app')

@section('title', 'Property Owner - Legatura')

@section('content')
    <div class="property-owner-homepage">
        <!-- Post Project Section -->
        @php
            $sessionUser  = session('user');
            $sessionUserId = $sessionUser->user_id ?? ($sessionUser->id ?? null);
            $hpOwnerRecord = $sessionUserId ? \DB::table('property_owners')->where('user_id', $sessionUserId)->first() : null;
            $hpFreshUser   = $sessionUserId ? \DB::table('users')->where('user_id', $sessionUserId)->first() : null;
            $hpFirstName   = $hpOwnerRecord->first_name ?? '';
            $hpLastName    = $hpOwnerRecord->last_name  ?? '';
            $hpFullName    = trim($hpFirstName . ' ' . $hpLastName) ?: ($sessionUser->username ?? 'User');
            $hpProfilePic  = $hpFreshUser->profile_pic ?? ($sessionUser->profile_pic ?? null);
            $hpParts       = preg_split('/\s+/', trim($hpFullName));
            $hpInitials    = strtoupper(substr($hpParts[0], 0, 1) . (isset($hpParts[1]) ? substr($hpParts[1], 0, 1) : ''));
        @endphp
        <div class="post-project-section">
            <div class="post-project-container">
                <div class="avatar-container">
                    @if($hpProfilePic)
                        <div class="user-avatar navbar-avatar-initials">
                            <img src="{{ asset('storage/' . $hpProfilePic) }}" alt="{{ $hpFullName }}"
                                style="width:100%;height:100%;object-fit:cover;display:block;"
                                onerror="this.style.display='none'; this.parentElement.innerHTML='<span>{{ $hpInitials }}</span>';">
                        </div>
                    @else
                        <div class="user-avatar navbar-avatar-initials">
                            <span>{{ $hpInitials }}</span>
                        </div>
                    @endif
                </div>
                <button type="button" class="post-project-button" id="openPostModalBtn" aria-label="Post your project">
                    <span class="post-project-text">Post your project ...</span>
                </button>
            </div>
        </div>

        <!-- Main Content -->
        <div class="main-content">
            <div class="content-container">
                <!-- Section Header -->
                <div class="section-header">
                    <h2 class="section-title">Popular Contractors</h2>
                    <div class="filter-container">
                        <button type="button" class="filter-icon-btn" id="filterIconBtn" aria-label="Filter contractors">
                            <i class="fi fi-rr-filter"></i>
                            <span class="filter-badge hidden" id="filterBadge"></span>
                        </button>
                        <div class="filter-dropdown" id="filterDropdown">
                            <div class="filter-dropdown-content">
                                <div class="filter-dropdown-header">
                                    <h3 class="filter-dropdown-title">
                                        <i class="fi fi-rr-filter"></i>
                                        Filter Contractors
                                    </h3>
                                    <button type="button" class="filter-close-btn" id="filterCloseBtn" aria-label="Close filter">
                                        <i class="fi fi-rr-cross-small"></i>
                                    </button>
                                </div>
                                <div class="filter-dropdown-body">
                                    <div class="filter-group">
                                        <label class="filter-label">
                                            <i class="fi fi-rr-wrench"></i>
                                            Contractor Type
                                        </label>
                                        <select class="filter-select" id="filterContractorType">
                                            <option value="">All Types</option>
                                        </select>
                                    </div>
                                    <div class="filter-group">
                                        <label class="filter-label">
                                            <i class="fi fi-rr-marker"></i>
                                            Location (Province)
                                        </label>
                                        <select class="filter-select" id="filterProvince">
                                            <option value="">All Provinces</option>
                                        </select>
                                    </div>
                                    <div class="filter-group">
                                        <label class="filter-label">
                                            <i class="fi fi-rr-marker"></i>
                                            Location (City)
                                        </label>
                                        <select class="filter-select" id="filterCity">
                                            <option value="">All Cities</option>
                                        </select>
                                    </div>
                                    <div class="filter-group">
                                        <label class="filter-label">
                                            <i class="fi fi-rr-star"></i>
                                            Minimum Rating
                                        </label>
                                        <select class="filter-select" id="filterRating">
                                            <option value="">Any Rating</option>
                                            <option value="4.5">4.5+ Stars</option>
                                            <option value="4.0">4.0+ Stars</option>
                                            <option value="3.5">3.5+ Stars</option>
                                            <option value="3.0">3.0+ Stars</option>
                                        </select>
                                    </div>
                                    <div class="filter-group">
                                        <label class="filter-label">
                                            <i class="fi fi-rr-briefcase"></i>
                                            Minimum Experience (Years)
                                        </label>
                                        <select class="filter-select" id="filterExperience">
                                            <option value="">Any Experience</option>
                                            <option value="10">10+ Years</option>
                                            <option value="20">20+ Years</option>
                                            <option value="30">30+ Years</option>
                                            <option value="40">40+ Years</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="filter-dropdown-footer">
                                    <button type="button" class="btn-filter-clear" id="filterClearBtn">
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

                <!-- Contractors Grid -->
                <div class="contractors-grid" id="contractorsGrid">
                    @if(isset($contractors) && count($contractors) > 0)
                        @foreach($contractors as $contractor)
                            <div class="contractor-card">
                                <!-- Cover Photo (uses coverPhotoUri with fallback) -->
                                @php
                                    $coverPhoto = $contractor->cover_photo ?? null;
                                    $defaultCover = asset('img/defaults/cp_default.jpg');
                                    $coverPhotoUri = $coverPhoto ? asset('storage/' . $coverPhoto) : $defaultCover;
                                @endphp
                                <div class="contractor-cover" style="background-color: #F0F0F0; background-image: url('{{ $coverPhotoUri }}'); background-size: cover; background-position: center; position: relative;">
                                    {{-- keep a hidden img to trigger onerror fallback for broken storage URLs --}}
                                    @if($coverPhoto)
                                        <img src="{{ $coverPhotoUri }}" alt="{{ $contractor->company_name }}" class="cover-photo visually-hidden" onerror="this.closest('.contractor-cover').style.backgroundImage = 'url({{ $defaultCover }})'; this.remove();">
                                    @endif

                                    {{-- Avatar placed inside cover so its position is stable across renders --}}
                                    <div class="contractor-avatar-wrapper">
                                        @php
                                            $contractorName = trim($contractor->company_name ?? '');
                                            $initials = collect(explode(' ', $contractorName))->filter()->map(function($w){ return strtoupper(substr($w,0,1)); })->take(2)->join('');
                                            $logoUrl = $contractor->logo_url ?? null;
                                            $defaultAvatar = asset('img/defaults/contractor_default.png');
                                            $avatarUri = $logoUrl ? asset('storage/' . $logoUrl) : $defaultAvatar;
                                        @endphp
                                        <div class="contractor-avatar" style="background: none; padding: 0;">
                                            @if($logoUrl)
                                                <img src="{{ $avatarUri }}" alt="{{ $contractor->company_name }} logo" style="width:100%;height:100%;object-fit:cover;border-radius:50%;background:#f97316;" onerror="this.src='{{ $defaultAvatar }}'">
                                            @else
                                                <img src="{{ $defaultAvatar }}" alt="Default contractor avatar" style="width:100%;height:100%;object-fit:cover;border-radius:50%;background:#f97316;">
                                            @endif
                                            <span class="contractor-initials" style="display:none;">{{ $initials ?: '—' }}</span>
                                        </div>
                                    </div>
                                </div>

                                <!-- Badge Overlay (top right) -->
                                <div class="contractor-badge-overlay">
                                    <span class="badge-text">{{ $contractor->type_name ?? 'Contractor' }}</span>
                                </div>

                                <!-- Header: avatar (overlap) and company info -->
                                <div class="contractor-header">
                                    <div class="contractor-info-block">
                                        <h3 class="contractor-name">{{ $contractor->company_name ?? '—' }}</h3>
                                        <p class="contractor-experience">{{ $contractor->years_of_experience ?? 0 }} years experience</p>
                                    </div>
                                </div>

                                <!-- Details -->
                                <div class="contractor-details-container">
                                    <div class="detail-item">
                                        <i class="fi fi-rr-marker"></i>
                                        <span class="detail-text">{{ $contractor->business_permit_city ?? '—' }}</span>
                                    </div>
                                    <div class="detail-item">
                                        <i class="fi fi-rr-star"></i>
                                        <span class="detail-text">4.5 rating • 0 reviews</span>
                                    </div>
                                    <div class="detail-item">
                                        <i class="fi fi-rr-briefcase"></i>
                                        <span class="detail-text">{{ $contractor->completed_projects ?? 0 }} projects completed</span>
                                    </div>
                                </div>

                                <!-- footer intentionally left blank (contact button removed) -->
                                <div class="contractor-card-footer"></div>
                            </div>
                        @endforeach
                    @else
                        {{-- No contractors - keep empty state below visible via JS/CSS if needed --}}
                    @endif
                </div>

                <!-- Loading Spinner for Infinite Scroll -->
                <div id="contractorsLoading" class="contractors-loading" style="display:none; text-align:center; padding: 1.5rem;">
                    <span class="loader"></span>
                    <span style="margin-left: 0.5rem; color: #6b7280;">Loading more contractors...</span>
                </div>

                <!-- Empty State -->
                <div class="empty-state hidden" id="emptyState">
                    <i class="fi fi-rr-user"></i>
                    <p>No contractors found</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Contractor Card Template (Hidden) -->
    <template id="contractorCardTemplate">
        <div class="contractor-card">
            <!-- Cover Photo -->
            <div class="contractor-cover" style="background-color: #F0F0F0; background-image: url('{{ asset('img/defaults/cp_default.jpg') }}'); background-size: cover; background-position: center; position: relative;">
                <div class="contractor-avatar-wrapper">
                    <div class="contractor-avatar">
                        <img src="{{ asset('img/defaults/contractor_default.png') }}" alt="Default contractor avatar">
                        <span class="contractor-initials"></span>
                    </div>
                </div>
            </div>

            <!-- Badge Overlay (top right) -->
            <div class="contractor-badge-overlay">
                <span class="badge-text"></span>
            </div>

            <!-- Company Info -->
            <div class="contractor-info-block">
                <h3 class="contractor-name"></h3>
                <p class="contractor-experience"></p>
            </div>

            <!-- Details -->
            <div class="contractor-details-container">
                <div class="detail-item">
                    <i class="fi fi-rr-marker"></i>
                    <span class="detail-text"></span>
                </div>
                <div class="detail-item">
                    <i class="fi fi-rr-star"></i>
                    <span class="detail-text"></span>
                </div>
                <div class="detail-item">
                    <i class="fi fi-rr-briefcase"></i>
                    <span class="detail-text"></span>
                </div>
            </div>

            <!-- footer placeholder (button removed) -->
            <div class="contractor-card-footer"></div>
        </div>
    </template>

    <!-- Include Post Project Modal -->
    @include('owner.propertyOwner_Modals.ownerPosting_Modal')
@endsection

@section('extra_css')
    <link rel="stylesheet" href="{{ asset('css/owner/propertyOwner_Homepage.css') }}">
    <link rel="stylesheet" href="{{ asset('css/owner/propertyOwner_Modals/ownerPosting_Modal.css') }}">
@endsection

@section('extra_js')
    @if(isset($jsContractors))
        <script>
            window.serverContractors = {!! json_encode($jsContractors, JSON_UNESCAPED_SLASHES) !!};
        </script>
    @endif
    <script src="{{ asset('js/owner/propertyOwner_Homepage.js') }}"></script>
    <script src="{{ asset('js/owner/propertyOwner_Modals/ownerPosting_Modal.js') }}"></script>
    <script>
        // Update navbar search placeholder and set active link when on property owner homepage
        document.addEventListener('DOMContentLoaded', () => {
            const navbarSearchInput = document.querySelector('.navbar-search-input');
            if (navbarSearchInput) {
                navbarSearchInput.placeholder = 'Search contractors...';
            }
            
            // Set Home link as active
            const navbarLinks = document.querySelectorAll('.navbar-link');
            navbarLinks.forEach(link => {
                link.classList.remove('active');
                if (link.textContent.trim() === 'Home' || link.getAttribute('href') === '{{ route("owner.homepage") }}') {
                    link.classList.add('active');
                }
            });
        });
    </script>
@endsection
