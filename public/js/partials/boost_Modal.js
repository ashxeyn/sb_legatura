// Boost Modal JavaScript
document.addEventListener('DOMContentLoaded', function () {
    // State management
    let selectedPlan = null;
    let selectedPost = null;
    let currentSection = 'plan-selection'; // 'plan-selection', 'post-selection', 'boost-summary'
    let ownerPosts = [];
    let boostedPosts = [];

    // Modal elements
    const boostModal = document.getElementById('boostModal');
    const boostLink = document.getElementById('boostLink');
    const closeBoostModalBtn = document.getElementById('closeBoostModalBtn');
    const boostModalOverlay = document.getElementById('boostModalOverlay');

    // Tab elements
    const boostsTab = document.getElementById('boostsTab');
    const dashboardTab = document.getElementById('dashboardTab');
    const boostsContent = document.getElementById('boostsContent');
    const dashboardContent = document.getElementById('dashboardContent');

    // Section elements
    const planSelectionSection = document.getElementById('planSelectionSection');
    const postSelectionSection = document.getElementById('postSelectionSection');
    const boostSummarySection = document.getElementById('boostSummarySection');

    // Plan selection elements
    const planCards = document.querySelectorAll('.boost-plan-card');
    const choosePostBtn = document.getElementById('choosePostBtn');

    // Post selection elements
    const backToPlansBtn = document.getElementById('backToPlansBtn');
    const postSearchInput = document.getElementById('postSearchInput');
    const postsListContainer = document.getElementById('postsListContainer');
    const confirmBoostBtn = document.getElementById('confirmBoostBtn');

    // Boost summary elements
    const selectedPostPreview = document.getElementById('selectedPostPreview');
    const selectedPlanDetails = document.getElementById('selectedPlanDetails');
    const changePostBtn = document.getElementById('changePostBtn');
    const boostNowBtn = document.getElementById('boostNowBtn');

    // Success modal elements
    const boostSuccessModal = document.getElementById('boostSuccessModal');
    const boostSuccessOverlay = document.getElementById('boostSuccessOverlay');
    const closeBoostSuccessBtn = document.getElementById('closeBoostSuccessBtn');
    const boostReachValue = document.getElementById('boostReachValue');

    // Dashboard elements
    const boostedPostsList = document.getElementById('boostedPostsList');

    // Account settings modal
    const accountSettingsModal = document.getElementById('accountSettingsModal');

    // Load data
    /*
    function loadProjectsData() {
        // Projects are now loaded via PHP in the blade file
    }
    
    async function loadBoostedProjectsData() {
       // Boosted projects are now loaded via PHP
    }
    */

    // Open boost modal
    if (boostLink) {
        boostLink.addEventListener('click', function (e) {
            e.preventDefault();
            openBoostModal();

            // Close account settings modal if open
            if (accountSettingsModal) {
                accountSettingsModal.classList.remove('active');
            }
        });
    }

    // Close boost modal
    function closeBoostModal() {
        if (boostModal) {
            boostModal.classList.remove('active');
        }
    }

    if (closeBoostModalBtn) {
        closeBoostModalBtn.addEventListener('click', closeBoostModal);
    }

    if (boostModalOverlay) {
        boostModalOverlay.addEventListener('click', closeBoostModal);
    }

    // Escape key to close
    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape') {
            if (boostModal && boostModal.classList.contains('active')) {
                closeBoostModal();
            }
            if (boostSuccessModal && boostSuccessModal.classList.contains('active')) {
                closeBoostSuccessModal();
            }
        }
    });

    // Open boost modal
    function openBoostModal() {
        if (boostModal) {
            boostModal.classList.add('active');
            showPlanSelection();

            // Load and display dashboard/boosted posts
            loadDashboard();

            // Projects are pre-loaded via PHP, we just need to re-attach listeners
            attachPostSelectionListeners();

            // Auto-select the single plan
            const singlePlan = document.querySelector('.boost-plan-card.selected');
            if (singlePlan) {
                selectedPlan = {
                    type: singlePlan.dataset.plan,
                    reach: singlePlan.dataset.reach,
                    price: singlePlan.dataset.price
                };
            }
        }
    }

    // Tab switching
    function switchTab(tabName) {
        // Update tab buttons
        document.querySelectorAll('.boost-tab').forEach(tab => {
            tab.classList.remove('active');
        });

        // Update tab content
        document.querySelectorAll('.boost-tab-content').forEach(content => {
            content.classList.remove('active');
        });

        if (tabName === 'boosts') {
            boostsTab.classList.add('active');
            boostsContent.classList.add('active');
        } else if (tabName === 'dashboard') {
            dashboardTab.classList.add('active');
            dashboardContent.classList.add('active');
        }
    }

    if (boostsTab) {
        boostsTab.addEventListener('click', () => switchTab('boosts'));
    }

    if (dashboardTab) {
        dashboardTab.addEventListener('click', () => switchTab('dashboard'));
    }

    // Plan selection
    planCards.forEach(card => {
        card.addEventListener('click', function () {
            // Remove selected class from all cards
            planCards.forEach(c => c.classList.remove('selected'));

            // Add selected class to clicked card
            this.classList.add('selected');

            // Store selected plan
            selectedPlan = {
                type: this.dataset.plan,
                reach: this.dataset.reach,
                price: this.dataset.price
            };
        });
    });

    // Choose post button
    if (choosePostBtn) {
        choosePostBtn.addEventListener('click', function () {
            if (!selectedPlan) {
                alert('Please select a boost plan first');
                return;
            }
            showPostSelection();
        });
    }

    // Back to plans
    if (backToPlansBtn) {
        backToPlansBtn.addEventListener('click', function () {
            showPlanSelection();
        });
    }

    // Show sections
    function showPlanSelection() {
        currentSection = 'plan-selection';
        planSelectionSection.classList.remove('hidden');
        postSelectionSection.classList.add('hidden');
        boostSummarySection.classList.add('hidden');
    }

    function showPostSelection() {
        currentSection = 'post-selection';
        planSelectionSection.classList.add('hidden');
        postSelectionSection.classList.remove('hidden');
        boostSummarySection.classList.add('hidden');
        // Do NOT call loadPosts() as it clears PHP content
        attachPostSelectionListeners();
    }

    function showBoostSummary() {
        currentSection = 'boost-summary';
        planSelectionSection.classList.add('hidden');
        postSelectionSection.classList.add('hidden');
        boostSummarySection.classList.remove('hidden');
        displayBoostSummary();
    }

    // Load posts (display) - DEPRECATED / DISABLED
    function loadPosts() {
        // Disabled to prevent clearing PHP-rendered content
        console.log('loadPosts disabled for server-side rendering');
    }

    // Attach listeners to PHP rendered list
    function attachPostSelectionListeners() {
        const postsList = document.getElementById('postsListContainer');
        if (!postsList) return;

        // Use event delegation - remove first to avoid duplicates (though minimal risk with this logic)
        postsList.removeEventListener('click', handlePostSelectionClick);
        postsList.addEventListener('click', handlePostSelectionClick);
    }

    function handlePostSelectionClick(e) {
        const postCard = e.target.closest('.post-card');

        if (postCard) {
            const projectId = postCard.dataset.postId;
            const title = postCard.querySelector('.post-card-title').textContent;
            const description = postCard.querySelector('.post-card-description') ? postCard.querySelector('.post-card-description').textContent : '';
            const image = postCard.querySelector('.post-card-image img') ? postCard.querySelector('.post-card-image img').src : '';

            selectedPost = {
                id: projectId,
                title: title,
                description: description,
                image: image,
            };

            // Visual feedback
            document.querySelectorAll('.post-card').forEach(p => p.classList.remove('selected'));
            postCard.classList.add('selected');

            // Handle radio button
            const radio = postCard.querySelector('.post-radio');
            if (radio) {
                radio.checked = true;
            }

            // Enable confirm button
            updateConfirmButton();
        }
    }


    // Handle post selection - Legacy function kept if needed but handlePostSelectionClick is used now
    function handlePostSelection(post, postCard) {
        // ...
    }

    // Update confirm button state
    function updateConfirmButton() {
        if (confirmBoostBtn) {
            confirmBoostBtn.disabled = !selectedPost;
        }
    }

    // Confirm button
    if (confirmBoostBtn) {
        confirmBoostBtn.addEventListener('click', function () {
            if (!selectedPost) return;
            showBoostSummary();
        });
    }

    // Search posts
    if (postSearchInput) {
        postSearchInput.addEventListener('input', function (e) {
            const searchTerm = e.target.value.toLowerCase();
            const postItems = postsListContainer.querySelectorAll('.post-card');

            postItems.forEach(item => {
                const title = item.querySelector('.post-card-title').textContent.toLowerCase();
                const description = item.querySelector('.post-card-description').textContent.toLowerCase();

                if (title.includes(searchTerm) || description.includes(searchTerm)) {
                    item.style.display = 'flex';
                } else {
                    item.style.display = 'none';
                }
            });
        });
    }

    // Display boost summary
    function displayBoostSummary() {
        if (!selectedPost || !selectedPlan) return;

        // Show selected post
        if (selectedPostPreview) {
            selectedPostPreview.innerHTML = `
                <div class="post-card">
                    <div class="post-card-image">
                        <img src="${selectedPost.image || 'https://images.unsplash.com/photo-1541888946425-d81bb19240f5?w=400&h=400&fit=crop'}" alt="Post image" />
                    </div>
                    <div class="post-card-content">
                        <div class="post-card-header">
                            <h4 class="post-card-title">${selectedPost.title}</h4>
                            <span class="post-chosen-badge">Post Chosen</span>
                        </div>
                        <p class="post-card-description">${selectedPost.description}</p>
                    </div>
                </div>
            `;
        }

        // Show selected plan
        if (selectedPlanDetails) {
            const reach = parseInt(selectedPlan.reach).toLocaleString();
            selectedPlanDetails.querySelector('.selected-plan-reach').textContent = `${reach}+ reach`;
            selectedPlanDetails.querySelector('.selected-plan-price').textContent = `₱${parseInt(selectedPlan.price).toLocaleString()}`;
        }
    }

    // Change post button
    if (changePostBtn) {
        changePostBtn.addEventListener('click', function () {
            showPostSelection();
        });
    }

    // Boost now button
    if (boostNowBtn) {
        boostNowBtn.addEventListener('click', async function () {
            if (!selectedPost || !selectedPlan) return;

            // Show loading
            const originalText = boostNowBtn.innerHTML;
            boostNowBtn.innerHTML = '<i class="fi fi-rr-spinner fi-spin"></i> Processing...';
            boostNowBtn.disabled = true;

            try {
                const response = await fetch('/boost/checkout', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    },
                    body: JSON.stringify({
                        project_id: selectedPost.id
                    })
                });

                const data = await response.json();

                if (data.success) {
                    window.location.href = data.checkout_url;
                } else {
                    alert('Boost failed: ' + (data.message || 'Unknown error'));
                    boostNowBtn.innerHTML = originalText;
                    boostNowBtn.disabled = false;
                }
            } catch (error) {
                console.error('Error boosting:', error);
                alert('An error occurred. Please try again.');
                boostNowBtn.innerHTML = originalText;
                boostNowBtn.disabled = false;
            }
        });
    }

    // Close success modal
    function closeBoostSuccessModal() {
        if (boostSuccessModal) {
            boostSuccessModal.classList.remove('active');
        }
    }

    if (closeBoostSuccessBtn) {
        closeBoostSuccessBtn.addEventListener('click', function () {
            closeBoostSuccessModal();
            // Open boost modal to dashboard tab
            openBoostModal();
            switchTab('dashboard');
        });
    }

    if (boostSuccessOverlay) {
        boostSuccessOverlay.addEventListener('click', closeBoostSuccessModal);
    }

    // Reset boost modal
    function resetBoostModal() {
        selectedPlan = null;
        selectedPost = null;

        // Remove selected classes
        planCards.forEach(c => c.classList.remove('selected'));

        // Reset to plan selection
        showPlanSelection();

        // Switch to boosts tab
        switchTab('boosts');

        // Clear search
        if (postSearchInput) {
            postSearchInput.value = '';
        }
    }

    // Load dashboard
    function loadDashboard() {
        // Disabled fetch for server-side rendering
    }

    // Load boosted posts
    function loadBoostedPosts() {
        if (!boostedPostsList) return;

        const template = document.getElementById('boostedPostTemplate');
        boostedPostsList.innerHTML = '';

        if (boostedPosts.length === 0) {
            boostedPostsList.innerHTML = '<p class="no-posts-message" style="text-align:center; padding: 20px;">No active boosted projects.</p>';
            return;
        }

        boostedPosts.forEach(post => {
            const clone = template.content.cloneNode(true);
            const boostedCard = clone.querySelector('.boosted-post-card');

            boostedCard.dataset.postId = post.id;
            boostedCard.querySelector('.boosted-post-image img').src = post.image || 'https://images.unsplash.com/photo-1541888946425-d81bb19240f5?w=400&h=400&fit=crop';
            boostedCard.querySelector('.boosted-post-title').textContent = post.title;
            boostedCard.querySelector('.boosted-post-description').textContent = post.description;
            boostedCard.querySelector('.location-text').textContent = post.location;
            boostedCard.querySelector('.end-date-text').textContent = post.endDate;

            boostedPostsList.appendChild(clone);
        });
    }
});
