(() => {
	// ========== SPLASH LOADING SCREEN ==========
	const splashShell = document.getElementById('splashShell');
	const carouselContainer = document.getElementById('carouselContainer');
	const bar = document.getElementById('splashProgressBar');

	if (!splashShell || !carouselContainer || !bar) return;

	let progress = 0;
	const loadingDuration = 3000; // 3 seconds
	const startTime = Date.now();

	const animateProgress = () => {
		const elapsed = Date.now() - startTime;
		progress = Math.min(100, (elapsed / loadingDuration) * 100);
		bar.style.width = `${progress}%`;
		splashShell.setAttribute('aria-valuenow', String(Math.round(progress)));

		if (progress < 100) {
			requestAnimationFrame(animateProgress);
		} else {
			// Loading complete, transition to carousel
			setTimeout(() => {
				splashShell.classList.add('fade-out');
				carouselContainer.classList.remove('hidden');
				initCarousel();
			}, 300);
		}
	};

	requestAnimationFrame(animateProgress);

	// ========== CAROUSEL SLIDER ==========
	const initCarousel = () => {
		const slides = [
			{
				image: window.introSlide1 || '/img/opening1.png',
				description: 'Post projects, compare bids, and choose the <span class="accent">best option</span> with ease.'
			},
			{
				image: window.introSlide2 || '/img/opening2.png',
				description: 'Monitor progress with <span class="accent">real-time updates</span> from site to home.'
			},
			{
				image: window.introSlide3 || '/img/opening3.png',
				description: 'With <span class="accent">Legatura</span>, celebrate success with completed projects delivered on time'
			}
		];

		const imgEl = document.getElementById('carouselSlideImage');
		const titleEl = document.getElementById('carouselSlideTitle');
		const descEl = document.getElementById('carouselSlideDesc');
		const dotsEl = document.getElementById('carouselDots');
		const container = document.getElementById('carouselContainer');
		const welcomeContainer = document.getElementById('welcomeContainer');
		const skipBtn = document.getElementById('carouselSkip');

		if (!imgEl || !titleEl || !descEl || !dotsEl || !container) return;

		let currentIndex = 0;

		const renderDots = () => {
			dotsEl.innerHTML = '';
			slides.forEach((_, i) => {
				const btn = document.createElement('button');
				btn.type = 'button';
				btn.setAttribute('aria-label', `Slide ${i + 1}`);
				btn.setAttribute('aria-current', i === currentIndex ? 'true' : 'false');
				btn.addEventListener('click', (e) => {
					e.stopPropagation();
					goToSlide(i);
				});
				dotsEl.appendChild(btn);
			});
		};

		const render = () => {
			const slide = slides[currentIndex];
			imgEl.src = slide.image;
			imgEl.alt = slide.title;
			titleEl.textContent = slide.title;
			descEl.innerHTML = slide.description;
			renderDots();
		};

		const goToSlide = (index) => {
			currentIndex = (index + slides.length) % slides.length;
			render();
		};

		const showWelcome = () => {
			container.classList.add('hidden');
			if (welcomeContainer) {
				welcomeContainer.classList.remove('hidden');
			}
		};

		const nextSlide = () => {
			if (currentIndex < slides.length - 1) {
				goToSlide(currentIndex + 1);
			} else {
				// Last slide - show welcome screen
				showWelcome();
			}
		};

		// Click anywhere to advance
		container.addEventListener('click', nextSlide);

		if (skipBtn) {
			skipBtn.addEventListener('click', (e) => {
				e.stopPropagation();
				showWelcome();
			});
		}

		// Keyboard navigation
		document.addEventListener('keydown', (e) => {
			if (e.key === 'ArrowRight' || e.key === ' ') {
				e.preventDefault();
				nextSlide();
			}
			if (e.key === 'ArrowLeft') {
				e.preventDefault();
				if (currentIndex > 0) {
					goToSlide(currentIndex - 1);
				}
			}
			if ((e.key === 'Enter' || e.key === ' ') && document.activeElement === skipBtn) {
				e.preventDefault();
				showWelcome();
			}
		});

		// Initial render
		render();
	};
})();

