<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Landing Page</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap">
    <link rel="stylesheet" href="https://cdn-uicons.flaticon.com/2.4.0/uicons-regular-rounded/css/uicons-regular-rounded.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/remixicon/3.5.0/remixicon.min.css">
    <link rel="stylesheet" href="{{ asset('css/signUp_logIN/landingPage.css') }}">
</head>
<body>
    <!-- Navbar -->
    <nav class="hero-nav" id="top">
        <div class="hero-nav-inner">
            <div class="hero-nav-logo">
                <img src="{{ asset('img/LEGATURA.svg') }}" alt="Legatura">
            </div>
            <ul class="hero-nav-links">
                <li><a href="#about">About</a></li>
                <li><a href="#features">Features</a></li>
                <li><a href="#user-experience">For Users</a></li>
                <li><a href="#plans">Subscriptions</a></li>
                <li><a href="#mvp">Core Solutions</a></li>
                <li><a href="#team">Team</a></li>
                <li><a href="#contact">Contact</a></li>
            </ul>
            <button type="button" class="hero-nav-cta">Download App</button>
            <button class="hero-nav-hamburger" id="navHamburger" aria-label="Open menu">
                <span></span><span></span><span></span>
            </button>
        </div>
    </nav>

    <!-- Mobile fullscreen overlay menu -->
    <div class="mobile-menu-overlay" id="navMobileMenu">
        <div class="mobile-menu-header">
            <div class="mobile-menu-logo">
                <img src="{{ asset('img/logo_legatura.svg') }}" alt="Legatura">
            </div>
            <button class="mobile-menu-close" id="navMobileClose" aria-label="Close menu">
                <span></span><span></span>
            </button>
        </div>
        <nav class="mobile-menu-links">
            <ul>
                <li><a href="#about">About</a></li>
                <li><a href="#features">Features</a></li>
                <li><a href="#user-experience">For Users</a></li>
                <li><a href="#plans">Subscriptions</a></li>
                <li><a href="#video-demo">Why Legatura?</a></li>
                <li><a href="#mvp">Core Solutions</a></li>
                <li><a href="#team">Team</a></li>
                <li><a href="#contact">Contact</a></li>
            </ul>
        </nav>
    </div>

   <section class="hero">
        <!-- Full-cover background image -->
        <div class="hero-image">
            <img src="{{ asset('img/ZC.svg') }}" alt="Construction and building projects">
        </div>

        <!-- Gradient overlay: light at top → transparent → dark at bottom -->
        <div class="hero-overlay"></div>

        <!-- Centered hero content -->
        <div class="hero-body">
            <h1 class="hero-headline">
                <span class="hl-line1">BUILD BETTER</span><br>
                <span class="hl-line2">BUILD WITH</span><br>
                <span class="hl-line3">LEGATURA</span>
            </h1>
            <p class="hero-subtitle">
                The bridge between vision and execution. We provide property owners with total project visibility while ensuring contractors receive fair, timely payments for every milestone achieved.
            </p>
            <button type="button" class="hero-body-cta">Download App</button>
        </div>
    </section>

    <div class="main-content">
    <!-- About Section -->
    <section id="about" class="about py-16 md:py-20">
        <div class="w-full px-8 md:px-16 lg:px-24 text-center">
            <h2 class="text-2xl md:text-3xl lg:text-4xl font-extrabold mb-4 md:mb-6 text-white">About Legatura</h2>
            <p class="text-sm md:text-base lg:text-lg leading-relaxed mb-8 md:mb-12 max-w-5xl mx-auto text-white/90">
                Legatura is a platform that connects property owners with trusted contractors, simplifying project management, bidding, and communication to deliver construction projects efficiently and reliably.
            </p>
            <div class="about-track grid grid-cols-1 md:grid-cols-2 gap-6 md:gap-8 lg:gap-12 px-0 md:px-8 lg:px-16" id="aboutTrack">
                <div class="about-card p-6 md:p-8 rounded-lg">
                    <h4 class="text-lg md:text-xl lg:text-2xl font-bold mb-3 md:mb-4 text-white">Empowering Project Success & Quality</h4>
                    <p class="text-xs md:text-sm lg:text-base leading-relaxed text-white/85">
                        Legatura empowers project success through transparent workflows, verified contractors, and real-time project tracking, creating a more efficient and reliable construction ecosystem.
                    </p>
                </div>
                <div class="about-card p-6 md:p-8 rounded-lg">
                    <h4 class="text-lg md:text-xl lg:text-2xl font-bold mb-3 md:mb-4 text-white">Enhancing Trust & Collaboration</h4>
                    <p class="text-xs md:text-sm lg:text-base leading-relaxed text-white/85">
                       Legatura connects property owners and contractors through a transparent platform that encourages trust, collaboration, and high-quality project delivery.
                    </p>
                </div>
            </div>
            <div class="about-dots" id="aboutDots" aria-label="About carousel pagination"></div>
        </div>
    </section>

    <!-- Dual User Experience Section -->
    <section id="user-experience" class="user-experience py-16 md:py-20">
        <div class="w-full px-8 md:px-16 lg:px-24 text-center">
            <h2 class="text-3xl md:text-4xl font-extrabold mb-4 text-white"><span style="color:#1E1E1E">Property Owners</span> & <span style="color:#1E1E1E">Contractors</span></h2>
            <p class="text-base md:text-lg leading-relaxed mb-12 max-w-3xl mx-auto text-white/90">
                Discover how Legatura empowers both sides of construction projects
            </p>
            <div class="grid grid-cols-1 gap-8 md:gap-12 px-4 md:px-8 lg:px-16">
                <!-- Property Owner Card -->
                <div class="user-card owner-card p-8 rounded-lg">
                    <img src="{{ asset('img/owner1.svg') }}" alt="Property Owner" class="w-full h-auto">
                </div>

                <!-- Modern Separator Line -->
                <div class="separator-container">
                    <div class="modern-separator"></div>
                </div>

                <!-- Contractor Card -->
                <div class="user-card contractor-card p-8 rounded-lg">
                    <img src="{{ asset('img/contractor1.svg') }}" alt="Contractor" class="w-full h-auto">
                </div>
            </div>
        </div>
    </section>

    <!-- Features Section -->
    <section id="features" class="features py-16 md:py-20">
        <div class="w-full px-8 md:px-16 lg:px-24 text-center">
            <h2 class="text-3xl md:text-4xl font-extrabold mb-4 text-white">Legatura Features</h2>
            <p class="text-base md:text-lg leading-relaxed mb-12 max-w-3xl mx-auto text-white/90">
                Tools designed to keep every project clear, competitive, and on track
            </p>
            <div class="features-track grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 md:gap-8 px-4 md:px-8 lg:px-16 text-left" id="featuresTrack">
                <div class="feature-card p-6 rounded-lg">
                    <div class="feature-icon">
                        <i class="ri-briefcase-4-line"></i>
                    </div>
                    <h4 class="text-lg md:text-xl font-bold mb-3 text-white">Smart Project Posting</h4>
                    <p class="text-sm md:text-base text-white/85">
                        Define scope, budget, timeline, and requirements in minutes.
                    </p>
                </div>
                <div class="feature-card p-6 rounded-lg">
                    <div class="feature-icon">
                        <i class="ri-auction-line"></i>
                    </div>
                    <h4 class="text-lg md:text-xl font-bold mb-3 text-white">Competitive Bidding</h4>
                    <p class="text-sm md:text-base text-white/85">
                        Compare bids side-by-side and choose the best value.
                    </p>
                </div>
                <div class="feature-card p-6 rounded-lg">
                    <div class="feature-icon">
                        <i class="ri-shield-check-line"></i>
                    </div>
                    <h4 class="text-lg md:text-xl font-bold mb-3 text-white">Contractor Verification</h4>
                    <p class="text-sm md:text-base text-white/85">
                        Work only with verified professionals and vetted profiles.
                    </p>
                </div>
                <div class="feature-card p-6 rounded-lg">
                    <div class="feature-icon">
                        <i class="ri-line-chart-line"></i>
                    </div>
                    <h4 class="text-lg md:text-xl font-bold mb-3 text-white">Real-Time Tracking</h4>
                    <p class="text-sm md:text-base text-white/85">
                        Stay updated with milestones, progress, and deliverables.
                    </p>
                </div>
                <div class="feature-card p-6 rounded-lg">
                    <div class="feature-icon">
                        <i class="ri-bank-card-line"></i>
                    </div>
                    <h4 class="text-lg md:text-xl font-bold mb-3 text-white">Secure Payments</h4>
                    <p class="text-sm md:text-base text-white/85">
                        Transparent payment handling tied to project progress.
                    </p>
                </div>
                <div class="feature-card p-6 rounded-lg">
                    <div class="feature-icon">
                        <i class="ri-star-smile-line"></i>
                    </div>
                    <h4 class="text-lg md:text-xl font-bold mb-3 text-white">Ratings and Reviews</h4>
                    <p class="text-sm md:text-base text-white/85">
                        Build trust through verified feedback and work history.
                    </p>
                </div>
            </div>
            <div class="features-dots" id="featuresDots" aria-label="Features carousel pagination"></div>
        </div>
    </section>

    <!-- Boosting & Subscription Section -->
    <section id="plans" class="plans-showcase py-16 md:py-20">
        <div class="w-full px-8 md:px-16 lg:px-24 text-center">
            <h2 class="text-3xl md:text-4xl font-extrabold mb-4 text-black"><span style="color:#1E1E1E">Boost</span> and <span style="color:#1E1E1E">Subscriptions</span></h2>
            <p class="text-base md:text-lg leading-relaxed mb-12 max-w-3xl mx-auto text-black/80">
                Property owners can boost project posts, while contractors unlock premium growth tools.
            </p>
            <div class="plans-track grid grid-cols-1 lg:grid-cols-2 gap-8 md:gap-12 px-4 md:px-8 lg:px-16 text-left" id="plansTrack">
                <div class="plan-panel boost-panel p-6 md:p-8 rounded-lg" data-plan-slide>
                    <div class="panel-header">
                        <div class="panel-icon boost">
                            <i class="fi fi-rr-rocket"></i>
                        </div>
                        <div>
                            <h3 class="text-xl md:text-2xl font-bold text-black">Boosting for Property Owners</h3>
                            <p class="text-sm md:text-base text-black/70">Boost visibility and attract qualified contractors to your project.</p>
                        </div>
                    </div>
                    <div class="panel-card-grid">
                        <button class="panel-card" type="button">
                            <div class="panel-card-emoji"><i class="ri-rocket-2-line" style="color:#ec7e00;"></i></div>
                            <div>
                                <div class="panel-card-title">Boost Your Project</div>
                                <div class="panel-card-meta">Reach upto 1,000+ contractors</div>
                            </div>
                            <span class="panel-card-price">₱49<span style="font-size: 0.75em; font-weight: 400; opacity: 0.8;">/project</span></span>
                        </button>
                    </div>
                    <ul class="panel-list">
                        <li><i class="fi fi-rr-check-circle"></i> Instant visibility boost for your project</li>
                        <li><i class="fi fi-rr-check-circle"></i> Attract more qualified contractor bids</li>
                        <li><i class="fi fi-rr-check-circle"></i> Active boost duration of 7 days</li>
                        <li><i class="fi fi-rr-check-circle"></i> Track engagement and bid metrics</li>
                    </ul>
                </div>

                <div class="plan-panel subscription-panel p-6 md:p-8 rounded-lg" data-plan-slide>
                    <div class="panel-header">
                        <div class="panel-icon subscription">
                            <i class="fi fi-rr-crown"></i>
                        </div>
                        <div>
                            <h3 class="text-xl md:text-2xl font-bold text-black">Subscriptions for Contractors</h3>
                            <p class="text-sm md:text-base text-black/70">Get unlimited bids, analytics, and bid boosts.</p>
                        </div>
                    </div>
                    <div class="panel-card-grid">
                        <button class="panel-card tier-card is-selected" data-tier="gold" type="button">
                            <div class="panel-card-emoji"><i class="ri-vip-crown-line" style="color:#f59e0b;"></i></div>
                            <div>
                                <div class="panel-card-title">Gold Tier</div>
                                <div class="panel-card-meta">AI analytics + unlimited bids</div>
                            </div>
                            <span class="panel-card-price">₱1,999</span>
                        </button>
                        <button class="panel-card tier-card" data-tier="silver" type="button">
                            <div class="panel-card-emoji"><i class="ri-medal-line" style="color:#9ca3af;"></i></div>
                            <div>
                                <div class="panel-card-title">Silver Tier</div>
                                <div class="panel-card-meta">Boost bids + priority visibility</div>
                            </div>
                            <span class="panel-card-price">₱1,499</span>
                        </button>
                        <button class="panel-card tier-card" data-tier="bronze" type="button">
                            <div class="panel-card-emoji"><i class="ri-award-line" style="color:#b45309;"></i></div>
                            <div>
                                <div class="panel-card-title">Bronze Tier</div>
                                <div class="panel-card-meta">Core bidding essentials</div>
                            </div>
                            <span class="panel-card-price">₱999</span>
                        </button>
                    </div>
                    <ul class="panel-list" id="tierFeaturesList">
                        <li><i class="fi fi-rr-check-circle"></i> All Silver tier features included</li>
                        <li><i class="fi fi-rr-check-circle"></i> Unlimited bids per month</li>
                        <li><i class="fi fi-rr-check-circle"></i> AI-powered weather delay predictions</li>
                        <li><i class="fi fi-rr-check-circle"></i> Basic project listing and milestone tracking</li>
                    </ul>

                </div>
            </div>
            <div class="plans-dots" id="plansDots">
                <button type="button" class="plans-dot is-active" data-index="0" aria-label="Boosting plan"></button>
                <button type="button" class="plans-dot" data-index="1" aria-label="Subscription plan"></button>
            </div>
        </div>
    </section>

    <!-- Video Demo Section -->
    <section id="video-demo" class="video-demo py-10 md:py-16 lg:py-20">
        <div class="w-full px-4 sm:px-8 md:px-16 lg:px-24">

            <!-- Section Header -->
            <div class="text-center mb-8 md:mb-12">
                <h2 class="text-2xl sm:text-3xl md:text-4xl font-extrabold mb-3 md:mb-4 text-white">Why Choose Legatura?</h2>
            </div>

            <!-- Two-column — text left, hero image right -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 md:gap-12 items-center mb-8 md:mb-12">

                <!-- Left: Text Content -->
                <div class="text-left">
                    <h1 class="text-3xl sm:text-4xl md:text-5xl lg:text-6xl xl:text-7xl font-bold text-white mb-4 md:mb-6">
                        Build with
                        <span class="block text-transparent bg-clip-text bg-gradient-to-r from-orange-400 to-orange-600">
                            Confidence
                        </span>
                    </h1>
                    <p class="text-sm sm:text-base md:text-lg lg:text-xl text-white/90 mb-6 md:mb-8 leading-relaxed">
                        Watch how Legatura streamlines construction project management from posting projects and receiving competitive bids to tracking progress in real time and ensuring every milestone is paid securely.
                    </p>
                </div>

                <!-- Right: Hero Image -->
                <div class="flex justify-center lg:justify-end">
                    <div class="mvp-img-wrap">
                        <img src="{{ asset('img/hero.png') }}" alt="Legatura App"
                            class="w-full max-w-xs sm:max-w-sm md:max-w-md lg:max-w-2xl xl:max-w-3xl h-auto object-contain">
                    </div>
                </div>

            </div>

            <!-- Modern Separator -->
            <div class="separator-container mb-12">
                <div class="modern-separator" style="background: #EC7E00; box-shadow: 0 2px 16px rgba(236,126,0,0.5);"></div>
            </div>

            <!-- Bottom: Full-width Video -->
            <div class="video-frame mx-auto">
                <iframe id="legatura-video" src="https://geo.dailymotion.com/player.html?video=xa1gf72&api=postMessage&queue-enable=false&queue-autoplay-next=false&sharing-enable=false&ui-start-screen-info=false" allow="autoplay; fullscreen; picture-in-picture; web-share" allowfullscreen frameborder="0"></iframe>
            </div>

            <!-- Modern Separator (below video) -->
            <div class="separator-container mt-8 md:mt-12 mb-4 md:mb-6">
                <div class="modern-separator" style="background: #EC7E00; box-shadow: 0 2px 16px rgba(236,126,0,0.5);"></div>
            </div>

        </div>
    </section>

    <!-- Everything You Need Section -->
    <section id="features-highlight" class="features pt-4 md:pt-6 pb-16 md:pb-20">
        <div class="w-full px-8 md:px-16 lg:px-24 text-center">

            <!-- Section Header -->
            <h2 class="text-2xl md:text-3xl lg:text-4xl font-extrabold mb-4 text-white">Everything You Need</h2>
            <p class="text-sm md:text-base lg:text-lg leading-relaxed mb-8 md:mb-12 max-w-3xl mx-auto text-white/90">
                Legatura provides all the tools you need to manage construction projects from start to finish, connecting you with the right professionals every step of the way.
            </p>

            <!-- Features Grid -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 md:gap-8 px-4 md:px-8 lg:px-16 text-left" id="featuresHighlightTrack">

                <!-- Feature 1: Find Verified Contractors -->
                <div class="feature-card p-6 rounded-lg">
                    <div class="feature-icon">
                        <i class="ri-user-star-line"></i>
                    </div>
                    <h4 class="text-lg md:text-xl font-bold mb-3 text-white">Find Verified Contractors</h4>
                    <p class="text-sm md:text-base text-white/85">Browse and connect with pre-vetted contractors in your area. View portfolios, ratings, and reviews from real clients.</p>
                    <ul class="feature-card-list">
                        <li><i class="ri-check-line"></i> Background verified professionals</li>
                        <li><i class="ri-check-line"></i> Portfolio &amp; credentials review</li>
                        <li><i class="ri-check-line"></i> Real client testimonials</li>
                    </ul>
                </div>

                <!-- Feature 2: Project Management -->
                <div class="feature-card p-6 rounded-lg">
                    <div class="feature-icon">
                        <i class="ri-file-list-3-line"></i>
                    </div>
                    <h4 class="text-lg md:text-xl font-bold mb-3 text-white">Project Management</h4>
                    <p class="text-sm md:text-base text-white/85">Track progress, manage timelines, and communicate with your team all in one place. Stay organized from planning to completion.</p>
                    <ul class="feature-card-list">
                        <li><i class="ri-check-line"></i> Timeline &amp; milestone tracking</li>
                        <li><i class="ri-check-line"></i> Document management</li>
                        <li><i class="ri-check-line"></i> Real-time collaboration</li>
                    </ul>
                </div>

                <!-- Feature 3: Reviews & Ratings -->
                <div class="feature-card p-6 rounded-lg">
                    <div class="feature-icon">
                        <i class="ri-star-line"></i>
                    </div>
                    <h4 class="text-lg md:text-xl font-bold mb-3 text-white">Reviews &amp; Ratings</h4>
                    <p class="text-sm md:text-base text-white/85">Make informed decisions with comprehensive review system. Rate contractors and share your experience with the community.</p>
                    <ul class="feature-card-list">
                        <li><i class="ri-check-line"></i> Verified review system</li>
                        <li><i class="ri-check-line"></i> Photo &amp; video testimonials</li>
                        <li><i class="ri-check-line"></i> Quality assurance metrics</li>
                    </ul>
                </div>

                <!-- Feature 4: Smart Matching -->
                <div class="feature-card p-6 rounded-lg">
                    <div class="feature-icon">
                        <i class="ri-robot-line"></i>
                    </div>
                    <h4 class="text-lg md:text-xl font-bold mb-3 text-white">Smart Matching</h4>
                    <p class="text-sm md:text-base text-white/85">Our AI-powered matching system connects you with the perfect contractors based on your project requirements and preferences.</p>
                    <ul class="feature-card-list">
                        <li><i class="ri-check-line"></i> AI-powered recommendations</li>
                        <li><i class="ri-check-line"></i> Skill &amp; specialty matching</li>
                        <li><i class="ri-check-line"></i> Budget &amp; timeline alignment</li>
                    </ul>
                </div>

                <!-- Feature 5: Secure Payments -->
                <div class="feature-card p-6 rounded-lg">
                    <div class="feature-icon">
                        <i class="ri-shield-check-line"></i>
                    </div>
                    <h4 class="text-lg md:text-xl font-bold mb-3 text-white">Secure Payments</h4>
                    <p class="text-sm md:text-base text-white/85">Protected payment system with escrow services ensures secure transactions for both clients and contractors.</p>
                    <ul class="feature-card-list">
                        <li><i class="ri-check-line"></i> Escrow protection</li>
                        <li><i class="ri-check-line"></i> Milestone-based payments</li>
                        <li><i class="ri-check-line"></i> Dispute resolution</li>
                    </ul>
                </div>

                <!-- Feature 6: Communication Hub -->
                <div class="feature-card p-6 rounded-lg">
                    <div class="feature-icon">
                        <i class="ri-message-2-line"></i>
                    </div>
                    <h4 class="text-lg md:text-xl font-bold mb-3 text-white">Communication Hub</h4>
                    <p class="text-sm md:text-base text-white/85">Integrated messaging, video calls, and file sharing keep everyone connected throughout your project lifecycle.</p>
                    <ul class="feature-card-list">
                        <li><i class="ri-check-line"></i> In-app messaging</li>
                        <li><i class="ri-check-line"></i> Video conferencing</li>
                        <li><i class="ri-check-line"></i> File &amp; photo sharing</li>
                    </ul>
                </div>

            </div>
            <div class="features-highlight-dots" id="featuresHighlightDots" aria-label="Features highlight carousel pagination"></div>
        </div>
    </section>

    <!-- MVP Showcase Section -->
    <section id="mvp" class="py-20" style="background:#f1f1f1;">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

            <!-- Section Header -->
            <div class="text-center mb-10 lg:mb-20">
                <h2 class="text-2xl sm:text-3xl lg:text-5xl font-bold mb-4" style="color:#1a1a1a;">
                    Our Core Solutions
                </h2>
                <p class="text-base lg:text-lg max-w-3xl mx-auto mb-6" style="color:#525252;">
                    These are the essential features that make Legatura the most trusted platform in construction management.
                </p>
                <div class="max-w-2xl mx-auto">
                    <p class="text-sm px-4 py-3 rounded-lg border" style="color:#737373; background:#f9fafb; border-color:#e5e7eb;">
                        <i class="ri-information-line" style="font-size:1rem; margin-right:6px; vertical-align:middle;"></i>
                        <span class="font-medium">Prototype Notice:</span> Screenshots and interface designs shown are from our
                        early development phase and are subject to change as we continue to refine and enhance the platform.
                    </p>
                </div>
            </div>

            <!-- MVP 1: Contractor Discovery -->
            <div class="mb-16 lg:mb-32">
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 lg:gap-16 items-center">
                    <div class="mvp-text-col">
                        <div class="flex items-center gap-3 mb-5">
                            <div class="w-12 h-12 lg:w-16 lg:h-16 rounded-xl flex items-center justify-center flex-shrink-0" style="background:#fff7ed;">
                                <i class="ri-search-line" style="font-size:1.5rem; color:#EC7E00;"></i>
                            </div>
                            <h3 class="text-xl sm:text-2xl lg:text-3xl font-bold" style="color:#1a1a1a;">Smart Contractor Discovery</h3>
                        </div>
                        <p class="text-base lg:text-lg mb-6" style="color:#525252;">
                            Find the perfect contractor for your project with our advanced matching algorithm. All contractors are
                            verified, licensed, and reviewed by real clients.
                        </p>
                        <div class="space-y-4 mb-8">
                            <div class="flex items-center">
                                <div class="w-2 h-2 rounded-full mr-4 flex-shrink-0" style="background:#EC7E00;"></div>
                                <span style="color:#404040;">Background verification &amp; licensing checks</span>
                            </div>
                            <div class="flex items-center">
                                <div class="w-2 h-2 rounded-full mr-4 flex-shrink-0" style="background:#EC7E00;"></div>
                                <span style="color:#404040;">Portfolio showcase with past projects</span>
                            </div>
                            <div class="flex items-center">
                                <div class="w-2 h-2 rounded-full mr-4 flex-shrink-0" style="background:#EC7E00;"></div>
                                <span style="color:#404040;">Skill-based matching with project requirements</span>
                            </div>
                            <div class="flex items-center">
                                <div class="w-2 h-2 rounded-full mr-4 flex-shrink-0" style="background:#EC7E00;"></div>
                                <span style="color:#404040;">Real-time availability and pricing</span>
                            </div>
                        </div>
                    </div>
                    <div class="mvp-img-col relative mt-6 lg:mt-0">
                        <div class="flex justify-center">
                            <div class="mvp-img-wrap">
                                <img src="{{ asset('img/contractor.png') }}" alt="Contractor Discovery App Screenshot">
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Separator -->
            <div class="separator-container my-6 lg:my-10">
                <div class="modern-separator" style="background: #3b82f6; box-shadow: 0 2px 16px rgba(59,130,246,0.35);"></div>
            </div>

            <!-- MVP 2: Project Management -->
            <div class="mb-16 lg:mb-32">
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 lg:gap-16 items-center">
                    <div class="mvp-img-col order-2 lg:order-1 mt-6 lg:mt-0">
                        <div class="flex justify-center">
                            <div class="mvp-img-wrap">
                                <img src="{{ asset('img/progress.png') }}" alt="Project Management App Screenshot">
                            </div>
                        </div>
                    </div>
                    <div class="mvp-text-col order-1 lg:order-2">
                        <div class="flex items-center gap-3 mb-5">
                            <div class="w-12 h-12 lg:w-16 lg:h-16 rounded-xl flex items-center justify-center flex-shrink-0" style="background:#eff6ff;">
                                <i class="ri-file-list-3-line" style="font-size:1.5rem; color:#2563eb;"></i>
                            </div>
                            <h3 class="text-xl sm:text-2xl lg:text-3xl font-bold" style="color:#1a1a1a;">Complete Project Management</h3>
                        </div>
                        <p class="text-base lg:text-lg mb-6" style="color:#525252;">
                            Track every aspect of your construction project from start to finish. Monitor progress, manage timelines,
                            and stay informed with real-time updates.
                        </p>
                        <div class="space-y-4 mb-8">
                            <div class="flex items-center">
                                <div class="w-2 h-2 rounded-full mr-4 flex-shrink-0" style="background:#2563eb;"></div>
                                <span style="color:#404040;">Interactive timeline with milestone tracking</span>
                            </div>
                            <div class="flex items-center">
                                <div class="w-2 h-2 rounded-full mr-4 flex-shrink-0" style="background:#2563eb;"></div>
                                <span style="color:#404040;">Document management and file sharing</span>
                            </div>
                            <div class="flex items-center">
                                <div class="w-2 h-2 rounded-full mr-4 flex-shrink-0" style="background:#2563eb;"></div>
                                <span style="color:#404040;">Budget tracking and expense management</span>
                            </div>
                            <div class="flex items-center">
                                <div class="w-2 h-2 rounded-full mr-4 flex-shrink-0" style="background:#2563eb;"></div>
                                <span style="color:#404040;">Team collaboration and task assignment</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Separator -->
            <div class="separator-container my-6 lg:my-10">
                <div class="modern-separator" style="background: #3b82f6; box-shadow: 0 2px 16px rgba(59,130,246,0.35);"></div>
            </div>

            <!-- MVP 3: Reviews & Trust System -->
            <div class="mb-16 lg:mb-32">
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 lg:gap-16 items-center">
                    <div class="mvp-text-col">
                        <div class="flex items-center gap-3 mb-5">
                            <div class="w-12 h-12 lg:w-16 lg:h-16 rounded-xl flex items-center justify-center flex-shrink-0" style="background:#fefce8;">
                                <i class="ri-star-line" style="font-size:1.5rem; color:#ca8a04;"></i>
                            </div>
                            <h3 class="text-xl sm:text-2xl lg:text-3xl font-bold" style="color:#1a1a1a;">Transparent Review System</h3>
                        </div>
                        <p class="text-base lg:text-lg mb-6" style="color:#525252;">
                            Make informed decisions with our comprehensive review and rating system. Every review is verified and
                            comes from real completed projects.
                        </p>
                        <div class="space-y-4 mb-8">
                            <div class="flex items-center">
                                <div class="w-2 h-2 rounded-full mr-4 flex-shrink-0" style="background:#ca8a04;"></div>
                                <span style="color:#404040;">Verified reviews from completed projects only</span>
                            </div>
                            <div class="flex items-center">
                                <div class="w-2 h-2 rounded-full mr-4 flex-shrink-0" style="background:#ca8a04;"></div>
                                <span style="color:#404040;">Photo and video testimonials</span>
                            </div>
                            <div class="flex items-center">
                                <div class="w-2 h-2 rounded-full mr-4 flex-shrink-0" style="background:#ca8a04;"></div>
                                <span style="color:#404040;">Detailed quality metrics and ratings</span>
                            </div>
                            <div class="flex items-center">
                                <div class="w-2 h-2 rounded-full mr-4 flex-shrink-0" style="background:#ca8a04;"></div>
                                <span style="color:#404040;">Response guarantee from contractors</span>
                            </div>
                        </div>
                    </div>
                    <div class="mvp-img-col relative mt-6 lg:mt-0">
                        <div class="flex justify-center">
                            <div class="mvp-img-wrap">
                                <img src="{{ asset('img/ratings.png') }}" alt="Reviews and Ratings App Screenshot">
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Separator -->
            <div class="separator-container my-6 lg:my-10">
                <div class="modern-separator" style="background: #3b82f6; box-shadow: 0 2px 16px rgba(59,130,246,0.35);"></div>
            </div>

            <!-- MVP 4: Secure Payments -->
            <div class="mb-16 lg:mb-32">
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 lg:gap-16 items-center">
                    <div class="mvp-img-col order-2 lg:order-1 mt-6 lg:mt-0">
                        <div class="flex justify-center">
                            <div class="mvp-img-wrap">
                                <img src="{{ asset('img/payment.png') }}" alt="Secure Payment App Screenshot">
                            </div>
                        </div>
                    </div>
                    <div class="mvp-text-col order-1 lg:order-2">
                        <div class="flex items-center gap-3 mb-5">
                            <div class="w-12 h-12 lg:w-16 lg:h-16 rounded-xl flex items-center justify-center flex-shrink-0" style="background:#f0fdf4;">
                                <i class="ri-shield-check-line" style="font-size:1.5rem; color:#16a34a;"></i>
                            </div>
                            <h3 class="text-xl sm:text-2xl lg:text-3xl font-bold" style="color:#1a1a1a;">Secure Payment Protection</h3>
                        </div>
                        <p class="text-base lg:text-lg mb-6" style="color:#525252;">
                            Your money is protected with our escrow system. Payments are released only when milestones are completed
                            to your satisfaction.
                        </p>
                        <div class="space-y-4 mb-8">
                            <div class="flex items-center">
                                <div class="w-2 h-2 rounded-full mr-4 flex-shrink-0" style="background:#16a34a;"></div>
                                <span style="color:#404040;">Milestone-based payment releases</span>
                            </div>
                            <div class="flex items-center">
                                <div class="w-2 h-2 rounded-full mr-4 flex-shrink-0" style="background:#16a34a;"></div>
                                <span style="color:#404040;">Escrow protection for all transactions</span>
                            </div>
                            <div class="flex items-center">
                                <div class="w-2 h-2 rounded-full mr-4 flex-shrink-0" style="background:#16a34a;"></div>
                                <span style="color:#404040;">Dispute resolution support</span>
                            </div>
                            <div class="flex items-center">
                                <div class="w-2 h-2 rounded-full mr-4 flex-shrink-0" style="background:#16a34a;"></div>
                                <span style="color:#404040;">Multiple payment methods accepted</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Separator -->
            <div class="separator-container my-6 lg:my-10">
                <div class="modern-separator" style="background: #3b82f6; box-shadow: 0 2px 16px rgba(59,130,246,0.35);"></div>
            </div>

            <!-- MVP 5: Communication Hub -->
            <div class="mb-8 lg:mb-20">
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 lg:gap-16 items-center">
                    <div class="mvp-text-col">
                        <div class="flex items-center gap-3 mb-5">
                            <div class="w-12 h-12 lg:w-16 lg:h-16 rounded-xl flex items-center justify-center flex-shrink-0" style="background:#faf5ff;">
                                <i class="ri-message-2-line" style="font-size:1.5rem; color:#9333ea;"></i>
                            </div>
                            <h3 class="text-xl sm:text-2xl lg:text-3xl font-bold" style="color:#1a1a1a;">Seamless Communication</h3>
                        </div>
                        <p class="text-base lg:text-lg mb-6" style="color:#525252;">
                            Stay connected with your project team through integrated messaging, video calls, and file sharing.
                            Everything happens in one place.
                        </p>
                        <div class="space-y-4 mb-8">
                            <div class="flex items-center">
                                <div class="w-2 h-2 rounded-full mr-4 flex-shrink-0" style="background:#9333ea;"></div>
                                <span style="color:#404040;">Real-time messaging with contractors</span>
                            </div>
                            <div class="flex items-center">
                                <div class="w-2 h-2 rounded-full mr-4 flex-shrink-0" style="background:#9333ea;"></div>
                                <span style="color:#404040;">Video calls for project discussions</span>
                            </div>
                            <div class="flex items-center">
                                <div class="w-2 h-2 rounded-full mr-4 flex-shrink-0" style="background:#9333ea;"></div>
                                <span style="color:#404040;">Photo and document sharing</span>
                            </div>
                            <div class="flex items-center">
                                <div class="w-2 h-2 rounded-full mr-4 flex-shrink-0" style="background:#9333ea;"></div>
                                <span style="color:#404040;">Project updates and notifications</span>
                            </div>
                        </div>
                    </div>
                    <div class="mvp-img-col relative mt-6 lg:mt-0">
                        <div class="flex justify-center">
                            <div class="mvp-img-wrap">
                                <img src="{{ asset('img/messaging.png') }}" alt="Communication and Messaging App Screenshot">
                            </div>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </section>

    <!-- Meet Our Team Section -->
    <section id="team" class="team-section">
        <div class="team-inner">

            <!-- Section Header -->
            <div class="team-header">
                <h2 class="team-section-title"><span>Meet</span> Our <span class="team-title-accent">Team</span></h2>
                <p class="team-section-sub">The passionate people behind Legatura, dedicated to transforming how construction projects are managed.</p>
            </div>

            <!-- Member Cards -->
            <div class="members-grid team-track" id="teamTrack">
                <div class="member-card">
                    <div class="member-photo" style="--grad: linear-gradient(135deg,#3b82f6,#2563eb)">
                        <img src="{{ asset('img/shane.png') }}" alt="Shane Hart Jimenez" onerror="this.style.display='none'">
                        <span class="member-initials">SH</span>
                    </div>
                    <div class="member-info">
                        <h4 class="member-name">Shane Hart Jimenez</h4>
                        <p class="member-role">Lead Programmer</p>
                        <p class="member-desc">Leads the development team with expertise in software architecture and innovative programming solutions.</p>
                        <div class="member-footer">
                            <a href="mailto:hz202300259@wmsu.edu.ph" class="member-email" title="hz202300259@wmsu.edu.ph">
                                <i class="ri-mail-line"></i>
                                <span>hz202300259@wmsu.edu.ph</span>
                            </a>
                        </div>
                    </div>
                </div>
                <div class="member-card">
                    <div class="member-photo" style="--grad: linear-gradient(135deg,#a855f7,#7c3aed)">
                        <img src="{{ asset('img/jeff.png') }}" alt="Jeffslazir Augeight Tampus" onerror="this.style.display='none'">
                        <span class="member-initials">JA</span>
                    </div>
                    <div class="member-info">
                        <h4 class="member-name">Jeffslazir Augeight Tampus</h4>
                        <p class="member-role">Project Manager</p>
                        <p class="member-desc">Coordinates team efforts and ensures projects are delivered on time with exceptional quality.</p>
                        <div class="member-footer">
                            <a href="mailto:hz202301528@wmsu.edu.ph" class="member-email" title="hz202301528@wmsu.edu.ph">
                                <i class="ri-mail-line"></i>
                                <span>hz202301528@wmsu.edu.ph</span>
                            </a>
                        </div>
                    </div>
                </div>
                <div class="member-card">
                    <div class="member-photo" style="--grad: linear-gradient(135deg,#22c55e,#16a34a)">
                        <img src="{{ asset('img/carl2.png') }}" alt="Carl Wayne Saludo" onerror="this.style.display='none'">
                        <span class="member-initials">CW</span>
                    </div>
                    <div class="member-info">
                        <h4 class="member-name">Carl Wayne Saludo</h4>
                        <p class="member-role">UI/UX Designer</p>
                        <p class="member-desc">Creates beautiful, user-friendly experiences that delight users and drive engagement through thoughtful design.</p>
                        <div class="member-footer">
                            <a href="mailto:hz202300241@wmsu.edu.ph" class="member-email" title="hz202300241@wmsu.edu.ph">
                                <i class="ri-mail-line"></i>
                                <span>hz202300241@wmsu.edu.ph</span>
                            </a>
                        </div>
                    </div>
                </div>
                <div class="member-card">
                    <div class="member-photo" style="--grad: linear-gradient(135deg,#f97316,#ea580c)">
                        <img src="{{ asset('img/olive.png') }}" alt="Olive Faith Padios" onerror="this.style.display='none'">
                        <span class="member-initials">OF</span>
                    </div>
                    <div class="member-info">
                        <h4 class="member-name">Olive Faith Padios</h4>
                        <p class="member-role">Design Architecture</p>
                        <p class="member-desc">Architects comprehensive design systems and ensures cohesive visual identity across all products.</p>
                        <div class="member-footer">
                            <a href="mailto:hz202300486@wmsu.edu.ph" class="member-email" title="hz202300486@wmsu.edu.ph">
                                <i class="ri-mail-line"></i>
                                <span>hz202300486@wmsu.edu.ph</span>
                            </a>
                        </div>
                    </div>
                </div>
                <div class="member-card">
                    <div class="member-photo" style="--grad: linear-gradient(135deg,#06b6d4,#0891b2)">
                        <img src="{{ asset('img/emman.png') }}" alt="Emmanuelle Santos" onerror="this.style.display='none'">
                        <span class="member-initials">ES</span>
                    </div>
                    <div class="member-info">
                        <h4 class="member-name">Emmanuelle Santos</h4>
                        <p class="member-role">Data Analyst</p>
                        <p class="member-desc">Transforms complex data into actionable insights that drive informed decision-making and strategic planning.</p>
                        <div class="member-footer">
                            <a href="mailto:hz202301270@wmsu.edu.ph" class="member-email" title="hz202301270@wmsu.edu.ph">
                                <i class="ri-mail-line"></i>
                                <span>hz202301270@wmsu.edu.ph</span>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
            <div class="team-dots" id="teamDots" aria-label="Team carousel pagination"></div>

        </div>
    </section>

    <!-- Contact Us Section -->
    <section id="contact" class="contact-us py-16 md:py-20" style="background:#f8f9fb;">
        <div class="w-full px-8 md:px-16 lg:px-24 text-center">
            <img src="{{ asset('img/logo2.0.svg') }}" alt="Legatura Logo" class="contact-logo mx-auto mb-6">
            <h2 class="text-3xl md:text-4xl font-extrabold mb-4" style="color:#1a1a1a;">Contact Us</h2>
            <p class="text-base md:text-lg leading-relaxed mb-12 max-w-3xl mx-auto" style="color:#525252;">
                Get in touch with us for any inquiries or support
            </p>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 md:gap-8 px-4 md:px-8 lg:px-16 text-left">
                <div class="contact-card p-6 rounded-lg">
                    <div class="contact-header">
                        <div class="contact-icon">
                            <i class="ri-mail-line"></i>
                        </div>
                        <h4 class="text-lg md:text-xl font-bold" style="color:#1a1a1a;">Email Address</h4>
                    </div>
                    <p class="text-sm md:text-base" style="color:#404040;">
                        legatura.info.official@gmail.com
                    </p>
                </div>
                <div class="contact-card p-6 rounded-lg">
                    <div class="contact-header">
                        <div class="contact-icon">
                            <i class="ri-phone-line"></i>
                        </div>
                        <h4 class="text-lg md:text-xl font-bold" style="color:#1a1a1a;">Phone Number</h4>
                    </div>
                    <p class="text-sm md:text-base" style="color:#404040;">
                        09755924862
                    </p>
                </div>
                <a href="https://www.facebook.com/people/Legatura/61581815672869/" target="_blank" rel="noopener noreferrer" class="contact-card contact-card-link p-6 rounded-lg">
                    <div class="contact-header">
                        <div class="contact-icon">
                            <i class="ri-facebook-circle-line"></i>
                        </div>
                        <h4 class="text-lg md:text-xl font-bold" style="color:#1a1a1a;">Find Us On Facebook</h4>
                    </div>
                    <p class="text-sm md:text-base" style="color:#404040;">
                        Legatura Official Page
                    </p>
                </a>
            </div>
        </div>
    </section>

    <!-- Footer Copyright -->
    <footer class="footer-copyright">
        <div class="w-full px-8 text-center">
            <p class="text-sm md:text-base text-white/70">
                © 2026 Legatura. All rights reserved.
            </p>
        </div>
    </footer>
    </div><!-- /.main-content -->

    <!-- ── Admin Login Modal (secret access) ─────────────────────── -->
    <div id="adminLoginModal" class="admin-modal-overlay" role="dialog" aria-modal="true" aria-labelledby="adminModalTitle"
        data-auto-open="{{ (session('error') || $errors->any()) ? 'true' : 'false' }}">
        <div class="admin-modal-card">
            <button type="button" class="admin-modal-close" id="adminModalClose" aria-label="Close">
                <i class="ri-close-line"></i>
            </button>

            <div class="admin-modal-body">
                <!-- Logo -->
                <div class="admin-modal-logo-wrap">
                    <div class="admin-modal-logo-ring"></div>
                    <img src="{{ asset('img/logo2.0.svg') }}" alt="Legatura" class="admin-modal-logo" loading="eager">
                </div>

                <!-- Header -->
                <div class="admin-modal-header">
                    <h2 id="adminModalTitle" class="admin-modal-title">Hi, Welcome Back!</h2>
                    <p class="admin-modal-subtitle">Hope you're doing fine.</p>
                </div>

                <div class="admin-modal-divider"></div>

                @if(session('error'))
                    <div class="admin-alert admin-alert-error">
                        <span class="admin-alert-icon"><i class="ri-error-warning-line"></i></span>
                        <p>{{ session('error') }}</p>
                    </div>
                @endif

                <form class="admin-modal-form" method="POST" action="/admin/login" autocomplete="off">
                    @csrf
                    <div class="admin-form-group">
                        @if($errors->has('username'))
                            <span class="admin-field-message">{{ $errors->first('username') }}</span>
                        @endif
                        <label class="admin-field">
                            <span class="admin-field-icon"><i class="ri-mail-line"></i></span>
                            <input type="text" name="username" placeholder="Username or Email"
                                value="{{ old('username') }}" required autocomplete="off"
                                class="@if($errors->has('username')) admin-input-error @endif">
                        </label>
                    </div>
                    <div class="admin-form-group">
                        @if($errors->has('password'))
                            <span class="admin-field-message">{{ $errors->first('password') }}</span>
                        @endif
                        <label class="admin-field">
                            <span class="admin-field-icon"><i class="ri-lock-line"></i></span>
                            <input type="password" name="password" placeholder="Password"
                                required autocomplete="new-password"
                                class="@if($errors->has('password')) admin-input-error @endif">
                            <button type="button" class="admin-toggle-pw" aria-label="Toggle password visibility">
                                <i class="ri-eye-line"></i>
                            </button>
                        </label>
                    </div>
                    <button type="submit" class="admin-modal-btn">Login</button>
                </form>
            </div><!-- /.admin-modal-body -->
        </div>
    </div>
    

    <script src="{{ asset('js/signUp_logIN/landingPage.js') }}" defer></script>
</body>
</html>
