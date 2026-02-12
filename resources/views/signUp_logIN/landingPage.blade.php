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
    <!-- Get Started Button -->
    <a href="/intro" class="hero-cta-button">
        <span>Get Started</span>
    </a>

   <section class="hero">
        <div class="hero-image">
            <img src="{{ asset('img/landingpage.png') }}" alt="Construction and building projects">
        </div>
        <div class="hero-logo">
            <img src="{{ asset('img/logo_legatura.svg') }}" alt="Legatura Logo">
        </div>
    </section>

    <!-- About Section -->
    <section class="about py-16 md:py-20">
        <div class="w-full px-8 md:px-16 lg:px-24 text-center">
            <h2 class="text-3xl md:text-4xl font-extrabold mb-6 text-white">About Legatura</h2>
            <p class="text-base md:text-lg leading-relaxed mb-12 max-w-5xl mx-auto text-white/90">
                Legatura is a comprehensive platform that bridges the gap between property owners and skilled contractors.
                It serves as a vital connection hub for construction projects, streamlining the bidding process,
                facilitating transparent communication, and ensuring quality project delivery. Committed to revolutionizing
                the construction industry, Legatura provides tools for project management, contractor verification, and
                competitive bidding that enhance efficiency, trust, and collaboration. Through innovative technology and
                active support, it strives to create a dynamic and reliable ecosystem, ensuring that every project is
                completed with excellence and every stakeholder's needs are met.
            </p>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-8 md:gap-12 px-4 md:px-8 lg:px-16">
                <div class="about-card p-8 rounded-lg">
                    <h4 class="text-xl md:text-2xl font-bold mb-4 text-white">Empowering Project Success & Quality</h4>
                    <p class="text-sm md:text-base leading-relaxed text-white/85">
                        Dedicated to fostering a transparent and efficient construction ecosystem, Legatura organizes
                        project workflows that promote quality, accountability, and professional excellence. Through
                        advanced bidding systems, contractor verification, and real-time project tracking, it enhances
                        stakeholder engagement, strengthens the construction community, and contributes to the continuous
                        improvement of the building experience.
                    </p>
                </div>
                <div class="about-card p-8 rounded-lg">
                    <h4 class="text-xl md:text-2xl font-bold mb-4 text-white">Enhancing Trust & Collaboration</h4>
                    <p class="text-sm md:text-base leading-relaxed text-white/85">
                        With a strong emphasis on transparency, collaboration, and industry development, Legatura serves
                        as a bridge between property owners and contractors. It ensures that project requirements are
                        clearly communicated, promotes a culture of trust and reliability, and helps shape a forward-thinking
                        construction environment where quality craftsmanship and client satisfaction are paramount.
                    </p>
                </div>
            </div>
        </div>
    </section>

    <!-- Dual User Experience Section -->
    <section class="user-experience py-16 md:py-20">
        <div class="w-full px-8 md:px-16 lg:px-24 text-center">
            <h2 class="text-3xl md:text-4xl font-extrabold mb-4 text-white">Property Owners & Contractors</h2>
            <p class="text-base md:text-lg leading-relaxed mb-12 max-w-3xl mx-auto text-white/90">
                Discover how Legatura empowers both sides of construction projects
            </p>
            <div class="grid grid-cols-1 gap-8 md:gap-12 px-4 md:px-8 lg:px-16">
                <!-- Property Owner Card -->
                <div class="user-card owner-card p-8 rounded-lg">
                    <div class="card-icon mb-6"></div>
                    <ul class="text-left space-y-4">
                        <li class="flex items-start">
                            <i class="fi fi-rr-check-circle owner-check"></i>
                            <span class="owner-text text-base">Post construction projects with detailed specifications and timelines</span>
                        </li>
                        <li class="flex items-start">
                            <i class="fi fi-rr-check-circle owner-check"></i>
                            <span class="owner-text text-base">Receive competitive bids from verified and qualified contractors</span>
                        </li>
                        <li class="flex items-start">
                            <i class="fi fi-rr-check-circle owner-check"></i>
                            <span class="owner-text text-base">Track project progress in real-time with transparent updates</span>
                        </li>
                        <li class="flex items-start">
                            <i class="fi fi-rr-check-circle owner-check"></i>
                            <span class="owner-text text-base">Verify contractor credentials, experience, and customer reviews</span>
                        </li>
                        <li class="flex items-start">
                            <i class="fi fi-rr-check-circle owner-check"></i>
                            <span class="owner-text text-base">Secure and transparent payment handling throughout the project</span>
                        </li>
                    </ul>
                </div>

                <!-- Modern Separator Line -->
                <div class="separator-container">
                    <div class="modern-separator"></div>
                </div>

                <!-- Contractor Card -->
                <div class="user-card contractor-card p-8 rounded-lg">
                    <div class="card-icon mb-6"></div>
                    <ul class="text-left space-y-4">
                        <li class="flex items-start">
                            <i class="fi fi-rr-check-circle contractor-check"></i>
                            <span class="contractor-text text-base">Discover and access qualified construction projects in your area</span>
                        </li>
                        <li class="flex items-start">
                            <i class="fi fi-rr-check-circle contractor-check"></i>
                            <span class="contractor-text text-base">Submit competitive bids and showcase your expertise and capabilities</span>
                        </li>
                        <li class="flex items-start">
                            <i class="fi fi-rr-check-circle contractor-check"></i>
                            <span class="contractor-text text-base">Build your professional reputation through quality work and client reviews</span>
                        </li>
                        <li class="flex items-start">
                            <i class="fi fi-rr-check-circle contractor-check"></i>
                            <span class="contractor-text text-base">Manage multiple projects efficiently with integrated project tools</span>
                        </li>
                        <li class="flex items-start">
                            <i class="fi fi-rr-check-circle contractor-check"></i>
                            <span class="contractor-text text-base">Ensure reliable and timely payment for completed work</span>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </section>

    <!-- Features Section -->
    <section class="features py-16 md:py-20">
        <div class="w-full px-8 md:px-16 lg:px-24 text-center">
            <h2 class="text-3xl md:text-4xl font-extrabold mb-4 text-white">Legatura Features</h2>
            <p class="text-base md:text-lg leading-relaxed mb-12 max-w-3xl mx-auto text-white/90">
                Tools designed to keep every project clear, competitive, and on track
            </p>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 md:gap-8 px-4 md:px-8 lg:px-16 text-left">
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
        </div>
    </section>

    <!-- Boosting & Subscription Section -->
    <section class="plans-showcase py-16 md:py-20">
        <div class="w-full px-8 md:px-16 lg:px-24 text-center">
            <h2 class="text-3xl md:text-4xl font-extrabold mb-4 text-black">Boosts and Subscriptions</h2>
            <p class="text-base md:text-lg leading-relaxed mb-12 max-w-3xl mx-auto text-black/80">
                Property owners can boost project posts, while contractors unlock premium growth tools.
            </p>
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 md:gap-12 px-4 md:px-8 lg:px-16 text-left">
                <div class="plan-panel boost-panel p-6 md:p-8 rounded-lg">
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
                            <div class="panel-card-emoji">🔥</div>
                            <div>
                                <div class="panel-card-title">Boost Your Project</div>
                                <div class="panel-card-meta">Reach 10,000+ contractors</div>
                            </div>
                            <span class="panel-card-price">₱49<span style="font-size: 0.75em; font-weight: 400; opacity: 0.8;">/project</span></span>
                        </button>
                    </div>
                    <ul class="panel-list">
                        <li><i class="fi fi-rr-check-circle"></i> Instant visibility boost for your project</li>
                        <li><i class="fi fi-rr-check-circle"></i> Attract more qualified contractor bids</li>
                        <li><i class="fi fi-rr-check-circle"></i> Active boost duration of 3 days</li>
                        <li><i class="fi fi-rr-check-circle"></i> Track engagement and bid metrics</li>
                    </ul>
                </div>

                <div class="plan-panel subscription-panel p-6 md:p-8 rounded-lg">
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
                        <button class="panel-card" type="button">
                            <div class="panel-card-emoji">👑</div>
                            <div>
                                <div class="panel-card-title">Gold Tier</div>
                                <div class="panel-card-meta">AI analytics + unlimited bids</div>
                            </div>
                            <span class="panel-card-price">₱1,999</span>
                        </button>
                        <button class="panel-card" type="button">
                            <div class="panel-card-emoji">🥈</div>
                            <div>
                                <div class="panel-card-title">Silver Tier</div>
                                <div class="panel-card-meta">Boost bids + priority visibility</div>
                            </div>
                            <span class="panel-card-price">₱1,499</span>
                        </button>
                        <button class="panel-card" type="button">
                            <div class="panel-card-emoji">🥉</div>
                            <div>
                                <div class="panel-card-title">Bronze Tier</div>
                                <div class="panel-card-meta">Core bidding essentials</div>
                            </div>
                            <span class="panel-card-price">₱999</span>
                        </button>
                    </div>
                    <ul class="panel-list">
                        <li><i class="fi fi-rr-check-circle"></i> Unlock AI-driven analytics</li>
                        <li><i class="fi fi-rr-check-circle"></i> Unlimited bids and boosted reach</li>
                        <li><i class="fi fi-rr-check-circle"></i> 7-day free trial on sign up</li>
                    </ul>

                </div>
            </div>
        </div>
    </section>

    <!-- Contact Us Section -->
    <section class="contact-us py-16 md:py-20">
        <div class="w-full px-8 md:px-16 lg:px-24 text-center">
            <img src="{{ asset('img/logo2.0.svg') }}" alt="Legatura Logo" class="contact-logo mx-auto mb-6">
            <h2 class="text-3xl md:text-4xl font-extrabold mb-4 text-white">Contact Us</h2>
            <p class="text-base md:text-lg leading-relaxed mb-12 max-w-3xl mx-auto text-white/90">
                Get in touch with us for any inquiries or support
            </p>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 md:gap-8 px-4 md:px-8 lg:px-16 text-left">
                <div class="contact-card p-6 rounded-lg">
                    <div class="contact-header">
                        <div class="contact-icon">
                            <i class="ri-mail-line"></i>
                        </div>
                        <h4 class="text-lg md:text-xl font-bold text-white">Email Address</h4>
                    </div>
                    <p class="text-sm md:text-base text-white/85">
                        legatura.info.official@gmail.com
                    </p>
                </div>
                <div class="contact-card p-6 rounded-lg">
                    <div class="contact-header">
                        <div class="contact-icon">
                            <i class="ri-phone-line"></i>
                        </div>
                        <h4 class="text-lg md:text-xl font-bold text-white">Phone Number</h4>
                    </div>
                    <p class="text-sm md:text-base text-white/85">
                        09755924862
                    </p>
                </div>
                <a href="https://www.facebook.com/people/Legatura/61581815672869/" target="_blank" rel="noopener noreferrer" class="contact-card contact-card-link p-6 rounded-lg">
                    <div class="contact-header">
                        <div class="contact-icon">
                            <i class="ri-facebook-circle-line"></i>
                        </div>
                        <h4 class="text-lg md:text-xl font-bold text-white">Find Us On Facebook</h4>
                    </div>
                    <p class="text-sm md:text-base text-white/85">
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

</body>
</html>
