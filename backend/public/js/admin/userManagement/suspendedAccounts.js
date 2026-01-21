// Tabs switching for Suspended Accounts
(function(){
	const cTab = document.getElementById('saTabContractors');
	const oTab = document.getElementById('saTabOwners');
	const cWrap = document.getElementById('contractorsTableWrap');
	const oWrap = document.getElementById('ownersTableWrap');
	function activate(which){
		if(which==='contractors'){
			cTab?.classList.add('text-orange-600','border-orange-500');
			cTab?.classList.remove('text-gray-600','border-transparent');
			oTab?.classList.remove('text-orange-600','border-orange-500');
			oTab?.classList.add('text-gray-600');
			cWrap?.classList.remove('hidden');
			oWrap?.classList.add('hidden');
		} else {
			oTab?.classList.add('text-orange-600','border-orange-500');
			oTab?.classList.remove('text-gray-600','border-transparent');
			cTab?.classList.remove('text-orange-600','border-orange-500');
			cTab?.classList.add('text-gray-600');
			oWrap?.classList.remove('hidden');
			cWrap?.classList.add('hidden');
		}
	}
	cTab?.addEventListener('click', ()=>activate('contractors'));
	oTab?.addEventListener('click', ()=>activate('owners'));
})();

// ============================================================================
// REACTIVATE HANDLER - Attach listeners after page load and AJAX updates
// ============================================================================
document.addEventListener('DOMContentLoaded', function() {
	attachReactivateListeners();
});

function attachReactivateListeners() {
	const reactivateButtons = document.querySelectorAll('.reactivate-btn');
	reactivateButtons.forEach(btn => {
		// Remove existing listener to avoid duplicates
		btn.removeEventListener('click', handleReactivate);
		btn.addEventListener('click', handleReactivate);
	});
}

// Contractor view modal
(function(){
	const modal = document.getElementById('saContractorModal');
	const panel = modal?.querySelector('.sa-modal-panel');
	const openButtons = document.querySelectorAll('.sa-view-btn');
	const close1 = document.getElementById('saContractorClose');
	const close2 = document.getElementById('saContractorClose2');
	const reactivateBtn = document.getElementById('saReactivateBtn');
	const rModal = document.getElementById('saReactivateModal');
	const rPanel = rModal?.querySelector('.sa-reactivate-panel');
	const rKeep = document.getElementById('saReactivateKeep');
	const rEdit = document.getElementById('saReactivateEdit');
	const rCancel = document.getElementById('saReactivateCancel');
	const rsModal = document.getElementById('saReactivateSuccessModal');
	const rsPanel = rsModal?.querySelector('.sa-reactivate-success-panel');
	const rsConfirm = document.getElementById('saReactivateSuccessConfirm');
	// Edit-before-reactivation modal
	const reModal = document.getElementById('saReactivateEditModal');
	const rePanel = reModal?.querySelector('.sa-reactivate-edit-panel');
	const reClose = document.getElementById('saReactivateEditClose');
	const reCancel = document.getElementById('saReactivateEditCancel');
	const reSubmit = document.getElementById('saReactivateEditSubmit');
	const reTabCompany = document.getElementById('saRETabCompany');
	const reTabRep = document.getElementById('saRETabRep');
	const reCompanySection = document.getElementById('saRECompanySection');
	const reRepSection = document.getElementById('saRERepSection');
	let currentKey = null; let currentRow = null; let currentName = null; let currentEntityType = 'contractor';

	// Demo data used to populate the view modal
	const data = {
		hb: {
			name:'HorizonBuild Corporation',
			email:'hb@corp.com',
			phone:'+63 912 345 6789',
			date:'18 Jul, 2025',
			reason:'Multiple policy violations',
			experienceYears: 7,
			projects: { total:7, finished:2 },
			rep: { name:'Sofia Delgado', role:'Secretary / Contact person', email:'sofia.ops@example.com', phone:'0928-555-1209', avatar:'https://i.pravatar.cc/80?img=17' },
			company: {
				registeredDate:'January 12, 2023',
				pcabNo:'7100581',
				pcabCategory:'General Building',
				businessPermitNo:'PAS-BP-0925-330',
				businessPermitExp:'December 31, 2025',
				TIN:'009-224-119-007'
			},
			documents:[
				{ name:'DTI/SEC Registration.pdf', size:'200 KB', status:'Approved', url:'#' },
				{ name:'Business Permit 2025.pdf', size:'420 KB', status:'Approved', url:'#' }
			],
			profile:{
				banner:'https://images.unsplash.com/photo-1503387762-592deb58ef4e?q=80&w=1400&auto=format&fit=crop',
				location:'Zamboanga City',
				rating:'2.4 Rating',
				description:'The company focuses on innovative designs, project management, and turnkey solutions within Metro Manila.',
				specialties:['Modern','Building','Warehouse','Factories','Large-scale']
			}
		},
		mb: {
			name:'MetroBase Construction',
			email:'mb@corp.com',
			phone:'+63 918 222 3344',
			date:'12 Jan, 2025',
			reason:'Repeated ToS breach',
			experienceYears: 5,
			projects: { total:3, finished:1 },
			rep: { name:'Arnold Cruz', role:'Operations Lead', email:'arnold.cruz@example.com', phone:'0917-800-4422', avatar:'https://i.pravatar.cc/80?img=22' },
			company: {
				registeredDate:'March 8, 2022',
				pcabNo:'7101123',
				pcabCategory:'General Engineering',
				businessPermitNo:'QC-BP-2025-1188',
				businessPermitExp:'November 15, 2025',
				TIN:'003-885-990-001'
			},
			documents:[
				{ name:'SEC Certificate.pdf', size:'180 KB', status:'Approved', url:'#' },
				{ name:'PCAB Renewal.pdf', size:'310 KB', status:'Pending', url:'#' }
			],
			profile:{
				banner:'https://images.unsplash.com/photo-1508450859948-4e04fabaa4ea?q=80&w=1400&auto=format&fit=crop',
				location:'Pasig City',
				rating:'3.8 Rating',
				description:'Turnkey construction with focus on commercial developments and project controls.',
				specialties:['Commercial','Project Management','Fit-out']
			}
		}
	};
	function initials(s){ return (s||'').split(/\s+/).slice(0,2).map(x=>x[0]?.toUpperCase()||'').join(''); }
	function setText(id, v){ const el = document.getElementById(id); if(el) el.textContent = v; }
	function setSrc(id, v){ const el = document.getElementById(id); if(el && 'src' in el) el.src = v; }
	function el(id){ return document.getElementById(id); }

	function chip(text){
		const span = document.createElement('span');
		span.className = 'px-2.5 py-1 rounded-full bg-gray-100 text-gray-700 text-xs';
		span.textContent = text;
		return span;
	}

	function showToast(message, type='default'){
		let container = document.getElementById('saToastContainer');
		if(!container){
			container = document.createElement('div');
			container.id = 'saToastContainer';
			container.className = 'fixed bottom-4 right-4 z-[80] space-y-2';
			document.body.appendChild(container);
		}
		const color = type==='success' ? 'bg-emerald-600' : type==='info' ? 'bg-indigo-600' : 'bg-gray-900';
		const t = document.createElement('div');
		t.className = `${color} text-white text-sm px-3 py-2 rounded-lg shadow-lg animate-[fadeIn_.2s_ease]`;
		t.textContent = message;
		container.appendChild(t);
		setTimeout(()=>{ t.style.transition='opacity .2s'; t.style.opacity='0'; setTimeout(()=>t.remove(), 200); }, 1800);
	}

	function renderDocs(docs){
		const wrap = el('saCDocs'); if(!wrap) return;
		wrap.innerHTML = '';
		docs.forEach(d=>{
			const row = document.createElement('div');
			row.className = 'group flex items-center justify-between px-3 py-2 rounded-lg hover:bg-gray-50 border border-transparent hover:border-gray-200 transition cursor-pointer';
			row.innerHTML = `
				<div class="flex items-center gap-3">
					<div class="w-8 h-8 rounded-lg bg-indigo-50 text-indigo-600 flex items-center justify-center"><i class="fi fi-rr-file"></i></div>
					<div>
						<div class="font-medium text-gray-900">${d.name}</div>
						<div class="text-xs text-gray-500">${d.size}</div>
					</div>
				</div>
				<span class="inline-flex items-center gap-1 px-2 py-1 text-xs rounded-full ${d.status==='Approved'?'bg-emerald-50 text-emerald-700 border border-emerald-200':'bg-amber-50 text-amber-700 border border-amber-200'}">
					<i class="${d.status==='Approved'?'fi fi-rr-check':'fi fi-rr-time-add'}"></i>${d.status}
				</span>
			`;
			row.addEventListener('click', ()=>{
				showToast('Opening document…','info');
				if(d.url && d.url!=='#') { window.open(d.url, '_blank'); }
			});
			wrap.appendChild(row);
		});
	}

	function open(key, row){
		const d = data[key]; if(!d) return;
		currentKey = key; currentRow = row || null; currentName = d.name; currentEntityType = 'contractor';
		// Header / identity
		setText('saCName', d.name);
		setText('saCInitials', initials(d.name));
		// Contact and stats
		setText('saCEmail', d.email);
		setText('saCPhone', d.phone);
		setText('saCExp', `${d.experienceYears} yrs`);
		setText('saCTotal', `${d.projects.total}`);
		setText('saCFinished', `${d.projects.finished}`);
		// Representative
		setText('saCRepName', d.rep.name);
		setText('saCRepRole', d.rep.role);
		setText('saCRepEmail', d.rep.email);
		setText('saCRepPhone', d.rep.phone);
		setSrc('saCRepAvatar', d.rep.avatar);
		// Suspension
		setText('saCDate', d.date);
		setText('saCReason', d.reason);
		// Company details
		setText('saCRegDate', d.company.registeredDate);
		setText('saCPCABNo', d.company.pcabNo);
		setText('saCPCABCat', d.company.pcabCategory);
		setText('saCBizNo', d.company.businessPermitNo);
		setText('saCBizExp', d.company.businessPermitExp);
		setText('saCTIN', d.company.TIN);
		// Documents
		renderDocs(d.documents || []);
		// Profile
		setSrc('saCBanner', d.profile.banner);
		setText('saCLocation', d.profile.location);
		setText('saCRating', d.profile.rating);
		setText('saCDescription', d.profile.description);
		const specs = el('saCSpecialties');
		if(specs){
			specs.innerHTML='';
			(d.profile.specialties||[]).forEach(s=> specs.appendChild(chip(s)));
		}

		// Wire interactive helpers each open
		document.querySelectorAll('[data-copy-target]')?.forEach(btn=>{
			btn.addEventListener('click', (e)=>{
				e.preventDefault();
				const sel = btn.getAttribute('data-copy-target');
				const tgt = sel ? document.querySelector(sel) : null;
				const text = tgt?.textContent?.trim() || '';
				if(text){ navigator.clipboard.writeText(text); showToast('Copied to clipboard','success'); }
			},{ once:true });
		});
		const toggle = el('saCTeamToggle');
		const more = el('saCTeamMore');
		if(toggle && more){
			toggle.onclick = ()=>{
				const openState = !more.classList.contains('hidden');
				more.classList.toggle('hidden');
				toggle.textContent = openState ? 'Show All' : 'Show Less';
			};
		}

		modal?.classList.remove('hidden');
		requestAnimationFrame(()=>{
			modal?.classList.add('flex');
			panel?.classList.remove('scale-95','opacity-0');
			panel?.classList.add('scale-100','opacity-100');
		});
	}
	function close(){
		panel?.classList.add('scale-95');
		panel?.classList.remove('scale-100');
		panel?.classList.add('opacity-0');
		setTimeout(()=>{ modal?.classList.add('hidden'); modal?.classList.remove('flex'); }, 150);
	}
	openButtons.forEach(btn=>btn.addEventListener('click', e=> open(e.currentTarget.getAttribute('data-key'), e.currentTarget.closest('tr'))));
	close1?.addEventListener('click', close);
	close2?.addEventListener('click', close);
	modal?.addEventListener('click', e=>{ if(e.target===modal) close(); });

	// Reactivate flow
	function csrfToken(){ return document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''; }
	function openReactivate(){
		if(!rModal) return;
		const title = document.getElementById('saReactivateTitle');
		const nameEl = document.getElementById('saReactivateName');
		if(title) title.textContent = 'Reactivate ' + (currentName || 'Account');
		if(nameEl) nameEl.textContent = currentName || 'this account';
		// Ensure Edit option is visible for supported entity types
		rEdit?.classList.remove('hidden');
		rModal.classList.remove('hidden');
		requestAnimationFrame(()=>{
			rModal.classList.add('flex');
			rPanel?.classList.remove('scale-95','opacity-0');
			rPanel?.classList.add('scale-100','opacity-100');
		});
	}
	function closeReactivate(){
		rPanel?.classList.add('scale-95');
		rPanel?.classList.remove('scale-100');
		rPanel?.classList.add('opacity-0');
		setTimeout(()=>{ rModal?.classList.add('hidden'); rModal?.classList.remove('flex'); }, 180);
	}
	async function doReactivate(mode){
		if(!currentKey) return;
		const btn = mode==='keep' ? rKeep : rEdit;
		if(!btn) return;
		const original = btn.innerHTML;
		btn.disabled = true; btn.classList.add('opacity-90','cursor-wait');
		btn.innerHTML = `
			<svg class="animate-spin h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
				<circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
				<path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"></path>
			</svg>
			<span>${mode==='keep'?'Reactivating…':'Preparing…'}</span>`;
		try{
			const res = await fetch('/admin/user-management/suspended-accounts/reactivate', {
				method:'POST',
				headers:{ 'Content-Type':'application/json', 'X-CSRF-TOKEN': csrfToken() },
				body: JSON.stringify({ id: currentKey, entityType: currentEntityType || 'contractor', mode })
			});
			const json = await res.json();
			if(json?.success){
				// Show success modal; row removal happens on confirm
				openReactivateSuccess();
			}else{
				showToast('Unable to reactivate. Please try again');
			}
		}catch(e){
			showToast('Network error. Please try again');
		}
		finally{
			btn.disabled = false; btn.classList.remove('opacity-90','cursor-wait'); btn.innerHTML = original;
		}
	}

	reactivateBtn?.addEventListener('click', openReactivate);
	rCancel?.addEventListener('click', closeReactivate);
	rModal?.addEventListener('click', e=>{ if(e.target===rModal) closeReactivate(); });
	rKeep?.addEventListener('click', ()=> doReactivate('keep'));
	rEdit?.addEventListener('click', ()=> currentEntityType==='owner' ? openOwnerEditBeforeReactivate() : openEditBeforeReactivate());

	// Success modal controls
	function openReactivateSuccess(){
		if(!rsModal) return;
		closeReactivate(); // hide options modal underneath
		rsModal.classList.remove('hidden');
		requestAnimationFrame(()=>{
			rsModal.classList.add('flex');
			rsPanel?.classList.remove('scale-95','opacity-0');
			rsPanel?.classList.add('scale-100','opacity-100');
		});
	}
	function closeReactivateSuccess(){
		rsPanel?.classList.add('scale-95');
		rsPanel?.classList.remove('scale-100');
		rsPanel?.classList.add('opacity-0');
		setTimeout(()=>{ rsModal?.classList.add('hidden'); rsModal?.classList.remove('flex'); }, 180);
	}
	rsModal?.addEventListener('click', e=>{ if(e.target===rsModal) closeReactivateSuccess(); });
	document.addEventListener('keydown', (e)=>{ if(e.key==='Escape' && rsModal && !rsModal.classList.contains('hidden')) closeReactivateSuccess(); });

	function closeActiveEntityModal(){
		if(currentEntityType === 'contractor'){
			close();
		}else{
			const om = document.getElementById('saOwnerModal');
			const op = om?.querySelector('.sa-owner-panel');
			op?.classList.add('scale-95');
			op?.classList.add('opacity-0');
			setTimeout(()=>{ om?.classList.add('hidden'); om?.classList.remove('flex'); }, 150);
		}
	}
	rsConfirm?.addEventListener('click', ()=>{
		// Remove table row with animation, then close all
		if(currentRow){
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
			setTimeout(()=> currentRow.remove(), 320);
		}
		closeReactivateSuccess();
		closeActiveEntityModal();
		showToast('Account reactivated','success');
	});

	// Edit-before-reactivation modal logic
	function openEditBeforeReactivate(){
		if(!reModal) return;
		// open edit modal, keep options modal visible underneath? We'll close it for clarity.
		closeReactivate();
		reModal.classList.remove('hidden');
		requestAnimationFrame(()=>{
			reModal.classList.add('flex');
			rePanel?.classList.remove('scale-95','opacity-0');
			rePanel?.classList.add('scale-100','opacity-100');
		});
	}
	function closeEditBeforeReactivate(){
		rePanel?.classList.add('scale-95');
		rePanel?.classList.remove('scale-100');
		rePanel?.classList.add('opacity-0');
		setTimeout(()=>{ reModal?.classList.add('hidden'); reModal?.classList.remove('flex'); }, 180);
	}
	reModal?.addEventListener('click', e=>{ if(e.target===reModal) closeEditBeforeReactivate(); });
	document.addEventListener('keydown', (e)=>{ if(e.key==='Escape' && reModal && !reModal.classList.contains('hidden')) closeEditBeforeReactivate(); });
	reClose?.addEventListener('click', closeEditBeforeReactivate);
	reCancel?.addEventListener('click', closeEditBeforeReactivate);

	// Tabs switch
	reTabCompany?.addEventListener('click', ()=>{
		reTabCompany.classList.add('bg-gradient-to-r','from-orange-500','to-orange-600','text-white');
		reTabCompany.classList.remove('bg-white','border-2','border-gray-300','text-gray-700');
		reTabRep.classList.add('bg-white','border-2','border-gray-300','text-gray-700');
		reTabRep.classList.remove('bg-gradient-to-r','from-orange-500','to-orange-600','text-white');
		reCompanySection?.classList.remove('hidden');
		reRepSection?.classList.add('hidden');
	});
	reTabRep?.addEventListener('click', ()=>{
		reTabRep.classList.add('bg-gradient-to-r','from-orange-500','to-orange-600','text-white');
		reTabRep.classList.remove('bg-white','border-2','border-gray-300','text-gray-700');
		reTabCompany.classList.add('bg-white','border-2','border-gray-300','text-gray-700');
		reTabCompany.classList.remove('bg-gradient-to-r','from-orange-500','to-orange-600','text-white');
		reCompanySection?.classList.add('hidden');
		reRepSection?.classList.remove('hidden');
	});

	// Fake client-side validation and submit -> AJAX
	reSubmit?.addEventListener('click', ()=>{
		const name = document.getElementById('saRECompanyName')?.value?.trim();
		if(!name){ showToast('Company name is required'); return; }
		reSubmit.disabled = true; const original = reSubmit.innerHTML; reSubmit.classList.add('opacity-90','cursor-wait');
		reSubmit.innerHTML = `
			<svg class="animate-spin h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
				<circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
				<path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"></path>
			</svg>
			<span>Reactivating…</span>`;
		// call existing reactivation with mode 'edit'
		doReactivate('edit').then(()=>{
			reSubmit.disabled = false; reSubmit.classList.remove('opacity-90','cursor-wait'); reSubmit.innerHTML = original;
			closeEditBeforeReactivate();
		});
	});

	// Expose a small API for other views (e.g., Owner) to reuse reactivation flow
	window.SAReactivate = {
		setContext: ({ id, row, name, type }) => { currentKey = id; currentRow = row || null; currentName = name || ''; currentEntityType = type || 'contractor'; },
		openOptions: openReactivate,
		doKeep: () => doReactivate('keep'),
		doEdit: () => doReactivate('edit'),
		openEdit: () => currentEntityType==='owner' ? openOwnerEditBeforeReactivate() : openEditBeforeReactivate()
	};
})();

// Owner view modal
(function(){
	const modal = document.getElementById('saOwnerModal');
	const panel = modal?.querySelector('.sa-owner-panel');
	const openButtons = document.querySelectorAll('.so-view-btn');
	const close1 = document.getElementById('saOwnerClose');
	const close2 = document.getElementById('saOwnerClose2');
	const reactivateBtn = document.getElementById('saOwnerReactivateBtn');

	const data = {
		jc: {
			name:'Jerome Castillo',
			email:'jerome@example.com',
			phone:'0999 123 4567',
			date:'12 Jan, 2025',
			reason:'Chargeback fraud',
			documents:[
				{ name:'Government ID.pdf', size:'220 KB', status:'Approved', url:'#' },
				{ name:'Proof of Address.pdf', size:'150 KB', status:'Approved', url:'#' }
			],
			profile:{
				banner:'https://images.unsplash.com/photo-1496307653780-42ee777d4833?q=80&w=1400&auto=format&fit=crop',
				location:'Quezon City',
				rating:'4.2 Rating',
				description:'Homeowner and property investor focusing on residential developments and renovations.',
				specialties:['Residential','Renovation','Interior']
			}
		},
	};
	function initials(s){ return (s||'').split(/\s+/).slice(0,2).map(x=>x[0]?.toUpperCase()||'').join(''); }
	function setText(id, v){ const el = document.getElementById(id); if(el) el.textContent = v; }
	function setSrc(id, v){ const el = document.getElementById(id); if(el && 'src' in el) el.src = v; }
	function el(id){ return document.getElementById(id); }
	function chip(text){ const span = document.createElement('span'); span.className='px-2.5 py-1 rounded-full bg-gray-100 text-gray-700 text-xs'; span.textContent=text; return span; }
	function showToast(message){
		let c = document.getElementById('saToastContainer');
		if(!c){ c = document.createElement('div'); c.id='saToastContainer'; c.className='fixed bottom-4 right-4 z-[80] space-y-2'; document.body.appendChild(c); }
		const t = document.createElement('div'); t.className='bg-gray-900 text-white text-sm px-3 py-2 rounded-lg shadow-lg'; t.textContent=message; c.appendChild(t); setTimeout(()=>{ t.style.opacity='0'; t.style.transition='opacity .2s'; setTimeout(()=>t.remove(),200); }, 1500);
	}
	function renderDocs(docs){
		const wrap = el('saODocs'); if(!wrap) return;
		wrap.innerHTML='';
		docs.forEach(d=>{
			const row = document.createElement('div');
			row.className='group flex items-center justify-between px-3 py-2 rounded-lg hover:bg-gray-50 border border-transparent hover:border-gray-200 transition cursor-pointer';
			row.innerHTML = `
				<div class="flex items-center gap-3">
					<div class="w-8 h-8 rounded-lg bg-indigo-50 text-indigo-600 flex items-center justify-center"><i class="fi fi-rr-file"></i></div>
					<div>
						<div class="font-medium text-gray-900">${d.name}</div>
						<div class="text-xs text-gray-500">${d.size}</div>
					</div>
				</div>
				<span class="inline-flex items-center gap-1 px-2 py-1 text-xs rounded-full ${d.status==='Approved'?'bg-emerald-50 text-emerald-700 border border-emerald-200':'bg-amber-50 text-amber-700 border border-amber-200'}">
					<i class="${d.status==='Approved'?'fi fi-rr-check':'fi fi-rr-time-add'}"></i>${d.status}
				</span>`;
			row.addEventListener('click', ()=>{ if(d.url && d.url!=='#') window.open(d.url,'_blank'); else showToast('Opening document…'); });
			wrap.appendChild(row);
		});
	}

	let currentKey=null, currentRow=null, currentName='';
	function open(key, row){
		const d = data[key]; if(!d) return;
		currentKey = key; currentRow = row || null; currentName = d.name;
		// Populate header/card
		setText('saOName', d.name);
		setText('saOInitials', initials(d.name));
		// Contact
		setText('saOEmail', d.email);
		setText('saOPhone', d.phone);
		// Suspension
		setText('saODate', d.date);
		setText('saOReason', d.reason);
		// Documents
		renderDocs(d.documents||[]);
		// Profile
		setSrc('saOBanner', d.profile.banner);
		setText('saOLocation', d.profile.location);
		setText('saORating', d.profile.rating);
		setText('saODescription', d.profile.description);
		const specs = el('saOSpecialties');
		if(specs){ specs.innerHTML=''; (d.profile.specialties||[]).forEach(s=> specs.appendChild(chip(s))); }

		// copy buttons wiring
		document.querySelectorAll('[data-copy-target]')?.forEach(btn=>{
			btn.addEventListener('click', (e)=>{
				e.preventDefault();
				const sel = btn.getAttribute('data-copy-target');
				const tgt = sel ? document.querySelector(sel) : null;
				const text = tgt?.textContent?.trim() || '';
				if(text){ navigator.clipboard.writeText(text); showToast('Copied to clipboard'); }
			}, { once:true });
		});

		// Set reactivation context for shared flow
		if(window.SAReactivate){ window.SAReactivate.setContext({ id: currentKey, row: currentRow, name: currentName, type: 'owner' }); }

		modal?.classList.remove('hidden');
		requestAnimationFrame(()=>{
			modal?.classList.add('flex');
			panel?.classList.remove('scale-95','opacity-0');
			panel?.classList.add('scale-100','opacity-100');
		});
	}
	function close(){
		panel?.classList.add('scale-95');
		panel?.classList.remove('scale-100');
		panel?.classList.add('opacity-0');
		setTimeout(()=>{ modal?.classList.add('hidden'); modal?.classList.remove('flex'); }, 150);
	}
	openButtons.forEach(btn=>btn.addEventListener('click', e=> open(e.currentTarget.getAttribute('data-key'), e.currentTarget.closest('tr'))));
	close1?.addEventListener('click', close);
	close2?.addEventListener('click', close);
	modal?.addEventListener('click', e=>{ if(e.target===modal) close(); });
	reactivateBtn?.addEventListener('click', ()=>{ if(window.SAReactivate){ window.SAReactivate.openOptions(); } });
})();

// Owner Edit-before-reactivation modal logic (parity with contractor)
(function(){
	const modal = document.getElementById('saREOwnerModal');
	if(!modal) return;
	const panel = modal.querySelector('.sa-reactivate-owner-edit-panel');
	const closeBtn = document.getElementById('saREOwnerClose');
	const cancelBtn = document.getElementById('saREOwnerCancel');
	const submitBtn = document.getElementById('saREOwnerSubmit');

	function open(){
		modal.classList.remove('hidden');
		requestAnimationFrame(()=>{
			modal.classList.add('flex');
			panel?.classList.remove('scale-95','opacity-0');
			panel?.classList.add('scale-100','opacity-100');
		});
	}
	function close(){
		panel?.classList.add('scale-95');
		panel?.classList.remove('scale-100');
		panel?.classList.add('opacity-0');
		setTimeout(()=>{ modal.classList.add('hidden'); modal.classList.remove('flex'); }, 180);
	}
	window.openOwnerEditBeforeReactivate = open;
	modal.addEventListener('click', e=>{ if(e.target===modal) close(); });
	document.addEventListener('keydown', (e)=>{ if(e.key==='Escape' && !modal.classList.contains('hidden')) close(); });
	closeBtn?.addEventListener('click', close);
	cancelBtn?.addEventListener('click', close);

	submitBtn?.addEventListener('click', ()=>{
		const first = document.getElementById('saREOwnerFirst')?.value?.trim();
		if(!first){
			// reuse toast from contractor scope if present
			if(typeof showToast==='function') showToast('First name is required');
			return;
		}
		submitBtn.disabled = true; const original = submitBtn.innerHTML; submitBtn.classList.add('opacity-90','cursor-wait');
		submitBtn.innerHTML = `
			<svg class="animate-spin h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
				<circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
				<path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"></path>
			</svg>
			<span>Reactivating…</span>`;
		if(window.SAReactivate){
			window.SAReactivate.doEdit();
		}
		// close the edit modal after starting reactivation; success modal will show
		setTimeout(()=>{
			submitBtn.disabled = false; submitBtn.classList.remove('opacity-90','cursor-wait'); submitBtn.innerHTML = original;
			close();
		}, 200);
	});
})();

// Shared Delete modal with ripple and animations
(function(){
	const modal = document.getElementById('saDeleteModal');
	const panel = modal?.querySelector('.sa-delete-panel');
	const openButtons = document.querySelectorAll('.sa-del-btn, .so-del-btn');
	const confirmBtn = document.getElementById('saDeleteConfirm');
	const cancelBtn = document.getElementById('saDeleteCancel');
	const titleEl = document.getElementById('saDeleteTitle');
	const nameEl = document.getElementById('saDeleteName');
	let currentRow = null;

	// inject ripple CSS once
	(function(){
		if(document.getElementById('sa-ripple-style')) return;
		const style = document.createElement('style');
		style.id = 'sa-ripple-style';
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

	function open(entityType, entityName, row){
		currentRow = row;
		if(titleEl) titleEl.textContent = `Delete ${entityType}`;
		if(nameEl) nameEl.textContent = entityName || 'this item';
		modal?.classList.remove('hidden');
		requestAnimationFrame(()=>{
			modal?.classList.add('flex');
			panel?.classList.remove('scale-95','opacity-0');
			panel?.classList.add('scale-100','opacity-100');
		});
	}
	function close(){
		panel?.classList.add('scale-95');
		panel?.classList.remove('scale-100');
		panel?.classList.add('opacity-0');
		setTimeout(()=>{ modal?.classList.add('hidden'); modal?.classList.remove('flex'); }, 200);
	}

	openButtons.forEach(btn => btn.addEventListener('click', (e)=>{
		const el = e.currentTarget;
		el.classList.add('action-btn');
		addRipple(el, e);
		const row = el.closest('tr');
		const name = el.getAttribute('data-name');
		const type = el.getAttribute('data-type') || 'Item';
		open(type, name, row);
	}));

	confirmBtn?.addEventListener('click', ()=>{
		if(!currentRow) { close(); return; }
		const original = confirmBtn.innerHTML;
		confirmBtn.disabled = true;
		confirmBtn.classList.add('opacity-80','cursor-not-allowed');
		confirmBtn.innerHTML = `
			<svg class="animate-spin h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
				<circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
				<path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"></path>
			</svg>
			<span>Deleting…</span>
		`;
		setTimeout(()=>{
			close();
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
			setTimeout(()=>{ currentRow.remove(); }, 320);
			confirmBtn.disabled = false;
			confirmBtn.classList.remove('opacity-80','cursor-not-allowed');
			confirmBtn.innerHTML = original;
		}, 800);
	});
	cancelBtn?.addEventListener('click', close);
	modal?.addEventListener('click', (e)=>{ if(e.target===modal) close(); });
	document.addEventListener('keydown', (e)=>{ if(e.key==='Escape' && !modal.classList.contains('hidden')) close(); });
})();

// ============================================================================
// REACTIVATE HANDLER - For AJAX updated buttons
// ============================================================================
let currentReactivateUserId = null;
let currentReactivateUserType = null;
let currentReactivateUserName = null;

function handleReactivate(event) {
	const btn = event.currentTarget;
	const userId = btn.getAttribute('data-id');
	const userType = btn.getAttribute('data-user-type');
	const userName = btn.getAttribute('data-name');

	if (!userId || !userType) {
		console.error('Missing data attributes for reactivation');
		return;
	}

	// Store current user data
	currentReactivateUserId = userId;
	currentReactivateUserType = userType;
	currentReactivateUserName = userName;

	// Open modal
	openReactivateSuspendedAccountModal(userName);
}

function openReactivateSuspendedAccountModal(userName) {
	// Set user name in modal
	document.getElementById('reactivateSuspendedAccountName').textContent = userName;

	// Show modal
	const modal = document.getElementById('reactivateSuspendedAccountModal');
	const modalContent = modal.querySelector('.modal-content');
	modal.classList.remove('hidden');
	modal.classList.add('flex');
	setTimeout(() => {
		modalContent.classList.remove('scale-95', 'opacity-0');
		modalContent.classList.add('scale-100', 'opacity-100');
	}, 10);
}

function closeReactivateSuspendedAccountModal() {
	const modal = document.getElementById('reactivateSuspendedAccountModal');
	const modalContent = modal.querySelector('.modal-content');

	modalContent.classList.remove('scale-100', 'opacity-100');
	modalContent.classList.add('scale-95', 'opacity-0');

	setTimeout(() => {
		modal.classList.add('hidden');
		modal.classList.remove('flex');
		currentReactivateUserId = null;
		currentReactivateUserType = null;
		currentReactivateUserName = null;
	}, 300);
}

function confirmReactivateSuspendedAccount() {
	if (!currentReactivateUserId || !currentReactivateUserType) {
		console.error('Missing user data for reactivation');
		return;
	}

	const confirmBtn = document.getElementById('confirmReactivateSuspendedAccountBtn');
	const originalBtnText = confirmBtn.innerHTML;
	confirmBtn.disabled = true;
	confirmBtn.innerHTML = '<i class="fi fi-rr-spinner animate-spin"></i> Reactivating...';

	const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');

	fetch('/admin/user-management/suspended-accounts/reactivate', {
		method: 'POST',
		headers: {
			'Content-Type': 'application/json',
			'X-CSRF-TOKEN': csrfToken,
			'Accept': 'application/json'
		},
		body: JSON.stringify({
			contractor_user_id: currentReactivateUserId,
			user_type: currentReactivateUserType
		})
	})
	.then(response => response.json())
	.then(data => {
		if (data.success) {
			// Close modal
			closeReactivateSuspendedAccountModal();

			// Show success notification
			showNotification(data.message || 'Account reactivated successfully!', 'success');

			// Find and remove the row from table
			const tableWrap = currentReactivateUserType === 'contractor' ?
				document.getElementById('contractorsTableWrap') :
				document.getElementById('ownersTableWrap');

			if (tableWrap) {
				const row = tableWrap.querySelector(`button[data-id="${currentReactivateUserId}"]`)?.closest('tr');
				if (row) {
					row.style.transition = 'opacity 0.3s ease, transform 0.3s ease';
					row.style.opacity = '0';
					row.style.transform = 'translateX(-16px)';
					setTimeout(() => {
						row.remove();
						// Check if table is now empty
						const tbody = tableWrap.querySelector('tbody');
						if (tbody && tbody.querySelectorAll('tr').length === 0) {
							// Reload to show empty state
							setTimeout(() => window.location.reload(), 1000);
						}
					}, 300);
				}
			}
		} else {
			// Show error notification
			showNotification(data.message || 'Failed to reactivate account', 'error');
		}
	})
	.catch(error => {
		console.error('Error:', error);
		showNotification('An error occurred. Please try again.', 'error');
	})
	.finally(() => {
		// Re-enable button
		confirmBtn.disabled = false;
		confirmBtn.innerHTML = originalBtnText;
	});
}

// Notification function (matching contractor_Views.js style)
function showNotification(message, type = 'success') {
	const notification = document.createElement('div');
	notification.className = `fixed top-24 right-8 z-[60] px-6 py-4 rounded-lg shadow-2xl transform transition-all duration-500 translate-x-full ${
		type === 'success' ? 'bg-green-500' : 'bg-red-500'
	} text-white font-semibold flex items-center gap-3`;
	notification.innerHTML = `
		<i class="fi fi-rr-${type === 'success' ? 'check-circle' : 'cross-circle'} text-2xl"></i>
		<span>${message}</span>
	`;
	document.body.appendChild(notification);

	setTimeout(() => {
		notification.style.transform = 'translateX(0)';
	}, 10);

	setTimeout(() => {
		notification.style.transform = 'translateX(150%)';
		setTimeout(() => notification.remove(), 500);
	}, 3000);
}

// Event listeners for modal
document.addEventListener('DOMContentLoaded', function() {
	const confirmBtn = document.getElementById('confirmReactivateSuspendedAccountBtn');
	const cancelBtn = document.getElementById('cancelReactivateSuspendedAccountBtn');
	const modal = document.getElementById('reactivateSuspendedAccountModal');

	if (confirmBtn) {
		confirmBtn.addEventListener('click', confirmReactivateSuspendedAccount);
	}

	if (cancelBtn) {
		cancelBtn.addEventListener('click', closeReactivateSuspendedAccountModal);
	}

	// Close on background click
	if (modal) {
		modal.addEventListener('click', function(e) {
			if (e.target === modal) {
				closeReactivateSuspendedAccountModal();
			}
		});
	}

	// Close on ESC key
	document.addEventListener('keydown', function(e) {
		if (e.key === 'Escape' && !modal.classList.contains('hidden')) {
			closeReactivateSuspendedAccountModal();
		}
	});
});
