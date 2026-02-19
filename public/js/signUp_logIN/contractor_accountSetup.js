document.addEventListener('DOMContentLoaded', () => {
	let currentStep = 1;
	const totalSteps = 3;
	const form = document.querySelector('.setup-form');
	const nextBtn = document.getElementById('nextBtn');
	const backBtn = document.getElementById('backBtn');
	const progressTabs = document.querySelectorAll('.progress-tab');
	const progressSegments = document.querySelectorAll('.progress-segment');
	const progressLabels = document.querySelectorAll('.progress-label');

	// Toast notification function
	const showToast = (message, type = 'info', duration = 3000) => {
		const toastEl = document.getElementById('toast');
		if (!toastEl) return;

		toastEl.textContent = message;
		toastEl.className = `toast-notification toast-${type}`;
		toastEl.style.display = 'block';
		toastEl.style.whiteSpace = 'pre-wrap';
		toastEl.style.maxHeight = '200px';
		toastEl.style.overflowY = 'auto';

		setTimeout(() => {
			toastEl.style.display = 'none';
		}, duration);
	};

	// Function called after OTP is successfully verified
	window.handleOtpVerified = () => {
		// Hide the OTP modal
		const otpModalBackdrop = document.getElementById('otpModalBackdrop');
		if (otpModalBackdrop) {
			otpModalBackdrop.style.display = 'none';
		}

		// Advance to Step 3
		currentStep = 3;
		showStep(currentStep);

		// Update navigation buttons
		backBtn.style.display = 'inline-block';
		nextBtn.textContent = 'Submit';
	};

	const showStep = (step) => {
		// Hide all steps
		document.querySelectorAll('.form-step').forEach(el => el.classList.remove('active'));

		// Show current step
		document.getElementById(`step-${step}`).classList.add('active');

		// Update progress tabs
		progressTabs.forEach(tab => {
			if (parseInt(tab.dataset.step) === step) {
				tab.classList.add('active');
			} else {
				tab.classList.remove('active');
			}
		});

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

		// Optional title/subtitle if present
		const titles = [
			'Company Information',
			'Account Setup',
			'Verification'
		];
		const subtitles = [
			'Tell us about your company and services.',
			'Create your login credentials.',
			'Verify your identity with official documents.'
		];

		const titleEl = document.querySelector('.setup-title');
		const subtitleEl = document.querySelector('.setup-subtitle');
		if (titleEl) titleEl.textContent = titles[step - 1] || '';
		if (subtitleEl) subtitleEl.textContent = subtitles[step - 1] || '';

		// Update button visibility
		if (step === 1) {
			backBtn.style.display = 'none';
			nextBtn.textContent = 'Next';
		} else if (step === totalSteps) {
			backBtn.style.display = 'block';
			nextBtn.textContent = 'Next';
		} else {
			backBtn.style.display = 'block';
			nextBtn.textContent = 'Next';
		}
	};

	// Modal dropdown helpers
	const modalData = {
		contractorTypes: window.contractorFormData?.contractorTypes || [],
		provinces: window.contractorFormData?.provinces || [],
		cities: window.contractorFormData?.cities || [],
		barangays: window.contractorFormData?.barangays || [],
		pcabCategories: ['AAAA', 'AAA', 'AA', 'A', 'B', 'C', 'D', 'Trade/E']
	};

	const appBasePath = window.appBasePath || '';
	const fallbackBase = `${window.location.origin}${appBasePath}`;
	const psgcBaseUrl = (window.psgcBaseUrl || `${fallbackBase}/api/psgc`).replace(/\/$/, '');

	window.openModal = function(modalId) {
		const modal = document.getElementById(modalId);
		if (!modal) return;

		if (modalId === 'contractorTypeModal') {
			populateModalList('contractorTypeList', modalData.contractorTypes, 'contractorTypeSearch', (item) => {
				selectContractorType(item.type_id || item.id, item.type_name || item.name);
			});
		} else if (modalId === 'provinceModal') {
			populateModalList('provinceList', modalData.provinces, 'provinceSearch', (item) => {
				selectProvince(item.code, item.name);
			});
		} else if (modalId === 'cityModal') {
			const provinceCode = document.getElementById('provinceValue')?.value;
			if (!provinceCode) {
				alert('Please select a province first');
				return;
			}
			if (modalData.cities.length > 0) {
				populateModalList('cityList', modalData.cities, 'citySearch', (item) => {
					selectCity(item.code, item.name);
				});
			} else {
				loadCitiesForModal(provinceCode);
			}
		} else if (modalId === 'barangayModal') {
			const cityCode = document.getElementById('cityValue')?.value;
			if (!cityCode) {
				alert('Please select a city first');
				return;
			}
			if (modalData.barangays.length > 0) {
				populateModalList('barangayList', modalData.barangays, 'barangaySearch', (item) => {
					selectBarangay(item.code, item.name);
				});
			} else {
				loadBarangaysForModal(cityCode);
			}
		} else if (modalId === 'pcabCategoryModal') {
			const categories = modalData.pcabCategories.map((value) => ({ name: value, value }));
			populateModalList('pcabCategoryList', categories, 'pcabCategorySearch', (item) => {
				selectPcabCategory(item.value || item.name);
			});
		}

		modal.style.display = 'flex';
	};

	window.closeModal = function(modalId) {
		const modal = document.getElementById(modalId);
		if (modal) {
			modal.style.display = 'none';
		}
	};

	document.querySelectorAll('.modal-overlay').forEach(overlay => {
		overlay.addEventListener('click', (e) => {
			if (e.target === overlay) {
				closeModal(overlay.id);
			}
		});
	});

	function selectContractorType(id, name) {
		const valueEl = document.getElementById('contractorTypeValue');
		const displayEl = document.getElementById('contractorTypeDisplay');
		if (valueEl) valueEl.value = id || '';
		if (displayEl) displayEl.textContent = name || 'Select contractor type';
		closeModal('contractorTypeModal');
		updateContractorTypeOther(id);
	}

	function selectProvince(code, name) {
		const valueEl = document.getElementById('provinceValue');
		const displayEl = document.getElementById('provinceDisplay');
		if (valueEl) valueEl.value = code || '';
		if (displayEl) displayEl.textContent = name || 'Select province';

		const cityValue = document.getElementById('cityValue');
		const cityDisplay = document.getElementById('cityDisplay');
		const barangayValue = document.getElementById('barangayValue');
		const barangayDisplay = document.getElementById('barangayDisplay');
		if (cityValue) cityValue.value = '';
		if (cityDisplay) cityDisplay.textContent = 'Select city/municipality';
		if (barangayValue) barangayValue.value = '';
		if (barangayDisplay) barangayDisplay.textContent = 'Select city first';

		modalData.cities = [];
		modalData.barangays = [];
		loadCitiesForModal(code);
		closeModal('provinceModal');
	}

	function selectCity(code, name) {
		const valueEl = document.getElementById('cityValue');
		const displayEl = document.getElementById('cityDisplay');
		if (valueEl) valueEl.value = code || '';
		if (displayEl) displayEl.textContent = name || 'Select city/municipality';

		const barangayValue = document.getElementById('barangayValue');
		const barangayDisplay = document.getElementById('barangayDisplay');
		if (barangayValue) barangayValue.value = '';
		if (barangayDisplay) barangayDisplay.textContent = 'Select barangay';

		modalData.barangays = [];
		loadBarangaysForModal(code);
		closeModal('cityModal');
	}

	function selectBarangay(code, name) {
		const valueEl = document.getElementById('barangayValue');
		const displayEl = document.getElementById('barangayDisplay');
		if (valueEl) valueEl.value = code || '';
		if (displayEl) displayEl.textContent = name || 'Select barangay';
		closeModal('barangayModal');
	}

	function selectPcabCategory(value) {
		const valueEl = document.getElementById('pcabCategoryValue');
		const displayEl = document.getElementById('pcabCategoryDisplay');
		if (valueEl) valueEl.value = value || '';
		if (displayEl) displayEl.textContent = value || 'Select PCAB Category';
		closeModal('pcabCategoryModal');
	}

	function populateModalList(listId, items, searchId, onSelect) {
		const listEl = document.getElementById(listId);
		const searchInput = document.getElementById(searchId);
		if (!listEl) return;

		let displayProp = 'name';
		if (items.length > 0 && items[0].type_name) {
			displayProp = 'type_name';
		}

		renderModalItems(listEl, items, onSelect, displayProp);

		if (searchInput) {
			searchInput.value = '';
			searchInput.oninput = () => {
				const query = searchInput.value.toLowerCase();
				const filtered = items.filter(item => {
					const text = item[displayProp] || item.name || '';
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
			button.innerHTML = `<span>${item[displayProp] || item.name}</span>`;
			button.onclick = () => onSelect(item);
			listEl.appendChild(button);
		});
	}

	async function loadCitiesForModal(provinceCode) {
		const listEl = document.getElementById('cityList');
		if (!listEl) return;
		listEl.innerHTML = '<div style="padding: 20px; text-align: center; color: #9ca3af;">Loading cities...</div>';
		try {
			const response = await fetch(`${psgcBaseUrl}/provinces/${provinceCode}/cities`);
			if (!response.ok) throw new Error(`Cities request failed: ${response.status}`);
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

	async function loadBarangaysForModal(cityCode) {
		const listEl = document.getElementById('barangayList');
		if (!listEl) return;
		listEl.innerHTML = '<div style="padding: 20px; text-align: center; color: #9ca3af;">Loading barangays...</div>';
		try {
			const response = await fetch(`${psgcBaseUrl}/cities/${cityCode}/barangays`);
			if (!response.ok) throw new Error(`Barangays request failed: ${response.status}`);
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

	const validateStep = (step) => {
		const stepElement = document.getElementById(`step-${step}`);
		const inputs = stepElement.querySelectorAll('input[required], select[required], textarea[required]');

		for (let input of inputs) {
			if (!input.value.trim()) {
				input.focus();
				alert('Please fill in all required fields.');
				return false;
			}
		}

		// Step 2 specific validation: check password match
		if (step === 2) {
			const passwordInput = document.getElementById('passwordInput');
			const confirmPasswordInput = document.getElementById('confirmPasswordInput');
			const passwordMismatchError = document.getElementById('passwordMismatchError');

			if (passwordInput && confirmPasswordInput) {
				if (passwordInput.value !== confirmPasswordInput.value) {
					passwordMismatchError.style.display = 'block';
					confirmPasswordInput.focus();
					return false;
				} else {
					passwordMismatchError.style.display = 'none';
				}

				// Validate password length (min 8 characters)
				if (passwordInput.value.length < 8) {
					alert('Password must be at least 8 characters long.');
					passwordInput.focus();
					return false;
				}
			}
		}

		return true;
	};

	// Real-time password mismatch validation for Step 2
	const passwordInput = document.getElementById('passwordInput');
	const confirmPasswordInput = document.getElementById('confirmPasswordInput');
	const passwordMismatchError = document.getElementById('passwordMismatchError');

	if (confirmPasswordInput && passwordMismatchError) {
		confirmPasswordInput.addEventListener('input', () => {
			if (confirmPasswordInput.value && passwordInput.value !== confirmPasswordInput.value) {
				passwordMismatchError.style.display = 'block';
			} else {
				passwordMismatchError.style.display = 'none';
			}
		});
	}

	// Password requirement validation
	const setupPasswordValidation = () => {
		const passwordInput = document.getElementById('passwordInput');
		const passwordRequirements = document.getElementById('passwordRequirements');

		if (!passwordInput || !passwordRequirements) return;

		const validatePassword = (password) => {
			const requirements = {
				min8: password.length >= 8,
				uppercase: /[A-Z]/.test(password),
				number: /[0-9]/.test(password),
				special: /[!@#$%^&*(),.?":{}|<>]/.test(password)
			};

			Object.keys(requirements).forEach(req => {
				const element = passwordRequirements.querySelector(`[data-req="${req}"]`);
				if (element) {
					element.style.color = requirements[req] ? '#22c55e' : '#ef4444';
				}
			});
		};

		passwordInput.addEventListener('focus', () => {
			passwordRequirements.style.display = 'block';
			validatePassword(passwordInput.value);
		});

		passwordInput.addEventListener('input', () => {
			if (passwordInput.value.length > 0) {
				passwordRequirements.style.display = 'block';
				validatePassword(passwordInput.value);
			}
		});

		passwordInput.addEventListener('blur', () => {
			if (passwordInput.value.length === 0) {
				passwordRequirements.style.display = 'none';
			}
		});
	};

	setupPasswordValidation();

	// Handle contractor type "Others" conditional display
	const contractorTypeValue = document.getElementById('contractorTypeValue');
	const contractorTypeOtherWrapper = document.getElementById('contractor_type_other_wrapper');
	const contractorTypeOtherInput = document.querySelector('input[name="contractor_type_other_text"]');
	const updateContractorTypeOther = (value) => {
		if (!contractorTypeOtherWrapper) return;
		if (String(value) === '9') {
			contractorTypeOtherWrapper.style.display = 'block';
			if (contractorTypeOtherInput) {
				contractorTypeOtherInput.setAttribute('required', 'required');
			}
		} else {
			contractorTypeOtherWrapper.style.display = 'none';
			if (contractorTypeOtherInput) {
				contractorTypeOtherInput.removeAttribute('required');
				contractorTypeOtherInput.value = '';
			}
		}
	};

	if (contractorTypeValue) {
		updateContractorTypeOther(contractorTypeValue.value);
	}

	// Calculate years of experience from founded_date
	const foundedDateInput = document.getElementById('founded_date');
	const yearsOfExperienceInput = document.getElementById('years_of_experience');

	if (foundedDateInput && yearsOfExperienceInput) {
		const calculateYearsOfExperience = () => {
			if (foundedDateInput.value) {
				const foundedDate = new Date(foundedDateInput.value);
				const today = new Date();
				let yearsOfExperience = today.getFullYear() - foundedDate.getFullYear();
				
				// Check if anniversary hasn't occurred this year
				const currentMonth = today.getMonth();
				const currentDay = today.getDate();
				const foundedMonth = foundedDate.getMonth();
				const foundedDay = foundedDate.getDate();

				if (currentMonth < foundedMonth || (currentMonth === foundedMonth && currentDay < foundedDay)) {
					yearsOfExperience--;
				}

				// Format: "X years (selected YYYY-MM-DD)"
				const selectedDate = foundedDateInput.value; // Already in YYYY-MM-DD format
				const yearText = yearsOfExperience > 1 ? 'years' : 'year';
				yearsOfExperienceInput.value = yearsOfExperience > 0 ? `${yearsOfExperience} ${yearText} (selected ${selectedDate})` : `Less than 1 year (selected ${selectedDate})`;
			} else {
				yearsOfExperienceInput.value = '';
			}
		};

		foundedDateInput.addEventListener('change', calculateYearsOfExperience);
		// Calculate on page load if date is already set
		calculateYearsOfExperience();
	}

	// Auto-prepend https:// to website URL if missing protocol
	const companyWebsiteInput = document.getElementById('company_website');
	if (companyWebsiteInput) {
		companyWebsiteInput.addEventListener('blur', () => {
			const value = companyWebsiteInput.value.trim();
			if (value && !value.match(/^https?:\/\//i)) {
				companyWebsiteInput.value = 'https://' + value;
			}
		});
	}

	nextBtn.addEventListener('click', () => {
		if (validateStep(currentStep)) {
			// Step 1: Submit via AJAX to save company information
			if (currentStep === 1) {
				// Show loading state
				const originalText = nextBtn.innerHTML;
				nextBtn.disabled = true;
				nextBtn.classList.add('loading');
				nextBtn.innerHTML = '<span class="spinner"></span>Loading...';

				// Collect Step 1 form data
				const step1Data = new FormData();
				const step1Element = document.getElementById('step-1');
				const step1Inputs = step1Element.querySelectorAll('input, select, textarea');
				
				// Get CSRF token
				const csrfTokenInput = document.querySelector('input[name="_token"]');
				const csrfToken = csrfTokenInput ? csrfTokenInput.value : '';
				step1Data.append('_token', csrfToken);
				
				// Add all Step 1 fields
				step1Inputs.forEach(input => {
					if (input.name) {
						step1Data.append(input.name, input.value);
					}
				});

				// Submit via AJAX to /accounts/signup/contractor/step1
				fetch('/accounts/signup/contractor/step1', {
					method: 'POST',
					body: step1Data,
					headers: {
						'X-Requested-With': 'XMLHttpRequest',
					},
					credentials: 'include'
				})
				.then(response => response.json())
				.then(data => {
					if (data.success) {
						// Advance to Step 2
						currentStep++;
						showStep(currentStep);
						// Restore button state
						nextBtn.disabled = false;
						nextBtn.classList.remove('loading');
						nextBtn.innerHTML = originalText;
					} else {
						// Show validation errors
						if (data.errors) {
							const errorMessages = Object.values(data.errors).flat().join('\n');
							showToast('Validation Error:\n' + errorMessages, 'error', 5000);
						} else {
							showToast(data.message || 'Error saving company information', 'error');
						}
						// Restore button state
						nextBtn.disabled = false;
						nextBtn.classList.remove('loading');
						nextBtn.innerHTML = originalText;
					}
				})
				.catch(error => {
					console.error('Error:', error);
					showToast('Network error. Please try again.', 'error');
					// Restore button state
					nextBtn.disabled = false;
					nextBtn.classList.remove('loading');
					nextBtn.innerHTML = originalText;
				});
			} else if (currentStep === 2) {
				// Step 2: Submit via AJAX and redirect to OTP verification
				// Show loading state
				const originalText = nextBtn.innerHTML;
				nextBtn.disabled = true;
				nextBtn.classList.add('loading');
				nextBtn.innerHTML = '<span class="spinner"></span>Loading...';

				// Collect Step 2 form data
				const step2Data = new FormData();
				const step2Element = document.getElementById('step-2');
				const step2Inputs = step2Element.querySelectorAll('input, select, textarea');
				
				// Get CSRF token from hidden input in form
				const csrfTokenInput = document.querySelector('input[name="_token"]');
				const csrfToken = csrfTokenInput ? csrfTokenInput.value : '';

				// Add CSRF token
				step2Data.append('_token', csrfToken);
				step2Data.append('step', '2');

				// Log the fields being sent
				const fieldsBeingSent = {};
				
				// Add all Step 2 fields
				step2Inputs.forEach(input => {
					if (input.name) {
						step2Data.append(input.name, input.value);
						fieldsBeingSent[input.name] = input.value;
					}
				});

				console.log('Submitting Step 2 data:', {
					step: '2',
					fields: fieldsBeingSent,
					hasToken: !!csrfToken,
					tokenValue: csrfToken
				});

				// Submit via AJAX
				fetch(form.action, {
					method: 'POST',
					body: step2Data,
					headers: {
						'X-Requested-With': 'XMLHttpRequest',
					},
					credentials: 'include' // Include cookies for session persistence
				})
				.then(response => {
					console.log('Response status:', response.status);
					return response.json();
				})
				.then(data => {
					console.log('Step 2 response:', data);
					if (data.success) {
						// Show toast and display OTP modal on the same page
						showToast('Verification code sent to your email', 'success', 2000);
						// Show the OTP modal instead of redirecting
						const otpModalBackdrop = document.getElementById('otpModalBackdrop');
						if (otpModalBackdrop) {
							otpModalBackdrop.style.display = 'flex';
						}
						// Restore button state
						nextBtn.disabled = false;
						nextBtn.classList.remove('loading');
						nextBtn.innerHTML = originalText;
					} else {
						// Show validation errors
						if (data.errors) {
							const errorMessages = Object.values(data.errors)
								.flat()
								.join('\n');
							console.error('Validation errors:', data.errors);
							showToast('Validation Error:\n' + errorMessages, 'error', 5000);
						} else {
							showToast(data.message || 'Error sending verification code', 'error');
						}
						// Restore button state on error
						nextBtn.disabled = false;
						nextBtn.classList.remove('loading');
						nextBtn.innerHTML = originalText;
					}
				})
				.catch(error => {
					console.error('Error:', error);
					showToast('Network error. Please try again.', 'error');
					// Restore button state on error
					nextBtn.disabled = false;
					nextBtn.classList.remove('loading');
					nextBtn.innerHTML = originalText;
				});
			} else if (currentStep === totalSteps) {
				// Step 3: Submit the form with all data
				// Show loading state
				const originalText = nextBtn.innerHTML;
				nextBtn.disabled = true;
				nextBtn.classList.add('loading');
				nextBtn.innerHTML = '<span class="spinner"></span>Submitting...';

				// Set the step value for backend routing
				const stepInput = document.getElementById('stepInput');
				if (stepInput) {
					stepInput.value = '3';
				}

				// Convert PSGC codes to names before form submission (to match mobile app format)
				const convertAddressCodestoNames = () => {
					const provinceValueEl = document.getElementById('provinceValue');
					const cityValueEl = document.getElementById('cityValue');
					const barangayValueEl = document.getElementById('barangayValue');

					const getNameByCode = (items, code) => {
						if (!code || !Array.isArray(items)) return code;
						const match = items.find(item => String(item.code) === String(code));
						return match ? match.name : code;
					};

					const provinceName = getNameByCode(modalData.provinces, provinceValueEl?.value);
					const cityName = getNameByCode(modalData.cities, cityValueEl?.value);
					const barangayName = getNameByCode(modalData.barangays, barangayValueEl?.value);

					if (provinceValueEl && provinceName) provinceValueEl.value = provinceName;
					if (cityValueEl && cityName) cityValueEl.value = cityName;
					if (barangayValueEl && barangayName) barangayValueEl.value = barangayName;
				};

				// Convert before submission
				convertAddressCodestoNames();

				// Now submit the form
				form.submit();
			}
		}
	});

	backBtn.addEventListener('click', () => {
		if (currentStep > 1) {
			currentStep--;
			showStep(currentStep);
		}
	});

	// Tab clicks (only backward navigation)
	progressTabs.forEach(tab => {
		tab.addEventListener('click', () => {
			const step = parseInt(tab.dataset.step);
			if (step < currentStep) {
				currentStep = step;
				showStep(currentStep);
			}
		});
	});

	// Upload areas - make clickable & support drag/drop
	const setupUploadAreas = () => {
		const MAX_FILE_SIZE = 5 * 1024 * 1024; // 5MB in bytes
		const uploadAreas = document.querySelectorAll('.upload-area');
		
		uploadAreas.forEach((area) => {
			const inputId = area.dataset.input;
			const input = inputId ? document.getElementById(inputId) : null;
			if (!input) return;

			// Get preview and remove button elements
			const preview = area.querySelector('.upload-preview');
			const removeBtn = area.querySelector('.upload-remove');
			const icon = area.querySelector('.upload-icon');
			const text = area.querySelector('.upload-text');
			
			let dragoverTimeout;

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
						showToast('Please select a valid image file (JPG, PNG, PDF)', 'error');
						input.value = '';
						return;
					}
					
					// Validate file size upfront
					if (file.size > MAX_FILE_SIZE) {
						showToast('File size must be less than 5MB', 'error');
						input.value = '';
						return;
					}
					
					// Use URL.createObjectURL for instant preview
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
									text.textContent = 'PDF selected: ' + file.name;
									text.style.display = 'block';
								}
								if (removeBtn) removeBtn.style.display = 'flex';
							} else {
								showToast('Failed to load image preview', 'error');
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
					if (text) {
						text.textContent = 'Upload image or file';
						text.style.display = 'block';
					}
				});
			}
		});
	};

	// OTP Modal close button handler
	const closeOtpBtn = document.getElementById('closeOtpBtn');
	if (closeOtpBtn) {
		closeOtpBtn.addEventListener('click', () => {
			const otpModalBackdrop = document.getElementById('otpModalBackdrop');
			if (otpModalBackdrop) {
				otpModalBackdrop.style.display = 'none';
			}
		});
	}

	// Check URL for step parameter (set after OTP verification)
	const urlParams = new URLSearchParams(window.location.search);
	const stepParam = urlParams.get('step');
	if (stepParam && !isNaN(stepParam)) {
		const requestedStep = parseInt(stepParam);
		if (requestedStep >= 1 && requestedStep <= totalSteps) {
			currentStep = requestedStep;
		}
	}

	showStep(currentStep);
	setupUploadAreas();
});
