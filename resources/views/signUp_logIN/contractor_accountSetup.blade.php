<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title>Legatura | Account Setup</title>
	<!-- Tailwind CSS 4 (local) -->
	<link rel="stylesheet" href="{{ asset('css/app.css') }}">
	<link rel="stylesheet" href="{{ asset('css/signUp_logIN/contractor_accountSetup.css') }}">
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
					<span class="progress-label" data-step="1">Company Information</span>
					<span class="progress-label" data-step="2">Account Setup</span>
					<span class="progress-label" data-step="3">Verification</span>
				</div>
			</div>
		</div>

		<!-- Form Section (Scrollable) -->
		<div class="setup-form-wrapper">
			<form class="setup-form" method="POST" action="#">

			<!-- Step 1: Company Information -->
			<div class="form-step active" id="step-1">
				<div class="field-stack">
					<div class="field-block">
						<label class="field">
							<span class="field-icon" aria-hidden="true"><i class="fi fi-rr-briefcase"></i></span>
							<input type="text" name="company_name" placeholder="Company name" required>
						</label>
					</div>

					<div class="field-block">
						<label class="field">
							<span class="field-icon" aria-hidden="true"><i class="fi fi-rr-envelope"></i></span>
							<input type="email" name="company_email" placeholder="Company Email" required>
						</label>
					</div>

					<div class="field-block">
						<label class="field">
							<span class="field-icon" aria-hidden="true"><i class="fi fi-rr-phone-call"></i></span>
							<input type="tel" name="company_contact" placeholder="Company Contact no." required>
						</label>
					</div>

					<div class="field-block">
						<label class="field">
							<span class="field-icon" aria-hidden="true"><i class="fi fi-rr-calendar"></i></span>
							<input type="text" name="years_operation" placeholder="Years of operation" required>
						</label>
					</div>

					<div class="field-block">
						<label class="field">
							<span class="field-icon" aria-hidden="true"><i class="fi fi-rr-briefcase"></i></span>
							<select name="contractor_type" required>
								<option value="">Contractor type</option>
								<option value="general">General Contractor</option>
								<option value="specialty">Specialty Contractor</option>
								<option value="subcontractor">Subcontractor</option>
								<option value="consultant">Consultant</option>
							</select>
						</label>
					</div>

					<div class="field-block">
						<label class="field">
							<span class="field-icon" aria-hidden="true"><i class="fi fi-rr-notebook"></i></span>
							<textarea name="services_offered" placeholder="Services offered" required></textarea>
						</label>
					</div>

					<p style="font-size: 12px; color: var(--muted-color); margin: 4px 0 0;">Business Address</p>
					<div class="field-block">
						<label class="field">
							<span class="field-icon" aria-hidden="true"><i class="fi fi-rr-marker"></i></span>
							<input type="text" name="street" placeholder="Street Address / Building / House No." required>
						</label>
					</div>

					<div class="field-block">
						<label class="field">
							<span class="field-icon" aria-hidden="true"><i class="fi fi-rr-marker"></i></span>
							<input type="text" name="city" placeholder="City / Municipality" required>
						</label>
					</div>

					<div class="field-block">
						<label class="field">
							<span class="field-icon" aria-hidden="true"><i class="fi fi-rr-marker"></i></span>
							<input type="text" name="province" placeholder="Province / State / Region" required>
						</label>
					</div>

					<div class="field-block">
						<label class="field">
							<span class="field-icon" aria-hidden="true"><i class="fi fi-rr-mailbox"></i></span>
							<input type="text" name="postal_code" placeholder="Postal code" required>
						</label>
					</div>

					<p style="font-size: 12px; color: var(--muted-color); margin: 8px 0 0;">Company Website / Socials (optional)</p>
					<div class="field-block">
						<label class="field">
							<span class="field-icon" aria-hidden="true"><i class="fi fi-rr-globe"></i></span>
							<input type="url" name="link_1" placeholder="https://">
						</label>
					</div>
					<div class="field-block">
						<label class="field">
							<span class="field-icon" aria-hidden="true"><i class="fi fi-rr-globe"></i></span>
							<input type="url" name="link_2" placeholder="https://">
						</label>
					</div>
					<div class="field-block">
						<label class="field">
							<span class="field-icon" aria-hidden="true"><i class="fi fi-rr-globe"></i></span>
							<input type="url" name="link_3" placeholder="https://">
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
                    <div class="upload-header">
						<div class="upload-header-icon"><i class="fi fi-rr-id-badge"></i></div>
						<div>
							<div class="upload-title">Upload Documents</div>
							<div class="upload-subtitle">Upload required compliance docs for verification</div>
						</div>
					</div>
					<div class="field-block">
						<label class="field-label">PCAB Number</label>
						<label class="field">
							<span class="field-icon" aria-hidden="true"><i class="fi fi-rr-id-badge"></i></span>
							<input type="text" name="pcab_number" placeholder="PCAB Number" required>
						</label>
					</div>

					<div class="field-block">
						<label class="field-label">PCAB Category</label>
						<label class="field">
							<span class="field-icon" aria-hidden="true"><i class="fi fi-rr-layers"></i></span>
							<input type="text" name="pcab_category" placeholder="PCAB Category" required>
						</label>
					</div>

					<div class="field-block">
						<label class="field-label">PCAB Expiration Date</label>
						<label class="field">
							<span class="field-icon" aria-hidden="true"><i class="fi fi-rr-calendar"></i></span>
							<input type="date" name="pcab_expiration" placeholder="PCAB Expiration Date" required>
						</label>
					</div>

					<div class="field-block">
						<label class="field-label">Business Permit Number</label>
						<label class="field">
							<span class="field-icon" aria-hidden="true"><i class="fi fi-rr-file-check"></i></span>
							<input type="text" name="business_permit_number" placeholder="Business Permit Number" required>
						</label>
					</div>

					<div class="field-block">
						<label class="field-label">Business Permit City</label>
						<label class="field">
							<span class="field-icon" aria-hidden="true"><i class="fi fi-rr-marker"></i></span>
							<input type="text" name="business_permit_city" placeholder="Business Permit City" required>
						</label>
					</div>

					<div class="field-block">
						<label class="field-label">Business Permit Expiration</label>
						<label class="field">
							<span class="field-icon" aria-hidden="true"><i class="fi fi-rr-calendar"></i></span>
							<input type="date" name="business_permit_expiration" placeholder="Business Permit Expiration" required>
						</label>
					</div>

					<div class="field-block">
						<label class="field-label">Tin Business Registration Number</label>
						<label class="field">
							<span class="field-icon" aria-hidden="true"><i class="fi fi-rr-hash"></i></span>
							<input type="text" name="tin_registration_number" placeholder="Tin Business Registration Number" required>
						</label>
					</div>
				</div>

				<div class="field-block">
					<p class="upload-section-label">DTI / SEC Registration</p>
					<div class="upload-area upload-accent" id="uploadAreaDti" data-input="dtiSecInput">
						<div class="upload-icon"><i class="fi fi-rr-cloud-upload"></i></div>
						<div class="upload-text">Upload image or file</div>
						<input type="file" name="dti_sec_registration" class="upload-input" id="dtiSecInput">
					</div>
				</div>

				<p style="font-size: 12px; color: var(--muted-color); margin-top: 12px; text-align: center;">
					By submitting you agree to our Terms & Privacy. We'll contact you after verification. All files should be valid and not expired. We'll verify these documents before approving your profile.
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

	<script src="{{ asset('js/signUp_logIN/contractor_accountSetup.js') }}"></script>
</body>
</html>
