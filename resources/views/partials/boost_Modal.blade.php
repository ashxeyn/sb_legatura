<!-- Boost Modal -->
<div id="boostModal" class="boost-modal">
    <div class="modal-overlay" id="boostModalOverlay"></div>
    <div class="boost-modal-container">
        <!-- Modal Header -->
        <div class="boost-modal-header">
            <h2 class="boost-modal-title">
                <i class="fi fi-rr-rocket"></i>
                Boost Your Posts
            </h2>
            <button class="boost-close-btn" id="closeBoostModalBtn" aria-label="Close modal">
                <i class="fi fi-rr-cross"></i>
            </button>
        </div>

        <!-- Tab Navigation -->
        <div class="boost-tabs">
            <button class="boost-tab active" data-tab="boosts" id="boostsTab">
                Boosts
            </button>
            <button class="boost-tab" data-tab="dashboard" id="dashboardTab">
                Dashboard
            </button>
        </div>

        <!-- Modal Body -->
        <div class="boost-modal-body">
            <!-- Boosts Tab Content -->
            <div class="boost-tab-content active" id="boostsContent">
                <div class="boosts-container">
                    <!-- Plan Selection -->
                    <div class="boost-plan-section" id="planSelectionSection">
                        <h3 class="boost-section-title">Choose your boosting plan</h3>

                        <div class="boost-plans-list">
                            <!-- Dynamic Boost Plan -->
                            @if(isset($boostPlan))
                                <div class="boost-plan-card single-plan" data-plan="boost" 
                                     data-reach="{{ $boostPlan->benefits[0] ?? '1,000+' }}" 
                                     data-price="{{ $boostPlan->amount / 100 }}">
                                    <div class="boost-plan-emoji">🚀</div>
                                    <div class="boost-plan-info">
                                        <div class="boost-plan-reach">{{ $boostPlan->benefits[0] ?? '1,000+' }} <span class="reach-label">reach</span></div>
                                        <div class="boost-plan-price">₱{{ number_format($boostPlan->amount / 100, 0) }} <span class="per-post">per post</span></div>
                                    </div>
                                </div>
                            @endif

                            @foreach($ownerPlans ?? [] as $plan)
                                @if($plan->plan_key === 'boost') @continue @endif
                                <div class="boost-plan-card single-plan" data-plan="{{ strtolower($plan->plan_key) }}" 
                                     data-reach="{{ $plan->benefits[0] ?? 'Unlimited' }}" 
                                     data-price="{{ $plan->amount / 100 }}">
                                    <div class="boost-plan-emoji">👑</div>
                                    <div class="boost-plan-info">
                                        <div class="boost-plan-reach">{{ strtoupper($plan->name) }}</div>
                                        <div class="boost-plan-price">₱{{ number_format($plan->amount / 100, 0) }}</div>
                                    </div>
                                </div>
                            @endforeach
                        </div>

                        <!-- Choose Post Section -->
                        <div class="choose-post-section">
                            <h4 class="choose-post-title">Choose post to boost:</h4>
                            <button class="choose-post-btn" id="choosePostBtn">
                                <i class="fi fi-rr-search"></i>
                                Choose
                            </button>
                        </div>
                    </div>

                    <!-- Post Selection -->
                    <div class="boost-post-selection hidden" id="postSelectionSection">
                        <div class="post-selection-header">
                            <button class="back-to-plans-btn" id="backToPlansBtn">
                                <i class="fi fi-rr-angle-left"></i>
                            </button>
                            <h3 class="post-selection-title">Select a post to boost</h3>
                        </div>

                        <!-- Search Bar -->
                        <div class="post-search-bar">
                            <i class="fi fi-rr-search"></i>
                            <input type="text" id="postSearchInput" placeholder="Search through your posts" />
                        </div>

                        <!-- Posts List -->
                        <div class="posts-list" id="postsListContainer">
                              @if(isset($boostableProjects) && count($boostableProjects) > 0)
                                @foreach($boostableProjects as $project)
                                    <div class="post-card" data-post-id="{{ $project->id }}">
                                        <div class="post-card-image">
                                            <img src="{{ $project->image }}" alt="Post image" />
                                        </div>
                                        <div class="post-card-content">
                                            <div class="post-card-header">
                                                <h4 class="post-card-title">{{ $project->title }}</h4>
                                                <span class="post-chosen-badge hidden">Post Chosen</span>
                                            </div>
                                            <p class="post-card-description">{{ strlen($project->description) > 100 ? substr($project->description, 0, 100) . '...' : $project->description }}</p>
                                            <div class="post-card-footer">
                                                <div class="post-card-meta">
                                                    <span class="post-card-location">
                                                        <i class="fi fi-rr-marker"></i>
                                                        <span class="location-text">{{ $project->location }}</span>
                                                    </span>
                                                    <span class="post-card-date">
                                                        <i class="fi fi-rr-calendar"></i>
                                                        <span class="date-text">{{ $project->date }}</span>
                                                    </span>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="post-card-select">
                                            <input type="radio" name="selectedPost" class="post-radio" />
                                        </div>
                                    </div>
                                @endforeach
                            @else
                                <div class="no-posts-found">
                                    <p>No eligible posts found to boost.</p>
                                    <small>Make sure your posts are Approved and Open.</small>
                                </div>
                            @endif
                        </div>

                        <!-- Confirm Button -->
                        <button class="confirm-boost-btn" id="confirmBoostBtn">
                            Confirm
                        </button>
                    </div>

                    <!-- Boosting Summary -->
                    <div class="boost-summary-section hidden" id="boostSummarySection">
                        <div class="boost-summary-header">
                            <div class="boost-summary-icon">
                                <i class="fi fi-rr-check-circle"></i>
                            </div>
                            <h3 class="boost-summary-title">Ready to Boost!</h3>
                        </div>

                        <!-- Selected Post Preview -->
                        <div class="selected-post-preview" id="selectedPostPreview">
                            <!-- Selected post will be shown here -->
                        </div>

                        <!-- Selected Plan -->
                        <div class="selected-plan-info">
                            <h4 class="selected-plan-label">Selected Plan:</h4>
                            <div class="selected-plan-details" id="selectedPlanDetails">
                                <span class="selected-plan-reach"></span>
                                <span class="selected-plan-price"></span>
                            </div>
                        </div>

                        <!-- Action Buttons -->
                        <div class="boost-summary-actions">
                            <button class="change-post-btn" id="changePostBtn">
                                <i class="fi fi-rr-refresh"></i>
                                Change Post
                            </button>
                            <button class="boost-now-btn" id="boostNowBtn">
                                <i class="fi fi-rr-rocket"></i>
                                Boost Now
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Dashboard Tab Content -->
            <div class="boost-tab-content" id="dashboardContent">
                <div class="dashboard-container">
                    <!-- Analytics Section -->
                    <div class="analytics-section">
                        <h3 class="dashboard-section-title">Analytics</h3>
                        @php
                            if (!isset($boostAnalytics) || !isset($boostedPosts)) {
                                try {
                                    $analytics = \App\Models\subs\platformPaymentClass::getBoostAnalytics(auth()->id());
                                    $posts = \App\Models\subs\platformPaymentClass::getBoostedPosts(auth()->id());
                                } catch (\Throwable $e) {
                                    $analytics = null;
                                    $posts = [];
                                }
                            } else {
                                $analytics = $boostAnalytics;
                                $posts = $boostedPosts;
                            }
                        @endphp
                        <div class="analytics-grid">
                            <div class="analytics-card">
                                <div class="analytics-label">Post reach</div>
                                <div class="analytics-value" id="analyticsReach">{{ $analytics['reach'] ?? '0' }}</div>
                                @php $reachChange = $analytics['reach_change'] ?? 0; @endphp
                                <div class="analytics-change {{ $reachChange >= 0 ? 'positive' : 'negative' }}">
                                    <i class="fi fi-rr-arrow-{{ $reachChange >= 0 ? 'up' : 'down' }}"></i>
                                    <span>{{ $reachChange }}%</span>
                                    <span class="change-period">{{ $analytics['period'] ?? '7d' }}</span>
                                </div>
                            </div>
                            <div class="analytics-card">
                                <div class="analytics-label">Bids</div>
                                <div class="analytics-value" id="analyticsBids">{{ $analytics['bids'] ?? '0' }}</div>
                                @php $bidsChange = $analytics['bids_change'] ?? 0; @endphp
                                <div class="analytics-change {{ $bidsChange >= 0 ? 'positive' : 'negative' }}">
                                    <i class="fi fi-rr-arrow-{{ $bidsChange >= 0 ? 'up' : 'down' }}"></i>
                                    <span>{{ $bidsChange }}%</span>
                                    <span class="change-period">{{ $analytics['period'] ?? '7d' }}</span>
                                </div>
                            </div>
                            <div class="analytics-card">
                                <div class="analytics-label">Clicks</div>
                                <div class="analytics-value" id="analyticsClicks">{{ $analytics['clicks'] ?? '0' }}</div>
                                @php $clicksChange = $analytics['clicks_change'] ?? 0; @endphp
                                <div class="analytics-change {{ $clicksChange >= 0 ? 'positive' : 'negative' }}">
                                    <i class="fi fi-rr-arrow-{{ $clicksChange >= 0 ? 'up' : 'down' }}"></i>
                                    <span>{{ $clicksChange }}%</span>
                                    <span class="change-period">{{ $analytics['period'] ?? '2d' }}</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Boosted Posts Section -->
                    <div class="boosted-posts-section">
                        <h3 class="dashboard-section-title">Boosted posts</h3>
                        @php $posts = $posts ?? []; @endphp
                        <div class="boosted-posts-list" id="boostedPostsList">
                            @if(empty($posts))
                                <div class="no-boosted-posts">No boosted posts</div>
                            @else
                                @foreach($posts as $post)
                                    <div class="boosted-post-card" data-post-id="{{ $post->id }}">
                                        <div class="boosted-post-image">
                                            <img src="{{ $post->image_url ?? asset('img/placeholder_project.svg') }}" alt="Post image" />
                                        </div>
                                        <div class="boosted-post-content">
                                            <h4 class="boosted-post-title">{{ $post->title }}</h4>
                                            <p class="boosted-post-description">{{ \Illuminate\Support\Str::limit($post->excerpt ?? ($post->description ?? ''), 150) }}</p>
                                            <div class="boosted-post-meta">
                                                <span class="boosted-post-location">
                                                    <i class="fi fi-rr-marker"></i>
                                                    <span class="location-text">{{ $post->location ?? '—' }}</span>
                                                </span>
                                            </div>
                                            <div class="boost-progress-section">
                                                <div class="boost-progress-header">
                                                    <span class="boost-progress-label">Boost Progress</span>
                                                    <span class="boost-progress-percentage">{{ $post->percentage ?? 0 }}%</span>
                                                </div>
                                                <div class="boost-progress-bar-container">
                                                    <div class="boost-progress-bar" style="width: {{ $post->percentage ?? 0 }}%"></div>
                                                </div>
                                                <div class="boost-end-date">
                                                    Boost will end in: <strong class="end-date-text">{{ isset($post->ends_at) ? (\Carbon\Carbon::parse($post->ends_at))->diffForHumans() : 'N/A' }}</strong>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Boost Success Modal -->
<div id="boostSuccessModal" class="boost-success-modal">
    <div class="modal-overlay" id="boostSuccessOverlay"></div>
    <div class="boost-success-container">
        <div class="boost-success-icon">
            <i class="fi fi-rr-rocket"></i>
        </div>
        <h3 class="boost-success-title">Boost Activated!</h3>
        <p class="boost-success-message">
            Your post is now being boosted and will reach <span id="boostReachValue">4,000+</span> people
        </p>
        <p class="boost-success-duration">
            Boost duration: <strong>7 days</strong>
        </p>
        <button class="boost-success-btn" id="closeBoostSuccessBtn">
            View Dashboard
        </button>
    </div>
</div>

<!-- Post Card Template -->
<template id="postCardTemplate">
    <div class="post-card" data-post-id="">
        <div class="post-card-image">
            <img src="" alt="Post image" />
        </div>
        <div class="post-card-content">
            <div class="post-card-header">
                <h4 class="post-card-title"></h4>
                <span class="post-chosen-badge hidden">Post Chosen</span>
            </div>
            <p class="post-card-description"></p>
            <div class="post-card-footer">
                <div class="post-card-meta">
                    <span class="post-card-location">
                        <i class="fi fi-rr-marker"></i>
                        <span class="location-text"></span>
                    </span>
                    <span class="post-card-date">
                        <i class="fi fi-rr-calendar"></i>
                        <span class="date-text"></span>
                    </span>
                </div>
            </div>
        </div>
        <div class="post-card-select">
            <input type="radio" name="selectedPost" class="post-radio" />
        </div>
    </div>
</template>

<!-- Boosted Post Card Template -->
<template id="boostedPostTemplate">
    <div class="boosted-post-card" data-post-id="">
        <div class="boosted-post-image">
            <img src="" alt="Post image" />
        </div>
        <div class="boosted-post-content">
            <h4 class="boosted-post-title"></h4>
            <p class="boosted-post-description"></p>
            <div class="boosted-post-meta">
                <span class="boosted-post-location">
                    <i class="fi fi-rr-marker"></i>
                    <span class="location-text"></span>
                </span>
            </div>
            <div class="boost-end-date">
                Boost will end in: <strong class="end-date-text"></strong>
            </div>
        </div>
    </div>
</template>
