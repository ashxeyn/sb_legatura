/**
 * Admin Payment History Modal
 * Shows detailed payment history with carry-forward amounts and all payment information
 */

window.openAdminPaymentHistoryModal = function(projectId, projectTitle) {
  const modal = document.getElementById('adminPaymentHistoryModal');
  if (!modal) return;

  // Set project title
  const titleEl = document.getElementById('paymentHistoryProjectTitle');
  if (titleEl) titleEl.textContent = projectTitle || 'Project Payment Details';

  // Fetch payment data
  fetchPaymentHistory(projectId);

  // Show modal
  modal.classList.remove('hidden');
  document.body.style.overflow = 'hidden';
};

window.closeAdminPaymentHistoryModal = function() {
  const modal = document.getElementById('adminPaymentHistoryModal');
  if (!modal) return;

  modal.classList.add('hidden');
  document.body.style.overflow = '';
};

async function fetchPaymentHistory(projectId) {
  try {
    const response = await fetch(`/admin/projects/${projectId}/payment-history`);
    const data = await response.json();

    console.log('Payment history response:', data);

    if (data.success) {
      renderPaymentHistory(data.data);
    } else {
      console.error('Payment history error:', data.message);
      showError(data.message || 'Failed to load payment history');
    }
  } catch (error) {
    console.error('Error fetching payment history:', error);
    showError('Error loading payment history');
  }
}

function renderPaymentHistory(data) {
  // Update summary cards
  document.getElementById('paymentHistoryTotalCost').textContent = formatCurrency(data.total_cost || 0);
  document.getElementById('paymentHistoryTotalPaid').textContent = formatCurrency(data.total_paid || 0);
  document.getElementById('paymentHistoryRemaining').textContent = formatCurrency(data.remaining_balance || 0);

  const listEl = document.getElementById('paymentHistoryList');
  const emptyEl = document.getElementById('paymentHistoryEmpty');
  
  if (!data.payments || data.payments.length === 0) {
    listEl.innerHTML = '';
    emptyEl.classList.remove('hidden');
    return;
  }

  emptyEl.classList.add('hidden');

  // Render payment items
  listEl.innerHTML = data.payments.map(payment => {
    const statusConfig = getStatusConfig(payment.payment_status);
    const hasCarryForward = payment.carry_forward_amount && parseFloat(payment.carry_forward_amount) > 0;
    
    return `
      <div class="bg-white border border-gray-200 rounded-xl p-4 hover:shadow-md transition-all duration-200">
        <div class="flex items-start justify-between mb-3">
          <div class="flex-1">
            <div class="flex items-center gap-2 mb-1">
              <span class="inline-block px-2.5 py-1 rounded-lg text-xs font-bold ${statusConfig.bgClass} ${statusConfig.textClass}">
                ${statusConfig.label}
              </span>
              ${payment.payment_type ? `
                <span class="inline-block px-2 py-0.5 bg-gray-100 text-gray-700 text-xs font-semibold rounded">
                  ${escapeHtml(payment.payment_type)}
                </span>
              ` : ''}
            </div>
            <h5 class="font-bold text-gray-900 text-sm">
              ${escapeHtml(payment.milestone_title || 'Payment')}
            </h5>
            <p class="text-xs text-gray-600 mt-0.5">
              ${payment.milestone_period || 'N/A'}
            </p>
          </div>
          <div class="text-right">
            <p class="text-lg font-bold text-indigo-600">
              ${formatCurrency(payment.amount || 0)}
            </p>
            <p class="text-xs text-gray-500">
              ${formatDate(payment.transaction_date)}
            </p>
          </div>
        </div>

        <!-- Payment Details Grid -->
        <div class="grid grid-cols-2 gap-3 pt-3 border-t border-gray-100">
          <div>
            <p class="text-xs text-gray-600 mb-0.5">Milestone Cost</p>
            <p class="text-sm font-semibold text-gray-900">${formatCurrency(payment.milestone_cost || 0)}</p>
          </div>
          <div>
            <p class="text-xs text-gray-600 mb-0.5">Amount Paid</p>
            <p class="text-sm font-semibold text-green-600">${formatCurrency(payment.total_paid || 0)}</p>
          </div>
          ${hasCarryForward ? `
            <div class="col-span-2">
              <div class="bg-blue-50 border border-blue-200 rounded-lg p-2">
                <div class="flex items-center justify-between">
                  <div class="flex items-center gap-2">
                    <svg class="w-4 h-4 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    <span class="text-xs font-semibold text-blue-700">Carry Forward</span>
                  </div>
                  <span class="text-sm font-bold text-blue-900">+${formatCurrency(payment.carry_forward_amount)}</span>
                </div>
              </div>
            </div>
          ` : ''}
          <div>
            <p class="text-xs text-gray-600 mb-0.5">Balance</p>
            <p class="text-sm font-semibold ${parseFloat(payment.balance || 0) > 0 ? 'text-amber-600' : 'text-gray-900'}">
              ${formatCurrency(payment.balance || 0)}
            </p>
          </div>
          ${payment.transaction_number ? `
            <div>
              <p class="text-xs text-gray-600 mb-0.5">Transaction #</p>
              <p class="text-sm font-mono text-gray-900">${escapeHtml(payment.transaction_number)}</p>
            </div>
          ` : ''}
        </div>

        ${payment.reason ? `
          <div class="mt-3 pt-3 border-t border-gray-100">
            <p class="text-xs text-gray-600 mb-1">Reason/Notes</p>
            <p class="text-sm text-gray-700">${escapeHtml(payment.reason)}</p>
          </div>
        ` : ''}
      </div>
    `;
  }).join('');
}

function getStatusConfig(status) {
  const configs = {
    'approved': {
      label: 'Approved',
      bgClass: 'bg-green-100',
      textClass: 'text-green-700'
    },
    'pending': {
      label: 'Pending',
      bgClass: 'bg-amber-100',
      textClass: 'text-amber-700'
    },
    'rejected': {
      label: 'Rejected',
      bgClass: 'bg-red-100',
      textClass: 'text-red-700'
    }
  };
  return configs[status] || configs['pending'];
}

function formatCurrency(amount) {
  const num = parseFloat(amount) || 0;
  return '₱' + num.toLocaleString('en-PH', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
}

function formatDate(dateString) {
  if (!dateString) return 'N/A';
  const date = new Date(dateString);
  return date.toLocaleDateString('en-US', { year: 'numeric', month: 'short', day: 'numeric' });
}

function escapeHtml(text) {
  if (!text) return '';
  const div = document.createElement('div');
  div.textContent = text;
  return div.innerHTML;
}

function showError(message) {
  const listEl = document.getElementById('paymentHistoryList');
  const emptyEl = document.getElementById('paymentHistoryEmpty');
  
  if (listEl) listEl.innerHTML = '';
  if (emptyEl) {
    emptyEl.classList.remove('hidden');
    emptyEl.querySelector('p').textContent = message;
  }
}

// Close modal on Escape key
document.addEventListener('keydown', (e) => {
  if (e.key === 'Escape') {
    closeAdminPaymentHistoryModal();
  }
});
