// Subscriptions Page Interactivity

document.addEventListener('DOMContentLoaded', function() {
  // Tab Switching
  const tabActive = document.getElementById('tabActiveSubscriptions');
  const tabExpired = document.getElementById('tabExpiredSubscriptions');
  const activeTable = document.getElementById('activeSubscriptionsTable');
  const expiredTable = document.getElementById('expiredSubscriptionsTable');

  if (tabActive && tabExpired) {
    tabActive.addEventListener('click', function() {
      tabActive.classList.add('active', 'border-orange-500', 'text-orange-600');
      tabActive.classList.remove('border-transparent', 'text-gray-600');
      tabExpired.classList.remove('active', 'border-orange-500', 'text-orange-600');
      tabExpired.classList.add('border-transparent', 'text-gray-600');
      
      activeTable.classList.remove('hidden');
      expiredTable.classList.add('hidden');
    });

    tabExpired.addEventListener('click', function() {
      tabExpired.classList.add('active', 'border-orange-500', 'text-orange-600');
      tabExpired.classList.remove('border-transparent', 'text-gray-600');
      tabActive.classList.remove('active', 'border-orange-500', 'text-orange-600');
      tabActive.classList.add('border-transparent', 'text-gray-600');
      
      expiredTable.classList.remove('hidden');
      activeTable.classList.add('hidden');
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
    panel.style.animation='modalSlideUp .3s ease-out reverse';
    setTimeout(()=>{
      deactivateModal.classList.add('hidden');
      deactivateModal.classList.remove('flex');
      panel.style.animation='';
      deactivateForm.reset();
      deactivateReasonError.classList.add('hidden');
      rowToDeactivate=null;
    },200);
  }
  if (closeDeactivateBtn) closeDeactivateBtn.addEventListener('click', closeDeactivateModal);
  if (cancelDeactivateBtn) cancelDeactivateBtn.addEventListener('click', closeDeactivateModal);
  if (deactivateModal) deactivateModal.addEventListener('click', e=>{ if (e.target===deactivateModal) closeDeactivateModal(); });

  document.querySelectorAll('.deactivate-subscription-btn').forEach(btn => {
    btn.addEventListener('click', e => {
      e.stopPropagation();
      const row = btn.closest('tr');
      const contractor = btn.dataset.name || row.querySelector('td:nth-child(2) span:last-child')?.textContent.trim() || 'Contractor';
      openDeactivateModal(contractor, row);
    });
  });

  if (deactivateForm) {
    deactivateForm.addEventListener('submit', e => {
      e.preventDefault();
      deactivateReasonError.classList.add('hidden');
      if (!deactivateReason.value.trim()) {
        deactivateReasonError.classList.remove('hidden');
        showToast('Reason required','error');
        return;
      }
      if (rowToDeactivate) {
        rowToDeactivate.classList.add('opacity-60');
        // Optional visual badge update
        const planBadge = rowToDeactivate.querySelector('td:nth-child(3) span');
        if (planBadge) {
          planBadge.textContent = (planBadge.textContent.trim() + ' (Deactivated)');
          planBadge.classList.add('line-through');
        }
      }
      showToast('Subscription deactivated','success');
      closeDeactivateModal();
    });
  }

  // ===== Renew Subscription Modal Logic =====
  const renewModal = document.getElementById('renewSubscriptionModal');
  const renewForm = document.getElementById('renewSubscriptionForm');
  const closeRenewBtn = document.getElementById('closeRenewSubscriptionBtn');
  const cancelRenewBtn = document.getElementById('cancelRenewSubscriptionBtn');
  const renewContractorName = document.getElementById('renewContractorName');
  const renewCurrentPlan = document.getElementById('renewCurrentPlan');
  const renewNewPlan = document.getElementById('renewNewPlan');
  const renewStartDate = document.getElementById('renewStartDate');
  const renewExpiryDate = document.getElementById('renewExpiryDate');
  const renewCharge = document.getElementById('renewCharge').querySelector('span');
  let rowToRenew = null;

  function computeExpiry(startValue) {
    const start = new Date(startValue);
    if (isNaN(start.getTime())) return '';
    // Add 30 days
    const newDate = new Date(start.getTime() + 30*24*60*60*1000);
    return newDate.toISOString().split('T')[0];
  }
  function planPrice(plan) {
    if (plan.includes('Gold')) return 1999;
    if (plan.includes('Silver')) return 1499;
    if (plan.includes('Bronze')) return 999;
    return 0;
  }
  function openRenewModal(rowRef) {
    rowToRenew = rowRef;
    const contractor = rowRef.querySelector('td:nth-child(2) span:last-child')?.textContent.trim() || 'Contractor';
    const plan = rowRef.querySelector('td:nth-child(3) span')?.textContent.trim() || 'Unknown Plan';
    renewContractorName.value = contractor;
    renewCurrentPlan.value = plan.replace('(Deactivated)','').trim();
    renewNewPlan.value = '';
    const today = new Date();
    const startStr = today.toISOString().split('T')[0];
    renewStartDate.value = startStr;
    renewExpiryDate.value = computeExpiry(startStr);
    renewCharge.textContent = `₱${planPrice(plan).toLocaleString()}.00`;
    renewModal.classList.remove('hidden');
    renewModal.classList.add('flex');
  }
  function closeRenewModal() {
    const panel = renewModal.querySelector('.renew-subscription-panel');
    panel.style.animation='modalSlideUp .3s ease-out reverse';
    setTimeout(()=>{
      renewModal.classList.add('hidden');
      renewModal.classList.remove('flex');
      panel.style.animation='';
      renewForm.reset();
      rowToRenew=null;
    },200);
  }
  if (closeRenewBtn) closeRenewBtn.addEventListener('click', closeRenewModal);
  if (cancelRenewBtn) cancelRenewBtn.addEventListener('click', closeRenewModal);
  if (renewModal) renewModal.addEventListener('click', e=>{ if (e.target===renewModal) closeRenewModal(); });

  renewStartDate.addEventListener('change', () => {
    renewExpiryDate.value = computeExpiry(renewStartDate.value);
  });
  renewNewPlan.addEventListener('change', () => {
    const effectivePlan = renewNewPlan.value || renewCurrentPlan.value;
    renewCharge.textContent = `₱${planPrice(effectivePlan).toLocaleString()}.00`;
  });

  document.querySelectorAll('.renew-subscription-btn').forEach(btn => {
    btn.addEventListener('click', e => {
      e.stopPropagation();
      const row = btn.closest('tr');
      openRenewModal(row);
    });
  });

  if (renewForm) {
    renewForm.addEventListener('submit', e => {
      e.preventDefault();
      if (!rowToRenew) { closeRenewModal(); return; }
      // Update row details
      const planCell = rowToRenew.querySelector('td:nth-child(3) span');
      const transactionCell = rowToRenew.querySelector('td:nth-child(4)');
      const expiryCell = rowToRenew.querySelector('td:nth-child(5)');
      let newPlan = renewNewPlan.value || renewCurrentPlan.value;
      if (planCell) {
        planCell.textContent = newPlan;
        planCell.className='inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold';
        if (newPlan.includes('Gold')) planCell.classList.add('bg-yellow-100','text-yellow-800');
        else if (newPlan.includes('Silver')) planCell.classList.add('bg-gray-200','text-gray-700');
        else planCell.classList.add('bg-orange-100','text-orange-800');
      }
      if (transactionCell) transactionCell.textContent = renewStartDate.value;
      if (expiryCell) expiryCell.textContent = renewExpiryDate.value;
      // Move row to active table if it came from expired
      const activeTbody = document.querySelector('#activeSubscriptionsTable tbody');
      const parentTbody = rowToRenew.parentElement;
      if (activeTbody && parentTbody && parentTbody.id !== 'activeSubscriptionsTbody' && document.getElementById('expiredSubscriptionsTable').contains(rowToRenew)) {
        rowToRenew.classList.remove('bg-red-50');
        // Replace renew button with deactivate button
        const renewBtn = rowToRenew.querySelector('.renew-subscription-btn');
        if (renewBtn) {
          const newDeactivate = document.createElement('button');
          newDeactivate.className='deactivate-subscription-btn w-10 h-10 flex items-center justify-center rounded-full bg-orange-50 hover:bg-orange-100 text-orange-500 transition-all hover:shadow-md';
          newDeactivate.title='Deactivate';
          newDeactivate.innerHTML='<i class="fi fi-rr-ban text-base"></i>';
          renewBtn.replaceWith(newDeactivate);
          newDeactivate.addEventListener('click', ev => {
            ev.stopPropagation();
            openDeactivateModal(renewContractorName.value, rowToRenew);
          });
        }
        activeTbody.appendChild(rowToRenew);
      }
      showToast('Subscription renewed','success');
      closeRenewModal();
    });
  }

  // Reset Filter Button
  const resetFilterBtn = document.querySelector('.reset-filter-btn');
  if (resetFilterBtn) {
    resetFilterBtn.addEventListener('click', function() {
      showToast('Filters reset', 'info');
    });
  }

  // Statistics Cards Click Handlers
  const statsCards = document.querySelectorAll('.stats-card');
  statsCards.forEach(card => {
    card.addEventListener('click', function() {
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

  if (addSubscriptionBtn) {
    addSubscriptionBtn.addEventListener('click', function() {
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
      
      // Validate plan type
      const planType = document.getElementById('planType');
      if (!planType.value) {
        document.getElementById('planTypeError').classList.remove('hidden');
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
      const formData = {
        subscription_name: subscriptionName.value.trim(),
        price: parseFloat(subscriptionPrice.value),
        plan_type: planType.value,
        benefits: benefits
      };
      
      console.log('Form Data:', formData);
      
      // Show success message
      showToast('Subscription plan added successfully!', 'success');
      
      // Close modal
      closeAddSubscriptionModal();
      
      // TODO: Send data to backend
      // fetch('/admin/subscriptions', {
      //   method: 'POST',
      //   headers: {
      //     'Content-Type': 'application/json',
      //     'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
      //   },
      //   body: JSON.stringify(formData)
      // }).then(response => response.json())
      //   .then(data => {
      //     showToast('Subscription plan added successfully!', 'success');
      //     closeAddSubscriptionModal();
      //   })
      //   .catch(error => {
      //     showToast('Error adding subscription plan', 'error');
      //   });
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
      <input type="text" class="flex-1 px-3 py-2 border-2 border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-400 focus:border-indigo-400 transition text-sm benefit-input" placeholder="Enter benefit" value="${textValue.replace(/"/g,'&quot;')}">
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
  editIcons.forEach(icon => {
    icon.addEventListener('click', (e) => {
      e.stopPropagation();
      const card = icon.closest('.subscription-card');
      if (!card) return;
      // Extract data
      const name = card.querySelector('h3')?.textContent.trim() || '';
      const rawPrice = card.querySelector('.text-5xl')?.textContent.replace(/[^0-9.,]/g,'').trim() || '0';
      const priceNumeric = parseFloat(rawPrice.replace(/,/g,''));
      const benefitItems = [...card.querySelectorAll('ul li span')].map(span => span.textContent.trim());

      // Prefill
      editNameInput.value = name;
      editPriceInput.value = isNaN(priceNumeric) ? '' : priceNumeric;
      editBillingCycleSelect.value = 'monthly'; // Default; could map from stored data later
      editBenefitsContainer.innerHTML = '';
      if (benefitItems.length === 0) benefitItems.push('');
      benefitItems.forEach(b => editBenefitsContainer.appendChild(createBenefitRow(b)));

      openEditModal();
    });
  });

  // Submit edit form
  if (editForm) {
    editForm.addEventListener('submit', (e) => {
      e.preventDefault();
      document.querySelectorAll('#editSubscriptionModal [id$="Error"]').forEach(el => el.classList.add('hidden'));
      let valid = true;
      if (!editNameInput.value.trim()) { document.getElementById('editSubscriptionNameError').classList.remove('hidden'); valid = false; }
      if (!editPriceInput.value || parseFloat(editPriceInput.value) <= 0) { document.getElementById('editSubscriptionPriceError').classList.remove('hidden'); valid = false; }
      if (!editBillingCycleSelect.value) { document.getElementById('editBillingCycleError').classList.remove('hidden'); valid = false; }
      if (!valid) { showToast('Fix required fields', 'error'); return; }

      // Collect benefits
      const benefits = [];
      const benefitInputs = editBenefitsContainer.querySelectorAll('.benefit-input');
      const benefitCheckboxes = editBenefitsContainer.querySelectorAll('.benefit-checkbox');
      benefitInputs.forEach((input, idx) => {
        if (input.value.trim() && benefitCheckboxes[idx].checked) benefits.push(input.value.trim());
      });

      // Update the card in UI (find by name match before editing or store ref)
      const cards = document.querySelectorAll('.subscription-card');
      let updated = false;
      cards.forEach(card => {
        const titleEl = card.querySelector('h3');
        if (titleEl && titleEl.textContent.trim() === editNameInput.dataset.originalName) {
          // Name changed
          titleEl.textContent = editNameInput.value.trim();
          // Price
          const priceEl = card.querySelector('.text-5xl');
          if (priceEl) priceEl.textContent = `₱ ${parseFloat(editPriceInput.value).toLocaleString()}`;
          // Benefits list
          const list = card.querySelector('ul');
          if (list) {
            list.innerHTML = '';
            benefits.forEach(b => {
              const li = document.createElement('li');
              li.className = 'flex items-start gap-2 text-sm text-gray-600';
              li.innerHTML = `<i class="fi fi-ss-check-circle text-orange-500 text-base mt-0.5"></i><span>${b}</span>`;
              list.appendChild(li);
            });
          }
          updated = true;
        }
      });

      // If no match found by original name, fallback to first card with old name before change
      if (!updated) {
        // Simple fallback: update first card whose title matches current form name (editing without rename)
        cards.forEach(card => {
          const titleEl = card.querySelector('h3');
          if (!updated && titleEl && titleEl.textContent.trim() === editNameInput.value.trim()) {
            const priceEl = card.querySelector('.text-5xl');
            if (priceEl) priceEl.textContent = `₱ ${parseFloat(editPriceInput.value).toLocaleString()}`;
            const list = card.querySelector('ul');
            if (list) {
              list.innerHTML = '';
              benefits.forEach(b => {
                const li = document.createElement('li');
                li.className = 'flex items-start gap-2 text-sm text-gray-600';
                li.innerHTML = `<i class="fi fi-ss-check-circle text-orange-500 text-base mt-0.5"></i><span>${b}</span>`;
                list.appendChild(li);
              });
            }
            updated = true;
          }
        });
      }

      showToast('Subscription plan updated', 'success');
      closeEditModal();

      // TODO: Persist via AJAX
      // fetch(`/admin/subscriptions/{id}`, { method: 'PUT', headers: {...}, body: JSON.stringify({...}) })
    });
  }

  // Delete Icons
  // ===== Delete Subscription Modal Logic =====
  const deleteModal = document.getElementById('deleteSubscriptionModal');
  const closeDeleteBtn = document.getElementById('closeDeleteSubscriptionBtn');
  const cancelDeleteBtn = document.getElementById('cancelDeleteSubscriptionBtn');
  const confirmDeleteBtn = document.getElementById('confirmDeleteSubscriptionBtn');
  const deletePlanNameSpan = document.getElementById('deletePlanName');
  let cardToDelete = null;

  function openDeleteModal(planName, cardRef) {
    deletePlanNameSpan.textContent = planName;
    cardToDelete = cardRef;
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
      const tierName = cardToDelete.querySelector('h3')?.textContent.trim() || 'Subscription';
      // Deletion animation sequence
      cardToDelete.style.animation = 'shake 0.5s';
      setTimeout(() => {
        cardToDelete.style.transition = 'all .35s ease';
        cardToDelete.style.opacity = '0';
        cardToDelete.style.transform = 'scale(0.85) translateY(10px)';
        setTimeout(() => {
          cardToDelete.remove();
          showToast(`${tierName} deleted successfully!`, 'success');
        }, 320);
      }, 520);
      closeDeleteModal();
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
    setTimeout(()=>{
      rowEditModal.classList.add('hidden');
      rowEditModal.classList.remove('flex');
      panel.style.animation='';
      rowEditForm.reset();
      rowEditStatusBadge.className='px-4 py-2.5 rounded-lg text-sm font-semibold flex items-center gap-2 border-2 border-gray-200 bg-gray-50 text-gray-700';
      rowEditStatusBadge.textContent='';
      rowEditingTarget=null;
    },200);
  }
  if (rowEditCloseBtn) rowEditCloseBtn.addEventListener('click', closeRowEditModal);
  if (rowEditCancelBtn) rowEditCancelBtn.addEventListener('click', closeRowEditModal);
  if (rowEditModal) rowEditModal.addEventListener('click', e => { if (e.target===rowEditModal) closeRowEditModal(); });

  function computeStatus(expiry) {
    const today = new Date();
    const exp = new Date(expiry);
    if (isNaN(exp.getTime())) return 'Unknown';
    if (exp < today) return 'Expired';
    const diffDays = Math.ceil((exp - today)/(1000*60*60*24));
    if (diffDays <= 10) return 'Expiring Soon';
    return 'Active';
  }
  function styleStatusBadge(status) {
    rowEditStatusBadge.classList.remove('active','expiring','expired');
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
        showToast('Start and Expiry dates required','error');
        return;
      }
      // Update dataset + table cells
      const planCell = rowEditingTarget.querySelector('td:nth-child(3) span');
      if (planCell) {
        planCell.textContent = rowEditPlan.value;
        // Adjust badge color classes quickly
        planCell.className='inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold';
        if (rowEditPlan.value.includes('Gold')) planCell.classList.add('bg-yellow-100','text-yellow-800');
        else if (rowEditPlan.value.includes('Silver')) planCell.classList.add('bg-gray-200','text-gray-700');
        else planCell.classList.add('bg-orange-100','text-orange-800');
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
      showToast('Subscription details updated','success');
      closeRowEditModal();
    });
  }

  // Card Click - View Details
  const subscriptionCards = document.querySelectorAll('.subscription-card');
  subscriptionCards.forEach(card => {
    card.addEventListener('click', function(e) {
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
    card.addEventListener('mouseenter', function() {
      this.style.transform = 'translateY(-12px) scale(1.02)';
    });
    
    card.addEventListener('mouseleave', function() {
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
