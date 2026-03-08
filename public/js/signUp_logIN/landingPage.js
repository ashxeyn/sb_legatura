// Navbar scroll effect
const nav = document.querySelector('.hero-nav');
window.addEventListener('scroll', () => {
    nav.classList.toggle('scrolled', window.scrollY > 40);
});

// Dailymotion video auto-loop via postMessage
(function() {
    const iframe = document.getElementById('legatura-video');
    let playerReady = false;

    window.addEventListener('message', function(event) {
        if (!event.origin.includes('dailymotion.com')) return;
        
        try {
            const data = typeof event.data === 'string' ? JSON.parse(event.data) : event.data;
            
            // When player is ready, subscribe to ended event
            if (data.event === 'apiready') {
                playerReady = true;
                iframe.contentWindow.postMessage(JSON.stringify({
                    command: 'addEventListener',
                    parameters: ['ended']
                }), '*');
            }
            
            // When video ends, restart it
            if (playerReady && (data.event === 'ended' || data.event === 'video_end')) {
                setTimeout(() => {
                    iframe.contentWindow.postMessage(JSON.stringify({
                        command: 'seek',
                        parameters: [0]
                    }), '*');
                    iframe.contentWindow.postMessage(JSON.stringify({
                        command: 'play'
                    }), '*');
                }, 100);
            }
        } catch(e) {
            console.log('Dailymotion event error:', e);
        }
    });
})();

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
function closeMenu(restoreScroll = true) {
    mobileMenu.classList.remove('open');
    hamburger.classList.remove('open');
    document.body.style.position = '';
    document.body.style.top = '';
    document.body.style.width = '';
    document.body.style.overflow = '';
    document.documentElement.style.overflow = '';
    if (restoreScroll) {
        window.scrollTo(0, scrollY);
    }
}

if (hamburger && mobileMenu) {
    hamburger.addEventListener('click', openMenu);
}
if (mobileClose) {
    mobileClose.addEventListener('click', closeMenu);
}
if (mobileMenu) {
    mobileMenu.querySelectorAll('a').forEach(link => {
        link.addEventListener('click', (e) => {
            const href = link.getAttribute('href');
            if (href && href.startsWith('#')) {
                e.preventDefault();
                // Close menu and unfix body first
                mobileMenu.classList.remove('open');
                hamburger.classList.remove('open');
                document.body.style.position = '';
                document.body.style.top = '';
                document.body.style.width = '';
                document.body.style.overflow = '';
                document.documentElement.style.overflow = '';
                // Wait one frame for layout to settle, then scroll to target
                requestAnimationFrame(() => {
                    const target = document.querySelector(href);
                    if (target) {
                        target.scrollIntoView({ behavior: 'smooth' });
                    }
                });
            } else {
                closeMenu(false);
            }
        });
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

// ── About carousel (mobile only) ────────────────────────────────────
(function () {
    const track = document.getElementById('aboutTrack');
    const dotsWrap = document.getElementById('aboutDots');
    if (!track || !dotsWrap) return;

    const slides = Array.from(track.querySelectorAll('.about-card'));
    if (!slides.length) return;

    const isMobile = () => window.matchMedia('(max-width: 768px)').matches;

    dotsWrap.innerHTML = slides.map((_, idx) => {
        const active = idx === 0 ? ' is-active' : '';
        return `<button type="button" class="about-dot${active}" data-index="${idx}" aria-label="Show about card ${idx + 1}"></button>`;
    }).join('');

    const dots = Array.from(dotsWrap.querySelectorAll('.about-dot'));

    const setActive = (idx) => {
        dots.forEach((dot, i) => dot.classList.toggle('is-active', i === idx));
    };

    const syncFromScroll = () => {
        if (!isMobile()) return;
        const center = track.scrollLeft + track.clientWidth / 2;
        let best = 0;
        let min = Infinity;

        slides.forEach((slide, i) => {
            const dist = Math.abs((slide.offsetLeft + slide.offsetWidth / 2) - center);
            if (dist < min) {
                min = dist;
                best = i;
            }
        });
        setActive(best);
    };

    dots.forEach((dot, idx) => {
        dot.addEventListener('click', () => {
            if (!isMobile()) return;
            slides[idx].scrollIntoView({ behavior: 'smooth', inline: 'center', block: 'nearest' });
            setActive(idx);
        });
    });

    track.addEventListener('scroll', syncFromScroll, { passive: true });
    window.addEventListener('resize', syncFromScroll);
    syncFromScroll();
})();
// ─────────────────────────────────────────────────────────────────────

// ── Features carousel (mobile only) ─────────────────────────────────
(function () {
    const track = document.getElementById('featuresTrack');
    const dotsWrap = document.getElementById('featuresDots');
    if (!track || !dotsWrap) return;

    const slides = Array.from(track.querySelectorAll('.feature-card'));
    if (!slides.length) return;

    const isMobile = () => window.matchMedia('(max-width: 768px)').matches;

    dotsWrap.innerHTML = slides.map((_, idx) => {
        const active = idx === 0 ? ' is-active' : '';
        return `<button type="button" class="features-dot${active}" data-index="${idx}" aria-label="Show feature ${idx + 1}"></button>`;
    }).join('');

    const dots = Array.from(dotsWrap.querySelectorAll('.features-dot'));

    const setActive = (idx) => {
        dots.forEach((dot, i) => dot.classList.toggle('is-active', i === idx));
    };

    const syncFromScroll = () => {
        if (!isMobile()) return;
        const center = track.scrollLeft + track.clientWidth / 2;
        let best = 0;
        let min = Infinity;
        slides.forEach((slide, i) => {
            const dist = Math.abs((slide.offsetLeft + slide.offsetWidth / 2) - center);
            if (dist < min) {
                min = dist;
                best = i;
            }
        });
        setActive(best);
    };

    dots.forEach((dot, idx) => {
        dot.addEventListener('click', () => {
            if (!isMobile()) return;
            slides[idx].scrollIntoView({ behavior: 'smooth', inline: 'center', block: 'nearest' });
            setActive(idx);
        });
    });

    track.addEventListener('scroll', syncFromScroll, { passive: true });
    window.addEventListener('resize', syncFromScroll);
    syncFromScroll();
})();
// ─────────────────────────────────────────────────────────────────────

// ── Plans carousel (mobile only) ────────────────────────────────────
(function () {
    const track = document.getElementById('plansTrack');
    const dotsWrap = document.getElementById('plansDots');
    if (!track || !dotsWrap) return;

    const slides = Array.from(track.querySelectorAll('[data-plan-slide]'));
    const dots   = Array.from(dotsWrap.querySelectorAll('.plans-dot'));

    const isMobile = () => window.matchMedia('(max-width: 768px)').matches;

    const setActive = (idx) => {
        dots.forEach((d, i) => d.classList.toggle('is-active', i === idx));
    };

    const syncFromScroll = () => {
        if (!isMobile()) return;
        const center = track.scrollLeft + track.clientWidth / 2;
        let best = 0, min = Infinity;
        slides.forEach((slide, i) => {
            const dist = Math.abs((slide.offsetLeft + slide.offsetWidth / 2) - center);
            if (dist < min) { min = dist; best = i; }
        });
        setActive(best);
    };

    dots.forEach((dot, idx) => {
        dot.addEventListener('click', () => {
            if (!isMobile()) return;
            slides[idx].scrollIntoView({ behavior: 'smooth', inline: 'center', block: 'nearest' });
            setActive(idx);
        });
    });

    track.addEventListener('scroll', syncFromScroll, { passive: true });
    window.addEventListener('resize', syncFromScroll);
    syncFromScroll();
})();
// ─────────────────────────────────────────────────────────────────────

// ── Team carousel (mobile only) ─────────────────────────────────────
(function () {
    const track = document.getElementById('teamTrack');
    const dotsWrap = document.getElementById('teamDots');
    if (!track || !dotsWrap) return;

    const slides = Array.from(track.querySelectorAll('.member-card'));
    if (!slides.length) return;

    const isMobile = () => window.matchMedia('(max-width: 768px)').matches;

    dotsWrap.innerHTML = slides.map((_, idx) => {
        const active = idx === 0 ? ' is-active' : '';
        return `<button type="button" class="team-dot${active}" data-index="${idx}" aria-label="Show team member ${idx + 1}"></button>`;
    }).join('');

    const dots = Array.from(dotsWrap.querySelectorAll('.team-dot'));

    const setActive = (idx) => {
        dots.forEach((dot, i) => dot.classList.toggle('is-active', i === idx));
    };

    const syncFromScroll = () => {
        if (!isMobile()) return;
        const center = track.scrollLeft + track.clientWidth / 2;
        let best = 0;
        let min = Infinity;
        slides.forEach((slide, i) => {
            const dist = Math.abs((slide.offsetLeft + slide.offsetWidth / 2) - center);
            if (dist < min) {
                min = dist;
                best = i;
            }
        });
        setActive(best);
    };

    dots.forEach((dot, idx) => {
        dot.addEventListener('click', () => {
            if (!isMobile()) return;
            slides[idx].scrollIntoView({ behavior: 'smooth', inline: 'center', block: 'nearest' });
            setActive(idx);
        });
    });

    track.addEventListener('scroll', syncFromScroll, { passive: true });
    window.addEventListener('resize', syncFromScroll);
    syncFromScroll();
})();
// ─────────────────────────────────────────────────────────────────────

// ── Everything You Need carousel (mobile only) ──────────────────────
(function () {
    const track = document.getElementById('featuresHighlightTrack');
    const dotsWrap = document.getElementById('featuresHighlightDots');
    if (!track || !dotsWrap) return;

    const slides = Array.from(track.querySelectorAll('.feature-card'));
    if (!slides.length) return;

    const isMobile = () => window.matchMedia('(max-width: 768px)').matches;

    dotsWrap.innerHTML = slides.map((_, idx) => {
        const active = idx === 0 ? ' is-active' : '';
        return `<button type="button" class="features-highlight-dot${active}" data-index="${idx}" aria-label="Show feature ${idx + 1}"></button>`;
    }).join('');

    const dots = Array.from(dotsWrap.querySelectorAll('.features-highlight-dot'));

    const setActive = (idx) => {
        dots.forEach((dot, i) => dot.classList.toggle('is-active', i === idx));
    };

    const syncFromScroll = () => {
        if (!isMobile()) return;
        const center = track.scrollLeft + track.clientWidth / 2;
        let best = 0;
        let min = Infinity;
        slides.forEach((slide, i) => {
            const dist = Math.abs((slide.offsetLeft + slide.offsetWidth / 2) - center);
            if (dist < min) {
                min = dist;
                best = i;
            }
        });
        setActive(best);
    };

    dots.forEach((dot, idx) => {
        dot.addEventListener('click', () => {
            if (!isMobile()) return;
            slides[idx].scrollIntoView({ behavior: 'smooth', inline: 'center', block: 'nearest' });
            setActive(idx);
        });
    });

    track.addEventListener('scroll', syncFromScroll, { passive: true });
    window.addEventListener('resize', syncFromScroll);
    syncFromScroll();
})();
// ─────────────────────────────────────────────────────────────────────

// ── Subscription Tier Selector ──────────────────────────────────────
(function () {
    const tierCards = document.querySelectorAll('.tier-card');
    const featuresList = document.getElementById('tierFeaturesList');
    
    if (!tierCards.length || !featuresList) return;

    const tierFeatures = {
        gold: [
            'All Silver tier features included',
            'Unlimited bids per month',
            'AI-powered weather delay predictions',
            'Basic project listing and milestone tracking'
        ],
        silver: [
            'All Bronze tier features included',
            'Up to 25 bids per month',
            'AI-powered weather delay predictions',
            'Basic project listing and milestone tracking'
        ],
        bronze: [
            'Basic project listing',
            'Milestone tracking',
            'Up to 10 bids per month'
        ]
    };

    tierCards.forEach(card => {
        card.addEventListener('click', () => {
            const tier = card.dataset.tier;
            
            // Update selected state
            tierCards.forEach(c => c.classList.remove('is-selected'));
            card.classList.add('is-selected');
            
            // Update features list
            const features = tierFeatures[tier];
            if (features) {
                featuresList.innerHTML = features.map(feature => 
                    `<li><i class="fi fi-rr-check-circle"></i> ${feature}</li>`
                ).join('');
            }
        });
    });
})();
// ─────────────────────────────────────────────────────────────────────

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

    // --- Features Highlight Section (Everything You Need) ---
    document.querySelectorAll('#features-highlight h2, #features-highlight > div > p').forEach(el => el.classList.add('scroll-fade'));
    document.querySelectorAll('#features-highlight .feature-card').forEach((el, i) => {
        el.classList.add('scroll-fade');
        el.classList.add('scroll-delay-' + ((i % 6) + 1));
    });

    // --- Plans Section ---
    document.querySelectorAll('#plans h2, #plans > div > p').forEach(el => el.classList.add('scroll-fade'));
    document.querySelectorAll('#plans .boost-panel').forEach(el => el.classList.add('scroll-fade-left'));
    document.querySelectorAll('#plans .subscription-panel').forEach(el => el.classList.add('scroll-fade-right'));

    // --- Video Demo Section ---
    document.querySelectorAll('#video-demo h2').forEach(el => el.classList.add('scroll-fade'));
    document.querySelectorAll('#video-demo .text-left h1').forEach(el => {
        el.classList.add('scroll-fade-left');
        el.classList.add('scroll-delay-1');
    });
    document.querySelectorAll('#video-demo .text-left p').forEach(el => {
        el.classList.add('scroll-fade-left');
        el.classList.add('scroll-delay-2');
    });
    document.querySelectorAll('#video-demo .flex img').forEach(el => {
        el.classList.add('scroll-fade-right');
        el.classList.add('scroll-delay-1');
    });
    document.querySelectorAll('#video-demo .separator-container').forEach(el => {
        el.classList.add('scroll-fade-scale');
        el.classList.add('scroll-delay-2');
    });
    document.querySelectorAll('#video-demo .video-frame').forEach(el => el.classList.add('scroll-fade-scale'));

    // --- Team Section ---
    document.querySelectorAll('#team .team-header').forEach(el => el.classList.add('scroll-fade'));
    document.querySelectorAll('#team .member-card').forEach((el, i) => {
        el.classList.add('scroll-fade');
        el.classList.add('scroll-delay-' + (i + 1));
    });

    // --- MVP Showcase Section ---
    document.querySelectorAll('#mvp .text-center h2').forEach(el => el.classList.add('scroll-fade'));
    document.querySelectorAll('#mvp .text-center > p').forEach(el => {
        el.classList.add('scroll-fade');
        el.classList.add('scroll-delay-1');
    });
    document.querySelectorAll('#mvp .text-center .max-w-2xl').forEach(el => {
        el.classList.add('scroll-fade');
        el.classList.add('scroll-delay-2');
    });
    document.querySelectorAll('#mvp .mvp-text-col').forEach(el => {
        const isRight = el.classList.contains('order-1'); // order-1 lg:order-2 = right side on desktop
        el.classList.add(isRight ? 'scroll-fade-right' : 'scroll-fade-left');
        el.classList.add('scroll-delay-1');
    });
    document.querySelectorAll('#mvp .mvp-img-col').forEach(el => {
        const isLeft = el.classList.contains('order-2'); // order-2 lg:order-1 = left side on desktop
        el.classList.add(isLeft ? 'scroll-fade-left' : 'scroll-fade-right');
        el.classList.add('scroll-delay-2');
    });
    document.querySelectorAll('#mvp .separator-container').forEach(el => el.classList.add('scroll-fade-scale'));

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

// ── Secret Admin Access (desktop only) ────────────────────────────
(function () {
    const isDesktop = () => window.matchMedia('(hover: hover) and (pointer: fine)').matches;

    const overlay  = document.getElementById('adminLoginModal');
    const closeBtn = document.getElementById('adminModalClose');

    function openAdminModal() {
        if (!overlay) return;
        overlay.classList.add('is-open');
        document.body.style.overflow = 'hidden';
        document.documentElement.style.overflow = 'hidden';
        const firstInput = overlay.querySelector('input');
        if (firstInput) setTimeout(() => firstInput.focus(), 280);
    }

    function closeAdminModal() {
        if (!overlay) return;
        overlay.classList.remove('is-open');
        document.body.style.overflow = '';
        document.documentElement.style.overflow = '';
    }

    // Auto-open if the page was redirected back with errors
    if (overlay && overlay.dataset.autoOpen === 'true') {
        openAdminModal();
    }

    // Close on backdrop click
    if (overlay) {
        overlay.addEventListener('click', (e) => {
            if (e.target === overlay) closeAdminModal();
        });
    }

    // Close button
    if (closeBtn) {
        closeBtn.addEventListener('click', closeAdminModal);
    }

    // Close on Escape
    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape' && overlay && overlay.classList.contains('is-open')) {
            closeAdminModal();
        }
    });

    // Password toggle inside modal
    if (overlay) {
        overlay.querySelectorAll('.admin-toggle-pw').forEach(btn => {
            btn.addEventListener('click', () => {
                const input = btn.closest('.admin-field').querySelector('input');
                const icon  = btn.querySelector('i');
                if (!input || !icon) return;
                if (input.type === 'password') {
                    input.type = 'text';
                    icon.className = 'ri-eye-off-line';
                } else {
                    input.type = 'password';
                    icon.className = 'ri-eye-line';
                }
            });
        });
    }

    // Method 1: Logo multi-click (5 times within 3 seconds)
    const logoEl = document.querySelector('.hero-nav-logo');
    let clickCount = 0;
    let clickTimer = null;

    if (logoEl) {
        logoEl.addEventListener('click', () => {
            if (!isDesktop()) return;

            clickCount++;

            if (clickCount === 5) {
                clickCount = 0;
                clearTimeout(clickTimer);
                openAdminModal();
                return;
            }

            clearTimeout(clickTimer);
            clickTimer = setTimeout(() => {
                clickCount = 0;
            }, 3000);
        });
    }

    // Method 2: Keyboard shortcut Ctrl+Shift+A (desktop only)
    document.addEventListener('keydown', (e) => {
        if (!isDesktop()) return;
        if (e.ctrlKey && e.shiftKey && e.key === 'A') {
            openAdminModal();
        }
    });
})();
// ──────────────────────────────────────────────────────────────────
