(function(){
    const csrf = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
    const tableBody = document.getElementById('paymentsTableBody') || document.querySelector('table tbody');
    const apiBase = '/api/admin/payments';

    if (!tableBody) return;

    function formatDate(d){
        if(!d) return '-';
        try{ return new Date(d).toLocaleString(); }catch(e){return d}
    }

    function paymentStatus(p){
        return p.payment_status || p.status || 'pending';
    }

    function renderRows(items){
        tableBody.innerHTML = '';
        if(!items || items.length === 0){
            tableBody.innerHTML = '<tr><td class="px-6 py-4" colspan="7">No payments found</td></tr>';
            return;
        }
        items.forEach(p => {
            const tr = document.createElement('tr');
            tr.className = 'hover:bg-gray-50 border-b';

            const amount = p.amount ? `₱${Number(p.amount).toLocaleString()}` : '-';
            const status = paymentStatus(p);
            const date = formatDate(p.transaction_date || p.transactionDate);
            const milestone = p.milestone_id || p.milestoneId || '-';

            const statusClass = status === 'approved' ? 'bg-green-100 text-green-800' : status === 'rejected' ? 'bg-rose-100 text-rose-800' : 'bg-yellow-100 text-yellow-800';

            tr.innerHTML = `
                <td class="px-6 py-4">${p.payment_id || p.id || '#'}</td>
                <td class="px-6 py-4">${milestone}</td>
                <td class="px-6 py-4">${amount}</td>
                <td class="px-6 py-4">${date}</td>
                <td class="px-6 py-4">
                    <span class="inline-block px-2 py-1 text-xs rounded-lg font-medium ${statusClass}">
                        ${status.charAt(0).toUpperCase() + status.slice(1)}
                    </span>
                </td>
                <td class="px-6 py-4 text-center">
                    <div class="flex items-center justify-center gap-2">
                        <button class="px-3 py-1 rounded-lg bg-indigo-600 text-white text-xs" data-action="view" data-id="${p.payment_id || p.id}">View</button>
                        <select class="px-2 py-1 rounded-lg border border-gray-300 text-xs" data-action="status" data-id="${p.payment_id || p.id}">
                            <option value="pending" ${status === 'pending' ? 'selected' : ''}>Pending</option>
                            <option value="approved" ${status === 'approved' ? 'selected' : ''}>Approve</option>
                            <option value="rejected" ${status === 'rejected' ? 'selected' : ''}>Reject</option>
                        </select>
                        <button class="px-3 py-1 rounded-lg bg-rose-600 text-white text-xs" data-action="delete" data-id="${p.payment_id || p.id}">Delete</button>
                    </div>
                </td>
            `;

            tableBody.appendChild(tr);
        });

        // attach handlers
        tableBody.querySelectorAll('button[data-action="view"]').forEach(btn=>{
            btn.addEventListener('click', function(){ showPaymentModal(this.getAttribute('data-id')) });
        });
        tableBody.querySelectorAll('select[data-action="status"]').forEach(sel=>{
            sel.addEventListener('change', function(){ updatePaymentStatus(this.getAttribute('data-id'), this.value) });
        });
        tableBody.querySelectorAll('button[data-action="delete"]').forEach(btn=>{
            btn.addEventListener('click', function(){ deletePayment(this.getAttribute('data-id')) });
        });
    }

    async function loadPayments(){
        try{
            const res = await fetch(apiBase, { credentials: 'same-origin' });
            if(!res.ok){ throw new Error('API error'); }
            const data = await res.json();
            const items = data.data || data;
            renderRows(items || []);
        }catch(e){
            console.error('Failed to load payments from API', e);
        }
    }

    async function showPaymentModal(id){
        if(!id) return;
        try{
            const res = await fetch(`${apiBase}/${id}`, { credentials: 'same-origin' });
            if(!res.ok){ throw new Error('Failed to fetch payment'); }
            const data = await res.json();
            const p = data.data || data;
            alert(`Payment ${p.payment_id || p.id}\nAmount: ₱${p.amount}\nStatus: ${paymentStatus(p)}\nDate: ${formatDate(p.transaction_date)}`);
        }catch(e){
            console.error('Failed to show payment modal', e);
            alert('Failed to load payment details');
        }
    }

    async function updatePaymentStatus(id, newStatus){
        if(!confirm(`Update payment status to ${newStatus}?`)) return;
        try{
            const res = await fetch(`${apiBase}/${id}`, {
                method: 'PATCH',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrf },
                body: JSON.stringify({ payment_status: newStatus }),
                credentials: 'same-origin'
            });
            if(!res.ok){ throw new Error('Update failed'); }
            alert('Updated');
            loadPayments();
        }catch(e){
            console.error('Update failed', e);
            alert('Update failed');
        }
    }

    async function deletePayment(id){
        if(!confirm('Delete this payment?')) return;
        try{
            const res = await fetch(`${apiBase}/${id}`, {
                method: 'DELETE',
                headers: { 'X-CSRF-TOKEN': csrf, 'Accept': 'application/json' },
                credentials: 'same-origin'
            });
            if(!res.ok){ throw new Error('Delete failed'); }
            alert('Deleted');
            loadPayments();
        }catch(e){
            console.error('Delete failed', e);
            alert('Delete failed');
        }
    }

    loadPayments();

})();
