// Owner-specific JavaScript functions

// Accept Bid Modal functionality
if (typeof window.AcceptBidModal === 'undefined') {
    window.AcceptBidModal = {
        selectedBidId: null,
        projectId: null,

        open: function(bidId, companyName, proposedCost, projectId) {
            this.selectedBidId = bidId;
            this.projectId = projectId;

            const companyNameEl = document.getElementById('acceptBidCompanyName');
            const costEl = document.getElementById('acceptBidCost');
            const modal = document.getElementById('acceptBidModal');

            if (companyNameEl) companyNameEl.textContent = companyName;
            if (costEl) {
                costEl.textContent = proposedCost.toLocaleString('en-US', {
                    minimumFractionDigits: 2,
                    maximumFractionDigits: 2
                });
            }

            if (modal) {
                modal.style.display = 'block';
                const errorMsg = document.getElementById('acceptBidErrorMessage');
                const successMsg = document.getElementById('acceptBidSuccessMessage');
                if (errorMsg) errorMsg.style.display = 'none';
                if (successMsg) successMsg.style.display = 'none';
            }
        },

        close: function() {
            const modal = document.getElementById('acceptBidModal');
            if (modal) {
                modal.style.display = 'none';
            }
            this.selectedBidId = null;
            this.projectId = null;

            const errorMsg = document.getElementById('acceptBidErrorMessage');
            const successMsg = document.getElementById('acceptBidSuccessMessage');
            if (errorMsg) errorMsg.style.display = 'none';
            if (successMsg) successMsg.style.display = 'none';
        },

        confirm: async function() {
            if (!this.selectedBidId || !this.projectId) {
                return;
            }

            const confirmBtn = document.getElementById('confirmAcceptBtn');
            const errorMsg = document.getElementById('acceptBidErrorMessage');
            const successMsg = document.getElementById('acceptBidSuccessMessage');

            if (confirmBtn) {
                confirmBtn.disabled = true;
                confirmBtn.textContent = 'Processing...';
            }

            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
            if (!csrfToken) {
                if (errorMsg) {
                    errorMsg.textContent = 'CSRF token not found. Please refresh the page.';
                    errorMsg.style.display = 'block';
                }
                if (confirmBtn) {
                    confirmBtn.disabled = false;
                    confirmBtn.textContent = 'Yes, Select This Contractor';
                }
                return;
            }

            try {
                const response = await fetch(`/owner/projects/${this.projectId}/bids/${this.selectedBidId}/accept`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': csrfToken,
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                });

                const data = await response.json();

                if (data.success) {
                    if (successMsg) {
                        successMsg.textContent = data.message;
                        successMsg.style.display = 'block';
                    }
                    setTimeout(() => {
                        window.location.reload();
                    }, 1500);
                } else {
                    if (errorMsg) {
                        errorMsg.textContent = data.message || 'An error occurred while accepting the bid.';
                        errorMsg.style.display = 'block';
                    }
                    if (confirmBtn) {
                        confirmBtn.disabled = false;
                        confirmBtn.textContent = 'Yes, Select This Contractor';
                    }
                }
            } catch (error) {
                console.error('Error:', error);
                if (errorMsg) {
                    errorMsg.textContent = 'An error occurred while accepting the bid. Please try again.';
                    errorMsg.style.display = 'block';
                }
                if (confirmBtn) {
                    confirmBtn.disabled = false;
                    confirmBtn.textContent = 'Yes, Select This Contractor';
                }
            }
        }
    };

    // Close modal when clicking outside - register handler
    if (typeof window.modalClickHandlers === 'undefined') {
        window.modalClickHandlers = [];

        // Initialize the global click handler if not already set up
        if (!window.modalClickHandlerInitialized) {
            window.addEventListener('click', function(event) {
                if (window.modalClickHandlers && window.modalClickHandlers.length > 0) {
                    window.modalClickHandlers.forEach(function(handler) {
                        handler(event);
                    });
                }
            });
            window.modalClickHandlerInitialized = true;
        }
    }

    window.modalClickHandlers.push(function(event) {
        const acceptBidModal = document.getElementById('acceptBidModal');
        if (event.target === acceptBidModal) {
            window.AcceptBidModal.close();
        }
    });
}

// Approve Milestone Modal functionality
if (typeof window.ApproveMilestoneModal === 'undefined') {
    window.ApproveMilestoneModal = {
        milestoneId: null,

        open: function(milestoneId) {
            this.milestoneId = milestoneId;
            document.getElementById('approve_milestone_id').value = milestoneId;
            document.getElementById('approveMilestoneModal').style.display = 'flex';
            document.body.style.overflow = 'hidden';
            document.getElementById('approveMilestoneErrorMessage').style.display = 'none';
            document.getElementById('approveMilestoneSuccessMessage').style.display = 'none';
        },

        close: function() {
            document.getElementById('approveMilestoneModal').style.display = 'none';
            document.body.style.overflow = '';
            this.milestoneId = null;
            document.getElementById('approve_milestone_id').value = '';
            document.getElementById('approveMilestoneErrorMessage').style.display = 'none';
            document.getElementById('approveMilestoneSuccessMessage').style.display = 'none';
        },

        confirm: async function() {
            if (!this.milestoneId) {
                return;
            }

            const confirmBtn = document.getElementById('confirmApproveMilestoneBtn');
            confirmBtn.disabled = true;
            confirmBtn.textContent = 'Approving...';

            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
            if (!csrfToken) {
                document.getElementById('approveMilestoneErrorMessage').textContent = 'CSRF token not found. Please refresh the page.';
                document.getElementById('approveMilestoneErrorMessage').style.display = 'block';
                confirmBtn.disabled = false;
                confirmBtn.textContent = 'Approve Milestone';
                return;
            }

            try {
                const response = await fetch(`/owner/milestones/${this.milestoneId}/approve`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': csrfToken,
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                });

                const data = await response.json();

                if (data.success) {
                    document.getElementById('approveMilestoneSuccessMessage').textContent = data.message;
                    document.getElementById('approveMilestoneSuccessMessage').style.display = 'block';
                    setTimeout(() => {
                        window.location.reload();
                    }, 1500);
                } else {
                    document.getElementById('approveMilestoneErrorMessage').textContent = data.message || 'An error occurred while approving the milestone.';
                    document.getElementById('approveMilestoneErrorMessage').style.display = 'block';
                    confirmBtn.disabled = false;
                    confirmBtn.textContent = 'Approve Milestone';
                }
            } catch (error) {
                console.error('Error:', error);
                document.getElementById('approveMilestoneErrorMessage').textContent = 'An error occurred while approving the milestone. Please try again.';
                document.getElementById('approveMilestoneErrorMessage').style.display = 'block';
                confirmBtn.disabled = false;
                confirmBtn.textContent = 'Approve Milestone';
            }
        }
    };

    // Reject Milestone Modal functionality
    window.RejectMilestoneModal = {
        milestoneId: null,

        open: function(milestoneId) {
            this.milestoneId = milestoneId;
            document.getElementById('reject_milestone_id').value = milestoneId;
            document.getElementById('rejection_reason').value = '';
            document.getElementById('rejectMilestoneModal').style.display = 'flex';
            document.body.style.overflow = 'hidden';
            document.getElementById('rejectMilestoneErrorMessage').style.display = 'none';
            document.getElementById('rejectMilestoneSuccessMessage').style.display = 'none';
        },

        close: function() {
            document.getElementById('rejectMilestoneModal').style.display = 'none';
            document.body.style.overflow = '';
            this.milestoneId = null;
            document.getElementById('reject_milestone_id').value = '';
            document.getElementById('rejection_reason').value = '';
            document.getElementById('rejectMilestoneErrorMessage').style.display = 'none';
            document.getElementById('rejectMilestoneSuccessMessage').style.display = 'none';
        },

        confirm: async function() {
            if (!this.milestoneId) {
                return;
            }

            const reason = document.getElementById('rejection_reason').value.trim();
            if (!reason) {
                document.getElementById('rejectMilestoneErrorMessage').textContent = 'Please provide a reason for rejection.';
                document.getElementById('rejectMilestoneErrorMessage').style.display = 'block';
                return;
            }

            const confirmBtn = document.getElementById('confirmRejectMilestoneBtn');
            confirmBtn.disabled = true;
            confirmBtn.textContent = 'Rejecting...';

            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
            if (!csrfToken) {
                document.getElementById('rejectMilestoneErrorMessage').textContent = 'CSRF token not found. Please refresh the page.';
                document.getElementById('rejectMilestoneErrorMessage').style.display = 'block';
                confirmBtn.disabled = false;
                confirmBtn.textContent = 'Reject Milestone';
                return;
            }

            try {
                const response = await fetch(`/owner/milestones/${this.milestoneId}/reject`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': csrfToken,
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    body: JSON.stringify({
                        rejection_reason: reason
                    })
                });

                const data = await response.json();

                if (data.success) {
                    document.getElementById('rejectMilestoneSuccessMessage').textContent = data.message;
                    document.getElementById('rejectMilestoneSuccessMessage').style.display = 'block';
                    setTimeout(() => {
                        window.location.reload();
                    }, 1500);
                } else {
                    document.getElementById('rejectMilestoneErrorMessage').textContent = data.message || 'An error occurred while rejecting the milestone.';
                    document.getElementById('rejectMilestoneErrorMessage').style.display = 'block';
                    confirmBtn.disabled = false;
                    confirmBtn.textContent = 'Reject Milestone';
                }
            } catch (error) {
                console.error('Error:', error);
                document.getElementById('rejectMilestoneErrorMessage').textContent = 'An error occurred while rejecting the milestone. Please try again.';
                document.getElementById('rejectMilestoneErrorMessage').style.display = 'block';
                confirmBtn.disabled = false;
                confirmBtn.textContent = 'Reject Milestone';
            }
        }
    };

    // Global functions for milestone modals
    function openApproveMilestoneModal(milestoneId) {
        if (window.ApproveMilestoneModal) {
            window.ApproveMilestoneModal.open(milestoneId);
        }
    }

    function closeApproveMilestoneModal() {
        if (window.ApproveMilestoneModal) {
            window.ApproveMilestoneModal.close();
        }
    }

    function confirmApproveMilestone() {
        if (window.ApproveMilestoneModal) {
            window.ApproveMilestoneModal.confirm();
        }
    }

    function openRejectMilestoneModal(milestoneId) {
        if (window.RejectMilestoneModal) {
            window.RejectMilestoneModal.open(milestoneId);
        }
    }

    function closeRejectMilestoneModal() {
        if (window.RejectMilestoneModal) {
            window.RejectMilestoneModal.close();
        }
    }

    function confirmRejectMilestone() {
        if (window.RejectMilestoneModal) {
            window.RejectMilestoneModal.confirm();
        }
    }

    // Register modal click handlers
    if (typeof window.modalClickHandlers === 'undefined') {
        window.modalClickHandlers = [];
    }
    window.modalClickHandlers.push(function(event) {
        const approveMilestoneModal = document.getElementById('approveMilestoneModal');
        const rejectMilestoneModal = document.getElementById('rejectMilestoneModal');
        if (event.target === approveMilestoneModal) {
            closeApproveMilestoneModal();
        }
        if (event.target === rejectMilestoneModal) {
            closeRejectMilestoneModal();
        }
    });
}

// Dashboard functions
function deleteProject(projectId) {
    if (confirm('Are you sure you want to delete this project?')) {
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
        if (!csrfToken) {
            alert('CSRF token not found. Please refresh the page.');
            return;
        }

        fetch(`/owner/projects/${projectId}`, {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': csrfToken,
                'Content-Type': 'application/json',
                'Accept': 'application/json'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert(data.message);
                window.location.reload();
            } else {
                alert('Error: ' + data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('An error occurred while deleting the project.');
        });
    }
}

function viewContractorProfile(contractorId) {
    // For now, just show an alert with contractor info
    // You can implement a modal or redirect to a profile page later
    alert('Contractor profile view - ID: ' + contractorId + '\nThis feature can be implemented later.');
}

// ============================================================================
// PROJECT POST FILE UPLOAD HANDLING
// ============================================================================

function handleFileSelection(input, containerId) {
    if (input.files && input.files.length > 0) {
        const file = input.files[0];
        const container = document.getElementById(containerId);
        const fileGroup = input.closest('.file-input-group');
        const removeBtn = fileGroup.querySelector('.remove-file-btn');

        input.classList.add('has-file');

        let fileNameDisplay = fileGroup.querySelector('.file-name-display');
        if (!fileNameDisplay) {
            fileNameDisplay = document.createElement('div');
            fileNameDisplay.className = 'file-name-display visible';
            fileGroup.insertBefore(fileNameDisplay, removeBtn);
        }
        fileNameDisplay.textContent = '📄 ' + file.name;
        fileNameDisplay.classList.add('visible');

        if (removeBtn) removeBtn.style.display = 'inline-block';

        if (!input.hasAttribute('required')) {
            const addMoreBtn = container.parentElement.querySelector('.add-more-files-btn');
            if (addMoreBtn) addMoreBtn.classList.add('visible');
        }
    }
}

function removeFileInput(btn, containerId) {
    const fileGroup = btn.closest('.file-input-group');
    const input = fileGroup.querySelector('.evidence-file-input');
    const fileNameDisplay = fileGroup.querySelector('.file-name-display');

    input.value = '';
    input.classList.remove('has-file');
    if (fileNameDisplay) {
        fileNameDisplay.remove();
    }
    btn.style.display = 'none';

    const container = document.getElementById(containerId);
    const fileGroups = container.querySelectorAll('.file-input-group');
    const hasFiles = Array.from(fileGroups).some(group => {
        const fileInput = group.querySelector('.evidence-file-input');
        return fileInput && fileInput.files && fileInput.files.length > 0;
    });

    if (!hasFiles) {
        const addMoreBtn = container.parentElement.querySelector('.add-more-files-btn');
        if (addMoreBtn) addMoreBtn.classList.remove('visible');
    }
}

function addMoreFiles(containerId, fieldName) {
    const container = document.getElementById(containerId);
    const fileGroups = container.querySelectorAll('.file-input-group');

    if (fileGroups.length >= 10) {
        alert('Maximum of 10 files allowed');
        return;
    }

    const existingInput = container.querySelector('.evidence-file-input');
    const acceptAttr = existingInput ? existingInput.getAttribute('accept') : '';
    const isMultiple = fieldName === 'others';

    const newFileGroup = document.createElement('div');
    newFileGroup.className = 'file-input-group';

    const newInput = document.createElement('input');
    newInput.type = 'file';
    newInput.className = 'evidence-file-input';
    newInput.accept = acceptAttr;
    if (isMultiple) {
        newInput.name = 'others[]';
    } else {
        newInput.name = fieldName;
    }
    newInput.addEventListener('change', function() {
        handleFileSelection(this, containerId);
    });

    const removeBtn = document.createElement('button');
    removeBtn.type = 'button';
    removeBtn.className = 'remove-file-btn';
    removeBtn.textContent = 'Remove';
    removeBtn.onclick = function() {
        removeFileInput(this, containerId);
        updateRemoveButtons(containerId);
    };

    newFileGroup.appendChild(newInput);
    newFileGroup.appendChild(removeBtn);
    container.appendChild(newFileGroup);

    updateRemoveButtons(containerId);
}

function updateRemoveButtons(containerId) {
    const container = document.getElementById(containerId);
    const fileGroups = container.querySelectorAll('.file-input-group');

    fileGroups.forEach((group, index) => {
        const removeBtn = group.querySelector('.remove-file-btn');
        const fileInput = group.querySelector('.evidence-file-input');

        if (removeBtn && fileInput) {
            const hasFile = fileInput.files && fileInput.files.length > 0;
            const shouldShow = fileGroups.length > 1 || hasFile;
            removeBtn.style.display = shouldShow ? 'inline-block' : 'none';
        }
    });
}

// ============================================================================
// PROJECT POST FORM INITIALIZATION
// ============================================================================

document.addEventListener('DOMContentLoaded', function() {
    // Initialize file inputs for project post form
    const fileInputs = document.querySelectorAll('.evidence-file-input');
    fileInputs.forEach(input => {
        const containerId = input.closest('[id$="-upload-container"]')?.id;
        if (containerId) {
            input.addEventListener('change', function() {
                handleFileSelection(this, containerId);
                updateRemoveButtons(containerId);
            });
        }
    });

    // Form validation for project post
    const projectForm = document.getElementById('projectForm');
    if (projectForm) {
        projectForm.addEventListener('submit', async function(e) {
            e.preventDefault();

            // Hide previous messages
            const successDiv = document.getElementById('projectFormSuccess');
            const errorDiv = document.getElementById('projectFormError');
            if (successDiv) successDiv.style.display = 'none';
            if (errorDiv) errorDiv.style.display = 'none';

            // Compose hidden project_location from barangay (PSGC select) + street + fixed city/province
            const barangayEl = document.getElementById('project_barangay');
            const streetEl = document.getElementById('street_address');
            const hiddenLocation = document.getElementById('project_location_hidden');
            if (hiddenLocation) {
                const barangayVal = barangayEl && barangayEl.selectedIndex > -1
                    ? (barangayEl.options[barangayEl.selectedIndex].getAttribute('data-name') || barangayEl.value).trim()
                    : '';
                const streetVal = streetEl ? streetEl.value.trim() : '';
                const city = 'Zamboanga City';
                const province = 'Zamboanga del Sur';
                let composed = '';
                if (barangayVal) composed += barangayVal + ', ';
                if (streetVal) composed += streetVal + ', ';
                composed += city + ', ' + province;
                hiddenLocation.value = composed;
            }
            const budgetMin = parseFloat(document.querySelector('input[name="budget_range_min"]').value);
            const budgetMax = parseFloat(document.querySelector('input[name="budget_range_max"]').value);

            if (budgetMax < budgetMin) {
                if (errorDiv) {
                    errorDiv.textContent = 'Maximum budget must be greater than or equal to minimum budget.';
                    errorDiv.style.display = 'block';
                    errorDiv.scrollIntoView({ behavior: 'smooth', block: 'center' });
                } else {
                    alert('Maximum budget must be greater than or equal to minimum budget.');
                }
                return false;
            }

            // Get CSRF token
            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') ||
                             document.querySelector('input[name="_token"]')?.value;

            // Disable submit button
            const submitBtn = projectForm.querySelector('button[type="submit"]');
            if (submitBtn) {
                submitBtn.disabled = true;
                submitBtn.textContent = 'Submitting...';
            }

            // Submit form via AJAX
            const formData = new FormData(projectForm);

            try {
                const response = await fetch('/owner/projects', {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': csrfToken,
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    body: formData
                });

                const data = await response.json();

                if (data.success) {
                    if (successDiv) {
                        successDiv.textContent = data.message || 'Project posted successfully!';
                        successDiv.style.display = 'block';
                        successDiv.scrollIntoView({ behavior: 'smooth', block: 'center' });
                    }

                    // Reset form after 2 seconds
                    setTimeout(() => {
                        window.location.href = data.redirect || '/dashboard';
                    }, 2000);
                } else {
                    if (errorDiv) {
                        let errorMessage = data.message || 'An error occurred while posting the project.';
                        if (data.errors && typeof data.errors === 'object') {
                            const errorList = Object.values(data.errors).flat();
                            errorMessage = errorList.join('<br>');
                        }
                        errorDiv.innerHTML = errorMessage;
                        errorDiv.style.display = 'block';
                        errorDiv.scrollIntoView({ behavior: 'smooth', block: 'center' });
                    }

                    if (submitBtn) {
                        submitBtn.disabled = false;
                        submitBtn.textContent = 'Post Project';
                    }
                }
            } catch (error) {
                if (errorDiv) {
                    errorDiv.textContent = 'Network error. Please try again.';
                    errorDiv.style.display = 'block';
                    errorDiv.scrollIntoView({ behavior: 'smooth', block: 'center' });
                }

                if (submitBtn) {
                    submitBtn.disabled = false;
                    submitBtn.textContent = 'Post Project';
                }
            }
        });
    }

    // Contractor Type "Others" handling
    const typeSelect = document.getElementById('project_type_id');
    const otherContainer = document.getElementById('other_contractor_type_container');
    const otherInput = document.getElementById('if_others_ctype');

    if (typeSelect) {
        function toggleOther() {
            const selected = typeSelect.options[typeSelect.selectedIndex];
            const name = (selected && selected.getAttribute('data-name')) ? selected.getAttribute('data-name').toLowerCase() : '';
            if (name === 'others') {
                otherContainer.style.display = 'block';
                if (otherInput) otherInput.setAttribute('required', 'required');
            } else {
                otherContainer.style.display = 'none';
                if (otherInput) {
                    otherInput.removeAttribute('required');
                }
            }
        }

        typeSelect.addEventListener('change', toggleOther);
        toggleOther();
    }

    // PSGC: populate barangays for Zamboanga City
    const barangaySelect = document.getElementById('project_barangay');
    if (barangaySelect) {
        const oldBarangay = barangaySelect.getAttribute('data-old-value');

        fetch('/api/psgc/provinces')
            .then(r => {
                if (!r.ok) throw new Error('Failed to load provinces: ' + r.status + ' ' + r.statusText);
                return r.json();
            })
            .then(provinces => {
                let prov = provinces.find(p => p.name && p.name.toLowerCase().includes('zamboanga del sur'));
                if (!prov) {
                    prov = provinces.find(p => {
                        const n = (p.name || '').toLowerCase();
                        return n.includes('zamboanga') && n.includes('sur');
                    });
                }
                if (!prov) {
                    prov = provinces.find(p => (p.name || '').toLowerCase().includes('zamboanga'));
                }
                if (!prov) throw new Error('Province not found in PSGC response');
                const provInput = document.getElementById('project_province_code_hidden');
                if (provInput) provInput.value = prov.code;
                return fetch('/api/psgc/provinces/' + prov.code + '/cities');
            })
            .then(r => {
                if (!r.ok) throw new Error('Failed to load cities: ' + r.status + ' ' + r.statusText);
                return r.json();
            })
            .then(cities => {
                let city = cities.find(c => c.name && c.name.toLowerCase().includes('zamboanga city'));
                if (!city) {
                    city = cities.find(c => (c.name || '').toLowerCase().includes('zamboanga'));
                }
                if (!city) throw new Error('City not found in PSGC response');
                const cityInput = document.getElementById('project_city_code_hidden');
                if (cityInput) cityInput.value = city.code;
                return fetch('/api/psgc/cities/' + city.code + '/barangays');
            })
            .then(r => r.json())
            .then(barangays => {
                barangaySelect.innerHTML = '<option value="">Select Barangay</option>';
                barangays.forEach(function(b) {
                    const option = document.createElement('option');
                    option.value = b.code;
                    option.setAttribute('data-name', b.name);
                    option.textContent = b.name;
                    if (oldBarangay && oldBarangay === b.name) {
                        option.selected = true;
                    }
                    barangaySelect.appendChild(option);
                });
                barangaySelect.disabled = false;
            })
            .catch(err => {
                const msg = err && err.message ? err.message : 'Unknown error';
                barangaySelect.innerHTML = '<option value="">Error loading barangays: ' + msg + '</option>';
                barangaySelect.disabled = false;
            });
    }
});
