// Subscription Modal JavaScript
document.addEventListener('DOMContentLoaded', function () {
    // Subscription Plan Data (will be populated from server)
    let subscriptionPlans = {};

    // Current subscription state (will be loaded from server)
    let currentSubscription = null;
    let selectedPlan = 'gold';

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

    // Overview Tab elements
    const currentPlanName = document.getElementById('currentPlanName');
    const currentPlanExpiry = document.getElementById('currentPlanExpiry');
    const currentBenefitsList = document.getElementById('currentBenefitsList');
    const cancelSubscriptionBtn = document.getElementById('cancelSubscriptionBtn');

    // Plans list elements
    const planCards = document.querySelectorAll('.plan-card');
    const selectedPlanBenefits = document.getElementById('selectedPlanBenefits');
    const mainSubscribeBtn = document.getElementById('mainSubscribeBtn');
    const alreadySubscribedMsg = document.getElementById('alreadySubscribedMsg');

    // Confirmation modal elements
    const subscriptionConfirmModal = document.getElementById('subscriptionConfirmModal');
    const subscriptionConfirmOverlay = document.getElementById('subscriptionConfirmOverlay');
    const confirmedPlanName = document.getElementById('confirmedPlanName');
    const closeSubscriptionConfirmBtn = document.getElementById('closeSubscriptionConfirmBtn');

    // Cancel confirmation elements
    const cancelSubscriptionConfirmModal = document.getElementById('cancelSubscriptionConfirmModal');
    const cancelSubscriptionConfirmOverlay = document.getElementById('cancelSubscriptionConfirmOverlay');
    const cancelSubscriptionNoBtn = document.getElementById('cancelSubscriptionNoBtn');
    const cancelSubscriptionYesBtn = document.getElementById('cancelSubscriptionYesBtn');

    // Open/Close logic
    function openSubscriptionModal() {
        console.log('Attempting to open subscription modal...');
        if (subscriptionModal) {
            subscriptionModal.classList.add('active');

            // Load state when opening
            loadSubscriptionState().then(() => {
                updateOverviewTab();
                updatePlansTab();
                attachOtherPlanListeners();
            });

            // Default to overview if subscribed, otherwise plans
            if (currentSubscription && currentSubscription.isActive) {
                switchTab('overview');
            } else {
                switchTab('plans');
            }
        } else {
            console.error('subscriptionModal element not found!');
        }
    }

    function closeSubscriptionModal() {
        if (subscriptionModal) {
            subscriptionModal.classList.remove('active');
        }
    }

    // Use event delegation for the opening link since it might be in another modal
    document.addEventListener('click', (e) => {
        const target = e.target.closest('#subscriptionLink');
        if (target) {
            console.log('Subscription link clicked');
            e.preventDefault();
            openSubscriptionModal();
            // Close sidebar menu if on mobile/sidebar logic exists elsewhere
            const userMenuDropdown = document.getElementById('userMenuDropdown');
            if (userMenuDropdown) userMenuDropdown.classList.add('hidden');
        }
    });

    if (closeSubscriptionModalBtn) {
        closeSubscriptionModalBtn.addEventListener('click', closeSubscriptionModal);
    }

    if (subscriptionModalOverlay) {
        subscriptionModalOverlay.addEventListener('click', closeSubscriptionModal);
    }

    // Fetch subscription state (best-effort)
    function loadSubscriptionState() {
        return fetch('/subs/modal-data').then(r => r.ok ? r.json() : Promise.reject()).then(data => {
            // Populate plans
            if (data.plans && data.plans.length > 0) {
                subscriptionPlans = {};
                data.plans.forEach(plan => {
                    subscriptionPlans[plan.plan_key.toLowerCase()] = {
                        name: plan.name,
                        price: `₱ ${parseFloat(plan.amount / 100).toLocaleString(undefined, { minimumFractionDigits: 0 })}`,
                        benefits: Array.isArray(plan.benefits) ? plan.benefits : (plan.benefits ? JSON.parse(plan.benefits) : [])
                    };
                });
            }

            const sub = data.subscription || null;
            if (!sub) {
                currentSubscription = null;
                return;
            }

            // Normalize subscription object
            currentSubscription = {
                plan: sub.plan_key || sub.plan || (sub.name ? sub.name.toLowerCase().split(' ')[0] : 'gold'),
                expiryDate: sub.expires_at || sub.expiry_date || sub.expires_at_formatted || null,
                isActive: true,
                benefits: sub.benefits || (sub.meta && sub.meta.benefits) || []
            };
            return;
        }).catch((error) => {
            console.error('Error loading subscription state:', error);
            currentSubscription = null;
        });
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
        if (!currentSubscription || !currentSubscription.isActive) {
            if (currentBenefitsList) currentBenefitsList.innerHTML = '<li>No subscription</li>';
            if (currentPlanName) currentPlanName.textContent = 'No subscription';
            if (currentPlanExpiry) currentPlanExpiry.textContent = '';
            // Hide cancel button when no subscription active
            if (cancelSubscriptionBtn) {
                cancelSubscriptionBtn.style.display = 'none';
            }

            // Show other plans by default
            const otherPlansHtml = Object.keys(subscriptionPlans).map(key => {
                const plan = subscriptionPlans[key];
                return `
                    <div class="other-plan-card ${key}-plan" data-plan="${key}">
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
                attachOtherPlanListeners();
            }

            return;
        }

        const planKey = currentSubscription.plan || 'gold';
        const planData = subscriptionPlans[planKey] || subscriptionPlans['gold'];

        // Update current plan info
        if (currentPlanName) {
            currentPlanName.textContent = planData.name;
        }
        if (currentPlanExpiry) {
            currentPlanExpiry.textContent = currentSubscription.expiryDate || '';
        }

        // Show cancel button when subscription active
        if (cancelSubscriptionBtn) {
            cancelSubscriptionBtn.style.display = 'block';
        }

        // Update benefits list
        if (currentBenefitsList) {
            currentBenefitsList.innerHTML = '';
            const benefits = currentSubscription.benefits && currentSubscription.benefits.length ? currentSubscription.benefits : planData.benefits;
            benefits.forEach(benefit => {
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
            .filter(key => key !== planKey)
            .map(key => {
                const plan = subscriptionPlans[key];
                return `
                    <div class="other-plan-card ${key}-plan" data-plan="${key}">
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
            attachOtherPlanListeners();
        }
    }

    // Attach listeners to "Other Plans" cards
    function attachOtherPlanListeners() {
        document.querySelectorAll('.other-plan-card').forEach(card => {
            card.addEventListener('click', function () {
                const planType = this.dataset.plan;
                switchTab('plans');
                // Select this plan in the plans tab
                selectedPlan = planType;
                const targetCard = document.querySelector(`.plan-card[data-plan="${planType}"]`);
                if (targetCard) {
                    planCards.forEach(c => c.classList.remove('selected'));
                    targetCard.classList.add('selected');
                    updatePlanBenefits(planType);
                    updateSubscribeButton();
                }
            });
        });
    }

    // Update Plans Tab
    function updatePlansTab() {
        // Update plan card states
        planCards.forEach(card => {
            const planType = card.dataset.plan;
            card.classList.remove('already-subscribed');
            // Remove selected class initially
            card.classList.remove('selected');
        });

        // Select the current or first available plan
        if (currentSubscription && currentSubscription.isActive) {
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
        card.addEventListener('click', function () {
            const planType = this.dataset.plan;
            selectedPlan = planType;

            // Update selected state
            planCards.forEach(c => c.classList.remove('selected'));
            this.classList.add('selected');

            updatePlanBenefits(planType);
            updateSubscribeButton();
        });
    });


    // Main subscribe button
    if (mainSubscribeBtn) {
        mainSubscribeBtn.addEventListener('click', function () {
            if (currentSubscription && currentSubscription.isActive && selectedPlan === currentSubscription.plan) {
                // Trigger cancellation confirmation
                if (cancelSubscriptionConfirmModal) {
                    cancelSubscriptionConfirmModal.classList.add('active');
                }
            } else {
                subscribeToPlan(selectedPlan);
            }
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
        if (mainSubscribeBtn) {
            mainSubscribeBtn.style.display = 'block';
            if (currentSubscription && currentSubscription.isActive && selectedPlan === currentSubscription.plan) {
                mainSubscribeBtn.innerHTML = 'Cancel Subscription';
                mainSubscribeBtn.classList.add('cancel-mode'); // Optional: for styling
            } else {
                mainSubscribeBtn.innerHTML = 'Subscribe Now';
                mainSubscribeBtn.classList.remove('cancel-mode');
            }
        }

        // Hide "already subscribed" message since we allow selection now
        if (alreadySubscribedMsg) {
            alreadySubscribedMsg.classList.add('hidden');
        }
    }

    // Subscribe to plan
    async function subscribeToPlan(planType) {
        const planData = subscriptionPlans[planType];

        console.log(`Subscribing to ${planData.name}...`);

        // Show loading state if button exists
        if (mainSubscribeBtn) {
            const originalText = mainSubscribeBtn.innerHTML;
            mainSubscribeBtn.innerHTML = '<i class="fi fi-rr-spinner fi-spin"></i> Processing...';
            mainSubscribeBtn.disabled = true;
        }

        try {
            const response = await fetch('/subscribe/checkout', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify({
                    plan_tier: planType
                })
            });

            const data = await response.json();

            if (data.success) {
                // Redirect to PayMongo Checkout
                window.location.href = data.checkout_url;
            } else {
                alert('Subscription failed: ' + (data.message || 'Unknown error'));
                if (mainSubscribeBtn) {
                    mainSubscribeBtn.innerHTML = 'Subscribe Now'; // Reset text
                    mainSubscribeBtn.disabled = false;
                }
            }
        } catch (error) {
            console.error('Error subscribing:', error);
            alert('An error occurred. Please try again.');
            if (mainSubscribeBtn) {
                mainSubscribeBtn.innerHTML = 'Subscribe Now'; // Reset text
                mainSubscribeBtn.disabled = false;
            }
        }
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
        cancelSubscriptionBtn.addEventListener('click', function () {
            if (cancelSubscriptionConfirmModal) {
                cancelSubscriptionConfirmModal.classList.add('active');
            }
        });
    }

    // Cancel subscription - No
    if (cancelSubscriptionNoBtn) {
        cancelSubscriptionNoBtn.addEventListener('click', function () {
            if (cancelSubscriptionConfirmModal) {
                cancelSubscriptionConfirmModal.classList.remove('active');
            }
        });
    }

    // Toast Notification Function (Matches Navbar style)
    function showToast(message, type = 'success', duration = 3500) {
        try {
            const toast = document.createElement('div');
            toast.className = 'site-toast site-toast-' + type;
            toast.style.position = 'fixed';
            toast.style.right = '20px';
            toast.style.top = '20px';
            toast.style.zIndex = 11000;
            toast.style.padding = '12px 16px';
            toast.style.borderRadius = '8px';
            toast.style.boxShadow = '0 2px 10px rgba(0,0,0,0.12)';
            toast.style.background = type === 'success' ? '#16a34a' : '#374151';
            toast.style.color = '#fff';
            toast.textContent = message;
            toast.style.opacity = '0';
            toast.style.transform = 'translateY(-8px)';
            toast.style.transition = 'opacity 0.3s ease, transform 0.3s ease';

            document.body.appendChild(toast);

            requestAnimationFrame(() => {
                toast.style.opacity = '1';
                toast.style.transform = 'translateY(0)';
            });

            setTimeout(() => {
                toast.style.opacity = '0';
                toast.style.transform = 'translateY(-8px)';
                setTimeout(() => toast.remove(), 350);
            }, duration);
        } catch (e) {
            console.debug('showToast failed', e);
        }
    }

    // Cancel subscription - Yes
    if (cancelSubscriptionYesBtn) {
        cancelSubscriptionYesBtn.addEventListener('click', function () {
            console.log('Canceling subscription...');

            // Disable button
            const originalText = cancelSubscriptionYesBtn.innerText;
            cancelSubscriptionYesBtn.innerText = 'Processing...';
            cancelSubscriptionYesBtn.disabled = true;

            fetch('/subscribe/cancel', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                }
            })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        currentSubscription.isActive = false;

                        // Close modals
                        if (cancelSubscriptionConfirmModal) {
                            cancelSubscriptionConfirmModal.classList.remove('active');
                        }
                        closeSubscriptionModal();

                        // Switch to plans tab for next opening
                        switchTab('plans');

                        showToast('Subscription cancelled successfully.', 'success');

                        // Delay reload to allow toast to be seen
                        setTimeout(() => {
                            window.location.reload();
                        }, 1500);

                    } else {
                        showToast('Failed to cancel: ' + (data.message || 'Unknown error'), 'error');
                    }
                })
                .catch(error => {
                    console.error('Error canceling:', error);
                    showToast('An error occurred. Please try again.', 'error');
                })
                .finally(() => {
                    cancelSubscriptionYesBtn.innerText = originalText;
                    cancelSubscriptionYesBtn.disabled = false;
                });
        });
    }

    // Close cancel confirmation modal on overlay click
    if (cancelSubscriptionConfirmOverlay) {
        cancelSubscriptionConfirmOverlay.addEventListener('click', function () {
            if (cancelSubscriptionConfirmModal) {
                cancelSubscriptionConfirmModal.classList.remove('active');
            }
        });
    }

    // Escape key for confirmation modals
    document.addEventListener('keydown', function (e) {
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
