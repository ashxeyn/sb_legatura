<!-- Subscription Modal -->
<div id="subscriptionModal" class="subscription-modal">
    <div class="modal-overlay" id="subscriptionModalOverlay"></div>
    <div class="subscription-modal-container">
        <!-- Modal Header -->
        <div class="subscription-modal-header">
            <h2 class="subscription-modal-title">
                <i class="fi fi-rr-crown"></i>
                Subscription Plans
            </h2>
            <button class="subscription-close-btn" id="closeSubscriptionModalBtn" aria-label="Close modal">
                <i class="fi fi-rr-cross"></i>
            </button>
        </div>

        <!-- Tab Navigation -->
        <div class="subscription-tabs">
            <button class="subscription-tab" data-tab="plans" id="plansTab">
                Plans
            </button>
            <button class="subscription-tab active" data-tab="overview" id="overviewTab">
                Overview
            </button>
        </div>

        <!-- Modal Body -->
        <div class="subscription-modal-body">
            <!-- Overview Tab Content -->
            <div class="subscription-tab-content active" id="overviewContent">
                <div class="overview-container">
                    <!-- Current Subscription -->
                    @php
                        // $subscription is now passed from the controller
                        $sub = $subscription ?? null; 
                        // If it's an array, cast to object for consistent property access
                        if (is_array($sub)) {
                            $sub = (object) $sub;
                        }
                    @endphp

                    @if(!$sub)
                        <div class="current-subscription no-subscription" id="noSubscriptionMessage">No subscription</div>
                    @else
                        <div class="current-subscription">
                            <div class="current-subscription-badge">
                                <i class="fi fi-rr-crown"></i>
                            </div>
                            <div class="current-subscription-info">
                                <p class="current-subscription-label">You are currently Subscribed to</p>
                                <h3 class="current-subscription-plan" id="currentPlanName">{{ $sub->name }}</h3>
                                <p class="current-subscription-expires">Subscription will end in:</p>
                                <p class="current-subscription-date" id="currentPlanExpiry">{{ isset($sub->expires_at) ? \Carbon\Carbon::parse($sub->expires_at)->format('m/d/Y') : 'N/A' }}</p>
                            </div>
                        </div>

                        <!-- Benefits Being Enjoyed -->
                        <div class="benefits-section">
                            <h4 class="benefits-title">Benefits being enjoyed:</h4>
                            <ul class="benefits-list" id="currentBenefitsList">
                                @php $benefits = $sub->benefits ?? [];
                                @endphp
                                @if(empty($benefits))
                                    <li class="benefit-item">No benefits listed</li>
                                @else
                                    @foreach($benefits as $benefit)
                                        <li class="benefit-item">
                                            <i class="fi fi-rr-check-circle"></i>
                                            <span>{{ $benefit }}</span>
                                        </li>
                                    @endforeach
                                @endif
                            </ul>
                        </div>
                    @endif

                    <!-- Other Plans -->
                    <div class="other-plans-section">
                        <h4 class="other-plans-title">Other plans:</h4>
                        <div class="other-plans-list">
                            @foreach($plans ?? [] as $plan)
                                @if(!empty($sub) && strtolower($sub->plan_key) == strtolower($plan->plan_key)) @continue @endif
                                <div class="other-plan-card {{ strtolower($plan->plan_key) }}-plan" 
                                     data-plan="{{ strtolower($plan->plan_key) }}"
                                     data-price="₱ {{ number_format($plan->amount / 100, 0) }}"
                                     data-name="{{ $plan->name }}"
                                     data-benefits='@json($plan->benefits)'>
                                    <div class="other-plan-info">
                                        <span class="other-plan-name">{{ strtoupper($plan->name) }}</span>
                                    </div>
                                    <span class="other-plan-price">₱ {{ number_format($plan->amount / 100, 0) }}</span>
                                </div>
                            @endforeach
                        </div>
                    </div>

                    <!-- Cancel Subscription Button (only when subscribed) -->
                    @if(!empty($sub))
                        <button class="cancel-subscription-btn" id="cancelSubscriptionBtn">
                            Cancel Subscription
                        </button>
                    @endif
                </div>
            </div>

            <!-- Plans Tab Content -->
            <div class="subscription-tab-content" id="plansContent">
                <div class="plans-container">
                    <div class="plans-header">
                        <h3 class="plans-title">Choose your subscription plan</h3>
                        <p class="plans-subtitle">And get a 7-day free trial</p>
                    </div>

                    <div class="plans-list">
                        @foreach($plans ?? [] as $plan)
                            <div class="plan-card {{ strtolower($plan->plan_key) }}-tier {{ (!empty($sub) && strtolower($sub->plan_key) == strtolower($plan->plan_key)) ? 'already-subscribed' : '' }}" 
                                 data-plan="{{ strtolower($plan->plan_key) }}"
                                 data-name="{{ $plan->name }}"
                                 data-price="₱ {{ number_format($plan->amount / 100, 0) }}"
                                 data-benefits='@json($plan->benefits)'>
                                <div class="plan-badge">
                                    <i class="fi fi-rr-crown"></i>
                                </div>
                                <div class="plan-header">
                                    <span class="plan-name">{{ strtoupper($plan->name) }}</span>
                                    <span class="plan-price">₱ {{ number_format($plan->amount / 100, 0) }}</span>
                                </div>
                            </div>
                        @endforeach
                    </div>

                    <!-- Benefits Section -->
                    <div class="plans-benefits">
                        <h4 class="plans-benefits-title">You'll get:</h4>
                        <ul class="plans-benefits-list" id="selectedPlanBenefits">
                            <li class="plans-benefit-item">
                                <i class="fi fi-rr-check-circle"></i>
                                <span>Unlock AI driven analytics</span>
                            </li>
                            <li class="plans-benefit-item">
                                <i class="fi fi-rr-check-circle"></i>
                                <span>Unlimited Bids</span>
                            </li>
                            <li class="plans-benefit-item">
                                <i class="fi fi-rr-check-circle"></i>
                                <span>Boost Bids for 1 month</span>
                            </li>
                        </ul>
                    </div>

                    <!-- Subscribe Button -->
                    <button class="main-subscribe-btn" id="mainSubscribeBtn">
                        Subscribe
                    </button>

                    <!-- Already Subscribed Message -->
                    <div class="already-subscribed-msg hidden" id="alreadySubscribedMsg">
                        Already subscribed to this plan
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Subscription Confirmation Modal -->
<div id="subscriptionConfirmModal" class="subscription-confirm-modal">
    <div class="modal-overlay" id="subscriptionConfirmOverlay"></div>
    <div class="subscription-confirm-container">
        <div class="subscription-confirm-icon">
            <i class="fi fi-rr-check-circle"></i>
        </div>
        <h3 class="subscription-confirm-title">Subscription Successful!</h3>
        <p class="subscription-confirm-message">
            You have successfully subscribed to <span id="confirmedPlanName">Gold Tier</span>
        </p>
        <p class="subscription-confirm-trial">
            Your 7-day free trial starts now
        </p>
        <button class="subscription-confirm-btn" id="closeSubscriptionConfirmBtn">
            Got it
        </button>
    </div>
</div>

<!-- Cancel Subscription Confirmation Modal -->
<div id="cancelSubscriptionConfirmModal" class="cancel-subscription-modal">
    <div class="modal-overlay" id="cancelSubscriptionConfirmOverlay"></div>
    <div class="cancel-subscription-container">
        <div class="cancel-subscription-icon">
            <i class="fi fi-rr-exclamation"></i>
        </div>
        <h3 class="cancel-subscription-title">Cancel Subscription?</h3>
        <p class="cancel-subscription-message">
            Are you sure you want to cancel your subscription?
        </p>
        <p class="cancel-subscription-submessage">
            You will lose access to all premium features at the end of your current billing period.
        </p>
        <div class="cancel-subscription-actions">
            <button class="cancel-subscription-no-btn" id="cancelSubscriptionNoBtn">
                No, Keep It
            </button>
            <button class="cancel-subscription-yes-btn" id="cancelSubscriptionYesBtn">
                Yes, Cancel
            </button>
        </div>
    </div>
</div>
