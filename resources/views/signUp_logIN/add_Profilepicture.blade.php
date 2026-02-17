<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title>Legatura | Add Profile Photo</title>
	<!-- Tailwind CSS 4 (local) -->
	<link rel="stylesheet" href="{{ asset('css/app.css') }}">
	<link rel="stylesheet" href="{{ asset('css/signUp_logIN/add_Profilepicture.css') }}">
	<link rel='stylesheet' href='https://cdn-uicons.flaticon.com/3.0.0/uicons-solid-straight/css/uicons-solid-straight.css'>
	<link rel='stylesheet' href='https://cdn-uicons.flaticon.com/3.0.0/uicons-solid-rounded/css/uicons-solid-rounded.css'>
	<link rel='stylesheet' href='https://cdn-uicons.flaticon.com/3.0.0/uicons-bold-rounded/css/uicons-bold-rounded.css'>
	<link rel='stylesheet' href='https://cdn-uicons.flaticon.com/3.0.0/uicons-regular-rounded/css/uicons-regular-rounded.css'>
</head>
<body class="min-h-screen">
	<div class="profile-container">
		<!-- Card Content -->
		<div class="profile-card">
			<form id="profileForm" method="POST" action="/accounts/signup/owner/final" enctype="multipart/form-data">
				@csrf
				<div class="setup-logo" aria-label="Legatura logo">
					<img src="{{ asset('img/logo2.0.svg') }}" alt="Legatura" class="setup-logo-img" loading="eager">
				</div>

				<!-- Title & Subtitle -->
				<div class="otp-header">
					<h1 class="otp-title">Add Profile Photo</h1>
				</div>

				<div class="avatar-wrapper" aria-label="Profile picture preview">
					<div class="avatar-circle" id="avatarCircle">
						<i class="fi fi-rr-user" id="avatarIcon"></i>
						<img id="avatarImg" style="display: none; width: 100%; height: 100%; object-fit: cover;" />
					</div>
					<button type="button" class="avatar-edit" id="editBtn" aria-label="Select profile photo">
						<i class="fi fi-rr-pencil"></i>
					</button>
					<input type="file" name="profile_pic" id="avatarInput" accept="image/*" class="avatar-input" aria-hidden="true">
				</div>

				<p class="profile-subtitle">Set your profile picture to make your account recognizable.</p>

				<div class="profile-actions">
					<button type="submit" class="btn btn-primary" id="continueBtn">Continue</button>
					<button type="button" class="btn btn-secondary" id="skipBtn">Skip for Now</button>
				</div>
			</form>
		</div>
	</div>

	<!-- Success Overlay -->
	<div class="profile-success-overlay" id="successOverlay" style="display: none;">
		<div class="profile-success-content">
			<div class="profile-success-icon">
				<i class="fi fi-rr-check"></i>
			</div>
			<h2 class="profile-success-text">Registration Successful!</h2>
			<p class="profile-success-subtext">Your account is pending admin approval.</p>
		</div>
	</div>

	<script src="{{ asset('js/signUp_logIN/add_Profilepicture.js') }}"></script>
</body>
</html>
