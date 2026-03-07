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
                <img src="{{ asset('img/logo_legatura.svg') }}" alt="Legatura">
            </div>
            <ul class="hero-nav-links">
                <li><a href="#about">About</a></li>
                <li><a href="#features">Features</a></li>
                <li><a href="#user-experience">For Users</a></li>
                <li><a href="#plans">Subscriptions</a></li>
                <li><a href="#team">Team</a></li>
                <li><a href="#contact">Contact</a></li>
            </ul>
            <a href="/intro" class="hero-nav-cta">Download App</a>
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
            <a href="/intro" class="hero-body-cta">Download App</a>
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
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 md:gap-8 lg:gap-12 px-0 md:px-8 lg:px-16">
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
        </div>
    </section>

    <!-- Dual User Experience Section -->
    <section id="user-experience" class="user-experience py-16 md:py-20">
        <div class="w-full px-8 md:px-16 lg:px-24 text-center">
            <h2 class="text-3xl md:text-4xl font-extrabold mb-4 text-white"><span style="color:#3b82f6">Property Owners</span> & <span style="color:#ec7e00">Contractors</span></h2>
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
    <section id="plans" class="plans-showcase py-16 md:py-20">
        <div class="w-full px-8 md:px-16 lg:px-24 text-center">
            <h2 class="text-3xl md:text-4xl font-extrabold mb-4 text-black"><span style="color:#3b82f6">Boost</span> and <span style="color:#ec7e00">Subscriptions</span></h2>
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
                    </ul>

                </div>
            </div>
        </div>
    </section>

    <!-- Video Demo Section -->
    <section id="video-demo" class="video-demo py-16 md:py-20">
        <div class="w-full px-8 md:px-16 lg:px-24 text-center">
            <h2 class="text-3xl md:text-4xl font-extrabold mb-4 text-white">Why Choose Legatura?</h2>
            <p class="text-base md:text-lg leading-relaxed mb-12 max-w-3xl mx-auto text-white/90">
                Watch how Legatura streamlines construction project management from posting projects and receiving competitive bids to tracking progress in real time and ensuring every milestone is paid securely
            </p>
            <div class="video-frame mx-auto">
                <iframe
                    src="https://geo.dailymotion.com/player.html?video=xa1gf72&related=0&queue-autoplay-next=0&queue-enable=0"
                    style="position:absolute;inset:0;width:100%;height:100%;border:none;border-radius:inherit;"
                    title="Legatura Demo — Dailymotion"
                    allow="web-share; fullscreen"
                    allowfullscreen
                ></iframe>
            </div>
        </div>
    </section>

    <!-- Meet Our Team Section -->
    <section id="team" class="team-section">
        <div class="team-inner">

            <!-- Section Header -->
            <div class="team-header">
                <h2 class="team-section-title"><span style="color:#3b82f6">Meet</span> Our <span class="team-title-accent">Team</span></h2>
                <p class="team-section-sub">The passionate people behind Legatura, dedicated to transforming how construction projects are managed.</p>
            </div>

            <!-- Member Cards -->
            <div class="members-grid">
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

        </div>
    </section>

    <!-- Contact Us Section -->
    <section id="contact" class="contact-us py-16 md:py-20">
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
    </div><!-- /.main-content -->

    <script>
        // Navbar scroll effect
        const nav = document.querySelector('.hero-nav');
        window.addEventListener('scroll', () => {
            nav.classList.toggle('scrolled', window.scrollY > 40);
        });

        // Hamburger toggle
        const hamburger = document.getElementById('navHamburger');
        const mobileMenu = document.getElementById('navMobileMenu');
        const mobileClose = document.getElementById('navMobileClose');
        let scrollY = 0;

        function openMenu() {
            scrollY = window.scrollY;
            document.body.style.position = 'fixed';
            document.body.style.top = `-${scrollY}px`;
            document.body.style.width = '100%';
            document.body.style.overflow = 'hidden';
            document.documentElement.style.overflow = 'hidden';
            mobileMenu.classList.add('open');
            hamburger.classList.add('open');
        }
        function closeMenu() {
            mobileMenu.classList.remove('open');
            hamburger.classList.remove('open');
            document.body.style.position = '';
            document.body.style.top = '';
            document.body.style.width = '';
            document.body.style.overflow = '';
            document.documentElement.style.overflow = '';
            window.scrollTo(0, scrollY);
        }

        if (hamburger && mobileMenu) {
            hamburger.addEventListener('click', openMenu);
        }
        if (mobileClose) {
            mobileClose.addEventListener('click', closeMenu);
        }
        if (mobileMenu) {
            mobileMenu.querySelectorAll('a').forEach(link => {
                link.addEventListener('click', closeMenu);
            });
        }

        // Parallax tilt on hero background image following cursor
        const hero = document.querySelector('.hero');
        const heroImg = document.querySelector('.hero-image img');
        if (hero && heroImg) {
            hero.addEventListener('mousemove', (e) => {
                const rect = hero.getBoundingClientRect();
                const x = (e.clientX - rect.left) / rect.width - 0.5;  // -0.5 to 0.5
                const y = (e.clientY - rect.top) / rect.height - 0.5;
                heroImg.style.transform = `scale(1.06) translate(${x * -18}px, ${y * -12}px)`;
            });
            hero.addEventListener('mouseleave', () => {
                heroImg.style.transform = 'scale(1.06) translate(0px, 0px)';
            });
        }

        // ── Scroll Fade-in / Fade-out Animations ──────────────────────────
        (function () {
            // --- About Section ---
            document.querySelectorAll('#about h2, #about > div > p').forEach(el => el.classList.add('scroll-fade'));
            document.querySelectorAll('#about .about-card').forEach((el, i) => {
                el.classList.add(i === 0 ? 'scroll-fade-left' : 'scroll-fade-right');
                el.classList.add('scroll-delay-' + (i + 1));
            });

            // --- User Experience Section ---
            document.querySelectorAll('#user-experience h2, #user-experience > div > p').forEach(el => el.classList.add('scroll-fade'));
            document.querySelectorAll('#user-experience .owner-card').forEach(el => el.classList.add('scroll-fade-left'));
            document.querySelectorAll('#user-experience .separator-container').forEach(el => el.classList.add('scroll-fade-scale'));
            document.querySelectorAll('#user-experience .contractor-card').forEach(el => el.classList.add('scroll-fade-right'));

            // --- Features Section ---
            document.querySelectorAll('#features h2, #features > div > p').forEach(el => el.classList.add('scroll-fade'));
            document.querySelectorAll('#features .feature-card').forEach((el, i) => {
                el.classList.add('scroll-fade');
                el.classList.add('scroll-delay-' + (i + 1));
            });

            // --- Plans Section ---
            document.querySelectorAll('#plans h2, #plans > div > p').forEach(el => el.classList.add('scroll-fade'));
            document.querySelectorAll('#plans .boost-panel').forEach(el => el.classList.add('scroll-fade-left'));
            document.querySelectorAll('#plans .subscription-panel').forEach(el => el.classList.add('scroll-fade-right'));

            // --- Video Demo Section ---
            document.querySelectorAll('#video-demo h2, #video-demo > div > p').forEach(el => el.classList.add('scroll-fade'));
            document.querySelectorAll('#video-demo .video-frame').forEach(el => el.classList.add('scroll-fade-scale'));

            // --- Team Section ---
            document.querySelectorAll('#team .team-header').forEach(el => el.classList.add('scroll-fade'));
            document.querySelectorAll('#team .member-card').forEach((el, i) => {
                el.classList.add('scroll-fade');
                el.classList.add('scroll-delay-' + (i + 1));
            });

            // --- Contact Section ---
            document.querySelectorAll('#contact .contact-logo').forEach(el => el.classList.add('scroll-fade-scale'));
            document.querySelectorAll('#contact h2, #contact > div > p').forEach(el => el.classList.add('scroll-fade'));
            document.querySelectorAll('#contact .contact-card').forEach((el, i) => {
                el.classList.add('scroll-fade');
                el.classList.add('scroll-delay-' + (i + 1));
            });

            // --- Footer (no animation) ---
            // Footer is always visible, no scroll animation

            // Collect every element that has a scroll-fade class
            const allFadeEls = document.querySelectorAll(
                '.scroll-fade, .scroll-fade-left, .scroll-fade-right, .scroll-fade-scale'
            );

            // IntersectionObserver — toggles .visible on enter/leave
            const observer = new IntersectionObserver((entries) => {
                entries.forEach(entry => {
                    if (entry.isIntersecting) {
                        entry.target.classList.add('visible');
                    } else {
                        entry.target.classList.remove('visible');
                    }
                });
            }, {
                threshold: 0.1,
                rootMargin: '0px 0px -60px 0px'
            });

            allFadeEls.forEach(el => observer.observe(el));
        })();
        // ──────────────────────────────────────────────────────────────────
    </script>
</body>
</html>
