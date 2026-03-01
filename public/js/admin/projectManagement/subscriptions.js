// Subscriptions Page Interactivity

document.addEventListener('DOMContentLoaded', function () {
  // Tab Switching
  const tabActive = document.getElementById('tabActiveSubscriptions');
  const tabExpired = document.getElementById('tabExpiredSubscriptions');
  const tabCancelled = document.getElementById('tabCancelledSubscriptions');
  const activeTable = document.getElementById('activeSubscriptionsTable');
  const expiredTable = document.getElementById('expiredSubscriptionsTable');
  const cancelledTable = document.getElementById('cancelledSubscriptionsTable');

  const tabs = [
    { btn: tabActive, table: activeTable },
    { btn: tabExpired, table: expiredTable },
    { btn: tabCancelled, table: cancelledTable }
  ];

  tabs.forEach(tab => {
    if (tab.btn && tab.table) {
      tab.btn.addEventListener('click', function () {
        tabs.forEach(t => {
          if (t.btn) {
            t.btn.classList.remove('active', 'border-orange-500', 'text-orange-600');
            t.btn.classList.add('border-transparent', 'text-gray-600');
          }
          if (t.table) t.table.classList.add('hidden');
        });
        tab.btn.classList.add('active', 'border-orange-500', 'text-orange-600');
        tab.btn.classList.remove('border-transparent', 'text-gray-600');
        tab.table.classList.remove('hidden');
      });
    }
  });

  // ===== Filter Logic =====
  const filterSearch = document.getElementById('filterSearch');
  const filterPlanType = document.getElementById('filterPlanType');
  const resetFilterBtn = document.getElementById('resetFilterBtn');

  function applyFilters() {
    const searchQuery = filterSearch?.value.toLowerCase().trim() || '';
    const planFilter = filterPlanType?.value.toLowerCase().trim() || '';

    [activeTable, expiredTable, cancelledTable].forEach(table => {
      if (!table) return;
      const tbody = table.querySelector('tbody');
      if (!tbody) return;

      const rows = Array.from(tbody.querySelectorAll('tr'));
      let visibleCount = 0;

      rows.forEach(row => {
        // Skip empty state rows
        if (row.querySelector('td[colspan]')) {
          row.classList.add('hidden');
          return;
        }

        const id = row.cells[0]?.textContent.toLowerCase().trim() || '';
        const name = row.cells[1]?.textContent.toLowerCase().trim() || '';
        const plan = row.cells[2]?.textContent.toLowerCase().trim() || '';

        const matchesSearch = id.includes(searchQuery) || name.includes(searchQuery);
        const matchesPlan = planFilter === '' || plan.includes(planFilter);

        if (matchesSearch && matchesPlan) {
          row.classList.remove('hidden');
          visibleCount++;
        } else {
          row.classList.add('hidden');
        }
      });

      // Show/Hide empty state row if exists
      const emptyRow = tbody.querySelector('td[colspan]')?.parentElement;
      if (emptyRow && visibleCount === 0) {
        emptyRow.classList.remove('hidden');
        const emptyText = emptyRow.querySelector('p, span:not(.text-xs)') || emptyRow.querySelector('td');
        if (emptyText && (searchQuery || planFilter)) {
          // If we are filtering, change the text to show no results found
          if (!emptyRow.dataset.originalText) emptyRow.dataset.originalText = emptyText.textContent;
          emptyText.textContent = 'No subscriptions match your filters';
        } else if (emptyRow.dataset.originalText) {
          emptyText.textContent = emptyRow.dataset.originalText;
        }
      }
    });
  }

  if (filterSearch) filterSearch.addEventListener('input', applyFilters);
  if (filterPlanType) filterPlanType.addEventListener('change', applyFilters);
  if (resetFilterBtn) {
    resetFilterBtn.addEventListener('click', () => {
      if (filterSearch) filterSearch.value = '';
      if (filterPlanType) filterPlanType.value = '';
      applyFilters();
      if (typeof showToast === 'function') showToast('Filters reset', 'info');
    });
  }

  // View buttons removed from table (cleanup listeners)

  // ===== Deactivate Subscription Modal Logic =====
  const deactivateModal = document.getElementById('deactivateSubscriptionModal');
  const deactivateForm = document.getElementById('deactivateSubscriptionForm');
  const closeDeactivateBtn = document.getElementById('closeDeactivateSubscriptionBtn');
  const cancelDeactivateBtn = document.getElementById('cancelDeactivateSubscriptionBtn');
  const deactivateNameSpan = document.getElementById('deactivateContractorName');
  const deactivateReason = document.getElementById('deactivateReason');
  const deactivateReasonError = document.getElementById('deactivateReasonError');
  let rowToDeactivate = null;

  function openDeactivateModal(contractor, rowRef) {
    deactivateNameSpan.textContent = contractor;
    rowToDeactivate = rowRef;
    deactivateModal.classList.remove('hidden');
    deactivateModal.classList.add('flex');
  }
  function closeDeactivateModal() {
    const panel = deactivateModal.querySelector('.deactivate-subscription-panel');
    panel.style.animation = 'modalSlideUp .3s ease-out reverse';
    setTimeout(() => {
      deactivateModal.classList.add('hidden');
      deactivateModal.classList.remove('flex');
      panel.style.animation = '';
      deactivateForm.reset();
      deactivateReasonError.classList.add('hidden');
      rowToDeactivate = null;
    }, 200);
  }
  if (closeDeactivateBtn) closeDeactivateBtn.addEventListener('click', closeDeactivateModal);
  if (cancelDeactivateBtn) cancelDeactivateBtn.addEventListener('click', closeDeactivateModal);
  if (deactivateModal) deactivateModal.addEventListener('click', e => { if (e.target === deactivateModal) closeDeactivateModal(); });

  document.querySelectorAll('.deactivate-subscription-btn').forEach(btn => {
    btn.addEventListener('click', e => {
      e.stopPropagation();
      const row = btn.closest('tr');
      const contractor = btn.dataset.name || row.querySelector('td:nth-child(2) span:last-child')?.textContent.trim() || 'Contractor';
      openDeactivateModal(contractor, row);
    });
  });

  // ===== Reactivate Subscription Modal Logic =====
  const reactivateModal = document.getElementById('reactivateSubscriptionModal');
  const closeReactivateBtn = document.getElementById('closeReactivateSubscriptionBtn');
  const cancelReactivateBtn = document.getElementById('cancelReactivateSubscriptionBtn');
  const confirmReactivateBtn = document.getElementById('confirmReactivateSubscriptionBtn');
  const reactivateNameSpan = document.getElementById('reactivateContractorName');
  let rowToReactivate = null;

  function openReactivateModal(contractor, rowRef) {
    reactivateNameSpan.textContent = contractor;
    rowToReactivate = rowRef;
    reactivateModal.classList.remove('hidden');
    reactivateModal.classList.add('flex');
    const panel = reactivateModal.querySelector('.reactivate-subscription-panel');
    panel.style.animation = 'modalSlideUp .3s ease-out forwards';
  }

  function closeReactivateModal() {
    const panel = reactivateModal.querySelector('.reactivate-subscription-panel');
    panel.style.animation = 'modalSlideUp .3s ease-out reverse';
    setTimeout(() => {
      reactivateModal.classList.add('hidden');
      reactivateModal.classList.remove('flex');
      panel.style.animation = '';
      rowToReactivate = null;
    }, 200);
  }

  if (closeReactivateBtn) closeReactivateBtn.addEventListener('click', closeReactivateModal);
  if (cancelReactivateBtn) cancelReactivateBtn.addEventListener('click', closeReactivateModal);
  if (reactivateModal) reactivateModal.addEventListener('click', e => { if (e.target === reactivateModal) closeReactivateModal(); });

  if (confirmReactivateBtn) {
    confirmReactivateBtn.addEventListener('click', () => {
      const id = rowToReactivate?.querySelector('.reactivate-subscription-btn')?.dataset.id;
      if (!id) {
        showToast('Could not find subscription ID', 'error');
        return;
      }

      const originalContent = confirmReactivateBtn.innerHTML;
      confirmReactivateBtn.disabled = true;
      confirmReactivateBtn.innerHTML = '<i class="fi fi-rr-spinner animate-spin text-sm"></i> Reactivating...';

      fetch(`/admin/project-management/subscriptions/${id}/reactivate`, {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '',
          'Accept': 'application/json'
        }
      })
        .then(async response => {
          const data = await response.json();
          if (!response.ok) throw new Error(data.message || 'Failed to reactivate');
          return data;
        })
        .then(result => {
          showToast('Subscription reactivated successfully!', 'success');
          closeReactivateModal();
          setTimeout(() => window.location.reload(), 800);
        })
        .catch(error => {
          showToast(error.message, 'error');
        })
        .finally(() => {
          confirmReactivateBtn.disabled = false;
          confirmReactivateBtn.innerHTML = originalContent;
        });
    });
  }

  document.addEventListener('click', e => {
    const btn = e.target.closest('.reactivate-subscription-btn');
    if (!btn) return;
    e.stopPropagation();
    const row = btn.closest('tr');
    const name = btn.dataset.name || row.querySelector('td:nth-child(2) span:last-child')?.textContent.trim() || 'Contractor';
    openReactivateModal(name, row);
  });

  if (deactivateForm) {
    // Clear validation styling on input
    if (deactivateReason) {
      deactivateReason.addEventListener('input', () => {
        if (deactivateReason.value.trim()) {
          deactivateReasonError.classList.add('hidden');
          deactivateReason.classList.remove('border-red-500', 'ring-2', 'ring-red-300');
        }
      });
    }

    deactivateForm.addEventListener('submit', e => {
      e.preventDefault();
      deactivateReasonError.classList.add('hidden');
      deactivateReason.classList.remove('border-red-500', 'ring-2', 'ring-red-300');

      const reason = deactivateReason.value.trim();
      if (!reason) {
        deactivateReasonError.classList.remove('hidden');
        deactivateReason.classList.add('border-red-500', 'ring-2', 'ring-red-300');
        deactivateReason.focus();
        showToast('Reason required', 'error');
        return;
      }

      const submitBtn = deactivateForm.querySelector('button[type="submit"]');
      const originalText = submitBtn.innerHTML;
      const paymentId = rowToDeactivate ? rowToDeactivate.querySelector('.deactivate-subscription-btn')?.dataset.id || rowToDeactivate.querySelector('.view-subscription-btn')?.dataset.id : null;

      if (!paymentId) {
        showToast('Could not find subscription ID', 'error');
        return;
      }

      submitBtn.disabled = true;
      submitBtn.innerHTML = '<i class="fi fi-rr-spinner animate-spin"></i> Deactivating...';

      fetch(`/admin/project-management/subscriptions/${paymentId}/deactivate`, {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '',
          'Accept': 'application/json'
        },
        body: JSON.stringify({ reason: reason })
      })
        .then(async response => {
          const data = await response.json();
          if (!response.ok) throw new Error(data.message || 'Failed to deactivate');
          return data;
        })
        .then(result => {
          showToast('Subscription deactivated successfully!', 'success');
          closeDeactivateModal();
          setTimeout(() => window.location.reload(), 800);
        })
        .catch(error => {
          showToast(error.message, 'error');
        })
        .finally(() => {
          submitBtn.disabled = false;
          submitBtn.innerHTML = originalText;
        });
    });
  }


  // ===== View Subscription Details Modal Logic =====
  const viewModal = document.getElementById('viewSubscriptionModal');
  const closeViewBtn = document.getElementById('closeViewSubscriptionBtn');
  const cancelViewBtn = document.getElementById('cancelViewSubscriptionBtn');

  function openViewModal(btn) {
    document.getElementById('viewSubId').textContent = String(btn.dataset.id).padStart(4, '0');
    document.getElementById('viewSubName').textContent = btn.dataset.user || '—';

    // Show project field only for boost/owner
    const projectContainer = document.getElementById('viewSubProjectContainer');
    const projectEl = document.getElementById('viewSubProject');
    if (btn.dataset.project && btn.dataset.project.trim() !== '') {
      projectContainer.classList.remove('hidden');
      projectEl.textContent = btn.dataset.project;
    } else {
      projectContainer.classList.add('hidden');
    }

    document.getElementById('viewSubPlan').textContent = btn.dataset.plan || '—';
    document.getElementById('viewSubPlanKey').textContent = btn.dataset.planKey || '—';
    document.getElementById('viewSubType').textContent = btn.dataset.type || '—';
    const billing = btn.dataset.billing || '—';
    document.getElementById('viewSubBilling').textContent = billing !== 'N/A' ? billing.charAt(0).toUpperCase() + billing.slice(1) : '—';
    document.getElementById('viewSubAmount').textContent = '₱' + (btn.dataset.amount || '0.00');
    const txn = btn.dataset.txn || '—';
    const txnEl = document.getElementById('viewSubTxn');
    txnEl.textContent = txn;
    txnEl.title = txn;
    document.getElementById('viewSubDate').textContent = btn.dataset.date || '—';
    document.getElementById('viewSubExpiry').textContent = btn.dataset.expiry || '—';

    // Show duration for one-time billing
    const durationContainer = document.getElementById('viewSubDurationContainer');
    const durationEl = document.getElementById('viewSubDuration');
    if (billing.toLowerCase() === 'one-time' && btn.dataset.duration) {
      durationContainer.classList.remove('hidden');
      durationEl.textContent = btn.dataset.duration + ' days';
    } else {
      durationContainer.classList.add('hidden');
    }

    // Dynamic status banner
    const statusBanner = document.getElementById('viewSubStatusBanner');
    const statusIcon = document.getElementById('viewSubStatusIcon');
    const statusText = document.getElementById('viewSubStatusText');
    if (btn.dataset.status === 'active') {
      statusBanner.className = 'bg-emerald-50 border border-emerald-200 rounded-xl p-4 flex items-start gap-3';
      statusIcon.className = 'fi fi-rr-badge-check text-emerald-500 text-xl mt-0.5';
      statusText.className = 'text-xs md:text-sm text-emerald-700';
      statusText.innerHTML = 'This subscription is currently <strong>active</strong>. Benefits are being applied to the user\'s account.';
    } else {
      statusBanner.className = 'bg-red-50 border border-red-200 rounded-xl p-4 flex items-start gap-3';
      statusIcon.className = 'fi fi-rr-calendar-clock text-red-500 text-xl mt-0.5';
      statusText.className = 'text-xs md:text-sm text-red-700';
      statusText.innerHTML = 'This subscription has <strong>expired</strong>. The user would need to purchase a new subscription or boost to restore benefits.';
    }

    viewModal.classList.remove('hidden');
    viewModal.classList.add('flex');
    const panel = viewModal.querySelector('.view-subscription-panel');
    panel.style.animation = 'modalSlideUp .3s ease-out forwards';
  }

  function closeViewModal() {
    const panel = viewModal.querySelector('.view-subscription-panel');
    panel.style.animation = 'modalSlideUp .3s ease-out reverse';
    setTimeout(() => {
      viewModal.classList.add('hidden');
      viewModal.classList.remove('flex');
      panel.style.animation = '';
    }, 200);
  }

  if (closeViewBtn) closeViewBtn.addEventListener('click', closeViewModal);
  if (cancelViewBtn) cancelViewBtn.addEventListener('click', closeViewModal);
  if (viewModal) viewModal.addEventListener('click', e => { if (e.target === viewModal) closeViewModal(); });

  document.querySelectorAll('.view-subscription-btn').forEach(btn => {
    btn.addEventListener('click', e => {
      e.stopPropagation();
      openViewModal(btn);
    });
  });

  // Filter logic consolidated at the top

  // Statistics Cards Click Handlers
  const statsCards = document.querySelectorAll('.stats-card');
  statsCards.forEach(card => {
    card.addEventListener('click', function () {
      const title = this.querySelector('p').textContent;
      const value = this.querySelector('.text-4xl').textContent;
      showToast(`${title}: ${value}`, 'info');
    });
  });

  // Animate numbers on page load
  animateNumbers();

  // Add Subscription Button - Open Modal
  const addSubscriptionBtn = document.querySelector('.add-subscription-btn');
  const addSubscriptionModal = document.getElementById('addSubscriptionModal');
  const closeAddSubscriptionBtn = document.getElementById('closeAddSubscriptionBtn');
  const cancelAddSubscriptionBtn = document.getElementById('cancelAddSubscriptionBtn');
  const addSubscriptionForm = document.getElementById('addSubscriptionForm');
  const addBenefitBtn = document.getElementById('addBenefitBtn');
  const benefitsContainer = document.getElementById('benefitsContainer');

  const billingCycle = document.getElementById('billingCycle');
  const durationDaysContainer = document.getElementById('durationDaysContainer');
  const durationDays = document.getElementById('durationDays');

  if (billingCycle && durationDaysContainer) {
    billingCycle.addEventListener('change', () => {
      if (billingCycle.value === 'one-time') {
        durationDaysContainer.classList.remove('hidden');
        durationDays.setAttribute('required', 'true');
      } else {
        durationDaysContainer.classList.add('hidden');
        durationDays.removeAttribute('required');
        durationDays.value = '';
      }
    });
  }

  if (addSubscriptionBtn) {
    addSubscriptionBtn.addEventListener('click', function () {
      addSubscriptionModal.classList.remove('hidden');
      addSubscriptionModal.classList.add('flex');
    });
  }

  // Close modal function
  function closeAddSubscriptionModal() {
    const panel = addSubscriptionModal.querySelector('.add-subscription-panel');
    panel.style.animation = 'modalSlideUp 0.3s ease-out reverse';

    setTimeout(() => {
      addSubscriptionModal.classList.add('hidden');
      addSubscriptionModal.classList.remove('flex');
      panel.style.animation = '';
      addSubscriptionForm.reset();
      // Reset benefits to one empty field
      benefitsContainer.innerHTML = `
        <div class="flex items-center gap-2 benefit-item">
          <input 
            type="checkbox" 
            class="w-4 h-4 text-orange-600 border-gray-300 rounded focus:ring-orange-500 benefit-checkbox" 
            checked
          >
          <input 
            type="text" 
            class="flex-1 px-3 py-2 border-2 border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-400 focus:border-orange-400 transition text-sm benefit-input" 
            placeholder="Enter benefit"
          >
          <button type="button" class="text-red-500 hover:text-red-700 transition p-1 remove-benefit-btn hidden">
            <i class="fi fi-rr-cross-small text-xl"></i>
          </button>
        </div>
      `;
      // Clear error messages
      document.querySelectorAll('[id$="Error"]').forEach(el => el.classList.add('hidden'));
    }, 200);
  }

  // Close modal events
  if (closeAddSubscriptionBtn) {
    closeAddSubscriptionBtn.addEventListener('click', closeAddSubscriptionModal);
  }

  if (cancelAddSubscriptionBtn) {
    cancelAddSubscriptionBtn.addEventListener('click', closeAddSubscriptionModal);
  }

  // Close on backdrop click
  if (addSubscriptionModal) {
    addSubscriptionModal.addEventListener('click', (e) => {
      if (e.target === addSubscriptionModal) {
        closeAddSubscriptionModal();
      }
    });
  }

  // Add benefit field
  if (addBenefitBtn) {
    addBenefitBtn.addEventListener('click', () => {
      const newBenefit = document.createElement('div');
      newBenefit.className = 'flex items-center gap-2 benefit-item';
      newBenefit.innerHTML = `
        <input 
          type="checkbox" 
          class="w-4 h-4 text-orange-600 border-gray-300 rounded focus:ring-orange-500 benefit-checkbox" 
          checked
        >
        <input 
          type="text" 
          class="flex-1 px-3 py-2 border-2 border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-400 focus:border-orange-400 transition text-sm benefit-input" 
          placeholder="Enter benefit"
        >
        <button type="button" class="text-red-500 hover:text-red-700 transition p-1 remove-benefit-btn">
          <i class="fi fi-rr-cross-small text-xl"></i>
        </button>
      `;
      benefitsContainer.appendChild(newBenefit);

      // Add remove event to the new button
      const removeBtn = newBenefit.querySelector('.remove-benefit-btn');
      removeBtn.addEventListener('click', () => {
        if (benefitsContainer.children.length > 1) {
          newBenefit.remove();
        } else {
          showToast('At least one benefit is required', 'error');
        }
      });
    });
  }

  // Remove benefit field delegation
  if (benefitsContainer) {
    benefitsContainer.addEventListener('click', (e) => {
      const removeBtn = e.target.closest('.remove-benefit-btn');
      if (removeBtn) {
        if (benefitsContainer.children.length > 1) {
          removeBtn.closest('.benefit-item').remove();
        } else {
          showToast('At least one benefit is required', 'error');
        }
      }
    });
  }

  // Form submission
  if (addSubscriptionForm) {
    addSubscriptionForm.addEventListener('submit', (e) => {
      e.preventDefault();

      // Clear previous errors
      document.querySelectorAll('[id$="Error"]').forEach(el => el.classList.add('hidden'));

      let isValid = true;

      // Validate subscription name
      const subscriptionName = document.getElementById('subscriptionName');
      if (!subscriptionName.value.trim()) {
        document.getElementById('subscriptionNameError').classList.remove('hidden');
        isValid = false;
      }

      // Validate price
      const subscriptionPrice = document.getElementById('subscriptionPrice');
      if (!subscriptionPrice.value || subscriptionPrice.value <= 0) {
        document.getElementById('subscriptionPriceError').classList.remove('hidden');
        isValid = false;
      }

      // Validate plan key
      const planKey = document.getElementById('planKey');
      if (!planKey.value.trim()) {
        document.getElementById('planKeyError').classList.remove('hidden');
        isValid = false;
      }

      // Validate target audience
      const forContractor = document.querySelector('input[name="for_contractor"]:checked');
      if (!forContractor) {
        document.getElementById('forContractorError').classList.remove('hidden');
        isValid = false;
      }

      if (!isValid) {
        showToast('Please fill in all required fields', 'error');
        return;
      }

      // Collect benefits
      const benefits = [];
      const benefitInputs = document.querySelectorAll('.benefit-input');
      const benefitCheckboxes = document.querySelectorAll('.benefit-checkbox');

      benefitInputs.forEach((input, index) => {
        if (input.value.trim() && benefitCheckboxes[index].checked) {
          benefits.push(input.value.trim());
        }
      });

      // Prepare form data
      const forContractorRadio = document.querySelector('input[name="for_contractor"]:checked');
      const formData = {
        subscription_name: subscriptionName.value.trim(),
        subscription_price: parseFloat(subscriptionPrice.value),
        billing_cycle: billingCycle ? billingCycle.value : 'monthly',
        duration_days: durationDays ? parseInt(durationDays.value) : null,
        plan_key: document.getElementById('planKey') ? document.getElementById('planKey').value.trim() : '',
        for_contractor: forContractorRadio ? parseInt(forContractorRadio.value) : 0,
        benefits: benefits
      };

      const submitBtn = addSubscriptionForm.querySelector('button[type="submit"]') || addSubscriptionForm.querySelector('.bg-orange-500');
      const originalBtnText = submitBtn.innerHTML;
      submitBtn.disabled = true;
      submitBtn.innerHTML = '<i class="fi fi-rr-spinner animate-spin text-xl"></i>';

      // Send data to backend
      fetch('/admin/project-management/subscriptions/plans', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '',
          'Accept': 'application/json'
        },
        body: JSON.stringify(formData)
      })
        .then(async response => {
          const data = await response.json();
          if (!response.ok) {
            if (response.status === 422 && data.errors) {
              // Handle validation errors under inputs
              Object.keys(data.errors).forEach(key => {
                // Convert backend names back to HTML IDs (e.g., subscription_name -> subscriptionName, plan_key -> planKey)
                let errorId = key.replace(/_([a-z])/g, (g) => g[1].toUpperCase()) + 'Error';
                const errorEl = document.getElementById(errorId);
                if (errorEl) {
                  errorEl.textContent = data.errors[key][0];
                  errorEl.classList.remove('hidden');
                }
              });
              throw new Error('Validation failed');
            }
            throw new Error(data.message || 'Failed to add subscription plan');
          }
          return data;
        })
        .then(result => {
          showToast('Subscription plan added successfully!', 'success');

          // Build new card HTML manually (or trigger a page reload)
          // Note: For full consistency with Laravel ID generation, a reload is safer since we need the `platform_payment_id` 
          // to attach to the edit/delete buttons, but the requirement was "should be updated the frontend as well if deleted", 
          // usually adding also means refreshing. I'll dynamically reload the page for now to ensure all icons have DB IDs, 
          // or just append. Let's append and use the name for delete to fail gracefully if there's no ID yet.

          // We really need the DB ID to attach to data-id to enable edit/delete immediately.
          // If the backend doesn't return the full created model ID in `result.data`, a reload is best.
          // Assuming the best way to handle this securely without breaking edit/delete is reloading.
          setTimeout(() => {
            window.location.reload();
          }, 800);

          closeAddSubscriptionModal();
        })
        .catch(error => {
          if (error.message !== 'Validation failed') {
            showToast(error.message, 'error');
          }
        })
        .finally(() => {
          submitBtn.disabled = false;
          submitBtn.innerHTML = originalBtnText;
        });
    });
  }

  // ===== Edit Subscription Modal Logic =====
  const editModal = document.getElementById('editSubscriptionModal');
  const editForm = document.getElementById('editSubscriptionForm');
  const editNameInput = document.getElementById('editSubscriptionName');
  const editPriceInput = document.getElementById('editSubscriptionPrice');
  const editBillingCycleSelect = document.getElementById('editBillingCycle');
  const editBenefitsContainer = document.getElementById('editBenefitsContainer');
  const editAddBenefitBtn = document.getElementById('editAddBenefitBtn');
  const closeEditSubscriptionBtn = document.getElementById('closeEditSubscriptionBtn');
  const cancelEditSubscriptionBtn = document.getElementById('cancelEditSubscriptionBtn');
  const editDurationDaysContainer = document.getElementById('editDurationDaysContainer');
  const editDurationDays = document.getElementById('editDurationDays');

  if (editBillingCycleSelect && editDurationDaysContainer) {
    editBillingCycleSelect.addEventListener('change', () => {
      if (editBillingCycleSelect.value === 'one-time') {
        editDurationDaysContainer.classList.remove('hidden');
        if (editDurationDays) editDurationDays.setAttribute('required', 'true');
      } else {
        editDurationDaysContainer.classList.add('hidden');
        if (editDurationDays) {
          editDurationDays.removeAttribute('required');
          editDurationDays.value = '';
        }
      }
    });
  }

  function openEditModal() {
    editModal.classList.remove('hidden');
    editModal.classList.add('flex');
  }

  function closeEditModal() {
    const panel = editModal.querySelector('.edit-subscription-panel');
    panel.style.animation = 'modalSlideUp 0.3s ease-out reverse';
    setTimeout(() => {
      editModal.classList.add('hidden');
      editModal.classList.remove('flex');
      panel.style.animation = '';
      // Reset form
      editForm.reset();
      editBenefitsContainer.innerHTML = '';
      document.querySelectorAll('#editSubscriptionModal [id$="Error"]').forEach(el => el.classList.add('hidden'));
    }, 200);
  }

  if (closeEditSubscriptionBtn) closeEditSubscriptionBtn.addEventListener('click', closeEditModal);
  if (cancelEditSubscriptionBtn) cancelEditSubscriptionBtn.addEventListener('click', closeEditModal);
  if (editModal) {
    editModal.addEventListener('click', (e) => { if (e.target === editModal) closeEditModal(); });
  }

  function createBenefitRow(textValue = '') {
    const row = document.createElement('div');
    row.className = 'flex items-center gap-2 benefit-item';
    row.innerHTML = `
      <input type="checkbox" class="w-4 h-4 text-indigo-600 border-gray-300 rounded focus:ring-indigo-500 benefit-checkbox" checked>
      <input type="text" class="flex-1 px-3 py-2 border-2 border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-400 focus:border-indigo-400 transition text-sm benefit-input" placeholder="Enter benefit" value="${textValue.replace(/"/g, '&quot;')}">
      <button type="button" class="text-red-500 hover:text-red-700 transition p-1 remove-benefit-btn hidden"><i class="fi fi-rr-cross-small text-xl"></i></button>`;
    const removeBtn = row.querySelector('.remove-benefit-btn');
    removeBtn.addEventListener('click', () => {
      if (editBenefitsContainer.children.length > 1) {
        row.remove();
      } else {
        showToast('At least one benefit is required', 'error');
      }
    });
    return row;
  }

  if (editAddBenefitBtn) {
    editAddBenefitBtn.addEventListener('click', () => {
      editBenefitsContainer.appendChild(createBenefitRow());
    });
  }

  // Populate and open modal when clicking edit
  const editIcons = document.querySelectorAll('.edit-icon');
  let currentEditingPlanId = null;

  editIcons.forEach(icon => {
    icon.addEventListener('click', (e) => {
      e.stopPropagation();
      const card = icon.closest('.subscription-card');
      if (!card) return;

      // Extract data from data attributes
      currentEditingPlanId = card.dataset.id;
      const name = card.dataset.name || '';
      const price = card.dataset.price || '0';
      const billingCycle = card.dataset.billingCycle || 'monthly';
      const durationDaysAttr = card.dataset.duration || '';
      let benefits = [];
      try {
        benefits = JSON.parse(card.dataset.benefits || '[]');
      } catch (err) {
        console.error('Error parsing benefits:', err);
      }

      // Prefill modal
      editNameInput.value = name;
      editPriceInput.value = price;
      editBillingCycleSelect.value = billingCycle;
      if (editDurationDays) {
        editDurationDays.value = durationDaysAttr;
      }

      if (editBillingCycleSelect.value === 'one-time') {
        if (editDurationDaysContainer) editDurationDaysContainer.classList.remove('hidden');
        if (editDurationDays) editDurationDays.setAttribute('required', 'true');
      } else {
        if (editDurationDaysContainer) editDurationDaysContainer.classList.add('hidden');
        if (editDurationDays) {
          editDurationDays.removeAttribute('required');
          editDurationDays.value = '';
        }
      }
      editBenefitsContainer.innerHTML = '';

      if (benefits.length === 0) benefits.push('');
      benefits.forEach(b => editBenefitsContainer.appendChild(createBenefitRow(b)));

      openEditModal();
    });
  });

  // Submit edit form
  if (editForm) {
    editForm.addEventListener('submit', (e) => {
      e.preventDefault();

      // Clear previous error states
      const modal = document.getElementById('editSubscriptionModal');
      modal.querySelectorAll('[id$="Error"]').forEach(el => el.classList.add('hidden'));
      modal.querySelectorAll('.border-red-500').forEach(el => el.classList.remove('border-red-500'));

      let valid = true;
      if (!editNameInput.value.trim()) {
        document.getElementById('editSubscriptionNameError').classList.remove('hidden');
        editNameInput.classList.add('border-red-500');
        valid = false;
      }
      if (!editPriceInput.value || parseFloat(editPriceInput.value) < 0) {
        document.getElementById('editSubscriptionPriceError').classList.remove('hidden');
        editPriceInput.classList.add('border-red-500');
        valid = false;
      }
      if (!editBillingCycleSelect.value) {
        document.getElementById('editBillingCycleError').classList.remove('hidden');
        editBillingCycleSelect.classList.add('border-red-500');
        valid = false;
      }

      // Collect benefits (from inputs only, ensuring at least one is present and not empty)
      const benefits = [];
      const benefitInputs = editBenefitsContainer.querySelectorAll('.benefit-input');
      benefitInputs.forEach(input => {
        if (input.value.trim()) benefits.push(input.value.trim());
      });

      if (benefits.length === 0) {
        showToast('At least one benefit is required', 'error');
        valid = false;
      }

      if (!valid) return;

      const submitBtn = editForm.querySelector('button[type="submit"]');
      const originalBtnText = submitBtn.innerHTML;
      submitBtn.disabled = true;
      submitBtn.innerHTML = '<i class="fi fi-rr-spinner animate-spin"></i> Saving...';

      // Prepare data
      const payload = {
        edit_subscription_name: editNameInput.value.trim(),
        edit_subscription_price: parseFloat(editPriceInput.value),
        edit_billing_cycle: editBillingCycleSelect.value,
        edit_duration_days: editDurationDays && editBillingCycleSelect.value === 'one-time' ? parseInt(editDurationDays.value) : null,
        benefits: benefits
      };

      // AJAX PUT Request
      fetch(`/admin/project-management/subscriptions/plans/${currentEditingPlanId}`, {
        method: 'PUT',
        headers: {
          'Content-Type': 'application/json',
          'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '',
          'Accept': 'application/json'
        },
        body: JSON.stringify(payload)
      })
        .then(async response => {
          const data = await response.json();
          if (!response.ok) {
            if (response.status === 422 && data.errors) {
              // Handle validation errors
              Object.keys(data.errors).forEach(key => {
                const errorEl = document.getElementById(`${key}Error`);
                if (errorEl) {
                  errorEl.textContent = data.errors[key][0];
                  errorEl.classList.remove('hidden');
                }
              });
              throw new Error('Validation failed');
            }
            throw new Error(data.message || 'Failed to update plan');
          }
          return data;
        })
        .then(result => {
          showToast('Subscription plan updated successfully!', 'success');

          // Update the specific card in UI without reload
          const card = document.querySelector(`.subscription-card[data-id="${currentEditingPlanId}"]`);
          if (card) {
            // Update text content
            card.querySelector('h3').textContent = payload.edit_subscription_name;
            card.querySelector('.text-5xl').textContent = `₱ ${parseFloat(payload.edit_subscription_price).toLocaleString(undefined, { minimumFractionDigits: 0 })}`;
            card.querySelector('p.text-gray-500').textContent = `${payload.edit_billing_cycle.charAt(0).toUpperCase() + payload.edit_billing_cycle.slice(1)} Charge`;

            // Update benefits list
            const benefitList = card.querySelector('ul');
            benefitList.innerHTML = benefits.map(b => `
                  <li class="flex items-start gap-2 text-sm text-gray-600">
                      <i class="fi fi-ss-check-circle text-orange-500 text-base mt-0.5"></i>
                      <span>${b}</span>
                  </li>
              `).join('');

            // Update data attributes so future edits have fresh data
            card.dataset.name = payload.edit_subscription_name;
            card.dataset.price = payload.edit_subscription_price;
            card.dataset.billingCycle = payload.edit_billing_cycle;
            card.dataset.duration = payload.edit_duration_days || '';
            card.dataset.benefits = JSON.stringify(benefits);
          }

          closeEditModal();
        })
        .catch(error => {
          if (error.message !== 'Validation failed') {
            showToast(error.message, 'error');
          }
        })
        .finally(() => {
          submitBtn.disabled = false;
          submitBtn.innerHTML = originalBtnText;
        });
    });
  }

  // Delete Icons
  // ===== Delete Subscription Modal Logic =====
  const deleteModal = document.getElementById('deleteSubscriptionModal');
  const closeDeleteBtn = document.getElementById('closeDeleteSubscriptionBtn');
  const cancelDeleteBtn = document.getElementById('cancelDeleteSubscriptionBtn');
  const confirmDeleteBtn = document.getElementById('confirmDeleteSubscriptionBtn');
  const deletePlanNameSpan = document.getElementById('deletePlanName');
  const deleteReasonInput = document.getElementById('deleteSubscriptionReason');
  const deleteReasonError = document.getElementById('deleteSubscriptionReasonError');
  let cardToDelete = null;

  function openDeleteModal(planName, cardRef) {
    deletePlanNameSpan.textContent = planName;
    cardToDelete = cardRef;
    if (deleteReasonInput) deleteReasonInput.value = '';
    if (deleteReasonInput) deleteReasonInput.classList.remove('border-red-500');
    if (deleteReasonError) deleteReasonError.classList.add('hidden');
    deleteModal.classList.remove('hidden');
    deleteModal.classList.add('flex');
  }

  function closeDeleteModal() {
    const panel = deleteModal.querySelector('.delete-subscription-panel');
    panel.style.animation = 'modalSlideUp 0.28s ease-out reverse';
    setTimeout(() => {
      deleteModal.classList.add('hidden');
      deleteModal.classList.remove('flex');
      panel.style.animation = '';
      cardToDelete = null;
    }, 180);
  }

  if (closeDeleteBtn) closeDeleteBtn.addEventListener('click', closeDeleteModal);
  if (cancelDeleteBtn) cancelDeleteBtn.addEventListener('click', closeDeleteModal);
  if (deleteModal) {
    deleteModal.addEventListener('click', (e) => { if (e.target === deleteModal) closeDeleteModal(); });
  }
  document.addEventListener('keydown', (e) => { if (e.key === 'Escape' && !deleteModal.classList.contains('hidden')) closeDeleteModal(); });

  const deleteIcons = document.querySelectorAll('.delete-icon');
  deleteIcons.forEach(icon => {
    icon.addEventListener('click', (e) => {
      e.stopPropagation();
      const card = icon.closest('.subscription-card');
      const tierName = card.querySelector('h3').textContent.trim();
      openDeleteModal(tierName, card);
    });
  });

  if (confirmDeleteBtn) {
    confirmDeleteBtn.addEventListener('click', () => {
      if (!cardToDelete) { closeDeleteModal(); return; }

      const reason = deleteReasonInput ? deleteReasonInput.value.trim() : '';
      if (!reason) {
        if (deleteReasonError) deleteReasonError.classList.remove('hidden');
        if (deleteReasonInput) deleteReasonInput.classList.add('border-red-500');
        return;
      }

      const planId = cardToDelete.dataset.id;
      const tierName = cardToDelete.querySelector('h3')?.textContent.trim() || 'Subscription';

      const originalBtnText = confirmDeleteBtn.innerHTML;
      confirmDeleteBtn.disabled = true;
      confirmDeleteBtn.innerHTML = '<i class="fi fi-rr-spinner animate-spin"></i> Deleting...';

      fetch(`/admin/project-management/subscriptions/plans/${planId}`, {
        method: 'DELETE',
        headers: {
          'Content-Type': 'application/json',
          'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '',
          'Accept': 'application/json'
        },
        body: JSON.stringify({ reason: reason })
      })
        .then(async response => {
          const data = await response.json();
          if (!response.ok) {
            throw new Error(data.message || 'Failed to delete plan');
          }
          return data;
        })
        .then(result => {
          // Store a local reference because closeDeleteModal sets cardToDelete to null
          const card = cardToDelete;

          // Deletion animation sequence
          if (card) {
            card.style.transition = 'all 0.4s cubic-bezier(0.4, 0, 0.2, 1)';
            card.style.opacity = '0';
            card.style.transform = 'scale(0.95)';
            setTimeout(() => {
              card.remove();
              showToast(`${tierName} deleted successfully!`, 'success');
            }, 400);
          }
          closeDeleteModal();
        })
        .catch(error => {
          showToast(error.message, 'error');
        })
        .finally(() => {
          confirmDeleteBtn.disabled = false;
          confirmDeleteBtn.innerHTML = originalBtnText;
        });
    });
  }

  // ===== Row Edit Subscription Modal Logic =====
  const rowEditModal = document.getElementById('rowEditSubscriptionModal');
  const rowEditForm = document.getElementById('rowEditSubscriptionForm');
  const rowEditCloseBtn = document.getElementById('rowEditCloseBtn');
  const rowEditCancelBtn = document.getElementById('rowEditCancelBtn');
  const rowEditContractor = document.getElementById('rowEditContractor');
  const rowEditStatusBadge = document.getElementById('rowEditStatusBadge');
  const rowEditPlan = document.getElementById('rowEditPlan');
  const rowEditRevenue = document.getElementById('rowEditRevenue');
  const rowEditStartDate = document.getElementById('rowEditStartDate');
  const rowEditExpiryDate = document.getElementById('rowEditExpiryDate');
  let rowEditingTarget = null;

  function openRowEditModal() {
    rowEditModal.classList.remove('hidden');
    rowEditModal.classList.add('flex');
  }
  function closeRowEditModal() {
    const panel = rowEditModal.querySelector('.row-edit-subscription-panel');
    panel.style.animation = 'modalSlideUp 0.3s ease-out reverse';
    setTimeout(() => {
      rowEditModal.classList.add('hidden');
      rowEditModal.classList.remove('flex');
      panel.style.animation = '';
      rowEditForm.reset();
      rowEditStatusBadge.className = 'px-4 py-2.5 rounded-lg text-sm font-semibold flex items-center gap-2 border-2 border-gray-200 bg-gray-50 text-gray-700';
      rowEditStatusBadge.textContent = '';
      rowEditingTarget = null;
    }, 200);
  }
  if (rowEditCloseBtn) rowEditCloseBtn.addEventListener('click', closeRowEditModal);
  if (rowEditCancelBtn) rowEditCancelBtn.addEventListener('click', closeRowEditModal);
  if (rowEditModal) rowEditModal.addEventListener('click', e => { if (e.target === rowEditModal) closeRowEditModal(); });

  function computeStatus(expiry) {
    const today = new Date();
    const exp = new Date(expiry);
    if (isNaN(exp.getTime())) return 'Unknown';
    if (exp < today) return 'Expired';
    const diffDays = Math.ceil((exp - today) / (1000 * 60 * 60 * 24));
    if (diffDays <= 7) return 'Expiring Soon';
    return 'Active';
  }
  function styleStatusBadge(status) {
    rowEditStatusBadge.classList.remove('active', 'expiring', 'expired');
    if (status === 'Active') rowEditStatusBadge.classList.add('active');
    else if (status === 'Expiring Soon') rowEditStatusBadge.classList.add('expiring');
    else if (status === 'Expired') rowEditStatusBadge.classList.add('expired');
    rowEditStatusBadge.textContent = status;
  }

  document.querySelectorAll('.edit-row-subscription-btn').forEach(btn => {
    btn.addEventListener('click', e => {
      e.stopPropagation();
      rowEditingTarget = btn.closest('tr');
      const contractor = btn.dataset.name;
      const plan = btn.dataset.subscription;
      const startDate = btn.dataset.transaction;
      const expiryDate = btn.dataset.expiration;
      const price = btn.dataset.price || '0';
      const status = computeStatus(expiryDate);

      rowEditContractor.value = contractor;
      rowEditPlan.value = plan;
      rowEditStartDate.value = startDate;
      rowEditExpiryDate.value = expiryDate;
      rowEditRevenue.value = `₱${parseFloat(price).toLocaleString()} (1 month)`;
      styleStatusBadge(status);
      openRowEditModal();
    });
  });

  if (rowEditForm) {
    rowEditForm.addEventListener('submit', e => {
      e.preventDefault();
      if (!rowEditingTarget) { closeRowEditModal(); return; }
      // Basic validation
      if (!rowEditStartDate.value || !rowEditExpiryDate.value) {
        showToast('Start and Expiry dates required', 'error');
        return;
      }
      // Update dataset + table cells
      const planCell = rowEditingTarget.querySelector('td:nth-child(3) span');
      if (planCell) {
        planCell.textContent = rowEditPlan.value;
        // Adjust badge color classes quickly
        planCell.className = 'inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold';
        if (rowEditPlan.value.includes('Gold')) planCell.classList.add('bg-yellow-100', 'text-yellow-800');
        else if (rowEditPlan.value.includes('Silver')) planCell.classList.add('bg-gray-200', 'text-gray-700');
        else planCell.classList.add('bg-orange-100', 'text-orange-800');
      }
      const startCell = rowEditingTarget.querySelector('td:nth-child(4)');
      const expiryCell = rowEditingTarget.querySelector('td:nth-child(5)');
      if (startCell) startCell.textContent = rowEditStartDate.value;
      if (expiryCell) expiryCell.textContent = rowEditExpiryDate.value;
      // Update edit button datasets
      const editBtn = rowEditingTarget.querySelector('.edit-row-subscription-btn');
      if (editBtn) {
        editBtn.dataset.transaction = rowEditStartDate.value;
        editBtn.dataset.expiration = rowEditExpiryDate.value;
        editBtn.dataset.subscription = rowEditPlan.value;
        editBtn.dataset.status = computeStatus(rowEditExpiryDate.value);
      }
      showToast('Subscription details updated', 'success');
      closeRowEditModal();
    });
  }

  // Card Click - View Details
  const subscriptionCards = document.querySelectorAll('.subscription-card');
  subscriptionCards.forEach(card => {
    card.addEventListener('click', function (e) {
      // Don't trigger if clicking on edit/delete buttons
      if (e.target.closest('.edit-icon') || e.target.closest('.delete-icon')) {
        return;
      }

      const tierName = this.querySelector('h3').textContent;
      const price = this.querySelector('.text-5xl').textContent;
      showToast(`Viewing ${tierName} - ${price}`, 'info');
    });
  });

  // Hover effect enhancement
  subscriptionCards.forEach(card => {
    card.addEventListener('mouseenter', function () {
      this.style.transform = 'translateY(-12px) scale(1.02)';
    });

    card.addEventListener('mouseleave', function () {
      this.style.transform = '';
    });
  });
});

// Toast Notification Function
function showToast(message, type = 'info') {
  // Remove existing toast
  const existingToast = document.querySelector('.toast-notification');
  if (existingToast) {
    existingToast.remove();
  }

  // Create toast
  const toast = document.createElement('div');
  toast.className = 'toast-notification fixed bottom-8 right-8 px-6 py-4 rounded-lg shadow-2xl text-white font-semibold z-50 transform transition-all duration-300 translate-y-0 opacity-100';

  // Set color based on type
  if (type === 'success') {
    toast.style.background = 'linear-gradient(135deg, #10b981, #059669)';
  } else if (type === 'error') {
    toast.style.background = 'linear-gradient(135deg, #ef4444, #dc2626)';
  } else {
    toast.style.background = 'linear-gradient(135deg, #3b82f6, #2563eb)';
  }

  toast.textContent = message;
  document.body.appendChild(toast);

  // Animate in
  setTimeout(() => {
    toast.style.transform = 'translateY(-10px)';
  }, 100);

  // Remove after 3 seconds
  setTimeout(() => {
    toast.style.opacity = '0';
    toast.style.transform = 'translateY(20px)';
    setTimeout(() => {
      toast.remove();
    }, 300);
  }, 3000);
}

// Animate numbers function
function animateNumbers() {
  const numberElements = document.querySelectorAll('.stats-card .text-4xl');

  numberElements.forEach(element => {
    const text = element.textContent.trim();
    const haspeso = text.includes('₱');
    const numStr = text.replace(/[₱,]/g, '');
    const targetNum = parseInt(numStr);

    if (isNaN(targetNum)) return;

    let currentNum = 0;
    const increment = Math.ceil(targetNum / 50);
    const duration = 1000;
    const stepTime = duration / (targetNum / increment);

    const timer = setInterval(() => {
      currentNum += increment;
      if (currentNum >= targetNum) {
        currentNum = targetNum;
        clearInterval(timer);
      }

      if (haspeso) {
        element.textContent = `₱${currentNum.toLocaleString()}`;
      } else {
        element.textContent = currentNum.toString();
      }
    }, stepTime);
  });
}

// Shake animation
const style = document.createElement('style');
style.textContent = `
  @keyframes shake {
    0%, 100% { transform: translateX(0) translateY(-8px); }
    10%, 30%, 50%, 70%, 90% { transform: translateX(-5px) translateY(-8px); }
    20%, 40%, 60%, 80% { transform: translateX(5px) translateY(-8px); }
  }
`;
document.head.appendChild(style);
