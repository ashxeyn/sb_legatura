document.addEventListener('DOMContentLoaded', () => {
	let currentStep = 1;
	const totalSteps = 3;
	const form = document.querySelector('.setup-form');
	const nextBtn = document.getElementById('nextBtn');
	const backBtn = document.getElementById('backBtn');
	const progressSegments = document.querySelectorAll('.progress-segment');
	const progressLabels = document.querySelectorAll('.progress-label');
	const otpModalBackdrop = document.getElementById('otpModalBackdrop');

	// OTP modal state
	let otpStep2Data = null;

	// Modal data storage
	let modalData = {
		validIds: window.formData?.validIds || [],
		occupations: window.formData?.occupations || [],
		provinces: window.formData?.provinces || [],
		cities: window.formData?.cities || [],
		barangays: window.formData?.barangays || []
	};

	// API base URL
	const appBasePath = window.appBasePath || '';
	const fallbackBase = `${window.location.origin}${appBasePath}`;
	const psgcBaseUrl = (window.psgcBaseUrl || `${fallbackBase}/api/psgc`).replace(/\/$/, '');

	// Modal functions
	window.openModal = function(modalId) {
		const modal = document.getElementById(modalId);
		if (!modal) return;

		// Populate modal content based on type
		if (modalId === 'validIdModal') {
			populateModalList('validIdList', modalData.validIds, 'validIdSearch', (item) => {
				selectValidId(item.id, item.valid_id_name || item.name);
			});
		} else if (modalId === 'occupationModal') {
			populateModalList('occupationList', modalData.occupations, 'occupationSearch', (item) => {
				selectOccupation(item.id, item.occupation_name);
			});
		} else if (modalId === 'provinceModal') {
			populateModalList('provinceList', modalData.provinces, 'provinceSearch', (item) => {
				selectProvince(item.code, item.name);
			});
		} else if (modalId === 'cityModal') {
			const provinceCode = document.getElementById('provinceValue').value;
			if (!provinceCode) {
				alert('Please select a province first');
				return;
			}
			// If cities already loaded, show them; otherwise fetch
			if (modalData.cities.length > 0) {
				populateModalList('cityList', modalData.cities, 'citySearch', (item) => {
					selectCity(item.code, item.name);
				});
			} else {
				loadCitiesForModal(provinceCode);
			}
		} else if (modalId === 'barangayModal') {
			const cityCode = document.getElementById('cityValue').value;
			if (!cityCode) {
				alert('Please select a city first');
				return;
			}
			// If barangays already loaded, show them; otherwise fetch
			if (modalData.barangays.length > 0) {
				populateModalList('barangayList', modalData.barangays, 'barangaySearch', (item) => {
					selectBarangay(item.code, item.name);
				});
			} else {
				loadBarangaysForModal(cityCode);
			}
		}

		modal.style.display = 'flex';
	};

	window.closeModal = function(modalId) {
		const modal = document.getElementById(modalId);
		if (modal) {
			modal.style.display = 'none';
		}
	};

	// Close modal when clicking overlay
	document.querySelectorAll('.modal-overlay').forEach(overlay => {
		overlay.addEventListener('click', (e) => {
			if (e.target === overlay) {
				closeModal(overlay.id);
			}
		});
	});

	// Selection handlers
	function selectValidId(id, name) {
		const valueEl = document.getElementById('validIdValue');
		const displayEl = document.getElementById('validIdDisplay');
		if (valueEl) valueEl.value = id;
		if (displayEl) displayEl.textContent = name || 'Select ID type';
		closeModal('validIdModal');
	}

	function selectOccupation(id, name) {
		document.getElementById('occupationValue').value = id;
		document.getElementById('occupationDisplay').textContent = name;
		closeModal('occupationModal');

		// Show/hide the occupation 'other' field
		const occupationOtherBlock = document.getElementById('occupationOtherBlock');
		const occupationOther = document.getElementById('occupationOther');
		if (occupationOtherBlock) {
			if (name.toLowerCase().includes('other')) {
				occupationOtherBlock.style.display = 'block';
				if (occupationOther) occupationOther.required = true;
			} else {
				occupationOtherBlock.style.display = 'none';
				if (occupationOther) occupationOther.required = false;
				if (occupationOther) occupationOther.value = '';
			}
		}
	}

	function selectProvince(code, name) {
		document.getElementById('provinceValue').value = code;
		document.getElementById('provinceDisplay').textContent = name;
		
		// Reset dependent fields
		document.getElementById('cityValue').value = '';
		document.getElementById('cityDisplay').textContent = 'Select city/municipality';
		document.getElementById('barangayValue').value = '';
		document.getElementById('barangayDisplay').textContent = 'Select city first';
		
		// Load cities for this province
		modalData.cities = [];
		modalData.barangays = [];
		loadCitiesForModal(code);
		
		closeModal('provinceModal');
	}

	function selectCity(code, name) {
		document.getElementById('cityValue').value = code;
		document.getElementById('cityDisplay').textContent = name;
		
		// Reset barangay
		document.getElementById('barangayValue').value = '';
		document.getElementById('barangayDisplay').textContent = 'Select barangay';
		
		// Load barangays for this city
		modalData.barangays = [];
		loadBarangaysForModal(code);
		
		closeModal('cityModal');
	}

	function selectBarangay(code, name) {
		document.getElementById('barangayValue').value = code;
		document.getElementById('barangayDisplay').textContent = name;
		closeModal('barangayModal');
	}

	// Populate modal list with items
	function populateModalList(listId, items, searchId, onSelect) {
		const listEl = document.getElementById(listId);
		const searchInput = document.getElementById(searchId);
		
		if (!listEl) return;

		// Detect property name (occupation_name, valid_id_name, name)
		let displayProp = 'name';
		if (items.length > 0) {
			if (items[0].occupation_name) {
				displayProp = 'occupation_name';
			} else if (items[0].valid_id_name) {
				displayProp = 'valid_id_name';
			}
		}

		// Store all items for searching
		listEl.dataset.allItems = JSON.stringify(items);
		listEl.dataset.displayProp = displayProp;
		listEl.dataset.onSelect = onSelect.toString();

		// Render items
		renderModalItems(listEl, items, onSelect, displayProp);

		// Set up search
		if (searchInput) {
			searchInput.value = '';
			searchInput.oninput = () => {
				const query = searchInput.value.toLowerCase();
				const filtered = items.filter(item => {
					const text = item[displayProp] || '';
					return text.toLowerCase().includes(query);
				});
				renderModalItems(listEl, filtered, onSelect, displayProp);
			};
		}
	}

	function renderModalItems(listEl, items, onSelect, displayProp = 'name') {
		listEl.innerHTML = '';
		
		if (items.length === 0) {
			listEl.innerHTML = '<div style="padding: 20px; text-align: center; color: #9ca3af;">No items found</div>';
			return;
		}

		items.forEach(item => {
			const button = document.createElement('button');
			button.type = 'button';
			button.className = 'modal-item';
			button.innerHTML = `<span>${item[displayProp]}</span>`;
			button.onclick = () => onSelect(item);
			listEl.appendChild(button);
		});
	}

	// Load cities from API
	async function loadCitiesForModal(provinceCode) {
		const listEl = document.getElementById('cityList');
		if (!listEl) return;

		listEl.innerHTML = '<div style="padding: 20px; text-align: center; color: #9ca3af;">Loading cities...</div>';

		try {
			const response = await fetch(`${psgcBaseUrl}/provinces/${provinceCode}/cities`);
			if (!response.ok) {
				throw new Error(`Cities request failed: ${response.status}`);
			}
			const payload = await response.json();
			const cities = Array.isArray(payload) ? payload : (payload.data || []);
			
			modalData.cities = cities;
			populateModalList('cityList', cities, 'citySearch', (item) => {
				selectCity(item.code, item.name);
			});
		} catch (error) {
			console.error('Error loading cities:', error);
			listEl.innerHTML = '<div style="padding: 20px; text-align: center; color: #ef4444;">Error loading cities</div>';
		}
	}

	// Load barangays from API
	async function loadBarangaysForModal(cityCode) {
		const listEl = document.getElementById('barangayList');
		if (!listEl) return;

		listEl.innerHTML = '<div style="padding: 20px; text-align: center; color: #9ca3af;">Loading barangays...</div>';

		try {
			const response = await fetch(`${psgcBaseUrl}/cities/${cityCode}/barangays`);
			if (!response.ok) {
				throw new Error(`Barangays request failed: ${response.status}`);
			}
			const payload = await response.json();
			const barangays = Array.isArray(payload) ? payload : (payload.data || []);
			
			modalData.barangays = barangays;
			populateModalList('barangayList', barangays, 'barangaySearch', (item) => {
				selectBarangay(item.code, item.name);
			});
		} catch (error) {
			console.error('Error loading barangays:', error);
			listEl.innerHTML = '<div style="padding: 20px; text-align: center; color: #ef4444;">Error loading barangays</div>';
		}
	}

	// Step navigation
	const showStep = (step) => {
		// Hide all steps
		document.querySelectorAll('.form-step').forEach(el => {
			el.classList.remove('active');
		});
		
		// Show current step
		document.getElementById(`step-${step}`).classList.add('active');

		// Reset Next button to normal state
		if (nextBtn) {
			nextBtn.disabled = false;
			nextBtn.classList.remove('loading');
			nextBtn.innerHTML = 'Next';
		}

		// Update progress segments and labels
		progressSegments.forEach(seg => {
			const s = parseInt(seg.dataset.step);
			if (s <= step) {
				seg.classList.add('active');
			} else {
				seg.classList.remove('active');
			}
		});

		progressLabels.forEach(lbl => {
			const s = parseInt(lbl.dataset.step);
			if (s === step) {
				lbl.classList.add('active');
			} else {
				lbl.classList.remove('active');
			}
		});

		// Update button visibility
		if (step === 1) {
			if (backBtn) backBtn.style.display = 'none';
			if (nextBtn) nextBtn.textContent = 'Next';
		} else if (step === totalSteps) {
			if (backBtn) backBtn.style.display = 'block';
			if (nextBtn) nextBtn.textContent = 'Next';
		} else {
			if (backBtn) backBtn.style.display = 'block';
			if (nextBtn) nextBtn.textContent = 'Next';
		}
	};

	const validateStep = (step) => {
		const stepElement = document.getElementById(`step-${step}`);
		const inputs = stepElement.querySelectorAll('input[required]');
		
		// Check hidden inputs for modal selections
		const hiddenInputs = stepElement.querySelectorAll('input[type="hidden"][required]');
		for (let input of hiddenInputs) {
			if (!input.value.trim()) {
				alert(`Please complete all required fields.`);
				return false;
			}
		}
		
		for (let input of inputs) {
			// Special validation for file inputs - check files.length instead of value
			if (input.type === 'file') {
				if (!input.files || input.files.length === 0) {
					alert(`Please select ${input.name === 'valid_id_photo' ? 'Valid ID - Front Side' : input.name === 'valid_id_back_photo' ? 'Valid ID - Back Side' : 'Police Clearance'} image.`);
					return false;
				}
			} else if (input.type !== 'hidden' && !input.value.trim()) {
				input.focus();
				alert(`Please fill in all required fields.`);
				return false;
			}

			// Additional validations for specific fields
			if (input.name === 'date_of_birth') {
				const dob = new Date(input.value);
				const today = new Date();
				const age = today.getFullYear() - dob.getFullYear();
				const monthDiff = today.getMonth() - dob.getMonth();
				const dayDiff = today.getDate() - dob.getDate();
				
				// Check if user is at least 18 years old
				const actualAge = (monthDiff < 0 || (monthDiff === 0 && dayDiff < 0)) ? age - 1 : age;
				
				if (actualAge < 18) {
					input.focus();
					alert('You must be at least 18 years old to register.');
					return false;
				}
			}

			// Phone number validation
			if (input.name === 'phone_number') {
				const phonePattern = /^09[0-9]{9}$/;
				if (!phonePattern.test(input.value.trim())) {
					input.focus();
					alert('Phone number must be in format: 09XXXXXXXXX (11 digits starting with 09)');
					return false;
				}
			}

			// Postal code validation
			if (input.name === 'owner_address_postal') {
				const postalPattern = /^[0-9]{4}$/;
				if (!postalPattern.test(input.value.trim())) {
					input.focus();
					alert('Postal code must be exactly 4 digits.');
					return false;
				}
			}
		}

		// Check password match in step 2
		if (step === 2) {
			const password = stepElement.querySelector('input[name="password"]');
			const confirmPassword = stepElement.querySelector('input[name="password_confirmation"]');
			
			if (password && confirmPassword && password.value !== confirmPassword.value) {
				confirmPassword.focus();
				alert('Passwords do not match.');
				return false;
			}
		}

		return true;
	};

	if (nextBtn) {
		nextBtn.addEventListener('click', async () => {
			if (validateStep(currentStep)) {
				// Show loading state
				const originalText = nextBtn.innerHTML;
				nextBtn.disabled = true;
				nextBtn.classList.add('loading');
				nextBtn.innerHTML = '<span class="spinner"></span>Loading...';

				try {
					if (currentStep === 2) {
						// For step 2, submit and show OTP modal instead of advancing
						const success = await submitMilestoneStep(2);
						if (success) {
							// Show success toast notification
							const emailInput = document.querySelector('input[name="email"]');
							const userEmail = emailInput ? emailInput.value : 'your email';
							showToast(`OTP successfully sent to ${userEmail}`);
							// Show OTP modal after successful Step 2 submission
							showOtpModal();
						} else {
							// Restore button on error
							nextBtn.disabled = false;
							nextBtn.classList.remove('loading');
							nextBtn.innerHTML = originalText;
						}
					} else if (currentStep === 3) {
						// Step 3 (Verification) — submit files then redirect to profile photo
						const success = await submitMilestoneStep(3);
						if (success) {
							console.log('Step 3 successful, redirecting to profile photo page...');
							window.location.href = '/add-profile-photo';
							// Don't restore button — page is navigating away
						} else {
							// Restore button on error
							nextBtn.disabled = false;
							nextBtn.classList.remove('loading');
							nextBtn.innerHTML = originalText;
						}
					} else {
						// Step 1 — submit and advance to next step
						const success = await submitMilestoneStep(currentStep);
						if (success) {
							currentStep++;
							showStep(currentStep);
						} else {
							nextBtn.disabled = false;
							nextBtn.classList.remove('loading');
							nextBtn.innerHTML = originalText;
						}
					}
				} catch (error) {
					// Always restore button on ANY exception
					console.error('Unhandled error in button handler:', error);
					nextBtn.disabled = false;
					nextBtn.classList.remove('loading');
					nextBtn.innerHTML = originalText;
					alert('Unexpected error: ' + error.message);
				}
			}
		});
	}

	if (backBtn) {
		backBtn.addEventListener('click', () => {
			if (currentStep > 1) {
				currentStep--;
				showStep(currentStep);
			}
		});
	}

	// Show Toast Notification
	const showToast = (message) => {
		const toast = document.getElementById('toast');
		if (toast) {
			toast.textContent = message;
			toast.classList.add('show');
			// Auto hide after 4 seconds
			setTimeout(() => {
				toast.classList.remove('show');
			}, 4000);
		}
	};

	// Show OTP modal
	const showOtpModal = () => {
		const otpModalBackdrop = document.getElementById('otpModalBackdrop');
		if (otpModalBackdrop) {
			// Get email from the input field
			const emailInput = document.querySelector('input[name="email"]');
			const userEmail = emailInput ? emailInput.value : '';
			
			// Update subtitle to show where OTP was sent
			const otpSubtitle = otpModalBackdrop.querySelector('.otp-subtitle');
			if (otpSubtitle && userEmail) {
				otpSubtitle.innerHTML = `Please input the code we sent to <strong>${userEmail}</strong>`;
			}
			
			otpModalBackdrop.style.display = 'flex';
			// Reset and focus first OTP input
			const otpInputs = otpModalBackdrop.querySelectorAll('.otp-input');
			otpInputs.forEach(input => input.value = '');
			if (otpInputs.length > 0) {
				otpInputs[0].focus();
			}
		}
	};

	// Hide OTP modal
	const hideOtpModal = () => {
		const otpModalBackdrop = document.getElementById('otpModalBackdrop');
		if (otpModalBackdrop) {
			otpModalBackdrop.style.display = 'none';
			// Reset inputs
			const otpInputs = otpModalBackdrop.querySelectorAll('.otp-input');
			otpInputs.forEach(input => {
				input.value = '';
				input.disabled = false;
				input.classList.remove('otp-input-success');
			});
		}
		
		// Re-enable Next button
		if (nextBtn) {
			nextBtn.disabled = false;
			nextBtn.classList.remove('loading');
			nextBtn.innerHTML = 'Next';
		}
	};

	// OTP modal close button handler
	const closeOtpBtn = document.getElementById('closeOtpBtn');
	if (closeOtpBtn) {
		closeOtpBtn.addEventListener('click', (e) => {
			e.preventDefault();
			hideOtpModal();
		});
	}

	// OTP modal backdrop click-to-close handler
	if (otpModalBackdrop) {
		otpModalBackdrop.addEventListener('click', (e) => {
			// Close if clicking outside the actual modal container
			if (e.target === otpModalBackdrop) {
				hideOtpModal();
			}
		});
	}

	// Handle OTP verification and advance to next step
	const handleOtpVerified = () => {
		hideOtpModal();
		currentStep = 3;
		showStep(currentStep);
	};

	// Expose for otp_Verification.js
	window.handleOtpVerified = handleOtpVerified;

	// Submit milestone step to backend
	const submitMilestoneStep = async (step) => {
		try {
			const formData = new FormData(form);
			// Get CSRF token from form input or meta tag
			const csrfToken = document.querySelector('input[name="_token"]')?.value || 
			                  document.querySelector('meta[name="csrf-token"]')?.content;

			if (!csrfToken) {
				console.error('CSRF token not found!');
				alert('Security error: CSRF token not found. Please refresh and try again.');
				return false;
			}

			let endpoint = '';
			let data = {};

			if (step === 1) {
				endpoint = '/accounts/signup/owner/step1';
				data = {
					first_name: document.querySelector('input[name="first_name"]')?.value,
					middle_name: document.querySelector('input[name="middle_name"]')?.value,
					last_name: document.querySelector('input[name="last_name"]')?.value,
					occupation_id: document.querySelector('input[name="occupation_id"]')?.value,
					occupation_other: document.querySelector('input[name="occupation_other"]')?.value,
					date_of_birth: document.querySelector('input[name="date_of_birth"]')?.value,
					phone_number: document.querySelector('input[name="phone_number"]')?.value,
					owner_address_street: document.querySelector('input[name="owner_address_street"]')?.value,
					owner_address_province: document.querySelector('input[name="owner_address_province"]')?.value,
					owner_address_city: document.querySelector('input[name="owner_address_city"]')?.value,
					owner_address_barangay: document.querySelector('input[name="owner_address_barangay"]')?.value,
					owner_address_postal: document.querySelector('input[name="owner_address_postal"]')?.value,
					_token: csrfToken
				};
			} else if (step === 2) {
				endpoint = '/accounts/signup/owner/step2';
				data = {
					username: document.querySelector('input[name="username"]')?.value,
					email: document.querySelector('input[name="email"]')?.value,
					password: document.querySelector('input[name="password"]')?.value,
					password_confirmation: document.querySelector('input[name="password_confirmation"]')?.value,
					_token: csrfToken
				};
			} else if (step === 3) {
				endpoint = '/accounts/signup/owner/step4';
				data = new FormData();
				
				// Get file inputs
				const validIdInput = document.querySelector('input[name="valid_id_photo"]');
				const validIdBackInput = document.querySelector('input[name="valid_id_back_photo"]');
				const policeClearanceInput = document.querySelector('input[name="police_clearance"]');
				
				// Validate files exist
				if (!validIdInput?.files[0]) {
					throw new Error('Valid ID - Front Side image is missing');
				}
				if (!validIdBackInput?.files[0]) {
					throw new Error('Valid ID - Back Side image is missing');
				}
				if (!policeClearanceInput?.files[0]) {
					throw new Error('Police Clearance image is missing');
				}
				
				// Append data to FormData
				data.append('valid_id_id', document.querySelector('input[name="valid_id_id"]')?.value || '');
				data.append('valid_id_photo', validIdInput.files[0]);
				data.append('valid_id_back_photo', validIdBackInput.files[0]);
				data.append('police_clearance', policeClearanceInput.files[0]);
				data.append('_token', csrfToken);
				
				console.log('Submitting Step 3 with files:');
				console.log('  - CSRF Token:', csrfToken ? '✓ Present' : '✗ MISSING!');
				console.log('  - Valid ID ID:', document.querySelector('input[name="valid_id_id"]')?.value || 'EMPTY');
				console.log('  - Valid ID Photo:', validIdInput.files[0]?.name, `(${(validIdInput.files[0]?.size / 1024 / 1024).toFixed(2)}MB)`);
				console.log('  - Valid ID Back Photo:', validIdBackInput.files[0]?.name, `(${(validIdBackInput.files[0]?.size / 1024 / 1024).toFixed(2)}MB)`);
				console.log('  - Police Clearance:', policeClearanceInput.files[0]?.name, `(${(policeClearanceInput.files[0]?.size / 1024 / 1024).toFixed(2)}MB)`);
				console.log('  - Police Clearance:', policeClearanceInput.files[0]?.name);
			}

			const response = await fetch(endpoint, {
				method: 'POST',
				headers: step === 3 ? {} : { 'Content-Type': 'application/json' },
				body: step === 3 ? data : JSON.stringify(data),
				credentials: 'include',
				signal: AbortSignal.timeout(30000) // 30 second timeout
			});

			// Parse response as text first to handle non-JSON responses
			const responseText = await response.text();
			let result;

			try {
				result = JSON.parse(responseText);
			} catch (e) {
				console.error('Response is not valid JSON:', responseText.substring(0, 500));
				if (!response.ok) {
					alert('Server Error (Status ' + response.status + '):\n\n' + (responseText.substring(0, 200) || 'The server returned an invalid response. Please try again.'));
				} else {
					alert('Unexpected Response: Server returned invalid data. Please try again.');
				}
				return false;
			}

			if (!response.ok) {
				if (result.errors) {
					// For step 3, provide detailed file debugging
					if (step === 3) {
						console.error('Step 3 Validation Errors:', result.errors);
						console.log('Files being submitted:');
						console.log('  valid_id_photo:', document.querySelector('input[name="valid_id_photo"]')?.files[0]?.name || 'NOT FOUND');
						console.log('  valid_id_back_photo:', document.querySelector('input[name="valid_id_back_photo"]')?.files[0]?.name || 'NOT FOUND');
						console.log('  police_clearance:', document.querySelector('input[name="police_clearance"]')?.files[0]?.name || 'NOT FOUND');
						console.log('  valid_id_id value:', document.querySelector('input[name="valid_id_id"]')?.value || 'NOT FOUND');
					}
					const errorMessages = result.errors;
					let errorText = 'Validation Errors:\n';
					if (Array.isArray(errorMessages)) {
						errorText += errorMessages.join('\n');
					} else if (typeof errorMessages === 'object') {
						for (const [field, msgs] of Object.entries(errorMessages)) {
							if (Array.isArray(msgs)) {
								errorText += `${field}: ${msgs.join(', ')}\n`;
							} else {
								errorText += `${field}: ${msgs}\n`;
							}
						}
					}
					alert(errorText);
				} else {
					alert(result.message || 'Error submitting form');
				}
				console.error('Server response error:', result);
				return false;
			}

			// Success - data sent and OTP generated (for step 2)
			if (step === 2 && result.message?.includes('OTP')) {
				console.log('OTP sent to email:', result.message);
			}
			
			console.log('Step ' + step + ' submitted successfully:', result);
			return true;
		} catch (error) {
			console.error('Error submitting step ' + step + ':', error);
			if (error.name === 'AbortError') {
				alert('Request timeout: The server took too long to respond. Please try again.');
			} else {
				alert('Error: ' + error.message);
			}
			return false;
		}
	};

	// Password visibility toggles
	const setupPasswordToggles = () => {
		const passwordToggle = document.getElementById('passwordToggle');
		const confirmPasswordToggle = document.getElementById('confirmPasswordToggle');
		const passwordInput = document.getElementById('passwordInput');
		const confirmPasswordInput = document.getElementById('confirmPasswordInput');

		if (passwordToggle && passwordInput) {
			passwordToggle.addEventListener('click', (e) => {
				e.preventDefault();
				e.stopPropagation();
				const isPassword = passwordInput.type === 'password';
				passwordInput.type = isPassword ? 'text' : 'password';
				// Toggle eye icon - you can update the icon class here if desired
				// For now, the icon stays the same and just indicates the field is toggleable
			});
		}

		if (confirmPasswordToggle && confirmPasswordInput) {
			confirmPasswordToggle.addEventListener('click', (e) => {
				e.preventDefault();
				e.stopPropagation();
				const isPassword = confirmPasswordInput.type === 'password';
				confirmPasswordInput.type = isPassword ? 'text' : 'password';
			});
		}
	};

	// Password requirement validation
	const setupPasswordValidation = () => {
		const passwordInput = document.getElementById('passwordInput');
		const passwordRequirements = document.getElementById('passwordRequirements');

		if (!passwordInput || !passwordRequirements) return;

		// Check password requirements in real-time
		const validatePassword = (password) => {
			const requirements = {
				min8: password.length >= 8,
				uppercase: /[A-Z]/.test(password),
				number: /[0-9]/.test(password),
				special: /[!@#$%^&*(),.?":{}|<>]/.test(password)
			};

			// Update visual state of each requirement
			Object.keys(requirements).forEach(req => {
				const element = passwordRequirements.querySelector(`[data-req="${req}"]`);
				if (element) {
					if (requirements[req]) {
						element.style.color = '#22c55e'; // Green for met
					} else {
						element.style.color = '#ef4444'; // Red for unmet
					}
				}
			});

			return requirements;
		};

		// Show requirements when focused or when typing
		passwordInput.addEventListener('focus', () => {
			passwordRequirements.style.display = 'block';
			validatePassword(passwordInput.value);
		});

		// Validate as user types
		passwordInput.addEventListener('input', () => {
			if (passwordInput.value.length > 0) {
				passwordRequirements.style.display = 'block';
				validatePassword(passwordInput.value);
			}
		});

		// Hide requirements when blur and empty
		passwordInput.addEventListener('blur', () => {
			if (passwordInput.value.length === 0) {
				passwordRequirements.style.display = 'none';
			}
		});
	};

	// Upload areas - make clickable
	const setupUploadAreas = () => {
		const uploadArea1 = document.getElementById('uploadArea1');
		const uploadArea2 = document.getElementById('uploadArea2');
		const uploadArea3 = document.getElementById('uploadArea3');
		const idFrontInput = document.getElementById('validIdFrontInput');
		const idBackInput = document.getElementById('validIdBackInput');
		const policeClearanceInput = document.getElementById('policeClearanceInput');

		if (!uploadArea1 || !uploadArea2 || !uploadArea3 || !idFrontInput || !idBackInput || !policeClearanceInput) return;

		const MAX_FILE_SIZE = 5 * 1024 * 1024; // 5MB
		let dragoverTimeout;

		const setupUploadArea = (area, input, previewId, removeId) => {
			const preview = document.getElementById(previewId);
			const removeBtn = document.getElementById(removeId);
			const icon = area.querySelector('.upload-icon');
			const text = area.querySelector('.upload-text');
			
			area.addEventListener('click', (e) => {
				// Don't trigger file input if clicking on remove button
				if (e.target.closest('.upload-remove')) return;
				input.click();
			});
			
			area.addEventListener('dragover', (e) => {
				e.preventDefault();
				e.stopPropagation();
				clearTimeout(dragoverTimeout);
				area.style.borderColor = 'var(--accent-end)';
				area.style.background = 'rgba(245, 124, 0, 0.05)';
			});
			
			area.addEventListener('dragleave', (e) => {
				if (e.target !== area) return;
				dragoverTimeout = setTimeout(() => {
					area.style.borderColor = 'var(--border-color)';
					area.style.background = 'var(--input-bg)';
				}, 50);
			});
			
			area.addEventListener('drop', (e) => {
				e.preventDefault();
				e.stopPropagation();
				area.style.borderColor = 'var(--border-color)';
				area.style.background = 'var(--input-bg)';
				
				if (e.dataTransfer.files.length) {
					input.files = e.dataTransfer.files;
					const event = new Event('change', { bubbles: true });
					input.dispatchEvent(event);
				}
			});
			
			input.addEventListener('change', () => {
				if (input.files && input.files[0]) {
					const file = input.files[0];
					
					// Validate file type - allow images and PDF
					const allowedTypes = ['image/jpeg', 'image/jpg', 'image/png', 'application/pdf'];
					if (!allowedTypes.includes(file.type)) {
						alert('Please select a valid image file (JPG, PNG, PDF)');
						input.value = '';
						return;
					}
					
					// Validate file size upfront
					if (file.size > MAX_FILE_SIZE) {
						alert('File size must be less than 5MB');
						input.value = '';
						return;
					}
					
					// Use URL.createObjectURL instead of FileReader for instant preview
					const blobUrl = URL.createObjectURL(file);
					
					if (preview) {
						// Create new image to load preview
						const img = new Image();
						img.onload = () => {
							preview.src = blobUrl;
							preview.style.display = 'block';
							if (icon) icon.style.display = 'none';
							if (text) text.style.display = 'none';
							if (removeBtn) removeBtn.style.display = 'flex';
						};
						img.onerror = () => {
							// For PDFs, we can't display a preview, so just show file selected
							if (file.type === 'application/pdf') {
								preview.style.display = 'none';
								if (icon) icon.style.display = 'none';
								if (text) {
									text.textContent = 'PDF selected';
									text.style.display = 'block';
								}
								if (removeBtn) removeBtn.style.display = 'flex';
							} else {
								alert('Failed to load image preview');
								URL.revokeObjectURL(blobUrl);
								input.value = '';
							}
						};
						img.src = blobUrl;
					}
				}
			});
			
			// Remove button functionality
			if (removeBtn) {
				removeBtn.addEventListener('click', (e) => {
					e.stopPropagation();
					// Clear file input
					input.value = '';
					// Hide preview and remove button
					if (preview) {
						preview.style.display = 'none';
						if (preview.src) URL.revokeObjectURL(preview.src);
						preview.src = '';
					}
					removeBtn.style.display = 'none';
					// Show upload icon and text again
					if (icon) icon.style.display = 'block';
					if (text) text.style.display = 'block';
				});
			}
		};

		setupUploadArea(uploadArea1, idFrontInput, 'previewArea1', 'removeArea1');
		setupUploadArea(uploadArea2, idBackInput, 'previewArea2', 'removeArea2');
		setupUploadArea(uploadArea3, policeClearanceInput, 'previewArea3', 'removeArea3');
	};

	// Initialize view and uploads
	showStep(currentStep);
	setupUploadAreas();
	setupPasswordToggles();
	setupPasswordValidation();
});