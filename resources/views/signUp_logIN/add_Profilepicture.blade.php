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
			@php
				// Detect actual signup type based on which session data actually exists,
				// NOT just signup_user_type (which can be stale/wrong from a previous session).
				$ownerStep2 = Session::get('owner_step2', []);
				$contractorStep2 = Session::get('contractor_step2', []);
				$sessionType = Session::get('signup_user_type', 'owner');

				// Prefer actual data presence over signup_user_type flag
				if (!empty($ownerStep2) && empty($contractorStep2)) {
					$signupType = 'owner';
				} elseif (!empty($contractorStep2) && empty($ownerStep2)) {
					$signupType = 'contractor';
				} else {
					// Fall back to session flag (or default owner) when both or neither have data
					$signupType = $sessionType;
				}

				$finalStepRoute = $signupType === 'contractor'
					? '/accounts/signup/contractor/final'
					: '/accounts/signup/owner/final';
			@endphp
			<form id="profileForm" method="POST" action="{{ $finalStepRoute }}" enctype="multipart/form-data">
				@csrf
				<!-- Hidden fields: Pass session data as fallback if session is lost -->
				@php
					// Reuse the detected signup type from above.
					// Fetch all potentially relevant session keys.
					if ($signupType === 'contractor') {
						$step1Data = Session::get('contractor_step1', []);
						$step2Data = Session::get('contractor_step2', []);
						$step4Data = Session::get('contractor_step4', []);
					} else {
						$step1Data = Session::get('owner_step1', []);
						$step2Data = Session::get('owner_step2', []);
						$step4Data = Session::get('owner_step4', []);
					}

					\Log::info('add_Profilepicture: Resolved signup type and session data', [
						'session_signup_user_type' => Session::get('signup_user_type', 'NOT_SET'),
						'resolved_type' => $signupType,
						'route' => $finalStepRoute,
						'has_step1' => !empty($step1Data),
						'has_step2' => !empty($step2Data),
						'has_step4' => !empty($step4Data),
						'step2_keys' => !empty($step2Data) ? array_keys($step2Data) : [],
					]);
				@endphp
				
				<!-- Always include hidden fields, even if empty, for fallback mechanism -->
				<input type="hidden" name="step1_data" value="{{ json_encode(is_array($step1Data) ? $step1Data : [], JSON_FORCE_OBJECT) }}">
				<input type="hidden" name="step2_data" value="{{ json_encode(is_array($step2Data) ? $step2Data : [], JSON_FORCE_OBJECT) }}">
				<input type="hidden" name="step4_data" value="{{ json_encode(is_array($step4Data) ? $step4Data : [], JSON_FORCE_OBJECT) }}">

				
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
