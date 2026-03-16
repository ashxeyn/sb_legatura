// Navbar scroll effect
const nav = document.querySelector('.hero-nav');
window.addEventListener('scroll', () => {
    nav.classList.toggle('scrolled', window.scrollY > 40);
});

// Dailymotion video controls via postMessage
(function () {
    const iframe = document.getElementById('legatura-video');
    if (!iframe) return;

    let playerReady = false;

    const pauseVideo = () => {
        if (!playerReady || !iframe.contentWindow) return;
        iframe.contentWindow.postMessage(JSON.stringify({ command: 'pause' }), '*');
    };

    window.addEventListener('message', (event) => {
        if (!event.origin.includes('dailymotion.com')) return;

        try {
            const data = typeof event.data === 'string' ? JSON.parse(event.data) : event.data;
            if (data && data.event === 'apiready') {
                playerReady = true;
            }
        } catch (e) {
            console.log('Dailymotion event error:', e);
        }
    });

    // Stop playback whenever the user scrolls up or down.
    let scrollTicking = false;
    window.addEventListener('scroll', () => {
        if (scrollTicking) return;
        scrollTicking = true;

        window.requestAnimationFrame(() => {
            pauseVideo();
            scrollTicking = false;
        });
    }, { passive: true });

    // Also stop playback when the video section leaves the viewport.
    const videoSection = document.getElementById('video-demo');
    if (videoSection && 'IntersectionObserver' in window) {
        const observer = new IntersectionObserver((entries) => {
            entries.forEach((entry) => {
                if (!entry.isIntersecting) {
                    pauseVideo();
                }
            });
        }, {
            threshold: 0.2
        });

        observer.observe(videoSection);
    }
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

    // Expand wrapper children so each feature-card is an individual slide
    const slides = [];
    Array.from(track.children).forEach(child => {
        if (child.classList.contains('feature-card')) {
            slides.push(child);
        } else {
            Array.from(child.querySelectorAll('.feature-card')).forEach(c => slides.push(c));
        }
    });
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

    // Expand wrapper children so each feature-card is an individual slide
    const slides = [];
    Array.from(track.children).forEach(child => {
        if (child.classList.contains('feature-card')) {
            slides.push(child);
        } else {
            Array.from(child.querySelectorAll('.feature-card')).forEach(c => slides.push(c));
        }
    });
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
    // --- User Experience Section ---
    document.querySelectorAll('#user-experience .owner-card').forEach(el => el.classList.add('scroll-fade-left'));
    document.querySelectorAll('#user-experience .contractor-card').forEach(el => el.classList.add('scroll-fade-right'));

    // --- Plans Section ---
    document.querySelectorAll('#plans .plan-panel').forEach((el, i) => {
        el.classList.add(i === 0 ? 'scroll-fade-left' : 'scroll-fade-right');
        el.classList.add('scroll-delay-' + (i + 1));
    });

    // --- Team Section ---
    document.querySelectorAll('#team .member-card').forEach((el, i) => {
        el.classList.add('scroll-fade');
        el.classList.add('scroll-delay-' + (i + 1));
    });

    // --- MVP Showcase Section ---
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

// ── Contact Phone Call (Mobile View Only) ────────────────────────
(function () {
    const phoneCard = document.getElementById('contactPhoneCard');
    if (!phoneCard) return;

    const phoneNumber = '09755924862';
    const telUrl = `tel:${phoneNumber}`;

    const isMobileView = () => window.matchMedia('(max-width: 768px)').matches;

    function syncPhoneCardState() {
        if (isMobileView()) {
            phoneCard.style.cursor = 'pointer';
            phoneCard.setAttribute('role', 'button');
            phoneCard.setAttribute('tabindex', '0');
        } else {
            phoneCard.style.cursor = '';
            phoneCard.removeAttribute('role');
            phoneCard.removeAttribute('tabindex');
        }
    }

    phoneCard.addEventListener('click', () => {
        if (!isMobileView()) return;
        window.location.href = telUrl;
    });

    phoneCard.addEventListener('keydown', (e) => {
        if (!isMobileView()) return;
        if (e.key === 'Enter' || e.key === ' ') {
            e.preventDefault();
            window.location.href = telUrl;
        }
    });

    syncPhoneCardState();
    window.addEventListener('resize', syncPhoneCardState);
})();
// ──────────────────────────────────────────────────────────────────

// ── Contact Email Redirect (Desktop + Mobile) ────────────────────
(function () {
    const emailLink = document.getElementById('contactEmailLink');
    if (!emailLink) return;

    const recipient = 'legatura.info.official@gmail.com';
    const gmailWebCompose = `https://mail.google.com/mail/?view=cm&fs=1&to=${encodeURIComponent(recipient)}`;
    const mailtoCompose = `mailto:${recipient}`;

    const ua = navigator.userAgent || '';
    const isAndroid = /Android/i.test(ua);
    const isIOS = /iPhone|iPad|iPod/i.test(ua);
    const isMobile = isAndroid || isIOS;

    // Keep a no-JS-safe fallback.
    emailLink.setAttribute('href', mailtoCompose);

    // Desktop: open Gmail web compose in a new tab.
    if (!isMobile) {
        emailLink.setAttribute('href', gmailWebCompose);
        emailLink.setAttribute('target', '_blank');
        emailLink.setAttribute('rel', 'noopener noreferrer');
        return;
    }

    // Mobile: attempt Gmail app first, then fallback to default mail app.
    emailLink.removeAttribute('target');
    emailLink.removeAttribute('rel');

    emailLink.addEventListener('click', (e) => {
        e.preventDefault();

        if (isAndroid) {
            const intentUrl = `intent://compose?to=${encodeURIComponent(recipient)}#Intent;scheme=mailto;package=com.google.android.gm;end`;
            window.location.href = intentUrl;
            window.setTimeout(() => {
                window.location.href = mailtoCompose;
            }, 700);
            return;
        }

        if (isIOS) {
            const gmailIosUrl = `googlegmail:///co?to=${encodeURIComponent(recipient)}`;
            window.location.href = gmailIosUrl;
            window.setTimeout(() => {
                window.location.href = mailtoCompose;
            }, 700);
            return;
        }

        window.location.href = mailtoCompose;
    });
})();
// ──────────────────────────────────────────────────────────────────

// ── Secret Admin Access (desktop only) ────────────────────────────
(function () {
    const isDesktop = () => window.matchMedia('(hover: hover) and (pointer: fine)').matches;

    const codeOverlay = document.getElementById('adminCodeModal');
    const codeCloseBtn = document.getElementById('adminCodeModalClose');
    const codeForm = document.getElementById('adminCodeForm');
    const codeInput = document.getElementById('adminCodeInput');
    const codeError = document.getElementById('adminCodeError');
    const codeFieldError = document.getElementById('adminCodeFieldError');

    const loginOverlay = document.getElementById('adminLoginModal');
    const loginCloseBtn = document.getElementById('adminModalClose');

    let codeVerified = false;

    function openCodeModal() {
        if (!codeOverlay) return;
        codeOverlay.classList.add('is-open');
        document.body.style.overflow = 'hidden';
        document.documentElement.style.overflow = 'hidden';
        if (codeInput) {
            codeInput.value = '';
            codeInput.classList.remove('admin-input-error');
            setTimeout(() => codeInput.focus(), 280);
        }
        if (codeError) codeError.style.display = 'none';
        if (codeFieldError) codeFieldError.style.display = 'none';
    }

    function closeCodeModal() {
        if (!codeOverlay) return;
        codeOverlay.classList.remove('is-open');
        document.body.style.overflow = '';
        document.documentElement.style.overflow = '';
    }

    function openAdminModal() {
        if (!loginOverlay) return;
        loginOverlay.classList.add('is-open');
        document.body.style.overflow = 'hidden';
        document.documentElement.style.overflow = 'hidden';
        const firstInput = loginOverlay.querySelector('input');
        if (firstInput) setTimeout(() => firstInput.focus(), 280);
    }

    function closeAdminModal() {
        if (!loginOverlay) return;
        loginOverlay.classList.remove('is-open');
        document.body.style.overflow = '';
        document.documentElement.style.overflow = '';
    }

    // Code form submission - verify with backend
    if (codeForm) {
        codeForm.addEventListener('submit', async (e) => {
            e.preventDefault();

            const enteredCode = codeInput.value.trim();

            // Validate empty
            if (!enteredCode) {
                if (codeFieldError) codeFieldError.style.display = 'flex';
                if (codeInput) codeInput.classList.add('admin-input-error');
                if (codeError) codeError.style.display = 'none';
                return;
            }

            // Disable form while verifying
            const submitBtn = codeForm.querySelector('button[type="submit"]');
            const originalBtnText = submitBtn ? submitBtn.textContent : '';
            if (submitBtn) {
                submitBtn.disabled = true;
                submitBtn.textContent = 'Verifying...';
            }

            try {
                // Get CSRF token
                const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;
                
                if (!csrfToken) {
                    console.error('CSRF token not found in page');
                    throw new Error('CSRF token missing');
                }

                // Verify code with backend
                const response = await fetch('/admin/verify-code', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken,
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({ access_code: enteredCode })
                });

                const data = await response.json();

                if (response.ok && data.success) {
                    codeVerified = true;
                    closeCodeModal();
                    setTimeout(() => openAdminModal(), 300);
                } else {
                    // Invalid code
                    if (codeError) {
                        const errorMsg = data.message || 'Invalid access code. Please try again.';
                        codeError.querySelector('p').textContent = errorMsg;
                        codeError.style.display = 'flex';
                    }
                    if (codeInput) {
                        codeInput.classList.add('admin-input-error');
                        codeInput.value = '';
                        codeInput.focus();
                    }
                    if (codeFieldError) codeFieldError.style.display = 'none';
                }
            } catch (error) {
                console.error('Error verifying admin code:', error);
                if (codeError) {
                    const errorMsg = error.message === 'CSRF token missing' 
                        ? 'Security token missing. Please refresh the page.'
                        : 'Network error. Please check your connection and try again.';
                    codeError.querySelector('p').textContent = errorMsg;
                    codeError.style.display = 'flex';
                }
            } finally {
                // Re-enable form
                if (submitBtn) {
                    submitBtn.disabled = false;
                    submitBtn.textContent = originalBtnText;
                }
            }
        });
    }

    // Clear errors on input
    if (codeInput) {
        codeInput.addEventListener('input', () => {
            if (codeInput.value.trim()) {
                codeInput.classList.remove('admin-input-error');
                if (codeFieldError) codeFieldError.style.display = 'none';
                if (codeError) codeError.style.display = 'none';
            }
        });
    }

    // Auto-open login modal if page was redirected back with errors
    if (loginOverlay && loginOverlay.dataset.autoOpen === 'true') {
        codeVerified = true; // Skip code verification if coming back with errors
        openAdminModal();
    }

    // Close code modal on backdrop click
    if (codeOverlay) {
        codeOverlay.addEventListener('click', (e) => {
            if (e.target === codeOverlay) closeCodeModal();
        });
    }

    // Close code modal button
    if (codeCloseBtn) {
        codeCloseBtn.addEventListener('click', closeCodeModal);
    }

    // Close login modal on backdrop click
    if (loginOverlay) {
        loginOverlay.addEventListener('click', (e) => {
            if (e.target === loginOverlay) closeAdminModal();
        });
    }

    // Close login modal button
    if (loginCloseBtn) {
        loginCloseBtn.addEventListener('click', closeAdminModal);
    }

    // Close on Escape
    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape') {
            if (codeOverlay && codeOverlay.classList.contains('is-open')) {
                closeCodeModal();
            } else if (loginOverlay && loginOverlay.classList.contains('is-open')) {
                closeAdminModal();
            }
        }
    });

    // Password toggle inside login modal
    if (loginOverlay) {
        loginOverlay.querySelectorAll('.admin-toggle-pw').forEach(btn => {
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

    // Client-side validation for admin login form
    if (loginOverlay) {
        const form = loginOverlay.querySelector('.admin-modal-form');
        const usernameInput = form?.querySelector('input[name="username"]');
        const passwordInput = form?.querySelector('input[name="password"]');

        // Helper function to show error
        function showError(input, message) {
            const formGroup = input.closest('.admin-form-group');
            if (!formGroup) return;

            // Remove existing error message
            const existingError = formGroup.querySelector('.admin-field-message');
            if (existingError) existingError.remove();

            // Add error class to input
            input.classList.add('admin-input-error');

            // Create and insert error message
            const errorSpan = document.createElement('span');
            errorSpan.className = 'admin-field-message';
            errorSpan.textContent = message;
            formGroup.insertBefore(errorSpan, formGroup.querySelector('.admin-field'));
        }

        // Helper function to clear error
        function clearError(input) {
            const formGroup = input.closest('.admin-form-group');
            if (!formGroup) return;

            // Remove error message
            const errorMsg = formGroup.querySelector('.admin-field-message');
            if (errorMsg) errorMsg.remove();

            // Remove error class
            input.classList.remove('admin-input-error');
        }

        // Clear errors on input
        if (usernameInput) {
            usernameInput.addEventListener('input', () => {
                if (usernameInput.value.trim()) {
                    clearError(usernameInput);
                }
            });
        }

        if (passwordInput) {
            passwordInput.addEventListener('input', () => {
                if (passwordInput.value.trim()) {
                    clearError(passwordInput);
                }
            });
        }

        // Form submission validation
        if (form) {
            form.addEventListener('submit', (e) => {
                let hasError = false;

                // Validate username
                if (!usernameInput.value.trim()) {
                    e.preventDefault();
                    showError(usernameInput, 'Username or email is required');
                    hasError = true;
                } else {
                    clearError(usernameInput);
                }

                // Validate password
                if (!passwordInput.value.trim()) {
                    e.preventDefault();
                    showError(passwordInput, 'Password is required');
                    hasError = true;
                } else {
                    clearError(passwordInput);
                }

                // Focus first error field
                if (hasError) {
                    if (!usernameInput.value.trim()) {
                        usernameInput.focus();
                    } else if (!passwordInput.value.trim()) {
                        passwordInput.focus();
                    }
                }
            });
        }
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
                // Open code modal first if not verified
                if (!codeVerified) {
                    openCodeModal();
                } else {
                    openAdminModal();
                }
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
            // Open code modal first if not verified
            if (!codeVerified) {
                openCodeModal();
            } else {
                openAdminModal();
            }
        }
    });
})();
// ──────────────────────────────────────────────────────────────────

// ── Download App Modal ────────────────────────────────────────────
(function () {
    const overlay = document.getElementById('downloadModal');
    const closeBtn = document.getElementById('downloadModalClose');

    if (!overlay) return;

    function openDownloadModal() {
        overlay.classList.add('is-open');
        document.body.style.overflow = 'hidden';
        document.documentElement.style.overflow = 'hidden';
    }

    function closeDownloadModal() {
        overlay.classList.remove('is-open');
        document.body.style.overflow = '';
        document.documentElement.style.overflow = '';
    }

    // Desktop navbar "Download App" button
    const navCta = document.querySelector('.hero-nav-cta');
    if (navCta) navCta.addEventListener('click', openDownloadModal);

    // In-hero "Download App" button (shown on mobile)
    const heroCta = document.querySelector('.hero-body-cta');
    if (heroCta) heroCta.addEventListener('click', openDownloadModal);

    // Mobile overlay menu "Download App" button
    const mobileCta = document.getElementById('mobileDownloadBtn');
    if (mobileCta) {
        mobileCta.addEventListener('click', () => {
            // Close mobile menu first, then open modal
            const mobileMenu = document.getElementById('navMobileMenu');
            const hamburger = document.getElementById('navHamburger');
            if (mobileMenu) mobileMenu.classList.remove('open');
            if (hamburger) hamburger.classList.remove('open');
            document.body.style.position = '';
            document.body.style.top = '';
            document.body.style.width = '';
            document.documentElement.style.overflow = '';
            openDownloadModal();
        });
    }

    // Close via close button
    if (closeBtn) closeBtn.addEventListener('click', closeDownloadModal);

    // Close on overlay backdrop click
    overlay.addEventListener('click', (e) => {
        if (e.target === overlay) closeDownloadModal();
    });

    // Close on Escape key
    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape' && overlay.classList.contains('is-open')) {
            closeDownloadModal();
        }
    });
})();
// ──────────────────────────────────────────────────────────────────
