<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title>Legatura | Email Verification</title>
	<!-- Tailwind CSS 4 (local) -->
	<link rel="stylesheet" href="{{ asset('css/app.css') }}">
	<link rel="stylesheet" href="{{ asset('css/signUp_logIN/otp_Verification.css') }}">
	<link rel='stylesheet' href='https://cdn-uicons.flaticon.com/3.0.0/uicons-solid-straight/css/uicons-solid-straight.css'>
	<link rel='stylesheet' href='https://cdn-uicons.flaticon.com/3.0.0/uicons-solid-rounded/css/uicons-solid-rounded.css'>
	<link rel='stylesheet' href='https://cdn-uicons.flaticon.com/3.0.0/uicons-bold-rounded/css/uicons-bold-rounded.css'>
	<link rel='stylesheet' href='https://cdn-uicons.flaticon.com/3.0.0/uicons-regular-rounded/css/uicons-regular-rounded.css'>
</head>
<body>
	<div class="otp-modal-backdrop" id="otpModalBackdrop">
		<div class="otp-container">
			<button class="otp-modal-close" id="closeOtpBtn" style="display: none;">
				<i class="fi fi-rr-cross"></i>
			</button>
			<div class="otp-card">
			<!-- CSRF Token -->
			<meta name="csrf-token" content="{{ csrf_token() }}">
			<!-- Logo -->
			<div class="setup-logo" aria-label="Legatura logo">
				<img src="{{ asset('img/logo2.0.svg') }}" alt="Legatura" class="setup-logo-img" loading="eager">
			</div>

			<!-- OTP Content -->
			<div class="otp-content">
				<!-- Title & Subtitle -->
				<div class="otp-header">
					<h1 class="otp-title">Email Verification</h1>
					<p class="otp-subtitle">Please input the code we sent to your email</p>
				</div>

				<!-- OTP Input Fields -->
				<form class="otp-form" id="otpForm">
					<div class="otp-input-group" id="otpInputGroup">
						<div class="otp-input-wrapper">
							<input type="text" class="otp-input" maxlength="1" inputmode="numeric" required>
							<div class="otp-input-dot"></div>
						</div>
						<div class="otp-input-wrapper">
							<input type="text" class="otp-input" maxlength="1" inputmode="numeric" required>
							<div class="otp-input-dot"></div>
						</div>
						<div class="otp-input-wrapper">
							<input type="text" class="otp-input" maxlength="1" inputmode="numeric" required>
							<div class="otp-input-dot"></div>
						</div>
						<div class="otp-input-wrapper">
							<input type="text" class="otp-input" maxlength="1" inputmode="numeric" required>
							<div class="otp-input-dot"></div>
						</div>
						<div class="otp-input-wrapper">
							<input type="text" class="otp-input" maxlength="1" inputmode="numeric" required>
							<div class="otp-input-dot"></div>
						</div>
						<div class="otp-input-wrapper">
							<input type="text" class="otp-input" maxlength="1" inputmode="numeric" required>
							<div class="otp-input-dot"></div>
						</div>
					</div>

					<!-- Progress Indicator -->
					<div class="otp-progress-container">
						<div class="otp-progress-bar">
							<div class="otp-progress-fill" id="progressFill"></div>
						</div>
						<p class="otp-progress-text"><span id="digitCount">0</span> of 6 digits entered</p>
					</div>

					<!-- Resend OTP Timer -->
					<div class="otp-resend">
						<span class="otp-resend-text">Didn't receive the code?</span>
						<button type="button" class="otp-resend-btn" id="resendBtn" disabled>
							<span id="timerLabel">Resend in <span id="timer">40</span></span>
						</button>
					</div>

					<!-- Continue Button -->
					<button type="submit" class="otp-btn" id="submitBtn" disabled>Continue</button>
				</form>

				<!-- Success Overlay -->
				<div class="otp-success-overlay" id="successOverlay">
					<div class="otp-success-content">
						<div class="otp-success-icon">
							<i class="fi fi-rr-check"></i>
						</div>
						<h2 class="otp-success-text">Verification Complete!</h2>
						<p class="otp-success-subtext">Please wait a moment...</p>
					</div>
				</div>
			</div>
		</div>
		</div>
	</div>

	<script src="{{ asset('js/signUp_logIN/otp_Verification.js') }}"></script>
</body>
</html>
