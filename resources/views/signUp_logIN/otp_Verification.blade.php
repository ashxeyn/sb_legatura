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
<body class="min-h-screen">
	<div class="otp-container">
		<div class="otp-card">
			<!-- Logo -->
			<div class="setup-logo" aria-label="Legatura logo">
				<img src="{{ asset('img/logo2.0.svg') }}" alt="Legatura" class="setup-logo-img" loading="eager">
			</div>

			<!-- OTP Content -->
			<div class="otp-content">
				<!-- Title & Subtitle -->
				<div class="otp-header">
					<h1 class="otp-title">Email verification</h1>
					<p class="otp-subtitle">Please input the code we sent to your email</p>
				</div>

				<!-- OTP Input Fields -->
				<form class="otp-form" id="otpForm">
					<div class="otp-input-group">
						<input type="text" class="otp-input" maxlength="1" placeholder="" required>
						<input type="text" class="otp-input" maxlength="1" placeholder="" required>
						<input type="text" class="otp-input" maxlength="1" placeholder="" required>
						<input type="text" class="otp-input" maxlength="1" placeholder="" required>
						<input type="text" class="otp-input" maxlength="1" placeholder="" required>
						<input type="text" class="otp-input" maxlength="1" placeholder="" required>
					</div>

					<!-- Resend OTP Timer -->
					<div class="otp-resend">
						<span class="otp-resend-text">Didn't receive the code?</span>
						<button type="button" class="otp-resend-btn" id="resendBtn" disabled>
							Resend in <span id="timer">40</span>
						</button>
					</div>

					<!-- Continue Button -->
					<button type="submit" class="otp-btn">Continue</button>
				</form>
			</div>
		</div>
	</div>

	<script src="{{ asset('js/signUp_logIN/otp_Verification.js') }}"></script>
</body>
</html>
