(function(){
    const csrf = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
    const tableBody = document.getElementById('milestonesTableBody') || document.querySelector('table tbody');
    const apiBase = '/api/admin/milestones';

    if (!tableBody) return;

    function formatDate(d){
        if(!d) return '-';
        try{ return new Date(d).toLocaleString(); }catch(e){return d}
    }

    function milestoneStatus(m){
        return m.status || 'pending';
    }

    function renderRows(items){
        tableBody.innerHTML = '';
        if(!items || items.length === 0){
            tableBody.innerHTML = '<tr><td class="px-6 py-4" colspan="7">No milestones found</td></tr>';
            return;
        }
        items.forEach(m => {
            const tr = document.createElement('tr');
            tr.className = 'hover:bg-gray-50 border-b';

            const amount = m.amount ? `₱${Number(m.amount).toLocaleString()}` : '-';
            const status = milestoneStatus(m);
            const dueDate = formatDate(m.due_date || m.dueDate);
            const project = (m.project && m.project.project_title) ? m.project.project_title : (m.project_title || '-');

            const statusClass = status === 'completed' ? 'bg-green-100 text-green-800' : status === 'in_progress' ? 'bg-blue-100 text-blue-800' : status === 'cancelled' ? 'bg-rose-100 text-rose-800' : 'bg-yellow-100 text-yellow-800';

            tr.innerHTML = `
                <td class="px-6 py-4">${m.milestone_id || m.id || '#'}</td>
                <td class="px-6 py-4">${m.title || '-'}</td>
                <td class="px-6 py-4">${project}</td>
                <td class="px-6 py-4">${amount}</td>
                <td class="px-6 py-4">${dueDate}</td>
                <td class="px-6 py-4">
                    <span class="inline-block px-2 py-1 text-xs rounded-lg font-medium ${statusClass}">
                        ${status.charAt(0).toUpperCase() + status.slice(1)}
                    </span>
                </td>
                <td class="px-6 py-4 text-center">
                    <div class="flex items-center justify-center gap-2">
                        <button class="px-3 py-1 rounded-lg bg-indigo-600 text-white text-xs" data-action="view" data-id="${m.milestone_id || m.id}">View</button>
                        <select class="px-2 py-1 rounded-lg border border-gray-300 text-xs" data-action="status" data-id="${m.milestone_id || m.id}">
                            <option value="pending" ${status === 'pending' ? 'selected' : ''}>Pending</option>
                            <option value="in_progress" ${status === 'in_progress' ? 'selected' : ''}>In Progress</option>
                            <option value="completed" ${status === 'completed' ? 'selected' : ''}>Complete</option>
                            <option value="cancelled" ${status === 'cancelled' ? 'selected' : ''}>Cancel</option>
                        </select>
                        <button class="px-3 py-1 rounded-lg bg-rose-600 text-white text-xs" data-action="delete" data-id="${m.milestone_id || m.id}">Delete</button>
                    </div>
                </td>
            `;

            tableBody.appendChild(tr);
        });

        // attach handlers
        tableBody.querySelectorAll('button[data-action="view"]').forEach(btn=>{
            btn.addEventListener('click', function(){ showMilestoneModal(this.getAttribute('data-id')) });
        });
        tableBody.querySelectorAll('select[data-action="status"]').forEach(sel=>{
            sel.addEventListener('change', function(){ updateMilestoneStatus(this.getAttribute('data-id'), this.value) });
        });
        tableBody.querySelectorAll('button[data-action="delete"]').forEach(btn=>{
            btn.addEventListener('click', function(){ deleteMilestone(this.getAttribute('data-id')) });
        });
    }

    async function loadMilestones(){
        try{
            const res = await fetch(apiBase, { credentials: 'same-origin' });
            if(!res.ok){ throw new Error('API error'); }
            const data = await res.json();
            const items = data.data || data;
            renderRows(items || []);
        }catch(e){
            console.error('Failed to load milestones from API', e);
        }
    }

    async function showMilestoneModal(id){
        if(!id) return;
        try{
            const res = await fetch(`${apiBase}/${id}`, { credentials: 'same-origin' });
            if(!res.ok){ throw new Error('Failed to fetch milestone'); }
            const data = await res.json();
            const m = data.data || data;
            alert(`Milestone ${m.milestone_id || m.id}\nTitle: ${m.title}\nAmount: ₱${m.amount}\nStatus: ${milestoneStatus(m)}\nDue: ${formatDate(m.due_date)}`);
        }catch(e){
            console.error('Failed to show milestone modal', e);
            alert('Failed to load milestone details');
        }
    }

    async function updateMilestoneStatus(id, newStatus){
        if(!confirm(`Update milestone status to ${newStatus}?`)) return;
        try{
            const res = await fetch(`${apiBase}/${id}`, {
                method: 'PATCH',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrf },
                body: JSON.stringify({ status: newStatus }),
                credentials: 'same-origin'
            });
            if(!res.ok){ throw new Error('Update failed'); }
            alert('Updated');
            loadMilestones();
        }catch(e){
            console.error('Update failed', e);
            alert('Update failed');
        }
    }

    async function deleteMilestone(id){
        if(!confirm('Delete this milestone?')) return;
        try{
            const res = await fetch(`${apiBase}/${id}`, {
                method: 'DELETE',
                headers: { 'X-CSRF-TOKEN': csrf, 'Accept': 'application/json' },
                credentials: 'same-origin'
            });
            if(!res.ok){ throw new Error('Delete failed'); }
            alert('Deleted');
            loadMilestones();
        }catch(e){
            console.error('Delete failed', e);
            alert('Delete failed');
        }
    }

    loadMilestones();

})();
