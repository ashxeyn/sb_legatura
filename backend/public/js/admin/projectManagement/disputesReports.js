// Disputes/Reports page interactivity

// Mock data for reports
const reportsData = [
  {
    id: 'DR-2025-001',
    reporter: 'John Martinez',
    type: 'Dispute',
    subject: 'Payment not received for completed work',
    description: 'I completed the renovation work as agreed, but the property owner has not released the payment. Multiple follow-ups have been ignored. I have all the completion certificates and photos as proof.',
    priority: 'high',
    status: 'pending',
    date: '2025-11-23',
    project: 'Commercial Building Renovation',
    attachments: [
      { name: 'completion-cert.pdf', type: 'pdf' },
      { name: 'work-photos.jpg', type: 'image', url: 'https://images.unsplash.com/photo-1541888946425-d81bb19240f5?w=400' }
    ],
    disputeDetails: {
      reason: 'Payment not received for completed work',
      requestedAction: 'Hold Project',
      remarks: 'Construction of a 2-story commercial complex with parking spaces, electrical systems, and interior finishing. Construction of a 2-story commercial complex with parking spaces, electrical systems, and interior funding.'
    },
    progressReport: {
      milestone: 'Milestone 9',
      outcome: 'Review and correction before next payment milestone'
    },
    documents: [
      { file: 'Progress Report', date: 'Dec 23, 2022', user: 'Carl Saualo', position: 'Architect', icon: 'pdf' },
      { file: 'Progress Report', date: 'Dec 23, 2022', user: 'Carl Saualo', position: 'Architect', icon: 'pdf' }
    ],
    resubmittedReports: [
      { by: 'Saludo Construction', type: 'Revised Progress Report', date: 'October 14, 2025', status: 'Under Review', id: 'RSB-1234234', remarks: 'Revised progress report with updated milestone completion data and supporting documentation.', files: [
        { name: 'Progress Report', date: 'Dec 23, 2022', uploadedBy: 'Carl Saludo', position: 'Architect' },
        { name: 'Progress Report', date: 'Dec 23, 2022', uploadedBy: 'Carl Saludo', position: 'Architect' },
        { name: 'Progress Report', date: 'Dec 23, 2022', uploadedBy: 'Carl Saludo', position: 'Architect' }
      ]},
      { by: 'Saludo Construction', type: 'Revised Progress Report', date: 'October 15, 2025', status: 'Rejected', id: 'RSB-1234235', remarks: 'Resubmission rejected due to incomplete documentation and missing required certifications.', files: [
        { name: 'Progress Report', date: 'Dec 23, 2022', uploadedBy: 'Carl Saludo', position: 'Architect' }
      ]}
    ],
    feedback: {
      from: 'Carl Holmes - (Property Owner)',
      resubmissionId: 'RSB-1243578',
      response: 'Approved',
      dateSubmitted: 'October 21, 2025',
      remarks: 'Construction of a 2-story commercial complex with parking space, electrical systems, and interior finishing. Construction of a 2-story commercial complex with parking space, electrical systems, and interior finishing. Construction of a 2-story commercial complex with parking space, electrical systems, and interior finishing. Construction of a 2-story commercial complex with parking space, electrical systems, and interior funding.'
    }
  },
  {
    id: 'DR-2025-002',
    reporter: 'Sarah Chen',
    type: 'Report',
    subject: 'Contractor using substandard materials',
    description: 'The contractor is using materials that do not match the agreed specifications. I have evidence showing the materials used are of lower quality than what was quoted.',
    priority: 'high',
    status: 'in-progress',
    date: '2025-11-22',
    project: 'Residential Home Extension',
    attachments: [
      { name: 'material-comparison.jpg', type: 'image', url: 'https://images.unsplash.com/photo-1504307651254-35680f356dfd?w=400' }
    ],
    disputeDetails: {
      reason: 'Substandard materials used',
      requestedAction: 'Replace materials',
      remarks: 'Materials provided do not match the agreed specifications. Quality is significantly lower than quoted.'
    },
    progressReport: {
      milestone: 'Milestone 5',
      outcome: 'Material replacement and quality inspection'
    },
    documents: [
      { file: 'Material Specifications', date: 'Nov 22, 2025', user: 'Sarah Chen', position: 'Property Owner', icon: 'pdf' }
    ],
    resubmittedReports: [
      { by: 'Chen Construction Co', type: 'Material Quality Report', date: 'November 22, 2025', status: 'Under Review', id: 'RSB-1234236', remarks: 'Material quality inspection report with certification from approved suppliers.', files: [
        { name: 'Quality Report', date: 'Nov 22, 2025', uploadedBy: 'Sarah Chen', position: 'Property Owner' }
      ]}
    ],
    feedback: {
      from: 'Sarah Chen - (Property Owner)',
      resubmissionId: 'RSB-1243579',
      response: 'Pending Review',
      dateSubmitted: 'November 22, 2025',
      remarks: 'Awaiting inspection of replacement materials to ensure compliance with original specifications.'
    }
  },
  {
    id: 'DR-2025-003',
    reporter: 'David Park',
    type: 'Dispute',
    subject: 'Project deadline missed by 3 weeks',
    description: 'The agreed completion date was November 1st, but work is still ongoing. This delay is causing significant financial impact to my business operations.',
    priority: 'medium',
    status: 'pending',
    date: '2025-11-21',
    project: 'Office Space Remodeling',
    attachments: [],
    disputeDetails: {
      reason: 'Project deadline exceeded',
      requestedAction: 'Expedite completion',
      remarks: 'Work delayed by 3 weeks causing business operation disruptions and financial losses.'
    },
    progressReport: {
      milestone: 'Milestone 7',
      outcome: 'Completion within 1 week with penalty waiver'
    },
    documents: [
      { file: 'Original Timeline', date: 'Nov 21, 2025', user: 'David Park', position: 'Property Owner', icon: 'pdf' }
    ]
  },
  {
    id: 'DR-2025-004',
    reporter: 'Lisa Anderson',
    type: 'Report',
    subject: 'Contractor not responding to messages',
    description: 'The contractor has stopped responding to all communication attempts for the past 10 days. Work has stalled and I need urgent updates on the project status.',
    priority: 'high',
    status: 'dispute',
    date: '2025-11-20',
    project: 'Kitchen Renovation',
    attachments: [
      { name: 'message-screenshots.pdf', type: 'pdf' }
    ],
    disputeDetails: {
      reason: 'Contractor unresponsive',
      requestedAction: 'Resume work immediately',
      remarks: 'No communication for 10 days. Work has completely stalled with no updates on project status.'
    },
    progressReport: {
      milestone: 'Milestone 4',
      outcome: 'Contractor to provide status update within 48 hours'
    },
    documents: [
      { file: 'Communication Log', date: 'Nov 20, 2025', user: 'Lisa Anderson', position: 'Property Owner', icon: 'pdf' }
    ]
  },
  {
    id: 'DR-2025-005',
    reporter: 'Michael Torres',
    type: 'Report',
    subject: 'Safety violations on construction site',
    description: 'Multiple safety protocol violations observed including lack of proper equipment, unsafe scaffolding, and no safety signage.',
    priority: 'high',
    status: 'pending',
    date: '2025-11-20',
    project: 'Bathroom Remodeling',
    attachments: [
      { name: 'safety-violation-1.jpg', type: 'image', url: 'https://images.unsplash.com/photo-1581578731548-c64695cc6952?w=400' },
      { name: 'safety-violation-2.jpg', type: 'image', url: 'https://images.unsplash.com/photo-1590856029826-c7a73142bbf1?w=400' }
    ],
    disputeDetails: {
      reason: 'Safety protocol violations',
      requestedAction: 'Halt work until compliance',
      remarks: 'Multiple violations observed: missing safety equipment, unsafe scaffolding, no proper signage. Immediate action required.'
    },
    progressReport: {
      milestone: 'Milestone 3',
      outcome: 'Safety audit and compliance before proceeding'
    },
    documents: [
      { file: 'Safety Inspection', date: 'Nov 20, 2025', user: 'Michael Torres', position: 'Property Owner', icon: 'pdf' },
      { file: 'Violation Report', date: 'Nov 20, 2025', user: 'Safety Inspector', position: 'Inspector', icon: 'pdf' }
    ]
  },
  {
    id: 'DR-2025-006',
    reporter: 'Emma Wilson',
    type: 'Dispute',
    subject: 'Additional charges not agreed upon',
    description: 'The contractor is demanding additional payment for work that was included in the original quote. I have the signed contract as proof.',
    priority: 'medium',
    status: 'pending',
    date: '2025-11-19',
    project: 'Garden Landscaping',
    attachments: [
      { name: 'original-contract.pdf', type: 'pdf' }
    ],
    disputeDetails: {
      reason: 'Unauthorized additional charges',
      requestedAction: 'Refund extra charges',
      remarks: 'Contractor demanding payment for work already included in original contract scope and quote.'
    },
    progressReport: {
      milestone: 'Milestone 6',
      outcome: 'Contract review and payment adjustment'
    },
    documents: [
      { file: 'Original Contract', date: 'Nov 19, 2025', user: 'Emma Wilson', position: 'Property Owner', icon: 'pdf' },
      { file: 'Invoice Dispute', date: 'Nov 19, 2025', user: 'Emma Wilson', position: 'Property Owner', icon: 'pdf' }
    ]
  },
  {
    id: 'DR-2025-007',
    reporter: 'Robert Kim',
    type: 'Report',
    subject: 'Poor quality workmanship',
    description: 'The electrical work completed does not meet professional standards. Multiple outlets are improperly installed and some wiring appears unsafe.',
    priority: 'high',
    status: 'in-progress',
    date: '2025-11-18',
    project: 'Basement Finishing',
    attachments: [
      { name: 'electrical-issues.jpg', type: 'image', url: 'https://images.unsplash.com/photo-1621905251918-48416bd8575a?w=400' }
    ]
  },
  {
    id: 'DR-2025-008',
    reporter: 'Anna Martinez',
    type: 'Dispute',
    subject: 'Property damage during construction',
    description: 'The contractor damaged my existing hardwood floors during the renovation. They refuse to take responsibility for the repairs.',
    priority: 'medium',
    status: 'resolved',
    date: '2025-11-15',
    project: 'Living Room Renovation',
    attachments: [
      { name: 'floor-damage.jpg', type: 'image', url: 'https://images.unsplash.com/photo-1631679706909-1844bbd07221?w=400' }
    ],
    disputeDetails: {
      reason: 'Property damage caused during renovation work',
      requestedAction: 'Repair or compensate for floor damage',
      remarks: 'Hardwood floors were damaged during demolition phase. Contractor initially denied responsibility but evidence clearly shows negligence. Repair estimate obtained from independent flooring specialist.'
    },
    progressReport: {
      milestone: 'Milestone 4',
      outcome: 'Contractor agreed to repair damages at no cost'
    },
    documents: [
      { file: 'Damage Assessment Report', date: 'Nov 15, 2025', user: 'Anna Martinez', position: 'Property Owner', icon: 'pdf' },
      { file: 'Before & After Photos', date: 'Nov 15, 2025', user: 'Anna Martinez', position: 'Property Owner', icon: 'pdf' },
      { file: 'Repair Estimate', date: 'Nov 16, 2025', user: 'Flooring Specialist', position: 'Inspector', icon: 'pdf' },
      { file: 'Settlement Agreement', date: 'Nov 20, 2025', user: 'Admin', position: 'Administrator', icon: 'pdf' }
    ],
    resubmittedReports: [
      { by: 'Martinez Construction', type: 'Floor Repair Plan', date: 'November 17, 2025', status: 'Approved', id: 'RSB-1234567', remarks: 'Detailed repair plan submitted with timeline and materials to be used. Independent inspector approved the proposed solution.', files: [
        { name: 'Repair Plan Document', date: 'Nov 17, 2025', uploadedBy: 'Martinez Construction', position: 'Contractor' },
        { name: 'Material Specifications', date: 'Nov 17, 2025', uploadedBy: 'Martinez Construction', position: 'Contractor' }
      ]},
      { by: 'Martinez Construction', type: 'Completion Certificate', date: 'November 22, 2025', status: 'Approved', id: 'RSB-1234568', remarks: 'Floor repair completed successfully. Property owner and independent inspector both verified quality of work.', files: [
        { name: 'Completion Photos', date: 'Nov 22, 2025', uploadedBy: 'Martinez Construction', position: 'Contractor' },
        { name: 'Quality Inspection Report', date: 'Nov 22, 2025', uploadedBy: 'Independent Inspector', position: 'Inspector' }
      ]}
    ],
    feedback: {
      from: 'Anna Martinez - (Property Owner)',
      resubmissionId: 'RSB-1234568',
      response: 'Approved',
      dateSubmitted: 'November 23, 2025',
      remarks: 'Very satisfied with the resolution. The contractor took full responsibility and completed the repairs professionally. The new flooring matches perfectly with the existing hardwood. Case resolved to my complete satisfaction.'
    }
  },
  {
    id: 'DR-2025-009',
    reporter: 'James Lee',
    type: 'Report',
    subject: 'Unauthorized subcontractors on site',
    description: 'The main contractor has hired subcontractors without informing me or getting approval as required in our contract.',
    priority: 'low',
    status: 'resolved',
    date: '2025-11-14',
    project: 'Roofing Replacement',
    attachments: [],
    disputeDetails: {
      reason: 'Unauthorized subcontractors hired without approval',
      requestedAction: 'Verify credentials and adjust contract terms',
      remarks: 'Contract explicitly states that all subcontractors must be approved by property owner. Found unknown workers on site without prior notification or credential verification.'
    },
    progressReport: {
      milestone: 'Milestone 2',
      outcome: 'All subcontractors verified and documented'
    },
    documents: [
      { file: 'Original Contract', date: 'Nov 14, 2025', user: 'James Lee', position: 'Property Owner', icon: 'pdf' },
      { file: 'Site Visit Report', date: 'Nov 14, 2025', user: 'James Lee', position: 'Property Owner', icon: 'pdf' },
      { file: 'Subcontractor Credentials', date: 'Nov 16, 2025', user: 'Lee Roofing Services', position: 'Contractor', icon: 'pdf' }
    ],
    resubmittedReports: [
      { by: 'Lee Roofing Services', type: 'Subcontractor Documentation', date: 'November 16, 2025', status: 'Approved', id: 'RSB-1234789', remarks: 'Complete list of all subcontractors with licenses, insurance certificates, and background checks submitted for approval.', files: [
        { name: 'Subcontractor List', date: 'Nov 16, 2025', uploadedBy: 'Lee Roofing Services', position: 'Contractor' },
        { name: 'License Verification', date: 'Nov 16, 2025', uploadedBy: 'Lee Roofing Services', position: 'Contractor' },
        { name: 'Insurance Certificates', date: 'Nov 16, 2025', uploadedBy: 'Lee Roofing Services', position: 'Contractor' }
      ]}
    ],
    feedback: {
      from: 'James Lee - (Property Owner)',
      resubmissionId: 'RSB-1234789',
      response: 'Approved',
      dateSubmitted: 'November 18, 2025',
      remarks: 'All subcontractor credentials have been verified and are satisfactory. Contractor has agreed to notify me in advance of any future subcontractor changes. Issue resolved amicably.'
    }
  },
  {
    id: 'DR-2025-010',
    reporter: 'Patricia Brown',
    type: 'Dispute',
    subject: 'Incomplete work at project closure',
    description: 'The contractor claims the project is complete, but several items from the scope of work are unfinished or poorly executed.',
    priority: 'medium',
    status: 'resolved',
    date: '2025-11-12',
    project: 'Outdoor Deck Construction',
    attachments: [
      { name: 'incomplete-work-list.pdf', type: 'pdf' },
      { name: 'incomplete-areas.jpg', type: 'image', url: 'https://images.unsplash.com/photo-1600585154340-be6161a56a0c?w=400' }
    ],
    disputeDetails: {
      reason: 'Multiple scope items incomplete at claimed project completion',
      requestedAction: 'Complete all remaining work items before final payment',
      remarks: 'Contractor requested final payment claiming 100% completion. Inspection revealed: railing sections missing, stain application incomplete, lighting fixtures not installed, and deck boards improperly secured. Original scope clearly includes all these items.'
    },
    progressReport: {
      milestone: 'Milestone 8',
      outcome: 'Punch list created and all items completed'
    },
    documents: [
      { file: 'Original Scope of Work', date: 'Nov 12, 2025', user: 'Patricia Brown', position: 'Property Owner', icon: 'pdf' },
      { file: 'Incomplete Items List', date: 'Nov 12, 2025', user: 'Patricia Brown', position: 'Property Owner', icon: 'pdf' },
      { file: 'Inspection Report', date: 'Nov 13, 2025', user: 'Building Inspector', position: 'Inspector', icon: 'pdf' },
      { file: 'Punch List Agreement', date: 'Nov 14, 2025', user: 'Admin', position: 'Administrator', icon: 'pdf' },
      { file: 'Final Completion Photos', date: 'Nov 24, 2025', user: 'Brown Deck Builders', position: 'Contractor', icon: 'pdf' }
    ],
    resubmittedReports: [
      { by: 'Brown Deck Builders', type: 'Punch List Progress Report', date: 'November 18, 2025', status: 'Approved', id: 'RSB-1234890', remarks: 'Progress update showing 60% of punch list items completed. Photos and detailed status for each item provided.', files: [
        { name: 'Progress Update', date: 'Nov 18, 2025', uploadedBy: 'Brown Deck Builders', position: 'Contractor' },
        { name: 'Completion Photos', date: 'Nov 18, 2025', uploadedBy: 'Brown Deck Builders', position: 'Contractor' }
      ]},
      { by: 'Brown Deck Builders', type: 'Final Completion Report', date: 'November 24, 2025', status: 'Approved', id: 'RSB-1234891', remarks: 'All punch list items completed. Comprehensive photo documentation and third-party inspection certificate submitted.', files: [
        { name: 'Final Inspection Certificate', date: 'Nov 24, 2025', uploadedBy: 'Building Inspector', position: 'Inspector' },
        { name: 'Complete Deck Photos', date: 'Nov 24, 2025', uploadedBy: 'Brown Deck Builders', position: 'Contractor' },
        { name: 'Material Warranties', date: 'Nov 24, 2025', uploadedBy: 'Brown Deck Builders', position: 'Contractor' }
      ]}
    ],
    feedback: {
      from: 'Patricia Brown - (Property Owner)',
      resubmissionId: 'RSB-1234891',
      response: 'Approved',
      dateSubmitted: 'November 25, 2025',
      remarks: 'All incomplete items have been finished to a high standard. The deck now matches the original specifications perfectly. Contractor was professional in addressing the concerns and completing the punch list. Final payment released and project successfully closed.'
    }
  }
];

let currentFilter = 'all';
let currentSort = 'date';
let selectedReport = null;
let currentResubmittedReport = null;

document.addEventListener('DOMContentLoaded', () => {
  renderTable();
  setupEventListeners();
});

// Render table rows
function renderTable(filter = 'all', sort = 'date') {
  const tbody = document.getElementById('reportsTableBody');
  let filtered = [...reportsData];

  // Apply filter
  if (filter === 'pending') {
    filtered = filtered.filter(r => r.status === 'pending');
  } else if (filter === 'disputes') {
    filtered = filtered.filter(r => r.type === 'Dispute');
  } else if (filter === 'resolved') {
    filtered = filtered.filter(r => r.status === 'resolved');
  }

  // Apply sort
  if (sort === 'date') {
    filtered.sort((a, b) => new Date(b.date) - new Date(a.date));
  } else if (sort === 'priority') {
    const priorityOrder = { high: 3, medium: 2, low: 1 };
    filtered.sort((a, b) => priorityOrder[b.priority] - priorityOrder[a.priority]);
  } else if (sort === 'status') {
    filtered.sort((a, b) => a.status.localeCompare(b.status));
  }

  tbody.innerHTML = filtered.map(report => `
    <tr class="report-row" data-id="${report.id}">
      <td class="px-6 py-4">
        <span class="text-sm font-bold text-indigo-600">${report.id}</span>
      </td>
      <td class="px-6 py-4">
        <div class="flex items-center gap-3">
          <div class="w-9 h-9 rounded-full bg-gradient-to-br from-indigo-500 to-purple-500 flex items-center justify-center text-white font-semibold text-xs">
            ${report.reporter.split(' ').map(n => n[0]).join('')}
          </div>
          <span class="text-sm font-semibold text-gray-800">${report.reporter}</span>
        </div>
      </td>
      <td class="px-6 py-4">
        <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold ${
          report.type === 'Dispute' 
            ? 'bg-red-100 text-red-700' 
            : 'bg-blue-100 text-blue-700'
        }">
          ${report.type}
        </span>
      </td>
      <td class="px-6 py-4">
        <p class="text-sm text-gray-800 font-medium truncate max-w-xs">${report.subject}</p>
      </td>
      <td class="px-6 py-4">
        <span class="priority-badge ${report.priority}">
          ${report.priority}
        </span>
      </td>
      <td class="px-6 py-4">
        <span class="status-badge ${report.status.replace(' ', '-')}">
          <span class="w-2 h-2 rounded-full ${getStatusColor(report.status)}"></span>
          ${report.status.charAt(0).toUpperCase() + report.status.slice(1).replace('-', ' ')}
        </span>
      </td>
      <td class="px-6 py-4">
        <span class="text-sm text-gray-600">${formatDate(report.date)}</span>
      </td>
      <td class="px-6 py-4 text-center">
        <button class="action-btn view" onclick="viewReport('${report.id}')">
          <i class="fi fi-rr-eye"></i>
          View
        </button>
      </td>
    </tr>
  `).join('');
}

// View report details
function viewReport(id) {
  selectedReport = reportsData.find(r => r.id === id);
  if (!selectedReport) return;

  // Populate modal
  document.getElementById('modalCaseId').textContent = `Case ${selectedReport.id}`;
  document.getElementById('modalReporter').textContent = selectedReport.reporter;
  document.getElementById('modalType').textContent = selectedReport.type;
  document.getElementById('modalDate').textContent = formatDate(selectedReport.date);
  document.getElementById('modalProject').textContent = selectedReport.project;
  document.getElementById('modalSubject').textContent = selectedReport.subject;
  document.getElementById('modalDescription').textContent = selectedReport.description;

  // Priority badge
  document.getElementById('modalPriority').innerHTML = `
    <span class="priority-badge ${selectedReport.priority}">${selectedReport.priority}</span>
  `;

  // Status badge
  document.getElementById('modalStatus').innerHTML = `
    <span class="status-badge ${selectedReport.status.replace(' ', '-')}">
      <span class="w-2 h-2 rounded-full ${getStatusColor(selectedReport.status)}"></span>
      ${selectedReport.status.charAt(0).toUpperCase() + selectedReport.status.slice(1).replace('-', ' ')}
    </span>
  `;

  // Dispute Details
  if (selectedReport.disputeDetails) {
    document.getElementById('modalReasonDispute').textContent = selectedReport.disputeDetails.reason || '-';
    document.getElementById('modalRequestedAction').textContent = selectedReport.disputeDetails.requestedAction || '-';
    document.getElementById('modalRemarks').textContent = selectedReport.disputeDetails.remarks || '-';
  }

  // Progress Report
  if (selectedReport.progressReport) {
    document.getElementById('modalMilestone').textContent = selectedReport.progressReport.milestone || '-';
    document.getElementById('modalOutcome').textContent = selectedReport.progressReport.outcome || '-';
  }

  // Supporting Documents
  const documentsTable = document.getElementById('modalDocumentsTable');
  if (selectedReport.documents && selectedReport.documents.length > 0) {
    documentsTable.innerHTML = selectedReport.documents.map(doc => `
      <tr class="hover:bg-gray-50 transition">
        <td class="px-4 py-3">
          <div class="flex items-center gap-2">
            <div class="w-8 h-8 rounded-lg bg-gradient-to-br from-red-100 to-rose-100 flex items-center justify-center">
              <i class="fi fi-rr-file-pdf text-red-600 text-sm"></i>
            </div>
            <span class="font-medium text-gray-800">${doc.file}</span>
          </div>
        </td>
        <td class="px-4 py-3 text-gray-600">${doc.date}</td>
        <td class="px-4 py-3 text-gray-600">${doc.user}</td>
        <td class="px-4 py-3">
          <span class="inline-flex px-2 py-1 rounded-full text-xs font-semibold bg-indigo-100 text-indigo-700">
            ${doc.position}
          </span>
        </td>
        <td class="px-4 py-3 text-center">
          <div class="flex items-center justify-center gap-2">
            <button class="download-doc-btn w-8 h-8 rounded-lg bg-gradient-to-br from-blue-500 to-indigo-500 hover:from-blue-600 hover:to-indigo-600 flex items-center justify-center text-white shadow-sm transition" title="Download" data-filename="${doc.file}" data-user="${doc.user}" data-date="${doc.date}">
              <i class="fi fi-rr-download text-xs"></i>
            </button>
            <button class="delete-doc-btn w-8 h-8 rounded-lg bg-gradient-to-br from-red-500 to-rose-500 hover:from-red-600 hover:to-rose-600 flex items-center justify-center text-white shadow-sm transition" title="Delete" data-filename="${doc.file}" data-user="${doc.user}" data-date="${doc.date}">
              <i class="fi fi-rr-trash text-xs"></i>
            </button>
          </div>
        </td>
      </tr>
    `).join('');

    // Add click listeners to download buttons
    document.querySelectorAll('.download-doc-btn').forEach(btn => {
      btn.addEventListener('click', (e) => {
        const filename = e.currentTarget.dataset.filename;
        showDownloadConfirmation(filename);
      });
    });

    // Add click listeners to delete buttons
    document.querySelectorAll('.delete-doc-btn').forEach(btn => {
      btn.addEventListener('click', (e) => {
        const filename = e.currentTarget.dataset.filename;
        const user = e.currentTarget.dataset.user;
        const date = e.currentTarget.dataset.date;
        showDeleteConfirmation(filename, user, date);
      });
    });
  } else {
    documentsTable.innerHTML = `
      <tr>
        <td colspan="5" class="px-4 py-8 text-center text-gray-500 text-sm">
          <i class="fi fi-rr-folder-open text-3xl text-gray-300 mb-2"></i>
          <p>No supporting documents available</p>
        </td>
      </tr>
    `;
  }

  // Resubmitted Reports
  const resubmittedTable = document.getElementById('modalResubmittedTable');
  if (selectedReport.resubmittedReports && selectedReport.resubmittedReports.length > 0) {
    resubmittedTable.innerHTML = selectedReport.resubmittedReports.map((report, index) => {
      const statusColors = {
        'Under Review': { bg: 'bg-amber-100', text: 'text-amber-700', border: 'border-amber-300' },
        'Rejected': { bg: 'bg-red-100', text: 'text-red-700', border: 'border-red-300' },
        'Approved': { bg: 'bg-emerald-100', text: 'text-emerald-700', border: 'border-emerald-300' }
      };
      const statusStyle = statusColors[report.status] || statusColors['Under Review'];
      
      return `
        <tr class="hover:bg-gray-50 transition">
          <td class="px-4 py-3 font-medium text-gray-800">${report.by}</td>
          <td class="px-4 py-3 text-gray-600">${report.type}</td>
          <td class="px-4 py-3 text-gray-600">${report.date}</td>
          <td class="px-4 py-3">
            <span class="inline-flex px-3 py-1 rounded-full text-xs font-semibold ${statusStyle.bg} ${statusStyle.text} border ${statusStyle.border}">
              ${report.status}
            </span>
          </td>
          <td class="px-4 py-3">
            <div class="flex items-center justify-center gap-2">
              <button class="view-resubmitted-btn w-8 h-8 rounded-lg bg-gradient-to-br from-blue-500 to-indigo-500 hover:from-blue-600 hover:to-indigo-600 flex items-center justify-center text-white shadow-sm transition" title="View" data-index="${index}">
                <i class="fi fi-rr-eye text-xs"></i>
              </button>
              <button class="download-resubmitted-btn w-8 h-8 rounded-lg bg-gradient-to-br from-emerald-500 to-teal-500 hover:from-emerald-600 hover:to-teal-600 flex items-center justify-center text-white shadow-sm transition" title="Download" data-filename="${report.type}" data-user="${report.by}" data-date="${report.date}">
                <i class="fi fi-rr-download text-xs"></i>
              </button>
              <button class="delete-resubmitted-btn w-8 h-8 rounded-lg bg-gradient-to-br from-red-500 to-rose-500 hover:from-red-600 hover:to-rose-600 flex items-center justify-center text-white shadow-sm transition" title="Delete" data-filename="${report.type}" data-user="${report.by}" data-date="${report.date}">
                <i class="fi fi-rr-trash text-xs"></i>
              </button>
            </div>
          </td>
        </tr>
      `;
    }).join('');

    // Add click listeners to view buttons
    document.querySelectorAll('.view-resubmitted-btn').forEach(btn => {
      btn.addEventListener('click', (e) => {
        const index = parseInt(e.currentTarget.dataset.index);
        viewResubmittedReport(selectedReport.resubmittedReports[index]);
      });
    });

    // Add click listeners to download buttons
    document.querySelectorAll('.download-resubmitted-btn').forEach(btn => {
      btn.addEventListener('click', (e) => {
        const filename = e.currentTarget.dataset.filename;
        showDownloadConfirmation(filename);
      });
    });

    // Add click listeners to delete buttons
    document.querySelectorAll('.delete-resubmitted-btn').forEach(btn => {
      btn.addEventListener('click', (e) => {
        const filename = e.currentTarget.dataset.filename;
        const user = e.currentTarget.dataset.user;
        const date = e.currentTarget.dataset.date;
        showDeleteConfirmation(filename, user, date);
      });
    });
  } else {
    resubmittedTable.innerHTML = `
      <tr>
        <td colspan="5" class="px-4 py-8 text-center text-gray-500 text-sm">
          <i class="fi fi-rr-refresh text-3xl text-gray-300 mb-2"></i>
          <p>No resubmitted reports available</p>
        </td>
      </tr>
    `;
  }

  // Feedback Monitoring
  if (selectedReport.feedback) {
    document.getElementById('modalFeedbackFrom').textContent = selectedReport.feedback.from || '-';
    document.getElementById('modalResubmissionId').textContent = selectedReport.feedback.resubmissionId || '-';
    document.getElementById('modalFeedbackDate').textContent = selectedReport.feedback.dateSubmitted || '-';
    document.getElementById('modalFeedbackRemarks').textContent = selectedReport.feedback.remarks || '-';
    
    const responseEl = document.getElementById('modalFeedbackResponse');
    const response = selectedReport.feedback.response || 'Pending';
    const responseColors = {
      'Approved': { bg: 'bg-emerald-100', text: 'text-emerald-700' },
      'Rejected': { bg: 'bg-red-100', text: 'text-red-700' },
      'Pending Review': { bg: 'bg-amber-100', text: 'text-amber-700' }
    };
    const responseStyle = responseColors[response] || responseColors['Pending Review'];
    responseEl.className = `inline-flex px-3 py-1 rounded-full text-xs font-semibold ${responseStyle.bg} ${responseStyle.text}`;
    responseEl.textContent = response;
  }

  // Attachments
  const attachmentsSection = document.getElementById('modalAttachmentsSection');
  const attachmentsContainer = document.getElementById('modalAttachments');
  
  if (selectedReport.attachments && selectedReport.attachments.length > 0) {
    attachmentsSection.classList.remove('hidden');
    attachmentsContainer.innerHTML = selectedReport.attachments.map(att => {
      if (att.type === 'image' && att.url) {
        return `
          <div class="attachment-preview">
            <img src="${att.url}" alt="${att.name}">
            <div class="flex-1 min-w-0">
              <p class="text-xs font-semibold text-gray-800 truncate">${att.name}</p>
              <p class="text-xs text-gray-500">Image</p>
            </div>
          </div>
        `;
      } else {
        return `
          <div class="attachment-preview">
            <div class="file-icon">
              <i class="fi fi-rr-file"></i>
            </div>
            <div class="flex-1 min-w-0">
              <p class="text-xs font-semibold text-gray-800 truncate">${att.name}</p>
              <p class="text-xs text-gray-500">${att.type.toUpperCase()}</p>
            </div>
          </div>
        `;
      }
    }).join('');
  } else {
    attachmentsSection.classList.add('hidden');
  }

  // Show/hide resolve button based on status
  const resolveBtn = document.getElementById('resolveBtn');
  if (selectedReport.status === 'resolved') {
    resolveBtn.classList.add('hidden');
  } else {
    resolveBtn.classList.remove('hidden');
  }

  showModal('viewDetailsModal');
}

// Setup event listeners
function setupEventListeners() {
  // Filter tabs
  document.querySelectorAll('.filter-tab').forEach(tab => {
    tab.addEventListener('click', () => {
      document.querySelectorAll('.filter-tab').forEach(t => t.classList.remove('active'));
      tab.classList.add('active');
      currentFilter = tab.dataset.filter;
      renderTable(currentFilter, currentSort);
    });
  });

  // Sort dropdown
  document.getElementById('sortBy').addEventListener('change', (e) => {
    currentSort = e.target.value;
    renderTable(currentFilter, currentSort);
  });

  // Global search
  document.getElementById('globalSearch').addEventListener('input', (e) => {
    const query = e.target.value.toLowerCase();
    document.querySelectorAll('.report-row').forEach(row => {
      const text = row.textContent.toLowerCase();
      row.style.display = text.includes(query) ? '' : 'none';
    });
  });

  // Modal close buttons
  document.querySelectorAll('.modal-close').forEach(btn => {
    btn.addEventListener('click', (e) => {
      const modal = e.target.closest('.modal-overlay');
      if (modal) hideModal(modal.id);
    });
  });

  // Close modal on overlay click
  document.querySelectorAll('.modal-overlay').forEach(overlay => {
    overlay.addEventListener('click', (e) => {
      if (e.target === overlay) {
        hideModal(overlay.id);
      }
    });
  });

  // Resolve button
  document.getElementById('resolveBtn').addEventListener('click', () => {
    showModal('resolveConfirmModal');
  });

  // Confirm resolve
  document.getElementById('confirmResolveBtn').addEventListener('click', () => {
    const notes = document.getElementById('resolutionNotes').value.trim();
    if (!notes) {
      toast('Please provide resolution notes', 'error');
      return;
    }

    // Update report status
    if (selectedReport) {
      selectedReport.status = 'resolved';
      hideModal('resolveConfirmModal');
      hideModal('viewDetailsModal');
      renderTable(currentFilter, currentSort);
      toast('Case marked as resolved successfully', 'success');
      document.getElementById('resolutionNotes').value = '';
    }
  });

  // Download confirmation
  document.getElementById('confirmDownloadBtn').addEventListener('click', () => {
    toast('File download started', 'success');
    hideModal('downloadConfirmModal');
  });

  // Delete confirmation
  document.getElementById('confirmDeleteBtn').addEventListener('click', () => {
    const reason = document.getElementById('deleteReason').value.trim();
    toast('File deleted successfully', 'success');
    hideModal('deleteConfirmModal');
    document.getElementById('deleteReason').value = '';
    // Refresh current modal content if needed
  });

  // Approve resubmitted report button
  document.getElementById('approveResubmittedBtn').addEventListener('click', () => {
    if (currentResubmittedReport) {
      document.getElementById('approveResubmissionId').textContent = currentResubmittedReport.id || '-';
      document.getElementById('approveResubmittedBy').textContent = currentResubmittedReport.by || '-';
      document.getElementById('approveResubmissionType').textContent = currentResubmittedReport.type || '-';
      showModal('approveResubmittedConfirmModal');
    }
  });

  // Reject resubmitted report button
  document.getElementById('rejectResubmittedBtn').addEventListener('click', () => {
    if (currentResubmittedReport) {
      document.getElementById('rejectResubmissionId').textContent = currentResubmittedReport.id || '-';
      document.getElementById('rejectResubmittedBy').textContent = currentResubmittedReport.by || '-';
      document.getElementById('rejectResubmissionType').textContent = currentResubmittedReport.type || '-';
      showModal('rejectResubmittedConfirmModal');
    }
  });

  // Confirm approve resubmitted report
  document.getElementById('confirmApproveResubmittedBtn').addEventListener('click', () => {
    const notes = document.getElementById('approveNotes').value.trim();
    if (currentResubmittedReport) {
      currentResubmittedReport.status = 'Approved';
      toast('Report approved successfully', 'success');
      hideModal('approveResubmittedConfirmModal');
      document.getElementById('approveNotes').value = '';
      // Refresh the resubmitted report modal with updated status
      viewResubmittedReport(currentResubmittedReport);
    }
  });

  // Confirm reject resubmitted report
  document.getElementById('confirmRejectResubmittedBtn').addEventListener('click', () => {
    const reason = document.getElementById('rejectReason').value.trim();
    if (!reason) {
      toast('Please provide a rejection reason', 'error');
      return;
    }
    if (currentResubmittedReport) {
      currentResubmittedReport.status = 'Rejected';
      toast('Report rejected successfully', 'success');
      hideModal('rejectResubmittedConfirmModal');
      document.getElementById('rejectReason').value = '';
      // Refresh the resubmitted report modal with updated status
      viewResubmittedReport(currentResubmittedReport);
    }
  });

  // Confirm download resubmitted file
  document.getElementById('confirmDownloadResubmittedBtn').addEventListener('click', () => {
    toast('File download started', 'success');
    hideModal('downloadResubmittedFileModal');
  });
}

// Helper functions
function getStatusColor(status) {
  const colors = {
    pending: 'bg-amber-500',
    'in-progress': 'bg-blue-500',
    resolved: 'bg-emerald-500',
    dispute: 'bg-red-500'
  };
  return colors[status] || 'bg-gray-500';
}

function formatDate(dateString) {
  const date = new Date(dateString);
  const now = new Date();
  const diffTime = Math.abs(now - date);
  const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24));

  if (diffDays === 0) return 'Today';
  if (diffDays === 1) return 'Yesterday';
  if (diffDays < 7) return `${diffDays} days ago`;
  
  return date.toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' });
}

function showModal(id) {
  const modal = document.getElementById(id);
  modal.classList.remove('hidden');
  modal.classList.add('flex');
}

function hideModal(id) {
  const modal = document.getElementById(id);
  modal.classList.add('hidden');
  modal.classList.remove('flex');
}

function toast(message, type = 'info') {
  const existing = document.querySelector('.toast');
  if (existing) existing.remove();

  const toast = document.createElement('div');
  toast.className = `toast ${type}`;
  
  const icon = type === 'success' ? 'fi-rr-check-circle' : type === 'error' ? 'fi-rr-cross-circle' : 'fi-rr-info';
  toast.innerHTML = `
    <i class="fi ${icon}"></i>
    <span>${message}</span>
  `;
  
  document.body.appendChild(toast);

  setTimeout(() => {
    toast.style.opacity = '0';
    toast.style.transform = 'translateY(10px)';
    setTimeout(() => toast.remove(), 300);
  }, 3000);
}

// View Resubmitted Report Details
function viewResubmittedReport(report) {
  if (!report) return;

  // Store current report for approve/reject actions
  currentResubmittedReport = report;

  // Populate modal fields
  document.getElementById('resubmittedId').textContent = report.id || '-';
  document.getElementById('resubmittedBy').textContent = report.by || '-';
  document.getElementById('resubmittedType').textContent = report.type || '-';
  document.getElementById('resubmittedDate').textContent = report.date || '-';
  document.getElementById('resubmittedRemarks').value = report.remarks || '';

  // Status badge
  const statusEl = document.getElementById('resubmittedStatus');
  const statusColors = {
    'Under Review': { bg: 'bg-amber-100', text: 'text-amber-700', border: 'border-amber-300' },
    'Rejected': { bg: 'bg-red-100', text: 'text-red-700', border: 'border-red-300' },
    'Approved': { bg: 'bg-emerald-100', text: 'text-emerald-700', border: 'border-emerald-300' }
  };
  const statusStyle = statusColors[report.status] || statusColors['Under Review'];
  statusEl.className = `inline-flex px-4 py-2 rounded-full text-sm font-semibold ${statusStyle.bg} ${statusStyle.text} border ${statusStyle.border}`;
  statusEl.textContent = report.status;

  // Populate files table
  const filesTable = document.getElementById('resubmittedFilesTable');
  if (report.files && report.files.length > 0) {
    filesTable.innerHTML = report.files.map((file, index) => `
      <tr class="hover:bg-gray-50 transition">
        <td class="px-4 py-3">
          <input type="checkbox" class="w-4 h-4 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
        </td>
        <td class="px-4 py-3">
          <div class="flex items-center gap-2">
            <div class="w-8 h-8 rounded-lg bg-gradient-to-br from-red-100 to-rose-100 flex items-center justify-center">
              <i class="fi fi-rr-file-pdf text-red-600 text-sm"></i>
            </div>
            <span class="font-medium text-gray-800">${file.name}</span>
          </div>
        </td>
        <td class="px-4 py-3 text-gray-600">${file.date}</td>
        <td class="px-4 py-3 text-gray-600">${file.uploadedBy}</td>
        <td class="px-4 py-3">
          <span class="inline-flex px-2 py-1 rounded-full text-xs font-semibold bg-indigo-100 text-indigo-700">
            ${file.position}
          </span>
        </td>
        <td class="px-4 py-3 text-center">
          <button class="download-resubmitted-file-btn w-8 h-8 rounded-lg bg-gradient-to-br from-emerald-500 to-teal-500 hover:from-emerald-600 hover:to-teal-600 flex items-center justify-center text-white shadow-sm transition mx-auto" title="Download" data-filename="${file.name}" data-reportid="${report.id}">
            <i class="fi fi-rr-download text-xs"></i>
          </button>
        </td>
      </tr>
    `).join('');

    // Add click listeners to download buttons
    document.querySelectorAll('.download-resubmitted-file-btn').forEach(btn => {
      btn.addEventListener('click', (e) => {
        const filename = e.currentTarget.dataset.filename;
        const reportId = e.currentTarget.dataset.reportid;
        showDownloadResubmittedFileConfirmation(filename, reportId);
      });
    });
  } else {
    filesTable.innerHTML = `
      <tr>
        <td colspan="6" class="px-4 py-8 text-center text-gray-500 text-sm">
          <i class="fi fi-rr-folder-open text-3xl text-gray-300 mb-2"></i>
          <p>No files uploaded</p>
        </td>
      </tr>
    `;
  }

  // Show modal
  showModal('resubmittedReportModal');
}

// Show download confirmation modal
function showDownloadConfirmation(filename) {
  document.getElementById('downloadFileName').textContent = `File: ${filename}`;
  document.getElementById('downloadFileNameDisplay').textContent = filename;
  showModal('downloadConfirmModal');
}

// Show delete confirmation modal
function showDeleteConfirmation(filename, uploadedBy, date) {
  document.getElementById('deleteFileNameDisplay').textContent = filename;
  document.getElementById('deleteFileUploader').textContent = uploadedBy;
  document.getElementById('deleteFileDate').textContent = date;
  showModal('deleteConfirmModal');
}

// Show download resubmitted file confirmation modal
function showDownloadResubmittedFileConfirmation(filename, reportId) {
  document.getElementById('downloadResubmittedFileName').textContent = `File: ${filename}`;
  document.getElementById('downloadResubmittedFileNameDisplay').textContent = filename;
  document.getElementById('downloadResubmittedReportId').textContent = reportId || 'N/A';
  showModal('downloadResubmittedFileModal');
}
