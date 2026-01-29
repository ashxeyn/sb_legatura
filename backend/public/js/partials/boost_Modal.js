// Boost Modal JavaScript
document.addEventListener('DOMContentLoaded', function() {
    // Sample posts data
    const ownerPosts = [
        {
            id: 1,
            title: 'Residential House Construction',
            description: 'A two-story residential house with modern minimalist design. Owner is looking for quality finishing, open space layout, and energy-efficient construction materials.',
            location: 'Zamboanga City',
            date: 'July 2026',
            image: 'https://images.unsplash.com/photo-1600596542815-ffad4c1539a9?w=400&h=400&fit=crop'
        },
        {
            id: 2,
            title: 'Modern Two-Storey Residential House',
            description: 'A two-story residential house with modern minimalist design. Owner is looking for quality finishing, open space layout, and energy-efficient construction materials.',
            location: 'Zamboanga City',
            date: 'July 2026',
            image: 'https://images.unsplash.com/photo-1600585154340-be6161a56a0c?w=400&h=400&fit=crop'
        },
        {
            id: 3,
            title: 'Commercial Office Renovation',
            description: 'A two-story residential house with modern minimalist design. Owner is looking for quality finishing, open space layout, and energy-efficient construction materials.',
            location: 'Zamboanga City',
            date: 'July 2026',
            image: 'https://images.unsplash.com/photo-1486406146926-c627a92ad1ab?w=400&h=400&fit=crop'
        },
        {
            id: 4,
            title: 'Residential Renovation',
            description: 'A two-story residential house with modern minimalist design. Owner is looking for quality finishing, open space layout, and energy-efficient construction materials.',
            location: 'Zamboanga City',
            date: 'July 2026',
            image: 'https://images.unsplash.com/photo-1560518883-ce09059eeffa?w=400&h=400&fit=crop'
        }
    ];

    // Sample boosted posts data
    const boostedPosts = [
        {
            id: 1,
            title: 'Residential House Construction',
            description: 'A two-story residential house with modern minimalist design. Owner is looking for quality finishing, open space layout, and energy-efficient construction materials.',
            location: 'Zamboanga City',
            endDate: '10/23/2025',
            image: 'https://images.unsplash.com/photo-1600596542815-ffad4c1539a9?w=400&h=400&fit=crop'
        },
        {
            id: 2,
            title: 'Modern Two-Storey Residential House',
            description: 'A two-story residential house with modern minimalist design. Owner is looking for quality finishing, open space layout, and energy-efficient construction materials.',
            location: 'Zamboanga City',
            endDate: '11/12/2025',
            image: 'https://images.unsplash.com/photo-1600585154340-be6161a56a0c?w=400&h=400&fit=crop'
        },
        {
            id: 3,
            title: 'Commercial Office Renovation',
            description: 'A two-story residential house with modern minimalist design. Owner is looking for quality finishing, open space layout, and energy-efficient construction materials.',
            location: 'Zamboanga City',
            endDate: '11/20/2025',
            image: 'https://images.unsplash.com/photo-1486406146926-c627a92ad1ab?w=400&h=400&fit=crop'
        }
    ];

    // State management
    let selectedPlan = null;
    let selectedPost = null;
    let currentSection = 'plan-selection'; // 'plan-selection', 'post-selection', 'boost-summary'

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

    // Open boost modal
    if (boostLink) {
        boostLink.addEventListener('click', function(e) {
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
    document.addEventListener('keydown', function(e) {
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
            loadDashboard();
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
        card.addEventListener('click', function() {
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
        choosePostBtn.addEventListener('click', function() {
            if (!selectedPlan) {
                alert('Please select a boost plan first');
                return;
            }
            showPostSelection();
        });
    }

    // Back to plans
    if (backToPlansBtn) {
        backToPlansBtn.addEventListener('click', function() {
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
        loadPosts();
    }

    function showBoostSummary() {
        currentSection = 'boost-summary';
        planSelectionSection.classList.add('hidden');
        postSelectionSection.classList.add('hidden');
        boostSummarySection.classList.remove('hidden');
        displayBoostSummary();
    }

    // Load posts
    function loadPosts() {
        if (!postsListContainer) return;

        const template = document.getElementById('postCardTemplate');
        postsListContainer.innerHTML = '';

        ownerPosts.forEach(post => {
            const clone = template.content.cloneNode(true);
            const postCard = clone.querySelector('.post-card');
            
            postCard.dataset.postId = post.id;
            postCard.querySelector('.post-card-image img').src = post.image;
            postCard.querySelector('.post-card-title').textContent = post.title;
            postCard.querySelector('.post-card-description').textContent = post.description;
            postCard.querySelector('.location-text').textContent = post.location;
            postCard.querySelector('.date-text').textContent = post.date;

            const radio = postCard.querySelector('.post-radio');
            radio.value = post.id;

            // Click on card to select
            postCard.addEventListener('click', function(e) {
                if (e.target.tagName !== 'INPUT') {
                    radio.checked = true;
                    handlePostSelection(post, postCard);
                }
            });

            // Click on radio
            radio.addEventListener('change', function() {
                if (this.checked) {
                    handlePostSelection(post, postCard);
                }
            });

            postsListContainer.appendChild(clone);
        });

        // Enable/disable confirm button
        updateConfirmButton();
    }

    // Handle post selection
    function handlePostSelection(post, postCard) {
        // Remove selected class from all cards
        document.querySelectorAll('.post-card').forEach(card => {
            card.classList.remove('selected');
        });

        // Add selected class to clicked card
        postCard.classList.add('selected');

        // Store selected post
        selectedPost = post;

        // Enable confirm button
        updateConfirmButton();
    }

    // Update confirm button state
    function updateConfirmButton() {
        if (confirmBoostBtn) {
            confirmBoostBtn.disabled = !selectedPost;
        }
    }

    // Confirm button
    if (confirmBoostBtn) {
        confirmBoostBtn.addEventListener('click', function() {
            if (!selectedPost) return;
            showBoostSummary();
        });
    }

    // Search posts
    if (postSearchInput) {
        postSearchInput.addEventListener('input', function(e) {
            const searchTerm = e.target.value.toLowerCase();
            const postCards = postsListContainer.querySelectorAll('.post-card');

            postCards.forEach(card => {
                const title = card.querySelector('.post-card-title').textContent.toLowerCase();
                const description = card.querySelector('.post-card-description').textContent.toLowerCase();
                
                if (title.includes(searchTerm) || description.includes(searchTerm)) {
                    card.style.display = 'flex';
                } else {
                    card.style.display = 'none';
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
                        <img src="${selectedPost.image}" alt="Post image" />
                    </div>
                    <div class="post-card-content">
                        <div class="post-card-header">
                            <h4 class="post-card-title">${selectedPost.title}</h4>
                            <span class="post-chosen-badge">Post Chosen</span>
                        </div>
                        <p class="post-card-description">${selectedPost.description}</p>
                        <div class="post-card-footer">
                            <div class="post-card-meta">
                                <span class="post-card-location">
                                    <i class="fi fi-rr-marker"></i>
                                    <span>${selectedPost.location}</span>
                                </span>
                                <span class="post-card-date">
                                    <i class="fi fi-rr-calendar"></i>
                                    <span>${selectedPost.date}</span>
                                </span>
                            </div>
                        </div>
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
        changePostBtn.addEventListener('click', function() {
            showPostSelection();
        });
    }

    // Boost now button
    if (boostNowBtn) {
        boostNowBtn.addEventListener('click', function() {
            if (!selectedPost || !selectedPlan) return;

            // Show success modal
            if (boostReachValue) {
                const reach = parseInt(selectedPlan.reach).toLocaleString();
                boostReachValue.textContent = `${reach}+`;
            }

            if (boostSuccessModal) {
                boostSuccessModal.classList.add('active');
            }

            // Close boost modal
            closeBoostModal();

            // Reset for next boost
            setTimeout(() => {
                resetBoostModal();
            }, 500);
        });
    }

    // Close success modal
    function closeBoostSuccessModal() {
        if (boostSuccessModal) {
            boostSuccessModal.classList.remove('active');
        }
    }

    if (closeBoostSuccessBtn) {
        closeBoostSuccessBtn.addEventListener('click', function() {
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
        loadBoostedPosts();
    }

    // Load boosted posts
    function loadBoostedPosts() {
        if (!boostedPostsList) return;

        const template = document.getElementById('boostedPostTemplate');
        boostedPostsList.innerHTML = '';

        boostedPosts.forEach(post => {
            const clone = template.content.cloneNode(true);
            const boostedCard = clone.querySelector('.boosted-post-card');
            
            boostedCard.dataset.postId = post.id;
            boostedCard.querySelector('.boosted-post-image img').src = post.image;
            boostedCard.querySelector('.boosted-post-title').textContent = post.title;
            boostedCard.querySelector('.boosted-post-description').textContent = post.description;
            boostedCard.querySelector('.location-text').textContent = post.location;
            boostedCard.querySelector('.end-date-text').textContent = post.endDate;

            boostedPostsList.appendChild(clone);
        });
    }
});
