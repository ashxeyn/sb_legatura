document.addEventListener('DOMContentLoaded', function() {

  // Fetch and update table content (similar to propertyOwner module)
  async function fetchAndUpdate(url) {
    try {
      const response = await fetch(url, {
        headers: {
          'X-Requested-With': 'XMLHttpRequest'
        }
      });

      if (!response.ok) throw new Error('Network response was not ok');

      const data = await response.json();

      const projectsWrap = document.getElementById('projectsTableWrap');
      if (projectsWrap && data.html) {
        projectsWrap.innerHTML = data.html;
      }

      // Re-attach action listeners after updating the table
      if (typeof window.attachActionListeners === 'function') {
        window.attachActionListeners();
      }

    } catch (error) {
      console.error('Error fetching data:', error);
    }
  }

  // Make fetchAndUpdate globally accessible
  window.refreshProjectsTable = function() {
    const url = window.location.href;
    fetchAndUpdate(url);
  };

  // Make attachActionListeners globally accessible so filters.js can call it after AJAX updates
  window.attachActionListeners = function() {
    const viewButtons = document.querySelectorAll('.view-btn');
    const editButtons = document.querySelectorAll('.edit-btn');
    const deleteButtons = document.querySelectorAll('.delete-btn');
    const restoreButtons = document.querySelectorAll('.restore-btn');

    viewButtons.forEach(btn => {
      btn.addEventListener('click', function(e) {
        e.stopPropagation();
        addRipple(this, e);
        const id = this.getAttribute('data-id');
        if (id) {
          setTimeout(() => {
            fetchProjectDetails(id);
          }, 200);
        }
      });
    });

    editButtons.forEach(btn => {
      btn.addEventListener('click', function(e) {
        e.stopPropagation();
        addRipple(this, e);
        const id = this.getAttribute('data-id');
        if (id) {
          setTimeout(() => {
            showEditProjectModal(id);
          }, 200);
        }
      });
    });

    deleteButtons.forEach(btn => {
      btn.addEventListener('click', function(e) {
        e.stopPropagation();
        addRipple(this, e);
        const id = this.getAttribute('data-id');
        if (id) {
          setTimeout(() => {
            showDeleteProjectModal(id);
          }, 200);
        }
      });
    });

    restoreButtons.forEach(btn => {
      btn.addEventListener('click', function(e) {
        e.stopPropagation();
        addRipple(this, e);
        const id = this.getAttribute('data-id');
        if (id) {
          setTimeout(() => {
            showRestoreProjectModal(id);
          }, 200);
        }
      });
    });

    // Add row click handlers for highlight
    const tableRows = document.querySelectorAll('#projectsTableBody tr');
    tableRows.forEach(row => {
      row.addEventListener('click', function() {
        tableRows.forEach(r => r.classList.remove('bg-orange-50'));
        this.classList.add('bg-orange-50');
      });
    });
  };

  // Initial attachment of action listeners
  if (typeof window.attachActionListeners === 'function') {
    window.attachActionListeners();
  }

  // Fetch project details and open appropriate modal
  async function fetchProjectDetails(projectId) {
    try {
      const response = await fetch(`/admin/project-management/${projectId}/details`, {
        headers: {
          'X-Requested-With': 'XMLHttpRequest',
          'Accept': 'application/json'
        }
      });

      if (!response.ok) throw new Error('Failed to fetch project details');

      const result = await response.json();

      if (result.success && result.data) {
        const data = result.data;

        // If project is deleted, use previous status to determine modal
        const statusToUse = (data.projectStatus === 'deleted' && data.previousStatus)
          ? data.previousStatus
          : data.projectStatus;

        // Determine which modal to open based on project status
        switch(statusToUse) {
          case 'open':
          case 'bidding_closed':
            showOpenProjectModal(projectId);
            break;
          case 'in_progress':
            showOngoingProjectModal(projectId);
            break;
          case 'completed':
            openCompletedProjectModal(data);
            break;
          case 'halt':
            openHaltedProjectModal(data);
            break;
          case 'terminated':
          case 'cancelled':
            openCancelledProjectModal(data);
            break;
          default:
            openBiddingDetailsModal(data);
        }
      } else {
        showNotification('Failed to load project details', 'error');
      }
    } catch (error) {
      console.error('Error fetching project details:', error);
      showNotification('An error occurred while loading project details', 'error');
    }
  }

  // Open Bidding Details Modal
  function openBiddingDetailsModal(data) {
    const modal = document.getElementById('biddingDetailsModal');
    if (!modal) return;

    // Populate basic info
    setTextContent('biddingProjectTitle', data.title);
    setTextContent('biddingProjectId', data.projectId);
    setTextContent('biddingOwnerName', data.ownerName);
    setTextContent('biddingPropertyType', data.propertyType);
    setTextContent('biddingLotSize', data.lotSize ? `${data.lotSize} sqm` : '—');
    setTextContent('biddingTimeline', data.timelineDisplay || '—');
    setTextContent('biddingBudget', formatBudget(data.budgetMin, data.budgetMax));
    setTextContent('biddingDeadline', formatDate(data.biddingDue));
    setTextContent('biddingDescription', data.description);

    // Show modal
    showModal(modal);
  }

  // Open Ongoing Project Modal
  function openOngoingProjectModal(data) {
    const modal = document.getElementById('ongoingProjectModal');
    if (!modal) return;

    // Populate header
    setTextContent('ongoingProjectTitle', data.title);
    setTextContent('ongoingProjectId', data.projectId);
    setTextContent('ongoingOwnerName', data.ownerName);

    // Populate project details
    setTextContent('ongoingPropertyType', data.propertyType);
    setTextContent('ongoingPropertyAddress', data.propertyAddress);
    setTextContent('ongoingLotSize', data.lotSize ? `${data.lotSize} sqm` : '—');
    setTextContent('ongoingFloorArea', data.floorArea ? `${data.floorArea} sqm` : '—');
    setTextContent('ongoingBudget', formatBudget(data.budgetMin, data.budgetMax));
    setTextContent('ongoingTimeline', data.timelineDisplay || '—');
    setTextContent('ongoingDescription', data.description);

    // Populate contractor details
    setTextContent('ongoingContractorName', data.contractorName);
    setTextContent('ongoingContractorRepName', data.contractorRepName || '—');
    setTextContent('ongoingContractorEmail', data.contractorEmail || '—');
    setTextContent('ongoingContractorPhone', data.contractorPhone || '—');
    setTextContent('ongoingContractorPcab', data.contractorPcab || '—');
    setTextContent('ongoingContractorCategory', data.contractorCategory || '—');
    setTextContent('ongoingContractorPcabExpiry', formatDate(data.contractorPcabExpiry));
    setTextContent('ongoingContractorPermit', data.contractorPermit || '—');
    setTextContent('ongoingContractorCity', data.contractorCity || '—');
    setTextContent('ongoingContractorPermitExpiry', formatDate(data.contractorPermitExpiry));
    setTextContent('ongoingContractorTin', data.contractorTin || '—');

    // Populate milestone timeline
    populateMilestoneTimeline('ongoingMilestoneTimeline', data.milestones);

    // Populate payment summary
    populatePaymentTable('ongoingPaymentTable', data.payments);

    // Load pending extension requests
    if (data.projectId) {
      loadPendingExtensions(data.projectId);
    }

    // Show modal
    showModal(modal);
  }

  // Open Completed Project Modal
  async function openCompletedProjectModal(data) {
    // Store the project ID globally when opening the modal
    window.currentProjectId = data.projectId;

    const modal = document.getElementById('completedProjectModal');
    if (!modal) return;

    try {
      // Fetch HTML-rendered content from backend
      const response = await fetch(`/admin/project-management/${data.projectId}/completed-details`);
      if (!response.ok) throw new Error('Failed to fetch completed project details');

      const result = await response.json();

      if (result.success && result.html) {
        // Inject the rendered HTML into the modal
        const modalContainer = modal.querySelector('.absolute > div');
        if (modalContainer) {
          modalContainer.innerHTML = result.html;
        }

        // Set the project ID on the modal element
        modal.dataset.projectId = data.projectId;

        // Show modal
        showModal(modal);

        // Re-attach action listeners
        attachCompletedModalListeners();
      } else {
        showNotification('Failed to load completed project details', 'error');
      }
    } catch (error) {
      console.error('Error loading completed project:', error);
      showNotification('An error occurred while loading project details', 'error');
    }
  }

  // Attach listeners for completed modal interactions
  function attachCompletedModalListeners() {
    // No additional listeners needed as onclick handlers are in the HTML
  }

  // Helper function to show milestone details
  window.showMilestoneDetails = function(itemId) {
    // Hide the default message
    const container = document.getElementById('completedDetailsContent');
    if (container) {
      const defaultMsg = container.querySelector('.text-gray-500');
      if (defaultMsg) defaultMsg.classList.add('hidden');
    }

    // Hide all detail divs
    const allDetails = document.querySelectorAll('[id^="milestone-detail-"]');
    allDetails.forEach(detail => detail.classList.add('hidden'));

    // Show the selected detail
    const selectedDetail = document.getElementById(`milestone-detail-${itemId}`);
    if (selectedDetail) {
      selectedDetail.classList.remove('hidden');
    }
  };

  // Toggle completed details visibility
  window.toggleCompletedDetails = function() {
    const detailsSection = document.getElementById('completedDetailsSection');
    if (detailsSection) {
      detailsSection.classList.toggle('hidden');
    }
  };

  // Toggle project summary inside completed modal
  window.toggleCompletedProjectSummary = async function(projectId) {
    const section = document.getElementById('completedProjectSummarySection');
    const content = document.getElementById('completedProjectSummaryContent');
    const label = document.getElementById('completedSummaryToggleLabel');
    if (!section) return;

    const isHidden = section.classList.contains('hidden');
    section.classList.toggle('hidden');
    if (label) label.textContent = isHidden ? 'Hide Project Summary' : 'View Project Summary';

    if (isHidden && content && content.dataset.loaded !== 'true') {
      const id = projectId || window.currentProjectId;
      if (!id) return;
      try {
        const res = await fetch(`/admin/project-management/${id}/summary`);
        const result = await res.json();
        if (result.success && result.html) {
          content.innerHTML = result.html;
          content.dataset.loaded = 'true';
        } else {
          content.innerHTML = `<p class="text-sm text-red-500 text-center py-4">${result.message || 'Failed to load summary.'}</p>`;
        }
      } catch (e) {
        content.innerHTML = '<p class="text-sm text-red-500 text-center py-4">Error loading summary.</p>';
      }
    }
  };

  // Toggle project summary inside halted modal
  window.toggleHaltedProjectSummary = async function(projectId) {
    const section = document.getElementById('haltedProjectSummarySection');
    const content = document.getElementById('haltedProjectSummaryContent');
    const label = document.getElementById('haltedSummaryToggleLabel');
    if (!section) return;

    const isHidden = section.classList.contains('hidden');
    section.classList.toggle('hidden');
    if (label) label.textContent = isHidden ? 'Hide Project Summary' : 'View Project Summary';

    if (isHidden && content && content.dataset.loaded !== 'true') {
      if (!projectId) return;
      try {
        const res = await fetch(`/admin/project-management/${projectId}/summary`);
        const result = await res.json();
        if (result.success && result.html) {
          content.innerHTML = result.html;
          content.dataset.loaded = 'true';
        } else {
          content.innerHTML = `<p class="text-sm text-red-500 text-center py-4">${result.message || 'Failed to load summary.'}</p>`;
        }
      } catch (e) {
        content.innerHTML = '<p class="text-sm text-red-500 text-center py-4">Error loading summary.</p>';
      }
    }
  };

  // Show standalone project summary modal (for in_progress / terminated)
  window.showProjectSummaryModal = async function(projectId) {
    const modal = document.getElementById('projectSummaryModal');
    const content = document.getElementById('psmBody');
    if (!modal || !content) return;

    modal.classList.remove('hidden');
    content.innerHTML = '<div class="flex items-center justify-center py-16"><p class="text-sm text-gray-400">Loading summary…</p></div>';

    try {
      const res = await fetch(`/admin/project-management/${projectId}/summary`);
      const result = await res.json();
      if (result.success && result.html) {
        content.innerHTML = result.html;
      } else {
        content.innerHTML = `<p class="text-sm text-red-500 text-center py-8">${result.message || 'Failed to load summary.'}</p>`;
      }
    } catch (e) {
      content.innerHTML = '<p class="text-sm text-red-500 text-center py-8">Error loading summary.</p>';
    }
  };

  window.hideProjectSummaryModal = function() {
    const modal = document.getElementById('projectSummaryModal');
    if (modal) modal.classList.add('hidden');
  };

  // Toggle collapsible sections inside projectSummaryContent partial
  window.psmToggle = function(sectionId, chevronId) {
    const section = document.getElementById(sectionId);
    const chevron = document.getElementById(chevronId);
    if (section) section.classList.toggle('hidden');
    if (chevron) chevron.classList.toggle('rotate-180');
  };

  // Open Halted Project Modal
  // Replaced with AJAX-based server-side rendering
  async function openHaltedProjectModal(data) {
    // Call the new showHaltedProjectModal function
    if (data && data.projectId) {
      await showHaltedProjectModal(data.projectId);
    }
  }

  // Open Cancelled Project Modal
  // Replaced with AJAX-based server-side rendering
  async function openCancelledProjectModal(data) {
    // Call the new showCancelledProjectModal function
    if (data && data.projectId) {
      await showCancelledProjectModal(data.projectId);
    }
  }

  // Helper functions for formatting
  function setTextContent(id, value) {
    const element = document.getElementById(id);
    if (element) {
      element.textContent = value || '—';
    }
  }

  function formatDate(dateString) {
    if (!dateString) return '—';
    const date = new Date(dateString);
    return date.toLocaleDateString('en-US', { year: 'numeric', month: 'long', day: 'numeric' });
  }

  function formatTimeline(min, max) {
    if (!min && !max) return '—';
    return `${min || '?'} - ${max || '?'}`;
  }

  function formatBudget(min, max) {
    if (!min && !max) return '—';
    const minFormatted = min ? `₱${parseFloat(min).toLocaleString()}` : '?';
    const maxFormatted = max ? `₱${parseFloat(max).toLocaleString()}` : '?';
    return `${minFormatted} - ${maxFormatted}`;
  }

  function getStatusClass(status) {
    const statusClasses = {
      'completed': 'bg-green-500',
      'in_progress': 'bg-blue-500',
      'not_started': 'bg-gray-400',
      'pending': 'bg-gray-400',
      'cancelled': 'bg-red-500',
      'rejected': 'bg-red-500',
      'delayed': 'bg-orange-500',
      'verified': 'bg-green-100 text-green-800',
      'unverified': 'bg-yellow-100 text-yellow-800',
      'approved': 'bg-green-100 text-green-800',
      'submitted': 'bg-blue-100 text-blue-800'
    };
    return statusClasses[status] || 'bg-gray-400';
  }

  function showModal(modal) {
    if (!modal) return;
    modal.classList.remove('hidden');
    const modalContent = modal.querySelector('.absolute > div, .modal-content');
    if (modalContent) {
      setTimeout(() => {
        modalContent.classList.remove('scale-95', 'opacity-0');
        modalContent.classList.add('scale-100', 'opacity-100');
      }, 10);
    }
    document.body.style.overflow = 'hidden';
  }

  function hideModal(modal) {
    if (!modal) return;
    const modalContent = modal.querySelector('.absolute > div, .modal-content');
    if (modalContent) {
      modalContent.classList.remove('scale-100', 'opacity-100');
      modalContent.classList.add('scale-95', 'opacity-0');
    }
    setTimeout(() => {
      modal.classList.add('hidden');
      document.body.style.overflow = 'auto';
    }, 300);
  }

  // Global modal close functions
  window.hideBiddingModal = function() {
    const modal = document.getElementById('biddingDetailsModal');
    hideModal(modal);
  };

  window.hideBiddingDetailsModal = function() {
    const modal = document.getElementById('biddingDetailsModal');
    hideModal(modal);
  };

  window.hideOngoingProjectModal = function() {
    const modal = document.getElementById('ongoingProjectModal');
    hideModal(modal);
  };

  window.hideCompletedProjectModal = function() {
    const modal = document.getElementById('completedProjectModal');
    hideModal(modal);
  };

  window.hideHaltedProjectModal = function() {
    const modal = document.getElementById('haltedProjectModal');
    hideModal(modal);
  };

  window.hideCancelledProjectModal = function() {
    const modal = document.getElementById('cancelledProjectModal');
    hideModal(modal);
  };

  function showNotification(message, type = 'success') {
    const notification = document.createElement('div');
    notification.className = `fixed top-24 right-8 z-[60] px-6 py-4 rounded-lg shadow-2xl transform transition-all duration-500 translate-x-full ${
      type === 'success' ? 'bg-green-500' : 'bg-red-500'
    } text-white font-semibold flex items-center gap-3`;
    notification.innerHTML = `
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

  function addRipple(button, event) {
    const ripple = document.createElement('span');
    const rect = button.getBoundingClientRect();
    const size = Math.max(rect.width, rect.height);
    const x = event.clientX - rect.left - size / 2;
    const y = event.clientY - rect.top - size / 2;

    ripple.style.width = ripple.style.height = size + 'px';
    ripple.style.left = x + 'px';
    ripple.style.top = y + 'px';
    ripple.classList.add('ripple');

    button.style.position = 'relative';
    button.style.overflow = 'hidden';
    button.appendChild(ripple);

    setTimeout(() => {
      ripple.remove();
    }, 600);
  }

  function showNotification(message, type = 'success') {
    const notification = document.createElement('div');
    notification.className = `fixed top-4 right-4 px-6 py-3 rounded-lg shadow-lg text-white font-medium z-50 transition-all duration-300 transform translate-x-full ${
      type === 'success' ? 'bg-green-500' :
      type === 'error' ? 'bg-red-500' :
      type === 'warning' ? 'bg-yellow-500' :
      'bg-blue-500'
    }`;
    notification.textContent = message;

    document.body.appendChild(notification);

    setTimeout(() => {
      notification.style.transform = 'translateX(0)';
    }, 10);

    setTimeout(() => {
      notification.style.transform = 'translateX(150%)';
      setTimeout(() => {
        notification.remove();
      }, 300);
    }, 3000);
  }

  // Show completion details modal - triggered from completed project modal
  window.showCompletionDetailsModal = async function(projectId) {
    try {
      const response = await fetch(`/admin/project-management/${projectId}/completion-details`, {
        headers: {
          'X-Requested-With': 'XMLHttpRequest',
          'Accept': 'application/json'
        }
      });

      if (!response.ok) throw new Error('Failed to fetch completion details');

      const result = await response.json();

      if (result.success && result.html) {
        // Parse the HTML to extract just the modal content
        const parser = new DOMParser();
        const doc = parser.parseFromString(result.html, 'text/html');
        const newModal = doc.getElementById('completionDetailsModal');

        if (newModal) {
          // Get the existing modal container
          const existingModal = document.getElementById('completionDetailsModal');

          if (existingModal && existingModal.parentNode) {
            // Replace the entire modal element
            existingModal.parentNode.replaceChild(newModal, existingModal);

            // Show the modal by removing the 'hidden' class
            newModal.classList.remove('hidden');
          }
        }
      } else {
        showNotification('Failed to load completion details', 'error');
      }
    } catch (error) {
      console.error('Error loading completion details:', error);
      showNotification('An error occurred while loading completion details', 'error');
    }
  };

  // Hide completion details modal
  window.hideCompletionDetailsModal = function() {
    const modal = document.getElementById('completionDetailsModal');
    if (modal) {
      modal.classList.add('hidden');
    }
  };

  // Show ongoing project modal with server-side rendered content
  window.showOngoingProjectModal = async function(projectId) {
    // Store the project ID globally when opening the modal
    window.currentProjectId = projectId;

    try {
      const response = await fetch(`/admin/project-management/${projectId}/ongoing-details`);
      const result = await response.json();

      if (result.success && result.html) {
        // Parse the HTML string into a DOM element
        const parser = new DOMParser();
        const doc = parser.parseFromString(result.html, 'text/html');
        const newModalContent = doc.getElementById('ongoingProjectModal');

        if (newModalContent) {
          // Find the existing modal in the page
          const existingModal = document.getElementById('ongoingProjectModal');

          if (existingModal) {
            // Replace only the inner HTML to preserve scroll and avoid glitching
            existingModal.innerHTML = newModalContent.innerHTML;
            // Set the project ID on the modal element
            existingModal.dataset.projectId = projectId;

            // Show the modal by removing the 'hidden' class
            existingModal.classList.remove('hidden');

            // Re-attach action listeners for milestone interactivity
            if (typeof window.attachActionListeners === 'function') {
              window.attachActionListeners();
            }
            
            // Load pending extension requests
            loadPendingExtensions(projectId);
          }
        }
      } else {
        showNotification('Failed to load ongoing project details', 'error');
      }
    } catch (error) {
      console.error('Error loading ongoing project details:', error);
      showNotification('An error occurred while loading ongoing project details', 'error');
    }
  };

  // Hide ongoing project modal
  window.hideOngoingProjectModal = function() {
    const modal = document.getElementById('ongoingProjectModal');
    if (modal) {
      modal.classList.add('hidden');
    }
  };

  // Show open project modal with server-side rendered content
  window.showOpenProjectModal = async function(projectId) {
    window.currentOpenProjectId = projectId;
    try {
      const response = await fetch(`/admin/project-management/${projectId}/open-details`);
      const result = await response.json();

      if (result.success && result.html) {
        // Parse the HTML string into a DOM element
        const parser = new DOMParser();
        const doc = parser.parseFromString(result.html, 'text/html');
        const newModalContent = doc.getElementById('biddingDetailsModal');

        if (newModalContent) {
          // Find the existing modal in the page
          const existingModal = document.getElementById('biddingDetailsModal');

          if (existingModal) {
            // Replace only the inner HTML to preserve scroll and avoid glitching
            existingModal.innerHTML = newModalContent.innerHTML;

            // Show the modal by removing the 'hidden' class
            existingModal.classList.remove('hidden');

            // Re-attach action listeners
            if (typeof window.attachActionListeners === 'function') {
              window.attachActionListeners();
            }
          }
        }
      } else {
        showNotification('Failed to load bidding project details', 'error');
      }
    } catch (error) {
      console.error('Error loading bidding project details:', error);
      showNotification('An error occurred while loading bidding project details', 'error');
    }
  };

  // Hide bidding modal
  window.hideBiddingModal = function() {
    const modal = document.getElementById('biddingDetailsModal');
    if (modal) {
      modal.classList.add('hidden');
    }
  };

  // Show bid status modal with server-side rendered content
  window.showBidStatusModal = async function(bidId) {
    try {
      const response = await fetch(`/admin/project-management/bids/${bidId}/details`);
      const result = await response.json();

      if (result.success && result.html) {
        // Parse the HTML string into a DOM element
        const parser = new DOMParser();
        const doc = parser.parseFromString(result.html, 'text/html');
        const newModalContent = doc.getElementById('bidStatusModal');

        if (newModalContent) {
          // Find the existing modal in the page
          const existingModal = document.getElementById('bidStatusModal');

          if (existingModal) {
            // Replace only the inner HTML to preserve scroll and avoid glitching
            existingModal.innerHTML = newModalContent.innerHTML;

            // Show the modal by removing the 'hidden' class
            existingModal.classList.remove('hidden');
          }
        }
      } else {
        showNotification('Failed to load bid details', 'error');
      }
    } catch (error) {
      console.error('Error loading bid details:', error);
      showNotification('An error occurred while loading bid details', 'error');
    }
  };

  // Hide bid status modal
  window.hideBidStatusModal = function() {
    const modal = document.getElementById('bidStatusModal');
    if (modal) {
      modal.classList.add('hidden');
    }
  };

  // Show accept bid modal with server-side rendered content
  window.showAcceptBidModal = async function(bidId) {
    try {
      const response = await fetch(`/admin/project-management/bids/${bidId}/accept-summary`);
      const result = await response.json();

      if (result.success && result.html) {
        // Parse the HTML string into a DOM element
        const parser = new DOMParser();
        const doc = parser.parseFromString(result.html, 'text/html');
        const newModalContent = doc.getElementById('acceptBidModal');

        if (newModalContent) {
          // Find the existing modal in the page
          const existingModal = document.getElementById('acceptBidModal');

          if (existingModal) {
            // Replace only the inner HTML to preserve scroll and avoid glitching
            existingModal.innerHTML = newModalContent.innerHTML;

            // Show the modal by removing the 'hidden' class
            existingModal.classList.remove('hidden');
          }
        }
      } else {
        showNotification('Failed to load bid summary', 'error');
      }
    } catch (error) {
      console.error('Error loading bid summary:', error);
      showNotification('An error occurred while loading bid summary', 'error');
    }
  };

  // Hide accept bid modal
  window.hideAcceptBidModal = function() {
    const modal = document.getElementById('acceptBidModal');
    if (modal) {
      modal.classList.add('hidden');
    }
  };

  // Confirm accept bid
  window.confirmAcceptBid = async function(bidId) {
    try {
      // Get CSRF token from meta tag or Laravel's global csrf token
      const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content')
        || document.querySelector('input[name="_token"]')?.value
        || '';

      const response = await fetch(`/admin/project-management/bids/${bidId}/accept`, {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'X-CSRF-TOKEN': csrfToken
        }
      });

      const result = await response.json();

      if (result.success) {
        showNotification('Bid accepted successfully', 'success');
        hideAcceptBidModal();
        hideBiddingModal();
        // Refresh the table to show updated status
        if (typeof window.refreshProjectsTable === 'function') {
          window.refreshProjectsTable();
        }
      } else {
        showNotification(result.message || 'Failed to accept bid', 'error');
      }
    } catch (error) {
      console.error('Error accepting bid:', error);
      showNotification('An error occurred while accepting bid', 'error');
    }
  };

  // Show reject bid modal with server-side rendered content
  window.showRejectBidModal = async function(bidId) {
    try {
      const response = await fetch(`/admin/project-management/bids/${bidId}/reject-summary`);
      const result = await response.json();

      if (result.success && result.html) {
        // Parse the HTML string into a DOM element
        const parser = new DOMParser();
        const doc = parser.parseFromString(result.html, 'text/html');
        const newModalContent = doc.getElementById('rejectBidModal');

        if (newModalContent) {
          // Find the existing modal in the page
          const existingModal = document.getElementById('rejectBidModal');

          if (existingModal) {
            // Replace only the inner HTML to preserve scroll and avoid glitching
            existingModal.innerHTML = newModalContent.innerHTML;

            // Show the modal by removing the 'hidden' class
            existingModal.classList.remove('hidden');
          }
        }
      } else {
        showNotification('Failed to load bid summary', 'error');
      }
    } catch (error) {
      console.error('Error loading bid summary:', error);
      showNotification('An error occurred while loading bid summary', 'error');
    }
  };

  // Hide reject bid modal
  window.hideRejectBidModal = function() {
    const modal = document.getElementById('rejectBidModal');
    if (modal) {
      modal.classList.add('hidden');
    }
  };

  // Confirm reject bid
  window.confirmRejectBid = async function(bidId) {
    try {
      // Get the rejection reason from the textarea
      const reason = document.getElementById('rejectReason')?.value || '';

      // Get CSRF token
      const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content')
        || document.querySelector('input[name="_token"]')?.value
        || '';

      const response = await fetch(`/admin/project-management/bids/${bidId}/reject`, {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'X-CSRF-TOKEN': csrfToken
        },
        body: JSON.stringify({ reason: reason })
      });

      const result = await response.json();

      if (result.success) {
        showNotification('Bid rejected successfully', 'success');
        hideRejectBidModal();
        hideBiddingModal();
        // Refresh the table to show updated status
        if (typeof window.refreshProjectsTable === 'function') {
          window.refreshProjectsTable();
        }
      } else {
        showNotification(result.message || 'Failed to reject bid', 'error');
      }
    } catch (error) {
      console.error('Error rejecting bid:', error);
      showNotification('An error occurred while rejecting bid', 'error');
    }
  };

  // Show delete project modal with server-side rendered content
  window.showDeleteProjectModal = async function(projectId) {
    try {
      if (!projectId) {
        showNotification('Invalid project ID', 'error');
        return;
      }

      const response = await fetch(`/admin/project-management/${projectId}/delete-summary`);
      const result = await response.json();

      if (result.success) {
        const modalContainer = document.getElementById('deleteProjectModalContainer');
        if (modalContainer) {
          modalContainer.innerHTML = result.html;
          const existingModal = document.getElementById('deleteProjectModal');
          if (existingModal) {
            existingModal.classList.remove('hidden');
          }
        }
      } else {
        showNotification('Failed to load project summary', 'error');
      }
    } catch (error) {
      console.error('Error loading project summary:', error);
      showNotification('An error occurred while loading project summary', 'error');
    }
  };

  // Hide delete project modal
  window.hideDeleteProjectModal = function() {
    const modal = document.getElementById('deleteProjectModal');
    if (modal) {
      modal.classList.add('hidden');
    }
  };

  // Confirm delete project
  window.confirmDeleteProject = async function() {
    try {
      // Clear previous error
      const errorElement = document.getElementById('error-delete-reason');
      if (errorElement) {
        errorElement.classList.add('hidden');
        errorElement.textContent = '';
      }

      // Get project ID from modal
      const modal = document.getElementById('deleteProjectModal');
      const projectId = modal?.dataset?.projectId;

      if (!projectId) {
        showNotification('Project ID not found', 'error');
        return;
      }

      // Get the deletion reason from the textarea
      const reason = document.getElementById('deleteReason')?.value || '';

      // Get CSRF token
      const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content')
        || document.querySelector('input[name="_token"]')?.value
        || '';

      const response = await fetch(`/admin/project-management/${projectId}`, {
        method: 'DELETE',
        headers: {
          'Content-Type': 'application/json',
          'X-CSRF-TOKEN': csrfToken,
          'Accept': 'application/json',
          'X-Requested-With': 'XMLHttpRequest'
        },
        body: JSON.stringify({ reason: reason })
      });

      const result = await response.json();

      if (result.success) {
        showNotification('Project deleted successfully', 'success');
        hideDeleteProjectModal();
        // Refresh the table to show updated status
        if (typeof window.refreshProjectsTable === 'function') {
          window.refreshProjectsTable();
        }
      } else {
        // Handle validation errors
        if (response.status === 422 && result.errors) {
          if (result.errors.reason && errorElement) {
            errorElement.textContent = result.errors.reason[0];
            errorElement.classList.remove('hidden');
          }
          // No toast notification for validation errors - error shown below field
        } else {
          showNotification(result.message || 'Failed to delete project', 'error');
        }
      }
    } catch (error) {
      console.error('Error deleting project:', error);

      // If it's a network error, show in toast
      if (error.name === 'TypeError' && error.message === 'Failed to fetch') {
        showNotification('Network error - please check your connection and try again', 'error');
      } else {
        showNotification('An error occurred while deleting project', 'error');
      }
    }
  };

  // Show restore project modal with server-side rendered content
  window.showRestoreProjectModal = async function(projectId) {
    try {
      if (!projectId) {
        showNotification('Invalid project ID', 'error');
        return;
      }

      const response = await fetch(`/admin/project-management/${projectId}/restore-summary`);
      const result = await response.json();

      if (result.success) {
        const modalContainer = document.getElementById('restoreProjectModalContainer');
        if (modalContainer) {
          modalContainer.innerHTML = result.html;
          const existingModal = document.getElementById('restoreProjectModal');
          if (existingModal) {
            existingModal.classList.remove('hidden');
          }
        }
      } else {
        showNotification('Failed to load project summary', 'error');
      }
    } catch (error) {
      console.error('Error loading project summary:', error);
      showNotification('An error occurred while loading project summary', 'error');
    }
  };

  // Hide restore project modal
  window.hideRestoreProjectModal = function() {
    const modal = document.getElementById('restoreProjectModal');
    if (modal) {
      modal.classList.add('hidden');
    }
  };

  // Confirm restore project
  window.confirmRestoreProject = async function() {
    try {
      // Get project ID from modal
      const modal = document.getElementById('restoreProjectModal');
      const projectId = modal?.dataset?.projectId;

      if (!projectId) {
        showNotification('Project ID not found', 'error');
        return;
      }

      // Get CSRF token
      const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content')
        || document.querySelector('input[name="_token"]')?.value
        || '';

      const response = await fetch(`/admin/project-management/${projectId}/restore`, {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'X-CSRF-TOKEN': csrfToken,
          'Accept': 'application/json',
          'X-Requested-With': 'XMLHttpRequest'
        }
      });

      const result = await response.json();

      if (result.success) {
        showNotification('Project restored successfully', 'success');
        hideRestoreProjectModal();
        // Refresh the table to show updated status
        if (typeof window.refreshProjectsTable === 'function') {
          window.refreshProjectsTable();
        }
      } else {
        showNotification(result.message || 'Failed to restore project', 'error');
      }
    } catch (error) {
      console.error('Error restoring project:', error);

      // If it's a network error, show in toast
      if (error.name === 'TypeError' && error.message === 'Failed to fetch') {
        showNotification('Network error - please check your connection and try again', 'error');
      } else {
        showNotification('An error occurred while restoring project', 'error');
      }
    }
  };

  // Show ongoing milestone details (toggle visibility)
  window.showOngoingMilestoneDetails = function(itemId) {
    // Store the selected item ID globally
    window.selectedMilestoneItemId = itemId;

    // Hide the default message
    const container = document.getElementById('ongoingDetailsContent');
    if (container) {
      const defaultMsg = container.querySelector('.text-gray-500');
      if (defaultMsg) defaultMsg.classList.add('hidden');
    }

    // Hide all milestone detail divs
    const allDetails = document.querySelectorAll('[id^="ongoing-milestone-detail-"]');
    allDetails.forEach(detail => detail.classList.add('hidden'));

    // Show the selected milestone detail
    const selectedDetail = document.getElementById(`ongoing-milestone-detail-${itemId}`);
    if (selectedDetail) {
      selectedDetail.classList.remove('hidden');
    }

    // Show the edit button
    const editBtn = document.getElementById('editOngoingMilestoneBtn');
    if (editBtn) {
      editBtn.classList.remove('hidden');
    }
  };

  // Show cancelled project modal with AJAX server-side rendering
  window.showCancelledProjectModal = async function(projectId) {
    try {
      const response = await fetch(`/admin/project-management/${projectId}/terminated-details`);
      const result = await response.json();

      if (result.success && result.html) {
        // Parse the HTML string into a DOM element
        const parser = new DOMParser();
        const doc = parser.parseFromString(result.html, 'text/html');
        const newModalContent = doc.getElementById('cancelledProjectModal');

        if (newModalContent) {
          // Find the existing modal in the page
          const existingModal = document.getElementById('cancelledProjectModal');

          if (existingModal) {
            // Replace only the inner HTML to preserve scroll and avoid glitching
            existingModal.innerHTML = newModalContent.innerHTML;

            // Show the modal by removing the 'hidden' class
            existingModal.classList.remove('hidden');
          }
        }
      } else {
        showNotification('Failed to load terminated project details', 'error');
      }
    } catch (error) {
      console.error('Error loading terminated project details:', error);
      showNotification('An error occurred while loading project details', 'error');
    }
  };

  // Hide cancelled project modal
  window.hideCancelledProjectModal = function() {
    const modal = document.getElementById('cancelledProjectModal');
    if (modal) {
      modal.classList.add('hidden');
    }
  };

  // Show terminated milestone details (interactive timeline)
  window.showTerminatedMilestoneDetail = function(itemId) {
    // Hide the default message if exists
    const container = document.getElementById('terminatedDetailsContent');
    if (container) {
      const defaultMsg = container.querySelector('.text-gray-500');
      if (defaultMsg) defaultMsg.classList.add('hidden');
    }

    // Hide all milestone detail divs
    const allDetails = document.querySelectorAll('[id^="term-detail-"]');
    allDetails.forEach(detail => detail.classList.add('hidden'));

    // Show the selected milestone detail
    const selectedDetail = document.getElementById(`term-detail-${itemId}`);
    if (selectedDetail) {
      selectedDetail.classList.remove('hidden');
    }
  };

  // Alias for consistency with Blade template
  window.showTerminatedMilestoneDetails = window.showTerminatedMilestoneDetail;

  // Show halted project modal with AJAX server-side rendering
  window.showHaltedProjectModal = async function(projectId) {
    try {
      const response = await fetch(`/admin/project-management/${projectId}/halted-details`);
      const result = await response.json();

      if (result.success && result.html) {
        // Parse the HTML string into a DOM element
        const parser = new DOMParser();
        const doc = parser.parseFromString(result.html, 'text/html');
        const newModalContent = doc.getElementById('haltedProjectModal');

        if (newModalContent) {
          // Find the existing modal in the page
          const existingModal = document.getElementById('haltedProjectModal');

          if (existingModal) {
            // Replace only the inner HTML to preserve scroll and avoid glitching
            existingModal.innerHTML = newModalContent.innerHTML;

            // Show the modal by removing the 'hidden' class
            existingModal.classList.remove('hidden');
            
            // Load pending extension requests
            loadPendingExtensions(projectId);
          }
        }
      } else {
        showNotification('Failed to load halted project details', 'error');
      }
    } catch (error) {
      console.error('Error loading halted project details:', error);
      showNotification('An error occurred while loading project details', 'error');
    }
  };

  // Hide halted project modal
  window.hideHaltedProjectModal = function() {
    const modal = document.getElementById('haltedProjectModal');
    if (modal) {
      modal.classList.add('hidden');
    }
  };

  // Show halted milestone details (interactive timeline)
  // Show halt details modal (administrative information)
  window.showHaltDetailsModal = async function(projectId) {
    try {
      const response = await fetch(`/admin/project-management/${projectId}/halt-details`);
      const result = await response.json();

      if (result.success && result.html) {
        // Parse the HTML string into a DOM element
        const parser = new DOMParser();
        const doc = parser.parseFromString(result.html, 'text/html');
        const newModalContent = doc.getElementById('haltDetailsModal');

        if (newModalContent) {
          // Find the existing modal in the page
          const existingModal = document.getElementById('haltDetailsModal');

          if (existingModal) {
            // Replace only the inner HTML to preserve scroll and avoid glitching
            existingModal.innerHTML = newModalContent.innerHTML;

            // Store the project ID on the modal for later use
            existingModal.setAttribute('data-project-id', projectId);

            // Show the modal by removing the 'hidden' class
            existingModal.classList.remove('hidden');
          }
        }
      } else {
        showNotification('Failed to load halt details', 'error');
      }
    } catch (error) {
      console.error('Error loading halt details:', error);
      showNotification('An error occurred while loading halt details', 'error');
    }
  };

  // Hide halt details modal
  window.hideHaltDetailsModal = function() {
    const modal = document.getElementById('haltDetailsModal');
    if (modal) {
      modal.classList.add('hidden');
    }
  };

  // Show cancel halt confirmation modal
  window.showCancelHaltConfirm = function() {
    const modal = document.getElementById('cancelHaltConfirmModal');
    if (modal) {
      modal.classList.remove('hidden');
    }
  };

  // Hide cancel halt confirmation modal
  window.hideCancelHaltConfirm = function() {
    const modal = document.getElementById('cancelHaltConfirmModal');
    if (modal) {
      modal.classList.add('hidden');
    }
  };

  // Show resume halt confirmation modal
  window.showResumeHaltConfirm = function() {
    const modal = document.getElementById('resumeHaltConfirmModal');
    if (modal) {
      modal.classList.remove('hidden');
    }
  };

  // Hide resume halt confirmation modal
  window.hideResumeHaltConfirm = function() {
    const modal = document.getElementById('resumeHaltConfirmModal');
    if (modal) {
      modal.classList.add('hidden');
    }
  };

  // Clear error when user types in the textarea
  const remarksInput = document.getElementById('cancelHaltRemarks');
  const remarksError = document.getElementById('cancelHaltRemarksError');

  if (remarksInput && remarksError) {
    remarksInput.addEventListener('input', function() {
      remarksError.textContent = '';
      remarksError.classList.add('hidden');
      remarksInput.classList.remove('border-red-500');
      remarksInput.classList.add('border-gray-300');
    });
  }

  // Confirm cancel halt - change project status to terminated
  window.confirmCancelHalt = async function() {
    const haltDetailsModal = document.getElementById('haltDetailsModal');
    const projectIdMatch = haltDetailsModal?.getAttribute('data-project-id');
    const remarksInput = document.getElementById('cancelHaltRemarks');
    const remarksError = document.getElementById('cancelHaltRemarksError');
    const remarks = remarksInput?.value.trim();

    // Clear previous error state first
    if (remarksError) {
      remarksError.textContent = '';
      remarksError.classList.add('hidden');
    }
    if (remarksInput) {
      remarksInput.classList.remove('border-red-500');
      remarksInput.classList.add('border-gray-300');
    }

    if (!projectIdMatch) {
      showNotification('Project ID not found', 'error');
      return;
    }

    // Validate remarks
    if (!remarks) {
      if (remarksError) {
        remarksError.textContent = 'A reason for termination is required.';
        remarksError.classList.remove('hidden');
      }
      if (remarksInput) {
        remarksInput.classList.remove('border-gray-300');
        remarksInput.classList.add('border-red-500');
        remarksInput.focus();
      }
      return;
    }

    // Validate 10-character minimum
    if (remarks.length < 10) {
      if (remarksError) {
        remarksError.textContent = 'The reason must be at least 10 characters.';
        remarksError.classList.remove('hidden');
      }
      if (remarksInput) {
        remarksInput.classList.remove('border-gray-300');
        remarksInput.classList.add('border-red-500');
        remarksInput.focus();
      }
      return;
    }

    try {
      const response = await fetch(`/admin/project-management/${projectIdMatch}/cancel-halt`, {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        },
        body: JSON.stringify({
          remarks: remarks
        })
      });

      const result = await response.json();

      if (result.success) {
        showNotification('Project terminated successfully', 'success');
        // Clear the remarks input and error
        if (remarksInput) remarksInput.value = '';
        if (remarksError) {
          remarksError.textContent = '';
          remarksError.classList.add('hidden');
        }
        hideCancelHaltConfirm();
        hideHaltDetailsModal();
        hideHaltedProjectModal();
        // Refresh the projects table
        if (typeof window.refreshProjectsTable === 'function') {
          window.refreshProjectsTable();
        }
      } else {
        // Show server validation errors below input
        if (result.errors && result.errors.remarks) {
          if (remarksError) {
            remarksError.textContent = result.errors.remarks[0];
            remarksError.classList.remove('hidden');
          }
          if (remarksInput) {
            remarksInput.classList.remove('border-gray-300');
            remarksInput.classList.add('border-red-500');
          }
        } else {
          showNotification(result.message || 'Failed to terminate project', 'error');
        }
      }
    } catch (error) {
      console.error('Error terminating project:', error);
      showNotification('An error occurred while terminating the project', 'error');
    }
  };

  // Confirm resume halt - change project status back to ongoing
  window.confirmResumeHalt = async function() {
    const haltDetailsModal = document.getElementById('haltDetailsModal');
    const projectIdMatch = haltDetailsModal?.getAttribute('data-project-id');

    if (!projectIdMatch) {
      showNotification('Project ID not found', 'error');
      return;
    }

    try {
      const response = await fetch(`/admin/project-management/${projectIdMatch}/resume-halt`, {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        }
      });

      const result = await response.json();

      if (result.success) {
        showNotification('Project resumed successfully', 'success');
        hideResumeHaltConfirm();
        hideHaltDetailsModal();
        hideHaltedProjectModal();
        // Refresh the projects table
        if (typeof window.refreshProjectsTable === 'function') {
          window.refreshProjectsTable();
        }
      } else {
        showNotification(result.message || 'Failed to resume project', 'error');
      }
    } catch (error) {
      console.error('Error resuming project:', error);
      showNotification('An error occurred while resuming the project', 'error');
    }
  };

  // Show Edit Project Modal (AJAX Load)
  window.showEditProjectModal = async function(projectId) {
    const modal = document.getElementById('editProjectModal');
    if (!modal) return;

    try {
      const response = await fetch(`/admin/project-management/${projectId}/edit`, {
        headers: {
          'X-Requested-With': 'XMLHttpRequest'
        }
      });

      const result = await response.json();

      if (result.success && result.html) {
        // Parse and inject HTML
        const parser = new DOMParser();
        const doc = parser.parseFromString(result.html, 'text/html');
        const newContent = doc.body.firstChild;

        const existingContent = modal.querySelector('.bg-white');
        if (existingContent) {
          existingContent.replaceWith(newContent);
        } else {
          const wrapper = modal.querySelector('.flex.items-center.justify-center');
          if (wrapper) {
            wrapper.innerHTML = '';
            wrapper.appendChild(newContent);
          }
        }

        // Attach contractor preview handler
        attachContractorPreviewHandler();

        // Initialize PSGC location dropdowns
        await initializeEditLocationDropdowns();

        // Show modal
        modal.classList.remove('hidden');
        
        // Prevent scroll reset by ensuring modal content doesn't trigger reflows
        const modalContent = modal.querySelector('.overflow-y-auto');
        if (modalContent) {
          modalContent.scrollTop = 0; // Start at top
        }
      } else {
        showNotification('Failed to load project details', 'error');
      }
    } catch (error) {
      console.error('Error loading edit project modal:', error);
      showNotification('An error occurred while loading the modal', 'error');
    }
  };

  // Hide Edit Project Modal
  window.hideEditProjectModal = function() {
    const modal = document.getElementById('editProjectModal');
    if (modal) {
      modal.classList.add('hidden');
    }
  };

  // Attach contractor preview handler
  function attachContractorPreviewHandler() {
    const contractorSelect = document.getElementById('editContractorSelect');
    const preview = document.getElementById('editContractorPreview');
    const bidInfo = document.getElementById('editBidInfo');

    if (!contractorSelect || !preview) return;

    contractorSelect.addEventListener('change', function() {
      const selectedOption = this.options[this.selectedIndex];

      if (this.value && this.value !== '') {
        // Show preview with selected contractor details
        preview.classList.remove('hidden');

        document.getElementById('previewCompany').textContent = selectedOption.getAttribute('data-company') || '—';
        document.getElementById('previewType').textContent = selectedOption.getAttribute('data-type') || '—';
        document.getElementById('previewPhone').textContent = selectedOption.getAttribute('data-phone') || '—';
        document.getElementById('previewEmail').textContent = selectedOption.getAttribute('data-email') || '—';
        document.getElementById('previewAddress').textContent = selectedOption.getAttribute('data-address') || '—';
        document.getElementById('previewPCAB').textContent = selectedOption.getAttribute('data-pcab') || '—';
        document.getElementById('previewPCABCategory').textContent = selectedOption.getAttribute('data-pcab-category') || '—';
        document.getElementById('previewPermit').textContent = selectedOption.getAttribute('data-permit') || '—';
        document.getElementById('previewPermitCity').textContent = selectedOption.getAttribute('data-permit-city') || '—';
        document.getElementById('previewTIN').textContent = selectedOption.getAttribute('data-tin') || '—';
        document.getElementById('previewExperience').textContent = selectedOption.getAttribute('data-experience')
          ? `${selectedOption.getAttribute('data-experience')} years`
          : '—';
        document.getElementById('previewBid').textContent = selectedOption.getAttribute('data-bid')
          ? `₱${parseFloat(selectedOption.getAttribute('data-bid')).toLocaleString('en-PH', {minimumFractionDigits: 2, maximumFractionDigits: 2})}`
          : '—';

        // Show and populate bid information
        if (bidInfo) {
          bidInfo.classList.remove('hidden');

          // Proposed cost
          const cost = selectedOption.getAttribute('data-bid');
          document.getElementById('bidCost').textContent = cost
            ? `₱${parseFloat(cost).toLocaleString('en-PH', {minimumFractionDigits: 2, maximumFractionDigits: 2})}`
            : '—';

          // Estimated timeline
          const timeline = selectedOption.getAttribute('data-timeline');
          document.getElementById('bidTimeline').textContent = timeline ? `${timeline} months` : '—';

          // Contractor notes
          const notes = selectedOption.getAttribute('data-notes');
          document.getElementById('bidNotes').textContent = notes || 'No notes provided';

          // Bid files
          const filesData = selectedOption.getAttribute('data-files');
          const bidFilesList = document.getElementById('bidFilesList');
          const bidFilesContainer = document.getElementById('bidFilesContainer');

          console.log('Bid files data:', filesData); // Debug log

          if (filesData) {
            try {
              const files = JSON.parse(filesData);
              console.log('Parsed files:', files); // Debug log

              if (files && files.length > 0) {
                bidFilesContainer.style.display = 'block';
                bidFilesList.innerHTML = '';

                files.forEach(file => {
                  const fileDiv = document.createElement('div');
                  fileDiv.className = 'flex items-center justify-between p-3 bg-white rounded-lg border border-gray-200';
                  fileDiv.innerHTML = `
                    <div class="flex items-center gap-3">
                      <svg class="w-5 h-5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/>
                      </svg>
                      <div>
                        <span class="text-sm text-gray-700 font-medium">${file.file_name || 'File'}</span>
                        ${file.description ? `<p class="text-xs text-gray-500">${file.description}</p>` : ''}
                      </div>
                    </div>
                    <a href="/storage/${file.file_path}" target="_blank" class="text-green-600 hover:text-green-700 text-sm font-medium">
                      View
                    </a>
                  `;
                  bidFilesList.appendChild(fileDiv);
                });
              } else {
                console.log('No files in array');
                bidFilesContainer.style.display = 'none';
              }
            } catch (e) {
              console.error('Error parsing bid files:', e);
              bidFilesContainer.style.display = 'none';
            }
          } else {
            console.log('No files data attribute');
            bidFilesContainer.style.display = 'none';
          }
        }
      } else {
        // Hide preview and bid info
        preview.classList.add('hidden');
        if (bidInfo) {
          bidInfo.classList.add('hidden');
        }
      }
    });
  }

  // Show Edit Project Confirmation Modal (placeholder - implement as needed)
  window.showEditProjectConfirmModal = async function() {
    // Clear previous errors
    document.querySelectorAll('[id^="error-"]').forEach(el => {
      el.classList.add('hidden');
      el.textContent = '';
    });

    // Get project ID
    const modalContent = document.querySelector('#editProjectModal .bg-white');
    const projectId = modalContent?.dataset?.projectId;

    if (!projectId) {
      showNotification('Project ID not found', 'error');
      return;
    }

    // Collect form data
    const projectData = {
      project_title: document.getElementById('editProjectTitle')?.value,
      project_description: document.getElementById('editProjectDescription')?.value,
      property_type: document.getElementById('editPropertyType')?.value,
      lot_size: document.getElementById('editLotSize')?.value,
      floor_area: document.getElementById('editFloorArea')?.value,
      selected_contractor_id: document.getElementById('editContractorSelect')?.value || null
    };

    // Build location string from dropdowns
    // Format: "Street, Barangay, City, Province" (actual format in database)
    const province = document.getElementById('editProvince');
    const city = document.getElementById('editCity');
    const barangay = document.getElementById('editBarangay');
    const street = document.getElementById('editStreet')?.value || '';

    const locationParts = [];
    
    // Order: Street, Barangay, City, Province (actual database format)
    if (street) {
      locationParts.push(street);
    }
    if (barangay?.selectedOptions[0]?.text && barangay.value) {
      locationParts.push(barangay.selectedOptions[0].text);
    }
    if (city?.selectedOptions[0]?.text && city.value) {
      locationParts.push(city.selectedOptions[0].text);
    }
    if (province?.selectedOptions[0]?.text && province.value) {
      locationParts.push(province.selectedOptions[0].text);
    }

    projectData.project_location = locationParts.join(', ');

    try {
      const response = await fetch(`/admin/project-management/${projectId}`, {
        method: 'PUT',
        headers: {
          'Content-Type': 'application/json',
          'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '',
          'X-Requested-With': 'XMLHttpRequest'
        },
        body: JSON.stringify(projectData)
      });

      const result = await response.json();

      if (result.success) {
        showNotification(result.message || 'Project updated successfully', 'success');
        hideEditProjectModal();
        // Refresh the table to show updated data
        if (typeof window.refreshProjectsTable === 'function') {
          window.refreshProjectsTable();
        }
      } else {
        // Handle validation errors
        if (response.status === 422 && result.errors) {
          Object.keys(result.errors).forEach(field => {
            const errorElement = document.getElementById(`error-${field}`);
            if (errorElement) {
              errorElement.textContent = result.errors[field][0];
              errorElement.classList.remove('hidden');
            }
          });
          // No toast notification for validation errors - errors shown below fields
        } else {
          showNotification(result.message || 'Failed to update project', 'error');
        }
      }
    } catch (error) {
      console.error('Error updating project:', error);
      showNotification('An error occurred while updating the project', 'error');
    }
  };

  // Initialize PSGC location dropdowns for edit modal
  async function initializeEditLocationDropdowns() {
    const provinceSelect = document.getElementById('editProvince');
    const citySelect = document.getElementById('editCity');
    const barangaySelect = document.getElementById('editBarangay');
    const streetInput = document.getElementById('editStreet');

    if (!provinceSelect) {
      console.error('Province select not found');
      return;
    }

    // Parse existing location from data attribute on the modal content
    const modalContent = document.querySelector('#editProjectModal .bg-white');
    const existingLocation = modalContent?.dataset?.location || '';
    
    console.log('=== ADDRESS PARSING DEBUG ===');
    console.log('Modal content element:', modalContent);
    console.log('Raw location from database:', existingLocation);
    console.log('Location length:', existingLocation.length);

    if (!existingLocation || existingLocation.trim() === '') {
      console.warn('No location data found in modal');
      return;
    }

    // Split by comma and trim each part
    const locationParts = existingLocation.split(',').map(part => part.trim()).filter(Boolean);
    console.log('Location parts after split:', locationParts);
    console.log('Number of parts:', locationParts.length);

    // Extract parts based on format: "Street, Barangay, City, Province"
    // Example: "Purok 365 Atuphai Street, Baluno, Zamboanga City, Zamboanga del Sur"
    let street = '', barangay = '', city = '', province = '';

    if (locationParts.length >= 4) {
      // Full format: Street, Barangay, City, Province
      street = locationParts[0];
      barangay = locationParts[1];
      city = locationParts[2];
      province = locationParts[3];
    } else if (locationParts.length === 3) {
      // Format: Barangay, City, Province (no street)
      barangay = locationParts[0];
      city = locationParts[1];
      province = locationParts[2];
    } else if (locationParts.length === 2) {
      // Format: City, Province
      city = locationParts[0];
      province = locationParts[1];
    } else if (locationParts.length === 1) {
      // Format: Province only
      province = locationParts[0];
    }

    // Normalize city name for PSGC matching
    // "Zamboanga City" in DB → "City of Zamboanga" in PSGC
    if (city.toLowerCase().includes('zamboanga') && city.toLowerCase().includes('city')) {
      city = 'City of Zamboanga';
    }

    // Populate street field immediately
    if (streetInput) streetInput.value = street;

    console.log('=== PARSED VALUES ===');
    console.log('Street:', street);
    console.log('Barangay:', barangay);
    console.log('City:', city);
    console.log('Province:', province);
    console.log('=====================');

    // Load provinces
    try {
      console.log('Fetching provinces from API...');
      const response = await fetch('/api/psgc/provinces');
      
      if (!response.ok) {
        throw new Error(`HTTP error! status: ${response.status}`);
      }
      
      const provincesData = await response.json();
      console.log('Provinces API response:', provincesData);
      
      // Handle different response formats
      const provinces = Array.isArray(provincesData) ? provincesData : (provincesData.data || []);
      console.log('Provinces array:', provinces);
      console.log('Number of provinces:', provinces.length);

      provinceSelect.innerHTML = '<option value="">Select Province</option>';
      let selectedProvinceCode = null;

      provinces.forEach(prov => {
        const option = document.createElement('option');
        option.value = prov.code;
        option.textContent = prov.name;

        // Match by name (case-insensitive, flexible matching)
        const normalizedProvName = prov.name.toLowerCase().replace(/\s+/g, ' ').trim();
        const normalizedSearchName = province.toLowerCase().replace(/\s+/g, ' ').trim();

        if (province && (normalizedProvName === normalizedSearchName ||
                        normalizedProvName.includes(normalizedSearchName) ||
                        normalizedSearchName.includes(normalizedProvName))) {
          option.selected = true;
          selectedProvinceCode = prov.code;
          console.log('✓ Matched province:', prov.name, 'with code:', prov.code);
        }

        provinceSelect.appendChild(option);
      });

      console.log('Selected province code:', selectedProvinceCode);

      // If province was selected, load cities
      if (selectedProvinceCode) {
        await loadCities(selectedProvinceCode, city, barangay);
      } else if (province) {
        console.warn('⚠ Province not matched:', province);
        console.warn('Available provinces:', provinces.map(p => p.name).join(', '));
      }
    } catch (error) {
      console.error('❌ Error loading provinces:', error);
    }

    // Helper function to load cities
    async function loadCities(provinceCode, cityToSelect = '', barangayToSelect = '') {
      console.log('Loading cities for province:', provinceCode, '| City to select:', cityToSelect);
      citySelect.innerHTML = '<option value="">Select City</option>';
      citySelect.disabled = false;

      try {
        const response = await fetch(`/api/psgc/provinces/${provinceCode}/cities`);
        
        if (!response.ok) {
          throw new Error(`HTTP error! status: ${response.status}`);
        }
        
        const citiesData = await response.json();
        console.log('Cities API response:', citiesData);
        
        // Handle different response formats
        const cities = Array.isArray(citiesData) ? citiesData : (citiesData.data || []);
        console.log('Cities array:', cities);
        console.log('Number of cities:', cities.length);
        
        let selectedCityCode = null;

        cities.forEach(cty => {
          const option = document.createElement('option');
          option.value = cty.code;
          option.textContent = cty.name;

          // Match by name (case-insensitive, flexible matching)
          const normalizedCityName = cty.name.toLowerCase().replace(/\s+/g, ' ').trim();
          const normalizedSearchName = cityToSelect.toLowerCase().replace(/\s+/g, ' ').trim();

          if (cityToSelect && (normalizedCityName === normalizedSearchName ||
                              normalizedCityName.includes(normalizedSearchName) ||
                              normalizedSearchName.includes(normalizedCityName))) {
            option.selected = true;
            selectedCityCode = cty.code;
            console.log('✓ Matched city:', cty.name, 'with code:', cty.code);
          }

          citySelect.appendChild(option);
        });

        console.log('Selected city code:', selectedCityCode);

        // If city was selected, load barangays
        if (selectedCityCode) {
          await loadBarangays(selectedCityCode, barangayToSelect);
        } else if (cityToSelect) {
          console.warn('⚠ City not matched:', cityToSelect);
          console.warn('Available cities:', cities.map(c => c.name).join(', '));
        }
      } catch (error) {
        console.error('❌ Error loading cities:', error);
      }
    }

    // Helper function to load barangays
    async function loadBarangays(cityCode, barangayToSelect = '') {
      console.log('Loading barangays for city:', cityCode, '| Barangay to select:', barangayToSelect);
      barangaySelect.innerHTML = '<option value="">Select Barangay</option>';
      barangaySelect.disabled = false;

      try {
        const response = await fetch(`/api/psgc/cities/${cityCode}/barangays`);
        
        if (!response.ok) {
          throw new Error(`HTTP error! status: ${response.status}`);
        }
        
        const barangaysData = await response.json();
        console.log('Barangays API response:', barangaysData);
        
        // Handle different response formats
        const barangays = Array.isArray(barangaysData) ? barangaysData : (barangaysData.data || []);
        console.log('Barangays array:', barangays);
        console.log('Number of barangays:', barangays.length);

        barangays.forEach(brgy => {
          const option = document.createElement('option');
          option.value = brgy.code;
          option.textContent = brgy.name;

          // Match by name (case-insensitive, flexible matching)
          const normalizedBrgyName = brgy.name.toLowerCase().replace(/\s+/g, ' ').trim();
          const normalizedSearchName = barangayToSelect.toLowerCase().replace(/\s+/g, ' ').trim();

          if (barangayToSelect && (normalizedBrgyName === normalizedSearchName ||
                                  normalizedBrgyName.includes(normalizedSearchName) ||
                                  normalizedSearchName.includes(normalizedBrgyName))) {
            option.selected = true;
            console.log('✓ Matched barangay:', brgy.name, 'with code:', brgy.code);
          }

          barangaySelect.appendChild(option);
        });

        if (barangayToSelect && !barangaySelect.value) {
          console.warn('⚠ Barangay not matched:', barangayToSelect);
          console.warn('Available barangays:', barangays.map(b => b.name).slice(0, 10).join(', '), '...');
        }
      } catch (error) {
        console.error('❌ Error loading barangays:', error);
      }
    }

    // Province change handler
    provinceSelect.addEventListener('change', async function() {
      citySelect.innerHTML = '<option value="">Select City</option>';
      barangaySelect.innerHTML = '<option value="">Select Barangay</option>';
      citySelect.disabled = !this.value;
      barangaySelect.disabled = true;

      if (this.value) {
        await loadCities(this.value);
      }
    });

    // City change handler
    citySelect.addEventListener('change', async function() {
      barangaySelect.innerHTML = '<option value="">Select Barangay</option>';
      barangaySelect.disabled = !this.value;

      if (this.value) {
        await loadBarangays(this.value);
      }
    });
  }

  // Show edit milestone item modal with server-side rendered content
  window.openEditMilestoneModal = async function(itemId) {
    try {
      if (!itemId) {
        showNotification('Invalid milestone item ID', 'error');
        return;
      }

      const response = await fetch(`/admin/project-management/milestone-item/${itemId}/edit`);
      const result = await response.json();

      if (result.success) {
        const modalContainer = document.getElementById('editMilestoneModalContainer');
        if (modalContainer) {
          modalContainer.innerHTML = result.html;
          const existingModal = document.getElementById('editMilestoneModal');
          if (existingModal) {
            existingModal.classList.remove('hidden');
          }
        }
      } else {
        showNotification('Failed to load milestone item', 'error');
      }
    } catch (error) {
      console.error('Error loading milestone item:', error);
      showNotification('An error occurred while loading milestone item', 'error');
    }
  };

  // Hide edit milestone modal
  window.hideEditMilestoneModal = function() {
    const modal = document.getElementById('editMilestoneModal');
    if (modal) {
      modal.classList.add('hidden');
    }
  };

  // Save milestone edit
  window.saveMilestoneEdit = async function() {
    try {
      // Clear all previous errors
      const errorElements = document.querySelectorAll('[id^="error-"]');
      errorElements.forEach(el => {
        el.classList.add('hidden');
        el.textContent = '';
      });

      // Get item ID from modal
      const modal = document.getElementById('editMilestoneModal');
      const itemId = modal?.dataset?.itemId;

      if (!itemId) {
        showNotification('Item ID not found', 'error');
        return;
      }

      // Get form values
      const milestone_item_title = document.getElementById('editMilestoneItemTitle')?.value || '';
      const milestone_item_description = document.getElementById('editMilestoneItemDescription')?.value || '';
      const date_to_finish = document.getElementById('editMilestoneItemDate')?.value || '';
      const milestone_item_cost = document.getElementById('editMilestoneItemCost')?.value || '';
      const item_status = document.getElementById('editMilestoneItemStatus')?.value || '';

      // Get CSRF token
      const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';

      const response = await fetch(`/admin/project-management/milestone-item/${itemId}`, {
        method: 'PUT',
        headers: {
          'Content-Type': 'application/json',
          'X-CSRF-TOKEN': csrfToken,
          'Accept': 'application/json',
          'X-Requested-With': 'XMLHttpRequest'
        },
        body: JSON.stringify({
          milestone_item_title,
          milestone_item_description,
          date_to_finish,
          milestone_item_cost,
          item_status
        })
      });

      const result = await response.json();

      if (result.success) {
        showNotification('Milestone item updated successfully', 'success');
        hideEditMilestoneModal();

        // Refresh the currently open modal with updated data
        if (window.currentProjectId) {
          const selectedItemId = window.selectedMilestoneItemId;

          // Determine which modal is open and refresh it
          const ongoingModal = document.getElementById('ongoingProjectModal');
          const completedModal = document.getElementById('completedProjectModal');

          if (ongoingModal && !ongoingModal.classList.contains('hidden')) {
            // Save scroll position before refresh
            const scrollContainer = ongoingModal.querySelector('.overflow-y-auto');
            const scrollPosition = scrollContainer ? scrollContainer.scrollTop : 0;
            
            // Refresh ongoing project modal
            await showOngoingProjectModal(window.currentProjectId);
            
            // Restore scroll position after refresh
            if (scrollContainer) {
              setTimeout(() => {
                const newScrollContainer = ongoingModal.querySelector('.overflow-y-auto');
                if (newScrollContainer) {
                  newScrollContainer.scrollTop = scrollPosition;
                }
              }, 100);
            }
            
            // Re-select the milestone after refresh
            if (selectedItemId) {
              setTimeout(() => showOngoingMilestoneDetails(selectedItemId), 300);
            }
          } else if (completedModal && !completedModal.classList.contains('hidden')) {
            // Save scroll position before refresh
            const scrollContainer = completedModal.querySelector('.overflow-y-auto');
            const scrollPosition = scrollContainer ? scrollContainer.scrollTop : 0;
            
            // Refresh completed project modal
            const detailsResponse = await fetch(`/admin/project-management/${window.currentProjectId}/details`);
            const detailsResult = await detailsResponse.json();
            if (detailsResult.success && detailsResult.data) {
              await openCompletedProjectModal(detailsResult.data);
              
              // Restore scroll position after refresh
              if (scrollContainer) {
                setTimeout(() => {
                  const newScrollContainer = completedModal.querySelector('.overflow-y-auto');
                  if (newScrollContainer) {
                    newScrollContainer.scrollTop = scrollPosition;
                  }
                }, 100);
              }
              
              // Re-select the milestone after refresh
              if (selectedItemId) {
                setTimeout(() => showMilestoneDetails(selectedItemId), 300);
              }
            }
          }
        }
      } else {
        // Handle validation errors
        if (response.status === 422 && result.errors) {
          // Map validation errors to error elements
          const errorMap = {
            'milestone_item_title': 'error-milestone-item-title',
            'milestone_item_description': 'error-milestone-item-description',
            'date_to_finish': 'error-date-to-finish',
            'milestone_item_cost': 'error-milestone-item-cost',
            'item_status': 'error-item-status'
          };

          Object.keys(result.errors).forEach(field => {
            const errorElementId = errorMap[field];
            if (errorElementId) {
              const errorElement = document.getElementById(errorElementId);
              if (errorElement) {
                errorElement.textContent = result.errors[field][0];
                errorElement.classList.remove('hidden');
              }
            }
          });
          // No toast notification for validation errors
        } else {
          showNotification(result.message || 'Failed to update milestone item', 'error');
        }
      }
    } catch (error) {
      console.error('Error updating milestone item:', error);
      showNotification('An error occurred while updating milestone item', 'error');
    }
  };

  // Show halt project modal with server-side rendered content
  window.showHaltProjectModal = async function(projectId) {
    try {
      if (!projectId) {
        showNotification('Invalid project ID', 'error');
        return;
      }

      const response = await fetch(`/admin/project-management/${projectId}/halt-summary`);
      const result = await response.json();

      if (result.success) {
        const modalContainer = document.getElementById('haltProjectModalContainer');
        if (modalContainer) {
          modalContainer.innerHTML = result.html;
          const existingModal = document.getElementById('haltProjectModal');
          if (existingModal) {
            existingModal.classList.remove('hidden');
          }
        }
      } else {
        showNotification('Failed to load project summary', 'error');
      }
    } catch (error) {
      console.error('Error loading project summary:', error);
      showNotification('An error occurred while loading project summary', 'error');
    }
  };

  // Hide halt project modal
  window.hideHaltProjectModal = function() {
    const modal = document.getElementById('haltProjectModal');
    if (modal) {
      modal.classList.add('hidden');
    }
  };

  // Confirm halt project
  window.confirmHaltProject = async function() {
    try {
      // Clear previous errors
      const errorElement = document.getElementById('error-halt-reason');
      if (errorElement) {
        errorElement.classList.add('hidden');
        errorElement.textContent = '';
      }
      const disputeError = document.getElementById('error-halt-dispute');
      if (disputeError) {
        disputeError.classList.add('hidden');
        disputeError.textContent = '';
      }

      // Get project ID from modal
      const modal = document.getElementById('haltProjectModal');
      const projectId = modal?.dataset?.projectId;

      if (!projectId) {
        showNotification('Project ID not found', 'error');
        return;
      }

      // Get the halt reason and remarks from the textareas
      const haltReason = document.getElementById('haltReason')?.value || '';
      const haltRemarks = document.getElementById('haltRemarks')?.value || '';
      const haltDispute = document.getElementById('haltDispute')?.value || '';

      // Get CSRF token
      const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content')
        || document.querySelector('input[name="_token"]')?.value
        || '';

      const response = await fetch(`/admin/project-management/${projectId}/halt`, {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'X-CSRF-TOKEN': csrfToken,
          'Accept': 'application/json',
          'X-Requested-With': 'XMLHttpRequest'
        },
        body: JSON.stringify({
          dispute_id: haltDispute,
          halt_reason: haltReason,
          project_remarks: haltRemarks
        })
      });

      const result = await response.json();

      if (result.success) {
        showNotification('Project halted successfully', 'success');
        hideHaltProjectModal();
        // Close the ongoing/bidding modal if open
        hideOngoingProjectModal();
        hideBiddingModal();
        // Refresh the table to show updated status
        if (typeof window.refreshProjectsTable === 'function') {
          window.refreshProjectsTable();
        }
      } else {
        // Handle validation errors
        if (response.status === 422 && result.errors) {
          // Show dispute error if present
          const disputeError = document.getElementById('error-halt-dispute');
          if (result.errors.dispute_id && disputeError) {
            disputeError.textContent = result.errors.dispute_id[0];
            disputeError.classList.remove('hidden');
          }
          // Show halt reason error if present
          if (result.errors.halt_reason && errorElement) {
            errorElement.textContent = result.errors.halt_reason[0];
            errorElement.classList.remove('hidden');
          }
          // No toast notification for validation errors - error shown below field
        } else {
          showNotification(result.message || 'Failed to halt project', 'error');
        }
      }
    } catch (error) {
      console.error('Error halting project:', error);

      // If it's a network error, show in toast
      if (error.name === 'TypeError' && error.message === 'Failed to fetch') {
        showNotification('Network error - please check your connection and try again', 'error');
      } else {
        showNotification('An error occurred while halting project', 'error');
      }
    }
  };

  // Resume halted project
  window.resumeHaltedProject = async function(projectId) {
    if (!confirm('Are you sure you want to resume this project? The project and all halted milestone items will be restored to their previous status.')) {
      return;
    }

    try {
      const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content')
        || document.querySelector('input[name="_token"]')?.value
        || '';

      const response = await fetch(`/admin/project-management/${projectId}/resume`, {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'X-CSRF-TOKEN': csrfToken,
          'Accept': 'application/json',
          'X-Requested-With': 'XMLHttpRequest'
        }
      });

      const result = await response.json();

      if (result.success) {
        showNotification('Project resumed successfully', 'success');
        hideHaltedProjectModal();
        // Refresh the table to show updated status
        if (typeof window.refreshProjectsTable === 'function') {
          window.refreshProjectsTable();
        }
      } else {
        showNotification(result.message || 'Failed to resume project', 'error');
      }
    } catch (error) {
      console.error('Error resuming project:', error);
      showNotification('An error occurred while resuming project', 'error');
    }
  };

});


// ============================================================================
// COLLAPSIBLE SECTIONS - Budget History & Change Audit Log
// ============================================================================

/**
 * Toggle Budget History section
 */
window.toggleBudgetHistory = function() {
  const content = document.getElementById('budgetHistoryContent');
  const chevron = document.getElementById('budgetHistoryChevron');
  
  if (content && chevron) {
    if (content.classList.contains('hidden')) {
      content.classList.remove('hidden');
      chevron.style.transform = 'rotate(180deg)';
    } else {
      content.classList.add('hidden');
      chevron.style.transform = 'rotate(0deg)';
    }
  }
};

/**
 * Toggle Change Audit Log section
 */
window.toggleChangeAuditLog = function() {
  const content = document.getElementById('auditLogContent');
  const chevron = document.getElementById('auditLogChevron');
  
  if (content && chevron) {
    if (content.classList.contains('hidden')) {
      content.classList.remove('hidden');
      chevron.style.transform = 'rotate(180deg)';
    } else {
      content.classList.add('hidden');
      chevron.style.transform = 'rotate(0deg)';
    }
  }
};

/**
 * Show milestone details in the completed project modal
 * Enhanced to show financial information
 */
window.showMilestoneDetails = function(itemId) {
  console.log('showMilestoneDetails called with itemId:', itemId);
  
  // Hide all milestone details
  const allDetails = document.querySelectorAll('[id^="milestone-detail-"]');
  console.log('Found milestone detail panels:', allDetails.length);
  allDetails.forEach(detail => {
    detail.classList.add('hidden');
    console.log('Hiding panel:', detail.id);
  });

  // Hide the "Select a milestone" message
  const noSelection = document.querySelector('#completedDetailsContent > div.text-sm.text-gray-500');
  if (noSelection) {
    noSelection.classList.add('hidden');
    console.log('Hiding "Select a milestone" message');
  }

  // Show the selected milestone details
  const selectedDetail = document.getElementById(`milestone-detail-${itemId}`);
  console.log('Looking for panel:', `milestone-detail-${itemId}`, 'Found:', selectedDetail);
  if (selectedDetail) {
    selectedDetail.classList.remove('hidden');
    console.log('Showing panel:', selectedDetail.id);
  } else {
    console.error('Milestone detail panel not found for itemId:', itemId);
  }

  // Store selected milestone ID for edit button
  window.selectedMilestoneItemId = itemId;

  // Show edit button if it exists
  const editBtn = document.getElementById('editMilestoneBtn');
  if (editBtn) {
    editBtn.classList.remove('hidden');
  }
};

/**
 * Format currency for display
 */
function formatCurrency(amount) {
  return new Intl.NumberFormat('en-PH', {
    style: 'currency',
    currency: 'PHP',
    minimumFractionDigits: 2
  }).format(amount);
}

/**
 * Calculate budget variance percentage
 */
function calculateVariance(original, final) {
  if (original === 0) return 0;
  return ((final - original) / original) * 100;
}

/**
 * Get variance color class
 */
function getVarianceColorClass(variance) {
  if (variance > 0) return 'text-red-600';
  if (variance < 0) return 'text-green-600';
  return 'text-gray-500';
}

/**
 * Initialize collapsible sections on modal load
 */
function initializeCollapsibleSections() {
  // Ensure all collapsible sections start collapsed
  const budgetContent = document.getElementById('budgetHistoryContent');
  const auditContent = document.getElementById('auditLogContent');
  const budgetChevron = document.getElementById('budgetHistoryChevron');
  const auditChevron = document.getElementById('auditLogChevron');

  if (budgetContent) budgetContent.classList.add('hidden');
  if (auditContent) auditContent.classList.add('hidden');
  if (budgetChevron) budgetChevron.style.transform = 'rotate(0deg)';
  if (auditChevron) auditChevron.style.transform = 'rotate(0deg)';
}

// Initialize when modal is shown
document.addEventListener('DOMContentLoaded', function() {
  // Listen for modal show events
  const observer = new MutationObserver(function(mutations) {
    mutations.forEach(function(mutation) {
      if (mutation.type === 'attributes' && mutation.attributeName === 'class') {
        const modal = mutation.target;
        if (modal.id === 'completedProjectModal' && !modal.classList.contains('hidden')) {
          initializeCollapsibleSections();
        }
      }
    });
  });

  const completedModal = document.getElementById('completedProjectModal');
  if (completedModal) {
    observer.observe(completedModal, { attributes: true });
  }
});


// ============================================================================
// ONGOING PROJECT MODAL - Collapsible Sections
// ============================================================================

/**
 * Toggle Budget Tracking section in ongoing modal
 */
window.toggleOngoingBudgetTracking = function() {
  const content = document.getElementById('ongoingBudgetContent');
  const chevron = document.getElementById('ongoingBudgetChevron');
  
  if (content && chevron) {
    if (content.classList.contains('hidden')) {
      content.classList.remove('hidden');
      chevron.style.transform = 'rotate(180deg)';
    } else {
      content.classList.add('hidden');
      chevron.style.transform = 'rotate(0deg)';
    }
  }
};

/**
 * Show milestone details in the ongoing project modal
 */
window.showOngoingMilestoneDetails = function(itemId) {
  // Hide all milestone details
  const allDetails = document.querySelectorAll('[id^="ongoing-milestone-detail-"]');
  allDetails.forEach(detail => {
    detail.classList.add('hidden');
  });

  // Hide the "Select a milestone" message
  const noSelection = document.querySelector('#ongoingDetailsContent > div.text-sm.text-gray-500');
  if (noSelection) {
    noSelection.classList.add('hidden');
  }

  // Show the selected milestone details
  const selectedDetail = document.getElementById(`ongoing-milestone-detail-${itemId}`);
  if (selectedDetail) {
    selectedDetail.classList.remove('hidden');
  }

  // Store selected milestone ID for edit button
  window.selectedMilestoneItemId = itemId;

  // Show edit button if it exists
  const editBtn = document.getElementById('editOngoingMilestoneBtn');
  if (editBtn) {
    editBtn.classList.remove('hidden');
  }
};


// ============================================================================
// HALTED PROJECT MODAL - Collapsible Sections
// ============================================================================

/**
 * Toggle Halt Comparison section in halted modal
 */
window.toggleHaltComparison = function() {
  const content = document.getElementById('haltComparisonContent');
  const chevron = document.getElementById('haltComparisonChevron');
  
  if (content && chevron) {
    if (content.classList.contains('hidden')) {
      content.classList.remove('hidden');
      chevron.style.transform = 'rotate(180deg)';
    } else {
      content.classList.add('hidden');
      chevron.style.transform = 'rotate(0deg)';
    }
  }
};

/**
 * Show milestone details in the halted project modal
 */
window.showHaltedMilestoneDetail = function(itemId) {
  // Store the selected item ID globally
  window.selectedMilestoneItemId = itemId;

  // Hide all milestone details
  const allDetails = document.querySelectorAll('[id^="halted-milestone-detail-"]');
  allDetails.forEach(detail => {
    detail.classList.add('hidden');
  });

  // Hide the "Select a milestone" message
  const noSelection = document.querySelector('#haltedDetailsContent > div.text-sm.text-gray-500');
  if (noSelection) {
    noSelection.classList.add('hidden');
  }

  // Show the selected milestone details
  const selectedDetail = document.getElementById(`halted-milestone-detail-${itemId}`);
  if (selectedDetail) {
    selectedDetail.classList.remove('hidden');
  }

  // Show the edit button
  const editBtn = document.getElementById('editHaltedMilestoneBtn');
  if (editBtn) {
    editBtn.classList.remove('hidden');
  }
};


// ═══════════════════════════════════════════════════════════════════════════
// TIMELINE EXTENSION FUNCTIONS
// ═══════════════════════════════════════════════════════════════════════════

/**
 * Show extend timeline modal
 */
function showExtendTimelineModal(projectId, currentEndDate, currentStartDate) {
  const modal = document.getElementById('extendTimelineModal');
  if (!modal) return;

  // Set project data
  document.getElementById('extendProjectId').value = projectId;
  document.getElementById('extendCurrentEndDate').value = currentEndDate;
  
  // Display current timeline
  if (currentStartDate) {
    document.getElementById('extendCurrentStart').textContent = formatDate(currentStartDate);
  }
  if (currentEndDate) {
    document.getElementById('extendCurrentEnd').textContent = formatDate(currentEndDate);
    
    // Calculate and display duration
    const start = new Date(currentStartDate);
    const end = new Date(currentEndDate);
    const days = Math.ceil((end - start) / (1000 * 60 * 60 * 24));
    document.getElementById('extendCurrentDuration').textContent = days + ' days';
    
    // Set min date for new end date (must be after current)
    const tomorrow = new Date(currentEndDate);
    tomorrow.setDate(tomorrow.getDate() + 1);
    document.getElementById('extendNewEndDate').min = tomorrow.toISOString().split('T')[0];
  }
  
  // Reset form
  document.getElementById('extendTimelineForm').reset();
  document.getElementById('extendTimelineError').classList.add('hidden');
  document.getElementById('extensionDurationDisplay').classList.add('hidden');
  document.getElementById('affectedMilestonesSection').classList.add('hidden');
  document.getElementById('reasonCharCount').textContent = '0';
  
  // Show modal
  modal.classList.remove('hidden');
  setTimeout(() => modal.classList.add('opacity-100'), 10);
}

/**
 * Hide extend timeline modal
 */
function hideExtendTimelineModal() {
  const modal = document.getElementById('extendTimelineModal');
  if (!modal) return;
  
  modal.classList.remove('opacity-100');
  setTimeout(() => {
    modal.classList.add('hidden');
    document.getElementById('extendTimelineForm').reset();
  }, 300);
}

/**
 * Calculate extension duration when new date is selected
 */
document.addEventListener('DOMContentLoaded', function() {
  const newEndDateInput = document.getElementById('extendNewEndDate');
  const reasonTextarea = document.getElementById('extendReason');
  
  if (newEndDateInput) {
    newEndDateInput.addEventListener('change', function() {
      const currentEndDate = document.getElementById('extendCurrentEndDate').value;
      const newEndDate = this.value;
      
      if (currentEndDate && newEndDate) {
        const current = new Date(currentEndDate);
        const newDate = new Date(newEndDate);
        const days = Math.ceil((newDate - current) / (1000 * 60 * 60 * 24));
        
        if (days > 0) {
          document.getElementById('extensionDays').textContent = days;
          document.getElementById('extensionDurationDisplay').classList.remove('hidden');
          
          // Fetch affected milestones
          const projectId = document.getElementById('extendProjectId').value;
          fetchAffectedMilestones(projectId, newEndDate);
        } else {
          document.getElementById('extensionDurationDisplay').classList.add('hidden');
          showExtensionError('New end date must be after the current end date');
        }
      }
    });
  }
  
  // Character count for reason
  if (reasonTextarea) {
    reasonTextarea.addEventListener('input', function() {
      const count = this.value.length;
      document.getElementById('reasonCharCount').textContent = count;
      
      if (count < 10) {
        document.getElementById('reasonCharCount').classList.add('text-red-500');
      } else {
        document.getElementById('reasonCharCount').classList.remove('text-red-500');
      }
    });
  }
  
  // Form submission
  const form = document.getElementById('extendTimelineForm');
  if (form) {
    form.addEventListener('submit', function(e) {
      e.preventDefault();
      submitTimelineExtension();
    });
  }
});

/**
 * Fetch affected milestones
 */
async function fetchAffectedMilestones(projectId, newEndDate) {
  try {
    const response = await fetch(`/admin/projects/${projectId}/affected-milestones?new_end_date=${newEndDate}`, {
      headers: {
        'X-Requested-With': 'XMLHttpRequest',
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
      }
    });
    
    if (!response.ok) throw new Error('Failed to fetch affected milestones');
    
    const data = await response.json();
    
    if (data.success && data.affected_milestones && data.affected_milestones.length > 0) {
      displayAffectedMilestones(data.affected_milestones);
    } else {
      document.getElementById('affectedMilestonesSection').classList.add('hidden');
    }
  } catch (error) {
    console.error('Error fetching affected milestones:', error);
  }
}

/**
 * Display affected milestones
 */
function displayAffectedMilestones(milestones) {
  const section = document.getElementById('affectedMilestonesSection');
  const list = document.getElementById('affectedMilestonesList');
  const count = document.getElementById('affectedMilestonesCount');
  
  count.textContent = milestones.length;
  
  let html = '<div class="space-y-2 text-sm">';
  milestones.forEach(milestone => {
    html += `
      <div class="flex items-center justify-between py-2 border-b border-amber-200 last:border-0">
        <div class="flex items-center gap-2">
          <svg class="w-4 h-4 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
          </svg>
          <span class="font-medium text-gray-900">${milestone.title}</span>
        </div>
        <div class="text-xs text-gray-600">
          ${formatDate(milestone.current_date)} → ${formatDate(milestone.new_date)}
        </div>
      </div>
    `;
  });
  html += '</div>';
  
  list.innerHTML = html;
  section.classList.remove('hidden');
}

/**
 * Submit timeline extension
 */
async function submitTimelineExtension() {
  const form = document.getElementById('extendTimelineForm');
  const submitBtn = document.getElementById('submitExtensionBtn');
  const formData = new FormData(form);
  
  // Disable submit button
  submitBtn.disabled = true;
  submitBtn.innerHTML = `
    <svg class="animate-spin w-5 h-5" fill="none" viewBox="0 0 24 24">
      <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
      <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
    </svg>
    <span>Submitting...</span>
  `;
  
  try {
    const projectId = formData.get('project_id');
    const response = await fetch(`/admin/projects/${projectId}/extend-timeline`, {
      method: 'POST',
      headers: {
        'X-Requested-With': 'XMLHttpRequest',
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
      },
      body: formData
    });
    
    const data = await response.json();
    
    if (data.success) {
      // Show success message
      showSuccessToast(data.message || 'Timeline extended successfully');
      
      // Hide modal
      hideExtendTimelineModal();
      
      // Refresh project details if modal is open
      const projectId = formData.get('project_id');
      if (projectId && window.currentProjectId) {
        // Determine which modal to refresh based on which one is currently open
        const ongoingModal = document.getElementById('ongoingProjectModal');
        const haltedModal = document.getElementById('haltedProjectModal');
        
        if (ongoingModal && !ongoingModal.classList.contains('hidden')) {
          // Refresh ongoing project modal
          await showOngoingProjectModal(projectId);
        } else if (haltedModal && !haltedModal.classList.contains('hidden')) {
          // Refresh halted project modal
          await showHaltedProjectModal(projectId);
        }
      }
      
      // Refresh table
      if (typeof window.refreshProjectsTable === 'function') {
        window.refreshProjectsTable();
      }
    } else {
      showExtensionError(data.message || 'Failed to extend timeline');
    }
  } catch (error) {
    console.error('Error submitting extension:', error);
    showExtensionError('An error occurred while submitting the extension request');
  } finally {
    // Re-enable submit button
    submitBtn.disabled = false;
    submitBtn.innerHTML = `
      <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
      </svg>
      <span>Submit Extension</span>
    `;
  }
}

/**
 * Show extension error
 */
function showExtensionError(message) {
  const errorDiv = document.getElementById('extendTimelineError');
  const errorMessage = document.getElementById('extendTimelineErrorMessage');
  
  errorMessage.textContent = message;
  errorDiv.classList.remove('hidden');
  
  // Scroll to error
  errorDiv.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
}

/**
 * Show success toast notification
 */
function showSuccessToast(message) {
  // Create toast element
  const toast = document.createElement('div');
  toast.className = 'fixed top-4 right-4 bg-green-500 text-white px-6 py-4 rounded-lg shadow-lg z-50 flex items-center gap-3 animate-slide-in';
  toast.innerHTML = `
    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
    </svg>
    <span class="font-semibold">${message}</span>
  `;
  
  document.body.appendChild(toast);
  
  // Remove after 3 seconds
  setTimeout(() => {
    toast.classList.add('animate-slide-out');
    setTimeout(() => toast.remove(), 300);
  }, 3000);
}

/**
 * Format date for display
 */
function formatDate(dateString) {
  if (!dateString) return '—';
  const date = new Date(dateString);
  const options = { year: 'numeric', month: 'short', day: 'numeric' };
  return date.toLocaleDateString('en-US', options);
}

// Make functions globally accessible
window.showExtendTimelineModal = showExtendTimelineModal;
window.hideExtendTimelineModal = hideExtendTimelineModal;

// ═══════════════════════════════════════════════════════════════════════════
// PENDING EXTENSION REQUESTS FUNCTIONS (PHASE 2)
// ═══════════════════════════════════════════════════════════════════════════

/**
 * Load and display pending extension requests
 */
async function loadPendingExtensions(projectId) {
  try {
    const response = await fetch(`/admin/projects/${projectId}/pending-extensions`, {
      headers: {
        'X-Requested-With': 'XMLHttpRequest',
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
      }
    });
    
    if (!response.ok) throw new Error('Failed to fetch pending extensions');
    
    const data = await response.json();
    
    if (data.success && data.requests && data.requests.length > 0) {
      displayPendingExtensions(data.requests);
    } else {
      hidePendingExtensionsSection();
    }
  } catch (error) {
    console.error('Error loading pending extensions:', error);
  }
}

/**
 * Display pending extension requests in modal
 */
function displayPendingExtensions(requests) {
  const section = document.getElementById('pendingExtensionsSection');
  const container = document.getElementById('pendingExtensionsContainer');
  const count = document.getElementById('pendingExtensionsCount');
  
  if (!section || !container || !count) return;
  
  count.textContent = requests.length;
  
  let html = '';
  requests.forEach(request => {
    const extensionDays = Math.ceil((new Date(request.proposed_end_date) - new Date(request.current_end_date)) / (1000 * 60 * 60 * 24));
    const submittedDate = new Date(request.created_at).toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' });
    
    html += `
      <div class="bg-white border-2 border-blue-200 rounded-lg p-5 hover:shadow-md transition-all duration-200">
        <div class="flex items-start justify-between mb-3">
          <div>
            <h4 class="font-bold text-gray-900 text-sm">Request #EXT-${request.extension_id}</h4>
            <p class="text-xs text-gray-600 mt-1">Submitted: ${submittedDate} by ${request.requester_name || 'Unknown'}</p>
          </div>
          <span class="px-2.5 py-1 bg-amber-100 text-amber-800 text-xs font-semibold rounded-full">Pending</span>
        </div>
        
        <div class="grid grid-cols-2 gap-3 mb-3 text-sm">
          <div class="bg-gray-50 rounded-lg p-3">
            <p class="text-xs text-gray-600 mb-1">Current End Date</p>
            <p class="font-semibold text-gray-900">${formatDate(request.current_end_date)}</p>
          </div>
          <div class="bg-blue-50 rounded-lg p-3">
            <p class="text-xs text-gray-600 mb-1">Proposed End Date</p>
            <p class="font-semibold text-blue-700">${formatDate(request.proposed_end_date)} <span class="text-xs">(+${extensionDays} days)</span></p>
          </div>
        </div>
        
        <div class="mb-4">
          <p class="text-xs text-gray-600 font-semibold mb-1">Reason:</p>
          <p class="text-sm text-gray-700 italic bg-gray-50 rounded p-2">"${request.reason}"</p>
        </div>
        
        <div class="flex items-center gap-2">
          <button 
            onclick="approveExtensionRequest(${request.extension_id}, ${request.project_id})"
            class="flex-1 px-3 py-2 bg-gradient-to-r from-green-500 to-emerald-600 text-white text-xs font-semibold rounded-lg hover:from-green-600 hover:to-emerald-700 transition-all duration-200 shadow-sm hover:shadow-md flex items-center justify-center gap-1"
          >
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
            </svg>
            Approve
          </button>
          <button 
            onclick="showRejectExtensionModal(${request.extension_id}, ${request.project_id})"
            class="flex-1 px-3 py-2 bg-gradient-to-r from-red-500 to-rose-600 text-white text-xs font-semibold rounded-lg hover:from-red-600 hover:to-rose-700 transition-all duration-200 shadow-sm hover:shadow-md flex items-center justify-center gap-1"
          >
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
            </svg>
            Reject
          </button>
          <button 
            onclick="showRevisionRequestModal(${request.extension_id}, ${request.project_id})"
            class="flex-1 px-3 py-2 bg-gradient-to-r from-amber-500 to-orange-600 text-white text-xs font-semibold rounded-lg hover:from-amber-600 hover:to-orange-700 transition-all duration-200 shadow-sm hover:shadow-md flex items-center justify-center gap-1"
          >
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
            </svg>
            Revise
          </button>
        </div>
      </div>
    `;
  });
  
  container.innerHTML = html;
  section.classList.remove('hidden');
}

/**
 * Hide pending extensions section
 */
function hidePendingExtensionsSection() {
  const section = document.getElementById('pendingExtensionsSection');
  if (section) {
    section.classList.add('hidden');
  }
}

/**
 * Approve extension request
 */
async function approveExtensionRequest(extensionId, projectId) {
  if (!confirm('Are you sure you want to approve this extension request? Milestone dates will be updated immediately.')) {
    return;
  }
  
  try {
    const response = await fetch(`/admin/projects/extensions/${extensionId}/approve`, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'X-Requested-With': 'XMLHttpRequest',
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
      },
      body: JSON.stringify({ notes: null })
    });
    
    const data = await response.json();
    
    if (data.success) {
      showSuccessToast(data.message || 'Extension request approved successfully');
      
      // Reload pending extensions
      await loadPendingExtensions(projectId);
      
      // Refresh project details if modal is open
      if (typeof refreshCurrentProjectModal === 'function') {
        refreshCurrentProjectModal();
      }
    } else {
      alert(data.message || 'Failed to approve extension request');
    }
  } catch (error) {
    console.error('Error approving extension:', error);
    alert('An error occurred while approving the extension request');
  }
}

/**
 * Show reject extension modal
 */
function showRejectExtensionModal(extensionId, projectId) {
  const reason = prompt('Please provide a reason for rejecting this extension request (minimum 10 characters):');
  
  if (reason === null) return; // User cancelled
  
  if (reason.length < 10) {
    alert('Reason must be at least 10 characters long');
    return;
  }
  
  rejectExtensionRequest(extensionId, projectId, reason);
}

/**
 * Reject extension request
 */
async function rejectExtensionRequest(extensionId, projectId, reason) {
  try {
    const response = await fetch(`/admin/projects/extensions/${extensionId}/reject`, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'X-Requested-With': 'XMLHttpRequest',
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
      },
      body: JSON.stringify({ reason })
    });
    
    const data = await response.json();
    
    if (data.success) {
      showSuccessToast(data.message || 'Extension request rejected successfully');
      
      // Reload pending extensions
      await loadPendingExtensions(projectId);
      
      // Refresh project details if modal is open
      if (typeof refreshCurrentProjectModal === 'function') {
        refreshCurrentProjectModal();
      }
    } else {
      alert(data.message || 'Failed to reject extension request');
    }
  } catch (error) {
    console.error('Error rejecting extension:', error);
    alert('An error occurred while rejecting the extension request');
  }
}

/**
 * Show revision request modal
 */
function showRevisionRequestModal(extensionId, projectId) {
  const feedback = prompt('Please provide feedback for revision (minimum 10 characters):');
  
  if (feedback === null) return; // User cancelled
  
  if (feedback.length < 10) {
    alert('Feedback must be at least 10 characters long');
    return;
  }
  
  requestExtensionRevision(extensionId, projectId, feedback);
}

/**
 * Request revision on extension request
 */
async function requestExtensionRevision(extensionId, projectId, feedback) {
  try {
    const response = await fetch(`/admin/projects/extensions/${extensionId}/request-revision`, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'X-Requested-With': 'XMLHttpRequest',
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
      },
      body: JSON.stringify({ feedback })
    });
    
    const data = await response.json();
    
    if (data.success) {
      showSuccessToast(data.message || 'Revision request sent successfully');
      
      // Reload pending extensions
      await loadPendingExtensions(projectId);
      
      // Refresh project details if modal is open
      if (typeof refreshCurrentProjectModal === 'function') {
        refreshCurrentProjectModal();
      }
    } else {
      alert(data.message || 'Failed to send revision request');
    }
  } catch (error) {
    console.error('Error requesting revision:', error);
    alert('An error occurred while requesting revision');
  }
}

/**
 * Toggle pending extensions section
 */
function togglePendingExtensions() {
  const content = document.getElementById('pendingExtensionsContent');
  const chevron = document.getElementById('pendingExtensionsChevron');
  
  if (content && chevron) {
    content.classList.toggle('hidden');
    chevron.classList.toggle('rotate-180');
  }
}

// ═══════════════════════════════════════════════════════════════════════════
// BULK DATE ADJUSTMENT FUNCTIONS (PHASE 4)
// ═══════════════════════════════════════════════════════════════════════════

/**
 * Show bulk adjust dates modal
 */
function showBulkAdjustDatesModal(projectId) {
  const modal = document.getElementById('bulkAdjustDatesModal');
  if (!modal) return;

  // Set project ID
  document.getElementById('bulkAdjustProjectId').value = projectId;
  
  // Reset form
  document.getElementById('bulkAdjustDatesForm').reset();
  document.getElementById('bulkAdjustError').classList.add('hidden');
  document.getElementById('bulkAdjustPreviewSection').classList.add('hidden');
  document.getElementById('bulkReasonCharCount').textContent = '0';
  
  // Show modal
  modal.classList.remove('hidden');
  setTimeout(() => modal.classList.add('opacity-100'), 10);
}

/**
 * Hide bulk adjust dates modal
 */
function hideBulkAdjustDatesModal() {
  const modal = document.getElementById('bulkAdjustDatesModal');
  if (!modal) return;
  
  modal.classList.remove('opacity-100');
  setTimeout(() => {
    modal.classList.add('hidden');
    document.getElementById('bulkAdjustDatesForm').reset();
  }, 300);
}

/**
 * Preview bulk adjustment
 */
async function previewBulkAdjustment() {
  const projectId = document.getElementById('bulkAdjustProjectId').value;
  const days = document.getElementById('bulkAdjustDays').value;
  const direction = document.querySelector('input[name="direction"]:checked').value;
  
  // Validate inputs
  if (!days || days < 1) {
    showBulkAdjustError('Please enter a valid number of days');
    return;
  }
  
  const previewBtn = document.getElementById('previewBulkAdjustBtn');
  previewBtn.disabled = true;
  previewBtn.innerHTML = `
    <svg class="animate-spin w-5 h-5" fill="none" viewBox="0 0 24 24">
      <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
      <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
    </svg>
    <span>Loading...</span>
  `;
  
  try {
    const response = await fetch(`/admin/projects/${projectId}/preview-bulk-adjustment?days=${days}&direction=${direction}`, {
      headers: {
        'X-Requested-With': 'XMLHttpRequest',
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
      }
    });
    
    const data = await response.json();
    
    if (data.success) {
      displayBulkAdjustmentPreview(data);
      document.getElementById('bulkAdjustError').classList.add('hidden');
    } else {
      showBulkAdjustError(data.message || 'Failed to preview adjustment');
    }
  } catch (error) {
    console.error('Error previewing bulk adjustment:', error);
    showBulkAdjustError('An error occurred while previewing changes');
  } finally {
    previewBtn.disabled = false;
    previewBtn.innerHTML = `
      <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
      </svg>
      <span>Preview Changes</span>
    `;
  }
}

/**
 * Display bulk adjustment preview
 */
function displayBulkAdjustmentPreview(data) {
  const section = document.getElementById('bulkAdjustPreviewSection');
  const list = document.getElementById('bulkPreviewList');
  const count = document.getElementById('bulkAffectedCount');
  const endDate = document.getElementById('bulkNewEndDate');
  
  count.textContent = data.affected_count;
  endDate.textContent = formatDate(data.new_end_date);
  
  let html = '';
  data.preview.forEach(item => {
    html += `
      <div class="flex items-center justify-between py-2 border-b border-gray-200 last:border-0">
        <div class="flex items-center gap-2">
          <span class="w-6 h-6 rounded-full bg-purple-100 text-purple-700 text-xs font-bold flex items-center justify-center">${item.sequence_order}</span>
          <span class="font-medium text-gray-900 text-sm">${item.title}</span>
        </div>
        <div class="text-xs text-gray-600">
          ${formatDate(item.current_date)} → ${formatDate(item.new_date)}
        </div>
      </div>
    `;
  });
  
  list.innerHTML = html;
  section.classList.remove('hidden');
}

/**
 * Show bulk adjust error
 */
function showBulkAdjustError(message) {
  const errorDiv = document.getElementById('bulkAdjustError');
  const errorMessage = document.getElementById('bulkAdjustErrorMessage');
  
  errorMessage.textContent = message;
  errorDiv.classList.remove('hidden');
  
  // Scroll to error
  errorDiv.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
}

/**
 * Submit bulk adjustment
 */
document.addEventListener('DOMContentLoaded', function() {
  const reasonTextarea = document.getElementById('bulkAdjustReason');
  
  // Character count for reason
  if (reasonTextarea) {
    reasonTextarea.addEventListener('input', function() {
      const count = this.value.length;
      document.getElementById('bulkReasonCharCount').textContent = count;
      
      if (count < 10) {
        document.getElementById('bulkReasonCharCount').classList.add('text-red-500');
      } else {
        document.getElementById('bulkReasonCharCount').classList.remove('text-red-500');
      }
    });
  }
  
  // Form submission
  const form = document.getElementById('bulkAdjustDatesForm');
  if (form) {
    form.addEventListener('submit', async function(e) {
      e.preventDefault();
      
      // Show confirmation modal instead of browser alert
      showBulkAdjustConfirmModal();
    });
  }
});

// Show bulk adjust confirmation modal
window.showBulkAdjustConfirmModal = function() {
  const formData = new FormData(document.getElementById('bulkAdjustDatesForm'));
  const days = parseInt(formData.get('days'));
  const direction = formData.get('direction');
  const reason = formData.get('reason');
  
  // Validate minimum days
  if (!days || days < 1) {
    alert('Please enter at least 1 day for the adjustment.');
    return;
  }
  
  // Get affected count from preview if available
  const affectedCountEl = document.getElementById('bulkAffectedCount');
  const affectedCount = affectedCountEl ? affectedCountEl.textContent : '0';
  
  // Populate confirmation modal
  document.getElementById('confirmAffectedCount').textContent = affectedCount;
  document.getElementById('confirmDaysCount').textContent = days;
  document.getElementById('confirmDirection').textContent = direction;
  document.getElementById('confirmReason').textContent = reason;
  
  // Show modal
  const modal = document.getElementById('bulkAdjustConfirmModal');
  if (modal) {
    modal.classList.remove('hidden');
  }
};

// Hide bulk adjust confirmation modal
window.hideBulkAdjustConfirmModal = function() {
  const modal = document.getElementById('bulkAdjustConfirmModal');
  if (modal) {
    modal.classList.add('hidden');
  }
};

// Confirm and execute bulk adjustment
window.confirmBulkAdjustment = async function() {
  // Hide confirmation modal
  hideBulkAdjustConfirmModal();
  
  const form = document.getElementById('bulkAdjustDatesForm');
  const submitBtn = document.getElementById('submitBulkAdjustBtn');
  const formData = new FormData(form);
  const projectId = formData.get('project_id');
  
  // Disable submit button
  submitBtn.disabled = true;
  submitBtn.innerHTML = `
    <svg class="animate-spin w-5 h-5" fill="none" viewBox="0 0 24 24">
      <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
      <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
    </svg>
    <span>Applying...</span>
  `;
  
  try {
    const response = await fetch(`/admin/projects/${projectId}/bulk-adjust-dates`, {
      method: 'POST',
      headers: {
        'X-Requested-With': 'XMLHttpRequest',
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
      },
      body: formData
    });
    
    const data = await response.json();
    
    if (data.success) {
      showSuccessToast(data.message || 'Milestone dates adjusted successfully');
      
      // Hide modal
      hideBulkAdjustDatesModal();
      
      // Refresh project details if modal is open
      if (typeof refreshCurrentProjectModal === 'function') {
        refreshCurrentProjectModal();
      }
      
      // Refresh table
      if (typeof window.refreshProjectsTable === 'function') {
        window.refreshProjectsTable();
      }
    } else {
      showBulkAdjustError(data.message || 'Failed to adjust milestone dates');
    }
  } catch (error) {
    console.error('Error submitting bulk adjustment:', error);
    showBulkAdjustError('An error occurred while applying changes');
  } finally {
    // Re-enable submit button
    submitBtn.disabled = false;
    submitBtn.innerHTML = `
      <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
      </svg>
      <span>Apply Changes</span>
    `;
  }
};

// ── Change Bidder ──────────────────────────────────────────────────────────────

function _changeBidderNotify(message, type) {
  const notification = document.createElement('div');
  notification.className = `fixed top-4 right-4 px-6 py-3 rounded-lg shadow-lg text-white font-medium z-[70] transition-all duration-300 transform translate-x-full ${
    type === 'success' ? 'bg-green-500' : 'bg-red-500'
  }`;
  notification.textContent = message;
  document.body.appendChild(notification);
  setTimeout(() => { notification.style.transform = 'translateX(0)'; }, 10);
  setTimeout(() => {
    notification.style.transform = 'translateX(150%)';
    setTimeout(() => notification.remove(), 300);
  }, 3000);
}

let _changeBidderProjectId = null;
let _changeBidderBidId = null;

window.showChangeBidderModal = function(projectId, bidId, contractorName) {
  _changeBidderProjectId = projectId;
  _changeBidderBidId = bidId;

  const nameEl = document.getElementById('changeBidderContractorName');
  if (nameEl) nameEl.textContent = contractorName;

  const modal = document.getElementById('changeBidderModal');
  if (modal) modal.classList.remove('hidden');
};

window.hideChangeBidderModal = function() {
  const modal = document.getElementById('changeBidderModal');
  if (modal) modal.classList.add('hidden');
  _changeBidderProjectId = null;
  _changeBidderBidId = null;
};

window.confirmChangeBidder = async function() {
  if (!_changeBidderProjectId || !_changeBidderBidId) return;

  const btn = document.getElementById('confirmChangeBidderBtn');
  if (btn) {
    btn.disabled = true;
    btn.innerHTML = '<svg class="animate-spin w-4 h-4" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path></svg> Saving...';
  }

  try {
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';

    const response = await fetch(`/admin/project-management/${_changeBidderProjectId}/change-bidder`, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': csrfToken
      },
      body: JSON.stringify({ bid_id: _changeBidderBidId })
    });

    const rawText = await response.text();
    let result;
    try {
      result = JSON.parse(rawText);
    } catch (e) {
      _changeBidderNotify('Server returned unexpected response.', 'error');
      return;
    }

    if (result.success) {
      _changeBidderNotify('Bidder changed successfully', 'success');
      const projectId = _changeBidderProjectId;
      hideChangeBidderModal();
      hideBiddingModal();
      if (typeof window.refreshProjectsTable === 'function') {
        window.refreshProjectsTable();
      }
      if (projectId) {
        setTimeout(() => window.showOpenProjectModal(projectId), 400);
      }
    } else {
      _changeBidderNotify(result.message || 'Failed to change bidder', 'error');
    }
  } catch (error) {
    console.error('Error changing bidder:', error);
    _changeBidderNotify('An error occurred while changing bidder', 'error');
  } finally {
    if (btn) {
      btn.disabled = false;
      btn.innerHTML = '<i class="fi fi-rr-refresh text-sm"></i> Confirm Change';
    }
  }
};

// ═══════════════════════════════════════════════════════════════════════════
// PROJECT SUMMARY MODAL (mirrors mobile projectSummary.tsx)
// Used for in_progress and terminated projects
// ═══════════════════════════════════════════════════════════════════════════

/**
 * Show the standalone project summary modal (for in_progress / terminated)
 */
window.showProjectSummaryModal = async function(projectId) {
  const modal = document.getElementById('projectSummaryModal');
  if (!modal) return;

  // Show modal with loading state
  modal.classList.remove('hidden');

  try {
    const response = await fetch(`/admin/project-management/${projectId}/summary`, {
      headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' }
    });
    const result = await response.json();

    if (result.success && result.html) {
      const body = document.getElementById('psmBody');
      if (body) body.innerHTML = result.html;
    } else {
      const body = document.getElementById('psmBody');
      if (body) body.innerHTML = '<p class="text-center text-red-500 py-8 text-sm">Failed to load project summary.</p>';
    }
  } catch (err) {
    console.error('Error loading project summary:', err);
    const body = document.getElementById('psmBody');
    if (body) body.innerHTML = '<p class="text-center text-red-500 py-8 text-sm">An error occurred while loading the summary.</p>';
  }
};

/**
 * Hide the standalone project summary modal
 */
window.hideProjectSummaryModal = function() {
  const modal = document.getElementById('projectSummaryModal');
  if (modal) modal.classList.add('hidden');
};

/**
 * Toggle collapsible sections inside the summary content
 */
window.psmToggle = function(sectionId) {
  const section = document.getElementById(sectionId);
  if (!section) return;

  const isHidden = section.classList.contains('hidden');
  section.classList.toggle('hidden', !isHidden);

  // Rotate chevron on the parent button
  const btn = section.previousElementSibling;
  if (btn) {
    const chevron = btn.querySelector('.psm-chevron');
    if (chevron) {
      chevron.classList.toggle('rotate-180', !isHidden);
    }
  }
};

// ── Completed modal: toggle project summary section ──────────────────────────

window.toggleCompletedProjectSummary = async function() {
  const section = document.getElementById('completedProjectSummarySection');
  const label   = document.getElementById('completedSummaryToggleLabel');
  if (!section) return;

  const isHidden = section.classList.contains('hidden');

  if (isHidden) {
    section.classList.remove('hidden');
    if (label) label.textContent = 'Hide Project Summary';

    // Load content if not yet loaded
    const content = document.getElementById('completedProjectSummaryContent');
    if (content && content.querySelector('p.text-gray-400')) {
      const projectId = window.currentProjectId || document.getElementById('completedProjectModal')?.dataset?.projectId;
      if (projectId) {
        try {
          const response = await fetch(`/admin/project-management/${projectId}/summary`, {
            headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' }
          });
          const result = await response.json();
          if (result.success && result.html) {
            content.innerHTML = result.html;
          } else {
            content.innerHTML = '<p class="text-center text-red-500 py-4 text-sm">Failed to load summary.</p>';
          }
        } catch (err) {
          content.innerHTML = '<p class="text-center text-red-500 py-4 text-sm">Error loading summary.</p>';
        }
      }
    }
  } else {
    section.classList.add('hidden');
    if (label) label.textContent = 'View Project Summary';
  }
};

// ── Halted modal: toggle project summary section ─────────────────────────────

window.toggleHaltedProjectSummary = async function(projectId) {
  const section = document.getElementById('haltedProjectSummarySection');
  const label   = document.getElementById('haltedSummaryToggleLabel');
  if (!section) return;

  const isHidden = section.classList.contains('hidden');

  if (isHidden) {
    section.classList.remove('hidden');
    if (label) label.textContent = 'Hide Project Summary';

    // Load content if not yet loaded
    const content = document.getElementById('haltedProjectSummaryContent');
    if (content && content.querySelector('p.text-gray-400')) {
      const pid = projectId || window.currentProjectId;
      if (pid) {
        try {
          const response = await fetch(`/admin/project-management/${pid}/summary`, {
            headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' }
          });
          const result = await response.json();
          if (result.success && result.html) {
            content.innerHTML = result.html;
          } else {
            content.innerHTML = '<p class="text-center text-red-500 py-4 text-sm">Failed to load summary.</p>';
          }
        } catch (err) {
          content.innerHTML = '<p class="text-center text-red-500 py-4 text-sm">Error loading summary.</p>';
        }
      }
    }
  } else {
    section.classList.add('hidden');
    if (label) label.textContent = 'View Project Summary';
  }
};


// ─────────────────────────────────────────────────────────────────────────────
// PROJECT SUMMARY MODAL
// ─────────────────────────────────────────────────────────────────────────────

/**
 * Toggle a collapsible section inside the project summary modal.
 */
function psmToggle(contentId, chevronId) {
  const content = document.getElementById(contentId);
  const chevron = document.getElementById(chevronId);
  if (!content) return;
  const isHidden = content.classList.contains('hidden');
  content.classList.toggle('hidden', !isHidden);
  if (chevron) chevron.classList.toggle('rotate-180', isHidden);
}

/**
 * Open the project summary modal and load content via AJAX.
 */
async function showProjectSummaryModal(projectId) {
  const modal    = document.getElementById('projectSummaryModal');
  const body     = document.getElementById('psmBody');
  const errorEl  = null;   // error shown inline in body
  const loadEl   = null;   // loading spinner already in psmBody default state

  if (!modal || !body) return;

  // Reset state — show spinner
  modal.classList.remove('hidden');
  body.innerHTML = `
    <div class="flex items-center justify-center py-12">
      <div class="text-center">
        <svg class="w-10 h-10 text-gray-300 mx-auto mb-3 animate-spin" fill="none" viewBox="0 0 24 24">
          <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
          <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
        </svg>
        <p class="text-sm text-gray-500">Loading summary…</p>
      </div>
    </div>`;

  try {
    const res = await fetch(`/admin/project-management/${projectId}/summary`, {
      headers: {
        'X-Requested-With': 'XMLHttpRequest',
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content ?? '',
      },
    });

    const html = await res.text();

    if (!res.ok) {
      body.innerHTML = `<div class="p-6 text-center text-red-600 font-semibold">Server error (${res.status}). Please try again.</div>`;
      return;
    }

    body.innerHTML = html;
  } catch (err) {
    body.innerHTML = `<div class="p-6 text-center text-red-600 font-semibold">Failed to load summary. Please try again.</div>`;
    console.error('Project summary fetch error:', err);
  }
}

/**
 * Close the project summary modal.
 */
function hideProjectSummaryModal() {
  const modal = document.getElementById('projectSummaryModal');
  if (modal) modal.classList.add('hidden');
}

/**
 * Toggle the inline summary section inside the completed project modal.
 */
async function toggleCompletedProjectSummary(projectId) {
  const section   = document.getElementById('completedProjectSummarySection');
  const content   = document.getElementById('completedProjectSummaryContent');
  const btnLabel  = document.getElementById('completedSummaryToggleLabel');
  if (!section) return;

  const isHidden = section.classList.contains('hidden');
  section.classList.toggle('hidden', !isHidden);
  if (btnLabel) btnLabel.textContent = isHidden ? 'Hide Project Summary' : 'View Project Summary';

  // Load content on first open
  if (isHidden && content && content.dataset.loaded !== '1') {
    content.dataset.loaded = '1';
    try {
      const res  = await fetch(`/admin/project-management/${projectId}/summary`, {
        headers: { 'X-Requested-With': 'XMLHttpRequest', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content ?? '' },
      });
      content.innerHTML = res.ok ? await res.text() : `<p class="text-sm text-red-500 text-center py-4">Failed to load summary.</p>`;
    } catch (e) {
      content.innerHTML = `<p class="text-sm text-red-500 text-center py-4">Failed to load summary.</p>`;
    }
  }
}

/**
 * Toggle the inline summary section inside the halted project modal.
 */
async function toggleHaltedProjectSummary(projectId) {
  const section   = document.getElementById('haltedProjectSummarySection');
  const content   = document.getElementById('haltedProjectSummaryContent');
  const btnLabel  = document.getElementById('haltedSummaryToggleLabel');
  if (!section) return;

  const isHidden = section.classList.contains('hidden');
  section.classList.toggle('hidden', !isHidden);
  if (btnLabel) btnLabel.textContent = isHidden ? 'Hide Project Summary' : 'View Project Summary';

  // Load content on first open
  if (isHidden && content && content.dataset.loaded !== '1') {
    content.dataset.loaded = '1';
    try {
      const res  = await fetch(`/admin/project-management/${projectId}/summary`, {
        headers: { 'X-Requested-With': 'XMLHttpRequest', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content ?? '' },
      });
      content.innerHTML = res.ok ? await res.text() : `<p class="text-sm text-red-500 text-center py-4">Failed to load summary.</p>`;
    } catch (e) {
      content.innerHTML = `<p class="text-sm text-red-500 text-center py-4">Failed to load summary.</p>`;
    }
  }
}

// Document Viewer Functions
window.openDocumentViewer = function(docSrc, docTitle = 'Document Viewer') {
  const modal = document.getElementById('documentViewerModal');
  const content = document.getElementById('docViewerContent');
  const title = document.getElementById('docViewerTitle');
  const downloadBtn = document.getElementById('docViewerDownload');
  
  if (modal && content && title) {
    title.textContent = docTitle;
    
    // Set download link
    if (downloadBtn) {
      downloadBtn.href = docSrc;
    }
    
    // Detect file type from extension
    const extension = docSrc.split('.').pop().toLowerCase();
    const imageExtensions = ['jpg', 'jpeg', 'png', 'gif', 'webp', 'svg', 'bmp'];
    const pdfExtensions = ['pdf'];
    
    // Clear previous content
    content.innerHTML = '';
    
    if (imageExtensions.includes(extension)) {
      // Display as image
      const img = document.createElement('img');
      img.src = docSrc;
      img.alt = docTitle;
      img.className = 'max-w-full max-h-full object-contain rounded-lg';
      img.onerror = function() {
        content.innerHTML = '<div class="text-white text-center"><p class="text-lg mb-2">Failed to load image</p><p class="text-sm text-gray-400">The file may not exist or is inaccessible</p></div>';
      };
      content.appendChild(img);
    } else if (pdfExtensions.includes(extension)) {
      // Display PDF in iframe with embed fallback
      const iframe = document.createElement('iframe');
      iframe.src = docSrc;
      iframe.className = 'w-full h-full rounded-lg';
      iframe.frameBorder = '0';
      iframe.onerror = function() {
        content.innerHTML = `<div class="text-white text-center p-6"><p class="text-lg mb-4">Unable to display PDF</p><a href="${docSrc}" download class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg inline-flex items-center gap-2"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>Download File</a></div>`;
      };
      content.appendChild(iframe);
    } else {
      // For other file types, show download option
      content.innerHTML = `
        <div class="text-white text-center p-6">
          <svg class="w-16 h-16 mx-auto mb-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/>
          </svg>
          <p class="text-lg mb-2">Preview not available</p>
          <p class="text-sm text-gray-400 mb-4">This file type cannot be previewed in the browser</p>
          <a href="${docSrc}" download class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg inline-flex items-center gap-2">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
            </svg>
            Download File
          </a>
        </div>
      `;
    }
    
    modal.classList.remove('hidden');
  }
};

window.closeDocumentViewer = function() {
  const modal = document.getElementById('documentViewerModal');
  const content = document.getElementById('docViewerContent');
  
  if (modal && content) {
    modal.classList.add('hidden');
    content.innerHTML = '';
  }
};

// Event delegation for document viewer buttons
document.addEventListener('click', function(e) {
  const btn = e.target.closest('.open-doc-btn');
  if (btn) {
    e.preventDefault();
    const docSrc = btn.getAttribute('data-doc-src');
    const docTitle = btn.getAttribute('data-doc-title') || 'Document Viewer';
    if (docSrc) {
      openDocumentViewer(docSrc, docTitle);
    }
  }
});
