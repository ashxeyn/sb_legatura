<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title>Legatura | Account Setup</title>
	<!-- Tailwind CSS 4 (local) -->
	<link rel="stylesheet" href="{{ asset('css/app.css') }}">
	<link rel="stylesheet" href="{{ asset('css/signUp_logIN/propertyOwner_accountSetup.css') }}">
	<link rel='stylesheet' href='https://cdn-uicons.flaticon.com/3.0.0/uicons-solid-straight/css/uicons-solid-straight.css'>
	<link rel='stylesheet' href='https://cdn-uicons.flaticon.com/3.0.0/uicons-solid-rounded/css/uicons-solid-rounded.css'>
	<link rel='stylesheet' href='https://cdn-uicons.flaticon.com/3.0.0/uicons-bold-rounded/css/uicons-bold-rounded.css'>
	<link rel='stylesheet' href='https://cdn-uicons.flaticon.com/3.0.0/uicons-regular-rounded/css/uicons-regular-rounded.css'>
</head>
<body class="min-h-screen">
	<div class="setup-container">
		<!-- Header Section (Fixed) -->
		<div class="setup-header-wrapper">
			<!-- Logo -->
			<div class="setup-logo" aria-label="Legatura logo">
				<img src="{{ asset('img/logo2.0.svg') }}" alt="Legatura" class="setup-logo-img" loading="eager">
			</div>

			<!-- Progress Bar -->
			<div class="setup-progress-bar" aria-label="Progress indicator">
				<div class="progress-track">
					<div class="progress-segment active" data-step="1"></div>
					<div class="progress-segment" data-step="2"></div>
					<div class="progress-segment" data-step="3"></div>
				</div>
				<div class="progress-labels">
					<span class="progress-label" data-step="1">Personal Information</span>
					<span class="progress-label" data-step="2">Account Setup</span>
					<span class="progress-label" data-step="3">Verification</span>
				</div>
			</div>
		</div>

		<!-- Form Section (Scrollable) -->
		<div class="setup-form-wrapper">
			<form class="setup-form" method="POST" action="#">

			<!-- Step 1: Personal Information -->
			<div class="form-step active" id="step-1">
				<div class="field-grid-2col">
					<div class="field-block">
						<label class="field-label">First name</label>
						<label class="field">
							<span class="field-icon" aria-hidden="true"><i class="fi fi-rr-user"></i></span>
							<input type="text" name="first_name" placeholder="First name" required>
						</label>
					</div>

					<div class="field-block">
						<label class="field-label">Middle name (Optional)</label>
						<label class="field">
							<span class="field-icon" aria-hidden="true"><i class="fi fi-rr-user"></i></span>
							<input type="text" name="middle_name" placeholder="Middle name (optional)">
						</label>
					</div>

					<div class="field-block">
						<label class="field-label">Last name</label>
						<label class="field">
							<span class="field-icon" aria-hidden="true"><i class="fi fi-rr-user"></i></span>
							<input type="text" name="last_name" placeholder="Last name" required>
						</label>
					</div>

					<div class="field-block">
						<label class="field-label">Occupation</label>
						<label class="field">
							<span class="field-icon" aria-hidden="true"><i class="fi fi-rr-briefcase"></i></span>
							<select name="occupation" required>
								<option value="">Select occupation</option>
								<option value="owner">Property Owner</option>
								<option value="investor">Investor</option>
								<option value="developer">Developer</option>
								<option value="other">Other</option>
							</select>
						</label>
					</div>

					<div class="field-block">
						<label class="field-label">Date of birth</label>
						<label class="field">
							<span class="field-icon" aria-hidden="true"><i class="fi fi-rr-calendar"></i></span>
							<input type="date" name="dob" required>
						</label>
					</div>

					<div class="field-block">
						<label class="field-label">Contact no</label>
						<label class="field">
							<span class="field-icon" aria-hidden="true"><i class="fi fi-rr-phone-call"></i></span>
							<input type="tel" name="contact_no" placeholder="+1 (555) 000-0000" required>
						</label>
					</div>
				</div>
			</div>

			<!-- Step 2: Account Setup -->
			<div class="form-step" id="step-2">
				<label class="field-label">Username</label>
				<label class="field">
					<span class="field-icon" aria-hidden="true"><i class="fi fi-rr-user"></i></span>
					<input type="text" name="username" placeholder="Username" required>
				</label>

				<label class="field-label">Email</label>
				<label class="field">
					<span class="field-icon" aria-hidden="true"><i class="fi fi-rr-envelope"></i></span>
					<input type="email" name="email" placeholder="Email" required>
				</label>

				<label class="field-label">Password</label>
				<label class="field">
					<span class="field-icon" aria-hidden="true"><i class="fi fi-rr-lock"></i></span>
					<input type="password" name="password" placeholder="Password" required>
				</label>

				<label class="field-label">Confirm password</label>
				<label class="field">
					<span class="field-icon" aria-hidden="true"><i class="fi fi-rr-lock"></i></span>
					<input type="password" name="password_confirmation" placeholder="Confirm password" required>
				</label>
			</div>

			<!-- Step 3: Verification -->
			<div class="form-step" id="step-3">
				<div class="field-grid-2col grid-verification">
					<div class="field-block">
						<label class="field-label">Type of valid ID</label>
						<label class="field">
							<span class="field-icon" aria-hidden="true"><i class="fi fi-rr-credit"></i></span>
							<select name="id_type" required>
								<option value="">Select ID type</option>
								<option value="passport">Passport</option>
								<option value="drivers_license">Driver's License</option>
								<option value="national_id">National ID</option>
								<option value="other">Other</option>
							</select>
						</label>
					</div>

					<div class="field-block">
						<label class="field-label">Valid ID number</label>
						<label class="field">
							<span class="field-icon" aria-hidden="true"><i class="fi fi-rr-hash"></i></span>
							<input type="text" name="id_number" placeholder="ID number" required>
						</label>
					</div>

					<div class="field-block">
						<label class="field-label">Upload image of file</label>
						<div class="upload-area" id="uploadArea1">
							<div class="upload-icon"><i class="fi fi-rr-cloud-upload"></i></div>
							<div class="upload-text">Click to upload or drag and drop</div>
							<input type="file" name="id_image" class="upload-input" id="idImageInput" accept="image/*">
						</div>
					</div>

					<div class="field-block">
						<label class="field-label">Police Clearance</label>
						<label class="field">
							<span class="field-icon" aria-hidden="true"><i class="fi fi-rr-file"></i></span>
							<input type="text" name="police_clearance" placeholder="Police Clearance reference" required>
						</label>
					</div>

					<div class="field-block">
						<label class="field-label">Upload image or file</label>
						<div class="upload-area" id="uploadArea2">
							<div class="upload-icon"><i class="fi fi-rr-cloud-upload"></i></div>
							<div class="upload-text">Click to upload or drag and drop</div>
							<input type="file" name="clearance_image" class="upload-input" id="clearanceImageInput">
						</div>
					</div>
				</div>

				<p style="font-size: 12px; color: var(--muted-color); margin-top: 12px; text-align: center;">
					By submitting you agree to our Terms & Privacy. We collect your info to help with verification.
				</p>
			</div>

			<!-- Form Actions -->
			<div class="form-actions">
				<button type="button" class="btn btn-secondary" id="backBtn" style="display: none;">Back</button>
				<button type="button" class="btn btn-primary" id="nextBtn">Next</button>
			</div>
			</form>
		</div>
	</div>

	<script src="{{ asset('js/signUp_logIN/propertyOwner_accountSetup.js') }}"></script>
</body>
</html>
