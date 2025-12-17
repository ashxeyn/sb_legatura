document.addEventListener('DOMContentLoaded', function() {
  // Period Dropdown Toggle
  const periodBtn = document.getElementById('periodBtn');
  const periodDropdown = document.getElementById('periodDropdown');
  const periodText = document.getElementById('periodText');
  const periodOptions = document.querySelectorAll('.period-option');

  if (periodBtn && periodDropdown) {
    periodBtn.addEventListener('click', function(e) {
      e.stopPropagation();
      periodDropdown.classList.toggle('hidden');
    });

    // Close dropdown when clicking outside
    document.addEventListener('click', function(e) {
      if (!periodBtn.contains(e.target) && !periodDropdown.contains(e.target)) {
        periodDropdown.classList.add('hidden');
      }
    });

    // Handle period selection
    periodOptions.forEach(option => {
      option.addEventListener('click', function() {
        periodText.textContent = this.textContent;
        periodDropdown.classList.add('hidden');
        
        // Add animation feedback
        periodBtn.classList.add('scale-95');
        setTimeout(() => {
          periodBtn.classList.remove('scale-95');
        }, 100);
      });
    });
  }

  // Action Buttons Interactivity
  const viewButtons = document.querySelectorAll('.view-btn');
  const editButtons = document.querySelectorAll('.edit-btn');
  const deleteButtons = document.querySelectorAll('.delete-btn');

  viewButtons.forEach(btn => {
    btn.addEventListener('click', function(e) {
      e.stopPropagation();
      
      // Add ripple effect
      addRipple(this, e);
      
      // Navigate to view page (use dummy ID for now, will use actual ID from database later)
      setTimeout(() => {
        const dummyId = Math.floor(Math.random() * 1000);
        window.location.href = `/admin/user-management/property-owners/${dummyId}`;
      }, 200);
    });
  });

  editButtons.forEach(btn => {
    btn.addEventListener('click', function(e) {
      e.stopPropagation();
      const row = this.closest('tr');
      const name = row.querySelector('.font-medium').textContent;
      const initials = row.querySelector('.w-10.h-10.rounded-full').textContent.trim();
      const dateRegistered = row.querySelectorAll('td')[1].textContent.trim();
      const occupation = row.querySelectorAll('td')[2].textContent.trim();
      
      addRipple(this, e);
      
      setTimeout(() => {
        openEditModal({
          name: name,
          initials: initials,
          dateRegistered: dateRegistered,
          occupation: occupation
        });
      }, 200);
    });
  });

  deleteButtons.forEach(btn => {
    btn.addEventListener('click', function(e) {
      e.stopPropagation();
      const row = this.closest('tr');
      const name = row.querySelector('.font-medium').textContent;
      
      addRipple(this, e);
      
      setTimeout(() => {
        openDeleteModal(name, row);
      }, 200);
    });
  });

  // Table row click highlight
  const tableRows = document.querySelectorAll('#propertyOwnersTable tr');
  tableRows.forEach(row => {
    row.addEventListener('click', function() {
      // Remove previous selection
      tableRows.forEach(r => r.classList.remove('bg-indigo-50'));
      
      // Add selection to current row
      this.classList.add('bg-indigo-50');
    });
  });

  // Ranking Filter
  const rankingFilter = document.getElementById('rankingFilter');
  if (rankingFilter) {
    rankingFilter.addEventListener('change', function() {
      const value = this.value;
      console.log('Sorting by:', value);
      
      // Add visual feedback
      this.classList.add('ring-2', 'ring-indigo-400');
      setTimeout(() => {
        this.classList.remove('ring-2', 'ring-indigo-400');
      }, 500);
      
      // Get all table rows
      const tbody = document.querySelector('#propertyOwnersTable');
      const rows = Array.from(tbody.querySelectorAll('tr'));
      
      // Sort rows based on selected criteria
      rows.sort((a, b) => {
        switch(value) {
          case 'name':
            const nameA = a.querySelector('.font-medium').textContent.trim().toLowerCase();
            const nameB = b.querySelector('.font-medium').textContent.trim().toLowerCase();
            return nameA.localeCompare(nameB);
            
          case 'projects':
            const projectsA = parseInt(a.querySelectorAll('.bg-indigo-100')[0].textContent);
            const projectsB = parseInt(b.querySelectorAll('.bg-indigo-100')[0].textContent);
            return projectsB - projectsA; // Descending order
            
          case 'date':
            const dateTextA = a.querySelectorAll('td')[1].textContent.replace(/\s+/g, ' ').trim();
            const dateTextB = b.querySelectorAll('td')[1].textContent.replace(/\s+/g, ' ').trim();
            const dateA = parseDateString(dateTextA);
            const dateB = parseDateString(dateTextB);
            return dateB - dateA; // Most recent first
            
          case 'ranking':
          default:
            // Keep original order (or implement custom ranking logic)
            return 0;
        }
      });
      
      // Remove all rows
      rows.forEach(row => row.remove());
      
      // Re-append sorted rows with animation
      rows.forEach((row, index) => {
        row.style.opacity = '0';
        row.style.transform = 'translateY(20px)';
        tbody.appendChild(row);
        
        setTimeout(() => {
          row.style.transition = 'all 0.4s ease';
          row.style.opacity = '1';
          row.style.transform = 'translateY(0)';
        }, index * 50);
      });
    });
  }

  // Helper function to parse date strings
  function parseDateString(dateStr) {
    const months = {
      'Jan': 0, 'Feb': 1, 'Mar': 2, 'Apr': 3, 'May': 4, 'Jun': 5,
      'Jul': 6, 'July': 6, 'Aug': 7, 'Sep': 8, 'Oct': 9, 'Nov': 10, 'Dec': 11
    };
    
    const parts = dateStr.match(/(\d+)\s+([A-Za-z]+),?\s*(\d{4})/);
    if (parts) {
      const day = parseInt(parts[1]);
      const month = months[parts[2]];
      const year = parseInt(parts[3]);
      return new Date(year, month, day);
    }
    return new Date();
  }

  // Add Property Owner Button
  const addBtn = document.querySelector('#addPropertyOwnerBtn');
  const modal = document.getElementById('addPropertyOwnerModal');
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
    saveBtn.addEventListener('click', function() {
      // Add form validation here
      alert('Property Owner saved successfully! (Form validation to be implemented)');
      closeModal();
    });
  }

  // Profile Picture Upload
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

  // ID Upload Area
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

    // Drag and drop
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

  // Password Toggle
  const togglePassword = document.getElementById('togglePassword');
  const passwordInput = document.getElementById('passwordInput');
  const eyeIcon = document.getElementById('eyeIcon');

  if (togglePassword) {
    togglePassword.addEventListener('click', function() {
      const type = passwordInput.type === 'password' ? 'text' : 'password';
      passwordInput.type = type;
      eyeIcon.className = type === 'password' ? 'fi fi-rr-eye' : 'fi fi-rr-eye-crossed';
    });
  }

  // Confirm Password Toggle
  const toggleConfirmPassword = document.getElementById('toggleConfirmPassword');
  const confirmPasswordInput = document.getElementById('confirmPasswordInput');
  const eyeIconConfirm = document.getElementById('eyeIconConfirm');

  if (toggleConfirmPassword) {
    toggleConfirmPassword.addEventListener('click', function() {
      const type = confirmPasswordInput.type === 'password' ? 'text' : 'password';
      confirmPasswordInput.type = type;
      eyeIconConfirm.className = type === 'password' ? 'fi fi-rr-eye' : 'fi fi-rr-eye-crossed';
    });
  }

  // Reset modal form
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
    });
    
    // Reset profile preview
    if (profilePreview && profileIcon) {
      profilePreview.classList.add('hidden');
      profileIcon.classList.remove('hidden');
    }
    
    // Reset ID file name
    if (idFileName) {
      idFileName.classList.add('hidden');
      idUploadArea.classList.remove('border-orange-400', 'bg-orange-50');
    }
  }

  // Add input animation on focus
  const modalInputs = document.querySelectorAll('#addPropertyOwnerModal input, #addPropertyOwnerModal select');
  modalInputs.forEach(input => {
    input.addEventListener('focus', function() {
      this.parentElement.classList.add('transform', 'scale-[1.02]');
      this.style.transition = 'all 0.2s ease';
    });
    
    input.addEventListener('blur', function() {
      this.parentElement.classList.remove('transform', 'scale-[1.02]');
    });
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

  // Add hover effect to avatar
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

  // Animate table on load
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

  // ===== EDIT MODAL FUNCTIONALITY =====
  const editModal = document.getElementById('editPropertyOwnerModal');
  const editModalContent = editModal ? editModal.querySelector('.modal-content') : null;
  const closeEditModalBtn = document.getElementById('closeEditModalBtn');
  const cancelEditBtn = document.getElementById('cancelEditBtn');
  const saveEditBtn = document.getElementById('saveEditBtn');
  const editProfileUpload = document.getElementById('editProfileUpload');
  const editProfilePreview = document.getElementById('editProfilePreview');
  const editProfileInitials = document.getElementById('editProfileInitials');

  // Open edit modal with user data
  function openEditModal(userData) {
    if (!editModal || !editModalContent) return;

    // Parse name
    const nameParts = userData.name.trim().split(' ');
    const firstName = nameParts[0] || '';
    const lastName = nameParts.length > 1 ? nameParts[nameParts.length - 1] : '';
    const middleName = nameParts.length > 2 ? nameParts.slice(1, -1).join(' ') : '';

    // Populate form fields
    document.getElementById('editFirstName').value = firstName;
    document.getElementById('editMiddleName').value = middleName;
    document.getElementById('editLastName').value = lastName;
    document.getElementById('editOccupation').value = userData.occupation || '';
    document.getElementById('editContactNumber').value = '0998 765 4321'; // Demo data
    document.getElementById('editDateOfBirth').value = '1989-02-16'; // Demo data
    document.getElementById('editEmail').value = `${firstName.toLowerCase()}@gmail.com`; // Demo data
    document.getElementById('editUsername').value = firstName.toLowerCase(); // Demo data

    // Set initials
    if (editProfileInitials) {
      editProfileInitials.textContent = userData.initials;
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

  // Prevent modal content click from closing
  if (editModalContent) {
    editModalContent.addEventListener('click', function(e) {
      e.stopPropagation();
    });
  }

  // Profile picture upload preview
  if (editProfileUpload && editProfilePreview && editProfileInitials) {
    editProfileUpload.addEventListener('change', function(e) {
      const file = e.target.files[0];
      if (file) {
        const reader = new FileReader();
        reader.onload = function(event) {
          editProfilePreview.src = event.target.result;
          editProfilePreview.classList.remove('hidden');
          editProfileInitials.classList.add('hidden');
        };
        reader.readAsDataURL(file);
      }
    });
  }

  // Save button handler
  if (saveEditBtn) {
    saveEditBtn.addEventListener('click', function() {
      // Add loading state
      const originalContent = saveEditBtn.innerHTML;
      saveEditBtn.innerHTML = '<i class="fi fi-rr-spinner animate-spin"></i> Saving...';
      saveEditBtn.disabled = true;

      // Simulate save (replace with actual AJAX call)
      setTimeout(() => {
        // Reset button
        saveEditBtn.innerHTML = originalContent;
        saveEditBtn.disabled = false;
        
        // Show success notification
        showNotification('Property owner updated successfully!', 'success');
        
        // Close modal
        closeEditModal();
      }, 1500);
    });
  }

  // Add input focus effects for edit modal
  const editInputs = editModal ? editModal.querySelectorAll('input[type="text"], input[type="email"], input[type="tel"], input[type="date"]') : [];
  editInputs.forEach(input => {
    input.addEventListener('focus', function() {
      this.parentElement.classList.add('ring-2', 'ring-orange-200');
    });
    
    input.addEventListener('blur', function() {
      this.parentElement.classList.remove('ring-2', 'ring-orange-200');
    });
  });

  // ESC key to close edit modal
  document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape' && editModal && !editModal.classList.contains('hidden')) {
      closeEditModal();
    }
  });

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

  // ===== DELETE MODAL FUNCTIONALITY =====
  const deleteModal = document.getElementById('deleteUserModal');
  const deleteModalContent = deleteModal ? deleteModal.querySelector('.modal-content') : null;
  const confirmDeleteBtn = document.getElementById('confirmDeleteBtn');
  const cancelDeleteBtn = document.getElementById('cancelDeleteBtn');
  const deleteUserNameSpan = document.getElementById('deleteUserName');
  let rowToDelete = null;

  // Open delete modal
  function openDeleteModal(userName, row) {
    if (!deleteModal || !deleteModalContent) return;

    rowToDelete = row;
    
    // Set user name
    if (deleteUserNameSpan) {
      deleteUserNameSpan.textContent = userName;
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
    }, 300);
  }

  // Cancel delete button
  if (cancelDeleteBtn) {
    cancelDeleteBtn.addEventListener('click', closeDeleteModal);
  }

  // Confirm delete button
  if (confirmDeleteBtn) {
    confirmDeleteBtn.addEventListener('click', function() {
      if (!rowToDelete) return;

      // Add loading state
      const originalContent = confirmDeleteBtn.innerHTML;
      confirmDeleteBtn.innerHTML = '<i class="fi fi-rr-spinner animate-spin"></i> Deleting...';
      confirmDeleteBtn.disabled = true;

      // Simulate deletion (replace with actual AJAX call)
      setTimeout(() => {
        // Reset button
        confirmDeleteBtn.innerHTML = originalContent;
        confirmDeleteBtn.disabled = false;
        
        // Close modal
        closeDeleteModal();

        // Add fade-out animation to row
        rowToDelete.style.opacity = '0';
        rowToDelete.style.transform = 'translateX(-20px)';
        rowToDelete.style.transition = 'all 0.3s ease';
        
        setTimeout(() => {
          rowToDelete.remove();
          showNotification('User deleted successfully!', 'success');
        }, 300);
      }, 1000);
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

  // Prevent modal content click from closing
  if (deleteModalContent) {
    deleteModalContent.addEventListener('click', function(e) {
      e.stopPropagation();
    });
  }

  // ESC key to close delete modal
  document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape' && deleteModal && !deleteModal.classList.contains('hidden')) {
      closeDeleteModal();
    }
  });

  // Make openDeleteModal globally accessible for delete buttons
  window.openDeleteModal = openDeleteModal;
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
