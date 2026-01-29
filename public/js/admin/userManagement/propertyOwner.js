document.addEventListener('DOMContentLoaded', function() {

  const dateFromInput = document.getElementById('dateFrom');
  const dateToInput = document.getElementById('dateTo');
  const searchInput = document.getElementById('searchInput');
  const resetBtn = document.getElementById('resetFilterBtn');
  const ownersWrap = document.getElementById('ownersTableWrap');

  let debounceTimer;

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
      const paginationLinks = document.querySelectorAll('#ownersTableWrap .pagination a');
      paginationLinks.forEach(link => {
          link.addEventListener('click', function(e) {
              e.preventDefault();
              const url = this.href;
              fetchAndUpdate(url);
          });
      });
  }

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
      btn.addEventListener('click', function(e) {
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

    const tableRows = document.querySelectorAll('#propertyOwnersTable tr');
    tableRows.forEach(row => {
      row.addEventListener('click', function() {
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

  if (addBtn && modal) {

    addBtn.addEventListener('click', function() {
      modal.classList.remove('hidden');
      document.body.style.overflow = 'hidden';

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

    modal.addEventListener('click', function(e) {
      if (e.target === modal) {
        closeModal();
      }
    });

    document.addEventListener('keydown', function(e) {
      if (e.key === 'Escape' && !modal.classList.contains('hidden')) {
        closeModal();
      }
    });

    saveBtn.addEventListener('click', async function() {
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
                    for (const [key, messages] of Object.entries(result.errors)) {
                        const input = modal.querySelector(`[name="${key}"]`);
                        if (input) {
                            input.classList.add('border-red-500');
                            const errorMsg = document.createElement('p');
                            errorMsg.className = 'text-red-500 text-xs mt-1 error-message';
                            errorMsg.textContent = messages[0];
                            input.parentElement.appendChild(errorMsg);
                        } else {

                            if (key === 'valid_id_photo') {
                                const area = document.getElementById('idFrontUploadArea');
                                if (area) {
                                    area.classList.add('border-red-500');
                                    const errorMsg = document.createElement('p');
                                    errorMsg.className = 'text-red-500 text-xs mt-1 error-message';
                                    errorMsg.textContent = messages[0];
                                    area.parentElement.appendChild(errorMsg);
                                }
                            } else if (key === 'valid_id_back_photo') {
                                const area = document.getElementById('idBackUploadArea');
                                if (area) {
                                    area.classList.add('border-red-500');
                                    const errorMsg = document.createElement('p');
                                    errorMsg.className = 'text-red-500 text-xs mt-1 error-message';
                                    errorMsg.textContent = messages[0];
                                    area.parentElement.appendChild(errorMsg);
                                }
                            } else if (key === 'police_clearance') {
                                const area = document.getElementById('policeClearanceUploadArea');
                                if (area) {
                                    area.classList.add('border-red-500');
                                    const errorMsg = document.createElement('p');
                                    errorMsg.className = 'text-red-500 text-xs mt-1 error-message';
                                    errorMsg.textContent = messages[0];
                                    area.parentElement.appendChild(errorMsg);
                                }
                            } else if (key === 'profile_pic') {
                                const container = document.querySelector('.relative.group'); // Profile pic container
                                if (container) {
                                    const errorMsg = document.createElement('p');
                                    errorMsg.className = 'text-red-500 text-xs mt-1 error-message text-center';
                                    errorMsg.textContent = messages[0];
                                    container.parentElement.appendChild(errorMsg);
                                }
                            }
                        }
                    }

                    const firstError = modal.querySelector('.error-message');
                    if (firstError) {
                        firstError.scrollIntoView({ behavior: 'smooth', block: 'center' });
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
      occupationSelect.addEventListener('change', function() {
          if (this.value === 'others') {
              occupationOtherInput.classList.remove('hidden');
          } else {
              occupationOtherInput.classList.add('hidden');
              occupationOtherInput.value = '';
          }
      });
  }

  function setupFileUpload(uploadId, areaId, fileNameId) {
      const upload = document.getElementById(uploadId);
      const area = document.getElementById(areaId);
      const fileName = document.getElementById(fileNameId);

      if (upload && area && fileName) {
          area.addEventListener('click', () => upload.click());

          upload.addEventListener('change', function() {
              if (this.files && this.files[0]) {
                  fileName.textContent = this.files[0].name;
                  fileName.classList.remove('hidden');
                  area.classList.add('border-orange-400', 'bg-orange-50');
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
              }
          });
      }
  }

  setupFileUpload('idFrontUpload', 'idFrontUploadArea', 'idFrontFileName');
  setupFileUpload('idBackUpload', 'idBackUploadArea', 'idBackFileName');
  setupFileUpload('policeClearanceUpload', 'policeClearanceUploadArea', 'policeClearanceFileName');

  const profileUpload = document.getElementById('profileUpload');
  const profilePreview = document.getElementById('profilePreview');
  const profileIcon = document.getElementById('profileIcon');

  if (profileUpload) {
    profileUpload.addEventListener('change', function(e) {
      const file = e.target.files[0];
      if (file) {
        const reader = new FileReader();
        reader.onload = function(event) {
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

    idUpload.addEventListener('change', function(e) {
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

      input.classList.remove('border-red-500');
    });

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
        if (el) el.classList.remove('border-orange-400', 'bg-orange-50');
    });
  }

  const modalInputs = document.querySelectorAll('#addPropertyOwnerModal input, #addPropertyOwnerModal select');
  modalInputs.forEach(input => {
    input.addEventListener('focus', function() {
      this.parentElement.classList.add('transform', 'scale-[1.02]');
      this.style.transition = 'all 0.2s ease';
    });

    input.addEventListener('blur', function() {
      this.parentElement.classList.remove('transform', 'scale-[1.02]');
    });

    input.addEventListener('input', function() {
        if (this.classList.contains('border-red-500')) {
            this.classList.remove('border-red-500');
            const errorMsg = this.parentElement.querySelector('.error-message');
            if (errorMsg) {
                errorMsg.remove();
            }
        }
    });

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

  const avatars = document.querySelectorAll('.w-10.h-10.rounded-full');
  avatars.forEach(avatar => {
    avatar.addEventListener('mouseenter', function() {
      this.style.transform = 'scale(1.1) rotate(5deg)';
      this.style.transition = 'all 0.3s ease';
    });

    avatar.addEventListener('mouseleave', function() {
      this.style.transform = 'scale(1) rotate(0deg)';
    });
  });

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

  const editModal = document.getElementById('editPropertyOwnerModal');
  const editModalContent = editModal ? editModal.querySelector('.modal-content') : null;
  const closeEditModalBtn = document.getElementById('closeEditModalBtn');
  const cancelEditBtn = document.getElementById('cancelEditBtn');
  const saveEditBtn = document.getElementById('saveEditBtn');
  const editProfileUpload = document.getElementById('editProfileUpload');
  const editProfilePreview = document.getElementById('editProfilePreview');
  const editProfileIcon = document.getElementById('editProfileIcon');

  async function openEditModal(userId) {
    if (!editModal || !editModalContent) return;

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
        document.getElementById('edit_phone_number').value = owner.phone_number;
        document.getElementById('edit_email').value = user.email;
        document.getElementById('edit_username').value = user.username;
        document.getElementById('edit_street_address').value = owner.street_address;
        document.getElementById('edit_zip_code').value = owner.zip_code;

        const occupationSelect = document.getElementById('edit_occupationSelect');
        const occupationOtherInput = document.getElementById('edit_occupationOtherInput');
        if (owner.occupation_id) {
            occupationSelect.value = owner.occupation_id;
            occupationOtherInput.classList.add('hidden');
        } else {
            occupationSelect.value = 'others';
            occupationOtherInput.value = owner.occupation;
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

        const provinceSelect = document.getElementById('edit_owner_address_province');
        const citySelect = document.getElementById('edit_owner_address_city');
        const barangaySelect = document.getElementById('edit_owner_address_barangay');

        let provinceCode = '';
        for (let i = 0; i < provinceSelect.options.length; i++) {
            if (provinceSelect.options[i].getAttribute('data-name') === owner.province) {
                provinceSelect.selectedIndex = i;
                provinceCode = provinceSelect.value;
                break;
            }
        }

        if (provinceCode) {

            const citiesResponse = await fetch(`/api/psgc/provinces/${provinceCode}/cities`);
            const cities = await citiesResponse.json();

            citySelect.innerHTML = '<option value="">Select City/Municipality</option>';
            let cityCode = '';
            cities.forEach(city => {
                const option = document.createElement('option');
                option.value = city.code;
                option.setAttribute('data-name', city.name);
                option.textContent = city.name;
                if (city.name === owner.city) {
                    option.selected = true;
                    cityCode = city.code;
                }
                citySelect.appendChild(option);
            });
            citySelect.disabled = false;

            if (cityCode) {

                const barangaysResponse = await fetch(`/api/psgc/cities/${cityCode}/barangays`);
                const barangays = await barangaysResponse.json();

                barangaySelect.innerHTML = '<option value="">Select Barangay</option>';
                barangays.forEach(barangay => {
                    const option = document.createElement('option');
                    option.value = barangay.code;
                    option.setAttribute('data-name', barangay.name);
                    option.textContent = barangay.name;
                    if (barangay.name === owner.barangay) {
                        option.selected = true;
                    }
                    barangaySelect.appendChild(option);
                });
                barangaySelect.disabled = false;
            }
        }

        document.getElementById('edit_valid_id_id').value = owner.valid_id_id;

        const currentIdFront = document.getElementById('currentIdFront');
        const currentIdBack = document.getElementById('currentIdBack');
        const currentPoliceClearance = document.getElementById('currentPoliceClearance');

        if (owner.valid_id_photo) {
            currentIdFront.innerHTML = `Current: <a href="/storage/${owner.valid_id_photo}" target="_blank" class="text-orange-500 hover:underline">View File</a>`;
        } else {
            currentIdFront.innerHTML = '';
        }

        if (owner.valid_id_back_photo) {
            currentIdBack.innerHTML = `Current: <a href="/storage/${owner.valid_id_back_photo}" target="_blank" class="text-orange-500 hover:underline">View File</a>`;
        } else {
            currentIdBack.innerHTML = '';
        }

        if (owner.police_clearance) {
            currentPoliceClearance.innerHTML = `Current: <a href="/storage/${owner.police_clearance}" target="_blank" class="text-orange-500 hover:underline">View File</a>`;
        } else {
            currentPoliceClearance.innerHTML = '';
        }

        editModal.classList.remove('hidden');
        editModal.classList.add('flex');
        document.body.style.overflow = 'hidden';

        setTimeout(() => {
            editModalContent.classList.remove('scale-95', 'opacity-0');
            editModalContent.classList.add('scale-100', 'opacity-100');
        }, 10);

    } catch (error) {
        console.error('Error fetching user data:', error);
        alert('Failed to load user data.');
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

      document.getElementById('editPropertyOwnerForm').reset();

      editModal.querySelectorAll('.error-message').forEach(el => el.remove());
      editModal.querySelectorAll('.border-red-500').forEach(el => el.classList.remove('border-red-500'));

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
    editModal.addEventListener('click', function(e) {
      if (e.target === editModal) {
        closeEditModal();
      }
    });
  }

  if (editModalContent) {
    editModalContent.addEventListener('click', function(e) {
      e.stopPropagation();
    });
  }

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

  setupFileUpload('editIdFrontUpload', 'editIdFrontUploadArea', 'editIdFrontFileName');
  setupFileUpload('editIdBackUpload', 'editIdBackUploadArea', 'editIdBackFileName');
  setupFileUpload('editPoliceClearanceUpload', 'editPoliceClearanceUploadArea', 'editPoliceClearanceFileName');

  const editProvince = document.getElementById('edit_owner_address_province');
  const editCity = document.getElementById('edit_owner_address_city');
  const editBarangay = document.getElementById('edit_owner_address_barangay');

  if (editProvince) {
      editProvince.addEventListener('change', function() {
          const provinceCode = this.value;

          editCity.innerHTML = '<option value="">Loading...</option>';
          editCity.disabled = true;
          editBarangay.innerHTML = '<option value="">Select City First</option>';
          editBarangay.disabled = true;

          if (provinceCode) {
              fetch('/api/psgc/provinces/' + provinceCode + '/cities')
                  .then(response => response.json())
                  .then(data => {
                      editCity.innerHTML = '<option value="">Select City/Municipality</option>';
                      data.forEach(function(city) {
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
              fetch('/api/psgc/cities/' + cityCode + '/barangays')
                  .then(response => response.json())
                  .then(data => {
                      editBarangay.innerHTML = '<option value="">Select Barangay</option>';
                      data.forEach(function(barangay) {
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
      editOccupationSelect.addEventListener('change', function() {
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
    editForm.addEventListener('submit', async function(e) {
      e.preventDefault();

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

      editModal.querySelectorAll('.error-message').forEach(el => el.remove());
      editModal.querySelectorAll('.border-red-500').forEach(el => el.classList.remove('border-red-500'));

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
                for (const [key, messages] of Object.entries(result.errors)) {

                    let input = editModal.querySelector(`[name="${key}"]`);

                    if (!input) {
                        if (key === 'valid_id_photo') {
                            const area = document.getElementById('editIdFrontUploadArea');
                            if (area) {
                                area.classList.add('border-red-500');
                                const errorMsg = document.createElement('p');
                                errorMsg.className = 'text-red-500 text-xs mt-1 error-message';
                                errorMsg.textContent = messages[0];
                                area.parentElement.appendChild(errorMsg);
                            }
                        } else if (key === 'valid_id_back_photo') {
                            const area = document.getElementById('editIdBackUploadArea');
                            if (area) {
                                area.classList.add('border-red-500');
                                const errorMsg = document.createElement('p');
                                errorMsg.className = 'text-red-500 text-xs mt-1 error-message';
                                errorMsg.textContent = messages[0];
                                area.parentElement.appendChild(errorMsg);
                            }
                        } else if (key === 'police_clearance') {
                            const area = document.getElementById('editPoliceClearanceUploadArea');
                            if (area) {
                                area.classList.add('border-red-500');
                                const errorMsg = document.createElement('p');
                                errorMsg.className = 'text-red-500 text-xs mt-1 error-message';
                                errorMsg.textContent = messages[0];
                                area.parentElement.appendChild(errorMsg);
                            }
                        } else if (key === 'profile_pic') {
                            const container = document.querySelector('#editPropertyOwnerModal .relative.group');
                            if (container) {
                                const errorMsg = document.createElement('p');
                                errorMsg.className = 'text-red-500 text-xs mt-1 error-message text-center';
                                errorMsg.textContent = messages[0];
                                container.parentElement.appendChild(errorMsg);
                            }
                        }
                    } else {
                        input.classList.add('border-red-500');
                        const errorMsg = document.createElement('p');
                        errorMsg.className = 'text-red-500 text-xs mt-1 error-message';
                        errorMsg.textContent = messages[0];
                        input.parentElement.appendChild(errorMsg);
                    }
                }

                const firstError = editModal.querySelector('.error-message');
                if (firstError) {
                    firstError.scrollIntoView({ behavior: 'smooth', block: 'center' });
                }
            } else {
                alert(result.message || 'An error occurred');
            }
        }
      } catch (error) {
        console.error('Error updating property owner:', error);
        alert('An error occurred while updating.');
      } finally {
        saveEditBtn.innerHTML = originalContent;
        saveEditBtn.disabled = false;
      }
    });
  }

  const editInputs = editModal ? editModal.querySelectorAll('input, select') : [];
  editInputs.forEach(input => {
    input.addEventListener('focus', function() {
      this.parentElement.classList.add('ring-2', 'ring-orange-200');
    });

    input.addEventListener('blur', function() {
      this.parentElement.classList.remove('ring-2', 'ring-orange-200');
    });

    input.addEventListener('input', function() {
        if (this.classList.contains('border-red-500')) {
            this.classList.remove('border-red-500');
            const errorMsg = this.parentElement.querySelector('.error-message');
            if (errorMsg) {
                errorMsg.remove();
            }
        }
    });

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

  document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape' && editModal && !editModal.classList.contains('hidden')) {
      closeEditModal();
    }
  });

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

  const deleteModal = document.getElementById('deleteUserModal');
  const deleteModalContent = deleteModal ? deleteModal.querySelector('.modal-content') : null;
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

  if (confirmDeleteBtn) {
    confirmDeleteBtn.addEventListener('click', async function() {
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
    deleteModal.addEventListener('click', function(e) {
      if (e.target === deleteModal) {
        closeDeleteModal();
      }
    });
  }

  if (deleteModalContent) {
    deleteModalContent.addEventListener('click', function(e) {
      e.stopPropagation();
    });
  }

  document.addEventListener('keydown', function(e) {
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

  #propertyOwnersTable tr {
    transition: all 0.2s ease;
  }

  #propertyOwnersTable tr:hover {
    transform: translateX(4px);
  }

  .scale-95 {
    transform: scale(0.95);
    transition: transform 0.1s ease;
  }
`;
document.head.appendChild(style);
