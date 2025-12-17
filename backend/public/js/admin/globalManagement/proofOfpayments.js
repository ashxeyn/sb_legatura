document.addEventListener('DOMContentLoaded', () => {
  // Animate numbers on load
  const statNumbers = document.querySelectorAll('.stat-number');
  
  statNumbers.forEach(element => {
    const text = element.textContent;
    const hasK = text.includes('k');
    const hasComma = text.includes(',');
    
    // Extract the numeric value
    let targetValue = parseFloat(text.replace(/[k,]/g, ''));
    
    if (hasK) {
      targetValue = targetValue * 1000;
    }
    
    animateValue(element, 0, targetValue, 1500, hasK, hasComma);
  });
  
  function animateValue(element, start, end, duration, hasK, hasComma) {
    const startTime = performance.now();
    
    function update(currentTime) {
      const elapsed = currentTime - startTime;
      const progress = Math.min(elapsed / duration, 1);
      
      // Easing function for smooth animation
      const easeOutQuart = 1 - Math.pow(1 - progress, 4);
      const current = start + (end - start) * easeOutQuart;
      
      let displayValue;
      if (hasK) {
        displayValue = (current / 1000).toFixed(0) + 'k';
      } else if (hasComma) {
        displayValue = Math.floor(current).toLocaleString();
      } else {
        displayValue = Math.floor(current);
      }
      
      element.textContent = displayValue;
      
      if (progress < 1) {
        requestAnimationFrame(update);
      }
    }
    
    requestAnimationFrame(update);
  }
  
  // Add click interaction for cards
  const statCards = document.querySelectorAll('.stat-card');
  
  statCards.forEach(card => {
    card.addEventListener('click', () => {
      // Add a pulse effect on click
      card.style.transform = 'scale(0.98)';
      setTimeout(() => {
        card.style.transform = '';
      }, 200);
    });
    
    // Add interactive tooltip on hover (optional)
    card.addEventListener('mouseenter', () => {
      const title = card.querySelector('p').textContent;
      card.setAttribute('title', `View detailed ${title} analytics`);
    });
  });
  
  // Parallax effect on mouse move
  statCards.forEach(card => {
    card.addEventListener('mousemove', (e) => {
      const rect = card.getBoundingClientRect();
      const x = e.clientX - rect.left;
      const y = e.clientY - rect.top;
      
      const centerX = rect.width / 2;
      const centerY = rect.height / 2;
      
      const deltaX = (x - centerX) / centerX;
      const deltaY = (y - centerY) / centerY;
      
      const icon = card.querySelector('.bg-blue-100, .bg-orange-100, .bg-red-100, .bg-green-100');
      if (icon) {
        icon.style.transform = `translate(${deltaX * 10}px, ${deltaY * 10}px) rotate(10deg) scale(1.1)`;
      }
    });
    
    card.addEventListener('mouseleave', () => {
      const icon = card.querySelector('.bg-blue-100, .bg-orange-100, .bg-red-100, .bg-green-100');
      if (icon) {
        icon.style.transform = '';
      }
    });
  });
  
  // Add ripple effect on click
  statCards.forEach(card => {
    card.style.position = 'relative';
    card.style.overflow = 'hidden';
    
    card.addEventListener('click', function(e) {
      const ripple = document.createElement('span');
      const rect = this.getBoundingClientRect();
      const size = Math.max(rect.width, rect.height);
      const x = e.clientX - rect.left - size / 2;
      const y = e.clientY - rect.top - size / 2;
      
      ripple.style.width = ripple.style.height = size + 'px';
      ripple.style.left = x + 'px';
      ripple.style.top = y + 'px';
      ripple.classList.add('ripple-effect');
      
      this.appendChild(ripple);
      
      setTimeout(() => {
        ripple.remove();
      }, 600);
    });
  });
  
  // Add CSS for ripple effect dynamically
  const style = document.createElement('style');
  style.textContent = `
    .ripple-effect {
      position: absolute;
      border-radius: 50%;
      background: rgba(255, 255, 255, 0.5);
      transform: scale(0);
      animation: ripple-animation 0.6s ease-out;
      pointer-events: none;
    }
    
    @keyframes ripple-animation {
      to {
        transform: scale(2);
        opacity: 0;
      }
    }
  `;
  document.head.appendChild(style);
  
  // Refresh animation on visibility change
  document.addEventListener('visibilitychange', () => {
    if (!document.hidden) {
      statNumbers.forEach(element => {
        const text = element.getAttribute('data-original-value') || element.textContent;
        element.setAttribute('data-original-value', text);
        // Re-animate if needed
      });
    }
  });

  // ============== Payments table interactions ==============
  const table = document.getElementById('paymentsTable');
  const pendingModal = document.getElementById('pendingPaymentModal');
  const completedModal = document.getElementById('completedPaymentModal');
  const invalidModal = document.getElementById('invalidPaymentModal');
  const editModal = document.getElementById('editPaymentModal');
  const deleteModal = document.getElementById('deletePaymentModal');
  const confirmApproveModal = document.getElementById('confirmApproveModal');
  const confirmRejectModal = document.getElementById('confirmRejectModal');
  const toast = document.getElementById('toast');
  const approveIdBadge = document.getElementById('approveIdBadge');
  const rejectIdBadge = document.getElementById('rejectIdBadge');
  const approveSummary = document.getElementById('approveSummary');
  const rejectSummary = document.getElementById('rejectSummary');
  const rejectReason = document.getElementById('rejectReason');
  const rejectReasonCounter = document.getElementById('rejectReasonCounter');
  const rejectReasonError = document.getElementById('rejectReasonError');

  // Populate modal helpers
  const els = {
    id: document.getElementById('pp-payment-id'),
    contractor: document.getElementById('pp-contractor'),
    project: document.getElementById('pp-project'),
    amount: document.getElementById('pp-amount'),
    date: document.getElementById('pp-date'),
    method: document.getElementById('pp-method'),
    status: document.getElementById('pp-status')
  };

  let currentRow = null;

  // Utility: open/close modal
  function openModal(modal) {
    modal.classList.remove('hidden');
    modal.classList.add('flex');
    try { document.body.classList.add('overflow-hidden'); } catch {}
  }
  function closeModal(modal) {
    modal.classList.add('hidden');
    modal.classList.remove('flex');
    // remove overflow-hidden if no other modals visible
    const anyOpen = [pendingModal, completedModal, invalidModal, editModal, deleteModal, confirmApproveModal, confirmRejectModal].some(m => m && !m.classList.contains('hidden'));
    if (!anyOpen) { try { document.body.classList.remove('overflow-hidden'); } catch {} }
  }

  // Close buttons for any modal
  document.querySelectorAll('[data-close-modal]').forEach(btn => {
    btn.addEventListener('click', () => {
      [pendingModal, completedModal, invalidModal, editModal, deleteModal, confirmApproveModal, confirmRejectModal].forEach(m => m && closeModal(m));
    });
  });

  // Click outside to close
  [pendingModal, completedModal, invalidModal, editModal, deleteModal, confirmApproveModal, confirmRejectModal].forEach(modal => {
    if (!modal) return;
    modal.addEventListener('click', (e) => {
      if (e.target === modal) closeModal(modal);
    });
  });

  // Bind View actions for Pending rows only
  if (table) {
    table.querySelectorAll('.action-btn--view').forEach(btn => {
      btn.addEventListener('click', (e) => {
        const tr = e.currentTarget.closest('tr');
        const cells = tr.querySelectorAll('td');
        const statusText = (cells[6]?.innerText || '').trim();

        // Extract shared values
        const id = (cells[0]?.innerText || '').trim();
        const project = (cells[1]?.innerText || '').trim();
        const contractor = (cells[2]?.querySelector('.font-semibold')?.innerText || '').trim();
        const amount = (cells[3]?.innerText || '').trim();
        const date = (cells[4]?.innerText || '').trim();
        const method = (cells[5]?.innerText || '').trim();

        currentRow = tr;

        if (/pending/i.test(statusText)) {
          if (els.id) els.id.textContent = id || '#—';
          if (els.project) els.project.textContent = project || '—';
          if (els.contractor) els.contractor.textContent = contractor || '—';
          if (els.amount) els.amount.textContent = amount || '—';
          if (els.date) els.date.textContent = friendlyDate(date) || date || '—';
          if (els.method) els.method.textContent = method || '—';
          if (els.status) {
            els.status.textContent = 'Pending';
            els.status.className = 'inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold bg-amber-100 text-amber-700 border border-amber-200';
          }
          openModal(pendingModal);
          return;
        }

        if (/completed/i.test(statusText)) {
          // Fill Completed modal fields
          setText('cp-payment-id', id || '#—');
          setText('cp-project', project || '—');
          setText('cp-contractor', contractor || '—');
          setText('cp-amount', amount || '—');
          setText('cp-date', friendlyDate(date) || date || '—');
          setText('cp-method', method || '—');
          setText('cp-owner', '—');
          setText('cp-description', 'Construction of a 2-story commercial complex with parking space, electrical systems, and interior finishing.');
          setText('cp-verified', friendlyDate(date) || date || '—');
          // Optionally default milestone/reference
          setText('cp-milestone', 'Milestone 3 • Rooftop Building');
          setText('cp-reference', 'PAY_XXXX_cash');
          const remarks = document.getElementById('cp-remarks');
          if (remarks) remarks.value = '';
          openModal(completedModal);
          return;
        }

        if (/invalid/i.test(statusText)) {
          // Fill Invalid modal fields
          setText('ip-payment-id', id || '#—');
          setText('ip-project', project || '—');
          setText('ip-contractor', contractor || '—');
          setText('ip-amount', amount || '—');
          setText('ip-date', friendlyDate(date) || date || '—');
          setText('ip-method', method || '—');
          setText('ip-owner', '—');
          setText('ip-description', 'Construction of a 2-story commercial complex with parking space, electrical systems, and interior finishing.');
          setText('ip-verified', friendlyDate(date) || date || '—');
          setText('ip-milestone', 'Milestone 3 • Rooftop Building');
          setText('ip-reference', 'PAY_XXXX_cash');
          const iRemarks = document.getElementById('ip-remarks');
          if (iRemarks) iRemarks.value = '';
          openModal(invalidModal);
          return;
        }
      });
    });

    // Bind Edit actions
    table.querySelectorAll('.action-btn--edit').forEach(btn => {
      btn.addEventListener('click', (e) => {
        const tr = e.currentTarget.closest('tr');
        const cells = tr.querySelectorAll('td');
        const id = (cells[0]?.innerText || '').trim();
        const project = (cells[1]?.innerText || '').trim();
        const contractor = (cells[2]?.querySelector('.font-semibold')?.innerText || '').trim();
        const amount = (cells[3]?.innerText || '').trim().replace('₱', '');
        const date = (cells[4]?.innerText || '').trim();
        const method = (cells[5]?.innerText || '').trim();
        const statusText = (cells[6]?.innerText || '').trim();

        // Populate edit form
        const editRef = document.getElementById('edit-reference');
        const editProject = document.getElementById('edit-project');
        const editMethod = document.getElementById('edit-method');
        const editAmount = document.getElementById('edit-amount');
        const editStatus = document.getElementById('edit-status');
        const editRemarks = document.getElementById('edit-remarks');

        if (editRef) editRef.value = 'PAY_' + id + '_' + method.toLowerCase();
        if (editProject) editProject.value = project;
        if (editMethod) editMethod.value = method;
        if (editAmount) editAmount.value = amount;
        if (editStatus) editStatus.value = statusText;
        if (editRemarks) editRemarks.value = '';

        openModal(editModal);
      });
    });

    // Bind Delete actions
    table.querySelectorAll('.action-btn--delete').forEach(btn => {
      btn.addEventListener('click', (e) => {
        const tr = e.currentTarget.closest('tr');
        const cells = tr.querySelectorAll('td');
        const id = (cells[0]?.innerText || '').trim();
        const project = (cells[1]?.innerText || '').trim();
        const contractor = (cells[2]?.querySelector('.font-semibold')?.innerText || '').trim();
        const amount = (cells[3]?.innerText || '').trim();

        // Populate delete modal
        const delId = document.getElementById('delete-payment-id');
        const delProject = document.getElementById('delete-project');
        const delContractor = document.getElementById('delete-contractor');
        const delAmount = document.getElementById('delete-amount');

        if (delId) delId.textContent = id;
        if (delProject) delProject.textContent = project;
        if (delContractor) delContractor.textContent = contractor;
        if (delAmount) delAmount.textContent = amount;

        openModal(deleteModal);
      });
    });
  }

  function setText(id, val) {
    const el = document.getElementById(id);
    if (el) el.textContent = val;
  }

  function friendlyDate(mdyy) {
    // Transform 03/12/21 -> March 12, 2021
    const m = mdyy.match(/(\d{2})\/(\d{2})\/(\d{2,4})/);
    if (!m) return mdyy;
    const months = ['January','February','March','April','May','June','July','August','September','October','November','December'];
    const mm = parseInt(m[1], 10) - 1;
    const dd = parseInt(m[2], 10);
    const yyyy = parseInt(m[3].length === 2 ? ('20' + m[3]) : m[3], 10);
    return `${months[mm]} ${dd}, ${yyyy}`;
  }

  // Footer buttons in pending modal
  const approveBtn = document.getElementById('pp-approve');
  const rejectBtn = document.getElementById('pp-reject');
  const confirmApproveBtn = document.getElementById('confirmApproveBtn');
  const confirmRejectBtn = document.getElementById('confirmRejectBtn');

  approveBtn?.addEventListener('click', () => {
    // Build summary
    const id = els.id?.textContent || '#—';
    const project = els.project?.textContent || '—';
    const contractor = els.contractor?.textContent || '—';
    if (approveIdBadge) approveIdBadge.textContent = id;
    if (approveSummary) approveSummary.textContent = `${id} • ${project} • ${contractor}`;
    openModal(confirmApproveModal);
  });
  rejectBtn?.addEventListener('click', () => {
    const id = els.id?.textContent || '#—';
    const project = els.project?.textContent || '—';
    const contractor = els.contractor?.textContent || '—';
    if (rejectIdBadge) rejectIdBadge.textContent = id;
    if (rejectSummary) rejectSummary.textContent = `${id} • ${project} • ${contractor}`;
    openModal(confirmRejectModal);
  });

  // Confirm approval -> update UI + toast
  confirmApproveBtn?.addEventListener('click', () => {
    // Loading state
    const original = confirmApproveBtn.innerHTML;
    confirmApproveBtn.disabled = true;
    confirmApproveBtn.innerHTML = '<svg class="animate-spin -ml-1 mr-2 h-4 w-4 inline text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"></path></svg>Processing…';
    setTimeout(() => {
    if (currentRow) {
      const statusCell = currentRow.querySelectorAll('td')[6];
      statusCell.innerHTML = '<span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold bg-green-100 text-green-700 border border-green-200">Completed</span>';
    }
    closeModal(confirmApproveModal);
    closeModal(pendingModal);
    showToast('Proof of payment approved');
    confirmApproveBtn.disabled = false;
    confirmApproveBtn.innerHTML = original;
    }, 700);
  });

  // Confirm rejection -> update UI + toast
  confirmRejectBtn?.addEventListener('click', () => {
    const hasReasonField = !!rejectReason;
    const reason = (rejectReason?.value || '').trim();
    if (hasReasonField && reason.length < 8) {
      if (rejectReasonError) rejectReasonError.classList.remove('hidden');
      if (rejectReason) {
        rejectReason.classList.add('wiggle');
        setTimeout(() => rejectReason.classList.remove('wiggle'), 300);
      }
      return;
    }

    const original = confirmRejectBtn.innerHTML;
    confirmRejectBtn.disabled = true;
    confirmRejectBtn.innerHTML = '<svg class="animate-spin -ml-1 mr-2 h-4 w-4 inline text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"></path></svg>Processing…';
    setTimeout(() => {
    if (currentRow) {
      const statusCell = currentRow.querySelectorAll('td')[6];
      statusCell.innerHTML = '<span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold bg-red-100 text-red-700 border border-red-200">Invalid</span>';
    }
    closeModal(confirmRejectModal);
    closeModal(pendingModal);
    const msg = hasReasonField ? ('Rejected: ' + reason.substring(0, 40) + (reason.length > 40 ? '…' : '')) : 'Proof of payment rejected';
    showToast(msg);
    confirmRejectBtn.disabled = false;
    confirmRejectBtn.innerHTML = original;
    }, 700);
  });

  function showToast(message) {
    if (!toast) return;
    const card = toast.querySelector('.toast-card');
    card.textContent = message;
    toast.classList.remove('hidden');
    card.classList.remove('toast-hide');
    card.classList.add('toast-show');
    setTimeout(() => {
      card.classList.remove('toast-show');
      card.classList.add('toast-hide');
      setTimeout(() => toast.classList.add('hidden'), 250);
    }, 1800);
  }

  // Keyboard interactions: ESC to close top modal, Enter to confirm on confirmations
  document.addEventListener('keydown', (e) => {
    if (e.key === 'Escape') {
      [confirmApproveModal, confirmRejectModal, pendingModal, completedModal, invalidModal, editModal, deleteModal].forEach(m => m && !m.classList.contains('hidden') && closeModal(m));
    }
    if (e.key === 'Enter') {
      if (confirmApproveModal && !confirmApproveModal.classList.contains('hidden')) confirmApproveBtn?.click();
      if (confirmRejectModal && !confirmRejectModal.classList.contains('hidden')) confirmRejectBtn?.click();
    }
  });

  // Save edit button
  const saveEditBtn = document.getElementById('saveEditBtn');
  saveEditBtn?.addEventListener('click', () => {
    const original = saveEditBtn.innerHTML;
    saveEditBtn.disabled = true;
    saveEditBtn.innerHTML = '<svg class="animate-spin -ml-1 mr-2 h-4 w-4 inline text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"></path></svg>Saving...';
    setTimeout(() => {
      closeModal(editModal);
      showToast('Payment details updated successfully');
      saveEditBtn.disabled = false;
      saveEditBtn.innerHTML = original;
    }, 800);
  });

  // Delete modal buttons
  const cancelDeletePaymentBtn = document.getElementById('cancelDeletePaymentBtn');
  const confirmDeletePaymentBtn = document.getElementById('confirmDeletePaymentBtn');

  cancelDeletePaymentBtn?.addEventListener('click', () => {
    closeModal(deleteModal);
  });

  confirmDeletePaymentBtn?.addEventListener('click', () => {
    const original = confirmDeletePaymentBtn.innerHTML;
    confirmDeletePaymentBtn.disabled = true;
    confirmDeletePaymentBtn.innerHTML = '<svg class="animate-spin -ml-1 mr-2 h-4 w-4 inline text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"></path></svg>Deleting...';
    setTimeout(() => {
      closeModal(deleteModal);
      showToast('Payment record deleted successfully');
      confirmDeletePaymentBtn.disabled = false;
      confirmDeletePaymentBtn.innerHTML = original;
      // In production: remove the row from table here
    }, 900);
  });

  // Toggle handlers
  function toggleActive(el) { if (!el) return; el.classList.toggle('active'); el.setAttribute('aria-pressed', el.classList.contains('active') ? 'true' : 'false'); }
  // no extra toggles in simplified confirmation modals

  // Rejection reason counter + validation live update
  rejectReason?.addEventListener('input', () => {
    const val = rejectReason.value.slice(0, 200);
    if (rejectReason.value !== val) rejectReason.value = val;
    if (rejectReasonCounter) rejectReasonCounter.textContent = String(val.length);
    if (val.length >= 8) rejectReasonError?.classList.add('hidden');
  });
});
