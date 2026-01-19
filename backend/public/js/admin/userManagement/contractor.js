document.addEventListener('DOMContentLoaded', function() {
  // Initialize Filters
  const dateFromInput = document.getElementById('dateFrom');
  const dateToInput = document.getElementById('dateTo');
  const searchInput = document.getElementById('searchInput');
  const resetBtn = document.getElementById('resetFilterBtn');
  const contractorsWrap = document.getElementById('contractorsTableWrap');

  let debounceTimer;

  // Function to fetch and update data
  async function fetchAndUpdate(url) {
      try {
          const response = await fetch(url, {
              headers: {
                  'X-Requested-With': 'XMLHttpRequest'
              }
          });

          if (!response.ok) throw new Error('Network response was not ok');

          const data = await response.json();

          if (contractorsWrap && data.html) {
              contractorsWrap.innerHTML = data.html;
          }

          // Update URL without reload
          window.history.pushState({}, '', url);

          // Re-attach pagination listeners
          attachPaginationListeners();

          // Re-attach action button listeners
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

      // Reset pagination when filtering
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
      const paginationLinks = document.querySelectorAll('#contractorsTableWrap .pagination a');
      paginationLinks.forEach(link => {
          link.addEventListener('click', function(e) {
              e.preventDefault();
              const url = this.href;
              fetchAndUpdate(url);
          });
      });
  }

  // Attach listeners
  if (dateFromInput) dateFromInput.addEventListener('change', handleFilterChange);
  if (dateToInput) dateToInput.addEventListener('change', handleFilterChange);
  if (searchInput) searchInput.addEventListener('input', handleSearchInput);

  if (resetBtn) {
      resetBtn.addEventListener('click', function() {
          if (dateFromInput) dateFromInput.value = '';
          if (dateToInput) dateToInput.value = '';
          if (searchInput) searchInput.value = '';
          handleFilterChange();
      });
  }

  // Initial pagination listeners
  attachPaginationListeners();

  // Populate inputs from URL on load
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

  // Action Buttons Interactivity (Wrapped in function for re-attachment)
  function attachActionListeners() {
    const viewButtons = document.querySelectorAll('.view-btn');
    const editButtons = document.querySelectorAll('.edit-btn');
    const deleteButtons = document.querySelectorAll('.delete-btn');

    viewButtons.forEach(btn => {
      btn.addEventListener('click', function(e) {
        e.stopPropagation();
        addRipple(this, e);
        const id = this.getAttribute('data-id');
        if (id) {
          setTimeout(() => {
            window.location.href = `/admin/user-management/contractor/view?id=${id}`;
          }, 200);
        }
      });
    });

    editButtons.forEach(btn => {
      btn.addEventListener('click', function(e) {
        e.stopPropagation();
        addRipple(this, e);
        const id = this.getAttribute('data-id');
        if (id) {
            setTimeout(() => {
                openEditModal(id);
            }, 200);
        }
      });
    });

    deleteButtons.forEach(btn => {
      btn.addEventListener('click', function(e) {
        e.stopPropagation();
        const row = this.closest('tr');
        const name = row.querySelector('.font-medium').textContent;
        const id = this.getAttribute('data-id');

        addRipple(this, e);

        setTimeout(() => {
          openDeleteModal(name, row, id);
        }, 200);
      });
    });

    // Table row click highlight
    const tableRows = document.querySelectorAll('#contractorsTable tr');
    tableRows.forEach(row => {
      row.addEventListener('click', function() {
        tableRows.forEach(r => r.classList.remove('bg-indigo-50'));
        this.classList.add('bg-indigo-50');
      });
    });
  }

  // Initial attachment
  attachActionListeners();

  // Add Contractor Button
  const addBtn = document.querySelector('#addContractorBtn');
  const modal = document.getElementById('addContractorModal');
  const closeModalBtn = document.getElementById('closeModalBtn');
  const cancelBtn = document.getElementById('cancelBtn');
  const saveBtn = document.getElementById('saveBtn');

  if (addBtn && modal) {
    // Open modal
    addBtn.addEventListener('click', function() {
      modal.classList.remove('hidden');
      document.body.style.overflow = 'hidden';

      // Animate modal content
      const modalContent = modal.querySelector('.modal-content');
      modalContent.style.transform = 'scale(0.9)';
      modalContent.style.opacity = '0';

      setTimeout(() => {
        modalContent.style.transition = 'all 0.3s ease';
        modalContent.style.transform = 'scale(1)';
        modalContent.style.opacity = '1';
      }, 10);
    });

    // Close modal functions
    const closeModal = () => {
      const modalContent = modal.querySelector('.modal-content');
      modalContent.style.transform = 'scale(0.9)';
      modalContent.style.opacity = '0';

      setTimeout(() => {
        modal.classList.add('hidden');
        document.body.style.overflow = 'auto';
        // Reset form (optional)
        resetModalForm();
      }, 300);
    };

    closeModalBtn.addEventListener('click', closeModal);
    cancelBtn.addEventListener('click', closeModal);

    // Close on outside click
    modal.addEventListener('click', function(e) {
      if (e.target === modal) {
        closeModal();
      }
    });

    // Close on Escape key
    document.addEventListener('keydown', function(e) {
      if (e.key === 'Escape' && !modal.classList.contains('hidden')) {
        closeModal();
      }
    });

    // Save button
    saveBtn.addEventListener('click', async function() {
        const formData = new FormData();

        // Collect inputs
        const inputs = modal.querySelectorAll('input, select, textarea');
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
                // Handle PSGC selects to send names instead of codes if needed
                if (input.id === 'contractor_address_province' || input.id === 'contractor_address_city' || input.id === 'contractor_address_barangay') {
                    if (input.selectedIndex > 0) {
                        const name = input.options[input.selectedIndex].getAttribute('data-name');
                        const fieldName = input.id === 'contractor_address_province' ? 'business_address_province' :
                                          (input.id === 'contractor_address_city' ? 'business_address_city' : 'business_address_barangay');
                        formData.append(fieldName, name);
                    }
                } else {
                    formData.append(input.name, input.value);
                }
            } else {
                formData.append(input.name, input.value);
            }
        });

        // Add CSRF token
        const csrfToken = document.querySelector('meta[name="csrf-token"]');
        if (csrfToken) {
            formData.append('_token', csrfToken.content);
        }

        // Add loading state
        const originalText = this.innerHTML;
        this.innerHTML = '<i class="fi fi-rr-spinner animate-spin"></i> Saving...';
        this.disabled = true;

        // Clear previous errors
        modal.querySelectorAll('.error-message').forEach(el => el.remove());
        modal.querySelectorAll('.border-red-500').forEach(el => el.classList.remove('border-red-500'));

        try {
            const response = await fetch('/admin/user-management/contractors/store', {
                method: 'POST',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: formData
            });

            const result = await response.json();

            if (response.ok) {
                showNotification('Contractor added successfully!', 'success');
                closeModal();
                // Refresh table
                handleFilterChange();
            } else {
                if (result.errors) {
                    for (const [key, messages] of Object.entries(result.errors)) {
                        const input = modal.querySelector(`[name="${key}"]`);
                        if (input) {
                            if (input.type === 'file' && input.id === 'dtiUpload') {
                                const dropzone = document.getElementById('dtiDropzone');
                                if (dropzone) {
                                    dropzone.classList.add('border-red-500');
                                    const errorDiv = document.createElement('div');
                                    errorDiv.className = 'text-red-500 text-xs mt-1 error-message';
                                    errorDiv.textContent = messages[0];
                                    dropzone.parentElement.appendChild(errorDiv);
                                }
                            } else {
                                input.classList.add('border-red-500');
                                const errorDiv = document.createElement('div');
                                errorDiv.className = 'text-red-500 text-xs mt-1 error-message';
                                errorDiv.textContent = messages[0];
                                input.parentElement.appendChild(errorDiv);
                            }
                        } else {
                            showNotification(messages[0], 'error');
                        }
                    }
                    // Scroll to first error
                    const firstError = modal.querySelector('.error-message');
                    if (firstError) {
                        firstError.scrollIntoView({ behavior: 'smooth', block: 'center' });
                    }
                } else {
                    showNotification(result.message || 'An error occurred', 'error');
                }
            }
        } catch (error) {
            console.error('Error:', error);
            showNotification('An unexpected error occurred', 'error');
        } finally {
            this.innerHTML = originalText;
            this.disabled = false;
        }
    });
  }

  // Contractor Type "Others" Toggle
  const contractorTypeSelect = document.getElementById('contractorTypeSelect');
  const contractorTypeOtherInput = document.getElementById('contractorTypeOtherInput');
  if (contractorTypeSelect && contractorTypeOtherInput) {
      contractorTypeSelect.addEventListener('change', function() {
          const selectedText = this.options[this.selectedIndex].text;
          if (selectedText === 'Others' || this.value == 9) {
              contractorTypeOtherInput.classList.remove('hidden');
              contractorTypeOtherInput.required = true;
          } else {
              contractorTypeOtherInput.classList.add('hidden');
              contractorTypeOtherInput.required = false;
              contractorTypeOtherInput.value = '';
          }
      });
  }

  // Address Handling (PSGC)
  const provinceSelect = document.getElementById('contractor_address_province');
  const citySelect = document.getElementById('contractor_address_city');
  const barangaySelect = document.getElementById('contractor_address_barangay');

  if (provinceSelect) {
      provinceSelect.addEventListener('change', function() {
          const provinceCode = this.value;
          citySelect.innerHTML = '<option value="">Select City/Municipality</option>';
          barangaySelect.innerHTML = '<option value="">Select Barangay</option>';
          citySelect.disabled = true;
          barangaySelect.disabled = true;

          if (provinceCode) {
              fetch(`/api/psgc/provinces/${provinceCode}/cities`)
                  .then(response => response.json())
                  .then(data => {
                      data.forEach(city => {
                          const option = document.createElement('option');
                          option.value = city.code;
                          option.textContent = city.name;
                          option.setAttribute('data-name', city.name);
                          citySelect.appendChild(option);
                      });
                      citySelect.disabled = false;
                  });
          }
      });
  }

  if (citySelect) {
      citySelect.addEventListener('change', function() {
          const cityCode = this.value;
          barangaySelect.innerHTML = '<option value="">Select Barangay</option>';
          barangaySelect.disabled = true;

          if (cityCode) {
              fetch(`/api/psgc/cities/${cityCode}/barangays`)
                  .then(response => response.json())
                  .then(data => {
                      data.forEach(barangay => {
                          const option = document.createElement('option');
                          option.value = barangay.code;
                          option.textContent = barangay.name;
                          option.setAttribute('data-name', barangay.name);
                          barangaySelect.appendChild(option);
                      });
                      barangaySelect.disabled = false;
                  });
          }
      });
  }

  // Profile Upload Preview
  const profileUpload = document.getElementById('profileUpload');
  const profilePreview = document.getElementById('profilePreview');
  const profileIcon = document.getElementById('profileIcon');

  if (profileUpload && profilePreview && profileIcon) {
      profileUpload.addEventListener('change', function(e) {
          const file = e.target.files[0];
          if (file) {
              const reader = new FileReader();
              reader.onload = function(e) {
                  profilePreview.src = e.target.result;
                  profilePreview.classList.remove('hidden');
                  profileIcon.classList.add('hidden');
              };
              reader.readAsDataURL(file);
          }
      });
  }

  // DTI/SEC Dropzone Upload
  const dtiDropzone = document.getElementById('dtiDropzone');
  const dtiUpload = document.getElementById('dtiUpload');
  const dtiFileName = document.getElementById('dtiFileName');

  if (dtiDropzone && dtiUpload) {
      const highlight = () => dtiDropzone.classList.add('ring-2', 'ring-orange-400');
      const unhighlight = () => dtiDropzone.classList.remove('ring-2', 'ring-orange-400');

      // Click to upload
      dtiDropzone.addEventListener('click', () => dtiUpload.click());

      ['dragenter', 'dragover'].forEach(evt => {
          dtiDropzone.addEventListener(evt, (e) => {
              e.preventDefault();
              e.stopPropagation();
              highlight();
          });
      });

      ['dragleave', 'drop'].forEach(evt => {
          dtiDropzone.addEventListener(evt, (e) => {
              e.preventDefault();
              e.stopPropagation();
              unhighlight();
          });
      });

      dtiDropzone.addEventListener('drop', (e) => {
          const file = e.dataTransfer && e.dataTransfer.files && e.dataTransfer.files[0];
          if (file) {
              // Create a new DataTransfer to set files
              const dataTransfer = new DataTransfer();
              dataTransfer.items.add(file);
              dtiUpload.files = dataTransfer.files;

              if (dtiFileName) {
                  const sizeKB = Math.round(file.size / 1024);
                  dtiFileName.textContent = `${file.name} • ${sizeKB} KB`;
              }
              // Clear error if any
              dtiDropzone.classList.remove('border-red-500');
              const errorMsg = dtiDropzone.parentElement.querySelector('.error-message');
              if (errorMsg) errorMsg.remove();
          }
      });

      dtiUpload.addEventListener('change', (e) => {
          const file = e.target.files && e.target.files[0];
          if (file && dtiFileName) {
              const sizeKB = Math.round(file.size / 1024);
              dtiFileName.textContent = `${file.name} • ${sizeKB} KB`;
          }
      });
  }

  // Reset modal form
  function resetModalForm() {
    const inputs = modal.querySelectorAll('input, select, textarea');
    inputs.forEach(input => {
      if (input.type === 'file') {
        input.value = '';
      } else if (input.type === 'checkbox' || input.type === 'radio') {
        input.checked = false;
      } else if (input.tagName === 'SELECT') {
        input.selectedIndex = 0;
      } else {
        input.value = '';
      }
      // Clear error styling
      input.classList.remove('border-red-500');
    });

    // Clear error messages
    modal.querySelectorAll('.error-message').forEach(el => el.remove());

    // Clear upload area errors
    modal.querySelectorAll('.border-red-500').forEach(el => el.classList.remove('border-red-500'));

    // Reset profile preview
    if (profilePreview && profileIcon) {
      profilePreview.classList.add('hidden');
      profileIcon.classList.remove('hidden');
    }

    // Reset DTI file name
    if (dtiFileName) {
      dtiFileName.textContent = '';
      if (dtiDropzone) dtiDropzone.classList.remove('border-orange-500', 'bg-orange-100');
    }

    // Reset Address Selects
    if (citySelect) {
        citySelect.innerHTML = '<option value="">Select City/Municipality</option>';
        citySelect.disabled = true;
    }
    if (barangaySelect) {
        barangaySelect.innerHTML = '<option value="">Select Barangay</option>';
        barangaySelect.disabled = true;
    }
  }

  // Add input animation on focus and clear errors on input
  const modalInputs = document.querySelectorAll('#addContractorModal input, #addContractorModal select, #addContractorModal textarea');
  modalInputs.forEach(input => {
    input.addEventListener('focus', function() {
      this.parentElement.classList.add('transform', 'scale-[1.02]');
      this.style.transition = 'all 0.2s ease';
    });

    input.addEventListener('blur', function() {
      this.parentElement.classList.remove('transform', 'scale-[1.02]');
    });

    // Clear errors on input
    input.addEventListener('input', function() {
        if (this.classList.contains('border-red-500')) {
            this.classList.remove('border-red-500');
            const errorMsg = this.parentElement.querySelector('.error-message');
            if (errorMsg) {
                errorMsg.remove();
            }
        }
    });

    // For select elements, use 'change' event
    if (input.tagName === 'SELECT') {
        input.addEventListener('change', function() {
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

  // Ripple effect function
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

  // Animate table on load
  const rows = document.querySelectorAll('#contractorsTable tr');
  rows.forEach((row, index) => {
    row.style.opacity = '0';
    row.style.transform = 'translateY(20px)';

    setTimeout(() => {
      row.style.transition = 'all 0.4s ease';
      row.style.opacity = '1';
      row.style.transform = 'translateY(0)';
    }, index * 50);
  });

  // ===== EDIT MODAL FUNCTIONALITY =====
  const editModal = document.getElementById('editContractorModal');
  const editModalContent = editModal ? editModal.querySelector('.modal-content') : null;
  const closeEditModalBtn = document.getElementById('closeEditModalBtn');
  const cancelEditBtn = document.getElementById('cancelEditBtn');
  const saveEditBtn = document.getElementById('saveEditBtn');
  const editProfileUpload = document.getElementById('editProfileUpload');
  const editProfilePreview = document.getElementById('editProfilePreview');
  const editProfileIcon = document.getElementById('editProfileIcon');

  // Open edit modal with contractor data
  async function openEditModal(contractorId) {
    if (!editModal || !editModalContent) return;

    try {
        const response = await fetch(`/admin/user-management/contractors/${contractorId}/edit`, {
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        });

        if (!response.ok) throw new Error('Failed to fetch contractor data');

        const result = await response.json();
        if (!result.success) throw new Error(result.error || 'Failed to fetch details');

        const data = result.data;

        // Populate form fields
        document.getElementById('edit_user_id').value = data.user_id;
        document.getElementById('edit_company_name').value = data.company_name || '';
        document.getElementById('edit_company_phone').value = data.company_phone || '';
        document.getElementById('edit_company_start_date').value = data.company_start_date || '';

        // Contractor Type
        const typeSelect = document.getElementById('edit_contractorTypeSelect');
        if (typeSelect) {
            typeSelect.value = data.type_id || '';
            if (data.type_id == 9) {
                const otherInput = document.getElementById('edit_contractorTypeOtherInput');
                if (otherInput) {
                    otherInput.classList.remove('hidden');
                    otherInput.value = data.contractor_type_other || '';
                }
            }
        }

        document.getElementById('edit_services_offered').value = data.services_offered || '';
        document.getElementById('edit_company_website').value = data.company_website || '';
        document.getElementById('edit_company_social_media').value = data.company_social_media || '';

        // Representative
        document.getElementById('edit_first_name').value = data.authorized_rep_fname || '';
        document.getElementById('edit_middle_name').value = data.authorized_rep_mname || '';
        document.getElementById('edit_last_name').value = data.authorized_rep_lname || '';
        document.getElementById('edit_company_email').value = data.email || '';
        document.getElementById('edit_username').value = data.username || '';

        // Address
        document.getElementById('edit_business_address_street').value = data.business_address_street || '';
        document.getElementById('edit_business_address_postal').value = data.business_address_postal || '';

        // Legal Docs
        document.getElementById('edit_picab_number').value = data.picab_number || '';
        document.getElementById('edit_picab_category').value = data.picab_category || '';
        document.getElementById('edit_picab_expiration_date').value = data.picab_expiration_date || '';
        document.getElementById('edit_business_permit_number').value = data.business_permit_number || '';
        document.getElementById('edit_business_permit_city').value = data.business_permit_city || '';
        document.getElementById('edit_business_permit_expiration').value = data.business_permit_expiration || '';
        document.getElementById('edit_tin_business_reg_number').value = data.tin_business_reg_number || '';

        // DTI/SEC File Link
        const dtiLinkContainer = document.getElementById('editCurrentDtiFile');
        if (dtiLinkContainer) {
            if (data.dti_sec_registration_photo) {
                dtiLinkContainer.classList.remove('hidden');
                dtiLinkContainer.querySelector('a').href = `/storage/${data.dti_sec_registration_photo}`;
            } else {
                dtiLinkContainer.classList.add('hidden');
            }
        }

        // Profile Pic Preview
        if (data.profile_pic) {
            editProfilePreview.src = `/storage/${data.profile_pic}`;
            editProfilePreview.classList.remove('hidden');
            editProfileIcon.classList.add('hidden');
        } else {
            editProfilePreview.classList.add('hidden');
            editProfileIcon.classList.remove('hidden');
        }

        // Address (PSGC) - Cascading Dropdowns
        const provinceSelect = document.getElementById('edit_contractor_address_province');
        const citySelect = document.getElementById('edit_contractor_address_city');
        const barangaySelect = document.getElementById('edit_contractor_address_barangay');

        // Set Province
        let provinceCode = '';
        if (data.business_address_province && provinceSelect) {
            for (let i = 0; i < provinceSelect.options.length; i++) {
                const optionName = provinceSelect.options[i].getAttribute('data-name');
                const optionValue = provinceSelect.options[i].value;

                if ((optionName && optionName.trim() === data.business_address_province.trim()) ||
                    (optionValue && optionValue === data.business_address_province.trim())) {
                    provinceSelect.selectedIndex = i;
                    provinceCode = provinceSelect.options[i].value;
                    break;
                }
            }
        }

        // Fetch and Set City
        if (provinceCode && citySelect) {
            try {
                const citiesResponse = await fetch(`/api/psgc/provinces/${provinceCode}/cities`);
                const cities = await citiesResponse.json();

                citySelect.innerHTML = '<option value="">Select City/Municipality</option>';
                let cityCode = '';

                cities.forEach(city => {
                    const option = document.createElement('option');
                    option.value = city.code;
                    option.setAttribute('data-name', city.name);
                    option.textContent = city.name;

                    if (data.business_address_city &&
                        (city.name.trim() === data.business_address_city.trim() ||
                         city.code === data.business_address_city.trim())) {
                        option.selected = true;
                        cityCode = city.code;
                    }
                    citySelect.appendChild(option);
                });
                citySelect.disabled = false;

                // Fetch and Set Barangay
                if (cityCode && barangaySelect) {
                    const barangaysResponse = await fetch(`/api/psgc/cities/${cityCode}/barangays`);
                    const barangays = await barangaysResponse.json();

                    barangaySelect.innerHTML = '<option value="">Select Barangay</option>';
                    barangays.forEach(barangay => {
                        const option = document.createElement('option');
                        option.value = barangay.code;
                        option.setAttribute('data-name', barangay.name);
                        option.textContent = barangay.name;

                        if (data.business_address_barangay &&
                            (barangay.name.trim() === data.business_address_barangay.trim() ||
                             barangay.code === data.business_address_barangay.trim())) {
                            option.selected = true;
                        }
                        barangaySelect.appendChild(option);
                    });
                    barangaySelect.disabled = false;
                }
            } catch (err) {
                console.error('Error fetching address data:', err);
            }
        }

        // Show modal
        editModal.classList.remove('hidden');
        editModal.classList.add('flex');
        document.body.style.overflow = 'hidden';

        // Trigger animation
        setTimeout(() => {
            editModalContent.classList.remove('scale-95', 'opacity-0');
            editModalContent.classList.add('scale-100', 'opacity-100');
        }, 10);

    } catch (error) {
        console.error('Error fetching contractor data:', error);
        showNotification('Failed to load contractor data', 'error');
    }
  }

  // Close edit modal function
  function closeEditModal() {
    if (!editModalContent) return;

    editModalContent.classList.remove('scale-100', 'opacity-100');
    editModalContent.classList.add('scale-95', 'opacity-0');

    setTimeout(() => {
      editModal.classList.add('hidden');
      editModal.classList.remove('flex');
      document.body.style.overflow = 'auto';

      // Reset form
      const form = document.getElementById('editContractorForm');
      if (form) form.reset();

      // Clear error messages and styles
      editModal.querySelectorAll('.error-message').forEach(el => el.remove());
      editModal.querySelectorAll('.border-red-500').forEach(el => el.classList.remove('border-red-500'));

      // Reset previews
      if (editProfilePreview) editProfilePreview.classList.add('hidden');
      if (editProfileIcon) editProfileIcon.classList.remove('hidden');
    }, 300);
  }

  // Close button handlers
  if (closeEditModalBtn) {
    closeEditModalBtn.addEventListener('click', closeEditModal);
  }

  if (cancelEditBtn) {
    cancelEditBtn.addEventListener('click', closeEditModal);
  }

  // Close on backdrop click
  if (editModal) {
    editModal.addEventListener('click', function(e) {
      if (e.target === editModal) {
        closeEditModal();
      }
    });
  }

  // Profile picture upload preview
  if (editProfileUpload && editProfilePreview && editProfileIcon) {
    editProfileUpload.addEventListener('change', function(e) {
      const file = e.target.files[0];
      if (file) {
        const reader = new FileReader();
        reader.onload = function(event) {
          editProfilePreview.src = event.target.result;
          editProfilePreview.classList.remove('hidden');
          editProfileIcon.classList.add('hidden');
        };
        reader.readAsDataURL(file);
      }
    });
  }

  // Edit Modal PSGC Logic
  const editProvince = document.getElementById('edit_contractor_address_province');
  const editCity = document.getElementById('edit_contractor_address_city');
  const editBarangay = document.getElementById('edit_contractor_address_barangay');

  if (editProvince) {
      editProvince.addEventListener('change', function() {
          const provinceCode = this.value;

          editCity.innerHTML = '<option value="">Loading...</option>';
          editCity.disabled = true;
          editBarangay.innerHTML = '<option value="">Select City First</option>';
          editBarangay.disabled = true;

          if (provinceCode) {
              fetch(`/api/psgc/provinces/${provinceCode}/cities`)
                  .then(response => response.json())
                  .then(data => {
                      editCity.innerHTML = '<option value="">Select City/Municipality</option>';
                      data.forEach(city => {
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
      editCity.addEventListener('change', function() {
          const cityCode = this.value;

          editBarangay.innerHTML = '<option value="">Loading...</option>';
          editBarangay.disabled = true;

          if (cityCode) {
              fetch(`/api/psgc/cities/${cityCode}/barangays`)
                  .then(response => response.json())
                  .then(data => {
                      editBarangay.innerHTML = '<option value="">Select Barangay</option>';
                      data.forEach(barangay => {
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

  // Edit Contractor Type "Others" Toggle
  const editContractorTypeSelect = document.getElementById('edit_contractorTypeSelect');
  const editContractorTypeOtherInput = document.getElementById('edit_contractorTypeOtherInput');
  if (editContractorTypeSelect && editContractorTypeOtherInput) {
      editContractorTypeSelect.addEventListener('change', function() {
          if (this.value == '9') {
              editContractorTypeOtherInput.classList.remove('hidden');
              editContractorTypeOtherInput.setAttribute('required', 'required');
          } else {
              editContractorTypeOtherInput.classList.add('hidden');
              editContractorTypeOtherInput.removeAttribute('required');
              editContractorTypeOtherInput.value = '';
          }
      });
  }

  // Save button handler
  if (saveEditBtn) {
    saveEditBtn.addEventListener('click', async function(e) {
      e.preventDefault();

      const form = document.getElementById('editContractorForm');
      const userId = document.getElementById('edit_user_id').value;
      const formData = new FormData(form);

      // Handle PSGC names
      if (editProvince && editProvince.selectedIndex > 0) {
          const name = editProvince.options[editProvince.selectedIndex].getAttribute('data-name') || editProvince.options[editProvince.selectedIndex].text;
          formData.set('business_address_province', name);
      }
      if (editCity && editCity.selectedIndex > 0) {
          const name = editCity.options[editCity.selectedIndex].getAttribute('data-name') || editCity.options[editCity.selectedIndex].text;
          formData.set('business_address_city', name);
      }
      if (editBarangay && editBarangay.selectedIndex > 0) {
          const name = editBarangay.options[editBarangay.selectedIndex].getAttribute('data-name') || editBarangay.options[editBarangay.selectedIndex].text;
          formData.set('business_address_barangay', name);
      }

      // Add CSRF token
      const csrfToken = document.querySelector('meta[name="csrf-token"]');
      if (csrfToken) {
          formData.append('_token', csrfToken.content);
      }

      // Add method spoofing for PUT
      formData.append('_method', 'PUT');

      // Add loading state
      const originalContent = this.innerHTML;
      this.innerHTML = '<i class="fi fi-rr-spinner animate-spin"></i> Saving...';
      this.disabled = true;

      // Clear previous errors
      editModal.querySelectorAll('.error-message').forEach(el => el.remove());
      editModal.querySelectorAll('.border-red-500').forEach(el => el.classList.remove('border-red-500'));

      try {
        const response = await fetch(`/admin/user-management/contractors/update/${userId}`, {
            method: 'POST',
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: formData
        });

        const result = await response.json();

        if (response.ok && result.success) {
            showNotification('Contractor updated successfully!', 'success');
            closeEditModal();
            
            // Check if we're on contractor_Views page
            const isViewPage = document.querySelector('[data-contractor-id]');
            if (isViewPage) {
                setTimeout(function() {
                    window.location.reload();
                }, 500);
            } else {
                handleFilterChange(); // Refresh table on main page
            }
        } else {
            if (result.errors) {
                for (const [key, messages] of Object.entries(result.errors)) {
                    const input = editModal.querySelector(`[name="${key}"]`);
                    if (input) {
                        input.classList.add('border-red-500');
                        const errorMsg = document.createElement('p');
                        errorMsg.className = 'text-red-500 text-xs mt-1 error-message';
                        errorMsg.textContent = messages[0];
                        input.parentElement.appendChild(errorMsg);
                    } else {
                        showNotification(messages[0], 'error');
                    }
                }
                // Scroll to first error
                const firstError = editModal.querySelector('.error-message');
                if (firstError) {
                    firstError.scrollIntoView({ behavior: 'smooth', block: 'center' });
                }
            } else {
                showNotification(result.message || 'An error occurred', 'error');
            }
        }
      } catch (error) {
        console.error('Error updating contractor:', error);
        showNotification('An error occurred while updating', 'error');
      } finally {
        this.innerHTML = originalContent;
        this.disabled = false;
      }
    });
  }

  // Add input focus effects for edit modal
  const editInputs = editModal ? editModal.querySelectorAll('input, select, textarea') : [];
  editInputs.forEach(input => {
    input.addEventListener('focus', function() {
      this.parentElement.classList.add('ring-2', 'ring-orange-200');
    });

    input.addEventListener('blur', function() {
      this.parentElement.classList.remove('ring-2', 'ring-orange-200');
    });

    // Clear errors on input
    input.addEventListener('input', function() {
        if (this.classList.contains('border-red-500')) {
            this.classList.remove('border-red-500');
            const errorMsg = this.parentElement.querySelector('.error-message');
            if (errorMsg) {
                errorMsg.remove();
            }
        }
    });

    // For select elements, use 'change' event
    if (input.tagName === 'SELECT') {
        input.addEventListener('change', function() {
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

  // ===== DELETE MODAL FUNCTIONALITY =====
  const deleteModal = document.getElementById('deleteContractorModal');
  const deleteModalContent = deleteModal ? deleteModal.querySelector('.modal-content') : null;
  const confirmDeleteBtn = document.getElementById('confirmDeleteBtn');
  const cancelDeleteBtn = document.getElementById('cancelDeleteBtn');
  const deleteContractorNameSpan = document.getElementById('deleteContractorName');
  const deletionReasonInput = document.getElementById('deletionReason');
  const deletionReasonError = document.getElementById('deletionReasonError');
  let rowToDelete = null;
  let idToDelete = null;

  // Open delete modal
  function openDeleteModal(contractorName, row, id) {
    if (!deleteModal || !deleteModalContent) return;

    rowToDelete = row;
    idToDelete = id;

    // Set contractor name
    if (deleteContractorNameSpan) {
      deleteContractorNameSpan.textContent = contractorName;
    }

    // Reset reason input
    if (deletionReasonInput) {
        deletionReasonInput.value = '';
        deletionReasonInput.classList.remove('border-red-500');
    }
    if (deletionReasonError) {
        deletionReasonError.classList.add('hidden');
    }

    // Show modal
    deleteModal.classList.remove('hidden');
    deleteModal.classList.add('flex');
    document.body.style.overflow = 'hidden';

    // Trigger animation
    setTimeout(() => {
      deleteModalContent.classList.remove('scale-95', 'opacity-0');
      deleteModalContent.classList.add('scale-100', 'opacity-100');
    }, 10);
  }

  // Close delete modal
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

  // Cancel delete button
  if (cancelDeleteBtn) {
    cancelDeleteBtn.addEventListener('click', closeDeleteModal);
  }

  // Confirm delete button
  if (confirmDeleteBtn) {
    confirmDeleteBtn.addEventListener('click', async function() {
      if (!rowToDelete || !idToDelete) return;

      // Validate reason
      const reason = deletionReasonInput.value.trim();
      if (!reason) {
          deletionReasonInput.classList.add('border-red-500');
          deletionReasonError.classList.remove('hidden');
          return;
      } else {
          deletionReasonInput.classList.remove('border-red-500');
          deletionReasonError.classList.add('hidden');
      }

      // Add loading state
      const originalContent = this.innerHTML;
      this.innerHTML = '<i class="fi fi-rr-spinner animate-spin"></i> Deleting...';
      this.disabled = true;

      try {
          const csrfToken = document.querySelector('meta[name="csrf-token"]').content;
          const response = await fetch(`/admin/user-management/contractors/${idToDelete}`, {
              method: 'POST',
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
            showNotification('Contractor deleted successfully!', 'success');
            closeDeleteModal();
            handleFilterChange(); // Refresh table data
          } else {
              showNotification(result.message || 'Failed to delete contractor', 'error');
          }
      } catch (error) {
          console.error('Error deleting contractor:', error);
          showNotification('An error occurred while deleting', 'error');
      } finally {
        // Reset button
        this.innerHTML = originalContent;
        this.disabled = false;
      }
    });
  }

  // Close on backdrop click
  if (deleteModal) {
    deleteModal.addEventListener('click', function(e) {
      if (e.target === deleteModal) {
        closeDeleteModal();
      }
    });
  }

  // Success notification helper
  function showNotification(message, type = 'success') {
    const notification = document.createElement('div');
    notification.className = `fixed top-24 right-8 z-[60] px-6 py-4 rounded-lg shadow-2xl transform transition-all duration-500 translate-x-full ${
      type === 'success' ? 'bg-green-500' : 'bg-red-500'
    } text-white font-semibold flex items-center gap-3`;
    notification.innerHTML = `
      <i class="fi fi-rr-${type === 'success' ? 'check-circle' : 'cross-circle'} text-2xl"></i>
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

  // Make functions globally accessible
  window.openDeleteModal = openDeleteModal;
  window.openEditModal = openEditModal;
  window.closeEditModal = closeEditModal;
});

// Add CSS for ripple effect
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

  #contractorsTable tr {
    transition: all 0.2s ease;
  }

  #contractorsTable tr:hover {
    transform: translateX(4px);
  }

  .scale-95 {
    transform: scale(0.95);
    transition: transform 0.1s ease;
  }

  .fi-rr-spinner {
    display: inline-block;
    animation: spin 1s linear infinite;
  }

  @keyframes spin {
    from { transform: rotate(0deg); }
    to { transform: rotate(360deg); }
  }
`;
document.head.appendChild(style);
