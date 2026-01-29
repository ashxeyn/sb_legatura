(function(){
    const csrf = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
    const tableBody = document.getElementById('projectsTableBody');
    const apiBase = '/api/admin/projects';

    if (!tableBody) return;

    function formatDate(d){
        if(!d) return '-';
        try{ return new Date(d).toLocaleString(); }catch(e){return d}
    }

    function ownerName(p){
        return (p.owner && (p.owner.first_name || p.owner.last_name)) ? ((p.owner.first_name||'') + ' ' + (p.owner.last_name||'')).trim() : (p.owner_name || p.owner?.name || p.owner?.full_name || '—');
    }

    function projectId(p){
        return p.project_id ?? p.id ?? p.projectId ?? '#'+(p.id||'');
    }

    function verificationStatus(p){
        return p.owner?.verification_status || p.owner?.verificationStatus || p.verification_status || '—';
    }

    function progressStatus(p){
        return p.project_status || p.progressStatus || p.status || '—';
    }

    function renderRows(items){
        tableBody.innerHTML = '';
        if(!items || items.length === 0){
            tableBody.innerHTML = '<tr><td class="px-6 py-4" colspan="7">No projects found</td></tr>';
            return;
        }
        items.forEach(p => {
            const tr = document.createElement('tr');
            tr.className = 'hover:bg-gray-50';

            const owner = ownerName(p);
            const pid = projectId(p);
            const verify = verificationStatus(p);
            const prog = progressStatus(p);
            const created = formatDate(p.created_at || p.submitted_at || p.createdAt);
            const updated = formatDate(p.updated_at || p.updatedAt);

            tr.innerHTML = `
                <td class="px-6 py-4">
                    <div class="flex items-center gap-3">
                        <div class="w-8 h-8 rounded-full bg-indigo-100 text-indigo-700 flex items-center justify-center font-semibold">${(owner||'--').split(' ').map(n=>n[0]||'').slice(0,2).join('')}</div>
                        <div>
                            <div class="text-sm font-semibold">${owner}</div>
                        </div>
                    </div>
                </td>
                <td class="px-6 py-4">${pid}</td>
                <td class="px-6 py-4">${verify}</td>
                <td class="px-6 py-4">${prog}</td>
                <td class="px-6 py-4">${created}</td>
                <td class="px-6 py-4">${updated}</td>
                <td class="px-6 py-4 text-center">
                    <div class="flex items-center justify-center gap-2">
                        <button class="px-3 py-1 rounded-lg bg-indigo-600 text-white text-xs" data-action="view" data-id="${p.project_id || p.id}">View</button>
                        <button class="px-3 py-1 rounded-lg bg-rose-600 text-white text-xs" data-action="delete" data-id="${p.project_id || p.id}">Delete</button>
                    </div>
                </td>
            `;

            tableBody.appendChild(tr);
        });

        // attach handlers
        tableBody.querySelectorAll('button[data-action="view"]').forEach(btn=>{
            btn.addEventListener('click', function(){ showProjectModal(this.getAttribute('data-id')) });
        });
        tableBody.querySelectorAll('button[data-action="delete"]').forEach(btn=>{
            btn.addEventListener('click', function(){ deleteProject(this.getAttribute('data-id')) });
        });
    }

    async function loadProjects(){
        try{
            const res = await fetch(apiBase, { credentials: 'same-origin' });
            if(!res.ok){ throw new Error('API error'); }
            const data = await res.json();
            // Laravel paginator returns {data: [...], ...}
            const items = data.data || data;
            renderRows(items || []);
        }catch(e){
            console.error('Failed to load projects from API, falling back to static data', e);
            // leave demo data (existing JS file may populate) or show empty
            // If the other script populated demo data, leave it.
        }
    }

    async function showProjectModal(id){
        if(!id) return;
        try{
            const res = await fetch(`${apiBase}/${id}`, { credentials: 'same-origin' });
            if(!res.ok){ throw new Error('Failed to fetch project'); }
            const data = await res.json();
            const p = data.data || data;
            // populate modal fields (guarded)
            document.getElementById('modalOwnerName').textContent = ownerName(p);
            document.getElementById('modalProjectId').textContent = projectId(p);
            document.getElementById('modalSubmittedAt').textContent = formatDate(p.created_at || p.submitted_at || p.createdAt);
            const verBadge = document.getElementById('modalVerificationBadge');
            const progBadge = document.getElementById('modalProgressBadge');
            if(verBadge) { verBadge.textContent = verificationStatus(p); verBadge.className = verBadge.className; }
            if(progBadge) { progBadge.textContent = progressStatus(p); progBadge.className = progBadge.className; }

            document.getElementById('modalPropertyType').textContent = p.property_type || p.propertyType || '-';
            document.getElementById('modalAddress').textContent = p.project_location || p.address || '-';
            document.getElementById('modalLotSize').textContent = p.lot_size || '-';
            document.getElementById('modalTimeline').textContent = p.to_finish || p.timeline || '-';
            document.getElementById('modalBudget').textContent = (p.budget_range_min && p.budget_range_max) ? (`₱${Number(p.budget_range_min).toLocaleString()} - ₱${Number(p.budget_range_max).toLocaleString()}`) : '-';
            document.getElementById('modalDeadline').textContent = formatDate(p.bidding_deadline || p.deadline || p.biddingDeadline);
            document.getElementById('modalDescription').textContent = p.project_description || p.description || '-';

            // files
            const filesContainer = document.getElementById('modalFiles');
            if(filesContainer){
                filesContainer.innerHTML = '';
                const files = p.files || p.project_files || [];
                files.forEach(f=>{
                    const a = document.createElement('a');
                    const path = f.file_path || f.path || f;
                    a.href = path ? ('/storage/' + path) : '#';
                    a.target = '_blank';
                    a.className = 'inline-block px-3 py-1 text-xs rounded-lg bg-indigo-50 text-indigo-700';
                    a.textContent = f.file_type ? `${f.file_type}` : (f.name || 'File');
                    filesContainer.appendChild(a);
                });
            }

            // show modal
            const modal = document.getElementById('biddingDetailsModal');
            if(modal) modal.classList.remove('hidden');

        }catch(e){
            console.error('Failed to show project modal', e);
            alert('Failed to load project details');
        }
    }

    function hideProjectModal(){
        const modal = document.getElementById('biddingDetailsModal');
        if(modal) modal.classList.add('hidden');
    }

    window.hideBiddingModal = hideProjectModal; // keep compatibility with inline onclicks

    async function deleteProject(id){
        if(!confirm('Delete this project?')) return;
        try{
            const res = await fetch(`${apiBase}/${id}`, {
                method: 'DELETE',
                headers: { 'X-CSRF-TOKEN': csrf, 'Accept': 'application/json' },
                credentials: 'same-origin'
            });
            if(!res.ok){ throw new Error('Delete failed'); }
            const json = await res.json();
            if(json.success !== false){
                alert('Deleted');
                loadProjects();
            } else {
                alert(json.message || 'Delete failed');
            }
        }catch(e){
            console.error('Delete failed', e);
            alert('Delete failed');
        }
    }

    // Wire modal close buttons if present
    document.addEventListener('click', function(e){
        if(e.target.matches('[data-close-modal]') || e.target.closest('#biddingDetailsModal .fi-rr-cross-small')){
            hideProjectModal();
        }
    });

    // initial load
    loadProjects();

})();
