
let isEditingHaltedMilestone = false;

function renderHaltedMilestoneTimeline() {
	const container = document.getElementById('haltedMilestoneTimeline');
	if (!container) return;
	container.innerHTML = '';
	HALTED_MILESTONE_DATA.forEach((m, idx) => {
		const statusIcon = m.status === 'completed' ?
			'<span class="inline-block w-5 h-5 rounded-full bg-green-500 text-white flex items-center justify-center">✔</span>' :
			m.status === 'halted' ?
			'<span class="inline-block w-5 h-5 rounded-full bg-rose-500 text-white flex items-center justify-center">!</span>' :
			'<span class="inline-block w-5 h-5 rounded-full bg-gray-300"></span>';

		const item = document.createElement('div');
		item.className = 'flex gap-3 py-3 border-b border-gray-100';
		item.innerHTML = `
			<div class="flex flex-col items-center">
				<div class="text-xs font-bold text-gray-600">${m.percent}%</div>
				<div class="w-1 h-full bg-gray-200 rounded"></div>
			</div>
			<div class="flex-1">
				<div class="flex items-center gap-2">
					${statusIcon}
					<p class="text-sm font-semibold text-gray-900">${m.title}</p>
				</div>
				<p class="text-xs text-gray-500">${m.date}</p>
				<p class="text-xs text-gray-600 leading-relaxed mt-1">${m.description}</p>
				<button class="text-xs text-rose-600 hover:text-rose-700 font-semibold mt-2" onclick="selectHaltedMilestone(${idx})">View Details</button>
			</div>
		`;
		container.appendChild(item);
	});
}

function selectHaltedMilestone(index) {
	const details = document.getElementById('haltedDetails');
	if (!details) return;
	const m = HALTED_MILESTONE_DATA[index];
	const files = (m.files || []).map(f => `<a href="#" class="flex items-center gap-1 text-rose-600 hover:text-rose-700 text-xs"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7H7v10h10V9m-4-2l4 4"/></svg>${f}</a>`).join('');
	details.innerHTML = `
		<div>
			<h4 class="text-sm font-bold text-gray-900">${m.title}</h4>
			<p class="text-xs text-gray-500">${m.date}</p>
			<p class="text-sm text-gray-600 leading-relaxed mt-2">${m.description}</p>
			<div class="mt-3">
				<span class="text-xs text-gray-500 block mb-1">Supporting Files</span>
				<div class="space-y-1">${files}</div>
			</div>
		</div>
	`;
}

function openEditHaltedMilestoneModal() {
	isEditingHaltedMilestone = true;
	// Reuse the existing edit modal if present, with halted context
	if (typeof openEditCompletedMilestoneModal === 'function') {
		openEditCompletedMilestoneModal();
	}
}

// Ensure save handler respects halted context
const originalSaveMilestoneEdit = typeof saveMilestoneEdit === 'function' ? saveMilestoneEdit : null;
if (originalSaveMilestoneEdit) {
	window.saveMilestoneEdit = function() {
		if (window.isEditingCompletedMilestone) {
			return originalSaveMilestoneEdit();
		}
		if (isEditingHaltedMilestone) {
			// Example: update HALTED_MILESTONE_DATA based on form values
			// Integrate with actual form inputs used in the shared edit modal
			// This placeholder assumes global `currentMilestoneIndex` and inputs exist
			try {
				const title = document.getElementById('milestoneTitleInput')?.value || '';
				const desc = document.getElementById('milestoneDescInput')?.value || '';
				const date = document.getElementById('milestoneDateInput')?.value || '';
				const percent = parseInt(document.getElementById('milestonePercentInput')?.value || '0', 10);
				const idx = window.currentMilestoneIndex ?? 0;
				HALTED_MILESTONE_DATA[idx] = { ...(HALTED_MILESTONE_DATA[idx] || {}), title, description: desc, date, percent };
				renderHaltedMilestoneTimeline();
				selectHaltedMilestone(idx);
			} catch (e) {
				console.warn('Halted milestone edit failed', e);
			} finally {
				isEditingHaltedMilestone = false;
			}
			// Close the shared edit modal if a close function exists
			if (typeof hideEditMilestoneModal === 'function') hideEditMilestoneModal();
			return;
		}
		// Fallback to original
		return originalSaveMilestoneEdit();
	};
}

// Hook into halted modal population to render timeline by default
if (typeof populateHaltedProjectModal === 'function') {
	const originalPopulateHaltedProjectModal = populateHaltedProjectModal;
	window.populateHaltedProjectModal = function(project) {
		originalPopulateHaltedProjectModal(project);
		renderHaltedMilestoneTimeline();
		// Preselect the halted milestone if any
		const idx = HALTED_MILESTONE_DATA.findIndex(m => m.status === 'halted');
		selectHaltedMilestone(idx >= 0 ? idx : 0);
	};
}
// Admin List of Projects interactivity

const projectsData = [
	{
		id: '#10421',
		owner: { name: 'Olive Ahat', initials: 'OA', avatar: 'https://images.unsplash.com/photo-1502685104226-ee32379fefbe?w=80' },
		verificationStatus: 'Approved',
		progressStatus: 'Completed',
		submittedAt: '2025-11-01',
		updatedAt: '2025-11-17'
	},
	{
		id: '#10422',
		owner: { name: 'Nesty Omongos', initials: 'NO', avatar: 'https://images.unsplash.com/photo-1535713875002-d1d0cf377fde?w=80' },
		verificationStatus: 'Pending',
		progressStatus: 'In Bidding',
		submittedAt: '2025-11-03',
		updatedAt: '2025-11-17',
		projectDetails: {
			propertyType: 'Residential - 2 Storey',
			address: 'Brgy. Sto. Niño, Davao City',
			lotSize: '180 sqm',
			timeline: '6 months',
			budget: 3500000,
			deadline: '2025-12-20',
			description: 'Construction of a two-storey modern minimalist residential house focusing on energy-efficient materials and open-space living areas.'
		},
		bidding: {
			startDate: '2025-11-05',
			endDate: '2025-11-20',
			status: 'Open',
			winningBidder: null,
			files: [
				{ name: 'Blueprint.pdf', type: 'pdf' },
				{ name: 'LotPlan.png', type: 'image' },
				{ name: 'ReqSpecs.docx', type: 'doc' }
			]
		},
		bids: [
			{ 
				id: 'B-2101', 
				companyName: 'SolidBuild Corp', 
				amount: 3400000, 
				duration: '5.5 months', 
				submittedAt: '2025-11-06', 
				status: 'Under Review',
				email: 'contact@solidbuild.com',
				pcabNo: '12345 AB-2026',
				pcabCategory: 'Category B',
				pcabExpiry: '2026-08-13',
				businessPermitNo: 'BP-2024-12345',
				permitCity: 'Davao City',
				businessPermitExpiry: '2025-12-31',
				tin: '123-456-789-000',
				proposedCost: 3400000,
				durationStart: '2025-12-01',
				durationEnd: '2026-05-15',
				description: 'A well-organized project schedule, detailing key milestones and completion timelines with focus on quality materials.',
				supportingFiles: [
					{ name: 'Progress Report', dateSubmitted: '2025-11-06', userName: 'Carl Saludo', position: 'Architect' },
					{ name: 'Progress Report', dateSubmitted: '2025-11-06', userName: 'Carl Saludo', position: 'Architect' },
					{ name: 'Progress Report', dateSubmitted: '2025-11-06', userName: 'Carl Saludo', position: 'Architect' }
				]
			},
			{ 
				id: 'B-2102', 
				companyName: 'PrimeConstruct', 
				amount: 3550000, 
				duration: '6 months', 
				submittedAt: '2025-11-07', 
				status: 'Under Review',
				email: 'info@primeconstruct.com',
				pcabNo: '67890 CD-2027',
				pcabCategory: 'Category A',
				pcabExpiry: '2027-10-20',
				businessPermitNo: 'BP-2024-67890',
				permitCity: 'Davao City',
				businessPermitExpiry: '2025-12-31',
				tin: '987-654-321-000',
				proposedCost: 3550000,
				durationStart: '2025-12-05',
				durationEnd: '2026-06-05',
				description: 'Comprehensive construction plan with premium materials and experienced workforce for timely delivery.',
				supportingFiles: [
					{ name: 'Blueprint Draft', dateSubmitted: '2025-11-07', userName: 'Maria Santos', position: 'Engineer' },
					{ name: 'Cost Estimate', dateSubmitted: '2025-11-07', userName: 'Maria Santos', position: 'Engineer' }
				]
			},
			{ 
				id: 'B-2103', 
				companyName: 'UrbanEdge Builders', 
				amount: 3305000, 
				duration: '5 months', 
				submittedAt: '2025-11-08', 
				status: 'Under Review',
				email: 'hello@urbanedge.com',
				pcabNo: '11223 EF-2026',
				pcabCategory: 'Category B',
				pcabExpiry: '2026-12-15',
				businessPermitNo: 'BP-2024-11223',
				permitCity: 'Davao City',
				businessPermitExpiry: '2025-12-31',
				tin: '456-789-123-000',
				proposedCost: 3305000,
				durationStart: '2025-11-25',
				durationEnd: '2026-04-25',
				description: 'Fast-track construction approach with cost-effective solutions without compromising quality standards.',
				supportingFiles: [
					{ name: 'Timeline Schedule', dateSubmitted: '2025-11-08', userName: 'John Rivera', position: 'Project Manager' }
				]
			}
		]
	},
	{
		id: '#10423',
		owner: { name: 'Jeff Holmes', initials: 'JH', avatar: 'https://images.unsplash.com/photo-1529626455594-4ff0802cfb7e?w=80' },
		verificationStatus: 'Approved',
		progressStatus: 'Ongoing',
		submittedAt: '2025-11-05',
		updatedAt: '2025-11-17',
		projectDetails: {
			title: 'Modern Residential Complex',
			propertyType: 'Residential',
			address: 'Canelar Zamboanga City 7000',
			lotSize: '2500',
			timeline: '8 months',
			budget: 1500000,
			deadline: '2026-02-15'
		},
		bidding: {
			startDate: '2025-09-15',
			endDate: '2025-11-30',
			status: 'Done',
			winningBidder: '#123457',
			files: [
				{ name: 'floor_plan.pdf', type: 'doc' },
				{ name: 'site_photo.jpeg', type: 'image' },
				{ name: 'elevation.pdf', type: 'doc' }
			]
		},
		bids: [
			{
				id: 'B-3002',
				companyName: 'Summit Builders Inc.',
				amount: 1500000,
				duration: '8 months',
				submittedAt: '2025-11-10',
				status: 'Accepted',
				email: 'summitbuilders@gmail.com',
				pcabNo: '54321-CD-2025',
				pcabCategory: 'Category A',
				pcabExpiry: '2026-10-20',
				businessPermitNo: 'BP-2025-1234',
				permitCity: 'Zamboanga City',
				businessPermitExpiry: '2026-01-31',
				tin: '987-654-321-000',
				proposedCost: 1500000,
				durationStart: '2025-12-01',
				durationEnd: '2026-07-31'
			}
		],
		ongoingDetails: {
			contractor: {
				companyName: 'Summit Builders Inc.',
				email: 'summitbuilders@gmail.com',
				pcabNo: '54321-CD-2025',
				pcabCategory: 'Category A',
				pcabExpiry: 'October 20, 2026',
				businessPermitNo: 'BP-2025-1234',
				permitCity: 'Zamboanga City',
				businessPermitExpiry: 'January 31, 2026',
				tin: '987-654-321-000'
			},
			milestones: [
				{ id: 5, title: 'Milestone 5', progress: 100, status: 'completed', date: 'Feb 15, 2026', description: 'Final walkthrough and handover.' },
				{ id: 4, title: 'Milestone 4', progress: 70, status: 'in-progress', date: 'Jan 20, 2026', description: 'Interior and exterior finishing.' },
				{ id: 3, title: 'Milestone 3', progress: 50, status: 'pending', date: 'Dec 30, 2025', description: 'Structural framing and roofing.' },
				{ id: 2, title: 'Milestone 2', progress: 30, status: 'completed', date: 'Nov 25, 2025', description: 'Foundation and utility setup.' },
				{ id: 1, title: 'Milestone 1', progress: 10, status: 'completed', date: 'Oct 15, 2025', description: 'Site clearing and mobilization.' }
			],
			reports: [
				{ title: 'Weekly Report - Nov 25', date: 'Nov 25, 2025', description: 'Foundation work at 70%, utilities installed.' },
				{ title: 'Safety Inspection - Nov 18', date: 'Nov 18, 2025', description: 'All safety protocols met, zero incidents.' }
			],
			payments: [
				{ milestone: 1, period: 'Sept 15 - Oct 5, 2025', amount: 150000, dateOfPayment: 'Oct 5, 2025', uploadedBy: 'Property Owner Name', proofOfPayment: 'sample_photo.jpeg', verificationStatus: 'Verified' },
				{ milestone: 2, period: 'Sept 15 - Oct 5, 2025', amount: 150000, dateOfPayment: 'Oct 5, 2025', uploadedBy: 'Property Owner Name', proofOfPayment: 'sample_photo.jpeg', verificationStatus: 'Pending' },
				{ milestone: 2, period: 'Sept 15 - Oct 5, 2025', amount: 150000, dateOfPayment: 'Oct 5, 2025', uploadedBy: 'Property Owner Name', proofOfPayment: 'sample_photo.jpeg', verificationStatus: 'Pending' },
				{ milestone: 3, period: 'Sept 15 - Oct 5, 2025', amount: 150000, dateOfPayment: 'Oct 5, 2025', uploadedBy: 'Property Owner Name', proofOfPayment: 'sample_photo.jpeg', verificationStatus: 'Invalid Receipt' }
			]
		}
	},
	{
		id: '#10424',
		owner: { name: 'Mar Manon-og', initials: 'MM', avatar: 'https://images.unsplash.com/photo-1494790108377-be9c29b29330?w=80' },
		verificationStatus: 'Rejected',
		progressStatus: 'Halted',
		submittedAt: '2025-11-06',
		updatedAt: '2025-11-16'
	},
	{
		id: '#10425',
		owner: { name: 'Maria Lordes', initials: 'ML', avatar: 'https://images.unsplash.com/photo-1544005313-94ddf0286df2?w=80' },
		verificationStatus: 'Approved',
		progressStatus: 'Cancelled',
		submittedAt: '2025-11-08',
		updatedAt: '2025-11-15'
	},
	{
		id: '#10426',
		owner: { name: 'Carlos Garcia', initials: 'CG', avatar: 'https://images.unsplash.com/photo-1520813792240-56fc4a3765a7?w=80' },
		verificationStatus: 'Approved',
		progressStatus: 'In Bidding',
		submittedAt: '2025-11-10',
		updatedAt: '2025-11-15',
		projectDetails: {
			propertyType: 'Commercial - Café Renovation',
			address: 'Central Business Dist., Cebu City',
			lotSize: '95 sqm',
			timeline: '3 months',
			budget: 1250000,
			deadline: '2025-12-05',
			description: 'Interior and façade renovation of an existing café emphasizing wood accents, improved bar flow, and customer seating optimization.'
		},
		bidding: {
			startDate: '2025-11-11',
			endDate: '2025-11-25',
			status: 'Open',
			winningBidder: null,
			files: [
				{ name: 'ExistingLayout.pdf', type: 'pdf' },
				{ name: 'MoodBoard.jpg', type: 'image' }
			]
		},
		bids: [
			{ 
				id: 'B-3101', 
				companyName: 'RenovaWorks', 
				amount: 1180000, 
				duration: '2.5 months', 
				submittedAt: '2025-11-12', 
				status: 'Under Review',
				email: 'contact@renovaworks.com',
				pcabNo: '55566 GH-2026',
				pcabCategory: 'Category C',
				pcabExpiry: '2026-06-30',
				businessPermitNo: 'BP-2024-55566',
				permitCity: 'Cebu City',
				businessPermitExpiry: '2025-12-31',
				tin: '111-222-333-000',
				proposedCost: 1180000,
				durationStart: '2025-12-10',
				durationEnd: '2026-02-25',
				description: 'Specialized in commercial renovation with modern aesthetic and efficient space utilization.',
				supportingFiles: [
					{ name: 'Renovation Plan', dateSubmitted: '2025-11-12', userName: 'Ana Cruz', position: 'Interior Designer' }
				]
			},
			{ 
				id: 'B-3102', 
				companyName: 'CebuFit Interiors', 
				amount: 1235000, 
				duration: '3 months', 
				submittedAt: '2025-11-13', 
				status: 'Under Review',
				email: 'hello@cebufit.com',
				pcabNo: '77788 IJ-2027',
				pcabCategory: 'Category B',
				pcabExpiry: '2027-03-15',
				businessPermitNo: 'BP-2024-77788',
				permitCity: 'Cebu City',
				businessPermitExpiry: '2025-12-31',
				tin: '222-333-444-000',
				proposedCost: 1235000,
				durationStart: '2025-12-15',
				durationEnd: '2026-03-15',
				description: 'Complete interior transformation with focus on functionality and ambiance for café environment.',
				supportingFiles: [
					{ name: 'Design Mockup', dateSubmitted: '2025-11-13', userName: 'Pedro Lim', position: 'Designer' },
					{ name: 'Material List', dateSubmitted: '2025-11-13', userName: 'Pedro Lim', position: 'Designer' }
				]
			}
		]
	},
	{
		id: '#10427',
		owner: { name: 'Emman Delgado', initials: 'ED', avatar: 'https://images.unsplash.com/photo-1517841905240-472988babdf9?w=80' },
		verificationStatus: 'Approved',
		progressStatus: 'Ongoing',
		submittedAt: '2025-11-12',
		updatedAt: '2025-11-14',
		projectDetails: {
			title: 'Greenfield Commercial Complex',
			propertyType: 'Residential',
			address: 'Tetuan Zamboanga City 7000',
			lotSize: '3000',
			timeline: '6 months',
			budget: 1000000,
			deadline: '2025-11-20'
		},
		bidding: {
			startDate: '2025-10-01',
			endDate: '2025-12-10',
			status: 'Done',
			winningBidder: '#123456',
			files: [
				{ name: 'sample_photo.jpeg', type: 'image' },
				{ name: 'sample_photo.jpeg', type: 'doc' },
				{ name: 'sample_photo.jpeg', type: 'image' }
			]
		},
		bids: [
			{
				id: 'B-3001',
				companyName: 'Panda Construction Company',
				amount: 3500000,
				duration: '6 months',
				submittedAt: '2025-11-05',
				status: 'Accepted',
				email: 'pandaconstruction@gmail.com',
				pcabNo: '12345-AB-2025',
				pcabCategory: 'Category B',
				pcabExpiry: '2026-08-15',
				businessPermitNo: 'BP-2025-0987',
				permitCity: 'Zamboanga City',
				businessPermitExpiry: '2025-12-31',
				tin: '123-456-789-000',
				proposedCost: 3500000,
				durationStart: '2025-12-01',
				durationEnd: '2026-05-30'
			}
		],
		ongoingDetails: {
			contractor: {
				companyName: 'Panda Construction Company',
				email: 'pandaconstruction@gmail.com',
				pcabNo: '12345-AB-2025',
				pcabCategory: 'Category B',
				pcabExpiry: '2026-08-15',
				businessPermitNo: 'BP-2025-0987',
				permitCity: 'Zamboanga City',
				businessPermitExpiry: '2025-12-31',
				tin: '123-456-789-000'
			},
			milestones: [
				{ id: 5, title: 'Milestone 5', progress: 100, status: 'completed', date: 'Dec 15, 2025', description: 'Final inspections and handover.' },
				{ id: 4, title: 'Milestone 4', progress: 80, status: 'in-progress', date: 'Nov 30, 2025', description: 'Interior finishing and fixtures.' },
				{ id: 3, title: 'Milestone 3', progress: 60, status: 'pending', date: 'Nov 10, 2025', description: 'Structural completion and roofing.' },
				{ id: 2, title: 'Milestone 2', progress: 40, status: 'completed', date: 'Oct 25, 2025', description: 'Foundation and framing.' },
				{ id: 1, title: 'Milestone 1', progress: 20, status: 'completed', date: 'Oct 10, 2025', description: 'Site prep and mobilization.' }
			],
			reports: [
				{ title: 'Weekly Report - Nov 28', date: 'Nov 28, 2025', description: 'Interior works at 80%, electrical rough-ins completed.' },
				{ title: 'Safety Audit - Nov 20', date: 'Nov 20, 2025', description: 'No incidents, PPE compliance at 100%.' }
			],
			payments: [
				{ milestone: 1, period: 'Oct 1 - Oct 10, 2025', amount: 200000, dateOfPayment: 'Oct 10, 2025', uploadedBy: 'Emman Delgado', proofOfPayment: 'receipt_m1.pdf', verificationStatus: 'Verified' },
				{ milestone: 2, period: 'Oct 11 - Oct 25, 2025', amount: 250000, dateOfPayment: 'Oct 25, 2025', uploadedBy: 'Emman Delgado', proofOfPayment: 'receipt_m2.pdf', verificationStatus: 'Verified' },
				{ milestone: 3, period: 'Oct 26 - Nov 10, 2025', amount: 300000, dateOfPayment: 'Nov 10, 2025', uploadedBy: 'Emman Delgado', proofOfPayment: 'receipt_m3.pdf', verificationStatus: 'Pending' },
				{ milestone: 4, period: 'Nov 11 - Nov 30, 2025', amount: 350000, dateOfPayment: 'Nov 30, 2025', uploadedBy: 'Emman Delgado', proofOfPayment: 'receipt_m4.pdf', verificationStatus: 'Verified' }
			]
		}
	}
];

const verificationBadgeStyles = {
	Approved: 'bg-emerald-100 text-emerald-700',
	Pending: 'bg-amber-100 text-amber-700',
	Rejected: 'bg-red-100 text-red-700'
};

const progressBadgeStyles = {
	'Completed': 'bg-emerald-100 text-emerald-700',
	'In Bidding': 'bg-indigo-100 text-indigo-700',
	'Ongoing': 'bg-amber-100 text-amber-700',
	'Halted': 'bg-rose-100 text-rose-700',
	'Cancelled': 'bg-gray-200 text-gray-600'
};

document.addEventListener('DOMContentLoaded', () => {
	renderProjectsTable();
	setupFilters();
});

// Holds the project currently shown in Ongoing modal
let currentOngoingProject = null;

function showOngoingProjectModal() {
	const modal = document.getElementById('ongoingProjectModal');
	if (!modal) return;
	modal.classList.remove('hidden');
	document.body.classList.add('overflow-hidden');
}

function hideOngoingProjectModal() {
	const modal = document.getElementById('ongoingProjectModal');
	if (!modal) return;
	modal.classList.add('hidden');
	document.body.classList.remove('overflow-hidden');
}

function onViewProject(id, status) {
	try {
		const normalize = v => (typeof v === 'string' ? v.replace(/^#/, '') : String(v));
		const targetId = normalize(id);
		let project = projectsData.find(p => normalize(p.id) === targetId);
		if (!project && (status === 'Ongoing' || status === 'ongoing')) {
			project = projectsData.find(p => (p.progressStatus || '').toLowerCase() === 'ongoing');
		}
		if (!project) return;
		
		if (status === 'Completed' || project.progressStatus === 'Completed') {
			populateCompletedProjectModal(project);
			showCompletedProjectModal();
			return;
		}
		
		if (status === 'Ongoing' || project.progressStatus === 'Ongoing') {
			// Ensure projectDetails exists for frontend-only display
			project.projectDetails = project.projectDetails || {
				title: 'Greenfield Commercial Complex',
				propertyType: 'Residential',
				address: 'Tetuan Zamboanga City 7000',
				timeline: '6 months',
				lotSize: '3000',
				budget: 1000000,
				deadline: '2025-11-20',
			};
			currentOngoingProject = project;
			populateOngoingProjectModal(project);
			showOngoingProjectModal();
			return;
		}

		if (status === 'In Bidding' || project.progressStatus === 'In Bidding') {
			populateBiddingModal(project);
			showBiddingModal();
			return;
		}

		if (status === 'Halted' || project.progressStatus === 'Halted') {
			populateHaltedProjectModal(project);
			showHaltedProjectModal();
			return;
		}

		if (status === 'Cancelled' || project.progressStatus === 'Cancelled') {
			populateCancelledProjectModal(project);
			showCancelledProjectModal();
			return;
		}

		// Default fallback
		console.log('View project', id, status, project);
	} catch (e) {
		console.error('onViewProject error:', e);
	}
}

function populateOngoingProjectModal(project) {
	const avatarEl = document.getElementById('ongoingOwnerAvatar');
	const ownerNameEl = document.getElementById('ongoingOwnerName');
	if (avatarEl) {
		const avatar = project.owner?.avatar || '';
		avatarEl.innerHTML = avatar
			? `<img src="${avatar}" alt="avatar" class="w-full h-full object-cover rounded-full">`
			: `<div class="w-full h-full rounded-full bg-white text-amber-700 flex items-center justify-center font-bold">${(project.owner?.initials || '--')}</div>`;
	}
	if (ownerNameEl) ownerNameEl.textContent = project.owner?.name || '';

	// Project Details fields
	const pd = project.projectDetails || {};
	const setText = (id, val) => { const el = document.getElementById(id); if (el) el.textContent = val ?? '—'; };
	setText('ongoingProjectTitle', pd.title);
	setText('ongoingProjectType', pd.propertyType);
	setText('ongoingProjectAddress', pd.address);
	setText('ongoingTimeline', pd.timeline);
	setText('ongoingLotSize', pd.lotSize);
	setText('ongoingBudget', pd.budget ? `PHP ${pd.budget.toLocaleString()}` : pd.budget);
	setText('ongoingDeadline', pd.deadline);

	// Uploaded photos and files (simple links like in screenshot)
	const photosEl = document.getElementById('ongoingPhotos');
	const filesEl = document.getElementById('ongoingFiles');
	let files = project.bidding?.files || [];
	// Ensure there are visible sample links even if files are empty (frontend only)
	if (!files.length) {
		files = [
			{ name: 'sample_photo.jpeg', type: 'image' },
			{ name: 'sample_photo.jpeg', type: 'doc' },
			{ name: 'sample_photo.jpeg', type: 'image' },
		];
	}
	if (photosEl) {
		photosEl.innerHTML = files
			.filter(f => f.type === 'image')
			.map(f => `<a href="#" class="text-amber-600 hover:text-amber-700 text-sm inline-flex items-center gap-1"><svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7h18M3 12h18M3 17h18"/></svg>${f.name}</a>`)
			.join('');
	}
	if (filesEl) {
		filesEl.innerHTML = files
			.filter(f => f.type !== 'image')
			.map(f => `<a href="#" class="text-amber-600 hover:text-amber-700 text-sm inline-flex items-center gap-1"><svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7h18M3 12h18M3 17h18"/></svg>${f.name}</a>`)
			.join('');
	}

	// Bidding Summary fields
	const bidsCount = project.bids?.length ?? 0;
	setText('ongoingTotalBids', bidsCount ? bidsCount.toString() : '4');
	setText('ongoingBidStatus', project.bidding?.status || 'Done');
	setText('ongoingWinningBidder', project.bidding?.winningBidder || '#123456');
	setText('ongoingBidStart', project.bidding?.startDate || 'Oct 1 – Dec 10, 2025');
	setText('ongoingBidEnd', project.bidding?.endDate || 'November 20, 2025');
	setText('ongoingLastUpdate', project.updatedAt || '03/12/2025');

	// Contractor Details fields
	const c = project.ongoingDetails?.contractor || {
		companyName: 'Panda Construction Company',
		email: 'pandaconstruction@gmail.com',
		pcabNo: '12345-AB-2025',
		pcabCategory: 'Category B',
		pcabExpiry: 'August 15, 2026',
		businessPermitNo: 'BP-2025-0987',
		permitCity: 'Zamboanga City',
		businessPermitExpiry: 'December 31, 2025',
		tin: '123-456-789-000',
	};
	setText('ongoingContractorName', c.companyName);
	setText('ongoingContractorEmail', c.email);
	setText('ongoingContractorPcab', c.pcabNo);
	setText('ongoingContractorCategory', c.pcabCategory);
	setText('ongoingContractorPcabExpiry', c.pcabExpiry);
	setText('ongoingContractorPermit', c.businessPermitNo);
	setText('ongoingContractorCity', c.permitCity);
	setText('ongoingContractorPermitExpiry', c.businessPermitExpiry);
	setText('ongoingContractorTin', c.tin);

	// Render Milestone Timeline
	renderMilestoneTimeline(project);

	const reportsEl = document.getElementById('ongoingReports');
	// Prefer enhanced renderer if available for interactive cards
	if (typeof renderOngoingReports === 'function') {
		renderOngoingReports(project);
	} else if (reportsEl) {
		const reports = project.ongoingDetails?.reports || [];
		reportsEl.innerHTML = reports
			.map(r => `<div class="p-3 rounded border border-gray-200 mb-2"><div class="font-semibold">${r.title}</div><div class="text-sm text-gray-600">${r.date}</div><div class="text-gray-700 mt-1">${r.description}</div></div>`)
			.join('');
	}

	// Payment Summary
	const payments = project.ongoingDetails?.payments || [];
	const totalPaid = payments.length;
	const totalAmount = payments.reduce((sum, p) => sum + p.amount, 0);
	const lastPayment = payments.length > 0 ? payments[payments.length - 1] : null;
	const verifiedCount = payments.filter(p => p.verificationStatus === 'Verified').length;
	const overallStatus = verifiedCount === totalPaid ? 'Fully Paid' : verifiedCount > 0 ? 'Partially Paid' : 'Pending';

	setText('ongoingPaidCount', `${totalPaid} / 5`);
	setText('ongoingTotalAmount', totalAmount > 0 ? `Php. ${totalAmount.toLocaleString('en-PH', { minimumFractionDigits: 3, maximumFractionDigits: 3 })}` : '—');
	setText('ongoingLastPaymentDate', lastPayment ? lastPayment.dateOfPayment : '—');
	setText('ongoingOverallStatus', overallStatus);

	// Payment Table
	const paymentTable = document.getElementById('ongoingPaymentTable');
	if (paymentTable && payments.length > 0) {
		paymentTable.innerHTML = payments.map(payment => {
			let statusBadgeClass = '';
			if (payment.verificationStatus === 'Verified') {
				statusBadgeClass = 'bg-green-500 text-white';
			} else if (payment.verificationStatus === 'Pending') {
				statusBadgeClass = 'bg-yellow-500 text-white';
			} else if (payment.verificationStatus === 'Invalid Receipt') {
				statusBadgeClass = 'bg-red-500 text-white';
			}
			return `
				<tr class="hover:bg-gray-50 transition-colors">
					<td class="px-4 py-3 text-gray-900">${payment.milestone}</td>
					<td class="px-4 py-3 text-gray-600">${payment.period}</td>
					<td class="px-4 py-3 text-gray-900 font-semibold">₱${payment.amount.toLocaleString('en-PH', { minimumFractionDigits: 2 })}</td>
					<td class="px-4 py-3 text-gray-600">${payment.dateOfPayment}</td>
					<td class="px-4 py-3 text-gray-600">${payment.uploadedBy}</td>
					<td class="px-4 py-3">
						<a href="#" class="inline-flex items-center gap-1 text-indigo-600 hover:text-indigo-800 text-xs">
							<svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
								<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"/>
							</svg>
							${payment.proofOfPayment}
						</a>
					</td>
					<td class="px-4 py-3">
						<span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold ${statusBadgeClass}">
							${payment.verificationStatus}
						</span>
					</td>
				</tr>
			`;
		}).join('');
	} else if (paymentTable) {
		paymentTable.innerHTML = `
			<tr>
				<td colspan="7" class="px-4 py-8 text-center text-gray-400 text-sm">No payment records available</td>
			</tr>
		`;
	}
}

// Global milestone data that both functions will use
const MILESTONE_DATA = [
	{
		id: 1,
		progress: 100,
		title: 'Milestone 5',
		date: '12 Dec 9:00 PM',
		description: 'People care about how you see the world, how you think, what motivates you, what you\'re struggling with or afraid of People care about how you see the world, how you think, what motivates you, what you\'re struggling with or afraid of.',
		status: 'pending'
	},
	{
		id: 2,
		progress: 80,
		title: 'Milestone 4',
		date: '21 DEC 11 PM',
		description: 'Construction phase completion with all structural work finished. Quality assurance tests conducted and passed successfully.',
		status: 'pending',
	},
	{
		id: 3,
		progress: 60,
		title: 'Milestone 3',
		date: '21 DEC 9:34 PM',
		description: 'Mid-project review completed. Foundation and framework construction in progress with scheduled timeline adjustments.',
		status: 'in-progress',
		reports: [
			{ title: 'Progress Report 3', date: '21 Dec 9:34 PM', description: 'Currently in progress, working on key deliverables. Framework 70% complete.' },
			{ title: 'Progress Report 3.2', date: '21 Dec 9:34 PM', description: 'Additional materials ordered for next phase. Timeline on track.' }
		],
		supportingFiles: ['progress_photos.jpeg', 'material_list.pdf']
	},
	{
		id: 4,
		progress: 40,
		title: 'Milestone 2',
		date: '20 DEC 2:30 AM',
		description: 'Site preparation and foundation work completed. All permits approved and documentation submitted to regulatory authorities.',
		status: 'completed',
		reports: [
			{ title: 'Progress Report 2', date: '20 Dec 2:30 AM', description: 'Foundation work completed ahead of schedule. All quality checks passed.' },
			{ title: 'Progress Report 2.2', date: '20 Dec 2:30 AM', description: 'Permit documentation submitted and approved by local authorities.' },
			{ title: 'Progress Report 2.3', date: '20 Dec 2:30 AM', description: 'Permit documentation submitted and approved by local authorities.' },
			{ title: 'Progress Report 2.4', date: '20 Dec 2:30 AM', description: 'Permit documentation submitted and approved by local authorities.' }
		],
		supportingFiles: ['foundation_report.pdf', 'permit_docs.jpeg']
	},
	{
		id: 5,
		progress: 20,
		title: 'Milestone 1',
		date: '18 DEC 4:54 AM',
		description: 'Initial project kickoff and planning phase completed. Site survey conducted and design plans finalized.',
		status: 'completed',
		reports: [
			{ title: 'Progress Report 1', date: '20 Dec 2:30 AM', description: 'Foundation work completed ahead of schedule. All quality checks passed.' },
			{ title: 'Progress Report 1.2', date: '20 Dec 2:30 AM', description: 'Permit documentation submitted and approved by local authorities.' },
			{ title: 'Progress Report 1.3', date: '20 Dec 2:30 AM', description: 'Permit documentation submitted and approved by local authorities.' },
			{ title: 'Progress Report 1.4', date: '20 Dec 2:30 AM', description: 'Permit documentation submitted and approved by local authorities.' }
		],
		supportingFiles: ['site_survey.pdf', 'design_plans.jpeg', 'approval_doc.pdf']
	}
];

function renderMilestoneTimeline(project) {
	const container = document.getElementById('ongoingMilestoneTimeline');
	if (!container) return;

	let html = '';
	MILESTONE_DATA.forEach((milestone, index) => {
		const isLast = index === MILESTONE_DATA.length - 1;
		const statusClass = milestone.status === 'completed' ? 'completed' : milestone.status === 'in-progress' ? 'in-progress' : 'pending';
		const statusIcon = milestone.status === 'completed' ? '✓' : milestone.status === 'in-progress' ? '◉' : '○';
		
		html += `
			<div class="milestone-item ${statusClass}" data-milestone-id="${milestone.id}" onclick="selectMilestone(${milestone.id})">
				<div class="milestone-left">
					<div class="milestone-progress-circle">${milestone.progress}%</div>
					${!isLast ? '<div class="milestone-connector"></div>' : ''}
				</div>
				<div class="milestone-middle">
					<div class="milestone-status-line ${statusClass}"></div>
					<div class="milestone-status-dot ${statusClass}">${statusIcon}</div>
				</div>
				<div class="milestone-right">
					<div class="milestone-content-card">
						<div class="milestone-header">
							<div>
								<h4 class="milestone-title">${milestone.title}</h4>
								<p class="milestone-date">${milestone.date}</p>
							</div>
							<span class="milestone-badge ${statusClass}">
								${milestone.status === 'completed' ? 'Completed' : milestone.status === 'in-progress' ? 'In Progress' : 'Pending'}
							</span>
						</div>
						<p class="milestone-description">${milestone.description}</p>
						<button class="milestone-view-link" onclick="event.stopPropagation(); selectMilestone(${milestone.id})">View Details</button>
					</div>
				</div>
			</div>
		`;
	});

	container.innerHTML = html;

	// Auto-select the in-progress milestone or the first one
	const inProgressMilestone = MILESTONE_DATA.find(m => m.status === 'in-progress');
	selectMilestone(inProgressMilestone ? inProgressMilestone.id : MILESTONE_DATA[0].id);
}

function selectMilestone(milestoneId) {
	const detailsContainer = document.getElementById('ongoingDetails');
	if (!detailsContainer) return;

	const milestone = MILESTONE_DATA.find(m => m.id === milestoneId);
	if (!milestone) return;

	// Update active state in timeline
	document.querySelectorAll('.milestone-item').forEach(item => {
		if (parseInt(item.dataset.milestoneId) === milestoneId) {
			item.classList.add('active');
		} else {
			item.classList.remove('active');
		}
	});

	// Render details panel with the specific milestone's data
	const statusClass = milestone.status === 'completed' ? 'completed' : milestone.status === 'in-progress' ? 'in-progress' : 'pending';
	const statusText = milestone.status === 'completed' ? 'Completed' : milestone.status === 'in-progress' ? 'In Progress' : 'Pending';

	let html = `
		<div class="detail-card">
			<div class="detail-header">
				<h4 class="detail-title">${milestone.title}</h4>
				<span class="detail-badge ${statusClass}">${statusText}</span>
			</div>
			<p class="detail-date">${milestone.date}</p>
			<p class="detail-description">${milestone.description}</p>
		</div>

		<div class="detail-card">
			<h5 class="detail-section-title">Supporting Files</h5>
			<div class="detail-files">
				${milestone.supportingFiles && milestone.supportingFiles.length > 0 
					? milestone.supportingFiles.map(file => `
						<a href="#" class="detail-file-link">
							<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
								<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"/>
							</svg>
							${file}
						</a>
					`).join('')
					: '<p class="text-sm text-gray-500">No supporting files available</p>'
				}
			</div>
		</div>

	<div class="detail-card">
		<div class="flex items-center justify-between mb-4">
			<h5 class="detail-section-title mb-0">List of Reports</h5>
			<button onclick="event.stopPropagation(); openEditReportModal(${milestoneId})" class="text-orange-600 hover:text-orange-700 hover:scale-105 transition-transform text-xs font-semibold flex items-center gap-1" title="Edit Reports">
				<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
					<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/>
				</svg>
				Edit
			</button>
		</div>
		<div class="detail-reports">
			${milestone.reports && milestone.reports.length > 0
				? milestone.reports.map((report, index) => `
					<div class="report-item">
						<div class="report-header">
							<h6 class="report-title">${report.title}</h6>
							<span class="report-date">${report.date}</span>
						</div>
						<p class="report-description">${report.description}</p>
						<a href="#" onclick="event.preventDefault(); openProgressReportModal(${milestoneId}, ${index})" class="report-view-link">View Details</a>
					</div>
				`).join('')
				: '<p class="text-sm text-gray-500">No reports available</p>'
			}
		</div>
	</div>
`;

detailsContainer.innerHTML = html;
}function renderProjectsTable() {
	const tbody = document.getElementById('projectsTableBody');
	const verificationFilter = document.getElementById('verificationFilter').value;
	const progressFilter = document.getElementById('progressFilter').value;
	const searchInput = document.querySelector('header input[type="text"]').value.trim().toLowerCase();

	const filtered = projectsData.filter(p => {
		const matchesVerification = verificationFilter === 'all' || p.verificationStatus === verificationFilter;
		const matchesProgress = progressFilter === 'all' || p.progressStatus === progressFilter;
		const matchesSearch = p.owner.name.toLowerCase().includes(searchInput) || p.id.toLowerCase().includes(searchInput);
		return matchesVerification && matchesProgress && matchesSearch;
	});

	tbody.innerHTML = filtered.map(project => `
		<tr class="hover:bg-orange-50 transition">
			<td class="px-6 py-4">
				<div class="flex items-center gap-3 min-w-[180px]">
					<div class="w-10 h-10 rounded-full bg-gradient-to-br from-orange-400 to-orange-600 text-white flex items-center justify-center overflow-hidden shadow-md">
						${project.owner.avatar ? `<img src="${project.owner.avatar}" alt="${project.owner.name}" class="w-full h-full object-cover" />` : `<span class="text-xs font-semibold">${project.owner.initials}</span>`}
					</div>
					<div class="flex flex-col">
						<span class="font-semibold text-gray-800">${project.owner.name}</span>
						<span class="text-xs text-gray-500">Property Owner</span>
					</div>
				</div>
			</td>
			<td class="px-6 py-4 font-semibold text-gray-700">${project.id}</td>
			<td class="px-6 py-4">
				<span class="inline-flex px-3 py-1 rounded-full text-xs font-semibold ${verificationBadgeStyles[project.verificationStatus]}">${project.verificationStatus}</span>
			</td>
			<td class="px-6 py-4">
				<span class="inline-flex px-3 py-1 rounded-full text-xs font-semibold ${progressBadgeStyles[project.progressStatus]}">${project.progressStatus}</span>
			</td>
			<td class="px-6 py-4 text-gray-600">${formatDisplayDate(project.submittedAt)}</td>
			<td class="px-6 py-4 text-gray-600">${formatDisplayDate(project.updatedAt)}</td>
			<td class="px-6 py-4">
				<div class="flex items-center justify-center gap-2">
					<button class="w-8 h-8 rounded-lg bg-indigo-100 hover:bg-indigo-200 text-indigo-700 flex items-center justify-center shadow-sm transition" title="View" data-id="${project.id}" onclick="onViewProject('${project.id}')">
						<i class="fi fi-rr-eye text-sm"></i>
					</button>
					<button class="w-8 h-8 rounded-lg bg-orange-100 hover:bg-orange-200 text-orange-600 flex items-center justify-center shadow-sm transition" title="Edit" data-id="${project.id}" onclick="openEditProjectModal('${project.id}')">
						<i class="fi fi-rr-edit text-sm"></i>
					</button>
					<button class="w-8 h-8 rounded-lg bg-red-100 hover:bg-red-200 text-red-600 flex items-center justify-center shadow-sm transition" title="Delete" data-id="${project.id}" onclick="onDeleteProject('${project.id}')">
						<i class="fi fi-rr-trash text-sm"></i>
					</button>
				</div>
			</td>
		</tr>
	`).join('');
}

function setupFilters() {
	document.getElementById('verificationFilter').addEventListener('change', renderProjectsTable);
	document.getElementById('progressFilter').addEventListener('change', renderProjectsTable);
	document.querySelector('header input[type="text"]').addEventListener('input', debounce(renderProjectsTable, 200));
	document.getElementById('exportProjectsBtn').addEventListener('click', exportProjectsCSV);
}

function formatDisplayDate(dateStr) {
	const d = new Date(dateStr);
	return d.toLocaleDateString('en-US', { month: '2-digit', day: '2-digit', year: 'numeric' });
}

function debounce(fn, wait) {
	let t;
	return (...args) => {
		clearTimeout(t);
		t = setTimeout(() => fn.apply(this, args), wait);
	};
}

function onEditProject(id) {
	console.log('Edit project', id);
	// Placeholder for future edit modal
}

let projectToDelete = null;

function onDeleteProject(id) {
	console.log('Delete project', id);
	projectToDelete = id;
	showDeleteProjectModal(id);
}

function showDeleteProjectModal(id) {
	const modal = document.getElementById('deleteProjectModal');
	const projectIdSpan = document.getElementById('deleteProjectId');
	
	if (projectIdSpan) {
		projectIdSpan.textContent = `#${id}`;
	}
	
	if (modal) {
		modal.classList.remove('hidden');
	}
}

function hideDeleteProjectModal() {
	const modal = document.getElementById('deleteProjectModal');
	if (modal) {
		modal.classList.add('hidden');
	}
	projectToDelete = null;
}

function confirmDeleteProject() {
	if (!projectToDelete) {
		console.error('No project selected for deletion');
		return;
	}
	
	console.log('Confirmed deletion of project:', projectToDelete);
	
	// TODO: Implement actual delete logic here
	// Example:
	// fetch(`/api/projects/${projectToDelete}`, {
	// 	method: 'DELETE',
	// 	headers: {
	// 		'Content-Type': 'application/json',
	// 		'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
	// 	}
	// })
	// .then(response => response.json())
	// .then(data => {
	// 	if (data.success) {
	// 		// Remove the project from the table
	// 		renderProjects();
	// 		alert('Project deleted successfully');
	// 	}
	// })
	// .catch(error => console.error('Error deleting project:', error));
	
	// For now, just show success message
	alert(`Project #${projectToDelete} has been deleted successfully.`);
	
	hideDeleteProjectModal();
	
	// Optionally refresh the table
	// renderProjects();
}

function exportProjectsCSV() {
	const rows = [['Project ID','Property Owner','Verification Status','Progress Status','Date Submitted','Last Updated']];
	const verificationFilter = document.getElementById('verificationFilter').value;
	const progressFilter = document.getElementById('progressFilter').value;
	const searchInput = document.querySelector('header input[type="text"]').value.trim().toLowerCase();
	const filtered = projectsData.filter(p => {
		const matchesVerification = verificationFilter === 'all' || p.verificationStatus === verificationFilter;
		const matchesProgress = progressFilter === 'all' || p.progressStatus === progressFilter;
		const matchesSearch = p.owner.name.toLowerCase().includes(searchInput) || p.id.toLowerCase().includes(searchInput);
		return matchesVerification && matchesProgress && matchesSearch;
	});
	filtered.forEach(p => {
		rows.push([p.id, p.owner.name, p.verificationStatus, p.progressStatus, formatDisplayDate(p.submittedAt), formatDisplayDate(p.updatedAt)]);
	});
	const csvContent = 'data:text/csv;charset=utf-8,' + rows.map(r => r.map(v => '"'+v+'"').join(',')).join('\n');
	const a = document.createElement('a');
	a.setAttribute('download', 'projects_export.csv');
	document.body.appendChild(a);
	// ---------------- Ongoing Project modal logic ----------------
	function showOngoingProjectModal() {
		document.getElementById('ongoingProjectModal').classList.remove('hidden');
		document.body.classList.add('overflow-hidden');
	}

	function hideOngoingProjectModal() {
		document.getElementById('ongoingProjectModal').classList.add('hidden');
		document.body.classList.remove('overflow-hidden');
	}

	function populateOngoingProjectModal(project) {
		currentOngoingProject = project;

		// Owner
		const avatar = document.getElementById('ongoingOwnerAvatar');
		if (project.owner.avatar) {
			avatar.innerHTML = `<img src="${project.owner.avatar}" alt="${project.owner.name}" class="w-full h-full object-cover">`;
		} else {
			avatar.innerHTML = `<span class="text-sm font-semibold text-amber-600">${project.owner.initials}</span>`;
		}
		document.getElementById('ongoingOwnerName').textContent = project.owner.name;

		// Project details
		const pd = project.projectDetails || {};
		document.getElementById('ongoingProjectTitle').textContent = pd.title || '—';
		document.getElementById('ongoingProjectType').textContent = pd.propertyType || '—';
		document.getElementById('ongoingProjectAddress').textContent = pd.address || '—';
		document.getElementById('ongoingTimeline').textContent = pd.timeline || '—';
		document.getElementById('ongoingLotSize').textContent = pd.lotSize || '—';
		document.getElementById('ongoingBudget').textContent = pd.budget ? formatCurrency(pd.budget) : '—';
		document.getElementById('ongoingDeadline').textContent = pd.deadline ? formatDisplayDate(pd.deadline) : '—';

		// Files
		const files = project.bidding?.files || [];
		document.getElementById('ongoingPhotos').innerHTML = files.filter(f => f.type === 'image').map(f => `
			<span class="inline-flex items-center gap-1 px-2 py-1 rounded bg-orange-50 text-orange-700 text-xs">
				<i class="fi fi-rr-picture text-xs"></i>${f.name}
			</span>
		`).join('') || '<span class="text-gray-400 text-xs">No photos</span>';
		document.getElementById('ongoingFiles').innerHTML = files.filter(f => f.type !== 'image').map(f => `
			<span class="inline-flex items-center gap-1 px-2 py-1 rounded bg-orange-50 text-orange-700 text-xs">
				<i class="fi fi-rr-document text-xs"></i>${f.name}
			</span>
		`).join('') || '<span class="text-gray-400 text-xs">No files</span>';

		// Contractor
		const contractor = project.ongoingDetails?.contractor || project.bids?.find(b => b.status === 'Accepted') || {};
		document.getElementById('ongoingContractorName').textContent = contractor.companyName || '—';
		document.getElementById('ongoingContractorEmail').textContent = contractor.email || '—';
		document.getElementById('ongoingContractorPcab').textContent = contractor.pcabNo || '—';
		document.getElementById('ongoingContractorCategory').textContent = contractor.pcabCategory || '—';
		document.getElementById('ongoingContractorPcabExpiry').textContent = contractor.pcabExpiry ? formatDisplayDate(contractor.pcabExpiry) : '—';
		document.getElementById('ongoingContractorPermit').textContent = contractor.businessPermitNo || '—';
		document.getElementById('ongoingContractorCity').textContent = contractor.permitCity || '—';
		document.getElementById('ongoingContractorPermitExpiry').textContent = contractor.businessPermitExpiry ? formatDisplayDate(contractor.businessPermitExpiry) : '—';
		document.getElementById('ongoingContractorTin').textContent = contractor.tin || '—';

		// Bidding summary
		const bidding = project.bidding || {};
		document.getElementById('ongoingTotalBids').textContent = (project.bids?.length || 0).toString();
		document.getElementById('ongoingBidStatus').textContent = bidding.status || '—';
		document.getElementById('ongoingBidStart').textContent = bidding.startDate ? formatDisplayDate(bidding.startDate) : '—';
		document.getElementById('ongoingBidEnd').textContent = bidding.endDate ? formatDisplayDate(bidding.endDate) : '—';
		document.getElementById('ongoingWinningBidder').textContent = bidding.winningBidder || '—';
		document.getElementById('ongoingLastUpdate').textContent = project.updatedAt ? formatDisplayDate(project.updatedAt) : '—';

		// Milestones timeline
		const milestones = project.ongoingDetails?.milestones || [];
		renderMilestoneTimeline(project, milestones);

		// Reports
		const reports = project.ongoingDetails?.reports || [];
		document.getElementById('ongoingReports').innerHTML = reports.map(r => `
			<div class="bg-orange-50 border border-orange-200 rounded-lg p-4 space-y-2">
				<div class="flex items-start justify-between gap-2">
					<h4 class="text-sm font-semibold text-gray-900">${r.title}</h4>
					<button class="text-amber-600 hover:text-amber-700">
						<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/></svg>
					</button>
				</div>
				<p class="text-xs text-gray-500">${r.date}</p>
				<p class="text-xs text-gray-600 leading-relaxed">${r.description}</p>
				<button class="text-xs text-amber-600 hover:text-amber-700 font-medium">View Details ></button>
			</div>
		`).join('') || '<span class="text-xs text-gray-400">No reports</span>';

		// Payment Summary
		const payments = project.ongoingDetails?.payments || [];
		const totalPaid = payments.length;
		const totalAmount = payments.reduce((sum, p) => sum + p.amount, 0);
		const lastPayment = payments.length > 0 ? payments[payments.length - 1] : null;
		const verifiedCount = payments.filter(p => p.verificationStatus === 'Verified').length;
		const overallStatus = verifiedCount === totalPaid ? 'Fully Paid' : verifiedCount > 0 ? 'Partially Paid' : 'Pending';

		document.getElementById('ongoingPaidCount').textContent = `${totalPaid} / 5`;
		document.getElementById('ongoingTotalAmount').textContent = totalAmount > 0 ? `Php. ${totalAmount.toLocaleString('en-PH', { minimumFractionDigits: 3, maximumFractionDigits: 3 })}` : '—';
		document.getElementById('ongoingLastPaymentDate').textContent = lastPayment ? lastPayment.dateOfPayment : '—';
		document.getElementById('ongoingOverallStatus').textContent = overallStatus;

		// Payment Table
		const paymentTable = document.getElementById('ongoingPaymentTable');
		if (payments.length > 0) {
			paymentTable.innerHTML = payments.map(payment => {
				let statusBadgeClass = '';
				if (payment.verificationStatus === 'Verified') {
					statusBadgeClass = 'bg-green-500 text-white';
				} else if (payment.verificationStatus === 'Pending') {
					statusBadgeClass = 'bg-yellow-500 text-white';
				} else if (payment.verificationStatus === 'Invalid Receipt') {
					statusBadgeClass = 'bg-red-500 text-white';
				}
				return `
					<tr class="hover:bg-gray-50 transition-colors">
						<td class="px-4 py-3 text-gray-900">${payment.milestone}</td>
						<td class="px-4 py-3 text-gray-600">${payment.period}</td>
						<td class="px-4 py-3 text-gray-900 font-semibold">₱${payment.amount.toLocaleString('en-PH', { minimumFractionDigits: 2 })}</td>
						<td class="px-4 py-3 text-gray-600">${payment.dateOfPayment}</td>
						<td class="px-4 py-3 text-gray-600">${payment.uploadedBy}</td>
						<td class="px-4 py-3">
							<a href="#" class="inline-flex items-center gap-1 text-indigo-600 hover:text-indigo-800 text-xs">
								<svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
									<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"/>
								</svg>
								${payment.proofOfPayment}
							</a>
						</td>
						<td class="px-4 py-3">
							<span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold ${statusBadgeClass}">
								${payment.verificationStatus}
							</span>
						</td>
					</tr>
				`;
			}).join('');
		} else {
			paymentTable.innerHTML = `
				<tr>
					<td colspan="7" class="px-4 py-8 text-center text-gray-400 text-sm">No payment records available</td>
				</tr>
			`;
		}
	}

	function viewOngoingBidDetails() {
		const project = currentOngoingProject;
		if (!project || !project.bids) return;
		const winningBid = project.bids.find(b => b.status === 'Accepted') || project.bids[0];
		if (!winningBid) return;
		populateBidStatusModal(project, winningBid);
		showBidStatusModal();
	}

	a.click();
	document.body.removeChild(a);
}

// ---------------- BIDDING MODAL LOGIC ----------------
function showBiddingModal() {
	document.getElementById('biddingDetailsModal').classList.remove('hidden');
	document.body.classList.add('overflow-hidden');
}
function hideBiddingModal() {
	document.getElementById('biddingDetailsModal').classList.add('hidden');
	document.body.classList.remove('overflow-hidden');
}

function populateBiddingModal(project) {
	// Owner section
	const avatarEl = document.getElementById('modalOwnerAvatar');
	avatarEl.innerHTML = project.owner.avatar
		? `<img src="${project.owner.avatar}" alt="${project.owner.name}" class="w-full h-full object-cover" />`
		: `<span class='text-xl font-semibold'>${project.owner.initials}</span>`;
	document.getElementById('modalOwnerName').textContent = project.owner.name;
	document.getElementById('modalProjectId').textContent = project.id;
	document.getElementById('modalSubmittedAt').textContent = `Submitted: ${formatDisplayDate(project.submittedAt)}`;

	// Badges
	const verBadge = document.getElementById('modalVerificationBadge');
	verBadge.textContent = project.verificationStatus;
	verBadge.className = `inline-flex px-3 py-1 rounded-full text-xs font-semibold ${verificationBadgeStyles[project.verificationStatus]}`;
	const progBadge = document.getElementById('modalProgressBadge');
	progBadge.textContent = project.progressStatus;
	progBadge.className = `inline-flex px-3 py-1 rounded-full text-xs font-semibold ${progressBadgeStyles[project.progressStatus]}`;

	// Project Details
	if (project.projectDetails) {
		document.getElementById('modalPropertyType').textContent = project.projectDetails.propertyType;
		document.getElementById('modalAddress').textContent = project.projectDetails.address;
		document.getElementById('modalLotSize').textContent = project.projectDetails.lotSize;
		document.getElementById('modalTimeline').textContent = project.projectDetails.timeline;
		document.getElementById('modalBudget').textContent = formatCurrency(project.projectDetails.budget);
		document.getElementById('modalDeadline').textContent = formatDisplayDate(project.projectDetails.deadline);
		document.getElementById('modalDescription').textContent = project.projectDetails.description;
	}

	// Bidding Summary
	if (project.bidding) {
		document.getElementById('modalBidStart').textContent = formatDisplayDate(project.bidding.startDate);
		document.getElementById('modalBidEnd').textContent = formatDisplayDate(project.bidding.endDate);
		document.getElementById('modalBidStatus').textContent = project.bidding.status;
		const winning = project.bidding.winningBidder || '—';
		document.getElementById('modalWinningBidder').textContent = winning;
		const filesWrap = document.getElementById('modalFiles');
		filesWrap.innerHTML = (project.bidding.files || []).map(f => fileBadge(f)).join('');
	}

	// Bids Table
	const bidsBody = document.getElementById('bidsTableBody');
	const bids = project.bids || [];
	bidsBody.innerHTML = bids.map(b => `
		<tr class="hover:bg-indigo-50 transition">
			<td class="px-6 py-3 font-medium text-gray-800">${b.companyName}</td>
			<td class="px-6 py-3 text-gray-700">${formatCurrency(b.amount)}</td>
			<td class="px-6 py-3 text-gray-600">${b.duration}</td>
			<td class="px-6 py-3 text-gray-600">${formatDisplayDate(b.submittedAt)}</td>
			<td class="px-6 py-3">
				<span class="inline-flex px-3 py-1 rounded-full text-xs font-semibold ${bidStatusStyles(b.status)}">${b.status}</span>
			</td>
			<td class="px-6 py-3">
				<div class="flex items-center justify-center gap-2">
					<button class="w-8 h-8 rounded-lg bg-indigo-100 hover:bg-indigo-200 text-indigo-700 flex items-center justify-center text-xs font-semibold shadow-sm transition" title="View Bid" onclick="onViewBid('${project.id}','${b.id}')">
						<i class="fi fi-rr-eye"></i>
					</button>
					<button class="w-8 h-8 rounded-lg bg-emerald-100 hover:bg-emerald-200 text-emerald-700 flex items-center justify-center text-xs font-semibold shadow-sm transition" title="Accept" onclick="acceptBid('${project.id}','${b.id}')">
						<i class="fi fi-rr-check"></i>
					</button>
					<button class="w-8 h-8 rounded-lg bg-rose-100 hover:bg-rose-200 text-rose-700 flex items-center justify-center text-xs font-semibold shadow-sm transition" title="Reject" onclick="rejectBid('${project.id}','${b.id}')">
						<i class="fi fi-rr-cross-small"></i>
					</button>
				</div>
			</td>
		</tr>
	`).join('');

	// Export bids handler
	const exportBtn = document.getElementById('exportBidsBtn');
	exportBtn.onclick = () => exportBidsCSV(project);
}

function bidStatusStyles(status) {
	const map = {
		'Under Review': 'bg-indigo-100 text-indigo-700',
		'Accepted': 'bg-emerald-100 text-emerald-700',
		'Rejected': 'bg-rose-100 text-rose-700'
	};
	return map[status] || 'bg-gray-100 text-gray-600';
}

function fileBadge(f) {
	const iconMap = { pdf: 'fi fi-rr-file-pdf', image: 'fi fi-rr-picture', doc: 'fi fi-rr-file' };
	const icon = iconMap[f.type] || 'fi fi-rr-file';
	return `<span class="inline-flex items-center gap-1 px-3 py-1 rounded-full text-xs font-medium bg-indigo-100 text-indigo-700"><i class='${icon}'></i>${f.name}</span>`;
}

function formatCurrency(n) {
	if (typeof n !== 'number') return n;
	return '₱' + n.toLocaleString('en-PH');
}

function exportBidsCSV(project) {
	const rows = [['Bid ID','Company','Amount','Duration','Submitted','Status']];
	(project.bids || []).forEach(b => {
		rows.push([b.id, b.companyName, b.amount, b.duration, formatDisplayDate(b.submittedAt), b.status]);
	});
	const csvContent = 'data:text/csv;charset=utf-8,' + rows.map(r => r.map(v => '"'+v+'"').join(',')).join('\n');
	const a = document.createElement('a');
	a.href = encodeURI(csvContent);
	a.download = project.id.replace('#','') + '_bids_export.csv';
	document.body.appendChild(a); a.click(); document.body.removeChild(a);
}

// ----- Bid Actions -----
function findProject(id) {
	return projectsData.find(p => p.id === id);
}

function onViewBid(projectId, bidId) {
	const project = findProject(projectId);
	if (!project) return;
	const bid = (project.bids || []).find(b => b.id === bidId);
	if (!bid) return;
	populateBidStatusModal(project, bid);
	showBidStatusModal();
}

function acceptBid(projectId, bidId) {
	const project = findProject(projectId);
	if (!project || !project.bids) return;
	const bid = project.bids.find(b => b.id === bidId);
	if (!bid) return;
	
	// Show confirmation modal
	showAcceptBidModal(project, bid);
}

function rejectBid(projectId, bidId) {
	const project = findProject(projectId);
	if (!project || !project.bids) return;
	const bid = project.bids.find(b => b.id === bidId);
	if (!bid) return;
	
	// Do not reject if already accepted
	if (bid.status === 'Accepted') {
		alert('Cannot reject an accepted bid.');
		return;
	}
	
	// Show confirmation modal
	showRejectBidModal(project, bid);
}

// Store current action context
let currentActionContext = { projectId: null, bidId: null };

// Accept Bid Modal Functions
function showAcceptBidModal(project, bid) {
	currentActionContext = { projectId: project.id, bidId: bid.id };
	document.getElementById('acceptBidCompany').textContent = bid.companyName;
	document.getElementById('acceptBidAmount').textContent = formatCurrency(bid.amount);
	document.getElementById('acceptBidDuration').textContent = bid.duration;
	document.getElementById('acceptBidModal').classList.remove('hidden');
	document.body.classList.add('overflow-hidden');
}

function hideAcceptBidModal() {
	document.getElementById('acceptBidModal').classList.add('hidden');
	document.body.classList.remove('overflow-hidden');
	currentActionContext = { projectId: null, bidId: null };
}

function confirmAcceptBid() {
	const { projectId, bidId } = currentActionContext;
	if (!projectId || !bidId) return;
	
	const project = findProject(projectId);
	if (!project || !project.bids) return;
	
	// Prevent multiple accepted bids
	project.bids.forEach(b => { if (b.status === 'Accepted') b.status = 'Under Review'; });
	const bid = project.bids.find(b => b.id === bidId);
	if (!bid) return;
	
	bid.status = 'Accepted';
	if (project.bidding) project.bidding.winningBidder = bid.companyName;
	
	hideAcceptBidModal();
	refreshBiddingModal(project);
	
	// Show success notification (you can replace with a toast notification)
	showNotification('Bid accepted successfully!', 'success');
}

// Reject Bid Modal Functions
function showRejectBidModal(project, bid) {
	currentActionContext = { projectId: project.id, bidId: bid.id };
	document.getElementById('rejectBidCompany').textContent = bid.companyName;
	document.getElementById('rejectBidAmount').textContent = formatCurrency(bid.amount);
	document.getElementById('rejectBidDuration').textContent = bid.duration;
	document.getElementById('rejectReason').value = '';
	document.getElementById('rejectBidModal').classList.remove('hidden');
	document.body.classList.add('overflow-hidden');
}

function hideRejectBidModal() {
	document.getElementById('rejectBidModal').classList.add('hidden');
	document.body.classList.remove('overflow-hidden');
	currentActionContext = { projectId: null, bidId: null };
}

function confirmRejectBid() {
	const { projectId, bidId } = currentActionContext;
	if (!projectId || !bidId) return;
	
	const project = findProject(projectId);
	if (!project || !project.bids) return;
	const bid = project.bids.find(b => b.id === bidId);
	if (!bid) return;
	
	const reason = document.getElementById('rejectReason').value.trim();
	bid.status = 'Rejected';
	if (reason) {
		bid.rejectionReason = reason;
	}
	
	hideRejectBidModal();
	refreshBiddingModal(project);
	
	// Show success notification
	showNotification('Bid rejected successfully!', 'error');
}

// Simple notification function (you can enhance this with a toast library)
function showNotification(message, type) {
	const colors = {
		success: 'bg-emerald-500',
		error: 'bg-rose-500',
		info: 'bg-indigo-500'
	};
	
	const notification = document.createElement('div');
	notification.className = `fixed top-4 right-4 ${colors[type] || colors.info} text-white px-6 py-3 rounded-lg shadow-lg z-[60] transform transition-all duration-300`;
	notification.textContent = message;
	document.body.appendChild(notification);
	
	setTimeout(() => {
		notification.style.opacity = '0';
		notification.style.transform = 'translateY(-20px)';
		setTimeout(() => notification.remove(), 300);
	}, 3000);
}

function refreshBiddingModal(project) {
	populateBiddingModal(project);
}

// ----- Bid Status Modal -----
function showBidStatusModal() {
	document.getElementById('bidStatusModal').classList.remove('hidden');
	document.body.classList.add('overflow-hidden');
}

function hideBidStatusModal() {
	document.getElementById('bidStatusModal').classList.add('hidden');
	document.body.classList.remove('overflow-hidden');
}

function populateBidStatusModal(project, bid) {
	// Status badge
	const statusBadge = document.getElementById('bidStatusBadge');
	statusBadge.textContent = bid.status;
	statusBadge.className = `px-3 py-1 rounded-md text-xs font-semibold ${bidStatusStyles(bid.status)}`;

	// Bidder Information
	document.getElementById('bidCompanyName').textContent = bid.companyName || 'N/A';
	document.getElementById('bidEmail').textContent = bid.email || 'N/A';
	document.getElementById('bidPcabNo').textContent = bid.pcabNo || 'N/A';
	document.getElementById('bidPcabCategory').textContent = bid.pcabCategory || 'N/A';
	document.getElementById('bidPcabExpiry').textContent = bid.pcabExpiry ? formatDisplayDate(bid.pcabExpiry) : 'N/A';
	document.getElementById('bidBusinessPermit').textContent = bid.businessPermitNo || 'N/A';
	document.getElementById('bidPermitCity').textContent = bid.permitCity || 'N/A';
	document.getElementById('bidBusinessPermitExpiry').textContent = bid.businessPermitExpiry ? formatDisplayDate(bid.businessPermitExpiry) : 'N/A';
	document.getElementById('bidTin').textContent = bid.tin || 'N/A';

	// Project Information
	if (project.projectDetails) {
		document.getElementById('bidProjectTitle').textContent = 'Project ' + project.id;
		document.getElementById('bidProjectAddress').textContent = project.projectDetails.address || 'N/A';
		document.getElementById('bidProjectType').textContent = project.projectDetails.propertyType || 'N/A';
		document.getElementById('bidLotSize').textContent = project.projectDetails.lotSize || 'N/A';
		document.getElementById('bidProjectTimeline').textContent = project.projectDetails.timeline || 'N/A';
		document.getElementById('bidProjectBudget').textContent = formatCurrency(project.projectDetails.budget);
		document.getElementById('bidDeadline').textContent = project.projectDetails.deadline ? formatDisplayDate(project.projectDetails.deadline) : 'N/A';
		
		// Uploaded photos
		const photosWrap = document.getElementById('bidUploadedPhotos');
		const files = project.bidding?.files || [];
		if (files.length > 0) {
			photosWrap.innerHTML = files.map(f => {
				const icon = f.type === 'image' ? 'fi fi-rr-picture' : (f.type === 'pdf' ? 'fi fi-rr-file-pdf' : 'fi fi-rr-file');
				return `<span class="inline-flex items-center gap-1 px-2 py-1 rounded bg-blue-50 text-blue-700 text-xs"><i class="${icon} text-xs"></i>${f.name}</span>`;
			}).join('');
		} else {
			photosWrap.innerHTML = '<span class="text-gray-400 text-xs">No files uploaded</span>';
		}
	}

	// Bid Details
	document.getElementById('bidProposedCost').value = bid.proposedCost ? formatCurrency(bid.proposedCost).replace('₱', '').trim() : '';
	document.getElementById('bidDurationStart').value = bid.durationStart ? formatDisplayDate(bid.durationStart) : '';
	document.getElementById('bidDurationEnd').value = bid.durationEnd ? formatDisplayDate(bid.durationEnd) : '';
	document.getElementById('bidDescription').value = bid.description || '';

	// Supporting Files Table
	const filesTable = document.getElementById('bidSupportingFilesTable');
	const supportingFiles = bid.supportingFiles || [];
	if (supportingFiles.length > 0) {
		filesTable.innerHTML = supportingFiles.map((file, idx) => `
			<tr class="hover:bg-gray-50 transition">
				<td class="px-4 py-3">
					<div class="flex items-center gap-2.5">
						<input type="checkbox" class="w-4 h-4 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500 cursor-pointer">
						<svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
							<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
						</svg>
						<span class="text-sm text-gray-900">${file.name}</span>
					</div>
				</td>
				<td class="px-4 py-3 text-sm text-gray-600">${file.dateSubmitted ? formatDisplayDate(file.dateSubmitted) : 'N/A'}</td>
				<td class="px-4 py-3 text-sm text-gray-600">${file.userName || 'N/A'}</td>
				<td class="px-4 py-3 text-sm text-gray-600">${file.position || 'N/A'}</td>
				<td class="px-4 py-3 text-center">
					<button class="inline-flex items-center justify-center w-8 h-8 rounded-lg bg-indigo-50 hover:bg-indigo-100 text-indigo-600 transition" title="Download">
						<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
							<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
						</svg>
					</button>
				</td>
			</tr>
		`).join('');
	} else {
		filesTable.innerHTML = `
			<tr>
				<td colspan="5" class="px-4 py-8 text-center text-gray-400 text-sm">No supporting files available</td>
			</tr>
		`;
	}
}

// ----- Progress Report Detail Modal -----
let currentReportData = null;

function showProgressReportModal() {
	document.getElementById('progressReportModal').classList.remove('hidden');
	document.body.classList.add('overflow-hidden');
}

function hideProgressReportModal() {
	document.getElementById('progressReportModal').classList.add('hidden');
	document.body.classList.remove('overflow-hidden');
	currentReportData = null;
}

function populateProgressReportModal(report) {
	currentReportData = report;
	
	// Populate report info
	document.getElementById('reportTitle').textContent = report.title || 'Progress Report';
	document.getElementById('reportDate').textContent = report.date || '';
	document.getElementById('reportDescription').textContent = report.description || '';
	
	// Sample file history data (you can pass this with the report object)
	const fileHistory = report.fileHistory || [
		{ name: 'Progress Report', dateSubmitted: 'Dec 23, 2022', uploadedBy: 'Carl Saludo', status: 'Approved', checked: true },
		{ name: 'Progress Report', dateSubmitted: 'Dec 23, 2022', uploadedBy: 'Carl Saludo', status: 'Reviewed', checked: true },
		{ name: 'Progress Report', dateSubmitted: 'Dec 23, 2022', uploadedBy: 'Carl Saludo', status: 'Rejected', checked: true }
	];
	
	// Populate file history table
	const fileHistoryTable = document.getElementById('fileHistoryTable');
	if (fileHistory && fileHistory.length > 0) {
		fileHistoryTable.innerHTML = fileHistory.map((file, index) => {
			let statusClass = '';
			if (file.status === 'Approved') {
				statusClass = 'text-green-700 bg-green-50 border-green-200';
			} else if (file.status === 'Reviewed') {
				statusClass = 'text-blue-700 bg-blue-50 border-blue-200';
			} else if (file.status === 'Rejected') {
				statusClass = 'text-rose-700 bg-rose-50 border-rose-200';
			}
			
			return `
				<tr class="hover:bg-gray-50 transition-colors">
					<td class="px-4 py-3">
						<input type="checkbox" class="file-checkbox w-4 h-4 rounded border-gray-300 text-orange-500 focus:ring-orange-500 cursor-pointer" ${file.checked ? 'checked' : ''}>
					</td>
					<td class="px-4 py-3">
						<div class="flex items-center gap-2">
							<div class="w-8 h-8 rounded bg-red-100 flex items-center justify-center flex-shrink-0">
								<svg class="w-4 h-4 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
									<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/>
								</svg>
							</div>
							<span class="text-sm font-medium text-gray-900">${file.name}</span>
						</div>
					</td>
					<td class="px-4 py-3 text-sm text-gray-600">${file.dateSubmitted}</td>
					<td class="px-4 py-3 text-sm text-gray-600">${file.uploadedBy}</td>
					<td class="px-4 py-3">
						<span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold border ${statusClass}">
							${file.status}
						</span>
					</td>
					<td class="px-4 py-3 text-center">
						<button onclick="downloadFile('${file.name}')" class="inline-flex items-center justify-center w-8 h-8 rounded-lg bg-gray-50 hover:bg-gray-100 text-gray-700 transition" title="Download">
							<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
								<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
							</svg>
						</button>
					</td>
				</tr>
			`;
		}).join('');
	} else {
		fileHistoryTable.innerHTML = `
			<tr>
				<td colspan="6" class="px-4 py-8 text-center text-gray-400 text-sm">No file history available</td>
			</tr>
		`;
	}
	
	// Setup event listeners
	setupProgressReportListeners();
}

function setupProgressReportListeners() {
	// Select all checkbox
	const selectAllCheckbox = document.getElementById('selectAllFiles');
	if (selectAllCheckbox) {
		selectAllCheckbox.addEventListener('change', function() {
			const fileCheckboxes = document.querySelectorAll('.file-checkbox');
			fileCheckboxes.forEach(checkbox => {
				checkbox.checked = this.checked;
			});
		});
	}
	
	// Edit button
	const editBtn = document.getElementById('editReportBtn');
	if (editBtn) {
		editBtn.onclick = function() {
			// Implement edit functionality
			showNotification('Edit functionality coming soon', 'info');
		};
	}
	
	// Download all button
	const downloadAllBtn = document.getElementById('downloadAllBtn');
	if (downloadAllBtn) {
		downloadAllBtn.onclick = function() {
			const checkedFiles = document.querySelectorAll('.file-checkbox:checked');
			if (checkedFiles.length === 0) {
				showNotification('Please select files to download', 'info');
				return;
			}
			showNotification(`Downloading ${checkedFiles.length} file(s)...`, 'success');
			// Implement actual download logic here
		};
	}
}

function downloadFile(fileName) {
	showNotification(`Downloading ${fileName}...`, 'success');
	// Implement actual file download logic here
}

function openProgressReportModal(milestoneId, reportIndex) {
	const milestone = MILESTONE_DATA.find(m => m.id === milestoneId);
	if (!milestone || !milestone.reports || !milestone.reports[reportIndex]) {
		showNotification('Report not found', 'error');
		return;
	}
	
	const report = milestone.reports[reportIndex];
	populateProgressReportModal(report);
	showProgressReportModal();
}

// Edit Progress Report Modal functions

function openEditReportModal(milestoneId) {
	const milestone = MILESTONE_DATA.find(m => m.id === milestoneId);
	if (!milestone) {
		showNotification('Milestone not found', 'error');
		return;
	}
	
	currentEditMilestoneId = milestoneId;
	
	// Populate form with first report data if exists
	if (milestone.reports && milestone.reports.length > 0) {
		const report = milestone.reports[0];
		document.getElementById('editReportTitle').value = report.title || '';
		document.getElementById('editReportDescription').value = report.description || '';
		document.getElementById('editReportDate').value = report.date || '';
	} else {
		// Clear form for new report
		document.getElementById('editReportTitle').value = '';
		document.getElementById('editReportDescription').value = '';
		document.getElementById('editReportDate').value = '';
	}
	
	// Render uploaded photos (sample data)
	const photosContainer = document.getElementById('editUploadedPhotos');
	const samplePhotos = [
		{ name: 'supporting_files_sample.pdf', icon: 'link' }
	];
	
	photosContainer.innerHTML = samplePhotos.map(photo => `
		<div class="flex items-center gap-2 p-2 border border-gray-200 rounded-lg bg-gray-50 group hover:bg-gray-100 transition">
			<svg class="w-5 h-5 text-orange-500 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
				<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1"/>
			</svg>
			<span class="text-sm text-blue-600 hover:underline cursor-pointer flex-1">${photo.name}</span>
			<button onclick="viewFile('${photo.name}')" class="p-1 hover:bg-white rounded transition" title="View">
				<svg class="w-4 h-4 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
					<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
					<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
				</svg>
			</button>
			<button onclick="downloadFileFromEdit('${photo.name}')" class="p-1 hover:bg-white rounded transition" title="Download">
				<svg class="w-4 h-4 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
					<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
				</svg>
			</button>
			<button onclick="removeFile(this)" class="p-1 hover:bg-white rounded transition" title="Remove">
				<svg class="w-4 h-4 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
					<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
				</svg>
			</button>
		</div>
	`).join('');
	
	// Render supporting files (sample data)
	const filesContainer = document.getElementById('editSupportingFiles');
	const sampleFiles = [
		{ name: 'supporting_files_sample.pdf', icon: 'link' },
		{ name: 'supporting_files_sample.pdf', icon: 'link' }
	];
	
	filesContainer.innerHTML = sampleFiles.map(file => `
		<div class="flex items-center gap-2 p-2 border border-gray-200 rounded-lg bg-gray-50 group hover:bg-gray-100 transition">
			<svg class="w-5 h-5 text-orange-500 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
				<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1"/>
			</svg>
			<span class="text-sm text-blue-600 hover:underline cursor-pointer flex-1">${file.name}</span>
			<button onclick="viewFile('${file.name}')" class="p-1 hover:bg-white rounded transition" title="View">
				<svg class="w-4 h-4 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
					<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
					<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
				</svg>
			</button>
			<button onclick="downloadFileFromEdit('${file.name}')" class="p-1 hover:bg-white rounded transition" title="Download">
				<svg class="w-4 h-4 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
					<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
				</svg>
			</button>
			<button onclick="removeFile(this)" class="p-1 hover:bg-white rounded transition" title="Remove">
				<svg class="w-4 h-4 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
					<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
				</svg>
			</button>
		</div>
	`).join('');
	
	showEditProgressReportModal();
}

function showEditProgressReportModal() {
	const modal = document.getElementById('editProgressReportModal');
	modal.classList.remove('hidden');
	document.body.classList.add('overflow-hidden');
}

function hideEditProgressReportModal() {
	const modal = document.getElementById('editProgressReportModal');
	modal.classList.add('hidden');
	document.body.classList.remove('overflow-hidden');
	currentEditMilestoneId = null;
}

function saveProgressReportEdit() {
	const title = document.getElementById('editReportTitle').value.trim();
	const description = document.getElementById('editReportDescription').value.trim();
	const date = document.getElementById('editReportDate').value.trim();
	
	if (!title || !description || !date) {
		showNotification('Please fill in all required fields', 'error');
		return;
	}
	
	// Here you would typically send the data to the backend
	showNotification('Progress report updated successfully!', 'success');
	hideEditProgressReportModal();
	
	// Optionally refresh the details view
	if (currentEditMilestoneId) {
		selectMilestone(currentEditMilestoneId);
	}
}

function addPhotoInput() {
	showNotification('Photo upload functionality coming soon', 'info');
	// Implementation for adding new photo input
}

function addFileInput() {
	showNotification('File upload functionality coming soon', 'info');
	// Implementation for adding new file input
}

function viewFile(fileName) {
	showNotification(`Viewing ${fileName}...`, 'info');
	// Implementation for viewing file
}

function downloadFileFromEdit(fileName) {
	showNotification(`Downloading ${fileName}...`, 'success');
	// Implementation for downloading file
}

function removeFile(button) {
	const fileItem = button.closest('div.flex');
	if (fileItem) {
		fileItem.remove();
		showNotification('File removed', 'success');
	}
}

// Edit Milestone Details Modal functions
let currentEditMilestoneData = null;
let currentEditMilestoneId = null;
let isEditingCompletedMilestone = false;

function openEditMilestoneModal() {
	// Get the currently selected milestone
	const activeItem = document.querySelector('.milestone-item.active');
	if (!activeItem) {
		showNotification('Please select a milestone first', 'error');
		return;
	}
	
	const milestoneId = parseInt(activeItem.dataset.milestoneId);
	const milestone = MILESTONE_DATA.find(m => m.id === milestoneId);
	
	if (!milestone) {
		showNotification('Milestone not found', 'error');
		return;
	}
	
	currentEditMilestoneData = milestone;
	
	// Populate form with milestone data
	document.getElementById('editMilestoneTitle').value = milestone.title || '';
	document.getElementById('editMilestoneDescription').value = milestone.description || '';
	document.getElementById('editMilestoneDate').value = milestone.date || '';
	
	// Render uploaded photos (sample data from milestone)
	const photosContainer = document.getElementById('editMilestonePhotos');
	const samplePhotos = [
		{ name: 'supporting_files_sample.pdf', icon: 'link' }
	];
	
	photosContainer.innerHTML = samplePhotos.map(photo => `
		<div class="flex items-center gap-2 p-2 border border-gray-200 rounded-lg bg-gray-50 group hover:bg-gray-100 transition">
			<svg class="w-5 h-5 text-orange-500 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
				<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1"/>
			</svg>
			<span class="text-sm text-blue-600 hover:underline cursor-pointer flex-1">${photo.name}</span>
			<button onclick="viewFile('${photo.name}')" class="p-1 hover:bg-white rounded transition" title="View">
				<svg class="w-4 h-4 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
					<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
					<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
				</svg>
			</button>
			<button onclick="downloadFileFromEdit('${photo.name}')" class="p-1 hover:bg-white rounded transition" title="Download">
				<svg class="w-4 h-4 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
					<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
				</svg>
			</button>
			<button onclick="removeMilestoneFile(this)" class="p-1 hover:bg-white rounded transition" title="Remove">
				<svg class="w-4 h-4 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
					<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
				</svg>
			</button>
		</div>
	`).join('');
	
	// Render supporting files (sample data from milestone)
	const filesContainer = document.getElementById('editMilestoneFiles');
	const sampleFiles = milestone.supportingFiles || [
		{ name: 'supporting_files_sample.pdf', url: '#' },
		{ name: 'supporting_files_sample.pdf', url: '#' }
	];
	
	filesContainer.innerHTML = sampleFiles.map(file => `
		<div class="flex items-center gap-2 p-2 border border-gray-200 rounded-lg bg-gray-50 group hover:bg-gray-100 transition">
			<svg class="w-5 h-5 text-orange-500 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
				<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1"/>
			</svg>
			<span class="text-sm text-blue-600 hover:underline cursor-pointer flex-1">${file.name}</span>
			<button onclick="viewFile('${file.name}')" class="p-1 hover:bg-white rounded transition" title="View">
				<svg class="w-4 h-4 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
					<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
					<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
				</svg>
			</button>
			<button onclick="downloadFileFromEdit('${file.name}')" class="p-1 hover:bg-white rounded transition" title="Download">
				<svg class="w-4 h-4 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
					<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
				</svg>
			</button>
			<button onclick="removeMilestoneFile(this)" class="p-1 hover:bg-white rounded transition" title="Remove">
				<svg class="w-4 h-4 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
					<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
				</svg>
			</button>
		</div>
	`).join('');
	
	showEditMilestoneModal();
}

function showEditMilestoneModal() {
	const modal = document.getElementById('editMilestoneModal');
	modal.classList.remove('hidden');
	document.body.classList.add('overflow-hidden');
}

function hideEditMilestoneModal() {
	const modal = document.getElementById('editMilestoneModal');
	modal.classList.add('hidden');
	document.body.classList.remove('overflow-hidden');
	currentEditMilestoneData = null;
}

function saveMilestoneEdit() {
	const title = document.getElementById('editMilestoneTitle').value.trim();
	const description = document.getElementById('editMilestoneDescription').value.trim();
	const date = document.getElementById('editMilestoneDate').value.trim();
	
	if (!title || !description || !date) {
		showNotification('Please fill in all required fields', 'error');
		return;
	}
	
	// Here you would typically send the data to the backend
	showNotification('Milestone details updated successfully!', 'success');
	hideEditMilestoneModal();
	
	// Optionally refresh the details view
	if (currentEditMilestoneData) {
		if (isEditingCompletedMilestone) {
			selectCompletedMilestone(currentEditMilestoneData.id);
		} else {
			selectMilestone(currentEditMilestoneData.id);
		}
	}
	
	// Reset flag
	isEditingCompletedMilestone = false;
}

function addMilestonePhotoInput() {
	showNotification('Photo upload functionality coming soon', 'info');
	// Implementation for adding new photo input
}

function addMilestoneFileInput() {
	showNotification('File upload functionality coming soon', 'info');
	// Implementation for adding new file input
}

// Completed Project Modal Functions
let currentCompletedProject = null;

function showCompletedProjectModal() {
	const modal = document.getElementById('completedProjectModal');
	if (!modal) return;
	modal.classList.remove('hidden');
	document.body.classList.add('overflow-hidden');
}

function hideCompletedProjectModal() {
	const modal = document.getElementById('completedProjectModal');
	if (!modal) return;
	modal.classList.add('hidden');
	document.body.classList.remove('overflow-hidden');
}

function toggleCompletedDetails() {
	// Open the Completion Details modal
	showCompletionDetailsModal();
}

function showCompletionDetailsModal() {
	const modal = document.getElementById('completionDetailsModal');
	if (!modal) return;
	
	// Populate with sample data (replace with actual data later)
	populateCompletionDetails();
	
	modal.classList.remove('hidden');
	document.body.classList.add('overflow-hidden');
}

function hideCompletionDetailsModal() {
	const modal = document.getElementById('completionDetailsModal');
	if (!modal) return;
	modal.classList.add('hidden');
	document.body.classList.remove('overflow-hidden');
}

function populateCompletionDetails() {
	// Sample data - replace with actual project data
	document.getElementById('completionStatus').textContent = 'Approved - Verified';
	document.getElementById('completionDateCompleted').textContent = 'October 8, 2025';
	document.getElementById('completionDuration').textContent = '85 days (Started: August 15, 2025)';
	document.getElementById('completionProgress').textContent = '100%';
	document.getElementById('completionVerifiedBy').textContent = 'Approved - Verified';
	document.getElementById('completionVerificationDate').textContent = 'Approved - Verified';
	
	// Feedback data
	document.getElementById('completionOwnerName').textContent = 'Carlos Saludo (Camelia Holmes)';
	document.getElementById('completionOwnerFeedback').value = 'Project cancelled due to unresolved financial verification and lack of response from the property owner. Contractor instructed to withdraw materials and manpower. Project archived for documentation purposes.';
	
	document.getElementById('completionContractorName').textContent = 'Carlos Saludo (WaoWao Builders)';
	document.getElementById('completionContractorFeedback').value = 'Project cancelled due to unresolved financial verification and lack of response from the property owner. Contractor instructed to withdraw materials and manpower. Project archived for documentation purposes.';
}

// Halted Project Modal Functions
let currentHaltedProject = null;

function showHaltedProjectModal() {
	const modal = document.getElementById('haltedProjectModal');
	if (!modal) return;
	modal.classList.remove('hidden');
	document.body.classList.add('overflow-hidden');
}

function hideHaltedProjectModal() {
	const modal = document.getElementById('haltedProjectModal');
	if (!modal) return;
	modal.classList.add('hidden');
	document.body.classList.remove('overflow-hidden');
}

function viewHaltDetails() {
	showHaltDetailsModal();
}

function showHaltDetailsModal() {
	const modal = document.getElementById('haltDetailsModal');
	if (!modal) return;
	populateHaltDetailsModal(currentHaltedProject || {});
	modal.classList.remove('hidden');
	document.body.classList.add('overflow-hidden');
	showNotification('Halt details loaded', 'success');
}

function hideHaltDetailsModal() {
	const modal = document.getElementById('haltDetailsModal');
	if (!modal) return;
	modal.classList.add('hidden');
	document.body.classList.remove('overflow-hidden');
}

function populateHaltDetailsModal(project) {
	// Sample data based on screenshot
	const data = {
		initiatedBy: 'Property Owner',
		cause: 'Financial Verification Hold',
		reason: 'Delay in milestone payment ver',
		remarks: 'Payment for Milestone 3 pending admin verification before proceeding with next phase. Payment for Milestone 3 pending admin verification before proceeding with next phasePayment for Milestone 3 pending admin verification before proceeding with next phasePayment for Milestone 3 pending admin verification before proceeding with next phase',
		noticeDate: 'October 10, 2025',
		affectedMilestone: 'Milestone 3: Structural Framing & Roofing',
		issueStatus: 'Awaiting Resolution',
		expectedResolution: 'October 17, 2025 (est.)',
		files: ['supporting_files_sample.pdf', 'supporting_files_sample.pdf']
	};

	setText('haltInitiatedBy', data.initiatedBy);
	setText('haltCauseOfHalt', data.cause);
	setText('haltReasonOfHalt', data.reason);
	const remarksEl = document.getElementById('haltRemarks');
	if (remarksEl) remarksEl.value = data.remarks;
	setText('haltNoticeDate', data.noticeDate);
	setText('haltAffectedMilestone', data.affectedMilestone);
	setText('haltIssueStatus', data.issueStatus);
	setText('haltExpectedResolutionDate', data.expectedResolution);
	const filesContainer = document.getElementById('haltSupportingFiles');
	if (filesContainer) {
		filesContainer.innerHTML = data.files.map(f => `
			<a href="#" class="inline-flex items-center gap-2 px-3 py-1.5 bg-rose-50 text-rose-600 rounded-lg text-xs font-medium hover:bg-rose-100 transition border border-rose-200">
				<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
					<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"/>
				</svg>
				${f}
			</a>
		`).join('');
	}
}

function showCancelHaltConfirm() {
	const modal = document.getElementById('cancelHaltConfirmModal');
	if (!modal) return;
	modal.classList.remove('hidden');
}
function hideCancelHaltConfirm() {
	const modal = document.getElementById('cancelHaltConfirmModal');
	if (!modal) return;
	modal.classList.add('hidden');
}
function confirmCancelHalt() {
	hideCancelHaltConfirm();
	hideHaltDetailsModal();
	showNotification('Project marked as cancelled', 'success');
	// TODO: backend call
}

function showResumeHaltConfirm() {
	const modal = document.getElementById('resumeHaltConfirmModal');
	if (!modal) return;
	modal.classList.remove('hidden');
}
function hideResumeHaltConfirm() {
	const modal = document.getElementById('resumeHaltConfirmModal');
	if (!modal) return;
	modal.classList.add('hidden');
}
function confirmResumeHalt() {
	hideResumeHaltConfirm();
	hideHaltDetailsModal();
	showNotification('Project resumed successfully', 'success');
	// TODO: backend call
}

function downloadHaltSupportingFiles() {
	showNotification('Downloading all supporting files (sample)', 'info');
	// Placeholder for zip download
}

// ========== Cancelled Project Modal Functions ==========

let currentCancelledProject = null;

const CANCELLED_MILESTONE_DATA = [
	{
		id: 1,
		progress: 100,
		title: 'Milestone 5',
		date: '12 Dec 9:00 PM',
		description: 'Project terminated before completion. Final milestone was not reached due to cancellation.',
		status: 'cancelled',
		supportingFiles: ['progress_photos.jpeg']
	},
	{
		id: 2,
		progress: 80,
		title: 'Milestone 4',
		date: '21 DEC 11 PM',
		description: 'Milestone work stopped due to project termination.',
		status: 'cancelled',
		supportingFiles: ['progress_photos.jpeg']
	},
	{
		id: 3,
		progress: 60,
		title: 'Milestone 3',
		date: '21 DEC 9:34 PM',
		description: 'Mid-project review completed before termination.',
		status: 'completed',
		supportingFiles: ['progress_photos.jpeg', 'material_list.pdf']
	},
	{
		id: 4,
		progress: 40,
		title: 'Milestone 2',
		date: '20 DEC 2:30 AM',
		description: 'Site preparation and foundation work completed.',
		status: 'completed',
		supportingFiles: ['foundation_report.pdf', 'permit_docs.jpeg']
	},
	{
		id: 5,
		progress: 20,
		title: 'Milestone 1',
		date: '18 DEC 4:54 AM',
		description: 'Initial project kickoff and planning phase completed.',
		status: 'completed',
		supportingFiles: ['site_survey.pdf', 'design_plans.jpeg']
	}
];

const CANCELLED_PAYMENT_DATA = [
	{ milestone: 1, period: 'Sept 15 - Oct 5, 2025', amount: 150000, dateOfPayment: 'Oct 5, 2025', uploadedBy: 'Property Owner Name', proofOfPayment: 'sample_photo.jpeg', verificationStatus: 'Verified' },
	{ milestone: 2, period: 'Sept 15 - Oct 5, 2025', amount: 150000, dateOfPayment: 'Oct 5, 2025', uploadedBy: 'Property Owner Name', proofOfPayment: 'sample_photo.jpeg', verificationStatus: 'Verified' }
];

function showCancelledProjectModal() {
	const modal = document.getElementById('cancelledProjectModal');
	console.log('showCancelledProjectModal - modal element:', modal);
	console.log('Modal classes before:', modal ? modal.className : 'MODAL NOT FOUND');
	if (!modal) {
		console.error('cancelledProjectModal element not found in DOM!');
		return;
	}
	modal.classList.remove('hidden');
	modal.style.cssText = 'display: flex !important; z-index: 9999 !important;';
	console.log('Modal classes after:', modal.className);
	console.log('Modal display style:', modal.style.display);
	document.body.classList.add('overflow-hidden');
	console.log('Modal should be VISIBLE now!');
}

function hideCancelledProjectModal() {
	const modal = document.getElementById('cancelledProjectModal');
	if (!modal) return;
	modal.classList.add('hidden');
	modal.style.display = 'none';
	document.body.classList.remove('overflow-hidden');
}

function showTerminationDetailsModal() {
	const modal = document.getElementById('terminationDetailsModal');
	if (!modal) return;
	populateTerminationDetails(currentCancelledProject || {});
	modal.classList.remove('hidden');
	modal.style.display = '';
	// Keep body overflow hidden since parent modal is still open
}

function hideTerminationDetailsModal() {
	const modal = document.getElementById('terminationDetailsModal');
	if (!modal) return;
	modal.classList.add('hidden');
	modal.style.display = 'none';
	// Don't remove overflow-hidden since cancelled modal is still open
}

function populateTerminationDetails(project) {
	// Sample data based on screenshot
	const data = {
		initiatedBy: 'Admin',
		cause: 'Financial Verification Hold',
		reason: 'Pending verification of payment receipts',
		remarks: 'Project cancelled due to unresolved financial verification and lack of response from the property owner. Contractor instructed to withdraw materials and manpower. Project archived for documentation purposes.',
		noticeDate: 'October 13, 2025',
		affectedMilestone: 'Milestone 3: Structural Framing & Roofing',
		finalStatus: 'Officially Terminated',
		files: ['supporting_files_sample.pdf', 'supporting_files_sample.pdf']
	};

	setText('terminationInitiatedBy', data.initiatedBy);
	setText('terminationCause', data.cause);
	setText('terminationReason', data.reason);
	const remarksEl = document.getElementById('terminationRemarks');
	if (remarksEl) remarksEl.value = data.remarks;
	setText('terminationNoticeDate', data.noticeDate);
	setText('terminationAffectedMilestone', data.affectedMilestone);
	setText('terminationFinalStatus', data.finalStatus);

	const filesContainer = document.getElementById('terminationSupportingFiles');
	if (filesContainer) {
		filesContainer.innerHTML = data.files.map(f => `
			<a href="#" class="inline-flex items-center gap-2 px-3 py-1.5 bg-gray-50 text-gray-600 rounded-lg text-xs font-medium hover:bg-gray-100 transition border border-gray-200">
				<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
					<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"/>
				</svg>
				${f}
			</a>
		`).join('');
	}
}

function downloadTerminationFiles() {
	showNotification('Downloading all termination files (sample)', 'info');
	// TODO: Implement actual file download
}

function populateCancelledProjectModal(project) {
	currentCancelledProject = project;
		
		// Populate owner info
		const avatarEl = document.getElementById('cancelledOwnerAvatar');
		const ownerNameEl = document.getElementById('cancelledOwnerName');
		const cancelledDateEl = document.getElementById('cancelledDate');
		
		if (avatarEl && project.owner) {
			if (project.owner.avatar) {
				avatarEl.innerHTML = `<img src="${project.owner.avatar}" alt="${project.owner.name}" class="w-full h-full object-cover" />`;
			} else {
				avatarEl.innerHTML = `<span class="text-sm font-semibold text-gray-100">${project.owner.initials || 'PO'}</span>`;
			}
		}
		
		if (ownerNameEl && project.owner) {
			ownerNameEl.textContent = project.owner.name || 'John Dela Cruz';
		}
		
		if (cancelledDateEl) {
			cancelledDateEl.textContent = project.updatedAt ? formatDisplayDate(project.updatedAt) : 'November 20, 2025';
		}
		
		// Populate termination details inline
		populateTerminationDetails(project);
		
		// Sample project details (based on screenshot)
		const projectDetails = {
			title: 'Greenfield Commercial Complex',
			address: 'Tetuan Zamboanga City 7000',
			propertyType: 'Residential',
			lotSize: '3000',
			timeline: '6 months',
			budget: 1000000,
			deadline: 'September 20, 2025',
			photos: [
				{ name: 'sample_photo.png', type: 'image' }
			],
			files: [
				{ name: 'sample_photo.png', type: 'image' },
				{ name: 'sample_photo.png', type: 'image' }
			]
		};
		
		// Sample contractor details
		const contractorDetails = {
			companyName: 'Panda Construction Company',
			email: 'pandaconstruction@gmail.com',
			pcabNo: '12345-AB-2025',
			category: 'Category B',
			pcabExpiry: 'August 15, 2026',
			permitNo: 'BP-2025-0987',
			city: 'Zamboanga City',
			permitExpiry: 'December 31, 2025',
			tin: '123-456-789-000'
		};
		
		// Populate project details
		setText('cancelledProjectTitle', projectDetails.title);
		setText('cancelledProjectAddress', projectDetails.address);
		setText('cancelledProjectType', projectDetails.propertyType);
		setText('cancelledLotSize', projectDetails.lotSize);
		setText('cancelledTimeline', projectDetails.timeline);
		setText('cancelledBudget', projectDetails.budget ? `PHP ${projectDetails.budget.toLocaleString('en-PH', { minimumFractionDigits: 3, maximumFractionDigits: 3 })}` : '—');
		setText('cancelledDeadline', projectDetails.deadline);
		
		// Populate photos
		const photosContainer = document.getElementById('cancelledPhotos');
		if (photosContainer && projectDetails.photos) {
			photosContainer.innerHTML = projectDetails.photos.map(photo => `
				<a href="#" class="inline-flex items-center gap-2 px-3 py-1.5 bg-orange-50 text-orange-600 rounded-lg text-xs font-medium hover:bg-orange-100 transition border border-orange-200">
					<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
						<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1"/>
					</svg>
					${photo.name}
				</a>
			`).join('');
		}
		
		// Populate files
		const filesContainer = document.getElementById('cancelledFiles');
		if (filesContainer && projectDetails.files) {
			filesContainer.innerHTML = projectDetails.files.map(file => `
				<a href="#" class="inline-flex items-center gap-2 px-3 py-1.5 bg-orange-50 text-orange-600 rounded-lg text-xs font-medium hover:bg-orange-100 transition border border-orange-200">
					<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
						<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"/>
					</svg>
					${file.name}
				</a>
			`).join('');
		}
		
		// Populate contractor details
		setText('cancelledContractorName', contractorDetails.companyName);
		setText('cancelledContractorEmail', contractorDetails.email);
		setText('cancelledContractorPcab', contractorDetails.pcabNo);
		setText('cancelledContractorCategory', contractorDetails.category);
		setText('cancelledContractorPcabExpiry', contractorDetails.pcabExpiry);
		setText('cancelledContractorPermit', contractorDetails.permitNo);
		setText('cancelledContractorCity', contractorDetails.city);
		setText('cancelledContractorPermitExpiry', contractorDetails.permitExpiry);
		setText('cancelledContractorTin', contractorDetails.tin);
		
	// Render milestone timeline
	renderCancelledMilestoneTimeline();

	// Render cancelled payment summary
	renderCancelledPaymentSummary();
}

function renderCancelledMilestoneTimeline() {
	const container = document.getElementById('cancelledMilestoneTimeline');
	if (!container) return;

	let html = '';
	CANCELLED_MILESTONE_DATA.forEach((milestone, index) => {
		const isLast = index === CANCELLED_MILESTONE_DATA.length - 1;
		const statusClass = milestone.status === 'completed' ? 'completed' : milestone.status === 'in-progress' ? 'in-progress' : milestone.status === 'cancelled' ? 'cancelled' : 'pending';
		const statusIcon = milestone.status === 'completed' ? '✓' : milestone.status === 'cancelled' ? '✕' : '○';
		const statusText = milestone.status === 'completed' ? 'Completed' : milestone.status === 'cancelled' ? 'Cancelled' : 'Pending';
		
		html += `
			<div class="milestone-item ${statusClass}" data-milestone-id="${milestone.id}" onclick="selectCancelledMilestone(${milestone.id})">
				<div class="milestone-left">
					<div class="milestone-progress-circle">${milestone.progress}%</div>
					${!isLast ? '<div class="milestone-connector"></div>' : ''}
				</div>
				<div class="milestone-middle">
					<div class="milestone-status-line ${statusClass}"></div>
					<div class="milestone-status-dot ${statusClass}">${statusIcon}</div>
				</div>
				<div class="milestone-right">
					<div class="milestone-content-card">
						<div class="milestone-header">
							<div>
								<h4 class="milestone-title">${milestone.title}</h4>
								<p class="milestone-date">${milestone.date}</p>
							</div>
							<span class="milestone-badge ${statusClass}">
								${statusText}
							</span>
						</div>
						<p class="milestone-description">${milestone.description}</p>
						<button class="milestone-view-link" onclick="event.stopPropagation(); selectCancelledMilestone(${milestone.id})">View Details</button>
					</div>
				</div>
			</div>
		`;
	});

	container.innerHTML = html;

	// Auto-select the first milestone
	selectCancelledMilestone(CANCELLED_MILESTONE_DATA[0].id);
}

function selectCancelledMilestone(milestoneId) {
	const detailsContainer = document.getElementById('cancelledDetails');
	if (!detailsContainer) return;

	const milestone = CANCELLED_MILESTONE_DATA.find(m => m.id === milestoneId);
	if (!milestone) return;

	// Update active state in timeline
	document.querySelectorAll('#cancelledMilestoneTimeline .milestone-item').forEach(item => {
		if (parseInt(item.dataset.milestoneId) === milestoneId) {
			item.classList.add('active');
		} else {
			item.classList.remove('active');
		}
	});

	// Render details panel with the specific milestone's data
	const statusClass = milestone.status === 'completed' ? 'completed' : milestone.status === 'in-progress' ? 'in-progress' : milestone.status === 'cancelled' ? 'cancelled' : 'pending';
	const statusText = milestone.status === 'completed' ? 'Completed' : milestone.status === 'in-progress' ? 'In Progress' : milestone.status === 'cancelled' ? 'Cancelled' : 'Pending';

	let html = `
		<div class="detail-card">
			<div class="detail-header">
				<h4 class="detail-title">${milestone.title}</h4>
				<span class="detail-badge ${statusClass}">${statusText}</span>
			</div>
			<p class="detail-date">${milestone.date}</p>
			<p class="detail-description">${milestone.description}</p>
		</div>

		<div class="detail-card">
			<h5 class="detail-section-title">Supporting Files</h5>
			<div class="detail-files">
				${milestone.supportingFiles && milestone.supportingFiles.length > 0 
					? milestone.supportingFiles.map(file => `
						<a href="#" class="detail-file-link">
							<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
								<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"/>
							</svg>
							${file}
						</a>
					`).join('')
					: '<p class="text-sm text-gray-500">No supporting files available</p>'
				}
			</div>
		</div>
	`;

	detailsContainer.innerHTML = html;
}

function renderCancelledPaymentSummary() {
	const totalPaid = CANCELLED_PAYMENT_DATA.length;
	const totalAmount = CANCELLED_PAYMENT_DATA.reduce((sum, p) => sum + p.amount, 0);
	const lastPayment = CANCELLED_PAYMENT_DATA.length > 0 ? CANCELLED_PAYMENT_DATA[CANCELLED_PAYMENT_DATA.length - 1] : null;
	const overallStatus = 'Partially Paid';

	setText('cancelledPaidCount', `${totalPaid} / 5`);
	setText('cancelledTotalAmount', `Php. ${totalAmount.toLocaleString('en-PH', { minimumFractionDigits: 3, maximumFractionDigits: 3 })}`);
	setText('cancelledLastPaymentDate', lastPayment ? lastPayment.dateOfPayment : '—');
	setText('cancelledOverallStatus', overallStatus);

	const tableBody = document.getElementById('cancelledPaymentTable');
	if (!tableBody) return;

	tableBody.innerHTML = CANCELLED_PAYMENT_DATA.map(p => {
		let badgeClass = 'bg-gray-500 text-white';
		if (p.verificationStatus === 'Verified') badgeClass = 'bg-green-500 text-white';
		else if (p.verificationStatus === 'Pending') badgeClass = 'bg-yellow-500 text-white';
		else if (p.verificationStatus === 'Invalid Receipt') badgeClass = 'bg-red-500 text-white';

		return `
			<tr class="hover:bg-gray-50 transition-colors">
				<td class="px-4 py-3 text-gray-900 font-medium">${p.milestone}</td>
				<td class="px-4 py-3 text-gray-600">${p.period}</td>
				<td class="px-4 py-3 text-gray-900 font-semibold">₱${p.amount.toLocaleString('en-PH', { minimumFractionDigits: 2 })}</td>
				<td class="px-4 py-3 text-gray-600">${p.dateOfPayment}</td>
				<td class="px-4 py-3 text-gray-600">${p.uploadedBy}</td>
				<td class="px-4 py-3">
					<a href="#" class="inline-flex items-center gap-1 text-gray-600 hover:text-gray-800 text-xs font-medium">
						<svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
							<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"/>
						</svg>
						${p.proofOfPayment}
					</a>
				</td>
				<td class="px-4 py-3">
					<span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold ${badgeClass}">${p.verificationStatus}</span>
				</td>
			</tr>
		`;
	}).join('');
}

function populateHaltedProjectModal(project) {
	currentHaltedProject = project;
	
	// Populate owner info
	const avatarEl = document.getElementById('haltedOwnerAvatar');
	const ownerNameEl = document.getElementById('haltedOwnerName');
	const haltedDateEl = document.getElementById('haltedDate');
	
	if (avatarEl && project.owner) {
		if (project.owner.avatar) {
			avatarEl.innerHTML = `<img src="${project.owner.avatar}" alt="${project.owner.name}" class="w-full h-full object-cover" />`;
		} else {
			avatarEl.innerHTML = `<span class="text-sm font-semibold text-rose-600">${project.owner.initials || 'PO'}</span>`;
		}
	}
	
	if (ownerNameEl && project.owner) {
		ownerNameEl.textContent = project.owner.name || 'John Dela Cruz';
	}
	
	if (haltedDateEl) {
		haltedDateEl.textContent = project.updatedAt ? formatDisplayDate(project.updatedAt) : 'November 20, 2025';
	}
	
	// Sample project details (based on screenshot)
	const projectDetails = {
		title: 'Greenfield Commercial Complex',
		address: 'Tetuan Zamboanga City 7000',
		propertyType: 'Residential',
		lotSize: '3000',
		timeline: '3000',
		budget: 1000000,
		deadline: 'September 20, 2025',
		photos: [
			{ name: 'sample_photo.png', type: 'image' }
		],
		files: [
			{ name: 'sample_photo.png', type: 'image' },
			{ name: 'sample_photo.png', type: 'image' }
		]
	};
	
	// Sample contractor details
	const contractorDetails = {
		companyName: 'Panda Construction Company',
		email: 'pandaconstruction@gmail.com',
		pcabNo: '12345-AB-2025',
		category: 'Category B',
		pcabExpiry: 'August 15, 2026',
		permitNo: 'BP-2025-0987',
		city: 'Zambo-City-78999',
		permitExpiry: 'December 31, 2025',
		tin: '123-456-789-000'
	};
	
	// Populate project details
	setText('haltedProjectTitle', projectDetails.title);
	setText('haltedProjectAddress', projectDetails.address);
	setText('haltedProjectType', projectDetails.propertyType);
	setText('haltedLotSize', projectDetails.lotSize);
	setText('haltedTimeline', projectDetails.timeline);
	setText('haltedBudget', projectDetails.budget ? `PHP ${projectDetails.budget.toLocaleString('en-PH', { minimumFractionDigits: 3, maximumFractionDigits: 3 })}` : '—');
	setText('haltedDeadline', projectDetails.deadline);
	
	// Populate photos
	const photosContainer = document.getElementById('haltedPhotos');
	if (photosContainer && projectDetails.photos) {
		photosContainer.innerHTML = projectDetails.photos.map(photo => `
			<a href="#" class="inline-flex items-center gap-2 px-3 py-1.5 bg-orange-50 text-orange-600 rounded-lg text-xs font-medium hover:bg-orange-100 transition border border-orange-200">
				<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
					<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1"/>
				</svg>
				${photo.name}
			</a>
		`).join('');
	}
	
	// Populate files
	const filesContainer = document.getElementById('haltedFiles');
	if (filesContainer && projectDetails.files) {
		filesContainer.innerHTML = projectDetails.files.map(file => `
			<a href="#" class="inline-flex items-center gap-2 px-3 py-1.5 bg-orange-50 text-orange-600 rounded-lg text-xs font-medium hover:bg-orange-100 transition border border-orange-200">
				<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
					<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"/>
				</svg>
				${file.name}
			</a>
		`).join('');
	}
	
	// Populate contractor details
	setText('haltedContractorName', contractorDetails.companyName);
	setText('haltedContractorEmail', contractorDetails.email);
	setText('haltedContractorPcab', contractorDetails.pcabNo);
	setText('haltedContractorCategory', contractorDetails.category);
	setText('haltedContractorPcabExpiry', contractorDetails.pcabExpiry);
	setText('haltedContractorPermit', contractorDetails.permitNo);
	setText('haltedContractorCity', contractorDetails.city);
	setText('haltedContractorPermitExpiry', contractorDetails.permitExpiry);
	setText('haltedContractorTin', contractorDetails.tin);
	
	// Render milestone timeline
	renderHaltedMilestoneTimeline(project);

	// Render halted payment summary (static sample based on screenshot)
	if (typeof renderHaltedPaymentSummary === 'function') {
		renderHaltedPaymentSummary(project);
	}
}

function populateCompletedProjectModal(project) {
	currentCompletedProject = project;
	
	// Populate owner info
	const avatarEl = document.getElementById('completedOwnerAvatar');
	const ownerNameEl = document.getElementById('completedOwnerName');
	const completedDateEl = document.getElementById('completedDate');
	
	if (avatarEl && project.owner) {
		if (project.owner.avatar) {
			avatarEl.innerHTML = `<img src="${project.owner.avatar}" alt="${project.owner.name}" class="w-full h-full object-cover" />`;
		} else {
			avatarEl.innerHTML = `<span class="text-sm font-semibold text-green-600">${project.owner.initials || 'PO'}</span>`;
		}
	}
	
	if (ownerNameEl && project.owner) {
		ownerNameEl.textContent = project.owner.name || 'Property Owner';
	}
	
	if (completedDateEl) {
		completedDateEl.textContent = project.updatedAt ? formatDisplayDate(project.updatedAt) : 'November 20, 2025';
	}
	
	// Sample project details (you can replace with actual data)
	const projectDetails = {
		title: 'Greenfield Commercial Complex',
		address: 'Tetuan Zamboanga City 7000',
		propertyType: 'Residential',
		lotSize: '3000',
		timeline: '3000',
		budget: 1000000,
		deadline: 'November 20, 2025',
		photos: [
			{ name: 'sample_photo.jpeg', type: 'image' }
		],
		files: [
			{ name: 'sample_photo.jpeg', type: 'image' },
			{ name: 'sample_photo.jpeg', type: 'image' }
		]
	};
	
	// Sample contractor details (you can replace with actual data)
	const contractorDetails = {
		companyName: 'Panda Construction Company',
		email: 'pandaconstruction@gmail.com',
		pcabNo: '12345-AB-2025',
		category: 'Category B',
		pcabExpiry: 'August 15, 2026',
		permitNo: 'BP-2025-0987',
		city: 'Zamboanga City',
		permitExpiry: 'December 31, 2025',
		tin: '123-456-789-000'
	};
	
	// Populate project details
	setText('completedProjectTitle', projectDetails.title);
	setText('completedProjectAddress', projectDetails.address);
	setText('completedProjectType', projectDetails.propertyType);
	setText('completedLotSize', projectDetails.lotSize);
	setText('completedTimeline', projectDetails.timeline);
	setText('completedBudget', projectDetails.budget ? `₱${projectDetails.budget.toLocaleString('en-PH')}` : '—');
	setText('completedDeadline', projectDetails.deadline);
	
	// Populate photos
	const photosContainer = document.getElementById('completedPhotos');
	if (photosContainer && projectDetails.photos) {
		photosContainer.innerHTML = projectDetails.photos.map(photo => `
			<a href="#" class="inline-flex items-center gap-2 px-3 py-1.5 bg-orange-50 text-orange-600 rounded-lg text-xs font-medium hover:bg-orange-100 transition border border-orange-200">
				<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
					<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1"/>
				</svg>
				${photo.name}
			</a>
		`).join('');
	}
	
	// Populate files
	const filesContainer = document.getElementById('completedFiles');
	if (filesContainer && projectDetails.files) {
		filesContainer.innerHTML = projectDetails.files.map(file => `
			<a href="#" class="inline-flex items-center gap-2 px-3 py-1.5 bg-orange-50 text-orange-600 rounded-lg text-xs font-medium hover:bg-orange-100 transition border border-orange-200">
				<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
					<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1"/>
				</svg>
				${file.name}
			</a>
		`).join('');
	}
	
	// Populate contractor details
	setText('completedContractorName', contractorDetails.companyName);
	setText('completedContractorEmail', contractorDetails.email);
	setText('completedContractorPcab', contractorDetails.pcabNo);
	setText('completedContractorCategory', contractorDetails.category);
	setText('completedContractorPcabExpiry', contractorDetails.pcabExpiry);
	setText('completedContractorPermit', contractorDetails.permitNo);
	setText('completedContractorCity', contractorDetails.city);
	setText('completedContractorPermitExpiry', contractorDetails.permitExpiry);
	setText('completedContractorTin', contractorDetails.tin);
	
	// Payment Summary - Sample completed payment data
	const completedPayments = [
		{
			milestone: 1,
			period: 'Sept 15 - Oct 5, 2025',
			amount: 150000.00,
			dateOfPayment: 'Oct 5, 2025',
			uploadedBy: 'Property Owner Name',
			proofOfPayment: 'sample_photo.jpeg',
			verificationStatus: 'Verified'
		},
		{
			milestone: 2,
			period: 'Sept 15 - Oct 5, 2025',
			amount: 150000.00,
			dateOfPayment: 'Oct 5, 2025',
			uploadedBy: 'Property Owner Name',
			proofOfPayment: 'sample_photo.jpeg',
			verificationStatus: 'Verified'
		},
		{
			milestone: 2,
			period: 'Sept 15 - Oct 5, 2025',
			amount: 150000.00,
			dateOfPayment: 'Oct 5, 2025',
			uploadedBy: 'Property Owner Name',
			proofOfPayment: 'sample_photo.jpeg',
			verificationStatus: 'Verified'
		},
		{
			milestone: 3,
			period: 'Sept 15 - Oct 5, 2025',
			amount: 150000.00,
			dateOfPayment: 'Oct 5, 2025',
			uploadedBy: 'Property Owner Name',
			proofOfPayment: 'sample_photo.jpeg',
			verificationStatus: 'Verified'
		},
		{
			milestone: 5,
			period: 'Sept 15 - Oct 5, 2025',
			amount: 10000000.00,
			dateOfPayment: 'Oct 13, 2025',
			uploadedBy: 'Property Owner Name',
			proofOfPayment: 'sample_photo.jpeg',
			verificationStatus: 'Verified'
		}
	];

	const totalPaid = completedPayments.length;
	const totalAmount = completedPayments.reduce((sum, p) => sum + p.amount, 0);
	const lastPayment = completedPayments.length > 0 ? completedPayments[completedPayments.length - 1] : null;
	const verifiedCount = completedPayments.filter(p => p.verificationStatus === 'Verified').length;
	const overallStatus = verifiedCount === totalPaid ? 'Fully Paid' : verifiedCount > 0 ? 'Partially Paid' : 'Pending';

	setText('completedPaidCount', `${totalPaid} / 5`);
	setText('completedTotalAmount', totalAmount > 0 ? `Php. ${totalAmount.toLocaleString('en-PH', { minimumFractionDigits: 3, maximumFractionDigits: 3 })}` : '—');
	setText('completedLastPaymentDate', lastPayment ? lastPayment.dateOfPayment : '—');
	setText('completedOverallStatus', overallStatus);

	// Payment Table
	const completedPaymentTable = document.getElementById('completedPaymentTable');
	if (completedPaymentTable && completedPayments.length > 0) {
		completedPaymentTable.innerHTML = completedPayments.map(payment => {
			let statusBadgeClass = '';
			if (payment.verificationStatus === 'Verified') {
				statusBadgeClass = 'bg-green-500 text-white';
			} else if (payment.verificationStatus === 'Pending') {
				statusBadgeClass = 'bg-yellow-500 text-white';
			} else if (payment.verificationStatus === 'Invalid Receipt') {
				statusBadgeClass = 'bg-red-500 text-white';
			}
			return `
				<tr class="hover:bg-green-50 transition-colors">
					<td class="px-4 py-3 text-gray-900 font-medium">${payment.milestone}</td>
					<td class="px-4 py-3 text-gray-600">${payment.period}</td>
					<td class="px-4 py-3 text-gray-900 font-semibold">₱${payment.amount.toLocaleString('en-PH', { minimumFractionDigits: 2 })}</td>
					<td class="px-4 py-3 text-gray-600">${payment.dateOfPayment}</td>
					<td class="px-4 py-3 text-gray-600">${payment.uploadedBy}</td>
					<td class="px-4 py-3">
						<a href="#" class="inline-flex items-center gap-1 text-green-600 hover:text-green-800 text-xs font-medium">
							<svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
								<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"/>
							</svg>
							${payment.proofOfPayment}
						</a>
					</td>
					<td class="px-4 py-3">
						<span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold ${statusBadgeClass}">
							${payment.verificationStatus}
						</span>
					</td>
				</tr>
			`;
		}).join('');
	} else if (completedPaymentTable) {
		completedPaymentTable.innerHTML = `
			<tr>
				<td colspan="7" class="px-4 py-8 text-center text-gray-400 text-sm">No payment records available</td>
			</tr>
		`;
	}
	
	// Render milestone timeline
	renderCompletedMilestoneTimeline(project);
}

function showCompletedBidDetails() {
	showNotification('Bid details functionality coming soon', 'info');
	// This could open another modal or expand a section with bid information
}

function setText(id, value) {
	const el = document.getElementById(id);
	if (el) el.textContent = value || '—';
}

function removeMilestoneFile(button) {
	const fileItem = button.closest('div.flex');
	if (fileItem) {
		fileItem.remove();
		showNotification('File removed', 'success');
	}
}

// ========== Completed Project Milestone Functions ==========

const COMPLETED_MILESTONE_DATA = [
	{
		id: 1,
		progress: 100,
		title: 'Milestone 5',
		date: '12 Dec 9:00 PM',
		description: 'People care about how you see the world, how you think, what motivates you, what you\'re struggling with or afraid of People care about how you see the world, how you think, what motivates you, what you\'re struggling with or afraid of.',
		status: 'completed',
		reports: [
			{ title: 'Progress Report 5', date: '20 Dec 2:30 AM', description: 'Foundation work completed ahead of schedule. All quality checks passed.' },
			{ title: 'Progress Report 5.2', date: '20 Dec 2:30 AM', description: 'Permit documentation submitted and approved by local authorities.' },
			{ title: 'Progress Report 5.3', date: '20 Dec 2:30 AM', description: 'Permit documentation submitted and approved by local authorities.' },
			{ title: 'Progress Report 5.4', date: '20 Dec 2:30 AM', description: 'Permit documentation submitted and approved by local authorities.' }
		],
		supportingFiles: ['progress_photos.jpeg', 'material_list.pdf'],
		supportingFiles: ['progress_photos.jpeg', 'material_list.pdf'],
		supportingFiles: ['progress_photos.jpeg', 'material_list.pdf']

	},
	{
		id: 2,
		progress: 80,
		title: 'Milestone 4',
		date: '21 DEC 11 PM',
		description: 'Construction phase completion with all structural work finished. Quality assurance tests conducted and passed successfully.',
		status: 'completed',
		reports: [
			{ title: 'Progress Report 4', date: '20 Dec 2:30 AM', description: 'Foundation work completed ahead of schedule. All quality checks passed.' },
			{ title: 'Progress Report 4.2', date: '20 Dec 2:30 AM', description: 'Permit documentation submitted and approved by local authorities.' },
			{ title: 'Progress Report 4.3', date: '20 Dec 2:30 AM', description: 'Permit documentation submitted and approved by local authorities.' },
			{ title: 'Progress Report 4.4', date: '20 Dec 2:30 AM', description: 'Permit documentation submitted and approved by local authorities.' }
		],
		supportingFiles: ['progress_photos.jpeg', 'material_list.pdf'],
		supportingFiles: ['progress_photos.jpeg', 'material_list.pdf']
	},
	{
		id: 3,
		progress: 60,
		title: 'Milestone 3',
		date: '21 DEC 9:34 PM',
		description: 'Mid-project review completed. Foundation and framework construction in progress with scheduled timeline adjustments.',
		status: 'completed',
		reports: [
			{ title: 'Progress Report 3', date: '20 Dec 2:30 AM', description: 'Foundation work completed ahead of schedule. All quality checks passed.' },
			{ title: 'Progress Report 3.2', date: '20 Dec 2:30 AM', description: 'Permit documentation submitted and approved by local authorities.' },
			{ title: 'Progress Report 3.3', date: '20 Dec 2:30 AM', description: 'Permit documentation submitted and approved by local authorities.' },
			{ title: 'Progress Report 3.4', date: '20 Dec 2:30 AM', description: 'Permit documentation submitted and approved by local authorities.' }
		],
		supportingFiles: ['progress_photos.jpeg', 'material_list.pdf'],
		supportingFiles: ['progress_photos.jpeg', 'material_list.pdf'],
		supportingFiles: ['progress_photos.jpeg', 'material_list.pdf']
	},
	{
		id: 4,
		progress: 40,
		title: 'Milestone 2',
		date: '20 DEC 2:30 AM',
		description: 'Site preparation and foundation work completed. All permits approved and documentation submitted to regulatory authorities.',
		status: 'completed',
		reports: [
			{ title: 'Progress Report 2', date: '20 Dec 2:30 AM', description: 'Foundation work completed ahead of schedule. All quality checks passed.' },
			{ title: 'Progress Report 2.2', date: '20 Dec 2:30 AM', description: 'Permit documentation submitted and approved by local authorities.' },
			{ title: 'Progress Report 2.3', date: '20 Dec 2:30 AM', description: 'Permit documentation submitted and approved by local authorities.' },
			{ title: 'Progress Report 2.4', date: '20 Dec 2:30 AM', description: 'Permit documentation submitted and approved by local authorities.' }
		],
		supportingFiles: ['foundation_report.pdf', 'permit_docs.jpeg']
	},
	{
		id: 5,
		progress: 20,
		title: 'Milestone 1',
		date: '18 DEC 4:54 AM',
		description: 'Initial project kickoff and planning phase completed. Site survey conducted and design plans finalized.',
		status: 'completed',
		reports: [
			{ title: 'Progress Report 1', date: '20 Dec 2:30 AM', description: 'Foundation work completed ahead of schedule. All quality checks passed.' },
			{ title: 'Progress Report 1.2', date: '20 Dec 2:30 AM', description: 'Permit documentation submitted and approved by local authorities.' },
			{ title: 'Progress Report 1.3', date: '20 Dec 2:30 AM', description: 'Permit documentation submitted and approved by local authorities.' },
			{ title: 'Progress Report 1.4', date: '20 Dec 2:30 AM', description: 'Permit documentation submitted and approved by local authorities.' }
		],
		supportingFiles: ['site_survey.pdf', 'design_plans.jpeg', 'approval_doc.pdf']
	}
];

function renderCompletedMilestoneTimeline(project) {
	const container = document.getElementById('completedMilestoneTimeline');
	if (!container) return;

	let html = '';
	COMPLETED_MILESTONE_DATA.forEach((milestone, index) => {
		const isLast = index === COMPLETED_MILESTONE_DATA.length - 1;
		const statusClass = milestone.status === 'completed' ? 'completed' : milestone.status === 'in-progress' ? 'in-progress' : 'pending';
		const statusIcon = milestone.status === 'completed' ? '✓' : milestone.status === 'in-progress' ? '◉' : '○';
		
		html += `
			<div class="milestone-item ${statusClass}" data-milestone-id="${milestone.id}" onclick="selectCompletedMilestone(${milestone.id})">
				<div class="milestone-left">
					<div class="milestone-progress-circle">${milestone.progress}%</div>
					${!isLast ? '<div class="milestone-connector"></div>' : ''}
				</div>
				<div class="milestone-middle">
					<div class="milestone-status-line ${statusClass}"></div>
					<div class="milestone-status-dot ${statusClass}">${statusIcon}</div>
				</div>
				<div class="milestone-right">
					<div class="milestone-content-card">
						<div class="milestone-header">
							<div>
								<h4 class="milestone-title">${milestone.title}</h4>
								<p class="milestone-date">${milestone.date}</p>
							</div>
							<span class="milestone-badge ${statusClass}">
								${milestone.status === 'completed' ? 'Completed' : milestone.status === 'in-progress' ? 'In Progress' : 'Pending'}
							</span>
						</div>
						<p class="milestone-description">${milestone.description}</p>
						<button class="milestone-view-link" onclick="event.stopPropagation(); selectCompletedMilestone(${milestone.id})">View Details</button>
					</div>
				</div>
			</div>
		`;
	});

	container.innerHTML = html;

	// Auto-select the first milestone
	selectCompletedMilestone(COMPLETED_MILESTONE_DATA[0].id);
}

function selectCompletedMilestone(milestoneId) {
	const detailsContainer = document.getElementById('completedDetails');
	if (!detailsContainer) return;

	const milestone = COMPLETED_MILESTONE_DATA.find(m => m.id === milestoneId);
	if (!milestone) return;

	// Update active state in timeline
	document.querySelectorAll('#completedMilestoneTimeline .milestone-item').forEach(item => {
		if (parseInt(item.dataset.milestoneId) === milestoneId) {
			item.classList.add('active');
		} else {
			item.classList.remove('active');
		}
	});

	// Render details panel with the specific milestone's data
	const statusClass = milestone.status === 'completed' ? 'completed' : milestone.status === 'in-progress' ? 'in-progress' : 'pending';
	const statusText = milestone.status === 'completed' ? 'Completed' : milestone.status === 'in-progress' ? 'In Progress' : 'Pending';

	let html = `
		<div class="detail-card">
			<div class="detail-header">
				<h4 class="detail-title">${milestone.title}</h4>
				<span class="detail-badge ${statusClass}">${statusText}</span>
			</div>
			<p class="detail-date">${milestone.date}</p>
			<p class="detail-description">${milestone.description}</p>
		</div>

		<div class="detail-card">
			<h5 class="detail-section-title">Supporting Files</h5>
			<div class="detail-files">
				${milestone.supportingFiles && milestone.supportingFiles.length > 0 
					? milestone.supportingFiles.map(file => `
						<a href="#" class="detail-file-link">
							<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
								<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"/>
							</svg>
							${file}
						</a>
					`).join('')
					: '<p class="text-sm text-gray-500">No supporting files available</p>'
				}
			</div>
		</div>

		<div class="detail-card">
			<div class="flex items-center justify-between mb-4">
				<h5 class="detail-section-title mb-0">List of Reports</h5>
			</div>
			<div class="detail-reports">
				${milestone.reports && milestone.reports.length > 0
					? milestone.reports.map((report, index) => `
						<div class="report-item">
							<div class="report-header">
								<h6 class="report-title">${report.title}</h6>
								<span class="report-date">${report.date}</span>
							</div>
							<p class="report-description">${report.description}</p>
							<a href="#" onclick="event.preventDefault(); openProgressReportModal(${milestoneId}, ${index})" class="report-view-link">View Details</a>
						</div>
					`).join('')
					: '<p class="text-sm text-gray-500">No reports available</p>'
				}
			</div>
		</div>
	`;

	detailsContainer.innerHTML = html;
}

function openEditCompletedMilestoneModal() {
	// Get the currently selected milestone
	const activeMilestone = document.querySelector('#completedMilestoneTimeline .milestone-item.active');
	if (!activeMilestone) {
		showNotification('Please select a milestone first', 'warning');
		return;
	}

	const milestoneId = parseInt(activeMilestone.dataset.milestoneId);
	const milestone = COMPLETED_MILESTONE_DATA.find(m => m.id === milestoneId);
	if (!milestone) {
		showNotification('Milestone data not found', 'error');
		return;
	}

	// Use the existing edit milestone modal
	currentEditMilestoneData = milestone;
	currentEditMilestoneId = milestoneId;
	isEditingCompletedMilestone = true;

	// Populate form fields
	document.getElementById('editMilestoneTitle').value = milestone.title;
	document.getElementById('editMilestoneDescription').value = milestone.description;
	document.getElementById('editMilestoneDate').value = milestone.date;

	// Populate supporting files
	const filesContainer = document.getElementById('editMilestoneFiles');
	if (milestone.supportingFiles && milestone.supportingFiles.length > 0) {
		filesContainer.innerHTML = milestone.supportingFiles.map(file => `
			<div class="flex items-center justify-between p-2 rounded hover:bg-orange-50">
				<a href="#" class="text-orange-600 hover:text-orange-700 font-medium text-sm flex items-center gap-2">
					<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
						<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"/>
					</svg>
					${file}
				</a>
				<div class="flex gap-2">
					<button onclick="viewFile('${file}')" class="text-blue-600 hover:text-blue-700 text-xs">View</button>
					<button onclick="downloadFileFromEdit('${file}')" class="text-green-600 hover:text-green-700 text-xs">Download</button>
					<button onclick="removeMilestoneFile(this)" class="text-red-600 hover:text-red-700 text-xs">Remove</button>
				</div>
			</div>
		`).join('');
	} else {
		filesContainer.innerHTML = '<p class="text-sm text-gray-500">No supporting files</p>';
	}

	showEditMilestoneModal();
}

// ========== Halted Project Milestone Functions ==========

function renderHaltedMilestoneTimeline(project) {
	const container = document.getElementById('haltedMilestoneTimeline');
	if (!container) return;

	let html = '';
	HALTED_MILESTONE_DATA.forEach((milestone, index) => {
		const isLast = index === HALTED_MILESTONE_DATA.length - 1;
		const statusClass = milestone.status === 'completed' ? 'completed' : milestone.status === 'in-progress' ? 'in-progress' : 'pending';
		const statusIcon = milestone.status === 'completed' ? '✓' : milestone.status === 'in-progress' ? '◉' : '○';
		
		html += `
			<div class="milestone-item ${statusClass}" data-milestone-id="${milestone.id}" onclick="selectHaltedMilestone(${milestone.id})">
				<div class="milestone-left">
					<div class="milestone-progress-circle">${milestone.progress}%</div>
					${!isLast ? '<div class="milestone-connector"></div>' : ''}
				</div>
				<div class="milestone-middle">
					<div class="milestone-status-line ${statusClass}"></div>
					<div class="milestone-status-dot ${statusClass}">${statusIcon}</div>
				</div>
				<div class="milestone-right">
					<div class="milestone-content-card">
						<div class="milestone-header">
							<div>
								<h4 class="milestone-title">${milestone.title}</h4>
								<p class="milestone-date">${milestone.date}</p>
							</div>
							<span class="milestone-badge ${statusClass}">
								${milestone.status === 'completed' ? 'Completed' : milestone.status === 'in-progress' ? 'In Progress' : 'Pending'}
							</span>
						</div>
						<p class="milestone-description">${milestone.description}</p>
						<button class="milestone-view-link" onclick="event.stopPropagation(); selectHaltedMilestone(${milestone.id})">View Details</button>
					</div>
				</div>
			</div>
		`;
	});

	container.innerHTML = html;

	// Auto-select the first milestone
	selectHaltedMilestone(HALTED_MILESTONE_DATA[0].id);
}

function selectHaltedMilestone(milestoneId) {
	const detailsContainer = document.getElementById('haltedDetails');
	if (!detailsContainer) return;

	const milestone = HALTED_MILESTONE_DATA.find(m => m.id === milestoneId);
	if (!milestone) return;

	// Update active state in timeline
	document.querySelectorAll('#haltedMilestoneTimeline .milestone-item').forEach(item => {
		if (parseInt(item.dataset.milestoneId) === milestoneId) {
			item.classList.add('active');
		} else {
			item.classList.remove('active');
		}
	});

	// Render details panel with the specific milestone's data
	const statusClass = milestone.status === 'completed' ? 'completed' : milestone.status === 'in-progress' ? 'in-progress' : 'pending';
	const statusText = milestone.status === 'completed' ? 'Completed' : milestone.status === 'in-progress' ? 'In Progress' : 'Pending';

	// Placeholder meta for halted state (could come from API / project object)
	const datePaused = 'October 10, 2025';
	const progressBeforeHalt = milestone.progress ? milestone.progress + '% Completed' : '—';

	let html = `
		<div class="detail-card">
			<div class="detail-header">
				<h4 class="detail-title">${milestone.title}</h4>
				<span class="detail-badge ${statusClass}">${statusText}</span>
			</div>
			<p class="detail-date text-sm text-gray-500">${milestone.date}</p>
			<p class="detail-description text-sm leading-relaxed mt-2">${milestone.description}</p>

			<div class="mt-4">
				<h5 class="detail-section-title text-xs font-semibold text-gray-700 mb-2">Supporting Files</h5>
				<div class="space-y-1">
					${milestone.supportingFiles && milestone.supportingFiles.length > 0 
						? milestone.supportingFiles.map(file => `
							<a href="#" class="flex items-center gap-2 text-xs px-2 py-1 rounded hover:bg-orange-50 text-orange-600 font-medium">
								<svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
									<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"/>
								</svg>
								${file}
							</a>
						`).join('')
						: '<p class="text-xs text-gray-500">No supporting files available</p>'
					}
				</div>
			</div>

			<hr class="my-6 border-gray-200" />

			<div class="flex flex-col items-center text-center mb-4">
				<div class="w-14 h-14 rounded-full bg-rose-100 flex items-center justify-center mb-3">
					<svg class="w-8 h-8 text-rose-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
						<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01M6.938 19h10.124c1.54 0 2.502-1.667 1.732-3L13.732 5c-.77-1.333-2.694-1.333-3.464 0L5.206 16c-.77 1.333.192 3 1.732 3z" />
					</svg>
				</div>
				<h4 class="text-sm font-semibold text-gray-800 tracking-wide mb-1">This project is currently HALTED.</h4>
				<p id="haltedReasonInline" class="text-xs text-gray-600 italic mb-4">Reason: Pending payment verification. Work will resume once cleared.</p>
				<div class="grid grid-cols-2 gap-6 w-full max-w-xs mb-6">
					<div class="text-left">
						<p class="text-[11px] uppercase font-semibold text-gray-500">Date Paused</p>
						<p class="text-xs font-medium text-gray-900">${datePaused}</p>
					</div>
					<div class="text-left">
						<p class="text-[11px] uppercase font-semibold text-gray-500">Progress Before Halt</p>
						<p class="text-xs font-medium text-gray-900">${progressBeforeHalt}</p>
					</div>
				</div>
				<button onclick="viewHaltDetails()" class="px-6 py-2 border border-orange-400 text-orange-600 hover:bg-orange-50 rounded-full text-xs font-semibold transition-all">
					View Halt Details
				</button>
			</div>
		</div>
	`;

	detailsContainer.innerHTML = html;
}

function openEditHaltedMilestoneModal() {
	// Get the currently selected milestone
	const activeMilestone = document.querySelector('#haltedMilestoneTimeline .milestone-item.active');
	if (!activeMilestone) {
		showNotification('Please select a milestone first', 'warning');
		return;
	}

	const milestoneId = parseInt(activeMilestone.dataset.milestoneId);
	const milestone = HALTED_MILESTONE_DATA.find(m => m.id === milestoneId);
	if (!milestone) {
		showNotification('Milestone data not found', 'error');
		return;
	}

	// Use the existing edit milestone modal
	currentEditMilestoneData = milestone;
	currentEditMilestoneId = milestoneId;
	isEditingHaltedMilestone = true;

	// Populate form fields
	document.getElementById('editMilestoneTitle').value = milestone.title;
	document.getElementById('editMilestoneDescription').value = milestone.description;
	document.getElementById('editMilestoneDate').value = milestone.date;

	// Populate supporting files
	const filesContainer = document.getElementById('editMilestoneFiles');
	if (milestone.supportingFiles && milestone.supportingFiles.length > 0) {
		filesContainer.innerHTML = milestone.supportingFiles.map(file => `
			<div class="flex items-center justify-between p-2 rounded hover:bg-orange-50">
				<a href="#" class="text-orange-600 hover:text-orange-700 font-medium text-sm flex items-center gap-2">
					<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
						<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"/>
					</svg>
					${file}
				</a>
				<div class="flex gap-2">
					<button onclick="viewFile('${file}')" class="text-blue-600 hover:text-blue-700 text-xs">View</button>
					<button onclick="downloadFileFromEdit('${file}')" class="text-green-600 hover:text-green-700 text-xs">Download</button>
					<button onclick="removeMilestoneFile(this)" class="text-red-600 hover:text-red-700 text-xs">Remove</button>
				</div>
			</div>
		`).join('');
	} else {
		filesContainer.innerHTML = '<p class="text-sm text-gray-500">No supporting files</p>';
	}

	showEditMilestoneModal();
}

// Helper to show/hide halted milestone + details section (future extensibility)
function toggleHaltedDetails(forceShow = false) {
	const timeline = document.getElementById('haltedMilestoneTimeline');
	const details = document.getElementById('haltedDetails');
	if (!timeline || !details) return;

	// If forceShow, remove hidden; else toggle
	[timeline, details].forEach(el => {
		if (forceShow) {
			el.classList.remove('hidden');
		} else {
			el.classList.toggle('hidden');
		}
	});
}
// Halted Milestone Data (separate to avoid cross-edit)
const HALTED_MILESTONE_DATA = [
	{
		id: 1,
		progress: 100,
		title: 'Milestone 5',
		date: '12 Dec 9:00 PM',
		description: 'People care about how you see the world, how you think, what motivates you, what you\'re struggling with or afraid of People care about how you see the world, how you think, what motivates you, what you\'re struggling with or afraid of.',
		status: 'pending'
	},
	{
		id: 2,
		progress: 80,
		title: 'Milestone 4',
		date: '21 DEC 11 PM',
		description: 'Construction phase completion with all structural work finished. Quality assurance tests conducted and passed successfully.',
		status: 'pending',
	},
	{
		id: 3,
		progress: 60,
		title: 'Milestone 3',
		date: '21 DEC 9:34 PM',
		description: 'Mid-project review completed. Foundation and framework construction in progress with scheduled timeline adjustments.',
		status: 'in-progress',
		reports: [
			{ title: 'Progress Report 3', date: '21 Dec 9:34 PM', description: 'Currently in progress, working on key deliverables. Framework 70% complete.' },
			{ title: 'Progress Report 3.2', date: '21 Dec 9:34 PM', description: 'Additional materials ordered for next phase. Timeline on track.' }
		],
		supportingFiles: ['progress_photos.jpeg', 'material_list.pdf']
	},
	{
		id: 4,
		progress: 40,
		title: 'Milestone 2',
		date: '20 DEC 2:30 AM',
		description: 'Site preparation and foundation work completed. All permits approved and documentation submitted to regulatory authorities.',
		status: 'completed',
		reports: [
			{ title: 'Progress Report 2', date: '20 Dec 2:30 AM', description: 'Foundation work completed ahead of schedule. All quality checks passed.' },
			{ title: 'Progress Report 2.2', date: '20 Dec 2:30 AM', description: 'Permit documentation submitted and approved by local authorities.' },
			{ title: 'Progress Report 2.3', date: '20 Dec 2:30 AM', description: 'Permit documentation submitted and approved by local authorities.' },
			{ title: 'Progress Report 2.4', date: '20 Dec 2:30 AM', description: 'Permit documentation submitted and approved by local authorities.' }
		],
		supportingFiles: ['foundation_report.pdf', 'permit_docs.jpeg']
	},
	{
		id: 5,
		progress: 20,
		title: 'Milestone 1',
		date: '18 DEC 4:54 AM',
		description: 'Initial project kickoff and planning phase completed. Site survey conducted and design plans finalized.',
		status: 'completed',
		reports: [
			{ title: 'Progress Report 1', date: '20 Dec 2:30 AM', description: 'Foundation work completed ahead of schedule. All quality checks passed.' },
			{ title: 'Progress Report 1.2', date: '20 Dec 2:30 AM', description: 'Permit documentation submitted and approved by local authorities.' },
			{ title: 'Progress Report 1.3', date: '20 Dec 2:30 AM', description: 'Permit documentation submitted and approved by local authorities.' },
			{ title: 'Progress Report 1.4', date: '20 Dec 2:30 AM', description: 'Permit documentation submitted and approved by local authorities.' }
		],
		supportingFiles: ['site_survey.pdf', 'design_plans.jpeg', 'approval_doc.pdf']
	}
];

// ========== Halted Project Payment Summary Data & Functions ==========
// Sample data derived from provided screenshot; adjust to real backend values later.
const HALTED_PAYMENT_DATA = [
	{ milestone: 1, period: 'Sept 15 - Oct 5, 2025', amount: 150000.00, dateOfPayment: 'Oct 5, 2025', uploadedBy: 'Property Owner Name', proofOfPayment: 'sample_photo.jpeg', verificationStatus: 'Verified' },
	{ milestone: 2, period: 'Sept 15 - Oct 5, 2025', amount: 150000.00, dateOfPayment: 'Oct 5, 2025', uploadedBy: 'Property Owner Name', proofOfPayment: 'sample_photo.jpeg', verificationStatus: 'Pending' },
	{ milestone: 2, period: 'Sept 15 - Oct 5, 2025', amount: 150000.00, dateOfPayment: 'Oct 5, 2025', uploadedBy: 'Property Owner Name', proofOfPayment: 'sample_photo.jpeg', verificationStatus: 'Pending' },
	{ milestone: 3, period: 'Sept 15 - Oct 5, 2025', amount: 150000.00, dateOfPayment: 'Oct 5, 2025', uploadedBy: 'Property Owner Name', proofOfPayment: 'sample_photo.jpeg', verificationStatus: 'Invalid Receipt' }
];

function renderHaltedPaymentSummary(project) {
	// Stats (hard-coded to match screenshot specification)
	setText('haltedPaidCount', '3 / 5');
	setText('haltedTotalAmount', 'Php. 10,000,000');
	setText('haltedLastPaymentDate', 'October 13, 2025');
	setText('haltedOverallStatus', 'Partially Paid');

	// Table rows
	const table = document.getElementById('haltedPaymentTable');
	if (!table) return;

	if (HALTED_PAYMENT_DATA.length === 0) {
		table.innerHTML = `<tr><td colspan="7" class="px-4 py-8 text-center text-gray-400 text-sm">No payment records available</td></tr>`;
		return;
	}

	table.innerHTML = HALTED_PAYMENT_DATA.map(p => {
		let badgeClass = '';
		if (p.verificationStatus === 'Verified') badgeClass = 'bg-green-500 text-white';
		else if (p.verificationStatus === 'Pending') badgeClass = 'bg-yellow-500 text-white';
		else if (p.verificationStatus === 'Invalid Receipt') badgeClass = 'bg-red-500 text-white';

		return `
			<tr class="hover:bg-rose-50 transition-colors">
				<td class="px-4 py-3 text-gray-900 font-medium">${p.milestone}</td>
				<td class="px-4 py-3 text-gray-600">${p.period}</td>
				<td class="px-4 py-3 text-gray-900 font-semibold">₱${p.amount.toLocaleString('en-PH', { minimumFractionDigits: 2 })}</td>
				<td class="px-4 py-3 text-gray-600">${p.dateOfPayment}</td>
				<td class="px-4 py-3 text-gray-600">${p.uploadedBy}</td>
				<td class="px-4 py-3">
					<a href="#" class="inline-flex items-center gap-1 text-rose-600 hover:text-rose-800 text-xs font-medium">
						<svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
							<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"/>
						</svg>
						${p.proofOfPayment}
					</a>
				</td>
				<td class="px-4 py-3">
					<span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold ${badgeClass}">${p.verificationStatus}</span>
				</td>
			</tr>
		`;
	}).join('');
}

// ============================================
// Edit Project Modal Functions
// ============================================

let currentEditProject = null;

function openEditProjectModal(projectId) {
	console.log('openEditProjectModal called with ID:', projectId);
	const project = projectsData.find(p => p.id === projectId);
	if (!project) {
		console.error('Project not found:', projectId);
		return;
	}
	
	currentEditProject = project;
	const modal = document.getElementById('editProjectModal');
	if (!modal) {
		console.error('Modal element not found: editProjectModal');
		return;
	}
	console.log('Modal found, populating fields...');

	// Populate form fields
	const pd = project.projectDetails || {};
	
	// Project Title
	const titleEl = document.getElementById('editProjectTitle');
	if (titleEl) titleEl.value = pd.title || '';

	// Description
	const descEl = document.getElementById('editProjectDescription');
	if (descEl) descEl.value = pd.description || '';

	// Property Address
	const addressEl = document.getElementById('editPropertyAddress');
	if (addressEl) addressEl.value = pd.address || '';

	// City/Municipality
	const cityEl = document.getElementById('editCityMunicipality');
	if (cityEl) cityEl.value = pd.city || '';

	// Province
	const provinceEl = document.getElementById('editProvince');
	if (provinceEl) provinceEl.value = pd.province || '';

	// Postal Code
	const postalEl = document.getElementById('editPostalCode');
	if (postalEl) postalEl.value = pd.postalCode || '';

	// Property Type
	const typeEl = document.getElementById('editPropertyType');
	if (typeEl) typeEl.value = pd.propertyType || '';

	// Lot Size
	const lotEl = document.getElementById('editLotSize');
	if (lotEl) lotEl.value = pd.lotSize || '';

	// Timeline
	const timelineMin = document.getElementById('editTimelineMin');
	const timelineMax = document.getElementById('editTimelineMax');
	if (timelineMin && pd.timeline) {
		const timeline = pd.timeline.split('-');
		timelineMin.value = timeline[0]?.trim() || '';
		timelineMax.value = timeline[1]?.trim() || timeline[0]?.trim() || '';
	}

	// Budget
	const budgetMin = document.getElementById('editBudgetMin');
	const budgetMax = document.getElementById('editBudgetMax');
	if (budgetMin && pd.budget) {
		budgetMin.value = pd.budget || '';
		budgetMax.value = pd.budgetMax || pd.budget || '';
	}

	// Bidding Deadline
	const deadlineEl = document.getElementById('editBiddingDeadline');
	if (deadlineEl) deadlineEl.value = pd.deadline || '';

	// Populate file lists
	populateEditFileList('editProjectPhotos', pd.photos || [], 'photo');
	populateEditFileList('editProjectLandTitle', pd.landTitle || [], 'document');
	populateEditFileList('editProjectSupportingFiles', pd.supportingFiles || [], 'file');

	// Show modal
	modal.classList.remove('hidden');
	modal.classList.add('flex');
	document.body.classList.add('overflow-hidden');
}

function hideEditProjectModal() {
	const modal = document.getElementById('editProjectModal');
	if (!modal) return;
	modal.classList.add('hidden');
	modal.classList.remove('flex');
	document.body.classList.remove('overflow-hidden');
	currentEditProject = null;
}

function populateEditFileList(containerId, files, type) {
	const container = document.getElementById(containerId);
	if (!container) return;

	if (!files || files.length === 0) {
		container.innerHTML = '<p class="text-xs text-gray-400 italic">No files uploaded</p>';
		return;
	}

	container.innerHTML = files.map((file, index) => `
		<div class="flex items-center gap-2 p-2 border border-gray-200 rounded-lg bg-gray-50 hover:bg-gray-100 transition group">
			<div class="w-8 h-8 rounded bg-orange-100 flex items-center justify-center flex-shrink-0">
				<svg class="w-4 h-4 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
					<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"/>
				</svg>
			</div>
			<div class="flex-1 min-w-0">
				<p class="text-xs font-semibold text-gray-900 truncate">${file.name || file}</p>
				<p class="text-xs text-gray-500">${type === 'photo' ? 'Image' : 'Document'}</p>
			</div>
			<button type="button" onclick="removeEditFile('${containerId}', ${index})" class="w-6 h-6 rounded hover:bg-red-100 flex items-center justify-center text-gray-400 hover:text-red-600 transition opacity-0 group-hover:opacity-100">
				<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
					<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
				</svg>
			</button>
		</div>
	`).join('');
}

function removeEditFile(containerId, index) {
	// Implement file removal logic here
	console.log('Remove file:', containerId, index);
}

function addPhotoField() {
	const input = document.createElement('input');
	input.type = 'file';
	input.accept = 'image/*';
	input.multiple = true;
	input.onchange = (e) => handleFileAdd('editProjectPhotos', e.target.files, 'photo');
	input.click();
}

function addLandTitleField() {
	const input = document.createElement('input');
	input.type = 'file';
	input.accept = '.pdf,.doc,.docx';
	input.multiple = true;
	input.onchange = (e) => handleFileAdd('editProjectLandTitle', e.target.files, 'document');
	input.click();
}

function addSupportingFileField() {
	const input = document.createElement('input');
	input.type = 'file';
	input.multiple = true;
	input.onchange = (e) => handleFileAdd('editProjectSupportingFiles', e.target.files, 'file');
	input.click();
}

function handleFileAdd(containerId, files, type) {
	if (!files || files.length === 0) return;
	
	const fileArray = Array.from(files).map(f => ({ name: f.name, file: f }));
	const container = document.getElementById(containerId);
	if (!container) return;

	const currentHtml = container.innerHTML;
	const newFilesHtml = Array.from(files).map((file, index) => `
		<div class="flex items-center gap-2 p-2 border border-gray-200 rounded-lg bg-gray-50 hover:bg-gray-100 transition group">
			<div class="w-8 h-8 rounded bg-orange-100 flex items-center justify-center flex-shrink-0">
				<svg class="w-4 h-4 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
					<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"/>
				</svg>
			</div>
			<div class="flex-1 min-w-0">
				<p class="text-xs font-semibold text-gray-900 truncate">${file.name}</p>
				<p class="text-xs text-gray-500">${type === 'photo' ? 'Image' : 'Document'} • New</p>
			</div>
			<button type="button" class="w-6 h-6 rounded hover:bg-red-100 flex items-center justify-center text-gray-400 hover:text-red-600 transition opacity-0 group-hover:opacity-100">
				<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
					<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
				</svg>
			</button>
		</div>
	`).join('');

	if (currentHtml.includes('No files uploaded')) {
		container.innerHTML = newFilesHtml;
	} else {
		container.innerHTML += newFilesHtml;
	}
}

function showEditConfirmation() {
	if (!currentEditProject) return;
	
	const confirmModal = document.getElementById('editProjectConfirmModal');
	if (!confirmModal) return;

	// Update confirmation text with project ID
	const projectIdEl = document.getElementById('confirmProjectId');
	if (projectIdEl) projectIdEl.textContent = currentEditProject.id;

	confirmModal.classList.remove('hidden');
	confirmModal.classList.add('flex');
}

function hideEditConfirmation() {
	const confirmModal = document.getElementById('editProjectConfirmModal');
	if (!confirmModal) return;
	confirmModal.classList.add('hidden');
	confirmModal.classList.remove('flex');
}

function confirmEditProject() {
	if (!currentEditProject) return;

	// Collect form data
	const formData = {
		title: document.getElementById('editProjectTitle')?.value,
		description: document.getElementById('editProjectDescription')?.value,
		address: document.getElementById('editPropertyAddress')?.value,
		city: document.getElementById('editCityMunicipality')?.value,
		province: document.getElementById('editProvince')?.value,
		postalCode: document.getElementById('editPostalCode')?.value,
		propertyType: document.getElementById('editPropertyType')?.value,
		lotSize: document.getElementById('editLotSize')?.value,
		timelineMin: document.getElementById('editTimelineMin')?.value,
		timelineMax: document.getElementById('editTimelineMax')?.value,
		budgetMin: document.getElementById('editBudgetMin')?.value,
		budgetMax: document.getElementById('editBudgetMax')?.value,
		deadline: document.getElementById('editBiddingDeadline')?.value
	};

	console.log('Saving project changes:', currentEditProject.id, formData);

	// Update project data (in production, send to server)
	if (currentEditProject.projectDetails) {
		Object.assign(currentEditProject.projectDetails, {
			title: formData.title,
			description: formData.description,
			address: formData.address,
			city: formData.city,
			province: formData.province,
			postalCode: formData.postalCode,
			propertyType: formData.propertyType,
			lotSize: formData.lotSize,
			timeline: `${formData.timelineMin} - ${formData.timelineMax}`,
			budget: formData.budgetMin,
			budgetMax: formData.budgetMax,
			deadline: formData.deadline
		});
	}

	// Show success notification
	showNotification('Project updated successfully!', 'success');

	// Close modals
	hideEditConfirmation();
	hideEditProjectModal();

	// Refresh table
	renderProjectsTable();
}

function showNotification(message, type = 'info') {
	// Simple notification implementation
	const notification = document.createElement('div');
	notification.className = `fixed top-4 right-4 px-6 py-3 rounded-lg shadow-lg text-white text-sm font-medium z-50 transform transition-all duration-300 ${
		type === 'success' ? 'bg-green-500' :
		type === 'error' ? 'bg-red-500' :
		type === 'warning' ? 'bg-amber-500' :
		'bg-blue-500'
	}`;
	notification.textContent = message;
	document.body.appendChild(notification);

	setTimeout(() => {
		notification.style.opacity = '0';
		notification.style.transform = 'translateX(100%)';
		setTimeout(() => notification.remove(), 300);
	}, 3000);
}

// Ensure functions are globally accessible
window.openEditProjectModal = openEditProjectModal;
window.hideEditProjectModal = hideEditProjectModal;
window.showEditConfirmation = showEditConfirmation;
window.hideEditConfirmation = hideEditConfirmation;
window.confirmEditProject = confirmEditProject;
window.addPhotoField = addPhotoField;
window.addLandTitleField = addLandTitleField;
window.addSupportingFileField = addSupportingFileField;

// Cancelled project modal functions
window.showCancelledProjectModal = showCancelledProjectModal;
window.hideCancelledProjectModal = hideCancelledProjectModal;
window.showTerminationDetailsModal = showTerminationDetailsModal;
window.hideTerminationDetailsModal = hideTerminationDetailsModal;
window.downloadTerminationFiles = downloadTerminationFiles;
window.selectCancelledMilestone = selectCancelledMilestone;






