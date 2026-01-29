<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title>Legatura | Choose Account Type</title>
	<!-- Tailwind CSS 4 (local) -->
	<link rel="stylesheet" href="{{ asset('css/app.css') }}">
	<link rel="stylesheet" href="{{ asset('css/signUp_logIN/accountType.css') }}">
	<link rel='stylesheet' href='https://cdn-uicons.flaticon.com/3.0.0/uicons-solid-straight/css/uicons-solid-straight.css'>
	<link rel='stylesheet' href='https://cdn-uicons.flaticon.com/3.0.0/uicons-solid-rounded/css/uicons-solid-rounded.css'>
	<link rel='stylesheet' href='https://cdn-uicons.flaticon.com/3.0.0/uicons-bold-rounded/css/uicons-bold-rounded.css'>
	<link rel='stylesheet' href='https://cdn-uicons.flaticon.com/3.0.0/uicons-regular-rounded/css/uicons-regular-rounded.css'>
</head>
<body class="min-h-screen">
	<div class="role-container">
		<!-- Logo -->
		<div class="role-logo" aria-label="Legatura logo">
			<img src="{{ asset('img/logo2.0.svg') }}" alt="Legatura" class="role-logo-img" loading="eager">
		</div>

		<!-- Heading -->
		<div class="role-header">
			<h1 class="role-title">Who are you?</h1>
			<p class="role-subtitle">Select whether you’re a property owner or a contractor to continue.</p>
		</div>

		<!-- Cards -->
		<div class="role-grid">
			<button type="button" class="role-card-only" data-role="owner" aria-pressed="false">
				<img src="{{ asset('img/propertyOwner.svg') }}" alt="Property Owner icon" class="role-card-svg">
			</button>

			<button type="button" class="role-card-only" data-role="contractor" aria-pressed="false">
				<img src="{{ asset('img/contractor.svg') }}" alt="Contractor icon" class="role-card-svg">
			</button>
		</div>

		<form class="role-actions" method="POST" id="roleForm" action="#">
			@csrf
			<input type="hidden" name="selected_role" id="selectedRole" value="">
			<button type="button" class="btn btn-primary" id="continueBtn" disabled>Continue</button>
		</form>
	</div>

	<script src="{{ asset('js/signUp_logIN/accountType.js') }}"></script>
</body>
</html>
