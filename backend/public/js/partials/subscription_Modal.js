// Subscription Modal JavaScript
document.addEventListener('DOMContentLoaded', function() {
    // Subscription Plan Data
    const subscriptionPlans = {
        gold: {
            name: 'Gold Tier',
            price: '₱ 1,999',
            benefits: [
                'Unlock AI driven analytics',
                'Unlimited Bids',
                'Boost Bids for 1 month'
            ]
        },
        silver: {
            name: 'Silver Tier',
            price: '₱ 1,499',
            benefits: [
                '7 Bids',
                'Boost Bids for 1 month'
            ]
        },
        bronze: {
            name: 'Bronze Tier',
            price: '₱ 999',
            benefits: [
                '4 Bids per month'
            ]
        }
    };

    // Current subscription state (simulated)
    let currentSubscription = {
        plan: 'gold',
        expiryDate: '10/23/2026',
        isActive: true
    };

    // Modal elements
    const subscriptionModal = document.getElementById('subscriptionModal');
    const subscriptionLink = document.getElementById('subscriptionLink');
    const closeSubscriptionModalBtn = document.getElementById('closeSubscriptionModalBtn');
    const subscriptionModalOverlay = document.getElementById('subscriptionModalOverlay');

    // Tab elements
    const overviewTab = document.getElementById('overviewTab');
    const plansTab = document.getElementById('plansTab');
    const overviewContent = document.getElementById('overviewContent');
    const plansContent = document.getElementById('plansContent');

    // Plans tab elements
    const planCards = document.querySelectorAll('.plan-card');
    const planSubscribeBtns = document.querySelectorAll('.plan-subscribe-btn');
    const mainSubscribeBtn = document.getElementById('mainSubscribeBtn');
    const selectedPlanBenefits = document.getElementById('selectedPlanBenefits');
    const alreadySubscribedMsg = document.getElementById('alreadySubscribedMsg');

    // Overview tab elements
    const currentPlanName = document.getElementById('currentPlanName');
    const currentPlanExpiry = document.getElementById('currentPlanExpiry');
    const currentBenefitsList = document.getElementById('currentBenefitsList');
    const cancelSubscriptionBtn = document.getElementById('cancelSubscriptionBtn');

    // Confirmation modals
    const subscriptionConfirmModal = document.getElementById('subscriptionConfirmModal');
    const subscriptionConfirmOverlay = document.getElementById('subscriptionConfirmOverlay');
    const closeSubscriptionConfirmBtn = document.getElementById('closeSubscriptionConfirmBtn');
    const confirmedPlanName = document.getElementById('confirmedPlanName');

    // Cancel subscription modal
    const cancelSubscriptionConfirmModal = document.getElementById('cancelSubscriptionConfirmModal');
    const cancelSubscriptionConfirmOverlay = document.getElementById('cancelSubscriptionConfirmOverlay');
    const cancelSubscriptionNoBtn = document.getElementById('cancelSubscriptionNoBtn');
    const cancelSubscriptionYesBtn = document.getElementById('cancelSubscriptionYesBtn');

    // Account settings modal
    const accountSettingsModal = document.getElementById('accountSettingsModal');

    let selectedPlan = 'gold';

    // Open subscription modal
    if (subscriptionLink) {
        subscriptionLink.addEventListener('click', function(e) {
            e.preventDefault();
            openSubscriptionModal();
            
            // Close account settings modal if open
            if (accountSettingsModal) {
                accountSettingsModal.classList.remove('active');
            }
        });
    }

    // Close subscription modal
    function closeSubscriptionModal() {
        if (subscriptionModal) {
            subscriptionModal.classList.remove('active');
        }
    }

    if (closeSubscriptionModalBtn) {
        closeSubscriptionModalBtn.addEventListener('click', closeSubscriptionModal);
    }

    if (subscriptionModalOverlay) {
        subscriptionModalOverlay.addEventListener('click', closeSubscriptionModal);
    }

    // Escape key to close
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape' && subscriptionModal && subscriptionModal.classList.contains('active')) {
            closeSubscriptionModal();
        }
    });

    // Open subscription modal
    function openSubscriptionModal() {
        if (subscriptionModal) {
            subscriptionModal.classList.add('active');
            updateOverviewTab();
            updatePlansTab();
        }
    }

    // Tab switching
    function switchTab(tabName) {
        // Update tab buttons
        document.querySelectorAll('.subscription-tab').forEach(tab => {
            tab.classList.remove('active');
        });

        // Update tab content
        document.querySelectorAll('.subscription-tab-content').forEach(content => {
            content.classList.remove('active');
        });

        if (tabName === 'overview') {
            overviewTab.classList.add('active');
            overviewContent.classList.add('active');
        } else if (tabName === 'plans') {
            plansTab.classList.add('active');
            plansContent.classList.add('active');
        }
    }

    if (overviewTab) {
        overviewTab.addEventListener('click', () => switchTab('overview'));
    }

    if (plansTab) {
        plansTab.addEventListener('click', () => switchTab('plans'));
    }

    // Update Overview Tab
    function updateOverviewTab() {
        if (currentSubscription.isActive) {
            const planData = subscriptionPlans[currentSubscription.plan];
            
            // Update current plan info
            if (currentPlanName) {
                currentPlanName.textContent = planData.name;
            }
            if (currentPlanExpiry) {
                currentPlanExpiry.textContent = currentSubscription.expiryDate;
            }

            // Update benefits list
            if (currentBenefitsList) {
                currentBenefitsList.innerHTML = '';
                planData.benefits.forEach(benefit => {
                    const li = document.createElement('li');
                    li.className = 'benefit-item';
                    li.innerHTML = `
                        <i class="fi fi-rr-check-circle"></i>
                        <span>${benefit}</span>
                    `;
                    currentBenefitsList.appendChild(li);
                });
            }

            // Update other plans (show plans that are not current)
            const otherPlansHtml = Object.keys(subscriptionPlans)
                .filter(key => key !== currentSubscription.plan)
                .map(key => {
                    const plan = subscriptionPlans[key];
                    const className = key === 'silver' ? 'silver-plan' : 'bronze-plan';
                    return `
                        <div class="other-plan-card ${className}">
                            <div class="other-plan-info">
                                <span class="other-plan-name">${plan.name.toUpperCase()}</span>
                            </div>
                            <span class="other-plan-price">${plan.price}</span>
                        </div>
                    `;
                }).join('');

            const otherPlansList = document.querySelector('.other-plans-list');
            if (otherPlansList) {
                otherPlansList.innerHTML = otherPlansHtml;
            }
        }
    }

    // Update Plans Tab
    function updatePlansTab() {
        // Update plan card states
        planCards.forEach(card => {
            const planType = card.dataset.plan;
            if (currentSubscription.isActive && planType === currentSubscription.plan) {
                card.classList.add('already-subscribed');
            } else {
                card.classList.remove('already-subscribed');
            }

            // Remove selected class initially
            card.classList.remove('selected');
        });

        // Select the current or first available plan
        if (currentSubscription.isActive) {
            selectedPlan = currentSubscription.plan;
            const currentCard = document.querySelector(`.plan-card[data-plan="${currentSubscription.plan}"]`);
            if (currentCard) {
                currentCard.classList.add('selected');
            }
        } else {
            selectedPlan = 'gold';
            const goldCard = document.querySelector('.plan-card[data-plan="gold"]');
            if (goldCard) {
                goldCard.classList.add('selected');
            }
        }

        updatePlanBenefits(selectedPlan);
        updateSubscribeButton();
    }

    // Plan card click
    planCards.forEach(card => {
        card.addEventListener('click', function() {
            const planType = this.dataset.plan;
            
            // Don't allow selecting already subscribed plan
            if (currentSubscription.isActive && planType === currentSubscription.plan) {
                return;
            }

            selectedPlan = planType;

            // Update selected state
            planCards.forEach(c => c.classList.remove('selected'));
            this.classList.add('selected');

            updatePlanBenefits(planType);
            updateSubscribeButton();
        });
    });

    // Plan subscribe buttons
    planSubscribeBtns.forEach(btn => {
        btn.addEventListener('click', function(e) {
            e.stopPropagation();
            const planType = this.dataset.plan;
            
            // Don't allow subscribing to current plan
            if (currentSubscription.isActive && planType === currentSubscription.plan) {
                return;
            }

            selectedPlan = planType;
            subscribeToPlan(planType);
        });
    });

    // Main subscribe button
    if (mainSubscribeBtn) {
        mainSubscribeBtn.addEventListener('click', function() {
            subscribeToPlan(selectedPlan);
        });
    }

    // Update plan benefits display
    function updatePlanBenefits(planType) {
        const planData = subscriptionPlans[planType];
        if (selectedPlanBenefits && planData) {
            selectedPlanBenefits.innerHTML = '';
            planData.benefits.forEach(benefit => {
                const li = document.createElement('li');
                li.className = 'plans-benefit-item';
                li.innerHTML = `
                    <i class="fi fi-rr-check-circle"></i>
                    <span>${benefit}</span>
                `;
                selectedPlanBenefits.appendChild(li);
            });
        }
    }

    // Update subscribe button
    function updateSubscribeButton() {
        if (currentSubscription.isActive && selectedPlan === currentSubscription.plan) {
            if (mainSubscribeBtn) {
                mainSubscribeBtn.style.display = 'none';
            }
            if (alreadySubscribedMsg) {
                alreadySubscribedMsg.classList.remove('hidden');
            }
        } else {
            if (mainSubscribeBtn) {
                mainSubscribeBtn.style.display = 'block';
            }
            if (alreadySubscribedMsg) {
                alreadySubscribedMsg.classList.add('hidden');
            }
        }
    }

    // Subscribe to plan
    function subscribeToPlan(planType) {
        const planData = subscriptionPlans[planType];
        
        // Show loading (simulated)
        console.log(`Subscribing to ${planData.name}...`);

        // Simulate API call
        setTimeout(() => {
            // Update current subscription
            currentSubscription = {
                plan: planType,
                expiryDate: getExpiryDate(),
                isActive: true
            };

            // Show confirmation modal
            if (confirmedPlanName) {
                confirmedPlanName.textContent = planData.name;
            }
            if (subscriptionConfirmModal) {
                subscriptionConfirmModal.classList.add('active');
            }

            // Close subscription modal
            closeSubscriptionModal();

            console.log(`Successfully subscribed to ${planData.name}`);
        }, 1000);
    }

    // Get expiry date (1 month from now)
    function getExpiryDate() {
        const date = new Date();
        date.setMonth(date.getMonth() + 1);
        const month = String(date.getMonth() + 1).padStart(2, '0');
        const day = String(date.getDate()).padStart(2, '0');
        const year = date.getFullYear();
        return `${month}/${day}/${year}`;
    }

    // Close subscription confirmation modal
    function closeSubscriptionConfirmModal() {
        if (subscriptionConfirmModal) {
            subscriptionConfirmModal.classList.remove('active');
        }
    }

    if (closeSubscriptionConfirmBtn) {
        closeSubscriptionConfirmBtn.addEventListener('click', closeSubscriptionConfirmModal);
    }

    if (subscriptionConfirmOverlay) {
        subscriptionConfirmOverlay.addEventListener('click', closeSubscriptionConfirmModal);
    }

    // Cancel subscription
    if (cancelSubscriptionBtn) {
        cancelSubscriptionBtn.addEventListener('click', function() {
            if (cancelSubscriptionConfirmModal) {
                cancelSubscriptionConfirmModal.classList.add('active');
            }
        });
    }

    // Cancel subscription - No
    if (cancelSubscriptionNoBtn) {
        cancelSubscriptionNoBtn.addEventListener('click', function() {
            if (cancelSubscriptionConfirmModal) {
                cancelSubscriptionConfirmModal.classList.remove('active');
            }
        });
    }

    // Cancel subscription - Yes
    if (cancelSubscriptionYesBtn) {
        cancelSubscriptionYesBtn.addEventListener('click', function() {
            console.log('Canceling subscription...');

            // Simulate API call
            setTimeout(() => {
                currentSubscription.isActive = false;
                
                // Close modals
                if (cancelSubscriptionConfirmModal) {
                    cancelSubscriptionConfirmModal.classList.remove('active');
                }
                closeSubscriptionModal();

                // Switch to plans tab for next opening
                switchTab('plans');

                console.log('Subscription cancelled successfully');
            }, 1000);
        });
    }

    // Close cancel confirmation modal on overlay click
    if (cancelSubscriptionConfirmOverlay) {
        cancelSubscriptionConfirmOverlay.addEventListener('click', function() {
            if (cancelSubscriptionConfirmModal) {
                cancelSubscriptionConfirmModal.classList.remove('active');
            }
        });
    }

    // Escape key for confirmation modals
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            if (subscriptionConfirmModal && subscriptionConfirmModal.classList.contains('active')) {
                closeSubscriptionConfirmModal();
            }
            if (cancelSubscriptionConfirmModal && cancelSubscriptionConfirmModal.classList.contains('active')) {
                cancelSubscriptionConfirmModal.classList.remove('active');
            }
        }
    });
});
