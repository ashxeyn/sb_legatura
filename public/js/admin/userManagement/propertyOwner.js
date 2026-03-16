document.addEventListener('DOMContentLoaded', function () {

  const dateFromInput = document.getElementById('dateFrom');
  const dateToInput = document.getElementById('dateTo');
  const searchInput = document.getElementById('searchInput') || document.getElementById('topNavSearch');
  const resetBtn = document.getElementById('resetFilterBtn');
  const ownersWrap = document.getElementById('ownersTableWrap');

  let debounceTimer;

  function animateOwnerTableRows() {
    const rows = document.querySelectorAll('#propertyOwnersTable tr');
    rows.forEach((row, index) => {
      row.style.opacity = '0';
      row.style.transform = 'translateY(20px)';

      setTimeout(() => {
        row.style.transition = 'all 0.4s ease';
        row.style.opacity = '1';
        row.style.transform = 'translateY(0)';
      }, index * 50);
    });
  }

  async function fetchAndUpdate(url) {
    try {
      const response = await fetch(url, {
        headers: {
          'X-Requested-With': 'XMLHttpRequest'
        }
      });

      if (!response.ok) throw new Error('Network response was not ok');

      const data = await response.json();

      if (ownersWrap && data.html) {
        ownersWrap.innerHTML = data.html;
      }

      window.history.pushState({}, '', url);

      attachPaginationListeners();

      attachActionListeners();

    } catch (error) {
      console.error('Error fetching data:', error);
    }
  }

  function buildUrl() {
    const url = new URL(window.location.href);
    const params = new URLSearchParams(url.search);

    if (dateFromInput && dateFromInput.value) {
      params.set('date_from', dateFromInput.value);
    } else {
      params.delete('date_from');
    }

    if (dateToInput && dateToInput.value) {
      params.set('date_to', dateToInput.value);
    } else {
      params.delete('date_to');
    }

    if (searchInput && searchInput.value) {
      params.set('search', searchInput.value);
    } else {
      params.delete('search');
    }

    params.delete('page');

    return `${url.pathname}?${params.toString()}`;
  }

  function handleFilterChange() {
    const url = buildUrl();
    fetchAndUpdate(url);
  }

  function handleSearchInput() {
    clearTimeout(debounceTimer);
    debounceTimer = setTimeout(() => {
      handleFilterChange();
    }, 300); // 300ms debounce
  }

  function attachPaginationListeners() {
    const paginationLinks = document.querySelectorAll('#ownersTableWrap .owner-page-link, #ownersTableWrap .pagination a');
    paginationLinks.forEach(link => {
      link.addEventListener('click', function (e) {
        e.preventDefault();
        const url = this.href;
        fetchAndUpdate(url);
      });
    });
  }

  if (dateFromInput) {
    dateFromInput.addEventListener('change', function () {
      if (this.value) {
        dateToInput.min = this.value;
        // If dateTo is already set and is before dateFrom, clear it
        if (dateToInput.value && dateToInput.value < this.value) {
          dateToInput.value = '';
        }
      }
      handleFilterChange();
    });
  }
  if (dateToInput) {
    dateToInput.addEventListener('change', function () {
      if (this.value) {
        dateFromInput.max = this.value;
        // If dateFrom is already set and is after dateTo, clear it
        if (dateFromInput.value && dateFromInput.value > this.value) {
          dateFromInput.value = '';
        }
      }
      handleFilterChange();
    });
  }
  if (searchInput) searchInput.addEventListener('input', handleSearchInput);

  if (resetBtn) {
    resetBtn.addEventListener('click', function () {
      if (dateFromInput) dateFromInput.value = '';
      if (dateToInput) dateToInput.value = '';
      if (searchInput) searchInput.value = '';
      handleFilterChange();
    });
  }

  attachPaginationListeners();

  const urlParams = new URLSearchParams(window.location.search);
  if (dateFromInput && urlParams.has('date_from')) {
    dateFromInput.value = urlParams.get('date_from');
  }
  if (dateToInput && urlParams.has('date_to')) {
    dateToInput.value = urlParams.get('date_to');
  }
  if (searchInput && urlParams.has('search')) {
    searchInput.value = urlParams.get('search');
  }

  function attachActionListeners() {
    const viewButtons = document.querySelectorAll('.view-btn');
    const editButtons = document.querySelectorAll('.edit-btn');
    const deleteButtons = document.querySelectorAll('.delete-btn');

    viewButtons.forEach(btn => {
      btn.addEventListener('click', function (e) {
        e.stopPropagation();
        addRipple(this, e);
        const id = this.getAttribute('data-id');
        if (id) {
          setTimeout(() => {
            window.location.href = `/admin/user-management/property-owners/${id}`;
          }, 200);
        }
      });
    });

    editButtons.forEach(btn => {
      btn.addEventListener('click', function (e) {
        e.stopPropagation();
        addRipple(this, e);
        const id = this.getAttribute('data-id');
        if (id) {
          openEditModal(id);
        }
      });
    });

    deleteButtons.forEach(btn => {
      btn.addEventListener('click', function (e) {
        e.stopPropagation();
        const row = this.closest('tr');
        const nameElement = row ? row.querySelector('.font-medium, .font-semibold') : null;
        const name = nameElement ? nameElement.textContent.trim() : 'this user';
        const id = this.getAttribute('data-id');

        addRipple(this, e);

        setTimeout(() => {
          openDeleteModal(name, row, id);
        }, 200);
      });
    });

    const tableRows = document.querySelectorAll('#propertyOwnersTable tr');
    tableRows.forEach(row => {
      row.addEventListener('click', function () {
        tableRows.forEach(r => r.classList.remove('bg-indigo-50'));
        this.classList.add('bg-indigo-50');
      });
    });
  }

  attachActionListeners();

  const addBtn = document.querySelector('#addPropertyOwnerBtn');
  const modal = document.getElementById('addPropertyOwnerModal');
  const closeModalBtn = document.getElementById('closeModalBtn');
  const cancelBtn = document.getElementById('cancelBtn');
  const saveBtn = document.getElementById('saveBtn');

  const addFormErrorFieldMap = {
    'addFirstName': 'addFirstNameError',
    'addLastName': 'addLastNameError',
    'occupationSelect': 'addOccupationError',
    'occupationOtherInput': 'addOccupationError',
    'addEmail': 'addEmailError',
    'addDateOfBirth': 'addDateOfBirthError',
    'owner_address_province': 'addProvinceError',
    'owner_address_city': 'addCityError',
    'owner_address_barangay': 'addBarangayError',
    'addStreetAddress': 'addStreetAddressError',
    'addZipCode': 'addZipCodeError',
    'addValidIdType': 'addValidIdTypeError',
    'idFrontUpload': 'addIdFrontError',
    'idBackUpload': 'addIdBackError',
    'policeClearanceUpload': 'addPoliceClearanceError'
  };

  const addFormErrorTargetMap = {
    'addFirstNameError': 'addFirstName',
    'addLastNameError': 'addLastName',
    'addOccupationError': 'occupationSelect',
    'addEmailError': 'addEmail',
    'addDateOfBirthError': 'addDateOfBirth',
    'addProvinceError': 'owner_address_province',
    'addCityError': 'owner_address_city',
    'addBarangayError': 'owner_address_barangay',
    'addStreetAddressError': 'addStreetAddress',
    'addZipCodeError': 'addZipCode',
    'addValidIdTypeError': 'addValidIdType',
    'addIdFrontError': 'idFrontUploadArea',
    'addIdBackError': 'idBackUploadArea',
    'addPoliceClearanceError': 'policeClearanceUploadArea'
  };

  function getAddFieldByIdOrName(id, name) {
    return document.getElementById(id) || (modal ? modal.querySelector(`[name="${name}"]`) : null);
  }

  function getFallbackTargetForError(errorElementId) {
    if (errorElementId === 'addStreetAddressError') {
      return getAddFieldByIdOrName('addStreetAddress', 'street_address');
    }
    if (errorElementId === 'addZipCodeError') {
      return getAddFieldByIdOrName('addZipCode', 'zip_code');
    }
    return null;
  }

  function clearAddFieldError(errorElementId) {
    const errorElement = document.getElementById(errorElementId);
    if (errorElement) {
      errorElement.classList.add('hidden');
      errorElement.textContent = '';
    }

    let targetId = addFormErrorTargetMap[errorElementId];
    if (errorElementId === 'addOccupationError') {
      const occupationSelect = document.getElementById('occupationSelect');
      const occupationOtherInput = document.getElementById('occupationOtherInput');
      const useOtherInput = occupationSelect && occupationSelect.value === 'others' && occupationOtherInput && !occupationOtherInput.classList.contains('hidden');
      targetId = useOtherInput ? 'occupationOtherInput' : 'occupationSelect';
    }

    let targetElement = targetId ? document.getElementById(targetId) : null;
    if (!targetElement) {
      targetElement = getFallbackTargetForError(errorElementId);
    }
    if (targetElement) {
      targetElement.classList.remove('error');

      const dynamicError = targetElement.parentElement
        ? targetElement.parentElement.querySelector(`.dynamic-add-owner-error[data-error-id="${errorElementId}"]`)
        : null;
      if (dynamicError) {
        dynamicError.remove();
      }
    }
  }

  function setAddFieldError(errorElementId, message) {
    const errorElement = document.getElementById(errorElementId);
    if (errorElement) {
      errorElement.textContent = message;
      errorElement.classList.remove('hidden');
    }

    let targetId = addFormErrorTargetMap[errorElementId];
    if (errorElementId === 'addOccupationError') {
      const occupationSelect = document.getElementById('occupationSelect');
      const occupationOtherInput = document.getElementById('occupationOtherInput');
      const useOtherInput = occupationSelect && occupationSelect.value === 'others' && occupationOtherInput && !occupationOtherInput.classList.contains('hidden');
      targetId = useOtherInput ? 'occupationOtherInput' : 'occupationSelect';
    }

    let targetElement = targetId ? document.getElementById(targetId) : null;
    if (!targetElement) {
      targetElement = getFallbackTargetForError(errorElementId);
    }
    if (targetElement) {
      targetElement.classList.add('error');

      if (!errorElement && targetElement.parentElement) {
        let dynamicError = targetElement.parentElement.querySelector(`.dynamic-add-owner-error[data-error-id="${errorElementId}"]`);
        if (!dynamicError) {
          dynamicError = document.createElement('p');
          dynamicError.className = 'dynamic-add-owner-error add-owner-error text-red-500 text-xs mt-1';
          dynamicError.dataset.errorId = errorElementId;
          targetElement.parentElement.appendChild(dynamicError);
        }
        dynamicError.textContent = message;
      }
    }
  }

  function clearAllAddOwnerErrors() {
    Object.values(addFormErrorFieldMap).forEach(errorId => {
      clearAddFieldError(errorId);
    });

    if (modal) {
      modal.querySelectorAll('.add-owner-field.error').forEach(el => el.classList.remove('error'));
      modal.querySelectorAll('.add-owner-dropzone.error').forEach(el => el.classList.remove('error'));
    }
  }

  if (addBtn && modal) {

    addBtn.addEventListener('click', function () {
      modal.classList.remove('hidden');
      document.body.style.overflow = 'hidden';

      clearAllAddOwnerErrors();

      const modalContent = modal.querySelector('.modal-content');
      modalContent.style.transform = 'scale(0.9)';
      modalContent.style.opacity = '0';

      setTimeout(() => {
        modalContent.style.transition = 'all 0.3s ease';
        modalContent.style.transform = 'scale(1)';
        modalContent.style.opacity = '1';
      }, 10);
    });

    const closeModal = () => {
      const modalContent = modal.querySelector('.modal-content');
      modalContent.style.transform = 'scale(0.9)';
      modalContent.style.opacity = '0';

      setTimeout(() => {
        modal.classList.add('hidden');
        document.body.style.overflow = 'auto';

        resetModalForm();
      }, 300);
    };

    closeModalBtn.addEventListener('click', closeModal);
    cancelBtn.addEventListener('click', closeModal);

    modal.addEventListener('click', function (e) {
      if (e.target === modal) {
        closeModal();
      }
    });

    document.addEventListener('keydown', function (e) {
      if (e.key === 'Escape' && !modal.classList.contains('hidden')) {
        closeModal();
      }
    });

    Object.entries(addFormErrorFieldMap).forEach(([fieldId, errorId]) => {
      const field = document.getElementById(fieldId);
      if (field) {
        field.addEventListener('change', () => clearAddFieldError(errorId));
        field.addEventListener('input', () => clearAddFieldError(errorId));
      }
    });

    // Fallback listeners in case markup is stale and id attributes are missing
    ['street_address', 'zip_code'].forEach(name => {
      const field = modal.querySelector(`[name="${name}"]`);
      const errorId = name === 'street_address' ? 'addStreetAddressError' : 'addZipCodeError';
      if (field) {
        field.addEventListener('change', () => clearAddFieldError(errorId));
        field.addEventListener('input', () => clearAddFieldError(errorId));
      }
    });

    // Validation function
    const validateAddForm = () => {
      let isValid = true;
      const errors = {};

      // Clear all errors first
      clearAllAddOwnerErrors();

      // Check First Name
      const firstName = document.getElementById('addFirstName');
      if (!firstName || !firstName.value.trim()) {
        errors['addFirstNameError'] = 'First name is required';
        isValid = false;
      }

      // Check Last Name
      const lastName = document.getElementById('addLastName');
      if (!lastName || !lastName.value.trim()) {
        errors['addLastNameError'] = 'Last name is required';
        isValid = false;
      }

      // Check Occupation
      const occupation = document.getElementById('occupationSelect');
      if (!occupation || !occupation.value) {
        errors['addOccupationError'] = 'Please select an occupation';
        isValid = false;
      }

      // Check Email
      const email = document.getElementById('addEmail');
      if (!email || !email.value.trim()) {
        errors['addEmailError'] = 'Email is required';
        isValid = false;
      } else if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email.value)) {
        errors['addEmailError'] = 'Please enter a valid email address';
        isValid = false;
      }

      // Check Date of Birth
      const dateOfBirth = document.getElementById('addDateOfBirth');
      if (!dateOfBirth || !dateOfBirth.value) {
        errors['addDateOfBirthError'] = 'Date of birth is required';
        isValid = false;
      } else {
        // Check if user is at least 15 years old
        const birthDate = new Date(dateOfBirth.value);
        const today = new Date();
        const age = today.getFullYear() - birthDate.getFullYear();
        const monthDiff = today.getMonth() - birthDate.getMonth();
        const dayDiff = today.getDate() - birthDate.getDate();
        
        const actualAge = monthDiff < 0 || (monthDiff === 0 && dayDiff < 0) ? age - 1 : age;
        
        if (actualAge < 15) {
          errors['addDateOfBirthError'] = 'User must be at least 15 years old';
          isValid = false;
        }
      }

      // Check Province
      const province = document.getElementById('owner_address_province');
      if (!province || !province.value) {
        errors['addProvinceError'] = 'Please select a province';
        isValid = false;
      }

      // Check City
      const city = document.getElementById('owner_address_city');
      if (!city || !city.value) {
        errors['addCityError'] = 'Please select a city/municipality';
        isValid = false;
      }

      // Check Barangay
      const barangay = document.getElementById('owner_address_barangay');
      if (!barangay || !barangay.value) {
        errors['addBarangayError'] = 'Please select a barangay';
        isValid = false;
      }

      // Check Street Address
      const streetAddress = getAddFieldByIdOrName('addStreetAddress', 'street_address');
      if (!streetAddress || !streetAddress.value.trim()) {
        errors['addStreetAddressError'] = 'Street address / unit no. is required';
        isValid = false;
      }

      // Check Zip Code
      const zipCode = getAddFieldByIdOrName('addZipCode', 'zip_code');
      if (!zipCode || !zipCode.value.trim()) {
        errors['addZipCodeError'] = 'Zip code is required';
        isValid = false;
      }

      // Check Valid ID Type
      const validIdType = document.getElementById('addValidIdType');
      if (!validIdType || !validIdType.value) {
        errors['addValidIdTypeError'] = 'Please select a valid ID type';
        isValid = false;
      }

      // Check Valid ID Front
      const idFront = document.getElementById('idFrontUpload');
      if (!idFront || !idFront.files || idFront.files.length === 0) {
        errors['addIdFrontError'] = 'Valid ID (Front) is required';
        isValid = false;
      }

      // Check Valid ID Back
      const idBack = document.getElementById('idBackUpload');
      if (!idBack || !idBack.files || idBack.files.length === 0) {
        errors['addIdBackError'] = 'Valid ID (Back) is required';
        isValid = false;
      }

      // Check Police Clearance
      const policeClearance = document.getElementById('policeClearanceUpload');
      if (!policeClearance || !policeClearance.files || policeClearance.files.length === 0) {
        errors['addPoliceClearanceError'] = 'Police Clearance is required';
        isValid = false;
      }

      // Display errors
      Object.entries(errors).forEach(([errorId, message]) => {
        setAddFieldError(errorId, message);
      });

      return isValid;
    };

    saveBtn.addEventListener('click', async function () {
      // Validate form first
      if (!validateAddForm()) {
        return;
      }

      const formData = new FormData();

      const inputs = modal.querySelectorAll('input, select');
      inputs.forEach(input => {
        if (input.type === 'file') {
          if (input.files[0]) {
            formData.append(input.name, input.files[0]);
          }
        } else if (input.type === 'checkbox' || input.type === 'radio') {
          if (input.checked) {
            formData.append(input.name, input.value);
          }
        } else if (input.tagName === 'SELECT') {

          if (input.id === 'owner_address_province' || input.id === 'owner_address_city' || input.id === 'owner_address_barangay') {
            if (input.selectedIndex > 0) {
              const name = input.options[input.selectedIndex].getAttribute('data-name');
              const fieldName = input.id === 'owner_address_province' ? 'province_name' :
                (input.id === 'owner_address_city' ? 'city_name' : 'barangay_name');
              formData.append(fieldName, name);
              formData.append(input.name, input.value); // Keep code as well
            }
          } else {
            formData.append(input.name, input.value);
          }
        } else {
          formData.append(input.name, input.value);
        }
      });

      const csrfToken = document.querySelector('meta[name="csrf-token"]');
      if (csrfToken) {
        formData.append('_token', csrfToken.content);
      }

      // Debug: Log all form data being sent
      console.log('=== Form Data Being Sent ===');
      for (let [key, value] of formData.entries()) {
        if (value instanceof File) {
          console.log(key + ':', value.name, '(' + value.size + ' bytes)');
        } else {
          console.log(key + ':', value);
        }
      }
      console.log('============================');

      clearAllAddOwnerErrors();
      modal.querySelectorAll('.error-message').forEach(el => el.remove());
      modal.querySelectorAll('.border-red-500').forEach(el => el.classList.remove('border-red-500'));

      try {
        const response = await fetch('/admin/user-management/property-owners/store', {
          method: 'POST',
          headers: {
            'X-Requested-With': 'XMLHttpRequest'
          },
          body: formData
        });

        const result = await response.json();

        if (response.ok) {
          showNotification('Property Owner added successfully!', 'success');
          closeModal();

          handleFilterChange();
        } else {
          if (result.errors) {
            const backendFieldMap = {
              first_name: 'addFirstNameError',
              last_name: 'addLastNameError',
              occupation_id: 'addOccupationError',
              occupation_other: 'addOccupationError',
              email: 'addEmailError',
              date_of_birth: 'addDateOfBirthError',
              province: 'addProvinceError',
              province_name: 'addProvinceError',
              city: 'addCityError',
              city_name: 'addCityError',
              barangay: 'addBarangayError',
              barangay_name: 'addBarangayError',
              street_address: 'addStreetAddressError',
              zip_code: 'addZipCodeError',
              valid_id_id: 'addValidIdTypeError',
              valid_id_photo: 'addIdFrontError',
              valid_id_back_photo: 'addIdBackError',
              police_clearance: 'addPoliceClearanceError'
            };

            for (const [key, messages] of Object.entries(result.errors)) {
              const errorMsg = Array.isArray(messages) ? messages[0] : messages;

              const mappedErrorId = backendFieldMap[key];
              if (mappedErrorId) {
                setAddFieldError(mappedErrorId, errorMsg);
              }
            }
          } else {
            alert(result.message || 'An error occurred');
          }
        }
      } catch (error) {
        console.error('Error saving property owner:', error);
        alert('An error occurred while saving.');
      }
    });
  }

  const occupationSelect = document.getElementById('occupationSelect');
  const occupationOtherInput = document.getElementById('occupationOtherInput');

  if (occupationSelect && occupationOtherInput) {
    occupationSelect.addEventListener('change', function () {
      if (this.value === 'others') {
        occupationOtherInput.classList.remove('hidden');
      } else {
        occupationOtherInput.classList.add('hidden');
        occupationOtherInput.value = '';
      }
    });
  }

  // PSGC Address Loading for Add Modal
  const addProvince = document.getElementById('owner_address_province');
  const addCity = document.getElementById('owner_address_city');
  const addBarangay = document.getElementById('owner_address_barangay');

  if (addProvince) {
    addProvince.addEventListener('change', function () {
      const provinceCode = this.value;

      addCity.innerHTML = '<option value="">Loading...</option>';
      addCity.disabled = true;
      addBarangay.innerHTML = '<option value="">Select City First</option>';
      addBarangay.disabled = true;

      if (provinceCode) {
        fetch('/api/psgc/provinces/' + provinceCode + '/cities')
          .then(response => response.json())
          .then(json => {
            console.log('Cities response:', json);
            const data = json.data || json || [];
            if (!Array.isArray(data)) {
              console.error('Cities data is not an array:', data);
              addCity.innerHTML = '<option value="">Error: Invalid data format</option>';
              return;
            }
            addCity.innerHTML = '<option value="">Select City/Municipality</option>';
            data.forEach(function (city) {
              const option = document.createElement('option');
              option.value = city.code;
              option.setAttribute('data-name', city.name);
              option.textContent = city.name;
              addCity.appendChild(option);
            });
            addCity.disabled = false;
          })
          .catch(error => {
            console.error('Error loading cities:', error);
            addCity.innerHTML = '<option value="">Error loading cities</option>';
          });
      } else {
        addCity.innerHTML = '<option value="">Select Province First</option>';
      }
    });
  }

  if (addCity) {
    addCity.addEventListener('change', function () {
      const cityCode = this.value;

      addBarangay.innerHTML = '<option value="">Loading...</option>';
      addBarangay.disabled = true;

      if (cityCode) {
        fetch('/api/psgc/cities/' + cityCode + '/barangays')
          .then(response => response.json())
          .then(json => {
            console.log('Barangays response:', json);
            const data = json.data || json || [];
            if (!Array.isArray(data)) {
              console.error('Barangays data is not an array:', data);
              addBarangay.innerHTML = '<option value="">Error: Invalid data format</option>';
              return;
            }
            addBarangay.innerHTML = '<option value="">Select Barangay</option>';
            data.forEach(function (barangay) {
              const option = document.createElement('option');
              option.value = barangay.code;
              option.setAttribute('data-name', barangay.name);
              option.textContent = barangay.name;
              addBarangay.appendChild(option);
            });
            addBarangay.disabled = false;
          })
          .catch(error => {
            console.error('Error loading barangays:', error);
            addBarangay.innerHTML = '<option value="">Error loading barangays</option>';
          });
      } else {
        addBarangay.innerHTML = '<option value="">Select City First</option>';
      }
    });
  }

  function setupFileUpload(uploadId, areaId, fileNameId, errorId) {
    const upload = document.getElementById(uploadId);
    const area = document.getElementById(areaId);
    const fileName = document.getElementById(fileNameId);

    if (upload && area && fileName) {
      area.addEventListener('click', () => upload.click());

      upload.addEventListener('change', function () {
        if (this.files && this.files[0]) {
          fileName.textContent = this.files[0].name;
          fileName.classList.remove('hidden');
          area.classList.add('border-orange-400', 'bg-orange-50');
          if (errorId) {
            clearAddFieldError(errorId);
          }
          if (area.classList.contains('edit-owner-dropzone')) {
            area.classList.remove('error');
            const staticError = area.parentElement ? area.parentElement.querySelector('.edit-owner-error') : null;
            if (staticError) {
              staticError.classList.add('hidden');
              staticError.textContent = '';
            }
            const dynamicError = area.parentElement ? area.parentElement.querySelector('.dynamic-edit-owner-error') : null;
            if (dynamicError) {
              dynamicError.remove();
            }
          }
        }
      });

      area.addEventListener('dragover', (e) => {
        e.preventDefault();
        area.classList.add('border-orange-400', 'bg-orange-50');
      });

      area.addEventListener('dragleave', () => {
        area.classList.remove('border-orange-400', 'bg-orange-50');
      });

      area.addEventListener('drop', (e) => {
        e.preventDefault();
        area.classList.remove('border-orange-400', 'bg-orange-50');
        if (e.dataTransfer.files && e.dataTransfer.files[0]) {
          upload.files = e.dataTransfer.files;
          fileName.textContent = e.dataTransfer.files[0].name;
          fileName.classList.remove('hidden');
          area.classList.add('border-orange-400', 'bg-orange-50');
          if (errorId) {
            clearAddFieldError(errorId);
          }
          if (area.classList.contains('edit-owner-dropzone')) {
            area.classList.remove('error');
            const staticError = area.parentElement ? area.parentElement.querySelector('.edit-owner-error') : null;
            if (staticError) {
              staticError.classList.add('hidden');
              staticError.textContent = '';
            }
            const dynamicError = area.parentElement ? area.parentElement.querySelector('.dynamic-edit-owner-error') : null;
            if (dynamicError) {
              dynamicError.remove();
            }
          }
        }
      });
    }
  }

  setupFileUpload('idFrontUpload', 'idFrontUploadArea', 'idFrontFileName', 'addIdFrontError');
  setupFileUpload('idBackUpload', 'idBackUploadArea', 'idBackFileName', 'addIdBackError');
  setupFileUpload('policeClearanceUpload', 'policeClearanceUploadArea', 'policeClearanceFileName', 'addPoliceClearanceError');

  const profileUpload = document.getElementById('profileUpload');
  const profilePreview = document.getElementById('profilePreview');
  const profileIcon = document.getElementById('profileIcon');

  if (profileUpload) {
    profileUpload.addEventListener('change', function (e) {
      const file = e.target.files[0];
      if (file) {
        const reader = new FileReader();
        reader.onload = function (event) {
          profilePreview.src = event.target.result;
          profilePreview.classList.remove('hidden');
          profileIcon.classList.add('hidden');
        };
        reader.readAsDataURL(file);
      }
    });
  }

  const idUploadArea = document.getElementById('idUploadArea');
  const idUpload = document.getElementById('idUpload');
  const idFileName = document.getElementById('idFileName');

  if (idUploadArea && idUpload) {
    idUploadArea.addEventListener('click', () => idUpload.click());

    idUpload.addEventListener('change', function (e) {
      const file = e.target.files[0];
      if (file) {
        idFileName.textContent = `Selected: ${file.name}`;
        idFileName.classList.remove('hidden');
        idUploadArea.classList.add('border-orange-400', 'bg-orange-50');
      }
    });

    idUploadArea.addEventListener('dragover', (e) => {
      e.preventDefault();
      idUploadArea.classList.add('border-orange-400', 'bg-orange-50');
    });

    idUploadArea.addEventListener('dragleave', () => {
      idUploadArea.classList.remove('border-orange-400', 'bg-orange-50');
    });

    idUploadArea.addEventListener('drop', (e) => {
      e.preventDefault();
      const file = e.dataTransfer.files[0];
      if (file && file.type.startsWith('image/')) {
        idUpload.files = e.dataTransfer.files;
        idFileName.textContent = `Selected: ${file.name}`;
        idFileName.classList.remove('hidden');
        idUploadArea.classList.add('border-orange-400', 'bg-orange-50');
      }
    });
  }

  function resetModalForm() {
    const inputs = modal.querySelectorAll('input, select');
    inputs.forEach(input => {
      if (input.type === 'file') {
        input.value = '';
      } else if (input.type === 'checkbox' || input.type === 'radio') {
        input.checked = false;
      } else {
        input.value = '';
      }

      input.classList.remove('border-red-500', 'error');
    });

    clearAllAddOwnerErrors();

    modal.querySelectorAll('.error-message').forEach(el => el.remove());

    modal.querySelectorAll('.border-red-500').forEach(el => el.classList.remove('border-red-500'));

    if (profilePreview && profileIcon) {
      profilePreview.classList.add('hidden');
      profileIcon.classList.remove('hidden');
    }

    if (idFileName) {
      idFileName.classList.add('hidden');
      idUploadArea.classList.remove('border-orange-400', 'bg-orange-50');
    }

    ['idFrontFileName', 'idBackFileName', 'policeClearanceFileName'].forEach(id => {
      const el = document.getElementById(id);
      if (el) el.classList.add('hidden');
    });

    ['idFrontUploadArea', 'idBackUploadArea', 'policeClearanceUploadArea'].forEach(id => {
      const el = document.getElementById(id);
      if (el) el.classList.remove('border-orange-400', 'bg-orange-50', 'error');
    });
  }

  const modalInputs = document.querySelectorAll('#addPropertyOwnerModal input, #addPropertyOwnerModal select');
  modalInputs.forEach(input => {
    input.addEventListener('focus', function () {
      this.parentElement.classList.add('transform', 'scale-[1.02]');
      this.style.transition = 'all 0.2s ease';
    });

    input.addEventListener('blur', function () {
      this.parentElement.classList.remove('transform', 'scale-[1.02]');
    });

    input.addEventListener('input', function () {
      const mappedErrorId = addFormErrorFieldMap[this.id];
      if (mappedErrorId) {
        clearAddFieldError(mappedErrorId);
      }

      if (this.classList.contains('border-red-500')) {
        this.classList.remove('border-red-500');
        const errorMsg = this.parentElement.querySelector('.error-message');
        if (errorMsg) {
          errorMsg.remove();
        }
      }
    });

    if (input.tagName === 'SELECT') {
      input.addEventListener('change', function () {
        const mappedErrorId = addFormErrorFieldMap[this.id];
        if (mappedErrorId) {
          clearAddFieldError(mappedErrorId);
        }

        if (this.classList.contains('border-red-500')) {
          this.classList.remove('border-red-500');
          const errorMsg = this.parentElement.querySelector('.error-message');
          if (errorMsg) {
            errorMsg.remove();
          }
        }
      });
    }
  });

  function addRipple(button, event) {
    const ripple = document.createElement('span');
    const rect = button.getBoundingClientRect();
    const size = Math.max(rect.width, rect.height);
    const x = event.clientX - rect.left - size / 2;
    const y = event.clientY - rect.top - size / 2;

    ripple.style.width = ripple.style.height = size + 'px';
    ripple.style.left = x + 'px';
    ripple.style.top = y + 'px';
    ripple.classList.add('ripple-effect');

    button.appendChild(ripple);

    setTimeout(() => {
      ripple.remove();
    }, 600);
  }

  animateOwnerTableRows();

  const editModal = document.getElementById('editPropertyOwnerModal');
  const editModalContent = editModal ? editModal.querySelector('.modal-content') : null;
  const closeEditModalBtn = document.getElementById('closeEditModalBtn');
  const cancelEditBtn = document.getElementById('cancelEditBtn');
  const saveEditBtn = document.getElementById('saveEditBtn');
  const editProfileUpload = document.getElementById('editProfileUpload');
  const editProfilePreview = document.getElementById('editProfilePreview');
  const editProfileIcon = document.getElementById('editProfileIcon');
  const defaultSaveEditBtnHtml = saveEditBtn ? saveEditBtn.innerHTML : '';
  const editPropertyOwnerErrorAlert = document.getElementById('editPropertyOwnerErrorAlert');
  const editPropertyOwnerErrorList = document.getElementById('editPropertyOwnerErrorList');
  const closeEditPropertyOwnerErrorAlert = document.getElementById('closeEditPropertyOwnerErrorAlert');
  let editPropertyOwnerFormInitialState = null;

  function captureEditPropertyOwnerFormState() {
    const form = document.getElementById('editPropertyOwnerForm');
    if (!form) {
      editPropertyOwnerFormInitialState = null;
      return;
    }

    editPropertyOwnerFormInitialState = {};
    form.querySelectorAll('input, select, textarea').forEach(input => {
      if (!input.name) {
        return;
      }

      if (input.type === 'file') {
        editPropertyOwnerFormInitialState[input.name] = input.files.length;
      } else if (input.type === 'checkbox' || input.type === 'radio') {
        editPropertyOwnerFormInitialState[input.name] = input.checked;
      } else {
        editPropertyOwnerFormInitialState[input.name] = input.value;
      }
    });
  }

  function hasEditPropertyOwnerFormChanged() {
    const form = document.getElementById('editPropertyOwnerForm');
    if (!form || !editPropertyOwnerFormInitialState) {
      return true;
    }

    const inputs = form.querySelectorAll('input, select, textarea');
    for (const input of inputs) {
      if (!input.name) {
        continue;
      }

      let currentValue;
      if (input.type === 'file') {
        currentValue = input.files.length;
      } else if (input.type === 'checkbox' || input.type === 'radio') {
        currentValue = input.checked;
      } else {
        currentValue = input.value;
      }

      if (currentValue !== editPropertyOwnerFormInitialState[input.name]) {
        return true;
      }
    }

    return false;
  }

  function showEditPropertyOwnerModalErrors(errors) {
    if (!editPropertyOwnerErrorAlert || !editPropertyOwnerErrorList) return;

    editPropertyOwnerErrorList.innerHTML = '';

    if (Array.isArray(errors)) {
      errors.forEach(error => {
        const li = document.createElement('li');
        li.textContent = error;
        editPropertyOwnerErrorList.appendChild(li);
      });
    } else if (typeof errors === 'object' && errors !== null) {
      Object.values(errors).forEach(messages => {
        if (Array.isArray(messages)) {
          messages.forEach(msg => {
            const li = document.createElement('li');
            li.textContent = msg;
            editPropertyOwnerErrorList.appendChild(li);
          });
        } else {
          const li = document.createElement('li');
          li.textContent = messages;
          editPropertyOwnerErrorList.appendChild(li);
        }
      });
    }

    editPropertyOwnerErrorAlert.classList.remove('hidden');

    const modalBody = document.querySelector('#editPropertyOwnerModal .edit-modal-scroll');
    if (modalBody) {
      modalBody.scrollTop = 0;
    }
  }

  function clearEditPropertyOwnerModalErrors() {
    if (editPropertyOwnerErrorAlert) {
      editPropertyOwnerErrorAlert.classList.add('hidden');
    }
    if (editPropertyOwnerErrorList) {
      editPropertyOwnerErrorList.innerHTML = '';
    }
  }

  function resolveEditErrorTarget(key) {
    const occupationSelect = document.getElementById('edit_occupationSelect');
    const occupationOtherInput = document.getElementById('edit_occupationOtherInput');
    const useOccupationOther = occupationSelect && occupationSelect.value === 'others' && occupationOtherInput && !occupationOtherInput.classList.contains('hidden');

    const map = {
      first_name: { targetId: 'edit_first_name', errorId: 'editFirstNameError' },
      last_name: { targetId: 'edit_last_name', errorId: 'editLastNameError' },
      date_of_birth: { targetId: 'edit_date_of_birth', errorId: 'editDateOfBirthError' },
      occupation_id: { targetId: 'edit_occupationSelect', errorId: 'editOccupationError' },
      occupation_other: { targetId: useOccupationOther ? 'edit_occupationOtherInput' : 'edit_occupationSelect', errorId: 'editOccupationError' },
      email: { targetId: 'edit_email', errorId: 'editEmailError' },
      username: { targetId: 'edit_username', errorId: 'editUsernameError' },
      password: { targetId: 'edit_password', errorId: 'editPasswordError' },
      province: { targetId: 'edit_owner_address_province', errorId: 'editProvinceError' },
      province_name: { targetId: 'edit_owner_address_province', errorId: 'editProvinceError' },
      city: { targetId: 'edit_owner_address_city', errorId: 'editCityError' },
      city_name: { targetId: 'edit_owner_address_city', errorId: 'editCityError' },
      barangay: { targetId: 'edit_owner_address_barangay', errorId: 'editBarangayError' },
      barangay_name: { targetId: 'edit_owner_address_barangay', errorId: 'editBarangayError' },
      street_address: { targetId: 'edit_street_address', errorId: 'editStreetAddressError' },
      zip_code: { targetId: 'edit_zip_code', errorId: 'editZipCodeError' },
      valid_id_id: { targetId: 'edit_valid_id_id', errorId: 'editValidIdTypeError' },
      valid_id_photo: { targetId: 'editIdFrontUploadArea', errorId: 'editValidIdFrontError' },
      valid_id_back_photo: { targetId: 'editIdBackUploadArea', errorId: 'editValidIdBackError' },
      police_clearance: { targetId: 'editPoliceClearanceUploadArea', errorId: 'editPoliceClearanceError' },
      profile_pic: { targetId: 'editProfileUpload', errorId: 'editProfilePicError' }
    };

    return map[key] || null;
  }

  function clearEditPropertyOwnerInlineErrors() {
    if (!editModal) return;

    editModal.querySelectorAll('.edit-owner-field.error').forEach(el => el.classList.remove('error'));
    editModal.querySelectorAll('.edit-owner-dropzone.error').forEach(el => el.classList.remove('error'));
    editModal.querySelectorAll('.edit-owner-error').forEach(el => {
      el.classList.add('hidden');
      el.textContent = '';
    });
    editModal.querySelectorAll('.dynamic-edit-owner-error').forEach(el => el.remove());

    editModal.querySelectorAll('.error-message').forEach(el => el.remove());
    editModal.querySelectorAll('.border-red-500').forEach(el => el.classList.remove('border-red-500'));
  }

  function showEditPropertyOwnerFieldError(key, message) {
    if (!editModal) return;

    const resolved = resolveEditErrorTarget(key);
    const target = resolved ? document.getElementById(resolved.targetId) : editModal.querySelector(`[name="${key}"]`);
    const mappedErrorElement = resolved && resolved.errorId ? document.getElementById(resolved.errorId) : null;

    if (!target && !mappedErrorElement) {
      return;
    }

    if (mappedErrorElement) {
      mappedErrorElement.textContent = message;
      mappedErrorElement.classList.remove('hidden');
    }

    if (target && (target.classList.contains('edit-owner-field') || target.classList.contains('edit-owner-dropzone'))) {
      target.classList.add('error');

      let errorElement = mappedErrorElement;
      if (!errorElement && target.parentElement) {
        errorElement = target.parentElement.querySelector('.edit-owner-error');
      }
      if (!errorElement && target.parentElement) {
        errorElement = document.createElement('p');
        errorElement.className = 'edit-owner-error dynamic-edit-owner-error text-red-500 text-[11px] mt-1';
        target.parentElement.appendChild(errorElement);
      }

      if (errorElement) {
        errorElement.textContent = message;
        errorElement.classList.remove('hidden');
      }
    } else if (target && !mappedErrorElement) {
      target.classList.add('border-red-500');
      const errorMsg = document.createElement('p');
      errorMsg.className = 'text-red-500 text-xs mt-1 error-message';
      errorMsg.textContent = message;
      target.parentElement.appendChild(errorMsg);
    }
  }

  if (closeEditPropertyOwnerErrorAlert) {
    closeEditPropertyOwnerErrorAlert.addEventListener('click', clearEditPropertyOwnerModalErrors);
  }

  async function openEditModal(userId) {
    if (!editModal || !editModalContent) return;

    clearEditPropertyOwnerModalErrors();
    clearEditPropertyOwnerInlineErrors();
    editPropertyOwnerFormInitialState = null;

    // Show modal instantly, then hydrate fields as data arrives.
    editModal.classList.remove('hidden');
    editModal.classList.add('flex');
    document.body.style.overflow = 'hidden';

    setTimeout(() => {
      editModalContent.classList.remove('scale-95', 'opacity-0');
      editModalContent.classList.add('scale-100', 'opacity-100');
    }, 10);

    const editFormEl = document.getElementById('editPropertyOwnerForm');
    const provinceSelect = document.getElementById('edit_owner_address_province');
    const citySelect = document.getElementById('edit_owner_address_city');
    const barangaySelect = document.getElementById('edit_owner_address_barangay');
    const currentIdFront = document.getElementById('currentIdFront');
    const currentIdBack = document.getElementById('currentIdBack');
    const currentPoliceClearance = document.getElementById('currentPoliceClearance');

    if (editFormEl) {
      editFormEl.reset();
      editFormEl.classList.add('opacity-60', 'pointer-events-none');
    }
    if (saveEditBtn) {
      saveEditBtn.disabled = true;
      saveEditBtn.innerHTML = '<i class="fi fi-rr-spinner animate-spin"></i> Loading...';
    }
    if (editProfilePreview && editProfileIcon) {
      editProfilePreview.classList.add('hidden');
      editProfileIcon.classList.remove('hidden');
      editProfilePreview.src = '';
    }
    if (provinceSelect) {
      provinceSelect.value = '';
    }
    if (citySelect) {
      citySelect.innerHTML = '<option value="">Select City/Municipality</option>';
      citySelect.disabled = true;
    }
    if (barangaySelect) {
      barangaySelect.innerHTML = '<option value="">Select Barangay</option>';
      barangaySelect.disabled = true;
    }
    if (currentIdFront) currentIdFront.innerHTML = '';
    if (currentIdBack) currentIdBack.innerHTML = '';
    if (currentPoliceClearance) currentPoliceClearance.innerHTML = '';

    try {
      const response = await fetch(`/admin/user-management/property-owners/${userId}/edit`, {
        headers: {
          'X-Requested-With': 'XMLHttpRequest'
        }
      });

      if (!response.ok) throw new Error('Failed to fetch user data');

      const data = await response.json();
      const user = data.user;
      const owner = data.owner;

      document.getElementById('edit_user_id').value = user.id;
      document.getElementById('edit_first_name').value = owner.first_name;
      document.getElementById('edit_middle_name').value = owner.middle_name || '';
      document.getElementById('edit_last_name').value = owner.last_name;
      document.getElementById('edit_date_of_birth').value = owner.date_of_birth;
      document.getElementById('edit_email').value = user.email;
      document.getElementById('edit_username').value = user.username;
      document.getElementById('edit_street_address').value = owner.street_address;
      document.getElementById('edit_zip_code').value = owner.zip_code;

      const occupationSelect = document.getElementById('edit_occupationSelect');
      const occupationOtherInput = document.getElementById('edit_occupationOtherInput');
      
      // Check if occupation_id exists and if occupation_other has a value
      if (owner.occupation_id) {
        occupationSelect.value = owner.occupation_id;
        
        // If occupation_other has a value, it means the occupation is "Others"
        if (owner.occupation_other && owner.occupation_other.trim() !== '') {
          occupationSelect.value = 'others';
          occupationOtherInput.value = owner.occupation_other;
          occupationOtherInput.classList.remove('hidden');
        } else {
          occupationOtherInput.classList.add('hidden');
          occupationOtherInput.value = '';
        }
      } else {
        // No occupation_id means it's a custom occupation
        occupationSelect.value = 'others';
        occupationOtherInput.value = owner.occupation || '';
        occupationOtherInput.classList.remove('hidden');
      }

      if (owner.profile_pic) {
        editProfilePreview.src = `/storage/${owner.profile_pic}`;
        editProfilePreview.classList.remove('hidden');
        editProfileIcon.classList.add('hidden');
      } else {
        editProfilePreview.classList.add('hidden');
        editProfileIcon.classList.remove('hidden');
      }

      let provinceCode = '';
      if (owner.province && provinceSelect) {
        const ownerProvStr = String(owner.province).trim();
        for (let i = 0; i < provinceSelect.options.length; i++) {
          const optionName = provinceSelect.options[i].getAttribute('data-name');
          const optionValue = provinceSelect.options[i].value;

          if ((optionName && String(optionName).trim() === ownerProvStr) ||
            (optionValue && String(optionValue).trim() === ownerProvStr)) {
            provinceSelect.selectedIndex = i;
            provinceCode = provinceSelect.options[i].value;
            break;
          }
        }
      }

      if (provinceCode) {
        const citiesResponse = await fetch(`/api/psgc/provinces/${provinceCode}/cities`);
        const citiesJson = await citiesResponse.json();
        const cities = Array.isArray(citiesJson) ? citiesJson : (citiesJson.data || []);

        citySelect.innerHTML = '<option value="">Select City/Municipality</option>';
        let cityCode = '';
        if (owner.city) {
          const ownerCityStr = String(owner.city).trim();
          cities.forEach(city => {
            const option = document.createElement('option');
            option.value = city.code;
            option.setAttribute('data-name', city.name);
            option.textContent = city.name;
            if ((city.name && String(city.name).trim() === ownerCityStr) ||
              String(city.code).trim() === ownerCityStr) {
              option.selected = true;
              cityCode = city.code;
            }
            citySelect.appendChild(option);
          });
        } else {
          cities.forEach(city => {
            const option = document.createElement('option');
            option.value = city.code;
            option.setAttribute('data-name', city.name);
            option.textContent = city.name;
            citySelect.appendChild(option);
          });
        }
        citySelect.disabled = false;

        if (cityCode) {
          const barangaysResponse = await fetch(`/api/psgc/cities/${cityCode}/barangays`);
          const barangaysJson = await barangaysResponse.json();
          const barangays = Array.isArray(barangaysJson) ? barangaysJson : (barangaysJson.data || []);

          barangaySelect.innerHTML = '<option value="">Select Barangay</option>';
          if (owner.barangay) {
            const ownerBrgyStr = String(owner.barangay).trim();
            barangays.forEach(barangay => {
              const option = document.createElement('option');
              option.value = barangay.code;
              option.setAttribute('data-name', barangay.name);
              option.textContent = barangay.name;
              if ((barangay.name && String(barangay.name).trim() === ownerBrgyStr) ||
                String(barangay.code).trim() === ownerBrgyStr) {
                option.selected = true;
              }
              barangaySelect.appendChild(option);
            });
          } else {
            barangays.forEach(barangay => {
              const option = document.createElement('option');
              option.value = barangay.code;
              option.setAttribute('data-name', barangay.name);
              option.textContent = barangay.name;
              barangaySelect.appendChild(option);
            });
          }
          barangaySelect.disabled = false;
        }
      }

      document.getElementById('edit_valid_id_id').value = owner.valid_id_id;

      if (owner.valid_id_photo) {
        currentIdFront.innerHTML = `Current: <a href="#" class="text-orange-500 hover:underline open-doc-btn" data-doc-src="/storage/${owner.valid_id_photo}" data-doc-title="Valid ID (Front)">View File</a>`;
      } else {
        currentIdFront.innerHTML = '';
      }

      if (owner.valid_id_back_photo) {
        currentIdBack.innerHTML = `Current: <a href="#" class="text-orange-500 hover:underline open-doc-btn" data-doc-src="/storage/${owner.valid_id_back_photo}" data-doc-title="Valid ID (Back)">View File</a>`;
      } else {
        currentIdBack.innerHTML = '';
      }

      if (owner.police_clearance) {
        currentPoliceClearance.innerHTML = `Current: <a href="#" class="text-orange-500 hover:underline open-doc-btn" data-doc-src="/storage/${owner.police_clearance}" data-doc-title="Police Clearance">View File</a>`;
      } else {
        currentPoliceClearance.innerHTML = '';
      }

      captureEditPropertyOwnerFormState();

    } catch (error) {
      console.error('Error fetching user data:', error);
      alert('Failed to load user data.');
    } finally {
      if (editFormEl) {
        editFormEl.classList.remove('opacity-60', 'pointer-events-none');
      }
      if (saveEditBtn) {
        saveEditBtn.disabled = false;
        saveEditBtn.innerHTML = defaultSaveEditBtnHtml;
      }
    }
  }

  function closeEditModal() {
    if (!editModalContent) return;

    editModalContent.classList.remove('scale-100', 'opacity-100');
    editModalContent.classList.add('scale-95', 'opacity-0');

    setTimeout(() => {
      editModal.classList.add('hidden');
      editModal.classList.remove('flex');
      document.body.style.overflow = 'auto';

      clearEditPropertyOwnerModalErrors();
      clearEditPropertyOwnerInlineErrors();
      editPropertyOwnerFormInitialState = null;

      document.getElementById('editPropertyOwnerForm').reset();

      editProfilePreview.classList.add('hidden');
      editProfileIcon.classList.remove('hidden');
      document.getElementById('editIdFrontFileName').classList.add('hidden');
      document.getElementById('editIdBackFileName').classList.add('hidden');
      document.getElementById('editPoliceClearanceFileName').classList.add('hidden');
      document.getElementById('editIdFrontUploadArea').classList.remove('border-orange-400', 'bg-orange-50');
      document.getElementById('editIdBackUploadArea').classList.remove('border-orange-400', 'bg-orange-50');
      document.getElementById('editPoliceClearanceUploadArea').classList.remove('border-orange-400', 'bg-orange-50');
    }, 300);
  }

  if (closeEditModalBtn) {
    closeEditModalBtn.addEventListener('click', closeEditModal);
  }

  if (cancelEditBtn) {
    cancelEditBtn.addEventListener('click', closeEditModal);
  }

  if (editModal) {
    editModal.addEventListener('click', function (e) {
      if (e.target === editModal) {
        closeEditModal();
      }
    });
  }

  if (editModalContent) {
    editModalContent.addEventListener('click', function (e) {
      // Allow clicks on document viewer buttons to bubble up
      if (e.target.closest('.open-doc-btn')) {
        return;
      }
      e.stopPropagation();
    });
  }

  if (editProfileUpload && editProfilePreview && editProfileIcon) {
    editProfileUpload.addEventListener('change', function (e) {
      const file = e.target.files[0];
      if (file) {
        const reader = new FileReader();
        reader.onload = function (event) {
          editProfilePreview.src = event.target.result;
          editProfilePreview.classList.remove('hidden');
          editProfileIcon.classList.add('hidden');
        };
        reader.readAsDataURL(file);
      }
    });
  }

  setupFileUpload('editIdFrontUpload', 'editIdFrontUploadArea', 'editIdFrontFileName');
  setupFileUpload('editIdBackUpload', 'editIdBackUploadArea', 'editIdBackFileName');
  setupFileUpload('editPoliceClearanceUpload', 'editPoliceClearanceUploadArea', 'editPoliceClearanceFileName');

  const editProvince = document.getElementById('edit_owner_address_province');
  const editCity = document.getElementById('edit_owner_address_city');
  const editBarangay = document.getElementById('edit_owner_address_barangay');

  if (editProvince) {
    editProvince.addEventListener('change', function () {
      const provinceCode = this.value;

      editCity.innerHTML = '<option value="">Loading...</option>';
      editCity.disabled = true;
      editBarangay.innerHTML = '<option value="">Select City First</option>';
      editBarangay.disabled = true;

      if (provinceCode) {
        fetch('/api/psgc/provinces/' + provinceCode + '/cities')
          .then(response => response.json())
          .then(json => {
            const data = Array.isArray(json) ? json : (json.data || []);
            editCity.innerHTML = '<option value="">Select City/Municipality</option>';
            data.forEach(function (city) {
              const option = document.createElement('option');
              option.value = city.code;
              option.setAttribute('data-name', city.name);
              option.textContent = city.name;
              editCity.appendChild(option);
            });
            editCity.disabled = false;
          })
          .catch(error => {
            editCity.innerHTML = '<option value="">Error loading cities</option>';
          });
      } else {
        editCity.innerHTML = '<option value="">Select Province First</option>';
      }
    });
  }

  if (editCity) {
    editCity.addEventListener('change', function () {
      const cityCode = this.value;

      editBarangay.innerHTML = '<option value="">Loading...</option>';
      editBarangay.disabled = true;

      if (cityCode) {
        fetch('/api/psgc/cities/' + cityCode + '/barangays')
          .then(response => response.json())
          .then(json => {
            const data = Array.isArray(json) ? json : (json.data || []);
            editBarangay.innerHTML = '<option value="">Select Barangay</option>';
            data.forEach(function (barangay) {
              const option = document.createElement('option');
              option.value = barangay.code;
              option.setAttribute('data-name', barangay.name);
              option.textContent = barangay.name;
              editBarangay.appendChild(option);
            });
            editBarangay.disabled = false;
          })
          .catch(error => {
            editBarangay.innerHTML = '<option value="">Error loading barangays</option>';
          });
      } else {
        editBarangay.innerHTML = '<option value="">Select City First</option>';
      }
    });
  }

  const editOccupationSelect = document.getElementById('edit_occupationSelect');
  const editOccupationOtherInput = document.getElementById('edit_occupationOtherInput');

  if (editOccupationSelect && editOccupationOtherInput) {
    editOccupationSelect.addEventListener('change', function () {
      if (this.value === 'others') {
        editOccupationOtherInput.classList.remove('hidden');
      } else {
        editOccupationOtherInput.classList.add('hidden');
        editOccupationOtherInput.value = '';
      }
    });
  }

  const editForm = document.getElementById('editPropertyOwnerForm');
  if (editForm) {
    editForm.addEventListener('submit', async function (e) {
      e.preventDefault();

      clearEditPropertyOwnerModalErrors();
      clearEditPropertyOwnerInlineErrors();

      if (!hasEditPropertyOwnerFormChanged()) {
        showEditPropertyOwnerModalErrors(['No changes detected. Please modify at least one field before saving.']);
        return;
      }

      const userId = document.getElementById('edit_user_id').value;
      const formData = new FormData(this);

      if (editProvince.selectedIndex > 0) {
        formData.append('province_name', editProvince.options[editProvince.selectedIndex].getAttribute('data-name'));
      }
      if (editCity.selectedIndex > 0) {
        formData.append('city_name', editCity.options[editCity.selectedIndex].getAttribute('data-name'));
      }
      if (editBarangay.selectedIndex > 0) {
        formData.append('barangay_name', editBarangay.options[editBarangay.selectedIndex].getAttribute('data-name'));
      }

      const csrfToken = document.querySelector('meta[name="csrf-token"]');
      if (csrfToken) {
        formData.append('_token', csrfToken.content);
      }

      formData.append('_method', 'PUT');

      const originalContent = saveEditBtn.innerHTML;
      saveEditBtn.innerHTML = '<i class="fi fi-rr-spinner animate-spin"></i> Saving...';
      saveEditBtn.disabled = true;

      try {
        const response = await fetch(`/admin/user-management/property-owners/${userId}`, {
          method: 'POST', // Use POST with _method=PUT for file uploads
          headers: {
            'X-Requested-With': 'XMLHttpRequest'
          },
          body: formData
        });

        const result = await response.json();

        if (response.ok) {
          showNotification('Property owner updated successfully!', 'success');
          closeEditModal();

          if (document.getElementById('ownersTableWrap')) {
            handleFilterChange(); // Refresh table
          } else {

            setTimeout(() => {
              window.location.reload();
            }, 500);
          }
        } else {
          if (result.errors) {
            const errorMessages = [];

            for (const [key, messages] of Object.entries(result.errors)) {
              const errorMsg = Array.isArray(messages) ? messages[0] : messages;
              errorMessages.push(errorMsg);
              showEditPropertyOwnerFieldError(key, errorMsg);
            }

            showEditPropertyOwnerModalErrors(errorMessages);
          } else {
            showEditPropertyOwnerModalErrors([result.message || 'An error occurred']);
          }
        }
      } catch (error) {
        console.error('Error updating property owner:', error);
        showEditPropertyOwnerModalErrors(['An error occurred while updating.']);
      } finally {
        saveEditBtn.innerHTML = originalContent;
        saveEditBtn.disabled = false;
      }
    });
  }

  const editInputs = editModal ? editModal.querySelectorAll('input, select, textarea') : [];
  editInputs.forEach(input => {
    input.addEventListener('focus', function () {
      this.parentElement.classList.add('ring-2', 'ring-orange-200');
    });

    input.addEventListener('blur', function () {
      this.parentElement.classList.remove('ring-2', 'ring-orange-200');
    });

    input.addEventListener('input', function () {
      if (this.classList.contains('border-red-500')) {
        this.classList.remove('border-red-500');
        const errorMsg = this.parentElement.querySelector('.error-message');
        if (errorMsg) {
          errorMsg.remove();
        }
      }

      if (this.classList.contains('edit-owner-field') && this.classList.contains('error')) {
        this.classList.remove('error');
      }

      const inlineError = this.parentElement.querySelector('.edit-owner-error');
      if (inlineError) {
        inlineError.classList.add('hidden');
        inlineError.textContent = '';
      }

      const dynamicInlineError = this.parentElement.querySelector('.dynamic-edit-owner-error');
      if (dynamicInlineError) {
        dynamicInlineError.remove();
      }
    });

    if (input.tagName === 'SELECT') {
      input.addEventListener('change', function () {
        if (this.classList.contains('border-red-500')) {
          this.classList.remove('border-red-500');
          const errorMsg = this.parentElement.querySelector('.error-message');
          if (errorMsg) {
            errorMsg.remove();
          }
        }

        if (this.classList.contains('edit-owner-field') && this.classList.contains('error')) {
          this.classList.remove('error');
        }

        const inlineError = this.parentElement.querySelector('.edit-owner-error');
        if (inlineError) {
          inlineError.classList.add('hidden');
          inlineError.textContent = '';
        }

        const dynamicInlineError = this.parentElement.querySelector('.dynamic-edit-owner-error');
        if (dynamicInlineError) {
          dynamicInlineError.remove();
        }
      });
    }
  });

  document.addEventListener('keydown', function (e) {
    if (e.key === 'Escape' && editModal && !editModal.classList.contains('hidden')) {
      closeEditModal();
    }
  });

  function showNotification(message, type = 'success') {
    const notification = document.createElement('div');
    notification.className = `fixed top-20 right-4 z-[60] max-w-[280px] px-3 py-2 rounded-md shadow-lg transform transition-all duration-500 translate-x-full ${type === 'success' ? 'bg-green-500' : 'bg-red-500'
      } text-white text-xs font-semibold leading-tight flex items-center gap-1.5`;
    notification.innerHTML = `
      <i class="fi fi-rr-${type === 'success' ? 'check-circle' : 'cross-circle'} text-base"></i>
      <span>${message}</span>
    `;
    document.body.appendChild(notification);

    setTimeout(() => {
      notification.style.transform = 'translateX(0)';
    }, 10);

    setTimeout(() => {
      notification.style.transform = 'translateX(150%)';
      setTimeout(() => notification.remove(), 500);
    }, 3000);
  }

  const deleteModal = document.getElementById('deleteUserModal');
  const deleteModalContent = deleteModal ? deleteModal.querySelector('.modal-content') : null;
  const closeDeleteModalBtn = document.getElementById('closeDeleteModalBtn');
  const confirmDeleteBtn = document.getElementById('confirmDeleteBtn');
  const cancelDeleteBtn = document.getElementById('cancelDeleteBtn');
  const deleteUserNameSpan = document.getElementById('deleteUserName');
  const deletionReasonInput = document.getElementById('deletionReason');
  const deletionReasonError = document.getElementById('deletionReasonError');
  let rowToDelete = null;
  let idToDelete = null;

  function openDeleteModal(userName, row, id) {
    if (!deleteModal || !deleteModalContent) return;

    rowToDelete = row;
    idToDelete = id;

    if (deleteUserNameSpan) {
      deleteUserNameSpan.textContent = userName;
    }

    if (deletionReasonInput) {
      deletionReasonInput.value = '';
      deletionReasonInput.classList.remove('border-red-500');
    }
    if (deletionReasonError) {
      deletionReasonError.classList.add('hidden');
    }

    deleteModal.classList.remove('hidden');
    deleteModal.classList.add('flex');
    document.body.style.overflow = 'hidden';

    setTimeout(() => {
      deleteModalContent.classList.remove('scale-95', 'opacity-0');
      deleteModalContent.classList.add('scale-100', 'opacity-100');
    }, 10);
  }

  function closeDeleteModal() {
    if (!deleteModalContent) return;

    deleteModalContent.classList.remove('scale-100', 'opacity-100');
    deleteModalContent.classList.add('scale-95', 'opacity-0');

    setTimeout(() => {
      deleteModal.classList.add('hidden');
      deleteModal.classList.remove('flex');
      document.body.style.overflow = 'auto';
      rowToDelete = null;
      idToDelete = null;
    }, 300);
  }

  if (cancelDeleteBtn) {
    cancelDeleteBtn.addEventListener('click', closeDeleteModal);
  }

  if (closeDeleteModalBtn) {
    closeDeleteModalBtn.addEventListener('click', closeDeleteModal);
  }

  if (confirmDeleteBtn) {
    confirmDeleteBtn.addEventListener('click', async function () {
      if (!rowToDelete || !idToDelete) return;

      const reason = deletionReasonInput.value.trim();
      if (!reason) {
        deletionReasonInput.classList.add('border-red-500');
        deletionReasonError.classList.remove('hidden');
        return;
      } else {
        deletionReasonInput.classList.remove('border-red-500');
        deletionReasonError.classList.add('hidden');
      }

      const originalContent = confirmDeleteBtn.innerHTML;
      confirmDeleteBtn.innerHTML = '<i class="fi fi-rr-spinner animate-spin"></i> Deleting...';
      confirmDeleteBtn.disabled = true;

      try {
        const csrfToken = document.querySelector('meta[name="csrf-token"]').content;
        const response = await fetch(`/admin/user-management/property-owners/${idToDelete}`, {
          method: 'POST', // Using POST with _method: DELETE
          headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': csrfToken,
            'X-Requested-With': 'XMLHttpRequest'
          },
          body: JSON.stringify({
            _method: 'DELETE',
            deletion_reason: reason
          })
        });

        const result = await response.json();

        if (response.ok) {
          showNotification('User deleted successfully!', 'success');
          closeDeleteModal();
          handleFilterChange(); // Refresh table data
        } else {
          alert(result.message || 'Failed to delete user.');
        }
      } catch (error) {
        console.error('Error deleting user:', error);
        alert('An error occurred while deleting.');
      } finally {

        confirmDeleteBtn.innerHTML = originalContent;
        confirmDeleteBtn.disabled = false;
      }
    });
  }

  if (deleteModal) {
    deleteModal.addEventListener('click', function (e) {
      if (e.target === deleteModal) {
        closeDeleteModal();
      }
    });
  }

  if (deleteModalContent) {
    deleteModalContent.addEventListener('click', function (e) {
      e.stopPropagation();
    });
  }

  document.addEventListener('keydown', function (e) {
    if (e.key === 'Escape' && deleteModal && !deleteModal.classList.contains('hidden')) {
      closeDeleteModal();
    }
  });

  window.openDeleteModal = openDeleteModal;
  window.openEditModal = openEditModal;
  window.closeEditModal = closeEditModal;
});

const style = document.createElement('style');
style.textContent = `
  .action-btn {
    position: relative;
    overflow: hidden;
  }

  .ripple-effect {
    position: absolute;
    border-radius: 50%;
    background: rgba(255, 255, 255, 0.6);
    transform: scale(0);
    animation: ripple-animation 0.6s ease-out;
    pointer-events: none;
  }

  @keyframes ripple-animation {
    to {
      transform: scale(4);
      opacity: 0;
    }
  }

  .action-btn:active {
    transform: scale(0.95);
  }

  .scale-95 {
    transform: scale(0.95);
    transition: transform 0.1s ease;
  }
`;
document.head.appendChild(style);


// ============================================
// Universal File Viewer (UFV) for Property Owner Page
// ============================================
(function() {
    console.log('UFV: Initializing...');
    const modal = document.getElementById('documentViewerModal');
    const iframe = document.getElementById('documentViewerFrame');
    const img = document.getElementById('documentViewerImg');
    const closeBtn = document.getElementById('closeDocumentViewerBtn');

    console.log('UFV: Modal found?', !!modal);
    console.log('UFV: Iframe found?', !!iframe);
    console.log('UFV: Img found?', !!img);

    if (!modal) {
        console.error('UFV: documentViewerModal not found!');
        return;
    }

    function openDocumentViewer(src, title) {
        console.log('UFV: Opening viewer with src:', src, 'title:', title);
        if (!modal) return;
        const isPdf = /\.pdf(\?|$)/i.test(src);
        const titleEl = document.getElementById('documentViewerTitle');
        const downloadLink = document.getElementById('documentViewerDownload');

        if (titleEl) titleEl.textContent = title || 'Document Viewer';
        if (downloadLink) downloadLink.href = src;

        if (isPdf) {
            if (iframe) {
                iframe.src = src;
                iframe.classList.remove('hidden');
            }
            if (img) img.classList.add('hidden');
        } else {
            if (img) {
                img.src = src;
                img.classList.remove('hidden');
            }
            if (iframe) iframe.classList.add('hidden');
        }

        modal.classList.remove('hidden');
        modal.classList.add('flex');
        document.body.style.overflow = 'hidden';

        const modalShell = modal.querySelector('.modal-shell');
        if (modalShell) {
            setTimeout(function() {
                modalShell.classList.remove('scale-95', 'opacity-0');
                modalShell.classList.add('scale-100', 'opacity-100');
            }, 10);
        }
    }

    function closeDocumentViewer() {
        console.log('UFV: Closing viewer');
        if (!modal) return;
        const modalShell = modal.querySelector('.modal-shell');
        if (modalShell) {
            modalShell.classList.remove('scale-100', 'opacity-100');
            modalShell.classList.add('scale-95', 'opacity-0');
        }
        setTimeout(function() {
            modal.classList.add('hidden');
            modal.classList.remove('flex');
            document.body.style.overflow = 'auto';
            if (iframe) iframe.src = '';
            if (img) img.src = '';
        }, 200);
    }

    // Delegated click handler for open buttons
    document.addEventListener('click', function(e) {
        console.log('UFV: Click detected on:', e.target);
        const btn = e.target.closest && e.target.closest('.open-doc-btn');
        console.log('UFV: Closest .open-doc-btn:', btn);
        if (btn) {
            console.log('UFV: Button clicked!');
            e.preventDefault();
            e.stopPropagation();
            const src = btn.getAttribute('data-doc-src');
            const title = btn.getAttribute('data-doc-title') || 'Document';
            console.log('UFV: src:', src, 'title:', title);
            if (src) {
                openDocumentViewer(src, title);
            }
        }
    }, true); // Use capture phase

    // Close button
    if (closeBtn) {
        closeBtn.addEventListener('click', closeDocumentViewer);
    }

    // Close on backdrop click
    if (modal) {
        modal.addEventListener('click', function(e) {
            if (e.target === modal) {
                closeDocumentViewer();
            }
        });
    }

    // Close on ESC key
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape' && modal && !modal.classList.contains('hidden')) {
            closeDocumentViewer();
        }
    });

    console.log('UFV: Initialization complete');
})();
