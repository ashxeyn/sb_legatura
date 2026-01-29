(function(){
    const csrf = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
    const tableBody = document.getElementById('bidsTableBody') || document.querySelector('table tbody');
    const apiBase = '/api/admin/bids';

    if (!tableBody) return;

    function formatDate(d){
        if(!d) return '-';
        try{ return new Date(d).toLocaleString(); }catch(e){return d}
    }

    function contractorName(b){
        return (b.contractor && (b.contractor.company_name || b.contractor.name)) ? (b.contractor.company_name || b.contractor.name) : (b.contractor_name || b.companyName || '—');
    }

    function projectTitle(b){
        return (b.project && b.project.project_title) ? b.project.project_title : (b.project_title || b.projectTitle || '—');
    }

    function bidStatus(b){
        return b.bid_status || b.status || 'pending';
    }

    function renderRows(items){
        tableBody.innerHTML = '';
        if(!items || items.length === 0){
            tableBody.innerHTML = '<tr><td class="px-6 py-4" colspan="7">No bids found</td></tr>';
            return;
        }
        items.forEach(b => {
            const tr = document.createElement('tr');
            tr.className = 'hover:bg-gray-50 border-b';

            const contractor = contractorName(b);
            const project = projectTitle(b);
            const amount = b.proposed_cost ? `₱${Number(b.proposed_cost).toLocaleString()}` : '-';
            const status = bidStatus(b);
            const submitted = formatDate(b.submitted_at || b.submittedAt);
            const created = formatDate(b.created_at || b.createdAt);

            const statusClass = status === 'accepted' ? 'bg-green-100 text-green-800' : status === 'rejected' ? 'bg-rose-100 text-rose-800' : 'bg-yellow-100 text-yellow-800';

            tr.innerHTML = `
                <td class="px-6 py-4">${bid.bid_id || b.id || '#'}</td>
                <td class="px-6 py-4">${project}</td>
                <td class="px-6 py-4">${contractor}</td>
                <td class="px-6 py-4">${amount}</td>
                <td class="px-6 py-4">
                    <span class="inline-block px-2 py-1 text-xs rounded-lg font-medium ${statusClass}">
                        ${status.charAt(0).toUpperCase() + status.slice(1)}
                    </span>
                </td>
                <td class="px-6 py-4">${submitted}</td>
                <td class="px-6 py-4 text-center">
                    <div class="flex items-center justify-center gap-2">
                        <button class="px-3 py-1 rounded-lg bg-indigo-600 text-white text-xs" data-action="view" data-id="${b.bid_id || b.id}">View</button>
                        <select class="px-2 py-1 rounded-lg border border-gray-300 text-xs" data-action="status" data-id="${b.bid_id || b.id}">
                            <option value="pending" ${status === 'pending' ? 'selected' : ''}>Pending</option>
                            <option value="accepted" ${status === 'accepted' ? 'selected' : ''}>Accept</option>
                            <option value="rejected" ${status === 'rejected' ? 'selected' : ''}>Reject</option>
                        </select>
                        <button class="px-3 py-1 rounded-lg bg-rose-600 text-white text-xs" data-action="delete" data-id="${b.bid_id || b.id}">Delete</button>
                    </div>
                </td>
            `;

            tableBody.appendChild(tr);
        });

        // attach handlers
        tableBody.querySelectorAll('button[data-action="view"]').forEach(btn=>{
            btn.addEventListener('click', function(){ showBidModal(this.getAttribute('data-id')) });
        });
        tableBody.querySelectorAll('select[data-action="status"]').forEach(sel=>{
            sel.addEventListener('change', function(){ updateBidStatus(this.getAttribute('data-id'), this.value) });
        });
        tableBody.querySelectorAll('button[data-action="delete"]').forEach(btn=>{
            btn.addEventListener('click', function(){ deleteBid(this.getAttribute('data-id')) });
        });
    }

    function renderHistoryTables(items){
        const approvedTbody = document.getElementById('approvedBidHistoryTable');
        const rejectedTbody = document.getElementById('rejectedBidHistoryTable');
        if(!approvedTbody && !rejectedTbody) return;

        const makeRow = (b) => {
            const id = b.bid_id || b.id || '#';
            const project = projectTitle(b);
            const contractor = contractorName(b);
            const amount = b.proposed_cost ? `₱${Number(b.proposed_cost).toLocaleString()}` : '-';
            const date = formatDate(b.submitted_at || b.submittedAt || b.created_at || b.createdAt);
            return `
                <tr class="hover:bg-gray-50 border-b">
                    <td class="px-4 py-2 text-sm">${id}</td>
                    <td class="px-4 py-2 text-sm">${project}</td>
                    <td class="px-4 py-2 text-sm">${contractor}</td>
                    <td class="px-4 py-2 text-sm">${amount}</td>
                    <td class="px-4 py-2 text-sm">${date}</td>
                </tr>
            `;
        };

        const approved = (items || []).filter(b => (b.bid_status || b.status || '').toString().toLowerCase() === 'accepted');
        const rejected = (items || []).filter(b => (b.bid_status || b.status || '').toString().toLowerCase() === 'rejected');

        if(approvedTbody){
            if(approved.length === 0){
                approvedTbody.innerHTML = '<tr><td class="px-4 py-2" colspan="5">No approved bids</td></tr>';
            }else{
                approvedTbody.innerHTML = approved.map(makeRow).join('');
            }
        }

        if(rejectedTbody){
            if(rejected.length === 0){
                rejectedTbody.innerHTML = '<tr><td class="px-4 py-2" colspan="5">No rejected bids</td></tr>';
            }else{
                rejectedTbody.innerHTML = rejected.map(makeRow).join('');
            }
        }
    }

    async function loadBids(){
        try{
            const res = await fetch(apiBase, { credentials: 'same-origin' });
            if(!res.ok){ throw new Error('API error'); }
            const data = await res.json();
            const items = data.data || data;
            renderRows(items || []);
            renderHistoryTables(items || []);
        }catch(e){
            console.error('Failed to load bids from API', e);
        }
    }

    async function showBidModal(id){
        if(!id) return;
        try{
            const res = await fetch(`${apiBase}/${id}`, { credentials: 'same-origin' });
            if(!res.ok){ throw new Error('Failed to fetch bid'); }
            const data = await res.json();
            const b = data.data || data;
            // populate modal if present
            const modal = document.querySelector('[data-bid-modal]') || document.getElementById('bidDetailsModal');
            if(modal){
                alert(`Bid ${b.bid_id || b.id}\nContractor: ${contractorName(b)}\nAmount: ₱${b.proposed_cost}\nStatus: ${bidStatus(b)}`);
            }
        }catch(e){
            console.error('Failed to show bid modal', e);
            alert('Failed to load bid details');
        }
    }

    async function updateBidStatus(id, newStatus){
        if(!confirm(`Update bid status to ${newStatus}?`)) return;
        try{
            const res = await fetch(`${apiBase}/${id}`, {
                method: 'PATCH',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrf },
                body: JSON.stringify({ bid_status: newStatus }),
                credentials: 'same-origin'
            });
            if(!res.ok){ throw new Error('Update failed'); }
            const json = await res.json();
            alert('Updated');
            loadBids();
        }catch(e){
            console.error('Update failed', e);
            alert('Update failed');
        }
    }

    async function deleteBid(id){
        if(!confirm('Delete this bid?')) return;
        try{
            const res = await fetch(`${apiBase}/${id}`, {
                method: 'DELETE',
                headers: { 'X-CSRF-TOKEN': csrf, 'Accept': 'application/json' },
                credentials: 'same-origin'
            });
            if(!res.ok){ throw new Error('Delete failed'); }
            alert('Deleted');
            loadBids();
        }catch(e){
            console.error('Delete failed', e);
            alert('Delete failed');
        }
    }

    loadBids();

})();
