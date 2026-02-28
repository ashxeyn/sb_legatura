<!DOCTYPE html>
<html lang="en">

<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title>Legatura | Account Setup</title>
	<!-- Tailwind CSS 4 (local) -->
	<link rel="stylesheet" href="{{ asset('css/app.css') }}">
	<link rel="stylesheet" href="{{ asset('css/signUp_logIN/contractor_accountSetup.css') }}">
	<link rel="stylesheet" href="{{ asset('css/signUp_logIN/otp_Verification.css') }}">
	<link rel='stylesheet'
		href='https://cdn-uicons.flaticon.com/3.0.0/uicons-solid-straight/css/uicons-solid-straight.css'>
	<link rel='stylesheet'
		href='https://cdn-uicons.flaticon.com/3.0.0/uicons-solid-rounded/css/uicons-solid-rounded.css'>
	<link rel='stylesheet' href='https://cdn-uicons.flaticon.com/3.0.0/uicons-bold-rounded/css/uicons-bold-rounded.css'>
	<link rel='stylesheet'
		href='https://cdn-uicons.flaticon.com/3.0.0/uicons-regular-rounded/css/uicons-regular-rounded.css'>
	<style>
		/* Toast Notification Styles */
		.toast-notification {
			position: fixed;
			top: 20px;
			right: 20px;
			background: #4caf50;
			color: white;
			padding: 16px 24px;
			border-radius: 8px;
			box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
			z-index: 9002;
			display: none;
			animation: slideInRight 0.3s ease;
			font-size: 14px;
			font-weight: 500;
			max-width: 300px;
		}

		.toast-notification.toast-success {
			background: #4caf50;
		}

		.toast-notification.toast-error {
			background: #f44336;
		}

		.toast-notification.toast-info {
			background: #2196f3;
		}

		.toast-notification.toast-warning {
			background: #ff9800;
		}

		@keyframes slideInRight {
			from {
				transform: translateX(400px);
				opacity: 0;
			}

			to {
				transform: translateX(0);
				opacity: 1;
			}
		}
	</style>
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
			<form class="setup-form" method="POST" action="{{ url('/contractor/account-setup') }}"
				enctype="multipart/form-data">
				@csrf
				<!-- Hidden step tracker for backend routing -->
				<input type="hidden" id="stepInput" name="step" value="1">

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
								<span class="field-icon" aria-hidden="true"><i class="fi fi-rr-phone-call"></i></span>
								<input type="tel" name="company_phone" placeholder="Company Phone (e.g., 09171234567)"
									maxlength="11" required>
							</label>
							<p style="font-size: 12px; color: var(--muted-color); margin: 4px 0 0;">11 digits starting
								with 09</p>
						</div>

						<div class="field-block">
							<label class="field">
								<span class="field-icon" aria-hidden="true"><i class="fi fi-rr-calendar"></i></span>
								<input type="date" name="founded_date" id="founded_date" placeholder="Founding Date"
									required>
							</label>
						</div>

						<div class="field-block">
							<label class="field">
								<span class="field-icon" aria-hidden="true"><i class="fi fi-rr-history"></i></span>
								<input type="text" id="years_of_experience" placeholder="Years of Experience" readonly
									style="background-color: #f5f5f5; cursor: not-allowed;">
							</label>
						</div>

						<div class="field-block">
							<input type="hidden" name="contractor_type_id" id="contractorTypeValue"
								value="{{ old('contractor_type_id') }}" required>
							<label class="field">
								<span class="field-icon" aria-hidden="true"><i class="fi fi-rr-briefcase"></i></span>
								<button type="button" class="select-button" onclick="openModal('contractorTypeModal')">
									<span id="contractorTypeDisplay">
										@php
											$selectedContractorType = old('contractor_type_id');
											$contractorTypeName = 'Select contractor type';
											foreach (($contractorTypes ?? []) as $type) {
												$typeId = data_get($type, 'type_id');
												if ((string) $typeId === (string) $selectedContractorType) {
													$contractorTypeName = data_get($type, 'type_name') ?: $contractorTypeName;
													break;
												}
											}
										@endphp
										{{ $contractorTypeName }}
									</span>
									<i class="fi fi-rr-angle-small-down"></i>
								</button>
							</label>
						</div>

						<div class="field-block" id="contractor_type_other_wrapper" style="display: none;">
							<label class="field">
								<span class="field-icon" aria-hidden="true"><i class="fi fi-rr-briefcase"></i></span>
								<input type="text" name="contractor_type_other_text"
									placeholder="Specify Other Contractor Type">
							</label>
						</div>

						<div class="field-block">
							<label class="field">
								<span class="field-icon" aria-hidden="true"><i class=""></i></span>
								<textarea name="services_offered" placeholder="Services offered" required></textarea>
							</label>
						</div>

						@php
							$hasCities = !empty($cities);
							$hasBarangays = !empty($barangays);
						@endphp

						<p style="font-size: 12px; color: var(--muted-color); margin: 4px 0 0;">Business Address</p>
						<div class="field-block">
							<label class="field">
								<span class="field-icon" aria-hidden="true"><i class="fi fi-rr-marker"></i></span>
								<input type="text" name="business_address_street"
									placeholder="Street Address / Building / House No." required>
							</label>
						</div>

						<div class="field-block">
							<input type="hidden" name="business_address_province" id="provinceValue"
								value="{{ old('business_address_province') }}" required>
							<label class="field">
								<span class="field-icon" aria-hidden="true"><i class="fi fi-rr-marker"></i></span>
								<button type="button" class="select-button" onclick="openModal('provinceModal')">
									<span id="provinceDisplay">
										@php
											$selectedProvince = old('business_address_province');
											$provinceName = 'Select province';
											foreach (($provinces ?? []) as $province) {
												$provinceCode = data_get($province, 'code');
												if ((string) $provinceCode === (string) $selectedProvince) {
													$provinceName = data_get($province, 'name') ?: $provinceName;
													break;
												}
											}
										@endphp
										{{ $provinceName }}
									</span>
									<i class="fi fi-rr-angle-small-down"></i>
								</button>
							</label>
						</div>

						<div class="field-block">
							<input type="hidden" name="business_address_city" id="cityValue"
								value="{{ old('business_address_city') }}" required>
							<label class="field">
								<span class="field-icon" aria-hidden="true"><i class="fi fi-rr-marker"></i></span>
								<button type="button" class="select-button" onclick="openModal('cityModal')"
									id="cityButton">
									<span id="cityDisplay">
										@php
											$selectedCity = old('business_address_city');
											$cityName = $hasCities ? 'Select city/municipality' : 'Select province first';
											if ($selectedCity && $hasCities) {
												foreach ($cities as $city) {
													if ((string) data_get($city, 'code') === (string) $selectedCity) {
														$cityName = data_get($city, 'name');
														break;
													}
												}
											}
										@endphp
										{{ $cityName }}
									</span>
									<i class="fi fi-rr-angle-small-down"></i>
								</button>
							</label>
						</div>

						<div class="field-block">
							<input type="hidden" name="business_address_barangay" id="barangayValue"
								value="{{ old('business_address_barangay') }}" required>
							<label class="field">
								<span class="field-icon" aria-hidden="true"><i class="fi fi-rr-marker"></i></span>
								<button type="button" class="select-button" onclick="openModal('barangayModal')"
									id="barangayButton">
									<span id="barangayDisplay">
										@php
											$selectedBarangay = old('business_address_barangay');
											$barangayName = $hasBarangays ? 'Select barangay' : 'Select city first';
											if ($selectedBarangay && $hasBarangays) {
												foreach ($barangays as $barangay) {
													if ((string) data_get($barangay, 'code') === (string) $selectedBarangay) {
														$barangayName = data_get($barangay, 'name');
														break;
													}
												}
											}
										@endphp
										{{ $barangayName }}
									</span>
									<i class="fi fi-rr-angle-small-down"></i>
								</button>
							</label>
						</div>

						<div class="field-block">
							<label class="field">
								<span class="field-icon" aria-hidden="true"><i class="fi fi-rr-mailbox"></i></span>
								<input type="text" name="business_address_postal" placeholder="Postal code" required>
							</label>
						</div>

						<p style="font-size: 12px; color: var(--muted-color); margin: 8px 0 0;">Company Website /
							Socials (optional)</p>
						<div class="field-block">
							<label class="field">
								<span class="field-icon" aria-hidden="true"><i class="fi fi-rr-globe"></i></span>
								<input type="url" name="company_website" id="company_website"
									placeholder="https://example.com">
							</label>
							<p style="font-size: 12px; color: var(--muted-color); margin: 4px 0 0;">Enter full URL
								(e.g., https://yoursite.com)</p>
						</div>
						<div class="field-block">
							<label class="field">
								<span class="field-icon" aria-hidden="true"><i class="fi fi-rr-globe"></i></span>
								<input type="text" name="company_social_media"
									placeholder="Social Media (Facebook, Instagram, etc.)">
							</label>
						</div>
					</div>
				</div>

				<!-- Step 2: Account Setup -->
				<div class="form-step" id="step-2">
					<div class="field-stack">
						<!-- Personal Information Section -->
						<p style="font-size: 14px; font-weight: 600; color: var(--text-color); margin: 0 0 16px 0;">
							Personal Information</p>

						<div class="field-block">
							<label class="field">
								<span class="field-icon" aria-hidden="true"><i class="fi fi-rr-user"></i></span>
								<input type="text" name="first_name" placeholder="First Name" required>
							</label>
						</div>

						<div class="field-block">
							<label class="field">
								<span class="field-icon" aria-hidden="true"><i class="fi fi-rr-user"></i></span>
								<input type="text" name="middle_name" placeholder="Middle Name (optional)">
							</label>
						</div>

						<div class="field-block">
							<label class="field">
								<span class="field-icon" aria-hidden="true"><i class="fi fi-rr-user"></i></span>
								<input type="text" name="last_name" placeholder="Last Name" required>
							</label>
						</div>

						<!-- Account Credentials Section -->
						<p style="font-size: 14px; font-weight: 600; color: var(--text-color); margin: 24px 0 16px 0;">
							Account Credentials</p>

						<div class="field-block">
							<label class="field">
								<span class="field-icon" aria-hidden="true"><i class="fi fi-rr-user"></i></span>
								<input type="text" name="username" placeholder="Username" required>
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
								<span class="field-icon" aria-hidden="true"><i class="fi fi-rr-lock"></i></span>
								<input type="password" name="password" id="passwordInput"
									placeholder="Password (min 8 characters)" required>
							</label>
							<!-- Password requirements helper -->
							<div class="password-requirements" id="passwordRequirements"
								style="display: none; margin-top: 8px; font-size: 12px; line-height: 1.6;">
								<p style="color: #666; margin: 0 0 6px 0; font-weight: 500;">Password must have:</p>
								<div class="requirement-item" data-req="min8">
									<i class="fi fi-rr-check" style="margin-right: 4px;"></i>
									<span>At least 8 characters</span>
								</div>
								<div class="requirement-item" data-req="uppercase">
									<i class="fi fi-rr-check" style="margin-right: 4px;"></i>
									<span>One uppercase letter (A-Z)</span>
								</div>
								<div class="requirement-item" data-req="number">
									<i class="fi fi-rr-check" style="margin-right: 4px;"></i>
									<span>One number (0-9)</span>
								</div>
								<div class="requirement-item" data-req="special">
									<i class="fi fi-rr-check" style="margin-right: 4px;"></i>
									<span>One special character (!@#$%^&*)</span>
								</div>
							</div>
							<p style="font-size: 12px; color: var(--muted-color); margin: 4px 0 0;">Minimum 8 characters
							</p>
						</div>

						<div class="field-block">
							<label class="field">
								<span class="field-icon" aria-hidden="true"><i class="fi fi-rr-lock"></i></span>
								<input type="password" name="password_confirmation" id="confirmPasswordInput"
									placeholder="Confirm Password" required>
							</label>
							<p id="passwordMismatchError"
								style="font-size: 12px; color: #d32f2f; margin: 4px 0 0; display: none;">Passwords do
								not match</p>
						</div>
					</div>
				</div>

				<!-- Step 3: Verification -->
				<div class="form-step" id="step-3">
					<div class="field-stack">
						<!-- PICAB Certification Section -->
						<p style="font-size: 18px; font-weight: bold; color: var(--text-color); margin: 0 0 20px 0;">
							PICAB Certification</p>

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
								<input type="hidden" name="pcab_category" id="pcabCategoryValue"
									value="{{ old('pcab_category') }}" required>
								<button type="button" class="select-button" onclick="openModal('pcabCategoryModal')">
									<span
										id="pcabCategoryDisplay">{{ old('pcab_category') ?: 'Select PCAB Category' }}</span>
									<i class="fi fi-rr-angle-small-down"></i>
								</button>
							</label>
						</div>

						<div class="field-block">
							<label class="field-label">PCAB Expiration Date</label>
							<label class="field">
								<span class="field-icon" aria-hidden="true"><i class="fi fi-rr-calendar"></i></span>
								<input type="date" name="pcab_expiration" required>
							</label>
						</div>

						<!-- Business Permit Section -->
						<p style="font-size: 18px; font-weight: bold; color: var(--text-color); margin: 32px 0 20px 0;">
							Business Permit</p>

						<div class="field-block">
							<label class="field-label">Business Permit Number</label>
							<label class="field">
								<span class="field-icon" aria-hidden="true"><i class="fi fi-rr-file-check"></i></span>
								<input type="text" name="business_permit_number" placeholder="Business Permit Number"
									required>
							</label>
						</div>

						<div class="field-block">
							<label class="field-label">Business Permit City</label>
							<label class="field">
								<span class="field-icon" aria-hidden="true"><i class="fi fi-rr-marker"></i></span>
								<input type="text" name="business_permit_city" placeholder="Business Permit City"
									required>
							</label>
						</div>

						<div class="field-block">
							<label class="field-label">Business Permit Expiration</label>
							<label class="field">
								<span class="field-icon" aria-hidden="true"><i class="fi fi-rr-calendar"></i></span>
								<input type="date" name="business_permit_expiration" required>
							</label>
						</div>

						<!-- Business Registration Section -->
						<p style="font-size: 18px; font-weight: bold; color: var(--text-color); margin: 32px 0 20px 0;">
							Business Registration</p>

						<div class="field-block">
							<label class="field-label">TIN/Business Registration Number</label>
							<label class="field">
								<span class="field-icon" aria-hidden="true"><i class="fi fi-rr-hash"></i></span>
								<input type="text" name="tin_registration_number"
									placeholder="TIN/Business Registration Number" required>
							</label>
						</div>

						<div class="field-block">
							<label class="field-label">DTI / SEC Registration Photo</label>
							<div class="upload-area upload-accent" id="uploadAreaDti" data-input="dtiSecInput">
								<div class="upload-icon"><i class="fi fi-rr-cloud-upload"></i></div>
								<div class="upload-text">Upload image or file</div>
								<img class="upload-preview" id="previewAreaDti" style="display: none;" />
								<button type="button" class="upload-remove" id="removeAreaDti" style="display: none;">
									<i class="fi fi-rr-cross"></i>
								</button>
								<input type="file" name="dti_sec_registration" class="upload-input" id="dtiSecInput"
									accept="image/jpeg,image/jpg,image/png,application/pdf" required>
							</div>
						</div>
					</div>

					<p style="font-size: 12px; color: var(--muted-color); margin-top: 12px; text-align: center;">
						By submitting you agree to our Terms & Privacy. All files should be valid and not expired. We'll
						verify these documents before approving your profile.
					</p>
				</div>

			</form>
		</div>

		<!-- Form Actions -->
		<div class="form-actions">
			<button type="button" class="btn btn-secondary" id="backBtn" style="display: none;">Back</button>
			<button type="button" class="btn btn-primary" id="nextBtn">Next</button>
		</div>
	</div>

	<!-- Toast Notification -->
	<div id="toast" class="toast-notification"></div>

	<!-- OTP Modal (shown after Step 2 Account Setup submission) -->
	<div class="otp-modal-backdrop" id="otpModalBackdrop" style="display: none;">
		<div class="otp-container">
			<button class="otp-modal-close" id="closeOtpBtn" type="button">
				<i class="fi fi-rr-cross"></i>
			</button>
			<div class="otp-card">
				<!-- CSRF Token for OTP -->
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
								<span id="timerLabel">Resend in <span id="timer">60</span></span>
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

	<!-- Contractor Type Modal -->
	<div id="contractorTypeModal" class="modal-overlay" style="display: none;">
		<div class="modal-container">
			<div class="modal-header">
				<h3 class="modal-title">Select Contractor Type</h3>
				<button type="button" class="modal-close" onclick="closeModal('contractorTypeModal')">
					<i class="fi fi-rr-cross"></i>
				</button>
			</div>
			<div class="modal-body">
				<div class="modal-search">
					<i class="fi fi-rr-search"></i>
					<input type="text" id="contractorTypeSearch" placeholder="Search contractor type..." />
				</div>
				<div class="modal-list" id="contractorTypeList"></div>
			</div>
		</div>
	</div>

	<!-- Province Modal -->
	<div id="provinceModal" class="modal-overlay" style="display: none;">
		<div class="modal-container">
			<div class="modal-header">
				<h3 class="modal-title">Select Province</h3>
				<button type="button" class="modal-close" onclick="closeModal('provinceModal')">
					<i class="fi fi-rr-cross"></i>
				</button>
			</div>
			<div class="modal-body">
				<div class="modal-search">
					<i class="fi fi-rr-search"></i>
					<input type="text" id="provinceSearch" placeholder="Search province..." />
				</div>
				<div class="modal-list" id="provinceList"></div>
			</div>
		</div>
	</div>

	<!-- City Modal -->
	<div id="cityModal" class="modal-overlay" style="display: none;">
		<div class="modal-container">
			<div class="modal-header">
				<h3 class="modal-title">Select City/Municipality</h3>
				<button type="button" class="modal-close" onclick="closeModal('cityModal')">
					<i class="fi fi-rr-cross"></i>
				</button>
			</div>
			<div class="modal-body">
				<div class="modal-search">
					<i class="fi fi-rr-search"></i>
					<input type="text" id="citySearch" placeholder="Search city..." />
				</div>
				<div class="modal-list" id="cityList"></div>
			</div>
		</div>
	</div>

	<!-- Barangay Modal -->
	<div id="barangayModal" class="modal-overlay" style="display: none;">
		<div class="modal-container">
			<div class="modal-header">
				<h3 class="modal-title">Select Barangay</h3>
				<button type="button" class="modal-close" onclick="closeModal('barangayModal')">
					<i class="fi fi-rr-cross"></i>
				</button>
			</div>
			<div class="modal-body">
				<div class="modal-search">
					<i class="fi fi-rr-search"></i>
					<input type="text" id="barangaySearch" placeholder="Search barangay..." />
				</div>
				<div class="modal-list" id="barangayList"></div>
			</div>
		</div>
	</div>

	<!-- PCAB Category Modal -->
	<div id="pcabCategoryModal" class="modal-overlay" style="display: none;">
		<div class="modal-container">
			<div class="modal-header">
				<h3 class="modal-title">Select PCAB Category</h3>
				<button type="button" class="modal-close" onclick="closeModal('pcabCategoryModal')">
					<i class="fi fi-rr-cross"></i>
				</button>
			</div>
			<div class="modal-body">
				<div class="modal-search">
					<i class="fi fi-rr-search"></i>
					<input type="text" id="pcabCategorySearch" placeholder="Search PCAB category..." />
				</div>
				<div class="modal-list" id="pcabCategoryList"></div>
			</div>
		</div>
	</div>

	<script src="{{ asset('js/signUp_logIN/contractor_accountSetup.js') }}"></script>
	<script src="{{ asset('js/signUp_logIN/otp_Verification.js') }}"></script>
	<script>
		window.appBasePath = "{{ rtrim(request()->getBasePath(), '/') }}";
		window.psgcBaseUrl = "{{ rtrim(request()->getSchemeAndHttpHost() . request()->getBasePath(), '/') }}/api/psgc";
		window.contractorFormData = {
			contractorTypes: @json($contractorTypes ?? []),
			provinces: @json($provinces ?? []),
			cities: @json($cities ?? []),
			barangays: @json($barangays ?? [])
		};
	</script>
</body>

</html>