// Simple tab switching
(function() {
	const tabContractors = document.getElementById('tabContractors');
	const tabOwners = document.getElementById('tabOwners');
	const contractorsWrap = document.getElementById('contractorsTableWrap');
	const ownersWrap = document.getElementById('ownersTableWrap');

	function activate(tab) {
		if (tab === 'contractors') {
			tabContractors?.classList.add('text-orange-600','border-orange-500');
			tabContractors?.classList.remove('text-gray-600','border-transparent');
			tabOwners?.classList.remove('text-orange-600','border-orange-500');
			tabOwners?.classList.add('text-gray-600');
			contractorsWrap?.classList.remove('hidden');
			ownersWrap?.classList.add('hidden');
		} else {
			tabOwners?.classList.add('text-orange-600','border-orange-500');
			tabOwners?.classList.remove('text-gray-600','border-transparent');
			tabContractors?.classList.remove('text-orange-600','border-orange-500');
			tabContractors?.classList.add('text-gray-600');
			ownersWrap?.classList.remove('hidden');
			contractorsWrap?.classList.add('hidden');
		}
	}

	tabContractors?.addEventListener('click', () => activate('contractors'));
	tabOwners?.addEventListener('click', () => activate('owners'));
})();

// Verification Modal Logic
(function() {
    let currentUserId = null;

    // Helper functions
    function setText(id, value) {
        const el = document.getElementById(id);
        if (el) el.textContent = value || 'N/A';
    }

    function setLink(id, path, filename) {
        const el = document.getElementById(id);
        if (el) {
            if (path) {
                el.href = `/storage/${path}`;
                // Update the filename text (second child, index 1, or find span)
                const spans = el.querySelectorAll('span');
                if (spans.length > 0) spans[0].textContent = filename || path.split('/').pop();
                // Update size if available (optional, or hide it)
                if (spans.length > 1) spans[1].textContent = '';

                el.classList.remove('hidden');
                el.classList.remove('pointer-events-none', 'opacity-50');
            } else {
                el.href = '#';
                el.classList.add('pointer-events-none', 'opacity-50');
                const spans = el.querySelectorAll('span');
                if (spans.length > 0) spans[0].textContent = 'Not Uploaded';
            }
        }
    }

    function setInitials(name) {
        return (name||'').split(/\s+/).slice(0,2).map(s=>s[0]?.toUpperCase()||'').join('');
    }

    function calculateAge(birthdate) {
        if (!birthdate) return 'N/A';
        const dob = new Date(birthdate);
        const diff_ms = Date.now() - dob.getTime();
        const age_dt = new Date(diff_ms);
        return Math.abs(age_dt.getUTCFullYear() - 1970);
    }

    // Modal Elements
    const contractorModal = document.getElementById('contractorVerificationModal');
    const ownerModal = document.getElementById('ownerVerificationModal');
    const acceptModal = document.getElementById('acceptConfirmModal');
    const rejectModal = document.getElementById('rejectConfirmModal');

    // Close Buttons
    document.getElementById('vrCloseBtn')?.addEventListener('click', () => toggleModal(contractorModal, false));
    document.getElementById('poCloseBtn')?.addEventListener('click', () => toggleModal(ownerModal, false));
    document.getElementById('acceptCancelBtn')?.addEventListener('click', () => toggleModal(acceptModal, false));
    document.getElementById('rejectCancelBtn')?.addEventListener('click', () => toggleModal(rejectModal, false));

    // Open Modal Logic
    function toggleModal(modal, show) {
        if (!modal) return;
        const panel = modal.querySelector('div[class*="panel"]');
        if (show) {
            modal.classList.remove('hidden');
            modal.classList.add('flex');
            setTimeout(() => {
                panel?.classList.remove('scale-95', 'opacity-0');
                panel?.classList.add('scale-100', 'opacity-100');
            }, 10);
        } else {
            panel?.classList.remove('scale-100', 'opacity-100');
            panel?.classList.add('scale-95', 'opacity-0');
            setTimeout(() => {
                modal.classList.add('hidden');
                modal.classList.remove('flex');
            }, 200);
        }
    }

    // Fetch and Populate Data
    async function openVerificationModal(id, type) {
        currentUserId = id;
        try {
            const response = await fetch(`/api/admin/users/verification-requests/${id}`);
            if (!response.ok) throw new Error('Failed to fetch details');
            const data = await response.json();
            console.log('Verification Request Data:', data); // Debug log

            const { user, profile } = data;

            if (type === 'contractor') {
                populateContractorModal(user, profile);
                toggleModal(contractorModal, true);
            } else {
                populateOwnerModal(user, profile);
                toggleModal(ownerModal, true);
            }
        } catch (error) {
            console.error(error);
            alert('Error loading details. Please try again.');
        }
    }

    function populateContractorModal(user, profile) {
        setText('vrCompanyName', profile.company_name || user.username);
        setText('vrCompanyContact', `${user.email} • ${profile.company_phone || 'N/A'}`);
        setText('vrCompanyInitials', setInitials(profile.company_name || user.username));

        // Representative section removed from view
        // setText('vrRepName', `${user.first_name} ${user.last_name}`);
        // setText('vrRepRole', profile.position || 'Representative');
        // setText('vrRepContact', user.phone_number);
        // setText('vrRepEmail', user.email);
        // setText('vrRepInitials', setInitials(`${user.first_name} ${user.last_name}`));

        setText('vrContractorType', profile.contractor_type);
        setText('vrYears', profile.experience_years);
        setText('vrServices', profile.services_offered);

        setText('vrPcabNo', profile.pcab_license_number);
        setText('vrPcabCategory', profile.pcab_category);
        setText('vrPcabExp', profile.pcab_validity);
        setText('vrBpExp', profile.business_permit_validity);
        setText('vrTin', profile.tin_number);
        setText('vrBpNo', profile.business_permit_number);
        setText('vrBpCity', profile.business_permit_city);
    }

    function populateOwnerModal(user, profile) {
        if (!profile) {
            console.error('Profile data is missing for property owner');
            // Clear fields or show error
            setText('poFullName', 'N/A');
            setText('poContactLine', 'N/A');
            return;
        }
        setText('poFullName', `${profile.first_name} ${profile.last_name}`);
        setText('poContactLine', `${user.email} • ${profile.phone_number || 'N/A'}`);
        setText('poInitials', setInitials(`${profile.first_name} ${profile.last_name}`));

        setText('poUsername', user.username);
        setText('poEmail', user.email);

        setText('poOccupation', profile.occupation || profile.occupation_other || 'N/A');
        setText('poDob', profile.birthdate || profile.date_of_birth || 'N/A');
        setText('poAge', calculateAge(profile.birthdate || profile.date_of_birth));
        setText('poAddress', profile.address || `${profile.city || ''}, ${profile.province || ''}`);

        setText('poValidIdType', profile.valid_id_type || 'N/A');
        // setText('poValidIdNumber', profile.valid_id_number || 'N/A'); // Removed as per request

        setLink('poValidIdPhoto', profile.valid_id_photo, 'Front.jpg');
        setLink('poValidIdBackPhoto', profile.valid_id_back_photo, 'Back.jpg');
        setLink('poPoliceClearance', profile.police_clearance, 'Police Clearance');
    }

    // Event Listeners for View Buttons
    document.addEventListener('click', function(e) {
        const btn = e.target.closest('.vr-view-btn, .po-view-btn');
        if (btn) {
            const id = btn.dataset.key;
            const type = btn.classList.contains('vr-view-btn') ? 'contractor' : 'property_owner';
            openVerificationModal(id, type);
        }
    });

    // Accept/Reject Logic
    const acceptBtns = [document.getElementById('vrAcceptBtn'), document.getElementById('poAcceptBtn')];
    const rejectBtns = [document.getElementById('vrRejectBtn'), document.getElementById('poRejectBtn')];

    acceptBtns.forEach(btn => btn?.addEventListener('click', () => {
        toggleModal(contractorModal, false);
        toggleModal(ownerModal, false);
        toggleModal(acceptModal, true);
    }));

    rejectBtns.forEach(btn => btn?.addEventListener('click', () => {
        toggleModal(contractorModal, false);
        toggleModal(ownerModal, false);
        toggleModal(rejectModal, true);
    }));

    // Confirm Actions
    document.getElementById('acceptConfirmBtn')?.addEventListener('click', async () => {
        if (!currentUserId) return;
        try {
            const response = await fetch(`/api/admin/users/verification-requests/${currentUserId}/approve`, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '',
                    'Content-Type': 'application/json',
                    'Accept': 'application/json'
                }
            });

            if (response.ok) {
                // Remove row from DOM
                const btn = document.querySelector(`button[data-key="${currentUserId}"]`);
                if (btn) {
                    const row = btn.closest('tr');
                    if (row) {
                        row.style.transition = 'opacity 0.5s';
                        row.style.opacity = '0';
                        setTimeout(() => row.remove(), 500);
                    }
                }
                toggleModal(acceptModal, false);
                // Optional: Show success toast
            } else {
                const err = await response.json();
                alert(err.message || 'Failed to approve user.');
            }
        } catch (error) {
            console.error(error);
            alert('An error occurred.');
        }
    });

    document.getElementById('rejectConfirmBtn')?.addEventListener('click', async () => {
        if (!currentUserId) return;
        const reason = document.getElementById('rejectReasonInput').value;
        if (!reason) {
            document.getElementById('rejectReasonError').classList.remove('hidden');
            return;
        }

        try {
            const response = await fetch(`/api/admin/users/verification-requests/${currentUserId}/reject`, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '',
                    'Content-Type': 'application/json',
                    'Accept': 'application/json'
                },
                body: JSON.stringify({ reason })
            });

            if (response.ok) {
                // Remove row from DOM
                const btn = document.querySelector(`button[data-key="${currentUserId}"]`);
                if (btn) {
                    const row = btn.closest('tr');
                    if (row) {
                        row.style.transition = 'opacity 0.5s';
                        row.style.opacity = '0';
                        setTimeout(() => row.remove(), 500);
                    }
                }
                toggleModal(rejectModal, false);
                // Clear input
                document.getElementById('rejectReasonInput').value = '';
            } else {
                const err = await response.json();
                alert(err.message || 'Failed to reject user.');
            }
        } catch (error) {
            console.error(error);
            alert('An error occurred.');
        }
    });

// Contractor verification modal interactions
(function() {
	const modal = document.getElementById('contractorVerificationModal');
	const panel = modal?.querySelector('.modal-panel');
	const openButtons = document.querySelectorAll('.vr-view-btn');
	const closeBtn = document.getElementById('vrCloseBtn');
	const acceptBtn = document.getElementById('vrAcceptBtn');
	const rejectBtn = document.getElementById('vrRejectBtn');

	// Confirm modals elements
	const acceptModal = document.getElementById('acceptConfirmModal');
	const acceptPanel = acceptModal?.querySelector('.accept-panel');
	const acceptCancelBtn = document.getElementById('acceptCancelBtn');
	const acceptConfirmBtn = document.getElementById('acceptConfirmBtn');

	const rejectModal = document.getElementById('rejectConfirmModal');
	const rejectPanel = rejectModal?.querySelector('.reject-panel');
	const rejectCancelBtn = document.getElementById('rejectCancelBtn');
	const rejectConfirmBtn = document.getElementById('rejectConfirmBtn');
	const rejectReasonInput = document.getElementById('rejectReasonInput');
	const rejectReasonError = document.getElementById('rejectReasonError');
	let currentRow = null;

	const data = {
		gth: {
			company: { name: 'GTH Builders and Developers', email: 'gth@builders.com', phone: '+63 912 345 1111' },
			rep: { name: 'Olive Faith Padios', role: 'Secretary', email: 'olive@gth.com', contact: '+63 912 345 6789', tel: '081 234 5678' },
			personal: { type: 'General Contractor', years: '2010', services: 'Residential & commercial builds, renovations, project management.' },
			compliance: { pcabNo: 'GTH-2025-12345', pcabCat: 'Category B', pcabExp: 'Aug 15, 2026', bpExp: 'Dec 31, 2025', tin: '123-456-789-000', bpNo: 'BP-2025-1001', bpCity: 'Zamboanga City', dti: '#' }
		},
		cab: {
			company: { name: 'Cabanating Architects Design & Construction', email: 'info@cadc.com', phone: '+63 918 765 2222' },
			rep: { name: 'Shane Hart Jimenez', role: 'Project Manager', email: 'shane@cadc.com', contact: '+63 918 765 4321', tel: '082 456 7890' },
			personal: { type: 'Design & Build', years: '2015', services: 'Architectural design, design-build delivery, interior fit-outs.' },
			compliance: { pcabNo: 'CADC-2025-67890', pcabCat: 'Category C', pcabExp: 'Jun 30, 2026', bpExp: 'Dec 31, 2025', tin: '987-654-321-000', bpNo: 'BP-2025-2002', bpCity: 'Zamboanga City', dti: '#' }
		},
		rcdg: {
			company: { name: 'RCDG Construction Corporation', email: 'hello@rcdg.com', phone: '+63 917 234 3333' },
			rep: { name: 'Carlos Rivera Lopez', role: 'Safety Officer', email: 'carlos@rcdg.com', contact: '+63 917 234 5678', tel: '083 987 6543' },
			personal: { type: 'General Contractor', years: '2001', services: 'Large-scale commercial projects, warehouses, facilities management.' },
			compliance: { pcabNo: 'RCDG-2025-54321', pcabCat: 'Category A', pcabExp: 'Nov 10, 2027', bpExp: 'Dec 31, 2025', tin: '555-222-111-000', bpNo: 'BP-2025-3003', bpCity: 'Zamboanga City', dti: '#' }
		}
	};

	function setText(id, value) { const el = document.getElementById(id); if (el) el.textContent = value; }
	function setInitials(name) { return (name||'').split(/\s+/).slice(0,2).map(s=>s[0]?.toUpperCase()||'').join(''); }

	function open(key, row) {
		const d = data[key];
		if (!d) return;
		currentRow = row;
		// Company
		setText('vrCompanyName', d.company.name);
		setText('vrCompanyContact', `${d.company.email} • ${d.company.phone}`);
		setText('vrCompanyInitials', setInitials(d.company.name));
		// Representative
		setText('vrRepName', d.rep.name);
		setText('vrRepRole', d.rep.role);
		setText('vrRepContact', d.rep.contact);
		setText('vrRepEmail', d.rep.email);
		setText('vrRepTel', d.rep.tel);
		const repInit = document.getElementById('vrRepInitials'); if (repInit) repInit.textContent = setInitials(d.rep.name);
		// Personal
		setText('vrContractorType', d.personal.type);
		setText('vrYears', d.personal.years);
		setText('vrServices', d.personal.services);
		// Compliance
		setText('vrPcabNo', d.compliance.pcabNo);
		setText('vrPcabCategory', d.compliance.pcabCat);
		setText('vrPcabExp', d.compliance.pcabExp);
		setText('vrBpExp', d.compliance.bpExp);
		setText('vrTin', d.compliance.tin);
		setText('vrPcabNo2', d.compliance.pcabNo);
		setText('vrBpNo', d.compliance.bpNo);
		setText('vrBpCity', d.compliance.bpCity);
		const link = document.getElementById('vrDtiFile'); if (link) link.href = d.compliance.dti || '#';

		// Show modal
		modal?.classList.remove('hidden');
		requestAnimationFrame(()=>{
			modal?.classList.add('flex');
			panel?.classList.remove('scale-95','opacity-0');
			panel?.classList.add('scale-100','opacity-100');
		});
	}

	function close() {
		if (!modal) return;
		panel?.classList.add('scale-95');
		panel?.classList.remove('scale-100');
		panel?.classList.add('opacity-0');
		setTimeout(()=>{ modal.classList.add('hidden'); modal.classList.remove('flex'); }, 150);
	}

	openButtons.forEach(btn => btn.addEventListener('click', (e)=>{
		const key = e.currentTarget.getAttribute('data-key');
		const row = e.currentTarget.closest('tr');
		open(key, row);
	}));

	closeBtn?.addEventListener('click', close);
	modal?.addEventListener('click', (e)=>{ if (e.target === modal) close(); });

	function updateStatus(row, label, color) {
		const pill = row?.querySelector('td:nth-child(4) span');
		if (!pill) return;
		pill.textContent = label;
		pill.className = 'inline-flex items-center px-3 py-1 rounded-full text-xs font-medium ' + color;
	}

	// Helpers to open/close small confirm modals
	function openSmall(modalEl, panelEl){
		modalEl.classList.remove('hidden');
		requestAnimationFrame(()=>{
			modalEl.classList.add('flex');
			panelEl.classList.remove('scale-95','opacity-0');
			panelEl.classList.add('scale-100','opacity-100');
		});
	}
	function closeSmall(modalEl, panelEl){
		panelEl.classList.add('scale-95');
		panelEl.classList.remove('scale-100');
		panelEl.classList.add('opacity-0');
		setTimeout(()=>{ modalEl.classList.add('hidden'); modalEl.classList.remove('flex'); }, 150);
	}

	// Open confirm modals from main buttons
	acceptBtn?.addEventListener('click', ()=>{ if(currentRow){ openSmall(acceptModal, acceptPanel); } });
	rejectBtn?.addEventListener('click', ()=>{ if(currentRow){ openSmall(rejectModal, rejectPanel); } });

	// Accept flow
	acceptCancelBtn?.addEventListener('click', ()=> closeSmall(acceptModal, acceptPanel));
	acceptModal?.addEventListener('click', (e)=>{ if(e.target===acceptModal) closeSmall(acceptModal, acceptPanel); });
	acceptConfirmBtn?.addEventListener('click', ()=>{
		if(currentRow){ updateStatus(currentRow, 'Accepted', 'bg-green-100 text-green-700'); }
		closeSmall(acceptModal, acceptPanel);
		close();
	});

	// Reject flow
	rejectCancelBtn?.addEventListener('click', ()=> closeSmall(rejectModal, rejectPanel));
	rejectModal?.addEventListener('click', (e)=>{ if(e.target===rejectModal) closeSmall(rejectModal, rejectPanel); });
	rejectConfirmBtn?.addEventListener('click', ()=>{
		const reason = (rejectReasonInput?.value || '').trim();
		if(!reason){
			rejectReasonError?.classList.remove('hidden');
			rejectReasonInput?.classList.add('border-red-400');
			rejectReasonInput?.focus();
			return;
		}
		rejectReasonError?.classList.add('hidden');
		rejectReasonInput?.classList.remove('border-red-400');
		if(currentRow){ updateStatus(currentRow, 'Pending', 'bg-yellow-100 text-yellow-700'); }
		closeSmall(rejectModal, rejectPanel);
		close();
	});
})();

// Owner verification modal interactions
(function(){
	const modal = document.getElementById('ownerVerificationModal');
	const panel = modal?.querySelector('.owner-modal-panel');
	const openButtons = document.querySelectorAll('.po-view-btn');
	const closeBtn = document.getElementById('poCloseBtn');
	const acceptBtn = document.getElementById('poAcceptBtn');
	const rejectBtn = document.getElementById('poRejectBtn');

	// Reuse confirm modals
	const acceptModal = document.getElementById('acceptConfirmModal');
	const acceptPanel = acceptModal?.querySelector('.accept-panel');
	const acceptCancelBtn = document.getElementById('acceptCancelBtn');
	const acceptConfirmBtn = document.getElementById('acceptConfirmBtn');

	const rejectModal = document.getElementById('rejectConfirmModal');
	const rejectPanel = rejectModal?.querySelector('.reject-panel');
	const rejectCancelBtn = document.getElementById('rejectCancelBtn');
	const rejectConfirmBtn = document.getElementById('rejectConfirmBtn');
	const rejectReasonInput = document.getElementById('rejectReasonInput');
	const rejectReasonError = document.getElementById('rejectReasonError');

	let currentRow = null;

	const data = {
		mm: { fullName: 'Mar Manon-og', username: 'mar_manon', email: 'mar@example.com', phone: '0988 123 4567', occupation: 'Civil Engineer', dob: '1991-03-12', age: '34', address: 'Sta. Maria, Barangay Tetuan, Zamboanga City, Zamboanga del Sur, 7000', validIdType: 'Philippine Passport', validIdNumber: 'P1234567', validIdPhoto: '#', policeClearance: '#' },
		cr: { fullName: 'Criscel Ann Delos Reyes', username: 'criscel_ann', email: 'criscel@example.com', phone: '0917 555 2211', occupation: 'Architect', dob: '1993-08-09', age: '32', address: 'Blk 2 Lot 5, Brgy. Lahug, Cebu City, Cebu, 6000', validIdType: 'National ID (PhilSys)', validIdNumber: '123456789012', validIdPhoto: '#', policeClearance: '#' },
		no: { fullName: 'Nesty Omongos', username: 'nesty_omongos', email: 'nesty@example.com', phone: '0908 777 9900', occupation: 'Business Owner', dob: '1990-10-19', age: '35', address: 'San Isidro St., Brgy. Poblacion, Davao City, Davao del Sur, 8000', validIdType: 'Driver\'s License', validIdNumber: 'N12-34-567890', validIdPhoto: '#', policeClearance: '#' }
	};

	function setText(id, value){ const el = document.getElementById(id); if(el) el.textContent = value; }
	function setInitials(name){ return (name||'').split(/\s+/).slice(0,2).map(s=>s[0]?.toUpperCase()||'').join(''); }

	function open(key, row){
		const d = data[key];
		if(!d) return;
		currentRow = row;
		setText('poFullName', d.fullName);
		setText('poContactLine', `${d.email} • ${d.phone}`);
		setText('poInitials', setInitials(d.fullName));
		setText('poUsername', d.username);
		setText('poEmail', d.email);
		setText('poOccupation', d.occupation);
		setText('poDob', d.dob);
		setText('poAge', d.age);
		setText('poAddress', d.address);
		setText('poValidIdType', d.validIdType);
		setText('poValidIdNumber', d.validIdNumber);
		const idLink = document.getElementById('poValidIdPhoto'); if(idLink) idLink.href = d.validIdPhoto || '#';
		const pcLink = document.getElementById('poPoliceClearance'); if(pcLink) pcLink.href = d.policeClearance || '#';

		modal?.classList.remove('hidden');
		requestAnimationFrame(()=>{
			modal?.classList.add('flex');
			panel?.classList.remove('scale-95','opacity-0');
			panel?.classList.add('scale-100','opacity-100');
		});
	}

	function close(){
		if(!modal) return;
		panel?.classList.add('scale-95');
		panel?.classList.remove('scale-100');
		panel?.classList.add('opacity-0');
		setTimeout(()=>{ modal.classList.add('hidden'); modal.classList.remove('flex'); }, 150);
	}

	function updateStatus(row, label, color){
		const pill = row?.querySelector('td:nth-child(4) span');
		if (!pill) return;
		pill.textContent = label;
		pill.className = 'inline-flex items-center px-3 py-1 rounded-full text-xs font-medium ' + color;
	}

	function openSmall(modalEl, panelEl){
		modalEl.classList.remove('hidden');
		requestAnimationFrame(()=>{
			modalEl.classList.add('flex');
			panelEl.classList.remove('scale-95','opacity-0');
			panelEl.classList.add('scale-100','opacity-100');
		});
	}
	function closeSmall(modalEl, panelEl){
		panelEl.classList.add('scale-95');
		panelEl.classList.remove('scale-100');
		panelEl.classList.add('opacity-0');
		setTimeout(()=>{ modalEl.classList.add('hidden'); modalEl.classList.remove('flex'); }, 150);
	}

	openButtons.forEach(btn => btn.addEventListener('click', (e)=>{
		const key = e.currentTarget.getAttribute('data-key');
		const row = e.currentTarget.closest('tr');
		open(key, row);
	}));

	closeBtn?.addEventListener('click', close);
	modal?.addEventListener('click', (e)=>{ if(e.target===modal) close(); });

	// Open confirm modals from main buttons
	acceptBtn?.addEventListener('click', ()=>{ if(currentRow){ openSmall(acceptModal, acceptPanel); } });
	rejectBtn?.addEventListener('click', ()=>{ if(currentRow){ openSmall(rejectModal, rejectPanel); } });

	// Accept flow
	acceptCancelBtn?.addEventListener('click', ()=> closeSmall(acceptModal, acceptPanel));
	acceptModal?.addEventListener('click', (e)=>{ if(e.target===acceptModal) closeSmall(acceptModal, acceptPanel); });
	acceptConfirmBtn?.addEventListener('click', ()=>{
		if(currentRow){ updateStatus(currentRow, 'Accepted', 'bg-green-100 text-green-700'); }
		closeSmall(acceptModal, acceptPanel);
		close();
	});

	// Reject flow
	rejectCancelBtn?.addEventListener('click', ()=> closeSmall(rejectModal, rejectPanel));
	rejectModal?.addEventListener('click', (e)=>{ if(e.target===rejectModal) closeSmall(rejectModal, rejectPanel); });
	rejectConfirmBtn?.addEventListener('click', ()=>{
		const reason = (rejectReasonInput?.value || '').trim();
		if(!reason){
			rejectReasonError?.classList.remove('hidden');
			rejectReasonInput?.classList.add('border-red-400');
			rejectReasonInput?.focus();
			return;
		}
		rejectReasonError?.classList.add('hidden');
		rejectReasonInput?.classList.remove('border-red-400');
		if(currentRow){ updateStatus(currentRow, 'Pending', 'bg-yellow-100 text-yellow-700'); }
		closeSmall(rejectModal, rejectPanel);
		close();
	});
})();

// Delete modal interactions (shared for contractors and owners)
(function(){
	const modal = document.getElementById('deleteConfirmModal');
	const panel = modal?.querySelector('.delete-panel');
	const confirmBtn = document.getElementById('deleteConfirmBtn');
	const cancelBtn = document.getElementById('deleteCancelBtn');
	const titleEl = document.getElementById('deleteTitle');
	const nameEl = document.getElementById('deleteName');

	const openButtons = document.querySelectorAll('.vr-del-btn, .po-del-btn');
	let currentRow = null;
	let currentBtn = null;

	// Inject ripple CSS once
	(function injectRipple(){
		if(document.getElementById('vr-ripple-style')) return;
		const style = document.createElement('style');
		style.id = 'vr-ripple-style';
		style.textContent = `
			.action-btn{position:relative;overflow:hidden}
			.ripple-effect{position:absolute;border-radius:50%;background:rgba(255,255,255,.6);transform:scale(0);animation:ripple-animation .6s ease-out;pointer-events:none}
			@keyframes ripple-animation{to{transform:scale(4);opacity:0}}
		`;
		document.head.appendChild(style);
	})();

	function addRipple(button, event){
		if(!button) return;
		const ripple = document.createElement('span');
		const rect = button.getBoundingClientRect();
		const size = Math.max(rect.width, rect.height);
		const x = (event?.clientX ?? rect.left + rect.width/2) - rect.left - size/2;
		const y = (event?.clientY ?? rect.top + rect.height/2) - rect.top - size/2;
		ripple.style.width = ripple.style.height = size + 'px';
		ripple.style.left = x + 'px';
		ripple.style.top = y + 'px';
		ripple.classList.add('ripple-effect');
		button.appendChild(ripple);
		setTimeout(()=> ripple.remove(), 600);
	}

	function showNotification(message, type='success'){
		const el = document.createElement('div');
		el.className = `fixed top-24 right-8 z-[75] px-6 py-4 rounded-lg shadow-2xl transform transition-all duration-500 translate-x-full ${type==='success'?'bg-green-500':'bg-red-500'} text-white font-semibold flex items-center gap-3`;
		el.innerHTML = `<i class="fi fi-rr-${type==='success'?'check-circle':'cross-circle'} text-2xl"></i><span>${message}</span>`;
		document.body.appendChild(el);
		setTimeout(()=>{ el.style.transform='translateX(0)'; },10);
		setTimeout(()=>{ el.style.transform='translateX(150%)'; setTimeout(()=> el.remove(), 500); },3000);
	}

	function open(entityType, entityName, row){
		currentRow = row;
		currentBtn = row?.querySelector(`[data-name="${entityName}"]`);
		if(titleEl) titleEl.textContent = `Delete ${entityType}`;
		if(nameEl) nameEl.textContent = entityName || 'this item';
		// Show overlay and animate panel in
		modal?.classList.remove('hidden');
		requestAnimationFrame(()=>{
			modal?.classList.add('flex');
			panel?.classList.remove('scale-95','opacity-0');
			panel?.classList.add('scale-100','opacity-100');
		});
	}

	function close(){
		// Animate panel out then hide overlay
		panel?.classList.add('scale-95');
		panel?.classList.remove('scale-100');
		panel?.classList.add('opacity-0');
		setTimeout(()=>{
			modal?.classList.add('hidden');
			modal?.classList.remove('flex');
		}, 200);
	}

	openButtons.forEach(btn => {
		btn.addEventListener('click', (e)=>{
			const entityType = e.currentTarget.getAttribute('data-type') || 'Item';
			const entityName = e.currentTarget.getAttribute('data-name') || 'this item';
			addRipple(e.currentTarget, e);
			const row = e.currentTarget.closest('tr');
			open(entityType, entityName, row);
		});
	});

	// Confirm delete flow
	confirmBtn?.addEventListener('click', ()=>{
		if(!currentRow) { close(); return; }
		const original = confirmBtn.innerHTML;
		confirmBtn.disabled = true;
		confirmBtn.classList.add('opacity-80','cursor-not-allowed');
		// Inline spinner ensures consistency
		confirmBtn.innerHTML = `
			<svg class="animate-spin h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
				<circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
				<path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"></path>
			</svg>
			<span>Deleting…</span>
		`;
		setTimeout(()=>{
			// Close modal first
			close();
			// Animate row fade, slide, and collapse for clearer feedback
			const h = currentRow.getBoundingClientRect().height + 'px';
			currentRow.style.height = h;
			currentRow.style.transition = 'opacity 300ms ease, transform 300ms ease, height 300ms ease, margin 300ms ease, padding 300ms ease';
			requestAnimationFrame(()=>{
				currentRow.style.opacity = '0';
				currentRow.style.transform = 'translateX(-16px)';
				currentRow.style.height = '0px';
				currentRow.style.marginTop = '0';
				currentRow.style.marginBottom = '0';
				currentRow.style.paddingTop = '0';
				currentRow.style.paddingBottom = '0';
			});
			setTimeout(()=>{
				currentRow.remove();
				showNotification('Deleted successfully!', 'success');
			}, 320);
			// Reset button
			confirmBtn.disabled = false;
			confirmBtn.classList.remove('opacity-80','cursor-not-allowed');
			confirmBtn.innerHTML = original;
		}, 800); // simulate AJAX
	});

	cancelBtn?.addEventListener('click', close);
	modal?.addEventListener('click', (e)=>{ if(e.target===modal) close(); });
	// ESC key closes modal
	document.addEventListener('keydown', (e)=>{ if(e.key==='Escape' && !modal.classList.contains('hidden')) close(); });
})();
