<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title>Legatura | Account Setup</title>
	<!-- Tailwind CSS 4 (local) -->
	<link rel="stylesheet" href="{{ asset('css/app.css') }}">
	<link rel="stylesheet" href="{{ asset('css/signUp_logIN/propertyOwner_accountSetup.css') }}">
	<link rel="stylesheet" href="{{ asset('css/signUp_logIN/otp_Verification.css') }}">
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
			<form class="setup-form" method="POST" action="{{ route('owner.account-setup') }}">
			@csrf

			<!-- Step 1: Personal Information -->
			<div class="form-step active" id="step-1">
				<h3 class="section-title">Personal Information</h3>
				<div class="field-grid-2col">
					<div class="field-block">
						<label class="field-label">First name *</label>
						<label class="field">
							<span class="field-icon" aria-hidden="true"><i class="fi fi-rr-user"></i></span>
							<input type="text" name="first_name" placeholder="First name" maxlength="100" required value="{{ old('first_name', request('first_name')) }}">
						</label>
					</div>

					<div class="field-block">
						<label class="field-label">Middle name (Optional)</label>
						<label class="field">
							<span class="field-icon" aria-hidden="true"><i class="fi fi-rr-user"></i></span>
							<input type="text" name="middle_name" placeholder="Middle name (optional)" maxlength="100" value="{{ old('middle_name', request('middle_name')) }}">
						</label>
					</div>

					<div class="field-block">
						<label class="field-label">Last name *</label>
						<label class="field">
							<span class="field-icon" aria-hidden="true"><i class="fi fi-rr-user"></i></span>
							<input type="text" name="last_name" placeholder="Last name" maxlength="100" required value="{{ old('last_name', request('last_name')) }}">
						</label>
					</div>

					<div class="field-block">
						<label class="field-label">Occupation *</label>
						<input type="hidden" name="occupation_id" id="occupationValue" value="{{ old('occupation_id', request('occupation_id')) }}" required />
						<label class="field">
							<span class="field-icon" aria-hidden="true"><i class="fi fi-rr-briefcase"></i></span>
							<button type="button" class="select-button" onclick="openModal('occupationModal')">
								<span id="occupationDisplay">
									@php
										$selectedOccupation = old('occupation_id', request('occupation_id'));
										$occupationName = 'Select occupation';
										if ($selectedOccupation) {
											foreach (($occupations ?? []) as $occ) {
												if ((string) $occ->id === (string) $selectedOccupation) {
													$occupationName = $occ->occupation_name;
													break;
												}
											}
										}
									@endphp
									{{ $occupationName }}
								</span>
								<i class="fi fi-rr-angle-small-down"></i>
							</button>
						</label>
					</div>

					<div class="field-block" id="occupationOtherBlock" style="display: {{ old('occupation_other', request('occupation_other')) ? 'block' : 'none' }};">
						<label class="field-label">Specify Occupation *</label>
						<label class="field">
							<span class="field-icon" aria-hidden="true"><i class="fi fi-rr-briefcase"></i></span>
							<input type="text" name="occupation_other" id="occupationOther" placeholder="Please specify" value="{{ old('occupation_other', request('occupation_other')) }}">
						</label>
					</div>

					<div class="field-block">
						<label class="field-label">Date of birth *</label>
						<label class="field">
							<span class="field-icon" aria-hidden="true"><i class="fi fi-rr-calendar"></i></span>
							<input type="date" name="date_of_birth" id="dateOfBirth" required value="{{ old('date_of_birth', request('date_of_birth')) }}">
						</label>
						<small style="font-size: 12px; color: #666; margin-top: 4px; display: block;">Must be 18+ years old</small>
					</div>

					<div class="field-block">
						<label class="field-label">Phone number *</label>
						<label class="field">
							<span class="field-icon" aria-hidden="true"><i class="fi fi-rr-phone-call"></i></span>
							<input type="tel" name="phone_number" placeholder="09171234567" pattern="09[0-9]{9}" maxlength="11" required value="{{ old('phone_number', request('phone_number')) }}">
						</label>
						<small style="font-size: 12px; color: #666; margin-top: 4px; display: block;">11 Digits Start with 09</small>
					</div>
				</div>

				@php
					$hasCities = !empty($cities);
					$hasBarangays = !empty($barangays);
				@endphp
				<h3 class="section-title" style="margin-top: 32px;">Address</h3>
				<div class="field-grid-2col">
					<div class="field-block" style="grid-column: 1 / -1;">
						<label class="field-label">Street/Building No. *</label>
						<label class="field">
							<span class="field-icon" aria-hidden="true"><i class="fi fi-rr-marker"></i></span>
							<input type="text" name="owner_address_street" placeholder="Street/Building No." required value="{{ old('owner_address_street', request('owner_address_street')) }}">
						</label>
					</div>

					<div class="field-block">
						<label class="field-label">Province *</label>
						<input type="hidden" name="owner_address_province" id="provinceValue" value="{{ old('owner_address_province', request('owner_address_province')) }}" required />
						<label class="field">
							<span class="field-icon" aria-hidden="true"><i class="fi fi-rr-map-marker"></i></span>
							<button type="button" class="select-button" onclick="openModal('provinceModal')">
								<span id="provinceDisplay">
									@php
										$selectedProvince = old('owner_address_province', request('owner_address_province'));
										$provinceName = 'Select province';
										if ($selectedProvince) {
											foreach (($provinces ?? []) as $prov) {
												if ((string) $prov['code'] === (string) $selectedProvince) {
													$provinceName = $prov['name'];
													break;
												}
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
						<label class="field-label">City/Municipality *</label>
						<input type="hidden" name="owner_address_city" id="cityValue" value="{{ old('owner_address_city', request('owner_address_city')) }}" required />
						<label class="field">
							<span class="field-icon" aria-hidden="true"><i class="fi fi-rr-map-marker"></i></span>
							<button type="button" class="select-button" onclick="openModal('cityModal')" id="cityButton">
								<span id="cityDisplay">
									@php
										$selectedCity = old('owner_address_city', request('owner_address_city'));
										$cityName = $hasCities ? 'Select city/municipality' : 'Select province first';
										if ($selectedCity && $hasCities) {
											foreach ($cities as $city) {
												if ((string) $city['code'] === (string) $selectedCity) {
													$cityName = $city['name'];
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
						<label class="field-label">Barangay *</label>
						<input type="hidden" name="owner_address_barangay" id="barangayValue" value="{{ old('owner_address_barangay', request('owner_address_barangay')) }}" required />
						<label class="field">
							<span class="field-icon" aria-hidden="true"><i class="fi fi-rr-map-marker"></i></span>
							<button type="button" class="select-button" onclick="openModal('barangayModal')" id="barangayButton">
								<span id="barangayDisplay">
									@php
										$selectedBarangay = old('owner_address_barangay', request('owner_address_barangay'));
										$barangayName = $hasBarangays ? 'Select barangay' : 'Select city first';
										if ($selectedBarangay && $hasBarangays) {
											foreach ($barangays as $barangay) {
												if ((string) $barangay['code'] === (string) $selectedBarangay) {
													$barangayName = $barangay['name'];
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
						<label class="field-label">Postal Code *</label>
						<label class="field">
							<span class="field-icon" aria-hidden="true"><i class="fi fi-rr-envelope"></i></span>
							<input type="text" name="owner_address_postal" placeholder="Postal Code" pattern="[0-9]{4}" maxlength="4" required value="{{ old('owner_address_postal', request('owner_address_postal')) }}">
						</label>
					</div>
				</div>
			</div>

			<!-- Step 2: Account Setup -->
			<div class="form-step" id="step-2">
				<h3 class="section-title">Account Setup</h3>
				<div class="field-grid-2col">
					<div class="field-block" style="grid-column: 1 / -1;">
						<label class="field-label">Username *</label>
						<label class="field">
							<span class="field-icon" aria-hidden="true"><i class="fi fi-rr-user"></i></span>
							<input type="text" name="username" id="usernameInput" placeholder="Username" maxlength="50" required value="{{ old('username', request('username')) }}">
						</label>
					</div>

					<div class="field-block" style="grid-column: 1 / -1;">
						<label class="field-label">Email *</label>
						<label class="field">
							<span class="field-icon" aria-hidden="true"><i class="fi fi-rr-envelope"></i></span>
							<input type="email" name="email" id="emailInput" placeholder="Email" required value="{{ old('email', request('email')) }}">
						</label>
					</div>

					<div class="field-block" style="grid-column: 1 / -1;">
						<label class="field-label">Password *</label>
						<label class="field password-field">
							<span class="field-icon" aria-hidden="true"><i class="fi fi-rr-lock"></i></span>
							<input type="password" name="password" id="passwordInput" placeholder="Password" required value="{{ old('password', request('password')) }}">
							<button type="button" class="password-toggle" id="passwordToggle" aria-label="Show/hide password">
								<i class="fi fi-rr-eye"></i>
							</button>
						</label>
						<!-- Password requirements helper -->
						<div class="password-requirements" id="passwordRequirements" style="display: none; margin-top: 8px; font-size: 12px; line-height: 1.6;">
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
					</div>

					<div class="field-block" style="grid-column: 1 / -1;">
						<label class="field-label">Confirm password *</label>
						<label class="field password-field">
							<span class="field-icon" aria-hidden="true"><i class="fi fi-rr-lock"></i></span>
							<input type="password" name="password_confirmation" id="confirmPasswordInput" placeholder="Confirm password" required value="{{ old('password_confirmation', request('password_confirmation')) }}">
							<button type="button" class="password-toggle" id="confirmPasswordToggle" aria-label="Show/hide password">
								<i class="fi fi-rr-eye"></i>
							</button>
						</label>
					</div>
				</div>
			</div>

			<!-- Step 3: Verification -->
			<div class="form-step" id="step-3">
				<div class="field-grid-2col grid-verification">
					<div class="field-block" style="grid-column: 1 / -1;">
						<label class="field-label">Type of valid ID</label>
						<input type="hidden" name="valid_id_id" id="validIdValue" value="{{ old('valid_id_id', request('valid_id_id')) }}" required />
						<label class="field">
							<span class="field-icon" aria-hidden="true"><i class="fi fi-rr-credit"></i></span>
							<button type="button" class="select-button" onclick="openModal('validIdModal')">
								<span id="validIdDisplay">
									@php
										$selectedValidId = old('valid_id_id', request('valid_id_id'));
										$validIdName = 'Select ID type';
										if ($selectedValidId) {
											foreach (($validIds ?? []) as $validId) {
												$validIdValue = $validId->id ?? $validId['id'] ?? null;
												$validIdLabel = $validId->valid_id_name ?? $validId['valid_id_name'] ?? $validId->name ?? $validId['name'] ?? null;
												if ((string) $validIdValue === (string) $selectedValidId) {
													$validIdName = $validIdLabel ?: 'Select ID type';
													break;
												}
											}
										}
									@endphp
									{{ $validIdName }}
								</span>
								<i class="fi fi-rr-angle-small-down"></i>
							</button>
						</label>
					</div>

					<!-- Valid ID Images Section -->
					<div class="section-separator" style="grid-column: 1 / -1;">
						<div class="section-title">Valid ID Images</div>
					</div>

					<div class="field-block">
						<label class="field-label">Valid ID - Front Side</label>
						<div class="upload-area" id="uploadArea1">
							<div class="upload-icon"><i class="fi fi-rr-cloud-upload"></i></div>
							<div class="upload-text">Valid ID - Front Side Upload image</div>
							<img class="upload-preview" id="previewArea1" style="display: none;" />
							<button type="button" class="upload-remove" id="removeArea1" style="display: none;">
								<i class="fi fi-rr-cross"></i>
							</button>
							<input type="file" name="valid_id_photo" class="upload-input" id="validIdFrontInput" accept="image/jpeg,image/jpg,image/png,application/pdf">
						</div>
					</div>

					<div class="field-block">
						<label class="field-label">Valid ID - Back Side</label>
						<div class="upload-area" id="uploadArea2">
							<div class="upload-icon"><i class="fi fi-rr-cloud-upload"></i></div>
							<div class="upload-text">Valid ID - Back Side Upload image</div>
							<img class="upload-preview" id="previewArea2" style="display: none;" />
							<button type="button" class="upload-remove" id="removeArea2" style="display: none;">
								<i class="fi fi-rr-cross"></i>
							</button>
							<input type="file" name="valid_id_back_photo" class="upload-input" id="validIdBackInput" accept="image/jpeg,image/jpg,image/png,application/pdf">
						</div>
					</div>

					<!-- Police Clearance Section -->
					<div class="section-separator" style="grid-column: 1 / -1;">
						<div class="section-title">Police Clearance</div>
					</div>

					<div class="field-block">
						<label class="field-label">Police Clearance Image</label>
						<div class="upload-area" id="uploadArea3">
							<div class="upload-icon"><i class="fi fi-rr-cloud-upload"></i></div>
							<div class="upload-text">Click to upload or drag and drop</div>
							<img class="upload-preview" id="previewArea3" style="display: none;" />
							<button type="button" class="upload-remove" id="removeArea3" style="display: none;">
								<i class="fi fi-rr-cross"></i>
							</button>
							<input type="file" name="police_clearance" class="upload-input" id="policeClearanceInput" accept="image/jpeg,image/jpg,image/png,application/pdf">
						</div>
					</div>
				</div>

				<p style="font-size: 12px; color: var(--muted-color); margin-top: 12px; text-align: center;">
					By submitting you agree to our Terms & Privacy. We collect your info to help with verification.
				</p>
			</div>
			</form>
		</div>

		<!-- Form Actions (Sticky) -->
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
	<!-- Valid ID Modal -->
	<div id="validIdModal" class="modal-overlay" style="display: none;">
		<div class="modal-container">
			<div class="modal-header">
				<h3 class="modal-title">Select ID Type</h3>
				<button type="button" class="modal-close" onclick="closeModal('validIdModal')">
					<i class="fi fi-rr-cross"></i>
				</button>
			</div>
			<div class="modal-body">
				<div class="modal-search">
					<i class="fi fi-rr-search"></i>
					<input type="text" id="validIdSearch" placeholder="Search ID type..." />
				</div>
				<div class="modal-list" id="validIdList"></div>
			</div>
		</div>
	</div>
	<!-- Occupation Modal -->
	<div id="occupationModal" class="modal-overlay" style="display: none;">
		<div class="modal-container">
			<div class="modal-header">
				<h3 class="modal-title">Select Occupation</h3>
				<button type="button" class="modal-close" onclick="closeModal('occupationModal')">
					<i class="fi fi-rr-cross"></i>
				</button>
			</div>
			<div class="modal-body">
				<div class="modal-search">
					<i class="fi fi-rr-search"></i>
					<input type="text" id="occupationSearch" placeholder="Search occupation..." />
				</div>
				<div class="modal-list" id="occupationList"></div>
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

	<script>
		window.appBasePath = "{{ rtrim(request()->getBasePath(), '/') }}";
		window.psgcBaseUrl = "{{ rtrim(request()->getSchemeAndHttpHost() . request()->getBasePath(), '/') }}/api/psgc";
		window.formData = {
			validIds: @json($validIds ?? []),
			occupations: @json($occupations ?? []),
			provinces: @json($provinces ?? []),
			cities: @json($cities ?? []),
			barangays: @json($barangays ?? [])
		};
	</script>
	<script src="{{ asset('js/signUp_logIN/propertyOwner_accountSetup.js') }}"></script>
	<script src="{{ asset('js/signUp_logIN/otp_Verification.js') }}"></script>
</body>
</html>
