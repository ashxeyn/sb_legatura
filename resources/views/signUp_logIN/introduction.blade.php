<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title>Legatura | Splash</title>
	<!-- Tailwind CSS 4 (local) -->
	<link rel="stylesheet" href="{{ asset('css/app.css') }}">
	<link rel="stylesheet" href="{{ asset('css/signUp_logIN/introduction.css') }}">
</head>
<body class="min-h-screen bg-neutral-50 flex items-center justify-center">
	<!-- Splash Loading Screen -->
	<div class="splash-shell" id="splashShell">
		<div class="splash-logo" aria-label="Legatura logo">
			<img src="{{ asset('img/logo2.0.svg') }}" alt="Legatura" class="logo-img" loading="eager">
		</div>

		<div class="splash-progress" role="progressbar" aria-valuemin="0" aria-valuemax="100" aria-valuenow="0">
			<span class="splash-progress__bar" id="splashProgressBar"></span>
		</div>
	</div>

	<!-- Carousel (shown after loading) -->
	<div class="carousel-container hidden" id="carouselContainer">
		<div class="carousel-card">
			<!-- Logo -->
			<div class="carousel-logo">
				<img src="{{ asset('img/logo2.0.svg') }}" alt="Legatura" class="carousel-logo-img">
			</div>

			<!-- Slide Content -->
			<div class="carousel-content">
				<!-- Slide Image -->
				<div class="carousel-image">
					<img id="carouselSlideImage" src="" alt="Slide" class="slide-img">
				</div>

				<!-- Slide Text -->
				<div class="carousel-text">
					<h2 id="carouselSlideTitle" class="carousel-title"></h2>
					<p id="carouselSlideDesc" class="carousel-description"></p>
				</div>
			</div>

			<!-- Dots Navigation -->
			<div class="carousel-dots" id="carouselDots" role="tablist"></div>

			<button class="carousel-skip" id="carouselSkip" type="button">Skip</button>
		</div>
	</div>

	<!-- Welcome Screen (shown after carousel) -->
	<div class="welcome-container hidden" id="welcomeContainer">
		<div class="welcome-card">
			<!-- Logo -->
			<div class="welcome-logo">
				<img src="{{ asset('img/logo2.0.svg') }}" alt="Legatura" class="welcome-logo-img">
			</div>

			<!-- Content -->
			<div class="welcome-content">
				<h1 class="welcome-title">WELCOME TO LEGATURA</h1>
				<p class="welcome-description">Connect with Skilled Professionals and Trusted Experts for Efficient and Successful Project Delivery.</p>
			</div>

			<!-- Buttons -->
			<div class="welcome-actions">
				<button class="btn btn-primary" onclick="window.location.href='/account-type'">
					Create an Account
				</button>
				<button class="btn btn-secondary" onclick="window.location.href='/login'">
					Log in
				</button>
			</div>
		</div>
	</div>

	<script src="{{ asset('js/signUp_logIN/introduction.js') }}"></script>
</body>
</html>
