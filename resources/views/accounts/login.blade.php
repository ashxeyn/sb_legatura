<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title>Legatura | Login</title>
	<!-- Tailwind CSS 4 (local) -->
	<link rel="stylesheet" href="{{ asset('css/app.css') }}">
	<link rel="stylesheet" href="{{ asset('css/signUp_logIN/logIn.css') }}">
    <link rel='stylesheet' href='https://cdn-uicons.flaticon.com/3.0.0/uicons-solid-straight/css/uicons-solid-straight.css'>
    <link rel='stylesheet' href='https://cdn-uicons.flaticon.com/3.0.0/uicons-solid-rounded/css/uicons-solid-rounded.css'>
    <link rel='stylesheet' href='https://cdn-uicons.flaticon.com/3.0.0/uicons-bold-rounded/css/uicons-bold-rounded.css'>
    <link rel='stylesheet' href='https://cdn-uicons.flaticon.com/3.0.0/uicons-regular-rounded/css/uicons-regular-rounded.css'>
</head>
<body class="min-h-screen">
	<div class="login-container">
		<div class="login-card">
			<!-- Logo -->
			<div class="login-logo" aria-label="Legatura logo">
				<img src="{{ asset('img/logo2.0.svg') }}" alt="Legatura" class="login-logo-img" loading="eager">
			</div>

			<!-- Heading -->
			<div class="login-header">
				<h1 class="login-title">Hi, Welcome Back!</h1>
				<p class="login-subtitle">Hope you're doing fine.</p>
			</div>

			<!-- Error Messages -->
			@if(session('error'))
				<div class="alert alert-error" role="alert">
					<span class="alert-icon">⚠</span>
					<div class="alert-content">
						<p>{{ session('error') }}</p>
					</div>
				</div>
			@endif

			@if(session('success'))
				<div class="alert alert-success" role="alert">
					<span class="alert-icon">✓</span>
					<div class="alert-content">
						<p>{{ session('success') }}</p>
					</div>
				</div>
			@endif

			<!-- Form -->
			<form class="login-form" method="POST" action="/accounts/login" id="loginForm" target="_self" autocomplete="off">
				@csrf
				@php
					$bothInvalid = session('error') === 'Invalid username and password';
				@endphp
				<div class="form-group">
					@if($errors->has('username'))
						<span class="field-error-message field-error-top">{{ $errors->first('username') }}</span>
					@endif
					<label class="field">
						<span class="field-icon" aria-hidden="true"><i class="fi fi-rr-envelope"></i></span>
						<input type="text" name="username" placeholder="Username or Email" value="{{ old('username') }}" required autofocus autocomplete="off"
							class="@if($errors->has('username') || $bothInvalid) field-error @endif">
					</label>
				</div>

				<div class="form-group">
					@if($errors->has('password'))
						<span class="field-error-message field-error-top">{{ $errors->first('password') }}</span>
					@endif
					<label class="field">
						<span class="field-icon" aria-hidden="true"><i class="fi fi-rr-lock"></i></span>
						<input type="password" name="password" placeholder="Password" required autocomplete="new-password"
							class="@if($errors->has('password') || $bothInvalid) field-error @endif">
						<button type="button" class="toggle-visibility" aria-label="Show password">
							<i class="fi fi-rr-eye eye-open"></i>
							<i class="fi fi-rr-eye-crossed eye-closed" style="display:none"></i>
						</button>
					</label>
				</div>

				<div class="login-links">
					<a href="#" class="link">Forgot password?</a>
				</div>

				<button type="submit" class="btn btn-primary">Login</button>
			</form>

			<div class="login-footer">
				<span>Don't have an account yet?</span>
				<a href="/account-type" class="link">Sign up</a>
			</div>
		</div>
	</div>

    <script src="{{ asset('js/signUp_logIN/logIn.js') }}"></script>
</body>
</html>
